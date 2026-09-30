import argparse
import json
import os
import platform
from collections.abc import Callable
from pathlib import Path
from statistics import median
from time import perf_counter
from typing import Any, cast

import psutil

from app.contracts.requests import TutorQueryRequest
from app.contracts.v2 import AnalysisV2Request
from app.domain.student_profile_v2 import analyze_student_v2
from app.retrieval.service import get_retriever, selected_retrieval_metadata
from app.tutor.service import answer_structured

SERVICE_ROOT = Path(__file__).resolve().parents[1]
RESULTS_PATH = SERVICE_ROOT / "data" / "evaluation" / "performance_results.json"


def _percentile(values: list[float], quantile: float) -> float:
    ordered = sorted(values)
    rank = (len(ordered) - 1) * quantile
    lower = int(rank)
    upper = min(lower + 1, len(ordered) - 1)
    fraction = rank - lower
    return ordered[lower] + (ordered[upper] - ordered[lower]) * fraction


def _measure(operation: Callable[[], object], repetitions: int) -> dict[str, object]:
    operation()
    measurements: list[float] = []
    for _ in range(repetitions):
        started = perf_counter()
        operation()
        measurements.append((perf_counter() - started) * 1_000)
    return {
        "repetitions": repetitions,
        "median_ms": round(median(measurements), 3),
        "p95_ms": round(_percentile(measurements, 0.95), 3),
        "minimum_ms": round(min(measurements), 3),
        "maximum_ms": round(max(measurements), 3),
    }


def _fixture() -> dict[str, Any]:
    raw = json.loads(
        (SERVICE_ROOT / "data" / "fixtures" / "complete_profile.json").read_text(
            encoding="utf-8"
        )
    )
    payload = cast(dict[str, Any], raw)
    for metadata in ("fixture_type", "seed", "scenario_note"):
        payload.pop(metadata, None)
    payload["schema_version"] = "2.0"
    return payload


def main() -> None:
    parser = argparse.ArgumentParser(description="Benchmark integral reproducible de Peter 3.")
    parser.add_argument("--repetitions", type=int, default=20)
    args = parser.parse_args()
    if args.repetitions < 20:
        raise ValueError("Se requieren al menos 20 repeticiones para estimar p95")

    analysis_request = AnalysisV2Request.model_validate(_fixture())
    question = (
        "materias primer semestre Ingeniería de Sistemas UCB Álgebra Lineal "
        "Matemáticas Discretas"
    )
    tutor_request = TutorQueryRequest(
        schema_version="1.0",
        question=question,
        subject="Ingeniería de Sistemas",
        level="primer semestre",
    )
    retriever = get_retriever()

    def run_analysis() -> object:
        return analyze_student_v2(analysis_request, "benchmark-peter3")

    def run_retrieval() -> object:
        return retriever.search(question, 5, official_only=True)

    def run_tutor() -> object:
        return answer_structured(tutor_request, retriever)

    def run_full_pipeline() -> object:
        analysis = run_analysis()
        hits = run_retrieval()
        tutor = run_tutor()
        return analysis, hits, tutor

    virtual_memory = psutil.virtual_memory()
    output = {
        "benchmark_id": "peter3-performance-2026-09-29",
        "scope": "LOCAL_CPU_WARM_PROCESS_REAL_SELECTED_INDEX",
        "fixture_classification": "SYNTHETIC_TEST_FIXTURE",
        "timing_clock": "time.perf_counter",
        "environment": {
            "platform": platform.platform(),
            "python": platform.python_version(),
            "processor": platform.processor() or "not_reported_by_platform",
            "logical_cpu_count": os.cpu_count(),
            "physical_cpu_count": psutil.cpu_count(logical=False),
            "memory_total_bytes": virtual_memory.total,
            "embedding_device": "cpu",
            **selected_retrieval_metadata(),
        },
        "results": {
            "analysis_v2": _measure(run_analysis, args.repetitions),
            "retrieval_hybrid": _measure(run_retrieval, args.repetitions),
            "tutor_structured_with_retrieval": _measure(run_tutor, args.repetitions),
            "full_pipeline": _measure(run_full_pipeline, args.repetitions),
        },
        "limitations": [
            "Proceso caliente: no incluye descarga/carga inicial del modelo ni del índice.",
            "Ejecución local de un solo proceso; no es una prueba de concurrencia ni capacidad.",
            "El pipeline integral se mide como funciones de servicio, sin latencia HTTP o red.",
        ],
    }
    RESULTS_PATH.write_text(
        json.dumps(output, ensure_ascii=False, indent=2) + "\n", encoding="utf-8"
    )
    print(json.dumps(output, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
