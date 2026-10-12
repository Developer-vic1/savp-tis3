from typing import Annotated, Any
from urllib.parse import quote

from fastapi import APIRouter, Depends, HTTPException, Request
from fastapi.responses import Response

from app.api.dependencies import knowledge_retriever, verify_api_key
from app.contracts.errors import DomainError, ErrorCode
from app.contracts.requests import KnowledgeSearchRequest
from app.contracts.responses import KnowledgeSearchResponse
from app.knowledge.academic_ingestion import (
    AcademicIngestionResponse,
    extract_approved_proposal,
    read_approved_extraction,
)
from app.knowledge.actualizacion_fuentes import SourceRefreshRequest, check_registered_source
from app.knowledge.analisis_curricular import iniciar_analisis
from app.knowledge.catalogo_nacional import NationalDirectoryResponse, national_directory
from app.knowledge.coleccion_universitaria import read_collection, start_collection
from app.knowledge.documento_coleccion import documento_guardado
from app.knowledge.governance import (
    KnowledgeGovernanceOverview,
    SourceAnalysisResponse,
    SourceCandidateRequest,
    SourceProposalRequest,
    SourceProposalReviewRequest,
    SourceProposalReviewResponse,
    SourceProposalSubmissionResponse,
    SourceUrlValidationRequest,
    SourceUrlValidationResponse,
    analyze_source_candidate,
    assess_bolivian_university_url,
    governance_overview,
    review_source_proposal,
    submit_source_proposal,
)
from app.knowledge.source_preview import SourcePreviewResponse, inspect_source_url
from app.knowledge.university_preview import UniversityPreviewResponse, inspect_university_url
from app.retrieval.hybrid import LexicalRetriever
from app.retrieval.service import (
    evidence_is_insufficient,
    hit_to_evidence,
    selected_retrieval_metadata,
)

router = APIRouter(prefix="/api/v1", tags=["knowledge"], dependencies=[Depends(verify_api_key)])


@router.post("/knowledge/governance/check-update")
def check_source_update(payload: SourceRefreshRequest, request: Request) -> dict[str, Any]:
    try:
        return check_registered_source(payload.source_id) | {
            "schema_version": "1.0", "trace_id": request.state.trace_id,
        }
    except ValueError as error:
        raise HTTPException(status_code=404, detail=str(error)) from error


@router.get("/knowledge/collections/unifranz")
def read_unifranz_collection(request: Request) -> dict[str, Any]:
    return read_collection() | {"trace_id": request.state.trace_id}


@router.post("/knowledge/collections/unifranz")
def start_unifranz_collection(request: Request) -> dict[str, Any]:
    try:
        return start_collection() | {"trace_id": request.state.trace_id}
    except RuntimeError as error:
        raise HTTPException(status_code=409, detail=str(error)) from error


@router.get("/knowledge/collections/unifranz/documents/{digest}")
def download_unifranz_document(digest: str) -> Response:
    try:
        content, name = documento_guardado(digest)
    except (OSError, ValueError) as error:
        raise HTTPException(
            status_code=404, detail="La copia guardada no está disponible o requiere verificación."
        ) from error
    return Response(
        content,
        media_type="application/pdf",
        headers={
            "X-SAVP-Document-Name": quote(name),
            "Cache-Control": "no-store",
            "X-Content-Type-Options": "nosniff",
            "Content-Disposition": "attachment; filename*=UTF-8''" + quote(name),
        },
    )


@router.post("/knowledge/collections/unifranz/analyze")
def analyze_unifranz_collection(request: Request) -> dict[str, Any]:
    try:
        return iniciar_analisis() | {"trace_id": request.state.trace_id}
    except RuntimeError as error:
        raise HTTPException(status_code=409, detail=str(error)) from error


@router.get("/knowledge/national-directory", response_model=NationalDirectoryResponse)
def directory(request: Request) -> NationalDirectoryResponse:
    return national_directory(request.state.trace_id)


