# Secretaria — V044–V057

Reutiliza Personas, Estudiantes, Inscripciones, procedencia/vinculación y Paralelos mediante autorización común. Cursos, Turnos y Gestión usan InstitutionalQueryController/InstitutionalQuery en solo lectura; el hook deniega los CRUD pedagógicos originales a Secretaría. OperationalAccountService limita cuentas operativas al ámbito autorizado; no concede roles administrativos por una operación de cuenta.

V051: nivel registrado, gestión/paralelo/turno correlacionados al mismo plan, estado, búsqueda y drawer de oferta mínima. V053: estado/franja, búsqueda y drawer de turno/plantillas. No se incluyen personas, notas ni responsables. Apertura y render reautorizan; revocación, actor ajeno, filtros inválidos, lectura y empty están probados con dobles/SQL sin BD. Teclado/foco/light-dark/responsive y datos reales requieren QA autorizada.

V048 usa documento_inscripcion_estudiante: consulta filtrada/paginada y descarga privada, Policy de documento y de inscripción, nosniff, hash y bitácora. No necesita tabla de expediente duplicada. Migrar históricos públicos requiere conciliación de archivos autorizada posterior.

V056 lista/descarga exclusivamente PDFs administrativos generados y autorizados; SQL/ZIP y familias académicas se excluyen. V054 reutiliza plazos de Tarea y prepara eventos detrás de flag. V055 Kardex ofrece solo disponibilidad/metadatos contractuales; no se abre contenido pedagógico sensible.

La vista cuentas.blade.php conservó exactamente el trabajo previo del usuario. No se certifica su CRUD sin DB/UX aisladas. Todas las modalidades del catálogo siguen pendientes de revisión funcional.
