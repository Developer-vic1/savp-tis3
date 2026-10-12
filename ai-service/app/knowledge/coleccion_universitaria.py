"""Colección documental UNIFRANZ; conserva evidencia sin publicar al catálogo."""

from __future__ import annotations

import hashlib
import json
import os
import re
import subprocess
import sys
import threading
import time
import unicodedata
from collections.abc import Callable
from contextlib import suppress
from datetime import UTC, datetime
from pathlib import Path
from typing import Any, cast
from urllib.parse import urljoin, urlsplit, urlunsplit

from bs4 import BeautifulSoup

from app.ingestion.chunking import _split_text
from app.ingestion.extractors import extract_html, extract_pdf
from app.ingestion.models import DocumentExtraction, ExtractedSegment
from app.knowledge.registry import SERVICE_ROOT
from app.knowledge.sedes_universitarias import leer_oferta_sedes
from app.knowledge.source_preview import PreviewFailure, _document_kind, _fetch_document

SOURCE_URL = "https://unifranz.edu.bo/carreras/"
COLLECTION_ROOT = SERVICE_ROOT / "data/knowledge/colecciones/unifranz"
MAX_BYTES = 12 * 1024 * 1024
MAX_PAGES = 80
RECOGNITION_SECONDS = 120
_RECOGNITION_PROGRAM = """
import json, math, sys
import pymupdf
path, page_number, tessdata, language = sys.argv[1:]
with pymupdf.open(path) as document:
    page = document[int(page_number) - 1]
    area = max(1, page.rect.width * page.rect.height)
    dpi = max(36, min(200, int(72 * math.sqrt(12000000 / area))))
    if area * (dpi / 72) ** 2 > 12000000:
        raise ValueError('Página demasiado grande para lectura de imágenes limitada')
    textpage = page.get_textpage_ocr(language=language, dpi=dpi, full=True, tessdata=tessdata)
    print(json.dumps({'text': page.get_text('text', textpage=textpage)[:100000], 'dpi': dpi}))
"""
_LOCK = threading.Lock()
_CURRICULUM = re.compile(r"malla|plan[-_\s]*(?:de[-_\s]*)?estudio|curr[ií]cul", re.I)
Fetcher = Callable[..., tuple[str, int, str, bytes]]


def _download_message(error: Exception) -> str:
    if isinstance(error, TimeoutError) or "timed out" in str(error).lower():
        return (
            "UNIFRANZ tardó demasiado en responder. La información guardada se conserva; "
            "puedes reanudar esta descarga."
        )
    if isinstance(error, PreviewFailure):
        return str(error)[:500]
    return (
        "No se pudo leer o guardar este documento. Conservamos el resultado anterior; "
        "revisa la fuente antes de reanudar."
    )


def _now() -> str:
    return datetime.now(UTC).isoformat()


def _empty() -> dict[str, Any]:
    return {
        "schema_version": "1.0",
        "institution": "UNIFRANZ",
        "source_url": SOURCE_URL,
        "status": "SIN_INICIAR",
        "careers": [],
        "progress": {"completed": 0, "total": 0, "current": None},
        "statistics": {"careers": 0, "curricula": 0, "extracted_documents": 0},
        "warnings": [],
        "updated_at": None,
    }


def _atomic(path: Path, content: bytes) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    temporary = path.with_name(path.name + ".tmp")
    with temporary.open("wb") as stream:
        stream.write(content)
        stream.flush()
        os.fsync(stream.fileno())
    # Windows puede mantener una lectura breve de la API abierta durante el reemplazo.
    for attempt in range(6):
        try:
            temporary.replace(path)
            break
        except PermissionError:
            if attempt == 5:
                raise
            time.sleep(0.02 * (attempt + 1))


def _save(root: Path, state: dict[str, Any]) -> None:
    _statistics(state)
    state["updated_at"] = _now()
    _atomic(root / "manifiesto.json", json.dumps(state, ensure_ascii=False, indent=2).encode())


def _statistics(state: dict[str, Any]) -> None:
    careers = state["careers"]
    documents = [document for career in careers for document in career["curricula"]]
    state["statistics"] = {
        "careers": len(careers),
        "curricula": len(documents),
        "extracted_documents": sum(
            bool(career.get("page_chunks")) and career.get("page_verified", True)
            for career in careers
        )
        + sum(bool(doc["chunks"]) and doc.get("evidence_verified", True) for doc in documents),
    }


