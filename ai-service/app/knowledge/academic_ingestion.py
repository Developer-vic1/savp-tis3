"""Ingesta preliminar de una fuente aprobada, separada del corpus publicado."""

from __future__ import annotations

import hashlib
import http.client
import json
import os
import re
import shutil
import threading
from collections.abc import Iterator
from contextlib import contextmanager
from datetime import UTC, datetime
from pathlib import Path
from tempfile import TemporaryDirectory
from typing import Literal

from bs4 import BeautifulSoup
from pydantic import Field

from app.config import get_settings
from app.ingestion.chunking import build_chunks
from app.ingestion.extractors import extract_html, extract_pdf
from app.ingestion.models import DocumentExtraction
from app.knowledge.governance import (
    GovernanceModel,
    SourceIntakeProposal,
    _load_intake_registry,
    _normalize_words,
    assess_bolivian_university_url,
)
from app.knowledge.registry import SERVICE_ROOT, SourceRecord
from app.knowledge.source_preview import (
    _FETCH_SLOTS,
    PreviewFailure,
    _academic_links,
    _document_kind,
    _extract_metadata,
    _fetch_document,
)

_WRITE_LOCK = threading.Lock()
_MAX_PDF_PAGES = 80
_MAX_PREVIEW_CHUNKS = 8


class AcademicFact(GovernanceModel):
    kind: Literal["DURACION", "MATERIA", "CARRERA"]
    value: str = Field(min_length=1, max_length=240)
    evidence: str = Field(min_length=1, max_length=500)
    page: int | None = None
    section: str | None = None
    source_url: str


class AcademicIngestionResponse(GovernanceModel):
    schema_version: str = "1.0"
    trace_id: str = "pending"
    proposal_id: str
    status: Literal["EXTRAIDA_PENDIENTE_REVISION", "NO_DISPONIBLE"]
    source_url: str
    final_url: str | None = None
    document_hash: str | None = None
    content_type: str | None = None
    metrics: dict[str, int] = Field(default_factory=dict)
    facts: list[AcademicFact] = Field(default_factory=list)
    chunks: list[dict[str, str | int | None]] = Field(default_factory=list)
    linked_sources: list[dict[str, str]] = Field(default_factory=list)
    coverage: dict[str, int] = Field(default_factory=dict)
    embeddings: dict[str, str | int | None] = Field(default_factory=dict)
    warnings: list[str] = Field(default_factory=list)


def _proposal(proposal_id: str) -> SourceIntakeProposal:
    proposal = next(
        (item for item in _load_intake_registry().proposals if item.proposal_id == proposal_id),
        None,
    )
    if proposal is None:
        raise LookupError("La propuesta no existe.")
    if proposal.status != "APROBADA_PENDIENTE_INGESTA":
        raise ValueError("La propuesta debe estar aprobada antes de extraer su contenido.")
    if assess_bolivian_university_url(proposal.url).status != "ACEPTADA":
        raise ValueError("La fuente aprobada ya no tiene un dominio universitario admitido.")
    return proposal


def _facts(
    extraction: DocumentExtraction, url: str, *, career_title: str | None = None
) -> list[AcademicFact]:
    """Solo texto presente; los candidatos no acreditan vigencia ni oferta de otra sede."""
    facts: list[AcademicFact] = []
    seen: set[tuple[str, str]] = set()
    expected_career = _normalize_words(career_title or "")
    for segment in extraction.segments:
        lines = segment.text.splitlines()
        career_heading = bool(
            expected_career and _normalize_words(segment.section or "") == expected_career
        )
        if career_heading and segment.section:
            lines.insert(0, segment.section)
        for line in lines:
            clean = re.sub(r"\s+", " ", line).strip()
            if not clean or len(clean) > 500:
                continue
            matches: list[tuple[str, str]] = []
            duration = re.search(
                r"\b(?:duraci[oó]n\s*[:\-]?\s*)?(\d{1,2})\s*"
                r"(semestres?|a[nñ]os?)\b",
                clean,
                re.I,
            )
            if duration and ("duraci" in clean.casefold() or len(clean) < 100):
                matches.append(("DURACION", duration.group(0).strip()))
            declared_career = re.fullmatch(
                r"(?:nombre\s+de\s+la\s+)?carrera\s*:\s*(.+)", clean, re.I
            )
            if expected_career and len(clean) < 120:
                if career_heading and _normalize_words(clean) == expected_career:
                    matches.append(("CARRERA", clean))
                elif (
                    declared_career
                    and _normalize_words(declared_career.group(1)) == expected_career
                ):
                    matches.append(("CARRERA", declared_career.group(1).strip()))
            if (
                segment.section
                and re.search(
                    r"(?:primer|1er|1°)\s+(?:semestre|a[nñ]o)|materias|asignaturas",
                    segment.section,
                    re.I,
                )
                and 6 <= len(clean) <= 100
                and not re.search(
                    r"semestre|materias|asignaturas|facultad|universidad", clean, re.I
                )
            ):
                matches.append(("MATERIA", clean))
            for kind, value in matches:
                key = (kind, value.casefold())
                if key in seen:
                    continue
                seen.add(key)
                facts.append(
                    AcademicFact(
                        kind=kind,
                        value=value[:240],
                        evidence=clean,
                        page=segment.page,
                        section=segment.section,
                        source_url=url,
                    )
                )
                if len(facts) >= 60:
                    return facts
    return facts


