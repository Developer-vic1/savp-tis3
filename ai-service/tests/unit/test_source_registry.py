from pathlib import Path

from app.knowledge.registry import (
    calculate_source_quality_metrics,
    load_source_manifest,
    validate_local_source_hashes,
)

SERVICE_ROOT = Path(__file__).resolve().parents[2]


def test_source_registry_completeness_and_quality() -> None:
    manifest = load_source_manifest()
    assert len(manifest.sources) >= 11
    assert manifest.manifest_version == "2.2.0"
    assert manifest.governance_policy == "OFFICIAL_SOURCES_ONLY_WITH_EXPLICIT_AUTHORITY_TIER"

    for source in manifest.sources:
        assert source.source_id.startswith("BO-")
        assert source.official is True
        assert source.authority_tier in (
            "TIER_1_MINISTRY_NATIONAL_REGULATION",
            "TIER_2_OFFICIAL_UNIVERSITY_CURRICULUM",
        )
        assert source.country == "Bolivia"
        assert source.verification_status == "VALID"
        assert source.sha256 is not None
        assert source.document_hash == f"sha256:{source.sha256}"
        assert (SERVICE_ROOT / source.local_path).is_file()


def test_source_registry_all_local_hashes_valid() -> None:
    errors = validate_local_source_hashes()
    assert errors == [], f"Hash validation failed for sources: {errors}"


def test_calculate_source_quality_metrics() -> None:
    metrics = calculate_source_quality_metrics()
    assert metrics["source_count"] >= 11
    assert metrics["official_source_ratio"] == 1.0
    assert metrics["hash_validity"] == "ALL_VALID"
    assert metrics["missing_local_sources"] == []
    assert metrics["superseded_sources"] == []
    assert metrics["unverified_sources"] == []
    assert metrics["metadata_completeness_ratio"] > 0.90
    tier_dist = metrics["tier_distribution"]
    assert isinstance(tier_dist, dict)
    assert tier_dist.get("TIER_1_MINISTRY_NATIONAL_REGULATION", 0) >= 2
    assert tier_dist.get("TIER_2_OFFICIAL_UNIVERSITY_CURRICULUM", 0) >= 9
