"""Banco anónimo y determinista, sin red, OCR, modelos ni escrituras institucionales.

Ejecutar desde ai-service: .venv/Scripts/python.exe scripts/medir_vista_previa.py
Los tiempos describen estas muestras y el equipo actual, no una generalización.
"""

from __future__ import annotations

import json
import statistics
import sys
import time
from pathlib import Path

import pymupdf

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))

from app.knowledge.source_preview import (  # noqa: E402
    PreviewFailure,
    _academic_links,
    _document_kind,
    _embedding,
    _extract_metadata,
)


def main() -> None:
    cases = [
        (
            "portal estudiantil",
            "https://academico.udabol.edu.bo/estudiante/solicitud",
            b"<title>Estudiante</title><nav>Notas Deudas Solicitudes Malla curricular</nav>",
            "text/html",
            "STUDENT_PORTAL",
            {},
        ),
        (
            "acceso",
            "https://www.upb.edu/login",
            b'<title>Login</title><form><input type="password"></form>',
            "text/html",
            "AUTHENTICATION",
            {"x-frame-options": "DENY"},
        ),
        (
            "universidad con acceso externo",
            "https://www.upb.edu/",
            b'<title>Universidad</title><nav><a href="/login">Estudiantes</a></nav>'
            b"<main><h1>Universidad</h1><p>Oferta universitaria publicada.</p></main>",
            "text/html",
            "PUBLIC_DOCUMENT",
            {"content-security-policy": "frame-ancestors 'self'"},
        ),
        (
            "carrera con menu estudiantil",
            "https://www.upb.edu/estudiante/carrera",
            b"<title>Malla curricular</title><nav>Notas Deudas Solicitudes</nav>"
            b"<main><h1>Malla curricular</h1><p>Plan de estudios oficial.</p></main>",
            "text/html",
            "PUBLIC_DOCUMENT",
            {},
        ),
        (
            "aplicacion JavaScript vacia",
            "https://www.udabol.edu.bo/",
            b'<html><head><title>Universidad</title></head><body><div id="app"></div>'
            b'<script src="/app.js"></script></body></html>',
            "text/html",
            "UNKNOWN",
            {},
        ),
        (
            "candidatos y destinos operativos",
            "https://www.upb.edu/",
            b"<title>Oferta universitaria</title><main><h1>Oferta universitaria</h1>"
            b'<a href="/carreras/sistemas">Ingenieria de Sistemas</a>'
            b'<a href="/publico/plan">Plan de estudios de Sistemas</a>'
            b'<a href="/reglamento">Reglamento academico</a>'
            b'<a href="/malla.pdf">Malla curricular digital</a>'
            b'<a href="/estudiante/malla">Malla curricular privada</a>'
            b'<a href="/solicitud/malla">Plan de estudios privado</a>'
            b'<a href="/login/malla">Malla curricular privada</a>'
            b'<a href="/publico?token=secreto">Plan de estudios privado</a>'
            b'<a href="https://umsa.bo/plan">Plan de estudios ajeno</a>'
            b'<a href="/carreras">Carreras</a></main>',
            "text/html",
            "PUBLIC_DOCUMENT",
            {},
        ),
        (
            "titulo academico con formulario de acceso",
            "https://www.upb.edu/auth/login",
            b'<title>Malla curricular</title><form><input type="password"></form>',
            "text/html",
            "AUTHENTICATION",
            {"x-frame-options": "SAMEORIGIN"},
        ),
    ]
    with pymupdf.open() as document:
        page = document.new_page()
        page.insert_text((50, 50), "Malla curricular anonima\nPlan de estudios publicado")
        cases.append(
            (
                "PDF digital",
                "https://www.upb.edu/malla.pdf",
                document.tobytes(),
                "application/pdf",
                "PUBLIC_DOCUMENT",
                {},
            )
        )
    with pymupdf.open() as document:
        document.new_page()
        cases.append(
            (
                "PDF sin texto digital",
                "https://www.upb.edu/escaneado.pdf",
                document.tobytes(),
                "application/pdf",
                "PUBLIC_DOCUMENT",
                {},
            )
        )
    total = time.perf_counter()
    results = []
    for name, url, content, kind, expected, headers in cases:
        samples = []
        failure = None
        classification = _document_kind(content, kind, url)
        documents, pages = (
            _academic_links(content, url)
            if kind != "application/pdf" and classification == "PUBLIC_DOCUMENT"
            else ([], [])
        )
        for _ in range(100):
            start = time.perf_counter()
            try:
                _extract_metadata(content, kind, academic_fields={})
            except PreviewFailure as error:
                failure = str(error)
            _document_kind(content, kind, url)
            _embedding(headers)
            if kind != "application/pdf" and classification == "PUBLIC_DOCUMENT":
                _academic_links(content, url)
            samples.append((time.perf_counter() - start) * 1000)
        results.append(
            {
                "caso": name,
                "esperado": expected,
                "obtenido": classification,
                "coincide": classification == expected,
                "lectura_digital": failure is None,
                "bloqueo": failure,
                "incrustacion": _embedding(headers).model_dump(),
                "documentos_candidatos": len(documents),
                "paginas_candidatas": len(pages),
                "p50_ms": round(statistics.median(samples), 3),
                "p95_ms": round(sorted(samples)[94], 3),
            }
        )
    print(
        json.dumps(
            {
                "repeticiones_por_caso": 100,
                "casos": results,
                "total_ms": round((time.perf_counter() - total) * 1000, 2),
            },
            ensure_ascii=True,
            indent=2,
        )
    )
    if not all(result["coincide"] for result in results):
        raise SystemExit(1)
    candidate_case = next(
        result for result in results if result["caso"] == "candidatos y destinos operativos"
    )
    if candidate_case["documentos_candidatos"] != 1 or candidate_case["paginas_candidatas"] != 3:
        raise SystemExit(1)


if __name__ == "__main__":
    main()
