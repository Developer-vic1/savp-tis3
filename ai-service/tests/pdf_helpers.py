from collections.abc import Callable
from pathlib import Path
from typing import Protocol, cast

import pymupdf


class PdfPixmap(Protocol):
    def tobytes(self, output: str) -> bytes: ...


class PdfPage(Protocol):
    @property
    def rect(self) -> object: ...

    def insert_text(
        self,
        point: tuple[int, int],
        text: str,
        *,
        fontsize: int,
    ) -> None: ...

    def get_pixmap(self, *, dpi: int, alpha: bool) -> PdfPixmap: ...

    def insert_image(self, rect: object, *, stream: bytes) -> None: ...


class PdfDocument(Protocol):
    def new_page(
        self,
        *,
        width: int = 595,
        height: int = 842,
    ) -> PdfPage: ...

    def save(self, path: str | Path) -> None: ...

    def close(self) -> None: ...


open_pdf = cast(Callable[[], PdfDocument], pymupdf.open)
