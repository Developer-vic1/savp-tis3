from __future__ import annotations

import re
from pathlib import Path
from typing import TYPE_CHECKING

from bs4 import BeautifulSoup
from PIL import Image

from app.ingestion.models import DocumentExtraction, ExtractedSegment
from app.knowledge.registry import SERVICE_ROOT, SourceRecord

if TYPE_CHECKING:
    import pymupdf

DIGITAL_TEXT_THRESHOLD = 80
OCR_DPI = 200


def _clean_text(value: str) -> str:
    lines = [re.sub(r"\s+", " ", line).strip() for line in value.splitlines()]
    return "\n".join(line for line in lines if line)


def _render_page(page: pymupdf.Page, dpi: int = OCR_DPI) -> Image.Image:
    pixmap = page.get_pixmap(dpi=dpi, alpha=False)
    return Image.frombytes("RGB", (pixmap.width, pixmap.height), pixmap.samples)


def extract_pdf(
    path: Path,
    source_id: str,
    max_pages: int | None = None,
) -> DocumentExtraction:
    import pymupdf

    segments: list[ExtractedSegment] = []
    digital_pages: list[int] = []
    ocr_pages: list[int] = []
    warnings: list[str] = []
    if max_pages is not None and max_pages < 1:
        raise ValueError("max_pages debe ser mayor que cero")
    with pymupdf.open(path) as document:  # type: ignore[no-untyped-call]
        for index, page in enumerate(document):
            if max_pages is not None and index >= max_pages:
                warnings.append(
                    f"Se revisaron {max_pages} de {document.page_count} páginas; "
                    "el resto requiere otra revisión documental."
                )
                break
            page_number = index + 1
            digital_text = _clean_text(page.get_text("text"))
            alphanumeric_count = sum(character.isalnum() for character in digital_text)
            if alphanumeric_count >= DIGITAL_TEXT_THRESHOLD:
                segments.append(
                    ExtractedSegment(
                        text=digital_text,
                        page=page_number,
                        extraction_method="DIGITAL",
                    )
                )
                digital_pages.append(page_number)
                continue

            warnings.append(
                f"Página {page_number} sin texto digital recuperable; OCR automático deshabilitado."
            )
    return DocumentExtraction(
        source_id=source_id,
        segments=segments,
        digital_pages=digital_pages,
        ocr_pages=ocr_pages,
        warnings=warnings,
    )


def extract_html(path: Path, source_id: str) -> DocumentExtraction:
    soup = BeautifulSoup(path.read_text(encoding="utf-8", errors="replace"), "lxml")
    for unwanted in soup(["script", "style", "nav", "footer", "header", "noscript"]):
        unwanted.decompose()
    root = soup.find("main") or soup.find("article") or soup.body or soup
    section: str | None = None
    segments: list[ExtractedSegment] = []
    paragraphs: list[str] = []
    stop_sections = {"plantel docente", "noticias"}

    def flush() -> None:
        if not paragraphs:
            if section is None:
                return
            paragraphs.append(section)
        segments.append(
            ExtractedSegment(
                text=_clean_text("\n".join(paragraphs)),
                section=section,
                extraction_method="HTML",
            )
        )
        paragraphs.clear()

    headings = {"h1", "h2", "h3", "h4", "h5", "h6"}
    blocks = [*sorted(headings), "p", "li", "div"]
    for node in root.find_all(blocks):
        if node.name == "div" and (
            node.find(blocks) is not None or node.find_parent(["p", "li"]) is not None
        ):
            continue
        text = _clean_text(node.get_text(" ", strip=True))
        if not text:
            continue
        if node.name in headings:
            flush()
            if text.casefold() in stop_sections:
                section = None
                break
            section = text
            continue
        if not paragraphs or paragraphs[-1] != text:
            paragraphs.append(text)
    flush()
    return DocumentExtraction(
        source_id=source_id,
        segments=segments,
        digital_pages=[],
        ocr_pages=[],
        warnings=[] if segments else ["HTML sin contenido textual recuperable."],
    )


def extract_text(path: Path, source_id: str) -> DocumentExtraction:
    section: str | None = None
    paragraphs: list[str] = []
    segments: list[ExtractedSegment] = []

    def flush() -> None:
        if paragraphs:
            segments.append(
                ExtractedSegment(
                    text=_clean_text(" ".join(paragraphs)),
                    section=section,
                    extraction_method="TEXT",
                )
            )
            paragraphs.clear()

    for raw_line in path.read_text(encoding="utf-8", errors="replace").splitlines():
        line = raw_line.strip()
        if line.startswith("#"):
            flush()
            section = line.lstrip("#").strip() or None
        elif not line:
            flush()
        else:
            paragraphs.append(line)
    flush()
    return DocumentExtraction(
        source_id=source_id,
        segments=segments,
        digital_pages=[],
        ocr_pages=[],
        warnings=[] if segments else ["Documento de texto vacío."],
    )


def extract_source(
    source: SourceRecord,
) -> DocumentExtraction:
    path = SERVICE_ROOT / source.local_path
    suffix = path.suffix.casefold()
    if suffix == ".pdf":
        return extract_pdf(path, source.source_id)
    if suffix in {".html", ".htm"}:
        return extract_html(path, source.source_id)
    if suffix in {".txt", ".md"}:
        return extract_text(path, source.source_id)
    raise ValueError(f"Tipo documental no soportado: {suffix or '<sin extensión>'}")


def render_pdf_page(path: Path, page_number: int, output: Path, dpi: int = 144) -> None:
    import pymupdf

    with pymupdf.open(path) as document:  # type: ignore[no-untyped-call]
        if page_number < 1 or page_number > document.page_count:
            raise ValueError(f"Página fuera de rango: {page_number}")
        output.parent.mkdir(parents=True, exist_ok=True)
        image = _render_page(document[page_number - 1], dpi=dpi)
        image.save(output)
