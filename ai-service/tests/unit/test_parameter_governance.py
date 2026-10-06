import json
from pathlib import Path

from app.knowledge.registry import known_evidence_reference_ids
from app.retrieval.service import (
    EXCERPT_MAX_CHARS,
    MIN_QUERY_TERM_OVERLAP,
)
from app.tutor.service import TUTOR_CANDIDATE_K, TUTOR_CONTEXT_K

SERVICE_ROOT = Path(__file__).resolve().parents[2]
PARAMS_REGISTRY_PATH = SERVICE_ROOT / "data" / "parameters" / "registry.json"

ALLOWED_CATEGORIES = {
    "PSYCHOMETRIC_DOMAIN",
    "EDUCATIONAL_NORMATIVE",
    "DECISION_MODEL_MCDA",
    "RETRIEVAL_IR_ENGINEERING",
    "PROMPT_LLM",
    "OPERATIONAL_API",
}

ALLOWED_CLASSIFICATIONS = {
    "DOCUMENTED",
    "DERIVED",
    "EMPIRICALLY_EVALUATED",
    "EXPERIMENTAL",
    "PROHIBITED",
}


def test_parameter_registry_integrity() -> None:
    assert PARAMS_REGISTRY_PATH.is_file()
    payload = json.loads(PARAMS_REGISTRY_PATH.read_text(encoding="utf-8"))

    assert payload["registry_version"] == "1.0.0"
    parameters = payload["parameters"]
    assert len(parameters) >= 20

    param_ids = set()
    prohibited_count = 0
    known_references = known_evidence_reference_ids()

    assert set(payload["categories"]) == ALLOWED_CATEGORIES
    assert set(payload["classifications"]) == ALLOWED_CLASSIFICATIONS

    for param in parameters:
        pid = param["parameter_id"]
        assert pid.startswith("PARAM-")
        assert pid not in param_ids, f"ID de parámetro duplicado: {pid}"
        param_ids.add(pid)

        assert param["category"] in ALLOWED_CATEGORIES
        assert param["classification"] in ALLOWED_CLASSIFICATIONS
        assert param["construct"]
        assert param["method"]
        assert param["scope"]
        assert param["version"]
        assert param["review_status"]
        assert isinstance(param["limitations"], list)
        assert set(param["source_ids"]) <= known_references

        if param["classification"] == "PROHIBITED":
            prohibited_count += 1

    # Verify that V1 arbitrary parameters are explicitly registered as PROHIBITED
    assert prohibited_count >= 2
    assert "PARAM-V1-AFFINITY-RIASEC-WEIGHT" in param_ids
    assert "PARAM-V1-ACADEMIC-REQUIRED-THRESHOLD" in param_ids

    by_id = {parameter["parameter_id"]: parameter for parameter in parameters}
    for parameter_id in (
        "PARAM-IR-BM25-K1",
        "PARAM-IR-BM25-B",
        "PARAM-IR-TOP-K-RETRIEVAL",
    ):
        assert by_id[parameter_id]["classification"] == "EXPERIMENTAL"

    assert by_id["PARAM-IR-CHUNK-TARGET-CHARS"]["value"] == 1200
    assert by_id["PARAM-IR-CHUNK-TARGET-CHARS"]["unit"] == "caracteres"
    assert by_id["PARAM-IR-CHUNK-OVERLAP-CHARS"]["value"] == 180
    assert by_id["PARAM-IR-CHUNK-OVERLAP-CHARS"]["unit"] == "caracteres"
    assert by_id["PARAM-IR-TOP-K-RETRIEVAL"]["value"] == TUTOR_CONTEXT_K
    assert (
        by_id["PARAM-IR-MIN-QUERY-TERM-OVERLAP"]["value"]
        == MIN_QUERY_TERM_OVERLAP
    )
    assert by_id["PARAM-IR-EXCERPT-MAX-CHARS"]["value"] == EXCERPT_MAX_CHARS
    assert by_id["PARAM-TUTOR-CANDIDATE-K"]["value"] == TUTOR_CANDIDATE_K
