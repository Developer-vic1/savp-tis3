from __future__ import annotations

import hashlib
import http.client
import ipaddress
import re
import socket
import ssl
import threading
import time
from collections.abc import Callable
from concurrent.futures import ThreadPoolExecutor
from concurrent.futures import TimeoutError as FutureTimeout
from datetime import UTC, datetime
from typing import Any, Literal, cast
from urllib.parse import quote, urljoin, urlsplit, urlunsplit

from bs4 import BeautifulSoup
from pydantic import Field

from app.knowledge.governance import (
    GovernanceModel,
    SourceUrlAssessment,
    _load_intake_registry,
    _normalize_url,
    assess_bolivian_university_url,
)
from app.knowledge.registry import load_source_manifest

MAX_BYTES = 4 * 1024 * 1024
DEADLINE_SECONDS = 12
_DNS_POOL = ThreadPoolExecutor(max_workers=2, thread_name_prefix="source-dns")
_DNS_SLOTS = threading.BoundedSemaphore(2)
_FETCH_SLOTS = threading.BoundedSemaphore(2)


class SourcePreviewResponse(GovernanceModel):
    schema_version: str = "1.0"
    trace_id: str = "pending"
    requested_url: str
    final_url: str | None = None
    status: Literal["VERIFICADA", "DUPLICADA", "BLOQUEADA", "NO_DISPONIBLE", "REQUIERE_REVISION"]
    reachable: bool | None = None
    http_status: int | None = None
    assessment: SourceUrlAssessment
    checked_at: datetime = Field(default_factory=lambda: datetime.now(UTC))
    content_type: str | None = None
    document_hash: str | None = None
    title: str | None = None
    excerpt: str = ""
    suggested_fields: dict[str, str] = Field(default_factory=dict)
    duplicate: dict[str, str] | None = None
    warnings: list[str] = Field(default_factory=list)
    message: str
    can_use: bool = False
    reading_fragments: list[dict[str, str]] = Field(default_factory=list, max_length=8)


class PreviewFailure(Exception):
    pass


class PinnedHTTPSConnection(http.client.HTTPSConnection):
    def __init__(self, host: str, address: str, timeout: float) -> None:
        self.tls_context = ssl.create_default_context()
        super().__init__(host, port=443, timeout=timeout, context=self.tls_context)
        self.address = address

    def connect(self) -> None:
        raw = socket.create_connection((self.address, 443), timeout=self.timeout)
        try:
            self.sock = self.tls_context.wrap_socket(raw, server_hostname=self.host)
        except BaseException:
            raw.close()
            raise


def _remaining(deadline: float) -> float:
    remaining = deadline - time.monotonic()
    if remaining <= 0:
        raise PreviewFailure("La comprobación agotó su tiempo; no se confirmó la URL.")
    return remaining


def _public_address(host: str, deadline: float) -> str:
    if not _DNS_SLOTS.acquire(blocking=False):
        raise PreviewFailure("El verificador está ocupado. Espere e intente nuevamente.")
    try:
        future = _DNS_POOL.submit(socket.getaddrinfo, host, 443, 0, socket.SOCK_STREAM)
    except BaseException:
        _DNS_SLOTS.release()
        raise
    future.add_done_callback(lambda _: _DNS_SLOTS.release())
    try:
        records = future.result(timeout=min(3, _remaining(deadline)))
    except (OSError, FutureTimeout) as error:
        future.cancel()
        raise PreviewFailure(
            "No se pudo resolver el dominio; no se confirmó su existencia."
        ) from error
    addresses = list(dict.fromkeys(str(record[4][0]) for record in records))
    if not addresses or any(not _is_public_address(address) for address in addresses):
        raise PreviewFailure(
            "El dominio resuelve a un destino privado o reservado y fue bloqueado."
        )
    return addresses[0]


def _is_public_address(value: str) -> bool:
    address = ipaddress.ip_address(value)
    if not address.is_global or address.is_multicast or address.is_reserved:
        return False
    if isinstance(address, ipaddress.IPv6Address):
        if address.sixtofour or address.teredo or address in ipaddress.ip_network("64:ff9b::/96"):
            return False
        if address.ipv4_mapped:
            return _is_public_address(str(address.ipv4_mapped))
    return True


