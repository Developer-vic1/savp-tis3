from __future__ import annotations

import json
import threading
from pathlib import Path

import pymupdf
import pytest

from app.knowledge import coleccion_universitaria as collection
from app.knowledge.source_preview import PreviewFailure

INDEX = collection.SOURCE_URL
CAREER = INDEX + "derecho/"
PDF = "https://unifranz.edu.bo/wp-content/uploads/malla-derecho.pdf"
HTML = (
    "<html><title>Derecho</title><main><h1>Derecho</h1><p>Sedes: La Paz y El Alto.</p>"
    "<p>Programa de Estudios: 8 semestres</p><h2>Malla curricular</h2>"
    f'<a href="{PDF}">Descargar malla</a></main></html>'
).encode()


def pdf_bytes(*, blank: bool = False) -> bytes:
    with pymupdf.open() as document:
        page = document.new_page()
        page.insert_text((40, 50), "Derecho Constitucional Derecho Civil Derecho Penal " * 8)
        if blank:
            document.new_page()
        return document.tobytes()


def fixture_fetch(calls: list[str], *, blank: bool = False):
    def fetch(url: str, **kwargs):
        calls.append(url)
        assert kwargs["max_bytes"] == collection.MAX_BYTES
        if url == INDEX:
            body = f'<html><title>Carreras UNIFRANZ</title><a href="{CAREER}">Derecho</a></html>'
            return url, 200, "text/html", body.encode()
        if url == CAREER:
            return url, 200, "text/html", HTML
        return url, 200, "application/pdf", pdf_bytes(blank=blank)

    return fetch


def test_discovery_deduplicates_and_rejects_external_portal_and_pagination():
    content = f'''<a href="{CAREER}?utm_source=x">Derecho</a>
        <a href="{CAREER}#malla">Derecho</a><a href="/carreras/page/2/">Más</a>
        <a href="https://ajena.bo/carreras/medicina/">Medicina</a>
        <a href="https://portal.unifranz.edu.bo/carreras/medicina/">Portal</a>
        <a href="https://unifranz.edu.bo:foo/carreras/medicina/">Inválido</a>
        <a href="/tag/carreras/medicina/">Tag</a>'''.encode()
    assert [row["url"] for row in collection.discover_careers(content)] == [CAREER]


def test_curricula_keep_different_campus_pdfs_and_reject_unrelated_guide():
    content = b"""<h2>Documentos</h2><a href="/guia.pdf">Guia estudiantil</a>
        <h2>Malla curricular</h2><a href="/malla-la-paz.pdf">La Paz</a>
        <a href="/malla-el-alto.pdf">El Alto</a><a href="/malla-el-alto.pdf">Duplicado</a>
        <a href="https://ajena.bo/malla.pdf">Malla ajena</a>"""
    rows = collection.discover_curricula(content, CAREER)
    assert len(rows) == 2
    assert {row["title"] for row in rows} == {"La Paz", "El Alto"}


def test_partial_pdf_preserves_provenance_and_resumes_without_redownloading(
    tmp_path: Path, monkeypatch
):
    monkeypatch.setattr(collection, "_recognition_runtime", lambda: None)
    calls: list[str] = []
    state = collection.run_collection(
        tmp_path, fetcher=fixture_fetch(calls, blank=True), pause_seconds=0
    )
    document = state["careers"][0]["curricula"][0]
    assert state["status"] == "PARCIAL"
    assert document["pages"] == 2 and document["digital_pages"] == 1
    assert document["status"] == "PARCIAL" and document["chunks"] > 0
    assert any("lectura de imágenes" in warning for warning in document["warnings"])
    # Una mención textual no sustituye indicadores de oferta de la ficha oficial.
    assert state["careers"][0]["campuses"] == []
    assert state["careers"][0]["campus_evidence"]["status"] == "PENDIENTE"
    assert state["careers"][0]["duration"] == "8 semestres"
    # La ficha HTML y la malla PDF son dos documentos con texto guardado.
    assert state["careers"][0]["page_chunks"] > 0
    assert collection.read_collection(tmp_path)["statistics"]["extracted_documents"] == 2
    chunk = json.loads(next((tmp_path / "fragmentos").glob("*.jsonl")).read_text().splitlines()[0])
    assert chunk["source_url"] in {CAREER, PDF}
    assert chunk["document_hash"].startswith("sha256:")
    assert chunk["evidence_layer"] == "COLECCION_PENDIENTE_REVISION"
    collection.run_collection(tmp_path, fetcher=fixture_fetch(calls), pause_seconds=0)
    assert calls == [INDEX, CAREER, PDF]


