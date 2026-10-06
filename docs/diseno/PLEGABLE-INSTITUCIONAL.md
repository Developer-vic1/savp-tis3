# Plegable institucional

Componente: `resources/views/components/plegable-institucional.blade.php`. Estilos únicos: `resources/css/plegable-institucional.css`, importados por `app.css`. Usa los tokens del tema y Phosphor. Conserva `<details>` y `<summary>` nativos para ratón, pantalla táctil, Enter y espacio; no necesita duplicar lógica Alpine ni inicializadores por vista.

```blade
<x-plegable-institucional
    etiqueta="Personalizar la comparación"
    descripcion="Elige los años y los datos que quieres contrastar."
    icono="ph-sliders-horizontal"
    class="mt-4"
>
    <x-slot:contador><span x-text="gestiones.length + ' elegidas'"></span></x-slot:contador>
    {{-- Controles o información del apartado. --}}
</x-plegable-institucional>
```

Propiedades: `etiqueta`, `descripcion`, `icono`, `abierto` y `compacto`. Para títulos dinámicos o con formato, utiliza `x-slot:titulo`. `x-slot:contador` es opcional. Los atributos del componente se pasan al `<details>`: `x-show`, `x-bind:open`, clases de separación, identificadores y claves Livewire siguen perteneciendo al contexto de la pantalla. No añade un `x-data` que oculte las variables del formulario.

```blade
<x-plegable-institucional icono="ph-shield-check" :abierto="count($cambios) > 0">
    <x-slot:titulo>Cambios por revisar</x-slot:titulo>
    {{-- Conserva la revisión y sus validaciones. --}}
</x-plegable-institucional>

<x-plegable-institucional etiqueta="Consultar valores" icono="ph-chart-bar" :compacto="true">
    {{-- Valores del gráfico. --}}
</x-plegable-institucional>
```

El icono depende del contenido: calendario para fechas, libros para materias y cargas, documento para fuentes o respaldos, conversación para personalizar mensajes y escudo para revisión. El contador no sustituye el título. Las áreas administrativas, Aula Virtual, orientación y errores comparten el componente; conservan sus contenidos, permisos y condiciones existentes.

Se sustituyeron 31 plegables en 21 vistas. La flecha gira al abrir, la cabecera cambia de estado y el contenido aparece con una transición breve. Con movimiento reducido se omiten animaciones. El texto puede ocupar varias líneas en móvil; los valores y tablas mantienen sus contenedores de desplazamiento.

Verificación: compilación de Blade y Vite completadas. En Gestión académica se comprobó apertura con Enter, cierre con espacio y ausencia de errores de consola. La cabecera no desbordó con la ventana reducida. Tema y movimiento reducido se revisaron en los estilos; la captura visual del navegador no estuvo disponible durante esta revisión.
