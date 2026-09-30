import json
from enum import StrEnum
from functools import lru_cache
from pathlib import Path
from typing import Literal

from pydantic import BaseModel, ConfigDict, Field, model_validator

from app.recommendation.config import BridgeRelation, load_bridge

SERVICE_ROOT = Path(__file__).resolve().parents[2]
BRIDGE_V2_PATH = SERVICE_ROOT / "data" / "bridge" / "secondary_university_v2.json"


class BridgeRelationStatus(StrEnum):
    DIRECTLY_DOCUMENTED = "DIRECTLY_DOCUMENTED"
    DOCUMENT_SUPPORTED_INFERENCE = "DOCUMENT_SUPPORTED_INFERENCE"
    HYPOTHESIS = "HYPOTHESIS"
    INSUFFICIENT_EVIDENCE = "INSUFFICIENT_EVIDENCE"


class BridgeV2Model(BaseModel):
    model_config = ConfigDict(extra="forbid", str_strip_whitespace=True)


class BridgeV2Relation(BridgeV2Model):
    relation_id: str = Field(min_length=1)
    secondary_content: str = Field(min_length=1)
    secondary_competency: str = Field(min_length=1)
    competency: str = Field(min_length=1)
    university_knowledge: str = Field(min_length=1)
    initial_subject: str = Field(min_length=1)
    career_ids: list[str] = Field(min_length=1)
    source_secondary: str = Field(min_length=1)
    source_university: str = Field(min_length=1)
    relation_status: BridgeRelationStatus
    evidence_label: str = Field(min_length=1)
    justification: str = Field(min_length=1)
    limitations: list[str] = Field(default_factory=list)
    version: str = Field(min_length=1)


class BridgeV2(BridgeV2Model):
    bridge_version: str = Field(min_length=1)
    status: str = Field(min_length=1)
    governance_principles: list[str]
    allowed_statuses: list[BridgeRelationStatus]
    relations: list[BridgeV2Relation]

    @model_validator(mode="after")
    def unique_relation_ids(self) -> "BridgeV2":
        relation_ids = [relation.relation_id for relation in self.relations]
        if len(relation_ids) != len(set(relation_ids)):
            raise ValueError("relation_id debe ser único")
        return self


class BridgeLoadResult(BridgeV2Model):
    bridge: BridgeV2 | None
    source_mode: Literal["V2_DOCUMENT", "LEGACY_EXPERIMENTAL_ADAPTER", "UNAVAILABLE"]
    warnings: list[str]


def _legacy_status(relation: BridgeRelation) -> BridgeRelationStatus:
    if relation.evidence_label == "HECHO DOCUMENTADO":
        return BridgeRelationStatus.DIRECTLY_DOCUMENTED
    if relation.evidence_label == "DECISIÓN DE DISEÑO":
        return BridgeRelationStatus.DOCUMENT_SUPPORTED_INFERENCE
    if relation.evidence_label == "HIPÓTESIS":
        return BridgeRelationStatus.HYPOTHESIS
    return BridgeRelationStatus.HYPOTHESIS


def _adapt_legacy_relation(relation: BridgeRelation) -> BridgeV2Relation:
    return BridgeV2Relation(
        relation_id=relation.relation_id,
        secondary_content=relation.secondary_content,
        secondary_competency=relation.competency,
        competency=relation.competency,
        university_knowledge=relation.university_knowledge,
        initial_subject=relation.initial_subject,
        career_ids=relation.career_ids,
        source_secondary=relation.source_secondary,
        source_university=relation.source_university,
        relation_status=_legacy_status(relation),
        evidence_label=relation.evidence_label,
        justification=relation.justification,
        limitations=[
            "Relación adaptada del bridge V1 experimental; no constituye equivalencia curricular."
        ],
        version=relation.version,
    )


def _load_path(path: Path) -> BridgeV2:
    with path.open(encoding="utf-8") as handle:
        payload = json.load(handle)
    return BridgeV2.model_validate(payload)


def load_bridge_v2(path: Path | None = None) -> BridgeLoadResult:
    requested_path = path or BRIDGE_V2_PATH
    if requested_path.is_file():
        return BridgeLoadResult(
            bridge=_load_path(requested_path),
            source_mode="V2_DOCUMENT",
            warnings=[],
        )
    if path is not None:
        return BridgeLoadResult(
            bridge=None,
            source_mode="UNAVAILABLE",
            warnings=[f"Bridge V2 no disponible en {requested_path}."],
        )

    legacy = load_bridge()
    adapted = BridgeV2(
        bridge_version=f"{legacy.bridge_version}:v2-adapter",
        status="LEGACY_EXPERIMENTAL_ADAPTER",
        governance_principles=[
            "AFFINITY_IS_NOT_PREPARATION",
            "MISSING_EVIDENCE_IS_NOT_ZERO",
        ],
        allowed_statuses=list(BridgeRelationStatus),
        relations=[_adapt_legacy_relation(relation) for relation in legacy.relations],
    )
    return BridgeLoadResult(
        bridge=adapted,
        source_mode="LEGACY_EXPERIMENTAL_ADAPTER",
        warnings=[
            "secondary_university_v2.json no está disponible; se usa un adaptador explícito "
            "del bridge V1 experimental."
        ],
    )


@lru_cache(maxsize=1)
def load_default_bridge_v2() -> BridgeLoadResult:
    return load_bridge_v2()
