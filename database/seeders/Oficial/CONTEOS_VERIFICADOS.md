# CONTEOS REALES POR GESTION

Comando completo probado: `php artisan migrate:fresh --seed` en la nueva base autorizada SAVPTIS3-OFICIAL, PostgreSQL 127.0.0.1:5432. La base anterior no fue modificada. Son datos sintéticos para estudio; docentes/personal y asignaciones originales preservados.

Los correos de acceso están normalizados a minúsculas para Fortify. Además de la ejecución integral, una prueba independiente creó las 612 cuentas y comprobó sus 612 contraseñas con el callback real de Fortify, en otra base aislada y con rollback. Véase CORREOS_Y_AUTENTICACION_FORTIFY.json.

El administrador oficial se conserva como PER_0001 / USU_0001. El seeder REALES/ADMINISTRADOR/AdministradorSistemaSeeder.php actualiza únicamente los datos confirmados por su titular, conserva la cuenta existente y hereda los 35 permisos del rol Administrador sin modificar el catálogo. Su autenticación se comprueba con el callback real de Fortify; la prueba aislada de dos ejecuciones confirmó idempotencia y ausencia de duplicados.

| Gestion | Nuevos | Inscritos | Tareas | Entregas | Notas tareas | Notas trimestrales | Asistencias | Cierres anuales |
|---|---:|---:|---:|---:|---:|---:|---:|---:|
| 2020 | 312 | 312 | 1388 | 15548 | 0 | 0 | 57161 | 312 |
| 2021 | 50 | 310 | 20633 | 233265 | 222337 | 11607 | 411203 | 310 |
| 2022 | 50 | 308 | 20671 | 229526 | 218685 | 11506 | 399241 | 308 |
| 2023 | 50 | 306 | 20903 | 228500 | 217461 | 11432 | 413082 | 306 |
| 2024 | 50 | 305 | 20578 | 228322 | 217157 | 11424 | 409495 | 305 |
| 2025 | 50 | 305 | 20963 | 229104 | 217990 | 11439 | 409100 | 305 |
| 2026 | 50 | 298 | 13599 | 149054 | 141840 | 7448 | 265926 | 0 |

612 estudiantes distintos; la suma anual cuenta reinscripciones de las mismas personas. 2020 no tiene notas; 2026 solo tiene primero y segundo trimestre, sin cierre anual.

## Todas las tablas

