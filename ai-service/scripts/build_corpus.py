import argparse
import json
from pathlib import Path

from app.ingestion.chunking import build_chunks
from app.ingestion.extractors import extract_source
from app.knowledge.registry import SERVICE_ROOT, load_source_manifest


def main() -> None:
    parser = argparse.ArgumentParser(description="Construye el corpus PETER 3 trazable.")
    parser.add_argument(
        "--output",
        type=Path,
        default=SERVICE_ROOT / "data" / "processed" / "corpus.jsonl",
    )
    parser.add_argument("--source-id", action="append", default=[])
    args = parser.parse_args()

    selected = set(args.source_id)
    sources = [
        source
        for source in load_source_manifest().sources
        if not selected or source.source_id in selected
    ]
    if selected - {source.source_id for source in sources}:
        missing = sorted(selected - {source.source_id for source in sources})
        raise ValueError(f"source_id no encontrado: {missing}")

    args.output.parent.mkdir(parents=True, exist_ok=True)
    statistics: list[dict[str, object]] = []
    total_chunks = 0
    with args.output.open("w", encoding="utf-8", newline="\n") as handle:
        for source in sources:
            extraction = extract_source(source)
            chunks = build_chunks(extraction, source)
            for chunk in chunks:
                handle.write(chunk.model_dump_json() + "\n")
            total_chunks += len(chunks)
            statistics.append(
                {
                    "source_id": source.source_id,
                    "segments": len(extraction.segments),
                    "chunks": len(chunks),
                    "digital_pages": len(extraction.digital_pages),
                    "ocr_pages": len(extraction.ocr_pages),
                    "warnings": extraction.warnings,
                }
            )
            print(
                f"{source.source_id}: {len(chunks)} chunks, "
                f"digital={len(extraction.digital_pages)}, ocr={len(extraction.ocr_pages)}"
            )

    manifest_path = args.output.with_name("corpus_manifest.json")
    manifest_path.write_text(
        json.dumps(
            {
                "corpus_version": "bo-official-corpus-1.0.0",
                "source_manifest_version": load_source_manifest().manifest_version,
                "source_count": len(sources),
                "chunk_count": total_chunks,
                "statistics": statistics,
            },
            ensure_ascii=False,
            indent=2,
        )
        + "\n",
        encoding="utf-8",
    )
    print(f"Corpus: {args.output}")
    print(f"Manifiesto: {manifest_path}")


if __name__ == "__main__":
    main()
