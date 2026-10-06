from __future__ import annotations

import ipaddress
import json
import os
import re
import threading
import unicodedata
from datetime import UTC, datetime
from typing import Annotated, Literal
from urllib.parse import parse_qsl, urlencode, urlsplit, urlunsplit
from uuid import uuid4

from pydantic import BaseModel, ConfigDict, Field

from app.config import get_settings
from app.knowledge.registry import SERVICE_ROOT, load_source_manifest

SOURCE_INTAKE_REGISTRY = SERVICE_ROOT / "data" / "knowledge" / "source_intake.json"
_REGISTRY_LOCK = threading.Lock()
MAX_SOURCE_PROPOSALS = 1_000
LimitedText = Annotated[str, Field(min_length=3, max_length=500)]


class GovernanceModel(BaseModel):
    model_config = ConfigDict(extra="forbid", str_strip_whitespace=True)


class UniversityDomain(GovernanceModel):
    domain: str
    institution: str
    aliases: list[str] = Field(default_factory=list)


TRUSTED_BOLIVIAN_UNIVERSITY_DOMAINS = (
    UniversityDomain(
        domain="ucb.edu.bo",
        institution="Universidad Católica Boliviana San Pablo",
        aliases=["UCB", "Universidad Católica Boliviana"],
    ),
    UniversityDomain(
        domain="umsa.bo",
        institution="Universidad Mayor de San Andrés",
        aliases=["UMSA"],
    ),
    UniversityDomain(
        domain="upb.edu",
        institution="Universidad Privada Boliviana",
        aliases=["UPB"],
    ),
    UniversityDomain(
        domain="unifranz.edu.bo",
        institution="Universidad Franz Tamayo",
        aliases=["UNIFRANZ", "Franz Tamayo"],
    ),
    UniversityDomain(
        domain="emi.edu.bo",
        institution="Escuela Militar de Ingeniería",
        aliases=["EMI"],
    ),
)


class SourceUrlAssessment(GovernanceModel):
    host: str | None
    status: str = Field(pattern=r"^(ACEPTADA|REQUIERE_REVISION_DE_DOMINIO|RECHAZADA)$")
    is_bolivian_university: bool
    recognized_institution: str | None = None
    message: str


class KnowledgeSourceSummary(GovernanceModel):
    source_id: str
    institution: str
    title: str
    url: str
    verification_status: str


class SourceAnalysisCheck(GovernanceModel):
    code: str = Field(pattern=r"^[A-Z_]+$")
    label: str
    status: Literal["CUMPLE", "REVISAR", "BLOQUEA"]
    message: str


class SourceIntakeProposal(GovernanceModel):
    proposal_id: str = Field(pattern=r"^KGI-[A-F0-9]{12}$")
    title: str = Field(min_length=3, max_length=240)
    declared_institution: str = Field(min_length=3, max_length=240)
    url: str = Field(min_length=12, max_length=2000)
    source_type: Literal[
        "OFFICIAL_CURRICULUM_PDF",
        "OFFICIAL_CAREER_HTML",
        "OFFICIAL_REGULATION_PDF",
        "OFFICIAL_UNIVERSITY_PAGE",
    ]
    scope: str = Field(min_length=10, max_length=500)
    publication_date: str | None = Field(default=None, pattern=r"^\d{4}(?:-\d{2}(?:-\d{2})?)?$")
    version: str = Field(min_length=1, max_length=120)
    campus: str = Field(min_length=2, max_length=160)
    city: str = Field(min_length=2, max_length=120)
    justification: str = Field(min_length=20, max_length=1000)
    limitations: list[LimitedText] = Field(default_factory=list, max_length=10)
    submitted_by_role: str = Field(pattern=r"^(Administrador|Director)$")
    assessment: SourceUrlAssessment
    analysis_checks: list[SourceAnalysisCheck] = Field(default_factory=list)
    status: str = Field(
        pattern=(
            r"^(PENDIENTE_REVISION|PENDIENTE_VERIFICACION_DE_DOMINIO|"
            r"APROBADA_PENDIENTE_INGESTA|RECHAZADA)$"
        )
    )
    submitted_at: datetime
    reviewed_at: datetime | None = None
    reviewed_by_role: str | None = Field(default=None, pattern=r"^(Administrador)$")
    review_note: str | None = Field(default=None, max_length=800)


class SourceIntakeRegistry(GovernanceModel):
    registry_version: str = "1.0.0"
    updated_at: datetime
    proposals: list[SourceIntakeProposal] = Field(default_factory=list)


