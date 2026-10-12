import json
import struct
from pathlib import Path
from types import SimpleNamespace

import pytest
from fastapi.testclient import TestClient

from app.ingestion.models import DocumentExtraction, ExtractedSegment
from app.knowledge import academic_ingestion as ingestion
from app.knowledge import vector_staging


def _approved() -> SimpleNamespace:
    return SimpleNamespace(
        proposal_id="KGI-123456789ABC",
        status="APROBADA_PENDIENTE_INGESTA",
        url="https://www.upb.edu/carreras/sistemas",
        source_type="OFFICIAL_CAREER_HTML",
        assessment=SimpleNamespace(recognized_institution="Universidad Privada Boliviana"),
        declared_institution="Universidad Privada Boliviana",
        title="Ingeniería de Sistemas",
        publication_date="2026",
        version="2026",
        scope="Perfil y plan de Ingeniería de Sistemas",
        campus="La Paz",
        city="La Paz",
        limitations=[],
    )


def test_approved_source_is_staged_with_traceable_chunks_and_no_fake_vectors(
    client: TestClient, monkeypatch, tmp_path: Path
) -> None:
    html = b"""
    <html><main><h1>Ingenieria de Sistemas</h1>
    <p>Ingenieria de Sistemas</p><p>Duracion: 8 semestres.</p>
    <h2>Primer semestre</h2><p>Algebra lineal</p>
    <a href="/malla.pdf">Malla curricular de Sistemas</a>
    </main></html>
    """
    monkeypatch.setattr(ingestion, "_proposal", lambda _: _approved())
    monkeypatch.setattr(
        ingestion,
        "_fetch_document",
        lambda _, **__: ("https://www.upb.edu/carreras/sistemas", 200, "text/html", html),
    )
    monkeypatch.setattr(ingestion, "_document_kind", lambda *_: "PUBLIC_DOCUMENT")
    monkeypatch.setattr(ingestion, "_stage_dir", lambda: tmp_path)

    response = client.post(
        "/api/v1/knowledge/governance/proposals/KGI-123456789ABC/extract", json={}
    )

    assert response.status_code == 200
    body = response.json()
    assert body["status"] == "EXTRAIDA_PENDIENTE_REVISION"
    assert body["document_hash"].startswith("sha256:")
    assert body["metrics"]["chunks"] >= 1
    assert body["metrics"]["facts"] >= 2
    assert {fact["kind"] for fact in body["facts"]} >= {"DURACION", "MATERIA"}
    assert body["embeddings"]["status"] == "NO_GENERADOS"
    assert body["embeddings"]["vectors"] == 0
    assert body["linked_sources"][0]["url"] == "https://www.upb.edu/malla.pdf"
    assert body["coverage"]["fetched_documents"] == 1
    assert body["trace_id"] == response.headers["X-Trace-Id"]
    stage = tmp_path / "KGI-123456789ABC"
    assert (stage / "instantanea.html").read_bytes() == html
    assert (stage / "manifest.json").is_file()
    assert (stage / "chunks.jsonl").is_file()
    _, _, staged_chunks = vector_staging._verified_stage("KGI-123456789ABC")
    assert len(staged_chunks) == body["metrics"]["chunks"]
    monkeypatch.setattr(
        ingestion,
        "_fetch_document",
        lambda *_args, **_kwargs: pytest.fail("No debe descargar la fuente guardada"),
    )
    saved = client.get("/api/v1/knowledge/governance/proposals/KGI-123456789ABC/extract")
    assert saved.status_code == 200
    assert saved.json()["document_hash"] == body["document_hash"]
    assert saved.json()["metrics"]["chunks"] == body["metrics"]["chunks"]
    (stage / "instantanea.html").write_bytes(b"contenido cambiado")
    assert (
        client.get("/api/v1/knowledge/governance/proposals/KGI-123456789ABC/extract").status_code
        == 409
    )
    with pytest.raises(ValueError, match="hash"):
        vector_staging._verified_stage("KGI-123456789ABC")


