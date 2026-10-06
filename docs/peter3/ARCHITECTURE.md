# Arquitectura

## Límite de responsabilidad

```text
PostgreSQL → Laravel (auth, permisos, privacidad) → DTO JSON
                                                    ↓ HTTP
                                            FastAPI /api/v1
                                                    ↓
                      contratos → perfil → RIASEC / analítica descriptiva
                                                    ↓
                                       respuesta versionada y explicable
```

Aporte Ingenieril SAVP no conoce tablas, Eloquent, controladores, vistas, roles ni dashboards. Solo procesa el contrato público y datos mínimos ya autorizados.

## Capas

- `app/api/v1`: transporte HTTP y códigos de estado.
- `app/contracts`: esquemas de entrada, salida y errores.
- `app/riasec`: instrumento versionado, validación, scoring e interpretación.
- `app/learning_analytics`: cálculos descriptivos puros.
- `app/domain`: snapshot, cobertura, hash y orquestación.
- `data/instruments`: artefactos científicos/licenciados inmutables y versionados.
- `data/fixtures`: casos sintéticos explícitos.
- `tests`: unidad, contrato e integración local.

## Flujo de análisis

1. Pydantic valida forma, rangos y tipos.
2. Se valida `schema_version`.
3. Se genera hash SHA‑256 de JSON canónico.
4. Se calcula RIASEC únicamente si el instrumento está completo.
5. Se calcula analítica académica solo con notas presentes; faltantes quedan `null`.
6. Se construye cobertura, estado, evidencias y advertencias.
7. Se devuelve `trace_id` y versiones de motor/perfil/criterio/instrumento.

## Resiliencia

- 200: análisis procesado, incluso `PARTIAL` o `INSUFFICIENT`.
- 422: contrato o instrumento inválido.
- 503: dependencia interna no disponible.
- 500: error inesperado, sin traceback al cliente.

## Evolución controlada

Afinidad, preparación, ranking, conocimiento, OCR, embeddings y LLM son módulos posteriores. Ningún LLM podrá alterar scores deterministas.
