import hashlib
import socket
import time
from types import SimpleNamespace

import pytest

from app.knowledge import source_preview as preview

URL = "https://www.upb.edu/documento-nuevo"
PORTAL_URL = "https://academico.udabol.edu.bo/estudiante/solicitud/solicitud_info/mostrar_interface"


@pytest.mark.parametrize(
    "limits", [{"deadline_seconds": 31}, {"socket_timeout": 16}, {"deadline_seconds": 0}]
)
def test_bulk_download_time_limits_cannot_be_unbounded(monkeypatch, limits):
    def forbidden(*args):
        raise AssertionError("No debe consultar DNS con límites inválidos")

    monkeypatch.setattr(preview, "_public_address", forbidden)
    with pytest.raises(ValueError):
        preview._fetch_document(URL, **limits)


def test_alternate_dns_address_is_still_public_and_mixed_results_are_rejected(monkeypatch):
    monkeypatch.setattr(
        socket,
        "getaddrinfo",
        lambda *args: [(2, 1, 6, "", ("8.8.8.8", 443)), (2, 1, 6, "", ("1.1.1.1", 443))],
    )
    assert preview._public_address("www.upb.edu", time.monotonic() + 3, 1) == "8.8.8.8"
    monkeypatch.setattr(
        socket,
        "getaddrinfo",
        lambda *args: [(2, 1, 6, "", ("8.8.8.8", 443)), (2, 1, 6, "", ("127.0.0.1", 443))],
    )
    with pytest.raises(preview.PreviewFailure, match="privado o reservado"):
        preview._public_address("www.upb.edu", time.monotonic() + 3, 1)


@pytest.fixture(autouse=True)
def isolated_registry(monkeypatch: pytest.MonkeyPatch) -> None:
    monkeypatch.setattr(preview, "load_source_manifest", lambda: SimpleNamespace(sources=[]))
    monkeypatch.setattr(preview, "_load_intake_registry", lambda: SimpleNamespace(proposals=[]))


@pytest.mark.parametrize(
    "url,html,kind",
    [
        (
            PORTAL_URL,
            "<title>Estudiante</title><header>UDABOL Universidad de Aquino Bolivia</header>"
            '<nav>Notas Deudas Solicitudes <a href="/malla.pdf">Malla curricular</a></nav>'
            "<main><h1>Estudiante</h1>Solicitudes</main>",
            "STUDENT_PORTAL",
        ),
        (
            "https://www.upb.edu/login",
            '<title>Login</title><form><input type="password">'
            '<a href="/malla.pdf">Malla curricular</a></form>',
            "AUTHENTICATION",
        ),
        (
            "https://www.upb.edu/",
            "<title>Universidad Privada Boliviana</title>"
            '<nav><a href="/login">Estudiantes</a></nav>'
            "<main><h1>Universidad Privada Boliviana</h1>"
            "<p>Oferta académica pública con carreras universitarias.</p></main>",
            "PUBLIC_DOCUMENT",
        ),
        ("https://www.upb.edu/estudiante/malla.pdf", None, "PUBLIC_DOCUMENT"),
        (
            "https://www.upb.edu/estudiante/carrera",
            "<title>Malla curricular</title><nav>Notas Deudas Solicitudes</nav>"
            "<main><h1>Malla curricular de Sistemas</h1><p>Plan de estudios oficial.</p></main>",
            "PUBLIC_DOCUMENT",
        ),
    ],
)
def test_portal_access_and_public_source_classification(monkeypatch, url, html, kind):
    if html is None:
        import pymupdf

        with pymupdf.open() as document:
            page = document.new_page()
            page.insert_text((50, 50), "Malla curricular de Sistemas\nPlan de estudios oficial")
            content = document.tobytes()
        content_type = "application/pdf"
    else:
        content, content_type = html.encode(), "text/html"
    monkeypatch.setattr(
        preview, "_fetch_document", lambda value: (value, 200, content_type, content)
    )
    result = preview.inspect_source_url(url)
    assert result.document_kind == kind
    if kind in {"STUDENT_PORTAL", "AUTHENTICATION"}:
        assert result.status == "REQUIERE_REVISION"
        assert result.can_use is False
        assert result.excerpt == ""
        assert result.reading_fragments == result.documents == []
        assert result.academic_pages == []
        assert result.suggested_fields == {}
        assert result.document_hash is None
    else:
        assert result.can_use is True


