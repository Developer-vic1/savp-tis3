# Resultados y seguimiento institucional

Ruta: `/admin/calificaciones`. Panel de consulta del administrador; no registra, rectifica ni anula notas. La escritura permanece en el flujo docente y GradeService. No se cambian permisos ni rutas de otros actores.

## Conexiones implementadas

| Información | Fuente canónica | Unión y criterio |
| --- | --- | --- |
| Estudiante, grado, paralelo y gestión | inscripción_estudiante → estudiante/persona, curso, paralelo, gestión_académica | cod_ins; se excluye ANULADA. Gestión activa inicial y selección explícita de históricos. Grado de la inscripción anual. |
| Notas y evolución | calificación → período_evaluación, plan_asignatura/asignatura o plan_especialidad/especialidad | cod_ins; VIGENTE y RECTIFICADA; período real del catálogo. ANULADA se excluye. |
| Riesgo académico descriptivo | notas observadas | Un estudiante con alguna nota ≤ 50 se cuenta una vez, independientemente del número de notas bajas. No se infiere reprobación anual. |
| Promoción / retención oficial | resultado_anual | cod_ins + est_ran VIGENTE; PROMOVIDO/EGRESADO y RETENIDO respectivamente. La ausencia de cierre no significa aprobación. |
| Continuidad escolar | inscripción_estudiante | RETIRADA / inscritos. Un retiro no demuestra deserción; no hay diagnóstico automático. |
| RIASEC local | orientación_actividades → orientación_resultados/respuestas/preguntas e instrumento_pregunta | cod_est + cod_gea; último intento en agregados, todos los intentos en ficha. |
| RIASEC del servicio Python | orientación_actividades.riasec_score y riasec_public | Perfil Holland, puntuaciones y respuestas guardadas. Cuando no existe enunciado local, se conserva identificador/versión del ítem; no se inventa su texto. |
| Estudio del aporte | orientación_actividades.analysis_snapshot + analysis_completed_at | Resultado persistido: relaciones de intereses, preparación observada, refuerzos, cobertura y limitaciones. Su fecha no se reemplaza por la fecha de consulta. |
| Asistencia, actividades e historia usadas por el aporte | student_snapshot del estudio guardado | Se indica disponibilidad de evidencia; no se transforma un dato de asistencia o tareas en un diagnóstico de compromiso o responsabilidad. |
| Reporte masivo | consulta filtrada del panel | CSV por estudiante: gestión, grado, notas, evidencia RIASEC/estudio, resultado anual, inscripción y marca sintética. Respeta permisos y protege celdas contra fórmulas. |

## Permisos y separación de dominios

`Calificaciones` y actor Administrador activo habilitan el panel. `orientacion.ver.institucional` habilita resultados, respuestas y análisis de orientación. Sin ese permiso se muestra acceso restringido y no se exportan valores de orientación. El estudiante consultado debe pertenecer al conjunto filtrado antes de abrir su ficha.

La ficha carga los históricos del estudiante seleccionado para comparar gestiones; los indicadores iniciales no mezclan gestiones. Los promedios generales son promedios individuales; la evolución por período resume notas observadas y especifica cobertura. La ficha compara asignaciones compartidas entre el primer y el último período disponible del mismo año; no atribuye esa variación al aporte.

## Generación del estudio: conexión existente y límites encontrados

`StudentOrientationService` construye el contexto estudiantil con notas actuales/históricas, RIASEC público, especialidad técnica y, cuando existe evidencia suficiente, asistencia y actividad de tareas. `OrientationReadiness` comprueba requisitos; `AporteIngenierilClient::analysisV2` llama al servicio Python, valida su contrato y el resultado se guarda en la actividad. Este panel **solo lee** ese resultado: abrirlo o filtrar no genera análisis ni envía información al servicio.

La generación actual acepta el RIASEC público validado (`riasec_public` + `riasec_score`); un resultado local o legado no se convierte automáticamente a ese instrumento. El panel sí muestra ambos formatos de resultados. Para una futura generación masiva hace falta un adaptador validado por versión de instrumento, autorización específica, ejecución en cola y conservación de trazabilidad/errores; no debe dispararse desde render().

Punto pendiente detectado en el servicio generador: la selección de RIASEC público busca el último del estudiante sin filtrar gestión y la deduplicación usa su hash global. Antes de habilitar regeneración masiva, debe definirse cómo conservar intereses históricos sin escribir un análisis de notas actuales sobre una actividad de otra gestión. El panel evita esa mezcla mediante cod_est + cod_gea. No se modificó el servicio generador en este cambio.

No existe en este panel un instrumento consolidado que evalúe compromisos/responsabilidad ni un resultado canónico de deserción confirmada. Esos resultados no se deducen de notas bajas o retiros. Los datos marcados SINTETICO en obs_cal se cuentan y advierten como registros de validación, no como actas oficiales. No se crearon datos ni se ejecutaron migraciones institucionales.

## Verificación

Desde `C:\laragon\www\savp-reestructuracion`, PowerShell:

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit tests/Feature/PanelResultadosAcademicosTest.php
php artisan view:cache
npm.cmd run build
```

Las pruebas crean tablas mínimas únicamente en SQLite `:memory:`: conteo sin duplicar notas, gestión/período, estado anual, intento actual, permisos, períodos ausentes, marcas sintéticas, perfiles locales/Python, lectura del estudio y bloqueo de mutaciones. No prueban generación Python en vivo.
