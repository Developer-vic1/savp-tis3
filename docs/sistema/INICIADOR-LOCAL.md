# Iniciador local de SAVP para Windows

## Uso

Ejecutar `ABRIR-SAVP.cmd` desde el Explorador. El archivo invoca PowerShell con una política de ejecución limitada a ese proceso; no cambia la política global de Windows. La carpeta puede tener espacios. Se solicita la base en cada ejecución para mostrar y verificar el destino real.

Hay dos modos:

- **Existente:** exige una conexión PostgreSQL válida y las tablas principales de SAVP. No crea bases, aplica migraciones ni ejecuta seeders. Conserva usuarios y contraseñas.
- **Demostración:** exige un nombre `savp_demo_...`. Solo crea una base si no existe y el operador escribe su nombre exacto. Solo migra una base sin tablas; solo carga el dataset si no hay registros de dominio. Puede completar la carga de una base con el esquema completo y sin datos. Si ya tiene datos, se abre conservándolos y el seeder no se repite. Una instalación parcial se detiene con diagnóstico y nunca se borra para intentar repararla.

La demostración pública sustituye únicamente la necesidad de fuentes privadas para explorar el programa: no representa la población, los horarios ni la oferta real de la institución. Tiene tres materias ilustrativas y dos turnos; los horarios de todos sus grupos están completos de lunes a viernes. Las notas se generan solo para períodos terminados a la fecha de preparación. Los seis años terminan en el año local de preparación. Ninguna regla académica, FK, trigger ni permiso se deshabilita.

## Preparación automática

