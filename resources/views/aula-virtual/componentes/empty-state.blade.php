@php
    $titulo = $titulo ?? 'Información académica disponible según inscripción.';
    $descripcion = $descripcion ?? 'Cuando exista información habilitada para tu cuenta, aparecerá en esta sección.';
@endphp

<section class="ui-panel text-center">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-lg bg-[var(--savp-green-soft)] text-[var(--savp-green)]">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.75c-2.25-1.5-5.25-1.5-7.5 0v11.25c2.25-1.5 5.25-1.5 7.5 0m0-11.25c2.25-1.5 5.25-1.5 7.5 0v11.25c-2.25-1.5-5.25-1.5-7.5 0m0-11.25v11.25" />
        </svg>
    </div>
    <h3 class="ui-title mt-4 text-lg font-black">{{ $titulo }}</h3>
    <p class="ui-subtitle mx-auto mt-2 max-w-xl text-sm leading-7">{{ $descripcion }}</p>
</section>
