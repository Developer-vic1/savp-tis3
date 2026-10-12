"""Genera embeddings reales de una fuente staged revisada, sin publicarla."""

import argparse
import json
from pathlib import Path

from app.knowledge.vector_staging import generate_staged_vectors


def main() -> None:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("proposal_id")
    parser.add_argument("--modelo-directorio", type=Path, required=True)
    parser.add_argument("--revisor", required=True)
    parser.add_argument("--nota-revision", required=True)
    args = parser.parse_args()
    result = generate_staged_vectors(
        args.proposal_id,
        model_directory=args.modelo_directorio,
        reviewer=args.revisor,
        review_note=args.nota_revision,
    )
    print(json.dumps(result, ensure_ascii=False, indent=2))


if __name__ == "__main__":
    main()