def test_failed_pdf_retries_only_failed_download(tmp_path: Path):
    calls: list[str] = []
    base = fixture_fetch(calls)

    def fail(url: str, **kwargs):
        if url == PDF:
            calls.append(url)
            raise PreviewFailure("Conexión agotada")
        return base(url, **kwargs)

    first = collection.run_collection(tmp_path, fetcher=fail, pause_seconds=0)
    assert first["careers"][0]["curricula"][0]["status"] == "NO_DISPONIBLE"
    second = collection.run_collection(tmp_path, fetcher=base, pause_seconds=0)
    assert second["status"] == "COMPLETADA"
    assert calls == [INDEX, CAREER, PDF, PDF, PDF]


def test_redirect_outside_domain_is_rejected(tmp_path: Path):
    def fetch(url: str, **kwargs):
        return "https://ajena.bo/carreras/", 200, "text/html", HTML

    state = collection.run_collection(tmp_path, fetcher=fetch, pause_seconds=0)
    assert state["status"] == "NO_DISPONIBLE"
    assert not list(tmp_path.glob("instantaneas/*"))


def test_start_returns_immediately_and_rejects_concurrent(monkeypatch, tmp_path: Path):
    ready = threading.Event()
    finish = threading.Event()
    original = collection._collect

    def paused(root, recollect, fetcher, pause_seconds):
        ready.set()
        finish.wait(3)
        return original(root, recollect, fixture_fetch([]), 0)

    monkeypatch.setattr(collection, "COLLECTION_ROOT", tmp_path)
    monkeypatch.setattr(collection, "_collect", paused)
    state = collection.start_collection()
    assert state["status"] == "EN_PROCESO"
    assert ready.wait(1)
    with pytest.raises(RuntimeError, match="proceso"):
        collection.start_collection()
    finish.set()
    for _ in range(100):
        if collection.read_collection()["status"] == "COMPLETADA":
            break
        threading.Event().wait(0.01)
    assert collection.read_collection()["status"] == "COMPLETADA"


def test_interrupted_manifest_without_active_lock_is_recoverable(tmp_path: Path):
    state = collection._empty()
    state["status"] = "EN_PROCESO"
    collection._save(tmp_path, state)
    interrupted = collection.read_collection(tmp_path)
    assert interrupted["status"] == "PARCIAL"
    assert any("interrumpió" in warning for warning in interrupted["warnings"])
    resumed = collection.run_collection(tmp_path, fetcher=fixture_fetch([]), pause_seconds=0)
    assert resumed["status"] == "COMPLETADA"


@pytest.mark.parametrize("final_url", [INDEX, INDEX + "medicina/"])
def test_career_redirect_to_home_or_other_career_is_not_attributed(tmp_path: Path, final_url: str):
    calls: list[str] = []
    base = fixture_fetch(calls)

    def fetch(url, **kwargs):
        if url == CAREER:
            calls.append(url)
            return final_url, 200, "text/html", HTML
        return base(url, **kwargs)

    state = collection.run_collection(tmp_path, fetcher=fetch, pause_seconds=0)
    assert state["careers"][0]["status"] == "NO_DISPONIBLE"
    assert state["careers"][0]["curricula"] == []
    assert state["statistics"]["extracted_documents"] == 0
    assert PDF not in calls
    assert not list((tmp_path / "fragmentos").glob("*.jsonl"))


def test_heading_identity_and_equivalent_www_slash_redirects(tmp_path: Path):
    calls: list[str] = []
    base = fixture_fetch(calls)

    def fetch(url, **kwargs):
        if url == CAREER:
            return "https://www.unifranz.edu.bo/carreras/derecho", 200, "text/html", HTML
        return base(url, **kwargs)

    state = collection.run_collection(tmp_path, fetcher=fetch, pause_seconds=0)
    assert state["status"] == "COMPLETADA"
    assert collection.read_collection(tmp_path)["status"] == "COMPLETADA"
    with pytest.raises(PreviewFailure, match="nombre publicado"):
        collection._career_content(
            HTML.replace(b"<h1>Derecho", b"<h1>Medicina"), CAREER, CAREER, "Derecho"
        )
    assert collection._normal_name("Ingeniería Comercial") == collection._normal_name(
        "INGENIERIA comercial"
    )


@pytest.mark.parametrize(
    ("text", "expected"),
    [
        ("Estudié hace 2 años. Programa de Estudios: 4 años", "4 años"),
        ("Experiencia de 20 años. Duración: 8 semestres", "8 semestres"),
        ("Programa de Estudios: 4 años y medio", "4 años y medio"),
        ("Duración: 4,5 años", "4,5 años"),
        ("Tengo 22 años y gané experiencia durante 4 años", None),
    ],
)
def test_duration_requires_explicit_academic_label(text, expected):
    assert collection._duration(text) == expected


