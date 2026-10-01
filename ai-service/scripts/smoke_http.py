"""Exercise the real local ASGI server and a small concurrent request burst."""

import json
import os
import socket
import subprocess
import sys
from collections.abc import Callable
from concurrent.futures import ThreadPoolExecutor
from datetime import UTC, datetime
from pathlib import Path
from statistics import median
from time import monotonic, perf_counter, sleep
from typing import Any

import httpx

ROOT = Path(__file__).resolve().parents[1]


def free_port() -> int:
    with socket.socket() as listener:
        listener.bind(("127.0.0.1", 0))
        return int(listener.getsockname()[1])


def run_smoke(base: str) -> dict[str, Any]:
    key = os.getenv("SAVP_AI_API_KEY")
    headers = {"X-SAVP-AI-Key": key} if key else {}
    fixture = json.loads((ROOT / "data/fixtures/complete_profile.json").read_text(encoding="utf-8"))
    for metadata in ("fixture_type", "seed", "scenario_note"):
        fixture.pop(metadata, None)
    fixture["schema_version"] = "2.0"
    with httpx.Client(base_url=base, headers=headers, timeout=30.0) as client:
        def probe(request: Callable[[], httpx.Response]) -> tuple[httpx.Response, dict[str, Any]]:
            started = perf_counter()
            response = request()
            return response, {
                "status": response.status_code,
                "latency_ms": round((perf_counter() - started) * 1000, 3),
                "trace_id": response.headers.get("X-Trace-Id"),
            }

        instrument, instrument_result = probe(
            lambda: client.get("/api/v2/riasec/instrument")
        )
        if instrument.status_code != 200:
            raise RuntimeError(f"RIASEC instrument: HTTP {instrument.status_code}")
        body = instrument.json()
        score_payload = {
            "instrument_version": body["instrument_version"],
            "responses": [{"item_id": item["item_id"], "value": 1} for item in body["items"]],
        }
        results = {"riasec_instrument": instrument_result}
        _, results["health"] = probe(lambda: client.get("/health"))
        _, results["riasec_score"] = probe(
            lambda: client.post("/api/v2/riasec/score", json=score_payload)
        )
        _, results["analysis_v2"] = probe(lambda: client.post("/api/v2/analysis", json=fixture))
        _, results["knowledge"] = probe(
            lambda: client.post(
                "/api/v1/knowledge/search",
                json={"schema_version": "1.0", "query": "materias iniciales de Ingeniería Civil"},
            )
        )
        _, results["tutor"] = probe(
            lambda: client.post(
                "/api/v1/tutor/query",
                json={"schema_version": "1.0", "question": "¿Qué materias iniciales hay?"},
            )
        )

    def concurrent_score(_: int) -> tuple[int, float]:
        with httpx.Client(base_url=base, headers=headers, timeout=10.0) as client:
            started = perf_counter()
            status = client.post("/api/v2/riasec/score", json=score_payload).status_code
            return status, (perf_counter() - started) * 1000

    with ThreadPoolExecutor(max_workers=4) as pool:
        concurrent = list(pool.map(concurrent_score, range(8)))
    results["concurrent_riasec"] = {
        "requests": len(concurrent),
        "passed": sum(status == 200 for status, _ in concurrent),
        "median_ms": round(median(latency for _, latency in concurrent), 3),
    }
    return results


def main() -> int:
    port = free_port()
    base = f"http://127.0.0.1:{port}"
    process = subprocess.Popen(
        [
            sys.executable, "-m", "uvicorn", "app.main:app",
            "--host", "127.0.0.1", "--port", str(port),
        ],
        cwd=ROOT,
        stdout=subprocess.DEVNULL,
        stderr=subprocess.DEVNULL,
        creationflags=getattr(subprocess, "CREATE_NO_WINDOW", 0),
    )
    try:
        deadline = monotonic() + 20
        while monotonic() < deadline:
            if process.poll() is not None:
                raise RuntimeError("FastAPI terminó antes de responder /health")
            try:
                if httpx.get(base + "/health", timeout=1).status_code == 200:
                    break
            except httpx.HTTPError:
                sleep(0.2)
        else:
            raise RuntimeError("FastAPI no respondió /health en 20 s")
        results = run_smoke(base)
        passed = all(
            value["status"] == 200
            for name, value in results.items()
            if name != "concurrent_riasec"
        ) and results["concurrent_riasec"]["passed"] == 8
        report = {
            "measured_at": datetime.now(UTC).isoformat(),
            "environment": "local FastAPI on Windows; 8 RIASEC requests, 4 workers",
            "results": results,
            "status": "PASS" if passed else "FAIL",
        }
        output = ROOT / "data/evaluation/http_smoke_phase21.json"
        output.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
        print(json.dumps(report, ensure_ascii=False, sort_keys=True))
        return 0 if passed else 1
    finally:
        process.terminate()
        try:
            process.wait(timeout=5)
        except subprocess.TimeoutExpired:
            process.kill()
            process.wait(timeout=5)


if __name__ == "__main__":
    raise SystemExit(main())
