from app.knowledge.registry import (
    calculate_source_quality_metrics,
    load_career_catalog,
    load_external_information_registry,
    load_source_manifest,
    validate_local_source_hashes,
)


def test_source_manifest_is_official_unique_and_matches_local_hashes() -> None:
    manifest = load_source_manifest()
    assert len(manifest.sources) == 17
    assert all(source.official for source in manifest.sources)
    assert all(source.evidence_layer == "CORPUS_VALIDADO" for source in manifest.sources)
    assert validate_local_source_hashes() == []


def test_initial_catalog_has_real_institution_specific_careers() -> None:
    catalog = load_career_catalog()
    assert len(catalog.universities) == 6
    assert len(catalog.careers) == 12
    civil_ids = {
        career.career_id for career in catalog.careers if career.name == "Ingeniería Civil"
    }
    assert civil_ids == {"BO-UCB-LP-ING-CIVIL", "BO-UMSA-LP-ING-CIVIL", "BO-UPEA-EA-ING-CIVIL"}
    assert {
        "BO-UCB-LP",
        "BO-UNIFRANZ-LP",
        "BO-UPB-LP",
        "BO-EMI-LP",
        "BO-UPEA-EA",
    } <= {university.university_id for university in catalog.universities}
    assert {
        "BO-UNIFRANZ-LP-ING-SISTEMAS-INNOVACION-DIGITAL",
        "BO-UPB-LP-ING-SISTEMAS-COMPUTACIONALES",
        "BO-EMI-LP-ING-SISTEMAS",
        "BO-UPEA-EA-ING-SISTEMAS",
        "BO-UPEA-EA-ING-CIVIL",
        "BO-UPEA-EA-PSICOLOGIA",
    } <= {career.career_id for career in catalog.careers}
    assert sum(career.recommendation_eligible for career in catalog.careers) == 8


def test_missing_entry_profile_is_explicit_not_invented() -> None:
    catalog = load_career_catalog()
    assert all(career.entry_profile is None for career in catalog.careers)
    eligible = [career for career in catalog.careers if career.recommendation_eligible]
    external = [career for career in catalog.careers if not career.recommendation_eligible]
    assert all(
        career.entry_profile_status == "NOT_PUBLISHED_IN_REVIEWED_SOURCES" for career in eligible
    )
    assert len(external) == 4
    assert all(career.entry_profile_status == "SOURCE_UNSNAPSHOTTED" for career in external)


def test_external_official_information_is_never_recommendation_evidence() -> None:
    registry = load_external_information_registry()
    assert len(registry.entries) == 4
    assert all(entry.evidence_layer == "FUENTE_OFICIAL_EXTERNA" for entry in registry.entries)
    assert all(entry.official is True and entry.recommendation_eligible is False for entry in registry.entries)
    assert all(entry.snapshot_status == "PENDING_LOCAL_VALIDATION" for entry in registry.entries)
    assert registry.entries[0].url == "https://www.emi.edu.bo/universidad/licenciatura/ingenieria-de-sistemas"


def test_source_quality_metrics_keep_external_information_separate() -> None:
    metrics = calculate_source_quality_metrics()
    assert metrics["source_count"] == 17
    assert metrics["recommendation_eligible_source_count"] == 17
    assert metrics["external_information_count"] == 4
    assert metrics["evidence_layer_distribution"] == {
        "CORPUS_VALIDADO": 17,
        "FUENTE_OFICIAL_EXTERNA": 4,
    }
