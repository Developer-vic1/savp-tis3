import hashlib
import json
from functools import lru_cache
from pathlib import Path

from pydantic import BaseModel, ConfigDict, Field, model_validator

SERVICE_ROOT = Path(__file__).resolve().parents[2]
SOURCE_MANIFEST = SERVICE_ROOT / "data" / "sources" / "sources.json"
CAREER_CATALOG = SERVICE_ROOT / "data" / "catalog" / "careers.json"


class RegistryModel(BaseModel):
    model_config = ConfigDict(extra="forbid")


class SourceRecord(RegistryModel):
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
    document_hash: str = Field(pattern=r"^sha256:[0-9a-f]{64}$")
    section: str | None
    page: int | None
    version: str
    status: str
    local_path: str


class SourceManifest(RegistryModel):
    manifest_version: str
    retrieved_at: str
    sources: list[SourceRecord] = Field(min_length=1)

    @model_validator(mode="after")
    def unique_source_ids(self) -> "SourceManifest":
        ids = [source.source_id for source in self.sources]
        if len(ids) != len(set(ids)):
            raise ValueError("source_id debe ser único")
        return self


class University(RegistryModel):
    university_id: str
    name: str
    campus: str
    city: str


class Career(RegistryModel):
    career_id: str
    university_id: str
    name: str
    aliases: list[str]
    degree: str
    duration_semesters: int = Field(gt=0)
    valid_from: str
    valid_until: str | None
    status: str
    entry_profile: str | None
    entry_profile_status: str
    professional_profile: str | None
    official_knowledge_areas: list[str] = Field(min_length=1)
    initial_subjects: list[str] = Field(min_length=1)
    source_ids: list[str] = Field(min_length=1)


class CareerCatalog(RegistryModel):
    catalog_version: str
    generated_at: str
    scope_note: str
    universities: list[University] = Field(min_length=1)
    careers: list[Career] = Field(min_length=1)

    @model_validator(mode="after")
    def unique_catalog_ids(self) -> "CareerCatalog":
        university_ids = [university.university_id for university in self.universities]
        career_ids = [career.career_id for career in self.careers]
        if len(university_ids) != len(set(university_ids)):
            raise ValueError("university_id debe ser único")
        if len(career_ids) != len(set(career_ids)):
            raise ValueError("career_id debe ser único")
        unknown = {
            career.university_id
            for career in self.careers
            if career.university_id not in university_ids
        }
        if unknown:
            raise ValueError(f"universidades desconocidas: {sorted(unknown)}")
        return self


def _read_json(path: Path) -> object:
    with path.open(encoding="utf-8") as handle:
        return json.load(handle)


@lru_cache(maxsize=1)
def load_source_manifest() -> SourceManifest:
    return SourceManifest.model_validate(_read_json(SOURCE_MANIFEST))


@lru_cache(maxsize=1)
def load_career_catalog() -> CareerCatalog:
    catalog = CareerCatalog.model_validate(_read_json(CAREER_CATALOG))
    source_ids = {source.source_id for source in load_source_manifest().sources}
    unknown = {
        source_id
        for career in catalog.careers
        for source_id in career.source_ids
        if source_id not in source_ids
    }
    if unknown:
        raise ValueError(f"fuentes desconocidas en catálogo: {sorted(unknown)}")
    return catalog


def validate_local_source_hashes() -> list[str]:
    errors: list[str] = []
    for source in load_source_manifest().sources:
        path = SERVICE_ROOT / source.local_path
        if not path.is_file():
            errors.append(f"{source.source_id}: archivo ausente")
            continue
        digest = hashlib.sha256(path.read_bytes()).hexdigest()
        expected = source.document_hash.removeprefix("sha256:")
        if digest != expected:
            errors.append(f"{source.source_id}: hash no coincide")
    return errors
