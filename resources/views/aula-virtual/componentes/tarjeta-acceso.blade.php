@php
    $titulo = $titulo ?? 'Acceso';
    $descripcion = $descripcion ?? 'Modulo disponible proximamente.';
    $valor = $valor ?? null;
    $badge = $badge ?? null;
    $tono = $tono ?? 'success';
@endphp

<article class="ui-card ui-card-hover rounded-[1.6rem] p-5">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="ui-muted text-xs font-black uppercase tracking-[0.16em]">{{ $titulo }}</p>

            @if ($valor !== null)
                <p class="ui-title mt-2 text-3xl font-black">{{ $valor }}</p>
            @endif
        </div>

        @if ($badge)
            <span class="ui-badge-{{ $tono }}">{{ $badge }}</span>
        @endif
    </div>

    <p class="ui-subtitle mt-3 text-sm leading-7">{{ $descripcion }}</p>
</article>