Se buscan herramientas instaladas antes de instalar nada. Para herramientas ausentes se usa [WinGet de Microsoft](https://learn.microsoft.com/en-us/windows/package-manager/winget/), con los paquetes `PHP.PHP.8.4`, `OpenJS.NodeJS.LTS`, `Python.Python.3.14`, `astral-sh.uv` y `PostgreSQL.PostgreSQL.17`. Los instaladores pueden necesitar intervención del operador y elevación de Windows. Si no hay WinGet, se intenta el procedimiento oficial mediante `Microsoft.WinGet.Client` desde PowerShell Gallery. No se desactivan validaciones de firma ni se ejecutan instaladores con huellas incorrectas.

Composer se reutiliza si está instalado; en otro caso se descarga su instalador oficial y se verifica el SHA-384 publicado antes de ejecutarlo, siguiendo [el procedimiento oficial de Composer](https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md). Se ejecuta `composer install --no-scripts`, nunca `composer setup`, que contiene una migración no adecuada para una base existente. Se descubre el catálogo de paquetes después de verificar y guardar la configuración.

Se ejecuta `npm ci --ignore-scripts` para los recursos y `uv sync --frozen --no-dev` para el Aporte. El arranque del Aporte usa su propio entorno Python, no una ruta de otro equipo. uv se instala como herramienta independiente mediante WinGet, evitando una dependencia de la caché pip de cada equipo. Un archivo privado de estado recuerda las etapas completadas y permite reutilizar las instalaciones cuando los locks no cambian; los recursos se recompilan cuando cambian sus fuentes.

Se prepara un `php.ini` privado del iniciador, con extensiones de PostgreSQL, archivos, imágenes y Unicode. No se modifica el `php.ini` global. No se instalan ni se publican bases institucionales, secretos, firmas, nóminas ni modelos privados. Las funciones documentales o analíticas que necesiten fuentes o modelos adicionales conservan los estados de disponibilidad y evidencia del sistema.

## Servicios y credenciales

Laravel y FastAPI escuchan exclusivamente en `127.0.0.1`. Si 8000/8001 están ocupados se seleccionan puertos libres sin detener procesos ajenos. El navegador se abre después de comprobar HTTP 200 en el acceso y en `/health`. Al pulsar Enter en la consola se detienen solamente los procesos creados por esta ejecución. El servicio PostgreSQL instalado se conserva, como el resto de servicios del equipo.

Las contraseñas se solicitan ocultas. La contraseña de PostgreSQL queda en `.env`, necesaria para operar Laravel; existe un respaldo local `.env.iniciador-respaldo`, excluido de Git. La contraseña de las cuentas demo solo se conserva como hash en su base. La clave del contrato HTTP del Aporte se genera localmente y se pasa al servicio por entorno, sin argumentos ni registro en logs. No se crea una ruta de acceso automático que sustituya la autenticación.

El programa no cambia contraseñas existentes, habilita cuentas institucionales ni amplía permisos para simular un ingreso exitoso. Solo el dataset vacío recibe los roles del proyecto y sus cuentas sintéticas.

## Diagnóstico

Comprobación de solo lectura, sin instalar, escribir `.env` ni iniciar servicios:

```powershell
powershell.exe -NoProfile -File scripts/iniciar-savp.ps1 -SoloDiagnostico -NoInstalar -Modo Existente
```

Para automatizar esa lectura con credenciales ya configuradas:

```powershell
powershell.exe -NoProfile -File scripts/iniciar-savp.ps1 -SoloDiagnostico -NoInstalar -NoInteractivo -Modo Existente -NombreBase NOMBRE_EXACTO
```

Opciones adicionales: `-SoloPreparar` prepara sin abrir servicios; `-NoAbrir` inicia servicios sin lanzar el navegador; `-NoInstalar` evita instalaciones de herramientas del sistema, pero permite preparar las dependencias del proyecto. `-NoInteractivo` exige parámetros explícitos y no permite crear una demostración vacía.

Los logs de procesos quedan en `storage/logs/iniciador/`. Un fallo detiene la secuencia; nunca continúa migrando otra conexión, omite una validación ni borra tablas. Si una migración inicial falla, conservar el diagnóstico y revisar esa base aislada; el iniciador no intenta una reconstrucción destructiva.

## Validación del dataset

`php scripts/pruebas/probar-iniciador-postgresql.php` crea una base PostgreSQL desechable con nombre aleatorio `savp_demo_prueba_...`, ejecuta las migraciones reales, comprueba conteos, roles, vínculo estudiantil, las seis inscripciones de sexto y las protecciones de carga repetida/base oficial. Al terminar elimina exclusivamente esa base creada por la prueba. Requiere un usuario local con permiso de crear bases. No migra la conexión institucional configurada. `--conservar` permite retenerla para inspección.

`powershell.exe -NoProfile -File scripts/pruebas/probar-iniciador.ps1` comprueba escritura literal de credenciales con caracteres especiales, preparación de extensiones, reserva de puertos, sintaxis PowerShell y bloqueo de la base protegida sin cambiar su `.env`.

## Verificación del 14/10/2026

- Nueve comprobaciones PowerShell correctas, incluido mantenimiento literal de contraseñas y conservación del `.env` original y de un puerto ocupado.
- Migraciones y carga de demostración correctas en PostgreSQL aislado, también con caché en base de datos: seis gestiones, 12 estudiantes, 36 docentes, dos turnos, 14 bloques, 72 horarios, 2.160 detalles y 42 inscripciones. Carga repetida y nombre oficial bloqueados.
- Copia de Git preparada en una ruta con espacios, sin nóminas ni fuentes documentales privadas. Composer y npm ejecutados en esa copia; build y compilación de vistas correctos.
- uv instalado desde WinGet, con comprobación de huella por el instalador. Sincronización congelada del Aporte en un entorno Python nuevo: 26 paquetes instalados según `uv.lock`.
- Arranque con puertos 8000/8001 ocupados: Laravel en 8002 y Aporte en 8003; comprobaciones HTTP correctas. Ingreso normal del estudiante de sexto, tres aulas actuales y 51 notas de 2021 a 2026 verificadas en el navegador; fuentes Phosphor cargadas.

No se probó reinstalar PHP, Node ni PostgreSQL en un Windows completamente vacío: este equipo ya dispone de esos runtimes. npm mostró 11 avisos de vulnerabilidad del lock existente; se conservaron sus versiones y no se aplicaron actualizaciones forzadas dentro del iniciador. Estas comprobaciones no certifican todos los flujos del sistema ni constituyen una auditoría de seguridad.
