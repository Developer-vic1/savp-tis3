# Rendimiento de estudiantes

La ventana académica reúne notas, respuestas de orientación y estudios conservados del aporte. Los gráficos permiten pasar del grupo a una carrera y a la evidencia de cada estudiante.

## Acceso y alcance

- Ruta principal: `/admin/rendimiento-estudiantes`. `/admin/lms-supervision` conserva su enlace anterior y presenta la misma ventana.
- Conserva el actor administrador y exige los permisos existentes de consulta institucional de cursos, calificaciones globales y estudiantes globales. Las respuestas, estudios y consultas a Python exigen además `orientacion.ver.institucional`.
- Selecciona inicialmente la gestión activa. Cada consulta exige una gestión: no suma inscripciones de años distintos. Permite consultar otra gestión explícitamente.
- Los filtros de grado, nivel, paralelo, turno, estudiante y seguimiento se aplican a la misma inscripción. El período recorta las notas mostradas; no recorta el perfil enviado a una vista previa del aporte.

## Fuentes y lectura de los gráficos

| Visualización | Respaldo | Interpretación |
|---|---|---|
| Cobertura de notas, RIASEC y estudios | `PanelResultadosAcademicos` | Coberturas independientes; finalizar RIASEC no implica tener un estudio. |
| Promedio por grado y materia | Notas vigentes o rectificadas de la inscripción | Promedio observado; las notas bajas indican seguimiento, no predicción de abandono. |
| Cobertura de seis tipos de evidencia | Último estudio del estudiante en la gestión | Disponible, parcial, insuficiente o ausente según la respuesta del aporte. |
| Carreras y estudiantes relacionados | Catálogo instalado de Python y perfiles del estudio | Relaciones académicas, técnicas y de intereses declarados por separado. |

Se muestran **todas** las carreras de `ai-service/data/catalog/careers.json`, sin limitarse a las primeras opciones. Se conserva su universidad y versión del catálogo. Las fuentes externas no elegibles se presentan solo como información y no contribuyen a las barras.

Para interpretar un estudio se comprueban el contrato v2, el HMAC del estudiante y la correspondencia de su trazabilidad. Solo se usa el último intento de la gestión: un intento nuevo pendiente no se reemplaza silenciosamente por un estudio anterior.

Una carrera incluida en `career_evidence_profiles` **no implica compatibilidad**. Las barras cuentan estudiantes una sola vez por carrera y requieren:

- Preparación observada: relaciones académicas observadas mayores que cero y evidencia académica no vacía.
- Formación técnica: estado disponible o parcial y evidencia técnica no vacía.
- Interés declarado: estado disponible o parcial y evidencia de intereses declarados no vacía.

Una referencia RIASEC disponible por sí sola no demuestra coincidencia de intereses con una carrera. No se calcula un porcentaje global, ranking, predicción de éxito ni decisión automática. Sin estudios interpretables se muestra pendiente; cero relaciones documentadas solo corresponde a una opción efectivamente revisada.

## Vista previa y prevención

`StudentOrientationService::institutionalContext` prepara el contexto de la inscripción autorizada sin suplantar al alumno. Reutiliza `OrientationReadiness`, exige el cuestionario público compatible y conserva los cuestionarios locales sin convertir escalas ni versiones automáticamente. La trayectoria institucional incluye solo gestiones anteriores a la seleccionada.

La revisión de preparación explica requisitos obligatorios, recomendaciones y formación que no aplica. La acción de análisis repite la comprobación en el servidor. `AporteIngenierilClient` valida y seudonimiza la solicitud antes de enviarla al servicio configurado.

**La vista previa no escribe ni reemplaza resultados institucionales.** El análisis guardado se obtiene del flujo existente de orientación. La comprobación de conexión usa el endpoint de salud, sin enviar un perfil estudiantil. Las notas modificadas después de un estudio se señalan como posible actualización pendiente.

## Verificación del cambio

En PowerShell, desde `C:\laragon\www\savp-reestructuracion`:

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit --filter 'RendimientoEstudiantilTest|PanelResultadosAcademicosTest|InstitutionalQueryInteractionTest|ClienteAporteIngenierilV2Test'
npm.cmd run build
```

La batería focalizada usa SQLite aislado y comprueba filtros, gestión, última actividad, referencias incorrectas, exclusión de fuentes externas, ausencia de compatibilidad implícita, permisos y vista previa sin escrituras. El 05/10/2026 pasaron 44 pruebas y 198 aserciones; la compilación de Vite terminó correctamente.

En el navegador se verificaron la ventana real, la selección de carrera con análisis pendiente, búsqueda sin resultados, ficha con notas y respuestas, comprobación de conexión y bloqueo explicado de un cuestionario incompatible. La vista móvil y el modo oscuro no mostraron desbordamiento horizontal. No se generó un estudio real: el registro revisado carecía del cuestionario público compatible.

La batería existente `StudentOrientationIntegrationTest` no pudo ejecutarse: su preparación intenta migrar SQLite, pero la migración canónica `2026_10_04_000001_permitir_plan_especialidad_en_clase_virtual` exige PostgreSQL. No representa una prueba completa de integración con Python. No ejecutar reconstrucciones ni seeders sobre `SAVPTIS3-OFICIAL` para resolverlo.

Referencias: [aporte y metodología](../sistema/04-APORTE-Y-METODOLOGIA.md), [recursos institucionales](../sistema/02-RECURSOS-Y-COMPONENTES.md), [datos y operación](../sistema/03-DATOS-Y-OPERACION.md).
