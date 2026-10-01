import json
from pathlib import Path

from app.knowledge.registry import (
    known_evidence_reference_ids,
    load_career_catalog,
)

SERVICE_ROOT = Path(__file__).resolve().parents[2]
CROSSWALK_PATH = SERVICE_ROOT / "data" / "crosswalk" / "career_occupation_v1.json"

ALLOWED_RELATION_TYPES = {
    "RELATED_OCCUPATION",
    "POSSIBLE_OCCUPATION",
    "INSUFFICIENT_EVIDENCE",
}


def test_career_occupation_crosswalk_integrity() -> None:
    assert CROSSWALK_PATH.is_file()
    payload = json.loads(CROSSWALK_PATH.read_text(encoding="utf-8"))

    assert payload["crosswalk_version"] == "career-occupation-v1.0.0"
    relations = payload["relations"]
    assert len(relations) >= 12

    catalog = load_career_catalog()
    known_careers = {c.career_id for c in catalog.careers}
    known_references = known_evidence_reference_ids()

    for relation in relations:
        assert relation["career_id"] in known_careers
        assert relation["occupation_id"]
        assert relation["occupation_system"] in ("ISCO-08", "O*NET-SOC-2019")
        assert relation["occupation_title"]
        assert relation["relation_type"] in ALLOWED_RELATION_TYPES
        assert (
            relation["relation_type"] != "EQUIVALENT"
        ), "No se permite EQUIVALENT sin demostración legal"
        assert relation["evidence_status"] in (
            "DIRECTLY_DOCUMENTED",
            "DOCUMENT_SUPPORTED_INFERENCE",
            "HYPOTHESIS",
            "INSUFFICIENT_EVIDENCE",
        )
        assert len(relation["justification"]) > 10
        assert len(relation["limitations"]) > 0
        assert relation["version"] == "1.0.0"
        assert set(relation["source_ids"]) <= known_references

        if (
            relation["occupation_system"] == "O*NET-SOC-2019"
            and relation["riasec_occupational_profile"] is not None
        ):
            assert len(relation["riasec_occupational_profile"]) in (2, 3)
            assert all(c in "RIASEC" for c in relation["riasec_occupational_profile"])

    assert all(
        relation["evidence_status"] != "DIRECTLY_DOCUMENTED"
        for relation in relations
    )


def test_verified_onet_31_codes_and_interest_profiles() -> None:
    payload = json.loads(CROSSWALK_PATH.read_text(encoding="utf-8"))
    actual = {
        relation["occupation_id"]: (
            relation["occupation_title"],
            relation["riasec_occupational_profile"],
        )
        for relation in payload["relations"]
        if relation["occupation_system"] == "O*NET-SOC-2019"
    }
    assert actual == {
        "ONET-15-1252.00": ("Software Developers", "IC"),
        "ONET-15-1211.00": ("Computer Systems Analysts", "IC"),
        "ONET-15-1241.00": ("Computer Network Architects", "ICR"),
        "ONET-19-3033.00": ("Clinical and Counseling Psychologists", "SIC"),
        "ONET-19-3034.00": ("School Psychologists", "SIC"),
        "ONET-19-3032.00": ("Industrial-Organizational Psychologists", "IEC"),
        "ONET-17-2051.00": ("Civil Engineers", "RIC"),
        "ONET-17-2081.00": ("Environmental Engineers", "IRC"),
        "ONET-19-2041.00": (
            "Environmental Scientists and Specialists",
            "IRC",
        ),
    }
