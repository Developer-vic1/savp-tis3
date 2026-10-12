"""Descarga institucional limitada: repetir el comando reanuda la cola local."""

from __future__ import annotations

import argparse
import json
import sys
from pathlib import Path

SERVICE_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(SERVICE_ROOT))

from app.knowledge.ingesta_nacional import (  # noqa: E402
    DEFAULT_OUTPUT,
    CrawlLimits,
    NationalCrawler,
    fallback_catalog,
)


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--catalogo", type=Path, default=SERVICE_ROOT / "data/catalog/universidades_bolivia.json"
    )
    parser.add_argument("--salida", type=Path, default=DEFAULT_OUTPUT)
    parser.add_argument("--institucion", action="append", default=[])
    parser.add_argument("--documentos-por-universidad", type=int, default=40)
    parser.add_argument("--documentos-por-ejecucion", type=int, default=100)
    parser.add_argument("--profundidad", type=int, default=3)
    parser.add_argument("--enlaces-por-documento", type=int, default=100)
    parser.add_argument("--max-mib", type=int, default=4)
    parser.add_argument("--paginas-pdf", type=int, default=30)
    parser.add_argument("--intentos", type=int, default=2)
    parser.add_argument("--pausa", type=float, default=0.5)
    parser.add_argument("--tiempo-limite", type=float, default=12)
    args = parser.parse_args()
    catalog = (
        json.loads(args.catalogo.read_text(encoding="utf-8"))
        if args.catalogo.exists()
        else fallback_catalog()
    )
    limits = CrawlLimits(
        max_documents_per_institution=args.documentos_por_universidad,
        run_documents=args.documentos_por_ejecucion,
        max_depth=args.profundidad,
        max_links_per_document=args.enlaces_por_documento,
        max_bytes=args.max_mib * 1024 * 1024,
        max_pdf_pages=args.paginas_pdf,
        max_attempts=args.intentos,
        pause_seconds=args.pausa,
        deadline_seconds=args.tiempo_limite,
    )
    report = NationalCrawler(catalog, args.salida, limits).run(set(args.institucion) or None)
    print(json.dumps(report, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
