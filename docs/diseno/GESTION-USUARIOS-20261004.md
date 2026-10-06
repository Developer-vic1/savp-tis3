# Gestión de usuarios: diseño y conexión pendiente

Fecha: 4 de octubre de 2026. Checkout: `C:\laragon\www\savp-reestructuracion`, rama `Fusion_Sistema`.

## Alcance

Este trabajo conserva identidad SAVP, tokens `--ui-*`, tema claro/oscuro y permisos existentes. La base oficial y sus migraciones se trabajan en otro chat. Aquí no se aplicaron migraciones, no se modificaron cuentas reales y no se enviaron mensajes de bienvenida.

## Componentes compartidos

- `resources/views/components/selector-institucional.blade.php`: selector con búsqueda, teclado, lista flotante y transición de 140 ms. Usa `resources/js/selector-institucional.js` y sus estilos. Admite Livewire con `modelo` o Alpine local con `enlace`; `numerico` conserva el tipo en mes/año. Las opciones tienen `valor`, `etiqueta` y, opcionalmente, `deshabilitada`. Respeta movimiento reducido y libera escuchas al destruirse.
- `calendario-institucional.blade.php`: fecha con límites, navegación y el selector compartido para mes/año. Recibe `modelo`, `identificador`, `minimo`, `maximo` e `inicio`.
- `paginacion-institucional.blade.php`: control compartido de páginas y cantidades 10/20/50; bloquea solicitudes repetidas y permite reintentar al fallar.

Ejemplo Livewire:

```blade
<x-selector-institucional modelo="filtroEstado" identificador="estado-cuentas"
    etiqueta="Estado" :opciones="[['valor'=>'ACTIVO','etiqueta'=>'Activo'],['valor'=>'INACTIVO','etiqueta'=>'Inactivo']]" />
```

Ejemplo dentro de un componente Alpine que posee `mes`:

```blade
<x-selector-institucional enlace="mes" :numerico="true" identificador="mes-fecha"
    etiqueta="Mes" :opciones="[['valor'=>0,'etiqueta'=>'Enero'],['valor'=>1,'etiqueta'=>'Febrero']]" />
```

Se utiliza ya en filtros, roles y estados de Usuarios y en mes/año del calendario. Permite sustituir otros selectores gradualmente sin duplicar implementación.

## Vista y datos

Tabla, directorio y galería conservan la preferencia local. Los indicadores se calculan sobre las cuentas filtradas: mosaico proporcional de roles, puntos por etapa de edad y cuadrícula porcentual de género. Los valores exactos están disponibles junto a los gráficos; no se inventan distribuciones ni movimientos para dar variedad.

La ficha separa Acceso, Permisos y Actividad. Muestra fechas disponibles, contacto de Persona, último inicio registrado y confirmación de dos pasos, sin revelar credenciales. Actividad consulta solo movimientos individuales de esa cuenta en `users`; no expone registros de otras personas ni atribuye automáticamente eventos masivos.

## Acceso y bitácora

El correo de acceso generado utiliza `uft3.nombrecompleto.apellidopaterno.2letrasapellidomaterno@gmail.com`; concatena los nombres y normaliza acentos. No admite edición. Un conflicto, incluida una dirección Gmail equivalente por puntos, impide crear otra cuenta.

El destinatario parte de Persona, permanece protegido hasta pulsar Cambiar correo de entrega y exige autorización del destinatario. Cambiarlo requiere motivo de 10 a 500 caracteres, incluido en la descripción de bitácora. Solicitar enlace de contraseña para una cuenta existente o modificar contraseña también requiere motivo. No se guardan contraseñas en la descripción.

Desactivar exige confirmación y motivo. Se impide desactivar al usuario actual y al último administrador activo. En una desactivación masiva, la descripción identifica por nombre a los afectados cuando son hasta tres; si son más de tres, indica cuántos fueron desactivados. El conteo excluye cuentas protegidas y las que ya estaban inactivas.

La bienvenida usa un enlace temporal para elegir contraseña y diferencia destinatario de correo de acceso. La dirección base se configura con `SAVP_URL_ACCESO_PUBLICO=http://localhost:8000`; se podrá sustituir después. No aparecen advertencias sobre dirección pública en la interfaz ni en la bienvenida. Credenciales SMTP permanecen en `.env`, nunca en este documento.

## Incorporación por fecha: pendiente de conexión oficial

La interfaz permite revisar una fecha futura e indica estado inicial Inactivo. El calendario limita a partir de mañana, hora de Bolivia. Confirmar una programación está bloqueado tanto en interfaz como en servidor mientras falta integrar la solución oficial. No hay migración ni comando nuevo de activación en este trabajo.

La integración oficial deberá entregar la programación pendiente por cuenta para la ficha y las vistas, guardar fecha/actor/estado y ejecutar activación a las 00:00 en `America/La_Paz` conservando permisos. La cancelación o sustitución de una programación y su ejecución necesitan descripción de bitácora. La existencia del control visual no significa que el proceso automático esté operativo.

## Verificación

Desde PowerShell en el checkout:

```powershell
npm.cmd run build
node --test tests/Frontend/*.test.mjs
& 'C:/laragon/bin/php/php-8.4.13-nts-Win32-vs17-x64/php.exe' -d extension=pdo_sqlite -d extension=sqlite3 vendor/phpunit/phpunit/phpunit tests/Feature/GestionPersonasDisenoTest.php
```

Compilación correcta; 29 pruebas frontend y 30 pruebas PHP con 189 aserciones, sobre SQLite en memoria y notificaciones simuladas. No se crean pruebas de snapshots de estilos.

QA en navegador: selección por búsqueda y teclado, filtro sin resultados y restauración, selector de mes y fecha futura, correo fijo y motivo al habilitar destinatario, vistas y pestañas de ficha. Revisados escritorio y móvil en ambos temas. La cuenta actual no tiene permisos granulares para confirmar creación/envío y no existen personas sin cuenta; no se elevan permisos ni se inventan registros para probar. La autenticación SMTP se comprobó sin enviar correo; no se certifica entrega real.
