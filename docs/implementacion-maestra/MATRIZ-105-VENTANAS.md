# 105 ventanas — estado conciliado

IDs, actor, nombre, tipo y ruta propuesta originales preservados. PARTIAL conserva trabajo interno y QA. Referencias completas: MATRIZ-CONCILIACION-105.csv.

| ID | Actor | Ventana original | Ruta adaptada | Estado | Migration | Support asociado |
|---|---|---|---|---|---|---|
| V001 | Administrador | Inicio | /admin | PARTIAL | MIG-003 solo campana persistente | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V002 | Administrador | Personas | /admin/gestion-personas | PARTIAL | NINGUNA NUEVA | PersonaInteligente |
| V003 | Administrador | Usuarios | /admin/gestion-usuarios | PARTIAL | NINGUNA NUEVA | InstitutionalRoleGovernance |
| V004 | Administrador | Personal institucional | /admin/personal-institucional | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V005 | Administrador | Estudiantes | /admin/gestion-estudiantes | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V006 | Administrador | Docentes | /admin/gestion-docentes | PARTIAL | NINGUNA NUEVA | DocenteInteligente |
| V007 | Administrador | Gestión académica | /admin/gestion-academica | PARTIAL | NINGUNA NUEVA | GestionAcademicaInteligente |
| V008 | Administrador | Cursos | /admin/gestion-cursos | PARTIAL | NINGUNA NUEVA | CursoInteligente |
| V009 | Administrador | Asignaturas | /admin/gestion-asignaturas | PARTIAL | NINGUNA NUEVA | AsignaturaInteligente |
| V010 | Administrador | Paralelos | /admin/gestion-paralelos | PARTIAL | NINGUNA NUEVA | ParaleloInteligente |
| V011 | Administrador | Turnos | /admin/gestion-turnos | PARTIAL | NINGUNA NUEVA | TurnoInteligente |
| V012 | Administrador | Inscripciones | /admin/gestion-inscripciones | PARTIAL | NINGUNA NUEVA | InscripcionAcademica |
| V013 | Administrador | Especialidades | /admin/especialidades-tecnicas | PARTIAL | NINGUNA NUEVA | EspecialidadTecnicaInteligente |
| V014 | Administrador | Planes de asignatura | /admin/planes-asignatura | PARTIAL | NINGUNA NUEVA | PlanAsignaturaInteligente |
| V015 | Administrador | Períodos de evaluación | /admin/periodo-evaluacion | PARTIAL | NINGUNA NUEVA | PeriodoEvaluacionInteligente |
| V016 | Administrador | Calendario | /admin/calendario | PARTIAL | MIG-005 para eventos institucionales; plazos existentes no requieren migration | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V017 | Administrador | Calificaciones institucionales | /admin/calificaciones | PARTIAL | NINGUNA NUEVA; MIG-006 integridad (ALTER, no ejecutada) | CalificacionInteligente |
| V018 | Administrador | Reportes académicos | /admin/reportes-academicos | PARTIAL | NINGUNA NUEVA | ReporteAcademicoInteligente |
| V019 | Administrador | Reportes administrativos | /admin/reportes-administrativos | PARTIAL | NINGUNA NUEVA | ReporteAdministrativoInteligente |
| V020 | Administrador | Roles y permisos | /admin/roles-permisos | PARTIAL | NINGUNA NUEVA | InstitutionalRoleGovernance |
| V021 | Administrador | Bitácora | /admin/bitacora | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V022 | Administrador | Kardex parámetros/auditoría | /admin/kardex-parametros | BLOCKED_EXTERNALLY_DB | MIG-001 | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V023 | Administrador | LMS institucional/supervisión | /admin/consultas/lms | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V024 | Administrador | Configuración LMS | /admin/lms-configuracion | BLOCKED_EXTERNALLY_INSTITUTIONAL | NINGUNA: definición pendiente | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V025 | Administrador | Configuración | /admin/configuracion | BLOCKED_EXTERNALLY_INSTITUTIONAL | NINGUNA: definición pendiente | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V026 | Administrador | Mi perfil | /user/profile | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V027 | Director | Inicio | /direccion | PARTIAL | MIG-003 solo campana persistente | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V028 | Director | Panorama institucional | /direccion | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V029 | Director | Gestión académica | /direccion/consultas/gestion | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V030 | Director | Rendimiento | /direccion/consultas/rendimiento | PARTIAL | NINGUNA NUEVA; MIG-006 integridad (ALTER, no ejecutada) | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V031 | Director | Cursos | /direccion/consultas/cursos | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V032 | Director | Estudiantes | /direccion/consultas/estudiantes | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V033 | Director | Docentes | /direccion/consultas/docentes | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V034 | Director | Asistencia del alcance | /direccion/consultas/asistencia | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V035 | Director | Kardex | /direccion/kardex | BLOCKED_EXTERNALLY_DB | MIG-001 | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V036 | Director | Prevención | /direccion/prevencion | BLOCKED_EXTERNALLY_INSTITUTIONAL | MIG-001/MIG-003 tras reglas aprobadas | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V037 | Director | Orientación | /direccion/consultas/orientacion | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V038 | Director | Seguimientos | /direccion/seguimientos | BLOCKED_EXTERNALLY_DB | MIG-001 | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V039 | Director | LMS institucional/supervisión | /direccion/consultas/lms | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V040 | Director | Reportes académicos | /direccion/consultas/reportes | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V041 | Director | Reportes administrativos | /direccion/consultas/reportes | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V042 | Director | Calendario | /direccion/calendario | PARTIAL | MIG-005 para eventos institucionales; plazos existentes no requieren migration | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V043 | Director | Mi perfil | /user/profile | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V044 | Secretaria | Inicio | /secretaria | PARTIAL | MIG-003 solo campana persistente | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V045 | Secretaria | Personas | /secretaria/personas | PARTIAL | NINGUNA NUEVA | PersonaInteligente |
| V046 | Secretaria | Estudiantes | /secretaria/estudiantes | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V047 | Secretaria | Inscripciones | /secretaria/inscripciones | PARTIAL | NINGUNA NUEVA | InscripcionAcademica |
| V048 | Secretaria | Documentación | /secretaria/documentacion | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V049 | Secretaria | Institución de procedencia | /secretaria/procedencia | PARTIAL | NINGUNA NUEVA | InstitucionProcedenciaInteligente |
| V050 | Secretaria | Tipo de vinculación | /secretaria/vinculacion | PARTIAL | NINGUNA NUEVA | TipoVinculacionEstudianteInteligente |
| V051 | Secretaria | Cursos | /secretaria/cursos | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V052 | Secretaria | Paralelos | /secretaria/paralelos | PARTIAL | NINGUNA NUEVA | ParaleloInteligente |
| V053 | Secretaria | Turnos | /secretaria/turnos | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V054 | Secretaria | Calendario | /secretaria/calendario | PARTIAL | MIG-005 para eventos institucionales; plazos existentes no requieren migration | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V055 | Secretaria | Kardex | /secretaria/kardex | BLOCKED_EXTERNALLY_DB | MIG-001 | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V056 | Secretaria | Reportes administrativos | /secretaria/reportes | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V057 | Secretaria | Mi perfil | /user/profile | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V058 | Regente | Inicio | /regencia | PARTIAL | MIG-003 solo campana persistente | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V059 | Regente | Mis grados | /regencia/mis-grados | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V060 | Regente | Cursos | /regencia/consultas/cursos | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V061 | Regente | Estudiantes | /regencia/consultas/estudiantes | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V062 | Regente | Asistencia del alcance | /regencia/consultas/asistencia | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V063 | Regente | Kardex | /regencia/kardex | BLOCKED_EXTERNALLY_DB | MIG-001 | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V064 | Regente | Seguimientos | /regencia/seguimientos | BLOCKED_EXTERNALLY_DB | MIG-001 | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V065 | Regente | LMS institucional/supervisión | /regencia/consultas/lms | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V066 | Regente | Alertas | /regencia/alertas | BLOCKED_EXTERNALLY_INSTITUTIONAL | MIG-001/MIG-003 tras reglas aprobadas | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V067 | Regente | Calendario | /regencia/calendario | PARTIAL | MIG-005 para eventos institucionales; plazos existentes no requieren migration | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V068 | Regente | Reportes académicos | /regencia/reportes | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V069 | Regente | Mi perfil | /user/profile | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V070 | Docente | Inicio | /docente | PARTIAL | MIG-003 solo campana persistente | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V071 | Docente | Mis cursos/materias | /docente/cursos | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V072 | Docente | Curso: resumen | /docente/cursos/{curso}?tab=resumen | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V073 | Docente | Curso: contenido/unidades | /docente/cursos/{curso}?tab=contenido | PARTIAL | MIG-002 para unidades; publicaciones existentes reutilizadas | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V074 | Docente | Curso: materiales | /docente/cursos/{curso}?tab=materiales | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V075 | Docente | Curso: actividades | /docente/cursos/{curso}?tab=actividades | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V076 | Docente | Curso: tareas | /docente/cursos/{curso}?tab=tareas | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V077 | Docente | Curso: entregas | /docente/cursos/{curso}?tab=entregas | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V078 | Docente | Curso: asistencia | /docente/cursos/{curso}?tab=asistencia | PARTIAL | NINGUNA NUEVA; MIG-006 integridad (ALTER, no ejecutada) | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V079 | Docente | Curso: calificaciones | /docente/cursos/{curso}?tab=calificaciones | PARTIAL | NINGUNA NUEVA; MIG-006 integridad (ALTER, no ejecutada) | CalificacionInteligente |
| V080 | Docente | Curso: estudiantes | /docente/cursos/{curso}?tab=estudiantes | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V081 | Docente | Calendario | /docente/calendario | PARTIAL | MIG-005 para eventos institucionales; plazos existentes no requieren migration | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V082 | Docente | Kardex | /docente/kardex | BLOCKED_EXTERNALLY_DB | MIG-001 | KardexInteligente |
| V083 | Docente | Seguimientos | /docente/seguimientos | BLOCKED_EXTERNALLY_DB | MIG-001 | KardexInteligente |
| V084 | Docente | Orientación | /aula-virtual/orientacion/seguimiento | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V085 | Docente | Reportes académicos | /aula-virtual/reportes | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V086 | Docente | Mi perfil | /user/profile | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V087 | Estudiante | Inicio | /estudiante | PARTIAL | MIG-003 solo campana persistente | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V088 | Estudiante | Mis cursos/materias | /estudiante/materias | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V089 | Estudiante | Curso: resumen | /estudiante/materias/{curso}?tab=resumen | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V090 | Estudiante | Curso: contenido/unidades | /estudiante/materias/{curso}?tab=contenido | PARTIAL | MIG-002 para unidades; publicaciones existentes reutilizadas | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V091 | Estudiante | Curso: materiales | /estudiante/materias/{curso}?tab=materiales | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V092 | Estudiante | Curso: tareas | /estudiante/materias/{curso}?tab=tareas | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V093 | Estudiante | Curso: entregas | /estudiante/materias/{curso}?tab=entregas | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V094 | Estudiante | Curso: calificaciones | /estudiante/materias/{curso}?tab=calificaciones | PARTIAL | NINGUNA NUEVA; MIG-006 integridad (ALTER, no ejecutada) | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V095 | Estudiante | Curso: asistencia | /estudiante/materias/{curso}?tab=asistencia | PARTIAL | NINGUNA NUEVA; MIG-006 integridad (ALTER, no ejecutada) | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V096 | Estudiante | Calendario | /estudiante/calendario | PARTIAL | MIG-005 para eventos institucionales; plazos existentes no requieren migration | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V097 | Estudiante | Mi progreso | /estudiante/progreso | PARTIAL | NINGUNA NUEVA; MIG-006 integridad (ALTER, no ejecutada) | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V098 | Estudiante | Seguimientos | /estudiante/seguimientos | BLOCKED_EXTERNALLY_DB | MIG-001 | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V099 | Estudiante | Mis intereses | /estudiante/intereses -> /aula-virtual/orientacion/explorador | PARTIAL | NINGUNA NUEVA; MIG-006 integridad (ALTER, no ejecutada) | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V100 | Estudiante | Mi futuro académico | /estudiante/futuro | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V101 | Estudiante | Mi preparación | /estudiante/preparacion | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V102 | Estudiante | Mi plan | /estudiante/plan | BLOCKED_EXTERNALLY_DB | MIG-004 | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V103 | Estudiante | Fuentes académicas | /estudiante/fuentes | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V104 | Estudiante | Asistente de estudio | /estudiante/asistente | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
| V105 | Estudiante | Mi perfil | /user/profile | PARTIAL | NINGUNA NUEVA | SIN ASOCIACION DIRECTA; revisar contexto de lectura o formulario hijo |