def test_navigation_labels_do_not_become_curriculum_evidence(monkeypatch):
    html = (
        b'<title>Universidad Privada Boliviana</title><nav><a href="/malla.pdf">'
        b"Malla curricular y plan de estudios</a></nav>"
        b'<main><h1>Universidad Privada Boliviana</h1><p><a href="/carrera">'
        b"Perfil profesional y malla curricular</a></p><p>Bienvenidos.</p></main>"
    )
    monkeypatch.setattr(preview, "_fetch_document", lambda url: (url, 200, "text/html", html))
    result = preview.inspect_source_url(URL)
    assert result.suggested_fields["source_type"] == "OFFICIAL_UNIVERSITY_PAGE"
    assert "malla" not in result.excerpt
    assert result.documents == []


@pytest.mark.parametrize(
    "paragraph,expected_scope",
    [
        (
            "La U.C.B. UAC Carmen Pampa celebra 33 años de trayectoria "
            "y comparte sus noticias institucionales.",
            False,
        ),
        (
            "La universidad celebra su aniversario con una noticia "
            "sobre el nuevo plan de estudios de Sistemas.",
            False,
        ),
        (
            "El plan de estudios de Ingeniería de Sistemas incluye programación, "
            "matemáticas y bases de datos para la formación profesional.",
            True,
        ),
    ],
)
def test_scope_suggestion_requires_academic_paragraph_preserving_readable_news(
    monkeypatch, paragraph, expected_scope
):
    html = (
        f"<title>Universidad</title><main><h1>Universidad</h1><p>{paragraph}</p>"
        '<a href="/carrera">Malla curricular y perfil profesional</a></main>'
    ).encode()
    monkeypatch.setattr(preview, "_fetch_document", lambda url: (url, 200, "text/html", html))
    result = preview.inspect_source_url(URL)
    assert result.can_use is True
    assert result.status == "VERIFICADA"
    assert paragraph in result.excerpt
    assert ("scope" in result.suggested_fields) is expected_scope
    if expected_scope:
        assert result.suggested_fields["scope"] == paragraph
    else:
        assert any("alcance académico explícito" in warning for warning in result.warnings)


def test_academic_meta_description_and_link_are_insufficient_for_scope(monkeypatch):
    html = (
        b'<title>Universidad</title><meta name="description" '
        b'content="El plan de estudios y perfil profesional de la universidad">'
        b"<main><p>Bienvenidos a nuestra universidad y noticias.</p>"
        b'<p><a href="/malla">Plan de estudios de Sistemas disponible '
        b"para descargar</a></p></main>"
    )
    monkeypatch.setattr(preview, "_fetch_document", lambda url: (url, 200, "text/html", html))
    result = preview.inspect_source_url(URL)
    assert result.can_use is True
    assert "scope" not in result.suggested_fields


def test_academic_pages_are_explicit_same_university_and_never_followed(monkeypatch):
    html = b"""<title>Oferta universitaria</title><main><h1>Oferta universitaria</h1>
    <a href="/carreras/sistemas">Ingenieria de Sistemas</a>
    <a href="/carreras/sistemas#perfil">Ingenieria de Sistemas duplicada</a>
    <a href="/estudios/plan">Plan de estudios de Sistemas</a>
    <a href="/normas/reglamento">Reglamento academico</a>
    <a href="/malla.pdf">Malla curricular digital</a>
    <a href="/estudiante/malla">Malla curricular de Sistemas</a>
    <a href="/estudiante/solicitud/malla">Plan de estudios de Sistemas</a>
    <a href="/login/malla">Malla curricular de Sistemas</a>
    <a href="/notas/malla">Malla curricular de Sistemas</a>
    <a href="/publico?token=abc">Plan de estudios de Sistemas</a>
    <a href="https://umsa.bo/plan">Plan de estudios de Sistemas</a>
    <a href="https://upb.edu.evil.bo/plan">Plan de estudios de Sistemas</a>
    <a href="http://www.upb.edu/carrera">Ingenieria de Sistemas</a>
    <a href="/carreras">Carreras</a><a href="/malla">Malla</a>
    <a href="/oculto">Ver</a><a href="/malla.pdf.exe">Plan de estudios</a>
    <a href="#perfil">Perfil profesional de Sistemas</a>
    </main>"""
    calls = []

    def fetch(url):
        calls.append(url)
        return url, 200, "text/html", html

    monkeypatch.setattr(preview, "_fetch_document", fetch)
    result = preview.inspect_source_url(URL)
    assert calls == [URL]
    assert result.academic_pages == [
        {
            "url": "https://www.upb.edu/carreras/sistemas",
            "title": "Ingenieria de Sistemas",
            "kind": "carrera",
        },
        {
            "url": "https://www.upb.edu/estudios/plan",
            "title": "Plan de estudios de Sistemas",
            "kind": "plan",
        },
        {
            "url": "https://www.upb.edu/normas/reglamento",
            "title": "Reglamento academico",
            "kind": "reglamento",
        },
    ]
    assert len(result.documents) == 1


