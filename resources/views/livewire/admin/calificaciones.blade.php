<div class="space-y-6">
    <section class="ui-card rounded-[2rem] p-6 sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div><p class="ui-kicker">Evaluación académica</p><h1 class="ui-title mt-2 text-3xl font-black">Calificaciones</h1><p class="ui-muted mt-2 max-w-3xl text-sm leading-6">Registra notas sobre 100, identifica riesgo académico y fortalezas para la orientación estudiantil.</p></div>
            <div class="flex gap-3"><button disabled class="ui-btn-secondary" title="Requiere maatwebsite/excel">Importar calificaciones</button><button wire:click="abrirCrear" class="ui-btn-primary">Nueva calificación</button></div>
        </div>
        <div class="ui-alert-info mt-5">La importación queda preparada visualmente, pero requiere autorización para instalar maatwebsite/excel.</div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="ui-card rounded-[1.6rem] p-5"><p class="ui-kicker">Promedio general</p><p class="mt-3 text-3xl font-black" style="color: var(--ui-primary)">{{ number_format($metricas['promedio'], 2) }}</p></article>
        <article class="ui-card rounded-[1.6rem] p-5"><p class="ui-kicker">En riesgo</p><p class="mt-3 text-3xl font-black" style="color: var(--ui-danger)">{{ $metricas['riesgo'] }}</p></article>
        <article class="ui-card rounded-[1.6rem] p-5"><p class="ui-kicker">Destacadas</p><p class="mt-3 text-3xl font-black" style="color: var(--ui-success)">{{ $metricas['destacadas'] }}</p></article>
        <article class="ui-card rounded-[1.6rem] p-5"><p class="ui-kicker">Menor promedio</p><p class="mt-3 text-lg font-black" style="color: var(--ui-warning)">{{ $metricas['menor']['nombre'] ?? 'Sin datos' }}</p><p class="mt-1 text-sm" style="color: var(--ui-muted)">{{ $metricas['menor']['promedio'] ?? 0 }}</p></article>
    </section>

    <section class="ui-card rounded-[2rem] p-5">
        <div class="grid gap-3 xl:grid-cols-[1fr_230px_260px_200px_auto]">
            <input wire:model.live.debounce.350ms="search" class="ui-input" placeholder="Buscar estudiante o asignatura...">
            <select wire:model.live="periodoFiltro" class="ui-input"><option value="">Todos los periodos</option>@foreach($periodos as $item)<option value="{{ $item->cod_pev }}">{{ $item->nom_pev }}</option>@endforeach</select>
            <select wire:model.live="asignaturaFiltro" class="ui-input"><option value="">Todas las asignaturas</option>@foreach($asignaturas as $item)<option value="{{ $item->cod_asi }}">{{ $item->nom_asi }}</option>@endforeach</select>
            <select wire:model.live="estado" class="ui-input"><option value="">Todos los estados</option><option>ACTIVO</option><option>INACTIVO</option><option>ANULADO</option></select>
            <button wire:click="limpiarFiltros" class="ui-btn-secondary">Limpiar</button>
        </div>
    </section>

    <section class="ui-card overflow-hidden rounded-[2rem]">
        <div class="overflow-x-auto"><table class="min-w-full divide-y divide-[var(--ui-border)]">
            <thead style="background: var(--ui-surface-muted)"><tr>@foreach(['Estudiante','Asignatura','Periodo','Nota','Desempeño','Estado','Acciones'] as $label)<th class="px-5 py-4 text-left text-xs font-black uppercase tracking-[0.12em]" style="color: var(--ui-muted)">{{ $label }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-[var(--ui-border)]">
                @forelse($calificaciones as $calificacion)
                    @php($p=$calificacion->estudiante?->persona)
                    @php($desempeno=$soporte->clasificar((float)$calificacion->not_cal))
                    <tr class="hover:bg-[var(--ui-surface-muted)]">
                        <td class="px-5 py-4 text-sm font-bold" style="color: var(--ui-text)">{{ trim(($p?->nom_per ?? '').' '.($p?->ape_pat_per ?? '').' '.($p?->ape_mat_per ?? '')) }}</td>
                        <td class="px-5 py-4 text-sm" style="color: var(--ui-text)">{{ $calificacion->asignatura?->nom_asi }}</td>
                        <td class="px-5 py-4 text-sm" style="color: var(--ui-muted)">{{ $calificacion->periodoEvaluacion?->nom_pev }}</td>
                        <td class="px-5 py-4 text-xl font-black" style="color: var(--ui-primary)">{{ number_format($calificacion->not_cal, 2) }}</td>
                        <td class="px-5 py-4"><span class="{{ $desempeno === 'Destacado' ? 'ui-badge-success' : ($desempeno === 'En riesgo' ? 'ui-badge-danger' : 'ui-badge-info') }}">{{ $desempeno }}</span></td>
                        <td class="px-5 py-4"><span class="{{ $calificacion->est_cal === 'ACTIVO' ? 'ui-badge-success' : 'ui-badge-warning' }}">{{ $calificacion->est_cal }}</span></td>
                        <td class="px-5 py-4"><div class="flex gap-2"><button wire:click="abrirEditar('{{ $calificacion->cod_cal }}')" class="ui-btn-secondary">Editar</button><button wire:click="cambiarEstado('{{ $calificacion->cod_cal }}')" class="ui-btn-secondary">{{ $calificacion->est_cal === 'ACTIVO' ? 'Anular' : 'Activar' }}</button></div></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-16 text-center"><p class="text-lg font-black" style="color: var(--ui-text)">Aún no existen calificaciones registradas</p><p class="mt-2 text-sm" style="color: var(--ui-muted)">Registra notas para habilitar indicadores académicos y vocacionales.</p></td></tr>
                @endforelse
            </tbody>
        </table></div>
        <div class="border-t p-4" style="border-color: var(--ui-border)">{{ $calificaciones->links() }}</div>
    </section>

    @if($modalFormulario)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" wire:click.self="cerrarFormulario">
            <section class="ui-card max-h-[92vh] w-full max-w-5xl overflow-y-auto rounded-[2rem] p-6 sm:p-8">
                <div class="flex justify-between gap-4"><div><p class="ui-kicker">Vista previa académica</p><h2 class="ui-title mt-2 text-2xl font-black">{{ $editando ? 'Editar calificación' : 'Nueva calificación' }}</h2></div><button wire:click="cerrarFormulario" class="ui-btn-secondary">Cerrar</button></div>
                <div class="mt-6 grid gap-5 lg:grid-cols-[1.15fr_.85fr]">
                    <div class="space-y-4">
                        <label><span class="ui-label">Estudiante</span><select wire:model.live="form.cod_est" class="ui-input mt-2"><option value="">Seleccionar</option>@foreach($estudiantes as $item)@php($p=$item->persona)<option value="{{ $item->cod_est }}">{{ trim(($p?->ape_pat_per ?? '').' '.($p?->ape_mat_per ?? '').' '.($p?->nom_per ?? '')) }}</option>@endforeach</select></label>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label><span class="ui-label">Asignatura</span><select wire:model.live="form.cod_asi" class="ui-input mt-2"><option value="">Seleccionar</option>@foreach($asignaturas as $item)<option value="{{ $item->cod_asi }}">{{ $item->nom_asi }}</option>@endforeach</select></label>
                            <label><span class="ui-label">Periodo</span><select wire:model.live="form.cod_pev" class="ui-input mt-2"><option value="">Seleccionar</option>@foreach($periodos as $item)<option value="{{ $item->cod_pev }}">{{ $item->nom_pev }}</option>@endforeach</select></label>
                            <label><span class="ui-label">Nota sobre 100</span><input type="number" min="0" max="100" step="0.01" wire:model.live="form.not_cal" class="ui-input mt-2"></label>
                            <label><span class="ui-label">Estado</span><select wire:model.live="form.est_cal" class="ui-input mt-2"><option>ACTIVO</option><option>INACTIVO</option><option>ANULADO</option></select></label>
                        </div>
                        <label><span class="ui-label">Observación</span><textarea rows="4" wire:model.live="form.obs_cal" class="ui-input mt-2"></textarea></label>
                    </div>
                    <aside class="space-y-4">
                        <div class="ui-card-soft p-5"><p class="ui-kicker">Completitud</p><p class="mt-2 text-3xl font-black" style="color: var(--ui-primary)">{{ $analisis['completitud'] ?? 0 }}%</p></div>
                        <div class="ui-card-soft p-5"><p class="ui-kicker">Desempeño calculado</p><p class="mt-2 text-xl font-black" style="color: {{ ($analisis['riesgo'] ?? false) ? 'var(--ui-danger)' : 'var(--ui-success)' }}">{{ $analisis['desempeno'] ?? 'Sin nota' }}</p><button wire:click="aplicarObservacion" class="ui-btn-secondary mt-4 w-full">Usar observación sugerida</button></div>
                        @if(!empty($analisis['bloqueos']))<div class="ui-alert-warning">@foreach($analisis['bloqueos'] as $item)<p>{{ $item }}</p>@endforeach</div>@endif
                    </aside>
                </div>
                <div class="mt-6 flex justify-end gap-3"><button wire:click="cerrarFormulario" class="ui-btn-secondary">Cancelar</button><button wire:click="guardar" class="ui-btn-primary" @disabled(!($analisis['puede_guardar'] ?? false))>Guardar calificación</button></div>
            </section>
        </div>
    @endif
</div>
