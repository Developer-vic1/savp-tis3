from typing import Literal

from pydantic import BaseModel, ConfigDict, Field

from app.contracts.evidence import (
    AvailabilityStatus,
    EvidenceStatement,
    Limitation,
    SourceReference,
)
from app.recommendation.bridge_v2 import BridgeRelationStatus

MatchBasis = Literal[
    "SUBJECT_LABEL_CONTAINMENT",
    "AREA_LABEL_CONTAINMENT",
    "DOCUMENTED_ALIAS",
    "SEMANTIC_HYPOTHESIS",
]


class EvidenceModel(BaseModel):
    model_config = ConfigDict(extra="forbid", str_strip_whitespace=True)


class AcademicEvidence(EvidenceModel):
    relation_id: str
    competency: str
    secondary_content: str
    university_knowledge: str
    initial_subject: str
    record_subject: str
    record_area: str | None
    record_period: str | None
    normalized_score: float = Field(ge=0, le=100)
    match_basis: MatchBasis
    relation_status: BridgeRelationStatus
    evidence_label: str
    source_secondary: str
    source_university: str


class TechnicalEvidence(EvidenceModel):
    relation_id: str
    observed_value: str
    observed_evidence: str | None
    matched_against: str
    match_basis: Literal["TECHNICAL_LABEL_CONTAINMENT"]
    relation_status: BridgeRelationStatus
    evidence_label: str
    source_secondary: str
    source_university: str


class DeclaredInterestEvidence(EvidenceModel):
    declared_interest: str
    catalog_value: str
    catalog_field: Literal[
        "career_name",
        "career_alias",
        "official_knowledge_area",
        "initial_subject",
    ]
    match_basis: Literal["CATALOG_LABEL_CONTAINMENT"]


class EvidenceQuality(EvidenceModel):
    academic_record_count: int = Field(ge=0)
    distinct_subject_count: int = Field(ge=0)
    distinct_area_count: int = Field(ge=0)
    ordered_period_count: int = Field(ge=0)
    temporal_coverage: float | None = Field(default=None, ge=0, le=1)
    attendance_available: bool
    activity_available: bool
    riasec_complete: bool
    technical_evidence_count: int = Field(ge=0)
    declared_interest_count: int = Field(ge=0)
    historical_period_count: int = Field(ge=0)
    missing_components: list[str]
    notes: list[str]


class PreparationEvidence(EvidenceModel):
    competency: str
    observed_subjects: list[str]
    observed_areas: list[str]
    normalized_observations: list[float]
    related_university_knowledge: list[str]
    initial_subjects: list[str]
    relation_ids: list[str]
    source_ids: list[str]
    relation_status: BridgeRelationStatus
    observation_status: AvailabilityStatus
    match_bases: list[MatchBasis]
    justification: str
    limitations: list[Limitation]


class ReinforcementArea(EvidenceModel):
    competency: str
    related_university_knowledge: str
    initial_subject: str
    status: Literal["REINFORCEMENT_AREA"] = "REINFORCEMENT_AREA"
    rationale: str
    relation_id: str
    source_ids: list[str]


class NumericGap(EvidenceModel):
    competency: str
    observed_value: float = Field(ge=0, le=100)
    documented_threshold: float = Field(ge=0, le=100)
    gap_value: float = Field(ge=0, le=100)
    scale: str
    source_id: str


class EvidenceRelation(EvidenceModel):
    status: AvailabilityStatus
    evidence: list[EvidenceStatement]
    sources: list[SourceReference]
    limitations: list[Limitation]


class PreparationEvidenceProfile(EvidenceModel):
    related_relations: int = Field(ge=0)
    relations_with_observed_academic_evidence: int = Field(ge=0)
    academic_evidence: list[AcademicEvidence]
    evidence_items: list[PreparationEvidence]
    areas_without_observed_evidence: list[str]
    reinforcement_areas: list[ReinforcementArea]
    numeric_gaps: list[NumericGap]
    interpretation: str


class CareerAcademicProgram(EvidenceModel):
    degree: str | None
    duration: str | None
    professional_profile: str | None
    knowledge_areas: list[str]
    documented_subjects: list[str]
    curriculum_status: AvailabilityStatus
    curriculum_scope: Literal[
        "DOCUMENTED_INITIAL_SUBJECTS",
        "OFFICIAL_CURRICULUM_SOURCE_ONLY",
        "UNAVAILABLE",
    ]
    curriculum_note: str
    sources: list[SourceReference]


class CareerEvidenceProfile(EvidenceModel):
    career_id: str
    career_name: str
    university: str
    academic_program: CareerAcademicProgram
    criteria_version: Literal["v2_evidence_based"] = "v2_evidence_based"
    vocational_interest_relation: EvidenceRelation
    technical_relation: EvidenceRelation
    declared_interest_relation: EvidenceRelation
    occupational_relation: EvidenceRelation
    riasec_reference_status: Literal[
        "AVAILABLE_DOCUMENTED_OCCUPATIONAL_CROSSWALK",
        "UNAVAILABLE_PENDING_OCCUPATIONAL_CROSSWALK",
    ]
    student_riasec_code: str | None
    preparation: PreparationEvidenceProfile
    technical_evidence: list[TechnicalEvidence]
    declared_interest_evidence: list[DeclaredInterestEvidence]
    evidence_quality: EvidenceQuality
    areas_observed: list[str]
    areas_without_evidence: list[str]
    sources: list[SourceReference]
    source_ids: list[str]
    limitations: list[Limitation]


class RecommendationV2Result(EvidenceModel):
    criteria_version: Literal["v2_evidence_based"] = "v2_evidence_based"
    aggregation_policy: Literal["NO_COMPOSITE_SCORE"] = "NO_COMPOSITE_SCORE"
    ranking_policy: Literal["NO_GLOBAL_RANKING"] = "NO_GLOBAL_RANKING"
    comparison_status: Literal[
        "NOT_APPLIED_NONCOMPARABLE_CONSTRUCTS"
    ] = "NOT_APPLIED_NONCOMPARABLE_CONSTRUCTS"
    crosswalk_version: str | None
    profiles: list[CareerEvidenceProfile]
    warnings: list[str]