def _normal_name(value: str) -> str:
    text = unicodedata.normalize("NFKD", value.casefold())
    return re.sub(
        r"[^a-z0-9]+", " ", "".join(c for c in text if not unicodedata.combining(c))
    ).strip()


def _career_content(content: bytes, expected_url: str, final_url: str, name: str) -> str:
    if not _url(final_url, final_url) or urlsplit(expected_url).path.rstrip("/") != urlsplit(
        final_url
    ).path.rstrip("/"):
        raise PreviewFailure(
            "La ficha redirige a otra carrera o página; no se atribuyó su contenido."
        )
    soup = BeautifulSoup(content, "lxml")
    for unwanted in soup(["nav", "header", "footer", "script", "style"]):
        unwanted.decompose()
    main = soup.find("main") or soup
    if not any(
        _normal_name(h.get_text(" ", strip=True)) == _normal_name(name) for h in main.find_all("h1")
    ):
        raise PreviewFailure("El nombre publicado no coincide con la carrera del índice oficial.")
    return main.get_text(" ", strip=True)


def _duration(text: str) -> str | None:
    found = re.search(
        r"\b(?:duraci[oó]n|programa\s+de\s+estudios)\s*[:\-]?\s*"
        r"(\d{1,2}(?:[.,]\d{1,2})?\s*(?:semestres?|a[ñn]os?)(?:\s+y\s+medio)?)\b",
        text,
        re.I,
    )
    return found.group(1) if found else None


def _verified_document(
    root: Path, digest: str, suffix: str, url: str, count: int, chunks_file: str | None = None
) -> bytes:
    if (
        not _url(url, url)
        or not re.fullmatch(r"sha256:[a-f0-9]{64}", digest)
        or type(count) is not int
        or count < 0
    ):
        raise ValueError("La huella o cardinalidad no es válida.")
    raw_hash = digest.removeprefix("sha256:")
    snapshot = (root / "instantaneas" / (raw_hash + suffix)).read_bytes()
    if hashlib.sha256(snapshot).hexdigest() != raw_hash:
        raise ValueError("La instantánea no coincide con su huella.")
    default_file = raw_hash + "-" + hashlib.sha256(url.encode()).hexdigest()[:16] + ".jsonl"
    if chunks_file and not re.fullmatch(
        raw_hash + r"-[a-f0-9]{16}(?:-[a-f0-9]{16})?\.jsonl", chunks_file
    ):
        raise ValueError("La versión de fragmentos no corresponde a la instantánea.")
    chunk_path = root / "fragmentos" / (chunks_file or default_file)
    chunks = [
        json.loads(line)
        for line in chunk_path.read_text(encoding="utf-8").splitlines()
        if line.strip()
    ]
    if len(chunks) != count or any(
        not isinstance(chunk, dict)
        or chunk.get("institution") != "UNIFRANZ"
        or chunk.get("source_url") != url
        or chunk.get("document_hash") != digest
        or not isinstance(chunk.get("text"), str)
        or not chunk["text"].strip()
        for chunk in chunks
    ):
        raise ValueError("Los fragmentos no corresponden a esta instantánea.")
    return snapshot


