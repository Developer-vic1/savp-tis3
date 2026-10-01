import json
from functools import lru_cache
from pathlib import Path
from typing import Any

INSTRUMENT_PATH = (
    Path(__file__).resolve().parents[2] / "data" / "instruments" / "onet_mini_ip_v2_es.json"
)


@lru_cache
def load_instrument() -> dict[str, Any]:
    with INSTRUMENT_PATH.open(encoding="utf-8") as handle:
        instrument: dict[str, Any] = json.load(handle)
    return instrument

