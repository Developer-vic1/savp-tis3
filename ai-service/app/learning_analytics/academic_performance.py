from collections import defaultdict
from statistics import fmean, pstdev

from app.contracts.evidence import AvailabilityStatus
from app.contracts.requests import AcademicData, AcademicRecord, AttendanceData
from app.contracts.responses import (
    AcademicProfile,
    EvidenceStatus,
    SubjectSummary,
    TemporalCoverage,
)
from app.contracts.v2 import (
    AcademicEvidenceProfileV2,
    DescriptiveStatistics,
    ObservedAcademicHighlight,
)
from app.learning_analytics.trends import linear_slope, trend_label


def _rounded(value: float) -> float:
    return round(value, 2)


def normalize_score(record: AcademicRecord) -> float:
    score_range = float(record.scale_max - record.scale_min)
    normalized = 100 * (float(record.score) - float(record.scale_min)) / score_range
    return _rounded(normalized)


def descriptive_statistics(records: list[AcademicRecord]) -> DescriptiveStatistics:
    values = [normalize_score(record) for record in records]
    if not values:
        raise ValueError("Se requiere al menos un registro para resumir evidencia académica")
    return DescriptiveStatistics(
        count=len(values),
        mean=_rounded(fmean(values)),
        minimum=min(values),
        maximum=max(values),
        standard_deviation=_rounded(pstdev(values)) if len(values) > 1 else None,
    )


def consistency_ratio(values: list[float]) -> float | None:
    if len(values) < 2:
        return None
    return _rounded(max(0.0, 1 - (pstdev(values) / 50)))


def _summary(records: list[AcademicRecord]) -> SubjectSummary:
    values = [normalize_score(record) for record in records]
    by_period: dict[int, list[float]] = defaultdict(list)
    for record in records:
        if record.period_order is not None:
            by_period[record.period_order].append(normalize_score(record))
    trend_points = [(order, fmean(period_values)) for order, period_values in by_period.items()]
    slope = linear_slope(trend_points)
    return SubjectSummary(
        count=len(values),
        mean=_rounded(fmean(values)),
        minimum=min(values),
        maximum=max(values),
        standard_deviation=_rounded(pstdev(values)) if len(values) > 1 else None,
        consistency_ratio=consistency_ratio(values),
        trend_slope=slope,
        trend_label=trend_label(slope),
    )


def _temporal_coverage(records: list[AcademicRecord]) -> TemporalCoverage:
    orders = sorted({record.period_order for record in records if record.period_order is not None})
    labels = sorted({record.period for record in records if record.period})
    if orders:
        first_order = orders[0]
        last_order = orders[-1]
        expected = last_order - first_order + 1
        ratio = _rounded(len(orders) / expected)
        periods = labels or [str(order) for order in orders]
        period_count = len(orders)
    else:
        first_order = None
        last_order = None
        expected = None
        ratio = None
        periods = labels
        period_count = len(labels)
    return TemporalCoverage(
        period_count=period_count,
        periods=periods,
        observed_orders=orders,
        first_order=first_order,
        last_order=last_order,
        expected_order_count=expected,
        ratio=ratio,
    )


def build_academic_profile(
    academic: AcademicData,
    attendance: AttendanceData | None,
) -> AcademicProfile:
    records = academic.records
    scores = [normalize_score(record) for record in records]
    by_subject: dict[str, list[AcademicRecord]] = defaultdict(list)
    by_area: dict[str, list[AcademicRecord]] = defaultdict(list)
    by_period: dict[int, list[float]] = defaultdict(list)
    for record in records:
        by_subject[record.subject].append(record)
        if record.area is not None:
            by_area[record.area].append(record)
        if record.period_order is not None:
            by_period[record.period_order].append(normalize_score(record))

    subjects = {subject: _summary(by_subject[subject]) for subject in sorted(by_subject)}
    areas = {area: _summary(by_area[area]) for area in sorted(by_area)}
    overall_points = [(order, fmean(values)) for order, values in by_period.items()]
    overall_slope = linear_slope(overall_points)
    temporal = _temporal_coverage(records)

    attendance_ratio = None
    if attendance is not None:
        attendance_ratio = _rounded(attendance.attended_classes / attendance.total_classes)

    strengths: list[str] = []
    reinforce: list[str] = []
    best_subject, best_summary = max(subjects.items(), key=lambda item: (item[1].mean, item[0]))
    weak_subject, weak_summary = min(subjects.items(), key=lambda item: (item[1].mean, item[0]))
    strengths.append(f"Mayor promedio observado en {best_subject}: {best_summary.mean}/100.")
    if len(subjects) > 1 or weak_summary.mean < 70:
        reinforce.append(
            f"Menor promedio observado en {weak_subject}: {weak_summary.mean}/100."
        )

    warnings: list[str] = []
    if temporal.period_count < 2 or len(temporal.observed_orders) < 2:
        warnings.append(
            "No hay al menos dos períodos ordenados; la tendencia global permanece desconocida."
        )
    if not areas:
        warnings.append("No se informaron áreas curriculares; solo se agregaron asignaturas.")
    evidence_status = (
        EvidenceStatus.SUFFICIENT
        if len(temporal.observed_orders) >= 2
        else EvidenceStatus.PARTIAL
    )

    return AcademicProfile(
        evidence_status=evidence_status,
        count=len(scores),
        overall_mean=_rounded(fmean(scores)),
        minimum=min(scores),
        maximum=max(scores),
        standard_deviation=_rounded(pstdev(scores)) if len(scores) > 1 else None,
        consistency_ratio=consistency_ratio(scores),
        overall_trend_slope=overall_slope,
        overall_trend_label=trend_label(overall_slope),
        subjects=subjects,
        areas=areas,
        temporal_coverage=temporal,
        attendance_ratio=attendance_ratio,
        strengths=strengths,
        areas_to_reinforce=reinforce,
        warnings=warnings,
    )


