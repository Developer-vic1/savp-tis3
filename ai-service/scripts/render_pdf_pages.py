import argparse
from pathlib import Path

from app.ingestion.extractors import render_pdf_page


def main() -> None:
    parser = argparse.ArgumentParser(description="Renderiza una página PDF para control visual.")
    parser.add_argument("input", type=Path)
    parser.add_argument("page", type=int, help="Página basada en 1")
    parser.add_argument("output", type=Path)
    parser.add_argument("--dpi", type=int, default=144)
    args = parser.parse_args()
    render_pdf_page(args.input, args.page, args.output, args.dpi)
    print(args.output.resolve())


if __name__ == "__main__":
    main()
