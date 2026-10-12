from __future__ import annotations

import json
from pathlib import Path
from types import SimpleNamespace

import pytest

from app.knowledge.ingesta_nacional import (
    CrawlLimits,
    NationalCrawler,
    atomic_json,
    canonical_url,
    fetch_confirmed_document,
)
from app.knowledge.source_preview import PreviewFailure

HOME = "https://universidad.edu.bo/"
CAREER = HOME + "carreras/ingenieria"
BODY = (
    "<html><title>Ingeniería de Sistemas</title><main>"
    "<h1>Ingeniería de Sistemas</h1><h2>Plan de estudios</h2>"
    "<p>Duración: 10 semestres</p><p>Sede: Cochabamba</p>"
    "<p>Cuota mensual Bs 450</p><p>Beca académica disponible según convocatoria</p>"
    "<p>Las asignaturas se distribuyen por semestre.</p></main></html>"
).encode()


def catalog(status: str = "CURRENT_VERIFIED") -> dict:
    return {
        "institutions": [
            {
                "institution_id": "BO-TEST",
                "name": "Universidad prueba",
                "official_domains": ["universidad.edu.bo"],
                "official_url": HOME,
                "seed_urls": [HOME],
                "verification_status": status,
            }
        ]
    }


def limits(**kwargs) -> CrawlLimits:
    return CrawlLimits(pause_seconds=0, **kwargs)


def test_resume_hash_dedup_evidence_and_runtime_unchanged(tmp_path: Path) -> None:
    calls = []
    home = (
        "<html><title>Universidad</title><nav>"
        '<a href="/carreras/ingenieria">Ingeniería de Sistemas</a>'
        '<a href="/carreras/duplicada">Malla duplicada</a>'
        '<a href="https://externa.bo/carreras/medicina">Medicina</a>'
        "</nav><main><h1>Universidad</h1><p>Oferta de pregrado</p></main></html>"
    ).encode()

    def fetch(url, _domains, _limits):
        calls.append(url)
        return url, 200, "text/html", home if url == HOME else BODY

    runtime = tmp_path / "runtime"
    runtime.mkdir()
    (runtime / "corpus.jsonl").write_text("evidencia aprobada", encoding="utf-8")
    output = tmp_path / "nacional"
    first = NationalCrawler(catalog(), output, limits(run_documents=1), fetcher=fetch).run()
    assert first["institutions"][0]["pending"] == 2
    assert json.loads(next(output.rglob("candidatos.json")).read_text(encoding="utf-8")) == []
    second = NationalCrawler(catalog(), output, limits(), fetcher=fetch).run()
    row = second["institutions"][0]
    assert row["valid"] == 2 and row["duplicates"] == 1
    assert calls == [HOME, CAREER, HOME + "carreras/duplicada"]
    facts = [
        fact
        for path in output.rglob("candidatos.json")
        for fact in json.loads(path.read_text(encoding="utf-8"))
    ]
    kinds = {fact["tipo"] for fact in facts}
    assert {
        "CARRERA",
        "DURACION",
        "SEDE",
        "MALLA_O_PLAN",
        "COSTO_PUBLICADO",
        "BECA_PUBLICADA",
    } <= kinds
    assert all(fact["source_url"] == CAREER for fact in facts)
    assert all(fact["evidencia"] and fact["section"] for fact in facts)
    NationalCrawler(catalog(), output, limits(), fetcher=fetch).run()
    assert len(calls) == 3
    assert (runtime / "corpus.jsonl").read_text(encoding="utf-8") == "evidencia aprobada"


@pytest.mark.parametrize(
    "url",
    [
        "https://universidad.edu.bo.evil.test/carreras/",
        "https://127.0.0.1/",
        "https://usuario:secreto@universidad.edu.bo/",
        "http://universidad.edu.bo/",
        "https://universidad.edu.bo:8443/",
        "https://universidad.edu.bo/%61dmin/carreras",
        "https://universidad.edu.bo/carreras?session=secreto",
        "https://intranet.universidad.edu.bo/",
    ],
)
def test_reject_unsafe_urls_before_network(url: str) -> None:
    assert canonical_url(url, ["universidad.edu.bo"]) is None


def test_normalize_tracking_and_fragment() -> None:
    assert canonical_url(CAREER + "?utm_source=ads#plan", ["universidad.edu.bo"]) == CAREER


def test_unconfirmed_institution_does_not_fetch(tmp_path: Path) -> None:
    def forbidden(*_args):
        pytest.fail("No debe descargar instituciones pendientes de verificación.")

    report = NationalCrawler(
        catalog("HISTORICAL_REGISTER_PENDING_CURRENT_VERIFICATION"),
        tmp_path,
        limits(),
        fetcher=forbidden,
    ).run()
    assert report["institutions"][0]["status"] == "PENDIENTE_VERIFICACION_DOMINIO"
    assert report["documents_this_run"] == 0


