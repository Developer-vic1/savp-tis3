import json
import platform
import statistics
import subprocess
import sys
from importlib.metadata import version
from pathlib import Path
from time import perf_counter
from typing import Any

import psutil

from app.contracts.requests import AnalysisRequest
from app.domain.student_profile import analyze_student

ROOT = Path(__file__).resolve().parents[1]
SAMPLE_SIZE = 1_000


def percentile(values: list[float], quantile: float) -> float:
    ordered = sorted(values)
    index = min(round((len(ordered) - 1) * quantile), len(ordered) - 1)
    return ordered[index]


def cold_import_times(samples: int = 5) -> list[float]:
    command = [sys.executable, "-c", "from app.main import app; assert app"]
    measurements = []
    for _ in range(samples):
        started = perf_counter()
        subprocess.run(command, cwd=ROOT, check=True, capture_output=True)
        measurements.append((perf_counter() - started) * 1_000)
    return measurements


def main() -> None:
    fixture_path = ROOT / "data" / "fixtures" / "complete_profile.json"
    with fixture_path.open(encoding="utf-8") as handle:
        raw: dict[str, Any] = json.load(handle)
    for metadata_key in ("fixture_type", "seed", "scenario_note"):
        raw.pop(metadata_key, None)
    request = AnalysisRequest.model_validate(raw)

    for _ in range(20):
        analyze_student(request, "benchmark-warmup")

    process = psutil.Process()
    rss_before = process.memory_info().rss
    latencies = []
    for _ in range(SAMPLE_SIZE):
        started = perf_counter()
        analyze_student(request, "benchmark")
        latencies.append((perf_counter() - started) * 1_000)
    rss_after = process.memory_info().rss
    cold_import = cold_import_times()

    result = {
        "date": "2026-09-28",
        "hardware": {
            "logical_cpu_count": psutil.cpu_count(logical=True),
            "physical_cpu_count": psutil.cpu_count(logical=False),
            "total_ram_bytes": psutil.virtual_memory().total,
        },
        "os": platform.platform(),
        "python": platform.python_version(),
        "versions": {
            "fastapi": version("fastapi"),
            "pydantic": version("pydantic"),
            "psutil": version("psutil"),
        },
        "dataset": "data/fixtures/complete_profile.json (SYNTHETIC)",
        "criteria_version": "core-criteria-0.1.0",
        "sample_size": SAMPLE_SIZE,
        "command": ".venv/Scripts/uv run python scripts/benchmark.py",
        "results": {
            "analysis_latency_ms": {
                "mean": round(statistics.fmean(latencies), 4),
                "median": round(statistics.median(latencies), 4),
                "p95": round(percentile(latencies, 0.95), 4),
                "maximum": round(max(latencies), 4),
            },
            "cold_app_import_ms": {
                "samples": len(cold_import),
                "mean": round(statistics.fmean(cold_import), 4),
                "minimum": round(min(cold_import), 4),
                "maximum": round(max(cold_import), 4),
            },
            "rss_before_bytes": rss_before,
            "rss_after_bytes": rss_after,
            "rss_delta_bytes": rss_after - rss_before,
        },
    }
    print(json.dumps(result, indent=2))


if __name__ == "__main__":
    main()

