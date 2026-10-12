"""Ofertas del catálogo local respaldadas por fuentes oficiales del corpus."""

import json
from functools import lru_cache
from typing import Any

from pydantic import BaseModel, ConfigDict, Field, model_validator

from app.knowledge.registry import SERVICE_ROOT, Career, load_career_catalog, load_source_manifest
from app.tutor.consulta_documental import normalized, requested_careers, requested_institutions


class VerifiedSemester(BaseModel):
    model_config = ConfigDict(extra="forbid")

    semester: int = Field(ge=1, le=12)
    page: int = Field(gt=0)
    subjects: list[str] = Field(default_factory=list)
    mention_subjects: dict[str, list[str]] = Field(default_factory=dict)

    @model_validator(mode="after")
    def has_legible_subjects(self) -> "VerifiedSemester":
        names = [
            *self.subjects,
            *(name for group in self.mention_subjects.values() for name in group),
        ]
        if not names or any(
            not name.strip() or len(name) > 160 or any(ord(char) < 32 for char in name)
            for name in names
        ):
            raise ValueError("La transcripción visual requiere nombres legibles de materias")
        if self.mention_subjects and len(self.mention_subjects) < 2:
            raise ValueError("Las materias de mención requieren las ramas diferenciadas")
        return self


class ProgramFact(BaseModel):
    model_config = ConfigDict(extra="forbid")

    name: str
    university_name: str
    source_id: str
    initial_subjects: list[str] = Field(default_factory=list)
    curriculum_pages: list[int] = Field(default_factory=list)
    semester_transcriptions: int = 0
    has_curriculum: bool = False
    verified_semesters: list[VerifiedSemester] = Field(default_factory=list)


@lru_cache(maxsize=1)
def _curricula() -> dict[str, dict[str, Any]]:
    path = SERVICE_ROOT / "data" / "catalog" / "mallas_academicas.json"
    entries = json.loads(path.read_text(encoding="utf-8"))["curricula"]
    return {entry["career_id"]: entry for entry in entries}


def _matching_universities(question: str) -> dict[str, str]:
    requested = requested_institutions(question)
    if len(requested) != 1:
        return {}
    return {
        university.university_id: university.name
        for university in load_career_catalog().universities
        if any(alias in normalized(university.name) for alias in requested[0])
    }


def _official_sources(available_source_ids: set[str]) -> set[str]:
    return {
        source.source_id
        for source in load_source_manifest().sources
        if source.official and source.source_id in available_source_ids
    }


def _program_fact(
    career: Career, university_name: str, sources: set[str], *, curriculum: bool
) -> ProgramFact | None:
    linked = [source_id for source_id in career.source_ids if source_id in sources]
    if not linked:
        return None
    curriculum_entry = _curricula().get(career.career_id, {})
    documents = [
        document
        for document in curriculum_entry.get("documents", [])
        if document.get("source_id") in linked
        and document.get("document_integrity") == "HASH_VERIFICADO_CON_MANIFIESTO"
        and document.get("document_hash")
        == next(
            (
                source.document_hash
                for source in load_source_manifest().sources
                if source.source_id == document.get("source_id")
            ),
            None,
        )
    ]
    curriculum_document = next(
        (
            document
            for document in documents
            if document.get("source_id") and "MALLA" in document["source_id"]
        ),
        None,
    )
    verified_semesters = (
        [
            VerifiedSemester.model_validate(
                {
                    "semester": row.get("semester"),
                    "page": row.get("page"),
                    "subjects": row.get("subjects"),
                    "mention_subjects": row.get("mention_subjects", {}),
                }
            )
            for row in curriculum_document.get("semester_transcriptions", [])
            if row.get("status") == "TRANSCRIPCION_VISUAL_VERIFICADA"
        ]
        if curriculum_document
        else []
    )
    if curriculum:
        selected = curriculum_document["source_id"] if curriculum_document else linked[0]
    else:
        selected = next((source_id for source_id in linked if "MALLA" not in source_id), linked[0])
    return ProgramFact(
        name=(
            career.name.replace("Ingenieria", "Ingeniería").replace("Innovacion", "Innovación")
        ),
        university_name=university_name,
        source_id=selected,
        initial_subjects=career.initial_subjects if curriculum_document else [],
        curriculum_pages=curriculum_document.get("curriculum_pages", [])
        if curriculum_document
        else [],
        semester_transcriptions=len(curriculum_document.get("semester_transcriptions", []))
        if curriculum_document
        else 0,
        has_curriculum=bool(curriculum_document),
        verified_semesters=verified_semesters,
    )


def listed_careers(question: str, available_source_ids: set[str]) -> list[ProgramFact]:
    universities = _matching_universities(question)
    if not universities:
        return []
    sources = _official_sources(available_source_ids)
    entries = [
        fact
        for career in load_career_catalog().careers
        if career.university_id in universities and career.evidence_layer == "CORPUS_VALIDADO"
        if (
            fact := _program_fact(
                career, universities[career.university_id], sources, curriculum=False
            )
        )
        is not None
    ]
    return sorted(entries, key=lambda entry: normalized(entry.name))


def subject_program(question: str, available_source_ids: set[str]) -> ProgramFact | None:
    universities = _matching_universities(question)
    careers = requested_careers(question)
    if not universities or not careers:
        return None
    sources = _official_sources(available_source_ids)
    matching = [
        career
        for career in load_career_catalog().careers
        if career.university_id in universities
        and career.evidence_layer == "CORPUS_VALIDADO"
        and any(
            marker in normalized(" ".join((career.name, *career.aliases)))
            for marker in careers
        )
    ]
    if len(matching) != 1:
        return None
    career = matching[0]
    return _program_fact(career, universities[career.university_id], sources, curriculum=True)
