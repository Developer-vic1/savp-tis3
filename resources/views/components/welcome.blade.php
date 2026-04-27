@extends('layouts.app')

@section('content')
    <style>
        .dashboard-bg {
            min-height: calc(100vh - 0px);
            background:
                radial-gradient(circle at top left, rgba(16, 185, 129, 0.10), transparent 24%),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.10), transparent 24%),
                linear-gradient(to bottom, #f8fafc, #eef4f1);
            position: relative;
        }

        .dashboard-bg::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: .06;
            background-image:
                linear-gradient(rgba(15, 23, 42, 0.9) 1px, transparent 1px),
                linear-gradient(90deg, rgba(15, 23, 42, 0.9) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
        }

        .soft-panel {
            background: rgba(255, 255, 255, 0.86);
            border: 1px solid rgba(226, 232, 240, 0.95);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .card-shadow {
            box-shadow:
                0 20px 40px rgba(15, 23, 42, 0.07),
                0 8px 20px rgba(15, 23, 42, 0.04);
        }

        .nav-link {
            transition: all .22s ease;
        }

        .nav-link:hover {
            transform: translateX(2px);
        }

        .quick-card {
            transition: all .22s ease;
        }

        .quick-card:hover {
            transform: translateY(-2px);
        }
    </style>

    <div class="dashboard-bg relative">
        <div class="relative z-10 flex min-h-screen">

            {{-- SIDEBAR --}}
            <aside class="hidden w-72 shrink-0 border-r border-slate-200/80 bg-white/80 backdrop-blur-xl lg:block">
                <div class="flex h-full flex-col px-6 py-6">
                    {{-- Marca --}}
                    <div class="flex items-center gap-4">
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-lg ring-1 ring-slate-200">
                            <img src="{{ asset('image/LOGO FT3 A.jpg') }}"
                                alt="Logo Franz Tamayo N°3"
                                class="h-10 w-10 object-contain">
                        </div>

                        <div>
                            <p class="font-display text-sm font-bold text-slate-950">
                                Franz Tamayo N°3
                            </p>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                SAVP – TIS 3
                            </p>
                        </div>
                    </div>

                    {{-- Rol --}}
                    <div class="mt-8 rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 p-4 text-white">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-100">
                            Panel actual
                        </p>
                        <p class="mt-2 text-lg font-bold">
                            Administrador
                        </p>
                        <p class="mt-1 text-sm text-white/90">
                            Control general del sistema institucional.
                        </p>
                    </div>

                    {{-- Navegación --}}
                    <nav class="mt-8 space-y-2">
                        <a href="#"
                            class="nav-link flex items-center gap-3 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 ring-1 ring-emerald-200">
                            <span>🏠</span>
                            <span>Resumen general</span>
                        </a>

                        <a href="#"
                            class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            <span>👥</span>
                            <span>Usuarios</span>
                        </a>

                        <a href="#"
                            class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            <span>🎓</span>
                            <span>Estudiantes</span>
                        </a>

                        <a href="#"
                            class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            <span>🧑‍🏫</span>
                            <span>Docentes</span>
                        </a>

                        <a href="#"
                            class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            <span>📘</span>
                            <span>Académico</span>
                        </a>

                        <a href="#"
                            class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            <span>🧭</span>
                            <span>Orientación</span>
                        </a>

                        <a href="#"
                            class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            <span>📊</span>
                            <span>Reportes</span>
                        </a>

                        <a href="#"
                            class="nav-link flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-medium text-slate-700 hover:bg-slate-50">
                            <span>⚙️</span>
                            <span>Configuración</span>
                        </a>
                    </nav>

                    {{-- Footer sidebar --}}
                    <div class="mt-auto rounded-2xl border border-slate-200 bg-white p-4 text-sm text-slate-600">
                        <p class="font-semibold text-slate-800">
                            Estado del sistema
                        </p>
                        <p class="mt-2">
                            Plataforma institucional operativa.
                        </p>
                        <div class="mt-3 flex items-center gap-2 text-emerald-700">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-semibold uppercase tracking-[0.16em]">Activo</span>
                        </div>
                    </div>
                </div>
            </aside>

            {{-- MAIN --}}
            <main class="flex-1 px-5 py-5 sm:px-6 lg:px-8">
                {{-- TOPBAR --}}
                <div class="soft-panel card-shadow rounded-[1.8rem] px-5 py-4 sm:px-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">
                                Panel administrativo
                            </p>
                            <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                                Bienvenido, {{ Auth::user()->name ?? 'Administrador' }}
                            </h1>
                            <p class="mt-2 text-sm leading-7 text-slate-600">
                                Gestiona usuarios, información institucional y supervisa la actividad general del sistema.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600">
                                {{ now()->format('d/m/Y') }}
                            </div>

                            <a href="#"
                                class="rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20">
                                Acción rápida
                            </a>
                        </div>
                    </div>
                </div>

                {{-- HERO / RESUMEN --}}
                <section class="mt-6">
                    <div class="grid gap-6 xl:grid-cols-[1.08fr_.92fr]">
                        <div class="soft-panel card-shadow rounded-[2rem] p-6 sm:p-8">
                            <span
                                class="inline-flex items-center rounded-full border border-emerald-200 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700 shadow-sm">
                                Gestión institucional
                            </span>

                            <h2 class="mt-5 max-w-3xl text-3xl font-black leading-tight tracking-tight text-slate-950 sm:text-4xl">
                                Control centralizado del sistema institucional y académico.
                            </h2>

                            <p class="mt-5 max-w-2xl text-base leading-8 text-slate-600">
                                Desde este panel puedes supervisar los módulos principales, administrar registros,
                                consultar información clave y dar seguimiento al funcionamiento general de la plataforma.
                            </p>

                            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                        Módulo
                                    </p>
                                    <p class="mt-2 text-lg font-bold text-slate-950">
                                        Administración
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                        Enfoque
                                    </p>
                                    <p class="mt-2 text-lg font-bold text-slate-950">
                                        Control y seguimiento
                                    </p>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-white p-5">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                        Estado
                                    </p>
                                    <p class="mt-2 text-lg font-bold text-emerald-700">
                                        Operativo
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div
                            class="rounded-[2rem] bg-gradient-to-br from-slate-950 via-emerald-950 to-sky-900 p-6 text-white shadow-2xl sm:p-8">
                            <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-200">
                                Vista rápida
                            </p>

                            <h3 class="mt-3 text-2xl font-black leading-tight">
                                Panel de control institucional
                            </h3>

                            <p class="mt-4 text-sm leading-8 text-white/85">
                                Accede a indicadores, movimientos recientes y rutas rápidas para administrar el sistema
                                de forma más ágil.
                            </p>

                            <div class="mt-8 space-y-4">
                                <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/10">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm text-slate-300">Progreso operativo</p>
                                        <p class="text-sm font-semibold text-emerald-300">Estable</p>
                                    </div>
                                    <div class="mt-4 h-3 rounded-full bg-white/10">
                                        <div class="h-3 w-[84%] rounded-full bg-gradient-to-r from-emerald-400 to-sky-400"></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/10">
                                        <p class="text-sm text-slate-300">Rol activo</p>
                                        <p class="mt-2 text-xl font-bold">Admin</p>
                                    </div>
                                    <div class="rounded-2xl bg-white/10 p-5 ring-1 ring-white/10">
                                        <p class="text-sm text-slate-300">Sesión</p>
                                        <p class="mt-2 text-xl font-bold">Segura</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- MÉTRICAS --}}
                <section class="mt-6">
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <article class="soft-panel card-shadow rounded-[1.8rem] p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                Usuarios
                            </p>
                            <p class="mt-3 text-3xl font-black text-slate-950">128</p>
                            <p class="mt-2 text-sm text-slate-600">
                                Registros activos en el sistema.
                            </p>
                        </article>

                        <article class="soft-panel card-shadow rounded-[1.8rem] p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                Estudiantes
                            </p>
                            <p class="mt-3 text-3xl font-black text-slate-950">542</p>
                            <p class="mt-2 text-sm text-slate-600">
                                Registros académicos consolidados.
                            </p>
                        </article>

                        <article class="soft-panel card-shadow rounded-[1.8rem] p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                Docentes
                            </p>
                            <p class="mt-3 text-3xl font-black text-slate-950">38</p>
                            <p class="mt-2 text-sm text-slate-600">
                                Personal docente disponible.
                            </p>
                        </article>

                        <article class="soft-panel card-shadow rounded-[1.8rem] p-5">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-500">
                                Especialidades
                            </p>
                            <p class="mt-3 text-3xl font-black text-slate-950">9</p>
                            <p class="mt-2 text-sm text-slate-600">
                                Áreas técnicas registradas.
                            </p>
                        </article>
                    </div>
                </section>

                {{-- ACCIONES RÁPIDAS --}}
                <section class="mt-6">
                    <div class="soft-panel card-shadow rounded-[2rem] p-6 sm:p-8">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-700">
                                    Acciones rápidas
                                </p>
                                <h3 class="mt-2 text-2xl font-black text-slate-950">
                                    Accesos directos del administrador
                                </h3>
                            </div>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            <a href="#"
                                class="quick-card rounded-[1.7rem] border border-slate-200 bg-white p-5 shadow-sm hover:border-emerald-300">
                                <p class="text-2xl">👤</p>
                                <h4 class="mt-3 text-lg font-bold text-slate-950">Registrar usuario</h4>
                                <p class="mt-2 text-sm leading-7 text-slate-600">
                                    Crear nuevos accesos al sistema.
                                </p>
                            </a>

                            <a href="#"
                                class="quick-card rounded-[1.7rem] border border-slate-200 bg-white p-5 shadow-sm hover:border-sky-300">
                                <p class="text-2xl">🧾</p>
                                <h4 class="mt-3 text-lg font-bold text-slate-950">Ver reportes</h4>
                                <p class="mt-2 text-sm leading-7 text-slate-600">
                                    Consultar resultados e indicadores.
                                </p>
                            </a>

                            <a href="#"
                                class="quick-card rounded-[1.7rem] border border-slate-200 bg-white p-5 shadow-sm hover:border-emerald-300">
                                <p class="text-2xl">🏫</p>
                                <h4 class="mt-3 text-lg font-bold text-slate-950">Gestionar datos</h4>
                                <p class="mt-2 text-sm leading-7 text-slate-600">
                                    Administrar información institucional.
                                </p>
                            </a>

                            <a href="#"
                                class="quick-card rounded-[1.7rem] border border-slate-200 bg-white p-5 shadow-sm hover:border-sky-300">
                                <p class="text-2xl">⚙️</p>
                                <h4 class="mt-3 text-lg font-bold text-slate-950">Configuración</h4>
                                <p class="mt-2 text-sm leading-7 text-slate-600">
                                    Ajustar parámetros del sistema.
                                </p>
                            </a>
                        </div>
                    </div>
                </section>

                {{-- BLOQUES INFERIORES --}}
                <section class="mt-6 grid gap-6 xl:grid-cols-[1.05fr_.95fr]">
                    <div class="soft-panel card-shadow rounded-[2rem] p-6 sm:p-8">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">
                            Actividad reciente
                        </p>
                        <h3 class="mt-2 text-2xl font-black text-slate-950">
                            Seguimiento del sistema
                        </h3>

                        <div class="mt-6 space-y-4">
                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <p class="text-sm font-semibold text-slate-900">Actualización de registros</p>
                                <p class="mt-1 text-sm text-slate-600">Se registraron movimientos recientes en módulos administrativos.</p>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <p class="text-sm font-semibold text-slate-900">Panel institucional activo</p>
                                <p class="mt-1 text-sm text-slate-600">La plataforma mantiene funcionamiento estable durante la sesión.</p>
                            </div>

                            <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                <p class="text-sm font-semibold text-slate-900">Supervisión general</p>
                                <p class="mt-1 text-sm text-slate-600">El administrador puede revisar módulos, accesos y estructura operativa.</p>
                            </div>
                        </div>
                    </div>

                    <div
                        class="rounded-[2rem] bg-gradient-to-br from-emerald-600 to-sky-600 p-6 text-white shadow-2xl shadow-emerald-500/20 sm:p-8">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-100">
                            Resumen institucional
                        </p>
                        <h3 class="mt-2 text-2xl font-black">
                            Administración central del sistema SAVP – TIS 3
                        </h3>
                        <p class="mt-4 text-sm leading-8 text-white/90">
                            Este panel consolida el acceso a funciones clave del sistema, permitiendo mantener orden,
                            seguimiento y control sobre la estructura institucional y académica.
                        </p>
                    </div>
                </section>
            </main>
        </div>
    </div>
@endsection