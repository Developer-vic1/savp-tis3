from fastapi.testclient import TestClient


def test_tutor_compares_system_needs_with_real_multiversity_sources(
    client: TestClient,
) -> None:
    response = client.post(
        "/api/v1/tutor/query",
        json={
            "schema_version": "1.0",
            "question": "Compara Sistemas UCB UPB UNIFRANZ materias primer semestre",
        },
    )

    assert response.status_code == 200
    body = response.json()
    assert body["insufficient_evidence"] is False
    assert "Mapa documental de preparación" in body["answer"]
    assert "BO-UCB-LP-SIS-MALLA-2026" in body["answer"]
    assert "BO-UPB-LP-SISC-MALLA-2026" in body["answer"]
    assert "no las inferiré" in body["answer"]
    assert {
        "BO-UCB-LP-SIS-MALLA-2026",
        "BO-UPB-LP-SISC-MALLA-2026",
        "BO-UNIFRANZ-LP-SISID-MALLA-2026",
    } <= {source["source_id"] for source in body["sources"]}
