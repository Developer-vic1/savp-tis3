# Consulta del calendario ministerial

La sección **Nueva gestión → Fechas y sus fuentes** separa las fechas de la BD del año elegido, la plantilla curricular y la consulta de publicaciones oficiales. Ningún botón guarda una gestión: solo prepara el borrador y conserva la validación independiente del servidor.

## Ejecución

Directorio: `C:\laragon\www\savp-reestructuracion`. Terminal: PowerShell.

La consulta usa `scripts/academico/estudiar_calendario.py`, Python con `pypdf`, Laravel y archivos privados. No usa modelos ni envía datos personales al Ministerio. Los documentos deben pertenecer a Educación Regular y al año solicitado; fechas ambiguas requieren revisión.

Variables locales:

```dotenv
CALENDARIO_ESTUDIO_PYTHON="C:/ruta/python.exe"
CALENDARIO_ESTUDIO_POWERSHELL="C:/ruta/pwsh.exe"
CALENDARIO_ESTUDIO_ARRANQUE_LOCAL=true
```

En Windows y entorno Laravel `local`, una solicitud inicia un trabajador oculto exclusivo. Los temporales y sus registros se mantienen dentro de `storage/app/private/estudio-calendario`. Al abrir el formulario con un pendiente, se comprueba el latido y se recupera el trabajador si dejó de ejecutarse. El proceso no realiza migraciones, seeders ni cambios en tablas académicas.

Para ejecutar manualmente:

```powershell
& 'C:\laragon\bin\php\php-8.4.13-nts-Win32-vs17-x64\php.exe' artisan academica:estudiar-calendario --seguir
```

Sin `--seguir`, procesa una revisión y termina. Fuera del entorno local debe configurarse un supervisor o tarea del sistema para ejecutar este comando; no se inicia automáticamente. Tras cambiar el código del trabajador, reinícialo porque es un proceso persistente.

Para identificar el proceso antes de detenerlo:

```powershell
Get-CimInstance Win32_Process |
  Where-Object { $_.Name -eq 'php.exe' -and $_.CommandLine -like '*savp-reestructuracion\artisan*academica:estudiar-calendario --seguir*' } |
  Select-Object ProcessId, ExecutablePath, CommandLine
```

Detén solo el PID verificado de este trabajador con `Stop-Process -Id <PID>`. No detengas el servidor PHP ni los servicios del editor. El siguiente formulario puede reiniciarlo cuando su latido expire (90 segundos).

## Pendientes y avisos

- Escaneo de pendientes cada 15 segundos; cada estudio tiene un límite de 60 segundos.
- Sin conexión o fuente temporalmente inaccesible: nuevo intento a partir de 60 segundos, mientras el trabajador siga activo.
- Sin publicación identificable o lectura incierta: revisión cada seis horas. No se extrapola el calendario de otro año.
- Resultado confirmado: se conserva con URL oficial, página, momento de consulta y SHA-256; una nueva solicitud reutiliza el resultado durante una hora.
- **Avisarme** registra un aviso interno para el usuario conectado. Se entrega al actualizar este componente o volver a abrir el formulario. No envía WhatsApp, correo ni notificaciones del navegador.
- El estado se guarda en archivos `{año}.json`; los avisos usan un identificador de usuario resumido mediante SHA-256. No borres estos archivos si deseas conservar los pendientes.

El formulario consulta el progreso mediante un componente independiente. No repite los cálculos del panel académico durante esa consulta. El trabajador debe permanecer ejecutándose para estudiar los pendientes con la página cerrada.

## Fuentes y límites

Solo se permite HTTPS en `minedu.gob.bo` y `www.minedu.gob.bo`, incluidas las redirecciones. El buscador oficial aporta candidatos; se revisan hasta cuatro publicaciones y tres PDF, con límites de tamaño y páginas. **Sin publicación** significa que no se identificaron fechas en estas fuentes consultadas, no una búsqueda exhaustiva de Internet.

La RM 0001/2026 de Educación Regular se consultó realmente: página 10, inicio 02/02/2026 y cierre 02/12/2026. El pendiente 2027 quedó sin publicación identificable en la consulta realizada. Ambos estados fueron comprobados en el navegador; la ausencia de conexión, la fuente ajena, el año incorrecto y las fechas ambiguas se comprobaron con controles aislados, sin desconectar el equipo.

Consulta visible: `http://127.0.0.1:8000/admin/gestion-academica`. Revisa las modificaciones posteriores de la norma y las disposiciones locales antes de aplicar las fechas.

## Rendimiento y datos ausentes

Se comparte la lectura del esquema únicamente dentro de una petición. No se almacenan datos académicos entre peticiones y se vuelve a consultar el esquema al comenzar otra, conservando compatibilidad con cambios en la BD.

Los conteos de asistencia, tareas, clases y calificaciones solo se calculan al abrir el detalle del año seleccionado. La estructura y los pendientes de cierre se cargan en sus apartados. La preparación interna medida pasó de 921 consultas y 2,7 segundos a 148 consultas y aproximadamente 0,6 segundos; estas medidas excluyen la transferencia HTTP y el renderizado del navegador.

Los días de referencia ausentes no se reemplazan en la BD. La interfaz muestra una estimación explícita por calendario cuando hay fechas, o un estado pendiente cuando no las hay. Una estimación no acredita los días efectivos de la norma. Los años duplicados y las fechas ajenas al año elegido bloquean el formulario; guardar conserva los requisitos del servidor.
