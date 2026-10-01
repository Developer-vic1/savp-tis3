import hashlib
import json
from datetime import UTC, datetime
from uuid import uuid4

from app.contracts.requests import AnalysisRequest
from app.contracts.responses import AnalysisResponse, AnalysisStatus, Coverage
from app.learning_analytics.academic_performance import build_academic_profile
from app.learning_analytics.activity import build_learning_activity_profile
from app.recommendation.engine import build_career_ranking
from app.riasec.scoring import score_riasec


def canonical_input_hash(request: AnalysisRequest) -> str:
    payload = request.model_dump(mode="json", exclude_none=False)
    canonical = json.dumps(payload, ensure_ascii=False, sort_keys=True, separators=(",", ":"))
    return hashlib.sha256(canonical.encode("utf-8")).hexdigest()


def _status(has_academic: bool, has_vocational: bool) -> AnalysisStatus:
    if has_academic and has_vocational:
        return AnalysisStatus.COMPLETE
    if has_academic or has_vocational:
        return AnalysisStatus.PARTIAL
    return AnalysisStatus.INSUFFICIENT


def analyze_student(request: AnalysisRequest, trace_id: str | None = None) -> AnalysisResponse:
    has_academic = request.academic is not None
    has_vocational = request.vocational is not None
    components = {
        "academic": has_academic,
        "attendance": request.attendance is not None,
        "vocational": has_vocational,
        "technical": request.technical is not None,
        "learning_activity": request.learning_activity is not None,
    }
    status = _status(has_academic, has_vocational)
    missing = [name for name, present in components.items() if not present]

    vocational_profile = score_riasec(request.vocational) if request.vocational else None
    academic_profile = (
        build_academic_profile(request.academic, request.attendance) if request.academic else None
    )
    learning_activity_profile = (
        build_learning_activity_profile(request.learning_activity)
        if request.learning_activity
        else None
    )
    career_ranking, ranking_warnings = build_career_ranking(
        request,
        vocational_profile,
        academic_profile,
    )

    strengths: list[str] = []
    areas_to_reinforce: list[str] = []
    warnings: list[str] = []
    if vocational_profile:
        strengths.append(
            "Intereses destacados: " + ", ".join(vocational_profile.top_codes) + "."
        )
        if vocational_profile.top_tie:
            warnings.append(
                "Existe empate en el puntaje RIASEC superior; se conservan todas las "
                "dimensiones empatadas."
            )
    else:
        warnings.append("No se recibió evidencia vocacional; no se calculó un perfil RIASEC.")

    if academic_profile:
        strengths.extend(academic_profile.strengths)
        areas_to_reinforce.extend(academic_profile.areas_to_reinforce)
        warnings.extend(academic_profile.warnings)
    else:
        warnings.append(
            "No se recibió evidencia académica; la preparación actual permanece desconocida."
        )

    if status is AnalysisStatus.INSUFFICIENT:
        warnings.append(
            "La evidencia disponible es insuficiente para un perfil académico-vocacional."
        )

    sources_used: list[dict[str, str | bool]] = []
    if vocational_profile:
        sources_used.append({
            "source_id": "ONET-MINI-IP-2.0-ES",
            "title": "O*NET Interest Profiler (Mini-IP Version 2.0)",
            "institution": "U.S. Department of Labor, Employment and Training Administration",
            "reference": "https://onetinterestprofiler.org/es/",
            "official": True,
        })

    return AnalysisResponse(
        status=status,
        trace_id=trace_id or str(uuid4()),
        student_ref=request.student_id,
        academic_period=request.academic_period,
        generated_at=datetime.now(UTC),
        instrument_version=vocational_profile.instrument_version if vocational_profile else None,
        input_hash=canonical_input_hash(request),
        coverage=Coverage(
            ratio=round(sum(components.values()) / len(components), 2),
            components=components,
        ),
        missing_components=missing,
        data_quality_status=status,
        vocational_profile=vocational_profile,
        academic_profile=academic_profile,
        learning_activity_profile=learning_activity_profile,
        ranking_status="RANKED" if career_ranking else "INSUFFICIENT_EVIDENCE",
        career_ranking=career_ranking,
        ranking_warnings=ranking_warnings,
        strengths=strengths,
        areas_to_reinforce=areas_to_reinforce,
        warnings=warnings,
        sources_used=sources_used,
    )