def test_unapproved_or_unknown_proposal_never_fetches(client: TestClient, monkeypatch) -> None:
    called = []
    monkeypatch.setattr(ingestion, "_fetch_document", lambda _, **__: called.append(True))
    monkeypatch.setattr(
        ingestion,
        "_load_intake_registry",
        lambda: SimpleNamespace(
            proposals=[SimpleNamespace(proposal_id="KGI-123456789ABC", status="PENDIENTE_REVISION")]
        ),
    )
    path = "/api/v1/knowledge/governance/proposals/"
    pending = client.post(path + "KGI-123456789ABC/extract", json={})
    unknown = client.post(path + "KGI-000000000000/extract", json={})
    assert pending.status_code == 409
    assert unknown.status_code == 404
    assert called == []


@pytest.mark.parametrize("observed_career", ["Administracion de Empresas", "Ingenieria Civil"])
def test_changed_career_page_is_not_staged(
    client: TestClient, monkeypatch, tmp_path: Path, observed_career: str
) -> None:
    monkeypatch.setattr(ingestion, "_proposal", lambda _: _approved())
    monkeypatch.setattr(ingestion, "_stage_dir", lambda: tmp_path)
    monkeypatch.setattr(ingestion, "_document_kind", lambda *_: "PUBLIC_DOCUMENT")
    monkeypatch.setattr(
        ingestion,
        "_fetch_document",
        lambda _, **__: (
            "https://www.upb.edu/carreras/sistemas",
            200,
            "text/html",
            (
                f"<html><main><h1>{observed_career}</h1>"
                f"<p>Plan de estudios de {observed_career}.</p></main></html>"
            ).encode(),
        ),
    )
    response = client.post(
        "/api/v1/knowledge/governance/proposals/KGI-123456789ABC/extract", json={}
    )
    assert response.status_code == 200
    assert response.json()["status"] == "NO_DISPONIBLE"
    assert "no coincide" in response.json()["warnings"][0]
    assert not (tmp_path / "KGI-123456789ABC").exists()


def test_timeout_reports_recovery_without_technical_detail(client: TestClient, monkeypatch) -> None:
    monkeypatch.setattr(ingestion, "_proposal", lambda _: _approved())

    def timeout(_: str, **__: object) -> None:
        raise TimeoutError("socket read timed out on internal address")

    monkeypatch.setattr(ingestion, "_fetch_document", timeout)
    response = client.post(
        "/api/v1/knowledge/governance/proposals/KGI-123456789ABC/extract", json={}
    )
    assert response.status_code == 200
    assert response.json()["status"] == "NO_DISPONIBLE"
    assert "reintentar" in response.json()["warnings"][0]
    assert "internal address" not in response.text


@pytest.mark.parametrize("plan_career", ["Administracion de Empresas", "Ingenieria Civil"])
def test_profile_survives_when_curriculum_block_belongs_to_other_career(
    client: TestClient, monkeypatch, tmp_path: Path, plan_career: str
) -> None:
    html = f"""
    <main><h3>Ingenieria de Sistemas</h3><h3>Conoce tu carrera</h3>
    <p>Disena soluciones tecnologicas con programacion y sistemas de informacion.</p>
    <h3>Plan de estudios</h3><h1>{plan_career}</h1>
    <p>Matematica financiera y contabilidad de empresas.</p></main>
    """.encode()
    monkeypatch.setattr(ingestion, "_proposal", lambda _: _approved())
    monkeypatch.setattr(ingestion, "_stage_dir", lambda: tmp_path)
    monkeypatch.setattr(ingestion, "_document_kind", lambda *_: "PUBLIC_DOCUMENT")
    monkeypatch.setattr(
        ingestion,
        "_fetch_document",
        lambda _, **__: ("https://www.upb.edu/carreras/sistemas", 200, "text/html", html),
    )
    response = client.post(
        "/api/v1/knowledge/governance/proposals/KGI-123456789ABC/extract", json={}
    )
    assert response.status_code == 200
    body = response.json()
    assert body["status"] == "EXTRAIDA_PENDIENTE_REVISION"
    assert "otra" in " ".join(body["warnings"])
    stored = (tmp_path / "KGI-123456789ABC" / "chunks.jsonl").read_text(encoding="utf-8")
    assert "programacion" in stored
    assert "contabilidad" not in stored


