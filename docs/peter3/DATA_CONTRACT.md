# Contrato de datos

## Minimización

El identificador universal es `student_id`, una referencia interna. No se aceptan nombre, CI, RUD, dirección, teléfono, correo ni datos familiares.

## Semántica de ausencia

- Campo omitido: no fue proporcionado.
- `null`: conocido como no disponible/no aplicable cuando el esquema lo permite.
- `0`: valor observado cero; nunca sustituto de ausencia.

## Académico

Cada observación tiene `subject`, `score` entre 0 y 100, `period` y `period_order`. La escala 0–100 es el contrato normalizado que Laravel deberá producir; Python no interpreta columnas internas.

## RIASEC

Los 30 reactivos son un conjunto versionado. El contrato V1 recibe enteros 0–4; la web/API oficial usa 1–5 y el consumidor debe convertir explícitamente con `official_web_value_to_internal`. No se admiten duplicados, faltantes, valores fraccionarios ni fuera de 0–4.

## Hash y trazabilidad

`input_hash` es SHA‑256 del request validado, serializado como JSON canónico con claves ordenadas. Permite reproducir el cálculo sin persistir datos personales adicionales.
