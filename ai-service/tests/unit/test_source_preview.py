import hashlib
import socket
import time
from types import SimpleNamespace

import pytest

from app.knowledge import source_preview as preview

URL = "https://www.upb.edu/documento-nuevo"


@pytest.fixture(autouse=True)
def isolated_registry(monkeypatch: pytest.MonkeyPatch) -> None:
    monkeypatch.setattr(preview, "load_source_manifest", lambda: SimpleNamespace(sources=[]))
    monkeypatch.setattr(preview, "_load_intake_registry", lambda: SimpleNamespace(proposals=[]))


@pytest.mark.parametrize(
    "url",
    [
        "https://www.berkeley.edu/",
        "https://127.0.0.1/",
        "https://upb.edu.evil.bo/",
        "https://unknown.edu.bo/",
        "https://user:pass@upb.edu/",
    ],
)
def test_untrusted_urls_never_download(monkeypatch: pytest.MonkeyPatch, url: str) -> None:
    def forbidden(value: str) -> None:
        pytest.fail("No debe consultar URLs ajenas o privadas.")

    monkeypatch.setattr(preview, "_fetch_document", forbidden)
    result = preview.inspect_source_url(url)
    assert result.can_use is False
    assert result.reachable is None


@pytest.mark.parametrize(
    "address",
    [
        "127.0.0.1",
        "10.0.0.1",
        "169.254.169.254",
        "::1",
        "192.0.2.1",
        "::ffff:127.0.0.1",
        "224.0.0.1",
        "2002:7f00:1::",
        "64:ff9b::a00:1",
    ],
)
def test_dns_private_reserved_or_mixed_results_are_blocked(
    monkeypatch: pytest.MonkeyPatch, address: str
) -> None:
    monkeypatch.setattr(
        socket,
        "getaddrinfo",
        lambda *args: [(2, 1, 6, "", ("8.8.8.8", 443)), (2, 1, 6, "", (address, 443))],
    )
    with pytest.raises(preview.PreviewFailure, match="privado o reservado"):
        preview._public_address("www.upb.edu", time.monotonic() + 3)


