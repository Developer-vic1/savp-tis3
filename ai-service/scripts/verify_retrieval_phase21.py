"""Require current DEV, freeze, and TEST artifacts before declaring retrieval ready."""

import hashlib
import json
from pathlib import Path
from typing import Any, cast

from app.knowledge.registry import SERVICE_ROOT
from app.retrieval.embeddings import MODEL_SPECS
from app.retrieval.index import CORPUS_PATH, SELECTED_INDEX

EVALUATION = SERVICE_ROOT / "data/evaluation"


def read(path: Path) -> dict[str, Any]:
    data = json.loads(path.read_text(encoding="utf-8"))
    if not isinstance(data, dict):
        raise ValueError(f"Se esperaba objeto JSON: {path}")
    return cast(dict[str, Any], data)


def main() -> int:
    try:
        corpus_sha = hashlib.sha256(CORPUS_PATH.read_bytes()).hexdigest()
        dev_path = EVALUATION / "retrieval_dev_phase21.json"
        dev = read(dev_path)
        freeze = read(EVALUATION / "retrieval_freeze_phase21.json")
        test = read(EVALUATION / "retrieval_test_phase21.json")
        selected = read(SELECTED_INDEX)
        if any(item["corpus_sha256"] != corpus_sha for item in (dev, freeze, test)):
            raise ValueError("DEV/FREEZE/TEST tienen otro hash de corpus")
        if dev["split"] != "DEV" or test["split"] != "TEST":
            raise ValueError("Splits incorrectos")
        if {item["model_id"] for item in dev["models"]} != set(MODEL_SPECS):
            raise ValueError("DEV no evaluó ambos modelos")
        if len(test["models"]) != 1 or test["models"][0]["model_id"] != freeze["model_id"]:
            raise ValueError("TEST no corresponde al modelo congelado")
        if selected["model_id"] != freeze["model_id"]:
            raise ValueError("El modelo seleccionado difiere del congelado")
        if freeze["dev_result_sha256"] != hashlib.sha256(dev_path.read_bytes()).hexdigest():
            raise ValueError("DEV cambió después de congelar")
        if test["evaluated_at"] <= freeze["frozen_at"]:
            raise ValueError("TEST se evaluó antes del freeze")
        for result in (*dev["models"], *test["models"]):
            if result["index_manifest"]["corpus_sha256"] != corpus_sha:
                raise ValueError("Resultado asociado a un índice anterior")
            if result["positive_query_count"] < 1:
                raise ValueError("No se midieron consultas con evidencia")
            for mode in ("semantic", "hybrid"):
                if set(result[mode]["metrics"]) != {
                    "recall_at_1", "recall_at_3", "recall_at_5", "mrr", "ndcg_at_5"
                }:
                    raise ValueError("Faltan métricas de retrieval")
        print("PASS: DEV, FREEZE y TEST corresponden al corpus e índices actuales")
        return 0
    except (OSError, KeyError, ValueError, TypeError, json.JSONDecodeError) as exc:
        print(f"FAIL: retrieval fase 2.1: {exc}")
        return 1


if __name__ == "__main__":
    raise SystemExit(main())
