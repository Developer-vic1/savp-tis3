"""Cola documental local, reanudable y separada del catálogo/corpus aprobado.

No consulta APIs de inferencia, bases de datos ni promueve fuentes. Los dominios
confirmados en el censo habilitan únicamente la descarga preliminar local.
"""

from __future__ import annotations

import hashlib
import http.client
import ipaddress
import json
import os
import re
import time
from collections.abc import Callable
from dataclasses import asdict, dataclass
from datetime import UTC, datetime
from pathlib import Path
from typing import Any
from urllib.parse import parse_qsl, quote, unquote, urlencode, urljoin, urlsplit, urlunsplit

from bs4 import BeautifulSoup

from app.ingestion.chunking import _split_text
from app.ingestion.extractors import extract_html, extract_pdf
from app.knowledge.governance import TRUSTED_BOLIVIAN_UNIVERSITY_DOMAINS
from app.knowledge.registry import SERVICE_ROOT
from app.knowledge.source_preview import (
    PinnedHTTPSConnection,
    PreviewFailure,
    _document_kind,
    _public_address,
    _remaining,
)

PRIVATE_ROUTE = re.compile(
    r"(?:^|[/_.-])(?:login|signin|auth|admin|estudiante|alumno|student|sistema|portal|"
    r"intranet|solicitudes?|notas|calificaciones|deudas|datos-personales|estado-de-cuenta)"
    r"(?:[/_.-]|$)",
    re.I,
)
ACADEMIC_LINK = re.compile(
    r"carreras?|pregrado|facultad|oferta.?acad[eé]mica|malla|curr[ií]cul|plan.?de.?estudios|"
    r"perfil.?profesional|arancel|tarifa|colegiatura|costos?|becas?|financiamiento|sede|campus",
    re.I,
)
CURRICULAR = re.compile(r"malla curricular|plan de estudios|perfil profesional|asignaturas", re.I)
CAREER_NAME = re.compile(
    r"ingenier[ií]a|licenciatura|psicolog[ií]a|medicina|odontolog[ií]a|derecho|"
    r"arquitectura|administraci[oó]n|contadur[ií]a|enfermer[ií]a|econom[ií]a|"
    r"comunicaci[oó]n|dise[nñ]o|gastronom[ií]a|turismo|bioqu[ií]mica|farmacia|"
    r"veterinaria|fisioterapia|nutrici[oó]n|pedagog[ií]a|trabajo social|inform[aá]tica",
    re.I,
)
TERMINAL = {"EXTRAIDA_PENDIENTE_REVISION", "DUPLICADA", "SIN_TEXTO", "BLOQUEADA"}
CONFIRMED_WEBSITE = {"CURRENT_VERIFIED", "OFFICIAL_DOMAIN_VERIFIED"}


@dataclass(frozen=True)
class CrawlLimits:
    max_documents_per_institution: int = 40
    max_depth: int = 3
    max_links_per_document: int = 100
    max_queue_per_institution: int = 1000
    max_hosts_per_institution: int = 6
    max_bytes: int = 4 * 1024 * 1024
    max_pdf_pages: int = 30
    max_chunks_per_document: int = 300
    max_attempts: int = 2
    pause_seconds: float = 0.5
    deadline_seconds: float = 12
    run_documents: int = 100

    def __post_init__(self) -> None:
        positive = (
            self.max_documents_per_institution,
            self.max_links_per_document,
            self.max_queue_per_institution,
            self.max_hosts_per_institution,
            self.max_pdf_pages,
            self.max_chunks_per_document,
            self.max_attempts,
            self.run_documents,
        )
        if any(value < 1 for value in positive) or not 0 <= self.max_depth <= 10:
            raise ValueError("Los límites documentales deben ser positivos; profundidad 0..10.")
        if not 1 <= self.max_bytes <= 16 * 1024 * 1024:
            raise ValueError("El documento debe limitarse a 1 byte..16 MiB.")
        if not 1 <= self.deadline_seconds <= 30 or not 0 <= self.pause_seconds <= 60:
            raise ValueError("Tiempo límite 1..30 s; pausa 0..60 s.")
        if self.max_attempts > 5:
            raise ValueError("Máximo cinco intentos por URL.")