class KnowledgeGovernanceOverview(GovernanceModel):
    schema_version: str = "1.0"
    trace_id: str
    source_count: int
    sources: list[KnowledgeSourceSummary]
    trusted_universities: list[UniversityDomain]
    proposals: list[SourceIntakeProposal]
    workflow: list[str]


class SourceUrlValidationRequest(GovernanceModel):
    url: str = Field(min_length=12, max_length=2000)


class SourceUrlValidationResponse(GovernanceModel):
    schema_version: str = "1.0"
    trace_id: str
    assessment: SourceUrlAssessment


class SourceCandidateRequest(GovernanceModel):
    title: str = Field(min_length=3, max_length=240)
    declared_institution: str = Field(min_length=3, max_length=240)
    url: str = Field(min_length=12, max_length=2000)
    source_type: Literal[
        "OFFICIAL_CURRICULUM_PDF",
        "OFFICIAL_CAREER_HTML",
        "OFFICIAL_REGULATION_PDF",
        "OFFICIAL_UNIVERSITY_PAGE",
    ]
    scope: str = Field(min_length=10, max_length=500)
    publication_date: str | None = Field(default=None, pattern=r"^\d{4}(?:-\d{2}(?:-\d{2})?)?$")
    version: str = Field(min_length=1, max_length=120)
    campus: str = Field(min_length=2, max_length=160)
    city: str = Field(min_length=2, max_length=120)
    justification: str = Field(min_length=20, max_length=1000)
    limitations: list[LimitedText] = Field(default_factory=list, max_length=10)


class SourceProposalRequest(SourceCandidateRequest):
    submitted_by_role: str = Field(pattern=r"^(Administrador|Director)$")


class SourceAnalysisResponse(GovernanceModel):
    schema_version: str = "1.0"
    trace_id: str
    assessment: SourceUrlAssessment
    readiness: Literal["LISTA_PARA_PROPONER", "REQUIERE_REVISION", "BLOQUEADA"]
    risk_level: Literal["BAJO", "MEDIO", "ALTO"]
    can_submit: bool
    recognized_institution: str | None = None
    checks: list[SourceAnalysisCheck]
    suggestions: list[str]


class SourceProposalSubmissionResponse(GovernanceModel):
    schema_version: str = "1.0"
    trace_id: str
    accepted: bool
    message: str
    assessment: SourceUrlAssessment
    proposal: SourceIntakeProposal | None = None


class SourceProposalReviewRequest(GovernanceModel):
    approved: bool
    review_note: str = Field(min_length=3, max_length=800)
    reviewed_by_role: str = Field(pattern=r"^(Administrador)$")


class SourceProposalReviewResponse(GovernanceModel):
    schema_version: str = "1.0"
    trace_id: str
    message: str
    proposal: SourceIntakeProposal


def assess_bolivian_university_url(url: str) -> SourceUrlAssessment:
    if re.search(r"[\s\x00-\x1f\x7f\\]|%(?:0[0-9a-f]|1[0-9a-f]|7f|5c)", url, re.I):
        return SourceUrlAssessment(
            host=None,
            status="RECHAZADA",
            is_bolivian_university=False,
            message="La URL contiene espacios, controles o barras invertidas no permitidos.",
        )
    try:
        parsed = urlsplit(url)
        host = (parsed.hostname or "").casefold().rstrip(".")
        port = parsed.port
    except ValueError:
        return SourceUrlAssessment(
            host=None,
            status="RECHAZADA",
            is_bolivian_university=False,
            message="La dirección no tiene un host o puerto válido.",
        )

    if (
        parsed.scheme.casefold() != "https"
        or not host
        or parsed.username is not None
        or parsed.password is not None
        or port not in {None, 443}
        or re.fullmatch(
            r"(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}",
            host,
        )
        is None
    ):
        return SourceUrlAssessment(
            host=host or None,
            status="RECHAZADA",
            is_bolivian_university=False,
            message="Solo se aceptan direcciones HTTPS sin credenciales ni puertos no estándar.",
        )

    try:
        ipaddress.ip_address(host)
    except ValueError:
        pass
    else:
        return SourceUrlAssessment(
            host=host,
            status="RECHAZADA",
            is_bolivian_university=False,
            message="No se aceptan direcciones IP como fuente universitaria.",
        )

    for university in TRUSTED_BOLIVIAN_UNIVERSITY_DOMAINS:
        if host == university.domain or host.endswith(f".{university.domain}"):
            return SourceUrlAssessment(
                host=host,
                status="ACEPTADA",
                is_bolivian_university=True,
                recognized_institution=university.institution,
                message=(
                    "El dominio coincide con una universidad boliviana registrada. "
                    "Aún requiere revisión humana y una instantánea local antes de "
                    "entrar al corpus."
                ),
            )

    if host.endswith(".bo"):
        return SourceUrlAssessment(
            host=host,
            status="REQUIERE_REVISION_DE_DOMINIO",
            is_bolivian_university=False,
            message=(
                "El dominio boliviano no está registrado como universidad confiable. "
                "Queda en revisión; no puede incorporarse al corpus todavía."
            ),
        )

    return SourceUrlAssessment(
        host=host,
        status="RECHAZADA",
        is_bolivian_university=False,
        message=(
            "El dominio no corresponde a una universidad boliviana confiable. "
            "No se aceptan fuentes extranjeras, por ejemplo, de California."
        ),
    )


