import re
import unicodedata
from collections import defaultdict
from typing import Literal

from app.contracts.evidence import (
    AvailabilityStatus,
    EvidenceStatement,
    Limitation,
    SourceReference,
)
from app.contracts.requests import AcademicRecord, AnalysisRequest
from app.contracts.responses import AcademicProfile, RiasecProfile
from app.contracts.v2 import AnalysisV2Request
from app.knowledge.registry import (
    Career,
    load_career_catalog,
    load_reference_registry,
    load_source_manifest,
)
from app.learning_analytics.academic_performance import normalize_score
from app.recommendation.bridge_v2 import (
    BridgeLoadResult,
    BridgeRelationStatus,
    BridgeV2Relation,
    load_default_bridge_v2,
)
from app.recommendation.config import BridgeRelation
from app.recommendation.evidence_config import load_recommendation_v2_policy
from app.recommendation.evidence_models import (
    AcademicEvidence,
    CareerAcademicProgram,
    CareerEvidenceProfile,
    DeclaredInterestEvidence,
    EvidenceQuality,
    EvidenceRelation,
    MatchBasis,
    PreparationEvidence,
    PreparationEvidenceProfile,
    RecommendationV2Result,
    ReinforcementArea,
    TechnicalEvidence,
)
from app.recommendation.occupational_crosswalk import (
    CareerOccupationRelation,
    CrosswalkLoadResult,
    OccupationRelationType,
    load_default_occupational_crosswalk,
)

CatalogField = Literal[
    "career_name",
    "career_alias",
    "official_knowledge_area",
    "initial_subject",
]
AnalysisInput = AnalysisRequest | AnalysisV2Request


def _normalize_text(value: str) -> str:
    folded = unicodedata.normalize("NFKD", value.casefold())
    without_marks = "".join(
        character for character in folded if not unicodedata.combining(character)
    )
    return re.sub(r"[^a-z0-9]+", " ", without_marks).strip()


def _contains_either(left: str, right: str) -> bool:
    normalized_left = _normalize_text(left)
    normalized_right = _normalize_text(right)
    if not normalized_left or not normalized_right:
        return False
    return normalized_left in normalized_right or normalized_right in normalized_left


def _academic_match_basis(
    record: AcademicRecord,
    relation: BridgeV2Relation | BridgeRelation,
) -> MatchBasis | None:
    if _contains_either(record.subject, relation.secondary_content):
        return "SUBJECT_LABEL_CONTAINMENT"
    if record.area and _contains_either(record.area, relation.secondary_content):
        return "AREA_LABEL_CONTAINMENT"
    return None


def _academic_evidence_for_relation(
    request: AnalysisInput,
    relation: BridgeV2Relation,
) -> list[AcademicEvidence]:
    if request.academic is None:
        return []
    records = sorted(
        request.academic.records,
        key=lambda item: (
            _normalize_text(item.subject),
            _normalize_text(item.area or ""),
            item.period_order if item.period_order is not None else -1,
            item.period or "",
            float(item.score),
        ),
    )
    evidence: list[AcademicEvidence] = []
    for record in records:
        basis = _academic_match_basis(record, relation)
        if basis is None:
            continue
        evidence.append(
            AcademicEvidence(
                relation_id=relation.relation_id,
                competency=relation.competency,
                secondary_content=relation.secondary_content,
                university_knowledge=relation.university_knowledge,
                initial_subject=relation.initial_subject,
                record_subject=record.subject,
                record_area=record.area,
                record_period=record.period,
                normalized_score=normalize_score(record),
                match_basis=basis,
                relation_status=relation.relation_status,
                evidence_label=relation.evidence_label,
                source_secondary=relation.source_secondary,
                source_university=relation.source_university,
            )
        )
    return evidence


