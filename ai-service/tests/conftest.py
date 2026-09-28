import json
from pathlib import Path
from typing import Any

import pytest
from fastapi.testclient import TestClient

from app.main import app

FIXTURES = Path(__file__).resolve().parents[1] / "data" / "fixtures"


@pytest.fixture
def client() -> TestClient:
    return TestClient(app, raise_server_exceptions=False)


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

