# SAVP-TIS3 — primer smoke test real

## Actualización del arranque — 2026-10-01

El HTTP 500 observado al enviar Login correspondía al servidor temporal del primer smoke test, que forzaba `DB_CONNECTION=sqlite` y `DB_DATABASE=:memory:`. PHP carece de `pdo_sqlite`, por lo que el intento de consultar `users` terminaba en `could not find driver` antes de conectar a una BD. El `.env` de reestructuración ya existía y originalmente tenía las mismas 54 variables y valores que el de `savp-tis3`.

Se detuvo ese servidor y se inició otro con el `.env` local de reestructuración, sin overrides SQLite. Estado actual: `APP_URL=http://localhost:8000`, driver configurado `pgsql` con `pdo_pgsql` disponible, sesiones y caché en archivos locales, cookie propia `savp_reestructuracion_session`. Aporte Ingenieril SAVP y las cinco funciones con persistencia propuesta siguen deshabilitados. El servidor PHP escucha en `127.0.0.1:8000` (proceso 30484); Vite permanece en `127.0.0.1:5173` (proceso 33152).

`GET http://localhost:8000/` y `GET http://localhost:8000/login` respondieron HTTP 200. La pestaña que conservaba el 500 se navegó mediante GET; ahora muestra el formulario Login y su `form.action` apunta a `http://localhost:8000/login`, sin errores de consola. La lectura estática de configuración mostró cero conexiones DB instanciadas: **no se hizo consulta PostgreSQL desde la terminal ni se validaron credenciales**. No se ejecutaron migrations, seeders ni modificaciones de registros. El inicio de sesión real queda pendiente de una prueba con una cuenta autorizada; el éxito del GET no certifica el POST.

Los datos del primer smoke test que siguen a continuación describen el servidor aislado anterior y se conservan como evidencia histórica del error.

Fecha: 2026-10-01. Checkout: `C:/laragon/www/savp-reestructuracion`. Rama: `feature/REESTRUCTURACION`.

Commit de cierre: `11316863dafb73a466e5586f76fe27e7cdadcd18`, publicado en la misma rama de origin. HEAD anterior: `2f9d5e8dc5a85983b244f32efcd30b14ebd4c16b`. Los SHA local, remoto y `ls-remote` coinciden. Un único commit, 315 archivos; 58 logs/reportes crudos/patch antiguo excluidos. Inventario exacto: [evidencia/cierre-git-manifiesto.csv](evidencia/cierre-git-manifiesto.csv).

Este informe se creó **después del push** y queda local, pendiente de versionado. No se creó un segundo commit ni se modificó código productivo durante el smoke test.

## Arranque

| Comprobación | Resultado | Evidencia |
|---|---|---|
| PHP server | PASS | `php artisan serve --host=127.0.0.1 --port=8000 --no-reload`; mensaje Server running; proceso 45824, sesión de terminal 51530 |
| Vite | PASS | `npm run dev -- --host 127.0.0.1`; Vite ready; proceso 33152, sesión 16853 |
| URL Laravel | http://127.0.0.1:8000 | Escucha local confirmada |
| URL Vite | http://127.0.0.1:5173 | Cliente HMR registra connecting/connected |
| URL Laragon | NO DETECTADA | Sin entrada específica en hosts ni configuración de sitio encontrada en los directorios Apache/Nginx revisados; no se asumió dominio .test |
| .env | EXISTE / CONSERVADO | APP_KEY presente; hash anterior/posterior coincide; secretos no mostrados |

Ambos procesos permanecen activos al cierre de esta prueba. Se dejó Login abierto en el navegador de Codex. Los overrides temporales de viewport se restauraron.

Entry point HTTP: `public/index.php`, servido por Laravel; no se abrió como archivo. Frontend: `resources/js/app.js` y `resources/css/app.css`, definidos como entradas de Vite. Routing: `routes/web.php`, `admin.php`, `aula_virtual.php`, `actors.php` y los archivos de cada workspace incluidos por ellos. No se ejecutaron los scripts Composer setup/dev que incluyen migrations o queue listeners.

## Aislamiento del proceso

El proceso Laravel recibió overrides de entorno, sin editar `.env`: `APP_ENV=local`, `APP_DEBUG=false`, `APP_URL=http://127.0.0.1:8000`, `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`, `DB_URL=null`, `DB_HOST=127.0.0.1`, `DB_PORT=1`, `SESSION_DRIVER=file`, `CACHE_STORE=array`, `QUEUE_CONNECTION=sync`, `MAIL_MAILER=array`, `BROADCAST_CONNECTION=null`. Aporte Ingenieril SAVP y las cinco flags de persistencia propuesta permanecen deshabilitados.

La configuración efectiva se verificó mediante `artisan about` y una lectura acotada de configuración que solo devolvió nombre de driver, destino en memoria y presencia booleana de APP_KEY. No se imprimió `config:show app` completo porque contiene la clave. SQLite no tiene PDO disponible: **no se creó una base de testing ni se aplicó un schema**. Las páginas públicas se sirven sin consultas institucionales; la persistencia/autenticación permanece bloqueada. Esta modalidad de arranque no valida la configuración original de PostgreSQL de `.env`.

Database: **NO MODIFICADA**. PostgreSQL institucional conectado/modificado: **NO**. Migrations: **NO EJECUTADAS (0)**. Seeders: **NO EJECUTADOS (0)**. Sin rollback, DDL ni creación de usuarios.

## Smoke test

