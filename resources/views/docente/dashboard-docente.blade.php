@extends('layouts.app')

@section('title', 'Dashboard Docente')

@section('content')
    @php
        $resumen = $resumen ?? [];
        $cardsSeleccion = $cardsSeleccion ?? [];
        $seguimientoAcademico = $seguimientoAcademico ?? [];
        $alertas = $alertas ?? [];
        $actividadReciente = $actividadReciente ?? [];
        $estructuraDocente = $estructuraDocente ?? [];

        $chartPermisos = $chartPermisos ?? [];
        $chartEstructuraAcademica = $chartEstructuraAcademica ?? [];
        $chartEvaluacion = $chartEvaluacion ?? [];
        $chartSeguimiento = $chartSeguimiento ?? [];
        $chartCargaDocente = $chartCargaDocente ?? [];

        $toneStyles = [
            'primary' => [
                'soft' =>
                    'background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);',
                'text' => 'color: var(--ui-primary);',
                'hover' => 'var(--ui-primary-soft)',
            ],
            'info' => [
                'soft' =>
                    'background: var(--ui-info-soft); color: var(--ui-info); --tw-ring-color: var(--ui-info-border);',
                'text' => 'color: var(--ui-info);',
                'hover' => 'var(--ui-info-soft)',
            ],
            'violet' => [
                'soft' =>
                    'background: var(--ui-violet-soft); color: var(--ui-violet); --tw-ring-color: var(--ui-violet-border);',
                'text' => 'color: var(--ui-violet);',
                'hover' => 'var(--ui-violet-soft)',
            ],
            'warning' => [
                'soft' =>
                    'background: var(--ui-warning-soft); color: var(--ui-warning); --tw-ring-color: var(--ui-warning-border);',
                'text' => 'color: var(--ui-warning);',
                'hover' => 'var(--ui-warning-soft)',
            ],
            'danger' => [
                'soft' =>
                    'background: var(--ui-danger-soft); color: var(--ui-danger); --tw-ring-color: var(--ui-danger-border);',
                'text' => 'color: var(--ui-danger);',
                'hover' => 'var(--ui-danger-soft)',
            ],
            'success' => [
                'soft' =>
                    'background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);',
                'text' => 'color: var(--ui-primary);',
                'hover' => 'var(--ui-primary-soft)',
            ],
        ];

        $icons = [
            'shield' =>
                '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75 11.25 15 15.75 9M12 3.75 4.5 6.75v5.25c0 4.18 2.78 8.09 7.5 9.75 4.72-1.66 7.5-5.57 7.5-9.75V6.75L12 3.75Z"/></svg>',
            'book' =>
                '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.75c-2.25-1.5-5.25-1.5-7.5 0v11.25c2.25-1.5 5.25-1.5 7.5 0m0-11.25c2.25-1.5 5.25-1.5 7.5 0v11.25c-2.25-1.5-5.25-1.5-7.5 0m0-11.25v11.25"/></svg>',
            'layers' =>
                '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6h.01M6 18h.01M18 6h.01M18 18h.01M6 6h12M6 18h12M12 6v12"/></svg>',
            'document' =>
                '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-7.5A2.25 2.25 0 0 0 17.25 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5A2.25 2.25 0 0 0 6.75 19.5h6.75M8.25 8.25h7.5M8.25 12h7.5M8.25 15.75h3.75M15 18.75 17.25 21 21 16.5"/></svg>',
            'users' =>
                '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 18.72a9.094 9.094 0 0 0 3.75-1.32 4.5 4.5 0 0 0-7.5-3.35M6 18.72a9.094 9.094 0 0 1-3.75-1.32 4.5 4.5 0 0 1 7.5-3.35M15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9 3a2.25 2.25 0 1 1 0-4.5 2.25 2.25 0 0 1 0 4.5Zm12 0a2.25 2.25 0 1 1 0-4.5 2.25 2.25 0 0 1 0 4.5Zm-9 5.25h6A3.75 3.75 0 0 1 18.75 19.5v.75H5.25v-.75A3.75 3.75 0 0 1 9 15.75Z"/></svg>',
            'chart' =>
                '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 19.5h16.5M6.75 16.5v-6M12 16.5v-9M17.25 16.5v-3.75"/></svg>',
            'clipboard' =>
                '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75 11.25 15 15 9.75M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12A2.25 2.25 0 0 1 17.25 20.25H6.75A2.25 2.25 0 0 1 4.5 18V6A2.25 2.25 0 0 1 6.75 3.75Z"/></svg>',
            'inbox' =>
                '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 13.5h4.5l1.5 2.25h4.5l1.5-2.25h4.5M5.25 4.5h13.5L21 13.5v4.5A1.5 1.5 0 0 1 19.5 19.5h-15A1.5 1.5 0 0 1 3 18v-4.5L5.25 4.5Z"/></svg>',
            'calendar' =>
                '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.75 3v2.25m10.5-2.25v2.25M3.75 8.25h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A1.5 1.5 0 0 1 18.75 20.25H5.25A1.5 1.5 0 0 1 3.75 18.75v-12A1.5 1.5 0 0 1 5.25 5.25Z"/></svg>',
            'bell' =>
                '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.857 17.082a23.85 23.85 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a3 3 0 0 1-5.714 0"/></svg>',
            'students' =>
                '<svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.26 10.147 12 5.625l7.74 4.522L12 14.67l-7.74-4.523Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.25 11.25v4.875c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125V11.25"/></svg>',
            'courses' =>
                '<svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 6.75c-2.25-1.5-5.25-1.5-7.5 0v11.25c2.25-1.5 5.25-1.5 7.5 0m0-11.25c2.25-1.5 5.25-1.5 7.5 0v11.25c-2.25-1.5-5.25-1.5-7.5 0m0-11.25v11.25"/></svg>',
            'parallel' =>
                '<svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 6h.01M6 18h.01M18 6h.01M18 18h.01M6 6h12M6 18h12M12 6v12"/></svg>',
            'subjects' =>
                '<svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.5 14.25v-7.5A2.25 2.25 0 0 0 17.25 4.5H6.75A2.25 2.25 0 0 0 4.5 6.75v10.5A2.25 2.25 0 0 0 6.75 19.5h6.75M8.25 8.25h7.5M8.25 12h7.5M8.25 15.75h3.75"/></svg>',
            'tools' =>
                '<svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.83-5.83M11.42 15.17 5.86 20.73a2.121 2.121 0 0 1-3-3l5.56-5.56M11.42 15.17l3.75-3.75M8.25 8.25l-2.5-2.5L3 8.5 5.5 11l2.75-2.75Z"/></svg>',
            'plan' =>
                '<svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 6.75h11.25M9 12h11.25M9 17.25h11.25M3.75 6.75h.008v.008H3.75V6.75Zm0 5.25h.008v.008H3.75V12Zm0 5.25h.008v.008H3.75v-.008Z"/></svg>',
            'grades' =>
                '<svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.48 3.499a.75.75 0 0 1 1.04 0l2.6 2.52 3.6.52a.75.75 0 0 1 .416 1.28l-2.605 2.538.615 3.584a.75.75 0 0 1-1.088.79L12.84 13.04l-3.218 1.692a.75.75 0 0 1-1.088-.79l.615-3.584-2.605-2.538a.75.75 0 0 1 .416-1.28l3.6-.52 2.6-2.52Z"/></svg>',
            'reports' =>
                '<svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3.75 19.5h16.5M6.75 16.5v-6M12 16.5v-9M17.25 16.5v-3.75"/></svg>',
            'lms' =>
                '<svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5A2.25 2.25 0 0 1 19.5 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 17.25V6.75Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.25 8.25h7.5M8.25 12h7.5M8.25 15.75h4.5"/></svg>',
        ];
    @endphp

    <div class="space-y-6">
        <section class="ui-card card-shadow rounded-[2rem] p-6 sm:p-7">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 ring-1"
                        style="background: var(--ui-info-soft); color: var(--ui-info); --tw-ring-color: var(--ui-info-border);">
                        <span class="h-2 w-2 rounded-full" style="background: var(--ui-info);"></span>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em]">
                            Panel docente
                        </p>
                    </div>

                    <h2 class="ui-title mt-3 text-3xl font-black tracking-tight">
                        @php
                            $horaActual = now()->format('H');
                            $saludo = 'Buen día';

                            if ($horaActual >= 12 && $horaActual < 18) {
                                $saludo = 'Buena tarde';
                            } elseif ($horaActual >= 18) {
                                $saludo = 'Buenas noches';
                            }
                        @endphp

                        {{ $saludo }}, {{ $nombreCompleto ?: 'Docente' }}
                    </h2>

                    <p class="ui-muted mt-3 max-w-3xl text-sm leading-7">
                        Gestiona el seguimiento académico de tus cursos, estudiantes, asignaturas,
                        calificaciones y reportes institucionales desde un panel adaptado a tus permisos docentes.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 xl:min-w-[470px]">
                    <div class="ui-card-soft px-4 py-4">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4" style="color: var(--ui-primary);" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]"
                                style="color: var(--ui-muted);">
                                Rol
                            </p>
                        </div>
                        <p class="mt-2 text-sm font-bold" style="color: var(--ui-text);">
                            {{ $rolActual ?? 'Docente' }}
                        </p>
                    </div>

                    <div class="ui-card-soft px-4 py-4">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4" style="color: var(--ui-info);" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M6.75 3v2.25m10.5-2.25v2.25M3.75 8.25h16.5" />
                            </svg>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]"
                                style="color: var(--ui-muted);">
                                Gestión
                            </p>
                        </div>
                        <p class="mt-2 text-sm font-bold" style="color: var(--ui-text);">
                            {{ $gestionActual ?? 'Gestión no configurada' }}
                        </p>
                    </div>

                    <div class="ui-card-soft px-4 py-4">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4" style="color: var(--ui-violet);" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 6v6l4 2M21 12A9 9 0 1 1 3 12a9 9 0 0 1 18 0Z" />
                            </svg>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]"
                                style="color: var(--ui-muted);">
                                Periodo
                            </p>
                        </div>
                        <p class="mt-2 text-sm font-bold" style="color: var(--ui-primary);">
                            {{ $periodoActual ?? 'Periodo no configurado' }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            @forelse ($resumen as $item)
                @php
                    $tone = $item['tone'] ?? 'primary';
                    $toneStyle = $toneStyles[$tone] ?? $toneStyles['primary'];
                    $icon = $item['icon'] ?? 'shield';
                @endphp

                <article class="ui-card ui-card-hover rounded-[1.8rem] p-5">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                            {{ $item['label'] ?? 'Indicador' }}
                        </p>

                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl ring-1"
                            style="{{ $toneStyle['soft'] }}">
                            {!! $icons[$icon] ?? $icons['shield'] !!}
                        </span>
                    </div>

                    <p class="mt-4 text-3xl font-black" style="color: var(--ui-text);">
                        {{ $item['value'] ?? 0 }}
                    </p>

                    <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                        {{ $item['desc'] ?? 'Información académica disponible.' }}
                    </p>

                    <div class="mt-4 inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold ring-1"
                        style="background: var(--ui-surface-muted); color: var(--ui-muted); --tw-ring-color: var(--ui-border);">
                        <span class="h-1.5 w-1.5 rounded-full" style="{{ $toneStyle['text'] }}"></span>
                        Vista docente
                    </div>
                </article>
            @empty
                <div class="ui-alert-info sm:col-span-2 xl:col-span-3 2xl:col-span-6">
                    No existen indicadores docentes disponibles.
                </div>
            @endforelse
        </section>

        <section class="ui-card rounded-[2rem] p-6 sm:p-8">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="ui-kicker">
                        Módulos disponibles
                    </p>
                    <h3 class="ui-title mt-2 text-2xl font-black">
                        Cards de selección del panel docente
                    </h3>
                    <p class="ui-muted mt-2 max-w-3xl text-sm leading-7">
                        Estas tarjetas se muestran según los permisos reales del docente. Las opciones sin ruta estable
                        permanecen preparadas con acceso pendiente para próximas fases.
                    </p>
                </div>

                <span class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold ring-1"
                    style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                    {{ count($cardsSeleccion) }} opciones habilitadas
                </span>
            </div>

            <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($cardsSeleccion as $card)
                    @php
                        $tone = $card['tono'] ?? 'primary';
                        $toneStyle = $toneStyles[$tone] ?? $toneStyles['primary'];
                        $icon = $card['icono'] ?? 'shield';
                        $href = $card['route'] ?? '#';
                        $isDisabled = $href === '#';
                    @endphp

                    <a href="{{ $href }}" class="group ui-card-hover block rounded-[1.8rem] border p-5 transition"
                        style="border-color: var(--ui-border); background: var(--ui-surface);"
                        @if ($isDisabled) onclick="event.preventDefault();" aria-disabled="true" @endif>
                        <div class="flex items-start justify-between gap-4">
                            <span
                                class="inline-flex h-14 w-14 items-center justify-center rounded-2xl ring-1 transition group-hover:scale-105"
                                style="{{ $toneStyle['soft'] }}">
                                {!! $icons[$icon] ?? $icons['shield'] !!}
                            </span>

                            <span
                                class="inline-flex rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-[0.14em] ring-1"
                                style="{{ $isDisabled
                                    ? 'background: var(--ui-surface-muted); color: var(--ui-muted); --tw-ring-color: var(--ui-border);'
                                    : $toneStyle['soft'] }}">
                                {{ $card['estado'] ?? ($isDisabled ? 'Pendiente' : 'Disponible') }}
                            </span>
                        </div>

                        <p class="mt-5 text-[11px] font-semibold uppercase tracking-[0.18em]"
                            style="{{ $toneStyle['text'] }}">
                            {{ $card['subtitulo'] ?? 'Docente' }}
                        </p>

                        <h4 class="mt-2 text-xl font-black" style="color: var(--ui-text);">
                            {{ $card['titulo'] ?? 'Módulo docente' }}
                        </h4>

                        <p class="mt-3 text-sm leading-7" style="color: var(--ui-muted);">
                            {{ $card['descripcion'] ?? 'Opción disponible para el seguimiento académico.' }}
                        </p>

                        <div class="mt-5 flex items-center gap-2 text-sm font-bold" style="{{ $toneStyle['text'] }}">
                            <span>{{ $isDisabled ? 'Preparado para próxima fase' : 'Ingresar' }}</span>
                            <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                    </a>
                @empty
                    <div class="ui-alert-warning md:col-span-2 xl:col-span-3">
                        No hay cards de selección habilitadas para este docente. Revisa los permisos asignados al rol.
                    </div>
                @endforelse
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.15fr_.85fr]">
            <div class="ui-card rounded-[2rem] p-6 sm:p-8">
                <div class="flex flex-col gap-2">
                    <p class="ui-kicker">
                        Seguimiento académico
                    </p>
                    <h3 class="ui-title text-2xl font-black">
                        Estado operativo del trabajo docente
                    </h3>
                    <p class="ui-muted text-sm leading-7">
                        Resume la información base para el seguimiento académico y la futura conexión con cursos,
                        calificaciones, reportes y Aula Virtual.
                    </p>
                </div>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    @forelse ($seguimientoAcademico as $item)
                        @php
                            $tone = $item['tono'] ?? 'primary';
                            $toneStyle = $toneStyles[$tone] ?? $toneStyles['primary'];
                        @endphp

                        <article class="ui-card-soft p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-black" style="color: var(--ui-text);">
                                        {{ $item['titulo'] ?? 'Seguimiento' }}
                                    </p>
                                    <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                                        {{ $item['descripcion'] ?? 'Detalle académico pendiente.' }}
                                    </p>
                                </div>

                                <span
                                    class="inline-flex min-w-[58px] justify-center rounded-2xl px-3 py-2 text-lg font-black ring-1"
                                    style="{{ $toneStyle['soft'] }}">
                                    {{ $item['valor'] ?? 0 }}
                                </span>
                            </div>

                            <div class="mt-4 inline-flex rounded-full px-3 py-1 text-[11px] font-semibold ring-1"
                                style="background: var(--ui-surface-muted); color: var(--ui-muted); --tw-ring-color: var(--ui-border);">
                                {{ $item['estado'] ?? 'Académico' }}
                            </div>
                        </article>
                    @empty
                        <div class="ui-alert-info sm:col-span-2">
                            No hay datos de seguimiento académico disponibles.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="ui-card rounded-[2rem] p-6 sm:p-8">
                <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-warning);">
                    Alertas docentes
                </p>
                <h3 class="ui-title mt-2 text-2xl font-black">
                    Revisión de permisos y estado
                </h3>

                <div class="mt-6 space-y-4">
                    @forelse ($alertas as $alerta)
                        @php
                            $tipo = $alerta['tipo'] ?? 'info';
                            $toneStyle = $toneStyles[$tipo] ?? $toneStyles['info'];
                        @endphp

                        <div class="rounded-2xl border p-4"
                            style="border-color: var(--ui-border); background: var(--ui-surface-soft);">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="text-sm font-black" style="color: var(--ui-text);">
                                        {{ $alerta['titulo'] ?? 'Alerta docente' }}
                                    </p>
                                    <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                                        {{ $alerta['descripcion'] ?? 'Revisión pendiente.' }}
                                    </p>
                                </div>

                                <span
                                    class="inline-flex min-w-[52px] justify-center rounded-2xl px-3 py-2 text-sm font-black ring-1"
                                    style="{{ $toneStyle['soft'] }}">
                                    {{ $alerta['valor'] ?? '!' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="ui-alert-success">
                            No existen alertas docentes pendientes.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-4">
            <div class="ui-card rounded-[2rem] p-6 sm:p-8 xl:col-span-1">
                <p class="ui-kicker">
                    Permisos
                </p>
                <h3 class="ui-title mt-2 text-xl font-black">
                    Permisos docentes
                </h3>
                <p class="ui-muted mt-2 text-sm leading-6">
                    Relación entre permisos habilitados y pendientes del panel docente.
                </p>

                <div class="mt-5 rounded-2xl p-3" style="background: var(--ui-surface-soft);">
                    <canvas id="chartPermisosDocente" height="230"></canvas>
                </div>
            </div>

            <div class="ui-card rounded-[2rem] p-6 sm:p-8 xl:col-span-1">
                <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-info);">
                    Estructura
                </p>
                <h3 class="ui-title mt-2 text-xl font-black">
                    Base académica
                </h3>
                <p class="ui-muted mt-2 text-sm leading-6">
                    Cursos, paralelos, asignaturas y especialidades técnicas.
                </p>

                <div class="mt-5 rounded-2xl p-3" style="background: var(--ui-surface-soft);">
                    <canvas id="chartEstructuraAcademica" height="230"></canvas>
                </div>
            </div>

            <div class="ui-card rounded-[2rem] p-6 sm:p-8 xl:col-span-1">
                <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-violet);">
                    Evaluación
                </p>
                <h3 class="ui-title mt-2 text-xl font-black">
                    Evaluación académica
                </h3>
                <p class="ui-muted mt-2 text-sm leading-6">
                    Periodos, calificaciones y reportes académicos disponibles.
                </p>

                <div class="mt-5 rounded-2xl p-3" style="background: var(--ui-surface-soft);">
                    <canvas id="chartEvaluacionDocente" height="230"></canvas>
                </div>
            </div>

            <div class="ui-card rounded-[2rem] p-6 sm:p-8 xl:col-span-1">
                <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-warning);">
                    Seguimiento
                </p>
                <h3 class="ui-title mt-2 text-xl font-black">
                    Carga docente
                </h3>
                <p class="ui-muted mt-2 text-sm leading-6">
                    Vista base para cursos, asignaturas, estudiantes y calificaciones.
                </p>

                <div class="mt-5 rounded-2xl p-3" style="background: var(--ui-surface-soft);">
                    <canvas id="chartCargaDocente" height="230"></canvas>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[.85fr_1.15fr]">
            <div class="ui-card rounded-[2rem] p-6 sm:p-8">
                <p class="ui-kicker">
                    Estructura de permisos
                </p>
                <h3 class="ui-title mt-2 text-2xl font-black">
                    Estado por grupo funcional
                </h3>

                <div class="mt-6 space-y-4">
                    @forelse ($estructuraDocente as $grupo)
                        @php
                            $porcentaje = $grupo['porcentaje'] ?? 0;
                        @endphp

                        <div class="ui-card-soft p-4">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-sm font-black" style="color: var(--ui-text);">
                                        {{ $grupo['grupo'] ?? 'Grupo' }}
                                    </p>
                                    <p class="mt-1 text-xs font-semibold" style="color: var(--ui-muted);">
                                        {{ $grupo['habilitados'] ?? 0 }} habilitados · {{ $grupo['pendientes'] ?? 0 }}
                                        pendientes
                                    </p>
                                </div>

                                <span class="text-sm font-black" style="color: var(--ui-primary);">
                                    {{ $porcentaje }}%
                                </span>
                            </div>

                            <div class="mt-3 h-2 overflow-hidden rounded-full"
                                style="background: var(--ui-surface-muted);">
                                <div class="h-full rounded-full"
                                    style="width: {{ $porcentaje }}%; background: var(--ui-primary);"></div>
                            </div>
                        </div>
                    @empty
                        <div class="ui-alert-info">
                            No hay estructura de permisos disponible.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="ui-card rounded-[2rem] p-6 sm:p-8">
                <p class="ui-kicker">
                    Actividad reciente
                </p>
                <h3 class="ui-title mt-2 text-2xl font-black">
                    Movimientos del panel docente
                </h3>

                <div class="mt-6 space-y-4">
                    @forelse ($actividadReciente as $item)
                        <div class="ui-card-soft p-4">
                            <div class="flex gap-4">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $item['color'] ?? '' }}">
                                    <span class="text-lg">{{ $item['icono'] ?? '•' }}</span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="text-sm font-black" style="color: var(--ui-text);">
                                            {{ $item['titulo'] ?? 'Actividad' }}
                                        </p>

                                        <span
                                            class="inline-flex w-fit items-center rounded-full px-3 py-1 text-xs font-medium ring-1"
                                            style="background: var(--ui-surface-muted); color: var(--ui-muted); --tw-ring-color: var(--ui-border);">
                                            {{ $item['fecha'] ?? now()->format('d/m/Y') }}
                                        </span>
                                    </div>

                                    <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                                        {{ $item['detalle'] ?? 'Movimiento registrado en el panel docente.' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="ui-alert-info">
                            No hay actividad reciente registrada.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        <section
            class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-emerald-600 to-sky-600 p-6 text-white shadow-2xl shadow-emerald-500/20 sm:p-8">
            <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-100">
                        Estado del panel docente
                    </p>
                    <h3 class="mt-2 text-2xl font-black">
                        {{ $estadoSistema ?? 'Operativo' }}
                    </h3>
                    <p class="mt-3 max-w-3xl text-sm leading-7 text-white/85">
                        Este panel prepara el seguimiento académico institucional del docente. Los datos de estudiantes,
                        cursos, asignaturas, calificaciones y reportes servirán como base para el análisis académico y
                        la futura integración con el Aula Virtual LMS.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 xl:min-w-[520px]">
                    <div class="rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/70">
                            Gestión
                        </p>
                        <p class="mt-1 text-sm font-black">
                            {{ $gestionActual ?? 'No configurada' }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/70">
                            Periodo
                        </p>
                        <p class="mt-1 text-sm font-black">
                            {{ $periodoActual ?? 'No configurado' }}
                        </p>
                    </div>

                    <div class="rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-white/70">
                            Fecha
                        </p>
                        <p class="mt-1 text-sm font-black">
                            {{ now()->format('d/m/Y') }}
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chartPermisosDocente = document.getElementById('chartPermisosDocente');
            const chartEstructuraAcademica = document.getElementById('chartEstructuraAcademica');
            const chartEvaluacionDocente = document.getElementById('chartEvaluacionDocente');
            const chartCargaDocente = document.getElementById('chartCargaDocente');

            const getChartTheme = () => {
                const styles = getComputedStyle(document.documentElement);

                return {
                    text: styles.getPropertyValue('--ui-text').trim(),
                    muted: styles.getPropertyValue('--ui-muted').trim(),
                    border: styles.getPropertyValue('--ui-border').trim(),
                    primary: styles.getPropertyValue('--ui-primary').trim(),
                    info: styles.getPropertyValue('--ui-info').trim(),
                    violet: styles.getPropertyValue('--ui-violet').trim(),
                    warning: styles.getPropertyValue('--ui-warning').trim(),
                    danger: styles.getPropertyValue('--ui-danger').trim(),
                    surface: styles.getPropertyValue('--ui-surface').trim(),
                };
            };

            const chartData = {
                permisos: {
                    labels: @json(array_keys($chartPermisos)),
                    values: @json(array_values($chartPermisos)),
                },
                estructura: {
                    labels: @json(array_keys($chartEstructuraAcademica)),
                    values: @json(array_values($chartEstructuraAcademica)),
                },
                evaluacion: {
                    labels: @json(array_keys($chartEvaluacion)),
                    values: @json(array_values($chartEvaluacion)),
                },
                carga: {
                    labels: @json(array_keys($chartCargaDocente)),
                    values: @json(array_values($chartCargaDocente)),
                },
            };

            let permisosInstance = null;
            let estructuraInstance = null;
            let evaluacionInstance = null;
            let cargaInstance = null;

            const baseOptions = (theme) => ({
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: theme.muted,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 16,
                            font: {
                                size: 12,
                                weight: '600',
                            },
                        },
                    },
                },
            });

            const barOptions = (theme) => ({
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    x: {
                        ticks: {
                            color: theme.muted,
                        },
                        grid: {
                            display: false,
                        },
                        border: {
                            color: theme.border,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: theme.muted,
                            precision: 0,
                        },
                        grid: {
                            color: theme.border,
                        },
                        border: {
                            color: theme.border,
                        },
                    },
                },
            });

            const renderCharts = () => {
                if (!window.Chart) {
                    console.warn('Chart.js no está disponible. Verifica resources/js/app.js');
                    return;
                }

                const theme = getChartTheme();

                if (permisosInstance) permisosInstance.destroy();
                if (estructuraInstance) estructuraInstance.destroy();
                if (evaluacionInstance) evaluacionInstance.destroy();
                if (cargaInstance) cargaInstance.destroy();

                if (chartPermisosDocente) {
                    permisosInstance = new Chart(chartPermisosDocente, {
                        type: 'doughnut',
                        data: {
                            labels: chartData.permisos.labels,
                            datasets: [{
                                data: chartData.permisos.values,
                                backgroundColor: [
                                    theme.primary,
                                    theme.warning,
                                    theme.info,
                                    theme.violet,
                                ],
                                borderColor: theme.surface,
                                borderWidth: 3,
                                hoverOffset: 8,
                            }],
                        },
                        options: {
                            ...baseOptions(theme),
                            cutout: '68%',
                        },
                    });
                }

                if (chartEstructuraAcademica) {
                    estructuraInstance = new Chart(chartEstructuraAcademica, {
                        type: 'bar',
                        data: {
                            labels: chartData.estructura.labels,
                            datasets: [{
                                label: 'Registros',
                                data: chartData.estructura.values,
                                backgroundColor: theme.info,
                                borderRadius: 10,
                                maxBarThickness: 44,
                            }],
                        },
                        options: barOptions(theme),
                    });
                }

                if (chartEvaluacionDocente) {
                    evaluacionInstance = new Chart(chartEvaluacionDocente, {
                        type: 'bar',
                        data: {
                            labels: chartData.evaluacion.labels,
                            datasets: [{
                                label: 'Evaluación',
                                data: chartData.evaluacion.values,
                                backgroundColor: theme.violet,
                                borderRadius: 10,
                                maxBarThickness: 44,
                            }],
                        },
                        options: barOptions(theme),
                    });
                }

                if (chartCargaDocente) {
                    cargaInstance = new Chart(chartCargaDocente, {
                        type: 'line',
                        data: {
                            labels: chartData.carga.labels,
                            datasets: [{
                                label: 'Carga docente',
                                data: chartData.carga.values,
                                borderColor: theme.primary,
                                backgroundColor: theme.primary,
                                tension: 0.35,
                                fill: false,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            }],
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    display: true,
                                    labels: {
                                        color: theme.muted,
                                        usePointStyle: true,
                                        font: {
                                            size: 12,
                                            weight: '600',
                                        },
                                    },
                                },
                            },
                            scales: {
                                x: {
                                    ticks: {
                                        color: theme.muted,
                                    },
                                    grid: {
                                        display: false,
                                    },
                                    border: {
                                        color: theme.border,
                                    },
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        color: theme.muted,
                                        precision: 0,
                                    },
                                    grid: {
                                        color: theme.border,
                                    },
                                    border: {
                                        color: theme.border,
                                    },
                                },
                            },
                        },
                    });
                }
            };

            renderCharts();

            window.addEventListener('theme-changed', () => {
                renderCharts();
            });
        });
    </script>
@endpush
