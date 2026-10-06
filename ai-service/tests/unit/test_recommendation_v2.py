import json
from pathlib import Path

from app.contracts.requests import AcademicRecord, AnalysisRequest
from app.learning_analytics.academic_performance import build_academic_profile
from app.recommendation.bridge_v2 import (
    BridgeLoadResult,
    BridgeRelationStatus,
    BridgeV2,
    BridgeV2Relation,
)
from app.recommendation.config import load_bridge
from app.recommendation.evidence_engine import (
    _academic_match_basis,
    build_career_evidence_profiles,
)
from app.recommendation.occupational_crosswalk import CrosswalkLoadResult
from app.riasec.scoring import score_riasec

FIXTURES = Path(__file__).resolve().parents[2] / "data" / "fixtures"


def _request(name: str) -> AnalysisRequest:
    payload = json.loads((FIXTURES / name).read_text(encoding="utf-8"))
    for metadata in ("fixture_type", "seed", "scenario_note"):
        payload.pop(metadata, None)
    return AnalysisRequest.model_validate(payload)


def test_v2_uses_record_area_independently_from_subject() -> None:
    relation = next(
        item for item in load_bridge().relations if item.relation_id == "BR-MATH-SIS-ALGEBRA"
    )
    record = AcademicRecord(
        subject="Asignatura Integrada",
        area="Matemática de Educación Secundaria Comunitaria Productiva",
        score=80,
    )
    assert _academic_match_basis(record, relation) == "AREA_LABEL_CONTAINMENT"


def test_v2_does_not_create_composite_scores_or_numeric_requirements() -> None:
    request = _request("complete_profile.json")
    vocational = score_riasec(request.vocational) if request.vocational else None
    academic = (
        build_academic_profile(request.academic, request.attendance) if request.academic else None
    )
    result = build_career_evidence_profiles(request, vocational, academic)
    serialized = json.dumps(result.model_dump(mode="json"), ensure_ascii=False)
    for forbidden in (
        "affinity_score",
        "preparation_score",
        "compatibility_score",
        "compatibility_weights",
        '"required"',
        '"magnitude"',
    ):
        assert forbidden not in serialized


def test_v2_exposes_traceable_academic_program_without_claiming_full_curriculum() -> None:
    request = _request("complete_profile.json")
    vocational = score_riasec(request.vocational) if request.vocational else None
    academic = (
        build_academic_profile(request.academic, request.attendance) if request.academic else None
    )
    result = build_career_evidence_profiles(request, vocational, academic)
    systems = next(
        profile for profile in result.profiles if profile.career_id == "BO-UCB-LP-ING-SISTEMAS"
    )

    assert systems.academic_program.duration == "9 semestres"
    assert systems.academic_program.professional_profile
    assert systems.academic_program.knowledge_areas
    assert "Introducción a la Programación" in systems.academic_program.documented_subjects
    assert systems.academic_program.curriculum_scope == "DOCUMENTED_INITIAL_SUBJECTS"
    assert systems.academic_program.curriculum_status == "PARTIAL"
    assert systems.academic_program.sources
    assert all(source.reference for source in systems.academic_program.sources)


def test_v2_missing_academic_evidence_remains_absent_not_zero() -> None:
    request = _request("insufficient_profile.json")
    vocational = score_riasec(request.vocational) if request.vocational else None
    academic = (
        build_academic_profile(request.academic, request.attendance) if request.academic else None
    )
    result = build_career_evidence_profiles(request, vocational, academic)
    assert result.profiles
    for profile in result.profiles:
        assert profile.preparation.relations_with_observed_academic_evidence == 0
        assert profile.preparation.academic_evidence == []


