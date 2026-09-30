import json
from pathlib import Path

from app.knowledge.registry import load_career_catalog, load_source_manifest

SERVICE_ROOT = Path(__file__).resolve().parents[2]
BRIDGE_V2_PATH = SERVICE_ROOT / "data" / "bridge" / "secondary_university_v2.json"

ALLOWED_STATUSES = {
    "DIRECTLY_DOCUMENTED",
    "DOCUMENT_SUPPORTED_INFERENCE",
    "HYPOTHESIS",
    "INSUFFICIENT_EVIDENCE",
}


def test_bridge_v2_structure_and_invariants() -> None:
    assert BRIDGE_V2_PATH.is_file()
    payload = json.loads(BRIDGE_V2_PATH.read_text(encoding="utf-8"))

    assert payload["bridge_version"] == "secondary-university-v2-evidence-based"
    assert "CONFIGURACIÓN EXPERIMENTAL" in payload["status"]
    relations = payload["relations"]
    assert len(relations) >= 15

    manifest = load_source_manifest()
    known_sources = {s.source_id for s in manifest.sources}
    catalog = load_career_catalog()
    known_careers = {c.career_id for c in catalog.careers}

    serialized = json.dumps(payload, ensure_ascii=False)
    # Ensure no forbidden decision weights or thresholds exist in V2 bridge
    for forbidden in ('"weight"', '"required"', '"confidence_status"', '"score"'):
        assert (
            forbidden not in serialized
        ), f"V2 bridge no debe contener campo prohibido: {forbidden}"

    for relation in relations:
        assert relation["relation_id"].startswith("BR2-")
        assert relation["secondary_content"]
        assert relation["competency"]
        assert relation["university_knowledge"]
        assert relation["initial_subject"]
        assert relation["relation_status"] in ALLOWED_STATUSES
        assert relation["source_secondary"] in known_sources
        assert relation["source_university"] in known_sources
        assert isinstance(relation["career_ids"], list)
        assert len(relation["career_ids"]) > 0
        for career_id in relation["career_ids"]:
            assert career_id in known_careers
        assert len(relation["limitations"]) > 0
        assert relation["version"] == "2.0.0"

    # The source documents establish each endpoint, not the relation itself.
    assert all(
        relation["relation_status"] != "DIRECTLY_DOCUMENTED"
        for relation in relations
    )


def test_bridge_v2_splits_sciences_and_bth_properly() -> None:
    payload = json.loads(BRIDGE_V2_PATH.read_text(encoding="utf-8"))
    relation_ids = {r["relation_id"] for r in payload["relations"]}

    # Verify split relations for environmental engineering
    assert "BR2-BIO-ENV-UCB" in relation_ids
    assert "BR2-CHEM-ENV-UCB" in relation_ids
    assert "BR2-MATH-ENV-UCB" in relation_ids
    assert "BR2-BTH-ENV-UCB" in relation_ids

    # Verify split relations for civil engineering UCB and UMSA
    assert "BR2-MATH-CIV-UCB" in relation_ids
    assert "BR2-PHYS-CIV-UCB" in relation_ids
    assert "BR2-CHEM-CIV-UCB" in relation_ids
    assert "BR2-BTH-CONST-CIV-UCB" in relation_ids

    assert "BR2-MATH-CIV-UMSA" in relation_ids
    assert "BR2-CHEM-CIV-UMSA" in relation_ids
    assert "BR2-BTH-CONST-CIV-UMSA" in relation_ids
