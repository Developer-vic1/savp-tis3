<section class="ui-card p-5 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="ui-muted text-xs font-bold uppercase tracking-wider">Organización académica · Gestión {{ $gestionVigente?->ani_gea ?? 'sin gestión activa' }}</p>
            <h2 class="ui-title mt-1 text-2xl font-black">Gestión de Paralelos</h2>
            <p class="ui-muted mt-1 text-sm">Cada letra es un catálogo; cada grado y turno tiene su propio grupo.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="abrirModalHistoricos" class="ui-btn-secondary"><i class="ph-duotone ph-clock-counter-clockwise" aria-hidden="true"></i> Históricos ({{ $resumen['historicos'] }})</button>
            <button type="button" wire:click="abrirModalCrear" class="ui-btn-primary"><i class="ph-duotone ph-plus" aria-hidden="true"></i> Registrar paralelo</button>
        </div>
    </div>
    <div class="mt-5 grid gap-3 sm:grid-cols-3">
        @foreach ([['Estudiantes vigentes', $resumen['estudiantes'], 'Inscripciones activas de esta gestión'], ['Grupos organizados', count($mapaGrupos), 'Grado + paralelo + turno'], ['Catálogo disponible', $resumen['activos'], $resumen['sin_uso'].' sin uso en esta gestión']] as [$titulo, $valor, $ayuda])
            <div class="ui-card-soft flex items-center gap-4 p-4"><strong class="ui-title text-3xl">{{ $valor }}</strong><div><p class="ui-title text-sm font-bold">{{ $titulo }}</p><p class="ui-muted text-xs">{{ $ayuda }}</p></div></div>
        @endforeach
    </div>
</section>

@php
    $turnosMapa = collect($mapaGrupos)->pluck('turno')->unique()->values();
    $gradosMapa = collect($mapaGrupos)->pluck('curso')->unique()->values();
    $letrasMapa = collect($mapaGrupos)->unique('codigo')->sortBy('paralelo')->values();
    $maximoDistribucion = max(1, collect($distribucion)->max('valor'));