def _fetch_document(url: str) -> tuple[str, int, str, bytes]:
    deadline = time.monotonic() + DEADLINE_SECONDS
    current = url
    institution = assess_bolivian_university_url(url).recognized_institution
    visited: set[str] = set()
    for _ in range(4):
        current_assessment = assess_bolivian_university_url(current)
        if (
            current_assessment.status != "ACEPTADA"
            or current_assessment.recognized_institution != institution
        ):
            raise PreviewFailure(
                "La redirección no pertenece a una universidad confiable con HTTPS."
            )
        # /carrera y /carrera/ pueden ser un salto canónico legítimo del servidor.
        # La clave de duplicidad editorial elimina la barra, la de redirección no.
        redirect_parts = urlsplit(current)
        normalized = urlunsplit(redirect_parts._replace(fragment=""))
        if normalized in visited:
            raise PreviewFailure("La página tiene un bucle de redirecciones.")
        visited.add(normalized)
        parsed = urlsplit(current)
        host = (parsed.hostname or "").rstrip(".")
        address = _public_address(host, deadline)
        connection = PinnedHTTPSConnection(host, address, min(4, _remaining(deadline)))
        try:
            target = quote(parsed.path or "/", safe="/%:@!$&'()*+,;=-._~")
            if parsed.query:
                target += "?" + quote(parsed.query, safe="%=&/:?@!$'()*+,;~-._")
            connection.request(
                "GET",
                target,
                headers={
                    "Accept": "text/html,application/pdf",
                    "Accept-Encoding": "identity",
                    "User-Agent": "SAVP-SourceVerifier/1.0",
                },
            )
            if connection.sock:
                connection.sock.settimeout(min(4, _remaining(deadline)))
            response = connection.getresponse()
            if response.status in {301, 302, 303, 307, 308}:
                location = response.getheader("Location")
                if not location or len(location) > 2000:
                    raise PreviewFailure("La redirección no tiene un destino válido.")
                current = urljoin(current, location)
                continue
            content_type = (response.getheader("Content-Type") or "").split(";")[0].lower().strip()
            if response.status != 200:
                return current, response.status, content_type, b""
            if content_type not in {"text/html", "application/xhtml+xml", "application/pdf"}:
                raise PreviewFailure(
                    "El servidor no devuelve HTML ni PDF; no se abrirá ese contenido."
                )
            encoding = (response.getheader("Content-Encoding") or "identity").lower()
            if encoding != "identity":
                raise PreviewFailure(
                    "El servidor envió contenido comprimido no admitido por este verificador."
                )
            length = response.getheader("Content-Length")
            if length and (not length.isdigit() or int(length) > MAX_BYTES):
                raise PreviewFailure(
                    "El documento supera el límite de 4 MiB o declara un tamaño inválido."
                )
            pieces: list[bytes] = []
            size = 0
            while True:
                if connection.sock:
                    connection.sock.settimeout(min(4, _remaining(deadline)))
                _remaining(deadline)
                piece = response.read1(min(65536, MAX_BYTES + 1 - size))
                if not piece:
                    break
                pieces.append(piece)
                size += len(piece)
                if size > MAX_BYTES:
                    raise PreviewFailure("El documento supera el límite de 4 MiB.")
            return (
                urlunsplit(parsed._replace(fragment="")),
                response.status,
                content_type,
                b"".join(pieces),
            )
        finally:
            connection.close()
    raise PreviewFailure("La página supera el máximo de tres redirecciones.")


def _text(value: str, maximum: int) -> str:
    return re.sub(r"\s+", " ", re.sub(r"[\x00-\x1f\x7f]", " ", value)).strip()[:maximum]


def _reading_fragments(excerpt: str, document_hash: str, url: str) -> list[dict[str, str]]:
    # Lectura preliminar del extracto. No sustituye el corpus aprobado ni sus índices.
    from app.ingestion.chunking import _split_text

    return [
        {"id": hashlib.sha256(f"{document_hash}|{ordinal}|{text}".encode()).hexdigest()[:24],
         "text": text, "url": url, "document_hash": document_hash}
        for ordinal, text in enumerate(_split_text(excerpt, 600, 80)[:8], start=1)
    ]


