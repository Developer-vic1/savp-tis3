# Kardex y seguimiento — propuesta concreta

Auditoría: este checkout no contiene NovedadEstudiante ni SeguimientoAcademico previos. Se contrastó la referencia conceptual solo lectura; novedad administrativa no sustituye observación pedagógica con responsable, revisión, rectificación, evidencia y contexto. No se creó tabla Novedad duplicada.

MIG-001 prepara seguimiento_academico como raíz canónica de Kardex, cinco catálogos versionados y revisiones/evidencias privadas. Los campos, FK, índices, CHECK, riesgos y rollback conservador están en 25-MIGRATIONS-PROPUESTAS.md. Sin filas de catálogo, seeds ni concesión de permisos.

SeguimientoAcademico, KardexPolicy, KardexRepository/ScopedKardexRepository, KardexService y DomainReadinessService preparados. El repositorio recibe usuario+estudiante, filtra cada seguimiento: Regente gestión/grado; Docente plan propio+inscripción correlacionada; Estudiante visible/NORMAL; Admin/Secretaria metadata mínima. Flag false evita consultas a tabla futura.

Register sigue cerrado (409); no se implementa una transición disciplinaria inventada. Escrituras, revisiones/evidencias/descarga y UI de timeline/form necesitan desarrollo bajo contrato formal; son pendientes internos explícitos, además de schema/catálogos externos. No presentar estas pantallas como CRUD completo.

KardexInteligente y KardexDraft reutilizan CatalogoInteligenteBase para revisar un borrador docente sin guardarlo: motivo vacío bloquea; descripción breve, contexto y acompañamiento sugieren revisión. No sanciona ni asigna nivel automáticamente. El componente exige actor/permiso y no consulta eventos institucionales con el flag cerrado. Tests separan bloqueo/advertencia, corrección y denegación a Regente. Recurrencia real y timeline aún requieren implementación y contexto persistente autorizado.