def now() -> str:
    return datetime.now(UTC).isoformat()


def canonical_url(value: str, domains: list[str]) -> str | None:
    """Valida cada salto; una coincidencia parcial de host nunca habilita acceso."""
    try:
        if len(value) > 2000 or re.search(r"[\x00-\x20\x7f\\]", value):
            return None
        parsed = urlsplit(value)
        host = (parsed.hostname or "").encode("idna").decode().lower().rstrip(".")
        if (
            parsed.scheme != "https"
            or parsed.username
            or parsed.password
            or parsed.port not in {None, 443}
            or not host
        ):
            return None
        try:
            ipaddress.ip_address(host)
            return None
        except ValueError:
            pass
        if not any(host == domain or host.endswith("." + domain) for domain in domains):
            return None
        path = unquote(parsed.path).casefold()
        if PRIVATE_ROUTE.search(path) or PRIVATE_ROUTE.search(host):
            return None
        query = parse_qsl(parsed.query, keep_blank_values=True)
        if any(
            re.search(r"token|password|session|clave|dni|ci|email", key, re.I) for key, _ in query
        ):
            return None
        query = [
            (key, val)
            for key, val in query
            if not key.lower().startswith("utm_") and key.lower() not in {"fbclid", "gclid"}
        ]
        return urlunsplit(("https", host, parsed.path or "/", urlencode(sorted(query)), ""))
    except (ValueError, UnicodeError):
        return None


def fetch_confirmed_document(
    url: str,
    domains: list[str],
    limits: CrawlLimits,
) -> tuple[str, int, str, bytes]:
    """Transporte fijado a IP pública: mismas defensas del verificador existente.

    El censo no modifica la confianza del verificador interactivo. Cada redirect
    vuelve a validar dominio, ruta y DNS antes de crear la conexión TLS.
    """
    deadline = time.monotonic() + limits.deadline_seconds
    current = url
    visited: set[str] = set()
    for _ in range(4):
        current = canonical_url(current, domains) or ""
        if not current or current in visited:
            raise PreviewFailure("Destino fuera del dominio confirmado, privado o en bucle.")
        visited.add(current)
        parsed = urlsplit(current)
        host = parsed.hostname or ""
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
                    "User-Agent": "SAVP-CatalogoNacional/1.0 (extraccion documental limitada)",
                },
            )
            if connection.sock:
                connection.sock.settimeout(min(4, _remaining(deadline)))
            response = connection.getresponse()
            if response.status in {301, 302, 303, 307, 308}:
                location = response.getheader("Location")
                if not location or len(location) > 2000:
                    raise PreviewFailure("Redirección sin destino válido.")
                current = urljoin(current, location)
                continue
            kind = (response.getheader("Content-Type") or "").split(";")[0].lower().strip()
            if response.status != 200:
                return current, response.status, kind, b""
            if kind not in {"text/html", "application/xhtml+xml", "application/pdf"}:
                raise PreviewFailure("Solo se descargan HTML y PDF.")
            if (response.getheader("Content-Encoding") or "identity").lower() != "identity":
                raise PreviewFailure("Compresión documental no admitida.")
            length = response.getheader("Content-Length")
            if length and (not length.isdigit() or int(length) > limits.max_bytes):
                raise PreviewFailure("Documento mayor al límite o tamaño inválido.")
            pieces: list[bytes] = []
            size = 0
            while True:
                if connection.sock:
                    connection.sock.settimeout(min(4, _remaining(deadline)))
                _remaining(deadline)
                piece = response.read1(min(65536, limits.max_bytes + 1 - size))
                if not piece:
                    break
                size += len(piece)
                if size > limits.max_bytes:
                    raise PreviewFailure("Documento mayor al límite.")
                pieces.append(piece)
            return current, response.status, kind, b"".join(pieces)
        finally:
            connection.close()
    raise PreviewFailure("Más de tres redirecciones.")


