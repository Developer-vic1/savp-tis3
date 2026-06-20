@php
    $href = $href ?? null;
    $type = $type ?? 'button';
    $icon = $icon ?? 'entrar';
    $label = $label ?? '';
    $title = $title ?? null;
    $variant = $variant ?? 'primary';

    $classes = $variant === 'primary'
        ? 'inline-flex items-center justify-center gap-2 rounded-lg bg-[var(--savp-green)] px-4 py-2.5 text-sm font-bold text-white transition hover:brightness-105'
        : 'inline-flex items-center justify-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-bold transition hover:bg-[var(--ui-surface-muted)]';

    $icons = [
        'entrar' => 'M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3',
        'crear-material' => 'M12 4.5v15m7.5-7.5h-15',
        'crear-tarea' => 'M9 12.75 11.25 15 15 9.75M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12A2.25 2.25 0 0 1 17.25 20.25H6.75A2.25 2.25 0 0 1 4.5 18V6A2.25 2.25 0 0 1 6.75 3.75Z',
        'entregar' => 'M12 16.5V3.75m0 0L7.5 8.25M12 3.75l4.5 4.5M4.5 16.5v1.875A2.625 2.625 0 0 0 7.125 21h9.75a2.625 2.625 0 0 0 2.625-2.625V16.5',
        'guardar' => 'M16.5 3.75h-9A2.25 2.25 0 0 0 5.25 6v12A2.25 2.25 0 0 0 7.5 20.25h9A2.25 2.25 0 0 0 18.75 18V6l-2.25-2.25ZM9 3.75v6h6v-6',
        'enviar' => 'M6 12 3.269 3.125A59.77 59.77 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.875L6 12Zm0 0h7.5',
        'revisar' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        'calificar' => 'M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111 5.52.442c.499.04.701.663.321.988l-4.204 3.602 1.285 5.385a.562.562 0 0 1-.84.61L12 16.79l-4.728 2.846a.562.562 0 0 1-.84-.61l1.285-5.385-4.204-3.602a.562.562 0 0 1 .321-.988l5.52-.442 2.126-5.111Z',
        'descargar' => 'M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M7.5 10.5 12 15m0 0 4.5-4.5M12 15V3',
        'reporte' => 'M3.75 3v18h16.5V8.25L15 3H3.75Zm11.25 0v5.25h5.25M8.25 13.5h7.5M8.25 16.5h7.5M8.25 10.5h3',
        'observacion' => 'M7.5 8.25h9m-9 3h6m-8.25 8.25V5.25A2.25 2.25 0 0 1 7.5 3h9a2.25 2.25 0 0 1 2.25 2.25v9A2.25 2.25 0 0 1 16.5 16.5H9l-3.75 3Z',
        'asistencia' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 1 1 15 0',
        'orientacion' => 'M12 18.75a6.75 6.75 0 1 0 0-13.5 6.75 6.75 0 0 0 0 13.5ZM12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3',
    ];
@endphp

@if ($href)
    <a href="{{ $href }}" class="{{ $classes }}" title="{{ $title ?? $label }}" aria-label="{{ $title ?? $label }}">
        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icons[$icon] ?? $icons['entrar'] }}" /></svg>
        @if ($label)<span>{{ $label }}</span>@endif
    </a>
@else
    <button type="{{ $type }}" class="{{ $classes }}" title="{{ $title ?? $label }}" aria-label="{{ $title ?? $label }}">
        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $icons[$icon] ?? $icons['entrar'] }}" /></svg>
        @if ($label)<span>{{ $label }}</span>@endif
    </button>
@endif