def test_career_cards_keep_duration_and_heading_only_areas_without_navigation(
    client: TestClient, monkeypatch, tmp_path: Path
) -> None:
    proposal = _approved()
    proposal.title = "Psicología"
    proposal.url = "https://unifranz.edu.bo/carreras/psicologia/"
    institution = ingestion.assess_bolivian_university_url(proposal.url).recognized_institution
    proposal.assessment.recognized_institution = institution
    proposal.declared_institution = institution
    html = """
    <html><body><header><div>Cabecera institucional</div></header><main>
    <nav><h2>Menú académico</h2><div>Duración: 99 años</div></nav>
    <h1>Psicología</h1><p>Formación científica para el bienestar humano.</p>
    <h2>Malla Curricular</h2><div><div role="text">
    Programa de Estudios: <span>4 años</span></div></div>
    <h3>Modalidad de graduación</h3><div><div role="listitem">Tesis</div></div>
    <h2>Áreas de Formación</h2><div><div>
    <h3>Procesos cognitivos, neurociencia y evaluación psicológica</h3></div>
    <div><h3>Psicología clínica y de salud</h3></div></div>
    <footer><h3>Pie institucional</h3><div>Duración: 88 años</div></footer>
    </main><script>texto_prohibido()</script></body></html>
    """.encode()
    monkeypatch.setattr(ingestion, "_proposal", lambda _: proposal)
    monkeypatch.setattr(ingestion, "_stage_dir", lambda: tmp_path)
    monkeypatch.setattr(ingestion, "_document_kind", lambda *_: "PUBLIC_DOCUMENT")
    monkeypatch.setattr(
        ingestion,
        "_fetch_document",
        lambda _, **__: (proposal.url, 200, "text/html", html),
    )
    response = client.post(
        "/api/v1/knowledge/governance/proposals/KGI-123456789ABC/extract", json={}
    )
    assert response.status_code == 200
    assert response.json()["status"] == "EXTRAIDA_PENDIENTE_REVISION"
    durations = [fact for fact in response.json()["facts"] if fact["kind"] == "DURACION"]
    assert [fact["value"] for fact in durations] == ["4 años"]
    assert durations[0]["evidence"] == "Programa de Estudios: 4 años"
    stored = (tmp_path / proposal.proposal_id / "chunks.jsonl").read_text(encoding="utf-8")
    for academic_text in [
        "Tesis",
        "Procesos cognitivos, neurociencia y evaluación psicológica",
        "Psicología clínica y de salud",
    ]:
        assert academic_text in stored
    for noise in [
        "Cabecera",
        "Menú académico",
        "99 años",
        "Pie institucional",
        "88 años",
        "texto_prohibido",
    ]:
        assert noise not in stored


