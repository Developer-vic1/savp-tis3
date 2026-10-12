from types import SimpleNamespace

import pytest

from app.knowledge import actualizacion_fuentes as updates


@pytest.fixture
def source(monkeypatch):
    record = SimpleNamespace(
        source_id="BO-UPB-SIS",
        url="https://www.upb.edu/carrera",
        official=True,
        verification_status="VALID",
    )
    monkeypatch.setattr(updates, "load_source_manifest", lambda: SimpleNamespace(sources=[record]))
    return record


def test_only_registered_sources_can_be_checked(source, monkeypatch):
    monkeypatch.setattr(updates, "_fetch_document", lambda *args: pytest.fail("No network"))
    with pytest.raises(ValueError):
        updates.check_registered_source("inventada")
    source.url = "https://127.0.0.1/private"
    assert updates.check_registered_source(source.source_id)["status"] == "BLOQUEADA"


def test_unverified_and_ministerial_sources_do_not_contact_network(source, monkeypatch):
    monkeypatch.setattr(updates, "_fetch_document", lambda *args: pytest.fail("No network"))
    source.verification_status = "PENDING"
    assert updates.check_registered_source(source.source_id)["status"] == "BLOQUEADA"
    source.verification_status = "VALID"
    source.url = "https://www.minedu.gob.bo/curriculo.pdf"
    assert updates.check_registered_source(source.source_id)["status"] == "BLOQUEADA"


def test_html_change_ignores_navigation_but_detects_subject_changes(source, monkeypatch):
    def fetch(html):
        html = html.replace("<html>", "<html><title>Malla Sistemas UPB</title>")
        monkeypatch.setattr(
            updates, "_fetch_document", lambda *args: (source.url, 200, "text/html", html.encode())
        )
        return updates.check_registered_source(source.source_id)

    subjects = "Álgebra lineal y programación. " * 8
    first = fetch(f"<html><nav>Anterior</nav><main>{subjects}</main></html>")
    same = fetch(f"<html><nav>Nuevo</nav><main>{subjects}</main></html>")
    changed = fetch(f"<html><main>{subjects}Estadística</main></html>")
    assert first["status"] == "VERIFICADA"
    assert same["fingerprint"] == first["fingerprint"]
    assert changed["fingerprint"] != first["fingerprint"]


def test_changed_curriculum_link_and_campus_indicator_are_detected(source, monkeypatch):
    def fingerprint(pdf, css):
        html = (
            "<title>Sistemas</title><main>"
            + "Plan de estudios. " * 10
            + f'<a href="/{pdf}.pdf">Malla curricular</a>'
            + f'<span class="{css}">LPZ</span></main>'
        )
        monkeypatch.setattr(
            updates, "_fetch_document", lambda *args: (source.url, 200, "text/html", html.encode())
        )
        return updates.check_registered_source(source.source_id)["fingerprint"]

    initial = fingerprint("malla2026", "opacity-50")
    assert initial != fingerprint("malla2027", "opacity-50")
    assert initial != fingerprint("malla2026", "opacity-100 border-b-2")


def test_login_and_changed_destination_are_not_a_verified_revision(source, monkeypatch):
    html = b'<title>Login</title><input type="password"><main>Formulario acceso</main>'
    monkeypatch.setattr(
        updates, "_fetch_document", lambda *args: (source.url, 200, "text/html", html)
    )
    assert updates.check_registered_source(source.source_id)["status"] == "BLOQUEADA"
    monkeypatch.setattr(
        updates, "_fetch_document", lambda *args: ("https://www.upb.edu/", 200, "text/html", html)
    )
    assert updates.check_registered_source(source.source_id)["fingerprint"] is None


def test_redirect_block_preserves_reference_and_reading_slots(source, monkeypatch):
    def blocked(*args):
        raise updates.PreviewFailure("Destino privado")

    monkeypatch.setattr(updates, "_fetch_document", blocked)
    assert updates.check_registered_source(source.source_id)["fingerprint"] is None
    assert updates._FETCH_SLOTS.acquire(blocking=False)
    updates._FETCH_SLOTS.release()


def test_timeout_and_wrong_content_preserve_no_fingerprint(source, monkeypatch):
    def timeout(*args):
        raise TimeoutError()

    monkeypatch.setattr(updates, "_fetch_document", timeout)
    assert updates.check_registered_source(source.source_id)["fingerprint"] is None
    monkeypatch.setattr(
        updates,
        "_fetch_document",
        lambda *args: (source.url, 200, "application/pdf", b"<html>error</html>"),
    )
    assert updates.check_registered_source(source.source_id)["status"] == "NO_DISPONIBLE"


def test_pdf_bytes_detect_a_revision(source, monkeypatch):
    monkeypatch.setattr(
        updates,
        "_fetch_document",
        lambda *args: (source.url, 200, "application/pdf", b"%PDF-1.7 contenido"),
    )
    result = updates.check_registered_source(source.source_id)
    assert result["status"] == "VERIFICADA"
    assert len(result["fingerprint"]) == 64
