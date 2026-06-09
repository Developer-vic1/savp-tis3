<div class="space-y-5">
    <section class="ui-card rounded-[2rem] p-6 sm:p-8">
        <p class="ui-kicker">Comunidad educativa</p>
        <h1 class="ui-title mt-2 text-3xl font-black">Gestión de Docentes</h1>
        <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">
            Este módulo reutiliza la gestión institucional existente para evitar duplicar personas, docentes y asignaciones académicas.
        </p>
    </section>
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            'Docentes registrados' => $metricasDocentes['total'],
            'Docentes activos' => $metricasDocentes['activos'],
            'Asignaciones académicas' => $metricasDocentes['carga'],
            'Especialidades por completar' => $metricasDocentes['incompletos'],
        ] as $label => $value)
            <article class="ui-card rounded-[1.6rem] p-5">
                <p class="ui-kicker">{{ $label }}</p>
                <p class="mt-3 text-3xl font-black" style="color: var(--ui-primary)">{{ $value }}</p>
            </article>
        @endforeach
    </section>
    <section class="ui-card rounded-[2rem] p-5">
        <div class="grid gap-3 md:grid-cols-[1fr_240px_auto_auto]">
            <input wire:model.live.debounce.350ms="search" class="ui-input" placeholder="Buscar docente, CI o especialidad...">
            <select wire:model.live="estado" class="ui-input">
                <option value="">Todos los estados</option>
                <option value="ACTIVO">Activos</option>
                <option value="INACTIVO">Inactivos</option>
            </select>
            <button wire:click="limpiarFiltros" class="ui-btn-secondary">Limpiar</button>
            @can('Personal_Institucional')
                <a href="{{ route('admin.personal-institucional') }}" class="ui-btn-primary">Gestionar personal</a>
            @endcan
        </div>
    </section>

    <section class="ui-card overflow-hidden rounded-[2rem]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[var(--ui-border)]">
                <thead style="background: var(--ui-surface-muted)">
                    <tr>@foreach(['Docente', 'CI', 'Especialidad profesional', 'Carga académica', 'Completitud', 'Estado'] as $label)<th class="px-5 py-4 text-left text-xs font-black uppercase tracking-[0.12em]" style="color: var(--ui-muted)">{{ $label }}</th>@endforeach</tr>
                </thead>
                <tbody class="divide-y divide-[var(--ui-border)]">
                    @forelse($docentes as $docente)
                        @php($persona = $docente->personalInstitucional?->persona)
                        @php($analisisDocente = $soporteDocente->analizarEspecialidad($docente->esp_doc))
                        <tr class="hover:bg-[var(--ui-surface-muted)]">
                            <td class="px-5 py-4 text-sm font-black" style="color: var(--ui-text)">{{ trim(($persona?->nom_per ?? '').' '.($persona?->ape_pat_per ?? '').' '.($persona?->ape_mat_per ?? '')) ?: 'Sin persona vinculada' }}</td>
                            <td class="px-5 py-4 text-sm" style="color: var(--ui-muted)">{{ $persona?->ci_per ?? 'Sin registro' }}</td>
                            <td class="px-5 py-4 text-sm" style="color: var(--ui-text)">{{ $docente->esp_doc ?: 'Por completar' }}</td>
                            <td class="px-5 py-4"><span class="ui-badge-info">{{ $docente->planAsignaturas->count() }} asignaciones</span></td>
                            <td class="px-5 py-4"><span class="{{ $analisisDocente['puede_guardar'] ? 'ui-badge-success' : 'ui-badge-warning' }}">{{ $analisisDocente['completitud'] }}%</span></td>
                            <td class="px-5 py-4"><span class="{{ $docente->est_doc === 'ACTIVO' ? 'ui-badge-success' : 'ui-badge-warning' }}">{{ $docente->est_doc }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-16 text-center"><p class="text-lg font-black" style="color: var(--ui-text)">No existen docentes para los filtros aplicados</p><p class="mt-2 text-sm" style="color: var(--ui-muted)">La vinculación se gestiona desde Personal Institucional por un usuario autorizado.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t p-4" style="border-color: var(--ui-border)">{{ $docentes->links() }}</div>
    </section>
</div>