@pytest.mark.parametrize("url", [URL, PORTAL_URL])
def test_pages_are_capped_at_six_and_portals_never_publish_candidates(monkeypatch, url):
    title = "Estudiante" if url == PORTAL_URL else "Oferta universitaria"
    html = (
        f"<title>{title}</title>"
        + "".join(f'<a href="/carreras/{i}">Ingenieria de Sistemas {i}</a>' for i in range(8))
    ).encode()
    monkeypatch.setattr(preview, "_fetch_document", lambda value: (value, 200, "text/html", html))
    result = preview.inspect_source_url(url)
    assert len(result.academic_pages) == (0 if url == PORTAL_URL else 6)


@pytest.mark.parametrize(
    "headers,allowed",
    [
        ({}, None),
        ({"x-frame-options": "DENY"}, False),
        ({"x-frame-options": "sameorigin"}, False),
        ({"content-security-policy": "default-src 'self'; frame-ancestors 'none'"}, False),
        ({"content-security-policy": "frame-ancestors 'self'"}, False),
        ({"content-security-policy": "frame-ancestors https://portal.edu.bo"}, None),
        ({"content-security-policy": "frame-ancestors *", "x-frame-options": "DENY"}, True),
        ({"content-security-policy-report-only": "frame-ancestors 'none'"}, None),
    ],
)
def test_embedding_uses_final_enforced_headers_and_preserves_unknown_origin(headers, allowed):
    result = preview._embedding(headers)
    assert result.allowed is allowed
    assert set(result.model_dump()) == {"allowed", "reason"}


def test_fetch_headers_keep_four_value_tuple_contract_and_never_bypass_frame_denial(monkeypatch):
    content = b"<title>Malla curricular</title><main>Plan de estudios oficial.</main>"
    headers = {"Content-Type": "text/html", "X-Frame-Options": "DENY"}

    class Connection:
        sock = None

        def __init__(self, *args):
            pass

        def request(self, *args, **kwargs):
            assert not any(
                key.casefold() in {"cookie", "authorization"} for key in kwargs["headers"]
            )

        def getresponse(self):
            pieces = iter([content, b""])
            return SimpleNamespace(
                status=200, getheader=headers.get, read1=lambda size: next(pieces)
            )

        def close(self):
            pass

    monkeypatch.setattr(preview, "_public_address", lambda *args: "8.8.8.8")
    monkeypatch.setattr(preview, "PinnedHTTPSConnection", Connection)
    fetched = preview._fetch_document(URL)
    assert len(fetched) == 4 and tuple(fetched) == (URL, 200, "text/html", content)
    result = preview.inspect_source_url(URL)
    assert result.can_use is True
    assert result.embedding.allowed is False
    assert "otros sitios" in result.embedding.reason


def test_empty_javascript_layout_does_not_accredit_academic_content(monkeypatch):
    html = (
        b"<html><head><title>Universidad Internacional</title></head>"
        b'<body><div id="app"></div><script src="/app.js"></script></body></html>'
    )
    monkeypatch.setattr(preview, "_fetch_document", lambda url: (url, 200, "text/html", html))
    result = preview.inspect_source_url(URL)
    assert result.document_kind == "UNKNOWN"
    assert result.can_use is False and result.status == "REQUIERE_REVISION"
    assert result.reading_fragments == []


