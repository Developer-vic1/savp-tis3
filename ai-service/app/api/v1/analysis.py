from fastapi import APIRouter, Depends, Request

from app.api.dependencies import verify_api_key
from app.contracts.errors import DomainError, ErrorCode
from app.contracts.requests import AnalysisRequest
from app.contracts.responses import AnalysisResponse
from app.domain.student_profile import analyze_student

router = APIRouter(prefix="/api/v1", tags=["analysis"], dependencies=[Depends(verify_api_key)])


@router.post("/analysis", response_model=AnalysisResponse)
def analysis(payload: AnalysisRequest, request: Request) -> AnalysisResponse:
    if payload.schema_version != "1.0":
        raise DomainError(
            ErrorCode.UNSUPPORTED_SCHEMA_VERSION,
            "La versión de esquema solicitada no está soportada.",
            details=[{"supported_versions": ["1.0"]}],
        )
    return analyze_student(payload, request.state.trace_id)

