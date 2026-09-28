# Arquitectura del tutor

El contrato admite `STRUCTURED` y `LOCAL_LLM`, pero el primer hito solo activa `STRUCTURED`. Sin índice o fuentes recuperadas, responde `insufficient_evidence=true`, lista vacía de fuentes y no genera contenido académico.

La evolución futura será: pregunta autorizada → retrieval Top‑K → resultados deterministas/contexto mínimo → proveedor de respuesta. Un LLM local podrá redactar o resumir, pero no modificar RIASEC, afinidad, preparación, ranking o brechas.