@pytest.mark.parametrize(
    "damage", ["snapshot_missing", "snapshot_corrupt", "chunks_missing", "count", "url", "hash"]
)
def test_corrupt_pdf_recovers_only_affected_document_and_preserves_other_evidence(
    tmp_path: Path, damage: str
):

    calls: list[str] = []
    base = fixture_fetch(calls)
    state = collection.run_collection(tmp_path, fetcher=base, pause_seconds=0)
    document = state["careers"][0]["curricula"][0]
    digest = document["document_hash"].removeprefix("sha256:")
    snapshot = tmp_path / "instantaneas" / (digest + ".pdf")
    chunks = tmp_path / "fragmentos" / document["chunks_file"]
    page_hash = state["careers"][0]["page_hash"].removeprefix("sha256:")
    page_snapshot = tmp_path / "instantaneas" / (page_hash + ".html")
    page_before = page_snapshot.read_bytes()
    if damage == "snapshot_missing":
        snapshot.unlink()
    elif damage == "snapshot_corrupt":
        snapshot.write_bytes(b"PDF alterado")
    elif damage == "chunks_missing":
        chunks.unlink()
    elif damage == "count":
        chunks.write_text("", encoding="utf8")
    else:
        rows = [json.loads(line) for line in chunks.read_text(encoding="utf8").splitlines()]
        rows[0]["source_url" if damage == "url" else "document_hash"] = (
            CAREER if damage == "url" else "sha256:" + "0" * 64
        )
        chunks.write_text("\n".join(json.dumps(row) for row in rows), encoding="utf8")
    damaged_files = {p: p.read_bytes() for p in (snapshot, chunks) if p.exists()}
    read = collection.read_collection(tmp_path)
    assert read["status"] == "PARCIAL"
    assert read["careers"][0]["curricula"][0]["status"] == "NO_DISPONIBLE"
    assert read["statistics"]["extracted_documents"] == 1
    calls.clear()

    def unavailable(url, **kwargs):
        calls.append(url)
        raise PreviewFailure("Documento temporalmente no disponible")

    collection.run_collection(tmp_path, fetcher=unavailable, pause_seconds=0)
    assert calls == [PDF, PDF]
    assert page_snapshot.read_bytes() == page_before
    assert all(p.read_bytes() == content for p, content in damaged_files.items())
    calls.clear()
    recovered = collection.run_collection(tmp_path, fetcher=base, pause_seconds=0)
    assert calls == [PDF]
    assert recovered["status"] == "COMPLETADA"
    assert collection.read_collection(tmp_path)["statistics"]["extracted_documents"] == 2
    assert page_snapshot.read_bytes() == page_before


def test_corrupt_career_page_recovers_without_redownloading_valid_curriculum(tmp_path: Path):
    calls: list[str] = []
    base = fixture_fetch(calls)
    state = collection.run_collection(tmp_path, fetcher=base, pause_seconds=0)
    career = state["careers"][0]
    snapshot = tmp_path / "instantaneas" / (career["page_hash"].removeprefix("sha256:") + ".html")
    snapshot.write_bytes(b"Ficha alterada")
    read = collection.read_collection(tmp_path)
    assert read["careers"][0]["status"] == "NO_DISPONIBLE"
    assert read["careers"][0]["curricula"][0]["status"] == "EXTRAIDA"
    assert read["statistics"]["extracted_documents"] == 1
    calls.clear()
    recovered = collection.run_collection(tmp_path, fetcher=base, pause_seconds=0)
    assert calls == [CAREER]
    assert recovered["status"] == "COMPLETADA"
    assert collection.read_collection(tmp_path)["statistics"]["extracted_documents"] == 2


def test_recognition_resume_uses_saved_pdf_preserves_digital_and_page_provenance(
    tmp_path: Path, monkeypatch
):
    calls: list[str] = []
    base = fixture_fetch(calls, blank=True)
    monkeypatch.setattr(collection, "_recognition_runtime", lambda: None)
    first = collection.run_collection(tmp_path, fetcher=base, pause_seconds=0)
    document = first["careers"][0]["curricula"][0]
    original_hash = document["document_hash"]
    original_time = document["retrieved_at"]
    runtime = {
        "engine": "Tesseract/PyMuPDF",
        "version": "test",
        "language": "spa",
        "tessdata": "test",
    }
    monkeypatch.setattr(collection, "_recognition_runtime", lambda: runtime)
    recognized_text = "Derecho Internacional Derecho Tributario Derecho Laboral " * 4
    recognized_pages = []

    def recognize(path, page, runtime, timeout):
        recognized_pages.append(page)
        return recognized_text

    monkeypatch.setattr(collection, "_recognize_page", recognize)
    calls.clear()
    second = collection.run_collection(tmp_path, fetcher=base, pause_seconds=0)
    document = second["careers"][0]["curricula"][0]
    assert not calls and recognized_pages == [2]
    assert document["document_hash"] == original_hash and document["retrieved_at"] == original_time
    assert document["digital_pages"] == 1 and document["recognized_pages"] == 1
    assert document["readable_pages"] == 2 and document["status"] == "EXTRAIDA"
    assert document["page_methods"] == [
        {"page": 1, "method": "DIGITAL", "review_required": False},
        {"page": 2, "method": "OCR", "review_required": True},
    ]

    chunk_path = tmp_path / "fragmentos" / document["chunks_file"]
    chunks = [json.loads(line) for line in chunk_path.read_text(encoding="utf8").splitlines()]
    assert any(chunk["page"] == 1 and chunk["extraction_method"] == "DIGITAL" for chunk in chunks)
    recognized = [chunk for chunk in chunks if chunk["page"] == 2]
    assert recognized[0]["review_required"]
    assert recognized[0]["recognition_engine"] == "Tesseract/PyMuPDF"
    assert recognized[0]["document_hash"] == original_hash
    assert "Derecho Tributario" in recognized[0]["text"]
    assert collection.read_collection(tmp_path)["status"] == "COMPLETADA"


