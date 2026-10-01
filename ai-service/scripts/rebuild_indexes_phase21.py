"""Rebuild both real embedding indexes after checking upstream provenance."""

import hashlib
import subprocess
import sys

from app.retrieval.embeddings import MODEL_SPECS
from app.retrieval.index import CORPUS_PATH, build_and_save_index, load_corpus


def main() -> int:
    check = subprocess.run(
        [sys.executable, "scripts/verify_sources.py", "--upstream-only"],
        check=False,
    )
    if check.returncode:
        print("FAIL: fuentes o corpus inválidos; no se generan embeddings")
        return 1
    chunks = load_corpus()
    expected_sha = hashlib.sha256(CORPUS_PATH.read_bytes()).hexdigest()
    if len(chunks) != 773 or len({chunk.chunk_id for chunk in chunks}) != 773:
        print("FAIL: el corpus definitivo debe tener 773 IDs únicos")
        return 1
    for model_id in MODEL_SPECS:
        print(f"Construyendo embeddings reales: {model_id}", flush=True)
        index, manifest = build_and_save_index(model_id)
        if (
            manifest["corpus_sha256"] != expected_sha
            or manifest["chunk_count"] != len(chunks)
            or index.index.ntotal != len(chunks)
            or index.chunks != chunks
        ):
            print(f"FAIL: índice inválido: {model_id}")
            return 1
        print(f"VALID {model_id}: {index.index.ntotal} vectores, d={index.index.d}")
    print("PASS: ejecutar verify_sources.py y test_index_integrity.py")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