def discover_links(content: bytes, url: str, domains: list[str], cap: int) -> list[dict[str, str]]:
    soup = BeautifulSoup(content, "lxml")
    links: dict[str, dict[str, str]] = {}
    for node in soup.find_all("a", href=True):
        label = re.sub(r"\s+", " ", node.get_text(" ", strip=True))[:240]
        href = str(node.get("href", ""))
        if not ACADEMIC_LINK.search(label + " " + unquote(href)):
            continue
        candidate = canonical_url(urljoin(url, href), domains)
        if candidate and candidate != url:
            links.setdefault(candidate, {"url": candidate, "label": label, "from_url": url})
        if len(links) >= cap:
            break
    return list(links.values())


def candidate_facts(extraction: Any, url: str, career_title: str | None) -> list[dict[str, Any]]:
    facts: list[dict[str, Any]] = []
    seen: set[tuple[str, str]] = set()
    for segment in extraction.segments:
        lines = [segment.section or "", *segment.text.splitlines()]
        for raw in lines:
            line = re.sub(r"\s+", " ", raw).strip()
            if not line or len(line) > 650:
                continue
            values: list[tuple[str, str]] = []
            if career_title and line == career_title:
                values.append(("CARRERA", line))
            declared = re.fullmatch(r"(?:nombre de la )?carrera\s*:\s*(.+)", line, re.I)
            if declared and CAREER_NAME.search(declared.group(1)):
                values.append(("CARRERA", declared.group(1)))
            if re.search(r"\bduraci[oó]n\b", line, re.I) and re.search(
                r"\b\d{1,2}\s*(?:semestres?|a[nñ]os?|meses?)\b",
                line,
                re.I,
            ):
                values.append(("DURACION", line))
            if re.match(r"sede\s*[:\-]\s*\S", line, re.I):
                values.append(("SEDE", line))
            if re.search(r"malla curricular|plan de estudios", line, re.I):
                values.append(("MALLA_O_PLAN", line))
            if re.search(r"perfil profesional", line, re.I):
                values.append(("PERFIL_PROFESIONAL", line))
            if re.search(r"arancel|cuota|costo|colegiatura|matr[ií]cula", line, re.I) and re.search(
                r"(?:\bBs\.?|BOB|USD|US\$|\$|bolivianos)\s*[\d.,]+|"
                r"[\d.,]+\s*(?:Bs\.?|BOB|USD|bolivianos)\b",
                line,
                re.I,
            ):
                values.append(("COSTO_PUBLICADO", line))
            if re.search(r"\bbeca\w*\b", line, re.I) and len(line) >= 25:
                values.append(("BECA_PUBLICADA", line))
            for kind, value in values:
                key = (kind, value.casefold())
                if key in seen:
                    continue
                seen.add(key)
                facts.append(
                    {
                        "tipo": kind,
                        "valor": value,
                        "evidencia": line,
                        "source_url": url,
                        "page": segment.page,
                        "section": segment.section,
                        "carrera": career_title,
                        "estado": "CANDIDATO_PENDIENTE_REVISION",
                    }
                )
    return facts[:200]


def atomic_json(path: Path, value: Any) -> None:
    temporary = path.with_suffix(path.suffix + ".tmp")
    temporary.write_text(json.dumps(value, ensure_ascii=False, indent=2), encoding="utf-8")
    # Windows can hold the previous file briefly while another reader inspects
    # coverage. Keep the old complete file until replacement succeeds.
    for attempt in range(5):
        try:
            os.replace(temporary, path)
            return
        except PermissionError:
            if attempt == 4:
                raise
            time.sleep(0.05 * (attempt + 1))


def fact_counts(facts: list[dict[str, Any]]) -> dict[str, int]:
    kinds = {
        "career": {"CARRERA"},
        "curriculum": {"MALLA_O_PLAN"},
        "duration": {"DURACION"},
        "economic": {"COSTO_PUBLICADO", "BECA_PUBLICADA"},
    }
    return {key: sum(fact.get("tipo") in values for fact in facts) for key, values in kinds.items()}


