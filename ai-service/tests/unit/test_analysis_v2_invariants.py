import inspect
import json
from datetime import UTC, datetime
from pathlib import Path
from typing import cast

from app.contracts.v2 import AnalysisV2Request
from app.domain.student_profile_v2 import analyze_student_v2
from app.learning_analytics.academic_performance import normalize_score
from app.recommendation import evidence_engine

FIXTURES = Path(__file__).resolve().parents[2] / "data" / "fixtures"
FIXED_TIME = datetime(2026, 9, 29, tzinfo=UTC)


def _payload(name: str = "complete_profile.json") -> dict[str, object]:
    payload = cast(dict[str, object], json.loads((FIXTURES / name).read_text(encoding="utf-8")))
    for metadata in ("fixture_type", "seed", "scenario_note"):
        payload.pop(metadata, None)
    payload["schema_version"] = "2.0"
    return payload


def _request(payload: dict[str, object] | None = None) -> AnalysisV2Request:
    return AnalysisV2Request.model_validate(payload or _payload())


def _analytical_dump(request: AnalysisV2Request) -> dict[str, object]:
    result = analyze_student_v2(
        request,
        "deterministic-trace",
        generated_at=FIXED_TIME,
    )
    return cast(dict[str, object], result.model_dump(mode="json"))


def test_changing_grades_does_not_change_riasec() -> None:
    base_payload = _payload()
    changed_payload = json.loads(json.dumps(base_payload))
    for record in changed_payload["academic"]["records"]:
        record["score"] = 10
    base = analyze_student_v2(_request(base_payload), "base", generated_at=FIXED_TIME)
    changed = analyze_student_v2(
        _request(cast(dict[str, object], changed_payload)),
        "changed",
        generated_at=FIXED_TIME,
    )
    assert (
        base.student_snapshot.vocational_interest_evidence
        == changed.student_snapshot.vocational_interest_evidence
    )


def test_changing_riasec_does_not_change_academic_evidence() -> None:
    base_payload = _payload()
    changed_payload = json.loads(json.dumps(base_payload))
    for response in changed_payload["vocational"]["responses"]:
        response["value"] = (response["value"] + 1) % 5
    base = analyze_student_v2(_request(base_payload), "base", generated_at=FIXED_TIME)
    changed = analyze_student_v2(
        _request(cast(dict[str, object], changed_payload)),
        "changed",
        generated_at=FIXED_TIME,
    )
    assert base.student_snapshot.academic_evidence == changed.student_snapshot.academic_evidence


def test_missing_academic_evidence_is_not_zero() -> None:
    payload = _payload()
    payload.pop("academic", None)
    result = analyze_student_v2(_request(payload), "missing", generated_at=FIXED_TIME)
    assert result.student_snapshot.academic_evidence.summary is None
    for profile in result.career_evidence_profiles:
        assert profile.preparation.academic_evidence == []
        assert all(
            item.observation_status == "UNAVAILABLE"
            for item in profile.preparation.evidence_items
        )


def test_preparation_does_not_contain_evidence_quality() -> None:
    result = analyze_student_v2(_request(), "quality", generated_at=FIXED_TIME)
    for profile in result.career_evidence_profiles:
        assert "evidence_quality" not in profile.preparation.model_dump(mode="json")


def test_temporal_metadata_does_not_change_content_observations() -> None:
    payload = _payload()
    without_time = json.loads(json.dumps(payload))
    for record in without_time["academic"]["records"]:
        record.pop("period", None)
        record.pop("period_order", None)
    with_result = analyze_student_v2(_request(payload), "with", generated_at=FIXED_TIME)
    without_result = analyze_student_v2(
        _request(cast(dict[str, object], without_time)),
        "without",
        generated_at=FIXED_TIME,
    )
    by_career_with = {item.career_id: item for item in with_result.career_evidence_profiles}
    by_career_without = {
        item.career_id: item for item in without_result.career_evidence_profiles
    }
    for career_id, with_profile in by_career_with.items():
        without_profile = by_career_without[career_id]
        assert [
            item.normalized_observations for item in with_profile.preparation.evidence_items
        ] == [
            item.normalized_observations
            for item in without_profile.preparation.evidence_items
        ]


def test_same_input_produces_same_output_with_fixed_execution_metadata() -> None:
    request = _request()
    assert _analytical_dump(request) == _analytical_dump(request)


def test_equivalent_record_order_does_not_change_substantive_output() -> None:
    payload = _payload()
    reversed_payload = json.loads(json.dumps(payload))
    reversed_payload["academic"]["records"].reverse()
    first = analyze_student_v2(_request(payload), "first", generated_at=FIXED_TIME)
    second = analyze_student_v2(
        _request(cast(dict[str, object], reversed_payload)),
        "second",
        generated_at=FIXED_TIME,
    )
    assert first.student_snapshot == second.student_snapshot
    assert first.career_evidence_profiles == second.career_evidence_profiles


def test_equivalent_scales_normalize_to_same_value() -> None:
    request = _request()
    record_type = type(request.academic.records[0]) if request.academic else None
    assert record_type is not None
    score_20 = record_type(subject="Matemática", score=15, scale_max=20)
    score_100 = record_type(subject="Matemática", score=75, scale_max=100)
    assert normalize_score(score_20) == normalize_score(score_100) == 75


def test_career_without_evidence_gets_no_strong_conclusion() -> None:
    payload = _payload("insufficient_profile.json")
    result = analyze_student_v2(_request(payload), "none", generated_at=FIXED_TIME)
    for profile in result.career_evidence_profiles:
        assert profile.technical_relation.status == "UNAVAILABLE"
        assert profile.declared_interest_relation.status == "UNAVAILABLE"
        assert profile.preparation.relations_with_observed_academic_evidence == 0


def test_llm_is_not_part_of_analytical_engine() -> None:
    domain_source = inspect.getsource(analyze_student_v2).casefold()
    engine_source = inspect.getsource(evidence_engine).casefold()
    assert "local_llm" not in domain_source + engine_source
    assert "tutor" not in domain_source + engine_source


def test_output_has_no_success_probability_or_inferred_intelligence() -> None:
    serialized = json.dumps(_analytical_dump(_request()), ensure_ascii=False).casefold()
    assert "probability_of_success" not in serialized
    assert '"intelligence"' not in serialized
    assert "tu inteligencia es" not in serialized
