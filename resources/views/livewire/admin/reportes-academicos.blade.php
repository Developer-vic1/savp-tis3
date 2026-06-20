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
            {{-- Botones de descarga --}}
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.reportes.academico-general.pdf') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2"
                   style="background:#059669; focus-ring-color:#059669;"
                   title="Descargar Reporte Académico General en PDF"
                   aria-label="Descargar Reporte Académico General en PDF">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
                    </svg>
                    PDF General
                </a>
                <a href="{{ route('admin.reportes.calificaciones.pdf') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                   style="background:#047857;"
                   title="Descargar Reporte de Calificaciones en PDF"
                   aria-label="Descargar Reporte de Calificaciones en PDF">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
                    </svg>
                    PDF Calificaciones
                </a>
                <a href="{{ route('admin.reportes.estudiantes-riesgo.pdf') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                   style="background:#dc2626;"
                   title="Descargar Reporte de Estudiantes en Riesgo en PDF"
                   aria-label="Descargar Reporte de Estudiantes en Riesgo en PDF">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    PDF Riesgo
                </a>
            </div>
        </div>
        <div class="ui-alert-info mt-5">
            Las áreas y carreras se presentan como inferencia institucional calculada. No se persisten porque actualmente no existen tablas de carreras, intereses ni perfiles vocacionales.
        </div>
    </section>

    {{-- Tarjetas de reportes disponibles --}}
    <section class="grid gap-4 sm:grid-cols-3">
        {{-- PDF General --}}
        <article class="ui-card rounded-[1.6rem] p-5">
            <div class="mb-3 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background:#ecfdf5;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#059669;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-sm" style="color:var(--ui-text)">Reporte General</p>
                    <p class="text-xs" style="color:var(--ui-muted)">Académico completo</p>
                </div>
            </div>
            <p class="text-xs mb-4" style="color:var(--ui-muted)">Promedio general, distribución cualitativa, rendimiento por asignatura y compatibilidad vocacional.</p>
            <a href="{{ route('admin.reportes.academico-general.pdf') }}"
               class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90"
               style="background:#059669;"
               title="Descargar PDF Académico General" aria-label="Descargar PDF Académico General">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
                Descargar PDF
            </a>
        </article>

        {{-- PDF Calificaciones --}}
        <article class="ui-card rounded-[1.6rem] p-5">
            <div class="mb-3 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background:#ecfdf5;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#059669;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-sm" style="color:var(--ui-text)">Calificaciones</p>
                    <p class="text-xs" style="color:var(--ui-muted)">Listado detallado</p>
                </div>
            </div>
            <p class="text-xs mb-4" style="color:var(--ui-muted)">Listado completo de calificaciones activas con desempeño, notas máximas/mínimas y observaciones.</p>
            <a href="{{ route('admin.reportes.calificaciones.pdf') }}"
               class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90"
               style="background:#047857;"
               title="Descargar PDF Calificaciones" aria-label="Descargar PDF Calificaciones">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
                Descargar PDF
            </a>
        </article>

        {{-- PDF Estudiantes en Riesgo --}}
        <article class="ui-card rounded-[1.6rem] p-5">
            <div class="mb-3 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background:#fff1f2;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#dc2626;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-sm" style="color:var(--ui-text)">Estudiantes en Riesgo</p>
                    <p class="text-xs" style="color:var(--ui-muted)">Seguimiento prioritario</p>
                </div>
            </div>
            <p class="text-xs mb-4" style="color:var(--ui-muted)">Estudiantes con rendimiento crítico, clasificados por nivel de riesgo y asignaturas con bajo desempeño.</p>
            <a href="{{ route('admin.reportes.estudiantes-riesgo.pdf') }}"
               class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90"
               style="background:#dc2626;"
               title="Descargar PDF Estudiantes en Riesgo" aria-label="Descargar PDF Estudiantes en Riesgo">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
                Descargar PDF
            </a>
        </article>
    </section>

    {{-- Reportes vocacionales --}}
    <section class="grid gap-4 sm:grid-cols-3">
        {{-- PDF RIASEC --}}
        <article class="ui-card rounded-[1.6rem] p-5">
            <div class="mb-3 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background:#f5f3ff;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#7c3aed;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-sm" style="color:var(--ui-text)">Vocacional RIASEC</p>
                    <p class="text-xs" style="color:var(--ui-muted)">Perfiles orientacionales</p>
                </div>
            </div>
            <p class="text-xs mb-4" style="color:var(--ui-muted)">Perfil RIASEC institucional, distribución por especialidad técnica y carreras recomendadas.</p>
            <a href="{{ route('admin.reportes.vocacional-riasec.pdf') }}"
               class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90"
               style="background:#7c3aed;"
               title="Descargar PDF RIASEC" aria-label="Descargar PDF RIASEC">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
                Descargar PDF
            </a>
        </article>

        {{-- PDF Compatibilidad de Carreras --}}
        <article class="ui-card rounded-[1.6rem] p-5">
            <div class="mb-3 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background:#f5f3ff;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#7c3aed;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-sm" style="color:var(--ui-text)">Compatibilidad Carreras</p>
                    <p class="text-xs" style="color:var(--ui-muted)">Proyección académica</p>
                </div>
            </div>
            <p class="text-xs mb-4" style="color:var(--ui-muted)">Compatibilidad por especialidad, área profesional, riesgo académico y carreras afines por perfil.</p>
            <a href="{{ route('admin.reportes.compatibilidad-carreras.pdf') }}"
               class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90"
               style="background:#6d28d9;"
               title="Descargar PDF Compatibilidad Carreras" aria-label="Descargar PDF Compatibilidad Carreras">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
                Descargar PDF
            </a>
        </article>

        {{-- PDF Institucional Completo --}}
        <article class="ui-card rounded-[1.6rem] p-5">
            <div class="mb-3 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background:#ecfdf5;">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="color:#059669;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-sm" style="color:var(--ui-text)">Institucional Completo</p>
                    <p class="text-xs" style="color:var(--ui-muted)">Reporte integral</p>
                </div>
            </div>
            <p class="text-xs mb-4" style="color:var(--ui-muted)">Reporte completo con portada, índice, todas las secciones académicas, administrativas y vocacionales.</p>
            <a href="{{ route('admin.reportes.institucional-completo.pdf') }}"
               class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90"
               style="background:linear-gradient(135deg, #059669, #0284c7);"
               title="Descargar PDF Institucional Completo" aria-label="Descargar PDF Institucional Completo">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
                Descargar PDF Completo
            </a>
        </article>
    </section>

    {{-- Paquete descargable ZIP + SQL --}}
    <section class="ui-card rounded-[2rem] p-5">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="ui-kicker">Paquete de exportación</p>
                <p class="ui-title mt-1 text-lg font-black">Descarga masiva de reportes</p>
                <p class="ui-muted mt-1 text-xs">ZIP con todos los PDFs + respaldo SQL académico organizados por carpeta.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.reportes.paquete.zip') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                   style="background:#0f172a;"
                   title="Descargar paquete ZIP con todos los reportes" aria-label="Descargar ZIP completo">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    Descargar ZIP completo
                </a>
                <a href="{{ route('admin.reportes.respaldo-academico.sql') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                   style="background:#4338ca;"
                   title="Descargar respaldo SQL académico" aria-label="Descargar respaldo SQL">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                    Respaldo SQL
                </a>
            </div>
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