def _verify_evidence(root: Path, state: dict[str, Any]) -> None:
    damaged = False
    for career in state["careers"]:
        if career.get("page_hash") or career["status"] in {"PARCIAL", "COMPLETADA"}:
            try:
                content = _verified_document(
                    root,
                    career.get("page_hash", ""),
                    ".html",
                    career.get("final_url", career["url"]),
                    career.get("page_chunks", 0),
                )
                text = _career_content(
                    content, career["url"], career.get("final_url", career["url"]), career["name"]
                )
                career["duration"] = _duration(text)
                career.update(
                    leer_oferta_sedes(
                        content,
                        career["name"],
                        career.get("final_url", career["url"]),
                        career["page_hash"],
                        career.get("retrieved_at"),
                    )
                )
                career["page_verified"] = True
            except (OSError, ValueError, TypeError, PreviewFailure):
                career["status"] = "NO_DISPONIBLE"
                career["page_verified"] = False
                career["campuses"] = []
                career["campus_availability"] = []
                career["campus_evidence"] = {
                    "status": "PENDIENTE",
                    "source_url": career["url"],
                    "document_hash": None,
                    "retrieved_at": None,
                    "method": "INDICADORES_DE_OFERTA",
                }
                career["warnings"] = [
                    "La ficha guardada no pudo verificarse. Reanuda su descarga; "
                    "las otras mallas se conservan."
                ]
                damaged = True
        for document in career["curricula"]:
            if document.get("document_hash") or document["status"] in {
                "EXTRAIDA",
                "PARCIAL",
                "SIN_TEXTO_DIGITAL",
            }:
                try:
                    _verified_document(
                        root,
                        document.get("document_hash", ""),
                        ".pdf",
                        document.get("final_url", document["url"]),
                        document["chunks"],
                        document.get("chunks_file"),
                    )
                    document["evidence_verified"] = True
                except (OSError, ValueError, TypeError):
                    document["status"] = "NO_DISPONIBLE"
                    document["evidence_verified"] = False
                    document["warnings"] = [
                        "La instantánea o sus fragmentos no pudieron verificarse. "
                        "Reanuda solo esta descarga."
                    ]
                    if career["status"] == "COMPLETADA":
                        career["status"] = "PARCIAL"
                    damaged = True
    if damaged and state["status"] == "COMPLETADA":
        state["status"] = "PARCIAL"
    _statistics(state)


def read_collection(output_dir: Path | None = None) -> dict[str, Any]:
    root = output_dir or COLLECTION_ROOT
    try:
        state = json.loads((root / "manifiesto.json").read_text(encoding="utf-8"))
        if state.get("institution") != "UNIFRANZ" or state.get("schema_version") != "1.0":
            raise ValueError("Manifiesto no reconocido")
        if state.get("status") == "EN_PROCESO":
            try:
                release = _claim(root)
            except RuntimeError:
                pass  # El hilo local u otro proceso conserva el bloqueo.
            else:
                release()
                state["status"] = "PARCIAL"
                state["progress"]["current"] = None
                state["warnings"] = [
                    *state.get("warnings", []),
                    "La recopilación se interrumpió. Puedes reanudar los documentos pendientes.",
                ]
        _verify_evidence(root, state)
        return cast(dict[str, Any], state)
    except FileNotFoundError:
        return _empty()
    except (OSError, ValueError, TypeError, KeyError, AttributeError):
        state = _empty()
        state["status"] = "NO_DISPONIBLE"
        state["warnings"] = ["No pudimos leer el manifiesto guardado; requiere revisión."]
        return state


def _url(href: str, page: str) -> str | None:
    if len(href) > 2000:
        return None
    try:
        parts = urlsplit(urljoin(page, href))
        port = parts.port
    except ValueError:
        return None
    if (
        parts.scheme != "https"
        or parts.hostname not in {"unifranz.edu.bo", "www.unifranz.edu.bo"}
        or parts.username
        or parts.password
        or port not in {None, 443}
    ):
        return None
    if re.search(r"/(?:login|auth|portal|estudiante|alumno|tag|category)(?:/|$)", parts.path, re.I):
        return None
    return urlunsplit(("https", parts.netloc.lower(), parts.path, "", ""))


def discover_careers(content: bytes, page: str = SOURCE_URL) -> list[dict[str, Any]]:
    careers: dict[str, dict[str, Any]] = {}
    for anchor in BeautifulSoup(content, "lxml").find_all("a", href=True):
        url = _url(str(anchor["href"]), page)
        if not url or not re.fullmatch(r"/carreras/[^/]+/?", urlsplit(url).path):
            continue
        if urlsplit(url).path.split("/")[2].lower() in {"page", "tag", "category"}:
            continue
        url = url.rstrip("/") + "/"
        label = re.sub(r"\s+", " ", anchor.get_text(" ", strip=True)).strip()[:240]
        if not label:
            continue
        careers.setdefault(
            url,
            {
                "name": label,
                "url": url,
                "status": "PENDIENTE",
                "campuses": [],
                "duration": None,
                "summary": "",
                "curricula": [],
                "warnings": [],
            },
        )
    return list(careers.values())


