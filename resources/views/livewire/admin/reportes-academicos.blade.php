<div class="space-y-6">
    <section class="ui-card rounded-[2rem] p-6 sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="ui-kicker">Análisis académico-vocacional</p>
                <h1 class="ui-title mt-2 text-3xl font-black">Reportes académicos</h1>
                <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">
                    Integra rendimiento, riesgo y especialidad técnica BTH para apoyar decisiones de orientación.
                </p>
            </div>
            <button disabled class="ui-btn-secondary" title="Requiere autorización para instalar spatie/laravel-pdf">
                Exportar PDF
            </button>
        </div>
        <div class="ui-alert-info mt-5">
            Las áreas y carreras se presentan como inferencia institucional calculada. No se persisten porque actualmente no existen tablas de carreras, intereses ni perfiles vocacionales.
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['Promedio general', number_format($metricas['promedio'], 2), 'var(--ui-primary)'],
            ['Registros analizados', $metricas['registros'], 'var(--ui-info)'],
            ['En riesgo', $metricas['riesgo'], 'var(--ui-danger)'],
            ['Destacados', $metricas['destacados'], 'var(--ui-success)'],
        ] as [$label, $value, $color])
            <article class="ui-card rounded-[1.6rem] p-5">
                <p class="ui-kicker">{{ $label }}</p>
                <p class="mt-3 text-3xl font-black" style="color: {{ $color }}">{{ $value }}</p>
            </article>
        @endforeach
    </section>

    <section class="ui-card rounded-[2rem] p-5">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[repeat(5,minmax(0,1fr))_auto]">
            <select wire:model.live="periodoFiltro" class="ui-input">
                <option value="">Todos los periodos</option>
                @foreach($periodos as $item)<option value="{{ $item->cod_pev }}">{{ $item->nom_pev }}</option>@endforeach
            </select>
            <select wire:model.live="asignaturaFiltro" class="ui-input">
                <option value="">Todas las asignaturas</option>
                @foreach($asignaturas as $item)<option value="{{ $item->cod_asi }}">{{ $item->nom_asi }}</option>@endforeach
            </select>
            <select wire:model.live="estudianteFiltro" class="ui-input">
                <option value="">Todos los estudiantes</option>
                @foreach($estudiantes as $item)
                    @php($persona = $item->persona)
                    <option value="{{ $item->cod_est }}">{{ trim(($persona?->ape_pat_per ?? '').' '.($persona?->ape_mat_per ?? '').' '.($persona?->nom_per ?? '')) }}</option>
                @endforeach
            </select>
            <select wire:model.live="especialidadFiltro" class="ui-input">
                <option value="">Todas las especialidades</option>
                @foreach($especialidades as $item)<option value="{{ $item->cod_esp }}">{{ $item->nom_esp }}</option>@endforeach
            </select>
            <select wire:model.live="desempenoFiltro" class="ui-input">
                <option value="">Todo desempeño</option>
                @foreach(['Destacado', 'Aprobado', 'En seguimiento', 'En riesgo'] as $item)<option>{{ $item }}</option>@endforeach
            </select>
            <button wire:click="limpiarFiltros" class="ui-btn-secondary">Limpiar</button>
        </div>
    </section>

    <section class="grid gap-5 xl:grid-cols-2">
        <article class="ui-card rounded-[2rem] p-6">
            <div class="flex items-center justify-between gap-3">
                <div><p class="ui-kicker">Indicador comparativo</p><h2 class="ui-title mt-2 text-xl font-black">Rendimiento por asignatura</h2></div>
                <span class="ui-badge-info">{{ $rendimientoAsignatura->count() }} asignaturas</span>
            </div>
            <div class="mt-5 space-y-3">
                @forelse($rendimientoAsignatura as $item)
                    <div class="ui-card-soft flex items-center justify-between gap-4 p-4">
                        <div>
                            <p class="font-black" style="color: var(--ui-text)">{{ $item['nombre'] }}</p>
                            <p class="mt-1 text-xs" style="color: var(--ui-muted)">{{ $item['registros'] }} registros · {{ $item['riesgo'] }} en riesgo</p>
                        </div>
                        <span class="text-xl font-black" style="color: var(--ui-primary)">{{ number_format($item['promedio'], 2) }}</span>
                    </div>
                @empty
                    <div class="py-12 text-center text-sm" style="color: var(--ui-muted)">No existen calificaciones para los filtros seleccionados.</div>
                @endforelse
            </div>
        </article>

        <article class="ui-card rounded-[2rem] p-6">
            <div class="flex items-center justify-between gap-3">
                <div><p class="ui-kicker">Distribución</p><h2 class="ui-title mt-2 text-xl font-black">Desempeño cualitativo</h2></div>
                <span class="ui-badge-info">{{ $rendimientoPeriodo->count() }} periodos</span>
            </div>
            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                @foreach(['Destacado', 'Aprobado', 'En seguimiento', 'En riesgo'] as $estado)
                    <div class="ui-card-soft p-4">
                        <p class="text-sm font-bold" style="color: var(--ui-muted)">{{ $estado }}</p>
                        <p class="mt-2 text-2xl font-black" style="color: {{ $estado === 'En riesgo' ? 'var(--ui-danger)' : ($estado === 'Destacado' ? 'var(--ui-success)' : 'var(--ui-primary)') }}">
                            {{ $distribucion[$estado] ?? 0 }}
                        </p>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 space-y-3">
                @foreach($rendimientoPeriodo as $item)
                    <div class="flex items-center justify-between border-t pt-3 text-sm" style="border-color: var(--ui-border)">
                        <span style="color: var(--ui-text)">{{ $item['nombre'] }} · {{ $item['registros'] }} registros</span>
                        <strong style="color: var(--ui-primary)">{{ number_format($item['promedio'], 2) }}</strong>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="ui-card rounded-[2rem] p-6">
        <div>
            <p class="ui-kicker">Intereses académico-profesionales</p>
            <h2 class="ui-title mt-2 text-2xl font-black">Compatibilidad por especialidad técnica</h2>
            <p class="ui-muted mt-2 text-sm">La explicación combina especialidad BTH y promedio de las calificaciones filtradas.</p>
        </div>
        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            @forelse($orientaciones as $item)
                <article class="ui-card-soft p-5">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-lg font-black" style="color: var(--ui-text)">{{ $item['especialidad'] }}</p>
                            <p class="mt-1 text-sm font-bold" style="color: var(--ui-primary)">{{ $item['area'] }}</p>
                        </div>
                        <span class="ui-badge-success">{{ $item['porcentaje'] }}% · Prom. {{ number_format($item['promedio'], 2) }}</span>
                    </div>
                    <p class="mt-4 text-sm leading-6" style="color: var(--ui-muted)">{{ $item['explicacion'] }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach($item['carreras'] as $carrera)<span class="ui-badge-info">{{ $carrera }}</span>@endforeach
                    </div>
                    <p class="mt-4 text-xs font-bold uppercase tracking-[0.12em]" style="color: var(--ui-muted)">{{ $item['estudiantes'] }} estudiantes asociados</p>
                </article>
            @empty
                <div class="col-span-full py-14 text-center">
                    <p class="text-lg font-black" style="color: var(--ui-text)">Aún no hay datos suficientes para inferir compatibilidad</p>
                    <p class="mt-2 text-sm" style="color: var(--ui-muted)">Registra calificaciones de estudiantes vinculados a especialidades técnicas.</p>
                </div>
            @endforelse
        </div>
    </section>

    <section class="ui-card overflow-hidden rounded-[2rem]">
        <div class="border-b p-6" style="border-color: var(--ui-border)">
            <p class="ui-kicker">Trazabilidad</p>
            <h2 class="ui-title mt-2 text-xl font-black">Resultados analizados</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[var(--ui-border)]">
                <thead style="background: var(--ui-surface-muted)">
                    <tr>@foreach(['Estudiante', 'Especialidad', 'Asignatura', 'Periodo', 'Nota', 'Desempeño'] as $label)<th class="px-5 py-4 text-left text-xs font-black uppercase tracking-[0.12em]" style="color: var(--ui-muted)">{{ $label }}</th>@endforeach</tr>
                </thead>
                <tbody class="divide-y divide-[var(--ui-border)]">
                    @forelse($calificaciones as $item)
                        @php($persona = $item->estudiante?->persona)
                        <tr class="hover:bg-[var(--ui-surface-muted)]">
                            <td class="px-5 py-4 text-sm font-bold" style="color: var(--ui-text)">{{ trim(($persona?->nom_per ?? '').' '.($persona?->ape_pat_per ?? '').' '.($persona?->ape_mat_per ?? '')) }}</td>
                            <td class="px-5 py-4 text-sm" style="color: var(--ui-muted)">{{ $item->estudiante?->especialidad?->nom_esp ?? 'Sin especialidad' }}</td>
                            <td class="px-5 py-4 text-sm" style="color: var(--ui-text)">{{ $item->asignatura?->nom_asi }}</td>
                            <td class="px-5 py-4 text-sm" style="color: var(--ui-muted)">{{ $item->periodoEvaluacion?->nom_pev }}</td>
                            <td class="px-5 py-4 text-lg font-black" style="color: var(--ui-primary)">{{ number_format($item->not_cal, 2) }}</td>
                            <td class="px-5 py-4"><span class="{{ $item->desempeno_calculado === 'En riesgo' ? 'ui-badge-danger' : ($item->desempeno_calculado === 'Destacado' ? 'ui-badge-success' : 'ui-badge-info') }}">{{ $item->desempeno_calculado }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-16 text-center text-sm" style="color: var(--ui-muted)">No existen resultados para mostrar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
