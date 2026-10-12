"""Entrena un clasificador local de intención y audita su evaluación; sin LLM."""

import json
import re
import sys
import time
import unicodedata
from collections import Counter
from datetime import UTC, datetime
from pathlib import Path
from typing import Any

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from app.knowledge.registry import SERVICE_ROOT  # noqa: E402
from app.tutor.intenciones_locales import MODEL_PATH, load_local_model, predict, train  # noqa: E402
from app.tutor.service import classify_tutor_intent  # noqa: E402

LABELS = {"SOCIAL", "CAPABILITIES", "EVIDENCE_GROUNDED", "GENERAL_PEDAGOGICAL"}
DATASETS = {
    "training": "intenciones_entrenamiento.json",
    "development": "intenciones_evaluacion.json",
    "previously_reviewed_test": "intenciones_prueba_independiente.json",
    "adversarial_diagnostic": "intenciones_desafio.json",
}


def canonical(text: str) -> str:
    folded = unicodedata.normalize("NFKD", text.casefold())
    plain = "".join(char for char in folded if not unicodedata.combining(char))
    return " ".join(re.findall(r"[a-z0-9]+", plain))


def load_datasets(root: Path) -> dict[str, list[dict[str, str]]]:
    datasets: dict[str, list[dict[str, str]]] = {}
    seen: dict[str, str] = {}
    for name, filename in DATASETS.items():
        document = json.loads((root / filename).read_text(encoding="utf-8"))
        examples = document["examples"]
        if not isinstance(examples, list) or not examples:
            raise ValueError(f"{filename}: se requieren ejemplos")
        for index, row in enumerate(examples, start=1):
            if not isinstance(row, dict) or not isinstance(row.get("text"), str):
                raise ValueError(f"{filename}:{index}: texto inválido")
            if row.get("intent") not in LABELS:
                raise ValueError(f"{filename}:{index}: intención desconocida")
            key = canonical(row["text"])
            if not key:
                raise ValueError(f"{filename}:{index}: texto vacío")
            if key in seen:
                raise ValueError(
                    f"Ejemplo duplicado tras normalizar: {seen[key]} y {filename}:{index}"
                )
            seen[key] = f"{filename}:{index}"
        datasets[name] = examples
    if {row["intent"] for row in datasets["training"]} != LABELS:
        raise ValueError("El entrenamiento debe cubrir las cuatro intenciones")
    return datasets


def evaluate(examples: list[dict[str, str]], model: dict[str, Any]) -> dict[str, Any]:
    rows = [
        {
            **row,
            "model_prediction": predict(row["text"], model)[0],
            "deployed_prediction": classify_tutor_intent(row["text"]),
        }
        for row in examples
    ]
    confusion = Counter(
        (row["intent"], row["deployed_prediction"]) for row in rows
    )
    per_class = {}
    for label in sorted(LABELS):
        true_positive = confusion[label, label]
        predicted = sum(count for (_, result), count in confusion.items() if result == label)
        actual = sum(count for (expected, _), count in confusion.items() if expected == label)
        precision = true_positive / predicted if predicted else 0.0
        recall = true_positive / actual if actual else 0.0
        per_class[label] = {
            "support": actual,
            "precision": round(precision, 4),
            "recall": round(recall, 4),
            "f1": round(2 * precision * recall / (precision + recall), 4)
            if precision + recall
            else 0.0,
        }
    return {
        "examples": len(rows),
        "model_correct": sum(row["intent"] == row["model_prediction"] for row in rows),
        "deployed_correct": sum(
            row["intent"] == row["deployed_prediction"] for row in rows
        ),
        "model_accuracy": round(
            sum(row["intent"] == row["model_prediction"] for row in rows) / len(rows), 4
        ),
        "deployed_accuracy": round(
            sum(row["intent"] == row["deployed_prediction"] for row in rows) / len(rows), 4
        ),
        "per_class": per_class,
        "confusion": [
            {"expected": expected, "predicted": predicted, "count": count}
            for (expected, predicted), count in sorted(confusion.items())
        ],
        "unsafe_evidence_to_social_or_capabilities": sum(
            row["intent"] == "EVIDENCE_GROUNDED"
            and row["deployed_prediction"] in {"SOCIAL", "CAPABILITIES"}
            for row in rows
        ),
        "errors": [
            row for row in rows if row["intent"] != row["deployed_prediction"]
        ],
    }


def main() -> None:
    started = time.perf_counter()
    root = SERVICE_ROOT / "data/tutor"
    datasets = load_datasets(root)
    model = train(datasets["training"])
    MODEL_PATH.write_text(json.dumps(model, ensure_ascii=False, indent=2), encoding="utf-8")
    load_local_model.cache_clear()
    results = {
        name: evaluate(examples, model)
        for name, examples in datasets.items()
        if name != "training"
    }
    report = {
        "created_at": datetime.now(UTC).isoformat(),
        "model_version": model["version"],
        "training_examples": len(datasets["training"]),
        "vocabulary_terms": len(model["vocabulary"]),
        "training_seconds": model["training_seconds"],
        "total_seconds": round(time.perf_counter() - started, 6),
        "dataset_roles": {
            "development": "Ajustes conocidos; no prueba reservada",
            "previously_reviewed_test": (
                "Prueba histórica inspeccionada antes de esta revisión; retest, no ciego"
            ),
            "adversarial_diagnostic": (
                "Casos sintéticos nuevos de seguimiento y confusión; diagnóstico exploratorio"
            ),
        },
        "datasets": results,
        "limitations": [
            "Frases sintéticas pequeñas: no miden comprensión ni calidad de respuesta",
            "El clasificador aprende etiquetas; no materias, instituciones ni respuestas",
            "La predicción del modelo y la decisión del flujo con reglas son resultados distintos",
            "El flujo acepta SOCIAL/CAPABILITIES del modelo; evidencia usa reglas",
            "El conjunto histórico ya fue examinado; no es un test ciego futuro",
        ],
    }
    (root / "evaluacion_conversacion.json").write_text(
        json.dumps(report, ensure_ascii=False, indent=2), encoding="utf-8"
    )
    print(
        json.dumps(
            {
                "training_examples": report["training_examples"],
                "training_seconds": report["training_seconds"],
                "total_seconds": report["total_seconds"],
                "datasets": {
                    name: {
                        key: result[key]
                        for key in ("examples", "model_correct", "deployed_correct")
                    }
                    for name, result in results.items()
                },
            },
            ensure_ascii=True,
        )
    )


if __name__ == "__main__":
    main()