def discover_curricula(content: bytes, page: str) -> list[dict[str, Any]]:
    soup = BeautifulSoup(content, "lxml")
    documents: dict[str, dict[str, Any]] = {}
    for anchor in soup.find_all("a", href=True):
        if anchor.find_parent(["nav", "header", "footer"]):
            continue
        url = _url(str(anchor["href"]), page)
        if not url or not urlsplit(url).path.lower().endswith(".pdf"):
            continue
        label = anchor.get_text(" ", strip=True)
        context = (
            anchor.parent.get_text(" ", strip=True)[:500]
            if anchor.parent and anchor.parent.name not in {"body", "html"}
            else ""
        )
        heading = anchor.find_previous(["h2", "h3", "h4"])
        section = heading.get_text(" ", strip=True) if heading else ""
        if not _CURRICULUM.search(url + " " + label + " " + context + " " + section):
            continue
        documents.setdefault(
            url,
            {
                "url": url,
                "title": (label or section or "Malla curricular publicada")[:240],
                "status": "PENDIENTE",
                "pages": 0,
                "digital_pages": 0,
                "chunks": 0,
                "document_hash": None,
                "excerpt": "",
                "warnings": [],
            },
        )
    return list(documents.values())


def _claim(root: Path) -> Callable[[], None]:
    if not _LOCK.acquire(blocking=False):
        raise RuntimeError("La colección UNIFRANZ ya está en proceso.")
    stream = None
    try:
        root.mkdir(parents=True, exist_ok=True)
        stream = (root / ".coleccion.lock").open("a+b")
        stream.seek(0)
        if os.name == "nt":
            import msvcrt

            if not stream.read(1):
                stream.write(b"0")
                stream.flush()
            stream.seek(0)
            msvcrt.locking(stream.fileno(), msvcrt.LK_NBLCK, 1)
        else:
            import fcntl

            fcntl.flock(stream.fileno(), fcntl.LOCK_EX | fcntl.LOCK_NB)  # type: ignore[attr-defined]
    except (OSError, RuntimeError) as error:
        if stream:
            stream.close()
        _LOCK.release()
        raise RuntimeError(
            "La colección UNIFRANZ ya está en proceso o no puede guardarse."
        ) from error

    def release() -> None:
        stream.close()
        _LOCK.release()

    return release


def _fetch(url: str, fetcher: Fetcher) -> tuple[str, int, str, bytes]:
    if not _url(url, url):
        raise PreviewFailure("El enlace no pertenece al sitio público UNIFRANZ.")
    result = None
    for attempt in range(2):
        try:
            if fetcher is _fetch_document:
                result = fetcher(
                    url,
                    max_bytes=MAX_BYTES,
                    deadline_seconds=30,
                    socket_timeout=10,
                    address_index=attempt,
                )
            else:
                result = fetcher(url, max_bytes=MAX_BYTES)
            break
        except (OSError, PreviewFailure):
            if attempt == 1:
                raise
            time.sleep(0.35)
    assert result is not None
    final_url, status, kind, content = result
    if not _url(final_url, final_url) or status != 200 or len(content) > MAX_BYTES:
        raise PreviewFailure("No se pudo obtener un documento público UNIFRANZ válido.")
    if (
        kind not in {"text/html", "application/xhtml+xml", "application/pdf"}
        or _document_kind(content, kind, final_url) != "PUBLIC_DOCUMENT"
    ):
        raise PreviewFailure("El enlace no contiene documentación pública legible.")
    return result


def _snapshot(root: Path, content: bytes, suffix: str) -> tuple[Path, str]:
    digest = hashlib.sha256(content).hexdigest()
    path = root / "instantaneas" / (digest + suffix)
    if not path.exists() or hashlib.sha256(path.read_bytes()).hexdigest() != digest:
        _atomic(path, content)
    return path, "sha256:" + digest


def _recognition_runtime() -> dict[str, str] | None:
    import pymupdf

    candidates = [
        os.environ.get("SAVP_TESSDATA_DIR", ""),
        os.environ.get("TESSDATA_PREFIX", ""),
        str(SERVICE_ROOT / "data/knowledge/herramientas/tessdata"),
        str(SERVICE_ROOT / ".models/tessdata"),
    ]
    with suppress(RuntimeError):
        candidates.append(str(pymupdf.get_tessdata()))  # type: ignore[no-untyped-call]
    for candidate in candidates:
        if not candidate:
            continue
        root = Path(candidate)
        if (root / "spa.traineddata").is_file():
            return {
                "engine": "Tesseract/PyMuPDF",
                "version": pymupdf.VersionBind,
                "language": "spa",
                "tessdata": str(root.resolve()),
            }
    return None