def _extract_metadata(content: bytes, content_type: str) -> tuple[str | None, str, str | None]:
    if content_type == "application/pdf":
        import pymupdf

        if not content.startswith(b"%PDF-"):
            raise PreviewFailure("El contenido no coincide con el PDF declarado.")
        open_document = cast(Callable[..., Any], pymupdf.open)
        with open_document(stream=content, filetype="pdf") as document:
            if document.needs_pass or document.page_count == 0:
                raise PreviewFailure("El PDF está protegido o no contiene páginas legibles.")
            metadata_title = str((document.metadata or {}).get("title") or "")
            first_page = document[0].get_text("text")[:12000]
            if not first_page.strip():
                raise PreviewFailure(
                    "El PDF no tiene texto digital en la primera página; "
                    "no se inventan datos ni se aplica OCR."
                )
            title = _text(metadata_title, 240)
            if len(title) < 3 or re.search(
                r"microsoft|untitled|sin t[ií]tulo|\.docx?$", title, re.I
            ):
                lines = [
                    _text(line, 240) for line in first_page.splitlines() if len(line.strip()) >= 5
                ]
                title = next(
                    (
                        line
                        for line in lines
                        if re.search(r"malla|curricular|plan de estudios|reglamento", line, re.I)
                    ),
                    "",
                )
            return title or None, _text(first_page, 1500), None
    soup = BeautifulSoup(content, "lxml")
    heading = soup.find("h1")
    if heading and len(_text(heading.get_text(" "), 240)) < 3:
        heading = None
    open_graph = soup.find("meta", attrs={"property": "og:title"})
    title_node = soup.find("title")
    title = _text(
        heading.get_text(" ")
        if heading
        else str(open_graph.get("content", ""))
        if open_graph
        else title_node.get_text(" ")
        if title_node
        else "",
        240,
    )
    date_node = soup.find("meta", attrs={"property": "article:published_time"}) or soup.find(
        "meta", attrs={"name": "date"}
    )
    date = str(date_node.get("content", ""))[:10] if date_node else None
    if date:
        try:
            datetime.strptime(date, "%Y-%m-%d")
        except ValueError:
            date = None
    for node in soup(["script", "style", "noscript", "iframe", "nav", "footer", "header"]):
        node.decompose()
    root = soup.find("main") or soup.find("article") or soup.body or soup
    paragraphs = [node.get_text(" ", strip=True) for node in root.find_all("p")]
    useful = [text for text in paragraphs if len(text) >= 80]
    description_node = soup.find("meta", attrs={"name": "description"})
    description = str(description_node.get("content", "")) if description_node else ""
    excerpt = (
        description
        if len(description.strip()) >= 40
        else " ".join(useful)
        if useful
        else root.get_text(" ", strip=True)
    )
    return title or None, _text(excerpt, 1500), date


def _duplicate(urls: list[str], document_hash: str | None = None) -> dict[str, str] | None:
    normalized = {_normalize_url(url) for url in urls}
    for source in load_source_manifest().sources:
        if _normalize_url(source.url) in normalized or (
            document_hash and source.document_hash == document_hash
        ):
            return {
                "kind": "CORPUS",
                "id": source.source_id,
                "title": source.title,
                "status": "ACTIVA",
                "url": source.url,
            }
    for proposal in _load_intake_registry().proposals:
        if _normalize_url(proposal.url) in normalized:
            return {
                "kind": "PROPUESTA",
                "id": proposal.proposal_id,
                "title": proposal.title,
                "status": proposal.status,
                "url": proposal.url,
            }
    return None