def build_academic_evidence_profile_v2(
    academic: AcademicData | None,
) -> AcademicEvidenceProfileV2:
    if academic is None:
        return AcademicEvidenceProfileV2(
            status=AvailabilityStatus.UNAVAILABLE,
            summary=None,
            subjects={},
            areas={},
            periods={},
            temporal_period_count=0,
            temporal_coverage_ratio=None,
            best_observed_subject=None,
            best_observed_area=None,
            warnings=["No se recibió evidencia académica."],
        )

    records = academic.records
    by_subject: dict[str, list[AcademicRecord]] = defaultdict(list)
    by_area: dict[str, list[AcademicRecord]] = defaultdict(list)
    by_period: dict[str, list[AcademicRecord]] = defaultdict(list)
    for record in records:
        by_subject[record.subject].append(record)
        if record.area:
            by_area[record.area].append(record)
        period_label = record.period
        if period_label is None and record.period_order is not None:
            period_label = str(record.period_order)
        if period_label is not None:
            by_period[period_label].append(record)

    subjects = {
        label: descriptive_statistics(items)
        for label, items in sorted(by_subject.items())
    }
    areas = {
        label: descriptive_statistics(items) for label, items in sorted(by_area.items())
    }
    periods = {
        label: descriptive_statistics(items) for label, items in sorted(by_period.items())
    }
    temporal = _temporal_coverage(records)
    best_subject_label, best_subject = min(
        subjects.items(), key=lambda item: (-item[1].mean, item[0].casefold())
    )
    best_area_highlight: ObservedAcademicHighlight | None = None
    if areas:
        best_area_label, best_area = min(
            areas.items(), key=lambda item: (-item[1].mean, item[0].casefold())
        )
        best_area_highlight = ObservedAcademicHighlight(
            kind="BEST_OBSERVED_AREA",
            label=best_area_label,
            mean=best_area.mean,
            statement=(
                f"{best_area_label} es el área con mayor promedio observado entre los "
                "registros disponibles."
            ),
        )

    warnings: list[str] = []
    if temporal.period_count < 2 or len(temporal.observed_orders) < 2:
        warnings.append(
            "No hay dos períodos ordenados; la cobertura temporal es parcial o desconocida."
        )
    if not areas:
        warnings.append("No se informaron áreas curriculares.")
    status = (
        AvailabilityStatus.AVAILABLE
        if len(records) > 1 and len(temporal.observed_orders) >= 2
        else AvailabilityStatus.PARTIAL
    )
    return AcademicEvidenceProfileV2(
        status=status,
        summary=descriptive_statistics(records),
        subjects=subjects,
        areas=areas,
        periods=periods,
        temporal_period_count=temporal.period_count,
        temporal_coverage_ratio=temporal.ratio,
        best_observed_subject=ObservedAcademicHighlight(
            kind="BEST_OBSERVED_SUBJECT",
            label=best_subject_label,
            mean=best_subject.mean,
            statement=(
                f"{best_subject_label} es la asignatura con mayor promedio observado entre "
                "los registros disponibles."
            ),
        ),
        best_observed_area=best_area_highlight,
        warnings=warnings,
    )
