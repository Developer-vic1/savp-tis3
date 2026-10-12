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
from urllib.parse import quote, unquote, urljoin, urlsplit, urlunsplit

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


class EmbeddingAssessment(GovernanceModel):
    allowed: bool | None = None
    reason: str | None = Field(default=None, max_length=500)


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
    documents: list[dict[str, str]] = Field(default_factory=list, max_length=6)
    academic_pages: list[dict[str, str]] = Field(default_factory=list, max_length=6)
    document_kind: Literal["PUBLIC_DOCUMENT", "STUDENT_PORTAL", "AUTHENTICATION", "UNKNOWN"] = (
        "UNKNOWN"
    )
    embedding: EmbeddingAssessment = Field(default_factory=EmbeddingAssessment)


class FetchedDocument(tuple[str, int, str, bytes]):
    """Contrato iterable de cuatro valores; los encabezados no se publican ni se replican."""

    response_headers: dict[str, str]

    def __new__(
        cls, url: str, status: int, kind: str, content: bytes, headers: dict[str, str]
    ) -> FetchedDocument:
        value = super().__new__(cls, (url, status, kind, content))
        value.response_headers = headers
        return value


def _embedding(headers: dict[str, str]) -> EmbeddingAssessment:
    policy = headers.get("content-security-policy", "").lower()
    # frame-ancestors prevalece sobre X-Frame-Options; report-only no impone bloqueos.
    ancestors = re.search(r"(?:^|;)\s*frame-ancestors\s+([^;]*)", policy)
    if ancestors:
        sources = ancestors.group(1).split()
        if sources == ["'none'"] or sources == ["'self'"]:
            return EmbeddingAssessment(
                allowed=False,
                reason=(
                    "La universidad impide mostrar esta página dentro de otros sitios "
                    "según la política publicada por su servidor."
                ),
            )
        if "*" in sources:
            return EmbeddingAssessment(allowed=True)
        return EmbeddingAssessment(
            reason=(
                "La universidad limita los sitios que pueden incrustar esta página; "
                "el navegador comprobará su política."
            )
        )
    frame_options = headers.get("x-frame-options", "").strip().upper()
    if frame_options in {"DENY", "SAMEORIGIN"}:
        return EmbeddingAssessment(
            allowed=False,
            reason=(
                "La universidad impide mostrar esta página dentro de otros sitios "
                "según la política publicada por su servidor."
            ),
        )
    return EmbeddingAssessment(
        reason=(
            "No se detectó un bloqueo en los encabezados recibidos; "
            "el navegador puede aplicar otras restricciones."
        )
    )


def _document_kind(
    content: bytes, content_type: str, url: str
) -> Literal["PUBLIC_DOCUMENT", "STUDENT_PORTAL", "AUTHENTICATION", "UNKNOWN"]:
    if content_type == "application/pdf":
        return "PUBLIC_DOCUMENT"
    soup = BeautifulSoup(content, "lxml")
    title = _text(" ".join(node.get_text(" ") for node in soup.find_all(["title", "h1"])), 500)
    path = urlsplit(url).path.casefold()
    # Un botón de acceso o un enlace de menú no convierte una página pública en portal.
    login_title = bool(
        re.search(
            r"iniciar sesi[oó]n|inicio de sesi[oó]n|acceso al sistema|^login\b|autenticaci[oó]n",
            title,
            re.I,
        )
    )
    login_path = bool(re.search(r"/(?:login|signin|auth)(?:/|$)", path))
    if soup.find("input", attrs={"type": re.compile("^password$", re.I)}) and (
        login_title or login_path
    ):
        return "AUTHENTICATION"
    student_title = any(
        re.fullmatch(
            r"estudiante|alumno|portal (?:del )?estudiante|sistema acad[eé]mico(?:[\s:–-].*)?",
            _text(node.get_text(" "), 240),
            re.I,
        )
        for node in soup.find_all(["title", "h1"])
    )
    student_path = bool(re.search(r"/(?:estudiante|alumno|student)(?:/|$)", path))
    service_text = _text(soup.get_text(" "), 12000).casefold()
    services = sum(
        bool(re.search(pattern, service_text))
        for pattern in [
            r"\bnotas\b|calificaciones",
            r"\bdeudas\b|estado de cuenta",
            r"\bsolicitudes\b|solicitud",
            r"\bmatr[ií]cula\b|inscripci[oó]n",
        ]
    )
    if student_title and (student_path or services >= 2):
        return "STUDENT_PORTAL"
    for node in soup(
        ["title", "meta", "script", "style", "noscript", "iframe", "nav", "header", "footer"]
    ):
        node.decompose()
    if not soup.get_text(" ", strip=True):
        return "UNKNOWN"
    return "PUBLIC_DOCUMENT" if len(title.strip()) >= 3 else "UNKNOWN"


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


