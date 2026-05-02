@extends('layouts.app')

@section('title', 'Dashboard Administrador')

@section('content')
    <div class="space-y-6">

        {{-- ENCABEZADO COMPACTO --}}
        <section class="ui-card card-shadow rounded-[2rem] p-6 sm:p-7">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 ring-1"
                        style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                        <span class="h-2 w-2 rounded-full" style="background: var(--ui-primary);"></span>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em]">
                            Panel administrativo
                        </p>
                    </div>

                    <h2 class="ui-title mt-3 text-3xl font-black tracking-tight">
                        Buen día, {{ $nombreCompleto ?: 'Administrador' }}
                    </h2>

                    <p class="ui-muted mt-3 max-w-2xl text-sm leading-6">
                        Revisa indicadores, alertas y estado académico-administrativo de la plataforma.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 xl:min-w-[430px]">
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
                            {{ Auth::user()->getRoleNames()->first() ?? 'Sin rol' }}
                        </p>
                    </div>

                    <div class="ui-card-soft px-4 py-4">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4" style="color: var(--ui-info);" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M6.75 3v2.25m10.5-2.25v2.25M3.75 8.25h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A1.5 1.5 0 0 1 18.75 20.25H5.25A1.5 1.5 0 0 1 3.75 18.75v-12A1.5 1.5 0 0 1 5.25 5.25Z" />
                            </svg>
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]"
                                style="color: var(--ui-muted);">
                                Gestión actual
                            </p>
                        </div>
                        <p class="mt-2 text-sm font-bold" style="color: var(--ui-text);">
                            {{ $gestionActual }}
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
                                Periodo activo
                            </p>
                        </div>
                        <p class="mt-2 text-sm font-bold" style="color: var(--ui-primary);">
                            {{ $periodoActual }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- MÉTRICAS PRINCIPALES --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            @foreach ($resumen as $item)
                @php
                    $iconClasses = [
                        'users' => 'background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);',
                        'academic-cap' => 'background: var(--ui-info-soft); color: var(--ui-info); --tw-ring-color: var(--ui-info-border);',
                        'user-group' => 'background: var(--ui-violet-soft); color: var(--ui-violet); --tw-ring-color: var(--ui-violet-border);',
                        'clipboard-document' => 'background: var(--ui-warning-soft); color: var(--ui-warning); --tw-ring-color: var(--ui-warning-border);',
                        'wrench-screwdriver' => 'background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);',
                        'calendar-days' => 'background: var(--ui-info-soft); color: var(--ui-info); --tw-ring-color: var(--ui-info-border);',
                    ];

                    $icons = [
                        'users' => '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.162-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.106a6.375 6.375 0 0 1 12.75 0Zm-3.75-11.25a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>',
                        'academic-cap' => '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.26 10.147 12 5.625l7.74 4.522L12 14.67l-7.74-4.523Z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5.25 11.25v4.875c0 .621.504 1.125 1.125 1.125h11.25c.621 0 1.125-.504 1.125-1.125V11.25M12 14.625V21"/></svg>',
                        'user-group' => '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 18.72a9.094 9.094 0 0 0 3.75-1.32 4.5 4.5 0 0 0-7.5-3.35M6 18.72a9.094 9.094 0 0 1-3.75-1.32 4.5 4.5 0 0 1 7.5-3.35M15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-9 3a2.25 2.25 0 1 1 0-4.5 2.25 2.25 0 0 1 0 4.5Zm12 0a2.25 2.25 0 1 1 0-4.5 2.25 2.25 0 0 1 0 4.5Zm-9 5.25h6A3.75 3.75 0 0 1 18.75 19.5v.75H5.25v-.75A3.75 3.75 0 0 1 9 15.75Z"/></svg>',
                        'clipboard-document' => '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12.75 11.25 15 15 9.75M6.75 3.75h10.5A2.25 2.25 0 0 1 19.5 6v12A2.25 2.25 0 0 1 17.25 20.25H6.75A2.25 2.25 0 0 1 4.5 18V6A2.25 2.25 0 0 1 6.75 3.75Z"/></svg>',
                        'wrench-screwdriver' => '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.83-5.83M11.42 15.17 5.86 20.73a2.121 2.121 0 0 1-3-3l5.56-5.56M11.42 15.17l3.75-3.75M8.25 8.25l-2.5-2.5L3 8.5 5.5 11l2.75-2.75Zm8.25-2.25 1.5-1.5 1.5 1.5-1.5 1.5-1.5-1.5Z"/></svg>',
                        'calendar-days' => '<svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6.75 3v2.25m10.5-2.25v2.25M3.75 8.25h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A1.5 1.5 0 0 1 18.75 20.25H5.25A1.5 1.5 0 0 1 3.75 18.75v-12A1.5 1.5 0 0 1 5.25 5.25Z"/></svg>',
                    ];
                @endphp

                <article class="ui-card ui-card-hover rounded-[1.8rem] p-5">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                            {{ $item['label'] }}
                        </p>

                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl ring-1"
                            style="{{ $iconClasses[$item['icon']] ?? 'background: var(--ui-surface-muted); color: var(--ui-muted); --tw-ring-color: var(--ui-border);' }}">
                            {!! $icons[$item['icon']] ?? '' !!}
                        </span>
                    </div>

                    <p class="mt-4 text-3xl font-black" style="color: var(--ui-text);">
                        {{ $item['value'] }}
                    </p>

                    <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                        {{ $item['desc'] }}
                    </p>

                    <div class="mt-4 inline-flex items-center gap-2 rounded-full px-3 py-1 text-[11px] font-semibold ring-1"
                        style="background: var(--ui-surface-muted); color: var(--ui-muted); --tw-ring-color: var(--ui-border);">
                        <span class="h-1.5 w-1.5 rounded-full" style="background: var(--ui-primary);"></span>
                        Actualizado hoy
                    </div>
                </article>
            @endforeach
        </section>

        {{-- GRÁFICOS --}}
        <section class="grid gap-6 xl:grid-cols-3">
            <div class="ui-card rounded-[2rem] p-6 sm:p-8">
                <div class="mb-4">
                    <p class="ui-kicker">
                        Distribución de usuarios
                    </p>
                    <h3 class="ui-title mt-2 text-xl font-black">
                        Usuarios por rol
                    </h3>
                    <p class="ui-muted mt-2 text-sm leading-6">
                        Predomina el acceso estudiantil dentro de la plataforma institucional.
                    </p>
                </div>

                <div class="rounded-2xl p-3" style="background: var(--ui-surface-soft);">
                    <canvas id="chartRoles" height="220"></canvas>
                </div>
            </div>

            <div class="ui-card rounded-[2rem] p-6 sm:p-8">
                <div class="mb-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-info);">
                        Distribución académica
                    </p>
                    <h3 class="ui-title mt-2 text-xl font-black">
                        Estudiantes por especialidad
                    </h3>
                    <p class="ui-muted mt-2 text-sm leading-6">
                        Distribución actual de estudiantes por especialidad técnica.
                    </p>
                </div>

                <div class="rounded-2xl p-3" style="background: var(--ui-surface-soft);">
                    <canvas id="chartEspecialidades" height="220"></canvas>
                </div>
            </div>

            <div class="ui-card rounded-[2rem] p-6 sm:p-8">
                <div class="mb-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-violet);">
                        Inscripciones
                    </p>
                    <h3 class="ui-title mt-2 text-xl font-black">
                        Distribución por curso
                    </h3>
                    <p class="ui-muted mt-2 text-sm leading-6">
                        Carga de inscripciones según el nivel académico actual.
                    </p>
                </div>

                <div class="rounded-2xl p-3" style="background: var(--ui-surface-soft);">
                    <canvas id="chartInscripciones" height="220"></canvas>
                </div>
            </div>
        </section>

        {{-- ACTIVIDAD Y ALERTAS --}}
        <section class="grid gap-6 xl:grid-cols-[1.2fr_.8fr]">
            <div class="ui-card rounded-[2rem] p-6 sm:p-8">
                <p class="ui-kicker">
                    Actividad reciente
                </p>
                <h3 class="ui-title mt-2 text-2xl font-black">
                    Últimos movimientos del sistema
                </h3>

                <div class="mt-6 space-y-4">
                    @forelse ($actividadReciente as $item)
                        <div class="ui-card-soft p-4">
                            <div class="flex gap-4">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $item['color'] }}">
                                    <span class="text-lg">{{ $item['icono'] }}</span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="text-sm font-semibold" style="color: var(--ui-text);">
                                            {{ $item['titulo'] }}
                                        </p>
                                        <span
                                            class="inline-flex w-fit items-center rounded-full px-3 py-1 text-xs font-medium ring-1"
                                            style="background: var(--ui-surface-muted); color: var(--ui-muted); --tw-ring-color: var(--ui-border);">
                                            {{ $item['fecha'] }}
                                        </span>
                                    </div>

                                    <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                                        {{ $item['detalle'] }}
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

            <div class="space-y-6">
                <div class="ui-card rounded-[2rem] p-6 sm:p-8">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-danger);">
                        Alertas y pendientes
                    </p>
                    <h3 class="ui-title mt-2 text-2xl font-black">
                        Elementos que requieren revisión
                    </h3>

                    <div class="mt-6 space-y-4">
                        @forelse ($alertas as $alerta)
                            <div class="rounded-2xl border p-4 {{ $alerta['color'] }}">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold" style="color: var(--ui-text);">
                                            {{ $alerta['titulo'] }}
                                        </p>
                                        <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                                            {{ $alerta['descripcion'] }}
                                        </p>
                                    </div>

                                    <div class="flex h-12 min-w-[48px] items-center justify-center rounded-2xl text-lg font-black ring-1"
                                        style="background: var(--ui-surface); color: var(--ui-text); --tw-ring-color: var(--ui-border);">
                                        {{ $alerta['valor'] }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="ui-alert-success">
                                No existen alertas críticas pendientes.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-emerald-600 to-sky-600 p-6 text-white shadow-2xl shadow-emerald-500/20 sm:p-8">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-100">
                                Estado actual
                            </p>
                            <h3 class="mt-2 text-2xl font-black">
                                {{ $gestionActual }}
                            </h3>
                        </div>

                        <div class="rounded-2xl bg-white/10 p-3 ring-1 ring-white/10">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                    </div>

                    <div class="mt-5 space-y-3 text-sm">
                        <div
                            class="flex items-center justify-between rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                            <span class="text-white/80">Periodo activo</span>
                            <span class="font-semibold">{{ $periodoActual }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                            <span class="text-white/80">Estado del sistema</span>
                            <span class="font-semibold">{{ $estadoSistema }}</span>
                        </div>

                        <div
                            class="flex items-center justify-between rounded-2xl bg-white/10 px-4 py-3 ring-1 ring-white/10">
                            <span class="text-white/80">Fecha de control</span>
                            <span class="font-semibold">{{ now()->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- RESUMEN ESTRUCTURAL --}}
        <section class="ui-card rounded-[2rem] p-6 sm:p-8">
            <div class="flex flex-col gap-2">
                <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-info);">
                    Estructura académica
                </p>
                <h3 class="ui-title text-2xl font-black">
                    Resumen estructural del sistema
                </h3>
                <p class="ui-muted text-sm leading-7">
                    Estado general de la configuración académica e institucional cargada en la plataforma.
                </p>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($estructuraAcademica as $item)
                    <div class="ui-card-soft p-5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                                {{ $item['label'] }}
                            </p>
                            <span
                                class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] ring-1"
                                style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                                Configurado
                            </span>
                        </div>

                        <p class="mt-4 text-3xl font-black" style="color: var(--ui-text);">
                            {{ $item['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const chartRoles = document.getElementById('chartRoles');
            const chartEspecialidades = document.getElementById('chartEspecialidades');
            const chartInscripciones = document.getElementById('chartInscripciones');

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

            let chartRolesInstance = null;
            let chartEspecialidadesInstance = null;
            let chartInscripcionesInstance = null;

            const renderCharts = () => {
                if (!window.Chart) {
                    console.warn('Chart.js no está disponible. Verifica resources/js/app.js');
                    return;
                }

                const theme = getChartTheme();

                if (chartRolesInstance) chartRolesInstance.destroy();
                if (chartEspecialidadesInstance) chartEspecialidadesInstance.destroy();
                if (chartInscripcionesInstance) chartInscripcionesInstance.destroy();

                if (chartRoles) {
                    chartRolesInstance = new Chart(chartRoles, {
                        type: 'doughnut',
                        data: {
                            labels: @json(array_keys($chartRoles)),
                            datasets: [{
                                data: @json(array_values($chartRoles)),
                                backgroundColor: [
                                    theme.primary,
                                    theme.info,
                                    theme.violet,
                                    theme.warning,
                                    theme.danger,
                                    theme.muted,
                                ],
                                borderColor: theme.surface,
                                borderWidth: 3,
                                hoverOffset: 8,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'bottom',
                                    labels: {
                                        color: theme.muted,
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        padding: 18,
                                        font: {
                                            size: 12,
                                            weight: '600',
                                        },
                                    },
                                },
                            },
                            cutout: '68%',
                        }
                    });
                }

                if (chartEspecialidades) {
                    chartEspecialidadesInstance = new Chart(chartEspecialidades, {
                        type: 'bar',
                        data: {
                            labels: @json(array_keys($chartEspecialidades)),
                            datasets: [{
                                label: 'Estudiantes',
                                data: @json(array_values($chartEspecialidades)),
                                backgroundColor: theme.info,
                                borderRadius: 10,
                                maxBarThickness: 44,
                            }]
                        },
                        options: {
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
                        }
                    });
                }

                if (chartInscripciones) {
                    chartInscripcionesInstance = new Chart(chartInscripciones, {
                        type: 'line',
                        data: {
                            labels: @json(array_keys($chartInscripciones)),
                            datasets: [{
                                label: 'Inscripciones',
                                data: @json(array_values($chartInscripciones)),
                                borderColor: theme.violet,
                                backgroundColor: theme.violet,
                                tension: 0.35,
                                fill: false,
                                pointRadius: 4,
                                pointHoverRadius: 6,
                            }]
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
                        }
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