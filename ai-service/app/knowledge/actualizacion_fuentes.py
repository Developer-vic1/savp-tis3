"""Detecta cambios en fuentes universitarias registradas; nunca publica contenido."""

from __future__ import annotations

import hashlib
import json
import re
from datetime import UTC, datetime
from http.client import HTTPException
from urllib.parse import urljoin, urlsplit

from bs4 import BeautifulSoup
from pydantic import Field

from app.knowledge.governance import GovernanceModel, _normalize_url, assess_bolivian_university_url
from app.knowledge.registry import load_source_manifest
from app.knowledge.source_preview import (
    _FETCH_SLOTS,
    PreviewFailure,
    _document_kind,
    _fetch_document,
    _private_academic_link,
)


class SourceRefreshRequest(GovernanceModel):
    source_id: str = Field(min_length=1, max_length=160, pattern=r"^[A-Za-z0-9_-]+$")


def check_registered_source(source_id: str) -> dict[str, object]:
    source = next((s for s in load_source_manifest().sources if s.source_id == source_id), None)
    result: dict[str, object] = {
        "source_id": source_id,
        "checked_at": datetime.now(UTC).isoformat(),
        "status": "NO_DISPONIBLE",
        "fingerprint": None,
        "message": "No se pudo revisar la publicación. Se conserva la referencia anterior.",
    }
    if source is None:
        raise ValueError("La fuente no está registrada.")
    assessment = assess_bolivian_university_url(source.url)
    if (
        not source.official
        or source.verification_status != "VALID"
        or assessment.status != "ACEPTADA"
    ):
        return result | {
            "status": "BLOQUEADA",
            "message": "Solo se revisan fuentes universitarias oficiales validadas.",
        }
    if not _FETCH_SLOTS.acquire(blocking=False):
        return result | {"message": "Hay otras lecturas en curso. La revisión puede reintentarse."}
    try:
        final_url, status, content_type, content = _fetch_document(source.url)
        if status != 200:
            return result
        if urlsplit(final_url).path.rstrip("/") != urlsplit(source.url).path.rstrip("/"):
            return result | {
                "message": "La publicación cambió de destino. "
                "Revisa su nuevo enlace oficial antes de actualizarla."
            }
        if (
            _private_academic_link(final_url)
            or _document_kind(content, content_type, final_url) != "PUBLIC_DOCUMENT"
        ):
            return result | {
                "status": "BLOQUEADA",
                "message": "El destino no corresponde a una publicación académica pública.",
            }
        if "application/pdf" in content_type and content.startswith(b"%PDF-"):
            normalized = content
        elif "text/html" in content_type:
            soup = BeautifulSoup(content, "html.parser")
            for node in soup.select("script,style,nav,footer,header,noscript"):
                node.decompose()
            main = soup.find("main") or soup.find("article") or soup.body
            if main is None:
                return result
            text = re.sub(r"\s+", " ", main.get_text(" ", strip=True)).strip()
            if len(text) < 100:
                return result
            # Enlaces y marcadores de oferta también son datos aunque su texto no cambie.
            links = set()
            for anchor in main.find_all("a", href=True):
                link = urljoin(final_url, str(anchor.get("href", "")))
                link_assessment = assess_bolivian_university_url(link)
                if (
                    link_assessment.recognized_institution != assessment.recognized_institution
                    or link_assessment.status != "ACEPTADA"
                    or _private_academic_link(link)
                ):
                    continue
                if re.search(
                    r"malla|curricul|carrera|plan.?de.?estudio|\.pdf(?:\?|$)",
                    anchor.get_text(" ") + " " + link,
                    re.I,
                ):
                    links.add(_normalize_url(link))
            indicators = []
            for node in main.find_all(class_=True):
                attribute = node.get("class")
                classes = attribute.split() if isinstance(attribute, str) else attribute or []
                markers = sorted(set(classes) & {"opacity-50", "opacity-100", "border-b-2"})
                label = node.get_text(" ", strip=True)
                if markers and len(label) <= 80:
                    indicators.append([label, markers])
            normalized = json.dumps([text, sorted(links), indicators], ensure_ascii=False).encode(
                "utf-8"
            )
        else:
            return result
        return result | {
            "status": "VERIFICADA",
            "fingerprint": hashlib.sha256(normalized).hexdigest(),
            "message": "Publicación revisada. La versión vigente permanece disponible.",
        }
    except (OSError, ValueError, RuntimeError, HTTPException, PreviewFailure):
        return result
    finally:
        _FETCH_SLOTS.release()
