from functools import lru_cache
from pathlib import Path

from pydantic import Field
from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(
        env_prefix="SAVP_AI_",
        env_file=".env",
        env_file_encoding="utf-8",
        extra="ignore",
    )

    host: str = "127.0.0.1"
    port: int = Field(default=8001, ge=1, le=65535)
    api_key: str | None = None
    env: str = "development"
    log_level: str = "INFO"
    knowledge_path: Path = Path("./data/processed")
    index_path: Path = Path("./data/indices")
    semantic_enabled: bool = False
    local_llm_enabled: bool = False
    local_llm_url: str = "http://127.0.0.1:8091"
    local_llm_model: str = "mistralai/Ministral-3-3B-Instruct-2512-GGUF:Q4_K_M"
    local_llm_timeout_seconds: float = Field(default=45.0, gt=0, le=300)


@lru_cache
def get_settings() -> Settings:
    return Settings()
