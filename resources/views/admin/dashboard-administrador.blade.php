@extends('layouts.app')

@section('title', 'Dashboard Administrador')

@section('content')
    <div class="space-y-6">
        {{-- ENCABEZADO COMPACTO --}}
        <section class="soft-panel card-shadow rounded-[2rem] p-6 sm:p-7">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">
                        Panel administrativo
                    </p>

                    <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                        Buen día, {{ $nombreCompleto ?: 'Administrador' }}
                    </h2>

                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                        Revisa indicadores, alertas y estado académico-administrativo de la plataforma.
                    </p>
                </div>

                <div class="grid gap-3 sm:grid-cols-3 xl:min-w-[430px]">
                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Rol</p>
                        <p class="mt-2 text-sm font-bold text-slate-950">
                            {{ Auth::user()->getRoleNames()->first() ?? 'Sin rol' }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Gestión actual</p>
                        <p class="mt-2 text-sm font-bold text-slate-950">
                            {{ $gestionActual }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">Periodo activo</p>
                        <p class="mt-2 text-sm font-bold text-emerald-700">
                            {{ $periodoActual }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- MÉTRICAS PRINCIPALES --}}
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            @foreach ($resumen as $item)
                <article class="soft-panel card-shadow rounded-[1.8rem] p-5">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                            {{ $item['label'] }}
                        </p>
                        <span class="text-xl">{{ $item['icon'] }}</span>
                    </div>

                    <p class="mt-4 text-3xl font-black text-slate-950">{{ $item['value'] }}</p>

                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        {{ $item['desc'] }}
                    </p>

                    <div
                        class="mt-4 inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-[11px] font-medium text-slate-500">
                        Actualizado hoy
                    </div>
                </article>
            @endforeach
        </section>

        {{-- GRÁFICOS --}}
        <section class="grid gap-6 xl:grid-cols-3">
            <div class="soft-panel card-shadow rounded-[2rem] p-6 sm:p-8">
                <div class="mb-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">
                        Distribución de usuarios
                    </p>
                    <h3 class="mt-2 text-xl font-black text-slate-950">
                        Usuarios por rol
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Predomina el acceso estudiantil dentro de la plataforma institucional.
                    </p>
                </div>
                <canvas id="chartRoles" height="220"></canvas>
            </div>

            <div class="soft-panel card-shadow rounded-[2rem] p-6 sm:p-8">
                <div class="mb-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-700">
                        Distribución académica
                    </p>
                    <h3 class="mt-2 text-xl font-black text-slate-950">
                        Estudiantes por especialidad
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Distribución actual de estudiantes por especialidad técnica.
                    </p>
                </div>
                <canvas id="chartEspecialidades" height="220"></canvas>
            </div>

            <div class="soft-panel card-shadow rounded-[2rem] p-6 sm:p-8">
                <div class="mb-4">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-indigo-700">
                        Inscripciones
                    </p>
                    <h3 class="mt-2 text-xl font-black text-slate-950">
                        Distribución por curso
                    </h3>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Carga de inscripciones según el nivel académico actual.
                    </p>
                </div>
                <canvas id="chartInscripciones" height="220"></canvas>
            </div>
        </section>

        {{-- ACTIVIDAD Y ALERTAS --}}
        <section class="grid gap-6 xl:grid-cols-[1.2fr_.8fr]">
            <div class="soft-panel card-shadow rounded-[2rem] p-6 sm:p-8">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">
                    Actividad reciente
                </p>
                <h3 class="mt-2 text-2xl font-black text-slate-950">
                    Últimos movimientos del sistema
                </h3>

                <div class="mt-6 space-y-4">
                    @foreach ($actividadReciente as $item)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <div class="flex gap-4">
                                <div
                                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $item['color'] }}">
                                    <span class="text-lg">{{ $item['icono'] }}</span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ $item['titulo'] }}
                                        </p>
                                        <span
                                            class="inline-flex w-fit items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600">
                                            {{ $item['fecha'] }}
                                        </span>
                                    </div>

                                    <p class="mt-2 text-sm leading-6 text-slate-600">
                                        {{ $item['detalle'] }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-6">
                <div class="soft-panel card-shadow rounded-[2rem] p-6 sm:p-8">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-rose-700">
                        Alertas y pendientes
                    </p>
                    <h3 class="mt-2 text-2xl font-black text-slate-950">
                        Elementos que requieren revisión
                    </h3>

                    <div class="mt-6 space-y-4">
                        @foreach ($alertas as $alerta)
                            <div class="rounded-2xl border p-4 {{ $alerta['color'] }}">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="text-sm font-semibold text-slate-900">
                                            {{ $alerta['titulo'] }}
                                        </p>
                                        <p class="mt-2 text-sm leading-6 text-slate-600">
                                            {{ $alerta['descripcion'] }}
                                        </p>
                                    </div>

                                    <div
                                        class="flex h-12 min-w-[48px] items-center justify-center rounded-2xl bg-white text-lg font-black ring-1 {{ $alerta['color'] }}">
                                        {{ $alerta['valor'] }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div
                    class="rounded-[2rem] bg-gradient-to-br from-emerald-600 to-sky-600 p-6 text-white shadow-2xl shadow-emerald-500/20 sm:p-8">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-100">
                        Estado actual
                    </p>
                    <h3 class="mt-2 text-2xl font-black">
                        {{ $gestionActual }}
                    </h3>

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
        <section class="soft-panel card-shadow rounded-[2rem] p-6 sm:p-8">
            <div class="flex flex-col gap-2">
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-cyan-700">
                    Estructura académica
                </p>
                <h3 class="text-2xl font-black text-slate-950">
                    Resumen estructural del sistema
                </h3>
                <p class="text-sm leading-7 text-slate-600">
                    Estado general de la configuración académica e institucional cargada en la plataforma.
                </p>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($estructuraAcademica as $item)
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                                {{ $item['label'] }}
                            </p>
                            <span
                                class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                Configurado
                            </span>
                        </div>

                        <p class="mt-4 text-3xl font-black text-slate-950">
                            {{ $item['value'] }}
                        </p>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const chartRoles = document.getElementById('chartRoles');
        const chartEspecialidades = document.getElementById('chartEspecialidades');
        const chartInscripciones = document.getElementById('chartInscripciones');

        if (chartRoles) {
            new Chart(chartRoles, {
                type: 'doughnut',
                data: {
                    labels: @json(array_keys($chartRoles)),
                    datasets: [{
                        data: @json(array_values($chartRoles)),
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '68%'
                }
            });
        }

        if (chartEspecialidades) {
            new Chart(chartEspecialidades, {
                type: 'bar',
                data: {
                    labels: @json(array_keys($chartEspecialidades)),
                    datasets: [{
                        label: 'Estudiantes',
                        data: @json(array_values($chartEspecialidades)),
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        if (chartInscripciones) {
            new Chart(chartInscripciones, {
                type: 'line',
                data: {
                    labels: @json(array_keys($chartInscripciones)),
                    datasets: [{
                        label: 'Inscripciones',
                        data: @json(array_values($chartInscripciones)),
                        tension: 0.35,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }
    </script>
@endpush