def analyze_source_candidate(payload: SourceCandidateRequest) -> SourceAnalysisResponse:
    assessment = assess_bolivian_university_url(payload.url)
    checks: list[SourceAnalysisCheck] = []
    suggestions: list[str] = []

    checks.append(
        SourceAnalysisCheck(
            code="OFFICIAL_DOMAIN",
            label="Dominio universitario",
            status={
                "ACEPTADA": "CUMPLE",
                "REQUIERE_REVISION_DE_DOMINIO": "REVISAR",
                "RECHAZADA": "BLOQUEA",
            }[assessment.status],
            message=assessment.message,
        )
    )

    university = _trusted_university_for_host(assessment.host)
    institution_matches = university is not None and _institution_matches(
        payload.declared_institution, university
    )
    if university is None:
        institution_status = "REVISAR"
        institution_message = (
            "El dominio no está en el registro institucional; la universidad declarada "
            "debe verificarse manualmente."
        )
    elif institution_matches:
        institution_status = "CUMPLE"
        institution_message = "La universidad declarada coincide con el dominio oficial."
    else:
        institution_status = "BLOQUEA"
        institution_message = (
            f"El dominio pertenece a {university.institution}, pero el formulario "
            f"declara {payload.declared_institution}."
        )
    checks.append(
        SourceAnalysisCheck(
            code="INSTITUTION_MATCH",
            label="Universidad declarada",
            status=institution_status,
            message=institution_message,
        )
    )
    if not institution_matches and university is not None:
        suggestions.append(f"Corrija la universidad a: {university.institution}.")

    url_rejected = assessment.status == "RECHAZADA"
    parsed = urlsplit("") if url_rejected else urlsplit(payload.url)
    is_pdf_url = parsed.path.casefold().endswith(".pdf")
    expects_pdf = payload.source_type.endswith("_PDF")
    format_matches = not expects_pdf or is_pdf_url
    checks.append(
        SourceAnalysisCheck(
            code="DOCUMENT_FORMAT",
            label="Formato declarado",
            status="CUMPLE" if format_matches else "REVISAR",
            message=(
                "El formato de la URL es coherente con el tipo documental."
                if format_matches
                else "Se declaró un PDF, pero la dirección no termina en .pdf. Revise el enlace."
            ),
        )
    )
    if not format_matches:
        suggestions.append("Confirme que la URL abre el PDF oficial sin redirecciones.")

    academic_text = " ".join([payload.title, payload.scope, payload.justification]).casefold()
    academic_terms = {
        "admisión",
        "admision",
        "carrera",
        "curricular",
        "currículo",
        "curriculo",
        "ingeniería",
        "ingenieria",
        "malla",
        "plan de estudios",
        "perfil profesional",
        "programa académico",
        "programa academico",
        "universidad",
    }
    academic_scope = any(term in academic_text for term in academic_terms)
    checks.append(
        SourceAnalysisCheck(
            code="ACADEMIC_SCOPE",
            label="Pertinencia académica",
            status="CUMPLE" if academic_scope else "REVISAR",
            message=(
                "El contenido declarado tiene finalidad académica o curricular."
                if academic_scope
                else "No se identifica claramente una finalidad académica o curricular."
            ),
        )
    )
    if not academic_scope:
        suggestions.append("Explique qué carrera, malla, perfil o requisito académico aporta.")

    future_publication = False
    invalid_publication = False
    if payload.publication_date:
        if len(payload.publication_date) == 10:
            publication_format = "%Y-%m-%d"
        elif len(payload.publication_date) == 7:
            publication_format = "%Y-%m"
        else:
            publication_format = "%Y"
        try:
            publication = datetime.strptime(payload.publication_date, publication_format)
        except ValueError:
            invalid_publication = True
        else:
            future_publication = publication.year > _now().year + 1
    publication_status = (
        "BLOQUEA" if invalid_publication else "REVISAR" if future_publication else "CUMPLE"
    )
    checks.append(
        SourceAnalysisCheck(
            code="PUBLICATION_DATE",
            label="Fecha de publicación",
            status=publication_status,
            message=(
                "La fecha declarada no existe en el calendario."
                if invalid_publication
                else "La fecha declarada parece futura y debe confirmarse."
                if future_publication
                else "La fecha o gestión declarada tiene un formato válido."
            ),
        )
    )
    if invalid_publication:
        suggestions.append("Corrija la fecha de publicación antes de enviar la fuente.")

    duplicate = not url_rejected and _source_url_exists(payload.url, _load_intake_registry())
    checks.append(
        SourceAnalysisCheck(
            code="DUPLICATE_SOURCE",
            label="Duplicidad",
            status="REVISAR" if url_rejected else "BLOQUEA" if duplicate else "CUMPLE",
            message=(
                "La duplicidad no se comprobó porque la URL está bloqueada."
                if url_rejected
                else "La dirección ya está registrada en el corpus o en la cola de revisión."
                if duplicate
                else "No se encontró otra fuente registrada con la misma dirección."
            ),
        )
    )

    blocked = any(check.status == "BLOQUEA" for check in checks)
    review = any(check.status == "REVISAR" for check in checks)
    readiness = "BLOQUEADA" if blocked else "REQUIERE_REVISION" if review else "LISTA_PARA_PROPONER"
    return SourceAnalysisResponse(
        trace_id="pending",
        assessment=assessment,
        readiness=readiness,
        risk_level="ALTO" if blocked else "MEDIO" if review else "BAJO",
        can_submit=not blocked,
        recognized_institution=assessment.recognized_institution,
        checks=checks,
        suggestions=suggestions,
    )


