# Calendario, recuperaciones y universidades

Trabajo directo en `C:\laragon\www\savp-reestructuracion`, rama `Fusion_Sistema`. Se conservaron los cambios concurrentes. Sin pruebas automatizadas, migraciones, seeders, publicación ni escritura de registros institucionales.

## Recuperaciones

- Fecha posterior al caso, nunca pasada, dentro de la gestión activa única y del mismo trimestre. Si el caso cruza trimestres, requiere organizarlo por separado.
- Mínimo 60 minutos, dentro del horario escolar real consultado. El límite superior de una franja común es el menor tiempo afectado entre sus grupos; no se suma el tiempo paralelo para justificar una franja más larga.
- Más de siete días genera recomendación de coordinación; salir del trimestre bloquea la propuesta. El siguiente sábado se sugiere solo si está dentro del rango permitido.
- Día por confirmar conserva una observación obligatoria y permite indicar sábado o entre semana sin inventar una fecha.
- Modalidad presencial, virtual o por confirmar. Virtual exige un plan que explique actividades, acompañamiento y alternativas para quienes no puedan conectarse. Conserva los límites temporales y los cruces de docentes/grupos. El enlace del aula es opcional; no se crea una reunión ni se modifican tareas.
- Botón con animación y bloqueo de campos durante el cálculo, sin ventana adicional. Resultados previos se ocultan al editar los campos.

## Universidades

- Catálogo y fuentes existentes del aporte, con una identidad institucional que agrupa páginas de carreras y sedes. La URL de la visita se normaliza al portal de la sede; no se convierte cada carrera en una universidad.
- Mensajes locales para Google, YouTube, Facebook y otros servicios conocidos; un dominio desconocido requiere identificar la institución. Un dominio `.bo` por sí solo no acredita que sea universidad.
- Nuevo punto de consulta de Python: `POST /api/v1/knowledge/governance/university-preview`, autenticación interna existente. Solo recibe una URL, sin identidad ni datos de estudiantes/docentes.
- Reutiliza verificación de dominios confiables, HTTPS, DNS público fijado, redirecciones de la misma institución, límite de tamaño/tiempo y control de concurrencia. No incorpora fuentes ni escribe propuestas. Extrae candidatos de carreras del HTML del portal, hasta 30, conservando rutas y parámetros funcionales; descarta directorios y enlaces generales. Cada carrera sigue pendiente de revisión.
- Modal accesible de consulta con catálogo, fuentes y contenido leído. La descripción extensa queda desplegable. Página interna opcional en iframe aislado, sin scripts/formularios y sin enviar el referente; enlace externo disponible cuando el portal impide mostrarse dentro de otra página.
- El servicio configurado en `127.0.0.1:8001` estaba detenido. Se inició con el entorno existente: `.\.venv\Scripts\python.exe -m uvicorn app.main:app --host 127.0.0.1 --port 8001`, desde `ai-service`, en segundo plano. No se cambiaron claves, configuración ni exposición de red. Logs operativos en `output/diseno/universidades-servicio-20261004.*.log`.

## Datos y alcance de almacenamiento

Los cálculos consultan la BD oficial: grupos, vigencias de inscripción, planes generales/técnicos, horarios y trimestres. Se preserva la separación entre duración del caso y minutos de clases acumulados. Los borradores, PDF y plantillas de feriados se conservan solamente en el navegador. La descarga incluye `observacion_gestion` con estado, modalidad, plan, medio y franja. Registrar oficialmente eventos, documentos, recuperaciones u observaciones requiere el flujo institucional que está trabajando el otro chat; no se afirma que estas propuestas ya sean registros oficiales.

El apartado de seguimiento cuenta recuperaciones oficiales confirmadas/finalizadas, excluye las propuestas pendientes y distingue confirmar de realizar. Comparación académica: corrección de bucles SVG que quedaban fuera del alcance Alpine; trazos y ejes ahora se generan con Blade y siguen reaccionando a los indicadores/años elegidos.

## Verificación realizada

- Build Vite correcto (93 módulos), sintaxis PHP, Python e importación del nuevo módulo; compilación y sintaxis Blade. Sin crear ni ejecutar pruebas automatizadas.
- Navegador integrado: dos grupos de sexto, 25 estudiantes según vigencias, 10 sesiones, 5 h 30 min acumuladas y 3 h de duración del caso. La recuperación común de 3 h bloqueó con máximo 2 h 45 min; 2 h fue aceptada para revisión. Madrugada y menos de una hora bloqueados; aviso al superar siete días y día por confirmar comprobados. Calendario de recuperación: máximo 02/12/2026, cierre del tercer trimestre.
- Recuperación virtual: plan conservado y aviso para revisar acceso de los 25 estudiantes; no se presume que tengan conectividad. Borrador guardado y recuperado desde el navegador, sin modificar datos oficiales.
- Enlace `google.com` rechazado con descripción del servicio. Página de carrera UCB agrupada en `https://lpz.ucb.edu.bo/`. Python leyó realmente el portal y devolvió título, resumen y enlaces candidatos; el modal abrió y cerró sin nuevos errores de consola. No se incorporaron carreras automáticamente.
- Comparación 2021/2026: dos polígonos con coordenadas reales, seis ejes y línea con valores. Conserva las cifras reales y no califica rendimiento.
- Adaptación móvil revisada en esta sesión; el zoom/tamaño de captura del navegador integrado también mostró capturas recortadas, que no se usan como evidencia de un fallo del diseño. Se restableció la emulación temporal.

No se afirma cobertura de todos los dispositivos ni verificación completa del PDF. La vista interna de cada universidad depende de sus restricciones de publicación; no se evita su protección.
