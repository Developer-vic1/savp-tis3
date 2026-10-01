# Contrato de integración PETER 2 ↔ PETER 3

Estado fase 2.1: contrato documentado, integración bloqueada por los dos índices FAISS `STALE`.
La segunda laptop debe cerrar los gates descritos en `PHASE21_WIP_CHECKPOINT.md` antes de uso real.

Versión de esquema V2: `2.0`. URL base local sugerida: `http://127.0.0.1:8001`. En despliegue, PETER 2 debe usar una URL interna configurable y TLS en el enlace que corresponda. PETER 2 representa preguntas y resultados; PETER 3 conserva el instrumento, valida las respuestas y calcula RIASEC.

| Endpoint | Uso |
|---|---|
| `GET /health` | Salud barata; sin autenticación ni carga del LLM. |
| `GET /api/v2/riasec/instrument` | Reactivos oficiales, escala pública 1–5, versión, atribución y límites. |
| `POST /api/v2/riasec/score` | Scoring de 30 respuestas públicas 1–5. |
| `POST /api/v2/analysis` | Perfil analítico y evidencia de carreras. |
| `POST /api/v1/knowledge/search` | Recuperación de fuentes. |
| `POST /api/v1/tutor/query` | Tutor estructurado. |
| `POST /api/v1/analysis` | `LEGACY_EXPERIMENTAL_BASELINE`; no usar para integración nueva. |

Todos los endpoints salvo `/health` usan `X-SAVP-AI-Key` cuando `SAVP_AI_API_KEY` está configurada. En `SAVP_AI_ENV=production`, la ausencia de clave configurada produce `503`; una clave de cliente ausente o incorrecta produce `401`. Solo el servidor PHP debe conocer la clave. El modo de desarrollo sin clave debe usarse únicamente en loopback.

## RIASEC

Consultar `/api/v2/riasec/instrument` al iniciar el cuestionario; renderizar `items` en el orden entregado y las cinco opciones de `response_scale`. Enviar la misma `instrument_version`:

```json
{"instrument_version":"2.0-es-2025","responses":[{"item_id":1,"value":1},{"item_id":2,"value":5}]}
```

El ejemplo anterior muestra la forma de los elementos; una solicitud válida contiene exactamente los 30 IDs, una vez cada uno. Respuesta abreviada para 30 respuestas de valor 1:

```json
{"schema_version":"2.0","instrument_version":"2.0-es-2025","scores":{"R":0,"I":0,"A":0,"S":0,"E":0,"C":0},"top_codes":["R","I","A","S","E","C"],"holland_code":"RIA","limitations":["RIASEC describe intereses; no mide aptitud, inteligencia ni capacidad."],"trace_id":"<uuid>"}
```

La respuesta real contiene tres limitaciones. Los valores públicos `1..5` se convierten a `0..4` antes de sumar cinco reactivos por dimensión; el rango de cada score es `0..20`. `top_codes` incluye todos los empates máximos. `holland_code` toma los tres primeros códigos del orden determinista `RIASEC` tras ordenar por puntaje. PETER 2 no recalcula estos campos.

`POST /api/v2/analysis` recibe `schema_version: "2.0"`, `student_id` pseudónimo y componentes opcionales `academic`, `attendance`, `vocational`, `technical`, `declared_interests`, `history` y `learning_activity`. `vocational` usa por ahora la **escala interna 0–4** y exige `instrument_id`, `instrument_version` y las 30 respuestas. El consumidor puede omitir `vocational` si solo posee el resultado del endpoint de scoring; no debe enviar un score derivado en su lugar. Ejemplo mínimo válido: `{"schema_version":"2.0","student_id":"EST-001"}`. La respuesta incluye `schema_version`, `trace_id`, `analysis_status`, `student_snapshot`, `career_evidence_profiles`, `sources_used` y `traceability`. No expresa probabilidad de éxito ni carrera ganadora. Consultar `/openapi.json` para el esquema de todos los campos.

`POST /api/v1/knowledge/search` acepta `{"schema_version":"1.0","query":"materias iniciales de Ingeniería Civil UCB","top_k":5,"official_only":true}`. `POST /api/v1/tutor/query` acepta `{"schema_version":"1.0","question":"¿Qué evidencia hay sobre las materias iniciales?"}`. Ambos dependen del índice local; puede responderse con abstención o `503` si no está disponible.

## Errores y operación

El formato común es `{"error":{"code":"SAVP_AI_INVALID_REQUEST","message":"...","trace_id":"<uuid>","details":[]}}`. La cabecera `X-Trace-Id` coincide con el cuerpo en respuestas normales y de error. Los códigos actuales incluyen `401` para clave inválida, `422` para contrato o instrumento inválidos, `500` para error inesperado y `503` para dependencia o configuración indisponible. `400`, `403`, `404` y `409` no son casos contractuales implementados para estos endpoints; no asumirlos como garantía. El servidor nunca debe enviar traceback al cliente.

Timeout recomendado inicial de PETER 2: 5 s para `/health` y RIASEC, 30 s para análisis, búsqueda y tutor estructurado, a ajustar con mediciones del despliegue. Reintentar una vez los `GET` y los `POST` de cálculo sin efectos persistentes ante timeout o `503`, con espera breve; no reintentar `401` ni `422`. Si FastAPI cae, PETER 2 debe mostrar indisponibilidad temporal, conservar localmente el formulario según su política de privacidad y permitir reintentar sin inventar resultados. Registrar `trace_id` y latencia, evitando nombre, CI, correo y respuestas completas.

El texto, orden y dimensión de los 30 reactivos se verificaron contra la aplicación oficial;
la comprensión y equivalencia cultural boliviana requieren validación local. Sus resultados son
exploratorios. La integración PHP real y sus pruebas de red quedan para PETER 2.