def _technical_observations(request: AnalysisInput) -> list[tuple[str, str | None]]:
    if request.technical is None:
        return []
    values: list[tuple[str, str | None]] = []
    if request.technical.specialty:
        values.append((request.technical.specialty, None))
    values.extend((item.name, item.evidence) for item in request.technical.competencies)
    return sorted(values, key=lambda item: (_normalize_text(item[0]), item[1] or ""))


def _technical_evidence_for_relation(
    request: AnalysisInput,
    relation: BridgeV2Relation,
) -> list[TechnicalEvidence]:
    targets = (
        relation.secondary_content,
        relation.competency,
        relation.university_knowledge,
    )
    evidence: list[TechnicalEvidence] = []
    for observed, observed_evidence in _technical_observations(request):
        for target in targets:
            if not _contains_either(observed, target):
                continue
            evidence.append(
                TechnicalEvidence(
                    relation_id=relation.relation_id,
                    observed_value=observed,
                    observed_evidence=observed_evidence,
                    matched_against=target,
                    match_basis="TECHNICAL_LABEL_CONTAINMENT",
                    relation_status=relation.relation_status,
                    evidence_label=relation.evidence_label,
                    source_secondary=relation.source_secondary,
                    source_university=relation.source_university,
                )
            )
            break
    return evidence


def _declared_interest_values(request: AnalysisInput) -> list[str]:
    values = request.declared_interests or []
    return sorted(
        (item if isinstance(item, str) else item.text for item in values),
        key=_normalize_text,
    )


def _catalog_values(career: Career) -> list[tuple[CatalogField, str]]:
    values: list[tuple[CatalogField, str]] = [("career_name", career.name)]
    values.extend(("career_alias", alias) for alias in career.aliases)
    values.extend(("official_knowledge_area", value) for value in career.official_knowledge_areas)
    values.extend(("initial_subject", value) for value in career.initial_subjects)
    return values


def _declared_interest_evidence(
    request: AnalysisInput,
    career: Career,
) -> list[DeclaredInterestEvidence]:
    output: list[DeclaredInterestEvidence] = []
    seen: set[tuple[str, str, str]] = set()
    for declared in _declared_interest_values(request):
        for field, catalog_value in _catalog_values(career):
            if not _contains_either(declared, catalog_value):
                continue
            identity = (declared, catalog_value, field)
            if identity in seen:
                continue
            seen.add(identity)
            output.append(
                DeclaredInterestEvidence(
                    declared_interest=declared,
                    catalog_value=catalog_value,
                    catalog_field=field,
                    match_basis="CATALOG_LABEL_CONTAINMENT",
                )
            )
    return sorted(
        output,
        key=lambda item: (
            _normalize_text(item.declared_interest),
            item.catalog_field,
            _normalize_text(item.catalog_value),
        ),
    )


def _historical_period_count(request: AnalysisInput) -> int:
    if isinstance(request, AnalysisV2Request):
        return len(request.history)
    return len(request.history or [])


def build_evidence_quality(
    request: AnalysisInput,
    academic: AcademicProfile | None,
    vocational: RiasecProfile | None,
) -> EvidenceQuality:
    records = request.academic.records if request.academic else []
    ordered_periods = len(
        {record.period_order for record in records if record.period_order is not None}
    )
    technical_count = len(_technical_observations(request))
    declared_count = len(_declared_interest_values(request))
    history_count = _historical_period_count(request)
    missing_components: list[str] = []
    component_presence = {
        "academic": bool(records),
        "attendance": request.attendance is not None,
        "vocational_interest": vocational is not None,
        "technical": technical_count > 0,
        "learning_activity": request.learning_activity is not None,
        "declared_interest": declared_count > 0,
        "historical": history_count > 0,
    }
    missing_components.extend(name for name, present in component_presence.items() if not present)
    temporal_coverage = academic.temporal_coverage.ratio if academic else None
    notes: list[str] = []
    if not records:
        notes.append("No hay evidencia académica disponible.")
    if temporal_coverage is None:
        notes.append("La cobertura temporal no puede estimarse con los datos disponibles.")
    if vocational is None:
        notes.append("No existe un perfil de intereses RIASEC completo en esta ejecución.")
    if technical_count == 0:
        notes.append("No existe evidencia técnica BTH disponible.")
    return EvidenceQuality(
        academic_record_count=len(records),
        distinct_subject_count=len({record.subject for record in records}),
        distinct_area_count=len({record.area for record in records if record.area}),
        ordered_period_count=ordered_periods,
        temporal_coverage=temporal_coverage,
        attendance_available=request.attendance is not None,
        activity_available=request.learning_activity is not None,
        riasec_complete=vocational is not None,
        technical_evidence_count=technical_count,
        declared_interest_count=declared_count,
        historical_period_count=history_count,
        missing_components=missing_components,
        notes=notes,
    )


