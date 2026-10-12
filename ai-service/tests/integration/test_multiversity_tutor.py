from fastapi.testclient import TestClient


def test_tutor_does_not_assign_initial_catalog_subjects_to_first_semester_without_evidence(
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
    # La UCB sí imprime semestre + número y filas de asignatura, aun con corpus aplanado.
    assert "BO-UCB-LP-SIS-MALLA-2026" in body["answer"]
    assert "Mapa documental de preparación" not in body["answer"]
    assert all(source["source_id"] == "BO-UCB-LP-SIS-MALLA-2026" for source in body["sources"])


def test_tutor_endpoint_lists_official_unifranz_offer_without_claiming_campus(
    client: TestClient,
) -> None:
    response = client.post(
        "/api/v1/tutor/query",
        json={
            "schema_version": "1.0",
            "question": (
                "Hola quiero saber respecto a la universidad privada franz tamayo, "
                "que es lo que tienes, que carreras contiene, solo dime las carreras"
            ),
        },
    )
    assert response.status_code == 200
    body = response.json()
    assert body["insufficient_evidence"] is False
    assert [source["source_id"] for source in body["sources"]] == ["BO-UNIFRANZ-OFFER-INDEX"]
    assert body["sources"][0]["reference"] == "https://unifranz.edu.bo/carreras/"
    assert "17 carreras" in body["answer"]
    assert "Psicología" in body["answer"]
    assert "Medicina" in body["answer"]
    assert "Derecho" in body["answer"]
    assert "La oferta por sede" in body["answer"]
    assert "UPB" not in body["answer"]


def test_tutor_endpoint_answers_which_institutions_are_under_web_review(client: TestClient) -> None:
    response = client.post(
        "/api/v1/tutor/query",
        json={"schema_version": "1.0", "question": "qué universidades tenemos en revisión"},
    )
    assert response.status_code == 200
    body = response.json()
    assert body["insufficient_evidence"] is False
    assert body["sources"] == []
    assert "9 instituciones" in body["answer"]
    assert "Universidad Bethesda" in body["answer"]
    assert "Universidad Boliviana de Informática" in body["answer"]
    assert "Universidad Bethesda — Santa Cruz" in body["answer"]
    assert "Universidad Boliviana de Informática — Chuquisaca, La Paz" in body["answer"]
    assert "malla" not in body["answer"]


def test_tutor_counts_the_same_national_registry_as_the_directory(client: TestClient) -> None:
    directory = client.get("/api/v1/knowledge/national-directory").json()
    response = client.post(
        "/api/v1/tutor/query",
        json={
            "schema_version": "1.0",
            "question": "cuantas universidades tenemos registradas",
            "academic_context": {"institutional_university_count": 5},
        },
    )
    assert response.status_code == 200
    body = response.json()
    assert body["insufficient_evidence"] is False
    assert f"{directory['statistics']['institutions']} instituciones" in body["answer"]
    assert "La Paz (29)" in body["answer"]
    assert "Santa Cruz (21)" in body["answer"]
    assert "5 universidades activas con sede registrada" in body["answer"]
    assert "no confirma cuántas están habilitadas actualmente" in body["answer"]
    assert [source["source_id"] for source in body["sources"]] == ["minedu_guia_2016"]
    assert all("Psicología" not in source["title"] for source in body["sources"])


def test_tutor_lists_registered_institutions_with_departments(client: TestClient) -> None:
    response = client.post(
        "/api/v1/tutor/query",
        json={"schema_version": "1.0", "question": "qué universidades tenemos registradas"},
    )
    assert response.status_code == 200
    answer = response.json()["answer"]
    assert "- Universidad Mayor de San Andrés — La Paz" in answer
    assert "- Universidad Bethesda — Santa Cruz" in answer


def test_tutor_endpoint_lists_visual_checked_unifranz_malla_with_page_links(
    client: TestClient,
) -> None:
    response = client.post(
        "/api/v1/tutor/query",
        json={
            "schema_version": "1.0",
            "question": "materias de unifranz en ingenieria de sistemas",
        },
    )
    assert response.status_code == 200
    body = response.json()
    assert "Semestre 1 (página 3)" in body["answer"]
    assert "Fundamentos de Bases de Datos" in body["answer"]
    assert "Semestre 9 (página 4)" in body["answer"]
    assert "las materias de ambas ramas no son todas obligatorias" in body["answer"]
    assert "Medicina" not in body["answer"]
    assert [source["source_id"] for source in body["sources"]] == [
        "BO-UNIFRANZ-LP-SISID-MALLA-2026",
        "BO-UNIFRANZ-LP-SISID-MALLA-2026",
    ]
    assert [source["page"] for source in body["sources"]] == [3, 4]
