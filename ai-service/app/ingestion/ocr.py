from functools import lru_cache
from importlib.metadata import version
from pathlib import Path
from typing import Any, Protocol

import numpy as np
from PIL import Image

SERVICE_ROOT = Path(__file__).resolve().parents[2]


class OcrEngine(Protocol):
    engine_name: str
    language: str

    @property
    def engine_version(self) -> str: ...

    def extract(self, image: Image.Image) -> tuple[str, float | None]: ...


@lru_cache(maxsize=1)
def _reader() -> Any:
    import easyocr  # type: ignore[import-untyped]
    import torch

    model_directory = SERVICE_ROOT / ".cache" / "easyocr"
    model_directory.mkdir(parents=True, exist_ok=True)
    return easyocr.Reader(
        ["es", "en"],
        gpu=torch.cuda.is_available(),
        model_storage_directory=str(model_directory),
        download_enabled=True,
        verbose=False,
    )


class EasyOcrSpanish:
    engine_name = "EasyOCR"
    language = "es,en"

    @property
    def engine_version(self) -> str:
        return version("easyocr")

    def extract(self, image: Image.Image) -> tuple[str, float | None]:
        result: list[list[Any]] = _reader().readtext(
            np.asarray(image.convert("RGB")),
            detail=1,
            paragraph=False,
        )
        texts: list[str] = []
        confidences: list[float] = []
        for row in result:
            if len(row) < 3:
                continue
            text = str(row[1]).strip()
            if text:
                texts.append(text)
                confidences.append(float(row[2]))
        confidence = sum(confidences) / len(confidences) if confidences else None
        return "\n".join(texts), round(confidence, 4) if confidence is not None else None
