import re
import unicodedata
from collections import Counter
from typing import Any, Literal, cast

from app.contracts.requests import TutorConversationTurn, TutorQueryRequest
from app.contracts.responses import KnowledgeEvidence
from app.retrieval.hybrid import KnowledgeHit, LexicalRetriever, tokenize
from app.retrieval.service import evidence_is_insufficient, hit_to_evidence
from app.tutor.consulta_documental import (
    institution_registry_request,
    is_career_list_question,
    is_institution_review_question,
    is_subject_question,
    missing_scope,
    normalized,
    requested_careers,
    requested_institutions,
    semester,
    semester_text,
)
from app.tutor.datos_documentados import ProgramFact, listed_careers, subject_program
from app.tutor.datos_institucionales import (
    institutions_under_web_review,
    national_registry_summary,
    unifranz_published_offer,
)
from app.tutor.intenciones_locales import local_intent
from app.tutor.needs import system_needs_for_question
from app.tutor.providers import ProviderAnswer, StructuredAnswerProvider, TutorMaterial

ALLOWED_ACADEMIC_CONTEXT = {
    "academic_period",
    "areas_to_reinforce",
    "course",
    "preparation_route",
    "strengths",
    "institutional_university_count",
}
ALLOWED_STUDENT_CONTEXT = {
    "affinity_label",
    "areas_to_reinforce",
    "preparation_label",
    "riasec_code",
    "strengths",
    "technical_specialty",
}
TUTOR_CANDIDATE_K = 8
TUTOR_SEMESTER_CANDIDATE_K = 32
TUTOR_CONTEXT_K = 5
TUTOR_MAX_CHUNKS_PER_SOURCE = 1

TutorIntent = Literal[
    "SOCIAL",
    "CAPABILITIES",
    "GENERAL_PEDAGOGICAL",
    "EVIDENCE_GROUNDED",
]

SOCIAL_MARKERS = {
    "buenas",
    "buenas noches",
    "buenas tardes",
    "buenos dias",
    "como estas",
    "hola",
    "muchas gracias",
    "que tal",
    "gracias",
    "adios",
    "hasta luego",
    "nos vemos",
}
CAPABILITY_MARKERS = (
    "como puedes ayudar",
    "para que sirves",
    "que haces",
    "que puedes hacer",
    "quien eres",
)
OFFICIAL_FACT_MARKERS = (
    "admision",
    "beca",
    "carrera",
    "costo",
    "cuanto cuesta",
    "duracion",
    "inscripcion",
    "investigacion",
    "malla",
    "materias",
    "matricula",
    "plan de estudios",
    "perfil profesional",
    "primer semestre",
    "requisito",
    "semestre",
    "universidad",
    "fuente oficial",
    "fuentes",
    "ucb",
    "umsa",
    "unifranz",
    "upb",
)
FOLLOW_UP_MARKERS = (
    "a que te refieres",
    "comparalo",
    "de eso",
    "esa carrera",
    "esa materia",
    "esa universidad",
    "eso",
    "explicalo",
    "por que",
    "puedes ampliar",
    "y en ",
    "y esa",
    "y eso",
)
INSTITUTION_MARKERS = {
    "ucb": "universidad catolica boliviana",
    "universidad catolica": "universidad catolica boliviana",
    "umsa": "universidad mayor de san andres",
    "unifranz": "unifranz",
    "upb": "universidad privada boliviana",
}
CAREER_MARKERS = ("ambiental", "civil", "psicologia", "sistemas")


def _allowlisted(context: dict[str, Any] | None, allowed: set[str]) -> dict[str, Any]:
    if not context:
        return {}
    return {key: value for key, value in context.items() if key in allowed}


def _normalized(value: str) -> str:
    folded = unicodedata.normalize("NFKD", value.casefold())
    return "".join(character for character in folded if not unicodedata.combining(character))


