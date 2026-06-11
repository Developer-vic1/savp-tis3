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
            <button disabled class="ui-btn-secondary" title="Requiere autorización para instalar spatie/laravel-pdf">
                Exportar PDF
            </button>
        </div>
        <div class="mt-5 flex flex-wrap items-center gap-3">
            <span class="px-3 py-1 text-xs font-black uppercase tracking-wider rounded-full
                {{ $diagnostico['estado_general'] === 'OPERATIVO' ? 'bg-emerald-500/10 text-emerald-500' : '' }}
                {{ $diagnostico['estado_general'] === 'ADVERTENCIA' ? 'bg-amber-500/10 text-amber-500' : '' }}
                {{ $diagnostico['estado_general'] === 'CRITICO' ? 'bg-rose-500/10 text-rose-500' : '' }}
            ">
                {{ $diagnostico['estado'] }}
            </span>
            <span class="ui-badge-info">{{ $diagnostico['completitud'] }}% de cobertura estructural</span>
            <span class="text-sm" style="color: var(--ui-muted)">El PDF institucional queda preparado para integrarse cuando se autorice la librería.</span>
        </div>
    </section>

    <!-- 📊 TARJETAS DE MÉTRICAS -->
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($metricas as $label => $value)
            <article class="ui-card rounded-[1.6rem] p-5">
                <p class="ui-kicker">{{ $label }}</p>
                <p class="mt-3 text-3xl font-black" style="color: {{ $value === 0 ? 'var(--ui-warning)' : 'var(--ui-primary)' }}">{{ $value }}</p>
            </article>
        @endforeach
    </section>

    <!-- 🔍 DIAGNÓSTICO PREVENTIVO E HISTORIAL DE INSCRIPCIONES -->
    <section class="grid gap-5 xl:grid-cols-[1.25fr_.75fr]">
        <!-- 💡 Panel Inteligente de Alertas y Recomendaciones -->
        <article class="ui-card rounded-[2rem] p-6 space-y-5">
            <div>
                <p class="ui-kicker">Auditoría Operativa</p>
                <h2 class="ui-title mt-2 text-xl font-black">Diagnóstico de Cobertura de Datos</h2>
            </div>

            <div class="space-y-3">
                <span class="text-xs font-bold uppercase tracking-wider block" style="color: var(--ui-muted)">Alertas Administrativas</span>
                @foreach($diagnostico['alertas'] as $alerta)
                    <div class="flex items-center gap-3 p-4 text-xs rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-500 leading-relaxed">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>{{ $alerta }}</span>
                    </div>
                @endforeach
            </div>

            <div class="space-y-3 pt-2">
                <span class="text-xs font-bold uppercase tracking-wider block" style="color: var(--ui-muted)">Recomendaciones Operativas</span>
                @foreach($diagnostico['recomendaciones'] as $rec)
                    <div class="flex items-start gap-3 p-4 rounded-2xl bg-slate-900/40 border border-[var(--ui-border)] text-xs text-slate-300">
                        <span class="flex items-center justify-center w-5 h-5 rounded-lg bg-emerald-500/10 text-emerald-400 shrink-0 font-bold">✓</span>
                        <span>{{ $rec }}</span>
                    </div>
                @endforeach
            </div>
        </article>

        <!-- 📊 Módulos Cobertura & Inscripciones -->
        <article class="ui-card rounded-[2rem] p-6 space-y-5">
            <div>
                <p class="ui-kicker">Cobertura Funcional</p>
                <h2 class="ui-title mt-2 text-xl font-black">Estado de Módulos</h2>
            </div>

            <div class="space-y-3">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider block mb-2" style="color: var(--ui-muted)">Módulos con suficiencia de datos</span>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($diagnostico['modulos_fuertes'] as $mod)
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">{{ $mod }}</span>
                        @empty
                            <span class="text-xs" style="color: var(--ui-muted)">Ninguno supera el umbral básico.</span>
                        @endforelse
                    </div>
                </div>

                <div class="pt-2">
                    <span class="text-xs font-bold uppercase tracking-wider block mb-2" style="color: var(--ui-muted)">Módulos pendientes de carga</span>
                    <div class="flex flex-wrap gap-1.5">
                        @forelse($diagnostico['modulos_pendientes'] as $mod)
                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-rose-500/10 text-rose-400 border border-rose-500/20">{{ $mod }}</span>
                        @empty
                            <span class="text-xs" style="color: var(--ui-muted)">Todos los módulos poseen registros iniciales.</span>
                        @endforelse
                    </div>
                </div>

                <div class="pt-4 border-t" style="border-color: var(--ui-border)">
                    <span class="text-xs font-bold uppercase tracking-wider block mb-2" style="color: var(--ui-muted)">Distribución de Inscripciones</span>
                    @forelse($estadosInscripcion as $estado => $cantidad)
                        <div class="ui-card-soft flex items-center justify-between p-3 mt-1.5 text-xs">
                            <span class="font-bold" style="color: var(--ui-text)">{{ $estado ?: 'Sin estado' }}</span>
                            <span class="ui-badge-info">{{ $cantidad }} inscritos</span>
                        </div>
                    @empty
                        <div class="py-6 text-center text-xs" style="color: var(--ui-muted)">No existen inscripciones.</div>
                    @endforelse
                </div>
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
