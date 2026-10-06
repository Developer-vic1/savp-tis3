"""Vista institucional de solo lectura; agrupa carreras sin incorporar fuentes."""
from __future__ import annotations

import hashlib
import http.client
import re
from urllib.parse import parse_qsl, urlencode, urljoin, urlsplit, urlunsplit

from bs4 import BeautifulSoup
from pydantic import Field

from app.knowledge.governance import (
    _trusted_university_for_host,
    assess_bolivian_university_url,
)
from app.knowledge.source_preview import (
    PreviewFailure,
    SourcePreviewResponse,
    _FETCH_SLOTS,
    _duplicate,
    _extract_metadata,
    _fetch_document,
    _text,
)


class UniversityPreviewResponse(SourcePreviewResponse):
    university_key: str | None = None
    institutional_url: str | None = None
    career_links: list[dict[str, str]] = Field(default_factory=list, max_length=30)


def institutional_identity(url: str) -> tuple[str | None, str | None]:
    assessment = assess_bolivian_university_url(url)
    university = _trusted_university_for_host(assessment.host)
    if assessment.status != "ACEPTADA" or university is None:
        return None, None
    # Una identidad por universidad, conservando el portal de la sede elegida.
    return university.domain, urlunsplit(("https", assessment.host, "/", "", ""))


def _site_description(host: str | None) -> str | None:
    for roots, name in [
        (("google.com", "google.com.bo", "gmail.com"), "Google: buscador o servicio de correo"),
        (("youtube.com", "youtu.be"), "YouTube: plataforma de videos"),
        (("facebook.com", "fb.com"), "Facebook: red social"),
        (("instagram.com",), "Instagram: red social"),
        (("tiktok.com",), "TikTok: red social"),
    ]:
        if host and any(host == root or host.endswith("." + root) for root in roots):
            return name
    return None


def _career_links(content: bytes, page: str, institution: str) -> list[dict[str, str]]:
    soup = BeautifulSoup(content, "lxml")
    result: dict[str, dict[str, str]] = {}
    for anchor in soup.find_all("a", href=True):
        href = str(anchor.get("href", ""))
        if len(href) > 2000:
            continue
        url = urljoin(page, href)
        assessment = assess_bolivian_university_url(url)
        if assessment.status != "ACEPTADA" or assessment.recognized_institution != institution:
            continue
        parts = urlsplit(url)
        label = _text(anchor.get_text(" ", strip=True), 180)
        path = parts.path.lower()
        if re.search(r"director|contacto|admisi[oó]n|inscripci[oó]n|noticia|evento|biblioteca|postgrado|posgrado|pre[-\s]?universitari|diplomado", label + " " + path, re.I):
            continue
        if label.casefold() in {"carreras", "pregrado", "oferta académica", "facultades", "facultad"}:
            continue
        if not label or not re.search(r"carrera|pregrado|licenciatura|ingenier[ií]a|medicina|derecho|arquitectura|psicolog[ií]a|odontolog[ií]a", label + " " + path, re.I):
            continue
        # Cada ruta conserva su evidencia; parámetros y fragmentos no crean otra carrera.
        query = urlencode(sorted((key, value) for key, value in parse_qsl(parts.query, keep_blank_values=True)
                                if not key.lower().startswith("utm_") and key.lower() not in {"fbclid", "gclid"}))
        canonical = urlunsplit(("https", assessment.host, parts.path.rstrip("/") or "/", query, ""))
        result.setdefault(canonical, {"name": label, "url": canonical, "status": "POR_REVISAR"})
        if len(result) >= 30:
            break
    return list(result.values())


def inspect_university_url(url: str) -> UniversityPreviewResponse:
    assessment = assess_bolivian_university_url(url)
    identity, institutional_url = institutional_identity(url)
    base = {"requested_url": url, "assessment": assessment,
            "university_key": identity, "institutional_url": institutional_url}
    if not institutional_url:
        service = _site_description(assessment.host)
        message = (f"Este enlace pertenece a {service}. Comparte la página institucional de la universidad, no una red social o un buscador."
                   if service else "Todavía no podemos identificar este sitio como una universidad boliviana. Necesita comprobarse su institución y dominio antes de estudiar sus carreras.")
        if assessment.host is None:
            message = assessment.message
        return UniversityPreviewResponse(**base,
            status="BLOQUEADA" if assessment.status == "RECHAZADA" else "REQUIERE_REVISION", message=message)
    duplicate = _duplicate([institutional_url])
    if duplicate:
        return UniversityPreviewResponse(**base, status="DUPLICADA", duplicate=duplicate,
            title=duplicate["title"], message="La página institucional ya está registrada o en revisión. Sus carreras pertenecen al mismo expediente; no necesitas proponer la universidad otra vez.")
    if not _FETCH_SLOTS.acquire(blocking=False):
        return UniversityPreviewResponse(**base, status="NO_DISPONIBLE", message="Estamos revisando otras páginas. Intenta nuevamente en un momento.")
    try:
        final_url, status, content_type, content = _fetch_document(institutional_url)
        if status != 200 or content_type not in {"text/html", "application/xhtml+xml"}:
            return UniversityPreviewResponse(**base, status="NO_DISPONIBLE", http_status=status,
                message="No pudimos leer el portal institucional. Revisa su dirección; todavía no se considera verificado.")
        title, excerpt, _ = _extract_metadata(content, content_type)
        if not title or len(title) < 3 or re.search(r"^404|not found|access denied|just a moment|p[aá]gina no encontrada", title, re.I):
            return UniversityPreviewResponse(**base, status="REQUIERE_REVISION", message="La página no muestra información institucional suficiente o presenta una protección. Revisa el enlace.")
        careers = _career_links(content, final_url, assessment.recognized_institution or "")
        return UniversityPreviewResponse(**base, status="VERIFICADA", reachable=True,
            http_status=200, final_url=final_url, content_type=content_type,
            title=title, excerpt=excerpt, document_hash="sha256:" + hashlib.sha256(content).hexdigest(),
            suggested_fields={"title": title, "declared_institution": assessment.recognized_institution or "", "source_type": "OFFICIAL_UNIVERSITY_PAGE"},
            career_links=careers, can_use=True,
            message="Leímos el portal institucional. Sus enlaces de carreras se agrupan bajo esta universidad y quedan pendientes de revisión.",
            warnings=["Los enlaces detectados son candidatos: no acreditan que toda esa oferta esté vigente.", "Esta revisión no incorpora carreras ni crea otra universidad."])
    except PreviewFailure as error:
        return UniversityPreviewResponse(**base, status="NO_DISPONIBLE", message=str(error))
    except (OSError, http.client.HTTPException, ValueError, RuntimeError):
        return UniversityPreviewResponse(**base, status="NO_DISPONIBLE", message="No pudimos comprobar la página por conexión, formato o seguridad. Conserva el enlace para revisarlo después.")
    finally:
        _FETCH_SLOTS.release()
