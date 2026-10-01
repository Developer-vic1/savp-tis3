# Tema claro/oscuro

Se preserva resources/css/app.css y sus tokens. Shell único, sidebar, búsqueda, tablas y componentes nuevos usan ui-* y CSS variables. ThemeManager/evento theme-changed se reutilizan; SweetAlert/toasts usan surface/text/border/primary, sin segunda paleta.

Chart.js actualiza colores por tema y destruye instancias cuyo canvas se reemplaza en navegación Livewire. Vite valida compilación; no acredita contraste, legibilidad o ausencia de fallos JS en navegación autenticada.

Pendiente QA light/dark de seis actores: topbar/sidebar móvil, foco/tooltip, drawer/escape/retorno, dropdown/campana, formularios/validaciones, tablas y modales legacy a 375/768/1440. No se marca PASS visual en la matriz por detectar una clase CSS.
