import hashlib
import json
from functools import lru_cache
from pathlib import Path
from typing import Any, Literal

from pydantic import BaseModel, ConfigDict, Field, model_validator

SERVICE_ROOT = Path(__file__).resolve().parents[2]
SOURCE_MANIFEST = SERVICE_ROOT / "data" / "sources" / "sources.json"
REFERENCE_REGISTRY = SERVICE_ROOT / "data" / "sources" / "references.json"
CAREER_CATALOG = SERVICE_ROOT / "data" / "catalog" / "careers.json"
EXTERNAL_INFORMATION_REGISTRY = SERVICE_ROOT / "data" / "sources" / "external_information.json"


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
    country: str = "Bolivia"
    authority_tier: str = "TIER_2_OFFICIAL_UNIVERSITY_CURRICULUM"
    scope: str | None = None
    verification_status: str = "VALID"
    reference: str | None = None
    sha256: str | None = None
    license: str | None = None
    superseded_by: str | None = None
    snapshot_timestamp: str | None = None
    final_url: str | None = None
    content_type: str | None = None
    evidence_layer: Literal["CORPUS_VALIDADO"] = "CORPUS_VALIDADO"
    limitations: list[str] = Field(default_factory=list)


class SourceManifest(RegistryModel):
    manifest_version: str
    retrieved_at: str
    governance_policy: str | None = None
    default_evidence_layer: Literal["CORPUS_VALIDADO"] = "CORPUS_VALIDADO"
    sources: list[SourceRecord] = Field(min_length=1)

    @model_validator(mode="after")
    def unique_source_ids(self) -> "SourceManifest":
        ids = [source.source_id for source in self.sources]
        if len(ids) != len(set(ids)):
            raise ValueError("source_id debe ser único")
        return self


class ExternalInformationRecord(RegistryModel):
    external_id: str = Field(min_length=1)
    evidence_layer: Literal["FUENTE_OFICIAL_EXTERNA", "WEB_NO_VERIFICADA"]
    institution: str = Field(min_length=1)
    title: str = Field(min_length=1)
    country: str = "Bolivia"
    url: str = Field(min_length=1)
    official: bool
    retrieval_attempted_at: str
    snapshot_status: str = Field(min_length=1)
    http_status: int | None = Field(default=None, ge=100, le=599)
    recommendation_eligible: Literal[False] = False
    limitations: list[str] = Field(min_length=1)

    @model_validator(mode="after")
    def external_information_is_never_recommendation_evidence(self) -> "ExternalInformationRecord":
        if self.evidence_layer == "FUENTE_OFICIAL_EXTERNA" and not self.official:
            raise ValueError("FUENTE_OFICIAL_EXTERNA requiere official=true")
        return self


class ExternalInformationRegistry(RegistryModel):
    registry_version: str = Field(min_length=1)
    retrieved_at: str
    purpose: str = Field(min_length=1)
    entries: list[ExternalInformationRecord] = Field(min_length=1)

    @model_validator(mode="after")
    def unique_external_ids(self) -> "ExternalInformationRegistry":
        ids = [entry.external_id for entry in self.entries]
        if len(ids) != len(set(ids)):
            raise ValueError("external_id debe ser unico")
        return self


class ReferenceRecord(RegistryModel):
    reference_id: str = Field(min_length=1)
    reference_kind: str = Field(pattern=r"^(EXTERNAL_OFFICIAL|EXTERNAL_METHOD|INTERNAL_ARTIFACT)$")
    organization: str = Field(min_length=1)
    title: str = Field(min_length=1)
    url: str | None = None
    local_path: str | None = None
    publication_date: str | None = None
    retrieved_at: str
    version: str = Field(min_length=1)
    license: str | None = None
    verification_status: str = Field(pattern=r"^(VERIFIED|LOCAL_ARTIFACT)$")
    limitations: list[str] = Field(default_factory=list)

    @model_validator(mode="after")
    def location_matches_kind(self) -> "ReferenceRecord":
        if self.reference_kind == "INTERNAL_ARTIFACT":
            if self.local_path is None:
                raise ValueError("una referencia interna requiere local_path")
        elif self.url is None:
            raise ValueError("una referencia externa requiere url")
        return self


