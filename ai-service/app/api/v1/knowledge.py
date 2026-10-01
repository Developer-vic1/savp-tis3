from typing import Annotated

from fastapi import APIRouter, Depends, Request

from app.api.dependencies import knowledge_retriever, verify_api_key
from app.contracts.errors import DomainError, ErrorCode
from app.contracts.requests import KnowledgeSearchRequest
from app.contracts.responses import KnowledgeSearchResponse
from app.retrieval.hybrid import HybridRetriever
from app.retrieval.service import (
    evidence_is_insufficient,
    hit_to_evidence,
    selected_retrieval_metadata,
)

router = APIRouter(prefix="/api/v1", tags=["knowledge"], dependencies=[Depends(verify_api_key)])


@router.post("/knowledge/search", response_model=KnowledgeSearchResponse)
def search(
    payload: KnowledgeSearchRequest,
    request: Request,
    retriever: Annotated[HybridRetriever, Depends(knowledge_retriever)],
) -> KnowledgeSearchResponse:
    if payload.schema_version != "1.0":
        raise DomainError(
            ErrorCode.UNSUPPORTED_SCHEMA_VERSION,
            "La versión de esquema solicitada no está soportada.",
        )
    hits = retriever.search(
        payload.query,
        payload.top_k,
        institution=payload.institution,
        source_type=payload.source_type,
        official_only=payload.official_only,
    )
    insufficient = evidence_is_insufficient(payload.query, hits)
    metadata = selected_retrieval_metadata()
    warnings = []
    if insufficient:
        warnings.append(
            "La coincidencia recuperada no alcanza el umbral conservador de evidencia; "
            "revise las fuentes antes de usarla como respuesta."
        )
    return KnowledgeSearchResponse(
        trace_id=request.state.trace_id,
        results=[hit_to_evidence(hit, payload.query) for hit in hits],
        insufficient_evidence=insufficient,
        warnings=warnings,
        **metadata,
    )
