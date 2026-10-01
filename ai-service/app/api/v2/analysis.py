from fastapi import APIRouter, Depends, Request

from app.api.dependencies import verify_api_key
from app.contracts.v2 import AnalysisV2Request, AnalysisV2Response
from app.domain.student_profile_v2 import analyze_student_v2

router = APIRouter(prefix="/api/v2", tags=["analysis-v2"], dependencies=[Depends(verify_api_key)])


@router.post("/analysis", response_model=AnalysisV2Response)
def analysis_v2(payload: AnalysisV2Request, request: Request) -> AnalysisV2Response:
    return analyze_student_v2(payload, request.state.trace_id)
