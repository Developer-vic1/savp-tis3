"""Vectores locales de una extracción revisada; no publica índices de búsqueda."""

from __future__ import annotations

import ast
import hashlib
import importlib
import json
import math
import os
import struct
from pathlib import Path
from typing import Any

from app.ingestion.models import KnowledgeChunk

_MODEL_ID = "intfloat/multilingual-e5-small"


def _sha256(path: Path) -> str:
    digest = hashlib.sha256()
    with path.open("rb") as stream:
        for part in iter(lambda: stream.read(1024 * 1024), b""):
            digest.update(part)
    return digest.hexdigest()


def _verified_stage(proposal_id: str) -> tuple[Path, dict[str, Any], list[KnowledgeChunk]]:
    from app.knowledge.academic_ingestion import _proposal, _stage_dir

    _proposal(proposal_id)
    directory = _stage_dir() / proposal_id
    manifest_path = directory / "manifest.json"
    chunk_path = directory / "chunks.jsonl"
    if not manifest_path.is_file() or not chunk_path.is_file():
        raise ValueError("La fuente todavía no tiene una extracción preliminar guardada.")
    manifest = json.loads(manifest_path.read_text(encoding="utf-8"))
    if (
        not isinstance(manifest, dict)
        or manifest.get("proposal_id") != proposal_id
        or manifest.get("status") != "EXTRAIDA_PENDIENTE_REVISION"
    ):
        raise ValueError("El manifiesto de extracción no corresponde a la propuesta aprobada.")
    snapshot = directory / (
        "instantanea.pdf"
        if manifest.get("content_type") == "application/pdf"
        else "instantanea.html"
    )
    if not snapshot.is_file() or "sha256:" + _sha256(snapshot) != manifest.get("document_hash"):
        raise ValueError("El hash de la instantánea no coincide con el manifiesto.")
    chunks = [
        KnowledgeChunk.model_validate_json(line)
        for line in chunk_path.read_text(encoding="utf-8").splitlines()
        if line.strip()
    ]
    if (
        not chunks
        or len(chunks) != manifest.get("chunks")
        or len({chunk.chunk_id for chunk in chunks}) != len(chunks)
        or any(
            chunk.source_id != proposal_id or chunk.document_hash != manifest["document_hash"]
            for chunk in chunks
        )
    ):
        raise ValueError("Los chunks no corresponden a la instantánea aprobada.")
    return directory, manifest, chunks


def _chunks_sha256(chunks: list[KnowledgeChunk]) -> str:
    """Hash del contenido completo, independiente del espaciado del JSONL."""
    digest = hashlib.sha256()
    for chunk in chunks:
        digest.update(
            json.dumps(
                chunk.model_dump(mode="json"),
                sort_keys=True,
                ensure_ascii=False,
                separators=(",", ":"),
            ).encode("utf-8")
        )
        digest.update(b"\n")
    return digest.hexdigest()


def _verify_npy(path: Path, rows: int, dimension: int) -> None:
    """Comprueba float32 finitos y normalizados sin importar NumPy ni el modelo."""
    with path.open("rb") as stream:
        prefix = stream.read(8)
        if prefix[:6] != b"\x93NUMPY" or prefix[6:] not in {b"\x01\x00", b"\x02\x00"}:
            raise ValueError("El archivo de vectores no tiene un formato NPY admitido.")
        size_bytes = 2 if prefix[6] == 1 else 4
        size_raw = stream.read(size_bytes)
        if len(size_raw) != size_bytes:
            raise ValueError("El encabezado vectorial está incompleto.")
        header_size = int.from_bytes(size_raw, "little")
        if not 0 < header_size <= 10000:
            raise ValueError("El encabezado vectorial no es válido.")
        header = ast.literal_eval(stream.read(header_size).decode("latin1"))
        if (
            not isinstance(header, dict)
            or header.get("descr") not in {"<f4", "=f4"}
            or header.get("fortran_order") is not False
            or header.get("shape") != (rows, dimension)
            or rows < 1
            or not 32 <= dimension <= 16384
        ):
            raise ValueError("El tipo o la dimensión vectorial no coincide con el manifiesto.")
        if path.stat().st_size != stream.tell() + rows * dimension * 4:
            raise ValueError("El tamaño del archivo vectorial no coincide con su encabezado.")
        for _ in range(rows):
            values = struct.unpack(f"<{dimension}f", stream.read(dimension * 4))
            if not all(math.isfinite(value) for value in values) or not math.isclose(
                sum(value * value for value in values), 1.0, rel_tol=1e-4, abs_tol=1e-4
            ):
                raise ValueError("Los vectores contienen valores inválidos o no normalizados.")


