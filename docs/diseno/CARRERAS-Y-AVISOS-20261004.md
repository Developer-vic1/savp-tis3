# Carreras y avisos de pantalla

- Carreras en tarjetas reutilizables con iconos, estado y acciones. Conservan los tokens institucionales y se apilan en móvil.
- «Preparar estudio» abre la ficha documental existente con URL, carrera, universidad y tipo. Los permisos, la comprobación de URL, el análisis y la aprobación siguen siendo obligatorios.
- Python prepara fragmentos preliminares del extracto leído, con hash del documento, URL e identidad estable. No son el corpus completo ni se publican en el tutor. La ingesta aprobada conserva su procedimiento existente.
- Corregida detección de falsos bucles al redirigir una carrera a su ruta con barra final. Se mantienen validación de universidad, DNS público, HTTPS y límites de descarga/redirección.
- Campana muestra avisos reales de acciones durante la pantalla actual. El guardado efectivo del borrador emite su aviso después de escribirlo en el navegador. No persisten al recargar.
- La comprobación de solo lectura encontró `features.notifications=false` y ausencia de tabla `notifications`. No se ejecutaron migraciones, no se activó una configuración incompatible y no se escribieron registros institucionales. Para historial institucional, el trabajo de BD debe incorporar esa tabla y validar productores/identidad `cod_usu`.
- Verificado: compilación de recursos, sintaxis PHP/Blade/Python, prellenado de ficha, portal UCB real, generación de fragmento preliminar. Sin pruebas automatizadas nuevas.
