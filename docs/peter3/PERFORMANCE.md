# Rendimiento medido — PETER 3

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
