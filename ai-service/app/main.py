import logging
from collections.abc import Awaitable, Callable
from time import perf_counter
from typing import Any
from uuid import uuid4

from fastapi import FastAPI, Request
from fastapi.exceptions import RequestValidationError
from fastapi.responses import JSONResponse
from starlette.middleware.base import BaseHTTPMiddleware
from starlette.responses import Response
from starlette.types import ASGIApp, Message, Receive, Scope, Send

from app import __version__
from app.api.v1 import analysis, health, knowledge, tutor
from app.api.v2 import analysis as analysis_v2
from app.api.v2 import riasec as riasec_v2
from app.contracts.errors import DomainError, ErrorBody, ErrorCode, ErrorEnvelope

logger = logging.getLogger("savp-ai")


class RequestBodyLimitMiddleware:
    def __init__(self, app: ASGIApp) -> None:
        self.app = app

    async def __call__(self, scope: Scope, receive: Receive, send: Send) -> None:
        if scope["type"] != "http":
            await self.app(scope, receive, send)
            return
        body = bytearray()
        while True:
            message = await receive()
            if message["type"] == "http.disconnect":
                return
            chunk = message.get("body", b"")
            if len(body) + len(chunk) > 65_536:
                request = Request(scope)
                request.state.trace_id = str(uuid4())
                response = _error_response(
                    request,
                    ErrorCode.INVALID_REQUEST,
                    "La solicitud supera el tamaño permitido.",
                    413,
                )
                _security_headers(response, request.state.trace_id)
                await response(scope, receive, send)
                return
            body.extend(chunk)
            if not message.get("more_body", False):
                break

        replayed = False

        async def replay() -> Message:
            nonlocal replayed
            if not replayed:
                replayed = True
                return {"type": "http.request", "body": bytes(body), "more_body": False}
            return await receive()

        await self.app(scope, replay, send)


def _security_headers(response: Response, trace_id: str) -> None:
    response.headers["X-Trace-Id"] = trace_id
    response.headers["Cache-Control"] = "no-store"
    response.headers["X-Content-Type-Options"] = "nosniff"
    response.headers["X-Frame-Options"] = "DENY"
    response.headers["Referrer-Policy"] = "no-referrer"
    response.headers["Permissions-Policy"] = "camera=(), microphone=(), geolocation=()"


class TraceIdMiddleware(BaseHTTPMiddleware):
    async def dispatch(
        self,
        request: Request,
        call_next: Callable[[Request], Awaitable[Response]],
    ) -> Response:
        request.state.trace_id = str(uuid4())
        started = perf_counter()
        content_length = request.headers.get("content-length")
        try:
            oversized = content_length is not None and int(content_length) > 65_536
        except ValueError:
            oversized = True
        response: Response
        if oversized:
            response = _error_response(
                request,
                ErrorCode.INVALID_REQUEST,
                "La solicitud supera el tamaño permitido.",
                413,
            )
        else:
            response = await call_next(request)
        _security_headers(response, request.state.trace_id)
        response.headers["Server-Timing"] = f"app;dur={(perf_counter() - started) * 1000:.3f}"
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
    docs_url=None,
    redoc_url=None,
    openapi_url=None,
)
app.add_middleware(TraceIdMiddleware)
app.add_middleware(RequestBodyLimitMiddleware)
app.include_router(health.router)
app.include_router(analysis.router)
app.include_router(knowledge.router)
app.include_router(tutor.router)
app.include_router(analysis_v2.router)
app.include_router(riasec_v2.router)


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