@pytest.mark.parametrize("quarantine", [True, False])
def test_quarantine_or_explicit_empty_seeds_never_fall_back(tmp_path, quarantine):
    source = catalog()
    if quarantine:
        source["institutions"][0]["ingestion_status"] = "QUARANTINE_SITE_REVIEW_REQUIRED"
    else:
        source["institutions"][0]["seed_urls"] = []

    def forbidden(*_args):
        pytest.fail("No se debe descargar una institución bloqueada o sin semillas.")

    report = NationalCrawler(source, tmp_path, limits(), fetcher=forbidden).run()
    assert report["documents_this_run"] == 0


def test_atomic_save_retries_transient_windows_lock(monkeypatch, tmp_path):
    from app.knowledge import ingesta_nacional

    replace = ingesta_nacional.os.replace
    attempts = []

    def locked_once(source, destination):
        attempts.append(source)
        if len(attempts) == 1:
            raise PermissionError("Archivo bloqueado por lector")
        replace(source, destination)

    monkeypatch.setattr(ingesta_nacional.os, "replace", locked_once)
    monkeypatch.setattr(ingesta_nacional.time, "sleep", lambda *_: None)
    target = tmp_path / "estado.json"
    target.write_text('{"old": true}')
    atomic_json(target, {"new": True})
    assert json.loads(target.read_text()) == {"new": True}
    assert len(attempts) == 2


def test_verified_website_does_not_assert_institution_legal_current_status(tmp_path: Path) -> None:
    source = catalog("HISTORICAL_REGISTER_PENDING_CURRENT_VERIFICATION")
    source["institutions"][0]["website_verification_status"] = "OFFICIAL_DOMAIN_VERIFIED"

    def fetch(url, *_args):
        return url, 200, "text/html", BODY

    report = NationalCrawler(source, tmp_path, limits(), fetcher=fetch).run()
    assert report["institutions"][0]["valid"] == 1
    state = json.loads((tmp_path / "estado.json").read_text(encoding="utf-8"))
    assert state["institutions"]["BO-TEST"]["institution_verification_status"] == (
        "HISTORICAL_REGISTER_PENDING_CURRENT_VERIFICATION"
    )


def test_failed_download_retry_is_bounded_and_resumable(tmp_path: Path) -> None:
    calls = []

    def unavailable(url, *_args):
        calls.append(url)
        raise TimeoutError("Tiempo agotado")

    NationalCrawler(catalog(), tmp_path, limits(run_documents=1), fetcher=unavailable).run()
    report = NationalCrawler(catalog(), tmp_path, limits(), fetcher=unavailable).run()
    assert calls == [HOME, HOME]
    assert report["institutions"][0]["pending"] == 0
    assert "Tiempo agotado" in report["institutions"][0]["errors"][0]["message"]
    NationalCrawler(catalog(), tmp_path, limits(), fetcher=unavailable).run()
    assert len(calls) == 2


def test_dns_private_address_stops_transport(monkeypatch) -> None:
    import app.knowledge.ingesta_nacional as national
    import app.knowledge.source_preview as preview

    monkeypatch.setattr(
        preview.socket, "getaddrinfo", lambda *_args: [(2, 1, 6, "", ("10.0.0.12", 443))]
    )

    def connection(*_args):
        pytest.fail("No debe abrir conexión a IP privada.")

    monkeypatch.setattr(national, "PinnedHTTPSConnection", connection)
    with pytest.raises(PreviewFailure, match="privado"):
        fetch_confirmed_document(HOME, ["universidad.edu.bo"], limits())


def test_redirect_off_domain_stops_before_second_dns(monkeypatch) -> None:
    import app.knowledge.ingesta_nacional as national

    hosts = []
    monkeypatch.setattr(
        national, "_public_address", lambda host, _deadline: hosts.append(host) or "8.8.8.8"
    )
    response = SimpleNamespace(
        status=302,
        getheader=lambda key: "https://evil.test/carreras/" if key == "Location" else None,
    )

    class Connection:
        sock = None

        def __init__(self, *_args):
            pass

        def request(self, *_args, **_kwargs):
            pass

        def getresponse(self):
            return response

        def close(self):
            pass

    monkeypatch.setattr(national, "PinnedHTTPSConnection", Connection)
    with pytest.raises(PreviewFailure, match="fuera"):
        fetch_confirmed_document(HOME, ["universidad.edu.bo"], limits())
    assert hosts == ["universidad.edu.bo"]