@endphp
<section class="grid gap-4 xl:grid-cols-[1.5fr_1fr]">
    <article class="ui-card p-5" x-data="{ turno: @js($turnosMapa->first() ?? ''), vista: 'cifras' }">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div><h3 class="ui-title text-lg font-black">Mapa de grupos</h3><p class="ui-muted text-xs">Estudiantes / capacidad registrada. Selecciona un grupo para ver su ficha.</p></div>
            <div><x-selector-institucional enlace="turno" identificador="turno-mapa" etiqueta="Turno del mapa" :opciones="$turnosMapa->map(fn($t)=>['valor'=>$t,'etiqueta'=>$t])->all()" /></div>
        </div>
        <div class="mt-3 flex flex-wrap gap-2" aria-label="Representación del mapa"><button type="button" class="ui-btn-secondary text-xs" x-on:click="vista = 'cifras'" :aria-pressed="vista === 'cifras'">Cifras por grupo</button><button type="button" class="ui-btn-secondary text-xs" x-on:click="vista = 'ocupacion'" :aria-pressed="vista === 'ocupacion'">Anillos de ocupación</button></div>
        @if (!$gestionVigente)
            <div class="ui-alert-warning mt-4">No hay gestión activa. No se presenta la historia como organización vigente.</div>
        @elseif ($mapaGrupos)
            <div class="mt-4 overflow-x-auto">
                <table class="w-full text-sm"><caption class="sr-only">Distribución por grado, paralelo y turno</caption>
                    <thead><tr class="ui-muted text-xs"><th scope="col" class="p-2 text-left">Grado</th>@foreach ($letrasMapa as $letra)<th scope="col" class="p-2">{{ $letra['paralelo'] }}</th>@endforeach</tr></thead>
                    <tbody>@foreach ($gradosMapa as $grado)<tr><th scope="row" class="ui-title p-2 text-left font-semibold">{{ $grado }}</th>
                        @foreach ($letrasMapa as $letra)<td class="p-1 text-center">
                            @foreach ($turnosMapa as $turno)
                                @php $celda = collect($mapaGrupos)->first(fn ($g) => $g['curso'] === $grado && $g['codigo'] === $letra['codigo'] && $g['turno'] === $turno); @endphp
                                <div x-show="turno === @js($turno)">
                                    @if ($celda)
                                        <button type="button" wire:click="abrirModalDetalle('{{ $letra['codigo'] }}')" class="ui-card-soft w-full px-2 py-3 hover:opacity-80" aria-label="Ver paralelo {{ $letra['paralelo'] }}, {{ $grado }}, {{ $turno }}" style="color: {{ $celda['capacidad'] > 0 && $celda['estudiantes'] > $celda['capacidad'] ? 'var(--ui-danger)' : 'var(--ui-primary)' }};">
                                            <span x-show="vista === 'cifras'"><strong>{{ $celda['estudiantes'] }}</strong><span class="ui-muted text-xs"> / {{ $celda['capacidad'] ?: '—' }}</span></span>
                                            <span x-show="vista === 'ocupacion'" class="block" x-cloak>
                                                @if ($celda['capacidad'] > 0)
                                                    @php $ocupacion = round($celda['estudiantes'] / $celda['capacidad'] * 100); @endphp
                                                    <svg viewBox="0 0 44 44" class="mx-auto h-11 w-11" role="img" aria-label="{{ $ocupacion }}% de capacidad registrada; {{ $celda['estudiantes'] }} estudiantes para {{ $celda['capacidad'] }} plazas">
                                                        <circle cx="22" cy="22" r="18" fill="none" stroke="var(--ui-border)" stroke-width="3" />
                                                        <circle cx="22" cy="22" r="18" fill="none" stroke="currentColor" stroke-width="3" pathLength="100" stroke-dasharray="{{ min(100, $ocupacion) }} 100" transform="rotate(-90 22 22)" />
                                                        <text x="22" y="25" text-anchor="middle" fill="var(--ui-text)" font-size="10">{{ $ocupacion }}%</text>
                                                    </svg><span class="ui-muted text-xs">{{ $celda['estudiantes'] }} / {{ $celda['capacidad'] }}</span>
                                                @else<span class="ui-muted text-xs">Sin capacidad registrada</span>@endif
                                            </span>
                                            @if ($celda['capacidad'] > 0 && $celda['estudiantes'] > $celda['capacidad'])<span class="block text-xs">Excede capacidad</span>@endif
                                        </button>
                                    @else<span class="ui-muted text-xs">Sin grupo</span>@endif
                                </div>
                            @endforeach
                        </td>@endforeach
                    </tr>@endforeach</tbody>
                </table>
            </div>
        @else<p class="ui-muted mt-4 text-sm">Todavía no hay grupos activos en esta gestión.</p>@endif
        <p class="ui-muted mt-3 text-xs">La capacidad proviene del grupo. Un estudiante puede participar también en el turno técnico; las celdas no se suman como personas distintas.</p>
    </article>
    <article class="ui-card p-5">
        <h3 class="ui-title text-lg font-black">Distribución y decisiones</h3>
        <div class="mt-4 flex h-28 items-end gap-3" role="img" aria-label="Estudiantes vigentes por paralelo: {{ collect($distribucion)->map(fn ($d) => $d['nombre'].': '.$d['valor'])->join(', ') }}">
            @foreach ($distribucion as $item)
                <div class="flex h-full min-w-0 flex-1 flex-col justify-end text-center"><strong class="ui-title text-sm">{{ $item['valor'] }}</strong><div class="mx-auto mt-1 w-full max-w-16 rounded-t" style="height: {{ max(3, round($item['valor'] / $maximoDistribucion * 70)) }}px; background: var(--ui-primary);"></div><span class="ui-muted mt-1 text-xs">{{ $item['nombre'] }}</span></div>
            @endforeach
        </div>
        <p class="ui-muted mt-3 text-xs">Total por letra; compara grados y turnos en el mapa antes de proponer cambios.</p>
        <div class="mt-4 space-y-2">
            @foreach ($recomendaciones as $recomendacion)
                <div class="{{ match ($recomendacion['tipo']) { 'danger'=>'ui-alert-danger', 'warning'=>'ui-alert-warning', default=>'ui-alert-info' } }}"><p class="text-sm font-bold">{{ $recomendacion['titulo'] }}</p><p class="mt-1 text-xs leading-5">{{ $recomendacion['mensaje'] }}</p></div>
            @endforeach
        </div>
        <details class="ui-muted mt-4 text-xs"><summary class="cursor-pointer font-bold">Antes de incorporar un paralelo</summary><p class="mt-2 leading-5">Verifica demanda por grado, aula disponible, personal y presupuesto. Para fiscales y de convenio, la RM 0001/2026, art. 21, exige SICH actualizado, informe distrital y aprobación departamental. Registrar una letra no autoriza abrir grupos.</p><a href="https://www.minedu.gob.bo/files/documentos-normativos/resoluciones-ministeriales/1_RM_0001_EDUCACIN_REGULAR.pdf" target="_blank" rel="noopener" class="underline">Consultar norma oficial</a></details>
    </article>
</section>
