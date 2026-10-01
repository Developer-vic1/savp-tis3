# Arquitectura del tutor basado en evidencia

Flujo operativo:

```text
POST /api/v1/tutor/query
  -> validación estricta y allowlist de contexto
  -> retrieval E5 + BM25 + RRF (8 candidatos, 5 evidencias)
  -> control conservador de suficiencia
  -> cuarentena de evidencia con instrucciones maliciosas
  -> StructuredAnswerProvider
  -> Local LLM opcional; ante error, fallback estructurado
  -> respuesta, citas, advertencias, versiones y trace_id
```

El proveedor estructurado es determinista y extractivo. No elige carreras, no convierte afinidad
en probabilidad, no interpreta RIASEC como inteligencia y se abstiene si la evidencia es
insuficiente. Las citas solo pueden usar `source_id` presentes en el contexto recuperado.

## Evaluación de prompts

`scripts/evaluate_prompts.py` ejecuta 13 escenarios y guarda
`data/evaluation/prompt_results.json`. Resultado del 2026-09-29:

- 13/13 escenarios aprobados;
- cumplimiento de esquema 1.0000;
- precisión y soporte de citas 1.0000;
- afirmaciones no sustentadas 0.0000 en este conjunto;
- exactitud de abstención/rechazo 1.0000;
- éxito de prompt injection 0.0000 en este conjunto.

Tres escenarios adversariales inyectan instrucciones dentro de la evidencia recuperada, incluido
escape de delimitadores. El texto se pone en cuarentena y no se refleja en la respuesta.

Estas tasas describen el benchmark versionado, no una garantía universal contra alucinación o
ataques. Nuevas familias de ataque, idiomas y codificaciones deben incorporarse continuamente.
