"""Clasificador supervisado local de bloques curriculares; no decide carreras."""

import json
import math
import re
from pathlib import Path
from typing import Any

from app.knowledge.registry import SERVICE_ROOT

MODEL_PATH = SERVICE_ROOT / "data/knowledge/herramientas/clasificador_curricular.json"


def caracteristicas(text: str, in_region: bool) -> list[float]:
    letters = sum(character.isalpha() for character in text)
    return [
        1.0,
        float(in_region),
        min(len(text) / 150, 1),
        min(len(text.split()) / 12, 1),
        letters / max(len(text), 1),
        float(text.strip().isdigit()),
        float(text.strip().isupper()),
        float(bool(re.search(r"semestre|programa de|conoce más|microcredenciales", text, re.I))),
        sum(character.isupper() for character in text) / max(letters, 1),
    ]


def probabilidad(features: list[float], weights: list[float]) -> float:
    score = sum(value * weight for value, weight in zip(features, weights, strict=True))
    return 1 / (1 + math.exp(-max(-40, min(40, score))))


def entrenar(records: list[dict[str, Any]], epochs: int = 400) -> list[float]:
    if not records or {item["label"] for item in records} != {0, 1}:
        raise ValueError("El conjunto necesita materias y bloques no curriculares etiquetados.")
    weights = [0.0] * 9
    for _ in range(epochs):
        gradient = [0.0] * 9
        for record in records:
            features = record["features"]
            error = probabilidad(features, weights) - record["label"]
            for index, value in enumerate(features):
                gradient[index] += error * value
        weights = [
            weight - 0.8 * (value / len(records) + 0.001 * weight)
            for weight, value in zip(weights, gradient, strict=True)
        ]
    return [float(value) for value in weights]


def cargar_modelo(path: Path = MODEL_PATH) -> list[float] | None:
    if not path.is_file():
        return None
    data = json.loads(path.read_text(encoding="utf-8"))
    weights = data.get("weights", [])
    if (
        data.get("version") != "bloques-curriculares-v1"
        or len(weights) != 9
        or any(not isinstance(value, (int, float)) or not math.isfinite(value) for value in weights)
    ):
        raise ValueError("Modelo de bloques incompatible.")
    return [float(value) for value in weights]