def read_staged_vector_status(proposal_id: str) -> dict[str, str | int | None]:
    """Informa vectores verificables de la extracción vigente, sin cargar dependencias ML."""
    from app.knowledge.academic_ingestion import _stage_dir

    absent: dict[str, str | int | None] = {
        "status": "NO_GENERADOS",
        "model": None,
        "vectors": 0,
        "reason": "No hay embeddings revisados y verificables para esta extracción.",
    }
    embedding_path = _stage_dir() / proposal_id / "embedding_manifest.json"
    if not embedding_path.is_file():
        return absent
    try:
        directory, source_manifest, chunks = _verified_stage(proposal_id)
        manifest = json.loads(embedding_path.read_text(encoding="utf-8"))
        vectors_path = directory / "embeddings.npy"
        if (
            not isinstance(manifest, dict)
            or manifest.get("status") != "GENERADOS_PENDIENTES_PUBLICACION"
            or manifest.get("proposal_id") != proposal_id
            or manifest.get("source_hash") != source_manifest["document_hash"]
            or manifest.get("chunk_ids") != [chunk.chunk_id for chunk in chunks]
            or manifest.get("chunks_sha256") != _chunks_sha256(chunks)
            or manifest.get("model") != _MODEL_ID
            or manifest.get("vectors") != len(chunks)
            or manifest.get("dimension") != 384
            or not isinstance(manifest.get("reviewer"), str)
            or not manifest["reviewer"].strip()
            or not isinstance(manifest.get("review_note"), str)
            or len(manifest["review_note"].strip()) < 20
            or not vectors_path.is_file()
            or _sha256(vectors_path) != manifest.get("vectors_sha256")
        ):
            raise ValueError("Los embeddings no corresponden a la extracción vigente.")
        _verify_npy(vectors_path, len(chunks), manifest["dimension"])
    except (OSError, ValueError, LookupError, TypeError, SyntaxError, UnicodeError, struct.error):
        return absent
    return {
        "status": "GENERADOS_PENDIENTES_PUBLICACION",
        "model": manifest["model"],
        "vectors": len(chunks),
        "reason": (
            "Embeddings locales revisados; pendientes de publicación en el índice de búsqueda."
        ),
    }


def generate_staged_vectors(
    proposal_id: str, *, model_directory: Path, reviewer: str, review_note: str
) -> dict[str, Any]:
    if not reviewer.strip() or len(review_note.strip()) < 20:
        raise ValueError("Registre revisor y justificación de al menos 20 caracteres.")
    directory, manifest, chunks = _verified_stage(proposal_id)
    if not model_directory.is_dir() or not (model_directory / "modules.json").is_file():
        raise ValueError("Indique un modelo SentenceTransformer descargado en una ruta local.")
    weights = sorted(model_directory.rglob("*.safetensors"))
    if not weights:
        raise ValueError("El modelo local no tiene pesos verificables.")
    try:
        np = importlib.import_module("numpy")
        sentence_transformers = importlib.import_module("sentence_transformers")
    except ImportError as error:
        raise RuntimeError(
            "Faltan sentence-transformers y NumPy en este entorno; no se generaron vectores."
        ) from error
    model = sentence_transformers.SentenceTransformer(
        str(model_directory),
        device="cpu",
        local_files_only=True,
        trust_remote_code=False,
        model_kwargs={"use_safetensors": True},
    )
    vectors = np.asarray(
        model.encode(
            ["passage: " + chunk.text for chunk in chunks],
            batch_size=16,
            normalize_embeddings=True,
            convert_to_numpy=True,
            show_progress_bar=False,
        ),
        dtype=np.float32,
    )
    if (
        vectors.ndim != 2
        or vectors.shape[0] != len(chunks)
        or vectors.shape[1] != 384
        or not np.isfinite(vectors).all()
        or not np.allclose(np.linalg.norm(vectors, axis=1), 1.0, atol=1e-4)
    ):
        raise ValueError("El modelo no produjo un vector válido por cada chunk.")
    model_hash = hashlib.sha256()
    for weight in weights:
        model_hash.update(str(weight.relative_to(model_directory)).encode("utf-8"))
        model_hash.update(_sha256(weight).encode("ascii"))
    _, current_manifest, current_chunks = _verified_stage(proposal_id)
    if current_manifest["document_hash"] != manifest["document_hash"] or _chunks_sha256(
        current_chunks
    ) != _chunks_sha256(chunks):
        raise ValueError("La extracción cambió durante la vectorización; vuelva a revisarla.")
    vectors_tmp = directory / "embeddings.npy.tmp"
    with vectors_tmp.open("wb") as stream:
        np.save(stream, vectors, allow_pickle=False)
    os.replace(vectors_tmp, directory / "embeddings.npy")
    result: dict[str, Any] = {
        "status": "GENERADOS_PENDIENTES_PUBLICACION",
        "proposal_id": proposal_id,
        "source_hash": manifest["document_hash"],
        "chunk_ids": [chunk.chunk_id for chunk in chunks],
        "chunks_sha256": _chunks_sha256(chunks),
        "model": _MODEL_ID,
        "model_directory": str(model_directory.resolve()),
        "model_sha256": model_hash.hexdigest(),
        "dimension": int(vectors.shape[1]),
        "vectors": int(vectors.shape[0]),
        "vectors_sha256": _sha256(directory / "embeddings.npy"),
        "reviewer": reviewer.strip(),
        "review_note": review_note.strip(),
    }
    temporary = directory / "embedding_manifest.json.tmp"
    temporary.write_text(json.dumps(result, ensure_ascii=False, indent=2), encoding="utf-8")
    os.replace(temporary, directory / "embedding_manifest.json")
    return result
