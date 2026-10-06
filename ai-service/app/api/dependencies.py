import secrets
from functools import lru_cache

from fastapi import Header

from app.config import get_settings
from app.contracts.errors import DomainError, ErrorCode
from app.retrieval.hybrid import LexicalRetriever
from app.retrieval.service import get_retriever


def verify_api_key(x_savp_ai_key: str | None = Header(default=None)) -> None:
    settings = get_settings()
    expected = settings.api_key

    if not expected or len(expected) < 32:
        raise DomainError(
            ErrorCode.SERVICE_TEMPORARILY_UNAVAILABLE,
            "La autenticación del servicio no está configurada.",
            status_code=503,
        )
    if x_savp_ai_key is None or not secrets.compare_digest(expected, x_savp_ai_key):
        raise DomainError(
            ErrorCode.INVALID_REQUEST,
            "La credencial interna es inválida o está ausente.",
            status_code=401,
        )


@lru_cache(maxsize=1)
def knowledge_retriever() -> LexicalRetriever:
    try:
        return get_retriever()
    except Exception as exc:
        raise DomainError(
            ErrorCode.KNOWLEDGE_INDEX_UNAVAILABLE,
            "El índice de conocimiento no pudo cargarse.",
            status_code=503,
        ) from exc