def _recognize_page(path: Path, page: int, runtime: dict[str, str], timeout: float) -> str:
    environment = {**os.environ, "OMP_THREAD_LIMIT": "1"}
    result = subprocess.run(
        [
            sys.executable,
            "-c",
            _RECOGNITION_PROGRAM,
            str(path),
            str(page),
            runtime["tessdata"],
            runtime["language"],
        ],
        capture_output=True,
        timeout=min(35, timeout),
        check=True,
        env=environment,
        creationflags=subprocess.CREATE_NO_WINDOW if os.name == "nt" else 0,
    )
    output = json.loads(result.stdout.decode("utf-8"))
    return re.sub(r"[\x00-\x08\x0b\x0c\x0e-\x1f]", " ", output["text"]).strip()


def _extract_readable_pdf(
    path: Path, digest: str, previous_segments: list[ExtractedSegment] | None = None
) -> DocumentExtraction:
    import pymupdf

    extraction = extract_pdf(path, digest, max_pages=MAX_PAGES)
    for segment in previous_segments or []:
        if (
            segment.page not in extraction.digital_pages
            and segment.page
            and segment.page <= MAX_PAGES
        ):
            extraction.segments.append(segment)
            if segment.page not in extraction.ocr_pages:
                extraction.ocr_pages.append(segment.page)
    runtime = _recognition_runtime()
    # Las advertencias se expresan como lectura de imágenes en el flujo administrativo.
    extraction.warnings = [
        warning for warning in extraction.warnings if "OCR automático deshabilitado" not in warning
    ]
    with pymupdf.open(path) as document:  # type: ignore[no-untyped-call]
        pending = [
            page
            for page in range(1, min(document.page_count, MAX_PAGES) + 1)
            if page not in extraction.digital_pages and page not in extraction.ocr_pages
        ]
    if pending and not runtime:
        extraction.warnings.append(
            "La lectura de imágenes está pendiente: falta el modelo local de español "
            "spa.traineddata. Se conserva el texto digital y el PDF original."
        )
        return extraction
    deadline = time.monotonic() + RECOGNITION_SECONDS
    for page in pending:
        remaining = deadline - time.monotonic()
        if remaining <= 0:
            extraction.warnings.append(
                "Se agotó el tiempo de lectura de imágenes; puedes continuar desde el PDF guardado."
            )
            break
        try:
            text = _recognize_page(path, page, cast(dict[str, str], runtime), remaining)
            if sum(character.isalnum() for character in text) < 80:
                extraction.warnings.append(
                    f"Página {page}: no se recuperó texto suficiente de la imagen. "
                    "Revisa el original; una portada o fotografía puede no tener "
                    "contenido académico."
                )
                continue
            extraction.segments.append(
                ExtractedSegment(
                    text=text,
                    page=page,
                    extraction_method="OCR",
                    confidence=None,
                    ocr_engine=runtime["engine"] if runtime else None,
                    ocr_language=runtime["language"] if runtime else None,
                    ocr_version=runtime["version"] if runtime else None,
                )
            )
            extraction.ocr_pages.append(page)
        except (OSError, ValueError, subprocess.SubprocessError):
            extraction.warnings.append(
                f"Página {page}: no pudimos completar la lectura de imágenes. "
                "Puedes reanudarla desde el archivo guardado."
            )
    if extraction.ocr_pages:
        extraction.warnings.append(
            "El texto leído de imágenes necesita revisión contra el original antes de "
            "interpretar materias, semestres o requisitos."
        )
    extraction.segments.sort(key=lambda segment: segment.page or 0)
    return extraction


