from typing import Literal

from pydantic import BaseModel, ConfigDict, Field


class IngestionModel(BaseModel):
    model_config = ConfigDict(extra="forbid")


class ExtractedSegment(IngestionModel):
    text: str = Field(min_length=1)
    page: int | None = Field(default=None, gt=0)
    section: str | None = None
    extraction_method: Literal["DIGITAL", "OCR", "HTML", "TEXT"]
    confidence: float | None = Field(default=None, ge=0, le=1)
    ocr_engine: str | None = None
    ocr_language: str | None = None
    ocr_version: str | None = None


class DocumentExtraction(IngestionModel):
    source_id: str
    segments: list[ExtractedSegment]
    digital_pages: list[int]
    ocr_pages: list[int]
    warnings: list[str]


class KnowledgeChunk(IngestionModel):
    chunk_id: str
    source_id: str
    institution: str
    title: str
    source_type: str
    url: str
    official: bool
    publication_date: str | None
    retrieved_at: str
    valid_from: str | None
    valid_until: str | None
    document_hash: str
    section: str | None
    page: int | None
    version: str
    status: str
    extraction_method: Literal["DIGITAL", "OCR", "HTML", "TEXT"]
    extraction_confidence: float | None = Field(default=None, ge=0, le=1)
    ocr_engine: str | None = None
    ocr_language: str | None = None
    ocr_version: str | None = None
    ordinal: int = Field(gt=0)
    text: str = Field(min_length=1)