def test_v2_uses_documented_occupational_crosswalk_without_composite_score() -> None:
    request = _request("complete_profile.json")
    vocational = score_riasec(request.vocational) if request.vocational else None
    assert request.academic is not None
    academic = build_academic_profile(request.academic, request.attendance)
    result = build_career_evidence_profiles(request, vocational, academic)
    assert vocational is not None
    assert all(
        profile.student_riasec_code == vocational.holland_code for profile in result.profiles
    )
    by_career = {profile.career_id: profile for profile in result.profiles}
    new_without_crosswalk = {
        "BO-UNIFRANZ-LP-ING-SISTEMAS-INNOVACION-DIGITAL",
        "BO-UPB-LP-ING-SISTEMAS-COMPUTACIONALES",
    }
    for career_id, profile in by_career.items():
        if career_id in new_without_crosswalk:
            assert profile.riasec_reference_status == "UNAVAILABLE_PENDING_OCCUPATIONAL_CROSSWALK"
            assert profile.vocational_interest_relation.evidence == []
        else:
            assert profile.riasec_reference_status == "AVAILABLE_DOCUMENTED_OCCUPATIONAL_CROSSWALK"
            assert profile.vocational_interest_relation.evidence


def test_v2_without_crosswalk_marks_riasec_career_relation_unavailable() -> None:
    request = _request("complete_profile.json")
    vocational = score_riasec(request.vocational) if request.vocational else None
    assert request.academic is not None
    academic = build_academic_profile(request.academic, request.attendance)
    result = build_career_evidence_profiles(
        request,
        vocational,
        academic,
        crosswalk_result=CrosswalkLoadResult(
            crosswalk=None,
            warnings=["SYNTHETIC_TEST_FIXTURE: crosswalk ausente"],
        ),
    )
    assert all(
        profile.riasec_reference_status == "UNAVAILABLE_PENDING_OCCUPATIONAL_CROSSWALK"
        for profile in result.profiles
    )
    assert all(
        profile.vocational_interest_relation.status == "UNAVAILABLE" for profile in result.profiles
    )


def test_v2_every_bridge_based_evidence_is_traceable() -> None:
    request = _request("complete_profile.json")
    vocational = score_riasec(request.vocational) if request.vocational else None
    assert request.academic is not None
    academic = build_academic_profile(request.academic, request.attendance)
    result = build_career_evidence_profiles(request, vocational, academic)
    for profile in result.profiles:
        for academic_item in profile.preparation.academic_evidence:
            assert academic_item.relation_id
            assert academic_item.source_secondary
            assert academic_item.source_university
            assert academic_item.evidence_label
        for technical_item in profile.technical_evidence:
            assert technical_item.relation_id
            assert technical_item.source_secondary
            assert technical_item.source_university


def test_v2_insufficient_bridge_relation_never_becomes_strong_evidence() -> None:
    request = _request("complete_profile.json")
    assert request.academic is not None
    bridge = BridgeV2(
        bridge_version="synthetic-test-bridge",
        status="SYNTHETIC_TEST_FIXTURE",
        governance_principles=["MISSING_IS_NOT_ZERO"],
        allowed_statuses=list(BridgeRelationStatus),
        relations=[
            BridgeV2Relation(
                relation_id="SYN-INSUFFICIENT-1",
                secondary_content="BTH Sistemas Informáticos",
                secondary_competency="programación",
                competency="programación",
                university_knowledge="programación inicial",
                initial_subject="Introducción a la Programación",
                career_ids=["BO-UCB-LP-ING-SISTEMAS"],
                source_secondary="BO-ME-BTH-RM-0244-2023",
                source_university="BO-UCB-LP-SIS-MALLA-2026",
                relation_status="INSUFFICIENT_EVIDENCE",
                evidence_label="EVIDENCIA INSUFICIENTE",
                justification="Fixture controlado para validar propagación de estado.",
                limitations=["No usar para una conclusión fuerte."],
                version="test-1",
            )
        ],
    )
    result = build_career_evidence_profiles(
        request,
        score_riasec(request.vocational) if request.vocational else None,
        build_academic_profile(request.academic, request.attendance),
        BridgeLoadResult(
            bridge=bridge,
            source_mode="V2_DOCUMENT",
            warnings=[],
        ),
    )
    systems = next(
        profile for profile in result.profiles if profile.career_id == "BO-UCB-LP-ING-SISTEMAS"
    )
    assert systems.technical_relation.status == "INSUFFICIENT"
    assert all(
        item.relation_status == "INSUFFICIENT_EVIDENCE" for item in systems.technical_evidence
    )
