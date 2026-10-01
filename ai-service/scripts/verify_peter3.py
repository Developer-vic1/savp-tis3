"""Run the reproducible local PETER 3 gates; exit nonzero on any failure."""

import subprocess
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]

GATES = (
    ("source and corpus provenance", [sys.executable, "scripts/verify_sources.py"]),
    ("retrieval DEV/freeze/TEST", [sys.executable, "scripts/verify_retrieval_phase21.py"]),
    (
        "RIASEC contract",
        [sys.executable, "-m", "pytest", "tests/contract/test_riasec_v2.py", "--no-cov"],
    ),
    ("Recommendation V2 invariants", [sys.executable, "scripts/evaluate_recommendation_v2.py"]),
    ("prompt scenarios", [sys.executable, "scripts/evaluate_prompts.py"]),
    ("pytest, including E2E and HTTP contracts", [sys.executable, "-m", "pytest"]),
    ("real HTTP smoke", [sys.executable, "scripts/smoke_http.py"]),
    ("ruff", [sys.executable, "-m", "ruff", "check", "."]),
    ("mypy production and scripts", [sys.executable, "-m", "mypy", "app", "scripts"]),
    ("mypy repository", [sys.executable, "-m", "mypy", "."]),
    ("git diff --check", ["git", "diff", "--check"]),
)


def main() -> int:
    failures: list[str] = []
    for name, command in GATES:
        print(f"\n=== {name} ===", flush=True)
        try:
            result = subprocess.run(command, cwd=ROOT, check=False, timeout=300)
            failed = result.returncode != 0
        except subprocess.TimeoutExpired:
            print(f"{name}: TIMEOUT after 300 s", flush=True)
            failed = True
        print(f"{name}: {'FAIL' if failed else 'PASS'}", flush=True)
        if failed:
            failures.append(name)
    if failures:
        print(f"FAIL: {', '.join(failures)}")
        return 1
    print("PASS")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
