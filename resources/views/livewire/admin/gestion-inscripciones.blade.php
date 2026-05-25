@php
    $icon = function (string $name, string $class = 'h-5 w-5') {
        return match ($name) {
            'plus' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 5v14M5 12h14"/></svg>',
            'search' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
            'user' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/></svg>',
            'users' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M16 21a6 6 0 0 0-12 0"/><circle cx="10" cy="7" r="4"/><path d="M22 21a5 5 0 0 0-4-4.9"/><path d="M17 3.4a4 4 0 0 1 0 7.2"/></svg>',
            'calendar' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M8 2v4M16 2v4M3 10h18"/><rect x="3" y="4" width="18" height="18" rx="3"/></svg>',
            'file' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h6"/></svg>',
            'check' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m5 13 4 4L19 7"/></svg>',
            'warning' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/></svg>',
            'x' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 6 6 18M6 6l12 12"/></svg>',
            'edit' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>',
            'eye' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>',
            'trash' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M8 6V4h8v2M6 6l1 16h10l1-16"/></svg>',
            'download' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>',
            'arrow-right' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14"/><path d="m13 6 6 6-6 6"/></svg>',
            'arrow-left' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M19 12H5"/><path d="m11 6-6 6 6 6"/></svg>',
            'filter' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 5h18M6 12h12M10 19h4"/></svg>',
            'shield' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/><path d="m9 12 2 2 4-4"/></svg>',
            'book' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M4 4.5A2.5 2.5 0 0 1 6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5z"/></svg>',
            'layers' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5"/><path d="m3 17 9 5 9-5"/></svg>',
            'chart' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19V5"/><path d="M4 19h16"/><path d="M8 16v-5"/><path d="M12 16V8"/><path d="M16 16v-3"/></svg>',
            'clock' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
            'refresh' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 12a9 9 0 0 1-15.4 6.4L3 16"/><path d="M3 16h6v6"/><path d="M3 12A9 9 0 0 1 18.4 5.6L21 8"/><path d="M21 8h-6V2"/></svg>',
            'star' => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/></svg>',
            default => '<svg class="'.$class.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/></svg>',
        };
    };

    $gestionTrabajo = $catalogos['gestion_trabajo'] ?? null;
    $turnoManana = $catalogos['turno_manana'] ?? null;
    $cupo = $analisis['cupo'] ?? ['capacidad' => 35, 'inscritos' => 0, 'disponibles' => 35, 'porcentaje' => 0, 'estado' => 'DISPONIBLE'];
    $estadoRevision = $analisis['estado'] ?? 'PENDIENTE';
    $estadoConfirmacion = $confirmacionFinal['texto_boton'] ?? 'Guardar inscripción';
    $puedeConfirmar = (bool) ($confirmacionFinal['puede_confirmar'] ?? false);
    $panelDoc = $panelDocumental['analisis'] ?? [];
    $especialidadRevision = $analisis['especialidad_tecnica'] ?? [];
@endphp

