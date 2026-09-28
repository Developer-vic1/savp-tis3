from collections.abc import Generator

import pytest
from fastapi.testclient import TestClient

from app.api.dependencies import knowledge_retriever
from app.ingestion.models import KnowledgeChunk
from app.main import app
from app.retrieval.hybrid import HybridHit


def _hit() -> HybridHit:
    chunk = KnowledgeChunk(
        chunk_id="chunk-sistemas",
        source_id="BO-UCB-LP-SIS-MALLA-2026",
        institution="Universidad Católica Boliviana San Pablo - Sede La Paz",
        title="Ingeniería de Sistemas - Malla Curricular 2026",
        source_type="OFFICIAL_CURRICULUM_PDF",
        url="https://lpz.ucb.edu.bo/malla-sistemas.pdf",
        official=True,
        publication_date="2026",
        retrieved_at="2026-09-28",
        valid_from="2026",
        valid_until=None,
        document_hash="sha256:" + ("a" * 64),
        section="Primer semestre",
        page=1,
        version="2026",
        status="ACTIVE",
        extraction_method="DIGITAL",
        extraction_confidence=None,
        ordinal=1,
        text=(
            "Ingeniería de Sistemas UCB. Primer semestre: Álgebra Lineal, Matemáticas "
            "Discretas e Introducción a la Programación."
        ),
    )
    return HybridHit(
        chunk=chunk,
        score=0.96,
        semantic_score=0.88,
        lexical_score=8.2,
        semantic_rank=2,
        lexical_rank=1,
    )


class StubRetriever:
    def __init__(self, hits: list[HybridHit]) -> None:
        self.hits = hits
        self.last_filters: dict[str, object] = {}

    def search(
        self,
        query: str,
        top_k: int,
        *,
        institution: str | None = None,
        source_type: str | None = None,
        official_only: bool = True,
    ) -> list[HybridHit]:
        self.last_filters = {
            "query": query,
            "top_k": top_k,
            "institution": institution,
            "source_type": source_type,
            "official_only": official_only,
        }
        return self.hits[:top_k]


@pytest.fixture
def stub_retriever() -> Generator[StubRetriever]:
    retriever = StubRetriever([_hit()])
    app.dependency_overrides[knowledge_retriever] = lambda: retriever
    yield retriever
    app.dependency_overrides.pop(knowledge_retriever, None)


def test_knowledge_returns_traced_official_evidence(
    client: TestClient, stub_retriever: StubRetriever
) -> None:
    response = client.post(
        "/api/v1/knowledge/search",
        json={
            "schema_version": "1.0",
            "query": "materias del primer semestre de Sistemas UCB",
            "institution": "UCB",
            "source_type": "OFFICIAL_CURRICULUM_PDF",
            "top_k": 3,
        },
    )
    assert response.status_code == 200
    body = response.json()
    assert body["insufficient_evidence"] is False
    assert body["embedding_model"] == "intfloat/multilingual-e5-small"
    assert body["retrieval_version"] == "bm25-rrf-v1.0.0"
    assert body["results"][0]["source_id"] == "BO-UCB-LP-SIS-MALLA-2026"
    assert body["results"][0]["reference"].endswith("#page=1")
    assert stub_retriever.last_filters["institution"] == "UCB"
    assert stub_retriever.last_filters["official_only"] is True


def test_knowledge_marks_empty_retrieval_as_insufficient(
    client: TestClient, stub_retriever: StubRetriever
) -> None:
    stub_retriever.hits = []
    response = client.post(
        "/api/v1/knowledge/search",
        json={"schema_version": "1.0", "query": "astronomía marciana 2099"},
    )
    assert response.status_code == 200
    assert response.json()["insufficient_evidence"] is True
    assert response.json()["results"] == []


def test_tutor_answers_from_retrieved_sources(
    client: TestClient, stub_retriever: StubRetriever
) -> None:
    response = client.post(
        "/api/v1/tutor/query",
        json={
            "schema_version": "1.0",
            "question": "¿Qué materias tiene el primer semestre de Sistemas UCB?",
            "academic_context": {"course": "6to", "ci": "dato-no-autorizado"},
        },
    )
    assert response.status_code == 200
    body = response.json()
    assert body["answer_mode"] == "STRUCTURED"
    assert body["insufficient_evidence"] is False
    assert "Álgebra Lineal" in body["answer"]
    assert body["sources"][0]["source_id"] == "BO-UCB-LP-SIS-MALLA-2026"
    assert body["provider_version"] == "structured-answer-v1.0.0"


def test_tutor_refuses_to_invent_without_sources(
    client: TestClient, stub_retriever: StubRetriever
) -> None:
    stub_retriever.hits = []
    response = client.post(
        "/api/v1/tutor/query",
        json={"schema_version": "1.0", "question": "¿Cuál es la matrícula de Marte?"},
    )
    assert response.status_code == 200
    body = response.json()
    assert body["answer_mode"] == "STRUCTURED"
    assert body["insufficient_evidence"] is True
    assert body["sources"] == []
    assert "No encontré evidencia oficial suficiente" in body["answer"]
