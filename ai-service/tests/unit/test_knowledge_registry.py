from app.knowledge.registry import (
    calculate_source_quality_metrics,
    load_career_catalog,
    load_external_information_registry,
    load_source_manifest,
    validate_local_source_hashes,
)


def test_source_manifest_is_official_unique_and_matches_local_hashes() -> None:
    manifest = load_source_manifest()
    assert len(manifest.sources) == 16
    assert all(source.official for source in manifest.sources)
    assert all(source.evidence_layer == "CORPUS_VALIDADO" for source in manifest.sources)
    assert validate_local_source_hashes() == []


def test_initial_catalog_has_real_institution_specific_careers() -> None:
    catalog = load_career_catalog()
    assert len(catalog.universities) == 5
    assert len(catalog.careers) == 8
    civil_ids = {
        career.career_id for career in catalog.careers if career.name == "Ingeniería Civil"
    }
    assert civil_ids == {"BO-UCB-LP-ING-CIVIL", "BO-UMSA-LP-ING-CIVIL"}
    assert {
        "BO-UCB-LP",
        "BO-UNIFRANZ-LP",
        "BO-UPB-LP",
        "BO-EMI-LP",
    } <= {university.university_id for university in catalog.universities}
    assert {
        "BO-UNIFRANZ-LP-ING-SISTEMAS-INNOVACION-DIGITAL",
        "BO-UPB-LP-ING-SISTEMAS-COMPUTACIONALES",
        "BO-EMI-LP-ING-SISTEMAS",
    } <= {career.career_id for career in catalog.careers}
    assert sum(career.recommendation_eligible for career in catalog.careers) == 7


def test_missing_entry_profile_is_explicit_not_invented() -> None:
    catalog = load_career_catalog()
    assert all(career.entry_profile is None for career in catalog.careers)
    eligible = [career for career in catalog.careers if career.recommendation_eligible]
    external = [career for career in catalog.careers if not career.recommendation_eligible]
    assert all(
        career.entry_profile_status == "NOT_PUBLISHED_IN_REVIEWED_SOURCES" for career in eligible
    )
    assert len(external) == 1
    assert external[0].entry_profile_status == "SOURCE_UNSNAPSHOTTED"


def test_external_official_information_is_never_recommendation_evidence() -> None:
    registry = load_external_information_registry()
    assert len(registry.entries) == 1
    entry = registry.entries[0]
    assert entry.evidence_layer == "FUENTE_OFICIAL_EXTERNA"
    assert entry.official is True
    assert entry.recommendation_eligible is False
    assert entry.snapshot_status == "UNAVAILABLE_AT_RETRIEVAL"


def test_source_quality_metrics_keep_external_information_separate() -> None:
    metrics = calculate_source_quality_metrics()
    assert metrics["source_count"] == 16
    assert metrics["recommendation_eligible_source_count"] == 16
    assert metrics["external_information_count"] == 1
    assert metrics["evidence_layer_distribution"] == {
        "CORPUS_VALIDADO": 16,
        "FUENTE_OFICIAL_EXTERNA": 1,
    }
