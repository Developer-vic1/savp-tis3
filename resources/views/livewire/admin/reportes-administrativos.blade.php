<div class="space-y-6">
    <section class="ui-card rounded-[2rem] p-6 sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="ui-kicker">Control institucional</p>
                <h1 class="ui-title mt-2 text-3xl font-black">Reportes administrativos</h1>
                <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">
                    Consolida cobertura de datos, estado operativo y actividad reciente del sistema SAVP-TIS3.
                </p>
            </div>
            {{-- Botones de descarga funcionales --}}
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.reportes.administrativo.pdf') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                   style="background:#0284c7;"
                   title="Descargar Reporte Administrativo en PDF"
                   aria-label="Descargar Reporte Administrativo en PDF">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
                    </svg>
                    PDF Administrativo
                </a>
                <a href="{{ route('admin.reportes.bitacora.pdf') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                   style="background:#0369a1;"
                   title="Descargar Reporte de Bitácora en PDF"
                   aria-label="Descargar Reporte de Bitácora en PDF">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0 0l-4-4m4 4l4-4M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/>
                    </svg>
                    PDF Bitácora
                </a>
                <a href="{{ route('admin.reportes.respaldo-academico.sql') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                   style="background:#4338ca;"
                   title="Descargar Respaldo SQL Académico"
                   aria-label="Descargar Respaldo SQL Académico">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                    SQL
                </a>
                <a href="{{ route('admin.reportes.paquete.zip') }}"
                   class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                   style="background:#0f172a;"
                   title="Descargar Paquete ZIP de Todos los Reportes"
                   aria-label="Descargar paquete ZIP completo">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    ZIP Completo
                </a>
            </div>
        </div>
        <div class="mt-5 flex flex-wrap items-center gap-3">
            <span class="{{ $diagnostico['estado'] === 'Operativo' ? 'ui-badge-success' : 'ui-badge-warning' }}">{{ $diagnostico['estado'] }}</span>
            <span class="ui-badge-info">{{ $diagnostico['completitud'] }}% de cobertura estructural</span>
            <span class="text-sm" style="color: var(--ui-muted)">Reportes institucionales generados con mPDF — disponibles para descarga inmediata.</span>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($metricas as $label => $value)
            <article class="ui-card rounded-[1.6rem] p-5">
                <p class="ui-kicker">{{ $label }}</p>
                <p class="mt-3 text-3xl font-black" style="color: {{ $value === 0 ? 'var(--ui-warning)' : 'var(--ui-primary)' }}">{{ $value }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-5 xl:grid-cols-[1.25fr_.75fr]">
        <article class="ui-card rounded-[2rem] p-6">
            <p class="ui-kicker">Diagnóstico preventivo</p>
            <h2 class="ui-title mt-2 text-xl font-black">Estado general del sistema</h2>
            @if(empty($diagnostico['advertencias']))
                <div class="ui-alert-success mt-5">Todos los catálogos administrativos evaluados contienen registros.</div>
            @else
                <div class="ui-alert-warning mt-5">
                    <p class="font-black">Módulos sin registros</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($diagnostico['advertencias'] as $item)<span class="ui-badge-warning">{{ $item }}</span>@endforeach
                    </div>
                </div>
            @endif
        </article>

        <article class="ui-card rounded-[2rem] p-6">
            <p class="ui-kicker">Inscripciones</p>
            <h2 class="ui-title mt-2 text-xl font-black">Distribución por estado</h2>
            <div class="mt-5 space-y-3">
                @forelse($estadosInscripcion as $estado => $cantidad)
                    <div class="ui-card-soft flex items-center justify-between p-4">
                        <span class="text-sm font-bold" style="color: var(--ui-text)">{{ $estado ?: 'Sin estado' }}</span>
                        <span class="ui-badge-info">{{ $cantidad }}</span>
                    </div>
                @empty
                    <div class="py-10 text-center text-sm" style="color: var(--ui-muted)">No existen inscripciones registradas.</div>
                @endforelse
            </div>
        </article>
    </section>

    <section class="ui-card rounded-[2rem] p-5">
        <div class="grid gap-3 md:grid-cols-[1fr_280px_auto]">
            <input wire:model.live.debounce.350ms="search" class="ui-input" placeholder="Buscar acción, descripción o registro...">
            <select wire:model.live="moduloFiltro" class="ui-input">
                <option value="">Todos los módulos</option>
                @foreach($modulos as $modulo)<option>{{ $modulo }}</option>@endforeach
            </select>
            <button wire:click="limpiarFiltros" class="ui-btn-secondary">Limpiar</button>
        </div>
    </section>

    <section class="ui-card overflow-hidden rounded-[2rem]">
        <div class="border-b p-6" style="border-color: var(--ui-border)">
            <p class="ui-kicker">Auditoría</p>
            <h2 class="ui-title mt-2 text-xl font-black">Actividad reciente</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[var(--ui-border)]">
                <thead style="background: var(--ui-surface-muted)">
                    <tr>@foreach(['Fecha', 'Módulo', 'Acción', 'Registro', 'Resultado'] as $label)<th class="px-5 py-4 text-left text-xs font-black uppercase tracking-[0.12em]" style="color: var(--ui-muted)">{{ $label }}</th>@endforeach</tr>
                </thead>
                <tbody class="divide-y divide-[var(--ui-border)]">
                    @forelse($actividad as $item)
                        <tr class="hover:bg-[var(--ui-surface-muted)]">
                            <td class="px-5 py-4 text-sm" style="color: var(--ui-muted)">{{ $item->fec_bit?->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-4 text-sm font-bold" style="color: var(--ui-text)">{{ $item->mod_bit ?? 'General' }}</td>
                            <td class="px-5 py-4 text-sm" style="color: var(--ui-text)">{{ $item->acc_bit }}</td>
                            <td class="px-5 py-4 text-sm" style="color: var(--ui-muted)">{{ $item->nom_reg_bit ?? $item->reg_bit }}</td>
                            <td class="px-5 py-4"><span class="{{ in_array($item->res_bit, ['FALLIDO', 'BLOQUEADO'], true) ? 'ui-badge-danger' : 'ui-badge-success' }}">{{ $item->res_bit ?? 'REGISTRADO' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-16 text-center"><p class="text-lg font-black" style="color: var(--ui-text)">No existe actividad para los filtros aplicados</p><p class="mt-2 text-sm" style="color: var(--ui-muted)">La bitácora aparecerá aquí cuando registre acciones institucionales.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
