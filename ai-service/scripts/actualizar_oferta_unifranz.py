"""Guarda el índice institucional de carreras, sin atribuir sedes ni mallas."""

from __future__ import annotations

import hashlib
import json
import re
from datetime import UTC, datetime
from pathlib import Path
from urllib.parse import urlparse

import httpx
from bs4 import BeautifulSoup

INDEX_URL = "https://unifranz.edu.bo/carreras/"
DESTINATION = Path(__file__).resolve().parents[1] / "data/catalog/ofertas_publicadas.json"


def extract(html: bytes) -> list[dict[str, str]]:
    soup = BeautifulSoup(html, "html.parser")
    careers: dict[str, dict[str, str]] = {}
    for anchor in soup.select('a[role="menuitem"][href]'):
        href = anchor.get("href")
        if not isinstance(href, str):
            continue
        url = href.strip()
        parsed = urlparse(url)
        if parsed.scheme != "https" or parsed.netloc != "unifranz.edu.bo":
            continue
        if not re.fullmatch(r"/carreras/[a-z0-9-]+/", parsed.path):
            continue
        name = anchor.get_text(" ", strip=True)
        if not name or len(name) > 120:
            continue
        careers[url] = {"name": name, "url": url}
    if not 10 <= len(careers) <= 50:
        raise ValueError(
            "El índice oficial no produjo un listado plausible; revisar antes de publicar."
        )
    return list(careers.values())


def main() -> None:
    response = httpx.get(INDEX_URL, timeout=25, follow_redirects=True)
    response.raise_for_status()
    if str(response.url).rstrip("/") != INDEX_URL.rstrip("/"):
        raise ValueError("La fuente oficial redirigió a otra página; revisar manualmente.")
    careers = extract(response.content)
    snapshot = {
        "schema_version": "1.0",
        "institution": "Universidad Privada Franz Tamayo - UNIFRANZ",
        "institution_key": "unifranz",
        "source_url": INDEX_URL,
        "checked_at": datetime.now(UTC).isoformat(),
        "source_sha256": hashlib.sha256(response.content).hexdigest(),
        "scope": (
            "Índice institucional; no confirma oferta por sede, malla, costo ni vigencia legal."
        ),
        "careers": careers,
    }
    DESTINATION.parent.mkdir(parents=True, exist_ok=True)
    temporary = DESTINATION.with_suffix(".json.tmp")
    temporary.write_text(
        json.dumps(snapshot, ensure_ascii=False, indent=2) + "\n", encoding="utf-8"
    )
    temporary.replace(DESTINATION)
    print(f"{len(careers)} carreras publicadas: {DESTINATION}")


if __name__ == "__main__":
    main()
