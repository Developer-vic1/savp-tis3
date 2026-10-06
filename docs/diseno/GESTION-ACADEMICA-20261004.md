# Gestión académica y calendario institucional

Actualización visual del 4 de octubre de 2026 en `C:\laragon\www\savp-reestructuracion`, rama `Fusion_Sistema`. Trabajo sobre el checkout compartido; se conservaron los cambios de otros módulos y chats. Sin migraciones, seeders, creación de registros ni modificaciones de datos institucionales durante la revisión.

## Pantallas y recorrido

- `/admin/gestion-academica`: consulta anual, estructura, trimestres, inscripciones, seguimiento, comparativas, documentación, respaldos y revisión de cierre.
- `/admin/calendario?gestion=…`: agenda, fuentes y simulador de impacto. El acceso desde el expediente y el regreso conservan la gestión seleccionada en la misma pestaña.
- Cabecera compacta, indicadores de la selección y navegación por procesos. Colores, superficies, iconos y temas utilizan los recursos existentes de SAVP.
- La distribución proporcional usa el mosaico compartido, ampliado con valor y unidad configurables. Su configuración predeterminada de horas en Docentes se conserva. Seleccionar un curso muestra sus paralelos.
- La línea de trayectoria representa incorporación acumulada de inscripciones según sus fechas reales; no representa calificaciones, promoción ni cambios de curso.
- Alertas incluye mapa por apartado, causa y acceso para revisar. Una inconsistencia documental no establece riesgo académico ni responsabilidad de una persona.
- Las descargas CSV reúnen resumen, calendario y/o seguimiento consultados. No sustituyen un respaldo de base de datos ni los informes PDF institucionales. El texto exportado se protege frente a interpretación como fórmulas.

## Support y fuentes

Se localizó el analista anterior en `C:\laragon\www\savp-tis3\app\Support\Academico\CalendarioAcademicoInteligente.php`. El controlador actual mostraba un calendario mínimo y el calendario institucional dependía de una opción deshabilitada. Se recuperó la simulación mediante un adaptador de lectura, sin trasladar operaciones antiguas de confirmación, suspensión, recuperación o escritura.

`PanelGestionAcademica` consulta registros de la gestión, adapta relaciones actuales/legadas y presenta periodos, agenda, distribución, movimientos y referencias. `CalendarioAcademicoInteligente` conserva el contrato `puede_continuar`, `bloqueos`, `advertencias`, `impacto`.

Fuentes oficiales revisadas el 04/10/2026:

- [RM 0001/2026, Educación Regular](https://www.minedu.gob.bo/files/documentos-normativos/resoluciones-ministeriales/1_RM_0001_EDUCACIN_REGULAR.pdf): artículo 3, tres trimestres y 200 días efectivos de trabajo curricular.
- [Comunicado DGTHSO-026/2026](https://www.mintrabajo.gob.bo/wp-content/uploads/2026/05/COMUNICADO-DGTHSO-026-2026.pdf): 5 de junio.
- [Comunicado DGTHSO-029/2026](https://mintrabajo.gob.bo/wp-content/uploads/2026/06/COMUNICADO-DGTHSO-029-2026.pdf): traslado al 22 de junio.
- [MTEPS, feriado adicional del 7 de agosto](https://mintrabajo.gob.bo/index.php/nota_prensa/la-paz-22/): referencia al DS 5521.

Las referencias de 2026 no acreditan otros años. Los eventos faltantes se muestran como revisión pendiente y no se incorporan automáticamente. Los días calculados son una estimación del calendario registrado, no una certificación ministerial. Las suspensiones parciales o de grupos concretos no se restan como días generales sin clases.

Completar trimestres requiere uno de los órdenes 1–3 faltante, normativa contrastada y planificación inicial. La ventana noviembre/diciembre para preparar una gestión posterior se identifica como flujo institucional existente, no como disposición ministerial. Se hizo coherente su bloqueo de interfaz con la validación independiente del Support. Las fechas y la malla sugeridas siguen siendo propuestas; la copia de estructura no dispone de una operación validada y permanece bloqueada.

## Alcance del analista

- Rangos dentro de la gestión, en orden y de hasta 60 días inclusive; motivo requerido y longitud controlada.
- Estima sesiones del horario semanal y horas reloj según bloques de clase, fechas de plantilla, días de semana y grupos. Señala coincidencias con eventos existentes.
- Presenta docentes y grupos vinculados, tareas con fecha límite y evaluaciones registradas dentro del rango.
- Separa estudiantes con vigencias históricas de inscripciones actuales. Una tabla de vigencias vacía se representa como dato histórico no disponible, nunca como cero estudiantes.
- La simulación no decide ni confirma suspensiones, no cambia asistencia, notas o tareas y no escribe en bitácora. El motivo explica el caso que se está consultando. Las acciones institucionales existentes conservan sus validaciones y auditoría.
- Permisos y actor administrador se validan en las consultas y acciones del nuevo calendario.

## Verificación realizada

Sin crear ni ejecutar pruebas automatizadas, conforme al encargo. Se comprobó sintaxis PHP de los ocho archivos afectados, compilación Blade de los seis paneles y `npm.cmd run build` (91 módulos, compilación correcta).

Revisión en el navegador integrado con los registros existentes:

- Selección 2026: 600 inscripciones, 300 planes de asignatura, tres trimestres y 12 eventos. Fechas de trimestres consultadas de su configuración.
- Recorrido al Calendario y regreso con la gestión conservada; agenda, referencias y analista separados.
- Simulación del 22/06/2026: 155 sesiones semanales, 103,3 horas reloj, 24 grupos, 27 docentes y 600 estudiantes con inscripción actual en esos grupos. Vigencias históricas no disponibles. Tareas y evaluaciones consultadas: cero.
- Trayectoria de incorporaciones: cinco fechas del 2 al 6 de febrero, 120 inscripciones cada día; acumulado de 600.
- Mosaico de seis cursos, selección funcional de los cuatro paralelos del primer curso; composición de estados y aviso de una sola gestión para comparar.
- Cinco avisos agrupados en el mapa: cuatro de calendario/documentación y uno de seguimiento/vigencias.
- Apertura del formulario sin guardar: preparación 2027 bloqueada en octubre, botón Guardar deshabilitado y mensaje del Support coherente con el bloqueo.
- Tema oscuro y viewport estrecho: sin desbordamiento horizontal en un ancho CSS efectivo de 546 px. No se afirma emulación completa de teléfono ni cobertura de todos los tamaños/dispositivos.
- Descarga: respuesta HTTP 200 de Livewire con efecto de descarga `SAVP-gestion-2026-resumen.csv`, contenido CSV y tipo `text/csv`. El navegador integrado no entregó un evento de archivo descargado a su API; se confirmó el contenido entregado por el servidor, no el guardado en un navegador externo.

Se corrigió un error de compilación Blade en el filtro de seguimiento que ocultaba las comparativas. Los datos sin registros conservan estados vacíos explicados. No se ejecutaron creación, activación, cierre, respaldos ni otras operaciones institucionales durante QA.

Capturas en `output/diseno/`: `gestion-academica-20261004.png` y `calendario-impacto-20261004.png`.
