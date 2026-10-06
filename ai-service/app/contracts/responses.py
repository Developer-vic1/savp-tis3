from datetime import datetime
from enum import StrEnum
from typing import Any, Literal

from pydantic import BaseModel, ConfigDict, Field


class ContractModel(BaseModel):
    model_config = ConfigDict(extra="forbid")


class AnalysisStatus(StrEnum):
    COMPLETE = "COMPLETE"
    PARTIAL = "PARTIAL"
    INSUFFICIENT = "INSUFFICIENT"


class EvidenceStatus(StrEnum):
    SUFFICIENT = "SUFFICIENT"
    PARTIAL = "PARTIAL"
    INSUFFICIENT = "INSUFFICIENT"


class HealthResponse(ContractModel):
    status: Literal["ok"] = "ok"
    service: Literal["savp-ai"] = "savp-ai"
    service_version: Literal["0.1.0"] = "0.1.0"
    schema_version: Literal["1.0"] = "1.0"


class Coverage(ContractModel):
    ratio: float = Field(ge=0, le=1)
    components: dict[str, bool]


class RiasecProfile(ContractModel):
    instrument_id: str
    instrument_version: str
    scores: dict[str, int]
    normalized_scores: dict[str, float]
    answered_items: int
    expected_items: int
    coverage_ratio: float = Field(ge=0, le=1)
    ordered_codes: list[str]
    holland_code: str
    top_codes: list[str]
    top_tie: bool
    interpretation: str


class SubjectSummary(ContractModel):
    count: int
    mean: float
    minimum: float
    maximum: float
    standard_deviation: float | None
    consistency_ratio: float | None = Field(default=None, ge=0, le=1)
    trend_slope: float | None
    trend_label: str | None


class TemporalCoverage(ContractModel):
    period_count: int
    periods: list[str]
    observed_orders: list[int]
    first_order: int | None
    last_order: int | None
    expected_order_count: int | None
    ratio: float | None = Field(default=None, ge=0, le=1)


class LearningActivityProfile(ContractModel):
    evidence_status: EvidenceStatus
    assigned: int | None
    delivered: int | None
    missing: int | None
    late: int | None
    completion_ratio: float | None = Field(default=None, ge=0, le=1)
    on_time_ratio: float | None = Field(default=None, ge=0, le=1)
    late_ratio: float | None = Field(default=None, ge=0, le=1)
    graded_count: int
    mean_grade: float | None = Field(default=None, ge=0, le=100)
    grade_standard_deviation: float | None
    regularity_ratio: float | None = Field(default=None, ge=0, le=1)
    warnings: list[str]


class EvidenceItem(ContractModel):
    code: str
    label: str
    evidence: str
    source_context: str


class CompetencyGap(ContractModel):
    competency: str
    current: float | None = Field(default=None, ge=0, le=100)
    required: float = Field(ge=0, le=100)
    magnitude: float | None = Field(default=None, ge=0, le=100)
    priority: str
    evidence: str
    source: str
    explanation: str
    status: str


class PreparationRouteItem(ContractModel):
    topic: str
    order: int = Field(gt=0)
    priority: str
    description: str
    prerequisite: str | None
    source: str
    rationale: str


class CareerRecommendation(ContractModel):
    rank: int = Field(gt=0)
    career_id: str
    career_name: str
    institution_context: str
    affinity_score: float = Field(ge=0, le=100)
    affinity_coverage: float = Field(ge=0, le=1)
    affinity_components: dict[str, float | None]
    preparation_score: float = Field(ge=0, le=100)
    preparation_coverage: float = Field(ge=0, le=1)
    preparation_components: dict[str, float | None]
    compatibility_score: float = Field(ge=0, le=100)
    compatibility_label: str
    compatibility_weights: dict[str, float]
    criteria_version: str
    strengths: list[EvidenceItem]
    reinforcement_areas: list[CompetencyGap]
    preparation_route: list[PreparationRouteItem]
    explanation: str
    evidence: list[str]
    sources: list[str]


class AcademicProfile(ContractModel):
    evidence_status: EvidenceStatus
    count: int
    overall_mean: float
    minimum: float
    maximum: float
    standard_deviation: float | None
    consistency_ratio: float | None = Field(default=None, ge=0, le=1)
    overall_trend_slope: float | None
    overall_trend_label: str | None
    subjects: dict[str, SubjectSummary]
    areas: dict[str, SubjectSummary]
    temporal_coverage: TemporalCoverage
    attendance_ratio: float | None
    strengths: list[str]
    areas_to_reinforce: list[str]
    warnings: list[str]


class AnalysisResponse(ContractModel):
    schema_version: Literal["1.0"] = "1.0"
    status: AnalysisStatus
    trace_id: str
    student_ref: str
    academic_period: str | None
    generated_at: datetime
    engine_version: Literal["0.1.0"] = "0.1.0"
    criteria_version: str = "v1_experimental"
    profile_version: Literal["student-profile-0.1.0"] = "student-profile-0.1.0"
    instrument_version: str | None
    input_hash: str
    coverage: Coverage
    missing_components: list[str]
    data_quality_status: AnalysisStatus
    vocational_profile: RiasecProfile | None
    academic_profile: AcademicProfile | None
    learning_activity_profile: LearningActivityProfile | None
    ranking_status: Literal["RANKED", "INSUFFICIENT_EVIDENCE"]
    career_ranking: list[CareerRecommendation]
    ranking_warnings: list[str]
    strengths: list[str]
    areas_to_reinforce: list[str]
    warnings: list[str]
    sources_used: list[dict[str, Any]]


class KnowledgeEvidence(ContractModel):
    source_id: str
    chunk_id: str
    title: str
    institution: str
    source_type: str
    publication_date: str | None
    page: int | None
    section: str | None
    summary: str
    relevant_text: str
    relevance: float
    reference: str
    official: bool
    retrieval_method: str


class KnowledgeSearchResponse(ContractModel):
    schema_version: Literal["1.0"] = "1.0"
    trace_id: str
    results: list[KnowledgeEvidence]
    insufficient_evidence: bool
    warnings: list[str]
    corpus_version: str
    embedding_model: str
    retrieval_version: str


class TutorResponse(ContractModel):
    schema_version: Literal["1.0"] = "1.0"
    trace_id: str
    answer: str
    answer_mode: Literal["STRUCTURED"]
    suggested_topics: list[str]
    sources: list[KnowledgeEvidence]
    insufficient_evidence: bool
    warnings: list[str]
    provider_version: str = "structured-answer-v1.0.0"
    corpus_version: str | None = None
    embedding_model: str | None = None