| Tabla | Filas |
|---|---:|
| asignatura | 14 |
| asistencia_clase | 185583 |
| asistencia_estudiante | 2365208 |
| aula | 0 |
| bitacora | 69585 |
| cache | 0 |
| cache_locks | 0 |
| calendario_evento | 7 |
| calificacion | 64856 |
| calificacion_tarea | 1235470 |
| capacitacion_docente | 0 |
| captura_fuente | 0 |
| cargo_institucional | 0 |
| carrera | 7 |
| clase_estudiante | 26768 |
| clase_virtual | 2513 |
| configuracion_calendario_gestion | 19 |
| cuestionario | 5860 |
| curso | 6 |
| docente | 48 |
| documento_inscripcion_estudiante | 8576 |
| documento_personal | 0 |
| documento_traslado | 23 |
| entrega_archivo | 1250330 |
| entrega_tarea | 1313319 |
| especialidad_tecnica | 11 |
| estado_asistencia | 5 |
| estudiante | 612 |
| estudiante_responsable | 612 |
| evidencia_academica | 0 |
| expediente_traslado | 23 |
| experiencia_docente | 0 |
| failed_jobs | 0 |
| formacion_docente | 0 |
| foro_archivo | 5266 |
| foro_clase | 5860 |
| foro_mensaje | 43060 |
| foro_tema | 5860 |
| gestion_academica | 7 |
| grupo_academico | 280 |
| horario | 904 |
| horario_bloque | 357 |
| horario_detalle | 27192 |
| inscripcion_estudiante | 2144 |
| inscripcion_vigencia | 2875 |
| institucion_procedencia | 4 |
| instrumento_orientacion | 2 |
| instrumento_pregunta | 60 |
| intento_cuestionario | 62816 |
| job_batches | 0 |
| jobs | 0 |
| material_clase | 6207 |
| migrations | 107 |
| model_has_permissions | 0 |
| model_has_roles | 663 |
| nota_traslado | 1470 |
| novedad_estudiante | 210 |
| oferta_academica | 8 |
| opcion_pregunta | 35160 |
| orientacion_actividades | 943 |
| orientacion_carreras_sugeridas | 0 |
| orientacion_ofertas_sugeridas | 0 |
| orientacion_preguntas | 60 |
| orientacion_respuestas | 28290 |
| orientacion_resultados | 943 |
| paralelo | 4 |
| password_reset_tokens | 0 |
| periodo_evaluacion | 3 |
| permissions | 49 |
| persona | 1280 |
| personal_access_tokens | 0 |
| personal_institucional | 56 |
| plan_asignatura | 2100 |
| plan_especialidad | 476 |
| plantilla_horaria | 42 |
| pregunta_cuestionario | 29300 |
| publicacion_clase | 6207 |
| recurso_fuente | 16 |
| regente_asignaciones | 0 |
| registro_actividad_clase | 68743 |
| reportes_generados | 0 |
| requisito_documento_cargo | 0 |
| respaldo_gestion_academica | 7 |
| respuesta_cuestionario | 314080 |
| respuesta_opcion | 125632 |
| resultado_anual | 1846 |
| retroalimentacion_archivo | 61670 |
| role_has_permissions | 128 |
| roles | 6 |
| seccion_clase | 6207 |
| sede_universidad | 5 |
| seguimiento_academico | 21 |
| sesion_academica | 185583 |
| sessions | 0 |
| tarea | 118735 |
| tarea_material | 118735 |
| tipo_documento_personal | 0 |
| tipo_vinculacion_estudiante | 3 |
| turno | 2 |
| universidad | 5 |
| user_status_logs | 0 |
| users | 668 |
| version_oferta_academica | 8 |
| vinculo_personal | 0 |

## Desglose anual completo

### 2020

| Entidad | Filas |
|---|---:|
| gestion_academica | 1 |
| grupo_academico | 40 |
| inscripcion_estudiante | 312 |
| plan_asignatura | 300 |
| plan_especialidad | 68 |
| horario | 88 |
| horario_detalle | 2472 |
| sesion_academica | 4397 |
| clase_virtual | 359 |
| calendario_evento | 1 |
| configuracion_calendario_gestion | 1 |
| respaldo_gestion_academica | 1 |
| expediente_traslado | 0 |
| documento_traslado | 0 |
| nota_traslado | 0 |
| orientacion_actividades | 0 |
| orientacion_respuestas | 0 |
| orientacion_resultados | 0 |
| orientacion_carreras_sugeridas | 0 |
| orientacion_ofertas_sugeridas | 0 |
| inscripcion_vigencia | 416 |
| calificacion | 0 |
| resultado_anual | 312 |
| documento_inscripcion_estudiante | 1248 |
| novedad_estudiante | 0 |
| seguimiento_academico | 0 |
| clase_estudiante | 3887 |
| seccion_clase | 347 |
| publicacion_clase | 347 |
| material_clase | 347 |
| tarea | 1388 |
| cuestionario | 0 |
| foro_clase | 0 |
| asistencia_clase | 4397 |
| registro_actividad_clase | 3887 |
| tarea_material | 1388 |
| entrega_tarea | 15548 |
| calificacion_tarea | 0 |
| entrega_archivo | 14860 |
| retroalimentacion_archivo | 0 |
| pregunta_cuestionario | 0 |
| opcion_pregunta | 0 |
| intento_cuestionario | 0 |
| respuesta_cuestionario | 0 |
| respuesta_opcion | 0 |
| foro_tema | 0 |
| foro_mensaje | 0 |
| foro_archivo | 0 |
| asistencia_estudiante | 57161 |
| plantilla_horaria | 6 |
| horario_bloque | 51 |
| estudiantes_nuevos | 312 |
| resultados_por_estado: EGRESADO | 52 |
| resultados_por_estado: PROMOVIDO | 260 |
| asistencias_por_estado: F | 2877 |
| asistencias_por_estado: P | 50775 |
| asistencias_por_estado: T | 3509 |
| entregas_por_estado: ENTREGADO | 12878 |
| entregas_por_estado: ENTREGADO_TARDE | 1982 |
| entregas_por_estado: PENDIENTE | 688 |