def _source_references(source_ids: set[str] | list[str]) -> list[SourceReference]:
    manifest = {item.source_id: item for item in load_source_manifest().sources}
    registry = {item.reference_id: item for item in load_reference_registry().references}
    references: list[SourceReference] = []
    for source_id in sorted(source_ids):
        source = manifest.get(source_id)
        if source is None:
            external = registry.get(source_id)
            if external is None:
                references.append(SourceReference(source_id=source_id))
                continue
            references.append(
                SourceReference(
                    source_id=external.reference_id,
                    title=external.title,
                    institution=external.organization,
                    source_type=external.reference_kind,
                    reference=external.url or external.local_path,
                    version=external.version,
                    official=external.reference_kind == "EXTERNAL_OFFICIAL",
                    evidence_layer="EXTERNAL_REFERENCE",
                )
            )
            continue
        references.append(
            SourceReference(
                source_id=source.source_id,
                title=source.title,
                institution=source.institution,
                source_type=source.source_type,
                reference=source.url,
                version=source.version,
                official=source.official,
                evidence_layer=source.evidence_layer,
            )
        )
    return references


def _limitation(code: str, message: str, scope: str) -> Limitation:
    return Limitation(code=code, message=message, scope=scope)


def _academic_program(career: Career) -> CareerAcademicProgram:
    sources = _source_references(career.source_ids)
    curriculum_sources = [
        source for source in sources if source.source_type == "OFFICIAL_CURRICULUM_PDF"
    ]
    duration = career.duration_text
    if duration is None and career.duration_semesters is not None:
        duration = f"{career.duration_semesters} semestres"

    if career.initial_subjects:
        curriculum_status = AvailabilityStatus.PARTIAL
        curriculum_scope = "DOCUMENTED_INITIAL_SUBJECTS"
        curriculum_note = (
            "Se muestran las materias iniciales documentadas en el catálogo. "
            "La malla completa debe verificarse en la fuente oficial enlazada."
        )
    elif curriculum_sources:
        curriculum_status = AvailabilityStatus.PARTIAL
        curriculum_scope = "OFFICIAL_CURRICULUM_SOURCE_ONLY"
        curriculum_note = (
            "Existe una malla oficial en las fuentes revisadas, pero este resumen no publica una lista "
            "de materias suficientemente estructurada. Consulta la fuente original."
        )
    else:
        curriculum_status = AvailabilityStatus.UNAVAILABLE
        curriculum_scope = "UNAVAILABLE"
        curriculum_note = (
            "No hay una malla curricular verificable disponible para resumir sin inferencias."
        )

    return CareerAcademicProgram(
        degree=career.degree,
        duration=duration,
        professional_profile=career.professional_profile,
        knowledge_areas=career.official_knowledge_areas,
        documented_subjects=career.initial_subjects,
        curriculum_status=curriculum_status,
        curriculum_scope=curriculum_scope,
        curriculum_note=curriculum_note,
        sources=curriculum_sources,
    )


