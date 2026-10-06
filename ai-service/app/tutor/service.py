import re
import unicodedata
from collections import Counter
from functools import lru_cache
from typing import Any, Literal

from app.contracts.requests import TutorConversationTurn, TutorQueryRequest
from app.contracts.responses import KnowledgeEvidence
from app.knowledge.registry import load_career_catalog
from app.retrieval.hybrid import KnowledgeHit, LexicalRetriever, tokenize
from app.retrieval.service import evidence_is_insufficient, hit_to_evidence
from app.tutor.needs import system_needs_for_question
from app.tutor.providers import ProviderAnswer, StructuredAnswerProvider, TutorMaterial

ALLOWED_ACADEMIC_CONTEXT = {
    "academic_period",
    "areas_to_reinforce",
    "course",
    "preparation_route",
    "strengths",
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
    if any(marker in normalized for marker in CAPABILITY_MARKERS):
        return "CAPABILITIES"
    if any(marker in normalized for marker in OFFICIAL_FACT_MARKERS):
        return "EVIDENCE_GROUNDED"
    return "GENERAL_PEDAGOGICAL"


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
    if "materias iniciales" in normalized_question or (
        any(marker in normalized_question for marker in ("materias", "asignaturas"))
        and "semestre" in normalized_question
    ):
        query_parts.append("asignaturas del primer semestre")
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
    normalized_question = _normalized(question)
    requested_institutions = {
        institution
        for marker, institution in INSTITUTION_MARKERS.items()
        if marker in normalized_question
    }
    requested_careers = {marker for marker in CAREER_MARKERS if marker in normalized_question}
    filtered = [
        hit
        for hit in hits
        if (
            not requested_institutions
            or any(
                institution in _normalized(hit.chunk.institution)
                for institution in requested_institutions
            )
        )
        and (
            not requested_careers
            or any(career in _normalized(hit.chunk.title) for career in requested_careers)
        )
    ]
    curriculum_question = (
        any(marker in normalized_question for marker in ("materias", "asignaturas", "malla"))
        and "semestre" in normalized_question
    )
    curriculum_hits = [
        hit for hit in filtered if hit.chunk.source_type == "OFFICIAL_CURRICULUM_PDF"
    ]
    if curriculum_question and curriculum_hits:
        return curriculum_hits
    return filtered or hits


@lru_cache(maxsize=1)
def _initial_subjects_by_source() -> dict[str, list[str]]:
    subjects_by_source: dict[str, list[str]] = {}
    for career in load_career_catalog().careers:
        if not career.initial_subjects:
            continue
        for source_id in career.source_ids:
            subjects_by_source.setdefault(source_id, career.initial_subjects)
    return subjects_by_source


def _evidence_for_question(hit: KnowledgeHit, retrieval_query: str) -> KnowledgeEvidence:
    evidence = hit_to_evidence(hit, retrieval_query)
    normalized_query = _normalized(retrieval_query)
    if evidence.source_type == "OFFICIAL_CURRICULUM_PDF" and any(
        marker in normalized_query for marker in ("materias", "asignaturas", "semestre")
    ):
        initial_subjects = _initial_subjects_by_source().get(evidence.source_id, [])
        if initial_subjects:
            summary = "Materias iniciales documentadas: " + ", ".join(initial_subjects) + "."
            return evidence.model_copy(update={"summary": summary, "relevant_text": summary})
    return evidence


def answer_structured(
    payload: TutorQueryRequest,
    retriever: LexicalRetriever | None,
) -> tuple[ProviderAnswer, TutorMaterial]:
    intent = classify_tutor_intent(payload.question)
    retrieval_query = _retrieval_query(payload)
    requires_official_evidence = intent not in {"SOCIAL", "CAPABILITIES"}
    hits = (
        retriever.search(retrieval_query, TUTOR_CANDIDATE_K, official_only=True)
        if requires_official_evidence and retriever is not None
        else []
    )
    diverse_hits = _diverse_hits(_filter_hits_for_named_entities(payload.question, hits))
    insufficient = requires_official_evidence and evidence_is_insufficient(
        retrieval_query, diverse_hits
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
        system_needs=system_needs_for_question(payload.question),
    )
    return StructuredAnswerProvider().answer(material), material
