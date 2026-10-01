# Perfil analítico del estudiante

## Problema que resuelve

Consolidar evidencia autorizada y heterogénea en un snapshot versionado sin convertir ausencia en cero.

## Salida

Cada análisis contiene `student_ref`, período, fecha UTC, `profile_version`, `input_hash`, cobertura por componente, faltantes y estado de calidad.

El snapshot consolidado conserva además academia normalizada, asistencia, actividad de
aprendizaje, RIASEC bruto/normalizado/cobertura, especialidad BTH, competencias e intereses
declarados. Los componentes ausentes se expresan como `null` o faltantes; jamás se imputan.

## Regla inicial

**CONFIGURACIÓN EXPERIMENTAL.** Académico y vocacional son los componentes centrales. Ambos presentes → `COMPLETE`; uno presente → `PARTIAL`; ninguno → `INSUFFICIENT`. Asistencia, técnico, intereses declarados y actividad informan cobertura adicional.

## Privacidad

El snapshot no necesita identidad civil. Laravel debe enviar exclusivamente datos autorizados y normalizados.
