from pathlib import Path

import numpy as np

from app.ingestion.models import KnowledgeChunk
from app.retrieval.hybrid import Bm25Index, HybridRetriever, tokenize
from app.retrieval.index import SemanticIndex
from app.retrieval.metrics import (
    aggregate_metrics,
    ndcg_at_k,
    recall_at_k,
    reciprocal_rank,
)


class FakeBackend:
    model_id = "fake/model"

    def encode_documents(self, texts: list[str]) -> np.ndarray:
        assert len(texts) == 3
        return np.asarray([[1, 0], [0, 1], [0.8, 0.2]], dtype=np.float32)

    def encode_queries(self, texts: list[str]) -> np.ndarray:
        assert texts == ["programación"]
        return np.asarray([[1, 0]], dtype=np.float32)


class LexicalRescueBackend:
    model_id = "fake/rescue"

    def encode_documents(self, texts: list[str]) -> np.ndarray:
        assert len(texts) == 2
        return np.asarray([[0, 1], [1, 0]], dtype=np.float32)

    def encode_queries(self, texts: list[str]) -> np.ndarray:
        assert texts == ["álgebra lineal"]
        return np.asarray([[1, 0]], dtype=np.float32)


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


def test_exact_index_search_and_persistence(tmp_path: Path) -> None:
    chunks = [
        _chunk("1", "S1", "programación"),
        _chunk("2", "S2", "psicología"),
        _chunk("3", "S3", "software"),
    ]
    index, elapsed = SemanticIndex.create("fake/model", chunks, FakeBackend())
    hits = index.search("programación", 3)
    assert [hit.chunk.chunk_id for hit in hits] == ["1", "3", "2"]
    assert hits[0].score == 1
    index.save(tmp_path, elapsed)
    loaded = SemanticIndex.load(tmp_path, FakeBackend())
    assert [hit.chunk.chunk_id for hit in loaded.search("programación", 2)] == ["1", "3"]


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


def test_hybrid_rrf_can_rescue_literal_evidence() -> None:
    chunks = [
        _chunk("relevant", "S1", "Álgebra lineal"),
        _chunk("semantic-only", "S2", "Orientación universitaria"),
    ]
    index, _ = SemanticIndex.create("fake/rescue", chunks, LexicalRescueBackend())
    hits = HybridRetriever(index).search("álgebra lineal", 2)
    assert [hit.chunk.chunk_id for hit in hits] == ["relevant", "semantic-only"]
    assert hits[0].lexical_rank == 1
    assert hits[0].semantic_rank == 2
    assert hits[0].score > hits[1].score
