import hashlib
import json
from datetime import UTC, datetime
from uuid import uuid4

from app.contracts.evidence import AvailabilityStatus, Limitation, SourceReference
from app.contracts.responses import AnalysisStatus, EvidenceStatus
from app.contracts.v2 import (
    AnalysisV2Request,
    AnalysisV2Response,
    AttendanceEvidenceProfileV2,
    DeclaredInterestEvidenceProfileV2,
    HistoricalEvidenceProfileV2,
    LearningActivityEvidenceProfileV2,
    StudentAnalyticalSnapshotV2,
    TechnicalEvidenceProfileV2,
    TraceabilityV2,
    VocationalInterestEvidenceProfile,
)
from app.knowledge.registry import load_career_catalog
from app.learning_analytics.academic_performance import (
    build_academic_evidence_profile_v2,
    build_academic_profile,
)
from app.learning_analytics.activity import build_learning_activity_profile
from app.recommendation.bridge_v2 import BridgeLoadResult, load_default_bridge_v2
from app.recommendation.evidence_config import load_recommendation_v2_policy
from app.recommendation.evidence_engine import (
    build_career_evidence_profiles,
    build_evidence_quality,
)
from app.recommendation.occupational_crosswalk import (
    CrosswalkLoadResult,
    load_default_occupational_crosswalk,
)
from app.riasec.scoring import score_riasec

ENGINE_VERSION = "2.0.0"


def canonical_input_hash_v2(request: AnalysisV2Request) -> str:
    payload = request.model_dump(mode="json", exclude_none=False)
    canonical = json.dumps(payload, ensure_ascii=False, sort_keys=True, separators=(",", ":"))
    return hashlib.sha256(canonical.encode("utf-8")).hexdigest()


def _analysis_status(request: AnalysisV2Request) -> AnalysisStatus:
    if request.academic is not None and request.vocational is not None:
        return AnalysisStatus.COMPLETE
    if any(
        (
            request.academic is not None,
            request.vocational is not None,
            request.technical is not None,
            request.learning_activity is not None,
            bool(request.declared_interests),
            bool(request.history),
        )
    ):
        return AnalysisStatus.PARTIAL
    return AnalysisStatus.INSUFFICIENT


def _activity_status(status: EvidenceStatus) -> AvailabilityStatus:
    if status is EvidenceStatus.SUFFICIENT:
        return AvailabilityStatus.AVAILABLE
    if status is EvidenceStatus.PARTIAL:
        return AvailabilityStatus.PARTIAL
    return AvailabilityStatus.INSUFFICIENT


def _source_union(profiles_sources: list[list[SourceReference]]) -> list[SourceReference]:
    by_id: dict[str, SourceReference] = {}
    for sources in profiles_sources:
        for source in sources:
            current = by_id.get(source.source_id)
            if current is None or current.title is None:
                by_id[source.source_id] = source
    return [by_id[source_id] for source_id in sorted(by_id)]