def _vocational_relation(
    vocational: RiasecProfile | None,
    occupation_relations: list[CareerOccupationRelation],
) -> EvidenceRelation:
    comparable = [
        relation
        for relation in occupation_relations
        if relation.riasec_occupational_profile is not None
        and relation.relation_type is not OccupationRelationType.INSUFFICIENT_EVIDENCE
    ]
    limitations = [
        _limitation(
            "INTEREST_NOT_APTITUDE",
            "La comparación de códigos RIASEC describe intereses y no aptitud o capacidad.",
            "vocational_interest_relation",
        ),
        _limitation(
            "CAREER_NOT_OCCUPATION",
            "La carrera y la ocupación relacionada no son equivalentes.",
            "vocational_interest_relation",
        ),
    ]
    evidence: list[EvidenceStatement] = []
    if vocational is not None and comparable:
        evidence.extend(
            EvidenceStatement(
                kind="RIASEC_OCCUPATIONAL_REFERENCE",
                statement=(
                    f"El código Holland observado {vocational.holland_code} se presenta junto "
                    f"al perfil ocupacional documentado {relation.riasec_occupational_profile} "
                    f"de {relation.occupation_title}; no es un score de compatibilidad."
                ),
                source_ids=relation.source_ids,
                relation_id=relation.occupation_id,
                is_inference=relation.evidence_status
                is not BridgeRelationStatus.DIRECTLY_DOCUMENTED,
            )
            for relation in comparable
        )
    if vocational is None or not comparable:
        status = AvailabilityStatus.UNAVAILABLE
    elif all(
        relation.evidence_status is BridgeRelationStatus.INSUFFICIENT_EVIDENCE
        for relation in comparable
    ):
        status = AvailabilityStatus.INSUFFICIENT
    elif any(
        relation.evidence_status is BridgeRelationStatus.HYPOTHESIS for relation in comparable
    ):
        status = AvailabilityStatus.PARTIAL
    else:
        status = AvailabilityStatus.AVAILABLE
    source_ids = {source_id for relation in comparable for source_id in relation.source_ids}
    return EvidenceRelation(
        status=status,
        evidence=evidence,
        sources=_source_references(source_ids),
        limitations=limitations,
    )


def _technical_relation(
    observations: list[tuple[str, str | None]],
    evidence: list[TechnicalEvidence],
) -> EvidenceRelation:
    if not observations:
        status = AvailabilityStatus.UNAVAILABLE
    elif not evidence or all(
        item.relation_status is BridgeRelationStatus.INSUFFICIENT_EVIDENCE for item in evidence
    ):
        status = AvailabilityStatus.INSUFFICIENT
    elif all(
        item.relation_status
        in {
            BridgeRelationStatus.DIRECTLY_DOCUMENTED,
            BridgeRelationStatus.DOCUMENT_SUPPORTED_INFERENCE,
        }
        for item in evidence
    ):
        status = AvailabilityStatus.AVAILABLE
    else:
        status = AvailabilityStatus.PARTIAL
    statements = [
        EvidenceStatement(
            kind="RELATED_TECHNICAL_EVIDENCE",
            statement=(
                f"La observación técnica '{item.observed_value}' coincide por contención "
                f"de etiqueta con '{item.matched_against}'."
            ),
            source_ids=[item.source_secondary, item.source_university],
            relation_id=item.relation_id,
            is_inference=item.relation_status is not BridgeRelationStatus.DIRECTLY_DOCUMENTED,
        )
        for item in evidence
    ]
    source_ids = {
        source_id
        for item in evidence
        for source_id in (item.source_secondary, item.source_university)
    }
    limitations = [
        _limitation(
            "TECHNICAL_RELATION_NOT_CAREER_DECISION",
            "La evidencia BTH relacionada no prescribe continuidad en una carrera.",
            "technical_relation",
        )
    ]
    return EvidenceRelation(
        status=status,
        evidence=statements,
        sources=_source_references(source_ids),
        limitations=limitations,
    )