<div class="ui-page savp-inscripciones space-y-6">
    <div wire:loading.delay class="savp-loading-line"></div>

    {{-- =========================================================
        1. HERO PRINCIPAL
    ========================================================== --}}
    <section class="ui-panel savp-hero-panel relative overflow-hidden">
        <div class="savp-hero-orb savp-hero-orb-a"></div>
        <div class="savp-hero-orb savp-hero-orb-b"></div>

        <div class="relative grid gap-6 p-6 xl:grid-cols-[1fr_390px] xl:items-center">
            <div class="savp-fade-up">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="ui-badge-success">Comunidad Educativa</span>
                    <span class="ui-badge-muted">Asignación académica</span>
                    <span class="ui-badge-warning">Control documental</span>
                    <span class="ui-badge-muted">Turno Mañana por defecto</span>
                </div>

                <h1 class="ui-title mt-5 text-3xl font-black tracking-tight md:text-4xl">
                    Inscripciones
                </h1>

                <p class="ui-muted mt-3 max-w-4xl text-sm leading-6">
                    Administra la designación académica oficial del estudiante en la gestión activa:
                    curso, paralelo, turno, documentación, cupos y especialidad técnica BTH cuando corresponda.
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <button type="button" wire:click="abrirModalInscripcion" class="ui-btn-primary savp-btn-lift">
                        {!! $icon('plus', 'h-4 w-4') !!}
                        Nueva inscripción
                    </button>

                    <button type="button" wire:click="cambiarVista('tabla')" class="ui-btn-secondary savp-btn-lift">
                        {!! $icon('file', 'h-4 w-4') !!}
                        Ver registros
                    </button>

                    <button type="button" wire:click="exportarGeneral" class="ui-btn-secondary savp-btn-lift">
                        {!! $icon('download', 'h-4 w-4') !!}
                        Exportar PDF
                    </button>
                </div>
            </div>

            <div class="savp-fade-up savp-delay-1 ui-card-soft p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Gestión de trabajo</p>
                        <h2 class="ui-title mt-2 text-2xl font-black">
                            {{ $gestionTrabajo['anio'] ?? 'Sin gestión' }}
                        </h2>
                    </div>

                    <span class="{{ ($gestionTrabajo['permite_inscripcion'] ?? false) ? 'ui-badge-success' : 'ui-badge-warning' }}">
                        {{ $gestionTrabajo['estado'] ?? 'No detectada' }}
                    </span>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="ui-badge-muted p-2">{!! $icon('calendar', 'h-4 w-4') !!}</span>
                        <div>
                            <p class="ui-muted text-xs">Rango académico</p>
                            <p class="ui-title text-sm font-bold">{{ $gestionTrabajo['rango'] ?? 'Sin rango configurado' }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="ui-badge-success p-2">{!! $icon('clock', 'h-4 w-4') !!}</span>
                        <div>
                            <p class="ui-muted text-xs">Turno principal</p>
                            <p class="ui-title text-sm font-bold">
                                {{ $turnoManana['nombre'] ?? 'Turno Mañana no detectado' }}
                                @if (! empty($turnoManana['rango']))
                                    · {{ $turnoManana['rango'] }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                @if (! ($gestionTrabajo['permite_inscripcion'] ?? false))
                    <div class="ui-alert-warning mt-4">
                        Activa o planifica una gestión antes de confirmar inscripciones.
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- =========================================================
        2. RESUMEN INSTITUCIONAL
    ========================================================== --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <article class="ui-card-soft savp-card-enter p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="ui-muted text-xs">Inscritos activos</p>
                    <p class="ui-title mt-2 text-3xl font-black">{{ $resumen['activos'] ?? 0 }}</p>
                </div>
                <span class="ui-badge-success p-3">{!! $icon('users', 'h-5 w-5') !!}</span>
            </div>
            <p class="ui-muted mt-3 text-xs">Estudiantes vigentes en la gestión.</p>
        </article>

        <article class="ui-card-soft savp-card-enter savp-delay-1 p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="ui-muted text-xs">Sin inscripción</p>
                    <p class="ui-title mt-2 text-3xl font-black">{{ $resumen['sin_inscripcion'] ?? 0 }}</p>
                </div>
                <span class="ui-badge-warning p-3">{!! $icon('user', 'h-5 w-5') !!}</span>
            </div>
            <p class="ui-muted mt-3 text-xs">Pendientes de designación académica.</p>
        </article>

        <article class="ui-card-soft savp-card-enter savp-delay-2 p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="ui-muted text-xs">Observados</p>
                    <p class="ui-title mt-2 text-3xl font-black">{{ $resumen['observados'] ?? 0 }}</p>
                </div>
                <span class="ui-badge-warning p-3">{!! $icon('warning', 'h-5 w-5') !!}</span>
            </div>
            <p class="ui-muted mt-3 text-xs">Requieren seguimiento administrativo.</p>
        </article>

        <article class="ui-card-soft savp-card-enter savp-delay-3 p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="ui-muted text-xs">Documentos pendientes</p>
                    <p class="ui-title mt-2 text-3xl font-black">{{ $resumen['documentos_pendientes'] ?? 0 }}</p>
                </div>
                <span class="ui-badge-warning p-3">{!! $icon('file', 'h-5 w-5') !!}</span>
            </div>
            <p class="ui-muted mt-3 text-xs">Checklist por regularizar.</p>
        </article>

        <article class="ui-card-soft savp-card-enter savp-delay-3 p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="ui-muted text-xs">Especialidad pendiente</p>
                    <p class="ui-title mt-2 text-3xl font-black">{{ $resumen['especialidades_pendientes'] ?? 0 }}</p>
                </div>
                <span class="ui-badge-warning p-3">{!! $icon('star', 'h-5 w-5') !!}</span>
            </div>
            <p class="ui-muted mt-3 text-xs">Aplica desde 4to hacia arriba.</p>
        </article>
    </section>

    {{-- =========================================================
        3. CENTRO PRINCIPAL DE INSCRIPCIONES
    ========================================================== --}}
    <section class="grid gap-6 xl:grid-cols-[1fr_380px]">
        {{-- PANEL IZQUIERDO PRINCIPAL --}}
        <div class="space-y-6">
            {{-- CABECERA OPERATIVA --}}
            <section class="ui-panel savp-command-center">
                <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="ui-badge-success">Centro operativo</span>
                            <span class="ui-badge-muted">Gestión {{ $gestionTrabajo['anio'] ?? '—' }}</span>
                            <span class="{{ $turnoMananaAplicado ? 'ui-badge-success' : 'ui-badge-warning' }}">
                                Turno Mañana
                            </span>
                            <span class="ui-badge-warning">BTH desde 4to</span>
                        </div>

                        <h2 class="ui-title mt-3 text-2xl font-black">
                            Panel de control de inscripciones
                        </h2>

                        <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">
                            Gestiona estudiantes pendientes, registros observados, control documental, especialidades técnicas
                            y consulta institucional sin mostrar el procedimiento interno en la vista principal.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-2 xl:justify-end">
                        <button type="button" wire:click="abrirModalInscripcion" class="ui-btn-primary savp-btn-lift">
                            {!! $icon('plus', 'h-4 w-4') !!}
                            Nueva inscripción
                        </button>

                        <button type="button" wire:click="cambiarVista('observados')" class="ui-btn-secondary savp-btn-lift">
                            {!! $icon('warning', 'h-4 w-4') !!}
                            Observados
                        </button>

                        <button type="button" wire:click="cambiarVista('documentos')" class="ui-btn-secondary savp-btn-lift">
                            {!! $icon('file', 'h-4 w-4') !!}
                            Documentos
                        </button>
                    </div>
                </div>

                {{-- INDICADORES DE ATENCIÓN --}}
                <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <button type="button" wire:click="cambiarVista('proceso')" class="savp-mini-metric text-left">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="ui-muted text-xs">Pendientes</p>
                                <p class="ui-title mt-1 text-2xl font-black">{{ $resumen['sin_inscripcion'] ?? 0 }}</p>
                            </div>
                            <span class="ui-badge-warning p-2">{!! $icon('user', 'h-4 w-4') !!}</span>
                        </div>
                        <p class="ui-muted mt-2 text-xs">Listos para designación académica.</p>
                    </button>

                    <button type="button" wire:click="cambiarVista('observados')" class="savp-mini-metric text-left">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="ui-muted text-xs">Observados</p>
                                <p class="ui-title mt-1 text-2xl font-black">{{ $resumen['observados'] ?? 0 }}</p>
                            </div>
                            <span class="ui-badge-warning p-2">{!! $icon('warning', 'h-4 w-4') !!}</span>
                        </div>
                        <p class="ui-muted mt-2 text-xs">Casos con seguimiento institucional.</p>
                    </button>

                    <button type="button" wire:click="cambiarVista('documentos')" class="savp-mini-metric text-left">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="ui-muted text-xs">Documentos</p>
                                <p class="ui-title mt-1 text-2xl font-black">{{ $resumen['documentos_pendientes'] ?? 0 }}</p>
                            </div>
                            <span class="ui-badge-warning p-2">{!! $icon('file', 'h-4 w-4') !!}</span>
                        </div>
                        <p class="ui-muted mt-2 text-xs">Pendientes de regularización.</p>
                    </button>

                    <button type="button" wire:click="cambiarVista('curso')" class="savp-mini-metric text-left">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="ui-muted text-xs">Inscritos</p>
                                <p class="ui-title mt-1 text-2xl font-black">{{ $resumen['activos'] ?? 0 }}</p>
                            </div>
                            <span class="ui-badge-success p-2">{!! $icon('users', 'h-4 w-4') !!}</span>
                        </div>
                        <p class="ui-muted mt-2 text-xs">Activos en la gestión de trabajo.</p>
                    </button>
                </div>
            </section>

            {{-- CONTENIDO SEGÚN VISTA --}}
            @if ($vista === 'proceso')
                {{-- ESTUDIANTES PENDIENTES --}}
                <section class="ui-panel savp-fade-up">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <p class="ui-kicker">Pendientes de inscripción</p>
                            <h3 class="ui-title mt-1 text-xl font-black">
                                Estudiantes sin designación académica
                            </h3>
                            <p class="ui-muted mt-1 text-sm">
                                Selecciona un estudiante para abrir el formulario y asignarlo a gestión, curso, paralelo y turno.
                            </p>
                        </div>

                        <div class="relative">
                            <span class="ui-muted pointer-events-none absolute left-3 top-1/2 -translate-y-1/2">
                                {!! $icon('search', 'h-4 w-4') !!}
                            </span>
                            <input
                                type="text"
                                wire:model.live.debounce.800ms="busquedaPendientes"
                                class="ui-input min-w-[280px] pl-10"
                                placeholder="Buscar estudiante pendiente..."
                            >
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        @forelse ($estudiantesSinInscripcion as $estudiante)
                            <article class="ui-card-soft savp-pending-card p-4">
                                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                    <div class="flex gap-4">
                                        <div class="savp-avatar-md ui-badge-warning">
                                            {!! $icon('user', 'h-6 w-6') !!}
                                        </div>

                                        <div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h4 class="ui-title font-black">
                                                    {{ $estudiante['nombre_completo'] }}
                                                </h4>

                                                @if (($estudiante['activo'] ?? false))
                                                    <span class="ui-badge-success">Activo</span>
                                                @else
                                                    <span class="ui-badge-warning">Revisar</span>
                                                @endif
                                            </div>

                                            <p class="ui-muted mt-1 text-xs">
                                                CI: {{ $estudiante['ci_completo'] ?: 'Sin CI' }}
                                                · RUDE: {{ $estudiante['rud'] ?: 'Sin RUDE' }}
                                                · Edad: {{ $estudiante['edad'] ? $estudiante['edad'].' años' : 'Sin edad' }}
                                            </p>

                                            <div class="mt-3 flex flex-wrap gap-2">
                                                @if (! empty($estudiante['curso_sugerido']['curso_sugerido']))
                                                    <span class="ui-badge-muted">
                                                        Sugerido: {{ $estudiante['curso_sugerido']['curso_sugerido'] }}
                                                    </span>
                                                @else
                                                    <span class="ui-badge-warning">
                                                        Curso por definir
                                                    </span>
                                                @endif

                                                @if (! empty($estudiante['rud']))
                                                    <span class="ui-badge-success">RUDE registrado</span>
                                                @else
                                                    <span class="ui-badge-warning">RUDE pendiente</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-col gap-2 md:items-end">
                                        <button
                                            type="button"
                                            wire:click="seleccionarPendiente('{{ $estudiante['cod_est'] }}')"
                                            class="ui-btn-primary savp-btn-lift"
                                        >
                                            Inscribir
                                        </button>

                                        <p class="ui-muted text-[11px]">
                                            Abre formulario guiado
                                        </p>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="ui-card-soft col-span-full p-10 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl ui-badge-success">
                                    {!! $icon('check', 'h-8 w-8') !!}
                                </div>

                                <h4 class="ui-title mt-4 font-black">
                                    No hay estudiantes pendientes visibles
                                </h4>

                                <p class="ui-muted mt-2 text-sm">
                                    Cuando existan estudiantes activos sin inscripción en la gestión, aparecerán aquí.
                                </p>

                                <button type="button" wire:click="abrirModalInscripcion" class="ui-btn-primary mt-5">
                                    Crear inscripción manual
                                </button>
                            </div>
                        @endforelse
                    </div>
                </section>
            @endif

            @if ($vista === 'curso')
                {{-- VISTA POR CURSO --}}
                <section class="ui-panel savp-fade-up">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <p class="ui-kicker">Distribución académica</p>
                            <h3 class="ui-title mt-1 text-xl font-black">
                                Vista por curso
                            </h3>
                            <p class="ui-muted mt-1 text-sm">
                                Consulta rápida de cursos disponibles para filtrar registros y revisar concentración de estudiantes.
                            </p>
                        </div>

                        <button type="button" wire:click="exportarPorCurso" class="ui-btn-secondary">
                            {!! $icon('download', 'h-4 w-4') !!}
                            Reporte por curso
                        </button>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @forelse ($catalogos['cursos'] ?? [] as $curso)
                            <button
                                type="button"
                                wire:click="$set('filtros.cod_cur', '{{ $curso['cod_cur'] }}')"
                                class="ui-card-soft savp-academic-card p-5 text-left"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="ui-title text-lg font-black">{{ $curso['nombre'] }}</p>
                                        <p class="ui-muted mt-1 text-xs">
                                            {{ ($curso['requiere_especialidad'] ?? false) ? 'Especialidad BTH habilitada' : 'Formación regular' }}
                                        </p>
                                    </div>

                                    <span class="{{ ($curso['requiere_especialidad'] ?? false) ? 'ui-badge-warning' : 'ui-badge-muted' }}">
                                        {{ ($curso['requiere_especialidad'] ?? false) ? 'BTH' : 'Regular' }}
                                    </span>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="ui-badge-muted">Filtrar curso</span>
                                    @if (($filtros['cod_cur'] ?? '') === $curso['cod_cur'])
                                        <span class="ui-badge-success">Activo</span>
                                    @endif
                                </div>
                            </button>
                        @empty
                            <div class="ui-alert-info col-span-full">
                                No existen cursos activos para mostrar.
                            </div>
                        @endforelse
                    </div>
                </section>
            @endif

            @if ($vista === 'paralelo')
                {{-- VISTA POR PARALELO --}}
                <section class="ui-panel savp-fade-up">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <p class="ui-kicker">Paralelos y cupos</p>
                            <h3 class="ui-title mt-1 text-xl font-black">
                                Vista por paralelo
                            </h3>
                            <p class="ui-muted mt-1 text-sm">
                                Revisa paralelos activos y su capacidad referencial para apoyar la asignación académica.
                            </p>
                        </div>

                        <button type="button" wire:click="exportarPorParalelo" class="ui-btn-secondary">
                            {!! $icon('download', 'h-4 w-4') !!}
                            Reporte por paralelo
                        </button>
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        @forelse ($catalogos['paralelos'] ?? [] as $paralelo)
                            <button
                                type="button"
                                wire:click="$set('filtros.cod_par', '{{ $paralelo['cod_par'] }}')"
                                class="ui-card-soft savp-academic-card p-5 text-left"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="ui-title text-2xl font-black">{{ $paralelo['nombre'] }}</p>
                                        <p class="ui-muted mt-1 text-xs">
                                            Capacidad referencial: {{ $paralelo['capacidad'] ?? 35 }}
                                        </p>
                                    </div>

                                    <span class="ui-badge-muted">
                                        Paralelo
                                    </span>
                                </div>

                                <div class="mt-4">
                                    <div class="h-2 overflow-hidden rounded-full" style="background: var(--ui-surface-muted);">
                                        <div class="savp-progress h-full rounded-full" style="width: 35%;"></div>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <span class="ui-badge-muted">Filtrar paralelo</span>
                                    @if (($filtros['cod_par'] ?? '') === $paralelo['cod_par'])
                                        <span class="ui-badge-success">Activo</span>
                                    @endif
                                </div>
                            </button>
                        @empty
                            <div class="ui-alert-info col-span-full">
                                No existen paralelos activos para mostrar.
                            </div>
                        @endforelse
                    </div>
                </section>
            @endif

            @if ($vista === 'observados')
                {{-- VISTA OBSERVADOS --}}
                <section class="ui-panel savp-fade-up">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <p class="ui-kicker">Seguimiento institucional</p>
                            <h3 class="ui-title mt-1 text-xl font-black">
                                Inscripciones observadas
                            </h3>
                            <p class="ui-muted mt-1 text-sm">
                                Casos que requieren revisión documental, cupo, turno, edad/curso o respaldo administrativo.
                            </p>
                        </div>

                        <button type="button" wire:click="exportarObservados" class="ui-btn-secondary">
                            {!! $icon('download', 'h-4 w-4') !!}
                            Exportar observados
                        </button>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        <div class="ui-card-soft p-5">
                            <p class="ui-muted text-xs">Observados</p>
                            <p class="ui-title mt-2 text-3xl font-black">{{ $resumen['observados'] ?? 0 }}</p>
                            <p class="ui-muted mt-2 text-xs">Requieren seguimiento administrativo.</p>
                        </div>

                        <div class="ui-card-soft p-5">
                            <p class="ui-muted text-xs">Condicionales</p>
                            <p class="ui-title mt-2 text-3xl font-black">{{ $resumen['condicionales'] ?? 0 }}</p>
                            <p class="ui-muted mt-2 text-xs">Casos con condición especial.</p>
                        </div>

                        <div class="ui-card-soft p-5">
                            <p class="ui-muted text-xs">Provisionales</p>
                            <p class="ui-title mt-2 text-3xl font-black">{{ $resumen['provisionales'] ?? 0 }}</p>
                            <p class="ui-muted mt-2 text-xs">Casos que deben regularizarse.</p>
                        </div>
                    </div>

                    <div class="ui-alert-warning mt-5">
                        La tabla inferior ya está filtrada para mostrar registros observados, condicionales o provisionales.
                    </div>
                </section>
            @endif

            @if ($vista === 'documentos')
                {{-- VISTA DOCUMENTOS --}}
                <section class="ui-panel savp-fade-up">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <p class="ui-kicker">Control documental</p>
                            <h3 class="ui-title mt-1 text-xl font-black">
                                Documentos pendientes y observados
                            </h3>
                            <p class="ui-muted mt-1 text-sm">
                                Revisión de checklist documental asociado a inscripciones activas.
                            </p>
                        </div>

                        <button type="button" wire:click="exportarDocumentosPendientes" class="ui-btn-secondary">
                            {!! $icon('download', 'h-4 w-4') !!}
                            Exportar documentos
                        </button>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        <div class="ui-card-soft p-5">
                            <p class="ui-muted text-xs">Pendientes</p>
                            <p class="ui-title mt-2 text-3xl font-black">{{ $resumen['documentos_pendientes'] ?? 0 }}</p>
                            <p class="ui-muted mt-2 text-xs">Deben regularizarse.</p>
                        </div>

                        <div class="ui-card-soft p-5">
                            <p class="ui-muted text-xs">Observados</p>
                            <p class="ui-title mt-2 text-3xl font-black">{{ $resumen['documentos_observados'] ?? 0 }}</p>
                            <p class="ui-muted mt-2 text-xs">Requieren verificación.</p>
                        </div>

                        <div class="ui-card-soft p-5">
                            <p class="ui-muted text-xs">Checklist</p>
                            <p class="ui-title mt-2 text-3xl font-black">{{ $resumen['activos'] ?? 0 }}</p>
                            <p class="ui-muted mt-2 text-xs">Inscripciones activas a controlar.</p>
                        </div>
                    </div>

                    <div class="ui-alert-info mt-5">
                        Usa la acción de documentos en cada fila para revisar o actualizar la checklist.
                    </div>
                </section>
            @endif

            @if ($vista === 'historial')
                {{-- VISTA HISTORIAL --}}
                <section class="ui-panel savp-fade-up">
                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                        <div>
                            <p class="ui-kicker">Trazabilidad</p>
                            <h3 class="ui-title mt-1 text-xl font-black">
                                Historial de anulados y retirados
                            </h3>
                            <p class="ui-muted mt-1 text-sm">
                                Los registros no se eliminan físicamente. Se conservan para auditoría institucional.
                            </p>
                        </div>
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div class="ui-card-soft p-5">
                            <p class="ui-muted text-xs">Anulados</p>
                            <p class="ui-title mt-2 text-3xl font-black">{{ $resumen['anulados'] ?? 0 }}</p>
                            <p class="ui-muted mt-2 text-xs">Inscripciones anuladas lógicamente.</p>
                        </div>

                        <div class="ui-card-soft p-5">
                            <p class="ui-muted text-xs">Retirados</p>
                            <p class="ui-title mt-2 text-3xl font-black">{{ $resumen['retirados'] ?? 0 }}</p>
                            <p class="ui-muted mt-2 text-xs">Estudiantes retirados de la gestión.</p>
                        </div>
                    </div>

                    <div class="ui-alert-info mt-5">
                        La tabla inferior muestra registros históricos según el filtro activo.
                    </div>
                </section>
            @endif
        </div>

        {{-- PANEL DERECHO --}}
        <aside class="space-y-6">
            {{-- ACCIONES RÁPIDAS --}}
            <section class="ui-panel savp-side-panel">
                <p class="ui-kicker">Acciones rápidas</p>
                <h3 class="ui-title mt-1 text-lg font-black">
                    Gestión de inscripción
                </h3>

                <p class="ui-muted mt-2 text-sm leading-6">
                    Inicia una inscripción, revisa casos observados o genera reportes institucionales.
                </p>

                <div class="mt-5 space-y-3">
                    <button type="button" wire:click="abrirModalInscripcion" class="ui-btn-primary w-full justify-center">
                        {!! $icon('plus', 'h-4 w-4') !!}
                        Crear inscripción
                    </button>

                    <button type="button" wire:click="cambiarVista('proceso')" class="ui-btn-secondary w-full justify-center">
                        {!! $icon('user', 'h-4 w-4') !!}
                        Pendientes
                    </button>

                    <button type="button" wire:click="cambiarVista('tabla')" class="ui-btn-secondary w-full justify-center">
                        {!! $icon('file', 'h-4 w-4') !!}
                        Ver tabla
                    </button>
                </div>
            </section>

            {{-- REGLAS DEL SISTEMA --}}
            <section class="ui-panel savp-side-panel">
                <p class="ui-kicker">Reglas de inscripción</p>

                <div class="mt-4 space-y-3">
                    <div class="savp-rule-item">
                        <span class="ui-badge-success p-2">{!! $icon('clock', 'h-4 w-4') !!}</span>
                        <div>
                            <p class="ui-title text-sm font-black">Turno Mañana</p>
                            <p class="ui-muted text-xs">Se aplica como turno base de inscripción.</p>
                        </div>
                    </div>

                    <div class="savp-rule-item">
                        <span class="ui-badge-warning p-2">{!! $icon('star', 'h-4 w-4') !!}</span>
                        <div>
                            <p class="ui-title text-sm font-black">Especialidad BTH</p>
                            <p class="ui-muted text-xs">Opcional desde 4to hacia arriba.</p>
                        </div>
                    </div>

                    <div class="savp-rule-item">
                        <span class="ui-badge-muted p-2">{!! $icon('shield', 'h-4 w-4') !!}</span>
                        <div>
                            <p class="ui-title text-sm font-black">Trazabilidad</p>
                            <p class="ui-muted text-xs">No se elimina físicamente ningún registro.</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- REPORTES --}}
            <section class="ui-panel savp-side-panel">
                <p class="ui-kicker">Reportes</p>
                <h3 class="ui-title mt-1 text-lg font-black">
                    Salidas institucionales
                </h3>

                <div class="mt-4 grid gap-2">
                    <button type="button" wire:click="exportarGeneral" class="ui-btn-secondary justify-center">
                        Reporte general
                    </button>
                    <button type="button" wire:click="exportarPorCurso" class="ui-btn-secondary justify-center">
                        Por curso
                    </button>
                    <button type="button" wire:click="exportarPorParalelo" class="ui-btn-secondary justify-center">
                        Por paralelo
                    </button>
                    <button type="button" wire:click="exportarObservados" class="ui-btn-secondary justify-center">
                        Observados
                    </button>
                    <button type="button" wire:click="exportarDocumentosPendientes" class="ui-btn-secondary justify-center">
                        Documentos pendientes
                    </button>
                    <button type="button" wire:click="exportarEspecialidadesPendientes" class="ui-btn-secondary justify-center">
                        Especialidades pendientes
                    </button>
                </div>
            </section>
        </aside>
    </section>

    {{-- =========================================================
        4. NAVEGACIÓN DE CONSULTA
    ========================================================== --}}
    <section class="ui-panel savp-fade-up">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="ui-kicker">Consulta institucional</p>
                <h2 class="ui-title mt-1 text-xl font-black">Registros y vistas especializadas</h2>
                <p class="ui-muted mt-1 text-sm">
                    Cambia entre pendientes, tabla, cursos, paralelos, observados, documentos e historial.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach ([
                    'proceso' => 'Pendientes',
                    'tabla' => 'Tabla',
                    'curso' => 'Cursos',
                    'paralelo' => 'Paralelos',
                    'observados' => 'Observados',
                    'documentos' => 'Documentos',
                    'historial' => 'Historial',
                ] as $key => $label)
                    <button
                        type="button"
                        wire:click="cambiarVista('{{ $key }}')"
                        class="{{ $vista === $key ? 'ui-btn-primary' : 'ui-btn-secondary' }} savp-btn-lift px-4 py-2 text-xs"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    {{-- =========================================================
        5. FILTROS AVANZADOS
    ========================================================== --}}
    <section class="ui-panel savp-fade-up">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="ui-kicker">Filtros avanzados</p>
                <h3 class="ui-title mt-1 text-xl font-black">
                    Buscar y depurar registros
                </h3>
                <p class="ui-muted mt-1 text-sm">
                    Filtra por gestión, curso, paralelo, turno, tipo, estado, documentos y especialidad.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <div class="relative">
                    <span class="ui-muted pointer-events-none absolute left-3 top-1/2 -translate-y-1/2">
                        {!! $icon('search', 'h-4 w-4') !!}
                    </span>
                    <input
                        type="text"
                        wire:model.live.debounce.800ms="busquedaTabla"
                        class="ui-input min-w-[280px] pl-10"
                        placeholder="Buscar estudiante, CI o RUDE..."
                    >
                </div>

                <select wire:model.live="porPagina" class="ui-select w-28">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>

                <button type="button" wire:click="limpiarFiltros" class="ui-btn-secondary">
                    {!! $icon('filter', 'h-4 w-4') !!}
                    Limpiar
                </button>
            </div>
        </div>

        <div class="mt-5 grid gap-3 md:grid-cols-4 xl:grid-cols-9">
            <select wire:model.live="filtros.cod_gea" class="ui-select">
                <option value="">Gestión</option>
                @foreach ($catalogos['gestiones'] ?? [] as $gestion)
                    <option value="{{ $gestion['cod_gea'] }}">Gestión {{ $gestion['anio'] }}</option>
                @endforeach
            </select>

            <select wire:model.live="filtros.cod_cur" class="ui-select">
                <option value="">Curso</option>
                @foreach ($catalogos['cursos'] ?? [] as $curso)
                    <option value="{{ $curso['cod_cur'] }}">{{ $curso['nombre'] }}</option>
                @endforeach
            </select>

            <select wire:model.live="filtros.cod_par" class="ui-select">
                <option value="">Paralelo</option>
                @foreach ($catalogos['paralelos'] ?? [] as $paralelo)
                    <option value="{{ $paralelo['cod_par'] }}">{{ $paralelo['nombre'] }}</option>
                @endforeach
            </select>

            <select wire:model.live="filtros.cod_tur" class="ui-select">
                <option value="">Turno</option>
                @foreach ($catalogos['turnos'] ?? [] as $turno)
                    <option value="{{ $turno['cod_tur'] }}">{{ $turno['nombre'] }}</option>
                @endforeach
            </select>

            <select wire:model.live="filtros.tip_ins" class="ui-select">
                <option value="">Tipo</option>
                @foreach ($catalogos['tipos_inscripcion'] ?? [] as $tipo)
                    <option value="{{ $tipo }}">{{ ucfirst(strtolower($tipo)) }}</option>
                @endforeach
            </select>

            <select wire:model.live="filtros.est_ins" class="ui-select">
                <option value="">Estado</option>
                @foreach ($catalogos['estados_inscripcion'] ?? [] as $estado)
                    <option value="{{ $estado }}">{{ ucfirst(strtolower($estado)) }}</option>
                @endforeach
            </select>

            <select wire:model.live="filtros.documentos" class="ui-select">
                <option value="">Documentos</option>
                <option value="PENDIENTES">Pendientes</option>
                <option value="OBSERVADOS">Observados</option>
                <option value="COMPLETOS">Completos</option>
            </select>

            <select wire:model.live="filtros.especialidad" class="ui-select">
                <option value="">Especialidad</option>
                <option value="NO_APLICA">No aplica</option>
                <option value="PENDIENTE">Pendiente</option>
                <option value="ASIGNADA">Asignada</option>
                <option value="OBSERVADA">Observada</option>
            </select>

            <select wire:model.live="filtros.prioridad" class="ui-select">
                <option value="">Prioridad</option>
                <option value="ALTA">Alta</option>
                <option value="MEDIA">Media</option>
                <option value="BAJA">Baja</option>
            </select>
        </div>
    </section>

    {{-- =========================================================
        6. TABLA PROFESIONAL DE REGISTROS
    ========================================================== --}}
    <section class="ui-panel savp-fade-up">
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
            <div>
                <p class="ui-kicker">Listado institucional</p>
                <h3 class="ui-title mt-1 text-xl font-black">
                    Inscripciones registradas
                </h3>
                <p class="ui-muted mt-1 text-sm">
                    Consulta, edita, revisa documentos, genera constancias, anula o registra retiros sin eliminación física.
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <button type="button" wire:click="generarNomina" class="ui-btn-secondary">
                    {!! $icon('download', 'h-4 w-4') !!}
                    Nómina
                </button>

                <button type="button" wire:click="exportarGeneral" class="ui-btn-secondary">
                    {!! $icon('download', 'h-4 w-4') !!}
                    Exportar
                </button>
            </div>
        </div>

        <div class="mt-5 overflow-x-auto">
            <table class="w-full min-w-[1240px] text-left text-sm">
                <thead>
                    <tr class="ui-muted border-b" style="border-color: var(--ui-border);">
                        <th class="px-4 py-3 text-xs font-black uppercase tracking-[0.18em]">Estudiante</th>
                        <th class="px-4 py-3 text-xs font-black uppercase tracking-[0.18em]">Gestión</th>
                        <th class="px-4 py-3 text-xs font-black uppercase tracking-[0.18em]">Asignación</th>
                        <th class="px-4 py-3 text-xs font-black uppercase tracking-[0.18em]">Tipo</th>
                        <th class="px-4 py-3 text-xs font-black uppercase tracking-[0.18em]">Especialidad</th>
                        <th class="px-4 py-3 text-xs font-black uppercase tracking-[0.18em]">Estado</th>
                        <th class="px-4 py-3 text-xs font-black uppercase tracking-[0.18em]">Documentos</th>
                        <th class="px-4 py-3 text-xs font-black uppercase tracking-[0.18em]">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($inscripciones as $inscripcion)
                        <tr class="savp-row border-b transition" style="border-color: var(--ui-border);">
                            <td class="px-4 py-4">
                                <div class="flex items-start gap-3">
                                    <div class="savp-avatar-sm ui-badge-muted">
                                        {!! $icon('user', 'h-4 w-4') !!}
                                    </div>

                                    <div>
                                        <p class="ui-title font-black">
                                            {{ trim(($inscripcion->nom_per ?? '').' '.($inscripcion->ape_pat_per ?? '').' '.($inscripcion->ape_mat_per ?? '')) ?: 'Estudiante' }}
                                        </p>
                                        <p class="ui-muted mt-1 text-xs">
                                            CI: {{ $inscripcion->ci_per ?? '—' }}
                                            @if (! empty($inscripcion->rud_est))
                                                · RUDE: {{ $inscripcion->rud_est }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-4">
                                <p class="ui-title font-bold">{{ $inscripcion->ani_gea ?? '—' }}</p>
                                <p class="ui-muted text-xs">{{ $inscripcion->fei_ins ?? '' }}</p>
                            </td>

                            <td class="px-4 py-4">
                                <p class="ui-title font-bold">
                                    {{ $inscripcion->nom_cur ?? 'Curso' }} · {{ $inscripcion->nom_par ?? 'Paralelo' }}
                                </p>
                                <p class="ui-muted text-xs">{{ $inscripcion->nom_tur ?? 'Turno' }}</p>
                            </td>

                            <td class="px-4 py-4">
                                <span class="ui-badge-muted">
                                    {{ ucfirst(strtolower($inscripcion->tip_ins ?? 'Regular')) }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                <span class="{{ $this->badgeEspecialidad($inscripcion->est_esp_tec_ins ?? 'NO_APLICA') }}">
                                    {{ str_replace('_', ' ', ucfirst(strtolower($inscripcion->est_esp_tec_ins ?? 'NO_APLICA'))) }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                <span class="{{ $this->badgeEstado($inscripcion->est_ins ?? 'PENDIENTE') }}">
                                    {{ ucfirst(strtolower($inscripcion->est_ins ?? 'Pendiente')) }}
                                </span>
                            </td>

                            <td class="px-4 py-4">
                                @if (($inscripcion->documentos_pendientes ?? 0) > 0 || ($inscripcion->documentos_observados ?? 0) > 0)
                                    <span class="ui-badge-warning">
                                        {{ ($inscripcion->documentos_pendientes ?? 0) + ($inscripcion->documentos_observados ?? 0) }} pendientes/observados
                                    </span>
                                @elseif ($inscripcion->doc_com_ins)
                                    <span class="ui-badge-success">Completo</span>
                                @else
                                    <span class="ui-badge-muted">Sin checklist</span>
                                @endif
                            </td>

                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" wire:click="abrirDetalle('{{ $inscripcion->cod_ins }}')" class="ui-icon-btn" title="Ver detalle">
                                        {!! $icon('eye', 'h-4 w-4') !!}
                                    </button>

                                    <button type="button" wire:click="abrirEditar('{{ $inscripcion->cod_ins }}')" class="ui-icon-btn" title="Editar">
                                        {!! $icon('edit', 'h-4 w-4') !!}
                                    </button>

                                    <button type="button" wire:click="abrirDocumentos('{{ $inscripcion->cod_ins }}')" class="ui-icon-btn" title="Documentos">
                                        {!! $icon('file', 'h-4 w-4') !!}
                                    </button>

                                    <button type="button" wire:click="generarConstancia('{{ $inscripcion->cod_ins }}')" class="ui-icon-btn" title="Constancia">
                                        {!! $icon('download', 'h-4 w-4') !!}
                                    </button>

                                    @if (in_array($inscripcion->est_ins ?? '', ['ANULADA', 'RETIRADA']))
                                        <button type="button" wire:click="reactivarInscripcion('{{ $inscripcion->cod_ins }}')" class="ui-icon-btn" title="Reactivar">
                                            {!! $icon('refresh', 'h-4 w-4') !!}
                                        </button>
                                    @else
                                        <button type="button" wire:click="confirmarAnular('{{ $inscripcion->cod_ins }}')" class="ui-icon-btn" title="Anular">
                                            {!! $icon('trash', 'h-4 w-4') !!}
                                        </button>

                                        <button type="button" wire:click="confirmarRetiro('{{ $inscripcion->cod_ins }}')" class="ui-icon-btn" title="Retirar">
                                            {!! $icon('x', 'h-4 w-4') !!}
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-14 text-center">
                                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl ui-badge-muted">
                                    {!! $icon('file', 'h-8 w-8') !!}
                                </div>
                                <h4 class="ui-title mt-4 font-black">No existen inscripciones registradas</h4>
                                <p class="ui-muted mt-2 text-sm">
                                    Crea una inscripción desde el botón principal o selecciona un estudiante pendiente.
                                </p>
                                <button type="button" wire:click="abrirModalInscripcion" class="ui-btn-primary mt-5">
                                    Nueva inscripción
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-5">
            {{ $inscripciones->links() }}
        </div>
    </section>

    {{-- =========================================================
        7. MODAL PRINCIPAL: INSCRIPCIÓN
    ========================================================== --}}
    @if ($modalInscripcion)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop savp-modal-backdrop" wire:click="cerrarModalInscripcion"></div>

            <div class="ui-modal savp-modal-shell my-6 flex max-h-[92vh] w-full max-w-7xl flex-col overflow-hidden">
                <div class="ui-modal-header sticky top-0 z-10 flex items-start justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Formulario institucional</p>
                        <h3 class="ui-title text-xl font-black">
                            {{ $modoFormulario === 'crear' ? 'Nueva inscripción' : 'Editar inscripción' }}
                        </h3>
                        <p class="ui-muted mt-1 text-sm">
                            Designa al estudiante a gestión, curso, paralelo y turno. La especialidad técnica BTH es opcional desde 4to.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarModalInscripcion" class="ui-icon-btn">
                        {!! $icon('x') !!}
                    </button>
                </div>

                <div class="border-b px-5 py-4" style="border-color: var(--ui-border);">
                    <div class="grid gap-3 md:grid-cols-3 xl:grid-cols-6">
                        @foreach ($pasosWizard as $numero => $nombre)
                            @php
                                $estadoPaso = $pasos[$numero]['estado'] ?? 'PENDIENTE';
                            @endphp

                            <button
                                type="button"
                                wire:click="irAPaso({{ $numero }})"
                                class="savp-step-card ui-card-soft p-3 text-left {{ $pasoInscripcion === $numero ? 'savp-step-active' : '' }}"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <span class="{{ $this->badgePaso($estadoPaso) }} flex h-8 w-8 items-center justify-center rounded-xl">
                                        {{ $numero }}
                                    </span>
                                    <span class="{{ $this->badgePaso($estadoPaso) }}">
                                        {{ ucfirst(strtolower($estadoPaso)) }}
                                    </span>
                                </div>
                                <p class="ui-title mt-3 text-xs font-black">{{ $nombre }}</p>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-5">
                    <div class="grid gap-6">
                        <section class="space-y-5">
                            {{-- PASO 1: IDENTIFICACIÓN DEL ESTUDIANTE --}}
                            @if ($pasoInscripcion === 1)
                                <div class="space-y-5">
                                    {{-- CABECERA DEL PASO --}}
                                    <section class="ui-panel">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="ui-badge-success">Paso 1</span>
                                                    <span class="ui-badge-muted">Identificación</span>
                                                    <span class="ui-badge-warning">Control de duplicidad</span>
                                                </div>

                                                <h4 class="ui-title mt-3 text-2xl font-black">
                                                    Identificar estudiante
                                                </h4>

                                                <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">
                                                    Busque al estudiante por RUDE, CI o nombre para comenzar el proceso de inscripción.
                                                </p>
                                            </div>

                                            <div class="ui-card-soft min-w-[230px] p-4">
                                                <p class="ui-muted text-xs">Gestión de inscripción</p>
                                                <p class="ui-title mt-1 text-lg font-black">
                                                    {{ $gestionTrabajo['anio'] ?? 'Sin gestión' }}
                                                </p>
                                                <p class="ui-muted mt-1 text-xs">
                                                    {{ $gestionTrabajo['rango'] ?? 'Rango no configurado' }}
                                                </p>
                                            </div>
                                        </div>
                                    </section>

                                    {{-- BUSCADOR PRINCIPAL --}}
                                    <section class="ui-panel">
                                        <div class="grid gap-5 xl:grid-cols-[1fr_320px]">
                                            <div>
                                                <p class="ui-kicker">Búsqueda institucional</p>
                                                <h5 class="ui-title mt-1 text-xl font-black">
                                                    Buscar estudiante registrado
                                                </h5>
                                                <p class="ui-muted mt-1 text-sm">
                                                    El estudiante debe existir previamente como registro académico para poder inscribirse.
                                                </p>

                                                <div class="savp-search-shell mt-5">
                                                    <span class="ui-muted pointer-events-none absolute left-5 top-1/2 -translate-y-1/2">
                                                        {!! $icon('search', 'h-5 w-5') !!}
                                                    </span>

                                                    <input
                                                        type="text"
                                                        wire:model.live.debounce.800ms="busquedaEstudiante"
                                                        class="ui-input savp-search-input pl-14"
                                                        placeholder="Buscar por RUDE, CI, nombre, apellido o teléfono..."
                                                        autocomplete="off"
                                                    >

                                                    <div wire:loading.delay wire:target="busquedaEstudiante" class="savp-search-loading">
                                                        <span class="savp-spinner"></span>
                                                        <span>Buscando</span>
                                                    </div>
                                                </div>

                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    <span class="ui-badge-muted">RUDE</span>
                                                    <span class="ui-badge-muted">CI</span>
                                                    <span class="ui-badge-muted">Nombre completo</span>
                                                    <span class="ui-badge-muted">Teléfono</span>
                                                </div>
                                            </div>

                                            <aside class="ui-card-soft p-4">
                                                <div class="flex items-start gap-3">
                                                    <span class="ui-badge-success p-2">
                                                        {!! $icon('shield', 'h-4 w-4') !!}
                                                    </span>

                                                    <div>
                                                        <p class="ui-title font-black">Validación inicial</p>
                                                        <p class="ui-muted mt-1 text-xs leading-5">
                                                            Al seleccionar un estudiante, se revisa si ya tiene inscripción activa,
                                                            si sus datos mínimos están completos y si existe curso sugerido por edad.
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="mt-4 space-y-2">
                                                    <div class="flex items-center justify-between gap-3 rounded-2xl border px-3 py-2" style="border-color: var(--ui-border);">
                                                        <span class="ui-muted text-xs">Turno base</span>
                                                        <span class="{{ $turnoMananaAplicado ? 'ui-badge-success' : 'ui-badge-warning' }}">
                                                            {{ $turnoMananaAplicado ? 'Mañana' : 'Revisar' }}
                                                        </span>
                                                    </div>

                                                    <div class="flex items-center justify-between gap-3 rounded-2xl border px-3 py-2" style="border-color: var(--ui-border);">
                                                        <span class="ui-muted text-xs">Especialidad BTH</span>
                                                        <span class="ui-badge-muted">Desde 4to</span>
                                                    </div>
                                                </div>
                                            </aside>
                                        </div>
                                    </section>

                                    {{-- ESTUDIANTE SELECCIONADO --}}
                                    @if ($estudianteSeleccionado)
                                        <section class="ui-panel savp-selected-student">
                                            <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                                                <div class="flex gap-4">
                                                    <div class="savp-avatar-lg ui-badge-success">
                                                        {!! $icon('user', 'h-8 w-8') !!}
                                                    </div>

                                                    <div>
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <span class="ui-badge-success">Seleccionado</span>

                                                            @if (($estudianteSeleccionado['activo'] ?? false))
                                                                <span class="ui-badge-success">Activo</span>
                                                            @else
                                                                <span class="ui-badge-warning">Revisar estado</span>
                                                            @endif

                                                            @if (! empty($situacionEstudiante['situacion']))
                                                                <span class="{{ ($situacionEstudiante['situacion'] ?? '') === 'YA_INSCRITO' ? 'ui-badge-danger' : 'ui-badge-muted' }}">
                                                                    {{ str_replace('_', ' ', $situacionEstudiante['situacion']) }}
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <h5 class="ui-title mt-3 text-2xl font-black">
                                                            {{ $estudianteSeleccionado['nombre_completo'] }}
                                                        </h5>

                                                        <p class="ui-muted mt-2 text-sm leading-6">
                                                            {{ $situacionEstudiante['mensaje'] ?? 'Estudiante listo para revisión inicial.' }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <button
                                                    type="button"
                                                    wire:click="limpiarEstudianteSeleccionado"
                                                    class="ui-btn-secondary"
                                                >
                                                    Cambiar estudiante
                                                </button>
                                            </div>

                                            {{-- DATOS CLAVE --}}
                                            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">CI</p>
                                                    <p class="ui-title mt-1 font-black">
                                                        {{ $estudianteSeleccionado['ci_completo'] ?: 'Sin CI registrado' }}
                                                    </p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">RUDE</p>
                                                    <p class="ui-title mt-1 font-black">
                                                        {{ $estudianteSeleccionado['rud'] ?: 'Sin RUDE registrado' }}
                                                    </p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Edad</p>
                                                    <p class="ui-title mt-1 font-black">
                                                        {{ ($estudianteSeleccionado['edad'] ?? null) ? ($estudianteSeleccionado['edad'].' años') : 'Sin fecha de nacimiento' }}
                                                    </p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Teléfono</p>
                                                    <p class="ui-title mt-1 font-black">
                                                        {{ $estudianteSeleccionado['telefono'] ?: 'Sin teléfono' }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mt-4 grid gap-4 md:grid-cols-2">
                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Dirección</p>
                                                    <p class="ui-title mt-1 text-sm font-bold">
                                                        {{ $estudianteSeleccionado['direccion'] ?: 'Sin dirección registrada' }}
                                                    </p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Correo</p>
                                                    <p class="ui-title mt-1 text-sm font-bold">
                                                        {{ $estudianteSeleccionado['correo'] ?: 'Sin correo registrado' }}
                                                    </p>
                                                </div>
                                            </div>

                                            {{-- ALERTAS DEL ESTUDIANTE --}}
                                            <div class="mt-5 grid gap-3 lg:grid-cols-3">
                                                @if (empty($estudianteSeleccionado['rud']))
                                                    <div class="ui-alert-warning">
                                                        El estudiante no tiene RUDE registrado. Puede continuar, pero debe regularizarse.
                                                    </div>
                                                @endif

                                                @if (empty($estudianteSeleccionado['ci']))
                                                    <div class="ui-alert-warning">
                                                        El estudiante no tiene CI registrado. Verifica documentación.
                                                    </div>
                                                @endif

                                                @if (! ($estudianteSeleccionado['activo'] ?? false))
                                                    <div class="ui-alert-danger">
                                                        El estudiante o la persona asociada no está activa. Revisa antes de confirmar.
                                                    </div>
                                                @endif

                                                @if (($situacionEstudiante['situacion'] ?? '') === 'YA_INSCRITO')
                                                    <div class="ui-alert-danger">
                                                        Ya existe una inscripción activa para esta gestión. No se recomienda duplicar.
                                                    </div>
                                                @endif

                                                @if (($situacionEstudiante['situacion'] ?? '') === 'SIN_HISTORIAL')
                                                    <div class="ui-alert-info">
                                                        No presenta historial previo. Se sugiere inscripción como nuevo estudiante.
                                                    </div>
                                                @endif

                                                @if (($situacionEstudiante['situacion'] ?? '') === 'REGULAR')
                                                    <div class="ui-alert-success">
                                                        Presenta historial previo. Se sugiere inscripción regular.
                                                    </div>
                                                @endif
                                            </div>
                                        </section>
                                    @endif

                                    {{-- RESULTADOS DE BÚSQUEDA --}}
                                    <section class="ui-panel">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                            <div>
                                                <p class="ui-kicker">Resultados</p>
                                                <h5 class="ui-title mt-1 text-xl font-black">
                                                    Estudiantes encontrados
                                                </h5>
                                                <p class="ui-muted mt-1 text-sm">
                                                    Selecciona el estudiante correcto para continuar con la inscripción.
                                                </p>
                                            </div>

                                            @if (! empty($busquedaEstudiante))
                                                <span class="ui-badge-muted">
                                                    Búsqueda: {{ $busquedaEstudiante }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mt-5 space-y-3">
                                            @forelse ($resultadosEstudiantes as $estudiante)
                                                @php
                                                    $seleccionado = ($estudianteSeleccionado['cod_est'] ?? null) === ($estudiante['cod_est'] ?? null);
                                                @endphp

                                                <article class="ui-card-soft savp-student-card p-4 {{ $seleccionado ? 'savp-student-selected' : '' }}">
                                                    <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                                        <div class="flex gap-4">
                                                            <div class="{{ $seleccionado ? 'ui-badge-success' : 'ui-badge-muted' }} savp-avatar-md">
                                                                {!! $icon('user', 'h-6 w-6') !!}
                                                            </div>

                                                            <div>
                                                                <div class="flex flex-wrap items-center gap-2">
                                                                    <h6 class="ui-title font-black">
                                                                        {{ $estudiante['nombre_completo'] }}
                                                                    </h6>

                                                                    @if ($seleccionado)
                                                                        <span class="ui-badge-success">Seleccionado</span>
                                                                    @endif

                                                                    @if (($estudiante['activo'] ?? false))
                                                                        <span class="ui-badge-success">Activo</span>
                                                                    @else
                                                                        <span class="ui-badge-warning">Revisar</span>
                                                                    @endif
                                                                </div>

                                                                <p class="ui-muted mt-1 text-xs">
                                                                    CI: {{ $estudiante['ci_completo'] ?: 'Sin CI' }}
                                                                    · RUDE: {{ $estudiante['rud'] ?: 'Sin RUDE' }}
                                                                    · Edad: {{ $estudiante['edad'] ? $estudiante['edad'].' años' : 'Sin edad' }}
                                                                    · Tel: {{ $estudiante['telefono'] ?: 'Sin teléfono' }}
                                                                </p>

                                                                <div class="mt-3 flex flex-wrap gap-2">
                                                                    @if (! empty($estudiante['direccion']))
                                                                        <span class="ui-badge-muted">Dirección registrada</span>
                                                                    @else
                                                                        <span class="ui-badge-warning">Sin dirección</span>
                                                                    @endif

                                                                    @if (! empty($estudiante['rud']))
                                                                        <span class="ui-badge-success">RUDE registrado</span>
                                                                    @else
                                                                        <span class="ui-badge-warning">RUDE pendiente</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="flex flex-wrap gap-2 xl:justify-end">
                                                            <button
                                                                type="button"
                                                                wire:click="seleccionarEstudiante('{{ $estudiante['cod_est'] }}')"
                                                                class="{{ $seleccionado ? 'ui-btn-secondary' : 'ui-btn-primary' }}"
                                                            >
                                                                {{ $seleccionado ? 'Seleccionado' : 'Seleccionar' }}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </article>
                                            @empty
                                                @if (mb_strlen(trim($busquedaEstudiante)) >= 2)
                                                    <div class="ui-card-soft p-8 text-center">
                                                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl ui-badge-warning">
                                                            {!! $icon('search', 'h-8 w-8') !!}
                                                        </div>

                                                        <h5 class="ui-title mt-4 font-black">
                                                            No se encontraron estudiantes
                                                        </h5>

                                                        <p class="ui-muted mt-2 text-sm">
                                                            Verifica el RUDE, CI o nombre. Si el estudiante no existe, primero debe registrarse en el módulo correspondiente.
                                                        </p>
                                                    </div>
                                                @else
                                                    <div class="ui-card-soft p-8 text-center">
                                                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl ui-badge-muted">
                                                            {!! $icon('user', 'h-8 w-8') !!}
                                                        </div>

                                                        <h5 class="ui-title mt-4 font-black">
                                                            Esperando búsqueda
                                                        </h5>

                                                        <p class="ui-muted mt-2 text-sm">
                                                            Escribe al menos dos caracteres para buscar estudiantes registrados.
                                                        </p>
                                                    </div>
                                                @endif
                                            @endforelse
                                        </div>
                                    </section>

                                    {{-- HISTORIAL Y CURSO SUGERIDO --}}
                                    @if ($estudianteSeleccionado)
                                        <section class="grid gap-5 xl:grid-cols-[1fr_360px]">
                                            {{-- HISTORIAL --}}
                                            <div class="ui-panel">
                                                <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                                    <div>
                                                        <p class="ui-kicker">Historial académico</p>
                                                        <h5 class="ui-title mt-1 text-xl font-black">
                                                            Inscripciones anteriores
                                                        </h5>
                                                        <p class="ui-muted mt-1 text-sm">
                                                            Referencia para clasificar si corresponde regular, nuevo, traslado, reinscripción o revisión.
                                                        </p>
                                                    </div>

                                                    <span class="ui-badge-muted">
                                                        {{ count($historialEstudiante) }} registro(s)
                                                    </span>
                                                </div>

                                                <div class="mt-5 space-y-3">
                                                    @forelse ($historialEstudiante as $historial)
                                                        <article class="ui-card-soft p-4">
                                                            <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                                                <div>
                                                                    <p class="ui-title font-black">
                                                                        Gestión {{ $historial['gestion'] ?? '—' }}
                                                                    </p>

                                                                    <p class="ui-muted mt-1 text-xs">
                                                                        {{ $historial['curso'] ?? 'Curso no registrado' }}
                                                                        · {{ $historial['paralelo'] ?? 'Paralelo no registrado' }}
                                                                        · {{ $historial['turno'] ?? 'Turno no registrado' }}
                                                                    </p>

                                                                    @if (! empty($historial['observacion']))
                                                                        <p class="ui-muted mt-2 text-xs">
                                                                            {{ $historial['observacion'] }}
                                                                        </p>
                                                                    @endif
                                                                </div>

                                                                <div class="flex flex-wrap gap-2 md:justify-end">
                                                                    <span class="{{ $this->badgeEstado($historial['estado'] ?? 'PENDIENTE') }}">
                                                                        {{ ucfirst(strtolower($historial['estado'] ?? 'Pendiente')) }}
                                                                    </span>

                                                                    <span class="ui-badge-muted">
                                                                        {{ ucfirst(strtolower($historial['tipo'] ?? 'Regular')) }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </article>
                                                    @empty
                                                        <div class="ui-alert-info">
                                                            No existen inscripciones anteriores registradas. Se puede tratar como nuevo estudiante.
                                                        </div>
                                                    @endforelse
                                                </div>
                                            </div>

                                            {{-- CURSO SUGERIDO --}}
                                            <aside class="ui-panel">
                                                <div class="flex items-start gap-3">
                                                    <span class="ui-badge-success p-2">
                                                        {!! $icon('book', 'h-5 w-5') !!}
                                                    </span>

                                                    <div>
                                                        <p class="ui-kicker">Curso sugerido</p>
                                                        <h5 class="ui-title mt-1 text-xl font-black">
                                                            {{ $cursoSugerido['nombre'] ?? ($analisis['curso_sugerido_disponible']['nombre'] ?? 'Sin sugerencia') }}
                                                        </h5>
                                                    </div>
                                                </div>

                                                <p class="ui-muted mt-4 text-sm leading-6">
                                                    {{ $analisis['curso_edad']['mensaje'] ?? 'La sugerencia aparece cuando el estudiante tiene fecha de nacimiento registrada.' }}
                                                </p>

                                                <div class="mt-5 space-y-3">
                                                    <div class="ui-card-soft p-4">
                                                        <p class="ui-muted text-xs">Edad registrada</p>
                                                        <p class="ui-title mt-1 font-black">
                                                            {{ ($estudianteSeleccionado['edad'] ?? null) ? ($estudianteSeleccionado['edad'].' años') : 'Sin edad' }}
                                                        </p>
                                                    </div>

                                                    <div class="ui-card-soft p-4">
                                                        <p class="ui-muted text-xs">Tipo sugerido</p>
                                                        <p class="ui-title mt-1 font-black">
                                                            {{ $situacionEstudiante['tipo_sugerido'] ?? 'REGULAR' }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="mt-5 flex flex-col gap-2">
                                                    @if (! empty($cursoSugerido['cod_cur']) || ! empty($analisis['curso_sugerido_disponible']['cod_cur']))
                                                        <button
                                                            type="button"
                                                            wire:click="aplicarCursoSugerido"
                                                            class="ui-btn-primary justify-center"
                                                        >
                                                            Aplicar curso sugerido
                                                        </button>

                                                        <button
                                                            type="button"
                                                            wire:click="ignorarCursoSugerido"
                                                            class="ui-btn-secondary justify-center"
                                                        >
                                                            Omitir sugerencia
                                                        </button>
                                                    @else
                                                        <button type="button" class="ui-btn-secondary justify-center" disabled>
                                                            Sin sugerencia aplicable
                                                        </button>
                                                    @endif
                                                </div>

                                                <div class="ui-alert-info mt-5">
                                                    La sugerencia por edad es referencial. La asignación final se define en el paso de asignación académica.
                                                </div>
                                            </aside>
                                        </section>
                                    @endif
                                </div>
                            @endif

                            {{-- PASO 2: ASIGNACIÓN ACADÉMICA --}}
                            @if ($pasoInscripcion === 2)
                                <div class="space-y-5">
                                    {{-- CABECERA DEL PASO --}}
                                    <section class="ui-panel">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="ui-badge-success">Paso 2</span>
                                                    <span class="ui-badge-muted">Asignación académica</span>
                                                    <span class="ui-badge-warning">Cupo y turno</span>

                                                    @if ($mostrarPanelEspecialidad)
                                                        <span class="ui-badge-warning">BTH opcional</span>
                                                    @else
                                                        <span class="ui-badge-muted">BTH no aplica</span>
                                                    @endif
                                                </div>

                                                <h4 class="ui-title mt-3 text-2xl font-black">
                                                    Designar curso, paralelo y turno
                                                </h4>

                                                <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">
                                                    Defina la gestión, curso, paralelo y turno correspondientes para la inscripción.
                                                </p>
                                            </div>

                                            <div class="ui-card-soft min-w-[260px] p-4">
                                                <p class="ui-muted text-xs">Estudiante</p>
                                                <p class="ui-title mt-1 font-black">
                                                    {{ $estudianteSeleccionado['nombre_completo'] ?? 'Sin estudiante seleccionado' }}
                                                </p>

                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    <span class="{{ $turnoMananaAplicado ? 'ui-badge-success' : 'ui-badge-warning' }}">
                                                        {{ $turnoMananaAplicado ? 'Turno Mañana aplicado' : 'Turno por revisar' }}
                                                    </span>

                                                    <span class="{{ $this->badgeRevision($estadoRevision) }}">
                                                        {{ $estadoRevision }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    {{-- PANEL SUPERIOR: TURNO PRINCIPAL (compacto) --}}
                                    <section class="grid gap-5 xl:grid-cols-1">
                                        <section class="ui-panel">
                                            <div class="flex items-start gap-3">
                                                <span class="{{ $turnoMananaAplicado ? 'ui-badge-success' : 'ui-badge-warning' }} p-2">
                                                    {!! $icon('clock', 'h-5 w-5') !!}
                                                </span>

                                                <div>
                                                    <p class="ui-kicker">Turno principal</p>
                                                    <h5 class="ui-title mt-1 text-xl font-black">
                                                        {{ $turnoManana['nombre'] ?? 'Mañana no detectado' }}
                                                    </h5>

                                                    <p class="ui-muted mt-2 text-sm leading-6">
                                                        @if (! empty($turnoManana['rango']))
                                                            {{ $turnoManana['rango'] }} · Turno recomendado para toda inscripción.
                                                        @else
                                                            Configura el turno Mañana en Gestión de Turnos para aplicar esta regla automáticamente.
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mt-5 space-y-3">
                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Estado del turno</p>
                                                    <p class="ui-title mt-1 font-black">
                                                        {{ $turnoMananaAplicado ? 'Correcto' : 'Requiere revisión' }}
                                                    </p>
                                                </div>

                                                @if (! $turnoMananaAplicado)
                                                    <button type="button" wire:click="forzarTurnoManana" class="ui-btn-primary w-full justify-center">
                                                        {!! $icon('clock', 'h-4 w-4') !!}
                                                        Aplicar Turno Mañana
                                                    </button>
                                                @endif
                                            </div>

                                            @if (! $turnoMananaAplicado)
                                                <div class="ui-alert-warning mt-4">
                                                    El turno seleccionado no coincide con la regla principal. Puede continuar, pero quedará observado.
                                                </div>
                                            @else
                                                <div class="ui-alert-success mt-4">
                                                    El estudiante será inscrito en el turno principal de la institución.
                                                </div>
                                            @endif
                                        </section>
                                    </section>

                                    {{-- FORMULARIO DE ASIGNACIÓN --}}
                                    <section class="ui-panel">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                            <div>
                                                <p class="ui-kicker">Datos académicos</p>
                                                <h5 class="ui-title mt-1 text-xl font-black">
                                                    Asignación para la gestión actual
                                                </h5>

                                                <p class="ui-muted mt-1 text-sm">
                                                    Selecciona la gestión, curso, paralelo y turno. El cupo se recalcula automáticamente.
                                                </p>
                                            </div>

                                            <div class="flex flex-wrap gap-2">
                                                <span class="{{ $this->badgeCupo($cupo['estado'] ?? 'DISPONIBLE') }}">
                                                    Cupo: {{ str_replace('_', ' ', $cupo['estado'] ?? 'DISPONIBLE') }}
                                                </span>

                                                <span class="{{ $this->badgeEstado($formInscripcion['est_ins'] ?? 'PENDIENTE') }}">
                                                    {{ $formInscripcion['est_ins'] ?? 'PENDIENTE' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                            {{-- GESTIÓN --}}
                                            <div>
                                                <label class="ui-label">Gestión académica</label>
                                                <select wire:model.live="formInscripcion.cod_gea" class="ui-select">
                                                    <option value="">Seleccionar gestión</option>
                                                    @foreach ($catalogos['gestiones'] ?? [] as $gestion)
                                                        <option value="{{ $gestion['cod_gea'] }}">
                                                            Gestión {{ $gestion['anio'] }} · {{ $gestion['estado'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('formInscripcion.cod_gea')
                                                    <p class="ui-error">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            {{-- FECHA --}}
                                            <div>
                                                <label class="ui-label">Fecha de inscripción</label>
                                                <input type="date" wire:model.live="formInscripcion.fei_ins" class="ui-input">
                                                @error('formInscripcion.fei_ins')
                                                    <p class="ui-error">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            {{-- TIPO --}}
                                            <div>
                                                <label class="ui-label">Tipo de inscripción</label>
                                                <select wire:model.live="formInscripcion.tip_ins" class="ui-select">
                                                    @foreach ($catalogos['tipos_inscripcion'] ?? [] as $tipo)
                                                        <option value="{{ $tipo }}">{{ ucfirst(strtolower($tipo)) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('formInscripcion.tip_ins')
                                                    <p class="ui-error">{{ $message }}</p>
                                                @enderror
                                                @if (! empty($situacionEstudiante['tipo_sugerido']) && ($formInscripcion['tip_ins'] ?? '') !== $situacionEstudiante['tipo_sugerido'])
                                                    <div class="mt-2">
                                                        <button type="button" wire:click="seleccionarTipoRapido('{{ $situacionEstudiante['tipo_sugerido'] }}')" class="ui-btn-secondary px-3 py-2 text-xs">
                                                            Aplicar sugerencia: {{ $situacionEstudiante['tipo_sugerido'] }}
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>

                                            {{-- CONDICIÓN --}}
                                            <div>
                                                <label class="ui-label">Condición</label>
                                                <select wire:model.live="formInscripcion.con_ins" class="ui-select">
                                                    @foreach ($catalogos['condiciones_inscripcion'] ?? [] as $condicion)
                                                        <option value="{{ $condicion }}">{{ ucfirst(strtolower($condicion)) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('formInscripcion.con_ins')
                                                    <p class="ui-error">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            {{-- CURSO --}}
                                            <div>
                                                <label class="ui-label">Curso</label>
                                                <select wire:model.live="formInscripcion.cod_cur" class="ui-select">
                                                    <option value="">Seleccionar curso</option>
                                                    @foreach ($catalogos['cursos'] ?? [] as $curso)
                                                        <option value="{{ $curso['cod_cur'] }}">
                                                            {{ $curso['nombre'] }}
                                                            @if ($curso['requiere_especialidad'] ?? false)
                                                                · BTH
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('formInscripcion.cod_cur')
                                                    <p class="ui-error">{{ $message }}</p>
                                                @enderror
                                                @php
                                                    $codCursoSugerido = $cursoSugerido['cod_cur'] ?? ($analisis['curso_sugerido_disponible']['cod_cur'] ?? null);
                                                    $nombreCursoSugerido = $cursoSugerido['nombre'] ?? ($analisis['curso_sugerido_disponible']['nombre'] ?? null);
                                                @endphp

                                                @if (! empty($codCursoSugerido) && ($formInscripcion['cod_cur'] ?? '') !== $codCursoSugerido && ! ($cursoSugeridoAplicado ?? false))
                                                    <div class="mt-2 flex items-center justify-between gap-3 ui-card-soft p-3">
                                                        <p class="ui-muted text-xs">
                                                            Curso sugerido: <span class="ui-title font-black">{{ $nombreCursoSugerido }}</span>
                                                        </p>
                                                        <button type="button" wire:click="aplicarCursoSugerido" class="ui-btn-secondary px-3 py-2 text-xs">
                                                            Aplicar
                                                        </button>
                                                    </div>
                                                @elseif (($cursoSugeridoAplicado ?? false) && ! empty($codCursoSugerido) && ($formInscripcion['cod_cur'] ?? '') === $codCursoSugerido)
                                                    <p class="ui-muted mt-2 text-xs">Curso sugerido aplicado.</p>
                                                @endif
                                            </div>

                                            {{-- PARALELO --}}
                                            <div>
                                                <label class="ui-label">Paralelo</label>
                                                <select wire:model.live="formInscripcion.cod_par" class="ui-select">
                                                    <option value="">Seleccionar paralelo</option>
                                                    @foreach ($catalogos['paralelos'] ?? [] as $paralelo)
                                                        <option value="{{ $paralelo['cod_par'] }}">
                                                            {{ $paralelo['nombre'] }}
                                                            @if (! empty($paralelo['capacidad']))
                                                                · Cap. {{ $paralelo['capacidad'] }}
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('formInscripcion.cod_par')
                                                    <p class="ui-error">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            {{-- TURNO --}}
                                            <div>
                                                <label class="ui-label">Turno</label>
                                                <select wire:model.live="formInscripcion.cod_tur" class="ui-select">
                                                    <option value="">Seleccionar turno</option>
                                                    @foreach ($catalogos['turnos'] ?? [] as $turno)
                                                        <option value="{{ $turno['cod_tur'] }}">
                                                            {{ $turno['nombre'] }} · {{ $turno['rango'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('formInscripcion.cod_tur')
                                                    <p class="ui-error">{{ $message }}</p>
                                                @enderror
                                                @if (! empty($turnoManana['cod_tur']) && ($formInscripcion['cod_tur'] ?? '') !== $turnoManana['cod_tur'] && ! ($turnoSugeridoAplicado ?? false))
                                                    <div class="mt-2 flex items-center justify-between gap-3 ui-card-soft p-3">
                                                        <p class="ui-muted text-xs">
                                                            Turno sugerido: <span class="ui-title font-black">{{ $turnoManana['nombre'] ?? 'Mañana' }}</span>
                                                        </p>
                                                        <button type="button" wire:click="forzarTurnoManana" class="ui-btn-secondary px-3 py-2 text-xs">
                                                            Aplicar
                                                        </button>
                                                    </div>
                                                @elseif (($turnoSugeridoAplicado ?? false) && ($formInscripcion['cod_tur'] ?? '') === ($turnoManana['cod_tur'] ?? ''))
                                                    <p class="ui-muted mt-2 text-xs">Turno sugerido aplicado.</p>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- PROCEDENCIA (controlada) --}}
                                        <div class="mt-5 grid gap-4 xl:grid-cols-[1fr_260px]">
                                            <div>
                                                <label class="ui-label">Procedencia</label>
                                                <select wire:model.live="formInscripcion.tip_pro_ins" class="ui-select">
                                                    @foreach ($catalogos['tipos_procedencia'] ?? [] as $tipo)
                                                        <option value="{{ $tipo }}">{{ str_replace('_', ' ', $tipo) }}</option>
                                                    @endforeach
                                                </select>
                                                @error('formInscripcion.tip_pro_ins')
                                                    <p class="ui-error">{{ $message }}</p>
                                                @enderror

                                                @php
                                                    $tipPro = $formInscripcion['tip_pro_ins'] ?? 'SIN_REGISTRO';
                                                    $requiereDetalle = in_array($tipPro, ['OTRA_UNIDAD', 'TRASLADO_DEPARTAMENTAL', 'TRASLADO_INTERDEPARTAMENTAL', 'EXTERIOR', 'OTRO'], true);
                                                @endphp

                                                @if ($requiereDetalle)
                                                    <div class="mt-2">
                                                        <label class="ui-label text-xs">Unidad educativa de procedencia</label>
                                                        <input
                                                            type="text"
                                                            wire:model.live.debounce.800ms="formInscripcion.pro_ins"
                                                            class="ui-input mt-1"
                                                            placeholder="Nombre de la unidad educativa"
                                                        >
                                                        @error('formInscripcion.pro_ins')
                                                            <p class="ui-error">{{ $message }}</p>
                                                        @enderror
                                                    </div>
                                                @endif
                                            </div>

                                            <div class="ui-card-soft flex items-center justify-between gap-3 p-4">
                                                <div>
                                                    <p class="ui-muted text-xs">Sobrecupo</p>
                                                    <p class="ui-title mt-1 font-black">
                                                        {{ ($formInscripcion['sob_aut_ins'] ?? false) ? 'Autorizado' : 'No autorizado' }}
                                                    </p>
                                                </div>

                                                <button type="button" wire:click="alternarSobrecupo" class="ui-btn-secondary px-3 py-2 text-xs">
                                                    {{ ($formInscripcion['sob_aut_ins'] ?? false) ? 'Quitar' : 'Autorizar' }}
                                                </button>
                                            </div>
                                        </div>
                                    </section>

                                    {{-- PANEL DE CUPO EN TIEMPO REAL --}}
                                    <section class="grid gap-5">
                                        <article class="ui-panel">
                                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                                <div>
                                                    <p class="ui-kicker">Cupo del paralelo</p>
                                                    <h5 class="ui-title mt-1 text-xl font-black">
                                                        {{ $cupo['disponibles'] ?? 0 }} cupos disponibles
                                                    </h5>

                                                    <p class="ui-muted mt-1 text-sm">
                                                        El cupo se calcula según gestión, curso, paralelo y turno seleccionados.
                                                    </p>
                                                </div>

                                                <span class="{{ $this->badgeCupo($cupo['estado'] ?? 'DISPONIBLE') }}">
                                                    {{ str_replace('_', ' ', $cupo['estado'] ?? 'DISPONIBLE') }}
                                                </span>
                                            </div>

                                            <div class="mt-5 grid gap-4 md:grid-cols-4">
                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Capacidad</p>
                                                    <p class="ui-title mt-1 text-2xl font-black">{{ $cupo['capacidad'] ?? 35 }}</p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Inscritos</p>
                                                    <p class="ui-title mt-1 text-2xl font-black">{{ $cupo['inscritos'] ?? 0 }}</p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Disponibles</p>
                                                    <p class="ui-title mt-1 text-2xl font-black">{{ $cupo['disponibles'] ?? 0 }}</p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Ocupación</p>
                                                    <p class="ui-title mt-1 text-2xl font-black">{{ $cupo['porcentaje'] ?? 0 }}%</p>
                                                </div>
                                            </div>

                                            <div class="mt-5 h-3 overflow-hidden rounded-full" style="background: var(--ui-surface-muted);">
                                                <div
                                                    class="savp-progress h-full rounded-full"
                                                    style="width: {{ min(100, max(0, $cupo['porcentaje'] ?? 0)) }}%;"
                                                ></div>
                                            </div>

                                            <div class="mt-5">
                                                @if (($cupo['estado'] ?? '') === 'LLENO' && ! ($formInscripcion['sob_aut_ins'] ?? false))
                                                    <div class="ui-alert-danger">
                                                        El paralelo seleccionado está lleno. Para continuar, se requiere sobrecupo autorizado o cambiar paralelo.
                                                    </div>
                                                @elseif (($cupo['estado'] ?? '') === 'LLENO' && ($formInscripcion['sob_aut_ins'] ?? false))
                                                    <div class="ui-alert-warning">
                                                        Se registrará con sobrecupo autorizado y quedará como condición especial.
                                                    </div>
                                                @elseif (($cupo['estado'] ?? '') === 'CASI_LLENO')
                                                    <div class="ui-alert-warning">
                                                        El paralelo está cerca de su capacidad máxima. Revisa disponibilidad antes de confirmar.
                                                    </div>
                                                @else
                                                    <div class="ui-alert-success">
                                                        Existe disponibilidad para continuar con la inscripción.
                                                    </div>
                                                @endif
                                            </div>
                                        </article>
                                    </section>

                                    {{-- ESPECIALIDAD TÉCNICA BTH --}}
                                    @if ($mostrarPanelEspecialidad)
                                        <section class="ui-panel savp-specialty-panel">
                                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                                <div class="flex gap-4">
                                                    <span class="savp-avatar-md ui-badge-warning">
                                                        {!! $icon('star', 'h-6 w-6') !!}
                                                    </span>

                                                    <div>
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <span class="ui-badge-warning">BTH</span>
                                                            <span class="ui-badge-muted">Opcional</span>
                                                            <span class="{{ $this->badgeEspecialidad($formInscripcion['est_esp_tec_ins'] ?? 'PENDIENTE') }}">
                                                                {{ str_replace('_', ' ', $formInscripcion['est_esp_tec_ins'] ?? 'PENDIENTE') }}
                                                            </span>
                                                        </div>

                                                        <h5 class="ui-title mt-3 text-xl font-black">
                                                            Especialidad técnica
                                                        </h5>

                                                        <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">
                                                            Desde 4to hacia arriba, el estudiante puede ser asociado a una especialidad técnica del Bachillerato Técnico Humanístico.
                                                            Si todavía no eligió, la inscripción puede continuar con especialidad pendiente.
                                                        </p>
                                                    </div>
                                                </div>

                                                @if (($formInscripcion['est_esp_tec_ins'] ?? '') !== 'PENDIENTE' && ! ($especialidadPendienteAplicada ?? false))
                                                    <button type="button" wire:click="dejarEspecialidadPendiente" class="ui-btn-secondary">
                                                        Dejar pendiente
                                                    </button>
                                                @endif
                                            </div>

                                            @if (($formInscripcion['est_esp_tec_ins'] ?? '') === 'PENDIENTE')
                                                <div class="ui-alert-warning mt-5">
                                                    Especialidad pendiente de elección. Podrá completarse posteriormente.
                                                </div>
                                            @endif

                                            <div class="mt-6 grid gap-4 md:grid-cols-2">
                                                <div>
                                                    <label class="ui-label">Especialidad técnica</label>
                                                    <select wire:model.live="formInscripcion.cod_esp_tec" class="ui-select">
                                                        <option value="">Pendiente de elección</option>
                                                        @foreach ($catalogos['especialidades_tecnicas'] ?? [] as $especialidad)
                                                            <option value="{{ $especialidad['cod_esp_tec'] }}">{{ $especialidad['nombre'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('formInscripcion.cod_esp_tec')
                                                        <p class="ui-error">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                <div>
                                                    <label class="ui-label">Estado de especialidad</label>
                                                    <select wire:model.live="formInscripcion.est_esp_tec_ins" class="ui-select">
                                                        <option value="PENDIENTE">Pendiente</option>
                                                        <option value="ASIGNADA">Asignada</option>
                                                        <option value="OBSERVADA">Observada</option>
                                                    </select>
                                                    @error('formInscripcion.est_esp_tec_ins')
                                                        <p class="ui-error">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="mt-5">
                                                <label class="ui-label">Observación de especialidad</label>
                                                <textarea
                                                    wire:model.live.debounce.900ms="formInscripcion.obs_esp_tec_ins"
                                                    rows="3"
                                                    class="ui-textarea"
                                                    placeholder="Ejemplo: el estudiante elegirá especialidad después de orientación técnica."
                                                ></textarea>
                                                @error('formInscripcion.obs_esp_tec_ins')
                                                    <p class="ui-error">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            @if (($formInscripcion['est_esp_tec_ins'] ?? '') === 'PENDIENTE')
                                                <div class="ui-alert-warning mt-5">
                                                    La especialidad técnica quedó pendiente. Esto no bloquea la inscripción, pero debe completarse posteriormente.
                                                </div>
                                            @elseif (($formInscripcion['est_esp_tec_ins'] ?? '') === 'ASIGNADA')
                                                <div class="ui-alert-success mt-5">
                                                    La especialidad técnica fue asignada correctamente para el estudiante.
                                                </div>
                                            @elseif (($formInscripcion['est_esp_tec_ins'] ?? '') === 'OBSERVADA')
                                                <div class="ui-alert-warning mt-5">
                                                    La especialidad técnica está observada. Registra una observación clara para seguimiento.
                                                </div>
                                            @endif
                                        </section>
                                    @else
                                        <section class="ui-panel">
                                            <div class="flex items-start gap-4">
                                                <span class="savp-avatar-md ui-badge-muted">
                                                    {!! $icon('star', 'h-6 w-6') !!}
                                                </span>

                                                <div>
                                                    <p class="ui-kicker">Formación técnica BTH</p>
                                                    <h5 class="ui-title mt-1 text-xl font-black">
                                                        Especialidad no aplicable
                                                    </h5>

                                                    <p class="ui-muted mt-2 text-sm leading-6">
                                                        La especialidad técnica se habilita para 4to, 5to y 6to de secundaria.
                                                        Selecciona un curso superior si corresponde asignar especialidad.
                                                    </p>
                                                </div>
                                            </div>
                                        </section>
                                    @endif

                                    {{-- OBSERVACIÓN INSTITUCIONAL --}}
                                    <section class="ui-panel">
                                        <p class="ui-kicker">Observación institucional</p>
                                        <h5 class="ui-title mt-1 text-xl font-black">
                                            Registro administrativo complementario
                                        </h5>

                                        <p class="ui-muted mt-1 text-sm">
                                            Utiliza este campo para dejar respaldo de traslados, casos especiales, cambios de turno, sobrecupo o documentación pendiente.
                                        </p>

                                        <div class="mt-5">
                                            <textarea
                                                wire:model.live.debounce.900ms="formInscripcion.obs_ins"
                                                rows="4"
                                                class="ui-textarea"
                                                placeholder="Ejemplo: inscripción regular con especialidad técnica pendiente de elección."
                                            ></textarea>
                                            @error('formInscripcion.obs_ins')
                                                <p class="ui-error">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </section>
                                </div>
                            @endif

                            {{-- PASO 3: DOCUMENTOS --}}
                            @if ($pasoInscripcion === 3)
                                @php
                                    $etiquetasEstadoDoc = [
                                        'PENDIENTE'  => 'Pendiente de regularización',
                                        'PRESENTADO' => 'Archivo recibido',
                                        'VALIDADO'   => 'Validado',
                                        'OBSERVADO'  => 'Observado',
                                        'NO_APLICA'  => 'No aplica',
                                    ];
                                @endphp
                                <div class="space-y-4">

                                    {{-- Cabecera + selector compacto --}}
                                    <section class="ui-panel">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="ui-badge-success">Paso 3</span>
                                                    <span class="ui-badge-muted">Revisión documental</span>
                                                </div>
                                                <h4 class="ui-title mt-2 text-xl font-black">Lista documental</h4>
                                            </div>

                                            {{-- Selector de catálogo oculto por defecto (Alpine toggle) --}}
                                            <div x-data="{ abierto: false }" class="flex flex-wrap items-center gap-2">
                                                <button
                                                    type="button"
                                                    x-show="!abierto"
                                                    @click="abierto = true"
                                                    class="ui-btn-secondary text-sm"
                                                >
                                                    {!! $icon('plus', 'h-4 w-4') !!} Agregar documento
                                                </button>

                                                <div x-show="abierto" x-transition class="flex flex-wrap items-center gap-2">
                                                    <select wire:model.live="documentoCatalogoSeleccionado" class="ui-select text-sm">
                                                        <option value="">Selecciona documento...</option>
                                                        @foreach ($documentosDisponibles as $docDisponible)
                                                            <option value="{{ $docDisponible['clave_doc'] }}">{{ $docDisponible['nom_die'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button
                                                        type="button"
                                                        wire:click="agregarDocumentoDesdeCatalogo"
                                                        class="ui-btn-primary text-sm"
                                                    >Agregar</button>
                                                    <button
                                                        type="button"
                                                        @click="abierto = false"
                                                        class="ui-btn-secondary text-sm"
                                                    >Cancelar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    {{-- Grid de fichas documentales --}}
                                    <div class="grid gap-4 xl:grid-cols-2">
                                        @forelse ($documentos as $index => $documento)
                                             @php
                                                 $estadoDoc      = $documento['est_die'] ?? 'PENDIENTE';
                                                 $tieneArchivo   = ! empty($documento['rut_die']);
                                                $editableFecha  = (bool) ($documento['fecha_limite_editable'] ?? false);
                                                $tieneSugerencia = ! empty($documento['obs_sugerida'] ?? '');
                                                $mostrarObsFecha = in_array($estadoDoc, ['OBSERVADO', 'PENDIENTE']);
                                                $mostrarUpload   = ! in_array($estadoDoc, ['NO_APLICA', 'VALIDADO']);
                                                 $etiquetaEst     = $etiquetasEstadoDoc[$estadoDoc] ?? ucfirst(strtolower($estadoDoc));
                                                 if ($estadoDoc === 'PRESENTADO' && ! $tieneArchivo) {
                                                     $etiquetaEst = 'Esperando archivo PDF';
                                                 }
                                             @endphp

                                            <article class="ui-card-soft flex flex-col gap-3 p-4">

                                                {{-- 1. Encabezado: nombre, tipo, obligatorio, estado --}}
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <p class="ui-title text-sm font-black leading-snug">
                                                            {{ $documento['nom_die'] ?? 'Documento' }}
                                                        </p>
                                                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                                            <span class="ui-badge-muted text-xs">{{ $documento['tip_die'] ?? 'GENERAL' }}</span>
                                                            @if ($documento['obligatorio'] ?? false)
                                                                <span class="ui-badge-warning text-xs">Obligatorio</span>
                                                            @else
                                                                <span class="ui-badge-muted text-xs">Opcional</span>
                                                            @endif
                                                            <span class="{{ $this->badgeDocumento($estadoDoc) }} text-xs">{{ $etiquetaEst }}</span>
                                                        </div>
                                                    </div>
                                                    <button
                                                        type="button"
                                                        wire:click="quitarDocumento({{ $index }})"
                                                        class="ui-icon-btn shrink-0"
                                                        title="Quitar documento"
                                                    >{!! $icon('trash', 'h-4 w-4') !!}</button>
                                                </div>

                                                {{-- 2. Archivo PDF --}}
                                                @if ($estadoDoc === 'VALIDADO')
                                                    @if ($tieneArchivo)
                                                        <div class="flex items-center gap-1.5 text-xs">
                                                            <span class="ui-badge-success p-1">{!! $icon('check', 'h-3 w-3') !!}</span>
                                                            <span class="ui-muted">Archivo validado</span>
                                                        </div>
                                                    @endif
                                                @elseif ($mostrarUpload)
                                                    <div>
                                                        <label class="ui-label text-xs">Archivo PDF</label>
                                                        <input
                                                            type="file"
                                                            wire:model="archivosDocumentos.{{ $index }}"
                                                            accept="application/pdf,.pdf"
                                                            class="ui-input mt-1 text-xs"
                                                        >
                                                        @error("archivosDocumentos.$index")
                                                            <p class="ui-error text-xs">{{ $message }}</p>
                                                        @enderror
                                                        @if ($tieneArchivo)
                                                            <p class="ui-muted mt-1 text-xs">Archivo cargado · Selecciona otro para reemplazar</p>
                                                        @endif
                                                    </div>
                                                @endif

                                                {{-- 3. Acciones contextuales --}}
                                                @if ($estadoDoc === 'PENDIENTE')
                                                    <div class="flex flex-wrap gap-1.5">
                                                        <button type="button" wire:click="documentoNoAplica({{ $index }})" class="ui-btn-secondary px-2.5 py-1.5 text-xs">No aplica</button>
                                                    </div>
                                                @elseif ($estadoDoc === 'PRESENTADO')
                                                    <div class="flex flex-wrap gap-1.5">
                                                        @if ($tieneArchivo)
                                                            <button type="button" wire:click="documentoValidado({{ $index }})" class="ui-btn-primary px-2.5 py-1.5 text-xs">Todo en orden</button>
                                                        @endif
                                                        <button type="button" wire:click="observarDocumento({{ $index }})" class="ui-btn-secondary px-2.5 py-1.5 text-xs">Observar</button>
                                                        <button type="button" wire:click="dejarDocumentoPendiente({{ $index }})" class="ui-btn-secondary px-2.5 py-1.5 text-xs">Dejar pendiente</button>
                                                        <button type="button" wire:click="documentoNoAplica({{ $index }})" class="ui-btn-secondary px-2.5 py-1.5 text-xs">No aplica</button>
                                                    </div>
                                                @elseif ($estadoDoc === 'VALIDADO')
                                                    <div class="flex flex-wrap gap-1.5">
                                                        <button type="button" wire:click="observarDocumento({{ $index }})" class="ui-btn-secondary px-2.5 py-1.5 text-xs">Corregir</button>
                                                        <button type="button" wire:click="documentoNoAplica({{ $index }})" class="ui-btn-secondary px-2.5 py-1.5 text-xs">No aplica</button>
                                                    </div>
                                                @elseif ($estadoDoc === 'OBSERVADO')
                                                    <div class="flex flex-wrap gap-1.5">
                                                        @if ($tieneArchivo)
                                                            <button type="button" wire:click="documentoValidado({{ $index }})" class="ui-btn-primary px-2.5 py-1.5 text-xs">Todo en orden</button>
                                                        @endif
                                                        <button type="button" wire:click="dejarDocumentoPendiente({{ $index }})" class="ui-btn-secondary px-2.5 py-1.5 text-xs">Dejar pendiente</button>
                                                        <button type="button" wire:click="documentoNoAplica({{ $index }})" class="ui-btn-secondary px-2.5 py-1.5 text-xs">No aplica</button>
                                                    </div>
                                                @elseif ($estadoDoc === 'NO_APLICA')
                                                    <div class="flex flex-wrap gap-1.5">
                                                        <button type="button" wire:click="dejarDocumentoPendiente({{ $index }})" class="ui-btn-secondary px-2.5 py-1.5 text-xs">Reactivar</button>
                                                    </div>
                                                @endif

                                                {{-- 4. Observación documental: solo si OBSERVADO o PENDIENTE --}}
                                                @if ($mostrarObsFecha)
                                                    <div>
                                                        <label class="ui-label text-xs">Observación documental</label>
                                                        <textarea
                                                            data-doc-index="{{ $index }}"
                                                            data-suggestion="{{ $documento['obs_sugerida'] ?? '' }}"
                                                            x-data="window.documentoAutocomplete.component()"
                                                            x-on:keydown.arrow-right.prevent="acceptNextWord($event)"
                                                            x-on:keydown.tab.prevent="acceptAll($event)"
                                                            wire:model.live.debounce.700ms="documentos.{{ $index }}.obs_die"
                                                            rows="2"
                                                            class="ui-textarea mt-1 text-sm"
                                                            placeholder="Describe la observación..."
                                                        ></textarea>
                                                        @if ($tieneSugerencia)
                                                            <p class="ui-muted mt-0.5 text-xs opacity-75">
                                                                Sugerencia: {{ \Illuminate\Support\Str::limit($documento['obs_sugerida'] ?? '', 70) }} · → completar palabra · TAB aplicar
                                                            </p>
                                                        @endif
                                                    </div>

                                                    {{-- 5. Fecha límite: solo si OBSERVADO o PENDIENTE --}}
                                                    <div>
                                                        <div class="flex items-center justify-between gap-2">
                                                            <label class="ui-label text-xs">Fecha límite</label>
                                                            @if (! $editableFecha)
                                                                <button
                                                                    type="button"
                                                                    wire:click="abrirConfirmacionModificarFechaDocumento({{ $index }})"
                                                                    class="ui-muted text-xs underline"
                                                                >Modificar</button>
                                                            @endif
                                                        </div>
                                                        <input
                                                            type="date"
                                                            wire:model.live="documentos.{{ $index }}.fec_lim_die"
                                                            class="ui-input mt-1 text-sm"
                                                            @disabled(! $editableFecha)
                                                        >
                                                    </div>
                                                @endif

                                            </article>
                                        @empty
                                            <div class="col-span-2 ui-card-soft p-8 text-center">
                                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl ui-badge-muted">
                                                    {!! $icon('file', 'h-7 w-7') !!}
                                                </div>
                                                <h5 class="ui-title mt-4 font-black">Sin documentos en lista</h5>
                                                <p class="ui-muted mt-2 text-sm">
                                                    Usa "Agregar documento" para añadir fichas documentales al expediente.
                                                </p>
                                            </div>
                                        @endforelse
                                    </div>

                                </div>
                            @endif

                            @if ($modalConfirmarFechaDocumento)
                                <div class="savp-nested-modal-wrap fixed inset-0 z-[9999] flex items-center justify-center px-4">
                                    <div class="savp-nested-modal-backdrop absolute inset-0 bg-slate-950/70"></div>
                                    <div class="savp-nested-modal-card relative z-[10000] w-full max-w-2xl rounded-xl border shadow-2xl" style="background: var(--ui-bg); border-color: var(--ui-border);">
                                        <div class="ui-modal-header">
                                            <h4 class="ui-title text-lg font-black">Modificar fecha límite</h4>
                                        </div>
                                        <div class="p-5 space-y-4">
                                            <p class="ui-muted text-sm">
                                                La fecha límite fue definida según normas educativas y reglas de regularización documental aplicadas por el sistema.
                                            </p>
                                            <p class="ui-muted text-sm">
                                                Si modifica esta fecha, el cambio quedará registrado en el sistema para fines de trazabilidad.
                                            </p>
                                            <p class="ui-muted text-sm">
                                                ¿Desea habilitar la edición de la fecha límite?
                                            </p>
                                            <div>
                                                <label class="ui-label">Motivo</label>
                                                <textarea wire:model.live.debounce.500ms="motivoModificarFechaDocumento" rows="3" class="ui-textarea" placeholder="Motivo obligatorio"></textarea>
                                            </div>
                                        </div>
                                        <div class="ui-modal-footer flex justify-end gap-2">
                                            <button type="button" wire:click="cancelarModificarFechaDocumento" class="ui-btn-secondary">Cancelar</button>
                                            <button type="button" wire:click="aceptarModificarFechaDocumento" class="ui-btn-primary">Aceptar</button>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- PASO 4: REVISIÓN Y CONFIRMACIÓN --}}
                            @if ($pasoInscripcion === 4)
                                <div class="space-y-5">
                                    {{-- CABECERA --}}
                                    <section class="ui-panel savp-confirm-panel">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="ui-badge-success">Paso 4</span>
                                                    <span class="ui-badge-muted">Confirmación final</span>

                                                    <span class="{{ $puedeConfirmar ? 'ui-badge-success' : 'ui-badge-danger' }}">
                                                        {{ $puedeConfirmar ? 'Puede guardarse' : 'Requiere corrección' }}
                                                    </span>
                                                </div>

                                                <h4 class="ui-title mt-3 text-2xl font-black">
                                                    Confirmar inscripción
                                                </h4>

                                                <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">
                                                    Verifique el resumen de los datos que se registrarán para la inscripción del estudiante.
                                                </p>
                                            </div>

                                            <div class="ui-card-soft min-w-[280px] p-4">
                                                <p class="ui-muted text-xs">Acción recomendada</p>
                                                <p class="ui-title mt-1 text-lg font-black">
                                                    {{ $estadoConfirmacion }}
                                                </p>

                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    <span class="{{ $this->badgeEstado($confirmacionFinal['estado_final'] ?? ($formInscripcion['est_ins'] ?? 'PENDIENTE')) }}">
                                                        {{ $confirmacionFinal['estado_final'] ?? ($formInscripcion['est_ins'] ?? 'PENDIENTE') }}
                                                    </span>

                                                    <span class="ui-badge-muted">
                                                        {{ $confirmacionFinal['condicion_final'] ?? ($formInscripcion['con_ins'] ?? 'NORMAL') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    {{-- TARJETA DE CONFIRMACIÓN --}}
                                    <section class="grid gap-5 xl:grid-cols-[1fr_390px]">
                                        <article class="ui-panel">
                                            <p class="ui-kicker">Resumen final</p>
                                            <h5 class="ui-title mt-1 text-xl font-black">
                                                Datos que serán registrados
                                            </h5>

                                            <div class="mt-6 space-y-4">
                                                {{-- ESTUDIANTE --}}
                                                <div class="savp-final-row">
                                                    <div class="flex items-start gap-3">
                                                        <span class="ui-badge-success p-2">
                                                            {!! $icon('user', 'h-4 w-4') !!}
                                                        </span>

                                                        <div>
                                                            <p class="ui-muted text-xs">Estudiante</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ $estudianteSeleccionado['nombre_completo'] ?? 'Sin estudiante seleccionado' }}
                                                            </p>
                                                            <p class="ui-muted mt-1 text-xs">
                                                                CI: {{ $estudianteSeleccionado['ci_completo'] ?? 'Sin CI' }}
                                                                · RUDE: {{ $estudianteSeleccionado['rud'] ?? 'Sin RUDE' }}
                                                                · Edad: {{ ($estudianteSeleccionado['edad'] ?? null) ? ($estudianteSeleccionado['edad'].' años') : 'Sin edad' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- ASIGNACIÓN --}}
                                                <div class="savp-final-row">
                                                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                                        <div>
                                                            <p class="ui-muted text-xs">Gestión</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ $gestionTrabajo['anio'] ?? 'Sin gestión' }}
                                                            </p>
                                                        </div>

                                                        <div>
                                                            <p class="ui-muted text-xs">Curso</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ collect($catalogos['cursos'] ?? [])->firstWhere('cod_cur', $formInscripcion['cod_cur'] ?? '')['nombre'] ?? 'No seleccionado' }}
                                                            </p>
                                                        </div>

                                                        <div>
                                                            <p class="ui-muted text-xs">Paralelo</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ collect($catalogos['paralelos'] ?? [])->firstWhere('cod_par', $formInscripcion['cod_par'] ?? '')['nombre'] ?? 'No seleccionado' }}
                                                            </p>
                                                        </div>

                                                        <div>
                                                            <p class="ui-muted text-xs">Turno</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ collect($catalogos['turnos'] ?? [])->firstWhere('cod_tur', $formInscripcion['cod_tur'] ?? '')['nombre'] ?? 'No seleccionado' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- ESTADO Y CONDICIÓN --}}
                                                <div class="savp-final-row">
                                                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                                        <div>
                                                            <p class="ui-muted text-xs">Tipo</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ ucfirst(strtolower($formInscripcion['tip_ins'] ?? 'REGULAR')) }}
                                                            </p>
                                                        </div>

                                                        <div>
                                                            <p class="ui-muted text-xs">Estado final</p>
                                                            <span class="{{ $this->badgeEstado($formInscripcion['est_ins'] ?? 'PENDIENTE') }}">
                                                                {{ $formInscripcion['est_ins'] ?? 'PENDIENTE' }}
                                                            </span>
                                                        </div>

                                                        <div>
                                                            <p class="ui-muted text-xs">Condición</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ $formInscripcion['con_ins'] ?? 'NORMAL' }}
                                                            </p>
                                                        </div>

                                                        <div>
                                                            <p class="ui-muted text-xs">Fecha</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ $formInscripcion['fei_ins'] ?? now()->toDateString() }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- ESPECIALIDAD --}}
                                                <div class="savp-final-row">
                                                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                                        <div class="flex items-start gap-3">
                                                            <span class="{{ $this->badgeEspecialidad($formInscripcion['est_esp_tec_ins'] ?? 'NO_APLICA') }} p-2">
                                                                {!! $icon('star', 'h-4 w-4') !!}
                                                            </span>

                                                            <div>
                                                                <p class="ui-muted text-xs">Especialidad técnica BTH</p>
                                                                <p class="ui-title mt-1 font-black">
                                                                    @php
                                                                        $espFinal = collect($catalogos['especialidades_tecnicas'] ?? [])->firstWhere('cod_esp_tec', $formInscripcion['cod_esp_tec'] ?? '');
                                                                    @endphp

                                                                    {{ $espFinal['nombre'] ?? ($formInscripcion['est_esp_tec_ins'] ?? 'NO_APLICA') }}
                                                                </p>

                                                                <p class="ui-muted mt-1 text-xs">
                                                                    Estado: {{ $formInscripcion['est_esp_tec_ins'] ?? 'NO_APLICA' }}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <span class="{{ $this->badgeEspecialidad($formInscripcion['est_esp_tec_ins'] ?? 'NO_APLICA') }}">
                                                            {{ $formInscripcion['est_esp_tec_ins'] ?? 'NO_APLICA' }}
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- DOCUMENTOS --}}
                                                <div class="savp-final-row">
                                                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                                        <div>
                                                            <p class="ui-muted text-xs">Documentación</p>
                                                            <p class="ui-title mt-1 font-black">{{ count($documentos) }} documento(s) en lista documental</p>

                                                            <p class="ui-muted mt-1 text-xs">
                                                                {{ $analisis['documentos']['presentados'] ?? 0 }} presentados ·
                                                                {{ $analisis['documentos']['pendientes'] ?? 0 }} pendientes ·
                                                                {{ $analisis['documentos']['observados'] ?? 0 }} observados
                                                            </p>
                                                        </div>

                                                        @if (($analisis['documentos']['pendientes'] ?? 0) > 0 || ($analisis['documentos']['observados'] ?? 0) > 0)
                                                            <span class="ui-badge-warning">Seguimiento documental</span>
                                                        @elseif (count($documentos) > 0)
                                                            <span class="ui-badge-success">Documentación completa</span>
                                                        @else
                                                            <span class="ui-badge-muted">Sin lista documental</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </article>

                                        {{-- PANEL DE DECISIÓN --}}
                                        <aside class="space-y-5">
                                            <article class="ui-panel">
                                                <p class="ui-kicker">Decisión final</p>
                                                <h5 class="ui-title mt-1 text-xl font-black">
                                                    {{ $estadoConfirmacion }}
                                                </h5>

                                                <p class="ui-muted mt-2 text-sm leading-6">
                                                    {{ $confirmacionFinal['mensaje'] ?? 'La inscripción se guardará según el estado y condición sugeridos.' }}
                                                </p>

                                                <div class="mt-5 space-y-3">
                                                    <div class="ui-card-soft p-4">
                                                        <p class="ui-muted text-xs">Puede confirmar</p>
                                                        <p class="ui-title mt-1 font-black">
                                                            {{ $puedeConfirmar ? 'Sí' : 'No' }}
                                                        </p>
                                                    </div>

                                                    <div class="ui-card-soft p-4">
                                                        <p class="ui-muted text-xs">Bloqueos</p>
                                                        <p class="ui-title mt-1 font-black">
                                                            {{ count($analisis['bloqueos'] ?? []) }}
                                                        </p>
                                                    </div>

                                                    <div class="ui-card-soft p-4">
                                                        <p class="ui-muted text-xs">Observaciones</p>
                                                        <p class="ui-title mt-1 font-black">
                                                            {{ count($analisis['advertencias'] ?? []) }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </article>

                                            <article class="ui-panel">
                                                <p class="ui-kicker">Aceptación</p>

                                                <!-- Checkbox de Aceptación de Condiciones -->
                                                <div class="mt-4 mb-4 flex items-start gap-3">
                                                    <input
                                                        type="checkbox"
                                                        id="condicionesAceptadas"
                                                        wire:model.live="condicionesAceptadas"
                                                        class="ui-checkbox mt-1"
                                                    >
                                                    <label for="condicionesAceptadas" class="text-xs text-gray-600 dark:text-gray-400 cursor-pointer select-none leading-5">
                                                        Acepto los términos y condiciones de inscripción del estudiante de acuerdo a las normas vigentes de la institución.
                                                    </label>
                                                </div>

                                                <div class="mt-4">
                                                    <div class="ui-alert-warning">
                                                        Las acciones finales se encuentran en el pie del formulario.
                                                    </div>
                                                </div>

                                                @if (! $puedeConfirmar)
                                                    <div class="ui-alert-danger mt-4">
                                                        Existen bloqueos. Vuelve a revisión para corregirlos antes de confirmar.
                                                    </div>
                                                @elseif (($formInscripcion['est_ins'] ?? '') !== 'INSCRITO')
                                                    <div class="ui-alert-warning mt-4">
                                                        La inscripción puede guardarse con seguimiento porque presenta observaciones.
                                                    </div>
                                                @else
                                                    <div class="ui-alert-success mt-4">
                                                        La inscripción puede confirmarse correctamente.
                                                    </div>
                                                @endif
                                            </article>
                                        </aside>
                                    </section>

                                    {{-- RESPALDO INSTITUCIONAL --}}
                                    <section class="ui-panel">
                                        <p class="ui-kicker">Observación final</p>
                                        <h5 class="ui-title mt-1 text-xl font-black">
                                            Nota que acompañará la inscripción
                                        </h5>

                                        <p class="ui-muted mt-1 text-sm">
                                            Esta observación ayuda a comprender la decisión registrada, especialmente si se guarda como observada,
                                            condicional, provisional o con especialidad pendiente.
                                        </p>

                                        <div class="mt-5">
                                            <textarea
                                                wire:model.live.debounce.900ms="formInscripcion.mot_obs_ins"
                                                rows="4"
                                                class="ui-textarea"
                                                placeholder="Ejemplo: inscripción confirmada con especialidad técnica pendiente de elección."
                                            ></textarea>

                                            @error('formInscripcion.mot_obs_ins')
                                                <p class="ui-error">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </section>
                                </div>
                            @endif
                        </section>
                    </div>
                </div>

                <div class="ui-modal-footer sticky bottom-0 z-10 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="ui-muted text-xs">
                        La inscripción conserva trazabilidad y no elimina registros físicos.
                    </p>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="cerrarModalInscripcion" class="ui-btn-secondary">
                            Cancelar
                        </button>

                        @if ($pasoInscripcion > 1)
                            <button type="button" wire:click="pasoAnterior" class="ui-btn-secondary">
                                {!! $icon('arrow-left', 'h-4 w-4') !!}
                                Anterior
                            </button>
                        @endif

                        @if ($pasoInscripcion < 4)
                            <button type="button" wire:click="siguientePaso" class="ui-btn-primary">
                                Siguiente
                                {!! $icon('arrow-right', 'h-4 w-4') !!}
                            </button>
                        @else
                            <button type="button" wire:click="guardarPendiente" class="ui-btn-secondary">
                                Guardar pendiente
                            </button>

                            <button
                                type="button"
                                wire:click="confirmarInscripcion"
                                @disabled(! $puedeConfirmar || ! $condicionesAceptadas)
                                class="{{ ($puedeConfirmar && $condicionesAceptadas) ? 'ui-btn-primary' : 'ui-btn-secondary' }}"
                            >
                                {{ $estadoConfirmacion }}
                            </button>

                            <button
                                type="button"
                                wire:click="confirmarEImprimir"
                                @disabled(! $puedeConfirmar || ! $condicionesAceptadas)
                                class="{{ ($puedeConfirmar && $condicionesAceptadas) ? 'ui-btn-primary' : 'ui-btn-secondary' }}"
                            >
                                Confirmar e imprimir
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODALES SECUNDARIOS --}}
    @if ($modalChecklist)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop savp-modal-backdrop" wire:click="cerrarChecklist"></div>
            <div class="ui-modal savp-modal-shell w-full max-w-3xl overflow-hidden">
                <div class="ui-modal-header flex items-start justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Lista documental</p>
                        <h3 class="ui-title text-xl font-black">Actualizar documentos sin borrar información</h3>
                        <p class="ui-muted mt-1 text-sm">Puedes agregar recomendados sin reemplazar lo avanzado.</p>
                    </div>
                    <button type="button" wire:click="cerrarChecklist" class="ui-icon-btn">{!! $icon('x') !!}</button>
                </div>

                <div class="p-5 grid gap-4 md:grid-cols-2">
                    <button type="button" wire:click="aplicarChecklistAgregarFaltantes" class="ui-card-soft savp-student-card p-5 text-left">
                        <span class="ui-badge-success p-3">{!! $icon('plus', 'h-5 w-5') !!}</span>
                        <h4 class="ui-title mt-4 font-black">Agregar faltantes</h4>
                        <p class="ui-muted mt-2 text-sm">Conserva documentos actuales y agrega los recomendados que falten.</p>
                    </button>

                    <button type="button" wire:click="aplicarChecklistReemplazar" class="ui-card-soft savp-student-card p-5 text-left">
                        <span class="ui-badge-warning p-3">{!! $icon('warning', 'h-5 w-5') !!}</span>
                        <h4 class="ui-title mt-4 font-black">Reemplazar lista</h4>
                        <p class="ui-muted mt-2 text-sm">Usa la lista recomendada desde cero. Requiere cuidado administrativo.</p>
                    </button>
                </div>

                <div class="ui-modal-footer flex justify-end">
                    <button type="button" wire:click="cerrarChecklist" class="ui-btn-secondary">Cancelar</button>
                </div>
            </div>
        </div>
    @endif

    @if ($modalDetalle)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop savp-modal-backdrop" wire:click="cerrarDetalle"></div>
            <div class="ui-modal savp-modal-shell w-full max-w-5xl overflow-hidden">
                <div class="ui-modal-header flex items-start justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Detalle institucional</p>
                        <h3 class="ui-title text-xl font-black">{{ $detalleInscripcion['estudiante'] ?? 'Inscripción seleccionada' }}</h3>
                        <p class="ui-muted mt-1 text-sm">Consulta académica, documental y de trazabilidad.</p>
                    </div>
                    <button type="button" wire:click="cerrarDetalle" class="ui-icon-btn">{!! $icon('x') !!}</button>
                </div>

                <div class="p-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    <div class="ui-card-soft p-4">
                        <p class="ui-muted text-xs">CI / RUDE</p>
                        <p class="ui-title mt-1 font-black">{{ $detalleInscripcion['ci'] ?? 'Sin CI' }} · {{ $detalleInscripcion['rude'] ?? 'Sin RUDE' }}</p>
                    </div>

                    <div class="ui-card-soft p-4">
                        <p class="ui-muted text-xs">Gestión</p>
                        <p class="ui-title mt-1 font-black">{{ $detalleInscripcion['gestion'] ?? '—' }}</p>
                    </div>

                    <div class="ui-card-soft p-4">
                        <p class="ui-muted text-xs">Asignación</p>
                        <p class="ui-title mt-1 font-black">
                            {{ $detalleInscripcion['curso'] ?? 'Curso' }} · {{ $detalleInscripcion['paralelo'] ?? 'Paralelo' }} · {{ $detalleInscripcion['turno'] ?? 'Turno' }}
                        </p>
                    </div>

                    <div class="ui-card-soft p-4">
                        <p class="ui-muted text-xs">Tipo</p>
                        <p class="ui-title mt-1 font-black">{{ $detalleInscripcion['tipo'] ?? 'REGULAR' }}</p>
                    </div>

                    <div class="ui-card-soft p-4">
                        <p class="ui-muted text-xs">Estado</p>
                        <span class="{{ $this->badgeEstado($detalleInscripcion['estado'] ?? 'PENDIENTE') }}">
                            {{ $detalleInscripcion['estado'] ?? 'PENDIENTE' }}
                        </span>
                    </div>

                    <div class="ui-card-soft p-4">
                        <p class="ui-muted text-xs">Documentos</p>
                        <p class="ui-title mt-1 font-black">
                            {{ $detalleInscripcion['documentos_pendientes'] ?? 0 }} pendientes · {{ $detalleInscripcion['documentos_observados'] ?? 0 }} observados
                        </p>
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-wrap justify-end gap-2">
                    @if (! empty($detalleInscripcion['cod_ins']))
                        <button type="button" wire:click="abrirEditar('{{ $detalleInscripcion['cod_ins'] }}')" class="ui-btn-secondary">Editar</button>
                        <button type="button" wire:click="abrirDocumentos('{{ $detalleInscripcion['cod_ins'] }}')" class="ui-btn-secondary">Documentos</button>
                        <button type="button" wire:click="generarConstancia('{{ $detalleInscripcion['cod_ins'] }}')" class="ui-btn-primary">Constancia</button>
                    @endif
                    <button type="button" wire:click="cerrarDetalle" class="ui-btn-secondary">Cerrar</button>
                </div>
            </div>
        </div>
    @endif

    @if ($modalAcciones && $accionesInscripcion)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop savp-modal-backdrop" wire:click="cerrarModalAcciones"></div>
            <div class="ui-modal savp-modal-shell w-full max-w-3xl overflow-hidden">
                <div class="ui-modal-header flex items-start justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Acciones</p>
                        <h3 class="ui-title text-xl font-black">{{ $accionesInscripcion['estudiante'] ?? 'Inscripción' }}</h3>
                        <p class="ui-muted mt-1 text-sm">Acciones secundarias agrupadas para evitar saturación visual.</p>
                    </div>
                    <button type="button" wire:click="cerrarModalAcciones" class="ui-icon-btn">{!! $icon('x') !!}</button>
                </div>

                <div class="p-5 grid gap-3 sm:grid-cols-2">
                    <button type="button" wire:click="abrirDetalle('{{ $accionesInscripcion['cod_ins'] ?? '' }}')" class="ui-btn-secondary justify-center">
                        {!! $icon('eye', 'h-4 w-4') !!}
                        Ver
                    </button>

                    <button type="button" wire:click="abrirEditar('{{ $accionesInscripcion['cod_ins'] ?? '' }}')" class="ui-btn-secondary justify-center">
                        {!! $icon('edit', 'h-4 w-4') !!}
                        Editar
                    </button>

                    <button type="button" wire:click="abrirDocumentos('{{ $accionesInscripcion['cod_ins'] ?? '' }}')" class="ui-btn-secondary justify-center">
                        {!! $icon('file', 'h-4 w-4') !!}
                        Documentos
                    </button>

                    <button type="button" wire:click="generarConstancia('{{ $accionesInscripcion['cod_ins'] ?? '' }}')" class="ui-btn-primary justify-center">
                        {!! $icon('download', 'h-4 w-4') !!}
                        Constancia
                    </button>

                    @if (in_array(($accionesInscripcion['estado'] ?? ''), ['ANULADA', 'RETIRADA'], true))
                        <button type="button" wire:click="reactivarInscripcion('{{ $accionesInscripcion['cod_ins'] ?? '' }}')" class="ui-btn-secondary justify-center">
                            {!! $icon('refresh', 'h-4 w-4') !!}
                            Reactivar
                        </button>
                    @else
                        <button type="button" wire:click="confirmarAnular('{{ $accionesInscripcion['cod_ins'] ?? '' }}')" class="ui-btn-danger justify-center">
                            {!! $icon('trash', 'h-4 w-4') !!}
                            Anular
                        </button>

                        <button type="button" wire:click="confirmarRetiro('{{ $accionesInscripcion['cod_ins'] ?? '' }}')" class="ui-btn-secondary justify-center">
                            {!! $icon('x', 'h-4 w-4') !!}
                            Retirar
                        </button>
                    @endif
                </div>

                <div class="ui-modal-footer flex justify-end">
                    <button type="button" wire:click="cerrarModalAcciones" class="ui-btn-secondary">Cerrar</button>
                </div>
            </div>
        </div>
    @endif

    @if ($modalDocumentos)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop savp-modal-backdrop" wire:click="cerrarDocumentos"></div>
            <div class="ui-modal savp-modal-shell w-full max-w-6xl overflow-hidden">
                <div class="ui-modal-header flex items-start justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Control documental</p>
                        <h3 class="ui-title text-xl font-black">Documentos de inscripción</h3>
                        <p class="ui-muted mt-1 text-sm">Actualiza estados documentales sin borrar información de forma abrupta.</p>
                    </div>
                    <button type="button" wire:click="cerrarDocumentos" class="ui-icon-btn">{!! $icon('x') !!}</button>
                </div>

                <div class="max-h-[70vh] overflow-y-auto p-5">
                    <div class="mb-5 flex flex-wrap gap-2">
                        <button type="button" wire:click="generarChecklistEnModal" class="ui-btn-secondary">Generar recomendados</button>
                        <button type="button" wire:click="agregarDocumentoEnModal" class="ui-btn-primary">Agregar documento</button>
                    </div>

                    <div class="space-y-3">
                        @forelse ($documentosModal as $index => $documento)
                            @php
                                $estadoDoc = $documento['est_die'] ?? 'PRESENTADO';
                                $tieneArchivo = ! empty($documento['rut_die']);
                                $mostrarObsFecha = in_array($estadoDoc, ['OBSERVADO', 'PENDIENTE'], true);
                                $etiqueta = match (true) {
                                    $estadoDoc === 'PRESENTADO' && ! $tieneArchivo => 'Esperando archivo PDF',
                                    $estadoDoc === 'PRESENTADO' && $tieneArchivo => 'Archivo recibido',
                                    $estadoDoc === 'VALIDADO' => 'Validado',
                                    $estadoDoc === 'OBSERVADO' => 'Observado',
                                    $estadoDoc === 'PENDIENTE' => 'Pendiente de regularización',
                                    $estadoDoc === 'NO_APLICA' => 'No aplica',
                                    default => $estadoDoc,
                                };
                            @endphp

                            <article class="ui-card-soft flex flex-col gap-3 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="ui-title text-sm font-black leading-snug">{{ $documento['nom_die'] ?? 'Documento' }}</p>
                                        <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                            <span class="ui-badge-muted text-xs">{{ $documento['tip_die'] ?? 'GENERAL' }}</span>
                                            <span class="{{ $this->badgeDocumento($estadoDoc) }} text-xs">{{ $etiqueta }}</span>
                                        </div>
                                    </div>

                                    <button type="button" wire:click="quitarDocumentoEnModal({{ $index }})" class="ui-icon-btn shrink-0" title="Quitar documento">
                                        {!! $icon('trash', 'h-4 w-4') !!}
                                    </button>
                                </div>

                                <div class="flex flex-wrap gap-1.5">
                                    @if ($estadoDoc !== 'VALIDADO' && $estadoDoc !== 'NO_APLICA' && $tieneArchivo)
                                        <button type="button" wire:click="marcarDocumentoEnModal({{ $index }}, 'VALIDADO')" class="ui-btn-primary px-2.5 py-1.5 text-xs">Todo en orden</button>
                                    @endif
                                    @if ($estadoDoc !== 'NO_APLICA')
                                        <button type="button" wire:click="marcarDocumentoEnModal({{ $index }}, 'OBSERVADO')" class="ui-btn-secondary px-2.5 py-1.5 text-xs">Observar</button>
                                        <button type="button" wire:click="marcarDocumentoEnModal({{ $index }}, 'PENDIENTE')" class="ui-btn-secondary px-2.5 py-1.5 text-xs">Dejar pendiente</button>
                                        <button type="button" wire:click="marcarDocumentoEnModal({{ $index }}, 'NO_APLICA')" class="ui-btn-secondary px-2.5 py-1.5 text-xs">No aplica</button>
                                    @else
                                        <button type="button" wire:click="marcarDocumentoEnModal({{ $index }}, 'PRESENTADO')" class="ui-btn-secondary px-2.5 py-1.5 text-xs">Reactivar</button>
                                    @endif
                                </div>

                                @if ($mostrarObsFecha)
                                    <div>
                                        <label class="ui-label text-xs">Observación documental</label>
                                        <textarea wire:model.live.debounce.700ms="documentosModal.{{ $index }}.obs_die" rows="2" class="ui-textarea mt-1 text-sm" placeholder="Describe la observación..."></textarea>
                                    </div>

                                    <div>
                                        <label class="ui-label text-xs">Fecha límite</label>
                                        <input type="date" wire:model.live="documentosModal.{{ $index }}.fec_lim_die" class="ui-input mt-1 text-sm" disabled>
                                    </div>
                                @endif
                            </article>
                        @empty
                            <div class="ui-alert-info">No hay documentos registrados para esta inscripción.</div>
                        @endforelse
                    </div>
                </div>

                <div class="ui-modal-footer flex justify-end gap-2">
                    <button type="button" wire:click="cerrarDocumentos" class="ui-btn-secondary">Cancelar</button>
                    <button type="button" wire:click="guardarDocumentosModal" class="ui-btn-primary">Guardar documentos</button>
                </div>
            </div>
        </div>
    @endif

    @if ($modalAnular)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop savp-modal-backdrop" wire:click="cerrarAnular"></div>
            <div class="ui-modal savp-modal-shell w-full max-w-2xl overflow-hidden">
                <div class="ui-modal-header flex items-start justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Acción sensible</p>
                        <h3 class="ui-title text-xl font-black">¿Anular inscripción?</h3>
                        <p class="ui-muted mt-1 text-sm">La inscripción no será eliminada físicamente.</p>
                    </div>
                    <button type="button" wire:click="cerrarAnular" class="ui-icon-btn">{!! $icon('x') !!}</button>
                </div>

                <div class="p-5">
                    <div class="ui-alert-warning">Registra un motivo claro para conservar trazabilidad institucional.</div>

                    <div class="mt-4">
                        <label class="ui-label">Motivo de anulación</label>
                        <textarea wire:model.live.debounce.800ms="motivoAccion" rows="4" class="ui-textarea" placeholder="Describe el motivo de anulación."></textarea>
                        @error('motivoAccion') <p class="ui-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="ui-modal-footer flex justify-end gap-2">
                    <button type="button" wire:click="cerrarAnular" class="ui-btn-secondary">Cancelar</button>
                    <button type="button" wire:click="anularInscripcion" class="ui-btn-danger">Anular inscripción</button>
                </div>
            </div>
        </div>
    @endif

    @if ($modalRetirar)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop savp-modal-backdrop" wire:click="cerrarRetiro"></div>
            <div class="ui-modal savp-modal-shell w-full max-w-2xl overflow-hidden">
                <div class="ui-modal-header flex items-start justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Registro de retiro</p>
                        <h3 class="ui-title text-xl font-black">¿Registrar retiro académico?</h3>
                        <p class="ui-muted mt-1 text-sm">El estudiante será marcado como retirado, conservando historial.</p>
                    </div>
                    <button type="button" wire:click="cerrarRetiro" class="ui-icon-btn">{!! $icon('x') !!}</button>
                </div>

                <div class="p-5 space-y-4">
                    <div>
                        <label class="ui-label">Fecha de retiro</label>
                        <input type="date" wire:model.live="fechaRetiro" class="ui-input">
                        @error('fechaRetiro') <p class="ui-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="ui-label">Motivo de retiro</label>
                        <textarea wire:model.live.debounce.800ms="motivoAccion" rows="4" class="ui-textarea" placeholder="Describe el motivo de retiro."></textarea>
                        @error('motivoAccion') <p class="ui-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="ui-modal-footer flex justify-end gap-2">
                    <button type="button" wire:click="cerrarRetiro" class="ui-btn-secondary">Cancelar</button>
                    <button type="button" wire:click="registrarRetiro" class="ui-btn-danger">Registrar retiro</button>
                </div>
            </div>
        </div>
    @endif

    {{-- =========================================================
        ESTILOS Y ANIMACIONES
    ========================================================== --}}
    <style>
        .savp-inscripciones {
            --savp-ease: cubic-bezier(.22, 1, .36, 1);
        }

        .savp-loading-line {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            height: 3px;
            width: 100%;
            background: linear-gradient(90deg, transparent, var(--ui-primary), transparent);
            animation: savpLoading 1.1s linear infinite;
        }

        .savp-hero-panel {
            position: relative;
            isolation: isolate;
        }

        .savp-hero-orb {
            position: absolute;
            width: 22rem;
            height: 22rem;
            border-radius: 999px;
            filter: blur(42px);
            opacity: .18;
            pointer-events: none;
            background: var(--ui-primary);
            animation: savpFloat 9s var(--savp-ease) infinite alternate;
        }

        .savp-hero-orb-a {
            top: -8rem;
            right: 4rem;
        }

        .savp-hero-orb-b {
            bottom: -10rem;
            left: 18%;
            animation-delay: -3s;
            opacity: .10;
        }

        .savp-fade-up,
        .savp-card-enter {
            animation: savpFadeUp .62s var(--savp-ease) both;
        }

        .savp-delay-1 { animation-delay: .08s; }
        .savp-delay-2 { animation-delay: .16s; }
        .savp-delay-3 { animation-delay: .24s; }

        .savp-btn-lift {
            transition: transform .22s var(--savp-ease), box-shadow .22s var(--savp-ease), opacity .22s var(--savp-ease);
        }

        .savp-btn-lift:hover {
            transform: translateY(-2px);
        }

        .savp-student-card,
        .savp-step-card {
            position: relative;
            overflow: hidden;
            transition: transform .26s var(--savp-ease), border-color .26s var(--savp-ease), background .26s var(--savp-ease);
        }

        .savp-student-card:hover,
        .savp-step-card:hover {
            transform: translateY(-3px);
            border-color: var(--ui-primary);
        }

        .savp-step-active {
            border-color: var(--ui-primary) !important;
            box-shadow: 0 0 0 1px var(--ui-primary-soft);
        }

        .savp-step-active::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--ui-primary-soft), transparent 55%);
            opacity: .24;
            pointer-events: none;
        }

        .savp-specialty-panel {
            animation: savpFadeUp .34s var(--savp-ease) both;
        }

        .savp-row {
            transition: background .2s var(--savp-ease), transform .2s var(--savp-ease);
        }

        .savp-row:hover {
            background: var(--ui-surface-muted);
        }

        .savp-modal-wrap {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 1rem;
            overflow-y: auto;
        }

        .savp-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 9998;
            animation: savpBackdrop .24s ease-out both;
        }

        .savp-modal-shell {
            position: relative;
            z-index: 10000;
            animation: savpModalIn .34s var(--savp-ease) both;
        }

        @media (min-width: 768px) {
            .savp-modal-wrap {
                align-items: center;
            }
        }

        @keyframes savpFadeUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes savpFloat {
            from {
                transform: translate3d(0, 0, 0) scale(1);
            }
            to {
                transform: translate3d(18px, -12px, 0) scale(1.04);
            }
        }

        @keyframes savpBackdrop {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes savpModalIn {
            from {
                opacity: 0;
                transform: translateY(18px) scale(.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes savpLoading {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        @media (prefers-reduced-motion: reduce) {
            .savp-hero-orb,
            .savp-fade-up,
            .savp-card-enter,
            .savp-modal-shell,
            .savp-modal-backdrop,
            .savp-loading-line {
                animation: none !important;
            }

            .savp-btn-lift,
            .savp-student-card,
            .savp-step-card,
            .savp-row {
                transition: none !important;
            }
        }

        .savp-search-shell {
            position: relative;
        }

        .savp-search-input {
            min-height: 3.25rem;
            font-size: .95rem;
        }

        .savp-search-loading {
            position: absolute;
            right: .9rem;
            top: 50%;
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            transform: translateY(-50%);
            font-size: .72rem;
            color: var(--ui-muted);
        }

        .savp-spinner {
            width: .9rem;
            height: .9rem;
            border-radius: 999px;
            border: 2px solid var(--ui-border);
            border-top-color: var(--ui-primary);
            animation: savpSpin .7s linear infinite;
        }

        .savp-avatar-lg {
            display: flex;
            width: 4.5rem;
            height: 4.5rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: 1.5rem;
        }

        .savp-avatar-md {
            display: flex;
            width: 3rem;
            height: 3rem;
            flex-shrink: 0;
            align-items: center;
            justify-content: center;
            border-radius: 1.1rem;
        }

        .savp-selected-student {
            position: relative;
            overflow: hidden;
        }

        .savp-selected-student::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(135deg, var(--ui-primary-soft), transparent 50%);
            opacity: .14;
        }

        .savp-student-selected {
            border-color: var(--ui-primary) !important;
            box-shadow: 0 0 0 1px var(--ui-primary-soft);
        }

        @keyframes savpSpin {
            to {
                transform: rotate(360deg);
            }
        }

        .savp-progress {
            background: linear-gradient(90deg, var(--ui-primary), var(--ui-primary-soft));
            transition: width .7s var(--savp-ease);
        }

        .savp-specialty-panel {
            position: relative;
            overflow: hidden;
        }

        .savp-specialty-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(135deg, var(--ui-primary-soft), transparent 58%);
            opacity: .10;
        }

        .savp-prefill-panel {
            position: relative;
            overflow: hidden;
        }

        .savp-prefill-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(135deg, var(--ui-primary-soft), transparent 60%);
            opacity: .10;
        }

        .savp-doc-preview-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            border: 1px solid var(--ui-border);
            border-radius: 1rem;
            padding: .75rem;
            background: var(--ui-surface);
        }

        .savp-doc-card {
            transition: transform .22s var(--savp-ease), border-color .22s var(--savp-ease), background .22s var(--savp-ease);
        }

        .savp-doc-card:hover {
            transform: translateY(-2px);
            border-color: var(--ui-primary);
        }

        .savp-doc-action {
            border: 1px solid var(--ui-border);
            border-radius: .9rem;
            padding: .48rem .55rem;
            font-size: .68rem;
            font-weight: 800;
            color: var(--ui-muted);
            background: var(--ui-surface);
            transition: transform .18s var(--savp-ease), border-color .18s var(--savp-ease), background .18s var(--savp-ease), color .18s var(--savp-ease);
        }

        .savp-doc-action:hover {
            transform: translateY(-1px);
            border-color: var(--ui-primary);
            color: var(--ui-text);
        }

        .savp-doc-action-active {
            border-color: var(--ui-primary);
            background: var(--ui-primary-soft);
            color: var(--ui-text);
        }
        
        .savp-review-main,
        .savp-confirm-panel {
            position: relative;
            overflow: hidden;
        }

        .savp-review-main::before,
        .savp-confirm-panel::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(135deg, var(--ui-primary-soft), transparent 58%);
            opacity: .10;
        }

        .savp-preview-block,
        .savp-final-row {
            border: 1px solid var(--ui-border);
            border-radius: 1.25rem;
            padding: 1rem;
            background: var(--ui-surface);
            transition: transform .22s var(--savp-ease), border-color .22s var(--savp-ease), background .22s var(--savp-ease);
        }

        .savp-preview-block:hover,
        .savp-final-row:hover {
            transform: translateY(-2px);
            border-color: var(--ui-primary);
        }

        .savp-type-context,
        .savp-type-card {
            position: relative;
            overflow: hidden;
        }

        .savp-type-context::before {
            content: "";
            position: absolute;
            inset: 0;
            pointer-events: none;
            background: linear-gradient(135deg, var(--ui-primary-soft), transparent 58%);
            opacity: .10;
        }

        .savp-type-card {
            transition: transform .22s var(--savp-ease), border-color .22s var(--savp-ease), background .22s var(--savp-ease), box-shadow .22s var(--savp-ease);
        }

        .savp-type-card:hover {
            transform: translateY(-3px);
            border-color: var(--ui-primary);
        }

        .savp-type-active {
            border-color: var(--ui-primary) !important;
            box-shadow: 0 0 0 1px var(--ui-primary-soft);
        }

        .savp-type-active::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, var(--ui-primary-soft), transparent 60%);
            opacity: .18;
            pointer-events: none;
        }

        .savp-type-doc-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: .75rem;
            border: 1px solid var(--ui-border);
            border-radius: 1rem;
            padding: .75rem;
            background: var(--ui-surface);
        }
    </style>

    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('swal', (event) => {
                const data = Array.isArray(event) ? event[0] : event;

                if (!window.Swal) {
                    return;
                }

                Swal.fire({
                    icon: data.tipo || 'info',
                    title: data.mensaje || 'Proceso realizado',
                    toast: true,
                    position: 'top-end',
                    timer: 2800,
                    showConfirmButton: false,
                    background: 'var(--ui-surface)',
                    color: 'var(--ui-text)',
                    customClass: {
                        popup: 'rounded-2xl'
                    }
                });
            });
        });
    </script>
</div>
