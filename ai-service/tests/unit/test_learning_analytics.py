import pytest
from pydantic import ValidationError

from app.contracts.requests import (
    AcademicData,
    AcademicRecord,
    LearningActivityData,
    LearningTask,
)
from app.learning_analytics.academic_performance import build_academic_profile, normalize_score
from app.learning_analytics.activity import build_learning_activity_profile
from app.learning_analytics.trends import linear_slope, trend_label


def test_linear_slope_boundaries() -> None:
    assert linear_slope([]) is None
    assert linear_slope([(1, 50)]) is None
    assert linear_slope([(1, 50), (1, 80)]) is None
    assert linear_slope([(1, 60), (2, 70), (3, 80)]) == 10
    assert trend_label(10) == "ASCENDING"
    assert trend_label(-2) == "DESCENDING"
    assert trend_label(0.5) == "STABLE"


def test_academic_summary_does_not_invent_attendance() -> None:
    academic = AcademicData(
        records=[
            AcademicRecord(subject="Matemática", score=0, period_order=1),
            AcademicRecord(subject="Matemática", score=100, period_order=2),
        ]
    )
    profile = build_academic_profile(academic, None)
    assert profile.overall_mean == 50
    assert profile.minimum == 0
    assert profile.maximum == 100
    assert profile.attendance_ratio is None
    assert profile.evidence_status == "SUFFICIENT"


def test_different_scales_are_normalized_before_aggregation() -> None:
    records = [
        AcademicRecord(
            subject="Matemática",
            area="Ciencia",
            score=15,
            scale_min=0,
            scale_max=20,
            period="T1",
            period_order=1,
        ),
        AcademicRecord(
            subject="Matemática",
            area="Ciencia",
            score=90,
            period="T3",
            period_order=3,
        ),
    ]
    profile = build_academic_profile(AcademicData(records=records), None)
    assert normalize_score(records[0]) == 75
    assert profile.overall_mean == 82.5
    assert profile.subjects["Matemática"].mean == 82.5
    assert profile.areas["Ciencia"].mean == 82.5
    assert profile.overall_trend_slope == 7.5
    assert profile.temporal_coverage.ratio == 0.67


@pytest.mark.parametrize(
    "record",
    [
        {"subject": "Matemática", "score": 21, "scale_max": 20},
        {"subject": "Matemática", "score": 10, "scale_min": 20, "scale_max": 20},
    ],
)
def test_invalid_score_scales_are_rejected(record: dict[str, object]) -> None:
    with pytest.raises(ValidationError):
        AcademicRecord.model_validate(record)


def test_single_observation_keeps_variability_and_trend_unknown() -> None:
    profile = build_academic_profile(
        AcademicData(records=[AcademicRecord(subject="Artes", score=80)]),
        None,
    )
    assert profile.standard_deviation is None
    assert profile.consistency_ratio is None
    assert profile.overall_trend_slope is None
    assert profile.temporal_coverage.ratio is None
    assert profile.evidence_status == "PARTIAL"


def test_detailed_learning_activity_calculates_delivery_grades_and_regularity() -> None:
    activity = LearningActivityData(
        tasks=[
            LearningTask(
                task_id="1", delivered=True, score=15, scale_max=20, period_order=1
            ),
            LearningTask(task_id="2", delivered=True, late=True, score=80, period_order=1),
            LearningTask(task_id="3", delivered=True, score=90, period_order=2),
            LearningTask(task_id="4", delivered=False, period_order=2),
        ]
    )
    profile = build_learning_activity_profile(activity)
    assert profile.evidence_status == "SUFFICIENT"
    assert profile.assigned == 4
    assert profile.delivered == 3
    assert profile.missing == 1
    assert profile.completion_ratio == 0.75
    assert profile.on_time_ratio == 0.5
    assert profile.late_ratio == 0.33
    assert profile.mean_grade == 81.67
    assert profile.regularity_ratio == 0.5


def test_aggregate_activity_never_invents_grades_or_regularity() -> None:
    profile = build_learning_activity_profile(
        LearningActivityData(assigned=20, delivered=18, late=2)
    )
    assert profile.evidence_status == "PARTIAL"
    assert profile.completion_ratio == 0.9
    assert profile.on_time_ratio == 0.8
    assert profile.mean_grade is None
    assert profile.regularity_ratio is None


@pytest.mark.parametrize(
    "payload",
    [
        {"assigned": 2, "delivered": 3},
        {"assigned": 2, "delivered": 1, "late": 2},
        {"tasks": [{"task_id": "1", "delivered": False, "late": True}]},
    ],
)
def test_inconsistent_activity_is_rejected(payload: dict[str, object]) -> None:
    with pytest.raises(ValidationError):
        LearningActivityData.model_validate(payload)
