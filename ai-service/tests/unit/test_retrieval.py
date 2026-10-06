from app.ingestion.models import KnowledgeChunk
from app.retrieval.hybrid import Bm25Index, LexicalRetriever, tokenize
from app.retrieval.metrics import (
    aggregate_metrics,
    ndcg_at_k,
    recall_at_k,
    reciprocal_rank,
)


def _chunk(chunk_id: str, source_id: str, text: str) -> KnowledgeChunk:
    return KnowledgeChunk(
        chunk_id=chunk_id,
        source_id=source_id,
        institution="Institución",
        title="Título",
        source_type="OFFICIAL_CURRICULUM_PDF",
        url="https://example.edu.bo/source",
        official=True,
        publication_date="2026",
        retrieved_at="2026-09-28",
        valid_from="2026",
        valid_until=None,
        document_hash="sha256:" + ("a" * 64),
        section=None,
        page=1,
        version="1",
        status="ACTIVE",
        extraction_method="DIGITAL",
        extraction_confidence=None,
        ordinal=1,
        text=text,
    )


def test_lexical_retriever_returns_literal_evidence() -> None:
    chunks = [
        _chunk("1", "S1", "programación"),
        _chunk("2", "S2", "psicología"),
        _chunk("3", "S3", "software"),
    ]
    hits = LexicalRetriever(chunks).search("programación", 3)
    assert [hit.chunk.chunk_id for hit in hits] == ["1"]
    assert hits[0].score == 1
    assert hits[0].lexical_rank == 1


def test_retrieval_metrics_have_known_values() -> None:
    ranking = ["X", "A", "A", "B"]
    relevant = {"A", "B"}
    assert recall_at_k(ranking, relevant, 1) == 0
    assert recall_at_k(ranking, relevant, 4) == 1
    assert reciprocal_rank(ranking, relevant) == 0.5
    assert round(ndcg_at_k(ranking, relevant, 4), 4) == 0.6509
    metrics = aggregate_metrics([ranking], [relevant])
    assert metrics["recall_at_5"] == 1
    assert metrics["mrr"] == 0.5


def test_bm25_tokenization_is_accent_insensitive() -> None:
    chunks = [
        _chunk("1", "S1", "Álgebra lineal y cálculo"),
        _chunk("2", "S2", "Psicología social"),
    ]
    scores = Bm25Index(chunks).scores("algebra")
    assert tokenize("Álgebra en la UCB") == ["algebra", "ucb"]
    assert scores[0] > scores[1]


def test_lexical_retrieval_orders_strongest_literal_evidence_first() -> None:
    chunks = [
        _chunk("relevant", "S1", "Álgebra lineal"),
        _chunk("secondary", "S2", "Álgebra básica para orientación universitaria"),
    ]
    hits = LexicalRetriever(chunks).search("álgebra lineal", 2)
    assert [hit.chunk.chunk_id for hit in hits] == ["relevant", "secondary"]
    assert hits[0].lexical_rank == 1
    assert hits[0].score > hits[1].score
