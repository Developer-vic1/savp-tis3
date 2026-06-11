<div class="space-y-6" x-data="{
        color(name) {
            return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        },
        toast(icon, title, text) {
            Swal.fire({
                icon,
                title,
                text,
                confirmButtonText: 'Entendido',
                confirmButtonColor: this.color('--ui-primary') || '#059669',
                background: this.color('--ui-surface') || '#ffffff',
                color: this.color('--ui-text') || '#0f172a'
            });
        },
        confirmarEstado(codigo, nombre, activo) {
            const accion = activo ? 'desactivar' : 'reactivar';
            const titulo = activo ? '¿Desactivar especialidad?' : '¿Reactivar especialidad?';
            const texto = activo 
                ? `La especialidad técnica '${nombre}' será marcada como inactiva. No afectará a los estudiantes vinculados actualmente.`
                : `La especialidad técnica '${nombre}' volverá a estar activa y disponible para vinculación.`;
            
            Swal.fire({
                icon: activo ? 'warning' : 'question',
                title: titulo,
                text: texto,
                showCancelButton: true,
                confirmButtonText: activo ? 'Sí, desactivar' : 'Sí, reactivar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: activo ? '#d97706' : '#059669',
                cancelButtonColor: '#64748b',
                background: this.color('--ui-surface') || '#ffffff',
                color: this.color('--ui-text') || '#0f172a',
                reverseButtons: true
            }).then(result => {
                if (result.isConfirmed) {
                    $wire.cambiarEstado(codigo);
                }
            });
        }
    }">
    {{-- ENCABEZADO --}}
    <section class="ui-card overflow-hidden rounded-[2rem]">
        <div class="relative p-6 lg:p-8" style="background:
                radial-gradient(circle at top left, var(--ui-primary-soft), transparent 34%),
                radial-gradient(circle at top right, var(--ui-violet-soft), transparent 32%),
                linear-gradient(135deg, var(--ui-surface), var(--ui-surface-soft));">
            <div class="grid gap-6 lg:grid-cols-[1.35fr_.65fr] lg:items-center">
                <div class="space-y-5">
                    <div class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-black uppercase tracking-[0.18em]"
                        style="border-color: var(--ui-primary-border); background: var(--ui-primary-soft); color: var(--ui-primary);">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                        </svg>
                        Motor BTH activo
                    </div>

                    <div>
                        <h2 class="ui-title text-3xl font-black tracking-tight md:text-4xl">
                            Gestión de Especialidades Técnicas
                        </h2>

                        <p class="ui-muted mt-3 max-w-4xl text-sm leading-7 md:text-base">
                            Administra la oferta técnica BTH y su relación con competencias, asignaturas clave, áreas profesionales y orientación académico-vocacional. El motor valida y estructura perfiles vocacionales Holland (RIASEC) de forma coherente.
                        </p>
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="button" wire:click="abrirModalCrear" class="ui-btn-primary">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Registrar especialidad
                        </button>

                        <button type="button" x-on:click="document.getElementById('mapa-bth').scrollIntoView({behavior: 'smooth'})" class="ui-btn-secondary">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 6.143 12.015 3 15.03 6.143m-6.03 11.714L12.015 21l3.015-3.143m-9.045-3.428L3 11.286l3.015-3.143m12.03 6.857L21 11.286l-3.015-3.143m-3.015-3.428h.008v.008h-.008v-.008Z" />
                            </svg>
                            Mapa BTH
                        </button>
                    </div>
                </div>

                <div class="ui-card-soft p-5">
                    <div class="flex items-center gap-4">
                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl"
                            style="background: var(--ui-primary-soft); color: var(--ui-primary); border: 1px solid var(--ui-primary-border);">
                            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.6"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25A8.967 8.967 0 0 1 18 3.75c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                            </svg>
                        </div>

                        <div>
                            <p class="ui-title text-sm font-black">Alineación Vocacional BTH</p>
                            <p class="ui-muted mt-1 text-xs leading-5">
                                Persiste competencias, perfiles holísticos e inserción de estudiantes de secundaria.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl border p-3"
                            style="border-color: var(--ui-violet-border); background: var(--ui-violet-soft);">
                            <p class="text-xs font-bold" style="color: var(--ui-violet);">Clasificadas</p>
                            <p class="mt-1 text-xl font-black" style="color: var(--ui-violet);">
                                {{ $metricas['clasificados'] ?? 0 }}
                            </p>
                        </div>

                        <div class="rounded-2xl border p-3"
                            style="border-color: var(--ui-warning-border); background: var(--ui-warning-soft);">
                            <p class="text-xs font-bold" style="color: var(--ui-warning);">Pendientes</p>
                            <p class="mt-1 text-xl font-black" style="color: var(--ui-warning);">
                                {{ $metricas['pendientes'] ?? 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ALERTA --}}
    <section class="ui-alert-info">
        <div class="flex gap-3">
            <svg class="mt-0.5 h-5 w-5 flex-none" fill="none" stroke="currentColor" stroke-width="1.8"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>

            <p class="leading-6 text-sm">
                Las especialidades técnicas BTH clasificadas asocian automáticamente perfiles RIASEC, asignaturas de apoyo académico, familias profesionales e indicadores para el reporte vocacional integrado.
            </p>
        </div>
    </section>

    {{-- TARJETAS MÉTRICAS (6 TARJETAS) --}}
    <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
        <article class="ui-card ui-card-hover p-5 rounded-[1.6rem]">
            <div class="flex items-center justify-between">
                <p class="ui-muted text-xs font-black uppercase tracking-wider">Total</p>
                <span class="rounded-xl p-2" style="background: var(--ui-info-soft); color: var(--ui-info);">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.007 5.25H3.75v.008h.007V12Zm0 5.25H3.75v.008h.007v-.008Z" />
                    </svg>
                </span>
            </div>
            <p class="ui-title mt-4 text-3xl font-black">{{ $metricas['total'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Especialidades totales</p>
        </article>

        <article class="ui-card ui-card-hover p-5 rounded-[1.6rem]">
            <div class="flex items-center justify-between">
                <p class="ui-muted text-xs font-black uppercase tracking-wider">Activas</p>
                <span class="rounded-xl p-2" style="background: var(--ui-primary-soft); color: var(--ui-primary);">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
            </div>
            <p class="ui-title mt-4 text-3xl font-black">{{ $metricas['activos'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Activas en el sistema</p>
        </article>

        <article class="ui-card ui-card-hover p-5 rounded-[1.6rem]">
            <div class="flex items-center justify-between">
                <p class="ui-muted text-xs font-black uppercase tracking-wider">Inactivas</p>
                <span class="rounded-xl p-2" style="background: var(--ui-warning-soft); color: var(--ui-warning);">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </span>
            </div>
            <p class="ui-title mt-4 text-3xl font-black">{{ $metricas['inactivos'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Especialidades inactivas</p>
        </article>

        <article class="ui-card ui-card-hover p-5 rounded-[1.6rem]">
            <div class="flex items-center justify-between">
                <p class="ui-muted text-xs font-black uppercase tracking-wider">Clasificadas BTH</p>
                <span class="rounded-xl p-2" style="background: var(--ui-violet-soft); color: var(--ui-violet);">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
            </div>
            <p class="ui-title mt-4 text-3xl font-black" style="color: var(--ui-violet)">{{ $metricas['clasificados'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Con base curricular</p>
        </article>

        <article class="ui-card ui-card-hover p-5 rounded-[1.6rem]">
            <div class="flex items-center justify-between">
                <p class="ui-muted text-xs font-black uppercase tracking-wider">Pendientes BTH</p>
                <span class="rounded-xl p-2" style="background: var(--ui-warning-soft); color: var(--ui-warning);">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v6l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </span>
            </div>
            <p class="ui-title mt-4 text-3xl font-black" style="color: var(--ui-warning)">{{ $metricas['pendientes'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Sin clasificar BTH</p>
        </article>

        <article class="ui-card ui-card-hover p-5 rounded-[1.6rem]">
            <div class="flex items-center justify-between">
                <p class="ui-muted text-xs font-black uppercase tracking-wider">Familias BTH</p>
                <span class="rounded-xl p-2" style="background: var(--ui-success-soft); color: var(--ui-success);">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </span>
            </div>
            <p class="ui-title mt-4 text-3xl font-black" style="color: var(--ui-primary)">{{ $metricas['familias'] ?? 0 }}</p>
            <p class="ui-muted mt-1 text-xs">Familias detectadas</p>
        </article>
    </section>

    {{-- FILTROS ROW --}}
    <section class="ui-card p-4 rounded-[1.6rem]">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[2fr_1.2fr_1.5fr_1.5fr_1.5fr_auto]">
            <div>
                <label class="ui-label">Buscar especialidad</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 ui-muted"
                        fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input type="search" wire:model.live.debounce.400ms="search" class="ui-input pl-11"
                        placeholder="Buscar por código, nombre o descripción...">
                </div>
            </div>

            <div>
                <label class="ui-label">Estado</label>
                <select wire:model.live="estado" class="ui-select">
                    <option value="">Todos los estados</option>
                    <option value="ACTIVO">Activos</option>
                    <option value="INACTIVO">Inactivos</option>
                </select>
            </div>

            <div>
                <label class="ui-label">Familia Profesional</label>
                <select wire:model.live="familiaFiltro" class="ui-select" @disabled(!$hasExtended)>
                    <option value="">Todas las familias</option>
                    @foreach ($familias as $f)
                        <option value="{{ $f }}">{{ $f }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="ui-label">Campo Formativo</label>
                <select wire:model.live="campoFiltro" class="ui-select" @disabled(!$hasExtended)>
                    <option value="">Todos los campos</option>
                    @foreach ($camposFormativos as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="ui-label">Estado Inteligente</label>
                <select wire:model.live="estadoInteligenteFiltro" class="ui-select" @disabled(!$hasExtended)>
                    <option value="">Todos los análisis</option>
                    @foreach ($estadosInteligentes as $e)
                        <option value="{{ $e }}">{{ str_replace('_', ' ', $e) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end">
                <button type="button" wire:click="limpiarFiltros" class="ui-btn-secondary w-full">
                    Limpiar
                </button>
            </div>
        </div>
    </section>

    {{-- TABLA --}}
    <section class="ui-table-wrap rounded-[2rem]">
        <div class="flex flex-col gap-3 border-b p-5 md:flex-row md:items-center md:justify-between"
            style="border-color: var(--ui-border);">
            <div>
                <h3 class="ui-title text-lg font-black">Catálogo de Especialidades Técnicas</h3>
                <p class="ui-muted mt-1 text-sm">
                    Gestión curricular, vocacional e investigativa BTH homologada por el sistema.
                </p>
            </div>

            <div wire:loading class="text-sm font-bold animate-pulse" style="color: var(--ui-primary);">
                Actualizando información...
            </div>
        </div>

        <div class="overflow-x-auto ui-scrollbar">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th class="px-5 py-4 text-xs font-black uppercase tracking-[0.12em]">Código</th>
                        <th class="px-5 py-4 text-xs font-black uppercase tracking-[0.12em]">Especialidad</th>
                        <th class="px-5 py-4 text-xs font-black uppercase tracking-[0.12em]">Familia Profesional</th>
                        <th class="px-5 py-4 text-xs font-black uppercase tracking-[0.12em]">Campo Formativo</th>
                        <th class="px-5 py-4 text-xs font-black uppercase tracking-[0.12em]">RIASEC</th>
                        <th class="px-5 py-4 text-xs font-black uppercase tracking-[0.12em]">Confianza</th>
                        <th class="px-5 py-4 text-xs font-black uppercase tracking-[0.12em]">Inteligente</th>
                        <th class="px-5 py-4 text-xs font-black uppercase tracking-[0.12em]">Estado</th>
                        <th class="px-5 py-4 text-xs font-black uppercase tracking-[0.12em]">Estudiantes</th>
                        <th class="px-5 py-4 text-xs font-black uppercase tracking-[0.12em] text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($registros as $registro)
                        @php
                            $colorHex = '#6366f1';
                            $estadoInt = $registro->est_int_esp ?? 'PENDIENTE_CLASIFICACION';
                            $confVal = $registro->conf_esp ?? 0;
                            $clasificado = $registro->clas_bth_esp ?? false;
                            
                            if ($clasificado && !empty($registro->sig_esp)) {
                                $colorHex = app(App\Support\Academico\EspecialidadTecnicaInteligente::class)->colorEspecialidad($registro->sig_esp);
                            }
                            
                            $visualBadges = App\Support\Academico\EspecialidadTecnicaInteligente::estadoVisual($estadoInt);
                        @endphp
                        <tr class="transition hover:bg-[var(--ui-surface-muted)]" wire:key="especialidad-row-{{ $registro->cod_esp }}">
                            <td class="whitespace-nowrap">
                                <span class="ui-badge-muted font-black">{{ $registro->cod_esp }}</span>
                            </td>

                            <td class="min-w-[220px]">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 flex-none items-center justify-center rounded-xl text-xs font-black text-white"
                                        style="background: {{ $colorHex }};">
                                        {{ $registro->sig_esp ?: mb_substr($registro->nom_esp, 0, 2) }}
                                    </div>
                                    <div>
                                        <p class="ui-title font-black text-sm">{{ $registro->nom_esp }}</p>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="text-xs font-semibold ui-title">{{ $registro->fam_pro_esp ?: 'General' }}</span>
                            </td>

                            <td>
                                <span class="text-xs ui-muted leading-relaxed">{{ $registro->cam_for_esp ?: 'Cosmos y Sociedad' }}</span>
                            </td>

                            <td class="whitespace-nowrap">
                                @if(!empty($registro->perfil_riasec_esp))
                                    <span class="text-xs font-mono font-bold text-violet-500">{{ implode(' / ', $registro->perfil_riasec_esp) }}</span>
                                @else
                                    <span class="text-xs ui-muted">-</span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap">
                                <span class="text-xs font-black">{{ $confVal }}%</span>
                            </td>

                            <td class="whitespace-nowrap">
                                <span class="{{ $visualBadges['clase'] }} text-[10px]">
                                    {{ $visualBadges['texto'] }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap">
                                @if ($registro->est_esp === 'ACTIVO')
                                    <span class="ui-badge-success">
                                        <span class="h-2 w-2 rounded-full" style="background: var(--ui-primary);"></span>
                                        Activo
                                    </span>
                                @else
                                    <span class="ui-badge-warning">
                                        <span class="h-2 w-2 rounded-full" style="background: var(--ui-warning);"></span>
                                        Inactivo
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap font-bold text-indigo-500">
                                {{ $registro->estudiantes_count ?? 0 }} vinculados
                            </td>

                            <td class="whitespace-nowrap text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" wire:click="abrirModalDetalle('{{ $registro->getKey() }}')"
                                        class="ui-icon-btn" title="Ver detalle">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                        </svg>
                                    </button>

                                    <button type="button" wire:click="abrirModalEditar('{{ $registro->getKey() }}')"
                                        class="ui-icon-btn" title="Editar">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125"/>
                                        </svg>
                                    </button>

                                    <button type="button" x-on:click="confirmarEstado('{{ $registro->getKey() }}', '{{ addslashes($registro->nom_esp) }}', {{ $registro->est_esp === 'ACTIVO' ? 'true' : 'false' }})"
                                        class="ui-icon-btn" title="{{ $registro->est_esp === 'ACTIVO' ? 'Desactivar' : 'Activar' }}">
                                        @if ($registro->est_esp === 'ACTIVO')
                                            <svg class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                        @else
                                            <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                            </svg>
                                        @endif
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-16 text-center">
                                <div class="mx-auto flex max-w-md flex-col items-center px-5">
                                    <div class="flex h-16 w-16 items-center justify-center rounded-3xl"
                                        style="background: var(--ui-primary-soft); color: var(--ui-primary); border: 1px solid var(--ui-primary-border);">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.7"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>

                                    <h4 class="ui-title mt-4 text-lg font-black">No se encontraron especialidades</h4>
                                    <p class="ui-muted mt-2 text-sm leading-6">
                                        Ajuste los filtros o registre una nueva especialidad para iniciar la clasificación.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($registros->hasPages())
            <div class="border-t px-5 py-4" style="border-color: var(--ui-border);">
                {{ $registros->links() }}
            </div>
        @endif
    </section>

    {{-- SECCIÓN MAPA BTH --}}
    <section id="mapa-bth" class="ui-card p-6 rounded-[2rem] space-y-6">
        <div>
            <h3 class="ui-title text-xl font-black">Mapa académico-vocacional BTH</h3>
            <p class="ui-muted mt-1 text-sm">
                Alineación vocacional, áreas y carreras sugeridas para cada especialidad técnica activa.
            </p>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($especialidadesConAnalisis as $item)
                @php
                    $reg = $item['registro'];
                    $ana = $item['analisis'];
                    $vis = $ana['visualizacion'] ?? [];
                    $reconocida = ($ana['estado_inteligente'] ?? '') === 'RECONOCIDA' || ($ana['estado_inteligente'] ?? '') === 'REDACTABLE';
                    $color = $vis['color_hex'] ?? '#6366f1';
                @endphp
                <article class="ui-card-soft p-5 border rounded-2xl flex flex-col justify-between"
                    style="border-color: {{ $reconocida ? $color . '30' : 'var(--ui-border)' }};
                           background: linear-gradient(145deg, var(--ui-surface-soft), var(--ui-surface));">
                    
                    <div class="space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex h-10 w-10 flex-none items-center justify-center rounded-xl text-xs font-black text-white"
                                style="background: {{ $color }};">
                                {{ $reg->sig_esp ?: mb_substr($reg->nom_esp, 0, 2) }}
                            </div>

                            <div class="text-right">
                                <span class="px-2 py-0.5 text-[9px] font-black uppercase rounded-full"
                                      style="background: {{ $color }}15; color: {{ $color }};">
                                    {{ $reg->est_esp }}
                                </span>
                                <p class="text-[10px] ui-muted mt-1 font-mono">{{ $reg->cod_esp }}</p>
                            </div>
                        </div>

                        <div>
                            <h4 class="ui-title font-black text-base line-clamp-1" style="color: {{ $color }}">{{ $reg->nom_esp }}</h4>
                            <p class="ui-muted text-xs mt-1.5 line-clamp-2 leading-relaxed">
                                {{ $reg->des_esp ?: 'Sin descripción registrada.' }}
                            </p>
                        </div>

                        @if ($reconocida)
                            <div class="border-t pt-3 space-y-2 text-xs" style="border-color: var(--ui-border);">
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <span class="block ui-muted text-[9px] uppercase font-black">Familia Profesional</span>
                                        <span class="ui-title font-bold line-clamp-1">{{ $vis['familia_profesional'] ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="block ui-muted text-[9px] uppercase font-black">Campo Formativo</span>
                                        <span class="ui-title font-bold line-clamp-1">{{ $vis['campo_formativo'] ?? '-' }}</span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 mt-2">
                                    <div>
                                        <span class="block ui-muted text-[9px] uppercase font-black">Área BTH</span>
                                        <span class="ui-title font-bold">{{ $vis['area_bth'] ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="block ui-muted text-[9px] uppercase font-black">Perfil RIASEC</span>
                                        <span class="ui-title font-bold text-violet-500 font-mono">{{ $vis['perfil_riasec'] ?? '-' }}</span>
                                    </div>
                                </div>

                                @if (!empty($vis['carreras_relacionadas']))
                                    <div class="mt-3">
                                        <span class="block ui-muted text-[9px] uppercase font-black mb-1">Carreras Clave</span>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach (array_slice($vis['carreras_relacionadas'], 0, 3) as $carrera)
                                                <span class="px-2 py-0.5 text-[9px] font-bold rounded-lg border bg-[var(--ui-surface)]" style="border-color: var(--ui-border)">
                                                    {{ $carrera }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if (!empty($vis['asignaturas_relacionadas']))
                                    <div class="mt-2">
                                        <span class="block ui-muted text-[9px] uppercase font-black mb-1">Asignaturas Relacionadas</span>
                                        <div class="flex flex-wrap gap-1">
                                            @foreach (array_slice($vis['asignaturas_relacionadas'], 0, 3) as $asig)
                                                <span class="px-2 py-0.5 text-[9px] font-bold rounded-lg"
                                                      style="background: {{ $color }}10; color: {{ $color }};">
                                                    {{ $asig }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="border-t pt-4 text-center rounded-xl bg-red-500/5 border border-red-500/10 p-3"
                                 style="border-color: var(--ui-border);">
                                <span class="ui-badge-danger text-[10px] font-bold inline-block">Pendiente de clasificación BTH</span>
                                <p class="ui-muted text-[11px] mt-1.5 leading-relaxed">
                                    Esta especialidad requiere ser procesada por el motor inteligente para vincularse al mapa.
                                </p>
                                <button type="button" wire:click="clasificarConMotor('{{ $reg->cod_esp }}')" class="ui-btn-secondary py-1 px-3 text-[10px] rounded-lg mt-3 w-full font-bold">
                                    Clasificar con motor
                                </button>
                            </div>
                        @endif
                    </div>

                    <div class="mt-4 pt-3 border-t flex items-center justify-between text-xs" style="border-color: var(--ui-border);">
                        <span class="ui-muted font-bold">Estudiantes:</span>
                        <span class="font-black" style="color: {{ $color }}">{{ $reg->estudiantes_count ?? 0 }} registrados</span>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    {{-- MODAL CREAR --}}
    @if ($modalCrear)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="ui-modal-backdrop" wire:click="cerrarModales"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-6xl overflow-y-auto ui-scrollbar rounded-[2rem]">
                <div class="ui-modal-header sticky top-0 z-10 flex items-start justify-between">
                    <div>
                        <h3 class="ui-title text-2xl font-black">Registrar nueva especialidad BTH</h3>
                        <p class="ui-muted mt-1 text-sm">
                            Complete los datos básicos. El motor de análisis validará si corresponde a la oferta nacional.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarModales" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid gap-6 p-6 lg:grid-cols-[.95fr_1.05fr]">
                    {{-- Formulario izquierda --}}
                    <div class="space-y-5">
                        <div>
                            <label class="ui-label">Nombre de la especialidad</label>
                            <input type="text" wire:model.live.debounce.500ms="form.nom_esp" class="ui-input"
                                placeholder="Ej. Sistemas Informáticos, Gastronomía, Contabilidad...">
                            @error('form.nom_esp')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Descripción o visión formativa</label>
                            <textarea wire:model.live.debounce.500ms="form.des_esp" rows="4" class="ui-input"
                                      placeholder="Defina la descripción, objetivos o misión de la especialidad..."></textarea>
                            @error('form.des_esp')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Estado</label>
                            <select wire:model.live="form.est_esp" class="ui-select">
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                            @error('form.est_esp')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Ayuda contextual --}}
                        <div class="ui-card-soft p-4 rounded-xl space-y-2">
                            <h5 class="ui-title text-xs font-black uppercase tracking-wider">Ayuda Curricular BTH</h5>
                            <p class="ui-muted text-xs leading-relaxed">
                                Escriba un nombre formal de la oferta BTH para clasificar el registro automáticamente.
                            </p>
                            <div class="mt-2 text-xs font-semibold space-y-1">
                                <p class="ui-title text-xs font-bold">Catálogo de referencia:</p>
                                <div class="flex flex-wrap gap-1.5 mt-1">
                                    @foreach (['Sistemas Informáticos', 'Gastronomía', 'Contabilidad', 'Construcción Civil', 'Electrónica', 'Textiles y Confecciones', 'Mecánica Industrial', 'Mecánica Automotriz', 'Carpintería', 'Belleza Integral'] as $ex)
                                        <span class="px-2 py-0.5 text-[10px] rounded bg-slate-900/40 text-[var(--ui-muted)] border border-slate-900/20">{{ $ex }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Panel inteligente derecha --}}
                    <div class="space-y-5">
                        @php
                            $estadoPanel = $analisis['estado_inteligente'] ?? '';
                            $confianza = $analisis['confianza'] ?? 0;
                            $validez = $analisis['validez_academica'] ?? 0;
                            $completitud = $analisis['completitud_formulario'] ?? 0;
                            $advertencias = $analisis['advertencias'] ?? [];
                            $bloqueos = $analisis['bloqueos'] ?? [];
                            $coincidencias = $analisis['coincidencias'] ?? [];
                            $vis = $analisis['visualizacion'] ?? [];
                            $puedeGuardar = $analisis['puede_guardar'] ?? false;
                            
                            $panelClass = match ($estadoPanel) {
                                'RECONOCIDA' => 'ui-alert-success',
                                'REDACTABLE' => 'ui-alert-info',
                                'REQUIERE_REVISION' => 'ui-alert-warning',
                                'BLOQUEADA', 'DUPLICADA' => 'ui-alert-danger',
                                default => 'ui-card-soft',
                            };
                        @endphp

                        @if (empty($estadoPanel))
                            <div class="ui-card-soft p-8 text-center rounded-[1.6rem] border border-dashed flex flex-col items-center justify-center min-h-[300px]">
                                <div class="h-12 w-12 rounded-full flex items-center justify-center bg-slate-900/40 text-slate-400">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <h4 class="ui-title mt-4 font-black">Esperando entrada de datos</h4>
                                <p class="ui-muted mt-2 text-xs max-w-xs leading-relaxed">
                                    Escriba la especialidad en la izquierda para activar el análisis institucional BTH.
                                </p>
                            </div>
                        @else
                            <div class="{{ $panelClass }} p-5 rounded-2xl relative overflow-hidden">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-[0.16em] opacity-80">Análisis curricular BTH</p>
                                        <h4 class="mt-1.5 text-xl font-black">
                                            @if ($estadoPanel === 'RECONOCIDA')
                                                Especialidad reconocida
                                            @elseif ($estadoPanel === 'REDACTABLE')
                                                Corregible formal
                                            @elseif ($estadoPanel === 'REQUIERE_REVISION')
                                                Requiere validación
                                            @elseif ($estadoPanel === 'BLOQUEADA')
                                                Entrada bloqueada
                                            @elseif ($estadoPanel === 'DUPLICADA')
                                                Registro duplicado
                                            @else
                                                Análisis de validez
                                            @endif
                                        </h4>
                                    </div>
                                    <span class="px-3 py-1 text-xs font-black uppercase rounded-full" style="background: rgba(0,0,0,0.08)">
                                        {{ str_replace('_', ' ', $estadoPanel) }}
                                    </span>
                                </div>

                                <p class="mt-3 text-xs leading-relaxed">
                                    {{ $analisis['mensaje'] ?? '' }}
                                </p>

                                {{-- Completitud, Validez y Confianza --}}
                                <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-black/5 text-center">
                                    <div>
                                        <span class="block text-[10px] uppercase font-bold opacity-75">Formulario</span>
                                        <span class="text-lg font-black">{{ $completitud }}%</span>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] uppercase font-bold opacity-75">Validez BTH</span>
                                        <span class="text-lg font-black text-violet-600">{{ $validez }}%</span>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] uppercase font-bold opacity-75">Confianza</span>
                                        <span class="text-lg font-black text-emerald-600">{{ $confianza }}%</span>
                                    </div>
                                </div>
                            </div>

                            @if ($estadoPanel === 'BLOQUEADA' || $estadoPanel === 'DUPLICADA')
                                @if (!empty($bloqueos))
                                    <div class="ui-alert-danger p-4 rounded-xl border border-rose-500/20 text-rose-500 text-sm space-y-1">
                                        <p class="font-black flex items-center gap-2 text-xs uppercase tracking-wider">
                                            Motivo del bloqueo preventivo:
                                        </p>
                                        @foreach ($bloqueos as $blq)
                                            <p class="text-xs">• {{ $blq }}</p>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="ui-card-soft p-5 rounded-xl space-y-3">
                                    <h5 class="ui-title text-xs font-black uppercase tracking-wider">Sugerencias de ingreso:</h5>
                                    <p class="ui-muted text-xs leading-relaxed">
                                        Por favor, reemplace el texto del formulario por una especialidad BTH de la Unidad Educativa:
                                    </p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach (self::ejemplosValidos() as $exValid)
                                            <span class="px-2 py-0.5 rounded bg-slate-900/40 text-xs border">{{ $exValid }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if ($estadoPanel === 'REQUIERE_REVISION' || $estadoPanel === 'REDACTABLE')
                                @if ($confianza >= 25 && !empty($analisis['sugerencias']))
                                    <div class="ui-card-soft p-4 rounded-xl border space-y-3">
                                        <div>
                                            <span class="block ui-muted text-[10px] uppercase font-black">Sugerencia recomendada</span>
                                            <p class="ui-title font-black text-sm mt-1">¿Quiso decir: <span class="text-indigo-500">"{{ $analisis['sugerencias'][0] ?? '' }}"</span>?</p>
                                        </div>

                                        <button type="button" wire:click="aplicarSugerencia" class="ui-btn-primary w-full py-2 text-xs rounded-xl">
                                            Aplicar sugerencia formal
                                        </button>
                                    </div>
                                @endif

                                @if (!empty($advertencias))
                                    <div class="ui-alert-warning p-4 rounded-xl border text-sm space-y-1">
                                        <p class="font-black text-xs uppercase tracking-wider">Advertencias:</p>
                                        @foreach ($advertencias as $adv)
                                            <p class="text-xs">• {{ $adv }}</p>
                                        @endforeach
                                    </div>
                                @endif
                            @endif

                            {{-- Vista previa limitada para REDACTABLE (60-79) --}}
                            @if ($estadoPanel === 'REDACTABLE' && ($vis['mostrar_vista_previa_limitada'] ?? false))
                                <div class="ui-card-soft p-5 rounded-2xl border space-y-4" style="border-color: var(--ui-warning-border);">
                                    <div>
                                        <span class="ui-badge-warning text-[9px] font-black uppercase tracking-wider">Vista previa limitada</span>
                                        <h4 class="ui-title text-sm font-black mt-1">Alineación estimada: {{ $analisis['datos']['nom_esp'] }}</h4>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 text-xs border-t pt-3" style="border-color: var(--ui-border);">
                                        <div>
                                            <span class="block ui-muted text-[9px] uppercase font-black">Familia Profesional</span>
                                            <span class="font-bold ui-title">{{ $vis['familia_profesional'] ?? '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="block ui-muted text-[9px] uppercase font-black">Campo Formativo</span>
                                            <span class="font-bold ui-title">{{ $vis['campo_formativo'] ?? '-' }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <span class="block ui-muted text-[9px] uppercase font-black">Área BTH</span>
                                            <span class="font-bold ui-title">{{ $vis['area_bth'] ?? '-' }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <span class="block ui-muted text-[9px] uppercase font-black">Perfil RIASEC</span>
                                            <span class="font-bold text-violet-500 font-mono">{{ $vis['perfil_riasec'] ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Vista del Mapa Completo (RECONOCIDA >= 80) --}}
                            @if ($estadoPanel === 'RECONOCIDA' && ($vis['mostrar_mapa_completo'] ?? false))
                                <div class="ui-card-soft p-5 rounded-2xl border space-y-5" style="border-color: {{ $vis['color_hex'] ?? 'var(--ui-border)' }}40;">
                                    <div>
                                        <p class="text-[10px] font-black uppercase tracking-wider" style="color: {{ $vis['color_hex'] }}">Mapa Académico-Vocacional BTH</p>
                                        <h4 class="ui-title text-base font-black mt-1">{{ $analisis['datos']['nom_esp'] }}</h4>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3 text-xs border-t pt-3" style="border-color: var(--ui-border);">
                                        <div>
                                            <span class="block ui-muted text-[9px] uppercase font-black">Familia Profesional</span>
                                            <span class="font-bold ui-title">{{ $vis['familia_profesional'] ?? '-' }}</span>
                                        </div>
                                        <div>
                                            <span class="block ui-muted text-[9px] uppercase font-black">Campo Formativo</span>
                                            <span class="font-bold ui-title">{{ $vis['campo_formativo'] ?? '-' }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <span class="block ui-muted text-[9px] uppercase font-black">Área BTH</span>
                                            <span class="font-bold ui-title">{{ $vis['area_bth'] ?? '-' }}</span>
                                        </div>
                                        <div class="mt-2">
                                            <span class="block ui-muted text-[9px] uppercase font-black">Perfil RIASEC</span>
                                            <span class="font-bold text-violet-500 font-mono">{{ $vis['perfil_riasec'] ?? '-' }}</span>
                                        </div>
                                    </div>

                                    @if (!empty($vis['niveles']))
                                        <div class="border-t pt-3" style="border-color: var(--ui-border);">
                                            <span class="text-[10px] font-black uppercase tracking-wider block mb-2" style="color: var(--ui-muted)">Nivel de Competencias</span>
                                            <div class="space-y-2">
                                                @foreach($vis['niveles'] as $label => $lvl)
                                                    <div class="text-xs">
                                                        <div class="flex justify-between text-[10px] mb-1">
                                                            <span style="color: var(--ui-text)">{{ $label }}</span>
                                                            <span class="font-black">{{ $lvl }}%</span>
                                                        </div>
                                                        <div class="h-1.5 rounded-full w-full bg-slate-900/40 overflow-hidden">
                                                            <div class="h-full rounded-full transition-all"
                                                                 style="width: {{ $lvl }}%; background-color: {{ $vis['color_hex'] ?? 'var(--ui-primary)' }}"></div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if(!empty($vis['carreras_relacionadas']))
                                        <div class="border-t pt-3" style="border-color: var(--ui-border);">
                                            <span class="text-[10px] font-black uppercase tracking-wider block mb-1.5" style="color: var(--ui-muted)">Carreras Relacionadas</span>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($vis['carreras_relacionadas'] as $carrera)
                                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-indigo-500/10 text-indigo-400 border border-indigo-500/10">{{ $carrera }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if(!empty($vis['asignaturas_relacionadas']))
                                        <div class="border-t pt-3" style="border-color: var(--ui-border);">
                                            <span class="text-[10px] font-black uppercase tracking-wider block mb-1.5" style="color: var(--ui-muted)">Asignaturas Relacionadas</span>
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($vis['asignaturas_relacionadas'] as $asig)
                                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg"
                                                          style="background: {{ $vis['color_hex'] ?? 'var(--ui-primary)' }}10; color: {{ $vis['color_hex'] ?? 'var(--ui-primary)' }};">
                                                        {{ $asig }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if(!empty($analisis['explicacion']))
                                        <div class="border-t pt-3" style="border-color: var(--ui-border);">
                                            <span class="text-[10px] font-black uppercase tracking-wider block" style="color: var(--ui-muted)">Explicación Vocacional</span>
                                            <p class="ui-muted text-xs leading-relaxed mt-1">{{ $analisis['explicacion'] }}</p>
                                        </div>
                                    @endif

                                    @if(!empty($analisis['acciones_recomendadas']))
                                        <div class="border-t pt-3" style="border-color: var(--ui-border);">
                                            <span class="text-[10px] font-black uppercase tracking-wider block mb-1.5" style="color: var(--ui-muted)">Acciones Recomendadas</span>
                                            <div class="text-xs space-y-1" style="color: var(--ui-muted)">
                                                @foreach($analisis['acciones_recomendadas'] as $acc)
                                                    <p class="leading-relaxed">• {{ $acc }}</p>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-col-reverse gap-3 sm:flex-row sm:justify-end border-t p-6"
                     style="border-color: var(--ui-border); background: var(--ui-surface-soft);">
                    <button type="button" wire:click="cerrarModales" class="ui-btn-secondary px-5 py-2.5 rounded-xl">
                        Cancelar
                    </button>

                    <button type="button" wire:click="guardar" class="ui-btn-primary px-5 py-2.5 rounded-xl"
                            @disabled(!$puedeGuardar)>
                        Registrar especialidad
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL EDITAR --}}
    @if ($modalEditar)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="ui-modal-backdrop" wire:click="cerrarModales"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-6xl overflow-y-auto ui-scrollbar rounded-[2rem]">
                <div class="ui-modal-header sticky top-0 z-10 flex items-start justify-between">
                    <div>
                        <h3 class="ui-title text-2xl font-black">Editar especialidad técnica</h3>
                        <p class="ui-muted mt-1 text-sm">
                            El sistema auditará la consistencia académica de los cambios introducidos.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarModales" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid gap-6 p-6 lg:grid-cols-[.95fr_1.05fr]">
                    {{-- Formulario --}}
                    <div class="space-y-5">
                        <div>
                            <label class="ui-label">Código Especialidad</label>
                            <input type="text" wire:model="seleccionado" disabled class="ui-field-readonly w-full font-mono">
                        </div>

                        <div>
                            <label class="ui-label">Nombre de la especialidad</label>
                            <input type="text" wire:model.live.debounce.500ms="form.nom_esp" class="ui-input">
                            @error('form.nom_esp')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Descripción o misión formativa</label>
                            <textarea wire:model.live.debounce.500ms="form.des_esp" rows="4" class="ui-input"></textarea>
                            @error('form.des_esp')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Estado</label>
                            <select wire:model.live="form.est_esp" class="ui-select">
                                <option value="ACTIVO">ACTIVO</option>
                                <option value="INACTIVO">INACTIVO</option>
                            </select>
                            @error('form.est_esp')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Panel inteligente --}}
                    <div class="space-y-5">
                        @php
                            $estadoPanel = $analisis['estado_inteligente'] ?? '';
                            $confianza = $analisis['confianza'] ?? 0;
                            $validez = $analisis['validez_academica'] ?? 0;
                            $completitud = $analisis['completitud_formulario'] ?? 0;
                            $advertencias = $analisis['advertencias'] ?? [];
                            $bloqueos = $analisis['bloqueos'] ?? [];
                            $coincidencias = $analisis['coincidencias'] ?? [];
                            $vis = $analisis['visualizacion'] ?? [];
                            $puedeGuardar = $analisis['puede_guardar'] ?? false;
                            
                            $panelClass = match ($estadoPanel) {
                                'RECONOCIDA' => 'ui-alert-success',
                                'REDACTABLE' => 'ui-alert-info',
                                'REQUIERE_REVISION' => 'ui-alert-warning',
                                'BLOQUEADA', 'DUPLICADA' => 'ui-alert-danger',
                                default => 'ui-card-soft',
                            };
                        @endphp

                        <div class="{{ $panelClass }} p-5 rounded-2xl relative overflow-hidden">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.16em] opacity-80">Análisis curricular BTH</p>
                                    <h4 class="mt-1.5 text-xl font-black">
                                        @if ($estadoPanel === 'RECONOCIDA')
                                            Especialidad reconocida
                                        @elseif ($estadoPanel === 'REDACTABLE')
                                            Corregible formal
                                        @elseif ($estadoPanel === 'REQUIERE_REVISION')
                                            Requiere validación
                                        @elseif ($estadoPanel === 'BLOQUEADA')
                                            Entrada bloqueada
                                        @elseif ($estadoPanel === 'DUPLICADA')
                                            Registro duplicado
                                        @else
                                            Análisis de validez
                                        @endif
                                    </h4>
                                </div>
                                <span class="px-3 py-1 text-xs font-black uppercase rounded-full" style="background: rgba(0,0,0,0.08)">
                                    {{ str_replace('_', ' ', $estadoPanel) }}
                                </span>
                            </div>

                            <p class="mt-3 text-xs leading-relaxed">
                                {{ $analisis['mensaje'] ?? '' }}
                            </p>

                            <div class="grid grid-cols-3 gap-2 mt-4 pt-4 border-t border-black/5 text-center">
                                <div>
                                    <span class="block text-[10px] uppercase font-bold opacity-75">Formulario</span>
                                    <span class="text-lg font-black">{{ $completitud }}%</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] uppercase font-bold opacity-75">Validez BTH</span>
                                    <span class="text-lg font-black text-violet-600">{{ $validez }}%</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] uppercase font-bold opacity-75">Confianza</span>
                                    <span class="text-lg font-black text-emerald-600">{{ $confianza }}%</span>
                                </div>
                            </div>
                        </div>

                        @if ($estadoPanel === 'BLOQUEADA' || $estadoPanel === 'DUPLICADA')
                            @if (!empty($bloqueos))
                                <div class="ui-alert-danger p-4 rounded-xl border border-rose-500/20 text-rose-500 text-sm space-y-1">
                                    <p class="font-black flex items-center gap-2 text-xs uppercase tracking-wider">
                                        Motivo del bloqueo preventivo:
                                    </p>
                                    @foreach ($bloqueos as $blq)
                                        <p class="text-xs">• {{ $blq }}</p>
                                    @endforeach
                                </div>
                            @endif
                        @endif

                        @if ($estadoPanel === 'REQUIERE_REVISION' || $estadoPanel === 'REDACTABLE')
                            @if ($confianza >= 25 && !empty($analisis['sugerencias']))
                                <div class="ui-card-soft p-4 rounded-xl border space-y-3">
                                    <div>
                                        <span class="block ui-muted text-[10px] uppercase font-black">Sugerencia recomendada</span>
                                        <p class="ui-title font-black text-sm mt-1">¿Quiso decir: <span class="text-indigo-500">"{{ $analisis['sugerencias'][0] ?? '' }}"</span>?</p>
                                    </div>

                                    <button type="button" wire:click="aplicarSugerencia" class="ui-btn-primary w-full py-2 text-xs rounded-xl">
                                        Aplicar sugerencia formal
                                    </button>
                                </div>
                            @endif
                        @endif

                        {{-- Vista previa limitada --}}
                        @if ($estadoPanel === 'REDACTABLE' && ($vis['mostrar_vista_previa_limitada'] ?? false))
                            <div class="ui-card-soft p-5 rounded-2xl border space-y-4" style="border-color: var(--ui-warning-border);">
                                <div>
                                    <span class="ui-badge-warning text-[9px] font-black uppercase tracking-wider">Vista previa limitada</span>
                                    <h4 class="ui-title text-sm font-black mt-1">Alineación estimada: {{ $analisis['datos']['nom_esp'] }}</h4>
                                </div>

                                <div class="grid grid-cols-2 gap-3 text-xs border-t pt-3" style="border-color: var(--ui-border);">
                                    <div>
                                        <span class="block ui-muted text-[9px] uppercase font-black">Familia Profesional</span>
                                        <span class="font-bold ui-title">{{ $vis['familia_profesional'] ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="block ui-muted text-[9px] uppercase font-black">Campo Formativo</span>
                                        <span class="font-bold ui-title">{{ $vis['campo_formativo'] ?? '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Mapa Completo --}}
                        @if ($estadoPanel === 'RECONOCIDA' && ($vis['mostrar_mapa_completo'] ?? false))
                            <div class="ui-card-soft p-5 rounded-2xl border space-y-5" style="border-color: {{ $vis['color_hex'] ?? 'var(--ui-border)' }}40;">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-wider" style="color: {{ $vis['color_hex'] }}">Mapa Académico-Vocacional BTH</p>
                                    <h4 class="ui-title text-base font-black mt-1">{{ $analisis['datos']['nom_esp'] }}</h4>
                                </div>

                                <div class="grid grid-cols-2 gap-3 text-xs border-t pt-3" style="border-color: var(--ui-border);">
                                    <div>
                                        <span class="block ui-muted text-[9px] uppercase font-black">Familia Profesional</span>
                                        <span class="font-bold ui-title">{{ $vis['familia_profesional'] ?? '-' }}</span>
                                    </div>
                                    <div>
                                        <span class="block ui-muted text-[9px] uppercase font-black">Campo Formativo</span>
                                        <span class="font-bold ui-title">{{ $vis['campo_formativo'] ?? '-' }}</span>
                                    </div>
                                    <div class="mt-2">
                                        <span class="block ui-muted text-[9px] uppercase font-black">Área BTH</span>
                                        <span class="font-bold ui-title">{{ $vis['area_bth'] ?? '-' }}</span>
                                    </div>
                                    <div class="mt-2">
                                        <span class="block ui-muted text-[9px] uppercase font-black">Perfil RIASEC</span>
                                        <span class="font-bold text-violet-500 font-mono">{{ $vis['perfil_riasec'] ?? '-' }}</span>
                                    </div>
                                </div>

                                @if (!empty($vis['niveles']))
                                    <div class="border-t pt-3" style="border-color: var(--ui-border);">
                                        <span class="text-[10px] font-black uppercase tracking-wider block mb-2" style="color: var(--ui-muted)">Nivel de Competencias</span>
                                        <div class="space-y-2">
                                            @foreach($vis['niveles'] as $label => $lvl)
                                                <div class="text-xs">
                                                    <div class="flex justify-between text-[10px] mb-1">
                                                        <span style="color: var(--ui-text)">{{ $label }}</span>
                                                        <span class="font-black">{{ $lvl }}%</span>
                                                    </div>
                                                    <div class="h-1.5 rounded-full w-full bg-slate-900/40 overflow-hidden">
                                                        <div class="h-full rounded-full transition-all"
                                                             style="width: {{ $lvl }}%; background-color: {{ $vis['color_hex'] ?? 'var(--ui-primary)' }}"></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-col-reverse gap-3 sm:flex-row sm:justify-end border-t p-6"
                     style="border-color: var(--ui-border); background: var(--ui-surface-soft);">
                    <button type="button" wire:click="cerrarModales" class="ui-btn-secondary px-5 py-2.5 rounded-xl">
                        Cancelar
                    </button>

                    <button type="button" wire:click="guardar" class="ui-btn-primary px-5 py-2.5 rounded-xl"
                            @disabled(!$puedeGuardar)>
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL DETALLE --}}
    @if ($modalDetalle)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="ui-modal-backdrop" wire:click="cerrarModales"></div>

            <div class="ui-modal w-full max-w-3xl rounded-[2rem] p-6 sm:p-8 overflow-y-auto ui-scrollbar">
                <div class="flex justify-between gap-4 border-b pb-4" style="border-color: var(--ui-border);">
                    <div>
                        <p class="ui-kicker">Vista de información detallada</p>
                        <h2 class="ui-title text-2xl font-black">{{ $detalle['nom_esp'] ?? 'Detalle' }}</h2>
                    </div>
                    <button type="button" wire:click="cerrarModales" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="ui-card-soft p-4 rounded-xl">
                        <span class="text-xs font-bold uppercase tracking-[0.12em]" style="color: var(--ui-muted)">Código</span>
                        <p class="mt-1 text-sm font-black font-mono">{{ $detalle['cod_esp'] ?? '-' }}</p>
                    </div>

                    <div class="ui-card-soft p-4 rounded-xl">
                        <span class="text-xs font-bold uppercase tracking-[0.12em]" style="color: var(--ui-muted)">Sigla BTH</span>
                        <p class="mt-1 text-sm font-black text-indigo-500">{{ $detalle['sig_esp'] ?? '-' }}</p>
                    </div>

                    <div class="ui-card-soft p-4 rounded-xl">
                        <span class="text-xs font-bold uppercase tracking-[0.12em]" style="color: var(--ui-muted)">Familia Profesional</span>
                        <p class="mt-1 text-sm font-black">{{ $detalle['fam_pro_esp'] ?? 'General' }}</p>
                    </div>

                    <div class="ui-card-soft p-4 rounded-xl">
                        <span class="text-xs font-bold uppercase tracking-[0.12em]" style="color: var(--ui-muted)">Campo Formativo</span>
                        <p class="mt-1 text-sm font-black">{{ $detalle['cam_for_esp'] ?? '-' }}</p>
                    </div>

                    <div class="ui-card-soft p-4 rounded-xl sm:col-span-2">
                        <span class="text-xs font-bold uppercase tracking-[0.12em]" style="color: var(--ui-muted)">Descripción</span>
                        <p class="mt-1 text-xs leading-relaxed font-semibold">{{ $detalle['des_esp'] ?: 'Sin descripción.' }}</p>
                    </div>

                    @if(!empty($detalle['perfil_riasec_esp']))
                        <div class="ui-card-soft p-4 rounded-xl">
                            <span class="text-xs font-bold uppercase tracking-[0.12em]" style="color: var(--ui-muted)">Perfil RIASEC</span>
                            <p class="mt-1 text-sm font-black text-violet-500 font-mono">{{ implode(' / ', $detalle['perfil_riasec_esp']) }}</p>
                        </div>
                    @endif

                    @if(!empty($detalle['int_mul_esp']))
                        <div class="ui-card-soft p-4 rounded-xl">
                            <span class="text-xs font-bold uppercase tracking-[0.12em]" style="color: var(--ui-muted)">Inteligencias</span>
                            <p class="mt-1 text-xs font-bold">{{ implode(', ', $detalle['int_mul_esp']) }}</p>
                        </div>
                    @endif

                    @if(!empty($detalle['car_rel_esp']))
                        <div class="ui-card-soft p-4 rounded-xl sm:col-span-2">
                            <span class="text-xs font-bold uppercase tracking-[0.12em]" style="color: var(--ui-muted)">Carreras Recomendadas</span>
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                @foreach($detalle['car_rel_esp'] as $car)
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-indigo-500/10 text-indigo-400 border border-indigo-500/10">{{ $car }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(!empty($detalle['asi_rel_esp']))
                        <div class="ui-card-soft p-4 rounded-xl sm:col-span-2">
                            <span class="text-xs font-bold uppercase tracking-[0.12em]" style="color: var(--ui-muted)">Asignaturas Clave</span>
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                @foreach($detalle['asi_rel_esp'] as $asig)
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/10">{{ $asig }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if(!empty($detalle['comp_tec_esp']))
                        <div class="ui-card-soft p-4 rounded-xl sm:col-span-2">
                            <span class="text-xs font-bold uppercase tracking-[0.12em]" style="color: var(--ui-muted)">Competencias de Egreso</span>
                            <div class="text-xs space-y-1 mt-2 text-[var(--ui-text-soft)]">
                                @foreach($detalle['comp_tec_esp'] as $comp)
                                    <p>• {{ $comp }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <div class="mt-6 flex justify-end gap-3 border-t pt-4" style="border-color: var(--ui-border);">
                    <button type="button" wire:click="cerrarModales" class="ui-btn-secondary px-5 py-2 rounded-xl">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
