from typing import Any, Literal

from pydantic import BaseModel, ConfigDict, Field

PROMPT_SCHEMA_VERSION = "prompt-schema-v1.0.0"


class PromptModel(BaseModel):
    model_config = ConfigDict(extra="forbid")


class TutorClaim(PromptModel):
    claim_text: str
    citation_source_id: str
    locator: str | None = None
    evidence_type: Literal[
        "DIRECT_QUOTE", "EXTRACTIVE_SUMMARY", "GENERAL_STATEMENT"
    ] = "EXTRACTIVE_SUMMARY"


class TutorStructuredOutput(PromptModel):
    answer: str
    claims: list[TutorClaim] = Field(default_factory=list)
    sources: list[str] = Field(default_factory=list)
    limitations: list[str] = Field(default_factory=list)
    insufficient_evidence: bool = False
    suggested_topics: list[str] = Field(default_factory=list, max_length=8)
    warnings: list[str] = Field(default_factory=list)


class PromptMetadata(PromptModel):
    prompt_id: str
    version: str
    purpose: str
    prompt_hash: str
    schema_version: str = PROMPT_SCHEMA_VERSION


class PromptEvaluationScenario(PromptModel):
    scenario_id: str
    category: Literal[
        "FACTUAL_GROUNDED",
        "CAREER_DECISION_REFUSAL",
        "PSYCHOMETRIC_INTELLIGENCE_REFUSAL",
        "PROBABILITY_SUCCESS_REFUSAL",
        "HALLUCINATION_REFUSAL",
        "INSUFFICIENT_EVIDENCE_ABSTENTION",
        "PROMPT_INJECTION_DEFENSE",
        "FAKE_CITATION_REFUSAL",
        "AMBIGUOUS_QUERY",
        "PRIVACY_PII_PROTECTION",
    ]
    user_prompt: str
    retrieved_evidence_fixture: list[dict[str, Any]] = Field(default_factory=list)
    student_context: dict[str, Any] = Field(default_factory=dict)
    expected_behavior: str
    expected_abstention: bool = False
    expected_refusal: bool = False
    prohibited_substrings: list[str] = Field(default_factory=list)
    mandatory_substrings: list[str] = Field(default_factory=list)


class PromptEvaluationReport(PromptModel):
    report_version: str = "tutor-eval-v1.0.0"
    total_scenarios: int
    passed_scenarios: int
    failed_scenarios: int
    pass_rate: float
    schema_valid_rate: float
    citation_precision: float
    citation_support_rate: float | None = None
    unsupported_claim_rate: float | None = None
    citation_support_status: Literal["NOT_EVALUATED", "EVALUATED"] = "NOT_EVALUATED"
    abstention_accuracy: float
    prompt_injection_success_rate: float
    scenario_results: list[dict[str, Any]]