def _stage_dir() -> Path:
    intake_path = (
        get_settings().source_intake_path or SERVICE_ROOT / "data/knowledge/source_intake.json"
    )
    return intake_path.parent / "staged"


@contextmanager
def _promote_extraction(stage: Path, candidate: Path, names: list[str]) -> Iterator[None]:
    """Promueve archivos completos; restaura la extracción anterior ante fallos."""
    with TemporaryDirectory(prefix=".respaldo-ingesta-", dir=stage.parent) as backup_path:
        backup = Path(backup_path)
        previous = {name for name in names if (stage / name).is_file()}
        for name in previous:
            shutil.copy2(stage / name, backup / name)
        try:
            for name in names:
                os.replace(candidate / name, stage / name)
            yield
        except BaseException:
            for name in names:
                if name in previous:
                    os.replace(backup / name, stage / name)
                else:
                    (stage / name).unlink(missing_ok=True)
            raise


def _mismatched_plan(content: bytes, proposed_title: str) -> tuple[bool, bool]:
    """Detecta un plan rotulado con otra carrera dentro de una ficha válida."""
    generic = {
        "carrera",
        "perfil",
        "profesional",
        "universidad",
        "plan",
        "estudios",
        "de",
        "del",
        "la",
        "el",
        "en",
        "y",
        "gestion",
        "sede",
        "pregrado",
        "programa",
        "academico",
    }
    expected = set(_normalize_words(proposed_title).split()) - generic
    headings = [
        heading.get_text(" ", strip=True)
        for heading in BeautifulSoup(content, "lxml").find_all(["h1", "h2", "h3", "h4"])
    ]
    profile_heading = any(
        bool(expected) and expected <= (set(_normalize_words(title).split()) - generic)
        for title in headings
    )
    for index, title in enumerate(headings):
        if _normalize_words(title) != "plan de estudios":
            continue
        for next_title in headings[index + 1 :]:
            normalized = _normalize_words(next_title)
            if normalized == "plan de estudios":
                continue
            tokens = set(normalized.split()) - generic
            if (
                tokens
                and not expected <= tokens
                and re.search(
                    r"ingenieria|licenciatura|psicologia|administracion|medicina|arquitectura|derecho",
                    normalized,
                )
            ):
                return profile_heading, True
            break
    return profile_heading, False


def read_approved_extraction(proposal_id: str) -> AcademicIngestionResponse:
    """Lee la instantánea verificada sin descargar ni sustituir sus vectores."""
    from app.knowledge.vector_staging import _verified_stage, read_staged_vector_status

    proposal = _proposal(proposal_id)
    with _WRITE_LOCK:
        _, manifest, chunks = _verified_stage(proposal_id)
        if manifest.get("source_url") != proposal.url:
            raise ValueError("La extracción no corresponde a la URL aprobada.")
        facts = [AcademicFact.model_validate(value) for value in manifest.get("facts", [])]
        if any(fact.source_url != manifest["final_url"] for fact in facts):
            raise ValueError("Los datos no corresponden a la instantánea revisada.")
        return AcademicIngestionResponse(
            proposal_id=proposal_id,
            status="EXTRAIDA_PENDIENTE_REVISION",
            source_url=proposal.url,
            final_url=manifest["final_url"],
            document_hash=manifest["document_hash"],
            content_type=manifest["content_type"],
            metrics={
                "pages": len({chunk.page for chunk in chunks if chunk.page is not None}),
                "segments": manifest["segments"],
                "chunks": len(chunks),
                "facts": len(facts),
            },
            facts=facts,
            chunks=[
                {
                    "chunk_id": chunk.chunk_id,
                    "text": chunk.text[:1200],
                    "page": chunk.page,
                    "section": chunk.section,
                }
                for chunk in chunks[:_MAX_PREVIEW_CHUNKS]
            ],
            linked_sources=manifest.get("linked_sources", []),
            coverage=manifest.get("coverage", {}),
            embeddings=read_staged_vector_status(proposal_id),
            warnings=[
                *manifest.get("warnings", []),
                "Instantánea guardada; esta consulta no vuelve a leer la universidad.",
            ],
        )


