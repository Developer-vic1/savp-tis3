# BASE DE CONOCIMIENTO DE SOPORTES INTELIGENTES (SAVP)

Este documento centraliza las reglas de negocio, normativas, pedagógicas, institucionales y de integridad para el Sistema Académico y Vocacional Productivo (SAVP), enfocado exclusivamente en la **Educación Secundaria Comunitaria Productiva** (1.º a 6.º de Secundaria) en el Estado Plurinacional de Bolivia.

---

## Tipos de Reglas y Jerarquía de Autoridad

1. **INTEGRIDAD**: Regla objetiva e inviolable derivada del modelo relacional, integridad referencial y consistencia de datos (BD/Backend). Siempre deriva en **BLOQUEO** si se transgrede.
2. **NORMATIVA**: Regla respaldada por normativa educativa oficial boliviana (Resolución Ministerial N.º 0001/2026, RM 0190/2024 de Evaluación, Ley 070 Avelino Siñani - Elizardo Pérez y afines). Comportamiento según el alcance (Bloqueo si es prohibición legal, Advertencia si admite excepción fundamentada).
3. **INSTITUCIONAL**: Regla configurable propia de la unidad educativa (capacidad de aulas, turnos, horarios, ponderaciones de entrega). Comportamiento configurable (Advertencia o Bloqueo según parámetro).
4. **PEDAGOGICA**: Criterio educativo y didáctico respaldado (carga de tareas semanales, evaluación continua, retroalimentación). Genera **ADVERTENCIA** o **SUGERENCIA**, nunca bloqueo automático.
5. **ESTADISTICA**: Hallazgo analítico derivado de tendencias históricas (variación de calificaciones, patrones de asistencia). Genera **ADVERTENCIA**, nunca altera datos ni bloquea de forma autónoma.
6. **RECOMENDACION**: Sugerencia contextual para mejorar la toma de decisiones del usuario. Genera **SUGERENCIA**.

---

## Catálogo de Códigos de Hallazgo y Reglas

### 1. Personas y Usuarios

| Código | Nombre | Soporte | Tipo | Nivel / Comportamiento | Descripción y Excepciones | Fuente / Vigencia |
|---|---|---|---|---|---|---|
| `PER_CI_DUPLICADO` | Cédula de Identidad Duplicada | `PersonaInteligente` | INTEGRIDAD | CRÍTICO / **BLOQUEO** | No se permite registrar dos personas con el mismo número y complemento de CI. Excepción: si se trata de la misma persona en actualización. | SEGIP / Ley 070 (Vigente 2026) |
| `PER_CORREO_DUPLICADO` | Correo Electrónico Duplicado | `PersonaInteligente` | INTEGRIDAD | CRÍTICO / **BLOQUEO** | Dos personas activas no pueden compartir el mismo correo institucional o de contacto principal. | Estándar de autenticación y seguridad SAVP |
| `PER_HOMONIMO_POSIBLE` | Posible Homonimia Detectada | `PersonaInteligente` | ESTADÍSTICA | MEDIO / **ADVERTENCIA** | Coincidencia fonética o exacta en nombres y apellidos pero con diferente CI. Requiere verificación visual sin bloquear el registro. | Procedimiento SERECI / SEGIP |
| `PER_EDAD_ATIPICA_SEC` | Edad Atípica para Secundaria | `PersonaInteligente` | PEDAGÓGICA | MEDIO / **ADVERTENCIA** | Edad fuera del rango estándar (11 a 19 años) para secundaria regular. Excepción: casos de rezago escolar o educación extraordinaria; no bloquea. | RM 0001/2026 Art. 12 |
| `USR_SIN_ROL` | Usuario sin Rol Asignado | `UsuarioInteligente` | INTEGRIDAD | ALTO / **BLOQUEO** | Un usuario no puede operar en el sistema sin al menos un rol Spatie asignado. | Arquitectura RBAC SAVP |
| `USR_PERSONA_INACTIVA` | Usuario de Persona Inactiva | `UsuarioInteligente` | INTEGRIDAD | ALTO / **BLOQUEO** | No se puede activar o autenticar un usuario cuya persona base esté en estado `INACTIVO`. | Integridad SAVP |
| `USR_ROL_INCOMPATIBLE` | Rol Incompatible con Registro | `UsuarioInteligente` | NORMATIVA | MEDIO / **ADVERTENCIA** | Asignar rol `Docente` o `Estudiante` a un usuario sin registro correspondiente en las tablas especializadas. | Manual de Funciones y Roles SAVP |
| `USR_DESACTIVACION_IMPACTO` | Impacto de Desactivación | `UsuarioInteligente` | INSTITUCIONAL | MEDIO / **ADVERTENCIA** | Desactivar un usuario activo con clases, tutorías o tareas pendientes. | Procedimiento de Gestión de Accesos |

