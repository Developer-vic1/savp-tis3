import hashlib
import json

import pytest

from app.knowledge.documento_coleccion import documento_guardado


def test_download_requires_registered_verified_pdf(tmp_path):
    content = b"%PDF-1.7\nverifiable fixture"
    digest = hashlib.sha256(content).hexdigest()
    folder = tmp_path / "instantaneas"
    folder.mkdir()
    pdf = folder / (digest + ".pdf")
    pdf.write_bytes(content)
    state = {
        "schema_version": "1.0",
        "institution": "UNIFRANZ",
        "status": "PARCIAL",
        "careers": [
            {
                "name": "Medicina",
                "url": "https://unifranz.edu.bo/carreras/medicina/",
                "status": "PENDIENTE",
                "curricula": [
                    {
                        "title": "Malla curricular",
                        "url": "https://unifranz.edu.bo/malla.pdf",
                        "status": "PENDIENTE",
                        "document_hash": "sha256:" + digest,
                        "chunks": 0,
                    }
                ],
            }
        ],
    }
    (tmp_path / "manifiesto.json").write_text(json.dumps(state), encoding="utf-8")
    result, name = documento_guardado(digest, tmp_path)
    assert result == content
    assert name == "UNIFRANZ - Medicina - Malla curricular.pdf"
    pdf.write_bytes(b"%PDF-corrupto")
    with pytest.raises(ValueError):
        documento_guardado(digest, tmp_path)
    with pytest.raises(FileNotFoundError):
        documento_guardado("b" * 64, tmp_path)
    with pytest.raises(FileNotFoundError):
        documento_guardado("../manifiesto.json", tmp_path)
