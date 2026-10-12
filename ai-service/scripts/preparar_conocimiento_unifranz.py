"""Prepara y evalúa un índice documental local sin publicar al tutor institucional."""

import hashlib
import json
import sys
import time
from datetime import UTC, datetime
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from app.ingestion.models import KnowledgeChunk  # noqa: E402
from app.knowledge.coleccion_universitaria import (  # noqa: E402
    COLLECTION_ROOT,
    _atomic,
    _verified_document,
    read_collection,
)
from app.retrieval.hybrid import LexicalRetriever  # noqa: E402


def preparar() -> dict:
    started = time.perf_counter()
    state = read_collection()
    if state["status"] == "EN_PROCESO":
        raise RuntimeError("Espera a que termine la colección antes de preparar su índice.")
    chunks: list[KnowledgeChunk] = []
    sources = []
    careers_by_source = {}
    for career in state["careers"]:
        documents = [
            dict(
                career,
                document_hash=career.get("page_hash"),
                chunks=career.get("page_chunks", 0),
                title=career["name"],
                source_type="OFFICIAL_CAREER_PAGE",
            )
        ]
        documents.extend(
            dict(item, source_type="OFFICIAL_CURRICULUM_PDF") for item in career["curricula"]
        )
        for document in documents:
            if not document.get("document_hash") or not document.get("chunks"):
                continue
            url = document.get("final_url", document["url"])
            digest = document["document_hash"]
            raw_hash = digest.removeprefix("sha256:")
            suffix = ".pdf" if document["source_type"] == "OFFICIAL_CURRICULUM_PDF" else ".html"
            _verified_document(
                COLLECTION_ROOT,
                digest,
                suffix,
                url,
                document["chunks"],
                document.get("chunks_file"),
            )
            filename = (
                document.get("chunks_file")
                or raw_hash + "-" + hashlib.sha256(url.encode()).hexdigest()[:16] + ".jsonl"
            )
            records = [
                json.loads(line)
                for line in (COLLECTION_ROOT / "fragmentos" / filename)
                .read_text(encoding="utf-8")
                .splitlines()
                if line.strip()
            ]
            source_id = "UNIFRANZ-" + hashlib.sha256(url.encode()).hexdigest()[:16]
            careers_by_source[source_id] = career["name"]
            for ordinal, record in enumerate(records, 1):
                chunks.append(
                    KnowledgeChunk(
                        chunk_id=source_id + "-" + str(ordinal),
                        source_id=source_id,
                        institution="UNIFRANZ",
                        title=career["name"] + " · " + document["title"],
                        source_type=document["source_type"],
                        url=url,
                        official=True,
                        publication_date=None,
                        retrieved_at=record["retrieved_at"],
                        valid_from=None,
                        valid_until=None,
                        document_hash=digest,
                        section=record.get("section"),
                        page=record.get("page"),
                        version="coleccion-local-v1",
                        status="PENDIENTE_REVISION",
                        extraction_method=record["extraction_method"],
                        ordinal=ordinal,
                        text=record["text"],
                        ocr_engine=record.get("recognition_engine"),
                        ocr_language=record.get("recognition_language"),
                        ocr_version=record.get("recognition_version"),
                    )
                )
            sources.append(
                {
                    "career": career["name"],
                    "title": document["title"],
                    "url": url,
                    "document_hash": digest,
                    "chunks": len(records),
                    "type": document["source_type"],
                }
            )
    if not chunks:
        raise RuntimeError("No hay evidencia verificada para preparar el índice.")
    prepare_seconds = time.perf_counter() - started
    indexing_started = time.perf_counter()
    retriever = LexicalRetriever(chunks)
    indexing_seconds = time.perf_counter() - indexing_started
    cases = [
        ("Materias de Psicología y neuropsicología clínica", "Psicología"),
        ("Malla de Medicina y anatomía", "Medicina"),
        ("Materias de Enfermería y cuidados de salud", "Enfermería"),
        (
            "Ingeniería de Sistemas e Innovación Digital: programación y bases de datos",
            "Ingeniería de Sistemas e Innovación Digital",
        ),
        ("Preparación de alimentos y cocina profesional", "Gastronomía y Artes Culinarias"),
        ("Derecho constitucional y legislación", "Derecho"),
    ]
    evaluation_started = time.perf_counter()
    results = []
    for query, expected in cases:
        hits = retriever.search(query, top_k=3)
        found = [careers_by_source[hit.chunk.source_id] for hit in hits]
        rank = next((position for position, name in enumerate(found, 1) if name == expected), None)
        results.append(
            {
                "query": query,
                "expected": expected,
                "retrieved": found,
                "rank": rank,
                "evidence": [
                    {
                        "title": hit.chunk.title,
                        "url": hit.chunk.url,
                        "page": hit.chunk.page,
                        "text": hit.chunk.text[:350],
                    }
                    for hit in hits
                ],
            }
        )
    evaluation_seconds = time.perf_counter() - evaluation_started
    index_data = {
        "method": "BM25",
        "k1": retriever.lexical_index.k1,
        "b": retriever.lexical_index.b,
        "average_length": retriever.lexical_index.average_length,
        "document_frequencies": dict(retriever.lexical_index.document_frequencies),
        "chunks": [chunk.model_dump() for chunk in chunks],
    }
    _atomic(
        COLLECTION_ROOT / "preparacion" / "indice_documental.json",
        json.dumps(index_data, ensure_ascii=False).encode(),
    )
    report = {
        "created_at": datetime.now(UTC).isoformat(),
        "method": (
            "Preparación de índice documental BM25; sin ajuste de pesos de un modelo generativo"
        ),
        "published": False,
        "careers": len(set(careers_by_source.values())),
        "documents": len(sources),
        "pdfs": sum(source["type"] == "OFFICIAL_CURRICULUM_PDF" for source in sources),
        "chunks": len(chunks),
        "recognized_chunks": sum(chunk.extraction_method == "OCR" for chunk in chunks),
        "preparation_seconds": round(prepare_seconds, 4),
        "indexing_seconds": round(indexing_seconds, 4),
        "evaluation_seconds": round(evaluation_seconds, 4),
        "total_seconds": round(time.perf_counter() - started, 4),
        "top3_successes": sum(result["rank"] is not None for result in results),
        "cases": len(results),
        "mrr_at3": sum(1 / result["rank"] if result["rank"] else 0 for result in results)
        / len(results),
        "evaluation_scope": (
            "Seis consultas de control; no demuestra calidad general ni corrección curricular."
        ),
        "sources": sources,
        "results": results,
    }
    _atomic(
        COLLECTION_ROOT / "preparacion" / "informe.json",
        json.dumps(report, ensure_ascii=False, indent=2).encode(),
    )
    return report


if __name__ == "__main__":
    result = preparar()
    print(
        json.dumps(
            {key: value for key, value in result.items() if key not in {"sources", "results"}},
            ensure_ascii=False,
            indent=2,
        )
    )
