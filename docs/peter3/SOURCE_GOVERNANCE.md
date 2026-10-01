# Gobernanza de fuentes

> Estado auditado el 2026-09-30: 11 snapshots registrados; cuatro HTML UCB no coinciden con el SHA-256 del registro. La integridad de fuentes está en FAIL hasta recuperar su procedencia. Véase `PETER3_FINAL_AUDIT.md`.

## Problema, evidencia y componente

La orientación pierde trazabilidad si una URL cambia, un PDF se reemplaza silenciosamente o
una inferencia manual se presenta como texto oficial. Por ello, cada snapshot oficial se
preserva bajo `ai-service/data/raw/` y su identidad se registra en
`ai-service/data/sources/sources.json`.

El lote inicial contiene fuentes del Ministerio de Educación, UCB La Paz y UMSA. No usa
Wikipedia, redes sociales ni blogs.

## Contrato de fuente

Cada registro contiene obligatoriamente `source_id`, `institution`, `title`, `source_type`,
`url`, `official`, `publication_date`, `retrieved_at`, `valid_from`, `valid_until`,
`document_hash`, `section`, `page`, `version` y `status`. `local_path` vincula el snapshot.
Una nueva descarga con hash distinto crea una nueva versión; nunca sobrescribe en silencio la
evidencia anterior.

## Pruebas

El validador de catálogo comprobará esquema, unicidad, ruta local, SHA-256 y referencias.
La ingesta comprobará además que cada chunk herede fuente, institución, versión, sección o
página y hash.

## Prioridad

1. Ministerio de Educación de Bolivia y normativa oficial BTH.
2. Currículos/programas oficiales.
3. Portales y documentos oficiales de universidades.
4. Documentos institucionales expresamente autorizados.

## Admisión

Una fuente requiere identidad institucional verificable, URL original, fecha de recuperación, hash y revisión de licencia. Las versiones antiguas no se sobrescriben. Wikipedia, redes sociales, agregadores y blogs no son fuentes primarias.

## Privacidad

El corpus global nunca incluye notas, asistencia, respuestas RIASEC ni documentos privados del estudiante.
