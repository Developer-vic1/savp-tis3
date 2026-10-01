> **Estado de fase 2 (2026-09-30):** Las cifras y los PASS fechados el 2026-09-29 describen la l?nea base hist?rica. Se reconstruy? el corpus con cuatro snapshots HTML UCB nuevos y se corrigi? metadata de versi?n respaldada por hash; ambos ?ndices FAISS y sus m?tricas siguen `STALE` hasta reconstrucci?n y revalidaci?n. V?ase `FINAL_STATUS.md`.

# Rendimiento medido — PETER 3

Revalidación del 2026-10-01: no se pudo medir retrieval, tutor, E2E integral ni carga fría
del modelo con el corpus actual porque ambos índices siguen `STALE`. El smoke HTTP real
registró latencias por endpoint en `ai-service/data/evaluation/http_smoke_phase21.json`:
health, RIASEC y Analysis V2 respondieron 200; knowledge y tutor respondieron 503.

Fase 2.1: no hay medición nueva de retrieval, tutor o E2E porque ambos índices siguen `STALE`.
Las cifras siguientes pertenecen a sus entornos y fechas indicados; ninguna es un SLA vigente.

Fecha: 2026-09-29. Comando:

```powershell
.venv312\Scripts\python.exe scripts\benchmark_peter3.py --repetitions 20
```

Entorno: Windows 11 `10.0.26200`, Python 3.12.14, CPU AMD64 Family 25 Model 117,
8 núcleos físicos/16 lógicos, 16.435 GB RAM, ejecución CPU. Fixture sintético; E5 y el índice
FAISS seleccionado reales; proceso caliente.

| Componente | N | Mediana | p95 |
|---|---:|---:|---:|
| Analysis V2 | 20 | 10.293 ms | 15.690 ms |
| Retrieval híbrido | 20 | 44.997 ms | 49.809 ms |
| Tutor estructurado con retrieval | 20 | 52.223 ms | 60.452 ms |
| Pipeline integral | 20 | 108.883 ms | 125.236 ms |

El resultado íntegro está en `ai-service/data/evaluation/performance_results.json`. El pipeline
integral se mide mediante funciones de servicio; no incluye HTTP, red, concurrencia, descarga ni
carga fría del modelo/índice. Estas cifras no son un SLA. El LLM local no está incluido porque su
runtime no estaba disponible.

## Fase 2: mediciones locales del análisis, 2026-09-30

Python 3.12.10, Windows 11, CPU AMD64 Family 23 Model 160, 8 procesadores lógicos; fixture
sintético, 10 iteraciones de calentamiento y 50 mediciones por componente. Proceso caliente,
sin HTTP, retrieval, tutor ni concurrencia. Datos completos en
`ai-service/data/evaluation/analysis_v2_phase2_performance.json`.

| Componente | Mediana | p95 |
|---|---:|---:|
| Validación | 0.0497 ms | 0.0657 ms |
| RIASEC | 0.0278 ms | 0.0310 ms |
| Learning Analytics | 0.2670 ms | 0.3338 ms |
| Recommendation V2 | 5.1650 ms | 7.2662 ms |
| Análisis V2 completo | 6.2266 ms | 7.6337 ms |

Una medición independiente de inicio de proceso e importación de FastAPI tardó 3.265 s;
no incluye carga del modelo de embeddings, índice ni LLM. Está en
`ai-service/data/evaluation/cold_start_results.json`. El benchmark de retrieval del 2026-09-29
es histórico y no representa el corpus actual con índices `STALE`.