def test_blank_cover_is_not_transformed_into_subjects_and_prior_reading_is_preserved(
    tmp_path: Path, monkeypatch
):
    from app.ingestion.models import ExtractedSegment

    path = tmp_path / "mixed.pdf"
    path.write_bytes(pdf_bytes(blank=True))
    runtime = {
        "engine": "Tesseract/PyMuPDF",
        "version": "test",
        "language": "spa",
        "tessdata": "test",
    }
    monkeypatch.setattr(collection, "_recognition_runtime", lambda: runtime)
    monkeypatch.setattr(collection, "_recognize_page", lambda *args: "LOGO UNIFRANZ")
    reading = collection._extract_readable_pdf(path, "sha256:" + "a" * 64)
    assert reading.digital_pages == [1] and reading.ocr_pages == []
    assert any("portada" in warning for warning in reading.warnings)
    previous = ExtractedSegment(
        text="Texto ya leído de la malla " * 5,
        page=2,
        extraction_method="OCR",
        ocr_engine="Tesseract/PyMuPDF",
    )
    monkeypatch.setattr(collection, "_recognition_runtime", lambda: None)
    retained = collection._extract_readable_pdf(path, "sha256:" + "a" * 64, [previous])
    assert retained.ocr_pages == [2]
    assert any(segment.text == previous.text for segment in retained.segments)


@pytest.mark.ocr
def test_real_spanish_image_reading_with_available_local_model(tmp_path: Path):
    import io

    from PIL import Image, ImageDraw, ImageFont

    if not collection._recognition_runtime():
        pytest.skip("Falta modelo local de español para lectura de imágenes real")
    image = Image.new("RGB", (1800, 900), "white")
    draw = ImageDraw.Draw(image)
    font = ImageFont.truetype("C:/Windows/Fonts/arial.ttf", 54)
    text = (
        "Malla curricular de Derecho\nDerecho Constitucional\nDerecho Internacional\n"
        "Derecho Civil y Derecho Laboral\nPrograma de Estudios de la Universidad"
    )
    draw.multiline_text((70, 70), text, fill="black", font=font, spacing=25)
    output = io.BytesIO()
    image.save(output, format="PNG")
    path = tmp_path / "malla_imagen.pdf"
    with pymupdf.open() as document:
        page = document.new_page(width=900, height=450)
        page.insert_image(page.rect, stream=output.getvalue())
        document.save(path)
    reading = collection._extract_readable_pdf(path, "sha256:" + "b" * 64)
    assert reading.digital_pages == [] and reading.ocr_pages == [1]
    assert "Derecho Constitucional" in reading.segments[0].text
    assert reading.segments[0].extraction_method == "OCR"
    assert reading.segments[0].ocr_language == "spa"


def test_subject_candidates_exclude_microcredentials_and_respect_semester_columns(tmp_path):
    path = tmp_path / "columns.pdf"
    with pymupdf.open() as document:
        page = document.new_page(width=500, height=800)
        page.insert_text((80, 40), "Primer")
        page.insert_text((80, 58), "Semestre")
        page.insert_text((50, 100), "Derecho Civil")
        page.insert_text((310, 40), "Segundo")
        page.insert_text((310, 58), "Semestre")
        page.insert_text((280, 100), "Derecho Laboral")
        page.insert_text((40, 350), "PROFESIONALES - MICROCREDENCIALES")
        page.insert_text((50, 400), "Derecho Civil Derecho Laboral")
        document.save(path)
    digest = "sha256:" + "a" * 64
    extraction = collection.extract_pdf(path, digest)
    candidates = collection._subjects_candidates(
        path, digest, "https://unifranz.edu.bo/malla.pdf", extraction
    )
    assert [(item["name"], item["semester"]) for item in candidates] == [
        ("Derecho Civil", 1),
        ("Derecho Laboral", 2),
    ]
