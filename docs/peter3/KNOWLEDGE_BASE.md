# Base de conocimiento

La ingesta operativa se documenta en `OCR_PIPELINE.md`; gobierno y versiones en
`SOURCE_GOVERNANCE.md`. El corpus procesado se genera desde los snapshots del manifiesto y no
desde URLs vivas durante una consulta.

Estado: diseño, sin corpus indexado.

El conocimiento global deberá separar Ministerio de Educación, normativa BTH, currículo, universidades y mallas institucionales. Cada fuente conservará institución, título, URL, oficialidad, fechas, hash, versión, sección y página.

Hasta que exista un manifiesto revisado, `/api/v1/knowledge/search` devuelve 503 tipado y el tutor no inventa evidencia.
