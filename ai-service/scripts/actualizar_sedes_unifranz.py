"""Actualiza oferta por sede en la colección; verifica Arquitectura en su página oficial."""

import json
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from app.ingestion.extractors import extract_html  # noqa: E402
from app.knowledge.coleccion_universitaria import (  # noqa: E402
    COLLECTION_ROOT,
    _career_content,
    _claim,
    _fetch,
    _now,
    _persist_chunks,
    _save,
    _snapshot,
    read_collection,
)
from app.knowledge.sedes_universitarias import leer_oferta_sedes  # noqa: E402
from app.knowledge.source_preview import _fetch_document  # noqa: E402


def actualizar() -> dict:
    release = _claim(COLLECTION_ROOT)
    try:
        state = read_collection()
        architecture = next(
            career for career in state["careers"] if career["name"] == "Arquitectura"
        )
        final, _, kind, content = _fetch(architecture["url"], _fetch_document)
        if kind not in {"text/html", "application/xhtml+xml"}:
            raise ValueError("La fuente no es una ficha HTML.")
        _career_content(content, architecture["url"], final, architecture["name"])
        path, digest = _snapshot(COLLECTION_ROOT, content, ".html")
        chunks = _persist_chunks(
            COLLECTION_ROOT, extract_html(path, digest), final, digest, architecture["name"]
        )
        if not chunks:
            raise ValueError("La ficha no tiene lectura verificable.")
        architecture.update(
            {
                "page_hash": digest,
                "final_url": final,
                "page_chunks": chunks,
                "retrieved_at": _now(),
                "page_verified": True,
            }
        )
        architecture.update(
            leer_oferta_sedes(
                content, architecture["name"], final, digest, architecture["retrieved_at"]
            )
        )
        _save(COLLECTION_ROOT, state)
        return {
            "architecture_campuses": architecture["campuses"],
            "careers_with_verified_offering": sum(
                career.get("campus_evidence", {}).get("status") == "OFERTA_IDENTIFICADA"
                for career in state["careers"]
            ),
            "source_url": final,
            "retrieved_at": architecture["retrieved_at"],
        }
    finally:
        release()


if __name__ == "__main__":
    print(json.dumps(actualizar(), ensure_ascii=False, indent=2))
