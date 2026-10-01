> **Estado de fase 2 (2026-09-30):** Las cifras y los PASS fechados el 2026-09-29 describen la l?nea base hist?rica. Se reconstruy? el corpus con cuatro snapshots HTML UCB nuevos y se corrigi? metadata de versi?n respaldada por hash; ambos ?ndices FAISS y sus m?tricas siguen `STALE` hasta reconstrucci?n y revalidaci?n. V?ase `FINAL_STATUS.md`.

# Limitaciones reales

Fase 2.1, revalidación del 2026-10-01: Code Integrity del equipo bloquea `torch._C`
(eventos 3033/3077), impidiendo generar embeddings reales. Mypy 1.20.2 sí pasa en un
entorno limpio reproducido desde `uv.lock`: 0 errores en `mypy app scripts` y `mypy .`
tras corregir tres errores de tipos. No se han ejecutado índices ni retrieval nuevos.
La segunda laptop solo es accesible por sincronización GitHub, que no ofrece ejecución
remota desde esta sesión.

- No hay validación psicométrica específica en población boliviana ni estudio longitudinal.
- Bridge y crosswalk son inferencias documentales; requieren revisión experta y evaluación.
- El catálogo cubre cinco ofertas de dos universidades y no es exhaustivo.
- El resultado histórico de retrieval alcanza Recall@5 híbrido 0.6667 en el conjunto total; puede omitir evidencia.
- El benchmark de prompts tiene 13 escenarios y no prueba seguridad universal.
- El LLM local no fue evaluado porque el runtime no estuvo disponible.
- Los parámetros experimentales no fueron calibrados con resultados estudiantiles reales.
- Las métricas de rendimiento son de CPU/proceso caliente, no SLA ni prueba de concurrencia.
- La integración Laravel, base de datos y UI está fuera de alcance y no fue iniciada.
