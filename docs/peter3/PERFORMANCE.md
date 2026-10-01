# Rendimiento medido — PETER 3

Medición del 2026-10-01 en VicDev, Windows 11 `10.0.26200`, Python 3.12.10,
CPU AMD64 Family 23 Model 160 (4 núcleos físicos, 8 lógicos), 6.26 GB de RAM.
Se usó E5 CPU y el índice FAISS reconstruido para 773 chunks. La fixture de
análisis es sintética. Cada operación tuvo una ejecución de calentamiento y
20 repeticiones cronometradas con `time.perf_counter`.

| Componente, proceso caliente | N | Mediana | p95 |
|---|---:|---:|---:|
| Analysis V2 | 20 | 7.841 ms | 12.336 ms |
| Retrieval híbrido | 20 | 54.345 ms | 59.873 ms |
| Tutor estructurado con retrieval | 20 | 60.122 ms | 70.972 ms |
| Pipeline integral | 20 | 134.935 ms | 181.263 ms |

Los datos completos están en
`ai-service/data/evaluation/performance_results.json`. El pipeline se mide
mediante funciones de servicio; no incluye HTTP, red, descarga, carga inicial
de E5 ni LLM local. Estos resultados de un proceso local no son un SLA ni una
prueba de capacidad concurrente.

La prueba de inicio frío en un proceso nuevo midió **9.569 s** para iniciar
Python e importar FastAPI. No carga el modelo ni el índice. Su archivo es
`ai-service/data/evaluation/cold_start_results.json`. En el primer smoke con
`TestClient`, Knowledge tardó **135393.360 ms** al cargar E5 y el tutor
caliente **201.926 ms**. La repetición del verificador global, conservada en
`ai-service/data/evaluation/http_smoke_phase21.json`, midió **26034.964 ms**
para Knowledge y **75.381 ms** para Tutor; las seis rutas respondieron HTTP
200 con trace IDs. Ocho solicitudes RIASEC concurrentes con cuatro
trabajadores dieron 8/8 HTTP 200 (mediana **15.192 ms** en la repetición).
La variación de la carga fría es una limitación operativa de este host.
El smoke es en proceso y no prueba transporte de red.

Las latencias DEV/TEST se muestran sin filtrado en
`RETRIEVAL_EVALUATION.md`; ciertos p95 incluyen cargas frías excepcionales
de Windows. Los números del 2026-09-29 y los de Analysis V2 del 2026-09-30
son una línea base histórica de otros estados del corpus/entorno.