---

### 2. Comunidad Educativa (Estudiantes, Docentes, Personal)

| Código | Nombre | Soporte | Tipo | Nivel / Comportamiento | Descripción y Excepciones | Fuente / Vigencia |
|---|---|---|---|---|---|---|
| `EST_RUDE_DUPLICADO` | Código RUDE Duplicado | `EstudianteInteligente` | INTEGRIDAD | CRÍTICO / **BLOQUEO** | El Registro Único de Estudiantes (RUDE) debe ser único a nivel nacional por estudiante. | Sistema de Información Educativa (SIE) MinEdu |
| `EST_DOC_PENDIENTE` | Documentación Pendiente | `EstudianteInteligente` | NORMATIVA | MEDIO / **ADVERTENCIA** | Falta certificado de nacimiento o libreta previa. Permite inscripción condicional según plazo normativo (hasta 30 días). | RM 0001/2026 Art. 15 |
| `DOC_CHOQUE_HORARIO` | Conflicto Horario Docente | `DocenteInteligente` | INTEGRIDAD | CRÍTICO / **BLOQUEO** | El docente tiene asignada otra clase en el mismo día, turno y período horario. | Carga Horaria y Reglamento del Escalafón |
| `DOC_SOBRECARGA_HORARIA` | Sobrecarga Horaria Semanal | `DocenteInteligente` | NORMATIVA | MEDIO / **ADVERTENCIA** | El total de horas supera el límite semanal institucional/normativo (e.g. 120 hrs/mes). | Techo presupuestario MinEdu |

---

### 3. Gestión Académica, Cursos, Paralelos y Turnos

| Código | Nombre | Soporte | Tipo | Nivel / Comportamiento | Descripción y Excepciones | Fuente / Vigencia |
|---|---|---|---|---|---|---|
| `GES_CIERRE_CALIF_PEND` | Cierre con Calificaciones Pendientes | `GestionAcademicaInteligente` | INTEGRIDAD | CRÍTICO / **BLOQUEO** | No se puede consolidar y cerrar la gestión si existen períodos o asignaturas sin calificaciones. | RM 0190/2024 Reglamento de Evaluación |
| `PAR_CAPACIDAD_ALTA` | Paralelo Próximo a Capacidad Máxima | `ParaleloInteligente` | INSTITUCIONAL | MEDIO / **ADVERTENCIA** | Paralelo alcanza el 90% o más de su cupo máximo sugerido (habitualmente 30-35 estudiantes). | RM 0001/2026 Art. 9 |
| `PAR_CAPACIDAD_EXCEDIDA` | Cupo Máximo Excedido | `ParaleloInteligente` | INSTITUCIONAL | ALTO / **ADVERTENCIA/BLOQUEO** | Excede el cupo máximo físico/pedagógico configurado para el aula. | Parámetros del Centro Educativo |
| `CUR_SIN_PARALELO` | Curso sin Paralelos Activos | `CursoInteligente` | INTEGRIDAD | MEDIO / **ADVERTENCIA** | Curso del nivel secundario sin paralelos habilitados para inscripciones. | Estructura Curricular Secundaria |

---

### 4. Inscripciones y Calificaciones