def _declared_relation(
    declared_values: list[str],
    evidence: list[DeclaredInterestEvidence],
) -> EvidenceRelation:
    if not declared_values:
        status = AvailabilityStatus.UNAVAILABLE
    elif evidence:
        status = AvailabilityStatus.AVAILABLE
    else:
        status = AvailabilityStatus.INSUFFICIENT
    statements = [
        EvidenceStatement(
            kind="DECLARED_INTEREST_CATALOG_MATCH",
            statement=(
                f"El interés declarado '{item.declared_interest}' coincide con el campo "
                f"documentado {item.catalog_field}='{item.catalog_value}'."
            ),
        )
        for item in evidence
    ]
    return EvidenceRelation(
        status=status,
        evidence=statements,
        sources=[],
        limitations=[
            _limitation(
                "LEXICAL_CATALOG_MATCH_ONLY",
                (
                    "La relación usa contención de etiquetas documentadas; no infiere "
                    "intención oculta."
                ),
                "declared_interest_relation",
            )
        ],
    )


def _occupational_relation(
    occupation_relations: list[CareerOccupationRelation],
) -> EvidenceRelation:
    usable = [
        relation
        for relation in occupation_relations
        if relation.relation_type is not OccupationRelationType.INSUFFICIENT_EVIDENCE
    ]
    if not occupation_relations:
        status = AvailabilityStatus.UNAVAILABLE
    elif not usable or all(
        relation.evidence_status is BridgeRelationStatus.INSUFFICIENT_EVIDENCE
        for relation in occupation_relations
    ):
        status = AvailabilityStatus.INSUFFICIENT
    elif any(
        relation.relation_type is OccupationRelationType.POSSIBLE_OCCUPATION
        or relation.evidence_status is BridgeRelationStatus.HYPOTHESIS
        for relation in usable
    ):
        status = AvailabilityStatus.PARTIAL
    else:
        status = AvailabilityStatus.AVAILABLE
    statements = [
        EvidenceStatement(
            kind=relation.relation_type.value,
            statement=(
                f"{relation.occupation_title} ({relation.occupation_system}) figura como "
                f"{relation.relation_type.value}; {relation.justification}"
            ),
            source_ids=relation.source_ids,
            relation_id=relation.occupation_id,
            is_inference=relation.evidence_status is not BridgeRelationStatus.DIRECTLY_DOCUMENTED,
        )
        for relation in occupation_relations
    ]
    source_ids = {
        source_id for relation in occupation_relations for source_id in relation.source_ids
    }
    limitations = [
        _limitation("CAREER_NOT_OCCUPATION", limitation, "occupational_relation")
        for relation in occupation_relations
        for limitation in relation.limitations
    ]
    if not occupation_relations:
        limitations.append(
            _limitation(
                "OCCUPATIONAL_CROSSWALK_UNAVAILABLE",
                "No se cargó una relación documentada entre la carrera y ocupaciones.",
                "occupational_relation",
            )
        )
    return EvidenceRelation(
        status=status,
        evidence=statements,
        sources=_source_references(source_ids),
        limitations=limitations,
    )


def _preparation_item(
    relation: BridgeV2Relation,
    evidence: list[AcademicEvidence],
    academic_available: bool,
) -> PreparationEvidence:
    if evidence:
        observation_status = AvailabilityStatus.AVAILABLE
    elif academic_available:
        observation_status = AvailabilityStatus.INSUFFICIENT
    else:
        observation_status = AvailabilityStatus.UNAVAILABLE
    limitations = [
        _limitation(
            "RELATION_SCOPE",
            limitation,
            "preparation_evidence",
        )
        for limitation in relation.limitations
    ]
    return PreparationEvidence(
        competency=relation.competency,
        observed_subjects=sorted({item.record_subject for item in evidence}),
        observed_areas=sorted(
            {item.record_area for item in evidence if item.record_area is not None}
        ),
        normalized_observations=[item.normalized_score for item in evidence],
        related_university_knowledge=[relation.university_knowledge],
        initial_subjects=[relation.initial_subject],
        relation_ids=[relation.relation_id],
        source_ids=sorted({relation.source_secondary, relation.source_university}),
        relation_status=relation.relation_status,
        observation_status=observation_status,
        match_bases=sorted({item.match_basis for item in evidence}),
        justification=relation.justification,
        limitations=limitations,
    )


