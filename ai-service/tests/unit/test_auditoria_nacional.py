from __future__ import annotations

import importlib.util
import json
from pathlib import Path

from app.knowledge.ingesta_nacional import CrawlLimits, NationalCrawler

SCRIPT = Path(__file__).resolve().parents[2] / "scripts/auditar_catalogo_nacional.py"
SPEC = importlib.util.spec_from_file_location("auditoria_nacional", SCRIPT)
assert SPEC and SPEC.loader
audit_module = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(audit_module)
audit_catalog = audit_module.audit_catalog

URL = "https://universidad.edu.bo/carreras/ingenieria"
CATALOG = {
    "institutions": [
        {
            "institution_id": "BO-TEST",
            "name": "Universidad prueba",
            "official_domains": ["universidad.edu.bo"],
            "seed_urls": [URL],
            "website_verification_status": "OFFICIAL_DOMAIN_VERIFIED",
        }
    ]
}
HTML = (
    "<title>Ingeniería de Sistemas</title><main><h1>Ingeniería de Sistemas</h1>"
    "<h2>Plan de estudios</h2><p>Duración: 10 semestres</p>"
    "<p>Costo mensual Bs 450</p></main>"
).encode()


def stage(tmp_path: Path) -> Path:
    def fetch(url, *_args):
        return url, 200, "text/html", HTML

    NationalCrawler(CATALOG, tmp_path, CrawlLimits(pause_seconds=0), fetcher=fetch).run()
    return next(tmp_path.rglob("manifest.json"))


def test_audit_is_reproducible_read_only_and_never_training_ready(tmp_path: Path) -> None:
    manifest = stage(tmp_path)
    before = {path: path.read_bytes() for path in manifest.parent.iterdir()}
    report = audit_catalog(tmp_path, CATALOG)
    repeat = audit_catalog(tmp_path, CATALOG)
    assert report["inventory_hash"] == repeat["inventory_hash"]
    assert report["summary"]["critical"] == 0
    assert report["summary"]["training_eligible_documents"] == 0
    assert {path: path.read_bytes() for path in manifest.parent.iterdir()} == before
    assert report["summary"]["finding_counts"]["FEE_YEAR_MISSING"] == 1
    assert report["summary"]["finding_counts"]["FEE_CAMPUS_MISSING"] == 1


def test_tampered_snapshot_and_chunk_references_are_critical(tmp_path: Path) -> None:
    manifest_path = stage(tmp_path)
    manifest = json.loads(manifest_path.read_text(encoding="utf-8"))
    (manifest_path.parent / manifest["snapshot"]).write_bytes(b"tampered")
    chunk_path = manifest_path.parent / "chunks.jsonl"
    chunks = [json.loads(line) for line in chunk_path.read_text(encoding="utf-8").splitlines()]
    chunks[0]["source_url"] = "https://evil.test/"
    chunk_path.write_text("".join(json.dumps(chunk) + "\n" for chunk in chunks), encoding="utf-8")
    report = audit_catalog(tmp_path, CATALOG)
    codes = report["summary"]["finding_counts"]
    assert codes["SNAPSHOT_HASH_MISMATCH"] == 1
    assert codes["CHUNK_REFERENCE_MISMATCH"] == 1
    assert codes["CHUNK_ID_MISMATCH"] >= 1


def test_candidate_evidence_must_exist_in_saved_chunks(tmp_path: Path) -> None:
    manifest = stage(tmp_path)
    path = manifest.parent / "candidatos.json"
    facts = json.loads(path.read_text(encoding="utf-8"))
    facts[0]["evidencia"] = "Carrera inventada sin evidencia"
    facts[0]["source_url"] = "https://evil.test/"
    path.write_text(json.dumps(facts), encoding="utf-8")
    report = audit_catalog(tmp_path, CATALOG)
    codes = report["summary"]["finding_counts"]
    assert codes["EVIDENCE_NOT_IN_CHUNKS"] == 1
    assert codes["CANDIDATE_REFERENCE_MISMATCH"] == 1


def test_quote_across_real_chunk_overlap_is_supported_without_changing_artifacts():
    from app.ingestion.chunking import _split_text

    quote = "El perfil profesional reúne habilidades para estudiar medicina nuclear."
    text = "Contexto académico previo. " * 44 + quote + " Más información. " * 40
    chunks = [{"text": value, "page": 1, "section": "Perfil"}
              for value in _split_text(text, 1200, 120)]
    reconstructed = audit_module.evidence_texts(chunks)
    assert audit_module.normalized(text) in reconstructed
    assert any(audit_module.normalized(quote) in value for value in reconstructed)


def test_quote_cannot_join_different_pages_or_unrelated_sections():
    shared = "Un contexto largo suficientemente común "
    left = "Inicio. " + shared
    right = shared + "Final."
    quote = audit_module.normalized("Inicio. " + shared + "Final.")
    for page, section in ((2, "Perfil"), (1, "Malla")):
        chunks = [{"text": left, "page": 1, "section": "Perfil"},
                  {"text": right, "page": page, "section": section}]
        assert not any(quote in value for value in audit_module.evidence_texts(chunks))


def test_quarantined_site_blocks_even_hash_correct_artifact(tmp_path: Path) -> None:
    stage(tmp_path)
    catalog = json.loads(json.dumps(CATALOG))
    catalog["institutions"][0]["ingestion_status"] = "QUARANTINE_SITE_REVIEW_REQUIRED"
    report = audit_catalog(tmp_path, catalog)
    assert report["summary"]["finding_counts"]["INSTITUTION_QUARANTINED"] == 1


def test_unsafe_snapshot_reference_is_never_read(tmp_path: Path) -> None:
    path = stage(tmp_path)
    manifest = json.loads(path.read_text(encoding="utf-8"))
    manifest["snapshot"] = "../../secret.html"
    path.write_text(json.dumps(manifest), encoding="utf-8")
    report = audit_catalog(tmp_path, CATALOG)
    assert report["summary"]["finding_counts"]["SNAPSHOT_PATH_UNSAFE"] == 1


def test_scholarship_administrative_mention_is_not_a_distinct_benefit(tmp_path: Path) -> None:
    html = (
        "<title>Universidad</title><main><h1>Dirección de becas</h1>"
        "<p>Personal de la Dirección de Relaciones Convenios y Becas</p></main>"
    ).encode()

    def fetch(url, *_args):
        return url, 200, "text/html", html

    NationalCrawler(CATALOG, tmp_path, CrawlLimits(pause_seconds=0), fetcher=fetch).run()
    report = audit_catalog(tmp_path, CATALOG)
    assert report["summary"]["finding_counts"]["SCHOLARSHIP_CONTEXT_ONLY"] >= 1
