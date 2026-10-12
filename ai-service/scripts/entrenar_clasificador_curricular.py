"""Entrena clasificación de bloques con etiquetas visuales y evalúa una reserva fija."""

import hashlib
import json
import random
import sys
import time
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from app.knowledge.analisis_curricular import regiones, semestre_del_bloque  # noqa: E402
from app.knowledge.clasificador_bloques import (  # noqa: E402
    MODEL_PATH,
    caracteristicas,
    entrenar,
    probabilidad,
)
from app.knowledge.coleccion_universitaria import COLLECTION_ROOT, _atomic  # noqa: E402


def ejecutar() -> dict:
    import pymupdf

    annotations = Path("data/evaluation/psicologia_malla_etiquetada.json")
    gold = json.loads(annotations.read_text(encoding="utf-8"))
    path = COLLECTION_ROOT / "instantaneas" / (gold["document_hash"][7:] + ".pdf")
    if hashlib.sha256(path.read_bytes()).hexdigest() != gold["document_hash"][7:]:
        raise ValueError("El documento no corresponde a las etiquetas visuales.")
    records = []
    matched = 0
    with pymupdf.open(path) as document:
        for number, page in enumerate(document, 1):
            blocks, headings = regiones(page)
            for block in blocks:
                label = any(
                    item["page"] == number
                    and max(abs(a - b) for a, b in zip(item["bbox"], block[:4], strict=True)) < 1
                    for item in gold["subjects"]
                )
                matched += label
                records.append(
                    {
                        "page": number,
                        "bbox": list(block[:4]),
                        "text": block[4],
                        "features": caracteristicas(
                            block[4], semestre_del_bloque(block, headings) is not None
                        ),
                        "label": int(label),
                    }
                )
    if matched != gold["expected_total"]:
        raise ValueError("Las etiquetas no se corresponden con todos los bloques originales.")
    random.Random(20261009).shuffle(records)
    split = int(len(records) * 0.8)
    training, test = records[:split], records[split:]
    started = time.perf_counter()
    weights = entrenar(training)
    seconds = time.perf_counter() - started
    tp = fp = fn = tn = 0
    for record in test:
        predicted = probabilidad(record["features"], weights) >= 0.5
        tp += predicted and record["label"] == 1
        fp += predicted and record["label"] == 0
        fn += not predicted and record["label"] == 1
        tn += not predicted and record["label"] == 0
    report = {
        "version": "bloques-curriculares-v1",
        "method": "Regresión logística supervisada local",
        "weights": weights,
        "epochs": 400,
        "threshold": 0.5,
        "seed": 20261009,
        "training_seconds": round(seconds, 4),
        "examples": len(records),
        "train_examples": len(training),
        "holdout_examples": len(test),
        "positive_examples": matched,
        "gold_hash": gold["document_hash"],
        "labels_hash": hashlib.sha256(annotations.read_bytes()).hexdigest(),
        "confusion_matrix": {"tp": tp, "fp": fp, "fn": fn, "tn": tn},
        "precision": tp / max(tp + fp, 1),
        "recall": tp / max(tp + fn, 1),
        "scope": (
            "Reserva fija de bloques de un único folleto. "
            "No acredita generalización a otras universidades ni reentrena Tesseract."
        ),
        "source_url": gold["source_url"],
    }
    _atomic(MODEL_PATH, json.dumps(report, ensure_ascii=False, indent=2).encode())
    _atomic(
        Path("data/evaluation/entrenamiento_clasificador_curricular.json"),
        json.dumps(report, ensure_ascii=False, indent=2).encode(),
    )
    print(
        json.dumps(
            {key: value for key, value in report.items() if key != "weights"},
            ensure_ascii=False,
            indent=2,
        )
    )
    return report


if __name__ == "__main__":
    ejecutar()
