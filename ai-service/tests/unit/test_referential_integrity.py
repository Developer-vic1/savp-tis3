import json
from pathlib import Path
from typing import Any, cast

from app.knowledge.registry import (
    known_evidence_reference_ids,
    known_external_information_ids,
    load_reference_registry,
    load_source_manifest,
)

SERVICE_ROOT = Path(__file__).resolve().parents[2]


def _read(relative_path: str) -> dict[str, Any]:
    payload = json.loads((SERVICE_ROOT / relative_path).read_text(encoding="utf-8"))
    if not isinstance(payload, dict):
        raise TypeError(f"se esperaba un objeto JSON en {relative_path}")
    return cast(dict[str, Any], payload)


def test_reference_registry_locations_and_namespaces() -> None:
    registry = load_reference_registry()
    source_ids = {source.source_id for source in load_source_manifest().sources}
    reference_ids = {reference.reference_id for reference in registry.references}

    assert source_ids.isdisjoint(reference_ids)
    for reference in registry.references:
        if reference.reference_kind == "INTERNAL_ARTIFACT":
            assert reference.local_path is not None
            assert (SERVICE_ROOT / reference.local_path).is_file()
            assert reference.verification_status == "LOCAL_ARTIFACT"
        else:
            assert reference.url is not None
            assert reference.url.startswith("https://")
            assert reference.verification_status == "VERIFIED"


def test_all_governed_source_ids_resolve() -> None:
    known = known_evidence_reference_ids()
    known_external = known_external_information_ids()
    used: set[str] = set()

    bridge = _read("data/bridge/secondary_university_v2.json")
    for relation in bridge["relations"]:
        used.update((relation["source_secondary"], relation["source_university"]))

    catalog = _read("data/catalog/careers.json")
    for career in catalog["careers"]:
        career_sources = set(career.get("source_ids", []))
        if career.get("recommendation_eligible", True):
            used.update(career_sources)
            assert career_sources <= known
        else:
            assert career_sources <= known_external

    for path, collection in (
        ("data/crosswalk/career_occupation_v1.json", "relations"),
        ("data/parameters/registry.json", "parameters"),
    ):
        payload = _read(path)
        for item in payload[collection]:
            used.update(item.get("source_ids", []))

    for path in (
        "data/evaluation/retrieval_dev.json",
        "data/evaluation/retrieval_test.json",
        "data/evaluation/retrieval_queries.json",
    ):
        payload = _read(path)
        for query in payload["queries"]:
            used.update(query.get("relevant_source_ids", []))

    assert used
    assert used <= known, f"referencias fantasma: {sorted(used - known)}"


def test_retrieval_dataset_splits_and_chunk_labels_are_consistent() -> None:
    corpus_rows = [
        json.loads(line)
        for line in (SERVICE_ROOT / "data/processed/corpus.jsonl")
        .read_text(encoding="utf-8")
        .splitlines()
        if line.strip()
    ]
    source_by_chunk = {row["chunk_id"]: row["source_id"] for row in corpus_rows}
    assert len(source_by_chunk) == len(corpus_rows)

    dev = _read("data/evaluation/retrieval_dev.json")["queries"]
    test = _read("data/evaluation/retrieval_test.json")["queries"]
    combined = _read("data/evaluation/retrieval_queries.json")["queries"]
    dev_ids = {item["query_id"] for item in dev}
    test_ids = {item["query_id"] for item in test}
    combined_ids = {item["query_id"] for item in combined}

    assert dev_ids.isdisjoint(test_ids)
    assert dev_ids | test_ids == combined_ids
    assert len(combined_ids) == len(combined)
    assert len({item["query"] for item in combined}) == len(combined)

    for item in combined:
        relevant_sources = set(item["relevant_source_ids"])
        relevant_chunks = set(item["relevant_chunk_ids"])
        assert relevant_chunks <= source_by_chunk.keys()
        assert {source_by_chunk[chunk_id] for chunk_id in relevant_chunks} <= (relevant_sources)
        if item["category"] == "NO_ANSWER":
            assert not relevant_sources
            assert not relevant_chunks