def _public_address(host: str, deadline: float, address_index: int = 0) -> str:
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
    # Un orden estable permite que el segundo intento pruebe otra IP pública,
    # incluso cuando el DNS cambia el orden de sus respuestas.
    addresses = sorted(set(str(record[4][0]) for record in records))
    if not addresses or any(not _is_public_address(address) for address in addresses):
        raise PreviewFailure(
            "El dominio resuelve a un destino privado o reservado y fue bloqueado."
        )
    return addresses[address_index % len(addresses)]


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


def _fetch_document(
    url: str,
    max_bytes: int = MAX_BYTES,
    *,
    deadline_seconds: float = DEADLINE_SECONDS,
    socket_timeout: float = 4,
    address_index: int = 0,
) -> tuple[str, int, str, bytes]:
    if max_bytes < 1 or max_bytes > 16 * 1024 * 1024:
        raise ValueError("El límite documental debe estar entre 1 byte y 16 MiB.")
    if not 1 <= deadline_seconds <= 30 or not 1 <= socket_timeout <= 15:
        raise ValueError("La descarga admite hasta 30 segundos totales y 15 por lectura.")
    if address_index not in {0, 1}:
        raise ValueError("Solo se admite un destino público alternativo por reintento.")
    deadline = time.monotonic() + deadline_seconds
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
        address = (
            _public_address(host, deadline)
            if address_index == 0
            else _public_address(host, deadline, address_index)
        )
        connection = PinnedHTTPSConnection(host, address, min(socket_timeout, _remaining(deadline)))
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
                connection.sock.settimeout(min(socket_timeout, _remaining(deadline)))
            response = connection.getresponse()
            if response.status in {301, 302, 303, 307, 308}:
                location = response.getheader("Location")
                if not location or len(location) > 2000:
                    raise PreviewFailure("La redirección no tiene un destino válido.")
                current = urljoin(current, location)
                continue
            content_type = (response.getheader("Content-Type") or "").split(";")[0].lower().strip()
            headers = {
                name.lower(): response.getheader(name) or ""
                for name in ("X-Frame-Options", "Content-Security-Policy")
            }
            if response.status != 200:
                return FetchedDocument(current, response.status, content_type, b"", headers)
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
            if length and (not length.isdigit() or int(length) > max_bytes):
                raise PreviewFailure(
                    f"El documento supera el límite de {max_bytes // (1024 * 1024)} MiB "
                    "o declara un tamaño inválido."
                )
            pieces: list[bytes] = []
            size = 0
            while True:
                if connection.sock:
                    connection.sock.settimeout(min(socket_timeout, _remaining(deadline)))
                _remaining(deadline)
                piece = response.read1(min(65536, max_bytes + 1 - size))
                if not piece:
                    break
                pieces.append(piece)
                size += len(piece)
                if size > max_bytes:
                    raise PreviewFailure(
                        f"El documento supera el límite de {max_bytes // (1024 * 1024)} MiB."
                    )
            return FetchedDocument(
                urlunsplit(parsed._replace(fragment="")),
                response.status,
                content_type,
                b"".join(pieces),
                headers,
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
        {
            "id": hashlib.sha256(f"{document_hash}|{ordinal}|{text}".encode()).hexdigest()[:24],
            "text": text,
            "url": url,
            "document_hash": document_hash,
        }
        for ordinal, text in enumerate(_split_text(excerpt, 600, 80)[:8], start=1)
    ]


def _suggested_academic_scope(paragraphs: list[str]) -> str:
    """Contenido explícito, sin convertir noticias o etiquetas en un alcance curricular."""
    academic = r"malla curricular|plan de estudios|perfil profesional|reglamento acad[eé]mico"
    editorial = r"aniversario|celebra|celebraci[oó]n|noticia|evento|inaugur|conmemora|felicita"
    evidence = [
        _text(paragraph, 500)
        for paragraph in paragraphs
        if len(paragraph.strip()) >= 40
        and re.search(academic, paragraph, re.I)
        and not re.search(editorial, paragraph, re.I)
    ]
    return _text(" ".join(evidence), 500)


