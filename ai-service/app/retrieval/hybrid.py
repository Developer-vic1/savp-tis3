import math
import re
import unicodedata
from collections import Counter

from pydantic import BaseModel, ConfigDict, Field

from app.ingestion.models import KnowledgeChunk

LEXICAL_RETRIEVAL_VERSION = "bm25-local-v1.0.0"

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


class KnowledgeHit(BaseModel):
    model_config = ConfigDict(extra="forbid")

    chunk: KnowledgeChunk
    score: float = Field(ge=0, le=1)
    lexical_score: float = Field(ge=0)
    lexical_rank: int = Field(gt=0)


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


class LexicalRetriever:
    def __init__(self, chunks: list[KnowledgeChunk]) -> None:
        self.chunks = chunks
        self.lexical_index = Bm25Index(chunks)

    def search(
        self,
        query: str,
        top_k: int,
        *,
        institution: str | None = None,
        source_type: str | None = None,
        official_only: bool = True,
    ) -> list[KnowledgeHit]:
        if not query.strip() or top_k < 1:
            return []
        candidates = [
            (score, chunk)
            for score, chunk in zip(self.lexical_index.scores(query), self.chunks, strict=True)
            if score > 0
            and (not official_only or chunk.official)
            and (not institution or institution.casefold() in chunk.institution.casefold())
            and (not source_type or source_type.casefold() == chunk.source_type.casefold())
        ]
        candidates.sort(key=lambda item: (-item[0], item[1].chunk_id))
        if not candidates:
            return []
        highest_score = candidates[0][0]
        return [
            KnowledgeHit(
                chunk=chunk,
                score=round(raw_score / highest_score, 6),
                lexical_score=raw_score,
                lexical_rank=rank,
            )
            for rank, (raw_score, chunk) in enumerate(candidates[:top_k], start=1)
        ]