def _saved_recognition(root: Path, document: dict[str, Any]) -> list[ExtractedSegment]:
    digest = document.get("document_hash")
    if not digest:
        return []
    url = document.get("final_url", document["url"])
    default_file = (
        digest.removeprefix("sha256:")
        + "-"
        + hashlib.sha256(url.encode()).hexdigest()[:16]
        + ".jsonl"
    )
    chunk_path = root / "fragmentos" / document.get("chunks_file", default_file)
    segments = []
    for line in chunk_path.read_text(encoding="utf-8").splitlines():
        chunk = json.loads(line)
        if chunk.get("extraction_method") == "OCR":
            segments.append(
                ExtractedSegment(
                    text=chunk["text"],
                    page=chunk["page"],
                    extraction_method="OCR",
                    confidence=None,
                    ocr_engine=chunk.get("recognition_engine"),
                    ocr_language=chunk.get("recognition_language"),
                    ocr_version=chunk.get("recognition_version"),
                )
            )
    return segments


def _subjects_candidates(
    path: Path, digest: str, url: str, extraction: DocumentExtraction
) -> list[dict[str, Any]]:
    """Bloques digitales dentro de columnas semestrales; siempre requieren revisión."""
    import pymupdf

    ordinals = {
        "primer": 1,
        "segundo": 2,
        "tercer": 3,
        "cuarto": 4,
        "quinto": 5,
        "sexto": 6,
        "septimo": 7,
        "octavo": 8,
        "noveno": 9,
        "decimo": 10,
    }
    candidates: list[dict[str, Any]] = []
    with pymupdf.open(path) as document:  # type: ignore[no-untyped-call]
        for number in extraction.digital_pages:
            blocks = document[number - 1].get_text("blocks")
            headings = []
            for block in blocks:
                ordinal = ordinals.get(_normal_name(block[4]))
                if ordinal and any(
                    _normal_name(other[4]) == "semestre"
                    and abs(other[0] - block[0]) < 30
                    and abs(other[1] - block[1]) < 40
                    for other in blocks
                ):
                    headings.append((block[0], block[1], ordinal))
            if not headings:
                continue
            limits = [
                block[1]
                for block in blocks
                if re.search(
                    r"ELIGE|MENCIONES|MICROCREDENCIALES|PROFESIONALES|PRERREQUISITOS", block[4]
                )
            ]
            bottom = min(limits) if limits else document[number - 1].rect.height
            for block in blocks:
                raw = block[4]
                name = re.sub(r"\s+", " ", raw).strip()
                if (
                    block[1] >= bottom
                    or not 5 <= len(name) <= 180
                    or name.isupper()
                    or any(ord(character) < 32 and character not in "\n\r\t" for character in raw)
                    or not re.fullmatch(r"[A-ZÁÉÍÓÚÜÑ][A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9 /(),.:\-]+", name)
                    or _normal_name(name) in {*ordinals, "semestre"}
                ):
                    continue
                preceding = [
                    heading
                    for heading in headings
                    if heading[1] < block[1] and abs(heading[0] - block[0]) < 60
                ]
                if not preceding:
                    continue
                heading = max(preceding, key=lambda item: item[1])
                candidates.append(
                    {
                        "name": name,
                        "semester": heading[2],
                        "page": number,
                        "method": "DIGITAL",
                        "status": "PENDIENTE_REVISION",
                        "evidence_text": raw.strip(),
                        "bbox": [round(value, 2) for value in block[:4]],
                        "source_url": url,
                        "document_hash": digest,
                    }
                )
                if len(candidates) >= 200:
                    return candidates
    return candidates


def _persist_chunks(
    root: Path, extraction: Any, url: str, digest: str, title: str, chunks_file: str | None = None
) -> int:
    chunks = []
    for segment in extraction.segments:
        for text in _split_text(segment.text, 1200, 180):
            chunks.append(
                {
                    "institution": "UNIFRANZ",
                    "source_url": url,
                    "document_hash": digest,
                    "title": title,
                    "page": segment.page,
                    "section": segment.section,
                    "extraction_method": segment.extraction_method,
                    "recognition_engine": segment.ocr_engine,
                    "recognition_language": segment.ocr_language,
                    "recognition_version": segment.ocr_version,
                    "review_required": segment.extraction_method == "OCR",
                    "text": text,
                    "retrieved_at": _now(),
                    "evidence_layer": "COLECCION_PENDIENTE_REVISION",
                }
            )
    _atomic(
        root
        / "fragmentos"
        / (
            chunks_file
            or (
                digest.removeprefix("sha256:")
                + "-"
                + hashlib.sha256(url.encode()).hexdigest()[:16]
                + ".jsonl"
            )
        ),
        "\n".join(json.dumps(chunk, ensure_ascii=False) for chunk in chunks).encode(),
    )
    return len(chunks)


