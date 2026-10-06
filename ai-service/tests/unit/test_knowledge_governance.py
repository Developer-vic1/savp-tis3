from pathlib import Path

import pytest

from app.knowledge import governance
from app.knowledge.governance import (
    SourceCandidateRequest,
    SourceProposalRequest,
    SourceProposalReviewRequest,
    analyze_source_candidate,
    assess_bolivian_university_url,
    review_source_proposal,
    submit_source_proposal,
)


def _candidate(**overrides: object) -> dict[str, object]:
    return {
        "title": "Plan de estudios de Ingeniería de Sistemas",
        "declared_institution": "Universidad Privada Boliviana",
        "url": "https://nueva.upb.edu/documentos/plan-sistemas-2027.pdf",
        "source_type": "OFFICIAL_CURRICULUM_PDF",
        "scope": "Malla curricular y materias de Ingeniería de Sistemas.",
        "publication_date": "2027",
        "version": "Gestión 2027",
        "campus": "La Paz",
        "city": "La Paz",
        "justification": "Permite comparar materias iniciales y necesidades de preparación.",
        "limitations": ["No informa costos de matrícula."],
    } | overrides


def test_known_bolivian_university_domain_is_accepted() -> None:
    assessment = assess_bolivian_university_url(
        "https://www.upb.edu/carreras/ingenieria-de-sistemas"
    )

    assert assessment.status == "ACEPTADA"
    assert assessment.is_bolivian_university is True
    assert assessment.recognized_institution == "Universidad Privada Boliviana"


@pytest.mark.parametrize(
    "url",
    [
        "https://www.upb.edu:invalid/plan.pdf",
        "https://[::1/plan.pdf",
        "https://www.upb.edu/\nplan.pdf",
        "https://www.upb.edu/%0d%0aHost:localhost",
        "https://www.upb.edu\\@evil.bo/plan.pdf",
        "https://fake..upb.edu/plan.pdf",
        "https://user:pass@www.upb.edu/plan.pdf",
        "https://127.0.0.1/plan.pdf",
        "https://www.upb.edu:8080/plan.pdf",
    ],
)
def test_unsafe_urls_never_raise_or_pass_analysis(url: str) -> None:
    analysis = analyze_source_candidate(SourceCandidateRequest.model_validate(_candidate(url=url)))
    assert analysis.can_submit is False
    assert analysis.readiness == "BLOQUEADA"


def test_unknown_domain_cannot_be_approved_and_rejection_is_final(
    monkeypatch, tmp_path: Path
) -> None:
    monkeypatch.setattr(governance, "SOURCE_INTAKE_REGISTRY", tmp_path / "source_intake.json")
    submission = submit_source_proposal(
        SourceProposalRequest(
            **_candidate(url="https://sin-verificar.edu.bo/plan.pdf"), submitted_by_role="Director"
        )
    )
    assert submission.proposal is not None
    assert submission.proposal.status == "PENDIENTE_VERIFICACION_DE_DOMINIO"
    with pytest.raises(ValueError, match="dominio"):
        review_source_proposal(
            submission.proposal.proposal_id,
            SourceProposalReviewRequest(
                approved=True,
                review_note="Intenta aprobar sin verificar.",
                reviewed_by_role="Administrador",
            ),
        )
    rejection = review_source_proposal(
        submission.proposal.proposal_id,
        SourceProposalReviewRequest(
            approved=False,
            review_note="No se puede acreditar el dominio.",
            reviewed_by_role="Administrador",
        ),
    )
    assert rejection.status == "RECHAZADA"
    with pytest.raises(ValueError, match="revisada"):
        review_source_proposal(
            submission.proposal.proposal_id,
            SourceProposalReviewRequest(
                approved=True,
                review_note="Intenta modificar la decisión.",
                reviewed_by_role="Administrador",
            ),
        )


def test_unknown_bolivian_domain_requires_manual_review() -> None:
    assessment = assess_bolivian_university_url("https://portal.universidad-ejemplo.bo/sistemas")

    assert assessment.status == "REQUIERE_REVISION_DE_DOMINIO"
    assert assessment.is_bolivian_university is False


def test_foreign_university_domain_is_rejected() -> None:
    assessment = assess_bolivian_university_url(
        "https://www.berkeley.edu/academics/computer-science"
    )

    assert assessment.status == "RECHAZADA"
    assert assessment.is_bolivian_university is False
    assert "California" in assessment.message


def test_trusted_domain_cannot_be_spoofed_by_suffix_or_credentials() -> None:
    assert (
        assess_bolivian_university_url("https://www.upb.edu.evil.example/plan.pdf").status
        == "RECHAZADA"
    )
    assert (
        assess_bolivian_university_url("https://www.upb.edu@evil.example/plan.pdf").status
        == "RECHAZADA"
    )


def test_candidate_analysis_detects_institution_mismatch() -> None:
    analysis = analyze_source_candidate(
        SourceCandidateRequest.model_validate(
            _candidate(declared_institution="Universidad de California")
        )
    )

    assert analysis.can_submit is False
    assert analysis.readiness == "BLOQUEADA"
    assert any(
        check.code == "INSTITUTION_MATCH" and check.status == "BLOQUEA" for check in analysis.checks
    )


def test_institution_alias_must_match_exactly() -> None:
    analysis = analyze_source_candidate(
        SourceCandidateRequest.model_validate(
            _candidate(declared_institution="Universidad falsa asociada a UPB")
        )
    )

    assert analysis.can_submit is False
    assert analysis.readiness == "BLOQUEADA"


def test_invalid_calendar_date_is_blocked() -> None:
    analysis = analyze_source_candidate(
        SourceCandidateRequest.model_validate(_candidate(publication_date="2026-99-99"))
    )

    assert analysis.can_submit is False
    assert any(
        check.code == "PUBLICATION_DATE" and check.status == "BLOQUEA" for check in analysis.checks
    )


def test_accepted_source_is_staged_then_requires_administrator_approval(
    monkeypatch, tmp_path: Path
) -> None:
    monkeypatch.setattr(governance, "SOURCE_INTAKE_REGISTRY", tmp_path / "source_intake.json")
    submission = submit_source_proposal(
        SourceProposalRequest(**_candidate(), submitted_by_role="Director")
    )

    assert submission.accepted is True
    assert submission.proposal is not None
    assert submission.proposal.status == "PENDIENTE_REVISION"
    assert submission.proposal.analysis_checks

    proposal = review_source_proposal(
        submission.proposal.proposal_id,
        SourceProposalReviewRequest(
            approved=True,
            review_note="Dominio y finalidad curricular verificados.",
            reviewed_by_role="Administrador",
        ),
    )

    assert proposal.status == "APROBADA_PENDIENTE_INGESTA"
    assert (tmp_path / "source_intake.json").is_file()


def test_duplicate_url_cannot_bypass_detection_with_fragment(monkeypatch, tmp_path: Path) -> None:
    monkeypatch.setattr(governance, "SOURCE_INTAKE_REGISTRY", tmp_path / "source_intake.json")
    first = submit_source_proposal(
        SourceProposalRequest(**_candidate(), submitted_by_role="Director")
    )
    second = submit_source_proposal(
        SourceProposalRequest(
            **_candidate(url=f"{_candidate()['url']}#otra-seccion"),
            submitted_by_role="Director",
        )
    )

    assert first.accepted is True
    assert second.accepted is False
    assert second.proposal is None