def governance_overview(trace_id: str) -> KnowledgeGovernanceOverview:
    manifest = load_source_manifest()
    registry = _load_intake_registry()
    sources = [
        KnowledgeSourceSummary(
            source_id=source.source_id,
            institution=source.institution,
            title=source.title,
            url=source.url,
            verification_status=source.verification_status,
        )
        for source in manifest.sources
    ]
    return KnowledgeGovernanceOverview(
        trace_id=trace_id,
        source_count=len(sources),
        sources=sources,
        trusted_universities=list(TRUSTED_BOLIVIAN_UNIVERSITY_DOMAINS),
        proposals=sorted(
            registry.proposals,
            key=lambda proposal: proposal.submitted_at,
            reverse=True,
        ),
        workflow=[
            "Validar dominio oficial boliviano.",
            "Registrar propuesta visible solo para Director y Administrador.",
            "Revisar y aprobar la propuesta desde Administración.",
            "Descargar una instantánea local, verificar SHA-256 y reconstruir el corpus BM25.",
        ],
    )


def submit_source_proposal(payload: SourceProposalRequest) -> SourceProposalSubmissionResponse:
    candidate = SourceCandidateRequest.model_validate(
        payload.model_dump(exclude={"submitted_by_role"})
    )
    analysis = analyze_source_candidate(candidate)
    if not analysis.can_submit:
        return SourceProposalSubmissionResponse(
            trace_id="pending",
            accepted=False,
            message="La fuente tiene observaciones bloqueantes y no puede enviarse a revisión.",
            assessment=analysis.assessment,
        )

    with _REGISTRY_LOCK:
        registry = _load_intake_registry()
        if len(registry.proposals) >= MAX_SOURCE_PROPOSALS:
            return SourceProposalSubmissionResponse(
                trace_id="pending",
                accepted=False,
                message="La cola alcanzó su capacidad y requiere depuración administrativa.",
                assessment=analysis.assessment,
            )
        if _source_url_exists(payload.url, registry):
            return SourceProposalSubmissionResponse(
                trace_id="pending",
                accepted=False,
                message="La dirección ya está registrada en el corpus o en la cola de revisión.",
                assessment=analysis.assessment,
            )
        proposal = SourceIntakeProposal(
            proposal_id=f"KGI-{uuid4().hex[:12].upper()}",
            **candidate.model_dump(),
            submitted_by_role=payload.submitted_by_role,
            assessment=analysis.assessment,
            analysis_checks=analysis.checks,
            status=(
                "PENDIENTE_REVISION"
                if analysis.assessment.status == "ACEPTADA"
                else "PENDIENTE_VERIFICACION_DE_DOMINIO"
            ),
            submitted_at=_now(),
        )
        registry.proposals.append(proposal)
        _write_intake_registry(registry)

    return SourceProposalSubmissionResponse(
        trace_id="pending",
        accepted=True,
        message=(
            "La propuesta quedó pendiente de revisión administrativa."
            if proposal.status == "PENDIENTE_REVISION"
            else "La propuesta quedó pendiente de verificar el dominio universitario."
        ),
        assessment=analysis.assessment,
        proposal=proposal,
    )


