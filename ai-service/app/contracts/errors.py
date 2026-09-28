from enum import StrEnum
from typing import Any

from pydantic import BaseModel, ConfigDict, Field


class ErrorCode(StrEnum):
    INVALID_REQUEST = "SAVP_AI_INVALID_REQUEST"
    UNSUPPORTED_SCHEMA_VERSION = "SAVP_AI_UNSUPPORTED_SCHEMA_VERSION"
    INVALID_RIASEC_RESPONSE = "SAVP_AI_INVALID_RIASEC_RESPONSE"
    INCOMPLETE_INSTRUMENT = "SAVP_AI_INCOMPLETE_INSTRUMENT"
    INSUFFICIENT_ACADEMIC_DATA = "SAVP_AI_INSUFFICIENT_ACADEMIC_DATA"
    INSUFFICIENT_VOCATIONAL_DATA = "SAVP_AI_INSUFFICIENT_VOCATIONAL_DATA"
    INSUFFICIENT_PROFILE_DATA = "SAVP_AI_INSUFFICIENT_PROFILE_DATA"
    KNOWLEDGE_INDEX_UNAVAILABLE = "SAVP_AI_KNOWLEDGE_INDEX_UNAVAILABLE"
    LOCAL_LLM_UNAVAILABLE = "SAVP_AI_LOCAL_LLM_UNAVAILABLE"
    SERVICE_TEMPORARILY_UNAVAILABLE = "SAVP_AI_SERVICE_TEMPORARILY_UNAVAILABLE"
    INTERNAL_ERROR = "SAVP_AI_INTERNAL_ERROR"


class ErrorBody(BaseModel):
    model_config = ConfigDict(extra="forbid")

    code: ErrorCode
    message: str
    trace_id: str
    details: list[dict[str, Any]] = Field(default_factory=list)


class ErrorEnvelope(BaseModel):
    model_config = ConfigDict(extra="forbid")

    error: ErrorBody


class DomainError(Exception):
    def __init__(
        self,
        code: ErrorCode,
        message: str,
        *,
        status_code: int = 422,
        details: list[dict[str, Any]] | None = None,
    ) -> None:
        super().__init__(message)
        self.code = code
        self.message = message
        self.status_code = status_code
        self.details = details or []
