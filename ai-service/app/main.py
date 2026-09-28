import logging
from collections.abc import Awaitable, Callable
from typing import Any
from uuid import uuid4

from fastapi import FastAPI, Request
from fastapi.exceptions import RequestValidationError
from fastapi.responses import JSONResponse
from starlette.middleware.base import BaseHTTPMiddleware
from starlette.responses import Response

from app import __version__
from app.api.v1 import analysis, health, knowledge, tutor
from app.contracts.errors import DomainError, ErrorBody, ErrorCode, ErrorEnvelope

logger = logging.getLogger("savp-ai")


class TraceIdMiddleware(BaseHTTPMiddleware):
    async def dispatch(
        self,
        request: Request,
        call_next: Callable[[Request], Awaitable[Response]],
    ) -> Response:
        request.state.trace_id = str(uuid4())
        response = await call_next(request)
        response.headers["X-Trace-Id"] = request.state.trace_id
        return response


def _error_response(
    request: Request,
    code: ErrorCode,
    message: str,
    status_code: int,
    details: list[dict[str, Any]] | None = None,
) -> JSONResponse:
    envelope = ErrorEnvelope(
        error=ErrorBody(
            code=code,
            message=message,
            trace_id=getattr(request.state, "trace_id", str(uuid4())),
            details=details or [],
        )
    )
    return JSONResponse(status_code=status_code, content=envelope.model_dump(mode="json"))


app = FastAPI(
    title="SAVP AI Service",
    version=__version__,
    docs_url="/docs",
    redoc_url=None,
)
app.add_middleware(TraceIdMiddleware)
app.include_router(health.router)
app.include_router(analysis.router)
app.include_router(knowledge.router)
app.include_router(tutor.router)


@app.exception_handler(DomainError)
async def domain_error_handler(request: Request, exc: DomainError) -> JSONResponse:
    return _error_response(request, exc.code, exc.message, exc.status_code, exc.details)


@app.exception_handler(RequestValidationError)
async def validation_error_handler(request: Request, exc: RequestValidationError) -> JSONResponse:
    details = [
        {"location": list(error["loc"]), "message": error["msg"], "type": error["type"]}
        for error in exc.errors()
    ]
    return _error_response(
        request,
        ErrorCode.INVALID_REQUEST,
        "La solicitud no cumple el contrato.",
        422,
        details,
    )


@app.exception_handler(Exception)
async def unexpected_error_handler(request: Request, exc: Exception) -> JSONResponse:
    logger.exception("Unexpected error; trace_id=%s", getattr(request.state, "trace_id", "unknown"))
    return _error_response(
        request,
        ErrorCode.INTERNAL_ERROR,
        "Ocurrió un error interno inesperado.",
        500,
    )

