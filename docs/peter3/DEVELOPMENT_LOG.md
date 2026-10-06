> **Nota histórica (2026-09-30):** Este documento describe la línea base de esa fecha. El estado vigente de corpus, FAISS y gates está en `FINAL_STATUS.md`.

# Registro de desarrollo Aporte Ingenieril SAVP

## Consolidación final — 2026-09-29

- Se auditó el núcleo V2, RIASEC, Learning Analytics, bridge, catálogo, crosswalk y robustez.
- Se corrigieron códigos/perfiles O*NET 31.0 y se degradaron afirmaciones relacionales excesivas.
- Se incorporó la RM 0190/2024 y se validaron 12/12 snapshots por SHA-256 y URL.
- Se creó el registro de 11 referencias externas/internas y el test de integridad cruzada.
- Se alinearon 29 parámetros con constantes reales y clases de evidencia honestas.
- Se consolidó el corpus en 773 chunks, 12 fuentes, 414 páginas digitales y 22 páginas OCR;
  35 chunks conservan contenido OCR.
- Se reconstruyeron dos índices FAISS 773×384 y se reprodujo el benchmark de 30 consultas.
- Se ampliaron guards/evaluator a ataques dentro de evidencia; prompt evaluation cerró 13/13.
- Se añadió E2E real, demo de 14 bloques y benchmark integral por componente.
- Se resolvió el tipado PyMuPDF con un adaptador y `mypy .` quedó limpio.
- Cierre: 100 pruebas aprobadas, 1 omitida por condición OCR y cobertura total 92%.

Los resultados anteriores a esta consolidación eran hitos intermedios y quedan sustituidos por
`PETER3_FINAL_AUDIT.md`, `FINAL_STATUS.md` y los JSON de evaluación vigentes.
