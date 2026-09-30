# Evaluación del LLM local

Estado al 2026-09-29: `NOT_EVALUATED_RUNTIME_UNAVAILABLE`.

La configuración predeterminada mantiene `local_llm_enabled=false`. El endpoint previsto es
`http://127.0.0.1:8091` y el modelo configurado es
`mistralai/Ministral-3-3B-Instruct-2512-GGUF:Q4_K_M`. No se registran tokens/s, TTFT, RAM/VRAM ni
calidad del modelo porque no hubo un runtime local disponible y habilitado para medirlos.

La capa implementada usa temperatura 0, máximo 384 tokens, seed 20260928 y timeout de 45 s.
Valida esquema y citas; una indisponibilidad o respuesta inválida conserva la respuesta
estructurada segura. Ese fallback sí está probado, pero no sustituye la evaluación del modelo.

Para cerrar la evaluación pendiente se debe iniciar el runtime exacto, registrar versión y hash
del modelo, ejecutar los 13 escenarios más un conjunto ampliado y medir proceso frío/caliente,
TTFT, tokens/s, memoria, cumplimiento de esquema, soporte de citas y resistencia adversarial.
