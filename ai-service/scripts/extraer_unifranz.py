"""Colecciona carreras y mallas oficiales UNIFRANZ sin publicar al corpus."""

import argparse
import json
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from app.knowledge.coleccion_universitaria import run_collection  # noqa: E402


def main() -> int:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument(
        "--recolectar", action="store_true", help="Volver a descargar éxitos explícitamente"
    )
    args = parser.parse_args()
    state = run_collection(recollect=args.recolectar)
    print(
        json.dumps(
            {key: state[key] for key in ("status", "statistics", "warnings")},
            ensure_ascii=False,
            indent=2,
        )
    )
    return 1 if state["status"] == "NO_DISPONIBLE" else 0


if __name__ == "__main__":
    raise SystemExit(main())