def test_reextraction_reports_verified_vectors_and_clears_status_when_source_changes(
    client: TestClient, monkeypatch, tmp_path: Path
) -> None:
    proposal = _approved()
    html = b"<main><h1>Ingenieria de Sistemas</h1><p>Duracion: 8 semestres.</p></main>"
    monkeypatch.setattr(ingestion, "_proposal", lambda _: proposal)
    monkeypatch.setattr(ingestion, "_stage_dir", lambda: tmp_path)
    monkeypatch.setattr(ingestion, "_document_kind", lambda *_: "PUBLIC_DOCUMENT")
    monkeypatch.setattr(
        ingestion,
        "_fetch_document",
        lambda _, **__: (proposal.url, 200, "text/html", html),
    )
    endpoint = f"/api/v1/knowledge/governance/proposals/{proposal.proposal_id}/extract"
    first = client.post(endpoint, json={})
    assert first.json()["status"] == "EXTRAIDA_PENDIENTE_REVISION"
    directory, source_manifest, chunks = vector_staging._verified_stage(proposal.proposal_id)
    header = repr({"descr": "<f4", "fortran_order": False, "shape": (len(chunks), 384)})
    header_bytes = (header + " " * ((64 - (10 + len(header) + 1) % 64) % 64) + "\n").encode()
    vectors_path = directory / "embeddings.npy"
    vectors_path.write_bytes(
        b"\x93NUMPY\x01\x00"
        + struct.pack("<H", len(header_bytes))
        + header_bytes
        + struct.pack("<384f", 1.0, *([0.0] * 383)) * len(chunks)
    )
    embedding_manifest = {
        "status": "GENERADOS_PENDIENTES_PUBLICACION",
        "proposal_id": proposal.proposal_id,
        "source_hash": source_manifest["document_hash"],
        "chunk_ids": [chunk.chunk_id for chunk in chunks],
        "chunks_sha256": vector_staging._chunks_sha256(chunks),
        "model": "intfloat/multilingual-e5-small",
        "dimension": 384,
        "vectors": len(chunks),
        "vectors_sha256": vector_staging._sha256(vectors_path),
        "reviewer": "Revisor de prueba",
        "review_note": "Extracción sintética revisada para comprobar el estado vectorial.",
    }
    (directory / "embedding_manifest.json").write_text(
        json.dumps(embedding_manifest), encoding="utf-8"
    )
    repeated = client.post(endpoint, json={})
    assert repeated.json()["embeddings"]["status"] == "GENERADOS_PENDIENTES_PUBLICACION"
    assert repeated.json()["embeddings"]["vectors"] == len(chunks)
    stored = json.loads((directory / "manifest.json").read_text(encoding="utf-8"))
    assert stored["embeddings"] == repeated.json()["embeddings"]
    html = html.replace(b"8 semestres", b"10 semestres")
    changed = client.post(endpoint, json={})
    assert changed.json()["status"] == "EXTRAIDA_PENDIENTE_REVISION"
    assert changed.json()["embeddings"]["status"] == "NO_GENERADOS"
    assert changed.json()["embeddings"]["vectors"] == 0
    stored = json.loads((directory / "manifest.json").read_text(encoding="utf-8"))
    assert stored["embeddings"] == changed.json()["embeddings"]


