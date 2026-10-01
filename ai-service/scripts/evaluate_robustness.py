import json
from pathlib import Path
from typing import cast

from app.contracts.requests import AnalysisRequest
from app.learning_analytics.academic_performance import build_academic_profile
from app.recommendation.config import RecommendationCriteria, load_recommendation_criteria
from app.recommendation.dominance import non_dominated_alternatives
from app.recommendation.engine import build_career_ranking
from app.recommendation.robustness import evaluate_rank_robustness
from app.riasec.scoring import score_riasec

SERVICE_ROOT = Path(__file__).resolve().parents[1]


def main() -> None:
    fixture_path = SERVICE_ROOT / "data" / "fixtures" / "complete_profile.json"
    payload = cast(dict[str, object], json.loads(fixture_path.read_text(encoding="utf-8")))
    for metadata in ("fixture_type", "seed", "scenario_note"):
        payload.pop(metadata, None)
    request = AnalysisRequest.model_validate(payload)
    vocational = score_riasec(request.vocational) if request.vocational else None
    academic = (
        build_academic_profile(request.academic, request.attendance)
        if request.academic
        else None
    )
    base = load_recommendation_criteria()
    rankings: list[list[str]] = []
    parameter_sets: list[dict[str, float]] = []
    base_dimensions: dict[str, dict[str, float | None]] = {}
    for affinity_units in range(11):
        affinity_weight = affinity_units / 10
        weights = {
            "affinity": affinity_weight,
            "preparation": round(1 - affinity_weight, 1),
        }
        criteria_payload = base.model_dump(mode="json")
        criteria_payload["compatibility_weights"] = weights
        criteria = RecommendationCriteria.model_validate(criteria_payload)
        ranking, _ = build_career_ranking(request, vocational, academic, criteria)
        rankings.append([item.career_id for item in ranking])
        parameter_sets.append(weights)
        if not base_dimensions:
            base_dimensions = {
                item.career_id: {
                    "affinity": item.affinity_score,
                    "preparation": item.preparation_score,
                }
                for item in ranking
            }

    comparable_dimensions = ["affinity", "preparation"]
    pareto_front = non_dominated_alternatives(base_dimensions, comparable_dimensions)
    report = evaluate_rank_robustness(
        rankings,
        top_k=3,
        dominance_fronts=[pareto_front for _ in rankings],
    )
    output = {
        "evaluation_type": "LEGACY_EXPERIMENTAL_WEIGHT_SPACE",
        "criteria_version": base.criteria_version,
        "fixture_classification": "SYNTHETIC_TEST_FIXTURE",
        "parameter_sets": parameter_sets,
        "sensitivity_context": (
            "Afinidad y preparación V1 se agregan solo para estudiar sensibilidad del baseline."
        ),
        "robustness_context": "ROBUSTNESS_OVER_WEIGHT_SPACE_NOT_PROBABILITY",
        "pareto_dimensions": comparable_dimensions,
        "non_dominated_alternatives": pareto_front,
        "metrics": report.model_dump(mode="json"),
    }
    print(json.dumps(output, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
