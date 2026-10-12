"""Regresiones del clasificador de intención, sin inferir calidad documental."""

import json
from pathlib import Path

import pytest

from app.knowledge.registry import SERVICE_ROOT
from app.tutor.intenciones_locales import load_local_model, predict, train
from app.tutor.service import classify_tutor_intent
from scripts.entrenar_conversacion_documental import DATASETS, evaluate, load_datasets


def test_training_development_and_diagnostics_have_no_normalized_duplicates(
    tmp_path: Path,
) -> None:
    source = SERVICE_ROOT / "data/tutor"
    for filename in DATASETS.values():
        (tmp_path / filename).write_text(
            (source / filename).read_text(encoding="utf-8"), encoding="utf-8"
        )
    datasets = load_datasets(tmp_path)
    assert set(datasets) == set(DATASETS)
    development_path = tmp_path / DATASETS["development"]
    development = json.loads(development_path.read_text(encoding="utf-8"))
    development["examples"][0]["text"] = "¡Hóla!"
    development_path.write_text(
        json.dumps(development, ensure_ascii=False), encoding="utf-8"
    )
    with pytest.raises(ValueError, match="duplicado tras normalizar"):
        load_datasets(tmp_path)


def test_evaluation_keeps_model_and_deployed_predictions_separate() -> None:
    datasets = load_datasets(SERVICE_ROOT / "data/tutor")
    model = train(datasets["training"])
    result = evaluate(datasets["adversarial_diagnostic"], model)
    assert result["model_correct"] != result["deployed_correct"]
    assert sum(row["count"] for row in result["confusion"]) == result["examples"]
    assert result["unsafe_evidence_to_social_or_capabilities"] == 0
    assert any(
        row["intent"] != row["deployed_prediction"] for row in result["errors"]
    )


@pytest.mark.parametrize(
    ("question", "expected"),
    [
        ("Adiós hasta la próxima", "SOCIAL"),
        ("Hola, ¿guardas mis conversaciones?", "CAPABILITIES"),
        ("Gracias, ahora explícame fracciones", "GENERAL_PEDAGOGICAL"),
        ("¿Qué carreras ofrece UNIFRANZ en La Paz?", "EVIDENCE_GROUNDED"),
        ("¿Qué materias tiene Sistemas en UNIFRANZ?", "EVIDENCE_GROUNDED"),
        (
            "Enumera las asignaturas de Organización Empresarial por semestre en UNIFRANZ",
            "EVIDENCE_GROUNDED",
        ),
    ],
)
def test_router_preserves_substantive_question_over_small_talk(
    question: str, expected: str
) -> None:
    assert classify_tutor_intent(question) == expected


def test_saved_model_only_contains_intent_counts() -> None:
    model = load_local_model()
    assert model is not None
    assert model["algorithm"] == "multinomial_naive_bayes"
    assert model["examples"] == 72
    assert set(model["classes"]) == {
        "SOCIAL",
        "CAPABILITIES",
        "EVIDENCE_GROUNDED",
        "GENERAL_PEDAGOGICAL",
    }
    assert predict("Materias de Sistemas en UNIFRANZ", model)[0] == "EVIDENCE_GROUNDED"
