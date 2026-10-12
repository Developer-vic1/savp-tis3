"""Entrega una copia PDF verificada de la colección, sin descargas externas."""

import hashlib
import re
from pathlib import Path
from typing import Any

from app.knowledge.coleccion_universitaria import COLLECTION_ROOT, MAX_BYTES, read_collection


def documento_guardado(digest: str, root: Path = COLLECTION_ROOT) -> tuple[bytes, str]:
    if not re.fullmatch(r"[a-f0-9]{64}", digest):
        raise FileNotFoundError("Documento no encontrado.")
    state = read_collection(root)
    match: tuple[dict[str, Any], dict[str, Any]] | None = next(
        (
            (career, item)
            for career in state["careers"]
            for item in career["curricula"]
            if item.get("document_hash") == "sha256:" + digest
        ),
        None,
    )
    if match is None:
        raise FileNotFoundError("Documento no encontrado.")
    path = root / "instantaneas" / (digest + ".pdf")
    if path.stat().st_size > MAX_BYTES:
        raise ValueError("Documento fuera del límite permitido.")
    content = path.read_bytes()
    if not content.startswith(b"%PDF-") or hashlib.sha256(content).hexdigest() != digest:
        raise ValueError("No se pudo verificar la copia guardada.")
    career, item = match
    title = re.sub(r"^Descargar(?:\s+la)?\s+", "", item["title"], flags=re.I)
    name = re.sub(r"[^\w áéíóúÁÉÍÓÚñÑ().-]", " ", f"UNIFRANZ - {career['name']} - {title}")
    return content, re.sub(r"\s+", " ", name).strip(" .")[:180] + ".pdf"