def classify_tutor_intent(question: str) -> TutorIntent:
    normalized = " ".join(re.findall(r"[a-z0-9]+", _normalized(question)))
    conversational = normalized
    greetings = ("hola", "buenos dias", "buenas tardes", "buenas noches", "buenas")
    for greeting in greetings:
        if conversational.startswith(f"{greeting} "):
            conversational = conversational[len(greeting) :].strip()
            break
    if normalized in SOCIAL_MARKERS or conversational in SOCIAL_MARKERS:
        return "SOCIAL"
    if re.fullmatch(
        r"(?:nos vemos(?: pronto)?|buen dia(?: asistente)?|hola asistente)", normalized
    ):
        return "SOCIAL"
    if any(marker in normalized for marker in CAPABILITY_MARKERS):
        return "CAPABILITIES"
    if any(
        marker in normalized
        for marker in (
            "memoria",
            "recuerdas",
            "guardas",
            "conservas el historial",
            "funciones tienes",
            "asistente documental",
            "revisar documentos",
        )
    ):
        return "CAPABILITIES"
    if any(marker in normalized for marker in OFFICIAL_FACT_MARKERS) or requested_careers(question):
        return "EVIDENCE_GROUNDED"
    learned = local_intent(question)
    return (
        cast(TutorIntent, learned)
        if learned in {"SOCIAL", "CAPABILITIES"}
        else "GENERAL_PEDAGOGICAL"
    )


def _is_follow_up(question: str, history: list[TutorConversationTurn]) -> bool:
    if not history:
        return False
    normalized = _normalized(question)
    return len(tokenize(question)) <= 4 or any(marker in normalized for marker in FOLLOW_UP_MARKERS)


def _last_user_turn(history: list[TutorConversationTurn]) -> str | None:
    for turn in reversed(history):
        if turn.role == "user":
            return turn.content
    return None


def _retrieval_query(payload: TutorQueryRequest) -> str:
    query_parts = [payload.question]
    if _is_follow_up(payload.question, payload.conversation_history):
        previous_question = _last_user_turn(payload.conversation_history)
        if previous_question:
            query_parts.append(f"Contexto anterior: {previous_question}")
    if payload.subject:
        query_parts.append(f"Materia o tema: {payload.subject}")
    if payload.level:
        query_parts.append(f"Nivel: {payload.level}")
    normalized_question = _normalized(payload.question)
    requested_semester = semester(normalized_question)
    if requested_semester:
        query_parts.append(f"asignaturas del semestre {requested_semester}")
    academic_context = _allowlisted(payload.academic_context, ALLOWED_ACADEMIC_CONTEXT)
    for key in ("course", "academic_period", "areas_to_reinforce"):
        value = academic_context.get(key)
        if value:
            query_parts.append(f"{key}: {value}")
    return ". ".join(str(part) for part in query_parts)


def _diverse_hits(hits: list[KnowledgeHit]) -> list[KnowledgeHit]:
    selected: list[KnowledgeHit] = []
    selected_ids: set[str] = set()
    source_counts: Counter[str] = Counter()
    for pass_number in (1, 2):
        for hit in hits:
            source_id = hit.chunk.source_id
            if (
                hit.chunk.chunk_id in selected_ids
                or source_counts[source_id] >= TUTOR_MAX_CHUNKS_PER_SOURCE
            ):
                continue
            if pass_number == 1 and source_counts[source_id] > 0:
                continue
            selected.append(hit)
            selected_ids.add(hit.chunk.chunk_id)
            source_counts[source_id] += 1
            if len(selected) == TUTOR_CONTEXT_K:
                return selected
    return selected


def _filter_hits_for_named_entities(question: str, hits: list[KnowledgeHit]) -> list[KnowledgeHit]:
    institutions = requested_institutions(question)
    careers = requested_careers(question)
    filtered = [
        hit
        for hit in hits
        if (
            not institutions
            or any(
                any(alias in normalized(hit.chunk.institution) for alias in aliases)
                for aliases in institutions
            )
        )
        and (not careers or any(career in normalized(hit.chunk.title) for career in careers))
    ]
    target = semester(question)
    if is_subject_question(question) and target:
        scoped = []
        for hit in filtered:
            content = semester_text(hit.chunk.section, hit.chunk.text, target)
            if content:
                scoped.append(
                    hit.model_copy(update={"chunk": hit.chunk.model_copy(update={"text": content})})
                )
        return scoped
    return filtered


