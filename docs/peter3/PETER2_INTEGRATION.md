# Integración futura con PETER 2

## Dependencia para PETER 2

- Necesidad: acordar DTO JSON y semántica de cada campo.
- Endpoint: `POST /api/v1/analysis`.
- Datos esperados: referencia interna, notas 0–100 con período ordenado, asistencia agregada y respuestas RIASEC completas cuando existan.
- Cambio sugerido: crear en Laravel un mapper/DTO y cliente HTTP desacoplado, con autorización previa, timeout y fallback.
- Archivo a evaluar: PETER 2 decidirá ubicación; PETER 3 no prescribe ni modifica archivos Laravel.
- Motivo: impedir dependencia de tablas/Eloquent y minimizar datos.
- Riesgo: mezclar el reporte RIASEC existente con el nuevo resultado volvería a confundir especialidad/notas con intereses.

## Ejemplo mínimo

```json
{"schema_version":"1.0","student_id":"EST-001"}
```

## Fallback esperado

Ante conexión rechazada, timeout o 503, Laravel muestra un estado temporal sin producir HTTP 500 ni sustituir resultados con datos inventados.
