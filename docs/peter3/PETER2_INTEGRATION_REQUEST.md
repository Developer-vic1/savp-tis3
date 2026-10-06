# Solicitud de integración para PETER 2

## Necesidad

Construir, cuando PETER 2 lo autorice, un DTO normalizado y un cliente HTTP Laravel para el contrato v1. Aporte Ingenieril SAVP no realizará este cambio.

## Endpoint involucrado

`POST /api/v1/analysis` y, posteriormente, los endpoints de conocimiento/tutor.

## Datos esperados

- `student_id` interno, no identidad civil.
- notas 0–100 con asignatura y orden de período;
- asistencia agregada opcional;
- 30 respuestas O*NET® Mini‑IP completas cuando existan;
- especialidad/competencias verificables opcionales.

## Cambio sugerido y archivo a evaluar

PETER 2 debe decidir nombres/ubicaciones dentro de Laravel. Conceptualmente se requieren un Mapper/DTO, `AporteIngenierilClient`, timeout, header `X-SAVP-AI-Key` y fallback. No se prescribe un archivo concreto porque todavía no existe una convención confirmada en la base auditada.

## Motivo y riesgo

El mapeo impide acoplar Python a Eloquent y aplica minimización. El principal riesgo es reutilizar la “compatibilidad” Laravel actual, que deriva RIASEC de especialidad y promedio, mezclando afinidad con preparación.

## Ejemplo

Request mínimo:

```json
{"schema_version":"1.0","student_id":"EST-001"}
```

Respuesta degradada válida:

```json
{"schema_version":"1.0","status":"INSUFFICIENT","student_ref":"EST-001","academic_profile":null,"vocational_profile":null}
```

La respuesta real contiene además trazabilidad, cobertura, versiones, hash y advertencias según `API_CONTRACT.md`.
