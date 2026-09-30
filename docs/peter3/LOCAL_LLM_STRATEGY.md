# Estrategia de LLM Local y Resiliencia — SAVP-TIS3 (PETER 3)

## 1. Principio Arquitectónico Central
**"El LLM es una capa opcional de verbalización en lenguaje natural; nunca una dependencia obligatoria ni un árbitro decisor."**

El sistema SAVP-TIS3 está diseñado para operar con total garantía funcional y pedagógica en servidores locales bolivianos o entornos de bajos recursos donde no se disponga de aceleración por GPU para modelos de lenguaje masivos.

---

## 2. Niveles de Operación del Tutor

1. **Nivel 1 (Nuclear / Determinístico - `StructuredAnswerProvider`)**:
   - Siempre disponible sin costo computacional ni latencia.
   - Extrae afirmaciones estructuradas con citación directa de las fuentes recuperadas.
   - Aplica salvaguardas éticas y psicométricas de forma determinística por código.

2. **Nivel 2 (Verbalización Local - `LocalLlmProvider`)**:
   - Se activa únicamente si existe un runtime local activo configurado en `LOCAL_LLM_URL` (e.g. `http://127.0.0.1:8091`).
   - Genera respuestas fluidas en lenguaje natural siguiendo el esquema JSON estricto (`app/prompts/schemas.py`).
   - Obligado a operar con temperatura baja (`0.1`) para anclaje estricto.

---

## 3. Mecanismos de Contención y Fallback Automático

```
                    ┌────────────────────────────┐
                    │    Consulta al LLM Local   │
                    └─────────────┬──────────────┘
                                  │
                                  ▼
                    ┌────────────────────────────┐
                    │ ¿Runtime disponible?       │
                    │ (Conexión / Timeout < 30s) │
                    └──────┬──────────────┬──────┘
                           │              │
                        No │           Sí │
                           │              ▼
                           │    ┌────────────────────────────┐
                           │    │ ¿Esquema JSON válido?      │
                           │    │ ¿Citas [SOURCE_ID] válidas?│
                           │    │ ¿Sin inyecciones activadas?│
                           │    └──────┬──────────────┬──────┘
                           │           │              │
                           │        No │           Sí │
                           ▼           ▼              ▼
                    ┌──────────────────────┐   ┌─────────────────────┐
                    │ Fallback Automático  │   │ Entregar Respuesta  │
                    │ a StructuredProvider │   │ del LLM Local       │
                    └──────────────────────┘   └─────────────────────┘
```

---

## 4. Invariantes de Seguridad del LLM Local

1. **Aislamiento de Evidencia**: Todo texto recuperado entra bajo `--- BEGIN UNTRUSTED EVIDENCE ---`.
2. **Prohibición de Citas Inventadas**: Si el LLM genera una cita de una fuente que no estaba presente en la evidencia recuperada, se invalida la respuesta y se conmuta al proveedor estructurado.
3. **No Modificación de Scores**: El LLM no calcula notas, no altera rankings de carreras ni calcula compatibilidad.
