"""Measure FastAPI app initialization in a fresh Python process."""

import json
import platform
import subprocess
import sys
from datetime import UTC, datetime
from pathlib import Path
from time import perf_counter

ROOT = Path(__file__).resolve().parents[1]
OUTPUT = ROOT / "data/evaluation/cold_start_results.json"


def main() -> int:
    start = perf_counter()
    error = None
    try:
        result = subprocess.run(
            [
                sys.executable,
                "-c",
                "from app.main import app; assert app is not None",
            ],
            cwd=ROOT,
            capture_output=True,
            text=True,
            timeout=30,
            check=False,
        )
        returncode = result.returncode
        if returncode:
            error = result.stderr[-2000:]
    except subprocess.TimeoutExpired:
        returncode = 124
        error = "TIMEOUT_30_SECONDS"
    report = {
        "measured_at": datetime.now(UTC).isoformat(),
        "scope": "FRESH_PROCESS_FASTAPI_APP_INITIALIZATION",
        "python": platform.python_version(),
        "platform": platform.platform(),
        "seconds": round(perf_counter() - start, 3),
        "status": "PASS" if returncode == 0 else "FAIL",
        "error": error,
        "limitations": [
            "Una medición; incluye inicio de Python e importación de la aplicación.",
            "No carga el modelo de embeddings, índice ni LLM.",
            "Separada del benchmark de proceso caliente.",
        ],
    }
    OUTPUT.write_text(json.dumps(report, ensure_ascii=False, indent=2) + "\n", encoding="utf-8")
    print(json.dumps(report, ensure_ascii=False, indent=2))
    return returncode


if __name__ == "__main__":
    raise SystemExit(main())
