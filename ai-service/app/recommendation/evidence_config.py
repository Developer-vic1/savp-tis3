import json
from functools import lru_cache
from pathlib import Path
from typing import Literal

from pydantic import BaseModel, ConfigDict

SERVICE_ROOT = Path(__file__).resolve().parents[2]
CRITERIA_V2_PATH = SERVICE_ROOT / "data" / "criteria" / "recommendation_v2.json"


class EvidenceConfigModel(BaseModel):
    model_config = ConfigDict(extra="forbid")


class RecommendationV2Policy(EvidenceConfigModel):
    criteria_version: Literal["v2_evidence_based"]
    status: str
    purpose: str
    aggregation_policy: Literal["NO_COMPOSITE_SCORE"]
    ranking_policy: Literal["NO_GLOBAL_RANKING"]
    missing_data_policy: Literal["UNKNOWN_NOT_ZERO"]
    preparation_policy: Literal["OBSERVED_EVIDENCE_ONLY"]
    academic_match_policy: Literal["DOCUMENTED_LABEL_CONTAINMENT"]
    riasec_career_comparison: Literal["DOCUMENTED_OCCUPATIONAL_REFERENCE_ONLY"]
    technical_relation_policy: Literal["TRACEABLE_BRIDGE_RELATIONS_ONLY"]
    declared_interest_policy: Literal["DOCUMENTED_CATALOG_TERMS_ONLY"]
    limitations: list[str]


@lru_cache(maxsize=1)
def load_recommendation_v2_policy() -> RecommendationV2Policy:
    with CRITERIA_V2_PATH.open(encoding="utf-8") as handle:
        payload = json.load(handle)
    return RecommendationV2Policy.model_validate(payload)