def _collect(root: Path, recollect: bool, fetcher: Fetcher, pause_seconds: float) -> dict[str, Any]:
    state = read_collection(root)
    state["status"] = "EN_PROCESO"
    state["warnings"] = [
        "Colección documental pendiente de revisión; no modifica el catálogo publicado.",
        "Los PDF por sede se conservan por separado. La lectura de imágenes requiere revisión.",
    ]
    _save(root, state)
    if recollect or not state["careers"]:
        final, _, kind, content = _fetch(SOURCE_URL, fetcher)
        if kind not in {"text/html", "application/xhtml+xml"}:
            raise PreviewFailure("El índice de carreras no es una página HTML.")
        _, index_hash = _snapshot(root, content, ".html")
        state["index_hash"] = index_hash
        state["index_final_url"] = final
        state["index_retrieved_at"] = _now()
        state["careers"] = discover_careers(content, final)
        if not state["careers"]:
            raise PreviewFailure("No se encontraron enlaces de carreras en el índice oficial.")
    state["progress"] = {"completed": 0, "total": len(state["careers"]), "current": None}
    _save(root, state)
    for career in state["careers"]:
        state["progress"]["current"] = career["name"]
        _save(root, state)
        try:
            if recollect or career["status"] in {"PENDIENTE", "NO_DISPONIBLE"}:
                final, _, kind, content = _fetch(career["url"], fetcher)
                if kind not in {"text/html", "application/xhtml+xml"}:
                    raise PreviewFailure("La ficha de carrera no contiene HTML.")
                text = _career_content(content, career["url"], final, career["name"])
                path, digest = _snapshot(root, content, ".html")
                duration = _duration(text)
                previous = {doc["url"]: doc for doc in career["curricula"]}
                curricula = [
                    previous.get(doc["url"], doc) for doc in discover_curricula(content, final)
                ]
                page_extraction = extract_html(path, digest)
                summary = re.sub(
                    r"\s+",
                    " ",
                    " ".join(
                        segment.text
                        for segment in page_extraction.segments
                        if len(segment.text) > 50
                    ),
                ).strip()[:1200]
                page_chunks = _persist_chunks(root, page_extraction, final, digest, career["name"])
                if not page_chunks:
                    raise PreviewFailure("La ficha no contiene texto académico recuperable.")
                career.update(
                    {
                        "duration": duration,
                        "curricula": curricula,
                        "warnings": [],
                        "page_hash": digest,
                        "retrieved_at": _now(),
                        "final_url": final,
                        "summary": summary,
                        "page_chunks": page_chunks,
                        "page_verified": True,
                    }
                )
                career.update(
                    leer_oferta_sedes(
                        content, career["name"], final, digest, career["retrieved_at"]
                    )
                )
                career["status"] = "PARCIAL"
                time.sleep(pause_seconds)
            for document in career["curricula"]:
                if not recollect and document["status"] == "EXTRAIDA":
                    continue
                try:
                    previous_segments = []
                    if not recollect and document["status"] in {"PARCIAL", "SIN_TEXTO_DIGITAL"}:
                        final = document.get("final_url", document["url"])
                        digest = document["document_hash"]
                        content = _verified_document(
                            root,
                            digest,
                            ".pdf",
                            final,
                            document["chunks"],
                            document.get("chunks_file"),
                        )
                        path = root / "instantaneas" / (digest.removeprefix("sha256:") + ".pdf")
                        previous_segments = _saved_recognition(root, document)
                    else:
                        final, _, kind, content = _fetch(document["url"], fetcher)
                        if kind != "application/pdf" or not content.startswith(b"%PDF-"):
                            raise PreviewFailure("El enlace de malla no devuelve un PDF válido.")
                        path, digest = _snapshot(root, content, ".pdf")
                        document["retrieved_at"] = _now()
                    import pymupdf

                    with pymupdf.open(path) as pdf:  # type: ignore[no-untyped-call]
                        pages = pdf.page_count
                    extraction = _extract_readable_pdf(path, digest, previous_segments)
                    subjects = _subjects_candidates(path, digest, final, extraction)
                    reading_hash = hashlib.sha256(
                        extraction.model_dump_json().encode()
                    ).hexdigest()[:16]
                    chunks_file = (
                        digest.removeprefix("sha256:")
                        + "-"
                        + hashlib.sha256(final.encode()).hexdigest()[:16]
                        + "-"
                        + reading_hash
                        + ".jsonl"
                    )
                    chunks_count = _persist_chunks(
                        root, extraction, final, digest, document["title"], chunks_file
                    )
                    document.update(
                        {
                            "pages": pages,
                            "digital_pages": len(extraction.digital_pages),
                            "recognized_pages": len(extraction.ocr_pages),
                            "readable_pages": len(extraction.digital_pages)
                            + len(extraction.ocr_pages),
                            "page_methods": list(
                                {
                                    segment.page: {
                                        "page": segment.page,
                                        "method": segment.extraction_method,
                                        "review_required": segment.extraction_method == "OCR",
                                    }
                                    for segment in extraction.segments
                                }.values()
                            ),
                            "subjects_candidates": subjects,
                            "subject_review": {
                                "status": "PENDIENTE_REVISION" if subjects else "PENDIENTE_LECTURA",
                                "message": (
                                    "Revisa los nombres y semestres contra cada página original. "
                                    "Las imágenes leídas y bloques con fuentes dañadas "
                                    "requieren revisión manual."
                                ),
                            },
                            "chunks": chunks_count,
                            "chunks_file": chunks_file,
                            "document_hash": digest,
                            "final_url": final,
                            "extraction_updated_at": _now(),
                            "excerpt": "\n".join(segment.text for segment in extraction.segments)[
                                :1500
                            ],
                            "warnings": list(extraction.warnings),
                            "evidence_verified": True,
                        }
                    )
                    document["status"] = (
                        "EXTRAIDA"
                        if len(extraction.digital_pages) + len(extraction.ocr_pages) == pages
                        else "PARCIAL"
                        if extraction.digital_pages or extraction.ocr_pages
                        else "SIN_TEXTO_DIGITAL"
                    )
                except (OSError, ValueError, RuntimeError, PreviewFailure) as error:
                    document["status"] = "NO_DISPONIBLE"
                    document["warnings"] = [_download_message(error)]
                _save(root, state)
                time.sleep(pause_seconds)
            if not career["curricula"]:
                career["warnings"] = ["No se encontró una malla PDF en la ficha pública."]
            career["status"] = (
                "COMPLETADA"
                if career["curricula"]
                and all(doc["status"] == "EXTRAIDA" for doc in career["curricula"])
                else "PARCIAL"
            )
        except (OSError, ValueError, RuntimeError, PreviewFailure) as error:
            career["status"] = "NO_DISPONIBLE"
            career["warnings"] = [_download_message(error)]
        state["progress"]["completed"] += 1
        _save(root, state)
    state["status"] = (
        "COMPLETADA"
        if all(career["status"] == "COMPLETADA" for career in state["careers"])
        else "PARCIAL"
    )
    state["progress"]["current"] = None
    _save(root, state)
    return state


def run_collection(
    output_dir: Path | None = None,
    *,
    recollect: bool = False,
    fetcher: Fetcher | None = None,
    pause_seconds: float = 0.35,
    _release: Callable[[], None] | None = None,
) -> dict[str, Any]:
    root = output_dir or COLLECTION_ROOT
    release = _release or _claim(root)
    try:
        return _collect(root, recollect, fetcher or _fetch_document, pause_seconds)
    except Exception as error:
        state = read_collection(root)
        state["status"] = "NO_DISPONIBLE"
        state["progress"]["current"] = None
        state["warnings"] = ["No pudimos completar la colección: " + _download_message(error)]
        _save(root, state)
        return state
    finally:
        release()


def start_collection() -> dict[str, Any]:
    release = _claim(COLLECTION_ROOT)
    try:
        state = read_collection()
        state["status"] = "EN_PROCESO"
        _save(COLLECTION_ROOT, state)
        thread = threading.Thread(
            target=run_collection,
            kwargs={"_release": release},
            name="coleccion-unifranz",
            daemon=True,
        )
        thread.start()
    except Exception:
        release()
        raise
    return read_collection()