### 2021

| Entidad | Filas |
|---|---:|
| gestion_academica | 1 |
| grupo_academico | 40 |
| inscripcion_estudiante | 310 |
| plan_asignatura | 300 |
| plan_especialidad | 68 |
| horario | 136 |
| horario_detalle | 4120 |
| sesion_academica | 31820 |
| clase_virtual | 359 |
| calendario_evento | 1 |
| configuracion_calendario_gestion | 3 |
| respaldo_gestion_academica | 1 |
| expediente_traslado | 3 |
| documento_traslado | 3 |
| nota_traslado | 84 |
| orientacion_actividades | 157 |
| orientacion_respuestas | 4710 |
| orientacion_resultados | 157 |
| orientacion_carreras_sugeridas | 0 |
| orientacion_ofertas_sugeridas | 0 |
| inscripcion_vigencia | 414 |
| calificacion | 11607 |
| resultado_anual | 310 |
| documento_inscripcion_estudiante | 1240 |
| novedad_estudiante | 34 |
| seguimiento_academico | 0 |
| clase_estudiante | 3869 |
| seccion_clase | 1029 |
| publicacion_clase | 1029 |
| material_clase | 1029 |
| tarea | 20633 |
| cuestionario | 1029 |
| foro_clase | 1029 |
| asistencia_clase | 31820 |
| registro_actividad_clase | 11607 |
| tarea_material | 20633 |
| entrega_tarea | 233265 |
| calificacion_tarea | 222337 |
| entrega_archivo | 222337 |
| retroalimentacion_archivo | 11020 |
| pregunta_cuestionario | 5145 |
| opcion_pregunta | 6174 |
| intento_cuestionario | 11261 |
| respuesta_cuestionario | 56305 |
| respuesta_opcion | 22522 |
| foro_tema | 1029 |
| foro_mensaje | 7638 |
| foro_archivo | 939 |
| asistencia_estudiante | 411203 |
| plantilla_horaria | 6 |
| horario_bloque | 51 |
| estudiantes_nuevos | 50 |
| resultados_por_estado: EGRESADO | 52 |
| resultados_por_estado: PROMOVIDO | 258 |
| asistencias_por_estado: F | 20886 |
| asistencias_por_estado: L | 507 |
| asistencias_por_estado: P | 365485 |
| asistencias_por_estado: T | 24325 |
| entregas_por_estado: CALIFICADO | 222337 |
| entregas_por_estado: PENDIENTE | 10928 |

### 2022

