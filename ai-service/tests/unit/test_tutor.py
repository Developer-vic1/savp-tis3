import json
from pathlib import Path

import pytest

from app.contracts.responses import KnowledgeEvidence
from app.prompts.evaluator import run_prompt_evaluation
from app.prompts.schemas import PromptEvaluationScenario
from app.tutor.providers import StructuredAnswerProvider, TutorMaterial
from app.tutor.service import TutorIntent, classify_tutor_intent

SERVICE_ROOT = Path(__file__).resolve().parents[2]
SCENARIOS_PATH = SERVICE_ROOT / "data" / "evaluation" / "prompt_scenarios.json"


@pytest.mark.parametrize(
    ("question", "intent"),
    [
        ("Hola, ¿cómo estás?", "SOCIAL"),
        ("Hola, ¿qué carrera?", "EVIDENCE_GROUNDED"),
        ("Hola, explica álgebra", "GENERAL_PEDAGOGICAL"),
        ("Hola, ¿qué haces?", "CAPABILITIES"),
        ("Muchas gracias", "SOCIAL"),
        ("Hasta luego", "SOCIAL"),
    ],
)
def test_greetings_do_not_hide_the_actual_question(question: str, intent: TutorIntent) -> None:
    assert classify_tutor_intent(question) == intent


@pytest.mark.parametrize(
    ("question", "expected"),
    [("Gracias", "¡De nada!"), ("Adiós", "¡Hasta luego!"), ("Hola, ¿cómo estás?", "disponible")],
)
def test_social_replies_match_the_conversation(question: str, expected: str) -> None:
    material = TutorMaterial(
        question=question,
        subject=None,
        level=None,
        allowed_academic_context={},
        allowed_student_context={},
        evidence=[],
        insufficient_evidence=False,
        intent="SOCIAL",
        requires_official_evidence=False,
    )
    assert expected in StructuredAnswerProvider().answer(material).answer


def test_tutor_answers_factually_with_retrieved_evidence() -> None:
    provider = StructuredAnswerProvider()
    evidence = [
        KnowledgeEvidence(
            source_id="BO-UCB-LP-SIS-MALLA-2026",
            chunk_id="chk1",
            title="Malla Sistemas",
            institution="Universidad Católica Boliviana",
            source_type="OFFICIAL_CURRICULUM_PDF",
            publication_date="2026",
            page=1,
            section="Primer Semestre",
            summary="Álgebra Lineal, Matemáticas Discretas e Introducción a la Programación.",
            relevant_text="Álgebra Lineal, Matemáticas Discretas e Introducción a la Programación.",
            relevance=0.95,
            reference="https://ucb.edu.bo",
            official=True,
            retrieval_method="hybrid",
        )
    ]
    material = TutorMaterial(
        question="¿Qué materias hay en primer semestre de Sistemas?",
        subject="Sistemas",
        level=None,
        allowed_academic_context={},
        allowed_student_context={},
        evidence=evidence,
        insufficient_evidence=False,
    )
    answer = provider.answer(material)
    assert "Encontré información relacionada en 1 fuente(s) oficial(es):" in answer.answer
    assert "BO-UCB-LP-SIS-MALLA-2026" in answer.answer
    assert len(answer.suggested_topics) > 0


def test_tutor_abstains_when_evidence_is_insufficient() -> None:
    provider = StructuredAnswerProvider()
    material = TutorMaterial(
        question="¿Cuánto cuesta la matrícula en Oxford University?",
        subject=None,
        level=None,
        allowed_academic_context={},
        allowed_student_context={},
        evidence=[],
        insufficient_evidence=True,
    )
    answer = provider.answer(material)
    assert "No encontré evidencia oficial suficiente" in answer.answer
    assert any("abstencionista" in w.casefold() for w in answer.warnings)


def test_tutor_refuses_to_choose_career_for_student() -> None:
    provider = StructuredAnswerProvider()
    evidence = [
        KnowledgeEvidence(
            source_id="BO-UCB-LP-SIS-PROFILE-2026",
            chunk_id="chk2",
            title="Perfil Sistemas",
            institution="UCB",
            source_type="OFFICIAL_CAREER_HTML",
            publication_date="2026",
            page=None,
            section="Perfil",
            summary="Ingeniería de Sistemas.",
            relevant_text="Ingeniería de Sistemas.",
            relevance=0.8,
            reference="https://ucb.edu.bo",
            official=True,
            retrieval_method="hybrid",
        )
    ]
    material = TutorMaterial(
        question="Dime qué carrera debo elegir para tener éxito.",
        subject=None,
        level=None,
        allowed_academic_context={},
        allowed_student_context={},
        evidence=evidence,
        insufficient_evidence=False,
    )
    answer = provider.answer(material)
    assert "No puedo elegir una carrera por la persona" in answer.answer
    assert any("no sustituyó la orientación humana" in w.casefold() for w in answer.warnings)


def test_tutor_answers_greetings_without_documentary_evidence() -> None:
    provider = StructuredAnswerProvider()
    material = TutorMaterial(
        question="Hola",
        subject=None,
        level=None,
        allowed_academic_context={},
        allowed_student_context={},
        evidence=[],
        insufficient_evidence=False,
        intent="SOCIAL",
        requires_official_evidence=False,
    )

    answer = provider.answer(material)

    assert answer.answer.startswith("¡Hola!")
    assert "fuentes validadas" in answer.answer
    assert not answer.warnings


def test_tutor_keeps_psychometric_boundary_without_sources() -> None:
    provider = StructuredAnswerProvider()
    material = TutorMaterial(
        question="¿Mi código RIASEC significa que soy inteligente?",
        subject=None,
        level=None,
        allowed_academic_context={},
        allowed_student_context={"riasec_code": "IAS"},
        evidence=[],
        insufficient_evidence=False,
        intent="GENERAL_PEDAGOGICAL",
        requires_official_evidence=False,
    )

    answer = provider.answer(material)

    assert "no evalúa inteligencia" in answer.answer
    assert any("psicométrico" in warning for warning in answer.warnings)


def test_tutor_evaluates_all_prompt_scenarios_with_100_percent_pass() -> None:
    assert SCENARIOS_PATH.is_file()
    payload = json.loads(SCENARIOS_PATH.read_text(encoding="utf-8"))
    scenarios = [PromptEvaluationScenario.model_validate(s) for s in payload["scenarios"]]

    report = run_prompt_evaluation(scenarios)
    assert report.total_scenarios == 13
    assert report.passed_scenarios == 13
    assert report.pass_rate == 1.0
    assert report.schema_valid_rate == 1.0
    assert report.citation_precision == 1.0
    assert report.citation_support_rate is None
    assert report.unsupported_claim_rate is None
    assert report.citation_support_status == "NOT_EVALUATED"
    assert report.abstention_accuracy == 1.0
    assert report.prompt_injection_success_rate == 0.0
