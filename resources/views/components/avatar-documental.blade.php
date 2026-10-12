@props(['compacto' => false])
<span {{ $attributes->class(['kg-avatar-documental', 'kg-avatar-documental-compacto' => $compacto]) }} aria-hidden="true">
    <svg viewBox="0 0 96 96" xmlns="http://www.w3.org/2000/svg" focusable="false">
        <circle class="kg-avatar-fondo" cx="48" cy="48" r="44" />
        <path class="kg-avatar-libro" d="M48 30C38 23 25 23 15 27v43c12-4 22-3 33 4 11-7 21-8 33-4V27c-10-4-23-4-33 3Z" />
        <path class="kg-avatar-pliegue" d="M48 31v43M21 65c7-1 13 0 19 3M56 68c6-3 12-4 19-3" />
        <path class="kg-avatar-pagina" d="M22 34c6-1 11 0 16 2M58 36c5-2 10-3 16-2" />
        <g class="kg-avatar-rostro"><ellipse class="kg-avatar-ojo" cx="36" cy="45" rx="3" ry="4" /><ellipse class="kg-avatar-ojo" cx="60" cy="45" rx="3" ry="4" /><path class="kg-avatar-sonrisa" d="M40 54q8 8 16 0" /></g>
        <path class="kg-avatar-birrete" d="m31 18 17-8 17 8-17 8-17-8Zm10 6v5q7 4 14 0v-5M65 18v12" />
    </svg>
</span>
