import re
import unicodedata
from statistics import fmean

from app.contracts.requests import AcademicRecord, AnalysisRequest
from app.contracts.responses import (
    AcademicProfile,
    CareerRecommendation,
    CompetencyGap,
    EvidenceItem,
    PreparationRouteItem,
    RiasecProfile,
)
from app.knowledge.registry import Career, load_career_catalog
from app.learning_analytics.academic_performance import normalize_score
from app.recommendation.config import (
    BridgeRelation,
    CareerCriteria,
    PreparationRequirement,
    RecommendationCriteria,
    load_bridge,
    load_recommendation_criteria,
)


def _rounded(value: float) -> float:
    return round(value, 2)


def _normalize_text(value: str) -> str:
    folded = unicodedata.normalize("NFKD", value.casefold())
    without_marks = "".join(
        character for character in folded if not unicodedata.combining(character)
    )
    return re.sub(r"[^a-z0-9]+", " ", without_marks).strip()


def _token_similarity(left: str, right: str) -> float:
    left_tokens = set(_normalize_text(left).split())
    right_tokens = set(_normalize_text(right).split())
    if not left_tokens or not right_tokens:
        return 0.0
    return 100 * len(left_tokens & right_tokens) / len(left_tokens | right_tokens)


def _alignment(evidence: list[str], targets: list[str], *, aggregate: str) -> float | None:
    if not evidence:
        return None
    best_by_evidence = [
        max((_token_similarity(item, target) for target in targets), default=0.0)
        for item in evidence
    ]
    value = max(best_by_evidence) if aggregate == "max" else fmean(best_by_evidence)
    return _rounded(value)


def _weighted_available(
    values: dict[str, float | None], weights: dict[str, float]
) -> float | None:
    observed = {name: value for name, value in values.items() if value is not None}
    denominator = sum(weights[name] for name in observed)
    if not observed or denominator == 0:
        return None
    numerator = sum(float(value) * weights[name] for name, value in observed.items())
    return _rounded(numerator / denominator)


def _affinity(
    request: AnalysisRequest,
    vocational: RiasecProfile | None,
    profile: CareerCriteria,
    criteria: RecommendationCriteria,
) -> tuple[float | None, float, dict[str, float | None]]:
    riasec = None
    if vocational is not None:
        differences = [
            abs(vocational.normalized_scores[code] - profile.riasec_target[code])
            for code in "RIASEC"
        ]
        riasec = _rounded(max(0.0, 100 - fmean(differences)))

    technical_evidence: list[str] = []
    if request.technical is not None:
        if request.technical.specialty:
            technical_evidence.append(request.technical.specialty)
        technical_evidence.extend(item.name for item in request.technical.competencies)
    bth = _alignment(technical_evidence, profile.bth_tags, aggregate="max")
    interests = _alignment(
        request.declared_interests or [],
        profile.interest_keywords,
        aggregate="mean",
    )
    components = {"riasec": riasec, "bth": bth, "declared_interest": interests}
    coverage = _rounded(
        sum(
            criteria.affinity_weights[name]
            for name, value in components.items()
            if value is not None
        )
    )
    if coverage < criteria.minimum_affinity_coverage:
        return None, coverage, components
    return _weighted_available(components, criteria.affinity_weights), coverage, components


def _record_matches(record: AcademicRecord, requirement: PreparationRequirement) -> bool:
    subject = _normalize_text(record.subject)
    subject_aliases = {_normalize_text(alias) for alias in requirement.subject_aliases}
    area_aliases = {_normalize_text(alias) for alias in requirement.area_aliases}
    if any(alias in subject or subject in alias for alias in subject_aliases):
        return True
    return subject in area_aliases


def _current_scores(
    request: AnalysisRequest, profile: CareerCriteria
) -> dict[str, tuple[float | None, list[str]]]:
    values: dict[str, tuple[float | None, list[str]]] = {}
    records = request.academic.records if request.academic else []
    for requirement in profile.requirements:
        matches = [record for record in records if _record_matches(record, requirement)]
        scores = [normalize_score(record) for record in matches]
        evidence = [f"{record.subject}: {normalize_score(record)}/100" for record in matches]
        values[requirement.competency] = (
            _rounded(fmean(scores)) if scores else None,
            evidence,
        )
    return values


def _gap_priority(magnitude: float | None) -> str:
    if magnitude is None:
        return "UNKNOWN"
    if magnitude >= 20:
        return "HIGH"
    if magnitude > 0:
        return "MEDIUM"
    return "ACHIEVED"