def _evidence_for_question(hit: KnowledgeHit, retrieval_query: str) -> KnowledgeEvidence:
    return hit_to_evidence(hit, retrieval_query)


def _facts_to_hits(
    facts: list[ProgramFact], retriever: LexicalRetriever, *, pages: list[int] | None = None
) -> list[KnowledgeHit]:
    hits: list[KnowledgeHit] = []
    for fact in facts:
        candidates = [
            chunk
            for chunk in retriever.chunks
            if chunk.source_id == fact.source_id and chunk.official
        ]
        if not candidates:
            continue
        wanted_pages: list[int | None] = []
        wanted_pages.extend(pages or fact.curriculum_pages[:1])
        if not wanted_pages:
            wanted_pages.append(None)
        for preferred_page in wanted_pages:
            chosen = next(
                (chunk for chunk in candidates if preferred_page and chunk.page == preferred_page),
                None,
            )
            if chosen is None:
                if pages is not None:
                    continue
                chosen = candidates[0]
            if chosen.chunk_id not in {hit.chunk.chunk_id for hit in hits}:
                hits.append(
                    KnowledgeHit(
                        chunk=chosen,
                        score=1.0,
                        lexical_score=1.0,
                        lexical_rank=len(hits) + 1,
                    )
                )
    return hits


