@props(['analisis' => [], 'titulo' => 'Asistencia inteligente', 'mostrarCompletitud' => true, 'mostrarSugerencias' => true, 'mostrarCoincidencias' => true])
@php
    $blockers = $analisis['bloqueos'] ?? [];
    $warnings = $analisis['advertencias'] ?? [];
    $suggestions = $mostrarSugerencias ? ($analisis['sugerencias'] ?? []) : [];
    $completion = $analisis['completitud'] ?? data_get($analisis, 'resumen.campos_completitud.porcentaje');
    $action = data_get($analisis, 'resumen.accion_recomendada');
@endphp
<section {{ $attributes->class(['ui-card space-y-3 p-4']) }} aria-label="{{ $titulo }}" aria-live="polite" aria-atomic="true">
    <h3 class="font-bold" style="color: var(--ui-text)">{{ $titulo }}</h3>
    @if($analisis['mensaje'] ?? false)<p class="ui-muted text-sm">{{ $analisis['mensaje'] }}</p>@endif
    @foreach(['Bloqueos' => [$blockers, 'ui-alert-danger'], 'Advertencias' => [$warnings, 'ui-alert-warning'], 'Sugerencias' => [array_is_list($suggestions) ? $suggestions : [], 'ui-alert-info']] as $label => [$items, $style])
        @if($items)
            <div class="{{ $style }}"><p class="font-bold">{{ $label }} ({{ count($items) }})</p><ul class="list-disc pl-5">@foreach($items as $item)<li>{{ $item }}</li>@endforeach</ul></div>
        @endif
    @endforeach
    @if($mostrarCoincidencias && data_get($analisis, 'duplicidad.registro.nombre'))
        <p class="ui-muted text-sm">Coincidencia: {{ data_get($analisis, 'duplicidad.registro.nombre') }} · {{ data_get($analisis, 'duplicidad.similitud') }}% de similitud.</p>
    @endif
    @if($mostrarCompletitud && is_numeric($completion))
        <p class="ui-muted text-sm">Completitud preventiva: {{ max(0, min(100, (int) $completion)) }}%</p>
    @endif
    @if(is_array($action))
        <div class="ui-alert-info"><p class="font-bold">{{ $action['titulo'] ?? 'Acción recomendada' }}</p><p>{{ $action['descripcion'] ?? '' }}</p></div>
    @endif
</section>
