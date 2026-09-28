from fastapi.testclient import TestClient


def test_validation_error_has_stable_envelope(client: TestClient) -> None:
    response = client.post("/api/v1/analysis", json={"schema_version": "1.0"})
    assert response.status_code == 422
    body = response.json()
    assert body["error"]["code"] == "SAVP_AI_INVALID_REQUEST"
    assert body["error"]["trace_id"] == response.headers["x-trace-id"]
    assert "traceback" not in response.text.lower()


def test_unsupported_schema_has_specific_code(client: TestClient) -> None:
    response = client.post(
        "/api/v1/analysis",
        json={"schema_version": "9.9", "student_id": "EST-001"},
    )
    assert response.status_code == 422
    assert response.json()["error"]["code"] == "SAVP_AI_UNSUPPORTED_SCHEMA_VERSION"