def extract_approved_proposal(proposal_id: str) -> AcademicIngestionResponse:
    proposal = _proposal(proposal_id)
    base = {
        "proposal_id": proposal_id,
        "source_url": proposal.url,
        "embeddings": {
            "status": "NO_GENERADOS",
            "model": None,
            "vectors": 0,
            "reason": "La ingesta preliminar no ejecuta modelos vectoriales; el servicio usa BM25.",
        },
    }
    if not _FETCH_SLOTS.acquire(blocking=False):
        return AcademicIngestionResponse(
            **base,
            status="NO_DISPONIBLE",
            warnings=["El verificador está ocupado. Intenta de nuevo en un momento."],
        )
    try:
        final_url, status, content_type, content = _fetch_document(
            proposal.url, max_bytes=12 * 1024 * 1024
        )
        if status != 200:
            return AcademicIngestionResponse(
                **base,
                status="NO_DISPONIBLE",
                final_url=final_url,
                warnings=[f"La universidad respondió HTTP {status}; no se guardó la fuente."],
            )
        if _document_kind(content, content_type, final_url) != "PUBLIC_DOCUMENT":
            return AcademicIngestionResponse(
                **base,
                status="NO_DISPONIBLE",
                final_url=final_url,
                warnings=["La página no ofrece un documento académico público legible."],
            )
        if content_type == "application/pdf" and not content.startswith(b"%PDF-"):
            raise PreviewFailure("El contenido no corresponde a un PDF válido.")
        if content_type not in {"application/pdf", "text/html", "application/xhtml+xml"}:
            raise PreviewFailure("El formato no admite extracción académica.")
        if proposal.source_type.endswith("_PDF") and content_type != "application/pdf":
            raise PreviewFailure("La fuente aprobada como PDF respondió otro formato.")
        if not proposal.source_type.endswith("_PDF") and content_type == "application/pdf":
            raise PreviewFailure("La fuente aprobada como página respondió un PDF.")
        profile_only = False
        if proposal.source_type == "OFFICIAL_CAREER_HTML":
            observed_title, _, _ = _extract_metadata(content, content_type)
            generic = {
                "carrera",
                "perfil",
                "profesional",
                "universidad",
                "plan",
                "estudios",
                "de",
                "del",
                "la",
                "el",
                "en",
                "y",
                "gestión",
                "gestion",
                "sede",
                "pregrado",
                "programa",
                "académico",
                "academico",
            }
            expected = set(_normalize_words(proposal.title).split()) - generic
            observed = set(_normalize_words(observed_title or "").split()) - generic
            profile_heading, mismatched_plan = _mismatched_plan(content, proposal.title)
            profile_only = profile_heading and mismatched_plan
            if (not expected or not expected <= observed) and not profile_only:
                return AcademicIngestionResponse(
                    **base,
                    status="NO_DISPONIBLE",
                    final_url=final_url,
                    warnings=[
                        "El título visible no coincide con la carrera aprobada; "
                        "revise si el portal cambió de contenido o ruta."
                    ],
                )
        final_assessment = assess_bolivian_university_url(final_url)
        if final_assessment.recognized_institution != proposal.assessment.recognized_institution:
            raise PreviewFailure("La URL final no pertenece a la universidad aprobada.")
        linked_sources = []
        if content_type in {"text/html", "application/xhtml+xml"}:
            documents, pages = _academic_links(content, final_url)
            linked_sources = (documents + pages)[:3]
        digest = "sha256:" + hashlib.sha256(content).hexdigest()
        suffix = ".pdf" if content_type == "application/pdf" else ".html"
        stage = _stage_dir() / proposal_id
        stage.mkdir(parents=True, exist_ok=True)
        snapshot = stage / f"instantanea{suffix}"
        with (
            _WRITE_LOCK,
            TemporaryDirectory(prefix=".candidato-ingesta-", dir=stage.parent) as candidate_path,
        ):
            candidate = Path(candidate_path)
            candidate_snapshot = candidate / snapshot.name
            candidate_snapshot.write_bytes(content)
            extraction = (
                extract_pdf(candidate_snapshot, proposal_id, max_pages=_MAX_PDF_PAGES)
                if suffix == ".pdf"
                else extract_html(candidate_snapshot, proposal_id)
            )
            warnings = list(extraction.warnings)
            if profile_only:
                extraction.segments = [
                    segment
                    for segment in extraction.segments
                    if segment.section
                    and re.search(
                        r"conoce tu carrera|tu futuro profesional|perfil (?:del )?graduado",
                        _normalize_words(segment.section),
                    )
                ]
                warnings.append(
                    "La ficha describe la carrera, pero su bloque de plan de estudios "
                    "corresponde a otra. Se extrajo únicamente el perfil verificable; "
                    "la malla requiere otra fuente."
                )
            if not extraction.segments:
                return AcademicIngestionResponse(
                    **base,
                    status="NO_DISPONIBLE",
                    final_url=final_url,
                    warnings=[
                        *warnings,
                        "No se extrajo texto digital; requiere revisión u OCR validado.",
                    ],
                )
            source = SourceRecord(
                source_id=proposal_id,
                institution=proposal.declared_institution,
                title=proposal.title,
                source_type=proposal.source_type,
                url=final_url,
                official=True,
                publication_date=proposal.publication_date,
                retrieved_at=datetime.now(UTC).date().isoformat(),
                valid_from=None,
                valid_until=None,
                document_hash=digest,
                section=None,
                page=None,
                version=proposal.version,
                status="STAGED_REVIEW",
                local_path=str(snapshot.relative_to(SERVICE_ROOT))
                if snapshot.is_relative_to(SERVICE_ROOT)
                else str(snapshot),
                verification_status="PENDING_HUMAN_REVIEW",
                scope=proposal.scope,
                final_url=final_url,
                content_type=content_type,
                limitations=proposal.limitations,
            )
            chunks = build_chunks(extraction, source)
            facts = _facts(extraction, final_url, career_title=proposal.title)
            metadata = {
                "proposal_id": proposal_id,
                "source_url": proposal.url,
                "final_url": final_url,
                "institution": proposal.declared_institution,
                "campus": proposal.campus,
                "city": proposal.city,
                "publication_date": proposal.publication_date,
                "retrieved_at": datetime.now(UTC).isoformat(),
                "document_hash": digest,
                "source_type": proposal.source_type,
                "content_type": content_type,
                "status": "EXTRAIDA_PENDIENTE_REVISION",
                "snapshot": str(snapshot),
                "segments": len(extraction.segments),
                "chunks": len(chunks),
                "linked_sources": linked_sources,
                "coverage": {"fetched_documents": 1, "maximum_documents": 4},
                "facts": [fact.model_dump(mode="json") for fact in facts],
                "warnings": warnings,
                "embeddings": base["embeddings"],
            }
            (candidate / "manifest.json").write_text(
                json.dumps(metadata, ensure_ascii=False, indent=2), encoding="utf-8"
            )
            (candidate / "chunks.jsonl").write_text(
                "".join(chunk.model_dump_json() + "\n" for chunk in chunks), encoding="utf-8"
            )
            with _promote_extraction(
                stage, candidate, [snapshot.name, "chunks.jsonl", "manifest.json"]
            ):
                from app.knowledge.vector_staging import read_staged_vector_status

                base["embeddings"] = read_staged_vector_status(proposal_id)
                metadata["embeddings"] = base["embeddings"]
                manifest_tmp = candidate / "manifest.json"
                manifest_tmp.write_text(
                    json.dumps(metadata, ensure_ascii=False, indent=2), encoding="utf-8"
                )
                os.replace(manifest_tmp, stage / "manifest.json")
        return AcademicIngestionResponse(
            **base,
            status="EXTRAIDA_PENDIENTE_REVISION",
            final_url=final_url,
            document_hash=digest,
            content_type=content_type,
            metrics={
                "pages": len(extraction.digital_pages) + len(extraction.ocr_pages),
                "segments": len(extraction.segments),
                "chunks": len(chunks),
                "facts": len(facts),
            },
            facts=facts,
            chunks=[
                {
                    "chunk_id": chunk.chunk_id,
                    "text": chunk.text[:1200],
                    "page": chunk.page,
                    "section": chunk.section,
                }
                for chunk in chunks[:_MAX_PREVIEW_CHUNKS]
            ],
            linked_sources=linked_sources,
            coverage={"fetched_documents": 1, "maximum_documents": 4},
            warnings=[
                *warnings,
                "Los hechos detectados requieren revisión antes de entrar al catálogo o corpus.",
                "Los enlaces relacionados son candidatos para proponer "
                "como fuentes independientes.",
            ],
        )
    except PreviewFailure as error:
        return AcademicIngestionResponse(
            **base,
            status="NO_DISPONIBLE",
            warnings=[str(error)],
        )
    except TimeoutError:
        return AcademicIngestionResponse(
            **base,
            status="NO_DISPONIBLE",
            warnings=[
                "La universidad no respondió a tiempo; conserva la propuesta para reintentar."
            ],
        )
    except (OSError, http.client.HTTPException, ValueError, RuntimeError):
        return AcademicIngestionResponse(
            **base,
            status="NO_DISPONIBLE",
            warnings=["No se pudo extraer la fuente por conexión, formato o seguridad."],
        )
    finally:
        _FETCH_SLOTS.release()