def _career_profile(
    request: AnalysisInput,
    academic: AcademicProfile | None,
    vocational: RiasecProfile | None,
    career: Career,
    university: str,
    relations: list[BridgeV2Relation],
    occupation_relations: list[CareerOccupationRelation],
) -> CareerEvidenceProfile:
    ordered_relations = sorted(relations, key=lambda item: item.relation_id)
    academic_by_relation: dict[str, list[AcademicEvidence]] = defaultdict(list)
    technical_evidence: list[TechnicalEvidence] = []
    source_ids = set(career.source_ids)
    for relation in ordered_relations:
        source_ids.update((relation.source_secondary, relation.source_university))
        academic_by_relation[relation.relation_id].extend(
            _academic_evidence_for_relation(request, relation)
        )
        technical_evidence.extend(_technical_evidence_for_relation(request, relation))
    for occupation_relation in occupation_relations:
        source_ids.update(occupation_relation.source_ids)

    academic_evidence = [
        item
        for relation in ordered_relations
        for item in academic_by_relation[relation.relation_id]
    ]
    observed_relation_ids = {item.relation_id for item in academic_evidence}
    evidence_items = [
        _preparation_item(
            relation,
            academic_by_relation[relation.relation_id],
            request.academic is not None,
        )
        for relation in ordered_relations
    ]
    missing_relations = [
        relation
        for relation in ordered_relations
        if relation.relation_id not in observed_relation_ids
    ]
    reinforcement_areas = [
        ReinforcementArea(
            competency=relation.competency,
            related_university_knowledge=relation.university_knowledge,
            initial_subject=relation.initial_subject,
            rationale=(
                "Existe contenido relacionado, pero no se observó evidencia académica "
                "comparable; no se calcula una brecha numérica."
            ),
            relation_id=relation.relation_id,
            source_ids=sorted({relation.source_secondary, relation.source_university}),
        )
        for relation in missing_relations
    ]
    preparation = PreparationEvidenceProfile(
        related_relations=len(ordered_relations),
        relations_with_observed_academic_evidence=len(observed_relation_ids),
        academic_evidence=academic_evidence,
        evidence_items=evidence_items,
        areas_without_observed_evidence=[item.competency for item in missing_relations],
        reinforcement_areas=reinforcement_areas,
        numeric_gaps=[],
        interpretation=(
            "Las observaciones describen evidencia académica relacionada mediante un bridge "
            "trazable. No forman un porcentaje global de preparación ni una probabilidad de éxito."
        ),
    )
    declared_evidence = _declared_interest_evidence(request, career)
    technical_observations = _technical_observations(request)
    quality = build_evidence_quality(request, academic, vocational)
    limitations = [
        _limitation(
            "NO_COMPOSITE_SCORE",
            "No se calcula score compuesto de afinidad, preparación o compatibilidad.",
            career.career_id,
        ),
        _limitation(
            "UNKNOWN_NOT_ZERO",
            "La ausencia de evidencia se conserva como ausencia y nunca como cero.",
            career.career_id,
        ),
        _limitation(
            "NO_UNSOURCED_NUMERIC_GAPS",
            "No se calculan brechas numéricas sin requisito cuantitativo documentado.",
            career.career_id,
        ),
    ]
    if any(
        relation.relation_status
        in {BridgeRelationStatus.HYPOTHESIS, BridgeRelationStatus.INSUFFICIENT_EVIDENCE}
        for relation in ordered_relations
    ):
        limitations.append(
            _limitation(
                "NON_DOCUMENTED_BRIDGE_RELATIONS",
                "El bridge contiene hipótesis o evidencia insuficiente; no son equivalencias.",
                career.career_id,
            )
        )
    return CareerEvidenceProfile(
        career_id=career.career_id,
        career_name=career.name,
        university=university,
        academic_program=_academic_program(career),
        vocational_interest_relation=_vocational_relation(vocational, occupation_relations),
        technical_relation=_technical_relation(technical_observations, technical_evidence),
        declared_interest_relation=_declared_relation(
            _declared_interest_values(request), declared_evidence
        ),
        occupational_relation=_occupational_relation(occupation_relations),
        riasec_reference_status=(
            "AVAILABLE_DOCUMENTED_OCCUPATIONAL_CROSSWALK"
            if any(
                relation.riasec_occupational_profile is not None
                for relation in occupation_relations
            )
            else "UNAVAILABLE_PENDING_OCCUPATIONAL_CROSSWALK"
        ),
        student_riasec_code=vocational.holland_code if vocational else None,
        preparation=preparation,
        technical_evidence=technical_evidence,
        declared_interest_evidence=declared_evidence,
        evidence_quality=quality,
        areas_observed=sorted({item.competency for item in academic_evidence}, key=_normalize_text),
        areas_without_evidence=[item.competency for item in missing_relations],
        sources=_source_references(source_ids),
        source_ids=sorted(source_ids),
        limitations=limitations,
    )


