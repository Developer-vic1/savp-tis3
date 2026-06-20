@php
    $titulo = $titulo ?? '';
    $valor = $valor ?? 0;
    $descripcion = $descripcion ?? null;
    $estado = $estado ?? null;
@endphp

<article class="ui-card p-5">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="ui-muted text-xs font-black uppercase">{{ $titulo }}</p>
            <p class="ui-title mt-2 text-3xl font-black">{{ $valor }}</p>
        </div>
        @if ($estado)
            @include('aula-virtual.componentes.status-badge', ['estado' => $estado])
        @endif
    </div>
    @if ($descripcion)
        <p class="ui-subtitle mt-3 text-sm leading-6">{{ $descripcion }}</p>
    @endif
</article>