def analyze_student_v2(
    request: AnalysisV2Request,
    trace_id: str | None = None,
    *,
    generated_at: datetime | None = None,
    bridge_result: BridgeLoadResult | None = None,
    crosswalk_result: CrosswalkLoadResult | None = None,
) -> AnalysisV2Response:
    policy = load_recommendation_v2_policy()
    catalog = load_career_catalog()
    bridge = bridge_result or load_default_bridge_v2()
    crosswalk = crosswalk_result or load_default_occupational_crosswalk()
    vocational = score_riasec(request.vocational) if request.vocational else None
    academic_for_matching = (
        build_academic_profile(request.academic, request.attendance)
        if request.academic
        else None
    )
    academic_v2 = build_academic_evidence_profile_v2(request.academic)
    activity = (
        build_learning_activity_profile(request.learning_activity)
        if request.learning_activity
        else None
    )
    recommendations = build_career_evidence_profiles(
        request,
        vocational,
        academic_for_matching,
        bridge,
        crosswalk,
    )
    evidence_quality = build_evidence_quality(request, academic_for_matching, vocational)

    technical_specialty = request.technical.specialty if request.technical else None
    technical_competencies = (
        sorted(item.name for item in request.technical.competencies)
        if request.technical
        else []
    )
    technical_evidence = (
        sorted(
            item.evidence
            for item in request.technical.competencies
            if item.evidence is not None
        )
        if request.technical
        else []
    )
    technical_present = technical_specialty is not None or bool(technical_competencies)
    historical_record_count = sum(len(period.records) for period in request.history)
    if not request.history:
        historical_status = AvailabilityStatus.UNAVAILABLE
    elif historical_record_count == 0:
        historical_status = AvailabilityStatus.INSUFFICIENT
    elif any(not period.records for period in request.history):
        historical_status = AvailabilityStatus.PARTIAL
    else:
        historical_status = AvailabilityStatus.AVAILABLE

    snapshot = StudentAnalyticalSnapshotV2(
        academic_evidence=academic_v2,
        vocational_interest_evidence=VocationalInterestEvidenceProfile(
            status=(
                AvailabilityStatus.AVAILABLE
                if vocational is not None
                else AvailabilityStatus.UNAVAILABLE
            ),
            riasec=vocational,
            limitations=[
                Limitation(
                    code="RIASEC_CONSTRUCT_SCOPE",
                    message=(
                        "RIASEC describe intereses vocacionales; no inteligencia, aptitud, "
                        "capacidad ni probabilidad de éxito."
                    ),
                    scope="vocational_interest_evidence",
                )
            ],
        ),
        technical_evidence=TechnicalEvidenceProfileV2(
            status=(
                AvailabilityStatus.AVAILABLE
                if technical_present
                else AvailabilityStatus.UNAVAILABLE
            ),
            specialty=technical_specialty,
            competencies=technical_competencies,
            evidence=technical_evidence,
        ),
        learning_activity_evidence=LearningActivityEvidenceProfileV2(
            status=(
                _activity_status(activity.evidence_status)
                if activity is not None
                else AvailabilityStatus.UNAVAILABLE
            ),
            metrics=activity,
        ),
        attendance_evidence=AttendanceEvidenceProfileV2(
            status=(
                AvailabilityStatus.AVAILABLE
                if request.attendance is not None
                else AvailabilityStatus.UNAVAILABLE
            ),
            attended_classes=(
                request.attendance.attended_classes if request.attendance else None
            ),
            total_classes=request.attendance.total_classes if request.attendance else None,
            attendance_ratio=(
                round(
                    request.attendance.attended_classes
                    / request.attendance.total_classes,
                    2,
                )
                if request.attendance
                else None
            ),
        ),
        declared_interest_evidence=DeclaredInterestEvidenceProfileV2(
            status=(
                AvailabilityStatus.AVAILABLE
                if request.declared_interests
                else AvailabilityStatus.UNAVAILABLE
            ),
            interests=request.declared_interests,
        ),
        historical_evidence=HistoricalEvidenceProfileV2(
            status=historical_status,
            periods=sorted(
                request.history,
                key=lambda item: (
                    item.period_order if item.period_order is not None else -1,
                    item.period,
                ),
            ),
            period_count=len(request.history),
            record_count=historical_record_count,
        ),
        evidence_quality=evidence_quality,
        missing_components=evidence_quality.missing_components,
    )

    actual_trace_id = trace_id or str(uuid4())
    timestamp = generated_at or datetime.now(UTC)
    bridge_version = bridge.bridge.bridge_version if bridge.bridge is not None else None
    warnings = [*recommendations.warnings]
    if vocational is None:
        warnings.append("instrument_version es null porque no se recibió RIASEC completo.")
    if bridge_version is None:
        warnings.append("bridge_version es null porque el bridge V2 no está disponible.")
    crosswalk_version = (
        crosswalk.crosswalk.crosswalk_version if crosswalk.crosswalk is not None else None
    )
    if crosswalk_version is None:
        warnings.append(
            "crosswalk_version es null porque el crosswalk ocupacional no está disponible."
        )
    limitations = [
        Limitation(
            code="DECISION_SUPPORT_ONLY",
            message="SAVP apoya la exploración y no decide una carrera.",
            scope="analysis_v2",
        ),
        Limitation(
            code="NO_SUCCESS_PROBABILITY",
            message="La salida no estima probabilidad de éxito universitario.",
            scope="analysis_v2",
        ),
        Limitation(
            code="LEGACY_BASELINE_SEPARATE",
            message=(
                "V1 permanece como LEGACY_EXPERIMENTAL_BASELINE y no fundamenta las "
                "conclusiones V2."
            ),
            scope="analysis_v2",
        ),
    ]
    sources_used = _source_union([profile.sources for profile in recommendations.profiles])
    return AnalysisV2Response(
        engine_version=ENGINE_VERSION,
        criteria_version=policy.criteria_version,
        trace_id=actual_trace_id,
        student_ref=request.student_id,
        analysis_status=_analysis_status(request),
        student_snapshot=snapshot,
        career_evidence_profiles=recommendations.profiles,
        traceability=TraceabilityV2(
            input_hash=canonical_input_hash_v2(request),
            engine_version=ENGINE_VERSION,
            criteria_version=policy.criteria_version,
            bridge_version=bridge_version,
            bridge_source_mode=bridge.source_mode,
            catalog_version=catalog.catalog_version,
            crosswalk_version=crosswalk_version,
            instrument_version=vocational.instrument_version if vocational else None,
            generated_at=timestamp,
            trace_id=actual_trace_id,
        ),
        warnings=warnings,
        limitations=limitations,
        sources_used=sources_used,
    )
