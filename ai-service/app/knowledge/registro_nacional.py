"""Lectura común del registro nominal nacional; no certifica vigencia legal."""

import json
from pathlib import Path
from typing import Any

from app.knowledge.registry import SERVICE_ROOT

NATIONAL_REGISTRY = SERVICE_ROOT / "data/catalog/universidades_bolivia.json"


def load_national_registry(path: Path = NATIONAL_REGISTRY) -> dict[str, Any]:
    if not path.exists():
        return {}
    registry = json.loads(path.read_text(encoding="utf-8"))
    if not isinstance(registry, dict):
        raise ValueError("El registro nacional debe ser un objeto JSON.")
    institutions = registry.get("institutions", [])
    if not isinstance(institutions, list) or any(not isinstance(row, dict) for row in institutions):
        raise ValueError("Las instituciones del registro nacional deben ser objetos.")
    identifiers = [row.get("institution_id") for row in institutions]
    if len(identifiers) != len(set(identifiers)):
        raise ValueError("El registro nacional contiene instituciones duplicadas.")
    return registry