@router.get("/knowledge/governance", response_model=KnowledgeGovernanceOverview)
def governance(request: Request) -> KnowledgeGovernanceOverview:
    return governance_overview(request.state.trace_id)


@router.post("/knowledge/governance/validate", response_model=SourceUrlValidationResponse)
def validate_source_url(
    payload: SourceUrlValidationRequest, request: Request
) -> SourceUrlValidationResponse:
    return SourceUrlValidationResponse(
        trace_id=request.state.trace_id,
        assessment=assess_bolivian_university_url(payload.url),
    )


@router.post("/knowledge/governance/analyze", response_model=SourceAnalysisResponse)
def analyze_source(payload: SourceCandidateRequest, request: Request) -> SourceAnalysisResponse:
    result = analyze_source_candidate(payload)
    return result.model_copy(update={"trace_id": request.state.trace_id})


@router.post("/knowledge/governance/preview", response_model=SourcePreviewResponse)
def preview_source(payload: SourceUrlValidationRequest, request: Request) -> SourcePreviewResponse:
    result = inspect_source_url(payload.url)
    return result.model_copy(update={"trace_id": request.state.trace_id})


@router.post("/knowledge/governance/university-preview", response_model=UniversityPreviewResponse)
def preview_university(
    payload: SourceUrlValidationRequest, request: Request
) -> UniversityPreviewResponse:
    result = inspect_university_url(payload.url)
    return result.model_copy(update={"trace_id": request.state.trace_id})


@router.post("/knowledge/governance/proposals", response_model=SourceProposalSubmissionResponse)
def submit_source(
    payload: SourceProposalRequest, request: Request
) -> SourceProposalSubmissionResponse:
    result = submit_source_proposal(payload)
    return result.model_copy(update={"trace_id": request.state.trace_id})


@router.post(
    "/knowledge/governance/proposals/{proposal_id}/review",
    response_model=SourceProposalReviewResponse,
)
def review_source(
    proposal_id: str, payload: SourceProposalReviewRequest, request: Request
) -> SourceProposalReviewResponse:
    try:
        proposal = review_source_proposal(proposal_id, payload)
    except LookupError as error:
        raise HTTPException(status_code=404, detail=str(error)) from error
    except ValueError as error:
        raise HTTPException(status_code=409, detail=str(error)) from error
    return SourceProposalReviewResponse(
        trace_id=request.state.trace_id,
        message=(
            "La fuente fue aprobada para su ingesta controlada."
            if proposal.status == "APROBADA_PENDIENTE_INGESTA"
            else "La propuesta fue rechazada y no será incorporada al corpus."
        ),
        proposal=proposal,
    )


@router.post(
    "/knowledge/governance/proposals/{proposal_id}/extract",
    response_model=AcademicIngestionResponse,
)
def extract_source_proposal(proposal_id: str, request: Request) -> AcademicIngestionResponse:
    try:
        result = extract_approved_proposal(proposal_id)
    except LookupError as error:
        raise HTTPException(status_code=404, detail=str(error)) from error
    except ValueError as error:
        raise HTTPException(status_code=409, detail=str(error)) from error
    return result.model_copy(update={"trace_id": request.state.trace_id})


@router.get(
    "/knowledge/governance/proposals/{proposal_id}/extract",
    response_model=AcademicIngestionResponse,
)
def read_source_extraction(proposal_id: str, request: Request) -> AcademicIngestionResponse:
    try:
        result = read_approved_extraction(proposal_id)
    except LookupError as error:
        raise HTTPException(status_code=404, detail=str(error)) from error
    except (ValueError, OSError) as error:
        raise HTTPException(
            status_code=409, detail="La extracción guardada no está disponible o requiere revisión."
        ) from error
    return result.model_copy(update={"trace_id": request.state.trace_id})


@router.post("/knowledge/search", response_model=KnowledgeSearchResponse)
def search(
    payload: KnowledgeSearchRequest,
    request: Request,
    retriever: Annotated[LexicalRetriever, Depends(knowledge_retriever)],
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
