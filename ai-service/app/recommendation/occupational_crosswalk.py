import json
from enum import StrEnum
from functools import lru_cache
from pathlib import Path

from pydantic import BaseModel, ConfigDict, Field, model_validator

from app.recommendation.bridge_v2 import BridgeRelationStatus

SERVICE_ROOT = Path(__file__).resolve().parents[2]
CROSSWALK_PATH = SERVICE_ROOT / "data" / "crosswalk" / "career_occupation_v1.json"


class OccupationRelationType(StrEnum):
    RELATED_OCCUPATION = "RELATED_OCCUPATION"
    POSSIBLE_OCCUPATION = "POSSIBLE_OCCUPATION"
    INSUFFICIENT_EVIDENCE = "INSUFFICIENT_EVIDENCE"


class CrosswalkModel(BaseModel):
    model_config = ConfigDict(extra="forbid", str_strip_whitespace=True)


class CareerOccupationRelation(CrosswalkModel):
    career_id: str = Field(min_length=1)
    occupation_id: str = Field(min_length=1)
    occupation_system: str = Field(min_length=1)
    occupation_title: str = Field(min_length=1)
    relation_type: OccupationRelationType
    riasec_occupational_profile: str | None = Field(
        default=None, pattern=r"^[RIASEC]{2,3}$"
    )
    source_ids: list[str] = Field(min_length=1)
    evidence_status: BridgeRelationStatus
    justification: str = Field(min_length=1)
    limitations: list[str]
    version: str = Field(min_length=1)


class CareerOccupationCrosswalk(CrosswalkModel):
    crosswalk_version: str = Field(min_length=1)
    created_at: str = Field(min_length=1)
    taxonomy_standards: list[str]
    governance_principles: list[str]
    allowed_relation_types: list[OccupationRelationType]
    relations: list[CareerOccupationRelation]

    @model_validator(mode="after")
    def unique_relations(self) -> "CareerOccupationCrosswalk":
        keys = [
            (relation.career_id, relation.occupation_id)
            for relation in self.relations
        ]
        if len(keys) != len(set(keys)):
            raise ValueError("career_id + occupation_id debe ser único")
        return self


class CrosswalkLoadResult(CrosswalkModel):
    crosswalk: CareerOccupationCrosswalk | None
    warnings: list[str]


def load_occupational_crosswalk(path: Path | None = None) -> CrosswalkLoadResult:
    requested_path = path or CROSSWALK_PATH
    if not requested_path.is_file():
        return CrosswalkLoadResult(
            crosswalk=None,
            warnings=[f"Crosswalk carrera-ocupación no disponible en {requested_path}."],
        )
    with requested_path.open(encoding="utf-8") as handle:
        payload = json.load(handle)
    return CrosswalkLoadResult(
        crosswalk=CareerOccupationCrosswalk.model_validate(payload),
        warnings=[],
    )


@lru_cache(maxsize=1)
def load_default_occupational_crosswalk() -> CrosswalkLoadResult:
    return load_occupational_crosswalk()
