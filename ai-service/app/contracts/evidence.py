from enum import StrEnum

from pydantic import BaseModel, ConfigDict, Field


class EvidenceContract(BaseModel):
    model_config = ConfigDict(extra="forbid", str_strip_whitespace=True)


class AvailabilityStatus(StrEnum):
    AVAILABLE = "AVAILABLE"
    PARTIAL = "PARTIAL"
    INSUFFICIENT = "INSUFFICIENT"
    UNAVAILABLE = "UNAVAILABLE"


class SourceReference(EvidenceContract):
    source_id: str = Field(min_length=1)
    title: str | None = None
    institution: str | None = None
    reference: str | None = None
    version: str | None = None
    official: bool | None = None


class Limitation(EvidenceContract):
    code: str = Field(min_length=1)
    message: str = Field(min_length=1)
    scope: str = Field(min_length=1)


class EvidenceStatement(EvidenceContract):
    kind: str = Field(min_length=1)
    statement: str = Field(min_length=1)
    source_ids: list[str] = Field(default_factory=list)
    relation_id: str | None = None
    is_inference: bool = False
