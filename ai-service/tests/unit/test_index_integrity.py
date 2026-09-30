import hashlib
import json
from pathlib import Path

import faiss

from app.ingestion.models import KnowledgeChunk
from app.retrieval.index import INDEX_ROOT, SELECTED_INDEX, load_corpus

SERVICE_ROOT = Path(__file__).resolve().parents[2]


def test_faiss_indexes_cardinality_and_dimension() -> None:
    corpus = load_corpus()
    corpus_len = len(corpus)
    assert corpus_len > 0
    corpus_ids = [chunk.chunk_id for chunk in corpus]
    assert len(corpus_ids) == len(set(corpus_ids))
    corpus_sha256 = hashlib.sha256(
        (SERVICE_ROOT / "data" / "processed" / "corpus.jsonl").read_bytes()
    ).hexdigest()

    index_dirs = [d for d in INDEX_ROOT.iterdir() if d.is_dir()]
    assert len(index_dirs) >= 2

    for directory in index_dirs:
        index_file = directory / "index.faiss"
        chunks_file = directory / "chunks.jsonl"
        manifest_file = directory / "manifest.json"

        assert index_file.is_file(), f"Falta index.faiss en {directory}"
        assert chunks_file.is_file(), f"Falta chunks.jsonl en {directory}"
        assert manifest_file.is_file(), f"Falta manifest.json en {directory}"

        index = faiss.read_index(str(index_file))
        manifest = json.loads(manifest_file.read_text(encoding="utf-8"))

        chunk_lines = [
            line for line in chunks_file.read_text(encoding="utf-8").splitlines() if line.strip()
        ]
        parsed_chunks = [KnowledgeChunk.model_validate_json(line) for line in chunk_lines]

        # Invariant checks:
        assert (
            index.ntotal == corpus_len
        ), f"Cardinalidad de FAISS ({index.ntotal}) != corpus ({corpus_len})"
        assert (
            len(parsed_chunks) == corpus_len
        ), f"Cantidad de chunks ({len(parsed_chunks)}) != corpus ({corpus_len})"
        assert manifest["chunk_count"] == corpus_len
        index_chunk_ids = [chunk.chunk_id for chunk in parsed_chunks]
        assert index_chunk_ids == corpus_ids
        assert len(index_chunk_ids) == len(set(index_chunk_ids))
        assert manifest["corpus_sha256"] == corpus_sha256
        assert manifest["model_id"] in {
            "sentence-transformers/paraphrase-multilingual-MiniLM-L12-v2",
            "intfloat/multilingual-e5-small",
        }
        assert index.d == 384, f"Dimensión esperada 384, encontrada {index.d}"
        assert manifest["dimension"] == 384
        assert manifest["metric"] == "COSINE_VIA_NORMALIZED_INNER_PRODUCT"


def test_selected_index_consistency() -> None:
    assert SELECTED_INDEX.is_file()
    selected = json.loads(SELECTED_INDEX.read_text(encoding="utf-8"))

    assert "model_id" in selected
    assert "index_directory" in selected
    assert (SERVICE_ROOT / selected["index_directory"]).is_dir()
    assert (SERVICE_ROOT / selected["index_directory"] / "index.faiss").is_file()
