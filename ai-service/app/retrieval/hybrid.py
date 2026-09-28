import math
import re
import unicodedata
from collections import Counter

from pydantic import BaseModel, ConfigDict, Field

from app.ingestion.models import KnowledgeChunk
from app.retrieval.index import SemanticIndex

RRF_K = 60
SEMANTIC_WEIGHT = 1.0
LEXICAL_WEIGHT = 1.0
HYBRID_VERSION = "bm25-rrf-v1.0.0"

SPANISH_STOPWORDS = {
    "a",
    "al",
    "como",
    "con",
    "cual",
    "de",
    "del",
    "el",
    "en",
    "es",
    "esta",
    "la",
    "las",
    "lo",
    "los",
    "para",
    "por",
    "que",
    "se",
    "su",
    "un",
    "una",
    "y",
}


def tokenize(value: str) -> list[str]:
    folded = "".join(
        character
        for character in unicodedata.normalize("NFD", value.casefold())
        if unicodedata.category(character) != "Mn"
    )
    return [
        token
        for token in re.findall(r"[a-z0-9]+", folded)
        if len(token) > 2 and token not in SPANISH_STOPWORDS
    ]


def _searchable_text(chunk: KnowledgeChunk) -> str:
    return " ".join((chunk.title, chunk.section or "", chunk.text))


class HybridHit(BaseModel):
    model_config = ConfigDict(extra="forbid")

    chunk: KnowledgeChunk
    score: float = Field(ge=0, le=1)
    semantic_score: float = Field(ge=-1.01, le=1.01)
    lexical_score: float = Field(ge=0)
    semantic_rank: int = Field(gt=0)
    lexical_rank: int | None = Field(default=None, gt=0)


class Bm25Index:
    def __init__(
        self, chunks: list[KnowledgeChunk], *, k1: float = 1.5, b: float = 0.75
    ) -> None:
        self.chunks = chunks
        self.k1 = k1
        self.b = b
        self.term_frequencies = [Counter(tokenize(_searchable_text(chunk))) for chunk in chunks]
        self.document_lengths = [sum(frequencies.values()) for frequencies in self.term_frequencies]
        self.average_length = (
            sum(self.document_lengths) / len(self.document_lengths) if self.document_lengths else 0
        )
        self.document_frequencies: Counter[str] = Counter()
        for frequencies in self.term_frequencies:
            self.document_frequencies.update(frequencies.keys())

    def scores(self, query: str) -> list[float]:
        query_terms = set(tokenize(query))
        document_count = len(self.chunks)
        if not query_terms or not document_count or not self.average_length:
            return [0.0] * document_count
        scores: list[float] = []
        for frequencies, length in zip(
            self.term_frequencies, self.document_lengths, strict=True
        ):
            score = 0.0
            for term in query_terms:
                frequency = frequencies.get(term, 0)
                if not frequency:
                    continue
                document_frequency = self.document_frequencies[term]
                inverse_document_frequency = math.log(
                    1 + (document_count - document_frequency + 0.5) / (document_frequency + 0.5)
                )
                denominator = frequency + self.k1 * (
                    1 - self.b + self.b * length / self.average_length
                )
                score += inverse_document_frequency * frequency * (self.k1 + 1) / denominator
            scores.append(score)
        return scores


class HybridRetriever:
    def __init__(self, semantic_index: SemanticIndex) -> None:
        self.semantic_index = semantic_index
        self.lexical_index = Bm25Index(semantic_index.chunks)

    def search(
        self,
        query: str,
        top_k: int,
        *,
        institution: str | None = None,
        source_type: str | None = None,
        official_only: bool = True,
    ) -> list[HybridHit]:
        if not query.strip() or top_k < 1:
            return []
        semantic_hits = self.semantic_index.search(
            query,
            len(self.semantic_index.chunks),
            institution=institution,
            source_type=source_type,
            official_only=official_only,
        )
        if not semantic_hits:
            return []

        semantic_by_id = {
            hit.chunk.chunk_id: (rank, hit.score, hit.chunk)
            for rank, hit in enumerate(semantic_hits, start=1)
        }
        lexical_scores = self.lexical_index.scores(query)
        lexical_candidates = [
            (lexical_scores[position], chunk.chunk_id)
            for position, chunk in enumerate(self.semantic_index.chunks)
            if lexical_scores[position] > 0 and chunk.chunk_id in semantic_by_id
        ]
        lexical_candidates.sort(key=lambda item: (-item[0], item[1]))
        lexical_by_id = {
            chunk_id: (rank, score)
            for rank, (score, chunk_id) in enumerate(lexical_candidates, start=1)
        }

        ideal = (SEMANTIC_WEIGHT + LEXICAL_WEIGHT) / (RRF_K + 1)
        hits: list[HybridHit] = []
        for chunk_id, (semantic_rank, semantic_score, chunk) in semantic_by_id.items():
            lexical = lexical_by_id.get(chunk_id)
            raw_score = SEMANTIC_WEIGHT / (RRF_K + semantic_rank)
            lexical_rank: int | None = None
            lexical_score = 0.0
            if lexical:
                lexical_rank, lexical_score = lexical
                raw_score += LEXICAL_WEIGHT / (RRF_K + lexical_rank)
            hits.append(
                HybridHit(
                    chunk=chunk,
                    score=min(raw_score / ideal, 1.0),
                    semantic_score=semantic_score,
                    lexical_score=lexical_score,
                    semantic_rank=semantic_rank,
                    lexical_rank=lexical_rank,
                )
            )
        hits.sort(
            key=lambda hit: (
                -hit.score,
                hit.semantic_rank,
                hit.lexical_rank or len(self.semantic_index.chunks) + 1,
                hit.chunk.chunk_id,
            )
        )
        return hits[:top_k]
