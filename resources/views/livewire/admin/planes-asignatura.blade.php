<div class="space-y-6">
    <section class="ui-card rounded-[2rem] p-6 sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="ui-kicker">Planificación académica</p>
                <h1 class="ui-title mt-2 text-3xl font-black">Planes de Asignatura</h1>
                <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">
                    Vincula asignatura, docente, curso, paralelo, turno y gestión usando la estructura académica disponible.
                </p>
            </div>
            <button wire:click="abrirCrear" class="ui-btn-primary">Nuevo plan</button>
        </div>
        <div class="ui-alert-info mt-5">La tabla actual no dispone de objetivo, contenidos, criterios, observaciones ni periodo evaluativo. El plan gestiona únicamente las relaciones y horas existentes.</div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([['Planes registrados', $metricas['total']], ['Planes activos', $metricas['activos']], ['Horas planificadas', $metricas['horas']], ['Docentes asignados', $metricas['docentes']]] as [$label, $value])
            <article class="ui-card rounded-[1.6rem] p-5"><p class="ui-kicker">{{ $label }}</p><p class="mt-3 text-3xl font-black" style="color: var(--ui-primary)">{{ $value }}</p></article>
        @endforeach
    </section>

    <section class="ui-card rounded-[2rem] p-5">
        <div class="grid gap-3 lg:grid-cols-[1fr_260px_220px_auto]">
            <input wire:model.live.debounce.350ms="search" class="ui-input" placeholder="Buscar plan, asignatura o docente...">
            <select wire:model.live="asignaturaFiltro" class="ui-input"><option value="">Todas las asignaturas</option>@foreach($asignaturas as $item)<option value="{{ $item->cod_asi }}">{{ $item->nom_asi }}</option>@endforeach</select>
            <select wire:model.live="estado" class="ui-input"><option value="">Todos los estados</option><option value="ACTIVO">ACTIVO</option><option value="INACTIVO">INACTIVO</option></select>
            <button wire:click="limpiarFiltros" class="ui-btn-secondary">Limpiar</button>
        </div>
    </section>

    <section class="ui-card overflow-hidden rounded-[2rem]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[var(--ui-border)]">
                <thead style="background: var(--ui-surface-muted)"><tr>
                    @foreach(['Asignatura','Docente','Contexto académico','Horas','Estado','Acciones'] as $label)<th class="px-5 py-4 text-left text-xs font-black uppercase tracking-[0.12em]" style="color: var(--ui-muted)">{{ $label }}</th>@endforeach
                </tr></thead>
                <tbody class="divide-y divide-[var(--ui-border)]">
                    @forelse($planes as $plan)
                        @php($persona = $plan->docente?->personalInstitucional?->persona)
                        <tr class="hover:bg-[var(--ui-surface-muted)]">
                            <td class="px-5 py-4 text-sm font-bold" style="color: var(--ui-text)">{{ $plan->asignatura?->nom_asi }}</td>
                            <td class="px-5 py-4 text-sm" style="color: var(--ui-text)">{{ trim(($persona?->nom_per ?? '').' '.($persona?->ape_pat_per ?? '').' '.($persona?->ape_mat_per ?? '')) }}</td>
                            <td class="px-5 py-4 text-sm" style="color: var(--ui-muted)">{{ $plan->curso?->nom_cur }} {{ $plan->paralelo?->nom_par }} · {{ $plan->turno?->nom_tur }} · {{ $plan->gestionAcademica?->ani_gea }}</td>
                            <td class="px-5 py-4 text-sm font-black" style="color: var(--ui-info)">{{ $plan->hor_pas }}</td>
                            <td class="px-5 py-4"><span class="{{ $plan->est_pas === 'ACTIVO' ? 'ui-badge-success' : 'ui-badge-warning' }}">{{ $plan->est_pas }}</span></td>
                            <td class="px-5 py-4"><div class="flex gap-2"><button wire:click="abrirEditar('{{ $plan->cod_pas }}')" class="ui-btn-secondary">Editar</button><button wire:click="cambiarEstado('{{ $plan->cod_pas }}')" class="ui-btn-secondary">{{ $plan->est_pas === 'ACTIVO' ? 'Desactivar' : 'Activar' }}</button></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-16 text-center"><p class="text-lg font-black" style="color: var(--ui-text)">Aún no existen planes de asignatura</p><p class="mt-2 text-sm" style="color: var(--ui-muted)">Registra la primera asignación académica institucional.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t p-4" style="border-color: var(--ui-border)">{{ $planes->links() }}</div>
    </section>

    @if($modalFormulario)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" wire:click.self="cerrarFormulario">
            <section class="ui-card max-h-[92vh] w-full max-w-5xl overflow-y-auto rounded-[2rem] p-6 sm:p-8">
                <div class="flex justify-between gap-4"><div><p class="ui-kicker">Vista previa y validación</p><h2 class="ui-title mt-2 text-2xl font-black">{{ $editando ? 'Editar plan' : 'Nuevo plan de asignatura' }}</h2></div><button wire:click="cerrarFormulario" class="ui-btn-secondary">Cerrar</button></div>
                <div class="mt-6 grid gap-5 lg:grid-cols-[1.2fr_.8fr]">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label><span class="ui-label">Asignatura</span><select wire:model.live="form.cod_asi" class="ui-input mt-2"><option value="">Seleccionar</option>@foreach($asignaturas as $item)<option value="{{ $item->cod_asi }}">{{ $item->nom_asi }}</option>@endforeach</select></label>
                        <label><span class="ui-label">Docente</span><select wire:model.live="form.cod_doc" class="ui-input mt-2"><option value="">Seleccionar</option>@foreach($docentes as $item)@php($p=$item->personalInstitucional?->persona)<option value="{{ $item->cod_doc }}">{{ trim(($p?->nom_per ?? '').' '.($p?->ape_pat_per ?? '')) }}</option>@endforeach</select></label>
                        <label><span class="ui-label">Curso</span><select wire:model.live="form.cod_cur" class="ui-input mt-2"><option value="">Seleccionar</option>@foreach($cursos as $item)<option value="{{ $item->cod_cur }}">{{ $item->nom_cur }}</option>@endforeach</select></label>
                        <label><span class="ui-label">Paralelo</span><select wire:model.live="form.cod_par" class="ui-input mt-2"><option value="">Seleccionar</option>@foreach($paralelos as $item)<option value="{{ $item->cod_par }}">{{ $item->nom_par }}</option>@endforeach</select></label>
                        <label><span class="ui-label">Turno</span><select wire:model.live="form.cod_tur" class="ui-input mt-2"><option value="">Seleccionar</option>@foreach($turnos as $item)<option value="{{ $item->cod_tur }}">{{ $item->nom_tur }}</option>@endforeach</select></label>
                        <label><span class="ui-label">Gestión</span><select wire:model.live="form.cod_gea" class="ui-input mt-2"><option value="">Seleccionar</option>@foreach($gestiones as $item)<option value="{{ $item->cod_gea }}">{{ $item->ani_gea }}</option>@endforeach</select></label>
                        <label><span class="ui-label">Horas asignadas</span><input type="number" min="1" max="40" wire:model.live="form.hor_pas" class="ui-input mt-2"></label>
                        <label><span class="ui-label">Estado</span><select wire:model.live="form.est_pas" class="ui-input mt-2"><option>ACTIVO</option><option>INACTIVO</option></select></label>
                    </div>
                    <aside class="space-y-4">
                        <div class="ui-card-soft p-5"><p class="ui-kicker">Completitud</p><p class="mt-2 text-3xl font-black" style="color: var(--ui-primary)">{{ $analisis['completitud'] ?? 0 }}%</p></div>
                        @if(!empty($analisis['bloqueos']))<div class="ui-alert-warning">@foreach($analisis['bloqueos'] as $item)<p>{{ $item }}</p>@endforeach</div>@else<div class="ui-alert-success">La combinación académica está lista para guardarse.</div>@endif
                    </aside>
                </div>
                <div class="mt-6 flex justify-end gap-3"><button wire:click="cerrarFormulario" class="ui-btn-secondary">Cancelar</button><button wire:click="guardar" class="ui-btn-primary" @disabled(!($analisis['puede_guardar'] ?? false))>Guardar plan</button></div>
            </section>
        </div>
    @endif
</div>
