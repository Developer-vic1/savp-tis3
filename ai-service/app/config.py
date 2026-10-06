from functools import lru_cache
from pathlib import Path
from typing import Literal

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
    env: Literal["development", "test", "production", "prod"] = "development"
    log_level: str = "INFO"
    knowledge_path: Path = Path("./data/processed")
    source_intake_path: Path | None = None


@lru_cache
def get_settings() -> Settings:
    return Settings()
