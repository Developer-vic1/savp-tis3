<div
    class="space-y-6"
    x-data="{
        color(name) {
            return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
        },

        alerta(icon, title, text, colorVar = '--ui-primary') {
            Swal.fire({
                icon,
                title,
                text,
                confirmButtonText: 'Entendido',
                confirmButtonColor: this.color(colorVar) || '#059669',
                background: this.color('--ui-surface') || '#ffffff',
                color: this.color('--ui-text') || '#0f172a'
            });
        },

        init() {
            window.addEventListener('paralelo-creado', event => {
                this.alerta('success', 'Paralelo registrado', event.detail.mensaje ?? 'El paralelo fue registrado correctamente.');
            });

            window.addEventListener('paralelo-actualizado', event => {
                this.alerta('success', 'Paralelo actualizado', event.detail.mensaje ?? 'El paralelo fue actualizado correctamente.');
            });

            window.addEventListener('paralelo-desactivado', event => {
                this.alerta('success', 'Paralelo desactivado', event.detail.mensaje ?? 'El paralelo fue desactivado correctamente.');
            });

            window.addEventListener('paralelo-reactivado', event => {
                this.alerta('success', 'Paralelo reactivado', event.detail.mensaje ?? 'El paralelo fue reactivado correctamente.');
            });

            window.addEventListener('advertencia-general', event => {
                this.alerta('warning', 'Revisión requerida', event.detail.mensaje ?? 'Revisa la información antes de continuar.', '--ui-warning');
            });

            window.addEventListener('duplicado-inactivo', event => {
                this.alerta('warning', 'Histórico recuperable', event.detail.mensaje ?? 'Ya existe un paralelo inactivo con ese nombre.', '--ui-warning');
            });

            window.addEventListener('error-general', event => {
                this.alerta('error', 'No se pudo completar la acción', event.detail.mensaje ?? 'Ocurrió un error inesperado.', '--ui-danger');
            });


        }
    }"
