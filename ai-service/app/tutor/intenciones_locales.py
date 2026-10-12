"""Clasificación de intenciones por vocabulario español; no genera conocimiento."""

from __future__ import annotations

import json
import math
import re
import time
import unicodedata
from collections import Counter
from functools import lru_cache
from typing import Any, cast

from app.knowledge.registry import SERVICE_ROOT

MODEL_PATH = SERVICE_ROOT / "data/tutor/intenciones_modelo.json"


def words(text: str) -> list[str]:
    normalized = unicodedata.normalize("NFKD", text.casefold())
    normalized = "".join(c for c in normalized if not unicodedata.combining(c))
    tokens = re.findall(r"[a-z0-9]+", normalized)
    return tokens + [
        first + " " + second for first, second in zip(tokens, tokens[1:], strict=False)
    ]


def train(examples: list[dict[str, str]]) -> dict[str, Any]:
    started = time.perf_counter()
    counts: dict[str, Counter[str]] = {}
    classes: Counter[str] = Counter()
    vocabulary: set[str] = set()
    for example in examples:
        label = example["intent"]
        tokens = words(example["text"])
        vocabulary.update(tokens)
        counts.setdefault(label, Counter()).update(tokens)
        classes[label] += 1
    return {
        "version": "intenciones-es-local-1.1",
        "algorithm": "multinomial_naive_bayes",
        "examples": len(examples),
        "vocabulary": sorted(vocabulary),
        "classes": dict(classes),
        "counts": {label: dict(values) for label, values in counts.items()},
        "training_seconds": round(time.perf_counter() - started, 6),
        "scope": "Intenciones de conversación; no aprende materias ni respuestas académicas",
    }


def predict(text: str, model: dict[str, Any]) -> tuple[str, float]:
    vocabulary = set(model["vocabulary"])
    tokens = [token for token in words(text) if token in vocabulary]
    if not tokens:
        return "GENERAL_PEDAGOGICAL", 0.0
    total = sum(model["classes"].values())
    scores = {}
    for label, examples in model["classes"].items():
        counts = model["counts"][label]
        denominator = sum(counts.values()) + len(vocabulary)
        scores[label] = math.log(examples / total) + sum(
            math.log((counts.get(token, 0) + 1) / denominator) for token in tokens
        )
    best = max(scores, key=lambda label: scores[label])
    maximum = scores[best]
    probability = 1 / sum(math.exp(value - maximum) for value in scores.values())
    return best, probability


@lru_cache(maxsize=1)
def load_local_model() -> dict[str, Any] | None:
    try:
        return cast(dict[str, Any], json.loads(MODEL_PATH.read_text(encoding="utf-8")))
    except (OSError, ValueError):
        return None


def local_intent(question: str) -> str | None:
    model = load_local_model()
    if not model:
        return None
    intent, confidence = predict(question, model)
    if confidence >= 0.75:
        return intent
    # Las fórmulas breves de cierre suelen compartir pocas palabras con el
    # entrenamiento. Aceptarlas con menor umbral sólo si no son una pregunta.
    if intent == "SOCIAL" and confidence >= 0.6 and not {"¿", "?"} & set(question):
        return intent
    if intent == "CAPABILITIES" and confidence >= 0.6:
        return intent
    return None