| Entidad | Filas |
|---|---:|
| gestion_academica | 1 |
| grupo_academico | 40 |
| inscripcion_estudiante | 308 |
| plan_asignatura | 300 |
| plan_especialidad | 68 |
| horario | 136 |
| horario_detalle | 4120 |
| sesion_academica | 31209 |
| clase_virtual | 359 |
| calendario_evento | 1 |
| configuracion_calendario_gestion | 3 |
| respaldo_gestion_academica | 1 |
| expediente_traslado | 4 |
| documento_traslado | 4 |
| nota_traslado | 168 |
| orientacion_actividades | 155 |
| orientacion_respuestas | 4650 |
| orientacion_resultados | 155 |
| orientacion_carreras_sugeridas | 0 |
| orientacion_ofertas_sugeridas | 0 |
| inscripcion_vigencia | 413 |
| calificacion | 11506 |
| resultado_anual | 308 |
| documento_inscripcion_estudiante | 1232 |
| novedad_estudiante | 37 |
| seguimiento_academico | 21 |
| clase_estudiante | 3848 |
| seccion_clase | 1035 |
| publicacion_clase | 1035 |
| material_clase | 1035 |
| tarea | 20671 |
| cuestionario | 1035 |
| foro_clase | 1035 |
| asistencia_clase | 31209 |
| registro_actividad_clase | 11506 |
| tarea_material | 20671 |
| entrega_tarea | 229526 |
| calificacion_tarea | 218685 |
| entrega_archivo | 218685 |
| retroalimentacion_archivo | 10931 |
| pregunta_cuestionario | 5175 |
| opcion_pregunta | 6210 |
| intento_cuestionario | 11160 |
| respuesta_cuestionario | 55800 |
| respuesta_opcion | 22320 |
| foro_tema | 1035 |
| foro_mensaje | 7650 |
| foro_archivo | 939 |
| asistencia_estudiante | 399241 |
| plantilla_horaria | 6 |
| horario_bloque | 51 |
| estudiantes_nuevos | 50 |
| resultados_por_estado: EGRESADO | 49 |
| resultados_por_estado: PROMOVIDO | 238 |
| resultados_por_estado: RETENIDO | 18 |
| resultados_por_estado: RETIRADO | 3 |
| asistencias_por_estado: F | 20082 |
| asistencias_por_estado: L | 505 |
| asistencias_por_estado: P | 354789 |
| asistencias_por_estado: T | 23865 |
| entregas_por_estado: CALIFICADO | 218685 |
| entregas_por_estado: PENDIENTE | 10841 |

### 2023

| Entidad | Filas |
|---|---:|
| gestion_academica | 1 |
| grupo_academico | 40 |
| inscripcion_estudiante | 306 |
| plan_asignatura | 300 |
| plan_especialidad | 68 |
| horario | 136 |
| horario_detalle | 4120 |
| sesion_academica | 32497 |
| clase_virtual | 359 |
| calendario_evento | 1 |
| configuracion_calendario_gestion | 3 |
| respaldo_gestion_academica | 1 |
| expediente_traslado | 4 |
| documento_traslado | 4 |
| nota_traslado | 252 |
| orientacion_actividades | 158 |
| orientacion_respuestas | 4740 |
| orientacion_resultados | 158 |
| orientacion_carreras_sugeridas | 0 |
| orientacion_ofertas_sugeridas | 0 |
| inscripcion_vigencia | 409 |
| calificacion | 11432 |
| resultado_anual | 306 |
| documento_inscripcion_estudiante | 1224 |
| novedad_estudiante | 36 |
| seguimiento_academico | 0 |
| clase_estudiante | 3819 |
| seccion_clase | 1041 |
| publicacion_clase | 1041 |
| material_clase | 1041 |
| tarea | 20903 |
| cuestionario | 1041 |
| foro_clase | 1041 |
| asistencia_clase | 32497 |
| registro_actividad_clase | 11432 |
| tarea_material | 20903 |
| entrega_tarea | 228500 |
| calificacion_tarea | 217461 |
| entrega_archivo | 217461 |
| retroalimentacion_archivo | 10899 |
| pregunta_cuestionario | 5205 |
| opcion_pregunta | 6246 |
| intento_cuestionario | 11020 |
| respuesta_cuestionario | 55100 |
| respuesta_opcion | 22040 |
| foro_tema | 1041 |
| foro_mensaje | 7704 |
| foro_archivo | 963 |
| asistencia_estudiante | 413082 |
| plantilla_horaria | 6 |
| horario_bloque | 51 |
| estudiantes_nuevos | 50 |
| resultados_por_estado: EGRESADO | 52 |
| resultados_por_estado: PROMOVIDO | 252 |
| resultados_por_estado: RETIRADO | 2 |
| asistencias_por_estado: F | 21526 |
| asistencias_por_estado: L | 541 |
| asistencias_por_estado: P | 366227 |
| asistencias_por_estado: T | 24788 |
| entregas_por_estado: CALIFICADO | 217461 |
| entregas_por_estado: PENDIENTE | 11039 |

### 2024