def test_university_preview_does_not_list_portal_menu_careers(monkeypatch):
    from app.knowledge import university_preview as university

    html = (
        b"<title>Estudiante</title><nav>Notas Deudas Solicitudes "
        b'<a href="/carrera/ingenieria">Ingenieria de Sistemas</a></nav>'
    )
    monkeypatch.setattr(university, "_fetch_document", lambda url: (url, 200, "text/html", html))
    result = university.inspect_university_url(PORTAL_URL)
    assert result.document_kind == "STUDENT_PORTAL"
    assert result.can_use is False
    assert result.career_links == [] and result.excerpt == ""


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


def test_academic_pdf_links_use_published_titles_same_institution_and_no_extra_downloads(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    html = """<h1>Ingeniería de Sistemas</h1><main>
    <a href="../docs/malla.pdf"><strong>Malla curricular de Sistemas</strong></a>
    <a href="https://www.upb.edu/docs/malla.pdf#pagina">Malla duplicada</a>
    <a href="/docs/plan_estudios.PDF?version=2026" title="Plan de estudios 2026">Descargar</a>
    <a href="https://repositorio.upb.edu/reglamento.pdf">Reglamento académico</a>
    <a href="https://umsa.bo/malla.pdf">Malla de otra universidad</a>
    <a href="http://www.upb.edu/malla.pdf">Malla insegura</a>
    <a href="https://127.0.0.1/malla.pdf">Malla privada</a>
    <a href="https://unknown.edu.bo/malla.pdf">Malla no reconocida</a>
    <a href="/docs/malla.pdf.exe">Malla ejecutable</a>
    <a href="/docs/folleto.pdf">Folleto institucional</a>
    <a href="/docs/malla-sin-titulo.pdf"><img src="icono.png"></a>
    <a href="/docs/malla-generica.pdf">Descargar</a>
    </main>""".encode()
    calls = []

    def fetch(url):
        calls.append(url)
        return "https://www.upb.edu/carreras/sistemas", 200, "text/html", html

    monkeypatch.setattr(preview, "_fetch_document", fetch)
    result = preview.inspect_source_url(URL)
    assert calls == [URL]
    assert result.documents == [
        {
            "url": "https://www.upb.edu/docs/malla.pdf",
            "title": "Malla curricular de Sistemas",
            "kind": "malla",
        },
        {
            "url": "https://www.upb.edu/docs/plan_estudios.PDF?version=2026",
            "title": "Plan de estudios 2026",
            "kind": "malla",
        },
        {
            "url": "https://repositorio.upb.edu/reglamento.pdf",
            "title": "Reglamento académico",
            "kind": "reglamento",
        },
    ]


def test_academic_documents_are_capped_at_six_and_do_not_invent_titles() -> None:
    html = "".join(f'<a href="/malla-{i}.pdf">Malla publicada {i}</a>' for i in range(9))
    result = preview._academic_documents(html.encode(), URL)
    assert len(result) == 6
    assert result[-1]["title"] == "Malla publicada 5"
    assert preview._academic_documents(b'<a href="/malla.pdf"></a>', URL) == []


def test_pdf_without_digital_text_remains_blocked_and_never_suggests_documents(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    import pymupdf

    with pymupdf.open() as document:
        document.new_page()
        content = document.tobytes()
    monkeypatch.setattr(
        preview, "_fetch_document", lambda url: (url, 200, "application/pdf", content)
    )
    result = preview.inspect_source_url("https://www.upb.edu/malla-escaneada.pdf")
    assert result.status == "NO_DISPONIBLE"
    assert result.can_use is False
    assert result.documents == []
    assert "no se inventan datos ni se aplica OCR" in result.message


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


def test_remote_timeout_preserves_unverified_status_with_actionable_message(
    monkeypatch: pytest.MonkeyPatch,
) -> None:
    def slow_connection(url: str):
        raise TimeoutError("TLS handshake timed out")

    monkeypatch.setattr(preview, "_fetch_document", slow_connection)
    result = preview.inspect_source_url(URL)
    assert result.status == "NO_DISPONIBLE"
    assert result.can_use is False
    assert result.reachable is None
    assert result.excerpt == ""
    assert "tardó demasiado" in result.message
    assert "ficha se conserva" in result.message
    assert "TLS" not in result.message


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