def review_source_proposal(
    proposal_id: str, payload: SourceProposalReviewRequest
) -> SourceIntakeProposal:
    with _REGISTRY_LOCK:
        registry = _load_intake_registry()
        proposal = next(
            (item for item in registry.proposals if item.proposal_id == proposal_id), None
        )
        if proposal is None:
            raise LookupError("La propuesta no existe.")
        if proposal.status not in {
            "PENDIENTE_REVISION",
            "PENDIENTE_VERIFICACION_DE_DOMINIO",
        }:
            raise ValueError("La propuesta ya fue revisada.")
        if payload.approved and proposal.assessment.status != "ACEPTADA":
            raise ValueError(
                "No se puede aprobar una fuente cuyo dominio universitario aún no fue verificado."
            )
        proposal.status = "APROBADA_PENDIENTE_INGESTA" if payload.approved else "RECHAZADA"
        proposal.reviewed_at = _now()
        proposal.reviewed_by_role = payload.reviewed_by_role
        proposal.review_note = payload.review_note
        _write_intake_registry(registry)
        return proposal


def _trusted_university_for_host(host: str | None) -> UniversityDomain | None:
    if host is None:
        return None
    return next(
        (
            university
            for university in TRUSTED_BOLIVIAN_UNIVERSITY_DOMAINS
            if host == university.domain or host.endswith(f".{university.domain}")
        ),
        None,
    )


def _institution_matches(value: str, university: UniversityDomain) -> bool:
    declared = _normalize_words(value)
    candidates = [university.institution, *university.aliases]
    return declared in {_normalize_words(candidate) for candidate in candidates}


def _normalize_words(value: str) -> str:
    normalized = unicodedata.normalize("NFKD", value.casefold())
    without_accents = "".join(
        character for character in normalized if not unicodedata.combining(character)
    )
    return re.sub(r"[^a-z0-9]+", " ", without_accents).strip()


def _source_url_exists(url: str, registry: SourceIntakeRegistry) -> bool:
    normalized = _normalize_url(url)
    manifest_urls = {_normalize_url(source.url) for source in load_source_manifest().sources}
    proposal_urls = {_normalize_url(proposal.url) for proposal in registry.proposals}
    return normalized in manifest_urls | proposal_urls


def _normalize_url(url: str) -> str:
    parsed = urlsplit(url.strip())
    host = (parsed.hostname or "").casefold().rstrip(".")
    port = parsed.port
    netloc = host if port in {None, 443} else f"{host}:{port}"
    path = parsed.path.rstrip("/") or "/"
    query = urlencode(sorted(parse_qsl(parsed.query, keep_blank_values=True)))
    return urlunsplit((parsed.scheme.casefold(), netloc, path, query, ""))


def _load_intake_registry() -> SourceIntakeRegistry:
    path = get_settings().source_intake_path or SOURCE_INTAKE_REGISTRY
    if not path.is_file():
        return SourceIntakeRegistry(updated_at=_now())
    return SourceIntakeRegistry.model_validate_json(path.read_text(encoding="utf-8"))


def _write_intake_registry(registry: SourceIntakeRegistry) -> None:
    registry.updated_at = _now()
    path = get_settings().source_intake_path or SOURCE_INTAKE_REGISTRY
    path.parent.mkdir(parents=True, exist_ok=True)
    temporary = path.with_suffix(".tmp")
    temporary.write_text(
        json.dumps(registry.model_dump(mode="json"), ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    os.replace(temporary, path)


def _now() -> datetime:
    return datetime.now(UTC)
