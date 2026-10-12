"""Regresión con el corpus local real; prohíbe conexiones y no sustituye el recuperador."""

import socket

import pytest

from app.contracts.requests import TutorQueryRequest
from app.retrieval.service import get_retriever
from app.tutor.service import answer_structured


@pytest.fixture
def offline_retriever(monkeypatch):
    def network_forbidden(*args, **kwargs):
        pytest.fail("La consulta documental del corpus local no debe abrir conexiones")

    monkeypatch.setattr(socket, "getaddrinfo", network_forbidden)
    monkeypatch.setattr(socket, "create_connection", network_forbidden)
    return get_retriever()


def test_real_ucb_first_semester_returns_five_published_subjects_with_page(offline_retriever):
    answer, material = answer_structured(
        TutorQueryRequest(
            schema_version="1.0",
            question="¿Qué materias tiene el primer semestre de Ingeniería de Sistemas en UCB?",
        ),
        offline_retriever,
    )
    assert not material.insufficient_evidence
    assert len(material.evidence) == 1
    source = material.evidence[0]
    assert source.source_id == "BO-UCB-LP-SIS-MALLA-2026" and source.page == 1
    for subject in (
        "Álgebra Lineal",
        "Matemáticas Discretas",
        "Introducción a la Programación",
        "Ingeniería de Sistemas",
        "Pensamiento Crítico",
    ):
        assert subject in answer.answer
    assert source.reference.endswith("#page=1")
    assert "Cálculo I" not in answer.answer


def test_real_ucb_second_semester_never_lists_first_semester_subjects(offline_retriever):
    answer, material = answer_structured(
        TutorQueryRequest(
            schema_version="1.0",
            question="Materias del segundo semestre de Sistemas en UCB",
        ),
        offline_retriever,
    )
    assert not material.insufficient_evidence
    assert "Cálculo I" in answer.answer and "Programación I" in answer.answer
    assert "Pensamiento Crítico" not in answer.answer and "Álgebra Lineal" not in answer.answer
    assert "Semestre 2" in answer.answer


def test_real_unknown_semester_uses_friendly_gap_without_other_semesters(offline_retriever):
    answer, material = answer_structured(
        TutorQueryRequest(
            schema_version="1.0",
            question="Materias del duodécimo semestre de Sistemas en UCB",
        ),
        offline_retriever,
    )
    assert material.insufficient_evidence and not material.evidence
    assert "No encontré materias de ese semestre respaldadas" in answer.answer
    assert "corpus" not in answer.answer and "tutor" not in answer.answer
    assert "Álgebra Lineal" not in answer.answer


def test_unifranz_career_list_uses_official_index_without_other_sources(offline_retriever):
    answer, material = answer_structured(
        TutorQueryRequest(
            schema_version="1.0",
            question=(
                "Hola quiero saber respecto a la universidad privada franz tamayo, "
                "que es lo que tienes, que carreras contiene, solo dime las carreras"
            ),
        ),
        offline_retriever,
    )
    assert not material.insufficient_evidence
    assert [item.source_id for item in material.evidence] == ["BO-UNIFRANZ-OFFER-INDEX"]
    assert "17 carreras" in answer.answer
    assert "Ingeniería de Sistemas e Innovación Digital" in answer.answer
    assert "Psicología" in answer.answer
    assert "Medicina" in answer.answer
    assert "UPB" not in answer.answer and "Ministerio" not in answer.answer
    assert "UNIVERSIDAD FRANZ TAMAYO UNIFRANZ INNOVACIÓN" not in answer.answer
    assert all("unifranz.edu.bo" in item.reference for item in material.evidence)


def test_unifranz_systems_without_semester_lists_visual_verified_malla_and_branches(
    offline_retriever,
):
    answer, material = answer_structured(
        TutorQueryRequest(
            schema_version="1.0",
            question="materias de unifranz en ingenieria de sistemas",
        ),
        offline_retriever,
    )
    assert not material.insufficient_evidence
    assert [item.source_id for item in material.evidence] == [
        "BO-UNIFRANZ-LP-SISID-MALLA-2026",
        "BO-UNIFRANZ-LP-SISID-MALLA-2026",
    ]
    assert [item.page for item in material.evidence] == [3, 4]
    assert "Semestre 1 (página 3)" in answer.answer
    assert "Fundamentos de Bases de Datos" in answer.answer
    assert "Semestre 9 (página 4)" in answer.answer
    assert "Materias según la mención que elijas" in answer.answer
    assert "las materias de ambas ramas no son todas obligatorias" in answer.answer
    assert "Medicina" not in answer.answer
    assert "necesito: semestre" not in answer.answer


def test_unifranz_systems_specific_semester_uses_visual_transcription_not_corrupt_ocr(
    offline_retriever,
):
    answer, material = answer_structured(
        TutorQueryRequest(
            schema_version="1.0",
            question="materias del primer semestre de sistemas en unifranz",
        ),
        offline_retriever,
    )
    assert not material.insufficient_evidence
    assert [item.source_id for item in material.evidence] == [
        "BO-UNIFRANZ-LP-SISID-MALLA-2026"
    ]
    assert "Fundamentos de Bases de Datos" in answer.answer
    assert "Programación Fundamental" in answer.answer
    assert "Gestión de Bases de Datos" not in answer.answer
    assert "UPB" not in answer.answer and "Medicina" not in answer.answer


def test_unifranz_eighth_semester_separates_common_and_elective_mentions(offline_retriever):
    answer, material = answer_structured(
        TutorQueryRequest(
            schema_version="1.0",
            question="materias del octavo semestre de sistemas en unifranz",
        ),
        offline_retriever,
    )
    assert [item.page for item in material.evidence] == [4]
    assert "Materias comunes:\n- Auditoría Informática" in answer.answer
    assert "Inteligencia Artificial y Ciencia de Datos:" in answer.answer
    assert "Ciberseguridad y Blockchain:" in answer.answer
    assert "las materias de ambas ramas no son todas obligatorias" in answer.answer
    assert "#page=4" in material.evidence[0].reference