| Código | Nombre | Soporte | Tipo | Nivel / Comportamiento | Descripción y Excepciones | Fuente / Vigencia |
|---|---|---|---|---|---|---|
| `INS_DUPLICADA` | Doble Inscripción en la Misma Gestión | `InscripcionAcademica` | INTEGRIDAD | CRÍTICO / **BLOQUEO** | Un estudiante no puede estar inscrito en más de un curso/paralelo activo en la misma gestión escolar. | RM 0001/2026 / SIE MinEdu |
| `INS_DISPERSION_PARALELOS` | Desbalance de Paralelos | `InscripcionAcademica` | RECOMENDACIÓN | BAJO / **SUGERENCIA** | Sugiere inscribir en paralelos con menor carga para mantener equilibrio pedagógico. | Criterio de Equidad Educativa |
| `CAL_FUERA_RANGO` | Calificación Fuera de Rango (1-100) | `CalificacionInteligente` | INTEGRIDAD/NORMATIVA | CRÍTICO / **BLOQUEO** | La escala cuantitativa oficial en Bolivia para Secundaria es de 1 a 100 puntos (Mínimo de aprobación: 51). | RM 0190/2024 Art. 18 |
| `CAL_VARIACION_ATIPICA` | Variación Extrema de Calificación | `CalificacionInteligente` | ESTADÍSTICA | MEDIO / **ADVERTENCIA** | La calificación difiere en más de 35 puntos del promedio histórico del estudiante en el área. No bloquea; solicita confirmación. | Criterio Estadístico Preventivo SAVP |

---

### 5. Aula Virtual / LMS (Tareas, Entregas, Asistencia)

| Código | Nombre | Soporte | Tipo | Nivel / Comportamiento | Descripción y Excepciones | Fuente / Vigencia |
|---|---|---|---|---|---|---|
| `AV_TAREA_DUPLICADA` | Tarea Duplicada o con Título Idéntico | `TareaInteligente` | PEDAGÓGICA | MEDIO / **ADVERTENCIA** | Tarea con mismo título y tema en la misma semana/curso para evitar duplicación no intencional. | Buenas Prácticas Docentes |
| `AV_TAREA_CARGA_ALTA` | Sobrecarga de Tareas en la Misma Fecha | `TareaInteligente` | PEDAGÓGICA | MEDIO / **ADVERTENCIA** | Existen más de 3 tareas programadas para el mismo curso el mismo día o semana. | Prevención de Sobrecarga Estudiantil |
| `AV_ENTREGA_VACIA` | Entrega Definitiva sin Archivo ni Texto | `EntregaTareaInteligente` | INTEGRIDAD | CRÍTICO / **BLOQUEO** | No se puede marcar como entrega final una tarea que no adjunta documento ni texto de respuesta. | Control de Entregas SAVP LMS |
| `AV_ENTREGA_TARDIA` | Entrega Fuera de Plazo | `EntregaTareaInteligente` | INSTITUCIONAL | BAJO / **ADVERTENCIA** | La entrega se envía después de la fecha límite establecida por el docente. Se permite pero se marca tardía. | Política LMS de la Unidad Educativa |
| `AV_ASISTENCIA_INCOMPLETA` | Estudiantes sin Marcar Asistencia | `AsistenciaInteligente` | INTEGRIDAD | ALTO / **BLOQUEO** | No se puede consolidar la sesión de asistencia diaria si existen estudiantes de la lista sin estado asignado. | Registro Oficial de Asistencia MinEdu |
| `AV_ASISTENCIA_ANOMALA` | Patrón Anómalo de Asistencia | `AsistenciaInteligente` | ESTADÍSTICA | MEDIO / **ADVERTENCIA** | Estudiante con más de 3 faltas consecutivas injustificadas o 100% de inasistencia súbita en el paralelo. Alerta temprana sin modificar notas. | Protocolo de Retención Escolar MinEdu |
| `AV_ORIENTACION_INFORMATIVA` | Orientación Vocacional RIASEC | `OrientacionVocacionalInteligente` | PEDAGÓGICA | BAJO / **SUGERENCIA** | Los resultados de afinidad vocacional (Holland RIASEC) son exclusivamente orientativos y consultivos. | Marco Técnico de Orientación Vocacional |

---

### 6. Dashboards, Seguridad y Bitácora

| Código | Nombre | Soporte | Tipo | Nivel / Comportamiento | Descripción | Fuente |
|---|---|---|---|---|---|
| `SEC_BITACORA_ANOMALA` | Actividad Atípica Registrada | `BitacoraInteligente` | ESTADÍSTICA | MEDIO / **ADVERTENCIA** | Intentos reiterados de acceso fuera de turno o cambios masivos en poco tiempo. | Auditoría y Seguridad SAVP |
| `NOTIF_AGRUPAMIENTO` | Saturación de Notificaciones | `NotificacionInteligente` | RECOMENDACIÓN | BAJO / **SUGERENCIA** | Agrupación inteligente de eventos similares para evitar fatiga de alertas al usuario. | UX Preventiva SAVP |
