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
    parser.add_argument(
        "--merge-existing",
        action="store_true",
        help=(
            "Reprocesa los --source-id indicados y conserva, sin reextraer, las demás "
            "fuentes ya presentes en --output."
        ),
    )
    args = parser.parse_args()

    selected = set(args.source_id)
    all_sources = load_source_manifest().sources
    selected_sources = [
        source
        for source in all_sources
        if not selected or source.source_id in selected
    ]
    if selected - {source.source_id for source in selected_sources}:
        missing = sorted(selected - {source.source_id for source in selected_sources})
        raise ValueError(f"source_id no encontrado: {missing}")
    if args.merge_existing and not selected:
        raise ValueError("--merge-existing requiere al menos un --source-id")

    existing_lines: dict[str, list[str]] = {}
    existing_statistics: dict[str, dict[str, object]] = {}
    if args.merge_existing:
        if not args.output.is_file():
            raise ValueError("--merge-existing requiere que --output ya exista")
        for line in args.output.read_text(encoding="utf-8").splitlines():
            if not line.strip():
                continue
            source_id = str(json.loads(line)["source_id"])
            existing_lines.setdefault(source_id, []).append(line)
        prior_manifest_path = args.output.with_name("corpus_manifest.json")
        if prior_manifest_path.is_file():
            prior_manifest = json.loads(prior_manifest_path.read_text(encoding="utf-8"))
            existing_statistics = {
                str(item["source_id"]): item
                for item in prior_manifest.get("statistics", [])
            }
        sources = all_sources
    else:
        sources = selected_sources

    args.output.parent.mkdir(parents=True, exist_ok=True)
    statistics: list[dict[str, object]] = []
    total_chunks = 0
    with args.output.open("w", encoding="utf-8", newline="\n") as handle:
        for source in sources:
            if args.merge_existing and source.source_id not in selected:
                preserved = existing_lines.get(source.source_id)
                if not preserved:
                    raise ValueError(
                        f"no hay chunks existentes para conservar: {source.source_id}"
                    )
                for line in preserved:
                    handle.write(line + "\n")
                total_chunks += len(preserved)
                prior = existing_statistics.get(source.source_id)
                statistics.append(
                    prior
                    or {
                        "source_id": source.source_id,
                        "segments": None,
                        "chunks": len(preserved),
                        "digital_pages": None,
                        "ocr_pages": None,
                        "warnings": [
                            "Estadísticas de extracción no disponibles; chunks preservados."
                        ],
                    }
                )
                print(f"{source.source_id}: {len(preserved)} chunks preservados")
                continue
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
