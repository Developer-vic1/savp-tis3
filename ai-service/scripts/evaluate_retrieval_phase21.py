"""Evaluate DEV, freeze a model, then evaluate TEST once on the frozen setup."""

import argparse
import hashlib
import json
import math
from datetime import UTC, datetime
from pathlib import Path
from statistics import median
from time import perf_counter
from typing import Any, cast

from app.knowledge.registry import SERVICE_ROOT
from app.retrieval.embeddings import MODEL_SPECS, model_slug
from app.retrieval.hybrid import (
    HYBRID_VERSION,
    LEXICAL_WEIGHT,
    RRF_K,
    SEMANTIC_WEIGHT,
    HybridRetriever,
)
from app.retrieval.index import CORPUS_PATH, INDEX_ROOT, SELECTED_INDEX, SemanticIndex, load_corpus
from app.retrieval.metrics import aggregate_metrics

EVALUATION = SERVICE_ROOT / "data/evaluation"
FREEZE = EVALUATION / "retrieval_freeze_phase21.json"
DEV_RESULT = EVALUATION / "retrieval_dev_phase21.json"
TEST_RESULT = EVALUATION / "retrieval_test_phase21.json"


def corpus_sha() -> str:
    return hashlib.sha256(CORPUS_PATH.read_bytes()).hexdigest()


def read_json(path: Path) -> dict[str, Any]:
    data = json.loads(path.read_text(encoding="utf-8"))
    if not isinstance(data, dict):
        raise ValueError(f"Se esperaba objeto JSON: {path}")
    return cast(dict[str, Any], data)