def inspect_source_url(url: str) -> SourcePreviewResponse:
    assessment = assess_bolivian_university_url(url)
    base = {"requested_url": url, "assessment": assessment}
    if assessment.status != "ACEPTADA":
        return SourcePreviewResponse(
            **base,
            status="BLOQUEADA" if assessment.status == "RECHAZADA" else "REQUIERE_REVISION",
            message=assessment.message,
        )
    duplicate = _duplicate([url])
    if duplicate:
        return SourcePreviewResponse(
            **base,
            status="DUPLICADA",
            duplicate=duplicate,
            title=duplicate["title"],
            message=(
                "La URL ya figura en el corpus o en estudio. "
                "No se descargó nuevamente ni se creará otra propuesta."
            ),
        )
    if not _FETCH_SLOTS.acquire(blocking=False):
        return SourcePreviewResponse(
            **base,
            status="NO_DISPONIBLE",
            message="El verificador está ocupado. Espere e intente nuevamente.",
        )
    try:
        final_url, status, content_type, content = _fetch_document(url)
        if status != 200:
            return SourcePreviewResponse(
                **base,
                status="NO_DISPONIBLE",
                final_url=final_url,
                http_status=status,
                reachable=False if status in {404, 410} else None,
                message="La página no se encontró."
                if status in {404, 410}
                else f"La universidad respondió HTTP {status}; su contenido no pudo verificarse.",
            )
        title, excerpt, publication_date = _extract_metadata(content, content_type)
        document_hash = "sha256:" + hashlib.sha256(content).hexdigest()
        duplicate = _duplicate([url, final_url], document_hash)
        if title and re.search(
            r"^404|not found|p[aá]gina no encontrada|access denied|just a moment", title, re.I
        ):
            return SourcePreviewResponse(
                **base,
                status="NO_DISPONIBLE",
                final_url=final_url,
                http_status=status,
                message=(
                    "El sitio devolvió una página de error o de protección; "
                    "HTTP 200 no demuestra que el documento exista."
                ),
            )
        if duplicate:
            return SourcePreviewResponse(
                **base,
                status="DUPLICADA",
                final_url=final_url,
                http_status=status,
                reachable=True,
                title=title,
                duplicate=duplicate,
                document_hash=document_hash,
                excerpt=excerpt,
                message=(
                    "La URL final o el hash del documento coincide con una fuente ya registrada."
                ),
            )
        final_assessment = assess_bolivian_university_url(final_url)
        fields = {"declared_institution": final_assessment.recognized_institution or ""}
        if title:
            fields["title"] = title
        type_text = f"{title or ''} {excerpt}".casefold()
        if content_type == "application/pdf":
            if re.search(r"malla|plan de estudios|curricular", type_text):
                fields["source_type"] = "OFFICIAL_CURRICULUM_PDF"
            elif re.search(r"reglamento", type_text):
                fields["source_type"] = "OFFICIAL_REGULATION_PDF"
        else:
            fields["source_type"] = (
                "OFFICIAL_CAREER_HTML"
                if re.search(r"perfil profesional|plan de estudios|malla curricular", type_text)
                else "OFFICIAL_UNIVERSITY_PAGE"
            )
        if excerpt:
            fields["scope"] = excerpt[:500]
        if publication_date:
            fields["publication_date"] = publication_date
        warnings = [
            "Los datos detectados son sugerencias; confirme su alcance y pertinencia.",
            "No se inventan sede, ciudad, versión ni justificación. "
            "Complete lo que no esté publicado.",
        ]
        if content_type == "application/pdf":
            warnings.append("La vista previa recoge sólo la primera página digital del PDF.")
        if not title or len(title) < 3:
            warnings.append(
                "No se encontró un título publicado utilizable; no se habilitará la propuesta."
            )
        return SourcePreviewResponse(
            **{**base, "assessment": final_assessment},
            reading_fragments=_reading_fragments(excerpt, document_hash, final_url),
            status="VERIFICADA" if title and len(title) >= 3 else "REQUIERE_REVISION",
            reachable=True,
            final_url=final_url,
            http_status=status,
            content_type=content_type,
            document_hash=document_hash,
            title=title,
            excerpt=excerpt,
            suggested_fields=fields,
            warnings=warnings,
            can_use=bool(title and len(title) >= 3),
            message=(
                "La página respondió y se leyó su contenido. "
                "Revise el título y los datos detectados antes del análisis."
            ),
        )
    except PreviewFailure as error:
        return SourcePreviewResponse(**base, status="NO_DISPONIBLE", message=str(error))
    except (OSError, http.client.HTTPException, ValueError, RuntimeError):
        return SourcePreviewResponse(
            **base,
            status="NO_DISPONIBLE",
            message=(
                "No se pudo verificar el documento por seguridad, acceso, tamaño, "
                "formato o conexión. Revise la URL; "
                "no se considera válida ni inexistente por este fallo."
            ),
        )
    finally:
        _FETCH_SLOTS.release()