| Pantalla/capa | Estado | Resultado observado |
|---|---|---|
| Home `/` | PASS | HTTP 200; landing institucional Franz Tamayo N°3 visible |
| Login `/login` | PASS GET | HTTP 200; logo, formulario y controles visibles; el agente no envió credenciales ni formulario. Una solicitud de autenticación registrada después produjo RUNTIME-002 |
| Dashboard sin autenticar | PASS | HTTP 302 a `/login`, confirmado también por navegación real |
| Login Aula Virtual | PASS HTTP | `/aula-virtual/login` responde 200; recorrido visual interno pendiente |
| Recuperación | PASS HTTP | `/forgot-password` responde 200; no se solicitó correo/token |
| CSS | PASS | Hoja de Vite cargada y estilos aplicados; endpoint responde 200 |
| JavaScript | PASS | Módulo app.js y cliente Vite responden 200; themeManager inicializado |
| Livewire | PASS INICIAL | Script responde 200; `window.Livewire` y `window.Alpine` son objetos; sin errores de inicialización. No valida acciones de componentes autenticados |
| Assets/logo | PASS INICIAL | Logo institucional responde 200 y naturalWidth mayor que cero en Home/Login; sin 404 observados en consola |
| Dark mode | PASS PÚBLICO | LIGHT → DARK → LIGHT en Home y Login; clase/data-theme y fondo de tarjeta cambian; terminó en LIGHT |
| Responsive básico | FAIL | Login sin overflow; Home tiene overflow horizontal en tablet/mobile, detallado abajo |
| Browser console | 0 errores | Sin errores JS/Livewire/Alpine/Chart.js/Vite observados en las páginas recorridas |
| Laravel log | 1 error nuevo | Baseline de 276869 bytes; crecimiento al control final: 35420 bytes; fallo de driver SQLite en autenticación, RUNTIME-002 |
| Vite terminal | 0 errores | Aviso no fatal: datos Browserslist antiguos |

Las respuestas HTTP, mediciones y capturas temporales están fuera del repositorio en `C:/Users/LOQ/AppData/Local/Temp/savp-cierre-20261001/`. Imagen final de Login: `login-runtime.jpg`. No se versionaron screenshots ni logs.

## Hallazgo visual RUNTIME-001

**Home: desbordamiento horizontal inicial en tablet/mobile.** Se contrastó `documentElement.scrollWidth` con `clientWidth`, además de abrir el menú móvil y comprobar su navegación.

| Página/tamaño CSS observado | clientWidth | scrollWidth | Resultado |
|---|---:|---:|---|
| Login desktop, innerWidth 1440 / innerHeight 901 | 1416 | 1416 | PASS |
| Login tablet, innerWidth 769 / innerHeight 1024 | 769 | 769 | PASS |
| Login mobile, innerWidth 401 / innerHeight 844 | 376 | 376 | PASS |
| Home tablet, innerWidth 769 / innerHeight 1024 | 744 | 752 | FAIL: 8 px |
| Home mobile, innerWidth 401 / innerHeight 844 | 376 | 418 | FAIL: 42 px |

El navegador tiene zoom previo, por lo que se registraron las dimensiones CSS efectivas, no solo las solicitadas. La inspección de rectángulos detectó contenido que supera el borde derecho en `.scroll-reveal-right`, especialmente el bloque Organización académica/Formación integral. Código para revisar: `resources/views/welcome.blade.php:285` (`translateX(32px)`) y `:881` (contenedor de tarjetas). Es una **hipótesis causal pendiente de corrección/reprueba**, no una corrección certificada. No se hicieron cambios CSS masivos ni se ocultó el desbordamiento para obtener PASS.

## Autenticación y alcance pendiente

**RUNTIME-002 / BLOCKED_DB_RUNTIME:** al control final se detectó una solicitud de autenticación con HTTP 500 y un error nuevo de Laravel a las 05:56:09 UTC: `could not find driver`, conexión **sqlite**, destino **:memory:**, consulta de autenticación a `users`. El agente no introdujo credenciales ni realizó una acción de submit; no se atribuye origen a la solicitud sin evidencia adicional. No se copiaron correo ni datos del formulario al informe. El fallo ocurre antes de abrir la conexión: no hubo acceso PostgreSQL ni escritura DB. Se restauró Login mediante navegación GET desde Home, sin reenviar el POST. La falta de driver/schema/cuenta aislada permanece pendiente; no se intentó resolverla con migrations ni usando la configuración institucional.

Usuario de testing disponible: **NO**. Se pudo entrar autenticado: **NO**. Estado: **AUTH_BLOCKED_NO_TEST_USER**. No hay PostgreSQL aislado aprobado ni credenciales de una cuenta de testing autorizada; no se buscaron usuarios mediante consulta institucional ni se inventaron credenciales.

Para continuar se requiere un entorno de testing aislado aprobado, su schema autorizado y una cuenta de prueba con roles/permisos definidos. No basta una cuenta institucional para este proceso sin persistencia habilitada.

Sidebar, topbar, tablas, modales, workspaces de los seis actores y Support Inteligente: **SKIP por autenticación/entorno aislado**. No aparecen en las páginas públicas probadas. No se realizaron guardados. No se certificó ninguna Vxxx ni las seis migrations propuestas. Los GET públicos no tuvieron errores de persistencia; la autenticación sí registró BLOCKED_DB_RUNTIME. El bloqueo de persistencia permanece por diseño.

Runtime público disponible: **SÍ**. Listo para revisión visual autenticada pantalla por pantalla: **NO**, hasta resolver el entorno/cuenta de testing. Siguiente hallazgo público a revisar: RUNTIME-001.

Validación pre-commit: 191 PASS, 31 SKIP, 582 aserciones, 0 FAIL; Composer, build, rutas, Blade, 224 lint PHP y diff-check PASS. Tres archivos documentales requirieron limpieza de whitespace antes del commit; revalidación del índice PASS. Resumen: [evidencia/cierre-precommit-validacion.json](evidencia/cierre-precommit-validacion.json).

**NO MERGE. NO MIGRATE.**