def write_json(path: Path, value: dict[str, Any]) -> None:
    path.write_text(json.dumps(value, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")


def latency(values: list[float]) -> dict[str, float]:
    ordered = sorted(values)
    return {
        "median_ms": round(median(ordered), 4),
        "p95_ms": round(ordered[math.ceil(0.95 * len(ordered)) - 1], 4),
    }


def evaluate(model_id: str, queries: list[dict[str, Any]]) -> dict[str, Any]:
    directory = INDEX_ROOT / model_slug(model_id)
    manifest = read_json(directory / "manifest.json")
    corpus = load_corpus()
    if manifest["model_id"] != model_id or manifest["corpus_sha256"] != corpus_sha():
        raise ValueError(f"Índice STALE: {model_id}")
    index = SemanticIndex.load(directory)
    if index.chunks != corpus or index.index.ntotal != len(corpus):
        raise ValueError(f"Metadatos o cardinalidad STALE: {model_id}")
    hybrid = HybridRetriever(index)
    ids = {chunk.chunk_id: chunk.source_id for chunk in corpus}
    rankings: dict[str, list[list[str]]] = {"semantic": [], "hybrid": []}
    times: dict[str, list[float]] = {"semantic": [], "hybrid": []}
    relevant: list[set[str]] = []
    per_query: list[dict[str, Any]] = []
    for item in queries:
        expected = set(item["relevant_chunk_ids"])
        if expected - ids.keys():
            raise ValueError(f"IDs de relevancia ausentes: {item['query_id']}")
        if any(ids[chunk_id] not in item["relevant_source_ids"] for chunk_id in expected):
            raise ValueError(f"Fuente de relevancia inconsistente: {item['query_id']}")
        query = str(item["query"])
        started = perf_counter()
        semantic = index.search(query, 10)
        times["semantic"].append((perf_counter() - started) * 1000)
        started = perf_counter()
        fused = hybrid.search(query, 10)
        times["hybrid"].append((perf_counter() - started) * 1000)
        semantic_ids = [hit.chunk.chunk_id for hit in semantic]
        hybrid_ids = [hit.chunk.chunk_id for hit in fused]
        if expected:
            relevant.append(expected)
            rankings["semantic"].append(semantic_ids)
            rankings["hybrid"].append(hybrid_ids)
        per_query.append(
            {
                "query_id": item["query_id"],
                "category": item["category"],
                "relevant_chunk_ids": sorted(expected),
                "semantic_top_chunk_ids": semantic_ids,
                "hybrid_top_chunk_ids": hybrid_ids,
            }
        )
    return {
        "model_id": model_id,
        "index_manifest": manifest,
        "metrics_scope": "QUERIES_WITH_RELEVANT_CHUNKS",
        "positive_query_count": len(relevant),
        "no_relevant_chunk_count": len(queries) - len(relevant),
        "semantic": {"metrics": aggregate_metrics(rankings["semantic"], relevant),
                     "latency": latency(times["semantic"])},
        "hybrid": {"metrics": aggregate_metrics(rankings["hybrid"], relevant),
                   "latency": latency(times["hybrid"])},
        "per_query": per_query,
    }


def evaluate_split(split: str) -> None:
    output_path = DEV_RESULT if split == "DEV" else TEST_RESULT
    if split == "TEST":
        if output_path.exists():
            raise ValueError("TEST ya fue ejecutado; no se sobrescribe")
        freeze = read_json(FREEZE)
        selected = read_json(SELECTED_INDEX)
        if freeze["corpus_sha256"] != corpus_sha() or selected["model_id"] != freeze["model_id"]:
            raise ValueError("FREEZE no coincide con corpus o selección")
        models = [str(freeze["model_id"])]
    else:
        if FREEZE.exists():
            raise ValueError("Configuración ya congelada; DEV no se repite")
        models = list(MODEL_SPECS)
    dataset = read_json(EVALUATION / f"retrieval_{split.lower()}.json")
    results = [evaluate(model_id, dataset["queries"]) for model_id in models]
    write_json(
        output_path,
        {
            "split": split,
            "dataset_version": dataset["dataset_version"],
            "corpus_sha256": corpus_sha(),
            "evaluated_at": datetime.now(UTC).isoformat(),
            "query_count": len(dataset["queries"]),
            "models": results,
        },
    )
    print(f"{split} registrado: {output_path}")


def freeze(model_id: str) -> None:
    if FREEZE.exists() or TEST_RESULT.exists():
        raise ValueError("Ya existe FREEZE o resultado TEST")
    dev = read_json(DEV_RESULT)
    if dev["corpus_sha256"] != corpus_sha():
        raise ValueError("DEV corresponde a otro corpus")
    selected = next((item for item in dev["models"] if item["model_id"] == model_id), None)
    if selected is None:
        raise ValueError("El modelo elegido no fue evaluado en DEV")
    selection = {
        "selection_version": "retrieval-selection-phase2.1",
        "dataset_version": dev["dataset_version"],
        "model_id": model_id,
        "index_directory": str((INDEX_ROOT / model_slug(model_id)).relative_to(SERVICE_ROOT))
        .replace("\\", "/"),
        "selection_rule": "Manual después de revisar DEV; sin acceso a TEST",
        "relevance_level": "CHUNK_LEVEL_MANUAL",
        "hybrid_version": HYBRID_VERSION,
        "semantic_metrics": selected["semantic"]["metrics"],
        "hybrid_metrics": selected["hybrid"]["metrics"],
        "corpus_sha256": corpus_sha(),
    }
    marker = {
        "model_id": model_id,
        "corpus_sha256": corpus_sha(),
        "dev_result_sha256": hashlib.sha256(DEV_RESULT.read_bytes()).hexdigest(),
        "frozen_at": datetime.now(UTC).isoformat(),
        "parameters": {
            "top_k": 10,
            "rrf_k": RRF_K,
            "semantic_weight": SEMANTIC_WEIGHT,
            "lexical_weight": LEXICAL_WEIGHT,
            "bm25_k1": 1.5,
            "bm25_b": 0.75,
        },
        "parameter_status": "EXPERIMENTAL_UNCHANGED",
    }
    write_json(SELECTED_INDEX, selection)
    write_json(FREEZE, marker)
    print(f"FREEZE registrado: {FREEZE}")


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("action", choices=("dev", "freeze", "test"))
    parser.add_argument("--model-id", choices=tuple(MODEL_SPECS))
    args = parser.parse_args()
    if args.action == "freeze":
        if args.model_id is None:
            parser.error("freeze requiere --model-id")
        freeze(args.model_id)
    elif args.action == "dev":
        evaluate_split("DEV")
    else:
        evaluate_split("TEST")


if __name__ == "__main__":
    main()