def build_career_evidence_profiles(
    request: AnalysisInput,
    vocational: RiasecProfile | None,
    academic: AcademicProfile | None,
    bridge_result: BridgeLoadResult | None = None,
    crosswalk_result: CrosswalkLoadResult | None = None,
) -> RecommendationV2Result:
    policy = load_recommendation_v2_policy()
    catalog = load_career_catalog()
    loaded_bridge = bridge_result or load_default_bridge_v2()
    loaded_crosswalk = crosswalk_result or load_default_occupational_crosswalk()
    relations_by_career: dict[str, list[BridgeV2Relation]] = defaultdict(list)
    if loaded_bridge.bridge is not None:
        for relation in loaded_bridge.bridge.relations:
            for career_id in relation.career_ids:
                relations_by_career[career_id].append(relation)
    occupation_relations_by_career: dict[str, list[CareerOccupationRelation]] = defaultdict(list)
    if loaded_crosswalk.crosswalk is not None:
        for occupation_relation in loaded_crosswalk.crosswalk.relations:
            occupation_relations_by_career[occupation_relation.career_id].append(
                occupation_relation
            )

    institutions = {
        university.university_id: f"{university.name}, {university.campus}"
        for university in catalog.universities
    }
    profiles = [
        _career_profile(
            request=request,
            academic=academic,
            vocational=vocational,
            career=career,
            university=institutions[career.university_id],
            relations=relations_by_career.get(career.career_id, []),
            occupation_relations=occupation_relations_by_career.get(career.career_id, []),
        )
        for career in sorted(
            (item for item in catalog.careers if item.recommendation_eligible),
            key=lambda item: item.career_id,
        )
    ]
    warnings = [
        "V2 no produce un ranking global ni un porcentaje de compatibilidad.",
        "Los perfiles presentan dimensiones de evidencia independientes.",
        *loaded_bridge.warnings,
        *loaded_crosswalk.warnings,
    ]
    if loaded_bridge.bridge is None:
        warnings.append("No hay bridge disponible; los perfiles de carrera se limitan al catálogo.")
    return RecommendationV2Result(
        criteria_version=policy.criteria_version,
        aggregation_policy=policy.aggregation_policy,
        ranking_policy=policy.ranking_policy,
        crosswalk_version=(
            loaded_crosswalk.crosswalk.crosswalk_version
            if loaded_crosswalk.crosswalk is not None
            else None
        ),
        profiles=profiles,
        warnings=warnings,
    )
