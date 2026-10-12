"""Datos institucionales con alcance explícito para respuestas documentales."""

import json
from collections import Counter

from pydantic import BaseModel, ConfigDict, Field

from app.knowledge.registro_nacional import load_national_registry
from app.knowledge.registry import SERVICE_ROOT


class PublishedCareer(BaseModel):
    model_config = ConfigDict(extra="forbid")

    name: str
    url: str


class PublishedOffer(BaseModel):
    model_config = ConfigDict(extra="ignore")

    institution: str
    institution_key: str
    source_url: str
    checked_at: str
    careers: list[PublishedCareer] = Field(min_length=1)


class InstitutionReview(BaseModel):
    model_config = ConfigDict(extra="forbid")

    name: str
    website_verification_status: str
    departments: list[str]


class NationalRegistrySummary(BaseModel):
    model_config = ConfigDict(extra="forbid")

    institutions: list[InstitutionReview]
    by_department: dict[str, int]
    verified_domains: int
    coverage_note: str
    source_url: str | None


def unifranz_published_offer() -> PublishedOffer | None:
    path = SERVICE_ROOT / "data/catalog/ofertas_publicadas.json"
    if not path.is_file():
        return None
    offer = PublishedOffer.model_validate(json.loads(path.read_text(encoding="utf-8")))
    if (
        offer.institution_key != "unifranz"
        or offer.source_url != "https://unifranz.edu.bo/carreras/"
    ):
        raise ValueError("La oferta publicada no corresponde al índice oficial esperado.")
    if any(
        not career.url.startswith("https://unifranz.edu.bo/carreras/") for career in offer.careers
    ):
        raise ValueError("La oferta publicada contiene enlaces ajenos a UNIFRANZ.")
    return offer


def institutions_under_web_review() -> list[InstitutionReview]:
    registry = load_national_registry()
    pending = {
        "PENDING_CURRENT_VERIFICATION",
        "HISTORICAL_DOMAIN_WITH_CURRENT_UNRELATED_CONTENT",
    }
    return [
        InstitutionReview(
            name=item["name"],
            website_verification_status=item["website_verification_status"],
            departments=item.get("departments", []),
        )
        for item in registry["institutions"]
        if item.get("website_verification_status") in pending
    ]


def national_registry_summary() -> NationalRegistrySummary:
    registry = load_national_registry()
    if not registry.get("institutions"):
        raise ValueError("El registro nacional aún no está disponible para responder un conteo.")
    institutions = [
        InstitutionReview(
            name=item["name"],
            website_verification_status=item.get("website_verification_status", "SIN_VERIFICAR"),
            departments=item.get("departments", []),
        )
        for item in registry.get("institutions", [])
    ]
    source = next(
        (
            row.get("url")
            for row in registry.get("evidence_sources", [])
            if row.get("source_id") == "minedu_guia_2016"
        ),
        None,
    )
    return NationalRegistrySummary(
        institutions=institutions,
        by_department=dict(
            sorted(
                Counter(
                    department for item in institutions for department in set(item.departments)
                ).items()
            )
        ),
        verified_domains=sum(
            item.website_verification_status == "OFFICIAL_DOMAIN_VERIFIED" for item in institutions
        ),
        coverage_note=registry.get("coverage_note", ""),
        source_url=source,
    )
