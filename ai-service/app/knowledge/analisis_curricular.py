"""Reconoce regiones, analiza bloques y organiza materias con evidencia espacial."""

from __future__ import annotations

import json
import os
import re
import subprocess
import sys
import threading
import time
from collections.abc import Callable
from pathlib import Path
from typing import Any

from app.knowledge.clasificador_bloques import caracteristicas, cargar_modelo, probabilidad
from app.knowledge.coleccion_universitaria import (
    COLLECTION_ROOT,
    MAX_PAGES,
    _claim,
    _normal_name,
    _now,
    _recognition_runtime,
    _save,
    _verified_document,
    read_collection,
)

ORDINALS = {
    "primer": 1,
    "primero": 1,
    "segundo": 2,
    "tercer": 3,
    "tercero": 3,
    "cuarto": 4,
    "quinto": 5,
    "sexto": 6,
    "septimo": 7,
    "octavo": 8,
    "noveno": 9,
    "decimo": 10,
}
_ROI_PROGRAM = """
import json,sys,pymupdf
path,number,coordinates,tessdata=sys.argv[1:]
with pymupdf.open(path) as document:
    page=document[int(number)-1]
    rectangle=pymupdf.Rect(json.loads(coordinates))
    rectangle=(rectangle+(-3,-3,3,3))&page.rect
    pix=page.get_pixmap(matrix=pymupdf.Matrix(4,4),clip=rectangle,alpha=False)
    if pix.width*pix.height>12000000: raise ValueError('Región demasiado grande')
    with pymupdf.open('pdf',pix.pdfocr_tobytes(language='spa',tessdata=tessdata)) as recognized:
        print(json.dumps({'text':recognized[0].get_text()[:2000]}))
"""


def regiones(page: Any) -> tuple[list[tuple[Any, ...]], list[dict[str, Any]]]:
    blocks = page.get_text("blocks")
    words = page.get_text("words")
    headings = []
    # Dos columnas pueden compartir un bloque, pero no sus coordenadas de palabras.
    for block in words:
        number = ORDINALS.get(_normal_name(block[4]))
        if not number:
            match = re.fullmatch(r"(\d{1,2})\s*(?:semestre)", _normal_name(block[4]))
            number = int(match.group(1)) if match else None
        if (
            number
            and 1 <= number <= 10
            and (
                "semestre" in _normal_name(block[4])
                or any(
                    _normal_name(other[4]) == "semestre"
                    and abs(other[0] - block[0]) < 40
                    and abs(other[1] - block[1]) < 45
                    for other in words
                )
            )
        ):
            digits = [
                other
                for other in words
                if other[4].strip().isdigit()
                and int(other[4].strip()) == number
                and abs(other[1] - block[1]) < 30
                and 0 <= block[0] - other[0] < 65
            ]
            anchor = (
                min(digits, key=lambda item: abs(item[1] - block[1]))[0]
                if digits
                else block[0] - 30
            )
            headings.append({"number": number, "x": anchor, "top": block[3], "heading_y": block[1]})
    footer = min(
        (
            block[1]
            for block in blocks
            if re.search(
                r"ELIGE|MENCIONES|MICROCREDENCIALES|PROFESIONALES|PRERREQUISITOS", block[4]
            )
        ),
        default=page.rect.height,
    )
    for heading in headings:
        next_headers = [
            other["heading_y"]
            for other in headings
            if abs(other["x"] - heading["x"]) < 65 and other["heading_y"] > heading["heading_y"]
        ]
        heading["bottom"] = min([footer, *next_headers])
    return blocks, headings


def semestre_del_bloque(
    block: tuple[Any, ...], headings: list[dict[str, Any]]
) -> dict[str, Any] | None:
    candidates = [
        heading
        for heading in headings
        if heading["top"] - 3 <= block[1] < heading["bottom"] and abs(block[0] - heading["x"]) < 20
    ]
    return max(candidates, key=lambda item: item["top"]) if candidates else None


def nombre_limpio(text: str) -> str:
    return re.sub(r"\s+", " ", text.replace("–", "-").replace("—", "-")).strip()


