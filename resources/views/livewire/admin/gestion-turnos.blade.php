@php
    $icon = function (string $name, string $class = 'h-5 w-5') {
        return match ($name) {
            'plus' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14m7-7H5"/></svg>',
            'calendar' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1Z"/></svg>',
            'clock' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 1 1-20 0 10 10 0 0 1 20 0Z"/></svg>',
            'layers' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m12 3 9 5-9 5-9-5 9-5Zm-7 9 7 4 7-4M5 16l7 4 7-4"/></svg>',
            'table' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16M8 6v12m8-12v12"/></svg>',
            'grid' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h7v7H4V4Zm9 0h7v7h-7V4ZM4 13h7v7H4v-7Zm9 0h7v7h-7v-7Z"/></svg>',
            'timeline' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 4v16m12-16v16M6 8h12M6 16h12M9 8v8m6-8v8"/></svg>',
            'eye' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7 9.75-7 9.75 7 9.75 7-3.75 7-9.75 7-9.75-7-9.75-7Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>',
            'edit' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.65-1.65a2.1 2.1 0 0 1 2.97 2.97l-9.9 9.9-4.2 1.05 1.05-4.2 8.43-8.07Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h5"/></svg>',
            'trash' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12m-10 0V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m-9 0 1 14h8l1-14"/></svg>',
            'restore' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 10a7 7 0 0 1 12-4m2 8a7 7 0 0 1-12 4"/></svg>',
            'pdf' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M7 3h7l5 5v13H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 3v5h5M8 16h8M8 12h3"/></svg>',
            'warning' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/></svg>',
            'check' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>',
            'search' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M10.5 18a7.5 7.5 0 1 1 0-15 7.5 7.5 0 0 1 0 15Z"/></svg>',
            'filter' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h18l-7 8v5l-4 2v-7L3 5Z"/></svg>',
            'snow' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M4.2 7.5l15.6 9M19.8 7.5l-15.6 9M8 5.3 12 8l4-2.7M8 18.7 12 16l4 2.7M4.8 10.3 9 12l-4.2 1.7M19.2 10.3 15 12l4.2 1.7"/></svg>',
            'building' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M16 8h2a2 2 0 0 1 2 2v11M8 7h4M8 11h4M8 15h4M7 21h10"/></svg>',
            'x' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18"/></svg>',
            'bolt' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="m13 2-9 13h7l-1 7 10-14h-7l0-6Z"/></svg>',
            'copy' => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 8h11v11H8z"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 16H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v1"/></svg>',
            default => '<svg class="'.$class.'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2"/></svg>',
        };
    };

    $badgeEstado = function (?string $estado) {
        return $estado === 'ACTIVO' ? 'ui-badge-success' : 'ui-badge-danger';
    };

    $badgeTipo = function (?string $tipo) {
        return match ($tipo) {
            'REGULAR' => 'ui-badge-success',
            'INVIERNO' => 'ui-badge-violet',
            'AJUSTE' => 'ui-badge-warning',
            'EMERGENCIA' => 'ui-badge-danger',
            default => 'ui-badge-muted',
        };
    };

    $badgeRevision = function (?string $estado) {
        return match ($estado) {
            'VALIDO' => 'ui-badge-success',
            'CORREGIBLE', 'OBSERVADO', 'ADVERTENCIA' => 'ui-badge-warning',
            'BLOQUEADO' => 'ui-badge-danger',
            default => 'ui-badge-muted',
        };
    };

    $resumenCards = [
        [
            'titulo' => 'Turnos activos',
            'valor' => $resumen['turnos_activos'] ?? 0,
            'detalle' => 'Jornadas habilitadas',
            'icono' => 'calendar',
            'badge' => 'ui-badge-success',
        ],
        [
            'titulo' => 'Plantillas regulares',
            'valor' => $resumen['plantillas_regulares'] ?? 0,
            'detalle' => 'Base anual por turno',
            'icono' => 'layers',
            'badge' => 'ui-badge-success',
        ],
        [
            'titulo' => 'Plantillas invierno',
            'valor' => $resumen['plantillas_invierno'] ?? 0,
            'detalle' => 'Reemplazo temporal',
            'icono' => 'snow',
            'badge' => 'ui-badge-violet',
        ],
        [
            'titulo' => 'Bloques horarios',
            'valor' => $resumen['bloques_horarios'] ?? 0,
            'detalle' => 'Periodos configurados',
            'icono' => 'clock',
            'badge' => 'ui-badge-info',
        ],
        [
            'titulo' => 'Sin plantilla',
            'valor' => $resumen['bloques_sin_plantilla'] ?? 0,
            'detalle' => 'Requieren asociación',
            'icono' => 'warning',
            'badge' => (($resumen['bloques_sin_plantilla'] ?? 0) > 0) ? 'ui-badge-warning' : 'ui-badge-success',
        ],
        [
            'titulo' => 'Estructura',
            'valor' => ($resumen['estructura_corregible'] ?? false) ? 'Revisar' : 'Estable',
            'detalle' => 'Control del módulo',
            'icono' => ($resumen['estructura_corregible'] ?? false) ? 'warning' : 'check',
            'badge' => ($resumen['estructura_corregible'] ?? false) ? 'ui-badge-warning' : 'ui-badge-success',
        ],
    ];

    $vistas = [
        'jornada' => ['Jornada', 'grid'],
        'tabla' => ['Tabla', 'table'],
        'plantillas' => ['Plantillas', 'layers'],
        'agenda' => ['Agenda', 'timeline'],
        'bloques' => ['Bloques', 'clock'],
        'compacta' => ['Compacta', 'calendar'],
    ];

    $puedeCorregir = (bool) ($auditoria['resumen']['puede_corregir'] ?? false);
    $puedeGuardarTurno = (bool) ($analisisTurno['puede_continuar'] ?? false);
    $puedeGuardarPlantilla = (bool) ($analisisPlantilla['puede_continuar'] ?? false);
    $puedeGuardarBloque = (bool) ($analisisBloque['puede_continuar'] ?? false);
@endphp

<div
    x-data="gestionTurnosUI()"
    x-init="init()"
    class="ui-page relative overflow-hidden"