def _build_gaps(
    profile: CareerCriteria,
    current_scores: dict[str, tuple[float | None, list[str]]],
    relations: dict[str, BridgeRelation],
) -> list[CompetencyGap]:
    gaps: list[CompetencyGap] = []
    for requirement in profile.requirements:
        current, evidence_rows = current_scores[requirement.competency]
        magnitude = (
            _rounded(max(0.0, requirement.required - current))
            if current is not None
            else None
        )
        relation = relations[requirement.relation_id]
        gaps.append(
            CompetencyGap(
                competency=requirement.competency,
                current=current,
                required=requirement.required,
                magnitude=magnitude,
                priority=_gap_priority(magnitude),
                evidence="; ".join(evidence_rows) if evidence_rows else "Sin evidencia observada.",
                source=requirement.relation_id,
                explanation=(
                    "Área que puede reforzarse según la diferencia observada."
                    if magnitude and magnitude > 0
                    else (
                        "La evidencia observada alcanza el umbral experimental."
                        if magnitude == 0
                        else "No existe evidencia suficiente para estimar la brecha."
                    )
                ),
                status="OBSERVED" if current is not None else "INSUFFICIENT_EVIDENCE",
            )
        )
        if relation.review_status != "NEEDS_EXPERT_REVIEW":
            raise ValueError("El puente experimental debe conservar revisión pendiente")
    return gaps


def _preparation(
    request: AnalysisRequest,
    academic: AcademicProfile | None,
    profile: CareerCriteria,
    criteria: RecommendationCriteria,
    relations: dict[str, BridgeRelation],
) -> tuple[
    float | None,
    float,
    dict[str, float | None],
    list[CompetencyGap],
    dict[str, tuple[float | None, list[str]]],
]:
    current = _current_scores(request, profile)
    covered_weight = sum(
        requirement.weight
        for requirement in profile.requirements
        if current[requirement.competency][0] is not None
    )
    coverage = _rounded(covered_weight)
    gaps = _build_gaps(profile, current, relations)
    if academic is None or coverage < criteria.minimum_preparation_coverage:
        empty_components: dict[str, float | None] = {
            "knowledge": None,
            "consistency": None,
            "temporal": None,
        }
        return None, coverage, empty_components, gaps, current

    knowledge_numerator = 0.0
    for requirement in profile.requirements:
        current_score = current[requirement.competency][0]
        if current_score is not None:
            knowledge_numerator += current_score * requirement.weight
    knowledge = _rounded(knowledge_numerator / covered_weight)
    components = {
        "knowledge": knowledge,
        "consistency": (
            _rounded(academic.consistency_ratio * 100)
            if academic.consistency_ratio is not None
            else None
        ),
        "temporal": (
            _rounded(academic.temporal_coverage.ratio * 100)
            if academic.temporal_coverage.ratio is not None
            else None
        ),
    }
    return (
        _weighted_available(components, criteria.preparation_component_weights),
        coverage,
        components,
        gaps,
        current,
    )


def _strengths(
    vocational: RiasecProfile | None,
    bth_score: float | None,
    profile: CareerCriteria,
    current: dict[str, tuple[float | None, list[str]]],
) -> list[EvidenceItem]:
    strengths: list[EvidenceItem] = []
    if vocational is not None:
        strengths.append(
            EvidenceItem(
                code="RIASEC_TOP",
                label="Intereses RIASEC destacados",
                evidence=", ".join(vocational.top_codes),
                source_context=f"{vocational.instrument_id}:{vocational.instrument_version}",
            )
        )
    if bth_score is not None and bth_score >= 60:
        strengths.append(
            EvidenceItem(
                code="BTH_ALIGNMENT",
                label="Formación BTH relacionada",
                evidence=f"Alineación léxica experimental: {bth_score}/100.",
                source_context="Especialidad y competencias técnicas declaradas",
            )
        )
    for requirement in profile.requirements:
        score, evidence = current[requirement.competency]
        if score is not None and score >= requirement.required:
            strengths.append(
                EvidenceItem(
                    code="ACADEMIC_THRESHOLD",
                    label=f"Evidencia en {requirement.competency}",
                    evidence="; ".join(evidence),
                    source_context=requirement.relation_id,
                )
            )
    return strengths


def _route(
    gaps: list[CompetencyGap], profile: CareerCriteria
) -> list[PreparationRouteItem]:
    requirement_by_competency = {
        requirement.competency: requirement for requirement in profile.requirements
    }
    pending = [gap for gap in gaps if gap.magnitude is not None and gap.magnitude > 0]
    pending.sort(key=lambda gap: (-float(gap.magnitude or 0), gap.competency))
    selected_topics = {
        requirement_by_competency[gap.competency].topic for gap in pending
    }
    emitted_topics: set[str] = set()
    ordered: list[CompetencyGap] = []
    while pending:
        ready = [
            gap
            for gap in pending
            if (
                requirement_by_competency[gap.competency].prerequisite is None
                or requirement_by_competency[gap.competency].prerequisite not in selected_topics
                or requirement_by_competency[gap.competency].prerequisite in emitted_topics
            )
        ]
        chosen = ready[0] if ready else pending[0]
        pending.remove(chosen)
        ordered.append(chosen)
        emitted_topics.add(requirement_by_competency[chosen.competency].topic)

    return [
        PreparationRouteItem(
            topic=requirement_by_competency[gap.competency].topic,
            order=index,
            priority=gap.priority,
            description=f"Reforzar {gap.competency} con práctica guiada y verificación.",
            prerequisite=requirement_by_competency[gap.competency].prerequisite,
            source=requirement_by_competency[gap.competency].relation_id,
            rationale=f"Brecha experimental observada: {gap.magnitude}/100.",
        )
        for index, gap in enumerate(ordered, start=1)
    ]


