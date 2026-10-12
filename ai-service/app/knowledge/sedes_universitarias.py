"""Oferta por sede desde indicadores explícitos de la ficha de cada carrera."""

import re
import unicodedata
from typing import Any

from bs4 import BeautifulSoup

SEDES = {"CBBA": "Cochabamba", "EA": "El Alto", "LPZ": "La Paz", "SCZ": "Santa Cruz"}


def _normal(text: str) -> str:
    return re.sub(
        r"\s+",
        " ",
        "".join(
            character
            for character in unicodedata.normalize("NFKD", text.casefold())
            if not unicodedata.combining(character)
        ),
    ).strip()


def leer_oferta_sedes(
    content: bytes, career_name: str, source_url: str, document_hash: str, retrieved_at: str | None
) -> dict[str, Any]:
    soup = BeautifulSoup(content, "lxml")
    main = soup.select_one("main.career-page") or soup.find("main")
    pending = {
        "campuses": [],
        "campus_availability": [],
        "campus_evidence": {
            "status": "PENDIENTE",
            "source_url": source_url,
            "document_hash": document_hash,
            "retrieved_at": retrieved_at,
            "method": "INDICADORES_DE_OFERTA",
        },
    }
    if not main or not any(
        _normal(heading.get_text(" ", strip=True)) == _normal(career_name)
        for heading in main.find_all("h1")
    ):
        return pending
    labels = main.find_all(
        "span", string=lambda value: value and _normal(value) == "disponible en:"
    )
    if len(labels) != 1:
        return pending
    region = labels[0].find_next_sibling("div")
    if region is None:
        return pending
    availability: dict[str, dict[str, Any]] = {}
    for indicator in region.find_all("span"):
        code = indicator.get_text(" ", strip=True).upper()
        if code not in SEDES:
            continue
        if code in availability:
            availability[code]["status"] = "PENDIENTE"
            availability[code]["indicator"] = "DESCONOCIDO"
            continue
        class_attribute = indicator.get("class")
        classes = set(
            class_attribute.split() if isinstance(class_attribute, str) else class_attribute or []
        )
        active = {"opacity-100", "border-b-2"} <= classes and "opacity-50" not in classes
        inactive = (
            "opacity-50" in classes and "opacity-100" not in classes and "border-b-2" not in classes
        )
        status = "OFERTADA" if active else "NO_INDICADA" if inactive else "PENDIENTE"
        availability[code] = {
            "campus": SEDES[code],
            "status": status,
            "source_url": source_url,
            "evidence_text": "Disponible en: " + code,
            "indicator": "ACTIVO" if active else "INACTIVO" if inactive else "DESCONOCIDO",
        }
    result = list(availability.values())
    return {
        "campuses": [item["campus"] for item in result if item["status"] == "OFERTADA"],
        "campus_availability": result,
        "campus_evidence": {
            "status": "OFERTA_IDENTIFICADA"
            if len(result) == 4 and all(item["status"] != "PENDIENTE" for item in result)
            else "PENDIENTE",
            "source_url": source_url,
            "document_hash": document_hash,
            "retrieved_at": retrieved_at,
            "method": "INDICADORES_DE_OFERTA",
        },
    }
