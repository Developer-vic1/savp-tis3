from app.knowledge.registry import (
    load_career_catalog,
    load_source_manifest,
    validate_local_source_hashes,
)


def test_source_manifest_is_official_unique_and_matches_local_hashes() -> None:
    manifest = load_source_manifest()
    assert len(manifest.sources) == 12
    assert all(source.official for source in manifest.sources)
    assert validate_local_source_hashes() == []


def test_initial_catalog_has_real_institution_specific_careers() -> None:
    catalog = load_career_catalog()
    assert len(catalog.universities) == 2
    assert len(catalog.careers) == 5
    civil_ids = {
        career.career_id
        for career in catalog.careers
        if career.name == "Ingeniería Civil"
    }
    assert civil_ids == {"BO-UCB-LP-ING-CIVIL", "BO-UMSA-LP-ING-CIVIL"}
    assert all(career.valid_from for career in catalog.careers)
    assert all(career.initial_subjects for career in catalog.careers)


def test_missing_entry_profile_is_explicit_not_invented() -> None:
    catalog = load_career_catalog()
    assert all(career.entry_profile is None for career in catalog.careers)
    assert all(
        career.entry_profile_status == "NOT_PUBLISHED_IN_REVIEWED_SOURCES"
        for career in catalog.careers
    )