| Entidad | Filas |
|---|---:|
| gestion_academica | 1 |
| grupo_academico | 40 |
| inscripcion_estudiante | 305 |
| plan_asignatura | 300 |
| plan_especialidad | 68 |
| horario | 136 |
| horario_detalle | 4120 |
| sesion_academica | 32158 |
| clase_virtual | 359 |
| calendario_evento | 1 |
| configuracion_calendario_gestion | 3 |
| respaldo_gestion_academica | 1 |
| expediente_traslado | 4 |
| documento_traslado | 4 |
| nota_traslado | 210 |
| orientacion_actividades | 158 |
| orientacion_respuestas | 4740 |
| orientacion_resultados | 158 |
| orientacion_carreras_sugeridas | 0 |
| orientacion_ofertas_sugeridas | 0 |
| inscripcion_vigencia | 412 |
| calificacion | 11424 |
| resultado_anual | 305 |
| documento_inscripcion_estudiante | 1220 |
| novedad_estudiante | 37 |
| seguimiento_academico | 0 |
| clase_estudiante | 3808 |
| seccion_clase | 1029 |
| publicacion_clase | 1029 |
| material_clase | 1029 |
| tarea | 20578 |
| cuestionario | 1029 |
| foro_clase | 1029 |
| asistencia_clase | 32158 |
| registro_actividad_clase | 11424 |
| tarea_material | 20578 |
| entrega_tarea | 228322 |
| calificacion_tarea | 217157 |
| entrega_archivo | 217157 |
| retroalimentacion_archivo | 10865 |
| pregunta_cuestionario | 5145 |
| opcion_pregunta | 6174 |
| intento_cuestionario | 11083 |
| respuesta_cuestionario | 55415 |
| respuesta_opcion | 22166 |
| foro_tema | 1029 |
| foro_mensaje | 7566 |
| foro_archivo | 900 |
| asistencia_estudiante | 409495 |
| plantilla_horaria | 6 |
| horario_bloque | 51 |
| estudiantes_nuevos | 50 |
| resultados_por_estado: PROMOVIDO | 255 |
| resultados_por_estado: EGRESADO | 50 |
| asistencias_por_estado: F | 21006 |
| asistencias_por_estado: L | 471 |
| asistencias_por_estado: P | 363652 |
| asistencias_por_estado: T | 24366 |
| entregas_por_estado: CALIFICADO | 217157 |
| entregas_por_estado: PENDIENTE | 11165 |

### 2025

| Entidad | Filas |
|---|---:|
| gestion_academica | 1 |
| grupo_academico | 40 |
| inscripcion_estudiante | 305 |
| plan_asignatura | 300 |
| plan_especialidad | 68 |
| horario | 136 |
| horario_detalle | 4120 |
| sesion_academica | 32114 |
| clase_virtual | 359 |
| calendario_evento | 1 |
| configuracion_calendario_gestion | 3 |
| respaldo_gestion_academica | 1 |
| expediente_traslado | 4 |
| documento_traslado | 4 |
| nota_traslado | 378 |
| orientacion_actividades | 157 |
| orientacion_respuestas | 4710 |
| orientacion_resultados | 157 |
| orientacion_carreras_sugeridas | 0 |
| orientacion_ofertas_sugeridas | 0 |
| inscripcion_vigencia | 413 |
| calificacion | 11439 |
| resultado_anual | 305 |
| documento_inscripcion_estudiante | 1220 |
| novedad_estudiante | 33 |
| seguimiento_academico | 0 |
| clase_estudiante | 3813 |
| seccion_clase | 1044 |
| publicacion_clase | 1044 |
| material_clase | 1044 |
| tarea | 20963 |
| cuestionario | 1044 |
| foro_clase | 1044 |
| asistencia_clase | 32114 |
| registro_actividad_clase | 11439 |
| tarea_material | 20963 |
| entrega_tarea | 229104 |
| calificacion_tarea | 217990 |
| entrega_archivo | 217990 |
| retroalimentacion_archivo | 10872 |
| pregunta_cuestionario | 5220 |
| opcion_pregunta | 6264 |
| intento_cuestionario | 11080 |
| respuesta_cuestionario | 55400 |
| respuesta_opcion | 22160 |
| foro_tema | 1044 |
| foro_mensaje | 7566 |
| foro_archivo | 903 |
| asistencia_estudiante | 409100 |
| plantilla_horaria | 6 |
| horario_bloque | 51 |
| estudiantes_nuevos | 50 |
| resultados_por_estado: PROMOVIDO | 248 |
| resultados_por_estado: EGRESADO | 57 |
| asistencias_por_estado: F | 21246 |
| asistencias_por_estado: L | 404 |
| asistencias_por_estado: P | 363225 |
| asistencias_por_estado: T | 24225 |
| entregas_por_estado: CALIFICADO | 217990 |
| entregas_por_estado: PENDIENTE | 11114 |