>
    @include('livewire.admin.paralelos.panel')

    @include('livewire.admin.paralelos.filtros')

    {{-- TABLA --}}
    <section class="ui-table-wrap">
        <div class="flex flex-col gap-3 border-b p-5 md:flex-row md:items-center md:justify-between" style="border-color: var(--ui-border);">
            <div>
                <h3 class="ui-title text-lg font-black">Paralelos institucionales</h3>
                <p class="ui-muted mt-1 text-sm">
                    Uso de la gestión vigente. Ver muestra organización y capacidad; editar exige justificación y respaldo.
                </p>
            </div>

            <div wire:loading class="text-sm font-bold" style="color: var(--ui-primary);">
                Actualizando información...
            </div>
        </div>

        <div class="overflow-x-auto ui-scrollbar">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th><button type="button" wire:click="ordenarPor('nom_par')" class="hover:underline">Paralelo</button></th>
                        <th>Disponibilidad</th>
                        <th>Estudiantes</th>
                        <th>Uso académico</th>
                        <th>Impacto</th>
                        <th><button type="button" wire:click="ordenarPor('est_par')" class="hover:underline">Estado</button></th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($paralelos as $paralelo)
                        @php
                            $uso = $paralelo->uso_academico ?? [];
                            $impactoParalelo = $paralelo->impacto_academico ?? [];
                            $disponibilidad = $paralelo->disponibilidad ?? [];

                            $impactoBadge = match ($impactoParalelo['nivel'] ?? '') {
                                'ALTO' => 'ui-badge-success',
                                'MEDIO' => 'ui-badge-violet',
                                'BAJO' => 'ui-badge-info',
                                'HISTORICO' => 'ui-badge-warning',
                                default => 'ui-badge-muted',
                            };

                            $disponibilidadBadge = match ($disponibilidad['estado'] ?? '') {
                                'DISPONIBLE' => 'ui-badge-success',
                                'HISTORICO' => 'ui-badge-warning',
                                'SIN_USO' => 'ui-badge-warning',
                                default => 'ui-badge-muted',
                            };
                        @endphp

                        <tr wire:key="paralelo-{{ $paralelo->cod_par }}">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-11 w-11 items-center justify-center rounded-2xl text-sm font-black"
                                        style="background: var(--ui-primary-soft); color: var(--ui-primary); border: 1px solid var(--ui-primary-border);"
                                    >
                                        {{ mb_substr($paralelo->nom_par, 0, 2) }}
                                    </div>

                                    <div>
                                        <p class="ui-title font-black">Paralelo {{ $paralelo->nom_par }}</p>
                                        <p class="ui-muted text-xs">Catálogo institucional</p>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <span class="{{ $disponibilidadBadge }}">
                                    {{ $disponibilidad['texto'] ?? 'Sin información' }}
                                </span>
                            </td>

                            <td>
                                <p class="ui-title text-sm font-black">{{ $uso['estudiantes'] ?? 0 }} estudiantes</p>
                                <p class="ui-muted text-xs">{{ $uso['cursos'] ?? 0 }} cursos vinculados</p>
                            </td>

                            <td>
                                <p class="ui-title text-sm font-bold">{{ $uso['texto'] ?? 'Sin uso académico' }}</p>
                            </td>

                            <td>
                                <span class="{{ $impactoBadge }}">{{ $impactoParalelo['texto'] ?? 'Sin uso' }}</span>
                            </td>

                            <td>
                                @if ($paralelo->est_par === 'ACTIVO')
                                    <span class="ui-badge-success">Activo</span>
                                @else
                                    <span class="ui-badge-warning">Inactivo</span>
                                @endif
                            </td>

                            <td class="text-right">
                                <div class="inline-flex items-center gap-1.5">
                                    <button type="button" wire:click="abrirModalDetalle('{{ $paralelo->cod_par }}')" class="ui-icon-btn" title="Ver detalle">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>

                                    <button type="button" wire:click="abrirModalEditar('{{ $paralelo->cod_par }}')" class="ui-icon-btn" title="Editar">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                        </svg>
                                    </button>

                                    @if ($paralelo->est_par === 'ACTIVO')
                                        <button type="button" wire:click="solicitarDesactivar('{{ $paralelo->cod_par }}')" class="ui-icon-btn" style="color: var(--ui-warning);" title="Desactivar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                        </button>
                                    @else
                                        <button type="button" wire:click="solicitarReactivar('{{ $paralelo->cod_par }}')" class="ui-icon-btn" style="color: var(--ui-primary);" title="Reactivar">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-16 text-center">
                                <div class="mx-auto max-w-md">
                                    <h4 class="ui-title text-lg font-black">No se encontraron paralelos</h4>
                                    <p class="ui-muted mt-2 text-sm leading-6">
                                        Ajusta los filtros o registra un nuevo paralelo institucional.
                                    </p>
                                    <button type="button" wire:click="abrirModalCrear" class="ui-btn-primary mt-5">
                                        Registrar paralelo
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($paralelos->hasPages())
            <div class="border-t px-5 py-4" style="border-color: var(--ui-border);">
                {{ $paralelos->links() }}
            </div>
        @endif
    </section>

    @if ($modalCrear)
        @include('livewire.admin.paralelos.crear')
    @endif

    {{-- MODAL EDITAR --}}
    @if ($modalEditar)
        @teleport('body')
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="Paralelos: editar" x-trap.inert.noscroll="true" x-on:keydown.escape.window="$wire.cerrarModalEditar()">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-5xl overflow-y-auto ui-scrollbar">
                <div class="ui-modal-header flex items-start justify-between">
                    <div>
                        <h3 class="ui-title text-2xl font-black">Editar paralelo</h3>
                        <p class="ui-muted mt-1 text-sm">
                            Si tiene estudiantes o planificación, solo se permiten correcciones menores.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarModalEditar" aria-label="Cerrar editar de paralelo" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid gap-6 p-6 lg:grid-cols-[.9fr_1.1fr]">
                    <div class="space-y-5">
                        <div>
                            <label class="ui-label">Nombre del paralelo</label>
                            <input type="text" wire:model.live.debounce.400ms="formEditar.nom_par" class="ui-input">
                            @error('formEditar.nom_par')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-selector-institucional modelo="formEditar.est_par" identificador="editar-paralelo-estado" etiqueta="Estado" :opciones="collect($estadosDisponibles)->map(fn($texto,$valor)=>['valor'=>$valor,'etiqueta'=>$texto])->values()->all()" />
                        </div>

                        <div class="ui-alert-warning">
                            Cambiar A por B no es una corrección menor si ya existe historial académico. Usa edición solo
                            para normalizar nombres, no para cambiar la identidad del grupo.
                        </div>
                        @include('livewire.admin.paralelos.expediente')
                    </div>

                    @php
                        $estadoAnalisisEditar = $analisisEditar['estado_inteligente'] ?? '';
                        $panelEditar = match ($estadoAnalisisEditar) {
                            'VALIDO' => 'ui-alert-success',
                            'REDACTABLE' => 'ui-alert-info',
                            'DUPLICADO_ACTIVO' => 'ui-alert-danger',
                            'DUPLICADO_INACTIVO' => 'ui-alert-warning',
                            'REQUIERE_REVISION' => 'ui-alert-warning',
                            'BLOQUEADO' => 'ui-alert-danger',
                            default => 'ui-card-soft',
                        };
                    @endphp

                    <div class="space-y-5">
                        <div class="{{ $panelEditar }}">
                            <p class="text-xs font-black uppercase tracking-[0.16em]">Análisis inteligente</p>
                            <h4 class="mt-2 text-xl font-black">{{ $analisisEditar['nombre_sugerido'] ?: 'Pendiente' }}</h4>
                            <p class="mt-3 text-sm leading-6">{{ $analisisEditar['mensaje'] ?? 'Edita el paralelo para analizarlo.' }}</p>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Sugerencia</p>
                                <p class="ui-title mt-2 text-xl font-black">{{ $analisisEditar['nombre_sugerido'] ?: 'Pendiente' }}</p>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-black uppercase tracking-wider">Reconocimiento del nombre</p>
                                <p class="mt-2 text-xl font-black" style="color: var(--ui-primary);">{{ $analisisEditar['confianza'] ?? 0 }}%</p>
                            </div>
                        </div>

                        @if (!empty($analisisEditar['advertencias']))
                            <div class="ui-alert-warning">
                                <p class="font-black">Advertencias</p>
                                <ul class="mt-2 space-y-1 text-sm leading-6">
                                    @foreach ($analisisEditar['advertencias'] as $advertencia)
                                        <li>{{ $advertencia }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="cerrarModalEditar" class="ui-btn-secondary">Cancelar</button>
                    <button type="button" wire:click="usarSugerenciaEditar" class="ui-btn-secondary">Usar sugerencia</button>
                    @if(!$this->expedienteCompleto)<p class="ui-muted text-xs">Completa la justificación, revisa el PDF y confirma su alcance.</p>@endif
                    <button type="button" wire:click="guardarEdicionParalelo" wire:loading.attr="disabled" @disabled(!$this->expedienteCompleto) class="ui-btn-primary">
                        <span wire:loading.remove wire:target="guardarEdicionParalelo">Guardar cambios</span>
                        <span wire:loading wire:target="guardarEdicionParalelo">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
        @endteleport
    @endif

    {{-- MODAL DETALLE --}}
    @if ($modalDetalle)
        @teleport('body')
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="Paralelos: detalle" x-trap.inert.noscroll="true" x-on:keydown.escape.window="$wire.cerrarModalDetalle()">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-5xl overflow-y-auto ui-scrollbar">
                <div class="ui-modal-header flex items-start justify-between">
                    <div>
                        <h3 class="ui-title text-2xl font-black">Detalle del Paralelo {{ $detalleParalelo['nombre'] ?? '' }}</h3>
                        <p class="ui-muted mt-1 text-sm">Gestión vigente: grupos por grado y turno, capacidad y planificación. La historia se conserva.</p>
                    </div>

                    <button type="button" wire:click="cerrarModalDetalle" aria-label="Cerrar detalle de paralelo" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                @php
                    $detalleUso = $detalleParalelo['uso'] ?? [];
                    $detalleDisponibilidad = $detalleParalelo['disponibilidad'] ?? [];
                    $detalleImpacto = $detalleParalelo['impacto'] ?? [];
                    $detalleBitacora = $detalleParalelo['ultima_bitacora'] ?? null;
                    $detalleCursos = $detalleParalelo['distribucion_cursos'] ?? [];
                @endphp

                <div class="space-y-5 p-6">
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="ui-card-soft p-4">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Estado</p>
                            <p class="mt-2 text-lg font-black" style="color: {{ ($detalleParalelo['estado'] ?? '') === 'ACTIVO' ? 'var(--ui-primary)' : 'var(--ui-warning)' }};">
                                {{ $detalleParalelo['estado'] ?? '-' }}
                            </p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Disponibilidad</p>
                            <p class="ui-title mt-2 text-lg font-black">{{ $detalleDisponibilidad['texto'] ?? '-' }}</p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Estudiantes</p>
                            <p class="ui-title mt-2 text-lg font-black">{{ $detalleUso['estudiantes'] ?? 0 }}</p>
                        </div>

                        <div class="ui-card-soft p-4">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Impacto</p>
                            <p class="ui-title mt-2 text-lg font-black">{{ $detalleImpacto['texto'] ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="ui-card-soft p-5">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Cursos vinculados</p>
                            <p class="ui-title mt-3 text-3xl font-black">{{ $detalleUso['cursos'] ?? 0 }}</p>
                        </div>

                        <div class="ui-card-soft p-5">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Planes de asignatura</p>
                            <p class="ui-title mt-3 text-3xl font-black">{{ $detalleUso['planes_asignatura'] ?? 0 }}</p>
                        </div>

                        <div class="ui-card-soft p-5">
                            <p class="ui-muted text-xs font-black uppercase tracking-wider">Horarios vinculados</p>
                            <p class="ui-title mt-3 text-3xl font-black">{{ $detalleUso['horarios'] ?? 0 }}</p>
                        </div>
                    </div>

                    <div class="ui-card-soft p-5">
                        <h4 class="ui-title text-lg font-black">Grupos, capacidad y planificación</h4>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            @forelse ($detalleCursos as $curso)
                                <div class="ui-card p-3">
                                    <div class="flex flex-wrap items-center justify-between gap-2"><span class="ui-title text-sm font-bold">{{ $curso['curso'] }}</span>
                                    <span class="ui-muted text-sm">{{ $curso['estudiantes'] }} estudiantes · {{ $curso['capacidad'] ?? '—' }} plazas</span></div>
                                    <p class="ui-muted mt-1 text-xs">{{ $curso['planes'] }} planes curriculares · {{ $curso['especialidades'] }} planes técnicos</p>
                                    @if ($curso['capacidad'] > 0)<div class="mt-2 h-1.5 overflow-hidden rounded-full" style="background: var(--ui-surface-soft);"><div class="h-full" style="width: {{ min(100, round($curso['estudiantes'] / $curso['capacidad'] * 100)) }}%; background: {{ $curso['estudiantes'] > $curso['capacidad'] ? 'var(--ui-danger)' : 'var(--ui-primary)' }};"></div></div>@endif
                                    @if ($curso['capacidad'] > 0 && $curso['estudiantes'] > $curso['capacidad'])<p class="ui-error text-xs">Excede la capacidad registrada; revisa el expediente antes de redistribuir.</p>@endif
                                </div>
                            @empty
                                <p class="ui-muted text-sm">No existe distribución por curso registrada para este paralelo.</p>
                            @endforelse
                        </div>
                        <p class="ui-muted mt-3 text-xs">Miembros de cada grupo según su vigencia actual. Un estudiante puede participar también en formación técnica; estas filas no se suman como personas distintas.</p>
                    </div>

                    @if ($detalleBitacora)
                        <div class="ui-card-soft p-5">
                            <h4 class="ui-title text-lg font-black">Última acción registrada</h4>
                            <p class="ui-muted mt-2 text-sm leading-6">
                                {{ $detalleBitacora['descripcion'] ?? 'Acción registrada.' }}
                            </p>
                            <p class="ui-muted mt-2 text-xs">
                                Fecha: {{ $detalleBitacora['fecha'] ?? '-' }} · Acción: {{ $detalleBitacora['accion'] ?? '-' }}
                            </p>
                            @if ($expediente = $detalleBitacora['expediente'] ?? null)
                                <dl class="ui-muted mt-3 grid gap-2 text-sm sm:grid-cols-2"><div><dt class="font-bold">Documento</dt><dd>{{ $expediente['numero'] }} · {{ $expediente['fecha'] }}</dd></div><div><dt class="font-bold">Autoridad</dt><dd>{{ $expediente['autoridad'] }}</dd></div><div><dt class="font-bold">Gestión solicitada</dt><dd>{{ $expediente['gestion_solicitada'] }}</dd></div><div><dt class="font-bold">Comprobación registrada</dt><dd>{{ $expediente['verificacion'] }}</dd></div></dl>
                            @else<p class="ui-muted mt-3 text-xs">Esta acción anterior no contiene el nuevo expediente documental. No se presume que haya sido verificada.</p>@endif
                        </div>
                    @endif

                    <div class="ui-alert-info">
                        <p class="font-black">Recomendación institucional</p>
                        <p class="mt-2 leading-6">{{ $detalleParalelo['recomendacion'] ?? 'Sin recomendación disponible.' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endteleport
    @endif

    {{-- MODAL CATÁLOGO --}}
    @if ($modalCatalogo)
        @teleport('body')
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="Paralelos: catalogo" x-trap.inert.noscroll="true" x-on:keydown.escape.window="$wire.cerrarModalCatalogo()">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-5xl overflow-y-auto ui-scrollbar">
                <div class="ui-modal-header flex items-start justify-between">
                    <div>
                        <h3 class="ui-title text-2xl font-black">Distribución estudiantil y catálogo sugerido</h3>
                        <p class="ui-muted mt-1 text-sm">Usa paralelos simples y recuperables para mantener la organización académica limpia.</p>
                    </div>

                    <button type="button" wire:click="cerrarModalCatalogo" aria-label="Cerrar catalogo de paralelo" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($catalogoSugerido as $item)
                        <article class="ui-card-soft p-5">
                            <h4 class="ui-title text-xl font-black">Paralelo {{ $item['nombre'] }}</h4>
                            <p class="ui-muted mt-2 text-sm leading-6">{{ $item['descripcion'] }}</p>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <span class="ui-badge-info">{{ $item['tipo'] }}</span>
                                @if ($item['recomendado'])
                                    <span class="ui-badge-success">Recomendado</span>
                                @else
                                    <span class="ui-badge-warning">Uso excepcional</span>
                                @endif
                            </div>

                            <button type="button" wire:click="usarDesdeCatalogo('{{ $item['nombre'] }}')" class="ui-btn-primary mt-5 w-full">
                                Usar este paralelo
                            </button>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
        @endteleport
    @endif

    {{-- MODAL HISTÓRICOS --}}
    @if ($modalHistoricos)
        @teleport('body')
        <div class="fixed inset-0 z-[100] flex items-center justify-center p-4" role="dialog" aria-modal="true" aria-label="Paralelos: historicos" x-trap.inert.noscroll="true" x-on:keydown.escape.window="$wire.cerrarModalHistoricos()">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-4xl overflow-y-auto ui-scrollbar">
                <div class="ui-modal-header flex items-start justify-between">
                    <div>
                        <h3 class="ui-title text-2xl font-black">Históricos recuperables</h3>
                        <p class="ui-muted mt-1 text-sm">Paralelos inactivos que conservan trazabilidad académica.</p>
                    </div>

                    <button type="button" wire:click="cerrarModalHistoricos" aria-label="Cerrar historicos de paralelo" class="ui-icon-btn">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="space-y-3 p-6">
                    @forelse ($historicos as $historico)
                        <div class="ui-card-soft p-5">
                            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h4 class="ui-title font-black">Paralelo {{ $historico['nombre'] }}</h4>
                                    <p class="ui-muted mt-1 text-sm">
                                        {{ $historico['uso']['texto'] ?? 'Historial conservado' }}
                                    </p>

                                    @if (!empty($historico['ultima_bitacora']['fecha']))
                                        <p class="ui-muted mt-1 text-xs">
                                            Última desactivación: {{ $historico['ultima_bitacora']['fecha'] }}
                                        </p>
                                    @endif
                                </div>

                                <button type="button" wire:click="solicitarReactivar('{{ $historico['codigo'] }}')" class="ui-btn-primary">
                                    Reactivar
                                </button>
                            </div>
                        </div>
                    @empty
                        <p class="ui-muted text-sm">No existen paralelos históricos recuperables.</p>
                    @endforelse
                </div>
            </div>
        </div>
        @endteleport
    @endif
</div>
