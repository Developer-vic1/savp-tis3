import json
from statistics import fmean
from time import perf_counter
from typing import Any

import psutil
import torch

from app.knowledge.registry import SERVICE_ROOT
from app.retrieval.embeddings import MODEL_SPECS, model_slug
from app.retrieval.hybrid import HYBRID_VERSION, HybridRetriever
from app.retrieval.index import (
    INDEX_ROOT,
    SELECTED_INDEX,
    build_and_save_index,
    load_corpus,
)
from app.retrieval.metrics import aggregate_metrics

DATASET_PATH = SERVICE_ROOT / "data" / "evaluation" / "retrieval_queries.json"
RESULTS_PATH = SERVICE_ROOT / "data" / "evaluation" / "retrieval_results.json"


def main() -> None:
    dataset: dict[str, Any] = json.loads(DATASET_PATH.read_text(encoding="utf-8"))
    queries: list[dict[str, Any]] = dataset["queries"]
    corpus = load_corpus()
    chunk_sources = {chunk.chunk_id: chunk.source_id for chunk in corpus}
    for item in queries:
        missing = set(item["relevant_chunk_ids"]) - chunk_sources.keys()
        if missing:
            raise ValueError(f"{item['query_id']}: chunk_ids inexistentes: {sorted(missing)}")
        mislabeled = {
            chunk_id
            for chunk_id in item["relevant_chunk_ids"]
            if chunk_sources[chunk_id] not in item["relevant_source_ids"]
        }
        if mislabeled:
            raise ValueError(
                f"{item['query_id']}: chunk_ids fuera de sus fuentes: {sorted(mislabeled)}"
            )
    process = psutil.Process()
    results: list[dict[str, Any]] = []

    for model_id in MODEL_SPECS:
        rss_before = process.memory_info().rss
        started = perf_counter()
        semantic_index, manifest = build_and_save_index(model_id)
        build_total_seconds = perf_counter() - started
        hybrid_started = perf_counter()
        hybrid_retriever = HybridRetriever(semantic_index)
        hybrid_build_seconds = perf_counter() - hybrid_started
        semantic_chunk_rankings: list[list[str]] = []
        hybrid_chunk_rankings: list[list[str]] = []
        relevant_chunks: list[set[str]] = []
        semantic_source_rankings: list[list[str]] = []
        hybrid_source_rankings: list[list[str]] = []
        relevant_sources: list[set[str]] = []
        semantic_latencies_ms: list[float] = []
        hybrid_latencies_ms: list[float] = []
        per_query: list[dict[str, object]] = []
        for item in queries:
            query_started = perf_counter()
            semantic_hits = semantic_index.search(str(item["query"]), 10)
            semantic_latencies_ms.append((perf_counter() - query_started) * 1000)
            hybrid_query_started = perf_counter()
            hybrid_hits = hybrid_retriever.search(str(item["query"]), 10)
            hybrid_latencies_ms.append((perf_counter() - hybrid_query_started) * 1000)
            semantic_sources = [hit.chunk.source_id for hit in semantic_hits]
            semantic_chunks = [hit.chunk.chunk_id for hit in semantic_hits]
            hybrid_sources = [hit.chunk.source_id for hit in hybrid_hits]
            hybrid_chunks = [hit.chunk.chunk_id for hit in hybrid_hits]
            expected_chunks = {
                str(chunk_id) for chunk_id in item["relevant_chunk_ids"]
            }
            expected_sources = {
                str(source_id) for source_id in item["relevant_source_ids"]
            }
            semantic_chunk_rankings.append(semantic_chunks)
            hybrid_chunk_rankings.append(hybrid_chunks)
            relevant_chunks.append(expected_chunks)
            semantic_source_rankings.append(semantic_sources)
            hybrid_source_rankings.append(hybrid_sources)
            relevant_sources.append(expected_sources)
            per_query.append(
                {
                    "query_id": item["query_id"],
                    "relevant_chunk_ids": sorted(expected_chunks),
                    "semantic": {
                        "top_source_ids": semantic_sources,
                        "top_chunk_ids": semantic_chunks,
                        "top_scores": [round(hit.score, 6) for hit in semantic_hits],
                    },
                    "hybrid": {
                        "top_source_ids": hybrid_sources,
                        "top_chunk_ids": hybrid_chunks,
                        "top_scores": [round(hit.score, 6) for hit in hybrid_hits],
                        "semantic_ranks": [hit.semantic_rank for hit in hybrid_hits],
                        "lexical_ranks": [hit.lexical_rank for hit in hybrid_hits],
                    },
                }
            )
        semantic_chunk_metrics = aggregate_metrics(
            semantic_chunk_rankings, relevant_chunks
        )
        hybrid_chunk_metrics = aggregate_metrics(hybrid_chunk_rankings, relevant_chunks)
        semantic_source_metrics = aggregate_metrics(
            semantic_source_rankings, relevant_sources
        )
        hybrid_source_metrics = aggregate_metrics(hybrid_source_rankings, relevant_sources)
        rss_after = process.memory_info().rss
        results.append(
            {
                "model_id": model_id,
                "license": MODEL_SPECS[model_id]["license"],
                "index_manifest": manifest,
                "semantic_chunk_metrics": semantic_chunk_metrics,
                "hybrid_chunk_metrics": hybrid_chunk_metrics,
                "semantic_source_metrics_diagnostic": semantic_source_metrics,
                "hybrid_source_metrics_diagnostic": hybrid_source_metrics,
                "build_total_seconds": round(build_total_seconds, 4),
                "hybrid_build_seconds": round(hybrid_build_seconds, 4),
                "semantic_query_latency_ms_mean": round(
                    fmean(semantic_latencies_ms), 4
                ),
                "semantic_query_latency_ms_max": round(max(semantic_latencies_ms), 4),
                "hybrid_query_latency_ms_mean": round(fmean(hybrid_latencies_ms), 4),
                "hybrid_query_latency_ms_max": round(max(hybrid_latencies_ms), 4),
                "rss_delta_mb": round((rss_after - rss_before) / (1024**2), 2),
                "per_query": per_query,
            }
        )
        print(
            f"{model_id}: semantic={semantic_chunk_metrics}, "
            f"hybrid={hybrid_chunk_metrics}, "
            f"build={build_total_seconds:.2f}s, "
            f"hybrid_query_mean={fmean(hybrid_latencies_ms):.2f}ms"
        )

    selected = max(
        results,
        key=lambda result: (
            result["semantic_chunk_metrics"]["ndcg_at_5"],
            result["semantic_chunk_metrics"]["recall_at_5"],
            result["semantic_chunk_metrics"]["mrr"],
            -result["semantic_query_latency_ms_mean"],
        ),
    )
    selected_model = str(selected["model_id"])
    selection = {
        "selection_version": "retrieval-selection-v1.2.0",
        "dataset_version": dataset["dataset_version"],
        "model_id": selected_model,
        "index_directory": str(
            (INDEX_ROOT / model_slug(selected_model)).relative_to(SERVICE_ROOT)
        ).replace("\\", "/"),
        "selection_rule": (
            "max(semantic_ndcg_at_5, semantic_recall_at_5, semantic_mrr, "
            "-semantic_query_latency_mean)"
        ),
        "relevance_level": dataset["relevance_level"],
        "hybrid_version": HYBRID_VERSION,
        "semantic_metrics": selected["semantic_chunk_metrics"],
        "hybrid_metrics": selected["hybrid_chunk_metrics"],
    }
    output = {
        "evaluation_version": "embedding-ab-v1.2.0",
        "dataset_version": dataset["dataset_version"],
        "relevance_level": dataset["relevance_level"],
        "query_count": len(queries),
        "device": "cuda" if torch.cuda.is_available() else "cpu",
        "hybrid_version": HYBRID_VERSION,
        "models": results,
        "selected": selection,
    }
    RESULTS_PATH.write_text(
        json.dumps(output, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    SELECTED_INDEX.parent.mkdir(parents=True, exist_ok=True)
    SELECTED_INDEX.write_text(
        json.dumps(selection, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    print(f"Seleccionado: {selected_model}")
    print(f"Resultados: {RESULTS_PATH}")


if __name__ == "__main__":
    main()
