import socket

import pytest
from fastapi.testclient import TestClient


def test_tutor_and_real_local_corpus_work_without_external_network(
    client: TestClient, monkeypatch: pytest.MonkeyPatch
) -> None:
    def network_forbidden(*args: object, **kwargs: object) -> None:
        pytest.fail("El tutor local no debe consultar internet ni modelos externos.")

    monkeypatch.setattr(socket, "create_connection", network_forbidden)
    monkeypatch.setattr(socket, "getaddrinfo", network_forbidden)
    greeting = client.post(
        "/api/v1/tutor/query", json={"schema_version": "1.0", "question": "Hola"}
    )
    assert greeting.status_code == 200
    assert greeting.json()["answer"]
    assert greeting.json()["provider_version"] == "structured-answer-v3.0.0"
    response = client.post(
        "/api/v1/knowledge/search",
        json={
            "schema_version": "1.0",
            "query": "Ingeniería de Sistemas UPB perfil profesional",
            "institution": "UPB",
            "top_k": 3,
        },
    )
    assert response.status_code == 200
    assert response.json()["embedding_model"] == "NONE"
    assert response.json()["results"]
    assert any("UPB" in item["source_id"] for item in response.json()["results"])
