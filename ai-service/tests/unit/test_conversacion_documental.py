import json

import pytest

from app.contracts.requests import TutorConversationTurn, TutorQueryRequest
from app.ingestion.models import KnowledgeChunk
from app.knowledge.registry import SERVICE_ROOT
from app.retrieval.hybrid import KnowledgeHit
from app.tutor.consulta_documental import missing_scope, semester, semester_text
from app.tutor.intenciones_locales import predict, train
from app.tutor.service import (
    _filter_hits_for_named_entities,
    _retrieval_query,
    answer_structured,
    classify_tutor_intent,
)


def request(question, history=None):
    return TutorQueryRequest(
        schema_version="1.0", question=question, conversation_history=history or []
    )


def hit(number=2, institution="UNIFRANZ", title="Medicina", text="Anatomía y Fisiología"):
    return KnowledgeHit(
        score=0.9,
        lexical_score=0.9,
        lexical_rank=1,
        chunk=KnowledgeChunk(
            chunk_id=f"chunk-{number}-{institution}",
            source_id="FUENTE-OFICIAL",
            institution=institution,
            title=title,
            source_type="OFFICIAL_CURRICULUM_PDF",
            url="https://unifranz.edu.bo/malla.pdf",
            official=True,
            publication_date=None,
            retrieved_at="2026-10-11",
            valid_from=None,
            valid_until=None,
            document_hash="sha256:" + "a" * 64,
            section=f"Semestre {number}",
            page=number,
            version="1",
            status="VALID",
            extraction_method="DIGITAL",
            ordinal=1,
            text=text,
        ),
    )


@pytest.mark.parametrize(
    ("text", "expected"),
    [
        ("segundo semestre", 2),
        ("semestre 7", 7),
        ("3er semestre", 3),
        ("décimo semestre", 10),
        ("materias iniciales", None),
        ("semestre 99", None),
    ],
)
def test_semester_requires_supported_explicit_number(text, expected):
    assert semester(text) == expected


def test_semester_two_never_rewrites_query_or_evidence_as_semester_one():
    query = _retrieval_query(request("Materias del segundo semestre de Medicina UNIFRANZ"))
    assert "primer semestre" not in query and "semestre 2" in query
    rows = _filter_hits_for_named_entities(
        "Materias del segundo semestre de Medicina UNIFRANZ",
        [hit(1), hit(2), hit(2, "UCB"), hit(2, title="Derecho")],
    )
    assert len(rows) == 1 and rows[0].chunk.section == "Semestre 2"


def test_named_institution_never_falls_back_to_other_university():
    assert not _filter_hits_for_named_entities(
        "Materias Medicina UNIFRANZ semestre 2", [hit(2, "UCB")]
    )


def test_multisemester_chunk_extracts_only_requested_section_and_unlabeled_list_abstains():
    text = "Primer Semestre\nÁlgebra\nSegundo Semestre\nAnatomía\nTercer Semestre\nBioquímica"
    assert semester_text(None, text, 2) == "Segundo Semestre\nAnatomía"
    assert semester_text(None, "Materias iniciales: Anatomía", 1) is None


def test_missing_scope_is_explained_before_retrieval_gap():
    assert missing_scope("¿Qué materias estudiaré?") == ["universidad", "carrera"]
    answer, material = answer_structured(request("¿Qué materias estudiaré?"), None)
    assert "universidad, carrera" in answer.answer
    assert not material.evidence


def test_followup_keeps_current_semester_and_only_inherits_missing_entities():
    class Retriever:
        def search(self, *args, **kwargs):
            return [hit(1), hit(2)]

    history = [TutorConversationTurn(role="user", content="Materias Medicina UNIFRANZ semestre 1")]
    answer, material = answer_structured(request("¿Y el segundo semestre?", history), Retriever())
    assert material.clarification is None
    assert len(material.evidence) == 1 and material.evidence[0].section == "Semestre 2"
    assert "Anatomía" in answer.answer


def test_capabilities_explain_memory_without_claiming_persistent_history():
    answer, material = answer_structured(request("¿Tienes memoria permanente?"), None)
    assert material.intent == "CAPABILITIES"
    assert "No tengo memoria persistente" in answer.answer


def test_first_semester_ucb_systems_uses_only_requested_document_with_citation():
    class Retriever:
        def search(self, *args, **kwargs):
            return [
                hit(1, "UCB", "Ingeniería de Sistemas", "Álgebra Lineal y Programación I"),
                hit(2, "UCB", "Ingeniería de Sistemas", "Cálculo II"),
                hit(1, "UNIFRANZ", "Ingeniería de Sistemas", "Otra malla"),
            ]

    answer, material = answer_structured(
        request("Materias del primer semestre de Sistemas en UCB"), Retriever()
    )
    assert material.clarification is None
    assert len(material.evidence) == 1
    assert material.evidence[0].institution == "UCB"
    assert "Álgebra Lineal" in answer.answer
    assert "FUENTE-OFICIAL" in answer.answer and "p. 1" in answer.answer
    assert "Cálculo II" not in answer.answer and "Otra malla" not in answer.answer


def test_university_missing_asks_for_university_without_attributing_any_subject():
    answer, material = answer_structured(request("Materias Sistemas primer semestre"), None)
    assert "necesito: universidad" in answer.answer
    assert not material.evidence


def test_greeting_retains_administrative_identity_and_mixed_question_is_not_hidden():
    answer, material = answer_structured(request("Hola"), None)
    assert "asistente documental de SAVP" in answer.answer
    assert material.intent == "SOCIAL"
    answer, material = answer_structured(
        request("Hola, materias Sistemas segundo semestre UCB"), None
    )
    assert material.intent == "EVIDENCE_GROUNDED"
    assert "No encontré materias de ese semestre" in answer.answer


def test_training_and_independent_evaluation_are_disjoint_and_model_is_local():
    root = SERVICE_ROOT / "data/tutor"
    training = json.loads((root / "intenciones_entrenamiento.json").read_text(encoding="utf8"))[
        "examples"
    ]
    evaluation = json.loads((root / "intenciones_evaluacion.json").read_text(encoding="utf8"))[
        "examples"
    ]
    assert not {row["text"].casefold() for row in training} & {
        row["text"].casefold() for row in evaluation
    }
    model = train(training)
    assert model["algorithm"] == "multinomial_naive_bayes" and model["examples"] == len(training)
    assert predict("Materias primer semestre Medicina", model)[0] == "EVIDENCE_GROUNDED"
    assert classify_tutor_intent("Hola, materias Medicina semestre 2") == "EVIDENCE_GROUNDED"
