from collections.abc import Callable
from typing import Annotated

from fastapi import APIRouter, Depends, Request

from app.api.dependencies import knowledge_retriever, verify_api_key
from app.contracts.errors import DomainError, ErrorCode
from app.contracts.requests import TutorQueryRequest
from app.contracts.responses import TutorResponse
from app.retrieval.hybrid import LexicalRetriever
from app.retrieval.service import selected_retrieval_metadata
from app.tutor.providers import STRUCTURED_PROVIDER_VERSION
from app.tutor.service import answer_structured, classify_tutor_intent

router = APIRouter(prefix="/api/v1", tags=["tutor"], dependencies=[Depends(verify_api_key)])


def tutor_retriever_factory() -> Callable[[], LexicalRetriever]:
    return knowledge_retriever


@router.post("/tutor/query", response_model=TutorResponse)
def tutor(
    payload: TutorQueryRequest,
    request: Request,
    retriever_factory: Annotated[
        Callable[[], LexicalRetriever],
        Depends(tutor_retriever_factory),
    ],
) -> TutorResponse:
    if payload.schema_version != "1.0":
        raise DomainError(
            ErrorCode.UNSUPPORTED_SCHEMA_VERSION,
            "La versión de esquema solicitada no está soportada.",
    )
    intent = classify_tutor_intent(payload.question)
    retriever = (
        retriever_factory()
        if intent not in {"SOCIAL", "CAPABILITIES"}
        else None
    )
    provider_answer, material = answer_structured(payload, retriever)
    metadata = selected_retrieval_metadata()
    answer_mode = "STRUCTURED"
    provider_version = STRUCTURED_PROVIDER_VERSION
    return TutorResponse(
        trace_id=request.state.trace_id,
        answer=provider_answer.answer,
        answer_mode=answer_mode,
        suggested_topics=provider_answer.suggested_topics,
        sources=material.evidence,
        insufficient_evidence=material.insufficient_evidence,
        warnings=provider_answer.warnings,
        provider_version=provider_version,
        corpus_version=metadata["corpus_version"],
        embedding_model=metadata["embedding_model"],
    )
