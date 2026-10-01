import json
from functools import lru_cache
from pathlib import Path

from pydantic import BaseModel, ConfigDict, Field, model_validator

from app.knowledge.registry import load_career_catalog, load_source_manifest

SERVICE_ROOT = Path(__file__).resolve().parents[2]
BRIDGE_PATH = SERVICE_ROOT / "data" / "bridge" / "secondary_university_v1.json"
CRITERIA_PATH = SERVICE_ROOT / "data" / "criteria" / "recommendation_v1.json"
ALLOWED_EVIDENCE_LABELS = {
    "HECHO DOCUMENTADO",
    "DECISIÓN DE DISEÑO",
    "CONFIGURACIÓN EXPERIMENTAL",
    "HIPÓTESIS",
}


class ConfigModel(BaseModel):
    model_config = ConfigDict(extra="forbid")


class BridgeRelation(ConfigModel):
    relation_id: str
    secondary_content: str
    competency: str
    university_knowledge: str
    initial_subject: str
    career_ids: list[str] = Field(min_length=1)
    source_secondary: str
    source_university: str
    justification: str
    weight: float = Field(gt=0, le=1)
    confidence_status: str
    review_status: str
    evidence_label: str
    version: str


class Bridge(ConfigModel):
    bridge_version: str
    status: str
    review_status: str
    relations: list[BridgeRelation] = Field(min_length=1)

    @model_validator(mode="after")
    def validate_relations(self) -> "Bridge":
        ids = [relation.relation_id for relation in self.relations]
        if len(ids) != len(set(ids)):
            raise ValueError("relation_id debe ser único")
        invalid_labels = {
            relation.evidence_label
            for relation in self.relations
            if relation.evidence_label not in ALLOWED_EVIDENCE_LABELS
        }
        if invalid_labels:
            raise ValueError(f"etiquetas de evidencia inválidas: {sorted(invalid_labels)}")
        return self


class PreparationRequirement(ConfigModel):
    competency: str
    subject_aliases: list[str]
    area_aliases: list[str]
    required: float = Field(ge=0, le=100)
    weight: float = Field(gt=0, le=1)
    relation_id: str
    topic: str
    prerequisite: str | None


class CareerCriteria(ConfigModel):
    riasec_target: dict[str, float]
    bth_tags: list[str]
    interest_keywords: list[str]
    requirements: list[PreparationRequirement] = Field(min_length=1)

    @model_validator(mode="after")
    def validate_profile(self) -> "CareerCriteria":
        if set(self.riasec_target) != set("RIASEC"):
            raise ValueError("riasec_target debe contener R, I, A, S, E y C")
        if any(not 0 <= score <= 100 for score in self.riasec_target.values()):
            raise ValueError("los targets RIASEC deben encontrarse en 0–100")
        if abs(sum(requirement.weight for requirement in self.requirements) - 1) > 1e-9:
            raise ValueError("los pesos de requisitos deben sumar 1")
        return self


class RecommendationCriteria(ConfigModel):
    criteria_version: str
    status: str
    review_status: str
    affinity_weights: dict[str, float]
    preparation_component_weights: dict[str, float]
    compatibility_weights: dict[str, float]
    minimum_affinity_coverage: float = Field(gt=0, le=1)
    minimum_preparation_coverage: float = Field(gt=0, le=1)
    career_profiles: dict[str, CareerCriteria]

    @model_validator(mode="after")
    def validate_weights(self) -> "RecommendationCriteria":
        for name, weights in (
            ("affinity_weights", self.affinity_weights),
            ("preparation_component_weights", self.preparation_component_weights),
            ("compatibility_weights", self.compatibility_weights),
        ):
            if abs(sum(weights.values()) - 1) > 1e-9:
                raise ValueError(f"{name} debe sumar 1")
        return self


def _read_json(path: Path) -> object:
    with path.open(encoding="utf-8") as handle:
        return json.load(handle)


@lru_cache(maxsize=1)
def load_bridge() -> Bridge:
    bridge = Bridge.model_validate(_read_json(BRIDGE_PATH))
    sources = {source.source_id for source in load_source_manifest().sources}
    careers = {career.career_id for career in load_career_catalog().careers}
    unknown_sources = {
        source_id
        for relation in bridge.relations
        for source_id in (relation.source_secondary, relation.source_university)
        if source_id not in sources
    }
    unknown_careers = {
        career_id
        for relation in bridge.relations
        for career_id in relation.career_ids
        if career_id not in careers
    }
    if unknown_sources or unknown_careers:
        raise ValueError(
            f"referencias inválidas: sources={sorted(unknown_sources)}, "
            f"careers={sorted(unknown_careers)}"
        )
    return bridge


@lru_cache(maxsize=1)
def load_recommendation_criteria() -> RecommendationCriteria:
    criteria = RecommendationCriteria.model_validate(_read_json(CRITERIA_PATH))
    career_ids = {career.career_id for career in load_career_catalog().careers}
    if set(criteria.career_profiles) != career_ids:
        raise ValueError("los perfiles de criterios deben coincidir con el catálogo")
    relations = {relation.relation_id: relation for relation in load_bridge().relations}
    for career_id, profile in criteria.career_profiles.items():
        for requirement in profile.requirements:
            relation = relations.get(requirement.relation_id)
            if relation is None or career_id not in relation.career_ids:
                raise ValueError(
                    f"relación {requirement.relation_id} no aplica a {career_id}"
                )
    return criteria