def test_preview_extracts_plain_text_title_date_and_never_returns_executable_markup(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    html = (
        b'<title>Malla curricular</title><meta property="article:published_time" '
        b'content="2026-03-15T08:00:00"><main><h1>Malla curricular oficial</h1>'
        b"<p>Plan de estudios y perfil profesional.</p><script>alert(1)</script>"
        b'<iframe src="http://localhost"></iframe></main>'
    )
    monkeypatch.setattr(preview, "_fetch_document", lambda url: (url, 200, "text/html", html))
    result = preview.inspect_source_url(URL)
    assert result.can_use is True
    assert result.title == "Malla curricular oficial"
    assert result.suggested_fields["publication_date"] == "2026-03-15"
    assert "alert" not in result.excerpt and "iframe" not in result.excerpt
    assert "campus" not in result.suggested_fields
    assert "version" not in result.suggested_fields


@pytest.mark.parametrize("status", [403, 404, 410, 429, 500])
def test_http_errors_do_not_become_verified_documents(
    monkeypatch: pytest.MonkeyPatch, status: int
) -> None:
    monkeypatch.setattr(preview, "_fetch_document", lambda url: (url, status, "text/html", b""))
    result = preview.inspect_source_url(URL)
    assert result.can_use is False
    assert result.http_status == status
    assert result.reachable is (False if status in {404, 410} else None)


def test_offline_network_failure_preserves_unknown_existence(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    def disconnected(url: str) -> None:
        raise OSError("Network unavailable")

    monkeypatch.setattr(preview, "_fetch_document", disconnected)
    result = preview.inspect_source_url(URL)
    assert result.status == "NO_DISPONIBLE"
    assert result.reachable is None
    assert result.can_use is False


@pytest.mark.parametrize(
    "html",
    [b"<h1>404 Not Found</h1>", b"<title>Just a moment...</title>", b"<main>Sin titulo</main>"],
)
def test_http_200_does_not_prove_usable_content(
    monkeypatch: pytest.MonkeyPatch, html: bytes
) -> None:
    monkeypatch.setattr(preview, "_fetch_document", lambda url: (url, 200, "text/html", html))
    assert preview.inspect_source_url(URL).can_use is False


def test_duplicate_proposal_is_reported_without_fetching(monkeypatch: pytest.MonkeyPatch) -> None:
    proposal = SimpleNamespace(
        url=URL,
        proposal_id="KGI-ABCDEF123456",
        title="Malla oficial",
        status="APROBADA_PENDIENTE_INGESTA",
    )
    monkeypatch.setattr(
        preview, "_load_intake_registry", lambda: SimpleNamespace(proposals=[proposal])
    )
    result = preview.inspect_source_url(URL + "#seccion")
    assert result.status == "DUPLICADA"
    assert result.duplicate["kind"] == "PROPUESTA"
    assert result.reachable is None


def test_same_document_hash_on_another_url_is_duplicate(monkeypatch: pytest.MonkeyPatch) -> None:
    html = b"<title>Documento oficial</title><main>Plan de estudios</main>"
    source = SimpleNamespace(
        url="https://www.upb.edu/otro",
        document_hash="sha256:" + hashlib.sha256(html).hexdigest(),
        source_id="BO-EXISTENTE",
        title="Documento oficial",
    )
    monkeypatch.setattr(preview, "load_source_manifest", lambda: SimpleNamespace(sources=[source]))
    monkeypatch.setattr(preview, "_fetch_document", lambda url: (url, 200, "text/html", html))
    result = preview.inspect_source_url(URL)
    assert result.status == "DUPLICADA"
    assert result.duplicate["kind"] == "CORPUS"


@pytest.mark.parametrize(
    "destination",
    [
        "https://127.0.0.1/",
        "https://www.berkeley.edu/",
        "http://www.upb.edu/",
        "https://www.umsa.bo/",
    ],
)
def test_redirects_cannot_leave_safe_institution(
    monkeypatch: pytest.MonkeyPatch, destination: str
) -> None:
    calls = []

    class Connection:
        sock = None

        def __init__(self, host, address, timeout):
            calls.append((host, address))

        def request(self, method, target, headers):
            assert not any(
                name.lower() in {"cookie", "authorization", "x-savp-ai-key"} for name in headers
            )

        def getresponse(self):
            return SimpleNamespace(status=302, getheader=lambda name: destination)

        def close(self):
            pass

    monkeypatch.setattr(preview, "_public_address", lambda host, deadline: "8.8.8.8")
    monkeypatch.setattr(preview, "PinnedHTTPSConnection", Connection)
    with pytest.raises(preview.PreviewFailure, match="redirección"):
        preview._fetch_document(URL)
    assert calls == [("www.upb.edu", "8.8.8.8")]


@pytest.mark.parametrize(
    "headers",
    [
        {"Content-Length": str(preview.MAX_BYTES + 1)},
        {"Content-Type": "application/zip"},
        {"Content-Encoding": "gzip"},
    ],
)
def test_unsafe_format_size_or_compression_is_rejected_before_reading(
    monkeypatch: pytest.MonkeyPatch, headers: dict[str, str]
) -> None:
    class Connection:
        sock = None

        def __init__(self, *args):
            pass

        def request(self, *args, **kwargs):
            pass

        def getresponse(self):
            values = {"Content-Type": "text/html"} | headers
            return SimpleNamespace(status=200, getheader=values.get)

        def close(self):
            pass

    monkeypatch.setattr(preview, "_public_address", lambda *args: "8.8.8.8")
    monkeypatch.setattr(preview, "PinnedHTTPSConnection", Connection)
    with pytest.raises(preview.PreviewFailure):
        preview._fetch_document(URL)
