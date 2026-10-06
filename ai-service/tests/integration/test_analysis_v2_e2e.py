from typing import Any

from fastapi.testclient import TestClient


def test_analysis_v2_fixture_to_http_response(
    client: TestClient,
    load_fixture: Any,
) -> None:
    payload = load_fixture("complete_profile.json")
    payload["schema_version"] = "2.0"
    response = client.post("/api/v2/analysis", json=payload)
    assert response.status_code == 200
    body = response.json()
    assert body["schema_version"] == "2.0"
    assert body["engine_version"] == "2.0.0"
    assert body["criteria_version"] == "v2_evidence_based"
    assert body["analysis_status"] == "COMPLETE"
    assert len(body["traceability"]["input_hash"]) == 64
    assert body["traceability"]["catalog_version"] == "bo-careers-1.1.0"
    assert body["traceability"]["instrument_version"] == "2.0-es-2025"
    assert body["traceability"]["bridge_version"] is not None
    assert body["traceability"]["crosswalk_version"] == "career-occupation-v1.0.0"
    assert body["student_snapshot"]["vocational_interest_evidence"]["riasec"]
    assert body["student_snapshot"]["academic_evidence"]["summary"]["mean"] == 77
    assert body["career_evidence_profiles"]
    assert len(body["career_evidence_profiles"]) == 7
    assert {item["career_id"] for item in body["informational_external_careers"]} == {
        "BO-EMI-LP-ING-SISTEMAS"
    }
    assert body["informational_external_careers"][0]["recommendation_eligible"] is False
    by_career = {item["career_id"]: item for item in body["career_evidence_profiles"]}
    systems_program = by_career["BO-UCB-LP-ING-SISTEMAS"]["academic_program"]
    assert systems_program["professional_profile"]
    assert systems_program["knowledge_areas"]
    assert systems_program["documented_subjects"]
    assert systems_program["curriculum_scope"] == "DOCUMENTED_INITIAL_SUBJECTS"
    assert systems_program["sources"][0]["source_type"] == "OFFICIAL_CURRICULUM_PDF"
    assert (
        by_career["BO-UNIFRANZ-LP-ING-SISTEMAS-INNOVACION-DIGITAL"]["riasec_reference_status"]
        == "UNAVAILABLE_PENDING_OCCUPATIONAL_CROSSWALK"
    )
    assert (
        by_career["BO-UPB-LP-ING-SISTEMAS-COMPUTACIONALES"]["riasec_reference_status"]
        == "UNAVAILABLE_PENDING_OCCUPATIONAL_CROSSWALK"
    )
    assert "compatibility_score" not in response.text


def test_analysis_v2_minimal_profile_preserves_unknowns(client: TestClient) -> None:
    response = client.post(
        "/api/v2/analysis",
        json={"schema_version": "2.0", "student_id": "SYN-V2-EMPTY"},
    )
    assert response.status_code == 200
    body = response.json()
    assert body["analysis_status"] == "INSUFFICIENT"
    assert body["student_snapshot"]["academic_evidence"]["summary"] is None
    assert body["student_snapshot"]["attendance_evidence"]["attendance_ratio"] is None
    assert "academic" in body["student_snapshot"]["missing_components"]


def test_analysis_v2_rejects_extra_fields(client: TestClient) -> None:
    response = client.post(
        "/api/v2/analysis",
        json={
            "schema_version": "2.0",
            "student_id": "SYN-V2-STRICT",
            "compatibility_score": 99,
        },
    )
    assert response.status_code == 422
    assert response.json()["error"]["code"] == "SAVP_AI_INVALID_REQUEST"
