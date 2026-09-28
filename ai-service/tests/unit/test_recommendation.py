import json
from pathlib import Path

from app.contracts.requests import AnalysisRequest
from app.domain.student_profile import analyze_student
from app.learning_analytics.academic_performance import build_academic_profile
from app.recommendation.config import load_bridge, load_recommendation_criteria
from app.recommendation.engine import build_career_ranking
from app.riasec.scoring import score_riasec

FIXTURES = Path(__file__).resolve().parents[2] / "data" / "fixtures"


def _request(name: str) -> AnalysisRequest:
    payload = json.loads((FIXTURES / name).read_text(encoding="utf-8"))
    for metadata in ("fixture_type", "seed", "scenario_note"):
        payload.pop(metadata, None)
    return AnalysisRequest.model_validate(payload)


def test_bridge_and_weights_are_versioned_and_pending_expert_review() -> None:
    bridge = load_bridge()
    criteria = load_recommendation_criteria()
    assert bridge.status == "CONFIGURACIÓN EXPERIMENTAL"
    assert all(relation.review_status == "NEEDS_EXPERT_REVIEW" for relation in bridge.relations)
    assert criteria.criteria_version == "v1_experimental"
    assert criteria.affinity_weights == {
        "riasec": 0.6,
        "bth": 0.25,
        "declared_interest": 0.15,
    }
    assert criteria.compatibility_weights == {"affinity": 0.55, "preparation": 0.45}


def test_complete_profile_ranks_real_careers_deterministically() -> None:
    request = _request("complete_profile.json")
    first = analyze_student(request, "trace-1")
    second = analyze_student(request, "trace-2")
    first_order = [item.career_id for item in first.career_ranking]
    second_order = [item.career_id for item in second.career_ranking]
    assert first.ranking_status == "RANKED"
    assert first_order == second_order
    assert first_order[0] == "BO-UCB-LP-ING-SISTEMAS"
    assert first.career_ranking[0].compatibility_score == 82.82
    assert first_order.index("BO-UCB-LP-ING-CIVIL") < first_order.index(
        "BO-UMSA-LP-ING-CIVIL"
    )
    assert any("ING-AMBIENTAL" in warning for warning in first.ranking_warnings)


def test_affinity_and_preparation_remain_separate() -> None:
    low = analyze_student(_request("high_affinity_low_preparation.json"), "trace-low")
    systems = low.career_ranking[0]
    assert systems.career_id == "BO-UCB-LP-ING-SISTEMAS"
    assert systems.affinity_score == 80.83
    assert systems.preparation_score == 53.78
    math_gap = next(
        gap for gap in systems.reinforcement_areas if gap.competency == "razonamiento matemático"
    )
    assert math_gap.magnitude == max(0, math_gap.required - float(math_gap.current or 0))
    assert math_gap.priority == "HIGH"


def test_grades_do_not_change_affinity_but_do_change_preparation() -> None:
    base = _request("complete_profile.json")
    low_payload = base.model_dump(mode="json")
    for record in low_payload["academic"]["records"]:
        record["score"] = 40
    low = AnalysisRequest.model_validate(low_payload)
    high_result = analyze_student(base, "high")
    low_result = analyze_student(low, "low")
    high_systems = next(
        item for item in high_result.career_ranking if item.career_id == "BO-UCB-LP-ING-SISTEMAS"
    )
    low_systems = next(
        item for item in low_result.career_ranking if item.career_id == "BO-UCB-LP-ING-SISTEMAS"
    )
    assert high_systems.affinity_score == low_systems.affinity_score
    assert high_systems.preparation_score > low_systems.preparation_score


def test_route_respects_prerequisite_before_larger_dependent_gap() -> None:
    payload = _request("complete_profile.json").model_dump(mode="json")
    payload["academic"]["records"] = [
        {"subject": "Matemática", "score": 60, "period": "T1", "period_order": 1},
        {"subject": "Tecnología", "score": 0, "period": "T1", "period_order": 1},
    ]
    result = analyze_student(AnalysisRequest.model_validate(payload), "route")
    systems = next(
        item for item in result.career_ranking if item.career_id == "BO-UCB-LP-ING-SISTEMAS"
    )
    topics = [item.topic for item in systems.preparation_route]
    assert topics[:2] == [
        "Álgebra y matemática discreta",
        "Lógica y fundamentos de programación",
    ]


def test_insufficient_profile_does_not_generate_ranking() -> None:
    result = analyze_student(_request("insufficient_profile.json"), "empty")
    assert result.ranking_status == "INSUFFICIENT_EVIDENCE"
    assert result.career_ranking == []
    assert result.ranking_warnings[-1].startswith("No se generó ranking")


def test_sensitivity_to_compatibility_weights_and_coverage_threshold() -> None:
    request = _request("complete_profile.json")
    vocational = score_riasec(request.vocational) if request.vocational else None
    academic = (
        build_academic_profile(request.academic, request.attendance)
        if request.academic
        else None
    )
    base = load_recommendation_criteria()
    affinity_payload = base.model_dump(mode="json")
    affinity_payload["compatibility_weights"] = {"affinity": 0.8, "preparation": 0.2}
    affinity_heavy = type(base).model_validate(affinity_payload)
    base_ranking, _ = build_career_ranking(request, vocational, academic, base)
    affinity_ranking, _ = build_career_ranking(
        request, vocational, academic, affinity_heavy
    )
    assert base_ranking[0].career_id == affinity_ranking[0].career_id
    assert base_ranking[0].compatibility_score != affinity_ranking[0].compatibility_score

    strict_payload = base.model_dump(mode="json")
    strict_payload["minimum_preparation_coverage"] = 0.7
    strict = type(base).model_validate(strict_payload)
    strict_ranking, strict_warnings = build_career_ranking(
        request, vocational, academic, strict
    )
    assert strict_ranking == []
    assert strict_warnings[-1].startswith("No se generó ranking")
