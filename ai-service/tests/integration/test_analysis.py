from typing import Any

from fastapi.testclient import TestClient


def test_minimal_profile_is_insufficient_not_zero(client: TestClient) -> None:
    response = client.post(
        "/api/v1/analysis",
        json={"schema_version": "1.0", "student_id": "EST-001"},
    )
    assert response.status_code == 200
    body = response.json()
    assert body["status"] == "INSUFFICIENT"
    assert body["academic_profile"] is None
    assert body["vocational_profile"] is None
    assert body["coverage"]["ratio"] == 0


def test_complete_fixture_keeps_affinity_and_academics_separate(
    client: TestClient,
    load_fixture: Any,
) -> None:
    response = client.post("/api/v1/analysis", json=load_fixture("complete_profile.json"))
    assert response.status_code == 200
    body = response.json()
    assert body["status"] == "COMPLETE"
    assert body["vocational_profile"]["scores"] == {
        "R": 15,
        "I": 20,
        "A": 10,
        "S": 20,
        "E": 10,
        "C": 15,
    }
    assert body["vocational_profile"]["top_codes"] == ["I", "S"]
    assert body["academic_profile"]["subjects"]["Matemática"]["mean"] == 71
    assert body["academic_profile"]["subjects"]["Matemática"]["trend_slope"] == 6
    assert body["academic_profile"]["areas"]["Ciencia y Tecnología"]["mean"] == 71
    assert body["academic_profile"]["overall_trend_slope"] == 4
    assert body["vocational_profile"]["normalized_scores"]["I"] == 100
    assert body["vocational_profile"]["coverage_ratio"] == 1
    assert body["learning_activity_profile"]["completion_ratio"] == 0.75
    assert body["learning_activity_profile"]["mean_grade"] == 81.67
    assert body["ranking_status"] == "RANKED"
    assert body["career_ranking"][0]["career_id"] == "BO-UCB-LP-ING-SISTEMAS"
    assert body["career_ranking"][0]["criteria_version"] == "v1_experimental"
    assert body["coverage"]["ratio"] == 1
    assert len(body["input_hash"]) == 64


def test_partial_fixture_reports_missing_vocational_data(
    client: TestClient,
    load_fixture: Any,
) -> None:
    response = client.post(
        "/api/v1/analysis",
        json=load_fixture("partial_without_riasec.json"),
    )
    assert response.status_code == 200
    body = response.json()
    assert body["status"] == "PARTIAL"
    assert body["vocational_profile"] is None
    assert "vocational" in body["missing_components"]


def test_incomplete_instrument_is_422(client: TestClient) -> None:
    response = client.post(
        "/api/v1/analysis",
        json={
            "schema_version": "1.0",
            "student_id": "EST-001",
            "vocational": {
                "instrument_id": "onet-mini-ip-2.0-es",
                "instrument_version": "2.0-es-2025",
                "responses": [{"item_id": 1, "value": 4}],
            },
        },
    )
    assert response.status_code == 422
    assert response.json()["error"]["code"] == "SAVP_AI_INCOMPLETE_INSTRUMENT"


def test_out_of_range_riasec_is_invalid_request(client: TestClient) -> None:
    response = client.post(
        "/api/v1/analysis",
        json={
            "schema_version": "1.0",
            "student_id": "EST-001",
            "vocational": {
                "instrument_id": "onet-mini-ip-2.0-es",
                "instrument_version": "2.0-es-2025",
                "responses": [{"item_id": 1, "value": 5}],
            },
        },
    )
    assert response.status_code == 422
    assert response.json()["error"]["code"] == "SAVP_AI_INVALID_REQUEST"
