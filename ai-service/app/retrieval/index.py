import hashlib
import json
from pathlib import Path
from time import perf_counter
from typing import Protocol

import faiss
import numpy as np
from pydantic import BaseModel, ConfigDict, Field

from app.ingestion.models import KnowledgeChunk
from app.knowledge.registry import SERVICE_ROOT
from app.retrieval.embeddings import SentenceEmbeddingBackend, model_slug

CORPUS_PATH = SERVICE_ROOT / "data" / "processed" / "corpus.jsonl"
INDEX_ROOT = SERVICE_ROOT / "data" / "indexes"
SELECTED_INDEX = INDEX_ROOT / "selected.json"


class RetrievalModel(BaseModel):
    model_config = ConfigDict(extra="forbid")


class RetrievalHit(RetrievalModel):
    chunk: KnowledgeChunk
    score: float = Field(ge=-1.01, le=1.01)


class EmbeddingBackend(Protocol):
    model_id: str

    def encode_documents(self, texts: list[str]) -> np.ndarray: ...

    def encode_queries(self, texts: list[str]) -> np.ndarray: ...


def load_corpus(path: Path = CORPUS_PATH) -> list[KnowledgeChunk]:
    return [
        KnowledgeChunk.model_validate_json(line)
        for line in path.read_text(encoding="utf-8").splitlines()
        if line.strip()
    ]


def _embedding_text(chunk: KnowledgeChunk) -> str:
    parts = [chunk.title]
    if chunk.section:
        parts.append(chunk.section)
    parts.append(chunk.text)
    return ". ".join(parts)


class SemanticIndex:
    def __init__(
        self,
        model_id: str,
        index: faiss.Index,
        chunks: list[KnowledgeChunk],
        backend: EmbeddingBackend | None = None,
    ) -> None:
        if index.ntotal != len(chunks):
            raise ValueError("El índice y los metadatos no tienen la misma cardinalidad")
        self.model_id = model_id
        self.index = index
        self.chunks = chunks
        self.backend = backend or SentenceEmbeddingBackend(model_id)

    @classmethod
    def create(
        cls,
        model_id: str,
        chunks: list[KnowledgeChunk],
        backend: EmbeddingBackend | None = None,
    ) -> tuple["SemanticIndex", float]:
        selected_backend = backend or SentenceEmbeddingBackend(model_id)
        started = perf_counter()
        vectors = selected_backend.encode_documents([_embedding_text(chunk) for chunk in chunks])
        if vectors.ndim != 2 or vectors.shape[0] != len(chunks):
            raise ValueError("El backend devolvió una matriz de embeddings inválida")
        faiss.normalize_L2(vectors)
        index = faiss.IndexFlatIP(vectors.shape[1])
        index.add(vectors)
        return cls(model_id, index, chunks, selected_backend), perf_counter() - started

    def save(self, directory: Path, build_seconds: float) -> dict[str, object]:
        directory.mkdir(parents=True, exist_ok=True)
        faiss.write_index(self.index, str(directory / "index.faiss"))
        metadata = directory / "chunks.jsonl"
        metadata.write_text(
            "".join(chunk.model_dump_json() + "\n" for chunk in self.chunks),
            encoding="utf-8",
        )
        manifest: dict[str, object] = {
            "index_version": "faiss-flat-ip-v1.0.0",
            "model_id": self.model_id,
            "model_slug": model_slug(self.model_id),
            "dimension": self.index.d,
            "chunk_count": len(self.chunks),
            "metric": "COSINE_VIA_NORMALIZED_INNER_PRODUCT",
            "exact": True,
            "corpus_sha256": hashlib.sha256(CORPUS_PATH.read_bytes()).hexdigest(),
            "build_seconds": round(build_seconds, 4),
        }
        (directory / "manifest.json").write_text(
            json.dumps(manifest, ensure_ascii=False, indent=2) + "\n",
            encoding="utf-8",
        )
        return manifest

    @classmethod
    def load(
        cls,
        directory: Path,
        backend: EmbeddingBackend | None = None,
    ) -> "SemanticIndex":
        manifest = json.loads((directory / "manifest.json").read_text(encoding="utf-8"))
        model_id = str(manifest["model_id"])
        chunks = load_corpus(directory / "chunks.jsonl")
        index = faiss.read_index(str(directory / "index.faiss"))
        return cls(model_id, index, chunks, backend)

    @classmethod
    def load_selected(cls) -> "SemanticIndex":
        selected = json.loads(SELECTED_INDEX.read_text(encoding="utf-8"))
        return cls.load(SERVICE_ROOT / str(selected["index_directory"]))

    def search(
        self,
        query: str,
        top_k: int,
        *,
        institution: str | None = None,
        source_type: str | None = None,
        official_only: bool = True,
    ) -> list[RetrievalHit]:
        if not query.strip():
            return []
        vector = self.backend.encode_queries([query])
        faiss.normalize_L2(vector)
        scores, positions = self.index.search(vector, len(self.chunks))
        hits: list[RetrievalHit] = []
        for score, position in zip(scores[0], positions[0], strict=True):
            if position < 0:
                continue
            chunk = self.chunks[int(position)]
            if official_only and not chunk.official:
                continue
            if institution and institution.casefold() not in chunk.institution.casefold():
                continue
            if source_type and source_type.casefold() != chunk.source_type.casefold():
                continue
            hits.append(RetrievalHit(chunk=chunk, score=float(score)))
            if len(hits) >= top_k:
                break
        return hits


def build_and_save_index(model_id: str) -> tuple[SemanticIndex, dict[str, object]]:
    semantic_index, elapsed = SemanticIndex.create(model_id, load_corpus())
    directory = INDEX_ROOT / model_slug(model_id)
    manifest = semantic_index.save(directory, elapsed)
    return semantic_index, manifest
