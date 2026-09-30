from typing import Annotated

from fastapi import APIRouter, Depends, Request

from app.api.dependencies import knowledge_retriever, verify_api_key
from app.config import get_settings
from app.contracts.errors import DomainError, ErrorCode
from app.contracts.requests import TutorQueryRequest
from app.contracts.responses import TutorResponse
from app.retrieval.hybrid import HybridRetriever
from app.retrieval.service import selected_retrieval_metadata
from app.tutor.local_llm import LlamaCppHttpProvider, LocalLlmUnavailable
from app.tutor.providers import STRUCTURED_PROVIDER_VERSION
from app.tutor.service import answer_structured

router = APIRouter(prefix="/api/v1", tags=["tutor"], dependencies=[Depends(verify_api_key)])


@router.post("/tutor/query", response_model=TutorResponse)
def tutor(
    payload: TutorQueryRequest,
    request: Request,
    retriever: Annotated[HybridRetriever, Depends(knowledge_retriever)],
) -> TutorResponse:
    if payload.schema_version != "1.0":
        raise DomainError(
            ErrorCode.UNSUPPORTED_SCHEMA_VERSION,
            "La versión de esquema solicitada no está soportada.",
        )
    provider_answer, material = answer_structured(payload, retriever)
    metadata = selected_retrieval_metadata()
    answer_mode = "STRUCTURED"
    provider_version = STRUCTURED_PROVIDER_VERSION
    model_version = None
    if get_settings().local_llm_enabled and not material.insufficient_evidence:
        settings = get_settings()
        local_provider = LlamaCppHttpProvider(
            settings.local_llm_url,
            settings.local_llm_model,
            settings.local_llm_timeout_seconds,
        )
        try:
            provider_answer = local_provider.answer(material)
            answer_mode = "LOCAL_LLM"
            provider_version = local_provider.provider_version
            model_version = local_provider.model
        except LocalLlmUnavailable:
            provider_answer.warnings.append(
                "LOCAL_LLM_UNAVAILABLE: se conservó la respuesta estructurada segura."
            )
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
        model_version=model_version,
    )
