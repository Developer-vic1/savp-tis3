# Arquitectura del tutor de conocimiento

```text
POST /api/v1/tutor/query
  -> validación estricta y allowlist de contexto
  -> clasificación: saludo, capacidades o consulta de conocimiento
  -> BM25 local sobre corpus validado (cuando corresponde)
  -> filtrado por institución/carrera y diversidad de fuentes
  -> control conservador de suficiencia y cuarentena de evidencia
  -> respuesta estructurada con citas o petición clara de una fuente
```

El servicio no ejecuta modelos de IA. Los saludos y capacidades se definen en
`ai-service/data/tutor/conversation_catalog.json`; el resto de respuestas parte exclusivamente de
los documentos validados del corpus. El historial corto solo aclara una pregunta de seguimiento y
el contexto académico o estudiantil se limita a campos permitidos.

El tutor no elige carreras, no interpreta RIASEC como inteligencia y no transforma afinidad en una
probabilidad de éxito. Cuando no existe evidencia utilizable, responde que el conocimiento debe
agregarse o la consulta debe precisarse, sin completar información por conjetura.