def _compatibility_label(score: float) -> str:
    if score >= 80:
        return "HIGH_ORIENTATIVE_COMPATIBILITY"
    if score >= 65:
        return "MEDIUM_HIGH_ORIENTATIVE_COMPATIBILITY"
    if score >= 50:
        return "MEDIUM_ORIENTATIVE_COMPATIBILITY"
    return "EXPLORATORY_COMPATIBILITY"


def _recommendation(
    request: AnalysisRequest,
    vocational: RiasecProfile | None,
    academic: AcademicProfile | None,
    career: Career,
    institution: str,
    profile: CareerCriteria,
    criteria: RecommendationCriteria,
    relations: dict[str, BridgeRelation],
) -> tuple[CareerRecommendation | None, str | None]:
    affinity, affinity_coverage, affinity_components = _affinity(
        request, vocational, profile, criteria
    )
    preparation, preparation_coverage, preparation_components, gaps, current = _preparation(
        request, academic, profile, criteria, relations
    )
    if affinity is None or preparation is None:
        return None, (
            f"{career.career_id}: evidencia insuficiente "
            f"(afinidad={affinity_coverage}, preparación={preparation_coverage})."
        )
    compatibility = _rounded(
        affinity * criteria.compatibility_weights["affinity"]
        + preparation * criteria.compatibility_weights["preparation"]
    )
    sources = set(career.source_ids)
    for requirement in profile.requirements:
        relation = relations[requirement.relation_id]
        sources.update((relation.source_secondary, relation.source_university))
    strengths = _strengths(vocational, affinity_components["bth"], profile, current)
    return (
        CareerRecommendation(
            rank=1,
            career_id=career.career_id,
            career_name=career.name,
            institution_context=institution,
            affinity_score=affinity,
            affinity_coverage=affinity_coverage,
            affinity_components=affinity_components,
            preparation_score=preparation,
            preparation_coverage=preparation_coverage,
            preparation_components=preparation_components,
            compatibility_score=compatibility,
            compatibility_label=_compatibility_label(compatibility),
            compatibility_weights=criteria.compatibility_weights,
            criteria_version=criteria.criteria_version,
            strengths=strengths,
            reinforcement_areas=gaps,
            preparation_route=_route(gaps, profile),
            explanation=(
                f"Índice orientativo: {compatibility}/100, compuesto por afinidad "
                f"{affinity}/100 y preparación {preparation}/100. No es una probabilidad "
                "de éxito ni una decisión automática."
            ),
            evidence=[
                f"Cobertura de afinidad: {affinity_coverage}.",
                f"Cobertura de preparación: {preparation_coverage}.",
                "Criterios experimentales pendientes de revisión experta.",
            ],
            sources=sorted(sources),
        ),
        None,
    )


def build_career_ranking(
    request: AnalysisRequest,
    vocational: RiasecProfile | None,
    academic: AcademicProfile | None,
    criteria_override: RecommendationCriteria | None = None,
) -> tuple[list[CareerRecommendation], list[str]]:
    catalog = load_career_catalog()
    criteria = criteria_override or load_recommendation_criteria()
    relations = {relation.relation_id: relation for relation in load_bridge().relations}
    institutions = {
        university.university_id: f"{university.name}, {university.campus}"
        for university in catalog.universities
    }
    recommendations: list[CareerRecommendation] = []
    warnings: list[str] = []
    for career in catalog.careers:
        recommendation, warning = _recommendation(
            request,
            vocational,
            academic,
            career,
            institutions[career.university_id],
            criteria.career_profiles[career.career_id],
            criteria,
            relations,
        )
        if recommendation is not None:
            recommendations.append(recommendation)
        if warning is not None:
            warnings.append(warning)
    recommendations.sort(key=lambda item: (-item.compatibility_score, item.career_id))
    ranked = [
        recommendation.model_copy(update={"rank": rank})
        for rank, recommendation in enumerate(recommendations, start=1)
    ]
    if not ranked:
        warnings.append("No se generó ranking porque la evidencia mínima no está disponible.")
    return ranked, warnings