### 2026

| Entidad | Filas |
|---|---:|
| gestion_academica | 1 |
| grupo_academico | 40 |
| inscripcion_estudiante | 298 |
| plan_asignatura | 300 |
| plan_especialidad | 68 |
| horario | 136 |
| horario_detalle | 4120 |
| sesion_academica | 21388 |
| clase_virtual | 359 |
| calendario_evento | 1 |
| configuracion_calendario_gestion | 3 |
| respaldo_gestion_academica | 1 |
| expediente_traslado | 4 |
| documento_traslado | 4 |
| nota_traslado | 378 |
| orientacion_actividades | 158 |
| orientacion_respuestas | 4740 |
| orientacion_resultados | 158 |
| orientacion_carreras_sugeridas | 0 |
| orientacion_ofertas_sugeridas | 0 |
| inscripcion_vigencia | 398 |
| calificacion | 7448 |
| resultado_anual | 0 |
| documento_inscripcion_estudiante | 1192 |
| novedad_estudiante | 33 |
| seguimiento_academico | 0 |
| clase_estudiante | 3724 |
| seccion_clase | 682 |
| publicacion_clase | 682 |
| material_clase | 682 |
| tarea | 13599 |
| cuestionario | 682 |
| foro_clase | 682 |
| asistencia_clase | 21388 |
| registro_actividad_clase | 7448 |
| tarea_material | 13599 |
| entrega_tarea | 149054 |
| calificacion_tarea | 141840 |
| entrega_archivo | 141840 |
| retroalimentacion_archivo | 7083 |
| pregunta_cuestionario | 3410 |
| opcion_pregunta | 4092 |
| intento_cuestionario | 7212 |
| respuesta_cuestionario | 36060 |
| respuesta_opcion | 14424 |
| foro_tema | 682 |
| foro_mensaje | 4936 |
| foro_archivo | 622 |
| asistencia_estudiante | 265926 |
| plantilla_horaria | 6 |
| horario_bloque | 51 |
| estudiantes_nuevos | 50 |
| asistencias_por_estado: F | 13822 |
| asistencias_por_estado: L | 505 |
| asistencias_por_estado: P | 235728 |
| asistencias_por_estado: T | 15871 |
| entregas_por_estado: CALIFICADO | 141840 |
| entregas_por_estado: PENDIENTE | 7214 |

## Alcance verificado

Las FK/CHECK/EXCLUDE/triggers permanecieron activos. Los ZIP anuales contienen hechos y fichas. Las 612 fichas completas incluyen persona, cuenta sin secretos, responsables, todas las inscripciones/vigencias/notas/resultados/documentos/novedades/seguimientos y expedientes/notas de traslado. La actividad detallada de tareas/cuestionarios/foros/asistencia está en los ZIP. No se publican contraseñas ni la nómina privada docente.

La asistencia técnica requiere detalle real, según aprobación: la fuente contiene cero detalles PES; por tanto se conserva cero asistencia técnica. No se fabricaron bloques. Las clases técnicas sí tienen tareas, entregas, membresías y notas de sus estudiantes. No se inventaron capturas universitarias ni compatibilidades con carreras sin contrato especializado. Los ceros de tablas técnicas o evidencia representan ausencia de ese hecho, no una carga pendiente.