def _extract_metadata(
    content: bytes,
    content_type: str,
    *,
    academic_fields: dict[str, str] | None = None,
) -> tuple[str | None, str, str | None]:
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
            if academic_fields is not None:
                scope = _suggested_academic_scope([first_page])
                if scope:
                    academic_fields["scope"] = scope
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
    # Menús y anclas sólo prueban que hay un enlace; no describen su documento.
    for node in root.find_all("a"):
        node.decompose()
    paragraphs = [node.get_text(" ", strip=True) for node in root.find_all("p")]
    if academic_fields is not None:
        scope = _suggested_academic_scope(paragraphs)
        if scope:
            academic_fields["scope"] = scope
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


def _private_academic_link(url: str) -> bool:
    """Descarta rutas operativas explícitas; no afirma que los otros destinos sean públicos."""
    parts = urlsplit(url)
    path = unquote(parts.path).casefold()
    host = parts.hostname or ""
    return bool(
        re.search(
            r"/(?:estudiantes?|alumnos?|students?|login|signin|auth|solicitud(?:es)?|"
            r"notas|calificaciones|deudas|datos|cuenta|accounts?|intranet)(?:[/_.-]|$)",
            path,
        )
        or re.search(r"(?:^|[.&])(?:token|session|password|student_id|cod_est)=", parts.query, re.I)
        or re.match(r"(?:intranet|estudiante|student|login)\.", host, re.I)
    )


def _academic_links(
    content: bytes, page_url: str
) -> tuple[list[dict[str, str]], list[dict[str, str]]]:
    """Candidatos publicados, pendientes de comprobar; no descarga sus destinos."""
    institution = assess_bolivian_university_url(page_url).recognized_institution
    if not institution:
        return [], []
    soup = BeautifulSoup(content, "lxml")
    for node in soup(["script", "style", "noscript", "iframe"]):
        node.decompose()
    documents: list[dict[str, str]] = []
    pages: list[dict[str, str]] = []
    seen: set[str] = set()
    for anchor in soup.find_all("a", href=True):
        href = str(anchor.get("href", "")).strip()
        if not href or href.startswith("#") or len(href) > 2000:
            continue
        candidate = urljoin(page_url, href)
        assessment = assess_bolivian_university_url(candidate)
        if (
            assessment.status != "ACEPTADA"
            or assessment.recognized_institution != institution
            or _private_academic_link(candidate)
        ):
            continue
        title = _text(anchor.get_text(" ", strip=True), 240)
        published_title = _text(str(anchor.get("title") or anchor.get("aria-label") or ""), 240)
        if len(title) < 3 or re.fullmatch(r"descargar|download|ver|abrir|pdf", title, re.I):
            title = published_title
        if len(title) < 3:
            continue
        path = urlsplit(candidate).path.casefold()
        is_pdf = path.endswith(".pdf")
        # Los menús no prueban contenido documental. Las páginas de carrera con
        # nombres explícitos son sólo destinos sugeridos; nunca extractos de evidencia.
        if is_pdf and anchor.find_parent(["nav", "header", "footer"]):
            continue
        if not is_pdf and re.search(r"\.(?:pdf|exe|zip|docx?|xlsx?|png|jpe?g|svg)(?:\.|$)", path):
            continue
        if not is_pdf and re.fullmatch(
            r"carreras?|pregrado|facultades?|oferta acad[eé]mica|malla|plan|reglamento|"
            r"estudiantes?|alumnos?|inicio|ver|abrir|descargar",
            title,
            re.I,
        ):
            continue
        evidence = f"{title} {urlsplit(candidate).path}"
        kind = (
            "reglamento"
            if re.search(r"reglamento", evidence, re.I)
            else "malla"
            if re.search(r"malla|curricular|plan[\s_-]*(?:de[\s_-]*)?estudios", evidence, re.I)
            else None
        )
        if not is_pdf:
            kind = (
                "reglamento"
                if re.search(r"reglamento\s+\w", title, re.I)
                else "plan"
                if re.search(r"malla curricular|plan de estudios|perfil profesional", title, re.I)
                else "carrera"
                if re.search(
                    r"carrera (?:de|en)|ingenier[ií]a\s+\w|licenciatura\s+\w|"
                    r"medicina|odontolog[ií]a|arquitectura|psicolog[ií]a",
                    title,
                    re.I,
                )
                else None
            )
        if not kind:
            continue
        normalized = _normalize_url(candidate)
        if normalized in seen:
            continue
        seen.add(normalized)
        collection = documents if is_pdf else pages
        if len(collection) < 6:
            collection.append({"url": candidate, "title": title, "kind": kind})
        if len(documents) == 6 and len(pages) == 6:
            break
    return documents, pages


