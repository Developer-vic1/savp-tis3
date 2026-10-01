# Livewire y acciones

InstitutionalAuthorization revalida componentes Admin/Aula/Secretaria al boot y call, con módulos/actores permitidos. SharedCourseList, CourseWorkspace, StudentDrawer, ModuleSearch, AcademicSources, StudyAssistant, NotificationCenter y AcademicPlan reutilizan servicios canónicos.

IDs de clase, selección de expediente y respuestas externas son Locked; búsqueda/estado/paginación se validan. Shared no queda protegido por el hook de Admin: cada componente debe aplicar su propio permiso/ownership en mount/render/acción. AcademicGoalService vuelve a buscar meta por estudiante propio antes de editar.

Se corrigieron dos fallos comprobados en tests HTTP Livewire: colisión de método privado authorize con Component y propiedad message eliminada por @error. Tutor/fuentes usan statusMessage.

Tests verifican revocación, contexto Locked y fallback/contrato. Faltan flujos completos con persistencia y auditoría visual de todos los modales legacy; montaje correcto no acredita autorización de todos los campos.

InstitutionalQuery mantiene filtros en URL, paginación y limpieza de dependencias al cambiar gestión. InstitutionalRecordDrawer prepara detalle mínimo de cursos/turnos para Secretaría, reautorizando apertura/render. TeacherGradeForm agrega Support docente sin copiar el CRUD administrativo. AcademicPlan permite revisar borrador con persistencia deshabilitada. Persona usa ID de edición Locked; Calificaciones conserva selección/estado de edición Locked. Ver tests vigentes y 28/29.