def es_legible(text: str) -> bool:
    return bool(re.fullmatch(r"[A-ZÁÉÍÓÚÜÑ][A-Za-zÁÉÍÓÚÜÑáéíóúüñ0-9 /(),.:;\-]+", text))


def reconocer_region(path: Path, page: int, bbox: list[float], timeout: float) -> str:
    runtime = _recognition_runtime()
    if not runtime:
        raise RuntimeError("Falta el modelo local de español.")
    result = subprocess.run(
        [
            sys.executable,
            "-c",
            _ROI_PROGRAM,
            str(path),
            str(page),
            json.dumps(bbox),
            runtime["tessdata"],
        ],
        capture_output=True,
        check=True,
        timeout=min(timeout, 35),
        env={**os.environ, "OMP_THREAD_LIMIT": "1"},
        creationflags=subprocess.CREATE_NO_WINDOW if os.name == "nt" else 0,
    )
    return nombre_limpio(json.loads(result.stdout.decode("utf-8"))["text"])


def analizar_bloques(
    subjects: list[dict[str, Any]], weights: list[float] | None, pending: list[dict[str, Any]]
) -> list[dict[str, Any]]:
    accepted = []
    for subject in subjects:
        confidence = (
            probabilidad(caracteristicas(subject["name"], True), weights) if weights else 1.0
        )
        if confidence >= 0.5:
            accepted.append(subject)
        else:
            pending.append(
                {
                    "semester": subject["semester"],
                    "page": subject["page"],
                    "reason": "Bloque sin clasificación suficiente",
                }
            )
    return accepted


def organizar_materias(
    subjects: list[dict[str, Any]], declared: set[int]
) -> tuple[list[dict[str, Any]], list[dict[str, Any]]]:
    unique: dict[tuple[int, str], dict[str, Any]] = {}
    for subject in sorted(
        subjects, key=lambda item: (item["semester"], item["page"], item["bbox"][1])
    ):
        unique.setdefault((subject["semester"], _normal_name(subject["name"])), subject)
    subjects = list(unique.values())[:200]
    semesters = [
        {
            "number": number,
            "subjects": [subject for subject in subjects if subject["semester"] == number],
        }
        for number in sorted(declared)
    ]
    return subjects, semesters


def analizar_documento(
    path: Path, digest: str, url: str, phase: Callable[[str], None] | None = None
) -> dict[str, Any]:
    import pymupdf

    started = time.perf_counter()
    weights = cargar_modelo()
    subjects = []
    declared: set[int] = set()
    pending = []
    recognized = 0
    if phase:
        phase("RECONOCIMIENTO")
    with pymupdf.open(path) as document:  # type: ignore[no-untyped-call]
        for number in range(1, min(document.page_count, MAX_PAGES) + 1):
            blocks, headings = regiones(document[number - 1])
            declared.update(heading["number"] for heading in headings)
            for block in blocks:
                heading = semestre_del_bloque(block, headings)
                if not heading:
                    continue
                text = nombre_limpio(block[4])
                if (
                    not 5 <= len(text) <= 180
                    or text.isdigit()
                    or _normal_name(text) in {*ORDINALS, "semestre"}
                ):
                    continue
                method = "DIGITAL"
                if not es_legible(text):
                    remaining = 120 - (time.perf_counter() - started)
                    try:
                        if remaining <= 0:
                            raise TimeoutError("Límite de reconocimiento alcanzado.")
                        text = reconocer_region(path, number, list(block[:4]), remaining)
                        method = "OCR"
                        recognized += 1
                    except (OSError, ValueError, RuntimeError, subprocess.SubprocessError):
                        pending.append(
                            {
                                "semester": heading["number"],
                                "page": number,
                                "reason": "Nombre pendiente de lectura visual",
                            }
                        )
                        continue
                if not es_legible(text):
                    pending.append(
                        {
                            "semester": heading["number"],
                            "page": number,
                            "reason": "Texto ambiguo en una región curricular",
                        }
                    )
                    continue
                subjects.append(
                    {
                        "name": text,
                        "semester": heading["number"],
                        "page": number,
                        "method": method,
                        "status": "PENDIENTE_REVISION",
                        "evidence_text": text,
                        "bbox": [round(value, 2) for value in block[:4]],
                        "source_url": url,
                        "document_hash": digest,
                    }
                )
    if phase:
        phase("ANALISIS")
    subjects = analizar_bloques(subjects, weights, pending)
    if phase:
        phase("ORGANIZACION")
    subjects, semesters = organizar_materias(subjects, declared)
    return {
        "subjects_candidates": subjects,
        "excerpt": "\n\n".join(
            f"Semestre {semester['number']}\n"
            + "\n".join(subject["name"] for subject in semester["subjects"])
            for semester in semesters
        )[:1500],
        "curricular_structure": {
            "version": "estructura-curricular-v1",
            "semester_count": len(semesters),
            "subject_count": len(subjects),
            "semesters": semesters,
            "status": "REQUIERE_REVISION"
            if pending or not semesters
            else "ORGANIZADA_PENDIENTE_REVISION",
            "pending_regions": len(pending),
            "message": (
                "Materias organizadas por encabezados de semestre del documento. "
                "Confirma sus nombres y requisitos."
            ),
        },
        "analysis_metrics": {
            "seconds": round(time.perf_counter() - started, 4),
            "recognized_regions": recognized,
            "pending_regions": pending,
            "classifier": "bloques-curriculares-v1" if weights else "reglas-conservadoras",
        },
    }


