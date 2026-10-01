import argparse
import json
import os
import platform
from collections.abc import Callable
from datetime import UTC, datetime
from pathlib import Path
from statistics import median
from time import perf_counter
from typing import cast

from app.contracts.v2 import AnalysisV2Request
from app.domain.student_profile_v2 import analyze_student_v2
from app.learning_analytics.academic_performance import build_academic_profile
from app.recommendation.evidence_engine import build_career_evidence_profiles
from app.riasec.scoring import score_riasec

SERVICE_ROOT = Path(__file__).resolve().parents[1]


def _percentile(values: list[float], quantile: float) -> float:
    ordered = sorted(values)
    index = min(round((len(ordered) - 1) * quantile), len(ordered) - 1)
    return ordered[index]


def _measure(operation: Callable[[], object], repetitions: int) -> dict[str, float | int]:
    warmup = min(10, repetitions)
    for _ in range(warmup):
        operation()
    measurements: list[float] = []
    for _ in range(repetitions):
        started = perf_counter()
        operation()
        measurements.append((perf_counter() - started) * 1_000)
    return {
        "repetitions": repetitions,
        "warmup_iterations": warmup,
        "median_ms": round(median(measurements), 4),
        "p95_ms": round(_percentile(measurements, 0.95), 4),
    }


def main() -> None:
    parser = argparse.ArgumentParser(description="Benchmark reproducible del análisis V2.")
    parser.add_argument("--repetitions", type=int, default=200)
    parser.add_argument(
        "--output",
        type=Path,
        default=SERVICE_ROOT / "data/evaluation/analysis_v2_phase2_performance.json",
    )
    args = parser.parse_args()
    if args.repetitions < 20:
        raise ValueError("Se requieren al menos 20 repeticiones para reportar p95")

    fixture = SERVICE_ROOT / "data" / "fixtures" / "complete_profile.json"
    payload = cast(dict[str, object], json.loads(fixture.read_text(encoding="utf-8")))
    for metadata in ("fixture_type", "seed", "scenario_note"):
        payload.pop(metadata, None)
    payload["schema_version"] = "2.0"
    request = AnalysisV2Request.model_validate(payload)
    vocational = score_riasec(request.vocational) if request.vocational else None
    academic = (
        build_academic_profile(request.academic, request.attendance)
        if request.academic
        else None
    )
    results = {
        "validation_latency": _measure(
            lambda: AnalysisV2Request.model_validate(payload), args.repetitions
        ),
        "riasec_latency": _measure(
            lambda: score_riasec(request.vocational) if request.vocational else None,
            args.repetitions,
        ),
        "learning_analytics_latency": _measure(
            lambda: (
                build_academic_profile(request.academic, request.attendance)
                if request.academic
                else None
            ),
            args.repetitions,
        ),
        "recommendation_v2_latency": _measure(
            lambda: build_career_evidence_profiles(request, vocational, academic),
            args.repetitions,
        ),
        "complete_analysis_latency": _measure(
            lambda: analyze_student_v2(request, "benchmark-v2"), args.repetitions
        ),
    }
    output = {
        "benchmark": "ANALYSIS_V2_LOCAL_NO_EXTERNAL_SERVICES",
        "measured_at": datetime.now(UTC).isoformat(),
        "environment": {
            "python": platform.python_version(),
            "platform": platform.platform(),
            "processor": platform.processor() or "not_reported_by_platform",
            "logical_cpu_count": os.cpu_count(),
        },
        "fixture_classification": "SYNTHETIC_TEST_FIXTURE",
        "timing_clock": "time.perf_counter",
        "results": results,
        "limitations": ["Proceso caliente local; sin HTTP, retrieval, tutor ni concurrencia."],
    }
    args.output.write_text(
        json.dumps(output, ensure_ascii=False, indent=2) + "\n", encoding="utf-8"
    )
    print(json.dumps(output, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