def answer_structured(
    payload: TutorQueryRequest,
    retriever: LexicalRetriever | None,
) -> tuple[ProviderAnswer, TutorMaterial]:
    intent = classify_tutor_intent(payload.question)
    retrieval_query = _retrieval_query(payload)
    requires_official_evidence = intent not in {"SOCIAL", "CAPABILITIES"}
    scoped_question = payload.question
    if _is_follow_up(payload.question, payload.conversation_history):
        previous = _last_user_turn(payload.conversation_history)
        if previous:
            # Sólo recupera campos ausentes; semestre actual siempre prevalece.
            if not requested_institutions(scoped_question):
                scoped_question += " " + " ".join(
                    aliases[0] for aliases in requested_institutions(previous)
                )
            if not requested_careers(scoped_question):
                scoped_question += " " + " ".join(requested_careers(previous))
            if not semester(scoped_question) and semester(previous):
                scoped_question += f" semestre {semester(previous)}"
    institutions = requested_institutions(scoped_question)
    institution_scope = institutions[0][0] if len(institutions) == 1 else None
    review_query = is_institution_review_question(payload.question)
    registry_request = institution_registry_request(payload.question)
    published_offer = (
        unifranz_published_offer()
        if is_career_list_question(payload.question) and institution_scope == "unifranz"
        else None
    )
    if review_query or registry_request or published_offer:
        registry = national_registry_summary() if registry_request else None
        evidence = (
            [
                KnowledgeEvidence(
                    source_id="BO-UNIFRANZ-OFFER-INDEX",
                    chunk_id="BO-UNIFRANZ-OFFER-INDEX",
                    title="Carreras publicadas en el índice oficial de UNIFRANZ",
                    institution=published_offer.institution,
                    source_type="Índice institucional",
                    publication_date=published_offer.checked_at[:10],
                    page=None,
                    section="Facultades",
                    summary="Listado institucional de carreras; sedes y mallas se revisan aparte.",
                    relevant_text="; ".join(career.name for career in published_offer.careers),
                    relevance=1.0,
                    reference=published_offer.source_url,
                    official=True,
                    retrieval_method="OFFICIAL_INDEX_SNAPSHOT",
                )
            ]
            if published_offer
            else (
                [
                    KnowledgeEvidence(
                        source_id="minedu_guia_2016",
                        chunk_id="minedu_guia_2016-registro",
                        title="Guía de Universidades del Estado Plurinacional de Bolivia 2016",
                        institution="Ministerio de Educación de Bolivia",
                        source_type="Guía ministerial histórica",
                        publication_date="2016",
                        page=None,
                        section="Índice de instituciones",
                        summary=(
                            "Base nominal del directorio nacional; "
                            "el conteo único corresponde a SAVP."
                        ),
                        relevant_text=(
                            "El registro local conserva instituciones "
                            "y departamentos de la guía."
                        ),
                        relevance=1.0,
                        reference=registry.source_url,
                        official=True,
                        retrieval_method="NATIONAL_REGISTRY",
                    )
                ]
                if registry and registry.source_url
                else []
            )
        )
        material = TutorMaterial(
            question=payload.question,
            subject=payload.subject,
            level=payload.level,
            allowed_academic_context=_allowlisted(
                payload.academic_context, ALLOWED_ACADEMIC_CONTEXT
            ),
            allowed_student_context=_allowlisted(payload.student_context, ALLOWED_STUDENT_CONTEXT),
            conversation_history=payload.conversation_history,
            evidence=evidence,
            insufficient_evidence=False,
            intent="EVIDENCE_GROUNDED",
            requires_official_evidence=bool(published_offer or registry),
            published_offer=published_offer,
            institutions_under_review=institutions_under_web_review() if review_query else None,
            national_registry=registry,
            national_registry_request=registry_request,
        )
        return StructuredAnswerProvider().answer(material), material
    available = (
        {chunk.source_id for chunk in retriever.chunks}
        if isinstance(retriever, LexicalRetriever)
        else set()
    )
    career_listing = (
        listed_careers(scoped_question, available)
        if is_career_list_question(payload.question) and institution_scope and available
        else []
    )
    program = (
        subject_program(scoped_question, available)
        if is_subject_question(payload.question) and institution_scope and available
        else None
    )
    requested_semester = semester(scoped_question)
    verified_semester = (
        next(
            (item for item in program.verified_semesters if item.semester == requested_semester),
            None,
        )
        if program and requested_semester
        else None
    )
    if career_listing and retriever is not None:
        hits = _facts_to_hits(career_listing, retriever)
    elif program and verified_semester and retriever is not None:
        hits = _facts_to_hits([program], retriever, pages=[verified_semester.page])
    elif program and requested_semester is None and retriever is not None:
        pages = sorted({item.page for item in program.verified_semesters})
        hits = _facts_to_hits([program], retriever, pages=pages or None)
    else:
        hits = (
            retriever.search(
                retrieval_query,
                TUTOR_SEMESTER_CANDIDATE_K if semester(scoped_question) else TUTOR_CANDIDATE_K,
                institution=institution_scope,
                official_only=True,
            )
            if requires_official_evidence and retriever is not None
            else []
        )
    missing = missing_scope(scoped_question)
    clarification = (
        "Para consultar las materias necesito: "
        + ", ".join(missing)
        + ". Por ejemplo: Materias de Ingeniería de Sistemas en UNIFRANZ."
        if missing
        else None
    )
    diverse_hits = (
        hits
        if career_listing or verified_semester or (program and requested_semester is None)
        else _filter_hits_for_named_entities(scoped_question, hits)
    )
    if not (career_listing or verified_semester or (program and requested_semester is None)):
        diverse_hits = _diverse_hits(diverse_hits)
    unverified_semester = bool(
        program
        and requested_semester
        and (program.verified_semesters or program.semester_transcriptions == 0)
        and not diverse_hits
    )
    if unverified_semester and retriever is not None and program:
        diverse_hits = _facts_to_hits([program], retriever)
    insufficient = requires_official_evidence and (
        not diverse_hits
        or (
            not career_listing
            and not program
            and evidence_is_insufficient(retrieval_query, diverse_hits)
        )
        or unverified_semester
    )
    material = TutorMaterial(
        question=payload.question,
        subject=payload.subject,
        level=payload.level,
        allowed_academic_context=_allowlisted(payload.academic_context, ALLOWED_ACADEMIC_CONTEXT),
        allowed_student_context=_allowlisted(payload.student_context, ALLOWED_STUDENT_CONTEXT),
        conversation_history=payload.conversation_history,
        evidence=[_evidence_for_question(hit, retrieval_query) for hit in diverse_hits],
        insufficient_evidence=insufficient,
        intent=intent,
        requires_official_evidence=requires_official_evidence,
        system_needs=system_needs_for_question(payload.question)
        if not semester(scoped_question)
        else [],
        clarification=clarification,
        listed_careers=career_listing if diverse_hits else [],
        subject_program=program if diverse_hits else None,
        subject_semester_unverified=unverified_semester,
    )
    return StructuredAnswerProvider().answer(material), material
