import json
from collections.abc import Iterator
from pathlib import Path
from typing import Any

import pytest
from fastapi.testclient import TestClient

from app.config import get_settings
from app.main import app

FIXTURES = Path(__file__).resolve().parents[1] / "data" / "fixtures"
TEST_API_KEY = "test-only-internal-api-key-32-characters"


@pytest.fixture
def client(monkeypatch: pytest.MonkeyPatch) -> Iterator[TestClient]:
    monkeypatch.setenv("SAVP_AI_API_KEY", TEST_API_KEY)
    get_settings.cache_clear()
    with TestClient(
        app,
        raise_server_exceptions=False,
        headers={"X-SAVP-AI-Key": TEST_API_KEY},
    ) as test_client:
        yield test_client
    get_settings.cache_clear()


@pytest.fixture
def load_fixture() -> Any:
    def _load(name: str) -> dict[str, Any]:
        with (FIXTURES / name).open(encoding="utf-8") as handle:
            payload: dict[str, Any] = json.load(handle)
        payload.pop("fixture_type", None)
        payload.pop("seed", None)
        payload.pop("scenario_note", None)
        return payload

    return _load
