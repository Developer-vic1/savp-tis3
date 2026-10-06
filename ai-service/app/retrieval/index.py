from pathlib import Path

from app.ingestion.models import KnowledgeChunk
from app.knowledge.registry import SERVICE_ROOT

CORPUS_PATH = SERVICE_ROOT / "data" / "processed" / "corpus.jsonl"


def load_corpus(path: Path = CORPUS_PATH) -> list[KnowledgeChunk]:
    return [
        KnowledgeChunk.model_validate_json(line)
        for line in path.read_text(encoding="utf-8").splitlines()
        if line.strip()
    ]
