# Rendimiento medido

> Registro histórico del 2026-09-28. No se pudo repetir este benchmark en la auditoría del 2026-09-30 porque el entorno actual no tiene las dependencias Python instaladas. No usar estos valores como rendimiento actual ni como SLA.

Fecha: 2026-09-28. Comando: `.venv/Scripts/uv run python scripts/benchmark.py`.

## Entorno

- Windows 11 `10.0.26200`, Python `3.14.0`.
- CPU: 8 núcleos físicos / 16 lógicos.
- RAM total visible: 16,435,761,152 bytes.
- FastAPI `0.141.1`, Pydantic `2.13.5`, psutil `7.2.2`.
- Dataset: `complete_profile.json`, explícitamente sintético.
- Criterios: `core-criteria-0.1.0`.

## Resultados

| Medición | Resultado |
|---|---:|
| análisis directo, N | 1,000 |
| latencia media | 0.3740 ms |
| mediana | 0.3208 ms |
| p95 | 0.7061 ms |
| máximo | 1.2769 ms |
| importación fría de la app, N | 5 |
| importación fría media | 1,216.3399 ms |
| importación fría mínima/máxima | 1,119.1364 / 1,320.8621 ms |
| RSS antes/después | 34,967,552 / 34,668,544 bytes |

El delta RSS negativo (`-299,008` bytes) es ruido normal del proceso/gestor de memoria y no se interpreta como ahorro. Estas cifras son locales, no un SLA ni una medición de HTTP/red/concurrencia.