class ReferenceRegistry(RegistryModel):
    registry_version: str
    retrieved_at: str
    references: list[ReferenceRecord] = Field(min_length=1)

    @model_validator(mode="after")
    def unique_reference_ids(self) -> "ReferenceRegistry":
        ids = [reference.reference_id for reference in self.references]
        if len(ids) != len(set(ids)):
            raise ValueError("reference_id debe ser único")
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
    degree: str | None
    duration_semesters: int | None = Field(default=None, gt=0)
    duration_text: str | None = None
    valid_from: str | None
    valid_until: str | None
    status: str
    entry_profile: str | None
    entry_profile_status: str
    professional_profile: str | None
    official_knowledge_areas: list[str] = Field(default_factory=list)
    initial_subjects: list[str] = Field(default_factory=list)
    source_ids: list[str] = Field(min_length=1)
    evidence_layer: Literal["CORPUS_VALIDADO", "FUENTE_OFICIAL_EXTERNA", "WEB_NO_VERIFICADA"] = (
        "CORPUS_VALIDADO"
    )
    recommendation_eligible: bool = True

    @model_validator(mode="after")
    def recommendation_requires_validated_corpus(self) -> "Career":
        if self.recommendation_eligible and self.evidence_layer != "CORPUS_VALIDADO":
            raise ValueError("una carrera elegible para Recommendation V2 requiere CORPUS_VALIDADO")
        if not self.recommendation_eligible and self.evidence_layer == "CORPUS_VALIDADO":
            raise ValueError(
                "CORPUS_VALIDADO no puede quedar excluido de Recommendation V2 "
                "sin una regla explicita"
            )
        return self


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
def load_reference_registry() -> ReferenceRegistry:
    registry = ReferenceRegistry.model_validate(_read_json(REFERENCE_REGISTRY))
    missing = [
        reference.reference_id
        for reference in registry.references
        if reference.local_path is not None and not (SERVICE_ROOT / reference.local_path).is_file()
    ]
    if missing:
        raise ValueError(f"artefactos internos ausentes: {sorted(missing)}")
    return registry


@lru_cache(maxsize=1)
def load_external_information_registry() -> ExternalInformationRegistry:
    registry = ExternalInformationRegistry.model_validate(_read_json(EXTERNAL_INFORMATION_REGISTRY))
    source_ids = {source.source_id for source in load_source_manifest().sources}
    reference_ids = {reference.reference_id for reference in load_reference_registry().references}
    external_ids = {entry.external_id for entry in registry.entries}
    overlap = external_ids & (source_ids | reference_ids)
    if overlap:
        raise ValueError(f"IDs externos duplicados: {sorted(overlap)}")
    return registry


def known_evidence_reference_ids() -> set[str]:
    """Return every governed local-source and methodological reference ID."""
    source_ids = {source.source_id for source in load_source_manifest().sources}
    reference_ids = {reference.reference_id for reference in load_reference_registry().references}
    overlap = source_ids & reference_ids
    if overlap:
        raise ValueError(f"IDs duplicados entre registros: {sorted(overlap)}")
    return source_ids | reference_ids


def known_external_information_ids() -> set[str]:
    """Return IDs that are informational only and excluded from Recommendation V2."""
    return {entry.external_id for entry in load_external_information_registry().entries}


@lru_cache(maxsize=1)
def load_career_catalog() -> CareerCatalog:
    catalog = CareerCatalog.model_validate(_read_json(CAREER_CATALOG))
    source_ids = {source.source_id for source in load_source_manifest().sources}
    external_ids = known_external_information_ids()
    unknown = {
        source_id
        for career in catalog.careers
        if career.recommendation_eligible
        for source_id in career.source_ids
        if source_id not in source_ids
    } | {
        source_id
        for career in catalog.careers
        if not career.recommendation_eligible
        for source_id in career.source_ids
        if source_id not in external_ids
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


def calculate_source_quality_metrics() -> dict[str, Any]:
    manifest = load_source_manifest()
    external_registry = load_external_information_registry()
    total = len(manifest.sources)
    if total == 0:
        return {"source_count": 0, "official_source_ratio": 0.0}

    official_count = sum(1 for s in manifest.sources if s.official)
    hash_errors = validate_local_source_hashes()
    missing_sources = [
        s.source_id for s in manifest.sources if not (SERVICE_ROOT / s.local_path).is_file()
    ]
    superseded = [s.source_id for s in manifest.sources if s.superseded_by]
    unverified = [s.source_id for s in manifest.sources if s.verification_status != "VALID"]
    layer_distribution: dict[str, int] = {"CORPUS_VALIDADO": total}
    for entry in external_registry.entries:
        layer_distribution[entry.evidence_layer] = (
            layer_distribution.get(entry.evidence_layer, 0) + 1
        )

    tier_distribution: dict[str, int] = {}
    for s in manifest.sources:
        tier_distribution[s.authority_tier] = tier_distribution.get(s.authority_tier, 0) + 1

    # Check completeness of core metadata fields
    fields_to_check = [
        "source_id",
        "title",
        "institution",
        "authority_tier",
        "source_type",
        "publication_date",
        "retrieved_at",
        "document_hash",
        "local_path",
    ]
    total_checks = total * len(fields_to_check)
    passed_checks = sum(
        1 for s in manifest.sources for f in fields_to_check if getattr(s, f, None) is not None
    )

    return {
        "source_count": total,
        "official_source_count": official_count,
        "official_source_ratio": round(official_count / total, 4),
        "hash_validity": "ALL_VALID" if not hash_errors else f"ERRORS: {hash_errors}",
        "missing_local_sources": missing_sources,
        "superseded_sources": superseded,
        "unverified_sources": unverified,
        "external_information_count": len(external_registry.entries),
        "recommendation_eligible_source_count": total,
        "evidence_layer_distribution": layer_distribution,
        "tier_distribution": tier_distribution,
        "metadata_completeness_ratio": (
            round(passed_checks / total_checks, 4) if total_checks else 0.0
        ),
    }
