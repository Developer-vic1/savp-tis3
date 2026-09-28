from fastapi.testclient import TestClient


def test_health_contract_is_exact(client: TestClient) -> None:
    response = client.get("/health")
    assert response.status_code == 200
    assert response.json() == {
        "status": "ok",
        "service": "savp-ai",
        "service_version": "0.1.0",
        "schema_version": "1.0",
    }
    assert "x-trace-id" in response.headers