>
    <style>
        @keyframes savpFadeUp {
            from {
                opacity: 0;
                transform: translateY(14px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes savpPulseSoft {
            0%, 100% {
                opacity: .75;
                transform: scale(1);
            }

            50% {
                opacity: 1;
                transform: scale(1.015);
            }
        }

        .savp-appear {
            animation: savpFadeUp .32s ease-out both;
        }

        .savp-appear-1 {
            animation-delay: .04s;
        }

        .savp-appear-2 {
            animation-delay: .08s;
        }

        .savp-appear-3 {
            animation-delay: .12s;
        }

        .savp-soft-pulse {
            animation: savpPulseSoft 3.8s ease-in-out infinite;
        }

        .savp-shell {
            background:
                radial-gradient(circle at top left, var(--ui-primary-soft), transparent 34%),
                radial-gradient(circle at top right, var(--ui-violet-soft), transparent 30%),
                var(--ui-bg);
        }

        .savp-glass {
            background: color-mix(in srgb, var(--ui-surface) 88%, transparent);
        }

        .savp-action-tile {
            border-color: var(--ui-border);
            background: var(--ui-surface);
            color: var(--ui-text);
            transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease, background 180ms ease;
        }

        .savp-action-tile:hover:not(:disabled) {
            transform: translateY(-2px);
            border-color: var(--ui-primary-border);
            box-shadow: var(--ui-shadow-md);
        }

        .savp-action-tile:disabled {
            opacity: .58;
            cursor: not-allowed;
        }

        .savp-modal-wrap {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding: 1rem;
            overflow-y: auto;
        }

        @media (min-width: 768px) {
            .savp-modal-wrap {
                align-items: center;
            }
        }

        .swal2-popup.savp-swal {
            border-radius: 1.6rem !important;
            border: 1px solid var(--ui-border) !important;
            background: var(--ui-surface) !important;
            color: var(--ui-text) !important;
            box-shadow: var(--ui-shadow-lg) !important;
        }

        .swal2-confirm.savp-confirm {
            border-radius: 1rem !important;
            background: var(--ui-primary) !important;
            color: #ffffff !important;
            font-weight: 800 !important;
            padding: .75rem 1.25rem !important;
        }

        .swal2-cancel.savp-cancel {
            border-radius: 1rem !important;
            font-weight: 800 !important;
            padding: .75rem 1.25rem !important;
        }
    </style>

    <div class="savp-shell relative min-h-screen">
        <div class="relative mx-auto max-w-[1800px] px-4 py-6 sm:px-6 lg:px-8">
            {{-- ENCABEZADO --}}
            <section class="savp-appear grid gap-5 xl:grid-cols-[1fr_auto] xl:items-end">
                <div>
                    <div class="ui-badge-success">
                        {!! $icon('building', 'h-4 w-4') !!}
                        Organización académica institucional
                    </div>

                    <h1 class="ui-title mt-4 text-3xl font-black tracking-tight md:text-5xl">
                        Gestión de Turnos
                    </h1>

                    <p class="ui-subtitle mt-3 max-w-4xl text-sm leading-7">
                        Administra jornadas, plantillas regulares, horario de invierno y bloques académicos vinculados a la gestión activa o planificada.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2 xl:justify-end">
                    <button
                        type="button"
                        wire:click="auditarEstructuraHoraria"
                        class="ui-btn-secondary"
                    >
                        {!! $icon('warning', 'h-5 w-5') !!}
                        Auditar estructura
                    </button>

                    <button
                        type="button"
                        wire:click="corregirEstructuraHoraria"
                        wire:loading.attr="disabled"
                        wire:target="corregirEstructuraHoraria"
                        @disabled(! $puedeCorregir)
                        class="{{ $puedeCorregir ? 'ui-btn-primary' : 'ui-btn-secondary' }}"
                    >
                        {!! $icon('bolt', 'h-5 w-5') !!}
                        <span wire:loading.remove wire:target="corregirEstructuraHoraria">Corregir estructura</span>
                        <span wire:loading wire:target="corregirEstructuraHoraria">Corrigiendo...</span>
                    </button>

                    <button
                        type="button"
                        wire:click="abrirVistaPreviaAplicacion"
                        class="ui-btn-secondary"
                    >
                        {!! $icon('timeline', 'h-5 w-5') !!}
                        Vista previa
                    </button>
                </div>
            </section>

            {{-- HERO --}}
            <section class="savp-appear savp-appear-1 ui-card mt-6 overflow-hidden">
                <div class="grid gap-6 p-6 lg:grid-cols-[1.1fr_0.9fr] lg:p-8">
                    <div>
                        <div class="flex flex-wrap gap-2">
                            <span class="ui-badge-success">Regular + Invierno</span>
                            <span class="ui-badge-violet">Validación por gestión</span>
                            <span class="ui-badge-muted">Sin eliminación física</span>
                        </div>

                        <h2 class="ui-title mt-5 max-w-4xl text-2xl font-black tracking-tight md:text-4xl">
                            Control horario basado en plantillas
                        </h2>

                        <p class="ui-subtitle mt-4 max-w-3xl text-sm leading-7">
                            La plantilla regular funciona como base anual. La plantilla de invierno reemplaza temporalmente al horario regular dentro de su rango de vigencia. Los bloques deben estar asociados a una plantilla para evitar inconsistencias.
                        </p>

                        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                            <button
                                type="button"
                                wire:click="abrirModalCrearTurno"
                                class="savp-action-tile rounded-2xl border p-4 text-left"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-black">Registrar turno</span>
                                    {!! $icon('plus', 'h-5 w-5') !!}
                                </div>
                                <p class="ui-muted mt-2 text-xs leading-5">
                                    Jornada académica base.
                                </p>
                            </button>

                            <button
                                type="button"
                                wire:click="abrirModalCrearPlantilla(null, 'REGULAR')"
                                class="savp-action-tile rounded-2xl border p-4 text-left"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-black">Plantilla regular</span>
                                    {!! $icon('layers', 'h-5 w-5') !!}
                                </div>
                                <p class="ui-muted mt-2 text-xs leading-5">
                                    Base principal de la gestión.
                                </p>
                            </button>

                            <button
                                type="button"
                                wire:click="abrirModalCrearPlantilla(null, 'INVIERNO')"
                                class="savp-action-tile rounded-2xl border p-4 text-left"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-black">Plantilla invierno</span>
                                    {!! $icon('snow', 'h-5 w-5') !!}
                                </div>
                                <p class="ui-muted mt-2 text-xs leading-5">
                                    Reemplazo temporal.
                                </p>
                            </button>

                            <button
                                type="button"
                                wire:click="abrirModalCrearBloque"
                                @disabled(! $plantillaSeleccionada)
                                class="savp-action-tile rounded-2xl border p-4 text-left"
                            >
                                <div class="flex items-center justify-between gap-3">
                                    <span class="font-black">Crear bloque</span>
                                    {!! $icon('clock', 'h-5 w-5') !!}
                                </div>
                                <p class="ui-muted mt-2 text-xs leading-5">
                                    Periodo dentro de plantilla.
                                </p>
                            </button>
                        </div>
                    </div>

                    <aside class="ui-card-soft p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="ui-kicker">Gestión de trabajo</p>

                                @if ($gestionTrabajo)
                                    <h3 class="ui-title mt-2 text-3xl font-black">
                                        {{ $gestionTrabajo['anio'] ?? 'Gestión' }}
                                    </h3>
                                    <p class="ui-muted mt-1 text-sm">
                                        {{ $gestionTrabajo['rango'] ?? 'Sin rango definido' }}
                                    </p>
                                @else
                                    <h3 class="ui-title mt-2 text-xl font-black">
                                        Gestión no detectada
                                    </h3>
                                    <p class="ui-muted mt-1 text-sm">
                                        Activa o planifica una gestión para validar rangos.
                                    </p>
                                @endif
                            </div>

                            <div class="savp-soft-pulse ui-badge-success rounded-2xl p-3">
                                {!! $icon('calendar', 'h-7 w-7') !!}
                            </div>
                        </div>

                        <div class="mt-5 space-y-3">
                            <div class="ui-card-soft flex items-center justify-between gap-3 p-4">
                                <span class="ui-muted text-sm">Auditoría</span>
                                <span class="{{ $badgeRevision($auditoria['estado'] ?? 'INCOMPLETO') }}">
                                    {{ $auditoria['estado'] ?? 'INCOMPLETO' }}
                                </span>
                            </div>

                            <div class="ui-card-soft flex items-center justify-between gap-3 p-4">
                                <span class="ui-muted text-sm">Bloques sin plantilla</span>
                                <span class="text-xl font-black">
                                    {{ $resumen['bloques_sin_plantilla'] ?? 0 }}
                                </span>
                            </div>

                            <div class="ui-card-soft flex items-center justify-between gap-3 p-4">
                                <span class="ui-muted text-sm">Corrección disponible</span>
                                <span class="{{ $puedeCorregir ? 'ui-badge-warning' : 'ui-badge-success' }}">
                                    {{ $puedeCorregir ? 'Sí' : 'No requerida' }}
                                </span>
                            </div>
                        </div>
                    </aside>
                </div>
            </section>

            {{-- ALERTA --}}
            @if ($puedeCorregir)
                <section class="savp-appear savp-appear-2 ui-alert-warning mt-5">
                    <div class="grid gap-4 lg:grid-cols-[auto_1fr_auto] lg:items-center">
                        <div class="rounded-2xl">
                            {!! $icon('warning', 'h-7 w-7') !!}
                        </div>

                        <div>
                            <h3 class="font-black">Se detectó una estructura horaria corregible</h3>
                            <p class="mt-1 text-sm leading-6">
                                Existen bloques sin plantilla o plantillas regulares faltantes. La corrección segura no elimina registros; solo crea plantillas regulares necesarias y asocia los bloques existentes.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2 lg:justify-end">
                            <button type="button" wire:click="auditarEstructuraHoraria" class="ui-btn-secondary">
                                Ver diagnóstico
                            </button>

                            <button
                                type="button"
                                wire:click="corregirEstructuraHoraria"
                                wire:loading.attr="disabled"
                                wire:target="corregirEstructuraHoraria"
                                class="ui-btn-primary"
                            >
                                <span wire:loading.remove wire:target="corregirEstructuraHoraria">Corregir ahora</span>
                                <span wire:loading wire:target="corregirEstructuraHoraria">Corrigiendo...</span>
                            </button>
                        </div>
                    </div>
                </section>
            @endif

            {{-- RESUMEN --}}
            <section class="savp-appear savp-appear-3 mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
                @foreach ($resumenCards as $card)
                    <article class="ui-card ui-card-hover p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="ui-muted text-[11px] font-black uppercase tracking-[0.18em]">
                                    {{ $card['titulo'] }}
                                </p>

                                <p class="ui-title mt-3 text-2xl font-black tracking-tight">
                                    {{ $card['valor'] }}
                                </p>

                                <p class="ui-muted mt-1 text-xs font-semibold leading-5">
                                    {{ $card['detalle'] }}
                                </p>
                            </div>

                            <div class="{{ $card['badge'] }} rounded-2xl p-3">
                                {!! $icon($card['icono'], 'h-5 w-5') !!}
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>

            {{-- FILTROS --}}
            <section class="ui-card mt-6 p-4 md:p-5">
                <div class="flex flex-col gap-4 2xl:flex-row 2xl:items-center 2xl:justify-between">
                    <div class="flex flex-wrap gap-2">
                        @foreach ($vistas as $key => [$texto, $icono])
                            <button
                                type="button"
                                wire:click="cambiarVista('{{ $key }}')"
                                class="{{ $vista === $key ? 'ui-btn-primary' : 'ui-btn-secondary' }} px-4 py-2.5 text-xs"
                            >
                                {!! $icon($icono, 'h-4 w-4') !!}
                                {{ $texto }}
                            </button>
                        @endforeach
                    </div>

                    <div class="ui-muted text-xs font-bold">
                        <span wire:loading.flex class="items-center gap-2">
                            <span class="inline-flex h-2 w-2 animate-ping rounded-full" style="background: var(--ui-primary)"></span>
                            Actualizando módulo...
                        </span>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 xl:grid-cols-[1.2fr_0.65fr_0.7fr_0.7fr_0.7fr_auto]">
                    <div class="relative">
                        <div class="ui-muted pointer-events-none absolute left-4 top-1/2 -translate-y-1/2">
                            {!! $icon('search', 'h-4 w-4') !!}
                        </div>

                        <input
                            type="text"
                            wire:model.live.debounce.800ms="search"
                            class="ui-input pl-11"
                            placeholder="Buscar turno o plantilla..."
                        >
                    </div>

                    <select wire:model.live="estado" class="ui-select">
                        <option value="">Estado: todos</option>
                        <option value="ACTIVO">Activos</option>
                        <option value="INACTIVO">Inactivos</option>
                    </select>

                    <select wire:model.live="tipoPlantilla" class="ui-select">
                        <option value="">Tipo: todos</option>
                        <option value="REGULAR">Regular</option>
                        <option value="INVIERNO">Invierno</option>
                    </select>

                    <select wire:model.live="aplicacion" class="ui-select">
                        <option value="">Aplicación: todas</option>
                        <option value="APLICADA">Aplicadas</option>
                        <option value="NO_APLICADA">No aplicadas</option>
                    </select>

                    <select wire:model.live="usoAcademico" class="ui-select">
                        <option value="">Uso: todos</option>
                        <option value="CON_HORARIO">Con horario</option>
                        <option value="SIN_HORARIO">Sin horario</option>
                    </select>

                    <div class="flex gap-2">
                        <select wire:model.live="perPage" class="ui-select min-w-24">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>

                        <button type="button" wire:click="limpiarFiltros" class="ui-btn-secondary">
                            {!! $icon('filter', 'h-4 w-4') !!}
                            Limpiar
                        </button>
                    </div>
                </div>
            </section>

            {{-- CUERPO --}}
            <section class="mt-6 grid gap-6 2xl:grid-cols-[1fr_430px]">
                <main class="space-y-6">
                    {{-- JORNADA --}}
                    @if ($vista === 'jornada')
                        <section class="grid gap-5 xl:grid-cols-2">
                            @forelse ($turnos as $turno)
                                <article class="ui-card ui-card-hover p-5">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="ui-kicker">Jornada académica</p>
                                            <h3 class="ui-title mt-1 text-2xl font-black">
                                                {{ $turno['nombre'] }}
                                            </h3>
                                            <p class="ui-muted mt-1 text-sm">
                                                {{ $turno['rango'] }}
                                            </p>
                                        </div>

                                        <span class="{{ $badgeRevision($turno['revision']) }}">
                                            {{ $turno['revision'] === 'VALIDO' ? 'Correcto' : ucfirst(strtolower($turno['revision'])) }}
                                        </span>
                                    </div>

                                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                        <div class="ui-card-soft p-4">
                                            <p class="ui-muted text-xs">Plantilla regular</p>
                                            <p class="ui-title mt-1 line-clamp-1 font-black">
                                                {{ $turno['plantilla_regular']['nombre'] ?? 'No configurada' }}
                                            </p>

                                            <div class="mt-3 flex flex-wrap gap-2">
                                                <span class="ui-badge-success">Regular</span>
                                                <span class="{{ $turno['plantilla_regular'] ? 'ui-badge-success' : 'ui-badge-warning' }}">
                                                    {{ $turno['plantilla_regular'] ? 'Lista' : 'Pendiente' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="ui-card-soft p-4">
                                            <p class="ui-muted text-xs">Plantilla invierno</p>
                                            <p class="ui-title mt-1 line-clamp-1 font-black">
                                                {{ $turno['plantilla_invierno']['nombre'] ?? 'No configurada' }}
                                            </p>

                                            <div class="mt-3 flex flex-wrap gap-2">
                                                <span class="ui-badge-violet">Invierno</span>
                                                <span class="{{ $turno['plantilla_invierno'] ? 'ui-badge-violet' : 'ui-badge-muted' }}">
                                                    {{ $turno['plantilla_invierno'] ? 'Preparada' : 'Opcional' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-5 grid gap-3 sm:grid-cols-3">
                                        <div class="ui-card-soft p-4">
                                            <p class="ui-muted text-xs">Bloques</p>
                                            <p class="ui-title mt-1 text-xl font-black">
                                                {{ $turno['bloques_total'] }}
                                            </p>
                                        </div>

                                        <div class="ui-card-soft p-4">
                                            <p class="ui-muted text-xs">Sin plantilla</p>
                                            <p class="ui-title mt-1 text-xl font-black">
                                                {{ $turno['bloques_sin_plantilla'] }}
                                            </p>
                                        </div>

                                        <div class="ui-card-soft p-4">
                                            <p class="ui-muted text-xs">Estado</p>
                                            <span class="mt-2 {{ $badgeEstado($turno['estado']) }}">
                                                {{ ucfirst(strtolower($turno['estado'])) }}
                                            </span>
                                        </div>
                                    </div>

                                    @if ($turno['bloques_sin_plantilla'] > 0 || ! $turno['plantilla_regular'])
                                        <div class="ui-alert-warning mt-5">
                                            <div class="flex gap-3">
                                                {!! $icon('warning', 'h-5 w-5 shrink-0') !!}

                                                <div>
                                                    <p class="font-black">Este turno requiere corrección estructural</p>
                                                    <p class="mt-1 text-xs leading-5">
                                                        Se recomienda crear o asegurar la plantilla regular y asociar los bloques existentes.
                                                    </p>

                                                    <div class="mt-3 flex flex-wrap gap-2">
                                                        <button
                                                            type="button"
                                                            wire:click="crearPlantillaRegularParaTurno('{{ $turno['cod_tur'] }}')"
                                                            class="ui-btn-primary px-3 py-2 text-xs"
                                                        >
                                                            Crear plantilla regular
                                                        </button>

                                                        <button
                                                            type="button"
                                                            wire:click="asociarBloquesSinPlantillaDelTurno('{{ $turno['cod_tur'] }}')"
                                                            class="ui-btn-secondary px-3 py-2 text-xs"
                                                        >
                                                            Asociar bloques
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-5 flex flex-wrap gap-2">
                                        <button type="button" wire:click="abrirDetalleTurno('{{ $turno['cod_tur'] }}')" class="ui-btn-secondary px-3 py-2 text-xs">
                                            {!! $icon('eye', 'h-4 w-4') !!} Detalle
                                        </button>

                                        <button type="button" wire:click="abrirModalEditarTurno('{{ $turno['cod_tur'] }}')" class="ui-btn-secondary px-3 py-2 text-xs">
                                            {!! $icon('edit', 'h-4 w-4') !!} Editar
                                        </button>

                                        <button type="button" wire:click="abrirModalCrearPlantilla('{{ $turno['cod_tur'] }}', 'REGULAR')" class="ui-btn-secondary px-3 py-2 text-xs">
                                            {!! $icon('layers', 'h-4 w-4') !!} Regular
                                        </button>

                                        <button type="button" wire:click="abrirModalCrearPlantilla('{{ $turno['cod_tur'] }}', 'INVIERNO')" class="ui-btn-secondary px-3 py-2 text-xs">
                                            {!! $icon('snow', 'h-4 w-4') !!} Invierno
                                        </button>

                                        @if ($turno['plantilla_regular'])
                                            <button type="button" wire:click="duplicarComoInvierno('{{ $turno['plantilla_regular']['cod_pho'] }}')" class="ui-btn-secondary px-3 py-2 text-xs">
                                                {!! $icon('copy', 'h-4 w-4') !!} Duplicar invierno
                                            </button>
                                        @endif

                                        @if ($turno['estado'] === 'ACTIVO')
                                            <button type="button" wire:click="confirmarDesactivar('turno', '{{ $turno['cod_tur'] }}')" class="ui-btn-danger px-3 py-2 text-xs">
                                                {!! $icon('trash', 'h-4 w-4') !!} Desactivar
                                            </button>
                                        @else
                                            <button type="button" wire:click="reactivarRegistro('turno', '{{ $turno['cod_tur'] }}')" class="ui-btn-secondary px-3 py-2 text-xs">
                                                {!! $icon('restore', 'h-4 w-4') !!} Reactivar
                                            </button>
                                        @endif
                                    </div>
                                </article>
                            @empty
                                <div class="ui-card p-10 text-center xl:col-span-2">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl ui-badge-success">
                                        {!! $icon('calendar', 'h-8 w-8') !!}
                                    </div>

                                    <h3 class="ui-title mt-4 text-xl font-black">No existen turnos configurados</h3>
                                    <p class="ui-muted mt-2 text-sm">
                                        Registra los turnos institucionales para construir plantillas y bloques.
                                    </p>

                                    <button type="button" wire:click="abrirModalCrearTurno" class="ui-btn-primary mt-5">
                                        Registrar turno
                                    </button>
                                </div>
                            @endforelse
                        </section>

                        <div>
                            {{ $turnos->links() }}
                        </div>
                    @endif

                    {{-- TABLA --}}
                    @if ($vista === 'tabla')
                        <section class="ui-table-wrap">
                            <div class="overflow-x-auto">
                                <table class="ui-table">
                                    <thead>
                                        <tr>
                                            <th>Turno</th>
                                            <th>Jornada</th>
                                            <th>Regular</th>
                                            <th>Invierno</th>
                                            <th>Bloques</th>
                                            <th>Estado</th>
                                            <th class="text-right">Acciones</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($turnos as $turno)
                                            <tr>
                                                <td>
                                                    <div class="ui-title font-black">{{ $turno['nombre'] }}</div>
                                                    <div class="ui-muted text-xs">{{ $turno['mensaje_revision'] }}</div>
                                                </td>

                                                <td class="text-sm font-semibold">
                                                    {{ $turno['rango'] }}
                                                </td>

                                                <td>
                                                    <span class="{{ $turno['plantilla_regular'] ? 'ui-badge-success' : 'ui-badge-warning' }}">
                                                        {{ $turno['plantilla_regular'] ? 'Configurada' : 'Pendiente' }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <span class="{{ $turno['plantilla_invierno'] ? 'ui-badge-violet' : 'ui-badge-muted' }}">
                                                        {{ $turno['plantilla_invierno'] ? 'Preparada' : 'Sin definir' }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <span class="font-black">{{ $turno['bloques_total'] }}</span>

                                                    @if ($turno['bloques_sin_plantilla'] > 0)
                                                        <span class="ui-badge-warning ml-2">
                                                            {{ $turno['bloques_sin_plantilla'] }} sin plantilla
                                                        </span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <span class="{{ $badgeEstado($turno['estado']) }}">
                                                        {{ ucfirst(strtolower($turno['estado'])) }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <div class="flex justify-end gap-2">
                                                        <button type="button" wire:click="abrirDetalleTurno('{{ $turno['cod_tur'] }}')" class="ui-icon-btn">
                                                            {!! $icon('eye', 'h-4 w-4') !!}
                                                        </button>

                                                        <button type="button" wire:click="abrirModalEditarTurno('{{ $turno['cod_tur'] }}')" class="ui-icon-btn">
                                                            {!! $icon('edit', 'h-4 w-4') !!}
                                                        </button>

                                                        <button type="button" wire:click="crearPlantillaRegularParaTurno('{{ $turno['cod_tur'] }}')" class="ui-icon-btn">
                                                            {!! $icon('layers', 'h-4 w-4') !!}
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="py-12 text-center">
                                                    <span class="ui-muted text-sm">No existen turnos para mostrar.</span>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="ui-divider border-t p-4">
                                {{ $turnos->links() }}
                            </div>
                        </section>
                    @endif

                    {{-- PLANTILLAS --}}
                    @if ($vista === 'plantillas')
                        <section class="grid gap-5 xl:grid-cols-2">
                            @forelse ($plantillasAgrupadas as $grupo)
                                @php $turnoGrupo = $grupo->first()['turno'] ?? null; @endphp

                                <div class="ui-card p-5">
                                    <div class="mb-4 flex items-start justify-between gap-4">
                                        <div>
                                            <p class="ui-kicker">Turno</p>
                                            <h3 class="ui-title mt-1 text-xl font-black">{{ $turnoGrupo['nombre'] ?? 'Turno' }}</h3>
                                            <p class="ui-muted mt-1 text-sm">{{ $turnoGrupo['rango'] ?? 'Sin rango definido' }}</p>
                                        </div>

                                        <div class="flex gap-2">
                                            <button type="button" wire:click="abrirModalCrearPlantilla('{{ $turnoGrupo['cod_tur'] ?? '' }}', 'REGULAR')" class="ui-icon-btn">
                                                {!! $icon('layers') !!}
                                            </button>

                                            <button type="button" wire:click="abrirModalCrearPlantilla('{{ $turnoGrupo['cod_tur'] ?? '' }}', 'INVIERNO')" class="ui-icon-btn">
                                                {!! $icon('snow') !!}
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        @foreach ($grupo as $plantilla)
                                            <article class="ui-card-soft p-4">
                                                <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                                    <div>
                                                        <h4 class="ui-title font-black">{{ $plantilla['nombre'] }}</h4>
                                                        <p class="ui-muted mt-1 text-xs">{{ $plantilla['vigencia'] }}</p>
                                                    </div>

                                                    <div class="flex flex-wrap gap-2 md:justify-end">
                                                        <span class="{{ $badgeTipo($plantilla['tipo']) }}">
                                                            {{ ucfirst(strtolower($plantilla['tipo'])) }}
                                                        </span>

                                                        <span class="{{ $plantilla['aplicada'] ? 'ui-badge-success' : 'ui-badge-muted' }}">
                                                            {{ $plantilla['aplicada'] ? 'Aplicada' : 'No aplicada' }}
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                                    <div>
                                                        <p class="ui-muted text-xs">Duración base</p>
                                                        <p class="ui-title font-black">{{ $plantilla['duracion_base'] ?? '-' }} min</p>
                                                    </div>

                                                    <div>
                                                        <p class="ui-muted text-xs">Bloques</p>
                                                        <p class="ui-title font-black">{{ $plantilla['bloques_total'] }}</p>
                                                    </div>

                                                    <div>
                                                        <p class="ui-muted text-xs">Uso</p>
                                                        <p class="ui-title font-black">{{ $plantilla['uso_academico'] }}</p>
                                                    </div>
                                                </div>

                                                <div class="mt-4 flex flex-wrap gap-2">
                                                    <button type="button" wire:click="seleccionarPlantilla('{{ $plantilla['cod_pho'] }}')" class="ui-btn-secondary px-3 py-2 text-xs">Ver bloques</button>
                                                    <button type="button" wire:click="abrirModalEditarPlantilla('{{ $plantilla['cod_pho'] }}')" class="ui-btn-secondary px-3 py-2 text-xs">Editar</button>
                                                    <button type="button" wire:click="confirmarAplicarPlantilla('{{ $plantilla['cod_pho'] }}')" class="ui-btn-secondary px-3 py-2 text-xs">Aplicar</button>

                                                    @if ($plantilla['tipo'] === 'REGULAR')
                                                        <button type="button" wire:click="duplicarComoInvierno('{{ $plantilla['cod_pho'] }}')" class="ui-btn-secondary px-3 py-2 text-xs">Duplicar invierno</button>
                                                    @endif

                                                    @if ($plantilla['activa'])
                                                        <button type="button" wire:click="confirmarDesactivar('plantilla', '{{ $plantilla['cod_pho'] }}')" class="ui-btn-danger px-3 py-2 text-xs">Desactivar</button>
                                                    @else
                                                        <button type="button" wire:click="reactivarRegistro('plantilla', '{{ $plantilla['cod_pho'] }}')" class="ui-btn-secondary px-3 py-2 text-xs">Reactivar</button>
                                                    @endif
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="ui-card p-10 text-center xl:col-span-2">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl ui-badge-success">
                                        {!! $icon('layers', 'h-8 w-8') !!}
                                    </div>

                                    <h3 class="ui-title mt-4 text-xl font-black">No existen plantillas horarias</h3>
                                    <p class="ui-muted mt-2 text-sm">
                                        Crea una plantilla regular para cada turno y luego prepara la variante de invierno.
                                    </p>
                                </div>
                            @endforelse
                        </section>
                    @endif

                    {{-- AGENDA --}}
                    @if ($vista === 'agenda')
                        <section class="ui-card p-5">
                            <div class="mb-5 flex flex-col justify-between gap-4 md:flex-row md:items-center">
                                <div>
                                    <p class="ui-kicker">Vista previa</p>
                                    <h3 class="ui-title mt-1 text-2xl font-black">Aplicación regular e invierno</h3>
                                    <p class="ui-muted mt-2 text-sm">
                                        El horario de invierno reemplaza temporalmente al regular durante su vigencia.
                                    </p>
                                </div>

                                <button type="button" wire:click="abrirVistaPreviaAplicacion" class="ui-btn-secondary">
                                    Ampliar vista
                                </button>
                            </div>

                            @if ($vistaPrevia['disponible'] ?? false)
                                <div class="space-y-4">
                                    @forelse (($vistaPrevia['segmentos'] ?? []) as $segmento)
                                        <div class="ui-card-soft grid gap-4 p-4 md:grid-cols-[160px_1fr_auto] md:items-center">
                                            <div>
                                                <p class="ui-muted text-xs">Turno</p>
                                                <p class="ui-title font-black">{{ $segmento['turno'] ?? 'Turno' }}</p>
                                            </div>

                                            <div>
                                                <p class="ui-title font-black">{{ $segmento['plantilla'] ?? 'Plantilla' }}</p>
                                                <p class="ui-muted mt-1 text-sm">
                                                    {{ $segmento['fecha_inicio'] ?? '-' }} al {{ $segmento['fecha_fin'] ?? '-' }}
                                                </p>
                                                <p class="ui-muted mt-1 text-xs">{{ $segmento['mensaje'] ?? '' }}</p>
                                            </div>

                                            <span class="{{ $badgeTipo($segmento['tipo'] ?? null) }}">
                                                {{ ucfirst(strtolower($segmento['tipo'] ?? 'Regular')) }}
                                            </span>
                                        </div>
                                    @empty
                                        <div class="ui-card-soft p-8 text-center">
                                            <p class="ui-title font-black">No hay segmentos para mostrar</p>
                                        </div>
                                    @endforelse
                                </div>
                            @else
                                <div class="ui-alert-warning">
                                    {{ $vistaPrevia['mensaje'] ?? 'No se pudo generar la vista previa.' }}
                                </div>
                            @endif
                        </section>
                    @endif

                    {{-- BLOQUES / COMPACTA --}}
                    @if ($vista === 'bloques' || $vista === 'compacta')
                        <section class="ui-card overflow-hidden">
                            <div class="ui-modal-header">
                                <p class="ui-kicker">
                                    {{ $vista === 'bloques' ? 'Vista de bloques' : 'Vista compacta' }}
                                </p>
                                <h3 class="ui-title mt-1 text-2xl font-black">
                                    Estructura resumida de turnos
                                </h3>
                            </div>

                            <div class="divide-y ui-divider">
                                @forelse ($turnos as $turno)
                                    <div class="flex flex-col gap-3 p-4 md:flex-row md:items-center md:justify-between">
                                        <div class="flex items-center gap-4">
                                            <div class="ui-badge-success rounded-2xl p-3">
                                                {!! $icon('clock') !!}
                                            </div>

                                            <div>
                                                <p class="ui-title font-black">{{ $turno['nombre'] }}</p>
                                                <p class="ui-muted text-sm">{{ $turno['rango'] }}</p>
                                            </div>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="{{ $badgeEstado($turno['estado']) }}">
                                                {{ ucfirst(strtolower($turno['estado'])) }}
                                            </span>

                                            <span class="ui-badge-muted">
                                                {{ $turno['bloques_total'] }} bloques
                                            </span>

                                            <button type="button" wire:click="abrirDetalleTurno('{{ $turno['cod_tur'] }}')" class="ui-btn-secondary px-3 py-2 text-xs">
                                                Ver
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-10 text-center">
                                        <span class="ui-muted text-sm">No existen datos para mostrar.</span>
                                    </div>
                                @endforelse
                            </div>

                            <div class="ui-modal-footer">
                                {{ $turnos->links() }}
                            </div>
                        </section>
                    @endif
                </main>

                {{-- PANEL LATERAL --}}
                <aside class="ui-card h-fit p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="ui-kicker">Bloques de plantilla</p>
                            <h3 class="ui-title mt-1 text-xl font-black">Secuencia horaria</h3>
                            <p class="ui-muted mt-1 text-sm">
                                Los bloques siempre se asocian a una plantilla.
                            </p>
                        </div>

                        <button
                            type="button"
                            wire:click="abrirModalCrearBloque"
                            @disabled(! $plantillaSeleccionada)
                            class="ui-icon-btn"
                        >
                            {!! $icon('plus') !!}
                        </button>
                    </div>

                    <div class="mt-4">
                        <select wire:model.live="plantillaSeleccionada" class="ui-select">
                            <option value="">Seleccionar plantilla</option>
                            @foreach ($plantillasCatalogo as $plantilla)
                                <option value="{{ $plantilla['cod_pho'] }}">
                                    {{ $plantilla['nombre'] }} · {{ $plantilla['turno']['nombre'] ?? 'Turno' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        <button
                            type="button"
                            wire:click="validarBloquesDePlantilla"
                            @disabled(! $plantillaSeleccionada)
                            class="ui-btn-secondary px-3 py-2 text-xs"
                        >
                            Validar bloques
                        </button>

                        <button
                            type="button"
                            wire:click="exportarBloquesPdf"
                            class="ui-btn-secondary px-3 py-2 text-xs"
                        >
                            Exportar
                        </button>
                    </div>

                    <div class="mt-5 space-y-3">
                        @forelse ($bloquesPlantillaSeleccionada as $bloque)
                            <article class="ui-card-soft p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="ui-title font-black">{{ $bloque['nombre'] }}</p>
                                        <p class="ui-muted mt-1 text-xs">
                                            {{ $bloque['rango'] }} · {{ $bloque['duracion'] ?? '-' }} min
                                        </p>
                                    </div>

                                    <span class="{{ $badgeTipo($bloque['tipo'] === 'CLASE' ? 'REGULAR' : ($bloque['tipo'] === 'RECREO' ? 'AJUSTE' : null)) }}">
                                        {{ ucfirst(strtolower($bloque['tipo'])) }}
                                    </span>
                                </div>

                                <div class="mt-3 flex flex-wrap gap-2">
                                    <button type="button" wire:click="abrirModalEditarBloque('{{ $bloque['cod_hbl'] }}')" class="ui-btn-secondary px-3 py-2 text-xs">
                                        Editar
                                    </button>

                                    <button type="button" wire:click="reordenarBloque('arriba', '{{ $bloque['cod_hbl'] }}')" class="ui-btn-secondary px-3 py-2 text-xs">
                                        Subir
                                    </button>

                                    <button type="button" wire:click="reordenarBloque('abajo', '{{ $bloque['cod_hbl'] }}')" class="ui-btn-secondary px-3 py-2 text-xs">
                                        Bajar
                                    </button>

                                    @if ($bloque['estado'] === 'ACTIVO')
                                        <button type="button" wire:click="confirmarDesactivar('bloque', '{{ $bloque['cod_hbl'] }}')" class="ui-btn-danger px-3 py-2 text-xs">
                                            Desactivar
                                        </button>
                                    @else
                                        <button type="button" wire:click="reactivarRegistro('bloque', '{{ $bloque['cod_hbl'] }}')" class="ui-btn-secondary px-3 py-2 text-xs">
                                            Reactivar
                                        </button>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="ui-card-soft p-8 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl ui-badge-muted">
                                    {!! $icon('timeline') !!}
                                </div>

                                <p class="ui-title mt-3 font-black">No hay bloques para esta plantilla</p>
                                <p class="ui-muted mt-1 text-xs">
                                    Selecciona una plantilla o registra el primer bloque.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </aside>
            </section>
        </div>
    </div>

    {{-- MODAL TURNO --}}
    @if ($modalTurno)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-5xl overflow-y-auto">
                <div class="ui-modal-header flex items-center justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Turno académico</p>
                        <h3 class="ui-title text-xl font-black">
                            {{ $modoTurno === 'crear' ? 'Registrar turno' : 'Editar turno' }}
                        </h3>
                    </div>

                    <button type="button" wire:click="cerrarModalTurno" class="ui-icon-btn">
                        {!! $icon('x') !!}
                    </button>
                </div>

                <div class="grid gap-6 p-5 lg:grid-cols-[1fr_390px]">
                    <div class="space-y-4">
                        <div>
                            <label class="ui-label">Nombre del turno</label>
                            <input
                                type="text"
                                wire:model.live.debounce.1200ms="formTurno.nom_tur"
                                wire:blur="analizarTurnoTiempoReal"
                                class="ui-input"
                                placeholder="Mañana, Tarde o Especial"
                            >
                            @error('formTurno.nom_tur')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="ui-label">Hora de inicio</label>
                                <input
                                    type="time"
                                    wire:model.blur="formTurno.hor_ini_tur"
                                    wire:blur="analizarTurnoTiempoReal"
                                    class="ui-input"
                                >
                                @error('formTurno.hor_ini_tur')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="ui-label">Hora de finalización</label>
                                <input
                                    type="time"
                                    wire:model.blur="formTurno.hor_fin_tur"
                                    wire:blur="analizarTurnoTiempoReal"
                                    class="ui-input"
                                >
                                @error('formTurno.hor_fin_tur')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">Estado</label>
                            <select wire:model.live="formTurno.est_tur" wire:change="analizarTurnoTiempoReal" class="ui-select">
                                @foreach ($estadosRegistro as $estadoItem)
                                    <option value="{{ $estadoItem }}">{{ ucfirst(strtolower($estadoItem)) }}</option>
                                @endforeach
                            </select>
                            @error('formTurno.est_tur')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <aside class="ui-panel">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="ui-kicker">Revisión del sistema</p>
                                <p class="ui-subtitle mt-3 text-sm font-bold leading-6">
                                    {{ $analisisTurno['mensaje'] ?? 'Completa los datos para ejecutar la revisión.' }}
                                </p>
                            </div>

                            <span class="{{ $badgeRevision($analisisTurno['estado'] ?? 'INCOMPLETO') }}">
                                {{ $analisisTurno['estado'] ?? 'INCOMPLETO' }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-bold">Nivel de riesgo</p>
                                <p class="ui-title mt-1 text-lg font-black">{{ $analisisTurno['nivel_riesgo'] ?? 'BAJO' }}</p>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs font-bold">Resultado</p>
                                <p class="ui-title mt-1 text-lg font-black">
                                    {{ $puedeGuardarTurno ? 'Puede continuar' : 'Requiere revisión' }}
                                </p>
                            </div>
                        </div>

                        @foreach ([
                            'bloqueos' => ['Correcciones necesarias', 'ui-alert-danger'],
                            'advertencias' => ['Observaciones', 'ui-alert-warning'],
                            'sugerencias' => ['Recomendaciones', 'ui-alert-success'],
                        ] as $key => [$titulo, $alertClass])
                            @if (! empty($analisisTurno[$key]))
                                <div class="mt-4 space-y-2">
                                    <p class="ui-kicker">{{ $titulo }}</p>

                                    @foreach ($analisisTurno[$key] as $mensaje)
                                        <div class="{{ $alertClass }}">
                                            {{ $mensaje }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endforeach
                    </aside>
                </div>

                <div class="ui-modal-footer flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <button type="button" wire:click="cerrarModalTurno" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button
                        type="button"
                        wire:click="guardarTurno"
                        wire:loading.attr="disabled"
                        @disabled(! $puedeGuardarTurno)
                        class="{{ $puedeGuardarTurno ? 'ui-btn-primary' : 'ui-btn-secondary' }}"
                    >
                        <span wire:loading.remove wire:target="guardarTurno">
                            {{ $modoTurno === 'crear' ? 'Registrar turno' : 'Guardar cambios' }}
                        </span>
                        <span wire:loading wire:target="guardarTurno">
                            Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL PLANTILLA HORARIA --}}
    @if ($modalPlantilla)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-6xl overflow-y-auto">
                <div class="ui-modal-header flex items-center justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Plantilla horaria</p>
                        <h3 class="ui-title text-xl font-black">
                            {{ $modoPlantilla === 'crear' ? 'Crear plantilla horaria' : 'Editar plantilla horaria' }}
                        </h3>
                        <p class="ui-muted mt-1 text-sm">
                            Configura la base regular o el reemplazo temporal de invierno para un turno académico.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarModalPlantilla" class="ui-icon-btn">
                        {!! $icon('x') !!}
                    </button>
                </div>

                <div class="grid gap-6 p-5 xl:grid-cols-[1fr_430px]">
                    {{-- FORMULARIO --}}
                    <div class="space-y-5">
                        <div class="ui-alert-info">
                            <div class="flex gap-3">
                                <div class="shrink-0">
                                    {!! $icon('calendar', 'h-5 w-5') !!}
                                </div>

                                <div>
                                    <p class="font-black">Regla de configuración</p>
                                    <p class="mt-1 text-xs leading-5">
                                        La plantilla regular funciona como base anual. La plantilla de invierno debe tener rango de vigencia y reemplaza temporalmente la regular durante ese periodo.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div>
                                <label class="ui-label">Turno</label>
                                <select
                                    wire:model.live="formPlantilla.cod_tur"
                                    wire:change="analizarPlantillaTiempoReal"
                                    class="ui-select"
                                >
                                    <option value="">Seleccionar turno</option>
                                    @foreach ($turnosCatalogo as $turnoItem)
                                        <option value="{{ $turnoItem['cod_tur'] }}">
                                            {{ $turnoItem['nombre'] }} · {{ $turnoItem['rango'] }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('formPlantilla.cod_tur')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror

                                <p class="ui-help">
                                    El turno define el rango máximo donde podrán ubicarse sus bloques.
                                </p>
                            </div>

                            <div>
                                <label class="ui-label">Tipo de plantilla</label>
                                <select
                                    wire:model.live="formPlantilla.tip_pho"
                                    wire:change="analizarPlantillaTiempoReal"
                                    class="ui-select"
                                >
                                    <option value="REGULAR">Regular</option>
                                    <option value="INVIERNO">Invierno</option>
                                </select>

                                @error('formPlantilla.tip_pho')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror

                                <p class="ui-help">
                                    Para este módulo se recomienda trabajar solo con Regular e Invierno.
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">Nombre de plantilla</label>
                            <input
                                type="text"
                                wire:model.live.debounce.1200ms="formPlantilla.nom_pho"
                                wire:blur="analizarPlantillaTiempoReal"
                                class="ui-input"
                                placeholder="Ejemplo: Plantilla Regular - Mañana"
                            >

                            @error('formPlantilla.nom_pho')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror

                            <p class="ui-help">
                                Usa nombres claros para identificar si pertenece al turno Mañana, Tarde o una configuración institucional específica.
                            </p>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-3">
                            <div>
                                <label class="ui-label">Duración base</label>
                                <div class="relative">
                                    <input
                                        type="number"
                                        min="5"
                                        max="120"
                                        wire:model.live.debounce.800ms="formPlantilla.dur_blo_pho"
                                        wire:blur="analizarPlantillaTiempoReal"
                                        class="ui-input pr-14"
                                        placeholder="45"
                                    >
                                    <span class="ui-muted pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold">
                                        min
                                    </span>
                                </div>

                                @error('formPlantilla.dur_blo_pho')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror

                                <p class="ui-help">
                                    Regular suele usar una duración base mayor; invierno puede reducir la duración.
                                </p>
                            </div>

                            <div>
                                <label class="ui-label">Orden</label>
                                <input
                                    type="number"
                                    min="1"
                                    max="99"
                                    wire:model.live.debounce.800ms="formPlantilla.ord_pho"
                                    wire:blur="analizarPlantillaTiempoReal"
                                    class="ui-input"
                                    placeholder="1"
                                >

                                @error('formPlantilla.ord_pho')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror

                                <p class="ui-help">
                                    Define prioridad visual dentro del turno.
                                </p>
                            </div>

                            <div>
                                <label class="ui-label">Estado</label>
                                <select
                                    wire:model.live="formPlantilla.est_pho"
                                    wire:change="analizarPlantillaTiempoReal"
                                    class="ui-select"
                                >
                                    <option value="1">Activa</option>
                                    <option value="0">Inactiva</option>
                                </select>

                                @error('formPlantilla.est_pho')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror

                                <p class="ui-help">
                                    Inactiva conserva historial, pero no se usa para planificación.
                                </p>
                            </div>
                        </div>

                        <div class="ui-card-soft p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="ui-kicker">Vigencia</p>
                                    <h4 class="ui-title mt-1 font-black">
                                        Rango de aplicación de la plantilla
                                    </h4>
                                    <p class="ui-muted mt-1 text-sm leading-6">
                                        La plantilla regular puede quedar como base anual. La plantilla de invierno debe tener fechas y mantenerse dentro de la gestión académica.
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    wire:click="aplicarSugerenciaInvierno"
                                    class="ui-btn-secondary shrink-0 px-4 py-2.5 text-xs"
                                >
                                    {!! $icon('snow', 'h-4 w-4') !!}
                                    Sugerir invierno
                                </button>
                            </div>

                            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="ui-label">Fecha de inicio</label>
                                    <input
                                        type="date"
                                        wire:model.live="formPlantilla.fec_ini_pho"
                                        wire:change="analizarPlantillaTiempoReal"
                                        class="ui-input"
                                    >

                                    @error('formPlantilla.fec_ini_pho')
                                        <p class="ui-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Fecha de finalización</label>
                                    <input
                                        type="date"
                                        wire:model.live="formPlantilla.fec_fin_pho"
                                        wire:change="analizarPlantillaTiempoReal"
                                        class="ui-input"
                                    >

                                    @error('formPlantilla.fec_fin_pho')
                                        <p class="ui-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            @if (($formPlantilla['tip_pho'] ?? '') === 'REGULAR')
                                <div class="ui-alert-success mt-4">
                                    <div class="flex gap-3">
                                        {!! $icon('check', 'h-5 w-5 shrink-0') !!}
                                        <p class="text-xs leading-5">
                                            Una plantilla regular puede manejarse como base anual. Si no tiene fechas, el sistema la interpreta como referencia principal de la gestión.
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if (($formPlantilla['tip_pho'] ?? '') === 'INVIERNO')
                                <div class="ui-alert-warning mt-4">
                                    <div class="flex gap-3">
                                        {!! $icon('warning', 'h-5 w-5 shrink-0') !!}
                                        <p class="text-xs leading-5">
                                            Una plantilla de invierno debe tener fecha de inicio y fin. No elimina la plantilla regular; solo la reemplaza temporalmente durante su vigencia.
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="ui-card-soft p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="ui-kicker">Aplicación actual</p>
                                    <h4 class="ui-title mt-1 font-black">
                                        Usar como plantilla activa
                                    </h4>
                                    <p class="ui-muted mt-1 text-sm leading-6">
                                        Al marcar esta opción, la plantilla queda como referencia activa para el tipo seleccionado dentro del mismo turno.
                                    </p>
                                </div>

                                <label class="relative inline-flex cursor-pointer items-center">
                                    <input
                                        type="checkbox"
                                        wire:model.live="formPlantilla.act_pho"
                                        wire:change="analizarPlantillaTiempoReal"
                                        class="peer sr-only"
                                    >
                                    <div
                                        class="h-7 w-12 rounded-full border transition peer-checked:translate-x-0"
                                        style="background: {{ ($formPlantilla['act_pho'] ?? false) ? 'var(--ui-primary)' : 'var(--ui-surface-muted)' }}; border-color: var(--ui-border);"
                                    ></div>
                                    <span
                                        class="absolute left-1 top-1 h-5 w-5 rounded-full transition"
                                        style="background: var(--ui-surface); transform: {{ ($formPlantilla['act_pho'] ?? false) ? 'translateX(20px)' : 'translateX(0)' }};"
                                    ></span>
                                </label>
                            </div>

                            @error('formPlantilla.act_pho')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror

                            <div class="ui-alert-info mt-4">
                                <div class="flex gap-3">
                                    {!! $icon('layers', 'h-5 w-5 shrink-0') !!}
                                    <p class="text-xs leading-5">
                                        El sistema evita que existan dos plantillas activas del mismo tipo para el mismo turno. Si aplicas esta plantilla, la anterior quedará como no aplicada.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">Descripción institucional</label>
                            <textarea
                                wire:model.live.debounce.1200ms="formPlantilla.des_pho"
                                wire:blur="analizarPlantillaTiempoReal"
                                rows="4"
                                class="ui-textarea"
                                placeholder="Describe cuándo se usa esta plantilla, qué ajuste representa y si corresponde a horario regular o de invierno."
                            ></textarea>

                            @error('formPlantilla.des_pho')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- PANEL DE REVISIÓN --}}
                    <aside class="space-y-4">
                        <div class="ui-panel">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="ui-kicker">Revisión del sistema</p>
                                    <p class="ui-subtitle mt-3 text-sm font-bold leading-6">
                                        {{ $analisisPlantilla['mensaje'] ?? 'Completa los datos principales de la plantilla horaria.' }}
                                    </p>
                                </div>

                                <span class="{{ $badgeRevision($analisisPlantilla['estado'] ?? 'INCOMPLETO') }}">
                                    {{ $analisisPlantilla['estado'] ?? 'INCOMPLETO' }}
                                </span>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="ui-card-soft p-4">
                                    <p class="ui-muted text-xs font-bold">Nivel de riesgo</p>
                                    <p class="ui-title mt-1 text-lg font-black">
                                        {{ $analisisPlantilla['nivel_riesgo'] ?? 'BAJO' }}
                                    </p>
                                </div>

                                <div class="ui-card-soft p-4">
                                    <p class="ui-muted text-xs font-bold">Resultado</p>
                                    <p class="ui-title mt-1 text-lg font-black">
                                        {{ $puedeGuardarPlantilla ? 'Puede continuar' : 'Requiere revisión' }}
                                    </p>
                                </div>
                            </div>

                            @if (! empty($analisisPlantilla['resumen']))
                                <div class="ui-card-soft mt-4 p-4">
                                    <p class="ui-kicker">Resumen</p>

                                    <div class="mt-3 space-y-2">
                                        @foreach ($analisisPlantilla['resumen'] as $key => $value)
                                            @if (is_scalar($value) || is_null($value))
                                                <div class="flex items-center justify-between gap-4 text-xs">
                                                    <span class="ui-muted font-bold">
                                                        {{ str($key)->replace('_', ' ')->title() }}
                                                    </span>

                                                    <span class="ui-title text-right font-black">
                                                        {{ is_bool($value) ? ($value ? 'Sí' : 'No') : ($value ?? '—') }}
                                                    </span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (! empty($analisisPlantilla['bloqueos']))
                                <div class="mt-4 space-y-2">
                                    <p class="ui-kicker">Correcciones necesarias</p>

                                    @foreach ($analisisPlantilla['bloqueos'] as $mensaje)
                                        <div class="ui-alert-danger">
                                            {{ $mensaje }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if (! empty($analisisPlantilla['advertencias']))
                                <div class="mt-4 space-y-2">
                                    <p class="ui-kicker">Observaciones</p>

                                    @foreach ($analisisPlantilla['advertencias'] as $mensaje)
                                        <div class="ui-alert-warning">
                                            {{ $mensaje }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if (! empty($analisisPlantilla['sugerencias']))
                                <div class="mt-4 space-y-2">
                                    <p class="ui-kicker">Recomendaciones</p>

                                    @foreach ($analisisPlantilla['sugerencias'] as $mensaje)
                                        <div class="ui-alert-success">
                                            {{ $mensaje }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="ui-panel">
                            <p class="ui-kicker">Vista de aplicación</p>

                            <div class="mt-4 space-y-3">
                                <div class="ui-card-soft p-4">
                                    <p class="ui-muted text-xs">Tipo seleccionado</p>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <span class="{{ $badgeTipo($formPlantilla['tip_pho'] ?? null) }}">
                                            {{ ucfirst(strtolower($formPlantilla['tip_pho'] ?? 'Regular')) }}
                                        </span>

                                        <span class="{{ ($formPlantilla['act_pho'] ?? false) ? 'ui-badge-success' : 'ui-badge-muted' }}">
                                            {{ ($formPlantilla['act_pho'] ?? false) ? 'Aplicada' : 'No aplicada' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="ui-card-soft p-4">
                                    <p class="ui-muted text-xs">Vigencia</p>
                                    <p class="ui-title mt-1 font-black">
                                        @if (($formPlantilla['fec_ini_pho'] ?? '') && ($formPlantilla['fec_fin_pho'] ?? ''))
                                            {{ $formPlantilla['fec_ini_pho'] }} al {{ $formPlantilla['fec_fin_pho'] }}
                                        @else
                                            Base anual o sin rango definido
                                        @endif
                                    </p>
                                </div>

                                <div class="ui-card-soft p-4">
                                    <p class="ui-muted text-xs">Duración base</p>
                                    <p class="ui-title mt-1 text-xl font-black">
                                        {{ $formPlantilla['dur_blo_pho'] ?? '—' }}
                                        <span class="ui-muted text-xs font-bold">min</span>
                                    </p>
                                </div>
                            </div>

                            <div class="ui-alert-info mt-4">
                                <div class="flex gap-3">
                                    {!! $icon('timeline', 'h-5 w-5 shrink-0') !!}
                                    <p class="text-xs leading-5">
                                        Luego de guardar la plantilla, podrás crear o duplicar bloques. La vista previa mostrará cómo se aplicará respecto a la gestión académica.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="ui-modal-footer flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="ui-muted text-xs leading-5">
                        La plantilla se guarda sin mostrar códigos internos. Los identificadores quedan solo para la lógica del sistema.
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="cerrarModalPlantilla" class="ui-btn-secondary">
                            Cancelar
                        </button>

                        <button
                            type="button"
                            wire:click="guardarPlantilla"
                            wire:loading.attr="disabled"
                            @disabled(! $puedeGuardarPlantilla)
                            class="{{ $puedeGuardarPlantilla ? 'ui-btn-primary' : 'ui-btn-secondary' }}"
                        >
                            <span wire:loading.remove wire:target="guardarPlantilla">
                                {{ $modoPlantilla === 'crear' ? 'Guardar plantilla' : 'Guardar cambios' }}
                            </span>
                            <span wire:loading wire:target="guardarPlantilla">
                                Guardando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL BLOQUE HORARIO --}}
    @if ($modalBloque)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-6xl overflow-y-auto">
                <div class="ui-modal-header flex items-center justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Bloque horario</p>
                        <h3 class="ui-title text-xl font-black">
                            {{ $modoBloque === 'crear' ? 'Registrar bloque horario' : 'Editar bloque horario' }}
                        </h3>
                        <p class="ui-muted mt-1 text-sm">
                            Define un periodo dentro de una plantilla horaria. El bloque debe respetar el turno, la duración y la secuencia académica.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarModalBloque" class="ui-icon-btn">
                        {!! $icon('x') !!}
                    </button>
                </div>

                <div class="grid gap-6 p-5 xl:grid-cols-[1fr_430px]">
                    {{-- FORMULARIO --}}
                    <div class="space-y-5">
                        <div class="ui-alert-info">
                            <div class="flex gap-3">
                                <div class="shrink-0">
                                    {!! $icon('layers', 'h-5 w-5') !!}
                                </div>

                                <div>
                                    <p class="font-black">Asociación obligatoria por plantilla</p>
                                    <p class="mt-1 text-xs leading-5">
                                        Los bloques ya no deben quedar sueltos por turno. Cada bloque debe pertenecer a una plantilla regular o de invierno para evitar inconsistencias en la planificación horaria.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <div>
                                <label class="ui-label">Plantilla horaria</label>
                                <select
                                    wire:model.live="formBloque.cod_pho"
                                    wire:change="sincronizarTurnoDesdePlantilla"
                                    class="ui-select"
                                >
                                    <option value="">Seleccionar plantilla</option>
                                    @foreach ($plantillasCatalogo as $plantilla)
                                        <option value="{{ $plantilla['cod_pho'] }}">
                                            {{ $plantilla['nombre'] }} · {{ $plantilla['turno']['nombre'] ?? 'Turno' }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('formBloque.cod_pho')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror

                                <p class="ui-help">
                                    Al seleccionar la plantilla, el turno se sincroniza automáticamente.
                                </p>
                            </div>

                            <div>
                                <label class="ui-label">Turno sincronizado</label>
                                <select
                                    wire:model.live="formBloque.cod_tur"
                                    wire:change="analizarBloqueTiempoReal"
                                    class="ui-select"
                                    disabled
                                >
                                    <option value="">Selecciona primero una plantilla</option>
                                    @foreach ($turnosCatalogo as $turnoItem)
                                        <option value="{{ $turnoItem['cod_tur'] }}">
                                            {{ $turnoItem['nombre'] }} · {{ $turnoItem['rango'] }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('formBloque.cod_tur')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror

                                <p class="ui-help">
                                    El turno no se elige manualmente para evitar cruces entre plantilla y jornada.
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-3">
                            <div>
                                <label class="ui-label">Número de bloque</label>
                                <input
                                    type="number"
                                    min="1"
                                    max="30"
                                    wire:model.live.debounce.800ms="formBloque.num_hbl"
                                    wire:blur="analizarBloqueTiempoReal"
                                    class="ui-input"
                                    placeholder="1"
                                >

                                @error('formBloque.num_hbl')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror

                                <p class="ui-help">
                                    Define el orden dentro de la plantilla.
                                </p>
                            </div>

                            <div>
                                <label class="ui-label">Tipo de bloque</label>
                                <select
                                    wire:model.live="formBloque.tip_hbl"
                                    wire:change="analizarBloqueTiempoReal"
                                    class="ui-select"
                                >
                                    @foreach ($tiposBloque as $tipoBloque)
                                        <option value="{{ $tipoBloque }}">
                                            {{ ucfirst(strtolower($tipoBloque)) }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('formBloque.tip_hbl')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror

                                <p class="ui-help">
                                    Clase, recreo, descanso, formación, salida u otro.
                                </p>
                            </div>

                            <div>
                                <label class="ui-label">Estado</label>
                                <select
                                    wire:model.live="formBloque.est_hbl"
                                    wire:change="analizarBloqueTiempoReal"
                                    class="ui-select"
                                >
                                    @foreach ($estadosRegistro as $estadoItem)
                                        <option value="{{ $estadoItem }}">
                                            {{ ucfirst(strtolower($estadoItem)) }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('formBloque.est_hbl')
                                    <p class="ui-error">{{ $message }}</p>
                                @enderror

                                <p class="ui-help">
                                    Inactivo conserva historial sin eliminar físicamente.
                                </p>
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">Nombre del bloque</label>
                            <input
                                type="text"
                                wire:model.live.debounce.1200ms="formBloque.nom_hbl"
                                wire:blur="analizarBloqueTiempoReal"
                                class="ui-input"
                                placeholder="Ejemplo: 1er bloque, Recreo, Formación, Salida"
                            >

                            @error('formBloque.nom_hbl')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror

                            <p class="ui-help">
                                Usa nombres claros para facilitar la lectura de la agenda horaria.
                            </p>
                        </div>

                        <div class="ui-card-soft p-5">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <p class="ui-kicker">Rango horario</p>
                                    <h4 class="ui-title mt-1 font-black">
                                        Horario del bloque dentro de la plantilla
                                    </h4>
                                    <p class="ui-muted mt-1 text-sm leading-6">
                                        El rango debe estar dentro del turno y no debe solaparse con otros bloques de la misma plantilla.
                                    </p>
                                </div>

                                <span class="{{ $badgeRevision($analisisBloque['estado'] ?? 'INCOMPLETO') }}">
                                    {{ $analisisBloque['estado'] ?? 'INCOMPLETO' }}
                                </span>
                            </div>

                            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                <div>
                                    <label class="ui-label">Hora de inicio</label>
                                    <input
                                        type="time"
                                        wire:model.live="formBloque.hor_ini_hbl"
                                        wire:change="analizarBloqueTiempoReal"
                                        class="ui-input"
                                    >

                                    @error('formBloque.hor_ini_hbl')
                                        <p class="ui-error">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="ui-label">Hora de finalización</label>
                                    <input
                                        type="time"
                                        wire:model.live="formBloque.hor_fin_hbl"
                                        wire:change="analizarBloqueTiempoReal"
                                        class="ui-input"
                                    >

                                    @error('formBloque.hor_fin_hbl')
                                        <p class="ui-error">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                <div class="ui-card-soft p-4">
                                    <p class="ui-muted text-xs">Duración calculada</p>
                                    <p class="ui-title mt-1 text-xl font-black">
                                        {{ $analisisBloque['resumen']['duracion_minutos'] ?? '—' }}
                                        <span class="ui-muted text-xs font-bold">min</span>
                                    </p>
                                </div>

                                <div class="ui-card-soft p-4">
                                    <p class="ui-muted text-xs">Tipo</p>
                                    <span class="{{ $badgeTipo(($formBloque['tip_hbl'] ?? 'CLASE') === 'CLASE' ? 'REGULAR' : (($formBloque['tip_hbl'] ?? '') === 'RECREO' ? 'AJUSTE' : null)) }}">
                                        {{ ucfirst(strtolower($formBloque['tip_hbl'] ?? 'Clase')) }}
                                    </span>
                                </div>

                                <div class="ui-card-soft p-4">
                                    <p class="ui-muted text-xs">Resultado</p>
                                    <p class="ui-title mt-1 font-black">
                                        {{ $puedeGuardarBloque ? 'Puede registrarse' : 'Requiere revisión' }}
                                    </p>
                                </div>
                            </div>

                            <div class="ui-alert-info mt-4">
                                <div class="flex gap-3">
                                    {!! $icon('clock', 'h-5 w-5 shrink-0') !!}
                                    <p class="text-xs leading-5">
                                        Los bloques de clase deberían respetar la duración base de la plantilla. Recreo, descanso o salida pueden tener una duración diferente si está justificado.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">Observación institucional</label>
                            <textarea
                                wire:model.live.debounce.1200ms="formBloque.obs_hbl"
                                wire:blur="analizarBloqueTiempoReal"
                                rows="4"
                                class="ui-textarea"
                                placeholder="Describe si el bloque corresponde a clase, recreo, descanso, formación, salida o ajuste institucional."
                            ></textarea>

                            @error('formBloque.obs_hbl')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- PANEL DE REVISIÓN --}}
                    <aside class="space-y-4">
                        <div class="ui-panel">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="ui-kicker">Revisión del sistema</p>
                                    <p class="ui-subtitle mt-3 text-sm font-bold leading-6">
                                        {{ $analisisBloque['mensaje'] ?? 'Completa los datos principales del bloque horario.' }}
                                    </p>
                                </div>

                                <span class="{{ $badgeRevision($analisisBloque['estado'] ?? 'INCOMPLETO') }}">
                                    {{ $analisisBloque['estado'] ?? 'INCOMPLETO' }}
                                </span>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="ui-card-soft p-4">
                                    <p class="ui-muted text-xs font-bold">Nivel de riesgo</p>
                                    <p class="ui-title mt-1 text-lg font-black">
                                        {{ $analisisBloque['nivel_riesgo'] ?? 'BAJO' }}
                                    </p>
                                </div>

                                <div class="ui-card-soft p-4">
                                    <p class="ui-muted text-xs font-bold">Resultado</p>
                                    <p class="ui-title mt-1 text-lg font-black">
                                        {{ $puedeGuardarBloque ? 'Puede continuar' : 'Requiere revisión' }}
                                    </p>
                                </div>
                            </div>

                            @if (! empty($analisisBloque['resumen']))
                                <div class="ui-card-soft mt-4 p-4">
                                    <p class="ui-kicker">Resumen</p>

                                    <div class="mt-3 space-y-2">
                                        @foreach ($analisisBloque['resumen'] as $key => $value)
                                            @if (is_scalar($value) || is_null($value))
                                                <div class="flex items-center justify-between gap-4 text-xs">
                                                    <span class="ui-muted font-bold">
                                                        {{ str($key)->replace('_', ' ')->title() }}
                                                    </span>

                                                    <span class="ui-title text-right font-black">
                                                        {{ is_bool($value) ? ($value ? 'Sí' : 'No') : ($value ?? '—') }}
                                                    </span>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (! empty($analisisBloque['bloqueos']))
                                <div class="mt-4 space-y-2">
                                    <p class="ui-kicker">Correcciones necesarias</p>

                                    @foreach ($analisisBloque['bloqueos'] as $mensaje)
                                        <div class="ui-alert-danger">
                                            {{ $mensaje }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if (! empty($analisisBloque['advertencias']))
                                <div class="mt-4 space-y-2">
                                    <p class="ui-kicker">Observaciones</p>

                                    @foreach ($analisisBloque['advertencias'] as $mensaje)
                                        <div class="ui-alert-warning">
                                            {{ $mensaje }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if (! empty($analisisBloque['sugerencias']))
                                <div class="mt-4 space-y-2">
                                    <p class="ui-kicker">Recomendaciones</p>

                                    @foreach ($analisisBloque['sugerencias'] as $mensaje)
                                        <div class="ui-alert-success">
                                            {{ $mensaje }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="ui-panel">
                            <p class="ui-kicker">Vista previa del bloque</p>

                            <div class="ui-card-soft mt-4 p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="ui-muted text-xs">Bloque configurado</p>
                                        <p class="ui-title mt-1 text-lg font-black">
                                            {{ $formBloque['nom_hbl'] ?: 'Bloque sin nombre' }}
                                        </p>
                                        <p class="ui-muted mt-1 text-sm">
                                            {{ $formBloque['hor_ini_hbl'] ?: '--:--' }}
                                            -
                                            {{ $formBloque['hor_fin_hbl'] ?: '--:--' }}
                                        </p>
                                    </div>

                                    <span class="{{ $badgeTipo(($formBloque['tip_hbl'] ?? 'CLASE') === 'CLASE' ? 'REGULAR' : (($formBloque['tip_hbl'] ?? '') === 'RECREO' ? 'AJUSTE' : null)) }}">
                                        {{ ucfirst(strtolower($formBloque['tip_hbl'] ?? 'Clase')) }}
                                    </span>
                                </div>

                                <div class="mt-4 h-3 overflow-hidden rounded-full" style="background: var(--ui-surface-muted);">
                                    <div
                                        class="h-full rounded-full transition-all duration-700"
                                        style="background: var(--ui-primary); width: {{ min(100, max(8, (int) (($analisisBloque['resumen']['duracion_minutos'] ?? 0) * 2))) }}%;"
                                    ></div>
                                </div>

                                <p class="ui-muted mt-3 text-xs leading-5">
                                    La barra representa una lectura visual de duración. Si el bloque no coincide con la duración base de la plantilla, aparecerá como observación.
                                </p>
                            </div>

                            <div class="ui-alert-info mt-4">
                                <div class="flex gap-3">
                                    {!! $icon('layers', 'h-5 w-5 shrink-0') !!}
                                    <p class="text-xs leading-5">
                                        Al guardar, el bloque quedará asociado a la plantilla seleccionada. Esto permitirá manejar correctamente horario regular e invierno sin mezclar estructuras.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>

                <div class="ui-modal-footer flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="ui-muted text-xs leading-5">
                        El bloque se registra con trazabilidad y sin mostrar códigos internos al usuario.
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="cerrarModalBloque" class="ui-btn-secondary">
                            Cancelar
                        </button>

                        <button
                            type="button"
                            wire:click="guardarBloque"
                            wire:loading.attr="disabled"
                            @disabled(! $puedeGuardarBloque)
                            class="{{ $puedeGuardarBloque ? 'ui-btn-primary' : 'ui-btn-secondary' }}"
                        >
                            <span wire:loading.remove wire:target="guardarBloque">
                                {{ $modoBloque === 'crear' ? 'Registrar bloque' : 'Guardar cambios' }}
                            </span>
                            <span wire:loading wire:target="guardarBloque">
                                Guardando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL AUDITORÍA DE ESTRUCTURA HORARIA --}}
    @if ($modalAuditoria)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-6xl overflow-y-auto">
                <div class="ui-modal-header flex items-center justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Auditoría de estructura horaria</p>
                        <h3 class="ui-title text-xl font-black">
                            Diagnóstico de turnos, plantillas y bloques
                        </h3>
                        <p class="ui-muted mt-1 text-sm">
                            Revisa si existen bloques sin plantilla, plantillas regulares faltantes o configuraciones que puedan afectar la planificación académica.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarModalAuditoria" class="ui-icon-btn">
                        {!! $icon('x') !!}
                    </button>
                </div>

                <div class="grid gap-6 p-5 xl:grid-cols-[420px_1fr]">
                    {{-- RESUMEN DE AUDITORÍA --}}
                    <aside class="space-y-4">
                        <div class="ui-panel">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="ui-kicker">Estado general</p>
                                    <p class="ui-subtitle mt-3 text-sm font-bold leading-6">
                                        {{ $auditoria['mensaje'] ?? 'Auditoría no disponible.' }}
                                    </p>
                                </div>

                                <span class="{{ $badgeRevision($auditoria['estado'] ?? 'INCOMPLETO') }}">
                                    {{ $auditoria['estado'] ?? 'INCOMPLETO' }}
                                </span>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                <div class="ui-card-soft p-4">
                                    <p class="ui-muted text-xs font-bold">Nivel de riesgo</p>
                                    <p class="ui-title mt-1 text-lg font-black">
                                        {{ $auditoria['nivel_riesgo'] ?? 'BAJO' }}
                                    </p>
                                </div>

                                <div class="ui-card-soft p-4">
                                    <p class="ui-muted text-xs font-bold">Corrección</p>
                                    <p class="ui-title mt-1 text-lg font-black">
                                        {{ ($auditoria['resumen']['puede_corregir'] ?? false) ? 'Disponible' : 'No requerida' }}
                                    </p>
                                </div>

                                <div class="ui-card-soft p-4">
                                    <p class="ui-muted text-xs font-bold">Bloques sin plantilla</p>
                                    <p class="ui-title mt-1 text-lg font-black">
                                        {{ $auditoria['resumen']['bloques_sin_plantilla'] ?? 0 }}
                                    </p>
                                </div>

                                <div class="ui-card-soft p-4">
                                    <p class="ui-muted text-xs font-bold">Plantillas sin bloques</p>
                                    <p class="ui-title mt-1 text-lg font-black">
                                        {{ $auditoria['resumen']['plantillas_sin_bloques'] ?? 0 }}
                                    </p>
                                </div>
                            </div>

                            @if (! empty($auditoria['bloqueos']))
                                <div class="mt-4 space-y-2">
                                    <p class="ui-kicker">Bloqueos</p>

                                    @foreach ($auditoria['bloqueos'] as $mensaje)
                                        <div class="ui-alert-danger">
                                            {{ $mensaje }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if (! empty($auditoria['advertencias']))
                                <div class="mt-4 space-y-2">
                                    <p class="ui-kicker">Observaciones</p>

                                    @foreach ($auditoria['advertencias'] as $mensaje)
                                        <div class="ui-alert-warning">
                                            {{ $mensaje }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if (! empty($auditoria['sugerencias']))
                                <div class="mt-4 space-y-2">
                                    <p class="ui-kicker">Recomendaciones</p>

                                    @foreach ($auditoria['sugerencias'] as $mensaje)
                                        <div class="ui-alert-success">
                                            {{ $mensaje }}
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="ui-panel">
                            <p class="ui-kicker">Acciones sugeridas</p>

                            <div class="mt-4 space-y-2">
                                @forelse (($auditoria['resumen']['acciones_sugeridas'] ?? []) as $accion)
                                    <div class="ui-card-soft flex gap-3 p-3">
                                        <span class="shrink-0">
                                            {!! $icon('check', 'h-4 w-4') !!}
                                        </span>

                                        <p class="ui-subtitle text-xs font-bold leading-5">
                                            {{ $accion }}
                                        </p>
                                    </div>
                                @empty
                                    <div class="ui-alert-success">
                                        La estructura no requiere corrección automática.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="ui-panel">
                            <p class="ui-kicker">Corrección segura</p>

                            <p class="ui-muted mt-2 text-sm leading-6">
                                Esta acción no elimina registros. Si faltan plantillas regulares, las crea. Si existen bloques sin plantilla, los asocia a la plantilla regular correspondiente.
                            </p>

                            <button
                                type="button"
                                wire:click="corregirEstructuraHoraria"
                                wire:loading.attr="disabled"
                                wire:target="corregirEstructuraHoraria"
                                @disabled(! $puedeCorregir)
                                class="{{ $puedeCorregir ? 'ui-btn-primary' : 'ui-btn-secondary' }} mt-4 w-full justify-center"
                            >
                                <span wire:loading.remove wire:target="corregirEstructuraHoraria">
                                    Corregir estructura segura
                                </span>
                                <span wire:loading wire:target="corregirEstructuraHoraria">
                                    Corrigiendo estructura...
                                </span>
                            </button>
                        </div>
                    </aside>

                    {{-- DIAGNÓSTICO POR TURNO --}}
                    <section class="space-y-4">
                        <div class="ui-panel">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <p class="ui-kicker">Diagnóstico por turno</p>
                                    <h4 class="ui-title mt-1 text-xl font-black">
                                        Revisión de jornadas académicas
                                    </h4>
                                    <p class="ui-muted mt-1 text-sm leading-6">
                                        Cada turno se evalúa según existencia de plantilla regular, bloques asociados, bloques fuera de rango y posibles solapamientos de invierno.
                                    </p>
                                </div>

                                <button type="button" wire:click="auditarEstructuraHoraria" class="ui-btn-secondary">
                                    {!! $icon('warning', 'h-4 w-4') !!}
                                    Recalcular
                                </button>
                            </div>

                            <div class="mt-5 space-y-4">
                                @forelse (($auditoria['resumen']['turnos'] ?? []) as $turnoDiagnostico)
                                    @php
                                        $requiereRevision =
                                            ($turnoDiagnostico['bloques_sin_plantilla'] ?? 0) > 0 ||
                                            ! ($turnoDiagnostico['plantilla_regular_existe'] ?? false) ||
                                            ($turnoDiagnostico['bloques_fuera_turno'] ?? 0) > 0 ||
                                            ($turnoDiagnostico['plantillas_invierno_solapadas'] ?? 0) > 0;
                                    @endphp

                                    <article class="ui-card-soft p-4">
                                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h5 class="ui-title text-lg font-black">
                                                        {{ $turnoDiagnostico['turno'] ?? 'Turno' }}
                                                    </h5>

                                                    <span class="{{ $requiereRevision ? 'ui-badge-warning' : 'ui-badge-success' }}">
                                                        {{ $requiereRevision ? 'Revisar' : 'Correcto' }}
                                                    </span>
                                                </div>

                                                <p class="ui-muted mt-1 text-sm">
                                                    {{ $turnoDiagnostico['rango'] ?? 'Sin rango definido' }}
                                                </p>
                                            </div>

                                            <div class="flex flex-wrap gap-2">
                                                @if (! ($turnoDiagnostico['plantilla_regular_existe'] ?? false))
                                                    <span class="ui-badge-warning">Regular faltante</span>
                                                @else
                                                    <span class="ui-badge-success">Regular creada</span>
                                                @endif

                                                @if ($turnoDiagnostico['plantilla_invierno_existe'] ?? false)
                                                    <span class="ui-badge-violet">Invierno disponible</span>
                                                @else
                                                    <span class="ui-badge-muted">Sin invierno</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                                            <div class="ui-card-soft p-4">
                                                <p class="ui-muted text-xs">Bloques totales</p>
                                                <p class="ui-title mt-1 text-xl font-black">
                                                    {{ $turnoDiagnostico['bloques_totales'] ?? 0 }}
                                                </p>
                                            </div>

                                            <div class="ui-card-soft p-4">
                                                <p class="ui-muted text-xs">Sin plantilla</p>
                                                <p class="ui-title mt-1 text-xl font-black">
                                                    {{ $turnoDiagnostico['bloques_sin_plantilla'] ?? 0 }}
                                                </p>
                                            </div>

                                            <div class="ui-card-soft p-4">
                                                <p class="ui-muted text-xs">Fuera del turno</p>
                                                <p class="ui-title mt-1 text-xl font-black">
                                                    {{ $turnoDiagnostico['bloques_fuera_turno'] ?? 0 }}
                                                </p>
                                            </div>

                                            <div class="ui-card-soft p-4">
                                                <p class="ui-muted text-xs">Invierno solapado</p>
                                                <p class="ui-title mt-1 text-xl font-black">
                                                    {{ $turnoDiagnostico['plantillas_invierno_solapadas'] ?? 0 }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="ui-alert-info mt-4">
                                            <div class="flex gap-3">
                                                {!! $icon('bolt', 'h-5 w-5 shrink-0') !!}

                                                <div>
                                                    <p class="font-black">Acción recomendada</p>
                                                    <p class="mt-1 text-xs leading-5">
                                                        {{ $turnoDiagnostico['accion_sugerida'] ?? 'Sin acción sugerida.' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                @empty
                                    <div class="ui-card-soft p-10 text-center">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl ui-badge-muted">
                                            {!! $icon('calendar', 'h-7 w-7') !!}
                                        </div>

                                        <h4 class="ui-title mt-4 font-black">
                                            No existen turnos para diagnosticar
                                        </h4>

                                        <p class="ui-muted mt-2 text-sm">
                                            Registra turnos académicos antes de ejecutar la auditoría estructural.
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- VISTA PREVIA RESUMIDA --}}
                        <div class="ui-panel">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <p class="ui-kicker">Aplicación horaria</p>
                                    <h4 class="ui-title mt-1 text-xl font-black">
                                        Regular e invierno
                                    </h4>
                                    <p class="ui-muted mt-1 text-sm leading-6">
                                        El horario regular se mantiene como base y el invierno actúa como reemplazo temporal.
                                    </p>
                                </div>

                                <button type="button" wire:click="abrirVistaPreviaAplicacion" class="ui-btn-secondary">
                                    Ver agenda
                                </button>
                            </div>

                            <div class="mt-4 space-y-3">
                                @if (($vistaPrevia['disponible'] ?? false) && ! empty($vistaPrevia['segmentos']))
                                    @foreach (array_slice($vistaPrevia['segmentos'], 0, 4) as $segmento)
                                        <div class="ui-card-soft grid gap-3 p-4 md:grid-cols-[140px_1fr_auto] md:items-center">
                                            <div>
                                                <p class="ui-muted text-xs">Turno</p>
                                                <p class="ui-title font-black">
                                                    {{ $segmento['turno'] ?? 'Turno' }}
                                                </p>
                                            </div>

                                            <div>
                                                <p class="ui-title font-black">
                                                    {{ $segmento['plantilla'] ?? 'Plantilla' }}
                                                </p>
                                                <p class="ui-muted mt-1 text-xs">
                                                    {{ $segmento['fecha_inicio'] ?? '-' }}
                                                    al
                                                    {{ $segmento['fecha_fin'] ?? '-' }}
                                                </p>
                                            </div>

                                            <span class="{{ $badgeTipo($segmento['tipo'] ?? null) }}">
                                                {{ ucfirst(strtolower($segmento['tipo'] ?? 'Regular')) }}
                                            </span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="ui-alert-warning">
                                        {{ $vistaPrevia['mensaje'] ?? 'No existe una vista previa disponible para la gestión actual.' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </section>
                </div>

                <div class="ui-modal-footer flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="ui-muted text-xs leading-5">
                        La auditoría solo corrige asociaciones y estructura. No elimina horarios, bloques ni plantillas.
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="cerrarModalAuditoria" class="ui-btn-secondary">
                            Cerrar
                        </button>

                        <button
                            type="button"
                            wire:click="corregirEstructuraHoraria"
                            wire:loading.attr="disabled"
                            wire:target="corregirEstructuraHoraria"
                            @disabled(! $puedeCorregir)
                            class="{{ $puedeCorregir ? 'ui-btn-primary' : 'ui-btn-secondary' }}"
                        >
                            <span wire:loading.remove wire:target="corregirEstructuraHoraria">
                                Aplicar corrección segura
                            </span>
                            <span wire:loading wire:target="corregirEstructuraHoraria">
                                Corrigiendo...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL APLICAR PLANTILLA --}}
    @if ($modalAplicarPlantilla && $plantillaParaAplicar)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal w-full max-w-3xl overflow-hidden">
                <div class="ui-modal-header flex items-center justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Aplicación de plantilla</p>
                        <h3 class="ui-title text-xl font-black">
                            ¿Aplicar esta plantilla horaria?
                        </h3>
                        <p class="ui-muted mt-1 text-sm">
                            Confirma la plantilla que quedará como referencia activa para el turno seleccionado.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarModalAplicarPlantilla" class="ui-icon-btn">
                        {!! $icon('x') !!}
                    </button>
                </div>

                <div class="space-y-5 p-5">
                    <div class="ui-alert-info">
                        <div class="flex gap-3">
                            <div class="shrink-0">
                                {!! $icon('layers', 'h-5 w-5') !!}
                            </div>

                            <div>
                                <p class="font-black">Control de aplicación</p>
                                <p class="mt-1 text-xs leading-5">
                                    Al aplicar esta plantilla, quedará como referencia activa para su tipo dentro del turno. Las demás plantillas del mismo tipo y turno quedarán como no aplicadas.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="ui-card-soft p-5">
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div>
                                <p class="ui-kicker">Plantilla seleccionada</p>
                                <h4 class="ui-title mt-1 text-2xl font-black">
                                    {{ $plantillaParaAplicar['nombre'] ?? 'Plantilla horaria' }}
                                </h4>

                                <p class="ui-muted mt-2 text-sm">
                                    {{ $plantillaParaAplicar['turno']['nombre'] ?? 'Turno no definido' }}
                                    @if (! empty($plantillaParaAplicar['turno']['rango']))
                                        · {{ $plantillaParaAplicar['turno']['rango'] }}
                                    @endif
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2 md:justify-end">
                                <span class="{{ $badgeTipo($plantillaParaAplicar['tipo'] ?? null) }}">
                                    {{ ucfirst(strtolower($plantillaParaAplicar['tipo'] ?? 'Regular')) }}
                                </span>

                                <span class="{{ ($plantillaParaAplicar['aplicada'] ?? false) ? 'ui-badge-success' : 'ui-badge-muted' }}">
                                    {{ ($plantillaParaAplicar['aplicada'] ?? false) ? 'Actualmente aplicada' : 'No aplicada' }}
                                </span>

                                <span class="{{ ($plantillaParaAplicar['activa'] ?? false) ? 'ui-badge-success' : 'ui-badge-warning' }}">
                                    {{ ($plantillaParaAplicar['activa'] ?? false) ? 'Activa' : 'Inactiva' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-3 md:grid-cols-3">
                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs">Tipo</p>
                                <p class="ui-title mt-1 font-black">
                                    {{ ucfirst(strtolower($plantillaParaAplicar['tipo'] ?? 'Regular')) }}
                                </p>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs">Vigencia</p>
                                <p class="ui-title mt-1 font-black">
                                    {{ $plantillaParaAplicar['vigencia'] ?? 'Base anual' }}
                                </p>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="ui-muted text-xs">Bloques</p>
                                <p class="ui-title mt-1 font-black">
                                    {{ $plantillaParaAplicar['bloques_total'] ?? 0 }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if (($plantillaParaAplicar['tipo'] ?? '') === 'REGULAR')
                        <div class="ui-alert-success">
                            <div class="flex gap-3">
                                <div class="shrink-0">
                                    {!! $icon('check', 'h-5 w-5') !!}
                                </div>

                                <div>
                                    <p class="font-black">Aplicación como base regular</p>
                                    <p class="mt-1 text-xs leading-5">
                                        Esta plantilla será tomada como la estructura horaria regular del turno. Si existe horario de invierno configurado, este podrá reemplazarla temporalmente durante su vigencia.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (($plantillaParaAplicar['tipo'] ?? '') === 'INVIERNO')
                        <div class="ui-alert-warning">
                            <div class="flex gap-3">
                                <div class="shrink-0">
                                    {!! $icon('snow', 'h-5 w-5') !!}
                                </div>

                                <div>
                                    <p class="font-black">Aplicación como horario de invierno</p>
                                    <p class="mt-1 text-xs leading-5">
                                        Esta plantilla no elimina ni reemplaza permanentemente la plantilla regular. Solo se aplicará como referencia temporal durante su rango de vigencia.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (($plantillaParaAplicar['bloques_total'] ?? 0) <= 0)
                        <div class="ui-alert-warning">
                            <div class="flex gap-3">
                                <div class="shrink-0">
                                    {!! $icon('warning', 'h-5 w-5') !!}
                                </div>

                                <div>
                                    <p class="font-black">Plantilla sin bloques</p>
                                    <p class="mt-1 text-xs leading-5">
                                        La plantilla puede aplicarse, pero no será útil para construir horarios hasta que tenga bloques configurados. Se recomienda registrar bloques antes de usarla en planificación académica.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="ui-card-soft p-5">
                        <p class="ui-kicker">Impacto de la acción</p>

                        <div class="mt-4 space-y-3">
                            <div class="flex gap-3">
                                <div class="mt-0.5 shrink-0">
                                    {!! $icon('check', 'h-4 w-4') !!}
                                </div>
                                <p class="ui-muted text-sm leading-6">
                                    La plantilla seleccionada quedará marcada como aplicada para su tipo.
                                </p>
                            </div>

                            <div class="flex gap-3">
                                <div class="mt-0.5 shrink-0">
                                    {!! $icon('check', 'h-4 w-4') !!}
                                </div>
                                <p class="ui-muted text-sm leading-6">
                                    Las demás plantillas del mismo tipo y turno quedarán como no aplicadas.
                                </p>
                            </div>

                            <div class="flex gap-3">
                                <div class="mt-0.5 shrink-0">
                                    {!! $icon('check', 'h-4 w-4') !!}
                                </div>
                                <p class="ui-muted text-sm leading-6">
                                    La acción será registrada para conservar trazabilidad institucional.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="ui-muted text-xs leading-5">
                        Esta acción no elimina datos. Solo cambia la referencia activa de la plantilla.
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="cerrarModalAplicarPlantilla" class="ui-btn-secondary">
                            Cancelar
                        </button>

                        <button
                            type="button"
                            wire:click="aplicarPlantilla"
                            wire:loading.attr="disabled"
                            wire:target="aplicarPlantilla"
                            class="ui-btn-primary"
                        >
                            <span wire:loading.remove wire:target="aplicarPlantilla">
                                Aplicar plantilla
                            </span>
                            <span wire:loading wire:target="aplicarPlantilla">
                                Aplicando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL DESACTIVAR REGISTRO --}}
    @if ($modalDesactivar && $registroParaDesactivar)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal my-6 flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden">
                <div class="ui-modal-header sticky top-0 z-10 flex items-center justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Desactivación lógica</p>
                        <h3 class="ui-title text-xl font-black">
                            ¿Desactivar este registro?
                        </h3>
                        <p class="ui-muted mt-1 text-sm">
                            El registro no será eliminado físicamente. Se conservará su historial y trazabilidad institucional.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarModalDesactivar" class="ui-icon-btn">
                        {!! $icon('x') !!}
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-5">
                    <div class="grid gap-5 xl:grid-cols-[360px_1fr]">
                        {{-- COLUMNA IZQUIERDA --}}
                        <aside class="space-y-4">
                            <div class="ui-alert-warning">
                                <div class="flex gap-3">
                                    <div class="shrink-0">
                                        {!! $icon('warning', 'h-5 w-5') !!}
                                    </div>

                                    <div>
                                        <p class="font-black">Acción sensible</p>
                                        <p class="mt-1 text-xs leading-5">
                                            Desactivar no elimina datos, pero puede afectar la disponibilidad del registro para futuras planificaciones académicas.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="ui-card-soft p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="ui-kicker">Registro seleccionado</p>

                                        <h4 class="ui-title mt-1 text-2xl font-black">
                                            @switch($registroParaDesactivar['tipo'] ?? null)
                                                @case('turno')
                                                    Turno académico
                                                    @break

                                                @case('plantilla')
                                                    Plantilla horaria
                                                    @break

                                                @case('bloque')
                                                    Bloque horario
                                                    @break

                                                @default
                                                    Registro académico
                                            @endswitch
                                        </h4>

                                        <p class="ui-muted mt-2 text-sm">
                                            Baja lógica con conservación de trazabilidad.
                                        </p>
                                    </div>

                                    <span class="{{ $badgeRevision($registroParaDesactivar['analisis']['estado'] ?? 'OBSERVADO') }}">
                                        {{ $registroParaDesactivar['analisis']['estado'] ?? 'OBSERVADO' }}
                                    </span>
                                </div>

                                <div class="mt-5 space-y-3">
                                    <div class="ui-card-soft flex items-center justify-between gap-3 p-4">
                                        <span class="ui-muted text-xs font-bold">Tipo</span>
                                        <span class="ui-title text-sm font-black">
                                            @switch($registroParaDesactivar['tipo'] ?? null)
                                                @case('turno')
                                                    Turno
                                                    @break

                                                @case('plantilla')
                                                    Plantilla
                                                    @break

                                                @case('bloque')
                                                    Bloque
                                                    @break

                                                @default
                                                    Registro
                                            @endswitch
                                        </span>
                                    </div>

                                    <div class="ui-card-soft flex items-center justify-between gap-3 p-4">
                                        <span class="ui-muted text-xs font-bold">Riesgo</span>
                                        <span class="ui-title text-sm font-black">
                                            {{ $registroParaDesactivar['analisis']['nivel_riesgo'] ?? 'BAJO' }}
                                        </span>
                                    </div>

                                    <div class="ui-card-soft flex items-center justify-between gap-3 p-4">
                                        <span class="ui-muted text-xs font-bold">Resultado</span>
                                        <span class="ui-title text-sm font-black">
                                            {{ ($registroParaDesactivar['analisis']['puede_continuar'] ?? false) ? 'Permitido' : 'Requiere revisión' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            @if (($registroParaDesactivar['analisis']['puede_continuar'] ?? false))
                                <div class="ui-alert-info">
                                    <div class="flex gap-3">
                                        <div class="shrink-0">
                                            {!! $icon('check', 'h-5 w-5') !!}
                                        </div>

                                        <div>
                                            <p class="font-black">Desactivación permitida</p>
                                            <p class="mt-1 text-xs leading-5">
                                                El registro dejará de estar disponible para nuevas configuraciones, pero seguirá existiendo en el historial institucional.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="ui-alert-danger">
                                    <div class="flex gap-3">
                                        <div class="shrink-0">
                                            {!! $icon('warning', 'h-5 w-5') !!}
                                        </div>

                                        <div>
                                            <p class="font-black">No se recomienda continuar</p>
                                            <p class="mt-1 text-xs leading-5">
                                                El sistema detectó bloqueos o uso académico sensible. Revisa el registro antes de desactivarlo.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </aside>

                        {{-- COLUMNA DERECHA --}}
                        <section class="space-y-4">
                            <div class="ui-panel">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="ui-kicker">Revisión del sistema</p>
                                        <p class="ui-subtitle mt-3 text-sm font-bold leading-6">
                                            {{ $registroParaDesactivar['analisis']['mensaje'] ?? 'Revisa el registro antes de continuar.' }}
                                        </p>
                                    </div>

                                    <span class="{{ $badgeRevision($registroParaDesactivar['analisis']['estado'] ?? 'OBSERVADO') }}">
                                        {{ $registroParaDesactivar['analisis']['estado'] ?? 'OBSERVADO' }}
                                    </span>
                                </div>

                                @if (! empty($registroParaDesactivar['analisis']['resumen']))
                                    <div class="ui-card-soft mt-4 p-4">
                                        <p class="ui-kicker">Resumen de uso</p>

                                        <div class="mt-3 space-y-2">
                                            @foreach ($registroParaDesactivar['analisis']['resumen'] as $key => $value)
                                                @if (is_scalar($value) || is_null($value))
                                                    <div class="flex items-center justify-between gap-4 text-xs">
                                                        <span class="ui-muted font-bold">
                                                            {{ str($key)->replace('_', ' ')->title() }}
                                                        </span>

                                                        <span class="ui-title text-right font-black">
                                                            {{ is_bool($value) ? ($value ? 'Sí' : 'No') : ($value ?? '—') }}
                                                        </span>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                @if (! empty($registroParaDesactivar['analisis']['bloqueos']))
                                    <div class="mt-4 space-y-2">
                                        <p class="ui-kicker">Bloqueos</p>

                                        @foreach ($registroParaDesactivar['analisis']['bloqueos'] as $mensaje)
                                            <div class="ui-alert-danger">
                                                {{ $mensaje }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if (! empty($registroParaDesactivar['analisis']['advertencias']))
                                    <div class="mt-4 space-y-2">
                                        <p class="ui-kicker">Observaciones</p>

                                        @foreach ($registroParaDesactivar['analisis']['advertencias'] as $mensaje)
                                            <div class="ui-alert-warning">
                                                {{ $mensaje }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @if (! empty($registroParaDesactivar['analisis']['sugerencias']))
                                    <div class="mt-4 space-y-2">
                                        <p class="ui-kicker">Recomendaciones</p>

                                        @foreach ($registroParaDesactivar['analisis']['sugerencias'] as $mensaje)
                                            <div class="ui-alert-success">
                                                {{ $mensaje }}
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="ui-panel">
                                <p class="ui-kicker">Impacto de la desactivación</p>

                                <div class="mt-4 grid gap-3 md:grid-cols-3">
                                    <div class="ui-card-soft p-4">
                                        <div class="flex gap-3">
                                            <div class="shrink-0">
                                                {!! $icon('check', 'h-4 w-4') !!}
                                            </div>
                                            <p class="ui-muted text-xs leading-5">
                                                El registro quedará marcado como inactivo.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="ui-card-soft p-4">
                                        <div class="flex gap-3">
                                            <div class="shrink-0">
                                                {!! $icon('check', 'h-4 w-4') !!}
                                            </div>
                                            <p class="ui-muted text-xs leading-5">
                                                No se eliminarán relaciones ni historial.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="ui-card-soft p-4">
                                        <div class="flex gap-3">
                                            <div class="shrink-0">
                                                {!! $icon('check', 'h-4 w-4') !!}
                                            </div>
                                            <p class="ui-muted text-xs leading-5">
                                                La acción será registrada en bitácora.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="ui-modal-footer sticky bottom-0 z-10 flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="ui-muted text-xs leading-5">
                        La desactivación es lógica. No se ejecuta eliminación física.
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="cerrarModalDesactivar" class="ui-btn-secondary">
                            Cancelar
                        </button>

                        <button
                            type="button"
                            wire:click="desactivarRegistro"
                            wire:loading.attr="disabled"
                            wire:target="desactivarRegistro"
                            class="{{ ($registroParaDesactivar['analisis']['puede_continuar'] ?? false) ? 'ui-btn-danger' : 'ui-btn-secondary' }}"
                            @disabled(! ($registroParaDesactivar['analisis']['puede_continuar'] ?? false))
                        >
                            <span wire:loading.remove wire:target="desactivarRegistro">
                                Desactivar registro
                            </span>
                            <span wire:loading wire:target="desactivarRegistro">
                                Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL DETALLE TURNO --}}
    @if ($modalDetalleTurno && $detalleTurno)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-6xl overflow-y-auto">
                <div class="ui-modal-header flex items-center justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Detalle de jornada</p>
                        <h3 class="ui-title text-xl font-black">
                            {{ $detalleTurno['turno']['nombre'] ?? 'Turno académico' }}
                        </h3>
                        <p class="ui-muted mt-1 text-sm">
                            {{ $detalleTurno['turno']['rango'] ?? 'Sin rango definido' }}
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarDetalleTurno" class="ui-icon-btn">
                        {!! $icon('x') !!}
                    </button>
                </div>

                <div class="space-y-6 p-5">
                    {{-- RESUMEN DEL TURNO --}}
                    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="ui-card-soft p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="ui-muted text-xs">Jornada</p>
                                    <p class="ui-title mt-1 text-xl font-black">
                                        {{ $detalleTurno['turno']['rango'] ?? 'Sin rango' }}
                                    </p>
                                </div>

                                <span class="ui-badge-success rounded-2xl p-3">
                                    {!! $icon('clock', 'h-5 w-5') !!}
                                </span>
                            </div>
                        </div>

                        <div class="ui-card-soft p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="ui-muted text-xs">Plantillas</p>
                                    <p class="ui-title mt-1 text-xl font-black">
                                        {{ $detalleTurno['uso']['plantillas'] ?? 0 }}
                                    </p>
                                </div>

                                <span class="ui-badge-success rounded-2xl p-3">
                                    {!! $icon('layers', 'h-5 w-5') !!}
                                </span>
                            </div>
                        </div>

                        <div class="ui-card-soft p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="ui-muted text-xs">Bloques</p>
                                    <p class="ui-title mt-1 text-xl font-black">
                                        {{ $detalleTurno['uso']['bloques'] ?? 0 }}
                                    </p>
                                </div>

                                <span class="ui-badge-info rounded-2xl p-3">
                                    {!! $icon('calendar', 'h-5 w-5') !!}
                                </span>
                            </div>
                        </div>

                        <div class="ui-card-soft p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="ui-muted text-xs">Sin plantilla</p>
                                    <p class="ui-title mt-1 text-xl font-black">
                                        {{ $detalleTurno['uso']['bloques_sin_plantilla'] ?? 0 }}
                                    </p>
                                </div>

                                <span class="{{ (($detalleTurno['uso']['bloques_sin_plantilla'] ?? 0) > 0) ? 'ui-badge-warning' : 'ui-badge-success' }} rounded-2xl p-3">
                                    {!! $icon((($detalleTurno['uso']['bloques_sin_plantilla'] ?? 0) > 0) ? 'warning' : 'check', 'h-5 w-5') !!}
                                </span>
                            </div>
                        </div>
                    </section>

                    @if (($detalleTurno['uso']['bloques_sin_plantilla'] ?? 0) > 0)
                        <section class="ui-alert-warning">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex gap-3">
                                    <div class="shrink-0">
                                        {!! $icon('warning', 'h-5 w-5') !!}
                                    </div>

                                    <div>
                                        <p class="font-black">Este turno tiene bloques sin plantilla asociada</p>
                                        <p class="mt-1 text-xs leading-5">
                                            Se recomienda asociarlos a la plantilla regular correspondiente para evitar inconsistencias en horarios regulares e invierno.
                                        </p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    wire:click="asociarBloquesSinPlantillaDelTurno('{{ $detalleTurno['turno']['cod_tur'] ?? '' }}')"
                                    class="ui-btn-primary shrink-0"
                                >
                                    Asociar bloques
                                </button>
                            </div>
                        </section>
                    @endif

                    <section class="grid gap-6 xl:grid-cols-[1fr_1fr]">
                        {{-- PLANTILLAS DEL TURNO --}}
                        <div class="ui-panel">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <p class="ui-kicker">Plantillas asociadas</p>
                                    <h4 class="ui-title mt-1 text-xl font-black">
                                        Configuración regular e invierno
                                    </h4>
                                    <p class="ui-muted mt-1 text-sm leading-6">
                                        Las plantillas definen la estructura de bloques utilizada por el turno.
                                    </p>
                                </div>

                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        wire:click="abrirModalCrearPlantilla('{{ $detalleTurno['turno']['cod_tur'] ?? '' }}', 'REGULAR')"
                                        class="ui-icon-btn"
                                        title="Crear plantilla regular"
                                    >
                                        {!! $icon('layers') !!}
                                    </button>

                                    <button
                                        type="button"
                                        wire:click="abrirModalCrearPlantilla('{{ $detalleTurno['turno']['cod_tur'] ?? '' }}', 'INVIERNO')"
                                        class="ui-icon-btn"
                                        title="Crear plantilla invierno"
                                    >
                                        {!! $icon('snow') !!}
                                    </button>
                                </div>
                            </div>

                            <div class="mt-5 space-y-3">
                                @forelse (($detalleTurno['plantillas'] ?? []) as $plantilla)
                                    <article class="ui-card-soft p-4">
                                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h5 class="ui-title font-black">
                                                        {{ $plantilla['nombre'] ?? 'Plantilla horaria' }}
                                                    </h5>

                                                    <span class="{{ $badgeTipo($plantilla['tipo'] ?? null) }}">
                                                        {{ ucfirst(strtolower($plantilla['tipo'] ?? 'Regular')) }}
                                                    </span>
                                                </div>

                                                <p class="ui-muted mt-1 text-xs">
                                                    {{ $plantilla['vigencia'] ?? 'Base anual' }}
                                                </p>
                                            </div>

                                            <div class="flex flex-wrap gap-2 md:justify-end">
                                                <span class="{{ ($plantilla['aplicada'] ?? false) ? 'ui-badge-success' : 'ui-badge-muted' }}">
                                                    {{ ($plantilla['aplicada'] ?? false) ? 'Aplicada' : 'No aplicada' }}
                                                </span>

                                                <span class="{{ ($plantilla['activa'] ?? false) ? 'ui-badge-success' : 'ui-badge-warning' }}">
                                                    {{ ($plantilla['activa'] ?? false) ? 'Activa' : 'Inactiva' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                            <div>
                                                <p class="ui-muted text-xs">Duración base</p>
                                                <p class="ui-title font-black">
                                                    {{ $plantilla['duracion_base'] ?? '—' }}
                                                    <span class="ui-muted text-xs">min</span>
                                                </p>
                                            </div>

                                            <div>
                                                <p class="ui-muted text-xs">Bloques</p>
                                                <p class="ui-title font-black">
                                                    {{ $plantilla['bloques_total'] ?? 0 }}
                                                </p>
                                            </div>

                                            <div>
                                                <p class="ui-muted text-xs">Uso académico</p>
                                                <p class="ui-title font-black">
                                                    {{ $plantilla['uso_academico'] ?? 'Sin uso' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <button
                                                type="button"
                                                wire:click="seleccionarPlantilla('{{ $plantilla['cod_pho'] }}')"
                                                class="ui-btn-secondary px-3 py-2 text-xs"
                                            >
                                                Ver bloques
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="abrirModalEditarPlantilla('{{ $plantilla['cod_pho'] }}')"
                                                class="ui-btn-secondary px-3 py-2 text-xs"
                                            >
                                                Editar
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="confirmarAplicarPlantilla('{{ $plantilla['cod_pho'] }}')"
                                                class="ui-btn-secondary px-3 py-2 text-xs"
                                            >
                                                Aplicar
                                            </button>

                                            @if (($plantilla['tipo'] ?? null) === 'REGULAR')
                                                <button
                                                    type="button"
                                                    wire:click="duplicarComoInvierno('{{ $plantilla['cod_pho'] }}')"
                                                    class="ui-btn-secondary px-3 py-2 text-xs"
                                                >
                                                    Duplicar invierno
                                                </button>
                                            @endif

                                            @if ($plantilla['activa'] ?? false)
                                                <button
                                                    type="button"
                                                    wire:click="confirmarDesactivar('plantilla', '{{ $plantilla['cod_pho'] }}')"
                                                    class="ui-btn-danger px-3 py-2 text-xs"
                                                >
                                                    Desactivar
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    wire:click="reactivarRegistro('plantilla', '{{ $plantilla['cod_pho'] }}')"
                                                    class="ui-btn-secondary px-3 py-2 text-xs"
                                                >
                                                    Reactivar
                                                </button>
                                            @endif
                                        </div>
                                    </article>
                                @empty
                                    <div class="ui-card-soft p-8 text-center">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl ui-badge-muted">
                                            {!! $icon('layers', 'h-7 w-7') !!}
                                        </div>

                                        <h5 class="ui-title mt-4 font-black">
                                            No existen plantillas asociadas
                                        </h5>

                                        <p class="ui-muted mt-2 text-sm">
                                            Crea una plantilla regular para estructurar los bloques del turno.
                                        </p>

                                        <div class="mt-4 flex flex-wrap justify-center gap-2">
                                            <button
                                                type="button"
                                                wire:click="abrirModalCrearPlantilla('{{ $detalleTurno['turno']['cod_tur'] ?? '' }}', 'REGULAR')"
                                                class="ui-btn-primary"
                                            >
                                                Crear regular
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="abrirModalCrearPlantilla('{{ $detalleTurno['turno']['cod_tur'] ?? '' }}', 'INVIERNO')"
                                                class="ui-btn-secondary"
                                            >
                                                Crear invierno
                                            </button>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- BLOQUES DEL TURNO --}}
                        <div class="ui-panel">
                            <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                <div>
                                    <p class="ui-kicker">Bloques asociados</p>
                                    <h4 class="ui-title mt-1 text-xl font-black">
                                        Secuencia horaria del turno
                                    </h4>
                                    <p class="ui-muted mt-1 text-sm leading-6">
                                        Los bloques deben estar vinculados a una plantilla para que puedan formar horarios consistentes.
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    wire:click="abrirModalCrearBloque"
                                    class="ui-icon-btn"
                                    title="Crear bloque"
                                >
                                    {!! $icon('plus') !!}
                                </button>
                            </div>

                            <div class="mt-5 space-y-3">
                                @forelse (($detalleTurno['bloques'] ?? []) as $bloque)
                                    <article class="ui-card-soft p-4">
                                        <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h5 class="ui-title font-black">
                                                        {{ $bloque['nombre'] ?? 'Bloque horario' }}
                                                    </h5>

                                                    <span class="{{ $badgeTipo(($bloque['tipo'] ?? 'CLASE') === 'CLASE' ? 'REGULAR' : (($bloque['tipo'] ?? '') === 'RECREO' ? 'AJUSTE' : null)) }}">
                                                        {{ ucfirst(strtolower($bloque['tipo'] ?? 'Clase')) }}
                                                    </span>
                                                </div>

                                                <p class="ui-muted mt-1 text-xs">
                                                    {{ $bloque['rango'] ?? 'Sin rango' }}
                                                    @if (! empty($bloque['duracion']))
                                                        · {{ $bloque['duracion'] }} min
                                                    @endif
                                                </p>
                                            </div>

                                            <span class="{{ $badgeEstado($bloque['estado'] ?? 'ACTIVO') }}">
                                                {{ ucfirst(strtolower($bloque['estado'] ?? 'Activo')) }}
                                            </span>
                                        </div>

                                        @if (! empty($bloque['observacion']))
                                            <div class="ui-alert-info mt-4">
                                                <p class="text-xs leading-5">
                                                    {{ $bloque['observacion'] }}
                                                </p>
                                            </div>
                                        @endif

                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <button
                                                type="button"
                                                wire:click="abrirModalEditarBloque('{{ $bloque['cod_hbl'] }}')"
                                                class="ui-btn-secondary px-3 py-2 text-xs"
                                            >
                                                Editar
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="reordenarBloque('arriba', '{{ $bloque['cod_hbl'] }}')"
                                                class="ui-btn-secondary px-3 py-2 text-xs"
                                            >
                                                Subir
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="reordenarBloque('abajo', '{{ $bloque['cod_hbl'] }}')"
                                                class="ui-btn-secondary px-3 py-2 text-xs"
                                            >
                                                Bajar
                                            </button>

                                            @if (($bloque['estado'] ?? 'ACTIVO') === 'ACTIVO')
                                                <button
                                                    type="button"
                                                    wire:click="confirmarDesactivar('bloque', '{{ $bloque['cod_hbl'] }}')"
                                                    class="ui-btn-danger px-3 py-2 text-xs"
                                                >
                                                    Desactivar
                                                </button>
                                            @else
                                                <button
                                                    type="button"
                                                    wire:click="reactivarRegistro('bloque', '{{ $bloque['cod_hbl'] }}')"
                                                    class="ui-btn-secondary px-3 py-2 text-xs"
                                                >
                                                    Reactivar
                                                </button>
                                            @endif
                                        </div>
                                    </article>
                                @empty
                                    <div class="ui-card-soft p-8 text-center">
                                        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl ui-badge-muted">
                                            {!! $icon('clock', 'h-7 w-7') !!}
                                        </div>

                                        <h5 class="ui-title mt-4 font-black">
                                            No existen bloques asociados
                                        </h5>

                                        <p class="ui-muted mt-2 text-sm">
                                            Selecciona una plantilla y registra los bloques correspondientes.
                                        </p>

                                        <button
                                            type="button"
                                            wire:click="abrirModalCrearBloque"
                                            class="ui-btn-primary mt-4"
                                        >
                                            Crear bloque
                                        </button>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </section>

                    {{-- ACCIONES RÁPIDAS --}}
                    <section class="ui-panel">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <p class="ui-kicker">Acciones rápidas</p>
                                <h4 class="ui-title mt-1 text-xl font-black">
                                    Gestión del turno
                                </h4>
                                <p class="ui-muted mt-1 text-sm leading-6">
                                    Puedes preparar plantillas, crear bloques o ejecutar corrección estructural del turno.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2 lg:justify-end">
                                <button
                                    type="button"
                                    wire:click="crearPlantillaRegularParaTurno('{{ $detalleTurno['turno']['cod_tur'] ?? '' }}')"
                                    class="ui-btn-secondary"
                                >
                                    {!! $icon('layers', 'h-4 w-4') !!}
                                    Asegurar regular
                                </button>

                                <button
                                    type="button"
                                    wire:click="asociarBloquesSinPlantillaDelTurno('{{ $detalleTurno['turno']['cod_tur'] ?? '' }}')"
                                    class="ui-btn-secondary"
                                >
                                    {!! $icon('bolt', 'h-4 w-4') !!}
                                    Asociar bloques
                                </button>

                                <button
                                    type="button"
                                    wire:click="abrirModalEditarTurno('{{ $detalleTurno['turno']['cod_tur'] ?? '' }}')"
                                    class="ui-btn-primary"
                                >
                                    {!! $icon('edit', 'h-4 w-4') !!}
                                    Editar turno
                                </button>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="ui-modal-footer flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="ui-muted text-xs leading-5">
                        Este detalle muestra información operativa sin exponer códigos internos del sistema.
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="cerrarDetalleTurno" class="ui-btn-secondary">
                            Cerrar
                        </button>

                        <button
                            type="button"
                            wire:click="abrirModalEditarTurno('{{ $detalleTurno['turno']['cod_tur'] ?? '' }}')"
                            class="ui-btn-primary"
                        >
                            Editar turno
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL VISTA PREVIA DE APLICACIÓN HORARIA --}}
    @if ($modalVistaPrevia)
        <div class="savp-modal-wrap">
            <div class="ui-modal-backdrop"></div>

            <div class="ui-modal max-h-[92vh] w-full max-w-6xl overflow-y-auto">
                <div class="ui-modal-header flex items-center justify-between gap-4">
                    <div>
                        <p class="ui-kicker">Vista previa de aplicación horaria</p>
                        <h3 class="ui-title text-xl font-black">
                            Agenda regular e invierno
                        </h3>
                        <p class="ui-muted mt-1 text-sm">
                            Visualiza cómo se aplican las plantillas durante la gestión académica activa o planificada.
                        </p>
                    </div>

                    <button type="button" wire:click="cerrarVistaPreviaAplicacion" class="ui-icon-btn">
                        {!! $icon('x') !!}
                    </button>
                </div>

                <div class="space-y-6 p-5">
                    {{-- ESTADO GENERAL --}}
                    @if ($vistaPrevia['disponible'] ?? false)
                        <section class="ui-alert-info">
                            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                <div class="flex gap-3">
                                    <div class="shrink-0">
                                        {!! $icon('calendar', 'h-5 w-5') !!}
                                    </div>

                                    <div>
                                        <p class="font-black">Gestión académica detectada</p>
                                        <p class="mt-1 text-xs leading-5">
                                            @if (! empty($vistaPrevia['gestion']))
                                                Gestión {{ $vistaPrevia['gestion']['anio'] ?? 'actual' }}
                                                · {{ $vistaPrevia['gestion']['inicio'] ?? 'sin inicio' }}
                                                al {{ $vistaPrevia['gestion']['fin'] ?? 'sin cierre' }}.
                                            @else
                                                La vista previa se generó con la información disponible del sistema.
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <button type="button" wire:click="actualizarVistaPrevia" class="ui-btn-secondary shrink-0">
                                    {!! $icon('timeline', 'h-4 w-4') !!}
                                    Recalcular vista
                                </button>
                            </div>
                        </section>
                    @else
                        <section class="ui-alert-warning">
                            <div class="flex gap-3">
                                <div class="shrink-0">
                                    {!! $icon('warning', 'h-5 w-5') !!}
                                </div>

                                <div>
                                    <p class="font-black">Vista previa no disponible</p>
                                    <p class="mt-1 text-xs leading-5">
                                        {{ $vistaPrevia['mensaje'] ?? 'No se pudo generar la agenda de aplicación horaria. Verifica que exista una gestión académica activa o planificada.' }}
                                    </p>
                                </div>
                            </div>
                        </section>
                    @endif

                    {{-- RESUMEN CONCEPTUAL --}}
                    <section class="grid gap-4 md:grid-cols-3">
                        <div class="ui-card-soft p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="ui-muted text-xs">Plantilla regular</p>
                                    <h4 class="ui-title mt-1 text-lg font-black">
                                        Base anual
                                    </h4>
                                    <p class="ui-muted mt-2 text-sm leading-6">
                                        Se mantiene como referencia principal del turno durante la gestión.
                                    </p>
                                </div>

                                <span class="ui-badge-success rounded-2xl p-3">
                                    {!! $icon('layers', 'h-5 w-5') !!}
                                </span>
                            </div>
                        </div>

                        <div class="ui-card-soft p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="ui-muted text-xs">Plantilla invierno</p>
                                    <h4 class="ui-title mt-1 text-lg font-black">
                                        Reemplazo temporal
                                    </h4>
                                    <p class="ui-muted mt-2 text-sm leading-6">
                                        Sustituye temporalmente la regular solo durante su rango de vigencia.
                                    </p>
                                </div>

                                <span class="ui-badge-violet rounded-2xl p-3">
                                    {!! $icon('snow', 'h-5 w-5') !!}
                                </span>
                            </div>
                        </div>

                        <div class="ui-card-soft p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="ui-muted text-xs">Control de fechas</p>
                                    <h4 class="ui-title mt-1 text-lg font-black">
                                        Dentro de gestión
                                    </h4>
                                    <p class="ui-muted mt-2 text-sm leading-6">
                                        Las plantillas temporales deben mantenerse dentro del rango de la gestión académica.
                                    </p>
                                </div>

                                <span class="ui-badge-info rounded-2xl p-3">
                                    {!! $icon('calendar', 'h-5 w-5') !!}
                                </span>
                            </div>
                        </div>
                    </section>

                    {{-- AGENDA PRINCIPAL --}}
                    <section class="ui-panel">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="ui-kicker">Agenda de aplicación</p>
                                <h4 class="ui-title mt-1 text-xl font-black">
                                    Secuencia regular e invierno
                                </h4>
                                <p class="ui-muted mt-1 text-sm leading-6">
                                    El sistema divide la gestión en segmentos para mostrar cuándo aplica la plantilla regular y cuándo entra el horario de invierno.
                                </p>
                            </div>

                            <div class="flex flex-wrap gap-2 lg:justify-end">
                                <span class="ui-badge-success">Regular</span>
                                <span class="ui-badge-violet">Invierno</span>
                            </div>
                        </div>

                        <div class="mt-6 space-y-4">
                            @if (($vistaPrevia['disponible'] ?? false) && ! empty($vistaPrevia['segmentos']))
                                @foreach (($vistaPrevia['segmentos'] ?? []) as $index => $segmento)
                                    <article class="ui-card-soft p-4">
                                        <div class="grid gap-4 lg:grid-cols-[150px_1fr_auto] lg:items-center">
                                            <div>
                                                <p class="ui-muted text-xs">Turno</p>
                                                <p class="ui-title mt-1 font-black">
                                                    {{ $segmento['turno'] ?? 'Turno' }}
                                                </p>
                                            </div>

                                            <div>
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h5 class="ui-title font-black">
                                                        {{ $segmento['plantilla'] ?? 'Plantilla horaria' }}
                                                    </h5>

                                                    <span class="{{ $badgeTipo($segmento['tipo'] ?? null) }}">
                                                        {{ ucfirst(strtolower($segmento['tipo'] ?? 'Regular')) }}
                                                    </span>
                                                </div>

                                                <p class="ui-muted mt-1 text-sm">
                                                    {{ $segmento['fecha_inicio'] ?? '-' }}
                                                    al
                                                    {{ $segmento['fecha_fin'] ?? '-' }}
                                                </p>

                                                @if (! empty($segmento['mensaje']))
                                                    <p class="ui-muted mt-1 text-xs leading-5">
                                                        {{ $segmento['mensaje'] }}
                                                    </p>
                                                @endif
                                            </div>

                                            <div class="flex lg:justify-end">
                                                <span class="{{ ($segmento['tipo'] ?? '') === 'INVIERNO' ? 'ui-badge-violet' : 'ui-badge-success' }}">
                                                    Segmento {{ $index + 1 }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mt-4 overflow-hidden rounded-full" style="height: 10px; background: var(--ui-surface-muted);">
                                            <div
                                                class="h-full rounded-full transition-all duration-700"
                                                style="
                                                    width: {{ ($segmento['tipo'] ?? '') === 'INVIERNO' ? '70%' : '100%' }};
                                                    background: {{ ($segmento['tipo'] ?? '') === 'INVIERNO' ? 'var(--ui-violet)' : 'var(--ui-primary)' }};
                                                "
                                            ></div>
                                        </div>
                                    </article>
                                @endforeach
                            @else
                                <div class="ui-card-soft p-10 text-center">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl ui-badge-muted">
                                        {!! $icon('timeline', 'h-8 w-8') !!}
                                    </div>

                                    <h5 class="ui-title mt-4 text-lg font-black">
                                        No hay segmentos para mostrar
                                    </h5>

                                    <p class="ui-muted mt-2 text-sm leading-6">
                                        Crea plantillas regulares y, si corresponde, una plantilla de invierno con fechas válidas dentro de la gestión académica.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </section>

                    {{-- SIMULACIÓN DEL FORMULARIO --}}
                    @if (! empty($vistaPrevia['simulacion_formulario']))
                        @php
                            $simulacion = $vistaPrevia['simulacion_formulario'];
                        @endphp

                        <section class="ui-alert-info">
                            <div class="flex gap-3">
                                <div class="shrink-0">
                                    {!! $icon('eye', 'h-5 w-5') !!}
                                </div>

                                <div>
                                    <p class="font-black">Simulación de la plantilla en edición</p>
                                    <p class="mt-1 text-xs leading-5">
                                        {{ $simulacion['plantilla'] ?? 'Plantilla' }}
                                        para el turno {{ $simulacion['turno'] ?? 'seleccionado' }},
                                        desde {{ $simulacion['fecha_inicio'] ?? '-' }}
                                        hasta {{ $simulacion['fecha_fin'] ?? '-' }}.
                                    </p>
                                </div>
                            </div>
                        </section>
                    @endif

                    {{-- RECOMENDACIONES --}}
                    <section class="ui-panel">
                        <p class="ui-kicker">Recomendaciones de uso</p>

                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            <div class="ui-card-soft p-4">
                                <div class="flex gap-3">
                                    <div class="shrink-0">
                                        {!! $icon('check', 'h-4 w-4') !!}
                                    </div>
                                    <p class="ui-muted text-sm leading-6">
                                        Mantén una plantilla regular por turno como estructura base.
                                    </p>
                                </div>
                            </div>

                            <div class="ui-card-soft p-4">
                                <div class="flex gap-3">
                                    <div class="shrink-0">
                                        {!! $icon('check', 'h-4 w-4') !!}
                                    </div>
                                    <p class="ui-muted text-sm leading-6">
                                        Usa invierno solo como rango temporal dentro de la gestión.
                                    </p>
                                </div>
                            </div>

                            <div class="ui-card-soft p-4">
                                <div class="flex gap-3">
                                    <div class="shrink-0">
                                        {!! $icon('check', 'h-4 w-4') !!}
                                    </div>
                                    <p class="ui-muted text-sm leading-6">
                                        Evita modificar bloques usados por horarios académicos; crea una nueva plantilla si el cambio es fuerte.
                                    </p>
                                </div>
                            </div>

                            <div class="ui-card-soft p-4">
                                <div class="flex gap-3">
                                    <div class="shrink-0">
                                        {!! $icon('check', 'h-4 w-4') !!}
                                    </div>
                                    <p class="ui-muted text-sm leading-6">
                                        Verifica que la plantilla de invierno no se cruce con otra plantilla temporal del mismo turno.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="ui-modal-footer flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="ui-muted text-xs leading-5">
                        La vista previa no modifica datos. Solo muestra cómo se aplicará la estructura horaria durante la gestión.
                    </div>

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="cerrarVistaPreviaAplicacion" class="ui-btn-secondary">
                            Cerrar
                        </button>

                        <button type="button" wire:click="actualizarVistaPrevia" class="ui-btn-primary">
                            Recalcular vista
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <script>
        function gestionTurnosUI() {
            return {
                init() {
                    this.configurarEventosLivewire();
                    this.configurarTeclas();
                },

                swalBase() {
                    return {
                        customClass: {
                            popup: 'savp-swal',
                            confirmButton: 'savp-confirm',
                            cancelButton: 'savp-cancel',
                        },
                        buttonsStyling: true,
                    };
                },

                configurarEventosLivewire() {
                    window.addEventListener('success-general', event => {
                        if (!window.Swal) return;

                        Swal.fire({
                            ...this.swalBase(),
                            icon: 'success',
                            title: 'Operación completada',
                            text: event.detail.mensaje || 'La acción fue realizada correctamente.',
                            toast: true,
                            position: 'top-end',
                            timer: 2600,
                            showConfirmButton: false,
                            timerProgressBar: true,
                        });
                    });

                    window.addEventListener('error-general', event => {
                        if (!window.Swal) return;

                        Swal.fire({
                            ...this.swalBase(),
                            icon: 'warning',
                            title: 'Revisión requerida',
                            text: event.detail.mensaje || 'El sistema encontró una observación que debe revisarse.',
                            confirmButtonText: 'Entendido',
                        });
                    });
                },

                configurarTeclas() {
                    document.addEventListener('keydown', event => {
                        if (event.key !== 'Escape') return;

                        const activeElement = document.activeElement;

                        if (activeElement && ['INPUT', 'TEXTAREA', 'SELECT'].includes(activeElement.tagName)) {
                            activeElement.blur();
                        }
                    });
                },
            };
        }
    </script>
</div>