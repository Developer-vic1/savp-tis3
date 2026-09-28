import os
from pathlib import Path

import pymupdf
import pytest

from app.ingestion.extractors import extract_pdf
from app.ingestion.ocr import EasyOcrSpanish


@pytest.mark.ocr
@pytest.mark.skipif(
    os.getenv("RUN_REAL_OCR") != "1",
    reason="La prueba real descarga/carga el modelo OCR; ejecutar con RUN_REAL_OCR=1.",
)
def test_real_spanish_ocr_on_image_only_pdf(tmp_path: Path) -> None:
    source = pymupdf.open()
    page = source.new_page(width=900, height=500)
    page.insert_text((80, 160), "MATEMATICA Y CIENCIAS", fontsize=42)
    page.insert_text((80, 240), "Preparacion para la universidad", fontsize=30)
    image = page.get_pixmap(dpi=180, alpha=False).tobytes("png")
    source.close()

    path = tmp_path / "spanish-scanned.pdf"
    scanned = pymupdf.open()
    scanned_page = scanned.new_page(width=900, height=500)
    scanned_page.insert_image(scanned_page.rect, stream=image)
    scanned.save(path)
    scanned.close()

    result = extract_pdf(path, "REAL-OCR-TEST", EasyOcrSpanish())
    recovered = " ".join(segment.text.casefold() for segment in result.segments)
    assert result.ocr_pages == [1]
    assert "matematica" in recovered
    assert "universidad" in recovered
