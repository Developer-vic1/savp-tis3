import hashlib
import re

from app.ingestion.models import DocumentExtraction, KnowledgeChunk
from app.knowledge.registry import SourceRecord


def _split_text(text: str, target_chars: int, overlap_chars: int) -> list[str]:
    normalized = re.sub(r"\s+", " ", text).strip()
    if not normalized:
        return []
    if len(normalized) <= target_chars:
        return [normalized]
    chunks: list[str] = []
    start = 0
    while start < len(normalized):
        end = min(start + target_chars, len(normalized))
        if end < len(normalized):
            boundary = normalized.rfind(" ", start, end)
            if boundary > start:
                end = boundary
        piece = normalized[start:end].strip()
        if piece:
            chunks.append(piece)
        if end >= len(normalized):
            break
        next_start = max(0, end - overlap_chars)
        boundary = normalized.find(" ", next_start, end)
        start = boundary + 1 if boundary >= 0 else end
    return chunks


def build_chunks(
    extraction: DocumentExtraction,
    source: SourceRecord,
    target_chars: int = 1200,
    overlap_chars: int = 180,
) -> list[KnowledgeChunk]:
    if target_chars < 200:
        raise ValueError("target_chars debe ser al menos 200")
    if overlap_chars < 0 or overlap_chars >= target_chars:
        raise ValueError("overlap_chars debe encontrarse entre 0 y target_chars")
    chunks: list[KnowledgeChunk] = []
    ordinal = 0
    for segment in extraction.segments:
        for text in _split_text(segment.text, target_chars, overlap_chars):
            ordinal += 1
            identity = "|".join(
                (
                    source.source_id,
                    str(segment.page or ""),
                    segment.section or "",
                    str(ordinal),
                    text,
                )
            )
            chunk_id = hashlib.sha256(identity.encode("utf-8")).hexdigest()[:24]
            chunks.append(
                KnowledgeChunk(
                    chunk_id=chunk_id,
                    source_id=source.source_id,
                    institution=source.institution,
                    title=source.title,
                    source_type=source.source_type,
                    url=source.url,
                    official=source.official,
                    publication_date=source.publication_date,
                    retrieved_at=source.retrieved_at,
                    valid_from=source.valid_from,
                    valid_until=source.valid_until,
                    document_hash=source.document_hash,
                    section=segment.section or source.section,
                    page=segment.page or source.page,
                    version=source.version,
                    status=source.status,
                    extraction_method=segment.extraction_method,
                    extraction_confidence=segment.confidence,
                    ocr_engine=segment.ocr_engine,
                    ocr_language=segment.ocr_language,
                    ocr_version=segment.ocr_version,
                    ordinal=ordinal,
                    text=text,
                )
            )
    return chunks
