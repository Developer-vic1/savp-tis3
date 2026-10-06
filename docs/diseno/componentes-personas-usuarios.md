# Componentes institucionales reutilizables

Los módulos Personas y Usuarios comparten la paginación y su aviso de carga. El calendario de Personas es un componente Blade configurable para otros formularios Livewire. Todos conservan los tokens `--ui-*`, el tema institucional y la preferencia de movimiento reducido.

## Organización

| Recurso | Responsabilidad |
| --- | --- |
| `resources/views/components/calendario-institucional.blade.php` | Fecha editable y calendario con selección de año, mes y día. |
| `resources/views/components/paginacion-institucional.blade.php` | Rango, páginas y cantidades 10, 20 y 50. |
| `resources/views/vendor/livewire/paginacion-institucional.blade.php` | Adaptador del paginador Laravel al componente Blade. |
| `resources/views/components/estado-carga-institucional.blade.php` | Aviso visible durante las solicitudes de paginación. |
| `resources/js/componentes-institucionales.js` | Control de carga, navegación y calendario. |
| `resources/css/componentes-institucionales.css` | Estilos compartidos. |
| `resources/css/personas-institucional.css`, `resources/css/usuarios-institucional.css` | Presentación propia de cada módulo. |

`resources/js/app.js` carga los recursos compartidos antes de los módulos. El parcial anterior `livewire/admin/personas/paginacion` queda como adaptador de compatibilidad; no mantiene otra implementación.

## Reutilizar el calendario

La propiedad Livewire debe existir y contener `null`, una cadena vacía o una fecha ISO `YYYY-MM-DD`. Cada instancia requiere un identificador único. Los límites son fechas ISO; el servidor debe validar el mismo rango de manera independiente.

```blade
<x-calendario-institucional
    modelo="form.fecha"
    identificador="registro-fecha"
    nombre="fecha"
    etiqueta="Fecha del registro"
    :minimo="now()->startOfYear()->toDateString()"
    :maximo="now()->endOfYear()->toDateString()"
    :inicio="now()->toDateString()"
    :requerido="true"
/>
```

`inicio` indica qué mes mostrar cuando falta la fecha; no rellena el dato. La entrada conserva ISO para Livewire y muestra una ayuda `DD/MM/YYYY`. `validacion-local` es opcional y exige un formulario padre con `tocado` y `errores`, como `formularioPersona`; otros módulos deben dejarla desactivada salvo que integren ese contrato.

## Reutilizar la paginación

El componente Livewire necesita `WithPagination`, una propiedad `perPage` y validación de cantidades permitidas. Al cambiar filtros o cantidad debe reiniciar la página. Cada módulo conserva sus permisos y su consulta delimitada.

```blade
<div x-data="paginacionInstitucional()">
    <x-estado-carga-institucional mensaje="Cargando registros…" />
    <section x-ref="resultados" :aria-busy="cargandoPagina">
        {{-- Resultados paginados del módulo --}}
    </section>
    {{ $registros->onEachSide(1)->links('vendor.livewire.paginacion-institucional', [
        'cantidad' => $perPage,
        'entidad' => 'registros',
        'singular' => 'registro',
    ]) }}
</div>
```

Un módulo con su propio `x-data` incorpora `...paginacionInstitucional()` en su objeto. Si define `destroy()`, debe llamar también a `destruirPaginacion()`. Se bloquean clics repetidos mientras se espera la solicitud; ante un error se recuperan los controles y aparece un mensaje para reintentar. El aviso acompaña las solicitudes de cantidad/página, no todos los procesos del módulo.

## Validación y correcciones asociadas

- Personas valida inmediatamente nombres sin números y CI numérico; Support y servidor verifican nuevamente antes de guardar. Se conservan tildes, apóstrofes y guiones.
- La comprobación de CI excluye la misma persona al editar y detecta duplicados aunque cambie el complemento. Las consultas remotas usan una espera de escritura de 450 ms. La comprobación observada en navegador tardó aproximadamente 2,5 segundos; esto no garantiza un máximo de cinco segundos si el servidor o la conexión se ralentizan.
- La fecha de nacimiento se recupera en ISO y las fechas inexistentes se rechazan sin convertirlas en otra fecha.
- La creación de personas mantiene el estado activo; la edición conserva el estado existente. Las acciones para desactivar corresponden a Usuarios.
- Personal institucional fallaba al cargar relaciones de planes docentes incompatibles con el esquema en uso. El modelo de compatibilidad conserva las relaciones de esos planes. La gestión reconoce `ACTIVO` y `ACTIVA`; con más de una gestión activa no elige una arbitrariamente.

La experiencia de usuario no sustituye las reglas del servidor, los permisos o Support Inteligente. La detección de duplicados tampoco certifica la identidad ante un registro externo.

## Verificación

Terminal PowerShell, directorio `C:\laragon\www\savp-reestructuracion`:

```powershell
php -d extension=pdo_sqlite -d extension=sqlite3 vendor/bin/phpunit tests/Feature/GestionPersonasDisenoTest.php tests/Unit/SupportPreventiveTest.php --filter 'GestionPersonasDisenoTest|persona'
node --test tests/Frontend/componentes-institucionales.test.mjs
npm.cmd run build
```

Resultado: 21 pruebas PHP con 127 aserciones y cinco pruebas JavaScript. Las pruebas PHP usan SQLite en memoria; no modifican la base institucional. La compilación de recursos se verificó con Vite.

QA visual en el navegador integrado sobre `http://localhost:8000`: Personas, Usuarios y Personal institucional; cantidades 10/50, navegación, estados de carga, fecha existente, selección de calendario, nombres inválidos y CI duplicado. Se revisaron anchos móvil y escritorio, y modo oscuro de Usuarios. No se guardaron registros ni se desactivaron cuentas. No se afirma una comprobación de Chrome u Opera en esta entrega. Los formularios abiertos por el usuario se conservaron.
