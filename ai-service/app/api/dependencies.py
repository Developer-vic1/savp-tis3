import secrets
from functools import lru_cache

from fastapi import Header

from app.config import get_settings
from app.contracts.errors import DomainError, ErrorCode
from app.retrieval.hybrid import HybridRetriever
from app.retrieval.service import get_retriever


def verify_api_key(x_savp_ai_key: str | None = Header(default=None)) -> None:
    expected = get_settings().api_key
    if expected and (x_savp_ai_key is None or not secrets.compare_digest(expected, x_savp_ai_key)):
        raise DomainError(
            ErrorCode.INVALID_REQUEST,
            "La credencial interna es inválida o está ausente.",
            status_code=401,
        )


@lru_cache(maxsize=1)
def knowledge_retriever() -> HybridRetriever:
    try:
        return get_retriever()
    except Exception as exc:
        raise DomainError(
            ErrorCode.KNOWLEDGE_INDEX_UNAVAILABLE,
            "El índice de conocimiento no pudo cargarse.",
            status_code=503,
        ) from exc
