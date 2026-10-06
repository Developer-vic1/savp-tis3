import re
import unicodedata
from functools import lru_cache

from pydantic import BaseModel, ConfigDict, Field

from app.knowledge.registry import load_career_catalog

UNIVERSITY_MARKERS = {
    "ucb": "BO-UCB-LP",
    "universidad catolica": "BO-UCB-LP",
    "upb": "BO-UPB-LP",
    "universidad privada boliviana": "BO-UPB-LP",
    "unifranz": "BO-UNIFRANZ-LP",
    "universidad franz": "BO-UNIFRANZ-LP",
}


class SystemProgramNeed(BaseModel):
    model_config = ConfigDict(extra="forbid")

    university: str
    career: str
    initial_subjects: list[str] = Field(default_factory=list)
    source_id: str


def _normalized(value: str) -> str:
    folded = unicodedata.normalize("NFKD", value.casefold())
    return " ".join(re.findall(r"[a-z0-9]+", folded))


def _curriculum_source_id(source_ids: list[str]) -> str:
    return next((source_id for source_id in source_ids if "MALLA" in source_id), source_ids[0])


@lru_cache(maxsize=1)
def _system_programs() -> tuple[SystemProgramNeed, ...]:
    catalog = load_career_catalog()
    universities = {
        university.university_id: university.name for university in catalog.universities
    }
    programs = [
        SystemProgramNeed(
            university=universities[career.university_id],
            career=career.name,
            initial_subjects=career.initial_subjects,
            source_id=_curriculum_source_id(career.source_ids),
        )
        for career in catalog.careers
        if career.recommendation_eligible
        and career.evidence_layer == "CORPUS_VALIDADO"
        and "sistemas" in _normalized(" ".join((career.name, *career.aliases)))
    ]
    return tuple(sorted(programs, key=lambda program: (program.university, program.career)))


def system_needs_for_question(question: str) -> list[SystemProgramNeed]:
    normalized = _normalized(question)
    requested_universities = {
        university_id
        for marker, university_id in UNIVERSITY_MARKERS.items()
        if marker in normalized
    }
    if len(requested_universities) < 2 or "sistemas" not in normalized:
        return []
    return [
        program
        for program in _system_programs()
        if any(
            university_id in program.source_id
            for university_id in requested_universities
        )
    ]
