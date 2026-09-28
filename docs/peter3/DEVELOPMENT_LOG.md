# Registro de desarrollo

## 2026-09-28 — Learning Analytics ampliado

- Se añadieron escalas explícitas por nota y normalización determinista a 0–100.
- Se añadieron área curricular, tendencia global por promedios de período, dispersión,
  consistencia y cobertura temporal.
- Se modelaron tareas detalladas y métricas de entrega, puntualidad, atraso, notas y
  regularidad, con estados de evidencia y nulos honestos.
- RIASEC ahora expone puntajes brutos, normalizados y cobertura del instrumento completo.
- Evidencia ejecutada: `25 passed`; cobertura total 96%; Ruff limpio; mypy estricto limpio.

## 2026-09-28 — Fuentes, catálogo, puente y ranking

- Se preservaron 11 snapshots oficiales (Ministerio, UCB y UMSA) con SHA-256.
- Se creó un catálogo no exhaustivo de dos universidades y cinco identidades de carrera.
- Se versionaron 12 relaciones secundaria/BTH–universidad como configuración experimental
  pendiente de revisión experta.
- Se implementaron afinidad 60/25/15, preparación con cobertura, compatibilidad 55/45,
  ranking estable, fortalezas, brechas y ruta determinista.
- Sensibilidad ejecutada: pesos 80/20 y 20/80 conservan el primer resultado; cobertura mínima
  0.70 suprime el ranking.
- Evidencia ejecutada: `35 passed`; cobertura total 96%; Ruff limpio; mypy estricto del
  servicio limpio.

## 2026-09-28 — Ingesta y OCR

- Se implementó detección y extracción de PDF digital/escaneado, HTML y TXT/MD.
- Se ejecutó EasyOCR real en español sobre un PDF imagen-only: prueba aprobada en 89.28 s y
  confianza 0.8691.
- Se renderizaron e inspeccionaron tres páginas representativas según el flujo de QA PDF.
- Se generó `bo-official-corpus-1.0.0`: 11 fuentes, 818 chunks, 389 páginas digitales, 22
  páginas OCR y 11 páginas sin texto recuperable explícitamente advertidas.
- 35 chunks OCR conservan confianza (mínimo 0.4638; media 0.7656).
- Reconstrucción completa medida: 315.3 s en CPU.
- Evidencia ejecutada: `40 passed, 1 skipped`; la prueba omitida en suite ordinaria se ejecutó
  y aprobó de forma explícita con `RUN_REAL_OCR=1`; Ruff y mypy limpios.

## 2026-09-28 — Auditoría y aislamiento

- Se confirmó la base `integration/savp-consolidado` en `a5fb7eac...`.
- Git rechazó el cambio directo por cambios ajenos en Laravel; no se hizo stash, commit ni reset.
- Se creó `feature/APORTE` y un worktree hermano limpio.
- Se leyeron todos los Markdown encontrados y cuatro informes históricos.
- Se inspeccionaron únicamente rutas y servicios RIASEC relevantes para definir fronteras.

## 2026-09-28 — Investigación RIASEC

- Se verificó la versión oficial web Mini‑IP 2.0 (2025), español, 30 reactivos, escala y scoring.
- Se revisaron licencia, confiabilidad, validez convergente y ausencia de evidencia boliviana.
- Se decidió reproducción literal CC BY‑ND 4.0, sin adaptación cultural silenciosa.

## Regla para cada función

Antes del código se documentaron problema, evidencia, componente, contrato y tests en `RIASEC_IMPLEMENTATION.md`, `STUDENT_PROFILE.md` y `LEARNING_ANALYTICS.md`.

## Verificación final

- Entorno: Python 3.14.0; uv 0.12.19; lock reproducible generado.
- `pytest`: 16 aprobadas, 97% de cobertura.
- `ruff check .`: aprobado.
- `mypy app`: aprobado en modo estricto.
- Benchmark directo: N=1,000, media 0.3740 ms, p95 0.7061 ms.
- No se ejecutaron commit, push, merge, rebase, migraciones, seeders ni conexiones a PostgreSQL.
