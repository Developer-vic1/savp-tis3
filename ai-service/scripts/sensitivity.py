import json
from pathlib import Path
from typing import Any

from app.contracts.requests import AnalysisRequest
from app.learning_analytics.academic_performance import build_academic_profile
from app.recommendation.config import RecommendationCriteria, load_recommendation_criteria
from app.recommendation.engine import build_career_ranking
from app.riasec.scoring import score_riasec

SERVICE_ROOT = Path(__file__).resolve().parents[1]


def _fixture() -> AnalysisRequest:
    path = SERVICE_ROOT / "data" / "fixtures" / "complete_profile.json"
    payload: dict[str, Any] = json.loads(path.read_text(encoding="utf-8"))
    payload.pop("fixture_type", None)
    payload.pop("seed", None)
    return AnalysisRequest.model_validate(payload)


def _variant(
    base: RecommendationCriteria,
    *,
    compatibility_weights: dict[str, float] | None = None,
    minimum_preparation_coverage: float | None = None,
) -> RecommendationCriteria:
    payload = base.model_dump(mode="json")
    if compatibility_weights is not None:
        payload["compatibility_weights"] = compatibility_weights
    if minimum_preparation_coverage is not None:
        payload["minimum_preparation_coverage"] = minimum_preparation_coverage
    return RecommendationCriteria.model_validate(payload)


def main() -> None:
    request = _fixture()
    vocational = score_riasec(request.vocational) if request.vocational else None
    academic = (
        build_academic_profile(request.academic, request.attendance)
        if request.academic
        else None
    )
    base = load_recommendation_criteria()
    variants = {
        "base_55_45": base,
        "affinity_heavy_80_20": _variant(
            base, compatibility_weights={"affinity": 0.8, "preparation": 0.2}
        ),
        "preparation_heavy_20_80": _variant(
            base, compatibility_weights={"affinity": 0.2, "preparation": 0.8}
        ),
        "coverage_threshold_70": _variant(base, minimum_preparation_coverage=0.7),
    }
    output: dict[str, object] = {}
    for name, criteria in variants.items():
        ranking, warnings = build_career_ranking(
            request,
            vocational,
            academic,
            criteria_override=criteria,
        )
        output[name] = {
            "ranking": [
                {
                    "career_id": item.career_id,
                    "score": item.compatibility_score,
                }
                for item in ranking
            ],
            "warnings": warnings,
        }
    print(json.dumps(output, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