def test_budget_keeps_pending_queue(tmp_path: Path) -> None:
    home = (
        "<title>Universidad</title><main><h1>Oferta académica</h1>"
        '<a href="/carreras/ingenieria">Carreras</a></main>'
    ).encode()

    def fetch(url, *_args):
        return url, 200, "text/html", home if url == HOME else BODY

    report = NationalCrawler(
        catalog(), tmp_path, limits(max_documents_per_institution=1), fetcher=fetch
    ).run()
    assert report["documents_this_run"] == 1
    assert report["institutions"][0]["pending"] == 1


def test_resume_expands_saved_links_when_depth_limit_increases(tmp_path: Path) -> None:
    calls = []
    home = (
        b"<title>Oferta</title><main><h1>Universidad</h1>"
        b'<a href="/carreras/ingenieria">Carreras</a></main>'
    )

    def fetch(url, *_args):
        calls.append(url)
        return url, 200, "text/html", home if url == HOME else BODY

    NationalCrawler(catalog(), tmp_path, limits(max_depth=0), fetcher=fetch).run()
    assert calls == [HOME]
    NationalCrawler(catalog(), tmp_path, limits(max_depth=1), fetcher=fetch).run()
    assert calls == [HOME, CAREER]


def test_definitive_census_id_reuses_downloads(tmp_path: Path) -> None:
    calls = []

    def fetch(url, *_args):
        calls.append(url)
        return url, 200, "text/html", BODY

    NationalCrawler(catalog(), tmp_path, limits(), fetcher=fetch).run()
    updated = catalog()
    updated["institutions"][0]["institution_id"] = "BO-ID-DEFINITIVO"
    report = NationalCrawler(updated, tmp_path, limits(), fetcher=fetch).run()
    assert calls == [HOME]
    assert len(report["institutions"]) == 1
    assert report["institutions"][0]["institution_id"] == "BO-ID-DEFINITIVO"


def test_unconfirmed_census_does_not_count_old_fallback_twice(tmp_path: Path) -> None:
    def fetch(url, *_args):
        return url, 200, "text/html", BODY

    NationalCrawler(catalog(), tmp_path, limits(), fetcher=fetch).run()
    updated = catalog("HISTORICAL_REGISTER_PENDING_CURRENT_VERIFICATION")
    updated["institutions"][0]["institution_id"] = "BO-ID-DEFINITIVO"
    updated["institutions"][0]["official_domains"] = []
    updated["institutions"][0]["seed_urls"] = []
    report = NationalCrawler(updated, tmp_path, limits(), fetcher=fetch).run()
    assert len(report["institutions"]) == 1
    assert report["institutions"][0]["status"] == "PENDIENTE_VERIFICACION_DOMINIO"


def test_html_navigation_never_becomes_offer_or_fee(tmp_path: Path) -> None:
    html = (
        "<title>Universidad</title><nav><h1>Ingeniería</h1>"
        "<p>Duración: 10 semestres. Cuota Bs 500</p></nav>"
        "<main><h1>Noticias</h1><p>Conoce nuestra universidad.</p></main>"
    ).encode()

    def fetch(url, *_args):
        return url, 200, "text/html", html

    NationalCrawler(catalog(), tmp_path, limits(), fetcher=fetch).run()
    facts = json.loads(next(tmp_path.rglob("candidatos.json")).read_text(encoding="utf-8"))
    assert facts == []


def test_professional_profile_does_not_assert_curriculum(tmp_path: Path) -> None:
    html = (
        "<title>Ingeniería de Sistemas</title><main><h1>Ingeniería de Sistemas</h1>"
        "<h2>Perfil profesional</h2><p>El graduado desarrolla sistemas informáticos.</p>"
        "</main>"
    ).encode()

    def fetch(url, *_args):
        return url, 200, "text/html", html

    NationalCrawler(catalog(), tmp_path, limits(), fetcher=fetch).run()
    facts = json.loads(next(tmp_path.rglob("candidatos.json")).read_text(encoding="utf-8"))
    kinds = {fact["tipo"] for fact in facts}
    assert "PERFIL_PROFESIONAL" in kinds and "MALLA_O_PLAN" not in kinds


def test_scanned_pdf_no_ocr_or_inference(tmp_path: Path, monkeypatch) -> None:
    import pymupdf

    import app.knowledge.ingesta_nacional as national

    document = pymupdf.open()
    document.new_page()
    pdf = document.tobytes()
    document.close()

    def fetch(url, *_args):
        return url, 200, "application/pdf", pdf

    monkeypatch.setattr(national, "candidate_facts", lambda *_args: [])
    report = NationalCrawler(catalog(), tmp_path, limits(), fetcher=fetch).run()
    assert report["institutions"][0]["valid"] == 0
    assert report["institutions"][0]["errors"][0]["message"] == "SIN_TEXTO"
    manifest = json.loads(next(tmp_path.rglob("manifest.json")).read_text(encoding="utf-8"))
    assert manifest["chunks"] == 0 and "OCR automático deshabilitado" in manifest["warnings"][0]
