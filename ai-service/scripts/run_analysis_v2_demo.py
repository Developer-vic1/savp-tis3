import json
from datetime import UTC, datetime
from pathlib import Path
from typing import cast

from app.contracts.v2 import AnalysisV2Request
from app.domain.student_profile_v2 import analyze_student_v2

SERVICE_ROOT = Path(__file__).resolve().parents[1]


def main() -> None:
    fixture = SERVICE_ROOT / "data" / "fixtures" / "complete_profile.json"
    payload = cast(dict[str, object], json.loads(fixture.read_text(encoding="utf-8")))
    for metadata in ("fixture_type", "seed", "scenario_note"):
        payload.pop(metadata, None)
    payload["schema_version"] = "2.0"
    request = AnalysisV2Request.model_validate(payload)
    result = analyze_student_v2(
        request,
        "demo-analysis-v2",
        generated_at=datetime(2026, 9, 29, tzinfo=UTC),
    )
    print(json.dumps(result.model_dump(mode="json"), ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
