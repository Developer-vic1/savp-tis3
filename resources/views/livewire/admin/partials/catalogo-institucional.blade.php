<div class="space-y-6">
    <section class="ui-card rounded-[2rem] p-6 sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="ui-kicker">Gestión institucional</p>
                <h1 class="ui-title mt-2 text-3xl font-black">{{ $configuracion['titulo'] }}</h1>
                <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">{{ $configuracion['descripcion'] }}</p>
            </div>
            <button wire:click="abrirCrear" class="ui-btn-primary">Nuevo registro</button>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Total', $metricas['total'], 'var(--ui-primary)'],
            ['Activos', $metricas['activos'], 'var(--ui-success)'],
            ['Inactivos', $metricas['inactivos'], 'var(--ui-warning)'],
            [$configuracion['relacion_etiqueta'], $metricas['relacionados'], 'var(--ui-info)'],
        ] as [$etiqueta, $valor, $color])
            <article class="ui-card rounded-[1.6rem] p-5">
                <p class="text-xs font-bold uppercase tracking-[0.14em]" style="color: var(--ui-muted)">{{ $etiqueta }}</p>
                <p class="mt-3 text-3xl font-black" style="color: {{ $color }}">{{ $valor }}</p>
            </article>
        @endforeach
    </section>

    <section class="ui-card rounded-[2rem] p-5">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[repeat(4,minmax(0,1fr))_auto]">
            <input wire:model.live.debounce.350ms="search" class="ui-input" placeholder="Buscar por código, nombre o descripción...">
            <select wire:model.live="estado" class="ui-input">
                <option value="">Todos los estados</option>
                <option value="ACTIVO">Activos</option>
                <option value="INACTIVO">Inactivos</option>
            </select>
            @foreach($filtrosAdicionales as $indice => $filtro)
                <select wire:model.live="extraFiltro{{ $indice + 1 }}" class="ui-input">
                    <option value="">{{ $filtro['etiqueta'] }}</option>
                    @foreach($filtro['opciones'] as $opcion)<option value="{{ $opcion }}">{{ $opcion }}</option>@endforeach
                </select>
            @endforeach
            <button wire:click="limpiarFiltros" class="ui-btn-secondary">Limpiar filtros</button>
        </div>
    </section>

    <section class="ui-card overflow-hidden rounded-[2rem]">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-[var(--ui-border)]">
                <thead style="background: var(--ui-surface-muted)">
                    <tr>
                        @foreach ($configuracion['columnas'] as $etiqueta)
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-[0.12em]" style="color: var(--ui-muted)">{{ $etiqueta }}</th>
                        @endforeach
                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-[0.12em]" style="color: var(--ui-muted)">Relación</th>
                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-[0.12em]" style="color: var(--ui-muted)">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[var(--ui-border)]">
                    @forelse ($registros as $registro)
                        <tr class="transition hover:bg-[var(--ui-surface-muted)]">
                            @foreach ($configuracion['columnas'] as $campo => $etiqueta)
                                <td class="max-w-sm px-5 py-4 text-sm" style="color: var(--ui-text)">
                                    @if ($campo === $configuracion['estado'])
                                        <span class="{{ $registro->{$campo} === 'ACTIVO' ? 'ui-badge-success' : 'ui-badge-warning' }}">{{ $registro->{$campo} }}</span>
                                    @else
                                        {{ $registro->{$campo} ?: 'Sin registro' }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-5 py-4 text-sm font-bold" style="color: var(--ui-info)">
                                {{ $registro->{$configuracion['relacion']} ?? 0 }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-2">
                                    <button wire:click="abrirDetalle('{{ $registro->getKey() }}')" class="ui-btn-secondary">Ver</button>
                                    <button wire:click="abrirEditar('{{ $registro->getKey() }}')" class="ui-btn-secondary">Editar</button>
                                    <button wire:click="cambiarEstado('{{ $registro->getKey() }}')" class="ui-btn-secondary">
                                        {{ $registro->{$configuracion['estado']} === 'ACTIVO' ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($configuracion['columnas']) + 2 }}" class="px-6 py-16 text-center">
                                <p class="text-lg font-black" style="color: var(--ui-text)">No existen registros para los filtros aplicados</p>
                                <p class="mt-2 text-sm" style="color: var(--ui-muted)">Ajusta los filtros o registra nueva información institucional.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t p-4" style="border-color: var(--ui-border)">{{ $registros->links() }}</div>
    </section>

    @if ($modalFormulario)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" wire:click.self="cerrarFormulario">
            <section class="ui-card max-h-[92vh] w-full max-w-4xl overflow-y-auto rounded-[2rem] p-6 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="ui-kicker">{{ $editando ? 'Edición preventiva' : 'Nuevo registro' }}</p>
                        <h2 class="ui-title mt-2 text-2xl font-black">{{ $configuracion['titulo'] }}</h2>
                    </div>
                    <button wire:click="cerrarFormulario" class="ui-btn-secondary">Cerrar</button>
                </div>

                <div class="mt-6 grid gap-5 lg:grid-cols-[1.15fr_.85fr]">
                    <div class="space-y-4">
                        <label class="block">
                            <span class="ui-label">Nombre</span>
                            <input wire:model.live.debounce.350ms="form.{{ $configuracion['nombre'] }}" class="ui-input mt-2">
                            @error('form.' . $configuracion['nombre']) <span class="ui-error">{{ $message }}</span> @enderror
                        </label>

                        @if (isset($configuracion['descripcion_campo']))
                            <label class="block">
                                <span class="ui-label">Descripción</span>
                                <textarea wire:model.live.debounce.350ms="form.{{ $configuracion['descripcion_campo'] }}" rows="4" class="ui-input mt-2"></textarea>
                            </label>
                        @endif

                        @if (isset($configuracion['orden']))
                            <label class="block">
                                <span class="ui-label">Orden evaluativo</span>
                                <input type="number" min="1" max="20" wire:model.live="form.{{ $configuracion['orden'] }}" class="ui-input mt-2">
                            </label>
                        @endif

                        @if (isset($configuracion['tipo']))
                            <label class="block">
                                <span class="ui-label">Tipo de institución</span>
                                <input wire:model.live.debounce.350ms="form.{{ $configuracion['tipo'] }}" class="ui-input mt-2">
                            </label>
                        @endif

                        @if (isset($configuracion['ciudad']))
                            <label class="block">
                                <span class="ui-label">Ciudad</span>
                                <input wire:model.live.debounce.350ms="form.{{ $configuracion['ciudad'] }}" class="ui-input mt-2">
                            </label>
                        @endif

                        <label class="block">
                            <span class="ui-label">Estado</span>
                            <select wire:model.live="form.{{ $configuracion['estado'] }}" class="ui-input mt-2">
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                        </label>
                    </div>

                    <aside class="space-y-4">
                        <div class="ui-card-soft p-5">
                            <p class="ui-kicker">Estado Inteligente</p>
                            @if (!empty($analisis['estado_inteligente']))
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="px-3 py-1 text-xs font-black uppercase tracking-wider rounded-full
                                        {{ in_array($analisis['estado_inteligente'], ['RECONOCIDA', 'COHERENTE', 'FORTALEZA_ACADEMICA'], true) ? 'bg-emerald-500/10 text-emerald-500' : '' }}
                                        {{ in_array($analisis['estado_inteligente'], ['REDACTABLE'], true) ? 'bg-amber-500/10 text-amber-500' : '' }}
                                        {{ in_array($analisis['estado_inteligente'], ['REQUIERE_REVISION'], true) ? 'bg-blue-500/10 text-blue-500' : '' }}
                                        {{ in_array($analisis['estado_inteligente'], ['BLOQUEADA', 'DUPLICADA', 'INCOMPLETA', 'RIESGO_ACADEMICO'], true) ? 'bg-rose-500/10 text-rose-500' : '' }}
                                    ">
                                        {{ str_replace('_', ' ', $analisis['estado_inteligente']) }}
                                    </span>
                                    <span class="text-xs font-bold" style="color: var(--ui-muted)">Confianza: {{ $analisis['confianza'] ?? 0 }}%</span>
                                </div>
                            @else
                                <p class="mt-2 text-sm" style="color: var(--ui-muted)">Esperando entrada...</p>
                            @endif
                        </div>

                        <div class="ui-card-soft p-5">
                            <p class="ui-kicker">Completitud del Registro</p>
                            <p class="mt-2 text-3xl font-black" style="color: var(--ui-primary)">{{ $analisis['completitud'] ?? 0 }}%</p>
                            <div class="mt-3 h-2 overflow-hidden rounded-full" style="background: var(--ui-border)">
                                <div class="h-full rounded-full transition-all" style="width: {{ $analisis['completitud'] ?? 0 }}%; background: var(--ui-primary)"></div>
                            </div>
                        </div>

                        @if (!empty($analisis['bloqueos']))
                            <div class="ui-alert-danger p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-500 text-sm space-y-1">
                                <p class="font-black flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                    Bloqueo preventivo:
                                </p>
                                @foreach ($analisis['bloqueos'] as $bloqueo)
                                    <p class="text-xs">• {{ $bloqueo }}</p>
                                @endforeach
                            </div>
                        @endif

                        @if (!empty($analisis['advertencias']))
                            <div class="ui-alert-warning p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-500 text-sm space-y-1">
                                <p class="font-black flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Sugerencia / Advertencia:
                                </p>
                                @foreach ($analisis['advertencias'] as $advertencia)
                                    <p class="text-xs">• {{ $advertencia }}</p>
                                @endforeach
                            </div>
                        @endif

                        @if (!empty($analisis['visualizacion']))
                            <div class="ui-card-soft p-5 space-y-4">
                                <div>
                                    <p class="ui-kicker">Mapa Académico-Vocacional</p>
                                    <h3 class="mt-2 text-md font-black" style="color: {{ $analisis['visualizacion']['color_hex'] ?? 'var(--ui-primary)' }}">
                                        {{ $analisis['visualizacion']['area_bth'] ?? 'BTH' }}
                                    </h3>
                                    <p class="text-xs" style="color: var(--ui-muted)">{{ $analisis['visualizacion']['familia_profesional'] ?? '' }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="p-2 rounded-xl bg-slate-900/40">
                                        <span class="block text-[10px] uppercase font-bold" style="color: var(--ui-muted)">Perfil RIASEC</span>
                                        <span class="font-bold" style="color: var(--ui-text)">{{ $analisis['visualizacion']['perfil_riasec'] ?? '' }}</span>
                                    </div>
                                    <div class="p-2 rounded-xl bg-slate-900/40">
                                        <span class="block text-[10px] uppercase font-bold" style="color: var(--ui-muted)">Zona Inferida</span>
                                        <span class="font-bold" style="color: var(--ui-text)">{{ $analisis['visualizacion']['zona_inferida'] ?? ($analisis['visualizacion']['tipo_relacion'] ?? 'Institucional') }}</span>
                                    </div>
                                </div>

                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider block mb-2" style="color: var(--ui-muted)">Nivel de Competencias</span>
                                    <div class="space-y-2">
                                        @foreach($analisis['visualizacion']['niveles'] ?? [] as $label => $lvl)
                                            <div class="text-xs">
                                                <div class="flex justify-between text-[11px] mb-1">
                                                    <span style="color: var(--ui-text)">{{ $label }}</span>
                                                    <span class="font-bold">{{ $lvl }}%</span>
                                                </div>
                                                <div class="h-1.5 rounded-full w-full bg-slate-900/40 overflow-hidden">
                                                    <div class="h-full rounded-full transition-all" style="width: {{ $lvl }}%; background-color: {{ $analisis['visualizacion']['color_hex'] ?? 'var(--ui-primary)' }}"></div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                @if(!empty($analisis['visualizacion']['carreras_relacionadas']))
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider block mb-2" style="color: var(--ui-muted)">Carreras Recomendadas</span>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach($analisis['visualizacion']['carreras_relacionadas'] as $carrera)
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/10">{{ $carrera }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($analisis['acciones_recomendadas']))
                                    <div>
                                        <span class="text-xs font-bold uppercase tracking-wider block mb-2" style="color: var(--ui-muted)">Acciones Recomendadas</span>
                                        <div class="text-xs space-y-1.5" style="color: var(--ui-muted)">
                                            @foreach($analisis['acciones_recomendadas'] as $accion)
                                                <p class="leading-relaxed">• {{ $accion }}</p>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if (!empty($analisis['duplicidad']['registro']))
                            <div class="ui-alert-warning p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-500 text-sm">
                                Registro relacionado: <span class="font-bold">{{ $analisis['duplicidad']['registro']['nombre'] }}</span>
                                ({{ $analisis['duplicidad']['similitud'] }}% de similitud).
                            </div>
                        @endif

                        <button wire:click="aplicarSugerencias" class="ui-btn-secondary w-full">Aplicar correcciones</button>
                    </aside>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="cerrarFormulario" class="ui-btn-secondary">Cancelar</button>
                    <button wire:click="guardar" class="ui-btn-primary" @disabled(!($analisis['puede_guardar'] ?? false))>Guardar registro</button>
                </div>
            </section>
        </div>
    @endif

    @if ($modalDetalle)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/60 p-4" wire:click.self="cerrarDetalle">
            <section class="ui-card w-full max-w-2xl rounded-[2rem] p-6 sm:p-8">
                <div class="flex justify-between gap-4">
                    <div><p class="ui-kicker">Vista previa</p><h2 class="ui-title mt-2 text-2xl font-black">{{ $configuracion['titulo'] }}</h2></div>
                    <button wire:click="cerrarDetalle" class="ui-btn-secondary">Cerrar</button>
                </div>
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    @foreach ($detalle as $campo => $valor)
                        @if (!in_array($campo, ['created_at', 'updated_at'], true))
                            <div class="ui-card-soft p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.12em]" style="color: var(--ui-muted)">{{ str_replace('_', ' ', $campo) }}</p>
                                <p class="mt-2 text-sm font-bold" style="color: var(--ui-text)">{{ is_scalar($valor) ? $valor : '' }}</p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        </div>
    @endif
</div>
