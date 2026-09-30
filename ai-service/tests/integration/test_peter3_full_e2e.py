import json
from pathlib import Path
from typing import Any, cast

from fastapi.testclient import TestClient

SERVICE_ROOT = Path(__file__).resolve().parents[2]


def _complete_profile() -> dict[str, Any]:
    raw = json.loads(
        (SERVICE_ROOT / "data" / "fixtures" / "complete_profile.json").read_text(
            encoding="utf-8"
        )
    )
    payload = cast(dict[str, Any], raw)
    for metadata in ("fixture_type", "seed", "scenario_note"):
        payload.pop(metadata, None)
    payload["schema_version"] = "2.0"
    return payload


def test_peter3_full_e2e_with_real_corpus_and_selected_index(client: TestClient) -> None:
    analysis_response = client.post("/api/v2/analysis", json=_complete_profile())
    assert analysis_response.status_code == 200
    analysis = analysis_response.json()

    assert analysis["analysis_status"] == "COMPLETE"
    assert analysis["student_snapshot"]["vocational_interest_evidence"]["riasec"]
    assert analysis["student_snapshot"]["academic_evidence"]["summary"]
    assert analysis["student_snapshot"]["technical_evidence"]["status"] == "AVAILABLE"
    assert analysis["student_snapshot"]["evidence_quality"]["academic_record_count"] > 0
    assert analysis["student_snapshot"]["evidence_quality"]["riasec_complete"] is True
    assert len(analysis["career_evidence_profiles"]) == 5
    assert analysis["sources_used"]
    assert analysis["traceability"]["catalog_version"] == "bo-careers-1.0.0"
    assert analysis["traceability"]["crosswalk_version"] == "career-occupation-v1.0.0"

    query = (
        "materias primer semestre Ingeniería de Sistemas UCB Álgebra Lineal "
        "Matemáticas Discretas"
    )
    knowledge_response = client.post(
        "/api/v1/knowledge/search",
        json={"schema_version": "1.0", "query": query, "top_k": 5},
    )
    assert knowledge_response.status_code == 200
    knowledge = knowledge_response.json()
    assert knowledge["insufficient_evidence"] is False
    assert knowledge["results"]
    assert knowledge["results"][0]["source_id"] == "BO-UCB-LP-SIS-MALLA-2026"
    assert knowledge["embedding_model"] == "intfloat/multilingual-e5-small"

    tutor_response = client.post(
        "/api/v1/tutor/query",
        json={
            "schema_version": "1.0",
            "question": query,
            "subject": "Ingeniería de Sistemas",
            "level": "primer semestre",
            "academic_context": {
                "course": "6to Secundaria",
                "areas_to_reinforce": ["Matemática"],
            },
        },
    )
    assert tutor_response.status_code == 200
    tutor = tutor_response.json()
    assert tutor["answer_mode"] == "STRUCTURED"
    assert tutor["insufficient_evidence"] is False
    assert tutor["sources"]
    assert "BO-UCB-LP-SIS-MALLA-2026" in tutor["answer"]
    assert tutor["corpus_version"] == knowledge["corpus_version"]
    assert tutor["embedding_model"] == knowledge["embedding_model"]

    trace_ids = {
        analysis["trace_id"],
        knowledge["trace_id"],
        tutor["trace_id"],
    }
    assert len(trace_ids) == 3
    assert all(len(trace_id) == 36 for trace_id in trace_ids)
