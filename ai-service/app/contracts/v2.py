from datetime import datetime
from typing import Annotated, Literal

from pydantic import BaseModel, ConfigDict, Field, field_validator, model_validator

from app.contracts.evidence import AvailabilityStatus, Limitation, SourceReference
from app.contracts.requests import (
    AcademicData,
    AcademicRecord,
    AttendanceData,
    LearningActivityData,
    TechnicalData,
    VocationalData,
)
from app.contracts.responses import AnalysisStatus, LearningActivityProfile, RiasecProfile
from app.recommendation.evidence_models import CareerEvidenceProfile, EvidenceQuality
from app.riasec.public_contract import PublicRiasecData


class V2ContractModel(BaseModel):
    model_config = ConfigDict(extra="forbid", str_strip_whitespace=True)


class DeclaredInterest(V2ContractModel):
    text: str = Field(min_length=1, max_length=200)
    source: Literal["STUDENT_DECLARATION"] = "STUDENT_DECLARATION"


class HistoricalAcademicPeriod(V2ContractModel):
    period: str = Field(min_length=1, max_length=80)
    period_order: int | None = Field(default=None, ge=0, le=1000)
    records: list[AcademicRecord] = Field(default_factory=list, max_length=500)


class AnalysisV2Request(V2ContractModel):
    schema_version: Literal["2.0"] = "2.0"
    student_id: str = Field(min_length=1, max_length=100, pattern=r"^[A-Za-z0-9._:-]+$")
    academic_period: str | None = Field(default=None, max_length=80)
    course: str | None = Field(default=None, max_length=120)
    academic: AcademicData | None = None
    attendance: AttendanceData | None = None
    vocational: VocationalData | None = None
    riasec_public: PublicRiasecData | None = None
    technical: TechnicalData | None = None
    declared_interests: list[DeclaredInterest] = Field(default_factory=list, max_length=50)
    history: list[HistoricalAcademicPeriod] = Field(default_factory=list, max_length=100)
    learning_activity: LearningActivityData | None = None

    @model_validator(mode="after")
    def adapt_public_riasec(self) -> "AnalysisV2Request":
        if self.riasec_public is not None:
            if self.vocational is not None:
                raise ValueError("Use riasec_public o vocational, nunca ambos")
            self.vocational = self.riasec_public.to_vocational()
        return self

    @field_validator("declared_interests", mode="before")
    @classmethod
    def expand_declared_interest_strings(cls, value: object) -> object:
        if isinstance(value, list):
            return [{"text": item} if isinstance(item, str) else item for item in value]
        return value


NormalizedValue = Annotated[float, Field(ge=0, le=100, allow_inf_nan=False)]


class DescriptiveStatistics(V2ContractModel):
    count: int = Field(ge=1)
    mean: NormalizedValue
    minimum: NormalizedValue
    maximum: NormalizedValue
    standard_deviation: float | None = Field(default=None, ge=0, allow_inf_nan=False)


class ObservedAcademicHighlight(V2ContractModel):
    kind: Literal["BEST_OBSERVED_SUBJECT", "BEST_OBSERVED_AREA"]
    label: str
    mean: NormalizedValue
    statement: str


class AcademicEvidenceProfileV2(V2ContractModel):
    status: AvailabilityStatus
    summary: DescriptiveStatistics | None
    subjects: dict[str, DescriptiveStatistics]
    areas: dict[str, DescriptiveStatistics]
    periods: dict[str, DescriptiveStatistics]
    temporal_period_count: int = Field(ge=0)
    temporal_coverage_ratio: float | None = Field(default=None, ge=0, le=1)
    best_observed_subject: ObservedAcademicHighlight | None
    best_observed_area: ObservedAcademicHighlight | None
    warnings: list[str]


class VocationalInterestEvidenceProfile(V2ContractModel):
    status: AvailabilityStatus
    construct_name: Literal["VOCATIONAL_INTEREST"] = "VOCATIONAL_INTEREST"
    riasec: RiasecProfile | None
    limitations: list[Limitation]


class TechnicalEvidenceProfileV2(V2ContractModel):
    status: AvailabilityStatus
    specialty: str | None
    competencies: list[str]
    evidence: list[str]


class AttendanceEvidenceProfileV2(V2ContractModel):
    status: AvailabilityStatus
    attended_classes: int | None
    total_classes: int | None
    attendance_ratio: float | None = Field(default=None, ge=0, le=1)


class LearningActivityEvidenceProfileV2(V2ContractModel):
    status: AvailabilityStatus
    metrics: LearningActivityProfile | None


class DeclaredInterestEvidenceProfileV2(V2ContractModel):
    status: AvailabilityStatus
    interests: list[DeclaredInterest]


class HistoricalEvidenceProfileV2(V2ContractModel):
    status: AvailabilityStatus
    periods: list[HistoricalAcademicPeriod]
    period_count: int = Field(ge=0)
    record_count: int = Field(ge=0)


class StudentAnalyticalSnapshotV2(V2ContractModel):
    academic_evidence: AcademicEvidenceProfileV2
    vocational_interest_evidence: VocationalInterestEvidenceProfile
    technical_evidence: TechnicalEvidenceProfileV2
    learning_activity_evidence: LearningActivityEvidenceProfileV2
    attendance_evidence: AttendanceEvidenceProfileV2
    declared_interest_evidence: DeclaredInterestEvidenceProfileV2
    historical_evidence: HistoricalEvidenceProfileV2
    evidence_quality: EvidenceQuality
    missing_components: list[str]


class TraceabilityV2(V2ContractModel):
    input_hash: str = Field(pattern=r"^[0-9a-f]{64}$")
    engine_version: str
    criteria_version: str
    bridge_version: str | None
    bridge_source_mode: Literal["V2_DOCUMENT", "LEGACY_EXPERIMENTAL_ADAPTER", "UNAVAILABLE"]
    catalog_version: str | None
    crosswalk_version: str | None
    instrument_version: str | None
    generated_at: datetime
    trace_id: str


class AnalysisV2Response(V2ContractModel):
    schema_version: Literal["2.0"] = "2.0"
    engine_version: str
    criteria_version: str
    trace_id: str
    student_ref: str
    analysis_status: AnalysisStatus
    student_snapshot: StudentAnalyticalSnapshotV2
    career_evidence_profiles: list[CareerEvidenceProfile]
    traceability: TraceabilityV2
    warnings: list[str]
    limitations: list[Limitation]
    sources_used: list[SourceReference]
