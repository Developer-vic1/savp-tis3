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

                                    @if (in_array($inscripcion->est_ins ?? '', ['ANULADO', 'RETIRADO']))
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
            <div class="ui-modal-backdrop savp-modal-backdrop"></div>

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
                    <div class="grid gap-6 xl:grid-cols-[1fr_380px]">
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
                                                    Busca al estudiante por RUDE, CI, nombre, apellido o teléfono. Al seleccionarlo,
                                                    el sistema revisará su historial, edad, estado actual y posible duplicidad en la gestión activa.
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
                                                        {{ $estudianteSeleccionado['edad'] ? $estudianteSeleccionado['edad'].' años' : 'Sin fecha de nacimiento' }}
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
                                                            {{ $estudianteSeleccionado['edad'] ? $estudianteSeleccionado['edad'].' años' : 'Sin edad' }}
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

                            {{-- PASO 2: TIPO DE INSCRIPCIÓN --}}
                            @if ($pasoInscripcion === 2)
                                <div class="space-y-5">
                                    {{-- CABECERA --}}
                                    <section class="ui-panel">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="ui-badge-success">Paso 2</span>
                                                    <span class="ui-badge-muted">Clasificación</span>
                                                    <span class="ui-badge-warning">Tipo de inscripción</span>

                                                    @if (! empty($situacionEstudiante['tipo_sugerido']))
                                                        <span class="ui-badge-success">
                                                            Sugerido: {{ $situacionEstudiante['tipo_sugerido'] }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <h4 class="ui-title mt-3 text-2xl font-black">
                                                    Definir tipo de inscripción
                                                </h4>

                                                <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">
                                                    Clasifica el ingreso del estudiante según su historial académico, situación institucional,
                                                    procedencia y condiciones especiales. Esta selección ajustará la revisión y la checklist documental.
                                                </p>
                                            </div>

                                            <div class="ui-card-soft min-w-[280px] p-4">
                                                <p class="ui-muted text-xs">Estudiante seleccionado</p>
                                                <p class="ui-title mt-1 font-black">
                                                    {{ $estudianteSeleccionado['nombre_completo'] ?? 'Sin estudiante seleccionado' }}
                                                </p>

                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    @if (! empty($situacionEstudiante['situacion']))
                                                        <span class="{{ ($situacionEstudiante['situacion'] ?? '') === 'YA_INSCRITO' ? 'ui-badge-danger' : 'ui-badge-muted' }}">
                                                            {{ str_replace('_', ' ', $situacionEstudiante['situacion']) }}
                                                        </span>
                                                    @endif

                                                    <span class="{{ $this->badgeEstado($formInscripcion['est_ins'] ?? 'PENDIENTE') }}">
                                                        {{ $formInscripcion['est_ins'] ?? 'PENDIENTE' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    {{-- SITUACIÓN DEL ESTUDIANTE --}}
                                    <section class="grid gap-5 xl:grid-cols-[1fr_380px]">
                                        <article class="ui-panel savp-type-context">
                                            <div class="flex gap-4">
                                                <span class="savp-avatar-md ui-badge-success">
                                                    {!! $icon('shield', 'h-6 w-6') !!}
                                                </span>

                                                <div>
                                                    <p class="ui-kicker">Revisión por historial</p>
                                                    <h5 class="ui-title mt-1 text-xl font-black">
                                                        {{ str_replace('_', ' ', $situacionEstudiante['situacion'] ?? 'SIN CLASIFICAR') }}
                                                    </h5>

                                                    <p class="ui-muted mt-2 text-sm leading-6">
                                                        {{ $situacionEstudiante['mensaje'] ?? 'Selecciona un estudiante para analizar su historial y sugerir el tipo de inscripción.' }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mt-5 grid gap-4 md:grid-cols-3">
                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Tipo actual</p>
                                                    <p class="ui-title mt-1 font-black">
                                                        {{ $formInscripcion['tip_ins'] ?? 'REGULAR' }}
                                                    </p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Tipo sugerido</p>
                                                    <p class="ui-title mt-1 font-black">
                                                        {{ $situacionEstudiante['tipo_sugerido'] ?? ($analisis['tipo_sugerido']['tipo'] ?? 'REGULAR') }}
                                                    </p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Historial</p>
                                                    <p class="ui-title mt-1 font-black">
                                                        {{ count($historialEstudiante) }} registro(s)
                                                    </p>
                                                </div>
                                            </div>

                                            @if (($situacionEstudiante['situacion'] ?? '') === 'YA_INSCRITO')
                                                <div class="ui-alert-danger mt-5">
                                                    El estudiante ya tiene una inscripción activa en la gestión seleccionada. Revisa el registro existente antes de continuar.
                                                </div>
                                            @elseif (($situacionEstudiante['situacion'] ?? '') === 'SIN_HISTORIAL')
                                                <div class="ui-alert-info mt-5">
                                                    No se encontró historial institucional previo. Se recomienda clasificar como nuevo estudiante.
                                                </div>
                                            @elseif (($situacionEstudiante['situacion'] ?? '') === 'RETORNO')
                                                <div class="ui-alert-warning mt-5">
                                                    El estudiante presenta retiro anterior. Se recomienda reinscripción con observación institucional.
                                                </div>
                                            @else
                                                <div class="ui-alert-success mt-5">
                                                    La situación del estudiante puede clasificarse y continuar con la asignación académica.
                                                </div>
                                            @endif
                                        </article>

                                        {{-- PANEL DE AYUDA --}}
                                        <aside class="ui-panel">
                                            <p class="ui-kicker">Criterio de clasificación</p>
                                            <h5 class="ui-title mt-1 text-xl font-black">
                                                ¿Qué afecta esta elección?
                                            </h5>

                                            <div class="mt-5 space-y-3">
                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-title text-sm font-black">Checklist documental</p>
                                                    <p class="ui-muted mt-1 text-xs leading-5">
                                                        Traslado, extranjero y casos especiales pueden requerir documentos adicionales.
                                                    </p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-title text-sm font-black">Estado sugerido</p>
                                                    <p class="ui-muted mt-1 text-xs leading-5">
                                                        Casos excepcionales pueden guardarse como provisional u observado.
                                                    </p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-title text-sm font-black">Seguimiento institucional</p>
                                                    <p class="ui-muted mt-1 text-xs leading-5">
                                                        Vulnerabilidad, traslado o reinscripción deben conservar observación clara.
                                                    </p>
                                                </div>
                                            </div>
                                        </aside>
                                    </section>

                                    {{-- TARJETAS DE TIPO --}}
                                    <section class="ui-panel">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                            <div>
                                                <p class="ui-kicker">Tipos disponibles</p>
                                                <h5 class="ui-title mt-1 text-xl font-black">
                                                    Selecciona la clasificación correspondiente
                                                </h5>
                                                <p class="ui-muted mt-1 text-sm">
                                                    Elige el tipo más adecuado. El sistema no borra datos ni cambia documentos de forma agresiva.
                                                </p>
                                            </div>

                                            @if (! empty($situacionEstudiante['tipo_sugerido']))
                                                <button
                                                    type="button"
                                                    wire:click="seleccionarTipoRapido('{{ $situacionEstudiante['tipo_sugerido'] }}')"
                                                    class="ui-btn-primary"
                                                >
                                                    Aplicar sugerido
                                                </button>
                                            @endif
                                        </div>

                                        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                                            @foreach ([
                                                'REGULAR' => [
                                                    'titulo' => 'Regular',
                                                    'descripcion' => 'Continuidad académica del estudiante dentro de la institución.',
                                                    'badge' => 'Continuidad',
                                                    'alerta' => 'Ideal para estudiantes con historial institucional previo.',
                                                    'icono' => 'check',
                                                ],
                                                'NUEVO' => [
                                                    'titulo' => 'Nuevo',
                                                    'descripcion' => 'Primer registro de inscripción del estudiante en la unidad educativa.',
                                                    'badge' => 'Primer ingreso',
                                                    'alerta' => 'Requiere mayor control documental inicial.',
                                                    'icono' => 'user',
                                                ],
                                                'TRASLADO' => [
                                                    'titulo' => 'Traslado',
                                                    'descripcion' => 'Estudiante proveniente de otra unidad educativa.',
                                                    'badge' => 'Procedencia',
                                                    'alerta' => 'Se recomienda registrar unidad educativa de procedencia.',
                                                    'icono' => 'refresh',
                                                ],
                                                'REINSCRIPCION' => [
                                                    'titulo' => 'Reinscripción',
                                                    'descripcion' => 'Retorno después de retiro, interrupción o inscripción anterior no activa.',
                                                    'badge' => 'Retorno',
                                                    'alerta' => 'Debe revisarse el historial institucional previo.',
                                                    'icono' => 'calendar',
                                                ],
                                                'REPITENTE' => [
                                                    'titulo' => 'Repitente',
                                                    'descripcion' => 'Estudiante que cursará nuevamente el nivel académico.',
                                                    'badge' => 'Seguimiento',
                                                    'alerta' => 'Puede requerir observación académica.',
                                                    'icono' => 'book',
                                                ],
                                                'EXCEPCIONAL' => [
                                                    'titulo' => 'Excepcional',
                                                    'descripcion' => 'Caso especial que requiere respaldo administrativo.',
                                                    'badge' => 'Especial',
                                                    'alerta' => 'Debe registrarse justificación institucional.',
                                                    'icono' => 'warning',
                                                ],
                                                'VULNERABILIDAD' => [
                                                    'titulo' => 'Vulnerabilidad',
                                                    'descripcion' => 'Caso que requiere seguimiento prioritario y cuidado institucional.',
                                                    'badge' => 'Prioritario',
                                                    'alerta' => 'No debe bloquearse el acceso, pero sí conservar seguimiento.',
                                                    'icono' => 'shield',
                                                ],
                                                'EXTRANJERO' => [
                                                    'titulo' => 'Extranjero',
                                                    'descripcion' => 'Estudiante con documentación o procedencia internacional.',
                                                    'badge' => 'Documental',
                                                    'alerta' => 'Requiere revisión específica de identidad y respaldo.',
                                                    'icono' => 'file',
                                                ],
                                            ] as $tipo => $info)
                                                @php
                                                    $activo = ($formInscripcion['tip_ins'] ?? '') === $tipo;
                                                    $sugerido = ($situacionEstudiante['tipo_sugerido'] ?? null) === $tipo
                                                        || ($analisis['tipo_sugerido']['tipo'] ?? null) === $tipo;
                                                @endphp

                                                <button
                                                    type="button"
                                                    wire:click="seleccionarTipoRapido('{{ $tipo }}')"
                                                    class="savp-type-card ui-card-soft p-4 text-left {{ $activo ? 'savp-type-active' : '' }}"
                                                >
                                                    <div class="flex items-start justify-between gap-3">
                                                        <span class="{{ $activo ? 'ui-badge-success' : 'ui-badge-muted' }} p-2">
                                                            {!! $icon($info['icono'], 'h-5 w-5') !!}
                                                        </span>

                                                        <div class="flex flex-col items-end gap-2">
                                                            @if ($sugerido)
                                                                <span class="ui-badge-success">Sugerido</span>
                                                            @endif

                                                            <span class="{{ $activo ? 'ui-badge-success' : 'ui-badge-muted' }}">
                                                                {{ $info['badge'] }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <h6 class="ui-title mt-4 text-base font-black">
                                                        {{ $info['titulo'] }}
                                                    </h6>

                                                    <p class="ui-muted mt-2 text-xs leading-5">
                                                        {{ $info['descripcion'] }}
                                                    </p>

                                                    <div class="mt-4 rounded-2xl border px-3 py-2 text-xs" style="border-color: var(--ui-border); background: var(--ui-surface-muted); color: var(--ui-muted);">
                                                        {{ $info['alerta'] }}
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </section>

                                    {{-- CAMPOS COMPLEMENTARIOS SEGÚN TIPO --}}
                                    <section class="grid gap-5 xl:grid-cols-[1fr_390px]">
                                        <article class="ui-panel">
                                            <p class="ui-kicker">Datos complementarios</p>
                                            <h5 class="ui-title mt-1 text-xl font-black">
                                                Información para respaldo institucional
                                            </h5>

                                            <p class="ui-muted mt-1 text-sm">
                                                Algunos tipos requieren procedencia, motivo u observación para que el registro sea claro.
                                            </p>

                                            <div class="mt-5 grid gap-4 md:grid-cols-2">
                                                <div>
                                                    <label class="ui-label">
                                                        Procedencia
                                                        @if (($formInscripcion['tip_ins'] ?? '') === 'TRASLADO')
                                                            <span class="ui-badge-warning ml-2">Recomendado</span>
                                                        @endif
                                                    </label>

                                                    <input
                                                        type="text"
                                                        wire:model.live.debounce.800ms="formInscripcion.pro_ins"
                                                        class="ui-input"
                                                        placeholder="Unidad educativa de procedencia, si aplica"
                                                    >

                                                    @error('formInscripcion.pro_ins')
                                                        <p class="ui-error">{{ $message }}</p>
                                                    @enderror
                                                </div>

                                                <div>
                                                    <label class="ui-label">Condición inicial</label>
                                                    <select wire:model.live="formInscripcion.con_ins" class="ui-select">
                                                        @foreach ($catalogos['condiciones_inscripcion'] ?? [] as $condicion)
                                                            <option value="{{ $condicion }}">
                                                                {{ ucfirst(strtolower($condicion)) }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    @error('formInscripcion.con_ins')
                                                        <p class="ui-error">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="mt-5">
                                                <label class="ui-label">
                                                    Observación institucional
                                                    @if (in_array(($formInscripcion['tip_ins'] ?? ''), ['EXCEPCIONAL', 'VULNERABILIDAD', 'EXTRANJERO', 'REINSCRIPCION', 'REPITENTE'], true))
                                                        <span class="ui-badge-warning ml-2">Importante</span>
                                                    @endif
                                                </label>

                                                <textarea
                                                    wire:model.live.debounce.900ms="formInscripcion.obs_ins"
                                                    rows="4"
                                                    class="ui-textarea"
                                                    placeholder="Ejemplo: inscripción por traslado con documentación pendiente de regularización."
                                                ></textarea>

                                                @error('formInscripcion.obs_ins')
                                                    <p class="ui-error">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </article>

                                        {{-- IMPACTO DOCUMENTAL --}}
                                        <aside class="ui-panel">
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <p class="ui-kicker">Vista previa documental</p>
                                                    <h5 class="ui-title mt-1 text-xl font-black">
                                                        Checklist sugerida
                                                    </h5>
                                                </div>

                                                <button type="button" wire:click="previsualizarChecklist" class="ui-btn-secondary px-3 py-2 text-xs">
                                                    Actualizar
                                                </button>
                                            </div>

                                            <div class="mt-5">
                                                @if (! empty($previsualizacionChecklist))
                                                    <div class="grid grid-cols-3 gap-3">
                                                        <div class="ui-card-soft p-3">
                                                            <p class="ui-muted text-xs">Recomendados</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ count($previsualizacionChecklist['recomendados'] ?? []) }}
                                                            </p>
                                                        </div>

                                                        <div class="ui-card-soft p-3">
                                                            <p class="ui-muted text-xs">Actuales</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ count($previsualizacionChecklist['actuales'] ?? []) }}
                                                            </p>
                                                        </div>

                                                        <div class="ui-card-soft p-3">
                                                            <p class="ui-muted text-xs">Faltantes</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ count($previsualizacionChecklist['faltantes'] ?? []) }}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div class="ui-alert-info mt-4">
                                                        {{ $previsualizacionChecklist['mensaje'] ?? 'Checklist evaluada correctamente.' }}
                                                    </div>

                                                    <div class="mt-4 space-y-2">
                                                        @forelse (array_slice($previsualizacionChecklist['recomendados'] ?? [], 0, 5) as $doc)
                                                            <div class="savp-type-doc-row">
                                                                <div>
                                                                    <p class="ui-title text-sm font-black">
                                                                        {{ $doc['nom_die'] ?? 'Documento' }}
                                                                    </p>
                                                                    <p class="ui-muted text-xs">
                                                                        {{ $doc['tip_die'] ?? 'GENERAL' }}
                                                                    </p>
                                                                </div>

                                                                <span class="{{ $this->badgeDocumento($doc['est_die'] ?? 'PENDIENTE') }}">
                                                                    {{ ucfirst(strtolower($doc['est_die'] ?? 'Pendiente')) }}
                                                                </span>
                                                            </div>
                                                        @empty
                                                            <div class="ui-alert-info">
                                                                Presiona actualizar para ver documentos sugeridos.
                                                            </div>
                                                        @endforelse
                                                    </div>
                                                @else
                                                    <div class="ui-alert-info">
                                                        Genera una vista previa para ver qué documentos serán sugeridos en el siguiente paso.
                                                    </div>

                                                    <button type="button" wire:click="previsualizarChecklist" class="ui-btn-primary mt-4 w-full justify-center">
                                                        Generar vista previa
                                                    </button>
                                                @endif
                                            </div>
                                        </aside>
                                    </section>

                                    {{-- ALERTAS SEGÚN TIPO --}}
                                    <section class="grid gap-5 xl:grid-cols-3">
                                        <article class="ui-panel">
                                            <p class="ui-kicker">Bloqueos</p>

                                            <div class="mt-4 space-y-2">
                                                @forelse ($analisis['bloqueos'] ?? [] as $mensaje)
                                                    <div class="ui-alert-danger">{{ $mensaje }}</div>
                                                @empty
                                                    <div class="ui-alert-success">
                                                        No existen bloqueos críticos para clasificar el tipo de inscripción.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </article>

                                        <article class="ui-panel">
                                            <p class="ui-kicker">Observaciones</p>

                                            <div class="mt-4 space-y-2">
                                                @forelse ($analisis['advertencias'] ?? [] as $mensaje)
                                                    <div class="ui-alert-warning">{{ $mensaje }}</div>
                                                @empty
                                                    <div class="ui-alert-success">
                                                        No existen observaciones importantes en esta etapa.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </article>

                                        <article class="ui-panel">
                                            <p class="ui-kicker">Sugerencias</p>

                                            <div class="mt-4 space-y-2">
                                                @forelse ($analisis['sugerencias'] ?? [] as $mensaje)
                                                    <div class="ui-alert-info">{{ $mensaje }}</div>
                                                @empty
                                                    <div class="ui-alert-info">
                                                        Selecciona el tipo para obtener recomendaciones específicas.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </article>
                                    </section>
                                </div>
                            @endif

                            {{-- PASO 3: ASIGNACIÓN ACADÉMICA --}}
                            @if ($pasoInscripcion === 3)
                                <div class="space-y-5">
                                    {{-- CABECERA DEL PASO --}}
                                    <section class="ui-panel">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="ui-badge-success">Paso 3</span>
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
                                                    Esta sección define oficialmente dónde estudiará el estudiante durante la gestión seleccionada.
                                                    Por regla institucional, la inscripción se registra inicialmente en turno Mañana.
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

                                    {{-- PANEL SUPERIOR: SUGERENCIA Y TURNO --}}
                                    <section class="grid gap-5 xl:grid-cols-[1fr_360px]">
                                        {{-- CURSO SUGERIDO --}}
                                        <article class="ui-panel">
                                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                                <div class="flex gap-4">
                                                    <span class="savp-avatar-md ui-badge-success">
                                                        {!! $icon('book', 'h-6 w-6') !!}
                                                    </span>

                                                    <div>
                                                        <p class="ui-kicker">Curso sugerido por edad</p>
                                                        <h5 class="ui-title mt-1 text-xl font-black">
                                                            {{ $cursoSugerido['nombre'] ?? ($analisis['curso_sugerido_disponible']['nombre'] ?? 'Sin sugerencia disponible') }}
                                                        </h5>

                                                        <p class="ui-muted mt-2 text-sm leading-6">
                                                            {{ $analisis['curso_edad']['mensaje'] ?? 'Selecciona un estudiante con fecha de nacimiento registrada para obtener sugerencia referencial.' }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="flex flex-wrap gap-2 xl:justify-end">
                                                    @if (! empty($cursoSugerido['cod_cur']) || ! empty($analisis['curso_sugerido_disponible']['cod_cur']))
                                                        <button type="button" wire:click="aplicarCursoSugerido" class="ui-btn-primary">
                                                            Aplicar sugerencia
                                                        </button>

                                                        <button type="button" wire:click="ignorarCursoSugerido" class="ui-btn-secondary">
                                                            Omitir
                                                        </button>
                                                    @else
                                                        <button type="button" class="ui-btn-secondary" disabled>
                                                            Sin sugerencia
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="mt-5 grid gap-4 md:grid-cols-3">
                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Edad registrada</p>
                                                    <p class="ui-title mt-1 font-black">
                                                        {{ $estudianteSeleccionado['edad'] ? $estudianteSeleccionado['edad'].' años' : 'Sin edad' }}
                                                    </p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Comparación</p>
                                                    <p class="ui-title mt-1 font-black">
                                                        {{ $analisis['curso_edad']['estado'] ?? 'SIN_DATOS' }}
                                                    </p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Tipo sugerido</p>
                                                    <p class="ui-title mt-1 font-black">
                                                        {{ $situacionEstudiante['tipo_sugerido'] ?? ($formInscripcion['tip_ins'] ?? 'REGULAR') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </article>

                                        {{-- TURNO MAÑANA --}}
                                        <aside class="ui-panel">
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

                                                <button type="button" wire:click="forzarTurnoManana" class="ui-btn-primary w-full justify-center">
                                                    {!! $icon('clock', 'h-4 w-4') !!}
                                                    Aplicar Turno Mañana
                                                </button>
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
                                        </aside>
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
                                            </div>
                                        </div>

                                        {{-- PROCEDENCIA --}}
                                        <div class="mt-5 grid gap-4 xl:grid-cols-[1fr_260px]">
                                            <div>
                                                <label class="ui-label">Procedencia</label>
                                                <input
                                                    type="text"
                                                    wire:model.live.debounce.800ms="formInscripcion.pro_ins"
                                                    class="ui-input"
                                                    placeholder="Unidad educativa de procedencia, si aplica"
                                                >
                                                @error('formInscripcion.pro_ins')
                                                    <p class="ui-error">{{ $message }}</p>
                                                @enderror
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
                                    <section class="grid gap-5 xl:grid-cols-[1fr_420px]">
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

                                        {{-- REVISIÓN COMPACTA --}}
                                        <aside class="ui-panel">
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <p class="ui-kicker">Revisión rápida</p>
                                                    <h5 class="ui-title mt-1 text-xl font-black">
                                                        {{ $analisis['mensaje'] ?? 'Completa los datos' }}
                                                    </h5>
                                                </div>

                                                <span class="{{ $this->badgeRevision($estadoRevision) }}">
                                                    {{ $estadoRevision }}
                                                </span>
                                            </div>

                                            <div class="mt-5 space-y-3">
                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Estado sugerido</p>
                                                    <p class="ui-title mt-1 font-black">
                                                        {{ $analisis['estado_sugerido'] ?? 'PENDIENTE' }}
                                                    </p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Condición sugerida</p>
                                                    <p class="ui-title mt-1 font-black">
                                                        {{ $analisis['condicion_sugerida'] ?? 'NORMAL' }}
                                                    </p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Especialidad técnica</p>
                                                    <p class="ui-title mt-1 font-black">
                                                        {{ $especialidadRevision['estado_sugerido'] ?? ($formInscripcion['est_esp_tec_ins'] ?? 'NO_APLICA') }}
                                                    </p>
                                                </div>
                                            </div>

                                            <button type="button" wire:click="aplicarEstadoSugerido" class="ui-btn-secondary mt-5 w-full justify-center">
                                                Aplicar estado sugerido
                                            </button>
                                        </aside>
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

                                                <button type="button" wire:click="dejarEspecialidadPendiente" class="ui-btn-secondary">
                                                    Dejar pendiente
                                                </button>
                                            </div>

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

                            {{-- PASO 4: CONTROL DOCUMENTAL --}}
                            @if ($pasoInscripcion === 4)
                                <div class="space-y-5">
                                    {{-- CABECERA DEL PASO --}}
                                    <section class="ui-panel">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="ui-badge-success">Paso 4</span>
                                                    <span class="ui-badge-muted">Documentos</span>
                                                    <span class="ui-badge-warning">Checklist institucional</span>

                                                    @if (($panelDoc['pendientes'] ?? 0) > 0 || ($panelDoc['observados'] ?? 0) > 0)
                                                        <span class="ui-badge-warning">Con pendientes</span>
                                                    @elseif (($panelDoc['total'] ?? 0) > 0)
                                                        <span class="ui-badge-success">Sin pendientes críticos</span>
                                                    @else
                                                        <span class="ui-badge-muted">Sin checklist</span>
                                                    @endif
                                                </div>

                                                <h4 class="ui-title mt-3 text-2xl font-black">
                                                    Control documental de inscripción
                                                </h4>

                                                <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">
                                                    Registra los documentos presentados, pendientes, observados o no aplicables. El sistema puede
                                                    sugerir una checklist base según el tipo de inscripción sin borrar información ya registrada.
                                                </p>
                                            </div>

                                            <div class="ui-card-soft min-w-[260px] p-4">
                                                <p class="ui-muted text-xs">Tipo de inscripción</p>
                                                <p class="ui-title mt-1 text-lg font-black">
                                                    {{ ucfirst(strtolower($formInscripcion['tip_ins'] ?? 'REGULAR')) }}
                                                </p>

                                                <p class="ui-muted mt-2 text-xs">
                                                    La checklist sugerida se adapta al tipo seleccionado.
                                                </p>
                                            </div>
                                        </div>
                                    </section>

                                    {{-- PANEL DE PRELLENADO / VISTA PREVIA --}}
                                    <section class="grid gap-5 xl:grid-cols-[1fr_380px]">
                                        <article class="ui-panel savp-prefill-panel">
                                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                                <div class="flex gap-4">
                                                    <span class="savp-avatar-md ui-badge-success">
                                                        {!! $icon('file', 'h-6 w-6') !!}
                                                    </span>

                                                    <div>
                                                        <p class="ui-kicker">Prellenado sugerido</p>
                                                        <h5 class="ui-title mt-1 text-xl font-black">
                                                            Checklist base recomendada
                                                        </h5>

                                                        <p class="ui-muted mt-2 text-sm leading-6">
                                                            Puedes generar documentos iniciales según el tipo de inscripción actual.
                                                            Si ya existen documentos registrados, se recomienda agregar solo los faltantes.
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="flex flex-wrap gap-2 xl:justify-end">
                                                    <button type="button" wire:click="previsualizarChecklist" class="ui-btn-secondary">
                                                        Vista previa
                                                    </button>

                                                    <button type="button" wire:click="solicitarChecklistBase" class="ui-btn-primary">
                                                        Generar checklist
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- VISTA PREVIA --}}
                                            <div class="mt-5">
                                                @if (! empty($previsualizacionChecklist))
                                                    <div class="grid gap-4 md:grid-cols-3">
                                                        <div class="ui-card-soft p-4">
                                                            <p class="ui-muted text-xs">Acción recomendada</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ str_replace('_', ' ', $previsualizacionChecklist['accion_recomendada'] ?? 'GENERAR') }}
                                                            </p>
                                                        </div>

                                                        <div class="ui-card-soft p-4">
                                                            <p class="ui-muted text-xs">Documentos actuales</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ count($previsualizacionChecklist['actuales'] ?? []) }}
                                                            </p>
                                                        </div>

                                                        <div class="ui-card-soft p-4">
                                                            <p class="ui-muted text-xs">Faltantes sugeridos</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ count($previsualizacionChecklist['faltantes'] ?? []) }}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    <div class="ui-alert-info mt-4">
                                                        {{ $previsualizacionChecklist['mensaje'] ?? 'Vista previa generada correctamente.' }}
                                                    </div>

                                                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                                                        {{-- RECOMENDADOS --}}
                                                        <div class="ui-card-soft p-4">
                                                            <div class="flex items-center justify-between gap-3">
                                                                <div>
                                                                    <p class="ui-kicker">Recomendados</p>
                                                                    <h6 class="ui-title mt-1 font-black">
                                                                        Checklist sugerida
                                                                    </h6>
                                                                </div>

                                                                <span class="ui-badge-muted">
                                                                    {{ count($previsualizacionChecklist['recomendados'] ?? []) }}
                                                                </span>
                                                            </div>

                                                            <div class="mt-4 space-y-2">
                                                                @forelse (($previsualizacionChecklist['recomendados'] ?? []) as $doc)
                                                                    <div class="savp-doc-preview-row">
                                                                        <div>
                                                                            <p class="ui-title text-sm font-black">
                                                                                {{ $doc['nom_die'] ?? 'Documento' }}
                                                                            </p>
                                                                            <p class="ui-muted text-xs">
                                                                                {{ $doc['tip_die'] ?? 'GENERAL' }}
                                                                            </p>
                                                                        </div>

                                                                        <span class="{{ $this->badgeDocumento($doc['est_die'] ?? 'PENDIENTE') }}">
                                                                            {{ ucfirst(strtolower($doc['est_die'] ?? 'Pendiente')) }}
                                                                        </span>
                                                                    </div>
                                                                @empty
                                                                    <div class="ui-alert-info">
                                                                        No hay documentos recomendados para mostrar.
                                                                    </div>
                                                                @endforelse
                                                            </div>
                                                        </div>

                                                        {{-- FALTANTES --}}
                                                        <div class="ui-card-soft p-4">
                                                            <div class="flex items-center justify-between gap-3">
                                                                <div>
                                                                    <p class="ui-kicker">Faltantes</p>
                                                                    <h6 class="ui-title mt-1 font-black">
                                                                        Sugerencias por agregar
                                                                    </h6>
                                                                </div>

                                                                <span class="{{ count($previsualizacionChecklist['faltantes'] ?? []) > 0 ? 'ui-badge-warning' : 'ui-badge-success' }}">
                                                                    {{ count($previsualizacionChecklist['faltantes'] ?? []) }}
                                                                </span>
                                                            </div>

                                                            <div class="mt-4 space-y-2">
                                                                @forelse (($previsualizacionChecklist['faltantes'] ?? []) as $doc)
                                                                    <div class="savp-doc-preview-row">
                                                                        <div>
                                                                            <p class="ui-title text-sm font-black">
                                                                                {{ $doc['nom_die'] ?? 'Documento' }}
                                                                            </p>
                                                                            <p class="ui-muted text-xs">
                                                                                {{ $doc['tip_die'] ?? 'GENERAL' }}
                                                                            </p>
                                                                        </div>

                                                                        <span class="ui-badge-warning">
                                                                            Faltante
                                                                        </span>
                                                                    </div>
                                                                @empty
                                                                    <div class="ui-alert-success">
                                                                        No se detectaron documentos faltantes respecto a la checklist sugerida.
                                                                    </div>
                                                                @endforelse
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="ui-card-soft p-6">
                                                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                                            <div class="flex gap-4">
                                                                <span class="savp-avatar-md ui-badge-muted">
                                                                    {!! $icon('file', 'h-6 w-6') !!}
                                                                </span>

                                                                <div>
                                                                    <h6 class="ui-title font-black">
                                                                        Vista previa aún no generada
                                                                    </h6>

                                                                    <p class="ui-muted mt-2 text-sm leading-6">
                                                                        Presiona “Vista previa” para revisar qué documentos se sugieren antes de aplicarlos.
                                                                        Esto evita reemplazar información por error.
                                                                    </p>
                                                                </div>
                                                            </div>

                                                            <button type="button" wire:click="previsualizarChecklist" class="ui-btn-primary">
                                                                Generar vista previa
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </article>

                                        {{-- RESUMEN DOCUMENTAL --}}
                                        <aside class="ui-panel">
                                            <div class="flex items-start justify-between gap-4">
                                                <div>
                                                    <p class="ui-kicker">Resumen documental</p>
                                                    <h5 class="ui-title mt-1 text-xl font-black">
                                                        {{ count($documentos) }} documento(s)
                                                    </h5>
                                                </div>

                                                @if (($panelDoc['completos'] ?? false))
                                                    <span class="ui-badge-success">Completo</span>
                                                @elseif (count($documentos) === 0)
                                                    <span class="ui-badge-muted">Sin checklist</span>
                                                @else
                                                    <span class="ui-badge-warning">Revisar</span>
                                                @endif
                                            </div>

                                            <div class="mt-5 grid grid-cols-2 gap-3">
                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Presentados</p>
                                                    <p class="ui-title mt-1 text-2xl font-black">
                                                        {{ $panelDoc['presentados'] ?? 0 }}
                                                    </p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Pendientes</p>
                                                    <p class="ui-title mt-1 text-2xl font-black">
                                                        {{ $panelDoc['pendientes'] ?? 0 }}
                                                    </p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">Observados</p>
                                                    <p class="ui-title mt-1 text-2xl font-black">
                                                        {{ $panelDoc['observados'] ?? 0 }}
                                                    </p>
                                                </div>

                                                <div class="ui-card-soft p-4">
                                                    <p class="ui-muted text-xs">No aplica</p>
                                                    <p class="ui-title mt-1 text-2xl font-black">
                                                        {{ $panelDoc['no_aplica'] ?? 0 }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mt-5">
                                                @if (($panelDoc['pendientes'] ?? 0) > 0 || ($panelDoc['observados'] ?? 0) > 0)
                                                    <div class="ui-alert-warning">
                                                        Existen documentos pendientes u observados. La inscripción puede continuar con seguimiento.
                                                    </div>
                                                @elseif (count($documentos) > 0)
                                                    <div class="ui-alert-success">
                                                        No se detectan pendientes documentales críticos.
                                                    </div>
                                                @else
                                                    <div class="ui-alert-info">
                                                        Aún no se generó checklist documental.
                                                    </div>
                                                @endif
                                            </div>
                                        </aside>
                                    </section>

                                    {{-- BARRA DE ACCIONES DOCUMENTALES --}}
                                    <section class="ui-panel">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                            <div>
                                                <p class="ui-kicker">Acciones documentales</p>
                                                <h5 class="ui-title mt-1 text-xl font-black">
                                                    Gestionar checklist
                                                </h5>
                                                <p class="ui-muted mt-1 text-sm">
                                                    Puedes agregar documentos manuales o generar la checklist recomendada sin perder información.
                                                </p>
                                            </div>

                                            <div class="flex flex-wrap gap-2">
                                                <button type="button" wire:click="solicitarChecklistBase" class="ui-btn-primary">
                                                    {!! $icon('file', 'h-4 w-4') !!}
                                                    Checklist base
                                                </button>

                                                <button type="button" wire:click="aplicarChecklistAgregarFaltantes" class="ui-btn-secondary">
                                                    {!! $icon('plus', 'h-4 w-4') !!}
                                                    Agregar faltantes
                                                </button>

                                                <button type="button" wire:click="agregarDocumentoManual" class="ui-btn-secondary">
                                                    {!! $icon('plus', 'h-4 w-4') !!}
                                                    Documento manual
                                                </button>
                                            </div>
                                        </div>
                                    </section>

                                    {{-- LISTADO EDITABLE DE DOCUMENTOS --}}
                                    <section class="ui-panel">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                                            <div>
                                                <p class="ui-kicker">Checklist actual</p>
                                                <h5 class="ui-title mt-1 text-xl font-black">
                                                    Documentos de la inscripción
                                                </h5>
                                            </div>

                                            <span class="ui-badge-muted">
                                                {{ count($documentos) }} registro(s)
                                            </span>
                                        </div>

                                        <div class="mt-5 space-y-3">
                                            @forelse ($documentos as $index => $documento)
                                                @php
                                                    $estadoDoc = $documento['est_die'] ?? 'PENDIENTE';
                                                @endphp

                                                <article class="ui-card-soft savp-doc-card p-4">
                                                    <div class="grid gap-4 xl:grid-cols-[1.15fr_170px_1fr_auto] xl:items-start">
                                                        {{-- NOMBRE --}}
                                                        <div>
                                                            <label class="ui-label">Documento</label>
                                                            <input
                                                                type="text"
                                                                wire:model.live.debounce.800ms="documentos.{{ $index }}.nom_die"
                                                                class="ui-input"
                                                                placeholder="Nombre del documento"
                                                            >

                                                            <div class="mt-2 flex flex-wrap gap-2">
                                                                <span class="{{ $this->badgeDocumento($estadoDoc) }}">
                                                                    {{ ucfirst(strtolower($estadoDoc)) }}
                                                                </span>

                                                                @if (($documento['obligatorio'] ?? false))
                                                                    <span class="ui-badge-warning">Obligatorio</span>
                                                                @else
                                                                    <span class="ui-badge-muted">Complementario</span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        {{-- ESTADO --}}
                                                        <div>
                                                            <label class="ui-label">Estado</label>
                                                            <select wire:model.live="documentos.{{ $index }}.est_die" class="ui-select">
                                                                @foreach ($catalogos['estados_documento'] ?? [] as $estado)
                                                                    <option value="{{ $estado }}">{{ ucfirst(strtolower($estado)) }}</option>
                                                                @endforeach
                                                            </select>

                                                            <div class="mt-2 grid grid-cols-2 gap-2">
                                                                <button
                                                                    type="button"
                                                                    wire:click="marcarDocumento({{ $index }}, 'PRESENTADO')"
                                                                    class="savp-doc-action {{ $estadoDoc === 'PRESENTADO' ? 'savp-doc-action-active' : '' }}"
                                                                >
                                                                    Presentado
                                                                </button>

                                                                <button
                                                                    type="button"
                                                                    wire:click="marcarDocumento({{ $index }}, 'PENDIENTE')"
                                                                    class="savp-doc-action {{ $estadoDoc === 'PENDIENTE' ? 'savp-doc-action-active' : '' }}"
                                                                >
                                                                    Pendiente
                                                                </button>

                                                                <button
                                                                    type="button"
                                                                    wire:click="marcarDocumento({{ $index }}, 'OBSERVADO')"
                                                                    class="savp-doc-action {{ $estadoDoc === 'OBSERVADO' ? 'savp-doc-action-active' : '' }}"
                                                                >
                                                                    Observado
                                                                </button>

                                                                <button
                                                                    type="button"
                                                                    wire:click="marcarDocumento({{ $index }}, 'NO_APLICA')"
                                                                    class="savp-doc-action {{ $estadoDoc === 'NO_APLICA' ? 'savp-doc-action-active' : '' }}"
                                                                >
                                                                    No aplica
                                                                </button>
                                                            </div>
                                                        </div>

                                                        {{-- OBSERVACIÓN --}}
                                                        <div>
                                                            <label class="ui-label">Observación</label>
                                                            <textarea
                                                                wire:model.live.debounce.800ms="documentos.{{ $index }}.obs_die"
                                                                rows="3"
                                                                class="ui-textarea"
                                                                placeholder="Observación documental"
                                                            ></textarea>

                                                            @if (($estadoDoc ?? '') === 'PRESENTADO')
                                                                <p class="ui-muted mt-2 text-xs">
                                                                    Fecha: {{ $documento['fec_pre_die'] ?? now()->toDateString() }}
                                                                </p>
                                                            @endif
                                                        </div>

                                                        {{-- ACCIONES --}}
                                                        <div class="flex xl:justify-end">
                                                            <button
                                                                type="button"
                                                                wire:click="quitarDocumento({{ $index }})"
                                                                class="ui-icon-btn"
                                                                title="Quitar documento"
                                                            >
                                                                {!! $icon('trash', 'h-4 w-4') !!}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </article>
                                            @empty
                                                <div class="ui-card-soft p-8 text-center">
                                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl ui-badge-muted">
                                                        {!! $icon('file', 'h-8 w-8') !!}
                                                    </div>

                                                    <h5 class="ui-title mt-4 font-black">
                                                        No existe checklist documental
                                                    </h5>

                                                    <p class="ui-muted mt-2 text-sm">
                                                        Genera una checklist base o agrega documentos manualmente para controlar la inscripción.
                                                    </p>

                                                    <div class="mt-5 flex flex-wrap justify-center gap-2">
                                                        <button type="button" wire:click="solicitarChecklistBase" class="ui-btn-primary">
                                                            Generar checklist
                                                        </button>

                                                        <button type="button" wire:click="agregarDocumentoManual" class="ui-btn-secondary">
                                                            Agregar manual
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>
                                    </section>

                                    {{-- RECOMENDACIONES DOCUMENTALES --}}
                                    <section class="grid gap-5 xl:grid-cols-3">
                                        <article class="ui-panel">
                                            <p class="ui-kicker">Bloqueos</p>
                                            <div class="mt-4 space-y-2">
                                                @forelse (($analisis['bloqueos'] ?? []) as $mensaje)
                                                    <div class="ui-alert-danger">{{ $mensaje }}</div>
                                                @empty
                                                    <div class="ui-alert-success">No existen bloqueos documentales críticos.</div>
                                                @endforelse
                                            </div>
                                        </article>

                                        <article class="ui-panel">
                                            <p class="ui-kicker">Observaciones</p>
                                            <div class="mt-4 space-y-2">
                                                @forelse (($analisis['documentos']['advertencias'] ?? []) as $mensaje)
                                                    <div class="ui-alert-warning">{{ $mensaje }}</div>
                                                @empty
                                                    <div class="ui-alert-success">No existen observaciones documentales pendientes.</div>
                                                @endforelse
                                            </div>
                                        </article>

                                        <article class="ui-panel">
                                            <p class="ui-kicker">Sugerencias</p>
                                            <div class="mt-4 space-y-2">
                                                @forelse (($analisis['documentos']['sugerencias'] ?? []) as $mensaje)
                                                    <div class="ui-alert-info">{{ $mensaje }}</div>
                                                @empty
                                                    <div class="ui-alert-info">Genera una checklist para obtener recomendaciones documentales.</div>
                                                @endforelse
                                            </div>
                                        </article>
                                    </section>
                                </div>
                            @endif

                            {{-- PASO 5: REVISIÓN INSTITUCIONAL --}}
                            @if ($pasoInscripcion === 5)
                                <div class="space-y-5">
                                    {{-- CABECERA --}}
                                    <section class="ui-panel">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="ui-badge-success">Paso 5</span>
                                                    <span class="ui-badge-muted">Revisión institucional</span>

                                                    <span class="{{ $this->badgeRevision($estadoRevision) }}">
                                                        {{ $estadoRevision }}
                                                    </span>

                                                    <span class="{{ $this->badgeEstado($analisis['estado_sugerido'] ?? 'PENDIENTE') }}">
                                                        {{ $analisis['estado_sugerido'] ?? 'PENDIENTE' }}
                                                    </span>
                                                </div>

                                                <h4 class="ui-title mt-3 text-2xl font-black">
                                                    Revisión antes de confirmar
                                                </h4>

                                                <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">
                                                    Verifica la asignación académica, cupo, documentación, turno y especialidad técnica antes de guardar.
                                                    Si existen observaciones, la inscripción puede continuar con seguimiento institucional.
                                                </p>
                                            </div>

                                            <div class="ui-card-soft min-w-[270px] p-4">
                                                <p class="ui-muted text-xs">Resultado actual</p>
                                                <p class="ui-title mt-1 text-lg font-black">
                                                    {{ $analisis['mensaje'] ?? 'Completa los datos para revisar' }}
                                                </p>

                                                <div class="mt-3 flex flex-wrap gap-2">
                                                    <span class="{{ $this->badgeRevision($estadoRevision) }}">
                                                        Riesgo: {{ $analisis['nivel_riesgo'] ?? 'SIN_RIESGO' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    {{-- RESUMEN EJECUTIVO --}}
                                    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                                        <article class="ui-card-soft p-5">
                                            <p class="ui-muted text-xs">Estado sugerido</p>
                                            <p class="ui-title mt-2 text-xl font-black">
                                                {{ $analisis['estado_sugerido'] ?? 'PENDIENTE' }}
                                            </p>
                                            <p class="ui-muted mt-2 text-xs">Resultado recomendado para guardar.</p>
                                        </article>

                                        <article class="ui-card-soft p-5">
                                            <p class="ui-muted text-xs">Condición</p>
                                            <p class="ui-title mt-2 text-xl font-black">
                                                {{ $analisis['condicion_sugerida'] ?? 'NORMAL' }}
                                            </p>
                                            <p class="ui-muted mt-2 text-xs">Clasificación administrativa.</p>
                                        </article>

                                        <article class="ui-card-soft p-5">
                                            <p class="ui-muted text-xs">Cupo</p>
                                            <p class="ui-title mt-2 text-xl font-black">
                                                {{ $cupo['disponibles'] ?? 0 }} disponibles
                                            </p>
                                            <p class="ui-muted mt-2 text-xs">
                                                {{ $cupo['inscritos'] ?? 0 }}/{{ $cupo['capacidad'] ?? 35 }} ocupados.
                                            </p>
                                        </article>

                                        <article class="ui-card-soft p-5">
                                            <p class="ui-muted text-xs">Documentos</p>
                                            <p class="ui-title mt-2 text-xl font-black">
                                                {{ $analisis['documentos']['pendientes'] ?? 0 }} pendientes
                                            </p>
                                            <p class="ui-muted mt-2 text-xs">
                                                {{ $analisis['documentos']['observados'] ?? 0 }} observados.
                                            </p>
                                        </article>

                                        <article class="ui-card-soft p-5">
                                            <p class="ui-muted text-xs">Especialidad BTH</p>
                                            <p class="ui-title mt-2 text-xl font-black">
                                                {{ $especialidadRevision['estado_sugerido'] ?? ($formInscripcion['est_esp_tec_ins'] ?? 'NO_APLICA') }}
                                            </p>
                                            <p class="ui-muted mt-2 text-xs">Opcional desde 4to.</p>
                                        </article>
                                    </section>

                                    {{-- VISTA PREVIA DE ASIGNACIÓN --}}
                                    <section class="grid gap-5 xl:grid-cols-[1fr_410px]">
                                        <article class="ui-panel savp-review-main">
                                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                                <div>
                                                    <p class="ui-kicker">Vista previa de inscripción</p>
                                                    <h5 class="ui-title mt-1 text-xl font-black">
                                                        Designación académica final
                                                    </h5>

                                                    <p class="ui-muted mt-1 text-sm">
                                                        Esta es la información que quedará registrada para la gestión seleccionada.
                                                    </p>
                                                </div>

                                                <button type="button" wire:click="aplicarEstadoSugerido" class="ui-btn-secondary">
                                                    Aplicar estado sugerido
                                                </button>
                                            </div>

                                            <div class="mt-6 grid gap-4 md:grid-cols-2">
                                                <div class="savp-preview-block">
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
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="savp-preview-block">
                                                    <div class="flex items-start gap-3">
                                                        <span class="ui-badge-muted p-2">
                                                            {!! $icon('calendar', 'h-4 w-4') !!}
                                                        </span>

                                                        <div>
                                                            <p class="ui-muted text-xs">Gestión y fecha</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ $gestionTrabajo['anio'] ?? 'Sin gestión' }}
                                                            </p>
                                                            <p class="ui-muted mt-1 text-xs">
                                                                Fecha: {{ $formInscripcion['fei_ins'] ?? now()->toDateString() }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="savp-preview-block">
                                                    <div class="flex items-start gap-3">
                                                        <span class="ui-badge-success p-2">
                                                            {!! $icon('book', 'h-4 w-4') !!}
                                                        </span>

                                                        <div>
                                                            <p class="ui-muted text-xs">Curso y paralelo</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ collect($catalogos['cursos'] ?? [])->firstWhere('cod_cur', $formInscripcion['cod_cur'] ?? '')['nombre'] ?? 'Curso no seleccionado' }}
                                                                ·
                                                                {{ collect($catalogos['paralelos'] ?? [])->firstWhere('cod_par', $formInscripcion['cod_par'] ?? '')['nombre'] ?? 'Paralelo no seleccionado' }}
                                                            </p>
                                                            <p class="ui-muted mt-1 text-xs">
                                                                Comparación edad/curso: {{ $analisis['curso_edad']['estado'] ?? 'SIN_DATOS' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="savp-preview-block">
                                                    <div class="flex items-start gap-3">
                                                        <span class="{{ $turnoMananaAplicado ? 'ui-badge-success' : 'ui-badge-warning' }} p-2">
                                                            {!! $icon('clock', 'h-4 w-4') !!}
                                                        </span>

                                                        <div>
                                                            <p class="ui-muted text-xs">Turno</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ collect($catalogos['turnos'] ?? [])->firstWhere('cod_tur', $formInscripcion['cod_tur'] ?? '')['nombre'] ?? 'Turno no seleccionado' }}
                                                            </p>
                                                            <p class="ui-muted mt-1 text-xs">
                                                                {{ $turnoMananaAplicado ? 'Cumple la regla de turno Mañana.' : 'No coincide con turno Mañana; quedará observado.' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="savp-preview-block">
                                                    <div class="flex items-start gap-3">
                                                        <span class="{{ $this->badgeEspecialidad($formInscripcion['est_esp_tec_ins'] ?? 'NO_APLICA') }} p-2">
                                                            {!! $icon('star', 'h-4 w-4') !!}
                                                        </span>

                                                        <div>
                                                            <p class="ui-muted text-xs">Especialidad técnica</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                @php
                                                                    $espSeleccionada = collect($catalogos['especialidades_tecnicas'] ?? [])->firstWhere('cod_esp_tec', $formInscripcion['cod_esp_tec'] ?? '');
                                                                @endphp

                                                                {{ $espSeleccionada['nombre'] ?? ($formInscripcion['est_esp_tec_ins'] ?? 'NO_APLICA') }}
                                                            </p>
                                                            <p class="ui-muted mt-1 text-xs">
                                                                {{ $especialidadRevision['mensaje'] ?? 'Especialidad evaluada según el curso seleccionado.' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="savp-preview-block">
                                                    <div class="flex items-start gap-3">
                                                        <span class="{{ $this->badgeEstado($analisis['estado_sugerido'] ?? 'PENDIENTE') }} p-2">
                                                            {!! $icon('shield', 'h-4 w-4') !!}
                                                        </span>

                                                        <div>
                                                            <p class="ui-muted text-xs">Estado final sugerido</p>
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ $analisis['estado_sugerido'] ?? 'PENDIENTE' }}
                                                            </p>
                                                            <p class="ui-muted mt-1 text-xs">
                                                                Condición: {{ $analisis['condicion_sugerida'] ?? 'NORMAL' }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </article>

                                        {{-- PANEL DE CUPOS Y DOCUMENTOS --}}
                                        <aside class="space-y-5">
                                            <article class="ui-panel">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div>
                                                        <p class="ui-kicker">Cupo</p>
                                                        <h5 class="ui-title mt-1 text-xl font-black">
                                                            {{ $cupo['estado'] ?? 'DISPONIBLE' }}
                                                        </h5>
                                                    </div>

                                                    <span class="{{ $this->badgeCupo($cupo['estado'] ?? 'DISPONIBLE') }}">
                                                        {{ $cupo['porcentaje'] ?? 0 }}%
                                                    </span>
                                                </div>

                                                <div class="mt-4 grid grid-cols-3 gap-3">
                                                    <div class="ui-card-soft p-3">
                                                        <p class="ui-muted text-xs">Capacidad</p>
                                                        <p class="ui-title font-black">{{ $cupo['capacidad'] ?? 35 }}</p>
                                                    </div>

                                                    <div class="ui-card-soft p-3">
                                                        <p class="ui-muted text-xs">Inscritos</p>
                                                        <p class="ui-title font-black">{{ $cupo['inscritos'] ?? 0 }}</p>
                                                    </div>

                                                    <div class="ui-card-soft p-3">
                                                        <p class="ui-muted text-xs">Libre</p>
                                                        <p class="ui-title font-black">{{ $cupo['disponibles'] ?? 0 }}</p>
                                                    </div>
                                                </div>

                                                <div class="mt-4 h-3 overflow-hidden rounded-full" style="background: var(--ui-surface-muted);">
                                                    <div class="savp-progress h-full rounded-full" style="width: {{ min(100, max(0, $cupo['porcentaje'] ?? 0)) }}%;"></div>
                                                </div>
                                            </article>

                                            <article class="ui-panel">
                                                <p class="ui-kicker">Documentación</p>
                                                <h5 class="ui-title mt-1 text-xl font-black">
                                                    {{ count($documentos) }} documento(s)
                                                </h5>

                                                <div class="mt-4 grid grid-cols-2 gap-3">
                                                    <div class="ui-card-soft p-3">
                                                        <p class="ui-muted text-xs">Presentados</p>
                                                        <p class="ui-title font-black">{{ $analisis['documentos']['presentados'] ?? 0 }}</p>
                                                    </div>

                                                    <div class="ui-card-soft p-3">
                                                        <p class="ui-muted text-xs">Pendientes</p>
                                                        <p class="ui-title font-black">{{ $analisis['documentos']['pendientes'] ?? 0 }}</p>
                                                    </div>

                                                    <div class="ui-card-soft p-3">
                                                        <p class="ui-muted text-xs">Observados</p>
                                                        <p class="ui-title font-black">{{ $analisis['documentos']['observados'] ?? 0 }}</p>
                                                    </div>

                                                    <div class="ui-card-soft p-3">
                                                        <p class="ui-muted text-xs">No aplica</p>
                                                        <p class="ui-title font-black">{{ $analisis['documentos']['no_aplica'] ?? 0 }}</p>
                                                    </div>
                                                </div>
                                            </article>
                                        </aside>
                                    </section>

                                    {{-- BLOQUEOS / OBSERVACIONES / SUGERENCIAS --}}
                                    <section class="grid gap-5 xl:grid-cols-3">
                                        <article class="ui-panel">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="ui-kicker">Bloqueos</p>
                                                    <h5 class="ui-title mt-1 font-black">
                                                        {{ count($analisis['bloqueos'] ?? []) }} detectado(s)
                                                    </h5>
                                                </div>

                                                <span class="{{ count($analisis['bloqueos'] ?? []) > 0 ? 'ui-badge-danger' : 'ui-badge-success' }}">
                                                    {{ count($analisis['bloqueos'] ?? []) > 0 ? 'Corregir' : 'Correcto' }}
                                                </span>
                                            </div>

                                            <div class="mt-4 space-y-2">
                                                @forelse ($analisis['bloqueos'] ?? [] as $mensaje)
                                                    <div class="ui-alert-danger">
                                                        {{ $mensaje }}
                                                    </div>
                                                @empty
                                                    <div class="ui-alert-success">
                                                        No existen bloqueos críticos para continuar.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </article>

                                        <article class="ui-panel">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="ui-kicker">Observaciones</p>
                                                    <h5 class="ui-title mt-1 font-black">
                                                        {{ count($analisis['advertencias'] ?? []) }} registrada(s)
                                                    </h5>
                                                </div>

                                                <span class="{{ count($analisis['advertencias'] ?? []) > 0 ? 'ui-badge-warning' : 'ui-badge-success' }}">
                                                    {{ count($analisis['advertencias'] ?? []) > 0 ? 'Seguimiento' : 'Sin observaciones' }}
                                                </span>
                                            </div>

                                            <div class="mt-4 space-y-2">
                                                @forelse ($analisis['advertencias'] ?? [] as $mensaje)
                                                    <div class="ui-alert-warning">
                                                        {{ $mensaje }}
                                                    </div>
                                                @empty
                                                    <div class="ui-alert-success">
                                                        No existen observaciones pendientes.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </article>

                                        <article class="ui-panel">
                                            <div class="flex items-center justify-between gap-3">
                                                <div>
                                                    <p class="ui-kicker">Sugerencias</p>
                                                    <h5 class="ui-title mt-1 font-black">
                                                        {{ count($analisis['sugerencias'] ?? []) }} recomendación(es)
                                                    </h5>
                                                </div>

                                                <span class="ui-badge-muted">Apoyo</span>
                                            </div>

                                            <div class="mt-4 space-y-2">
                                                @forelse ($analisis['sugerencias'] ?? [] as $mensaje)
                                                    <div class="ui-alert-info">
                                                        {{ $mensaje }}
                                                    </div>
                                                @empty
                                                    <div class="ui-alert-info">
                                                        Completa el formulario para obtener recomendaciones.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </article>
                                    </section>

                                    {{-- OBSERVACIÓN FINAL ANTES DE CONFIRMAR --}}
                                    <section class="ui-panel">
                                        <p class="ui-kicker">Respaldo final</p>
                                        <h5 class="ui-title mt-1 text-xl font-black">
                                            Observación o justificación institucional
                                        </h5>

                                        <p class="ui-muted mt-1 text-sm">
                                            Si existen observaciones, sobrecupo, traslado, especialidad pendiente o documentación incompleta,
                                            registra una nota breve para que la bitácora y el historial sean comprensibles.
                                        </p>

                                        <div class="mt-5">
                                            <textarea
                                                wire:model.live.debounce.900ms="formInscripcion.mot_obs_ins"
                                                rows="4"
                                                class="ui-textarea"
                                                placeholder="Ejemplo: inscripción observada por documentos pendientes y especialidad técnica aún no definida."
                                            ></textarea>

                                            @error('formInscripcion.mot_obs_ins')
                                                <p class="ui-error">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </section>
                                </div>
                            @endif

                            {{-- PASO 6: CONFIRMACIÓN FINAL --}}
                            @if ($pasoInscripcion === 6)
                                <div class="space-y-5">
                                    {{-- CABECERA --}}
                                    <section class="ui-panel savp-confirm-panel">
                                        <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="ui-badge-success">Paso 6</span>
                                                    <span class="ui-badge-muted">Confirmación final</span>

                                                    <span class="{{ $puedeConfirmar ? 'ui-badge-success' : 'ui-badge-danger' }}">
                                                        {{ $puedeConfirmar ? 'Puede guardarse' : 'Requiere corrección' }}
                                                    </span>
                                                </div>

                                                <h4 class="ui-title mt-3 text-2xl font-black">
                                                    Confirmar inscripción
                                                </h4>

                                                <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">
                                                    Revisa el resumen final. Al guardar, se registrará la inscripción, sus documentos,
                                                    estado, condición, especialidad técnica si corresponde y trazabilidad institucional.
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
                                                                · Edad: {{ $estudianteSeleccionado['edad'] ? $estudianteSeleccionado['edad'].' años' : 'Sin edad' }}
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
                                                            <p class="ui-title mt-1 font-black">
                                                                {{ count($documentos) }} documento(s) en checklist
                                                            </p>

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
                                                            <span class="ui-badge-muted">Sin checklist</span>
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
                                                <p class="ui-kicker">Acciones disponibles</p>

                                                <div class="mt-4 space-y-3">
                                                    <button type="button" wire:click="guardarPendiente" class="ui-btn-secondary w-full justify-center">
                                                        Guardar pendiente
                                                    </button>

                                                    <button
                                                        type="button"
                                                        wire:click="confirmarInscripcion"
                                                        @disabled(! $puedeConfirmar)
                                                        class="{{ $puedeConfirmar ? 'ui-btn-primary' : 'ui-btn-secondary' }} w-full justify-center"
                                                    >
                                                        {{ $estadoConfirmacion }}
                                                    </button>

                                                    <button
                                                        type="button"
                                                        wire:click="confirmarEImprimir"
                                                        @disabled(! $puedeConfirmar)
                                                        class="{{ $puedeConfirmar ? 'ui-btn-primary' : 'ui-btn-secondary' }} w-full justify-center"
                                                    >
                                                        Confirmar e imprimir constancia
                                                    </button>
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

                        {{-- RESUMEN LATERAL --}}
                        <aside class="space-y-4">
                            <div class="ui-panel">
                                <p class="ui-kicker">Resumen</p>
                                <h4 class="ui-title mt-1 font-black">
                                    {{ $estudianteSeleccionado['nombre_completo'] ?? 'Sin estudiante seleccionado' }}
                                </h4>

                                <div class="mt-4 space-y-3">
                                    <div class="ui-card-soft p-3">
                                        <p class="ui-muted text-xs">Turno</p>
                                        <p class="ui-title font-black">
                                            {{ $turnoMananaAplicado ? 'Mañana aplicado' : 'Revisar turno' }}
                                        </p>
                                    </div>

                                    <div class="ui-card-soft p-3">
                                        <p class="ui-muted text-xs">Cupo</p>
                                        <p class="ui-title font-black">{{ $cupo['inscritos'] ?? 0 }}/{{ $cupo['capacidad'] ?? 35 }}</p>
                                    </div>

                                    <div class="ui-card-soft p-3">
                                        <p class="ui-muted text-xs">Documentos pendientes</p>
                                        <p class="ui-title font-black">{{ $analisis['documentos']['pendientes'] ?? 0 }}</p>
                                    </div>

                                    <div class="ui-card-soft p-3">
                                        <p class="ui-muted text-xs">Especialidad</p>
                                        <p class="ui-title font-black">{{ $formInscripcion['est_esp_tec_ins'] ?? 'NO_APLICA' }}</p>
                                    </div>

                                    <div class="ui-card-soft p-3">
                                        <p class="ui-muted text-xs">Confirmación</p>
                                        <p class="ui-title font-black">{{ $estadoConfirmacion }}</p>
                                    </div>
                                </div>
                            </div>
                        </aside>
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

                        @if ($pasoInscripcion < 6)
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
                                @disabled(! $puedeConfirmar)
                                class="{{ $puedeConfirmar ? 'ui-btn-primary' : 'ui-btn-secondary' }}"
                            >
                                {{ $estadoConfirmacion }}
                            </button>

                            <button
                                type="button"
                                wire:click="confirmarEImprimir"
                                @disabled(! $puedeConfirmar)
                                class="{{ $puedeConfirmar ? 'ui-btn-primary' : 'ui-btn-secondary' }}"
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
            <div class="ui-modal-backdrop savp-modal-backdrop"></div>
            <div class="ui-modal savp-modal-shell w-full max-w-3xl overflow-hidden">
                <div class="ui-modal-header flex items-start justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Checklist documental</p>
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
                        <h4 class="ui-title mt-4 font-black">Reemplazar checklist</h4>
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
            <div class="ui-modal-backdrop savp-modal-backdrop"></div>
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

    @if ($modalDocumentos)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop savp-modal-backdrop"></div>
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
                            <div class="ui-card-soft grid gap-3 p-4 xl:grid-cols-[1fr_160px_1fr_160px_auto] xl:items-center">
                                <input type="text" wire:model.live.debounce.800ms="documentosModal.{{ $index }}.nom_die" class="ui-input" placeholder="Nombre del documento">

                                <select wire:model.live="documentosModal.{{ $index }}.est_die" class="ui-select">
                                    @foreach ($catalogos['estados_documento'] ?? [] as $estado)
                                        <option value="{{ $estado }}">{{ ucfirst(strtolower($estado)) }}</option>
                                    @endforeach
                                </select>

                                <input type="text" wire:model.live.debounce.800ms="documentosModal.{{ $index }}.obs_die" class="ui-input" placeholder="Observación">

                                <input type="date" wire:model.live="documentosModal.{{ $index }}.fec_pre_die" class="ui-input">

                                <button type="button" wire:click="quitarDocumentoEnModal({{ $index }})" class="ui-icon-btn">
                                    {!! $icon('trash', 'h-4 w-4') !!}
                                </button>
                            </div>
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
            <div class="ui-modal-backdrop savp-modal-backdrop"></div>
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
            <div class="ui-modal-backdrop savp-modal-backdrop"></div>
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
            z-index: 80;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 1rem;
            overflow-y: auto;
        }

        .savp-modal-backdrop {
            animation: savpBackdrop .24s ease-out both;
        }

        .savp-modal-shell {
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