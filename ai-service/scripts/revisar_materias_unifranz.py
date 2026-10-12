"""Actualiza candidatos de materias sobre PDFs guardados, sin conexión externa."""

import hashlib
import json
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from app.ingestion.models import DocumentExtraction  # noqa: E402
from app.knowledge.coleccion_universitaria import (  # noqa: E402
    COLLECTION_ROOT,
    _claim,
    _save,
    _subjects_candidates,
    _verified_document,
    read_collection,
)


def revisar() -> int:
    release = _claim(COLLECTION_ROOT)
    try:
        state = read_collection()
        if state["status"] == "EN_PROCESO":
            raise RuntimeError("La colección debe terminar antes de revisar sus materias.")
        total = 0
        for career in state["careers"]:
            for document in career["curricula"]:
                if not document.get("document_hash") or not document.get("chunks"):
                    continue
                digest = document["document_hash"]
                url = document.get("final_url", document["url"])
                _verified_document(
                    COLLECTION_ROOT,
                    digest,
                    ".pdf",
                    url,
                    document["chunks"],
                    document.get("chunks_file"),
                )
                filename = (
                    document.get("chunks_file")
                    or digest[7:] + "-" + hashlib.sha256(url.encode()).hexdigest()[:16] + ".jsonl"
                )
                records = [
                    json.loads(line)
                    for line in (COLLECTION_ROOT / "fragmentos" / filename)
                    .read_text(encoding="utf-8")
                    .splitlines()
                    if line.strip()
                ]
                digital = sorted(
                    {
                        record["page"]
                        for record in records
                        if record["extraction_method"] == "DIGITAL"
                    }
                )
                extraction = DocumentExtraction(
                    source_id=digest, segments=[], digital_pages=digital, ocr_pages=[], warnings=[]
                )
                document["subjects_candidates"] = _subjects_candidates(
                    COLLECTION_ROOT / "instantaneas" / (digest[7:] + ".pdf"),
                    digest,
                    url,
                    extraction,
                )
                total += len(document["subjects_candidates"])
        _save(COLLECTION_ROOT, state)
        return total
    finally:
        release()


if __name__ == "__main__":
    print(json.dumps({"materias_propuestas_pendientes_revision": revisar()}))
