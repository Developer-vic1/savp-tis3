from pathlib import Path

from app.ingestion.chunking import build_chunks
from app.ingestion.extractors import extract_html, extract_pdf, extract_text
from app.ingestion.models import KnowledgeChunk
from app.knowledge.registry import load_source_manifest
from tests.pdf_helpers import open_pdf

DATA_ROOT = Path(__file__).resolve().parents[2] / "data"


def _digital_pdf(path: Path) -> None:
    document = open_pdf()
    page = document.new_page()
    page.insert_text(
        (72, 72),
        "Documento digital oficial de prueba. " * 8,
        fontsize=12,
    )
    document.save(path)
    document.close()


def _scanned_pdf(path: Path) -> None:
    source = open_pdf()
    page = source.new_page()
    page.insert_text((72, 120), "Matemática y ciencias para secundaria", fontsize=24)
    image = page.get_pixmap(dpi=160, alpha=False).tobytes("png")
    source.close()

    scanned = open_pdf()
    scanned_page = scanned.new_page()
    scanned_page.insert_image(scanned_page.rect, stream=image)
    scanned.save(path)
    scanned.close()


def test_digital_pdf_is_not_sent_to_ocr(tmp_path: Path) -> None:
    path = tmp_path / "digital.pdf"
    _digital_pdf(path)
    result = extract_pdf(path, "DIGITAL-TEST")
    assert result.digital_pages == [1]
    assert result.ocr_pages == []
    assert result.segments[0].extraction_method == "DIGITAL"


def test_image_only_pdf_requires_a_textual_source(tmp_path: Path) -> None:
    path = tmp_path / "scanned.pdf"
    _scanned_pdf(path)
    result = extract_pdf(path, "OCR-TEST")
    assert result.digital_pages == []
    assert result.ocr_pages == []
    assert result.segments == []
    assert "OCR automático deshabilitado" in result.warnings[0]


def test_html_removes_scripts_and_keeps_sections(tmp_path: Path) -> None:
    path = tmp_path / "career.html"
    path.write_text(
        "<html><body><main><h2>Perfil</h2><p>Ingeniería de Sistemas</p>"
        "<script>texto_prohibido()</script><li>Programación</li></main></body></html>",
        encoding="utf-8",
    )
    result = extract_html(path, "HTML-TEST")
    assert [segment.text for segment in result.segments] == ["Ingeniería de Sistemas\nProgramación"]
    assert all(segment.section == "Perfil" for segment in result.segments)


def test_html_stops_before_staff_and_navigation_noise(tmp_path: Path) -> None:
    path = tmp_path / "career.html"
    path.write_text(
        "<html><body><main><h2>Perfil</h2><p>Contenido académico</p>"
        "<h2>Plantel Docente</h2><p>Nombre personal</p>"
        "<h2>Noticias</h2><p>Contenido no curricular</p></main></body></html>",
        encoding="utf-8",
    )
    result = extract_html(path, "HTML-TEST")
    assert [segment.text for segment in result.segments] == ["Contenido académico"]


def test_text_sections_and_chunks_are_deterministic(tmp_path: Path) -> None:
    path = tmp_path / "source.md"
    path.write_text("# Matemática\n" + ("Álgebra y cálculo. " * 100), encoding="utf-8")
    extraction = extract_text(path, "BO-UCB-LP-SIS-MALLA-2026")
    source = next(
        source
        for source in load_source_manifest().sources
        if source.source_id == "BO-UCB-LP-SIS-MALLA-2026"
    )
    first = build_chunks(extraction, source, target_chars=300, overlap_chars=40)
    second = build_chunks(extraction, source, target_chars=300, overlap_chars=40)
    assert len(first) > 1
    assert [chunk.chunk_id for chunk in first] == [chunk.chunk_id for chunk in second]
    assert all(chunk.section == "Matemática" for chunk in first)
    assert all(chunk.document_hash == source.document_hash for chunk in first)
    assert all(chunk.official for chunk in first)


def test_generated_official_corpus_conforms_to_chunk_contract() -> None:
    corpus_path = DATA_ROOT / "processed" / "corpus.jsonl"
    chunks = [
        KnowledgeChunk.model_validate_json(line)
        for line in corpus_path.read_text(encoding="utf-8").splitlines()
    ]
    assert len(chunks) == 814
    assert len({chunk.chunk_id for chunk in chunks}) == len(chunks)
    assert len({chunk.source_id for chunk in chunks}) == 16
    assert all(chunk.official for chunk in chunks)
    ocr_chunks = [chunk for chunk in chunks if chunk.extraction_method == "OCR"]
    assert len(ocr_chunks) == 37
    assert all(chunk.extraction_confidence is not None for chunk in ocr_chunks)
    assert {chunk.ocr_engine for chunk in ocr_chunks} == {"EasyOCR"}
    assert {chunk.ocr_language for chunk in ocr_chunks} == {"es,en"}
    assert {chunk.ocr_version for chunk in ocr_chunks} == {"1.7.2"}
    assert min(float(chunk.extraction_confidence or 0) for chunk in ocr_chunks) == 0.4638