def fallback_catalog() -> dict[str, Any]:
    return {
        "schema_version": "1.0",
        "coverage_note": "Solo siete dominios ya registrados.",
        "institutions": [
            {
                "institution_id": item.domain.replace(".", "-"),
                "name": item.institution,
                "official_domains": [item.domain],
                "official_url": "https://" + item.domain + "/",
                "seed_urls": ["https://" + item.domain + "/"],
                "verification_status": "CURRENT_VERIFIED",
            }
            for item in TRUSTED_BOLIVIAN_UNIVERSITY_DOMAINS
        ],
    }


class NationalCrawler:
    def __init__(
        self,
        catalog: dict[str, Any],
        output: Path,
        limits: CrawlLimits,
        fetcher: Callable[..., tuple[str, int, str, bytes]] = fetch_confirmed_document,
        sleeper: Callable[[float], None] = time.sleep,
    ) -> None:
        self.catalog = catalog
        self.output = output
        self.limits = limits
        self.fetcher = fetcher
        self.sleeper = sleeper
        self.output.mkdir(parents=True, exist_ok=True)
        self.state_path = output / "estado.json"
        self.state: dict[str, Any] = (
            json.loads(self.state_path.read_text(encoding="utf-8"))
            if self.state_path.exists()
            else {"schema_version": "1.0", "institutions": {}}
        )
        self.documents_this_run = 0

    def _save(self) -> None:
        self.state["updated_at"] = now()
        self.state["limits"] = asdict(self.limits)
        atomic_json(self.state_path, self.state)
        atomic_json(self.output / "cobertura.json", self.coverage())

    def _seed(self, institution: dict[str, Any]) -> dict[str, Any]:
        identifier = str(institution["institution_id"])
        if not re.fullmatch(r"[a-zA-Z0-9_-]{1,100}", identifier):
            raise ValueError("Identificador institucional inseguro.")
        requested_domains = institution.get("official_domains", [])
        # El censo puede asignar un ID definitivo después del primer lote de siete.
        # Reutilizar la cola por los mismos dominios evita repetir las descargas.
        if identifier not in self.state["institutions"] and requested_domains:
            aliases = [
                key
                for key, value in self.state["institutions"].items()
                if set(value.get("domains", [])) == set(requested_domains)
            ]
            if len(aliases) == 1:
                previous = aliases[0]
                self.state["institutions"][identifier] = self.state["institutions"].pop(previous)
                self.state["institutions"][identifier]["previous_id"] = previous
        entry: dict[str, Any] = self.state["institutions"].setdefault(
            identifier,
            {
                "name": institution["name"],
                "queue": [],
                "urls": {},
                "hashes": {},
                "hosts": [],
                "downloads": 0,
                "attempts": 0,
            },
        )
        domains = institution.get("official_domains", [])
        if not isinstance(domains, list) or any(
            not isinstance(domain, str)
            or not re.fullmatch(r"[a-z0-9.-]+", domain)
            or "." not in domain
            for domain in domains
        ):
            raise ValueError("Dominios confirmados inválidos.")
        entry["domains"] = domains
        entry["institution_verification_status"] = institution.get("verification_status")
        entry["verification_status"] = institution.get(
            "website_verification_status", institution.get("verification_status")
        )
        if institution.get("ingestion_status") == "QUARANTINE_SITE_REVIEW_REQUIRED":
            entry["verification_status"] = "QUARANTINE_SITE_REVIEW_REQUIRED"
            return entry
        if entry["verification_status"] not in CONFIRMED_WEBSITE or not domains:
            return entry
        seeds = institution.get("seed_urls", [])
        if "seed_urls" not in institution and institution.get("official_url"):
            seeds = [institution["official_url"]]
        for seed in seeds:
            self._enqueue(entry, seed, 0, None)
        # Al aumentar profundidad se aprovechan los enlaces ya guardados.
        for record in list(entry["urls"].values()):
            if record["depth"] < self.limits.max_depth:
                for link in record.get("discovered_links", []):
                    self._enqueue(
                        entry,
                        link["url"],
                        record["depth"] + 1,
                        record.get("final_url", link.get("from_url")),
                    )
        return entry

    def _enqueue(self, entry: dict[str, Any], url: str, depth: int, origin: str | None) -> None:
        normalized = canonical_url(url, entry["domains"])
        if not normalized or normalized in entry["urls"]:
            return
        if len(entry["urls"]) >= self.limits.max_queue_per_institution:
            entry["discovery_limited"] = True
            return
        host = urlsplit(normalized).hostname
        if host not in entry["hosts"]:
            if len(entry["hosts"]) >= self.limits.max_hosts_per_institution:
                entry["hosts_limited"] = True
                return
            entry["hosts"].append(host)
        entry["urls"][normalized] = {
            "status": "PENDIENTE",
            "attempts": 0,
            "depth": depth,
            "from_url": origin,
        }
        entry["queue"].append(normalized)

    def _extract(
        self,
        identifier: str,
        entry: dict[str, Any],
        requested: str,
        final_url: str,
        kind: str,
        content: bytes,
    ) -> dict[str, Any]:
        digest = hashlib.sha256(content).hexdigest()
        if digest in entry["hashes"]:
            return {
                "status": "DUPLICADA",
                "document_hash": "sha256:" + digest,
                "duplicate_of": entry["hashes"][digest],
                "final_url": final_url,
            }
        stage = self.output / identifier / digest
        stage.mkdir(parents=True, exist_ok=True)
        suffix = ".pdf" if kind == "application/pdf" else ".html"
        snapshot = stage / ("instantanea" + suffix)
        snapshot.write_bytes(content)
        career = None
        if suffix == ".html":
            soup = BeautifulSoup(content, "lxml")
            for node in soup(
                [
                    "script",
                    "style",
                    "nav",
                    "header",
                    "footer",
                    "noscript",
                    "iframe",
                    "form",
                    "input",
                    "button",
                    "svg",
                ]
            ):
                node.decompose()
            heading = soup.find("h1")
            title = heading.get_text(" ", strip=True)[:200] if heading else ""
            body = soup.get_text(" ", strip=True)
            if title and CAREER_NAME.search(title) and CURRICULAR.search(body):
                career = title
            sanitized = stage / "lectura_sanitizada.html"
            sanitized.write_text(str(soup), encoding="utf-8")
            extraction = extract_html(sanitized, digest)
        else:
            extraction = extract_pdf(snapshot, digest, max_pages=self.limits.max_pdf_pages)
        chunks: list[dict[str, Any]] = []
        for segment in extraction.segments:
            for text in _split_text(segment.text, 1200, 120):
                chunks.append(
                    {
                        "chunk_id": hashlib.sha256(
                            f"{digest}|{segment.page}|{segment.section}|{len(chunks)}|{text}".encode()
                        ).hexdigest()[:24],
                        "text": text,
                        "page": segment.page,
                        "section": segment.section,
                        "source_url": final_url,
                        "document_hash": "sha256:" + digest,
                        "status": "CANDIDATO_PENDIENTE_REVISION",
                    }
                )
                if len(chunks) >= self.limits.max_chunks_per_document:
                    break
            if len(chunks) >= self.limits.max_chunks_per_document:
                extraction.warnings.append("Texto limitado por máximo de fragmentos.")
                break
        facts = candidate_facts(extraction, final_url, career)
        counts = fact_counts(facts)
        manifest = {
            "institution_id": identifier,
            "institution": entry["name"],
            "source_url": requested,
            "final_url": final_url,
            "retrieved_at": now(),
            "document_hash": "sha256:" + digest,
            "content_type": kind,
            "status": "EXTRAIDA_PENDIENTE_REVISION" if chunks else "SIN_TEXTO",
            "snapshot": snapshot.name,
            "chunks": len(chunks),
            "facts": len(facts),
            "candidate_counts": counts,
            "warnings": extraction.warnings,
            "coverage": {
                key: "NO_PUBLICADO_O_NO_DETECTADO" for key in ("duracion", "sede", "costo", "beca")
            },
            "limitations": [
                "Dominio confirmado no acredita vigencia ni oferta por sede.",
                "Enlaces de navegación solo orientan descubrimiento.",
                "Sin OCR, inferencia, embeddings ni promoción automática.",
            ],
        }
        for key, fact_kind in {
            "duracion": "DURACION",
            "sede": "SEDE",
            "costo": "COSTO_PUBLICADO",
            "beca": "BECA_PUBLICADA",
        }.items():
            if any(fact["tipo"] == fact_kind for fact in facts):
                manifest["coverage"][key] = "CANDIDATO_PENDIENTE_REVISION"
        atomic_json(stage / "manifest.json", manifest)
        atomic_json(stage / "candidatos.json", facts)
        (stage / "chunks.jsonl").write_text(
            "".join(json.dumps(chunk, ensure_ascii=False) + "\n" for chunk in chunks),
            encoding="utf-8",
        )
        entry["hashes"][digest] = final_url
        return {
            "status": manifest["status"],
            "document_hash": "sha256:" + digest,
            "manifest": str((stage / "manifest.json").relative_to(self.output)),
            "final_url": final_url,
            "chunks": len(chunks),
            "facts": len(facts),
            "candidate_counts": counts,
        }

    def _visit(self, identifier: str, entry: dict[str, Any], url: str) -> None:
        record = entry["urls"][url]
        record["attempts"] += 1
        entry["attempts"] += 1
        self.documents_this_run += 1
        record["status"] = "EN_PROCESO"
        self._save()  # La URL permanece en la cola hasta persistir el resultado.
        try:
            if not canonical_url(url, entry["domains"]):
                record["status"] = "BLOQUEADA"
                return
            final_url, status, kind, content = self.fetcher(url, entry["domains"], self.limits)
            if not canonical_url(final_url, entry["domains"]):
                raise PreviewFailure("URL final fuera de los dominios confirmados.")
            record["http_status"] = status
            if status != 200:
                raise PreviewFailure(f"HTTP {status}; no confirma invalidez de la fuente.")
            if kind not in {"text/html", "application/xhtml+xml", "application/pdf"}:
                raise PreviewFailure("Formato documental no admitido.")
            if len(content) > self.limits.max_bytes:
                raise PreviewFailure("Documento mayor al límite.")
            if kind == "application/pdf" and not content.startswith(b"%PDF-"):
                raise PreviewFailure("Firma PDF inválida.")
            if _document_kind(content, kind, final_url) != "PUBLIC_DOCUMENT":
                record["status"] = "BLOQUEADA"
                record["error"] = "Documento no público o portal de acceso."
                return
            entry["downloads"] += 1
            if kind in {"text/html", "application/xhtml+xml"}:
                links = discover_links(
                    content, final_url, entry["domains"], self.limits.max_links_per_document
                )
                record["discovered_links"] = links
                if record["depth"] < self.limits.max_depth:
                    for link in links:
                        self._enqueue(entry, link["url"], record["depth"] + 1, final_url)
            record.update(self._extract(identifier, entry, url, final_url, kind, content))
            record.pop("error", None)
        except (
            OSError,
            TimeoutError,
            http.client.HTTPException,
            PreviewFailure,
            ValueError,
            RuntimeError,
        ) as error:
            record["status"] = "NO_DISPONIBLE"
            record["error"] = str(error)[:500]
        finally:
            record["checked_at"] = now()

    def run(self, institution_ids: set[str] | None = None) -> dict[str, Any]:
        lock = self.output / "extraccion.lock"
        try:
            descriptor = os.open(lock, os.O_CREAT | os.O_EXCL | os.O_WRONLY)
        except FileExistsError as error:
            raise RuntimeError(
                "Ya existe una extracción activa; revise extraccion.lock."
            ) from error
        try:
            os.write(descriptor, str(os.getpid()).encode())
            os.close(descriptor)
            if self.state_path.exists():
                self.state = json.loads(self.state_path.read_text(encoding="utf-8"))
            self.state["catalog_institution_ids"] = [
                institution["institution_id"]
                for institution in self.catalog.get("institutions", [])
            ]
            for entry in self.state["institutions"].values():
                for record in entry["urls"].values():
                    if record["status"] == "EN_PROCESO":
                        record["status"] = "NO_DISPONIBLE"
                        record["error"] = "Ejecución interrumpida; pendiente de reintento limitado."
                    if record.get("manifest") and "candidate_counts" not in record:
                        facts_path = (
                            self.output / Path(record["manifest"]).parent / "candidatos.json"
                        )
                        if facts_path.exists():
                            facts = json.loads(facts_path.read_text(encoding="utf-8"))
                            record["candidate_counts"] = fact_counts(facts)
            selected = []
            for institution in self.catalog.get("institutions", []):
                identifier = institution["institution_id"]
                if institution_ids and identifier not in institution_ids:
                    continue
                selected.append((identifier, self._seed(institution)))
            self._save()
            # Una URL por universidad y vuelta evita monopolizar la ejecución.
            while self.documents_this_run < self.limits.run_documents:
                progressed = False
                for identifier, entry in selected:
                    if (
                        self.documents_this_run >= self.limits.run_documents
                        or entry["verification_status"] not in CONFIRMED_WEBSITE
                        or not entry["domains"]
                    ):
                        continue
                    if entry["downloads"] >= self.limits.max_documents_per_institution:
                        continue
                    while entry["queue"]:
                        url = entry["queue"][0]
                        record = entry["urls"][url]
                        if (
                            record["status"] in TERMINAL
                            or record["attempts"] >= self.limits.max_attempts
                        ):
                            entry["queue"].pop(0)
                            continue
                        self._visit(identifier, entry, url)
                        entry["queue"].pop(0)
                        if (
                            record["status"] == "NO_DISPONIBLE"
                            and record["attempts"] < self.limits.max_attempts
                        ):
                            entry["queue"].append(url)
                        self._save()
                        if self.limits.pause_seconds:
                            self.sleeper(self.limits.pause_seconds)
                        progressed = True
                        break
                if not progressed:
                    break
            self._save()
            return self.coverage()
        finally:
            lock.unlink(missing_ok=True)

    def coverage(self) -> dict[str, Any]:
        rows = []
        for identifier, entry in self.state["institutions"].items():
            current_ids = self.state.get("catalog_institution_ids")
            if current_ids is not None and identifier not in current_ids:
                continue
            records = list(entry["urls"].values())
            pending = sum(
                value["status"] in {"PENDIENTE", "EN_PROCESO"}
                or (
                    value["status"] == "NO_DISPONIBLE"
                    and value["attempts"] < self.limits.max_attempts
                )
                for value in records
            )
            errors = [
                {"url": url, "message": item.get("error", item["status"])}
                for url, item in entry["urls"].items()
                if item["status"] in {"NO_DISPONIBLE", "BLOQUEADA", "SIN_TEXTO"}
            ]
            valid = sum(value["status"] == "EXTRAIDA_PENDIENTE_REVISION" for value in records)
            if entry["verification_status"] not in CONFIRMED_WEBSITE or not entry["domains"]:
                status = "PENDIENTE_VERIFICACION_DOMINIO"
            elif pending or entry.get("discovery_limited") or entry.get("hosts_limited"):
                status = "PARCIAL_CON_PENDIENTES"
            elif errors:
                status = "PARCIAL_CON_ERRORES"
            else:
                status = "COLA_AGOTADA_REQUIERE_REVISION"
            rows.append(
                {
                    "institution_id": identifier,
                    "institution": entry["name"],
                    "status": status,
                    "urls_seen": len(records),
                    "downloaded": entry["downloads"],
                    "valid": valid,
                    "valid_meaning": "Texto digital extraído; pendiente revisión institucional.",
                    "pending": pending,
                    "errors": errors,
                    "duplicates": sum(value["status"] == "DUPLICADA" for value in records),
                    "attempts": entry["attempts"],
                    "candidate_counts": {
                        key: sum(value.get("candidate_counts", {}).get(key, 0) for value in records)
                        for key in ("career", "curriculum", "duration", "economic")
                    },
                }
            )
        return {
            "schema_version": "1.0",
            "generated_at": now(),
            "institutions": rows,
            "limits": asdict(self.limits),
            "documents_this_run": self.documents_this_run,
            "coverage_note": "Agotar la cola no prueba cobertura nacional ni ofertas vigentes.",
            "promotion": "NINGUNA; candidatos locales separados del runtime.",
        }


DEFAULT_OUTPUT = SERVICE_ROOT / "data/knowledge/nacional"