def _academic_documents(content: bytes, page_url: str) -> list[dict[str, str]]:
    return _academic_links(content, page_url)[0]


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
        fetched = _fetch_document(url)
        final_url, status, content_type, content = fetched
        embedding = _embedding(getattr(fetched, "response_headers", {}))
        if status != 200:
            return SourcePreviewResponse(
                **base,
                status="NO_DISPONIBLE",
                final_url=final_url,
                http_status=status,
                embedding=embedding,
                reachable=False if status in {404, 410} else None,
                message="La página no se encontró."
                if status in {404, 410}
                else f"La universidad respondió HTTP {status}; su contenido no pudo verificarse.",
            )
        academic_fields: dict[str, str] = {}
        title, excerpt, publication_date = _extract_metadata(
            content,
            content_type,
            academic_fields=academic_fields,
        )
        document_kind = _document_kind(content, content_type, final_url)
        document_hash = "sha256:" + hashlib.sha256(content).hexdigest()
        if document_kind in {"STUDENT_PORTAL", "AUTHENTICATION"}:
            return SourcePreviewResponse(
                **base,
                status="REQUIERE_REVISION",
                reachable=True,
                final_url=final_url,
                http_status=status,
                content_type=content_type,
                title="Portal de estudiantes"
                if document_kind == "STUDENT_PORTAL"
                else "Acceso universitario",
                document_kind=document_kind,
                embedding=embedding,
                message=(
                    "Este enlace es un portal de estudiantes o de acceso. "
                    "Sus menús no acreditan una malla curricular pública. "
                    "Comparte la página pública de la carrera o el PDF "
                    "publicado por la universidad."
                ),
            )
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
                document_kind=document_kind,
                embedding=embedding,
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
        fields.update(academic_fields)
        if publication_date:
            fields["publication_date"] = publication_date
        warnings = [
            "Los datos detectados son sugerencias; confirme su alcance y pertinencia.",
            "No se inventan sede, ciudad, versión ni justificación. "
            "Complete lo que no esté publicado.",
        ]
        if "scope" not in fields:
            warnings.append(
                "No se detectó un alcance académico explícito en el contenido. "
                "Complételo con evidencia de la carrera o del documento correspondiente."
            )
        if content_type == "application/pdf":
            warnings.append("La vista previa recoge sólo la primera página digital del PDF.")
        if not title or len(title) < 3:
            warnings.append(
                "No se encontró un título publicado utilizable; no se habilitará la propuesta."
            )
        can_use = bool(title and len(title) >= 3 and document_kind == "PUBLIC_DOCUMENT")
        if document_kind == "UNKNOWN":
            warnings.append(
                "La página no ofrece texto público legible; "
                "una aplicación JavaScript vacía no acredita contenido académico."
            )
        documents, academic_pages = (
            _academic_links(content, final_url)
            if content_type != "application/pdf" and can_use
            else ([], [])
        )
        return SourcePreviewResponse(
            **{**base, "assessment": final_assessment},
            document_kind=document_kind,
            embedding=embedding,
            reading_fragments=_reading_fragments(excerpt, document_hash, final_url)
            if can_use
            else [],
            documents=documents,
            academic_pages=academic_pages,
            status="VERIFICADA" if can_use else "REQUIERE_REVISION",
            reachable=True,
            final_url=final_url,
            http_status=status,
            content_type=content_type,
            document_hash=document_hash,
            title=title,
            excerpt=excerpt,
            suggested_fields=fields,
            warnings=warnings,
            can_use=can_use,
            message=(
                "La página respondió y se leyó su contenido. "
                "Revise el título y los datos detectados antes del análisis."
            ),
        )
    except PreviewFailure as error:
        return SourcePreviewResponse(**base, status="NO_DISPONIBLE", message=str(error))
    except TimeoutError:
        return SourcePreviewResponse(
            **base,
            status="NO_DISPONIBLE",
            message=(
                "La universidad tardó demasiado en establecer la conexión o enviar el documento. "
                "Vuelve a comprobar el enlace más tarde o usa otra publicación oficial. "
                "La ficha se conserva; este fallo no demuestra que la fuente sea inválida."
            ),
        )
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
