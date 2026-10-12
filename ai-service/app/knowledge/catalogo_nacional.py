"""Directorio de consulta: evidencia histórica, programas publicados y avance local."""

from __future__ import annotations

import json
import unicodedata
from pathlib import Path
from typing import Any

from pydantic import Field

from app.knowledge.governance import GovernanceModel
from app.knowledge.registro_nacional import NATIONAL_REGISTRY, load_national_registry
from app.knowledge.registry import SERVICE_ROOT, load_career_catalog, load_source_manifest
from app.tutor.datos_institucionales import unifranz_published_offer

NATIONAL_DIRECTORY = NATIONAL_REGISTRY
NATIONAL_COVERAGE = SERVICE_ROOT / "data/knowledge/nacional/cobertura.json"


class NationalDirectoryResponse(GovernanceModel):
    schema_version: str = "1.0"
    trace_id: str
    generated_at: str | None = None
    coverage_note: str
    evidence_sources: list[dict[str, Any]] = Field(default_factory=list)
    coverage_discrepancies: list[dict[str, Any]] = Field(default_factory=list)
    institutions: list[dict[str, Any]] = Field(default_factory=list)
    statistics: dict[str, int] = Field(default_factory=dict)
    collection_updated_at: str | None = None


def _read(path: Path) -> dict[str, Any]:
    if not path.exists():
        return {}
    # A missing report is normal before collecting. A corrupt registry must fail,
    # rather than silently appear to certify an empty directory.
    value = json.loads(path.read_text(encoding="utf-8"))
    if not isinstance(value, dict):
        raise ValueError("El directorio nacional debe ser un objeto JSON.")
    return value


def _key(value: str) -> str:
    return unicodedata.normalize("NFKD", value).encode("ascii", "ignore").decode().upper()


def national_directory(trace_id: str) -> NationalDirectoryResponse:
    registry = load_national_registry(NATIONAL_DIRECTORY)
    coverage = _read(NATIONAL_COVERAGE)
    catalog = load_career_catalog()
    sources = {item.source_id: item for item in load_source_manifest().sources}
    unifranz_offer = unifranz_published_offer()
    progress = {row["institution_id"]: row for row in coverage.get("institutions", [])}
    institutions: list[dict[str, Any]] = []
    for original in registry.get("institutions", []):
        institution = dict(original)
        code = _key(str(institution.get("acronym", "")))
        universities = {
            item.university_id: item
            for item in catalog.universities
            if code and _key(item.university_id.split("-")[1]) == code
        }
        programs: list[dict[str, Any]] = []
        for career in catalog.careers:
            if career.university_id not in universities or not career.recommendation_eligible:
                continue
            university = universities[career.university_id]
            programs.append(
                {
                    "career_id": career.career_id,
                    "name": career.name,
                    "degree": career.degree,
                    "university_id": career.university_id,
                    "campus": university.campus,
                    "city": university.city,
                    "duration_semesters": career.duration_semesters,
                    "duration_text": career.duration_text,
                    "source_ids": career.source_ids,
                    "sources": [
                        {"source_id": item.source_id, "title": item.title, "url": item.url}
                        for source_id in career.source_ids
                        if (item := sources.get(source_id)) is not None
                    ],
                    "evidence_layer": career.evidence_layer,
                    "status": career.status,
                    "initial_subjects": career.initial_subjects,
                    "curriculum_status": (
                        "MATERIAS_DOCUMENTADAS" if career.initial_subjects else "PENDIENTE_REVISION"
                    ),
                    "economic_status": "PENDIENTE_VERIFICACION",
                    "economic_evidence": [],
                }
            )
        institution["programs"] = programs
        institution["published_offer"] = (
            {
                "source_url": unifranz_offer.source_url,
                "checked_at": unifranz_offer.checked_at,
                "careers": [career.model_dump() for career in unifranz_offer.careers],
            }
            if code == "UNIFRANZ" and unifranz_offer
            else None
        )
        institution["collection"] = progress.get(
            institution["institution_id"],
            {"status": "NO_INICIADA", "valid": 0, "pending": 0, "errors": []},
        )
        institutions.append(institution)
    return NationalDirectoryResponse(
        trace_id=trace_id,
        generated_at=registry.get("generated_at"),
        collection_updated_at=coverage.get("generated_at"),
        coverage_note=registry.get(
            "coverage_note", "El registro nacional todavía no está disponible."
        ),
        evidence_sources=[
            {key: value for key, value in row.items() if key != "local_file"}
            for row in registry.get("evidence_sources", [])
        ],
        coverage_discrepancies=registry.get("coverage_discrepancies", []),
        institutions=institutions,
        statistics={
            "institutions": len(institutions),
            "domains_verified": sum(
                row.get("website_verification_status") == "OFFICIAL_DOMAIN_VERIFIED"
                for row in institutions
            ),
            "documented_programs": sum(len(row["programs"]) for row in institutions),
            "extracted_documents": sum(row["collection"].get("valid", 0) for row in institutions),
            "pending_urls": sum(row["collection"].get("pending", 0) for row in institutions),
        },
    )