def _procesar_coleccion(root: Path, release: Any) -> dict[str, Any]:
    started = time.perf_counter()
    state = read_collection(root)
    original_status = state.get("processing_previous_status", state["status"])
    state["status"] = "EN_PROCESO"
    documents = [
        (career, item)
        for career in state["careers"]
        for item in career["curricula"]
        if item.get("document_hash") and item.get("chunks")
    ]
    state["progress"] = {"completed": 0, "total": len(documents), "current": None}
    try:
        for career, item in documents:
            url = item.get("final_url", item["url"])
            digest = item["document_hash"]
            state["progress"]["current"] = career["name"]
            state["processing_stage"] = "RECONOCIMIENTO"
            _save(root, state)
            try:
                _verified_document(
                    root, digest, ".pdf", url, item["chunks"], item.get("chunks_file")
                )

                def phase(stage: str) -> None:
                    state["processing_stage"] = stage
                    _save(root, state)

                result = analizar_documento(
                    root / "instantaneas" / (digest[7:] + ".pdf"), digest, url, phase
                )
                state["processing_stage"] = "ORGANIZACION"
                _save(root, state)
                item.update(result)
                item["analysis_updated_at"] = _now()
            except (OSError, ValueError, RuntimeError):
                item["analysis_error"] = "La organización requiere revisión del original."
            state["progress"]["completed"] += 1
            _save(root, state)
        state["status"] = original_status if original_status != "EN_PROCESO" else "PARCIAL"
        state["processing_stage"] = "FINALIZADO"
        state["progress"]["current"] = None
        state["processing_seconds"] = round(time.perf_counter() - started, 4)
        _save(root, state)
        return state
    finally:
        state.pop("processing_previous_status", None)


def procesar_coleccion(root: Path = COLLECTION_ROOT, release: Any = None) -> dict[str, Any]:
    release = release or _claim(root)
    try:
        return _procesar_coleccion(root, release)
    finally:
        release()


def iniciar_analisis() -> dict[str, Any]:
    release = _claim(COLLECTION_ROOT)
    try:
        state = read_collection()
        state["processing_previous_status"] = state["status"]
        state["status"] = "EN_PROCESO"
        state["processing_stage"] = "RECONOCIMIENTO"
        state["progress"] = {
            "completed": 0,
            "total": sum(
                bool(item.get("document_hash") and item.get("chunks"))
                for career in state["careers"]
                for item in career["curricula"]
            ),
            "current": None,
        }
        _save(COLLECTION_ROOT, state)
        threading.Thread(
            target=procesar_coleccion,
            kwargs={"release": release},
            name="analisis-curricular",
            daemon=True,
        ).start()
    except Exception:
        release()
        raise
    return read_collection()
