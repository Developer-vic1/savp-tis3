{{--
Sidebar principal del sistema.
Este archivo actúa como puente entre el layout general y el componente del menú.

IMPORTANTE:
- No declares aquí x-data="{ sidebarOpen: true }"
- Esa variable ya debe existir en layouts/app.blade.php
- Aquí solo renderizamos el contenedor lateral
--}}

<aside class="hidden lg:block" aria-label="Barra lateral principal del sistema">
    <x-menu />
</aside>