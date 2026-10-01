# Rutas actuales

evidencia/support-routes.json es el inventario vigente de route:list: 162 rutas registradas incluyendo paquetes, alias y redirects. No representa 162 ventanas ni cobertura funcional. MATRIZ-CONCILIACION-105.csv conserva la ruta original y documenta la adaptación efectiva. La reflexión comprueba 105 acciones de controllers de App sin errores; no ejecuta consultas.

routes/actors.php incluye direccion, secretaria, regencia, docente, estudiante y workspace_domains. Todos bajo auth y middleware actor estricto; componentes/services reautorizan las acciones posteriores. Las URLs heredadas del Aula siguen disponibles para compatibilidad.

Se registraron calendarios para los seis actores, documentación privada Admin/Secretaria, reportes administrativos Secretaria y reportes paginados/PDF Regente. /actor/perfil redirige al perfil Jetstream propio. Las rutas de dominios bloqueados muestran disponibilidad; no se declaran CRUD por existir una URL.

Filtros se validan en servidor. IDs de curso/clase no reemplazan ownership ni correlación de inscripción. Reporte Regente vuelve a obtener el plan desde el scope antes de generar PDF.
