import hashlib
import json
from pathlib import Path

from app.retrieval.index import CORPUS_PATH, load_corpus

SERVICE_ROOT = Path(__file__).resolve().parents[2]


def test_corpus_manifest_matches_the_local_knowledge_base() -> None:
    chunks = load_corpus()
    manifest = json.loads(
        (SERVICE_ROOT / "data" / "processed" / "corpus_manifest.json").read_text(
            encoding="utf-8"
        )
    )

    assert chunks
    assert len(chunks) == manifest["chunk_count"]
    assert len({chunk.chunk_id for chunk in chunks}) == len(chunks)
    assert len({chunk.source_id for chunk in chunks}) == manifest["source_count"]
    assert hashlib.sha256(CORPUS_PATH.read_bytes()).hexdigest()


def test_corpus_loader_ignores_blank_lines(tmp_path: Path) -> None:
    corpus = tmp_path / "corpus.jsonl"
    corpus.write_text("\n", encoding="utf-8")

    assert load_corpus(corpus) == []
