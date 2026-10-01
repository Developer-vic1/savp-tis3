from typing import Any

import pytest
from fastapi.testclient import TestClient

from app.riasec.instrument import load_instrument


def public() -> dict[str, Any]:
    return {
        "instrument_version": load_instrument()["version"],
        "responses": [{"item_id": item, "value": item % 5 + 1} for item in range(1, 31)],
    }


def test_analysis_uses_same_official_scoring_as_public_endpoint(client: TestClient) -> None:
    data = public()
    score = client.post("/api/v2/riasec/score", json=data)
    response = client.post(
        "/api/v2/analysis",
        json={"schema_version": "2.0", "student_id": "pseudonym", "riasec_public": data},
    )
    assert response.status_code == score.status_code == 200
    result = response.json()
    riasec = result["student_snapshot"]["vocational_interest_evidence"]["riasec"]
    assert riasec["scores"] == score.json()["scores"]
    assert riasec["holland_code"] == score.json()["holland_code"]
    assert result["traceability"]["instrument_version"] == data["instrument_version"]
    assert result["trace_id"] == response.headers["x-trace-id"]
    assert float(response.headers["server-timing"].split("=")[1]) >= 0
    assert result["student_snapshot"]["academic_evidence"]["summary"] is None


@pytest.mark.parametrize("error", ["version", "duplicate", "scale", "ambiguous"])
def test_public_analysis_rejects_invalid_or_ambiguous_input(
    client: TestClient, error: str
) -> None:
    data = public()
    request: dict[str, Any] = {"student_id": "pseudonym", "riasec_public": data}
    if error == "version":
        data["instrument_version"] = "unsupported"
    elif error == "duplicate":
        data["responses"][-1]["item_id"] = 1
    elif error == "scale":
        data["responses"][0]["value"] = 0
    else:
        request["vocational"] = {
            "instrument_id": load_instrument()["instrument_id"],
            "instrument_version": data["instrument_version"],
            "responses": [{"item_id": i, "value": 0} for i in range(1, 31)],
        }
    assert client.post("/api/v2/analysis", json=request).status_code == 422
