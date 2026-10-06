import pytest
from fastapi.testclient import TestClient


def test_preview_returns_real_duplicate_without_network(client: TestClient) -> None:
    overview = client.get("/api/v1/knowledge/governance").json()
    source = next(item for item in overview["sources"] if "www.upb.edu/" in item["url"])
    response = client.post("/api/v1/knowledge/governance/preview", json={"url": source["url"]})
    assert response.status_code == 200
    assert response.json()["status"] == "DUPLICADA"
    assert response.json()["can_use"] is False
    assert response.json()["duplicate"]["id"] == source["source_id"]
    assert response.headers["Cache-Control"] == "no-store"


def test_preview_cannot_accept_forged_metadata(client: TestClient) -> None:
    response = client.post(
        "/api/v1/knowledge/governance/preview",
        json={"url": "https://www.upb.edu/", "can_use": True},
    )
    assert response.status_code == 422


def test_body_limit_applies_without_content_length(client: TestClient) -> None:
    response = client.post(
        "/api/v1/knowledge/governance/analyze",
        content=iter([b"x" * 40_000, b"x" * 30_000]),
        headers={"Content-Type": "application/json"},
    )
    assert response.status_code == 413
    assert "content-length" not in response.request.headers
    assert response.headers["Cache-Control"] == "no-store"
    assert response.headers["X-Trace-Id"]


def _candidate(**overrides: object) -> dict[str, object]:
    return {
        "title": "Plan curricular de Ingeniería de Sistemas",
        "declared_institution": "UPB",
        "url": "https://nueva.upb.edu/documentos/sistemas-2027.pdf",
        "source_type": "OFFICIAL_CURRICULUM_PDF",
        "scope": "Malla curricular de Ingeniería de Sistemas.",
        "publication_date": "2027",
        "version": "Gestión 2027",
        "campus": "La Paz",
        "city": "La Paz",
        "justification": "Amplía la comparación de materias y la preparación académica.",
        "limitations": [],
    } | overrides


def test_governance_exposes_only_staged_source_workflow(client: TestClient) -> None:
    response = client.get("/api/v1/knowledge/governance")

    assert response.status_code == 200
    body = response.json()
    assert body["source_count"] >= 1
    assert "ucb.edu.bo" in {item["domain"] for item in body["trusted_universities"]}
    assert "instantánea local" in " ".join(body["workflow"])


def test_governance_rejects_california_source_without_creating_proposal(
    client: TestClient,
) -> None:
    response = client.post(
        "/api/v1/knowledge/governance/proposals",
        json=_candidate(
            title="Computer Science curriculum",
            declared_institution="University of California",
            url="https://www.berkeley.edu/academics/computer-science",
            source_type="OFFICIAL_CAREER_HTML",
            scope="Programa académico de ciencias de la computación.",
            campus="Berkeley",
            city="California",
            justification="Se propone para comparar el programa con carreras de Sistemas.",
        )
        | {"submitted_by_role": "Director"},
    )

    assert response.status_code == 200
    body = response.json()
    assert body["accepted"] is False
    assert body["proposal"] is None
    assert body["assessment"]["status"] == "RECHAZADA"


def test_governance_analyzes_a_complete_bolivian_source(client: TestClient) -> None:
    response = client.post(
        "/api/v1/knowledge/governance/analyze",
        json=_candidate(),
    )

    assert response.status_code == 200
    body = response.json()
    assert body["can_submit"] is True
    assert body["readiness"] == "LISTA_PARA_PROPONER"
    assert all(check["status"] == "CUMPLE" for check in body["checks"])


@pytest.mark.parametrize(
    "url",
    [
        "https://www.upb.edu:invalid/plan.pdf",
        "https://[::1/plan.pdf",
        "https://www.upb.edu/%0d%0aHost:localhost",
    ],
)
def test_malformed_url_is_blocked_instead_of_causing_a_server_error(
    client: TestClient, url: str
) -> None:
    response = client.post("/api/v1/knowledge/governance/analyze", json=_candidate(url=url))
    assert response.status_code == 200
    assert response.json()["can_submit"] is False
    assert response.json()["readiness"] == "BLOQUEADA"


def test_governance_rejects_oversized_fields(client: TestClient) -> None:
    response = client.post(
        "/api/v1/knowledge/governance/analyze",
        json=_candidate(limitations=["x" * 501]),
    )

    assert response.status_code == 422
    assert response.json()["error"]["code"] == "SAVP_AI_INVALID_REQUEST"


def test_api_rejects_oversized_request_and_sets_security_headers(
    client: TestClient,
) -> None:
    response = client.post(
        "/api/v1/knowledge/governance/analyze",
        content=b"x" * 65_537,
        headers={"Content-Type": "application/json"},
    )

    assert response.status_code == 413
    assert response.headers["cache-control"] == "no-store"
    assert response.headers["x-content-type-options"] == "nosniff"
    assert response.headers["x-frame-options"] == "DENY"


def test_interactive_api_documentation_is_not_exposed(client: TestClient) -> None:
    assert client.get("/docs").status_code == 404
    assert client.get("/openapi.json").status_code == 404