@pytest.mark.parametrize("failure", ["sin_texto", "extractor", "promocion", "vector_status"])
def test_failed_reextraction_preserves_saved_evidence_and_vectors(
    client: TestClient, monkeypatch, tmp_path: Path, failure: str
) -> None:
    proposal = _approved()
    html = b"<main><h1>Ingenieria de Sistemas</h1><p>Duracion: 8 semestres.</p></main>"
    monkeypatch.setattr(ingestion, "_proposal", lambda _: proposal)
    monkeypatch.setattr(ingestion, "_stage_dir", lambda: tmp_path)
    monkeypatch.setattr(ingestion, "_document_kind", lambda *_: "PUBLIC_DOCUMENT")
    monkeypatch.setattr(
        ingestion, "_fetch_document", lambda _, **__: (proposal.url, 200, "text/html", html)
    )
    endpoint = f"/api/v1/knowledge/governance/proposals/{proposal.proposal_id}/extract"
    assert client.post(endpoint, json={}).json()["status"] == "EXTRAIDA_PENDIENTE_REVISION"
    directory, source_manifest, chunks = vector_staging._verified_stage(proposal.proposal_id)
    header = repr({"descr": "<f4", "fortran_order": False, "shape": (len(chunks), 384)})
    header_bytes = (header + " " * ((64 - (10 + len(header) + 1) % 64) % 64) + "\n").encode()
    vectors = directory / "embeddings.npy"
    vectors.write_bytes(
        b"\x93NUMPY\x01\x00"
        + struct.pack("<H", len(header_bytes))
        + header_bytes
        + struct.pack("<384f", 1.0, *([0.0] * 383)) * len(chunks)
    )
    (directory / "embedding_manifest.json").write_text(
        json.dumps(
            {
                "status": "GENERADOS_PENDIENTES_PUBLICACION",
                "proposal_id": proposal.proposal_id,
                "source_hash": source_manifest["document_hash"],
                "chunk_ids": [chunk.chunk_id for chunk in chunks],
                "chunks_sha256": vector_staging._chunks_sha256(chunks),
                "model": "intfloat/multilingual-e5-small",
                "dimension": 384,
                "vectors": len(chunks),
                "vectors_sha256": vector_staging._sha256(vectors),
                "reviewer": "Revisor de prueba",
                "review_note": "Extracción sintética revisada para comprobar conservación.",
            }
        ),
        encoding="utf-8",
    )
    saved_before = client.get(endpoint).json()
    assert saved_before["embeddings"]["status"] == "GENERADOS_PENDIENTES_PUBLICACION"
    before = {file.name: file.read_bytes() for file in directory.iterdir() if file.is_file()}
    html = html.replace(b"8 semestres", b"10 semestres")

    def fail(*_args, **_kwargs):
        raise RuntimeError("Fallo de prueba antes de completar la extracción")

    with monkeypatch.context() as failed_attempt:
        if failure == "sin_texto":
            failed_attempt.setattr(
                ingestion,
                "extract_html",
                lambda *_: DocumentExtraction(
                    source_id=proposal.proposal_id,
                    segments=[],
                    warnings=[],
                    digital_pages=[],
                    ocr_pages=[],
                ),
            )
        elif failure == "extractor":
            failed_attempt.setattr(ingestion, "extract_html", fail)
        elif failure == "vector_status":
            failed_attempt.setattr(vector_staging, "read_staged_vector_status", fail)
        else:
            replace = ingestion.os.replace

            def fail_once(source, destination):
                if (
                    Path(source).parent.name.startswith(".candidato-ingesta-")
                    and Path(destination).name == "chunks.jsonl"
                ):
                    raise OSError("Fallo de prueba durante promoción")
                return replace(source, destination)

            failed_attempt.setattr(ingestion.os, "replace", fail_once)
        response = client.post(endpoint, json={})
        assert response.status_code == 200
        assert response.json()["status"] == "NO_DISPONIBLE"

    after = {file.name: file.read_bytes() for file in directory.iterdir() if file.is_file()}
    assert after == before
    saved_after = client.get(endpoint)
    assert saved_after.status_code == 200
    assert saved_after.json()["document_hash"] == saved_before["document_hash"]
    assert saved_after.json()["facts"] == saved_before["facts"]
    assert saved_after.json()["chunks"] == saved_before["chunks"]
    assert saved_after.json()["embeddings"] == saved_before["embeddings"]
    assert not list(tmp_path.glob(".candidato-ingesta-*"))
    assert not list(tmp_path.glob(".respaldo-ingesta-*"))


@pytest.mark.parametrize("explicit_declaration", [False, True])
def test_career_facts_do_not_turn_training_areas_or_competencies_into_degrees(
    explicit_declaration: bool,
) -> None:
    extraction = DocumentExtraction(
        source_id="PSICOLOGIA-TEST",
        digital_pages=[],
        ocr_pages=[],
        warnings=[],
        segments=[
            ExtractedSegment(
                section="Identificación" if explicit_declaration else "Psicologia",
                text=(
                    "Carrera: Psicología"
                    if explicit_declaration
                    else "El profesional comprende problemáticas psicológicas y sociales."
                ),
                extraction_method="HTML",
            ),
            ExtractedSegment(
                section="Psicología clínica y de salud",
                text="Psicología clínica y de salud",
                extraction_method="HTML",
            ),
            ExtractedSegment(
                section="Psicología social, organizacional y del bienestar",
                text="Psicología social, organizacional y del bienestar",
                extraction_method="HTML",
            ),
            ExtractedSegment(
                section="Competencias profesionales",
                text="Generar conocimiento a través de la investigación aplicada en psicología.",
                extraction_method="HTML",
            ),
        ],
    )
    facts = ingestion._facts(
        extraction, "https://unifranz.edu.bo/carreras/psicologia/", career_title="Psicología"
    )
    careers = [fact for fact in facts if fact.kind == "CARRERA"]
    assert len(careers) == 1
    assert ingestion._normalize_words(careers[0].value) == "psicologia"
    assert careers[0].evidence == ("Carrera: Psicología" if explicit_declaration else "Psicologia")
