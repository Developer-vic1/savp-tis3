import json

import pytest

from app.knowledge import catalogo_nacional as directory


def test_real_registry_distinguishes_history_from_published_programs():
    result = directory.national_directory("trace-test")
    ids = [item["institution_id"] for item in result.institutions]
    assert len(ids) == len(set(ids)) == result.statistics["institutions"]
    assert "histórico" in result.coverage_note
    assert all("local_file" not in source for source in result.evidence_sources)
    programs = [program for item in result.institutions for program in item["programs"]]
    assert len(programs) == result.statistics["documented_programs"]
    assert programs
    assert all(program["evidence_layer"] == "CORPUS_VALIDADO" for program in programs)
    assert all(program["sources"] for program in programs)
    assert all(program["economic_evidence"] == [] for program in programs)
    assert all(program["economic_status"] == "PENDIENTE_VERIFICACION" for program in programs)
    assert any(program["duration_semesters"] is None for program in programs)
    unifranz = next(item for item in result.institutions if item.get("acronym") == "UNIFRANZ")
    assert len(unifranz["published_offer"]["careers"]) == 17
    assert unifranz["published_offer"]["source_url"] == "https://unifranz.edu.bo/carreras/"
    assert result.statistics["documented_programs"] < len(unifranz["published_offer"]["careers"])


def test_report_is_fresh_and_candidates_are_never_promoted(monkeypatch, tmp_path):
    registry_path = tmp_path / "registro.json"
    coverage_path = tmp_path / "avance.json"
    monkeypatch.setattr(directory, "NATIONAL_DIRECTORY", registry_path)
    monkeypatch.setattr(directory, "NATIONAL_COVERAGE", coverage_path)
    registry_path.write_text(json.dumps({"institutions": [{
        "institution_id": "otra", "acronym": "SIN_MAPEO", "name": "Registro de prueba",
        "website_verification_status": "OFFICIAL_DOMAIN_VERIFIED",
    }]}))
    for extracted in (1, 2):
        coverage_path.write_text(json.dumps({"institutions": [{
            "institution_id": "otra", "valid": extracted, "pending": 3,
            "candidate_counts": {"career": 12},
        }]}))
        result = directory.national_directory("trace-test")
        assert result.statistics["extracted_documents"] == extracted
        assert result.statistics["documented_programs"] == 0
        assert result.institutions[0]["programs"] == []


def test_missing_report_is_not_an_error_but_corrupt_registry_is(monkeypatch, tmp_path):
    path = tmp_path / "registro.json"
    monkeypatch.setattr(directory, "NATIONAL_DIRECTORY", path)
    monkeypatch.setattr(directory, "NATIONAL_COVERAGE", tmp_path / "no-existe.json")
    assert directory.national_directory("trace-test").statistics["institutions"] == 0
    path.write_text("[]")
    with pytest.raises(ValueError):
        directory.national_directory("trace-test")


def test_directory_api_uses_the_internal_key_and_trace(client):
    response = client.get("/api/v1/knowledge/national-directory")
    assert response.status_code == 200
    assert response.json()["trace_id"] == response.headers["X-Trace-Id"]
    assert response.headers["Cache-Control"] == "no-store"
    client.headers.pop("X-SAVP-AI-Key")
    assert client.get("/api/v1/knowledge/national-directory").status_code == 401


def test_verified_national_identity_is_accepted_but_quarantine_is_not(monkeypatch, tmp_path):
    from app.knowledge import governance

    path = tmp_path / "registro.json"
    path.write_text(json.dumps({"institutions": [
        {"name": "Universidad contrastada", "acronym": "UC", "official_domains": ["real.edu.bo"],
         "website_verification_status": "OFFICIAL_DOMAIN_VERIFIED"},
        {"name": "Universidad pendiente", "official_domains": ["pendiente.edu.bo"],
         "website_verification_status": "PENDING_CURRENT_VERIFICATION"},
        {"name": "Sitio bloqueado", "official_domains": ["bloqueada.edu.bo"],
         "website_verification_status": "OFFICIAL_DOMAIN_VERIFIED",
         "ingestion_status": "QUARANTINE_SITE_REVIEW_REQUIRED"},
    ]}))
    monkeypatch.setattr(governance, "NATIONAL_UNIVERSITY_REGISTRY", path)
    assessment = governance.assess_bolivian_university_url("https://real.edu.bo/carreras/")
    assert assessment.status == "ACEPTADA"
    for domain in ("pendiente.edu.bo", "bloqueada.edu.bo", "real.edu.bo.evil.test"):
        assert not governance.assess_bolivian_university_url(f"https://{domain}/").is_bolivian_university
