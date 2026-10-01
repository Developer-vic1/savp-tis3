import hashlib
import json
from collections import Counter
from typing import Any

import pytest
from fastapi.testclient import TestClient

from app.config import Settings
from app.riasec.instrument import load_instrument
from app.riasec.scoring import official_web_value_to_internal


def payload(value: int = 1) -> dict[str, Any]:
    instrument = load_instrument()
    return {
        "instrument_version": instrument["version"],
        "responses": [{"item_id": item["item_id"], "value": value} for item in instrument["items"]],
    }


def test_official_instrument_shape(client: TestClient) -> None:
    response = client.get("/api/v2/riasec/instrument")
    assert response.status_code == 200
    body = response.json()
    assert body["schema_version"] == "2.0"
    assert len(body["items"]) == 30
    assert [item["item_id"] for item in body["items"]] == list(range(1, 31))
    assert [option["value"] for option in body["response_scale"]] == list(range(1, 6))
    assert "code" not in body["items"][0]
    assert Counter(item["code"] for item in load_instrument()["items"]) == Counter(
        {code: 5 for code in "RIASEC"}
    )


def test_official_item_mapping_fingerprint() -> None:
    # Verified against all 30 index/area/text triples in the official Spanish web asset
    # https://onetinterestprofiler.org/es/assets/index-CD-U6n4j.js on 2026-09-30.
    items = load_instrument()["items"]
    canonical = json.dumps(items, ensure_ascii=False, sort_keys=True, separators=(",", ":"))
    assert hashlib.sha256(canonical.encode("utf-8")).hexdigest() == (
        "309e17a1101401a8559a81dd6f11f08924a1de407d984d0a9722313c12f1cdb4"
    )


@pytest.mark.parametrize("public,internal", [(1, 0), (2, 1), (3, 2), (4, 3), (5, 4)])
def test_scale_conversion(public: int, internal: int) -> None:
    assert official_web_value_to_internal(public) == internal


@pytest.mark.parametrize("invalid", [0, 6, True, 1.5])
def test_scale_conversion_rejects_invalid(invalid: object) -> None:
    with pytest.raises(ValueError):
        official_web_value_to_internal(invalid)  # type: ignore[arg-type]


@pytest.mark.parametrize("value,expected", [(1, 0), (5, 20)])
def test_score_extremes(client: TestClient, value: int, expected: int) -> None:
    response = client.post("/api/v2/riasec/score", json=payload(value))
    assert response.status_code == 200
    body = response.json()
    assert body["scores"] == {code: expected for code in "RIASEC"}
    assert body["top_codes"] == list("RIASEC")
    assert body["holland_code"] == "RIA"
    assert body["trace_id"] == response.headers["x-trace-id"]


@pytest.mark.parametrize("invalid", [0, 6, True, 1.5])
def test_score_rejects_invalid_values(client: TestClient, invalid: object) -> None:
    data = payload()
    data["responses"][0]["value"] = invalid
    assert client.post("/api/v2/riasec/score", json=data).status_code == 422


@pytest.mark.parametrize("count", [29, 31])
def test_score_requires_exactly_thirty(client: TestClient, count: int) -> None:
    data = payload()
    data["responses"] = (data["responses"] * 2)[:count]
    assert client.post("/api/v2/riasec/score", json=data).status_code == 422


def test_score_rejects_duplicate_and_unknown_id(client: TestClient) -> None:
    duplicate = payload()
    duplicate["responses"][-1]["item_id"] = 1
    response = client.post("/api/v2/riasec/score", json=duplicate)
    assert response.status_code == 422
    assert response.json()["error"]["code"] == "SAVP_AI_INCOMPLETE_INSTRUMENT"
    unknown = payload()
    unknown["responses"][-1]["item_id"] = 31
    assert client.post("/api/v2/riasec/score", json=unknown).status_code == 422


def test_score_is_independent_of_response_order(client: TestClient) -> None:
    data = payload()
    for response in data["responses"]:
        if response["item_id"] in {2, 4, 8, 10, 14, 16, 20, 22, 26, 28}:
            response["value"] = 5
    first = client.post("/api/v2/riasec/score", json=data).json()
    data["responses"].reverse()
    second = client.post("/api/v2/riasec/score", json=data).json()
    assert first["scores"] == second["scores"]
    assert first["top_codes"] == ["I", "S"]
    assert first["holland_code"] == second["holland_code"] == "ISR"


def test_wrong_version_is_rejected(client: TestClient) -> None:
    data = payload()
    data["instrument_version"] = "future"
    assert client.post("/api/v2/riasec/score", json=data).status_code == 422


def test_malformed_json_uses_sanitized_error(client: TestClient) -> None:
    response = client.post(
        "/api/v2/riasec/score", content="{", headers={"Content-Type": "application/json"}
    )
    assert response.status_code == 422
    assert response.json()["error"]["code"] == "SAVP_AI_INVALID_REQUEST"
    assert "traceback" not in response.text.casefold()


def test_production_requires_configured_key(
    client: TestClient, monkeypatch: pytest.MonkeyPatch
) -> None:
    monkeypatch.setattr(
        "app.api.dependencies.get_settings",
        lambda: Settings(env="production", api_key=None, _env_file=None),
    )
    response = client.get("/api/v2/riasec/instrument")
    assert response.status_code == 503
    assert response.json()["error"]["trace_id"] == response.headers["x-trace-id"]


def test_invalid_api_key_is_rejected(client: TestClient, monkeypatch: pytest.MonkeyPatch) -> None:
    monkeypatch.setattr(
        "app.api.dependencies.get_settings",
        lambda: Settings(env="production", api_key="valid", _env_file=None),
    )
    assert client.post(
        "/api/v2/riasec/score", json=payload(), headers={"X-SAVP-AI-Key": "invalid"}
    ).status_code == 401
