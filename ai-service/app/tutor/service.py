from typing import Any

from app.contracts.requests import TutorQueryRequest
from app.retrieval.hybrid import HybridRetriever
from app.retrieval.service import evidence_is_insufficient, hit_to_evidence
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
    "strengths",
}


def _allowlisted(context: dict[str, Any] | None, allowed: set[str]) -> dict[str, Any]:
    if not context:
        return {}
    return {key: value for key, value in context.items() if key in allowed}


def answer_structured(
    payload: TutorQueryRequest,
    retriever: HybridRetriever,
) -> tuple[ProviderAnswer, TutorMaterial]:
    query_parts = [payload.question]
    if payload.subject:
        query_parts.append(payload.subject)
    if payload.level:
        query_parts.append(payload.level)
    retrieval_query = ". ".join(query_parts)
    hits = retriever.search(retrieval_query, 8, official_only=True)
    insufficient = evidence_is_insufficient(retrieval_query, hits)
    material = TutorMaterial(
        question=payload.question,
        subject=payload.subject,
        level=payload.level,
        allowed_academic_context=_allowlisted(
            payload.academic_context, ALLOWED_ACADEMIC_CONTEXT
        ),
        allowed_student_context=_allowlisted(payload.student_context, ALLOWED_STUDENT_CONTEXT),
        evidence=[hit_to_evidence(hit, retrieval_query) for hit in hits[:5]],
        insufficient_evidence=insufficient,
    )
    return StructuredAnswerProvider().answer(material), material
