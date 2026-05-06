<div x-data="{
        tabDetalle: 'resumen',
        init() {
            this.$nextTick(() => {
                window.iniciarGraficosGestionCursos?.();
            });
        }
    }" x-on:curso-creado.window="
        Swal.fire({
            icon: 'success',
            title: 'Curso registrado',
            text: 'El curso académico fue registrado correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " x-on:curso-actualizado.window="
        Swal.fire({
            icon: 'success',
            title: 'Curso actualizado',
            text: 'La información institucional del curso fue actualizada correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " x-on:curso-desactivado.window="
        Swal.fire({
            icon: 'success',
            title: 'Curso desactivado',
            text: 'El curso fue desactivado sin eliminar su información histórica.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#d97706'
        });
    " x-on:curso-reactivado.window="
        Swal.fire({
            icon: 'success',
            title: 'Curso reactivado',
            text: 'El curso fue reactivado correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " x-on:clase-horario-creada.window="
        Swal.fire({
            icon: 'success',
            title: 'Clase agregada',
            text: 'La clase fue registrada correctamente dentro del horario institucional.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " x-on:clase-horario-quitada.window="
        Swal.fire({
            icon: 'success',
            title: 'Clase retirada',
            text: 'La clase fue retirada correctamente del horario institucional.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#d97706'
        });
    " x-on:error-general.window="
        Swal.fire({
            icon: 'error',
            title: 'No se pudo completar la acción',
            text: $event.detail.mensaje ?? 'Ocurrió un problema inesperado.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#dc2626'
        });
    " x-on:success-general.window="
        if ($event.detail.mensaje) {
            window.uiHelpers?.toast({
                icon: 'success',
                title: $event.detail.mensaje
            });
        }
    " class="space-y-6">
    <style>
        [x-cloak] {
            display: none !important;
        }

        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: none !important;
        }

        select::-ms-expand {
            display: none;
        }

        .curso-backdrop-diffuse {
            background:
                radial-gradient(circle at 15% 18%, rgba(5, 150, 105, 0.26), transparent 34%),
                radial-gradient(circle at 78% 12%, rgba(2, 132, 199, 0.22), transparent 34%),
                radial-gradient(circle at 50% 92%, rgba(124, 58, 237, 0.18), transparent 42%),
                rgba(15, 23, 42, 0.60);
            backdrop-filter: blur(16px);
        }

        html.dark .curso-backdrop-diffuse {
            background:
                radial-gradient(circle at 15% 18%, rgba(52, 211, 153, 0.18), transparent 34%),
                radial-gradient(circle at 78% 12%, rgba(56, 189, 248, 0.16), transparent 34%),
                radial-gradient(circle at 50% 92%, rgba(167, 139, 250, 0.14), transparent 42%),
                rgba(2, 6, 23, 0.78);
            backdrop-filter: blur(18px);
        }

        .curso-hero-bg {
            background:
                radial-gradient(circle at 12% 18%, var(--ui-primary-soft), transparent 30%),
                radial-gradient(circle at 82% 22%, var(--ui-info-soft), transparent 34%),
                radial-gradient(circle at 48% 100%, var(--ui-violet-soft), transparent 38%),
                linear-gradient(135deg, var(--ui-surface), var(--ui-surface-soft));
        }

        .curso-panel-glow {
            background:
                radial-gradient(circle at 20% 15%, var(--ui-primary-soft), transparent 30%),
                radial-gradient(circle at 85% 30%, var(--ui-info-soft), transparent 34%),
                linear-gradient(135deg, var(--ui-surface), var(--ui-surface-soft));
        }

        .curso-grid-bg {
            background-image:
                linear-gradient(to right, color-mix(in srgb, var(--ui-border) 55%, transparent) 1px, transparent 1px),
                linear-gradient(to bottom, color-mix(in srgb, var(--ui-border) 55%, transparent) 1px, transparent 1px);
            background-size: 28px 28px;
        }

        .curso-floating {
            animation: cursoFloat 7s ease-in-out infinite;
        }

        @keyframes cursoFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-8px);
            }
        }

        .curso-fade-in {
            animation: cursoFadeIn .28s ease-out both;
        }

        @keyframes cursoFadeIn {
            from {
                opacity: 0;
                transform: translateY(8px) scale(.985);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .curso-drawer-in {
            animation: cursoDrawerIn .32s cubic-bezier(.22, 1, .36, 1) both;
        }

        @keyframes cursoDrawerIn {
            from {
                opacity: 0;
                transform: translateX(30px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .curso-select-wrap::after {
            content: '';
            position: absolute;
            right: 1rem;
            top: 50%;
            width: .55rem;
            height: .55rem;
            border-right: 2px solid var(--ui-muted);
            border-bottom: 2px solid var(--ui-muted);
            transform: translateY(-65%) rotate(45deg);
            pointer-events: none;
        }

        .curso-tab {
            border: 1px solid var(--ui-border);
            background: var(--ui-surface);
            color: var(--ui-muted);
            transition: all .2s ease;
        }

        .curso-tab:hover {
            transform: translateY(-1px);
            color: var(--ui-text);
            border-color: var(--ui-primary-border);
        }

        .curso-tab-active {
            background: var(--ui-primary-soft);
            color: var(--ui-primary);
            border-color: var(--ui-primary-border);
            box-shadow: 0 0 0 4px var(--ui-ring);
        }

        .horario-cell {
            min-height: 112px;
            transition:
                transform .18s ease,
                box-shadow .18s ease,
                border-color .18s ease,
                background .18s ease;
        }

        .horario-cell:hover {
            transform: translateY(-2px);
            box-shadow: var(--ui-shadow-sm);
        }

        .horario-cell-free {
            border: 1px dashed var(--ui-border);
            background:
                linear-gradient(135deg, var(--ui-surface), var(--ui-surface-soft));
        }

        .horario-cell-free:hover {
            border-color: var(--ui-primary-border);
            background:
                radial-gradient(circle at 20% 20%, var(--ui-primary-soft), transparent 40%),
                var(--ui-surface);
        }

        .horario-cell-materia {
            border: 1px solid var(--ui-info-border);
            background:
                radial-gradient(circle at 15% 15%, var(--ui-info-soft), transparent 45%),
                var(--ui-surface);
        }

        .horario-cell-especialidad {
            border: 1px solid var(--ui-violet-border);
            background:
                radial-gradient(circle at 15% 15%, var(--ui-violet-soft), transparent 45%),
                var(--ui-surface);
        }

        .horario-cell-pendiente {
            border: 1px solid var(--ui-warning-border);
            background:
                radial-gradient(circle at 15% 15%, var(--ui-warning-soft), transparent 45%),
                var(--ui-surface);
        }

        .curso-input-invalid {
            border-color: var(--ui-danger) !important;
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--ui-danger) 18%, transparent) !important;
        }

        .curso-pill {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            border-radius: 9999px;
            padding: .35rem .7rem;
            font-size: .72rem;
            font-weight: 800;
            border: 1px solid var(--ui-border);
            background: var(--ui-surface-soft);
            color: var(--ui-muted);
        }

        .curso-mini-scroll {
            scrollbar-width: thin;
            scrollbar-color: color-mix(in srgb, var(--ui-muted) 35%, transparent) transparent;
        }

        .curso-mini-scroll::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .curso-mini-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .curso-mini-scroll::-webkit-scrollbar-thumb {
            background: color-mix(in srgb, var(--ui-muted) 35%, transparent);
            border-radius: 999px;
        }
    </style>

    {{-- ENCABEZADO INSTITUCIONAL --}}
    <section class="ui-card overflow-hidden rounded-[2rem] curso-fade-in">
        <div class="grid gap-0 xl:grid-cols-12">
            <div class="p-6 sm:p-7 xl:col-span-8">
                <div class="inline-flex flex-wrap items-center gap-2 rounded-full px-3 py-1 ring-1"
                    style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                    <span class="h-2 w-2 rounded-full" style="background: var(--ui-primary);"></span>
                    <span class="text-xs font-black uppercase tracking-[0.16em]">
                        Catálogo académico base
                    </span>
                </div>

                <h2 class="mt-4 text-3xl font-black tracking-tight md:text-4xl" style="color: var(--ui-text);">
                    Gestión de Cursos
                </h2>

                <p class="mt-3 max-w-3xl text-sm leading-6" style="color: var(--ui-muted);">
                    Administra los cursos oficiales de la institución y visualiza su relación con Plan de Asignatura,
                    Plan de Especialidad y la organización semanal de Horarios. Este módulo consulta y articula la
                    información académica sin duplicar datos curriculares.
                </p>

                <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center">
                    <button type="button" wire:click="abrirModalCrear" wire:loading.attr="disabled"
                        wire:target="abrirModalCrear" class="ui-btn-primary disabled:cursor-wait disabled:opacity-60">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Registrar curso
                    </button>

                    <button type="button"
                        onclick="document.getElementById('tabla-cursos')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                        class="ui-btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 6A2.25 2.25 0 0 1 6 3.75h12A2.25 2.25 0 0 1 20.25 6v12A2.25 2.25 0 0 1 18 20.25H6A2.25 2.25 0 0 1 3.75 18V6Zm0 3.75h16.5M9.75 3.75v16.5" />
                        </svg>
                        Ver cursos
                    </button>

                    <button type="button"
                        onclick="document.getElementById('panel-horarios-info')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                        class="ui-btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18M4.5 6.75h15A1.5 1.5 0 0 1 21 8.25v10.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A1.5 1.5 0 0 1 4.5 6.75Z" />
                        </svg>
                        Vista de horarios
                    </button>
                </div>
            </div>

            <div class="curso-hero-bg border-t p-6 sm:p-7 xl:col-span-4 xl:border-l xl:border-t-0"
                style="border-color: var(--ui-border);">
                <div class="curso-floating rounded-[2rem] border p-5 shadow-sm"
                    style="background: var(--ui-surface); border-color: var(--ui-border); box-shadow: var(--ui-shadow-sm);">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                                Gestión activa
                            </p>

                            <h3 class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                @if ($gestionActiva)
                                    Gestión {{ $gestionActiva->ani_gea ?? 'actual' }}
                                @else
                                    Sin gestión activa
                                @endif
                            </h3>

                            <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                                Panel académico institucional
                            </p>
                        </div>

                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl ring-1"
                            style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6.75v10.5m-6-8.25h12M4.5 5.25A2.25 2.25 0 0 1 6.75 3h10.5A2.25 2.25 0 0 1 19.5 5.25v13.5A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75V5.25Z" />
                            </svg>
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl border p-3"
                            style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                            <p class="text-xs" style="color: var(--ui-muted);">Cursos</p>
                            <p class="mt-1 text-2xl font-black" style="color: var(--ui-text);">{{ $totalCursos }}</p>
                        </div>

                        <div class="rounded-2xl border p-3"
                            style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                            <p class="text-xs" style="color: var(--ui-muted);">Horarios</p>
                            <p class="mt-1 text-2xl font-black" style="color: var(--ui-text);">{{ $totalConHorarios }}
                            </p>
                        </div>

                        <div class="rounded-2xl border p-3"
                            style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                            <p class="text-xs" style="color: var(--ui-muted);">Plan asignatura</p>
                            <p class="mt-1 text-2xl font-black" style="color: var(--ui-text);">
                                {{ $totalConPlanAsignatura }}
                            </p>
                        </div>

                        <div class="rounded-2xl border p-3"
                            style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                            <p class="text-xs" style="color: var(--ui-muted);">Especialidad</p>
                            <p class="mt-1 text-2xl font-black" style="color: var(--ui-text);">
                                {{ $totalConPlanEspecialidad }}
                            </p>
                        </div>
                    </div>

                    @unless ($gestionActiva)
                        <div class="ui-alert-warning mt-4">
                            No existe una gestión académica activa. Algunas consultas institucionales pueden quedar
                            limitadas.
                        </div>
                    @endunless
                </div>
            </div>
        </div>
    </section>

    {{-- CARDS RESUMEN --}}
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
        <article class="ui-card ui-card-hover p-5 xl:col-span-1">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                        Total
                    </p>
                    <h3 class="mt-2 text-3xl font-black" style="color: var(--ui-text);">{{ $totalCursos }}</h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl"
                    style="background: var(--ui-primary-soft); color: var(--ui-primary);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4.5 19.5h15M4.5 4.5h15M6.75 4.5v15m10.5-15v15M9 8.25h6M9 12h6M9 15.75h6" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs leading-5" style="color: var(--ui-muted);">Cursos oficiales registrados.</p>
        </article>

        <article class="ui-card ui-card-hover p-5 xl:col-span-1">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                        Activos
                    </p>
                    <h3 class="mt-2 text-3xl font-black" style="color: var(--ui-text);">{{ $totalActivos }}</h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl"
                    style="background: var(--ui-primary-soft); color: var(--ui-primary);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs leading-5" style="color: var(--ui-muted);">Disponibles para operación.</p>
        </article>

        <article class="ui-card ui-card-hover p-5 xl:col-span-1">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                        Plan asignatura
                    </p>
                    <h3 class="mt-2 text-3xl font-black" style="color: var(--ui-text);">{{ $totalConPlanAsignatura }}
                    </h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl"
                    style="background: var(--ui-info-soft); color: var(--ui-info);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.75v10.5M6.75 12h10.5M5.25 4.5h13.5A2.25 2.25 0 0 1 21 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 17.25V6.75A2.25 2.25 0 0 1 5.25 4.5Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs leading-5" style="color: var(--ui-muted);">Cursos con materias.</p>
        </article>

        <article class="ui-card ui-card-hover p-5 xl:col-span-1">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                        Especialidad
                    </p>
                    <h3 class="mt-2 text-3xl font-black" style="color: var(--ui-text);">{{ $totalConPlanEspecialidad }}
                    </h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl"
                    style="background: var(--ui-violet-soft); color: var(--ui-violet);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5 14.5M9.75 3.104c.251.023.501.05.75.082m-.75-.082a24.301 24.301 0 0 0-4.5 0m4.5 0v.04m0 0a2.25 2.25 0 0 1 2.25 2.25v2.25m0 0 1.5 1.5m-1.5-1.5h3.75m-3.75 0v8.25a2.25 2.25 0 0 0 2.25 2.25h.75m-8.25-5.25h4.5" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs leading-5" style="color: var(--ui-muted);">Carga técnica asociada.</p>
        </article>

        <article class="ui-card ui-card-hover p-5 xl:col-span-1">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                        Horarios
                    </p>
                    <h3 class="mt-2 text-3xl font-black" style="color: var(--ui-text);">{{ $totalConHorarios }}</h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl"
                    style="background: var(--ui-primary-soft); color: var(--ui-primary);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18M4.5 6.75h15A1.5 1.5 0 0 1 21 8.25v10.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A1.5 1.5 0 0 1 4.5 6.75Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs leading-5" style="color: var(--ui-muted);">Cursos organizados.</p>
        </article>

        <article class="ui-card ui-card-hover p-5 xl:col-span-1">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                        Pendientes
                    </p>
                    <h3 class="mt-2 text-3xl font-black" style="color: var(--ui-text);">{{ $totalSinHorarios }}</h3>
                </div>
                <div class="flex h-11 w-11 items-center justify-center rounded-2xl"
                    style="background: var(--ui-warning-soft); color: var(--ui-warning);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m0 3.75h.008v.008H12V16.5Zm9-4.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-xs leading-5" style="color: var(--ui-muted);">Cursos sin horario.</p>
        </article>
    </section>

    {{-- ALERTA DE ENFOQUE --}}
    <section class="ui-alert-info curso-fade-in">
        <div class="flex flex-col gap-3 md:flex-row md:items-start">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl"
                style="background: var(--ui-info-soft); color: var(--ui-info);">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
            </div>

            <div>
                <p class="font-black">Enfoque correcto del módulo</p>
                <p class="mt-1 text-sm leading-6">
                    Cursos administra el catálogo académico base. Las materias se leen desde Plan de Asignatura,
                    las especialidades desde Plan de Especialidad y la organización semanal se registra en Horarios.
                    En el detalle de cada curso puedes abrir la matriz institucional y crear una clase pulsando un
                    bloque libre.
                </p>
            </div>
        </div>
    </section>

    {{-- FILTROS --}}
    <section class="ui-card rounded-[2rem] p-5 curso-fade-in">
        <div class="grid gap-4 xl:grid-cols-12">
            <div class="xl:col-span-3">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Buscar curso</label>
                <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 shadow-sm transition focus-within:ring-4"
                    style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-text); --tw-ring-color: var(--ui-ring);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" style="color: var(--ui-muted);"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                    </svg>

                    <input type="text" wire:model.live.debounce.400ms="search"
                        placeholder="Buscar por curso, nivel o descripción..."
                        class="w-full border-0 bg-transparent p-0 text-sm focus:outline-none focus:ring-0"
                        style="color: var(--ui-text);">
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Estado</label>
                <div class="relative curso-select-wrap">
                    <select wire:model.live="estado" class="ui-select pr-10">
                        <option value="">Todos</option>
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                    </select>
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Nivel académico</label>
                <div class="relative curso-select-wrap">
                    <select wire:model.live="nivel" class="ui-select pr-10">
                        <option value="">Todos</option>
                        @foreach ($nivelesDisponibles as $nivelItem)
                            <option value="{{ $nivelItem }}">{{ $nivelItem }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Gestión</label>
                <div class="relative curso-select-wrap">
                    <select wire:model.live="gestionFiltro" class="ui-select pr-10">
                        <option value="">Gestión activa</option>
                        @foreach ($gestiones as $gestion)
                            <option value="{{ $gestion->cod_gea }}">
                                Gestión {{ $gestion->ani_gea ?? 'sin año' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="xl:col-span-1">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Materias</label>
                <div class="relative curso-select-wrap">
                    <select wire:model.live="filtroPlanAsignatura" class="ui-select pr-10">
                        <option value="">Todos</option>
                        <option value="con">Con</option>
                        <option value="sin">Sin</option>
                    </select>
                </div>
            </div>

            <div class="xl:col-span-1">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Horario</label>
                <div class="relative curso-select-wrap">
                    <select wire:model.live="filtroHorario" class="ui-select pr-10">
                        <option value="">Todos</option>
                        <option value="con">Con</option>
                        <option value="sin">Sin</option>
                    </select>
                </div>
            </div>

            <div class="xl:col-span-1">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Limpiar</label>
                <button type="button" wire:click="limpiarFiltros" class="ui-btn-secondary w-full px-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </section>

    {{-- CONTENIDO PRINCIPAL --}}
    <section id="tabla-cursos" class="grid gap-6 xl:grid-cols-12">
        {{-- TABLA --}}
        <div class="xl:col-span-8">
            <div class="ui-table-wrap overflow-hidden rounded-[2rem]">
                <div class="flex flex-col gap-4 border-b p-5 md:flex-row md:items-center md:justify-between"
                    style="border-color: var(--ui-border);">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                            Catálogo institucional
                        </p>

                        <h3 class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                            Cursos registrados
                        </h3>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="ui-badge-info">{{ $totalCursos }} cursos</span>
                        <span class="ui-badge-success">{{ $totalActivos }} activos</span>
                        <span class="ui-badge-warning">{{ $totalSinHorarios }} pendientes de horario</span>
                    </div>
                </div>

                <div class="overflow-x-auto curso-mini-scroll">
                    <table class="ui-table min-w-[980px]">
                        <thead>
                            <tr>
                                <th>Curso</th>
                                <th>Nivel</th>
                                <th>Plan asignatura</th>
                                <th>Especialidad</th>
                                <th>Horario</th>
                                <th>Estudiantes</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($cursos as $curso)
                                <tr wire:key="curso-{{ $curso['cod_cur'] }}">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl text-sm font-black ring-1"
                                                style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                                                {{ $curso['ord_cur'] }}°
                                            </div>

                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-black" style="color: var(--ui-text);">
                                                    {{ $curso['nom_cur'] }}
                                                </p>

                                                <p class="mt-0.5 max-w-[260px] truncate text-xs"
                                                    style="color: var(--ui-muted);">
                                                    {{ $curso['des_cur'] ?: 'Sin descripción institucional registrada.' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="ui-badge-info">{{ $curso['niv_cur'] }}</span>
                                    </td>

                                    <td>
                                        @if ($curso['plan_asignatura_count'] > 0)
                                            <span class="ui-badge-success">{{ $curso['plan_asignatura_count'] }} materias</span>
                                        @else
                                            <span class="ui-badge-warning">Sin materias</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($curso['plan_especialidad_count'] > 0)
                                            <span class="ui-badge-violet">{{ $curso['plan_especialidad_count'] }} técnica</span>
                                        @else
                                            <span class="curso-pill">No aplica / sin plan</span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="space-y-1">
                                            @if ($curso['horarios_count'] > 0)
                                                <span class="ui-badge-success">{{ $curso['horarios_count'] }} bloques</span>
                                            @else
                                                <span class="ui-badge-warning">Pendiente</span>
                                            @endif

                                            <div class="h-1.5 w-24 overflow-hidden rounded-full"
                                                style="background: var(--ui-surface-muted);">
                                                <div class="h-full rounded-full"
                                                    style="width: {{ $curso['porcentaje_horario'] }}%; background: var(--ui-primary);">
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <p class="text-sm font-black" style="color: var(--ui-text);">
                                            {{ $curso['inscritos_count'] }}
                                        </p>
                                        <p class="text-xs" style="color: var(--ui-muted);">
                                            estudiantes
                                        </p>
                                    </td>

                                    <td>
                                        @if ($curso['est_cur'] === 'ACTIVO')
                                            <span class="ui-badge-success">
                                                <span class="h-2 w-2 rounded-full"
                                                    style="background: var(--ui-primary);"></span>
                                                Activo
                                            </span>
                                        @else
                                            <span class="ui-badge-danger">
                                                <span class="h-2 w-2 rounded-full" style="background: var(--ui-danger);"></span>
                                                Inactivo
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="flex items-center justify-center gap-1.5">
                                            <button type="button" wire:click="abrirModalDetalle(@js($curso['cod_cur']))"
                                                wire:loading.attr="disabled" wire:target="abrirModalDetalle"
                                                class="ui-icon-btn disabled:cursor-wait disabled:opacity-60"
                                                title="Ver detalle institucional">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M2.036 12.322a1 1 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178a1 1 0 0 1 0 .644C20.577 16.49 16.639 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                            </button>

                                            <button type="button" wire:click="abrirModalEditar(@js($curso['cod_cur']))"
                                                wire:loading.attr="disabled" wire:target="abrirModalEditar"
                                                class="ui-icon-btn disabled:cursor-wait disabled:opacity-60"
                                                title="Editar curso">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m16.862 4.487 1.687-1.688a2.25 2.25 0 1 1 3.182 3.182L10.582 17.13a4.5 4.5 0 0 1-1.897 1.13L6 19l.74-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487Z" />
                                                </svg>
                                            </button>

                                            <button type="button" wire:click="irAHorarios(@js($curso['cod_cur']))"
                                                class="ui-icon-btn" title="Abrir módulo de horarios">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18M4.5 6.75h15A1.5 1.5 0 0 1 21 8.25v10.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A1.5 1.5 0 0 1 4.5 6.75Z" />
                                                </svg>
                                            </button>

                                            @if ($curso['est_cur'] === 'ACTIVO')
                                                <button type="button"
                                                    onclick="window.uiHelpers.confirm({
                                                                                                                                        title: '¿Desactivar curso?',
                                                                                                                                        text: 'El curso no será eliminado; quedará inactivo para nuevas operaciones.',
                                                                                                                                        icon: 'warning',
                                                                                                                                        confirmButtonText: 'Sí, desactivar',
                                                                                                                                        confirmButtonColor: '#d97706',
                                                                                                                                        onConfirm: () => $wire.desactivarCurso(@js($curso['cod_cur']))
                                                                                                                                    })"
                                                    class="ui-icon-btn" title="Desactivar curso">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18 12H6m15 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>
                                                </button>
                                            @else
                                                <button type="button"
                                                    onclick="window.uiHelpers.confirm({
                                                                                                                                        title: '¿Reactivar curso?',
                                                                                                                                        text: 'El curso volverá a estar disponible para operación académica.',
                                                                                                                                        icon: 'question',
                                                                                                                                        confirmButtonText: 'Sí, reactivar',
                                                                                                                                        confirmButtonColor: '#059669',
                                                                                                                                        onConfirm: () => $wire.reactivarCurso(@js($curso['cod_cur']))
                                                                                                                                    })"
                                                    class="ui-icon-btn" title="Reactivar curso">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-16 text-center">
                                        <div class="mx-auto max-w-lg">
                                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem]"
                                                style="background: var(--ui-surface-muted); color: var(--ui-muted);">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 6.75v10.5m-6-8.25h12M4.5 5.25A2.25 2.25 0 0 1 6.75 3h10.5A2.25 2.25 0 0 1 19.5 5.25v13.5A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75V5.25Z" />
                                                </svg>
                                            </div>

                                            <h3 class="mt-5 text-lg font-black" style="color: var(--ui-text);">
                                                No existen cursos registrados
                                            </h3>

                                            <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                                                Registra los cursos oficiales de la institución para iniciar la
                                                planificación académica.
                                            </p>

                                            <button type="button" wire:click="abrirModalCrear" class="ui-btn-primary mt-5">
                                                Registrar primer curso
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col gap-4 border-t px-6 py-4 lg:flex-row lg:items-center lg:justify-between"
                    style="border-color: var(--ui-border);">
                    @if (method_exists($cursos, 'total'))
                        <p class="text-sm" style="color: var(--ui-muted);">
                            Mostrando
                            <span class="font-semibold"
                                style="color: var(--ui-text);">{{ $cursos->firstItem() ?? 0 }}</span>
                            -
                            <span class="font-semibold" style="color: var(--ui-text);">{{ $cursos->lastItem() ?? 0 }}</span>
                            de
                            <span class="font-semibold" style="color: var(--ui-text);">{{ $cursos->total() }}</span>
                            cursos
                        </p>

                        <div>
                            {{ $cursos->links() }}
                        </div>
                    @else
                        <p class="text-sm" style="color: var(--ui-muted);">
                            Sin paginación disponible.
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- PANEL DERECHO --}}
        <aside class="space-y-4 xl:col-span-4">
            <div class="ui-card rounded-[2rem] p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                            Cobertura de horarios
                        </p>
                        <h3 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                            Cursos con organización semanal
                        </h3>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl ring-1"
                        style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18M4.5 6.75h15A1.5 1.5 0 0 1 21 8.25v10.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A1.5 1.5 0 0 1 4.5 6.75Z" />
                        </svg>
                    </div>
                </div>

                <div wire:ignore class="mt-4 h-56 rounded-2xl p-3" style="background: var(--ui-surface-soft);">
                    <canvas id="chartCursosHorarios"></canvas>
                </div>
            </div>

            <div class="ui-card rounded-[2rem] p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                            Estructura académica
                        </p>
                        <h3 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                            Planes por curso
                        </h3>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl ring-1"
                        style="background: var(--ui-violet-soft); color: var(--ui-violet); --tw-ring-color: var(--ui-violet-border);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.125 2.25h3.75A2.25 2.25 0 0 1 16.125 4.5v15a2.25 2.25 0 0 1-2.25 2.25h-3.75A2.25 2.25 0 0 1 7.875 19.5v-15a2.25 2.25 0 0 1 2.25-2.25ZM19.5 5.625v12.75M4.5 5.625v12.75" />
                        </svg>
                    </div>
                </div>

                <div wire:ignore class="mt-4 h-56 rounded-2xl p-3" style="background: var(--ui-surface-soft);">
                    <canvas id="chartCursosPlanAcademico"></canvas>
                </div>
            </div>

            <div id="panel-horarios-info" class="ui-card overflow-hidden rounded-[2rem]">
                <div class="curso-panel-glow p-5">
                    <p class="text-xs font-bold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                        Vista institucional
                    </p>

                    <h3 class="mt-2 text-xl font-black" style="color: var(--ui-text);">
                        Horarios por curso
                    </h3>

                    <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                        Abre el detalle de un curso para consultar su horario por gestión, paralelo y turno.
                        Si un bloque está libre, puedes pulsarlo y registrar una clase directamente.
                    </p>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl border p-3"
                            style="border-color: var(--ui-border); background: var(--ui-surface);">
                            <p class="text-xs" style="color: var(--ui-muted);">Celda libre</p>
                            <p class="mt-1 text-sm font-black" style="color: var(--ui-text);">Crear clase</p>
                        </div>

                        <div class="rounded-2xl border p-3"
                            style="border-color: var(--ui-border); background: var(--ui-surface);">
                            <p class="text-xs" style="color: var(--ui-muted);">Celda ocupada</p>
                            <p class="mt-1 text-sm font-black" style="color: var(--ui-text);">Ver detalle</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </section>

    {{-- MODAL CREAR CURSO INTELIGENTE --}}
    @if ($modalCrear)
        <div wire:key="modal-crear-curso-inteligente"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6" x-data="{
                    validacion: { valido: false, errores: {}, lista: [] },

                    recalcular() {
                        if (typeof window.validarCursoForm === 'function') {
                            this.validacion = window.validarCursoForm(this.$root);
                            return;
                        }

                        this.validacion = {
                            valido: true,
                            errores: {},
                            lista: []
                        };
                    },

                    puedeGuardar() {
                        return this.validacion.valido
                            && @js((bool) ($cursoInteligente['valido'] ?? false))
                            && !@js((bool) ($cursoInteligente['duplicado'] ?? false));
                    }
                }" x-init="$nextTick(() => recalcular())" x-on:input.debounce.150ms="recalcular()"
            x-on:change.debounce.150ms="recalcular()" x-on:keydown.escape.window="$wire.cerrarModalCrear()">
            {{-- BACKDROP --}}
            <div class="absolute inset-0 curso-backdrop-diffuse" wire:click="cerrarModalCrear"></div>

            {{-- MODAL --}}
            <div class="ui-modal curso-fade-in relative z-10 w-full max-w-6xl overflow-hidden" role="dialog"
                aria-modal="true" aria-label="Registrar curso inteligente">
                {{-- HEADER --}}
                <div class="curso-hero-bg border-b px-5 py-5 sm:px-6" style="border-color: var(--ui-border);">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 ring-1"
                                style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                                <span class="h-2 w-2 rounded-full" style="background: var(--ui-primary);"></span>
                                <span class="text-xs font-black uppercase tracking-[0.18em]">
                                    Nuevo curso institucional
                                </span>
                            </div>

                            <h3 class="mt-3 text-2xl font-black tracking-tight md:text-3xl" style="color: var(--ui-text);">
                                Registrar curso inteligente
                            </h3>

                            <p class="mt-2 max-w-4xl text-sm leading-6" style="color: var(--ui-muted);">
                                Escribe el curso de forma natural o selecciónalo desde el catálogo institucional.
                                El sistema interpretará la redacción, normalizará el nombre oficial, asignará el orden,
                                el nivel académico y generará una vista previa antes de guardar.
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarModalCrear" class="ui-icon-btn shrink-0"
                            title="Cerrar modal">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="max-h-[74vh] overflow-y-auto px-5 py-6 sm:px-6 curso-mini-scroll">
                    <div class="grid gap-6 xl:grid-cols-12">

                        {{-- COLUMNA IZQUIERDA --}}
                        <section class="space-y-5 xl:col-span-7">
                            {{-- ALERTA DE VALIDACIÓN --}}
                            <div x-show="!validacion.valido || @js(!($cursoInteligente['valido'] ?? false)) || @js((bool) ($cursoInteligente['duplicado'] ?? false))"
                                x-cloak class="ui-alert-danger">
                                <p class="font-black">
                                    Revisa los datos antes de guardar
                                </p>

                                <ul class="mt-2 list-disc space-y-1 pl-5 text-xs leading-5">
                                    <template x-for="error in validacion.lista" :key="error">
                                        <li x-text="error"></li>
                                    </template>

                                    @if (!($cursoInteligente['valido'] ?? false))
                                        <li>
                                            {{ $cursoInteligente['mensaje'] ?? 'El curso todavía no fue interpretado correctamente.' }}
                                        </li>
                                    @endif

                                    @if (($cursoInteligente['duplicado'] ?? false) === true)
                                        <li>
                                            Ya existe un curso registrado con este orden o nombre institucional.
                                        </li>
                                    @endif
                                </ul>
                            </div>

                            {{-- ENTRADA INTELIGENTE --}}
                            <div class="ui-card-soft overflow-hidden rounded-[1.75rem]">
                                <div class="border-b p-5" style="border-color: var(--ui-border);">
                                    <p class="text-xs font-black uppercase tracking-[0.16em]"
                                        style="color: var(--ui-muted);">
                                        Interpretación inteligente
                                    </p>

                                    <h4 class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                        Redacta o selecciona el curso
                                    </h4>

                                    <p class="mt-1 text-sm leading-6" style="color: var(--ui-muted);">
                                        Puedes escribir ejemplos como “4to secundaria”, “cuarto de secundaria”,
                                        “sexto técnico” o seleccionar directamente un curso oficial.
                                    </p>
                                </div>

                                <div class="space-y-5 p-5">
                                    {{-- INPUT INTELIGENTE --}}
                                    <div>
                                        <label class="ui-label">
                                            Redacción del curso <span class="text-red-500">*</span>
                                        </label>

                                        <div class="relative">
                                            <input type="text" wire:model.live.debounce.450ms="cursoInteligente.entrada"
                                                autocomplete="off" maxlength="120" class="ui-input pr-12"
                                                placeholder="Ej. cuarto secundaria, 4to, sexto técnico...">

                                            <div class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2"
                                                style="color: var(--ui-muted);">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M18.25 8.25 18 9.25l-.25-1a2.25 2.25 0 0 0-1.5-1.5l-1-.25 1-.25a2.25 2.25 0 0 0 1.5-1.5l.25-1 .25 1a2.25 2.25 0 0 0 1.5 1.5l1 .25-1 .25a2.25 2.25 0 0 0-1.5 1.5Z" />
                                                </svg>
                                            </div>
                                        </div>

                                        @error('cursoInteligente.entrada')
                                            <p class="ui-error">{{ $message }}</p>
                                        @enderror

                                        <p class="ui-help">
                                            El texto no se guarda crudo; se interpreta y se convierte en un nombre oficial.
                                        </p>
                                    </div>

                                    {{-- CATÁLOGO DE CURSOS --}}
                                    <div>
                                        <label class="ui-label">
                                            Catálogo institucional
                                        </label>

                                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                            @foreach ($catalogoCursosInstitucionales as $cursoCatalogo)
                                                @php
                                                    $ordenCatalogo = (int) ($cursoCatalogo['orden'] ?? 0);
                                                    $seleccionado = (int) ($cursoInteligente['orden'] ?? 0) === $ordenCatalogo;
                                                @endphp

                                                <button type="button"
                                                    wire:click="seleccionarCursoInstitucional({{ $ordenCatalogo }})"
                                                    wire:loading.attr="disabled" wire:target="seleccionarCursoInstitucional"
                                                    class="group rounded-[1.4rem] border p-4 text-left transition hover:-translate-y-0.5 disabled:cursor-wait disabled:opacity-60"
                                                    style="
                                                                    border-color: {{ $seleccionado ? 'var(--ui-primary-border)' : 'var(--ui-border)' }};
                                                                    background: {{ $seleccionado ? 'var(--ui-primary-soft)' : 'var(--ui-surface)' }};
                                                                    box-shadow: var(--ui-shadow-sm);
                                                                ">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="min-w-0">
                                                            <p class="truncate text-sm font-black"
                                                                style="color: var(--ui-text);">
                                                                {{ $cursoCatalogo['nombre'] ?? 'Curso' }}
                                                            </p>

                                                            <p class="mt-1 line-clamp-2 text-xs"
                                                                style="color: var(--ui-muted);">
                                                                {{ $cursoCatalogo['nivel'] ?? 'Nivel académico' }}
                                                            </p>
                                                        </div>

                                                        <span
                                                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl text-xs font-black"
                                                            style="background: var(--ui-surface-soft); color: var(--ui-primary);">
                                                            {{ $ordenCatalogo }}
                                                        </span>
                                                    </div>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- RESULTADO DE INTERPRETACIÓN --}}
                                    <div class="rounded-[1.5rem] border p-4"
                                        style="border-color: {{ ($cursoInteligente['valido'] ?? false) ? 'var(--ui-primary-border)' : 'var(--ui-warning-border)' }}; background: var(--ui-surface);">
                                        <div class="flex items-start gap-3">
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl"
                                                style="background: {{ ($cursoInteligente['valido'] ?? false) ? 'var(--ui-primary-soft)' : 'var(--ui-warning-soft)' }}; color: {{ ($cursoInteligente['valido'] ?? false) ? 'var(--ui-primary)' : 'var(--ui-warning)' }};">
                                                @if ($cursoInteligente['valido'] ?? false)
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M4.5 12.75 10.5 18.75 19.5 5.25" />
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M12 9v3.75m0 3.75h.008v.008H12V16.5Zm9-4.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>
                                                @endif
                                            </div>

                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm font-black" style="color: var(--ui-text);">
                                                    {{ $cursoInteligente['mensaje'] ?? 'Escribe o selecciona un curso institucional.' }}
                                                </p>

                                                <p class="mt-1 text-xs leading-5" style="color: var(--ui-muted);">
                                                    Confianza de interpretación:
                                                    <strong style="color: var(--ui-text);">
                                                        {{ $cursoInteligente['confianza'] ?? 0 }}%
                                                    </strong>
                                                </p>

                                                <div class="mt-3 h-2 overflow-hidden rounded-full"
                                                    style="background: var(--ui-surface-soft);">
                                                    <div class="h-full rounded-full transition-all duration-500"
                                                        style="width: {{ (int) ($cursoInteligente['confianza'] ?? 0) }}%; background: {{ ($cursoInteligente['valido'] ?? false) ? 'var(--ui-primary)' : 'var(--ui-warning)' }};">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @if (!empty($cursoInteligente['advertencias']))
                                            <div class="mt-4 space-y-2">
                                                @foreach ($cursoInteligente['advertencias'] as $advertencia)
                                                    <div class="rounded-2xl px-3 py-2 text-xs leading-5"
                                                        style="background: var(--ui-warning-soft); color: var(--ui-warning);">
                                                        {{ $advertencia }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if (($cursoInteligente['duplicado'] ?? false) === true)
                                            <div class="ui-alert-danger mt-4">
                                                Ya existe un curso con ese orden o nombre institucional. No se recomienda
                                                duplicar cursos base.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- DATOS OFICIALES GENERADOS --}}
                            <div class="ui-card-soft overflow-hidden rounded-[1.75rem]">
                                <div class="border-b p-5" style="border-color: var(--ui-border);">
                                    <p class="text-xs font-black uppercase tracking-[0.16em]"
                                        style="color: var(--ui-muted);">
                                        Datos oficiales generados
                                    </p>

                                    <h4 class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                        Registro que será guardado
                                    </h4>

                                    <p class="mt-1 text-sm leading-6" style="color: var(--ui-muted);">
                                        Estos campos se autocompletan desde la interpretación. Se muestran para revisión
                                        institucional.
                                    </p>
                                </div>

                                <div class="grid gap-5 p-5 md:grid-cols-2">
                                    {{-- NOMBRE OFICIAL --}}
                                    <div>
                                        <label class="ui-label">
                                            Nombre oficial <span class="text-red-500">*</span>
                                        </label>

                                        <input type="text" wire:model="form.nom_cur" data-field="nombreCurso"
                                            maxlength="120" readonly class="ui-input bg-slate-100/70 dark:bg-slate-800/60"
                                            placeholder="Se generará automáticamente">

                                        <p x-show="validacion.errores.nombreCurso" x-cloak
                                            x-text="validacion.errores.nombreCurso" class="ui-error"></p>

                                        @error('form.nom_cur')
                                            <p class="ui-error">{{ $message }}</p>
                                        @enderror

                                        <p class="ui-help">
                                            El nombre oficial no se escribe manualmente para evitar duplicidad o variantes.
                                        </p>
                                    </div>

                                    {{-- ORDEN ACADÉMICO --}}
                                    <div>
                                        <label class="ui-label">
                                            Orden académico <span class="text-red-500">*</span>
                                        </label>

                                        <input type="number" wire:model="form.ord_cur" data-field="ordenCurso" min="1"
                                            max="20" readonly class="ui-input bg-slate-100/70 dark:bg-slate-800/60"
                                            placeholder="Automático">

                                        <p x-show="validacion.errores.ordenCurso" x-cloak
                                            x-text="validacion.errores.ordenCurso" class="ui-error"></p>

                                        @error('form.ord_cur')
                                            <p class="ui-error">{{ $message }}</p>
                                        @enderror

                                        <p class="ui-help">
                                            Determina el orden institucional del curso dentro del nivel secundario.
                                        </p>
                                    </div>

                                    {{-- NIVEL ACADÉMICO --}}
                                    <div>
                                        <label class="ui-label">
                                            Nivel académico <span class="text-red-500">*</span>
                                        </label>

                                        <div class="relative curso-select-wrap">
                                            <select wire:model="form.niv_cur" data-field="nivelCurso"
                                                class="ui-select pr-10 bg-slate-100/70 dark:bg-slate-800/60" disabled>
                                                <option value="">Se generará automáticamente</option>
                                                @foreach ($nivelesDisponibles as $nivelItem)
                                                    <option value="{{ $nivelItem }}">
                                                        {{ $nivelItem }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Campo oculto para asegurar envío/validación visual porque el select disabled no
                                        cambia --}}
                                        <input type="hidden" wire:model="form.niv_cur" data-field="nivelCurso">

                                        <p x-show="validacion.errores.nivelCurso" x-cloak
                                            x-text="validacion.errores.nivelCurso" class="ui-error"></p>

                                        @error('form.niv_cur')
                                            <p class="ui-error">{{ $message }}</p>
                                        @enderror

                                        <p class="ui-help">
                                            1ro a 3ro: Técnica Tecnológica General. 4to a 6to: Especialización Técnica.
                                        </p>
                                    </div>

                                    {{-- ESTADO --}}
                                    <div>
                                        <label class="ui-label">
                                            Estado <span class="text-red-500">*</span>
                                        </label>

                                        <div class="relative curso-select-wrap">
                                            <select wire:model="form.est_cur" data-field="estadoCurso"
                                                class="ui-select pr-10">
                                                <option value="ACTIVO">Activo</option>
                                                <option value="INACTIVO">Inactivo</option>
                                            </select>
                                        </div>

                                        <p x-show="validacion.errores.estadoCurso" x-cloak
                                            x-text="validacion.errores.estadoCurso" class="ui-error"></p>

                                        @error('form.est_cur')
                                            <p class="ui-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- DESCRIPCIÓN --}}
                                    <div class="md:col-span-2">
                                        <label class="ui-label">
                                            Descripción institucional
                                        </label>

                                        <textarea wire:model="form.des_cur" maxlength="255" rows="4" class="ui-textarea"
                                            placeholder="El sistema sugerirá una descripción institucional..."></textarea>

                                        @error('form.des_cur')
                                            <p class="ui-error">{{ $message }}</p>
                                        @enderror

                                        <p class="ui-help">
                                            La descripción se sugiere automáticamente, pero puede ajustarse con criterio
                                            institucional.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        {{-- COLUMNA DERECHA: VISTA PREVIA --}}
                        <aside class="space-y-5 xl:col-span-5">
                            {{-- FICHA PREVIA --}}
                            <section class="ui-card-soft overflow-hidden rounded-[1.75rem]">
                                <div class="border-b p-5" style="border-color: var(--ui-border);">
                                    <p class="text-xs font-black uppercase tracking-[0.16em]"
                                        style="color: var(--ui-muted);">
                                        Vista previa
                                    </p>

                                    <h4 class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                        Ficha institucional del curso
                                    </h4>

                                    <p class="mt-1 text-sm leading-6" style="color: var(--ui-muted);">
                                        Así se visualizará el curso dentro del catálogo académico base.
                                    </p>
                                </div>

                                <div class="p-5">
                                    <div class="relative overflow-hidden rounded-[1.75rem] border p-5"
                                        style="border-color: var(--ui-primary-border); background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-surface));">
                                        <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full opacity-40 blur-2xl"
                                            style="background: var(--ui-primary);"></div>

                                        <div class="relative">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="text-xs font-black uppercase tracking-[0.16em]"
                                                        style="color: var(--ui-primary);">
                                                        Curso oficial
                                                    </p>

                                                    <h5 class="mt-2 text-2xl font-black tracking-tight"
                                                        style="color: var(--ui-text);">
                                                        {{ $cursoInteligente['nombre'] ?: 'Curso no definido' }}
                                                    </h5>

                                                    <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                                                        {{ $cursoInteligente['nivel'] ?: 'Nivel académico pendiente de interpretación' }}
                                                    </p>
                                                </div>

                                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-xl font-black"
                                                    style="background: var(--ui-surface); color: var(--ui-primary); box-shadow: var(--ui-shadow-sm);">
                                                    {{ $cursoInteligente['orden'] ?: '—' }}
                                                </div>
                                            </div>

                                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                                <div class="rounded-2xl border p-3"
                                                    style="border-color: var(--ui-border); background: var(--ui-surface);">
                                                    <p class="text-xs" style="color: var(--ui-muted);">
                                                        Categoría
                                                    </p>
                                                    <p class="mt-1 text-sm font-black" style="color: var(--ui-text);">
                                                        {{ $cursoInteligente['categoria'] ?: 'Pendiente' }}
                                                    </p>
                                                </div>

                                                <div class="rounded-2xl border p-3"
                                                    style="border-color: var(--ui-border); background: var(--ui-surface);">
                                                    <p class="text-xs" style="color: var(--ui-muted);">
                                                        Estado inicial
                                                    </p>
                                                    <p class="mt-1 text-sm font-black" style="color: var(--ui-text);">
                                                        {{ $form['est_cur'] ?? 'ACTIVO' }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mt-4 rounded-2xl border p-4"
                                                style="border-color: var(--ui-border); background: var(--ui-surface);">
                                                <p class="text-xs font-bold uppercase tracking-[0.14em]"
                                                    style="color: var(--ui-muted);">
                                                    Descripción
                                                </p>

                                                <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                                                    {{ $form['des_cur'] ?: 'La descripción institucional aparecerá aquí cuando el curso sea interpretado.' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            {{-- RELACIONES ESPERADAS --}}
                            <section class="ui-card-soft rounded-[1.75rem] p-5">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-[0.16em]"
                                            style="color: var(--ui-muted);">
                                            Relación académica
                                        </p>

                                        <h4 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                                            Módulos relacionados
                                        </h4>
                                    </div>

                                    @if ($cursoInteligente['requiere_plan_especialidad'] ?? false)
                                        <span class="ui-badge-violet">
                                            Técnica
                                        </span>
                                    @else
                                        <span class="ui-badge-info">
                                            General
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-4 space-y-3">
                                    @forelse ($cursoInteligente['relaciones_esperadas'] ?? [] as $relacion)
                                        <div class="flex items-center gap-3 rounded-2xl border px-4 py-3"
                                            style="border-color: var(--ui-border); background: var(--ui-surface);">
                                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl"
                                                style="background: var(--ui-primary-soft); color: var(--ui-primary);">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M4.5 12.75 10.5 18.75 19.5 5.25" />
                                                </svg>
                                            </div>

                                            <p class="text-sm font-semibold" style="color: var(--ui-text);">
                                                {{ $relacion }}
                                            </p>
                                        </div>
                                    @empty
                                        <div class="ui-alert-warning">
                                            Aún no hay relaciones sugeridas. Escribe o selecciona un curso para generar la
                                            ficha.
                                        </div>
                                    @endforelse
                                </div>
                            </section>

                            {{-- VALIDACIÓN INSTITUCIONAL --}}
                            <section class="ui-card-soft rounded-[1.75rem] p-5">
                                <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                                    Control institucional
                                </p>

                                <h4 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                                    Validación previa
                                </h4>

                                <div class="mt-4 space-y-3">
                                    <div class="flex items-center justify-between gap-3 rounded-2xl border px-4 py-3"
                                        style="border-color: var(--ui-border); background: var(--ui-surface);">
                                        <span class="text-sm" style="color: var(--ui-muted);">
                                            Nombre oficial reconocido
                                        </span>

                                        @if ($cursoInteligente['valido'] ?? false)
                                            <span class="ui-badge-success">Correcto</span>
                                        @else
                                            <span class="ui-badge-warning">Pendiente</span>
                                        @endif
                                    </div>

                                    <div class="flex items-center justify-between gap-3 rounded-2xl border px-4 py-3"
                                        style="border-color: var(--ui-border); background: var(--ui-surface);">
                                        <span class="text-sm" style="color: var(--ui-muted);">
                                            Orden académico
                                        </span>

                                        @if (!empty($cursoInteligente['orden']))
                                            <span class="ui-badge-success">
                                                {{ $cursoInteligente['orden'] }}
                                            </span>
                                        @else
                                            <span class="ui-badge-warning">Pendiente</span>
                                        @endif
                                    </div>

                                    <div class="flex items-center justify-between gap-3 rounded-2xl border px-4 py-3"
                                        style="border-color: var(--ui-border); background: var(--ui-surface);">
                                        <span class="text-sm" style="color: var(--ui-muted);">
                                            Duplicidad
                                        </span>

                                        @if ($cursoInteligente['duplicado'] ?? false)
                                            <span class="ui-badge-danger">Existe</span>
                                        @elseif ($cursoInteligente['valido'] ?? false)
                                            <span class="ui-badge-success">Sin duplicidad</span>
                                        @else
                                            <span class="ui-badge-warning">Pendiente</span>
                                        @endif
                                    </div>

                                    <div class="flex items-center justify-between gap-3 rounded-2xl border px-4 py-3"
                                        style="border-color: var(--ui-border); background: var(--ui-surface);">
                                        <span class="text-sm" style="color: var(--ui-muted);">
                                            Especialidad técnica
                                        </span>

                                        @if ($cursoInteligente['requiere_plan_especialidad'] ?? false)
                                            <span class="ui-badge-violet">Requerida</span>
                                        @else
                                            <span class="ui-badge-info">No inicial</span>
                                        @endif
                                    </div>
                                </div>
                            </section>
                        </aside>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="ui-modal-footer flex flex-col gap-3 border-t sm:flex-row sm:items-center sm:justify-end"
                    style="border-color: var(--ui-border);">
                    <p x-show="!puedeGuardar()" x-cloak class="text-sm font-medium sm:mr-auto"
                        style="color: var(--ui-danger);">
                        Completa una interpretación válida y sin duplicidad antes de guardar.
                    </p>

                    <p x-show="puedeGuardar()" x-cloak class="text-sm font-medium sm:mr-auto"
                        style="color: var(--ui-primary);">
                        Curso listo para registrarse en el catálogo académico.
                    </p>

                    <button type="button" wire:click="cerrarModalCrear" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button" wire:click="guardarCurso" wire:loading.attr="disabled" wire:target="guardarCurso"
                        :disabled="!puedeGuardar()"
                        :class="puedeGuardar()
                                ? 'ui-btn-primary'
                                : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none dark:bg-slate-700 dark:text-slate-400'"
                        class="rounded-2xl px-5 py-3 text-sm font-semibold transition">
                        <span wire:loading.remove wire:target="guardarCurso" class="inline-flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75 10.5 18.75 19.5 5.25" />
                            </svg>
                            Guardar curso
                        </span>

                        <span wire:loading wire:target="guardarCurso" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z">
                                </path>
                            </svg>
                            Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL EDITAR CURSO INSTITUCIONAL --}}
    @if ($modalEditar)
        <div wire:key="modal-editar-curso-{{ $formEditar['cod_cur'] ?? 'sin-curso' }}"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6" x-data="{
                validacion: { valido: false, errores: {}, lista: [] },
                modoAvanzado: false,

                recalcular() {
                    if (typeof window.validarCursoForm === 'function') {
                        this.validacion = window.validarCursoForm(this.$root);
                        return;
                    }

                    this.validacion = {
                        valido: true,
                        errores: {},
                        lista: []
                    };
                },

                confirmarGuardado() {
                    if (!this.validacion.valido) {
                        return;
                    }

                    if (this.modoAvanzado) {
                        window.confirmarAccionCurso({
                            title: '¿Actualizar datos estructurales del curso?',
                            text: 'Modificar nombre, orden o nivel puede afectar reportes, planificación académica y horarios relacionados. Continúa solo si el cambio es institucionalmente correcto.',
                            icon: 'warning',
                            confirmButtonText: 'Sí, actualizar',
                            confirmButtonColor: '#d97706',
                            onConfirm: () => $wire.actualizarCurso()
                        });

                        return;
                    }

                    $wire.actualizarCurso();
                }
            }" x-init="$nextTick(() => recalcular())" x-on:input.debounce.150ms="recalcular()"
            x-on:change.debounce.150ms="recalcular()" x-on:keydown.escape.window="$wire.cerrarModalEditar()">
            {{-- BACKDROP --}}
            <div class="absolute inset-0 curso-backdrop-diffuse" wire:click="cerrarModalEditar"></div>

            {{-- MODAL --}}
            <div class="ui-modal curso-fade-in relative z-10 w-full max-w-6xl overflow-hidden" role="dialog"
                aria-modal="true" aria-label="Editar curso institucional">
                {{-- HEADER --}}
                <div class="curso-hero-bg border-b px-5 py-5 sm:px-6" style="border-color: var(--ui-border);">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 ring-1"
                                style="background: var(--ui-info-soft); color: var(--ui-info); --tw-ring-color: var(--ui-info-border);">
                                <span class="h-2 w-2 rounded-full" style="background: var(--ui-info);"></span>
                                <span class="text-xs font-black uppercase tracking-[0.18em]">
                                    Edición institucional
                                </span>
                            </div>

                            <h3 class="mt-3 text-2xl font-black tracking-tight md:text-3xl" style="color: var(--ui-text);">
                                Editar curso
                            </h3>

                            <p class="mt-2 max-w-4xl text-sm leading-6" style="color: var(--ui-muted);">
                                Actualiza los datos administrativos del curso con criterio institucional. Si el curso ya
                                tiene
                                planificación, especialidad, horarios o estudiantes relacionados, evita modificar su
                                estructura
                                principal salvo que sea estrictamente necesario.
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarModalEditar" class="ui-icon-btn shrink-0"
                            title="Cerrar modal">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="max-h-[74vh] overflow-y-auto px-5 py-6 sm:px-6 curso-mini-scroll">
                    <div class="grid gap-6 xl:grid-cols-12">

                        {{-- COLUMNA IZQUIERDA: FORMULARIO --}}
                        <section class="space-y-5 xl:col-span-7">
                            {{-- ALERTA PRINCIPAL --}}
                            <div class="ui-alert-warning">
                                <p class="font-black">
                                    Edición con impacto académico
                                </p>
                                <p class="mt-1 text-sm leading-6">
                                    Este curso puede estar relacionado con Plan de Asignatura, Plan de Especialidad,
                                    Horarios, inscripciones y reportes. Cambiar su nombre, orden o nivel puede alterar la
                                    lectura institucional del sistema.
                                </p>
                            </div>

                            {{-- VALIDACIÓN --}}
                            <div x-show="!validacion.valido" x-cloak class="ui-alert-danger">
                                <p class="font-black">
                                    Revisa los datos antes de actualizar
                                </p>

                                <ul class="mt-2 list-disc space-y-1 pl-5 text-xs leading-5">
                                    <template x-for="error in validacion.lista" :key="error">
                                        <li x-text="error"></li>
                                    </template>
                                </ul>
                            </div>

                            {{-- DATOS ESTRUCTURALES --}}
                            <div class="ui-card-soft overflow-hidden rounded-[1.75rem]">
                                <div class="border-b p-5" style="border-color: var(--ui-border);">
                                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-[0.16em]"
                                                style="color: var(--ui-muted);">
                                                Datos estructurales
                                            </p>

                                            <h4 class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                                Identificación oficial del curso
                                            </h4>

                                            <p class="mt-1 text-sm leading-6" style="color: var(--ui-muted);">
                                                Por seguridad, estos campos se muestran protegidos. Activa la edición
                                                avanzada
                                                solo si debes corregir un dato institucional.
                                            </p>
                                        </div>

                                        <button type="button" x-on:click="modoAvanzado = !modoAvanzado"
                                            class="rounded-2xl px-4 py-2 text-sm font-black transition"
                                            :style="modoAvanzado
                                                ? 'background: var(--ui-warning-soft); color: var(--ui-warning);'
                                                : 'background: var(--ui-surface); color: var(--ui-muted); border: 1px solid var(--ui-border);'">
                                            <span x-show="!modoAvanzado">Activar edición avanzada</span>
                                            <span x-show="modoAvanzado" x-cloak>Bloquear edición avanzada</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="grid gap-5 p-5 md:grid-cols-2">
                                    {{-- NOMBRE --}}
                                    <div class="md:col-span-2">
                                        <label class="ui-label">
                                            Nombre oficial del curso <span class="text-red-500">*</span>
                                        </label>

                                        <input type="text" wire:model="formEditar.nom_cur" data-field="nombreCurso"
                                            maxlength="120" autocomplete="off" class="ui-input" :readonly="!modoAvanzado"
                                            :class="!modoAvanzado ? 'bg-slate-100/70 dark:bg-slate-800/60' : ''"
                                            placeholder="Ej. 4to de Secundaria">

                                        <p x-show="validacion.errores.nombreCurso" x-cloak
                                            x-text="validacion.errores.nombreCurso" class="ui-error"></p>

                                        @error('formEditar.nom_cur')
                                            <p class="ui-error">{{ $message }}</p>
                                        @enderror

                                        <p class="ui-help">
                                            Nombre institucional usado en reportes, planificación y horarios.
                                        </p>
                                    </div>

                                    {{-- ORDEN --}}
                                    <div>
                                        <label class="ui-label">
                                            Orden académico <span class="text-red-500">*</span>
                                        </label>

                                        <input type="number" wire:model="formEditar.ord_cur" data-field="ordenCurso" min="1"
                                            max="20" autocomplete="off" class="ui-input" :readonly="!modoAvanzado"
                                            :class="!modoAvanzado ? 'bg-slate-100/70 dark:bg-slate-800/60' : ''"
                                            placeholder="Ej. 4">

                                        <p x-show="validacion.errores.ordenCurso" x-cloak
                                            x-text="validacion.errores.ordenCurso" class="ui-error"></p>

                                        @error('formEditar.ord_cur')
                                            <p class="ui-error">{{ $message }}</p>
                                        @enderror

                                        <p class="ui-help">
                                            Define la secuencia académica del curso.
                                        </p>
                                    </div>

                                    {{-- NIVEL --}}
                                    <div>
                                        <label class="ui-label">
                                            Nivel académico <span class="text-red-500">*</span>
                                        </label>

                                        <div class="relative curso-select-wrap">
                                            <select wire:model="formEditar.niv_cur" data-field="nivelCurso"
                                                class="ui-select pr-10" :disabled="!modoAvanzado"
                                                :class="!modoAvanzado ? 'bg-slate-100/70 dark:bg-slate-800/60' : ''">
                                                <option value="">Seleccionar nivel</option>
                                                @foreach ($nivelesDisponibles as $nivelItem)
                                                    <option value="{{ $nivelItem }}">
                                                        {{ $nivelItem }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Campo espejo para que la validación visual pueda leer valor aunque el select
                                        esté disabled --}}
                                        <input type="hidden" wire:model="formEditar.niv_cur" data-field="nivelCurso">

                                        <p x-show="validacion.errores.nivelCurso" x-cloak
                                            x-text="validacion.errores.nivelCurso" class="ui-error"></p>

                                        @error('formEditar.niv_cur')
                                            <p class="ui-error">{{ $message }}</p>
                                        @enderror

                                        <p class="ui-help">
                                            Cambiar el nivel puede afectar filtros y reportes académicos.
                                        </p>
                                    </div>

                                    {{-- MENSAJE MODO AVANZADO --}}
                                    <div x-show="modoAvanzado" x-cloak class="md:col-span-2">
                                        <div class="ui-alert-warning">
                                            <p class="font-black">
                                                Edición avanzada activada
                                            </p>
                                            <p class="mt-1 text-sm leading-6">
                                                Estás editando campos estructurales del catálogo de cursos. Confirma que el
                                                cambio
                                                no duplicará información ni afectará horarios, planes o reportes
                                                relacionados.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- DATOS ADMINISTRATIVOS --}}
                            <div class="ui-card-soft overflow-hidden rounded-[1.75rem]">
                                <div class="border-b p-5" style="border-color: var(--ui-border);">
                                    <p class="text-xs font-black uppercase tracking-[0.16em]"
                                        style="color: var(--ui-muted);">
                                        Datos administrativos
                                    </p>

                                    <h4 class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                        Estado y descripción institucional
                                    </h4>

                                    <p class="mt-1 text-sm leading-6" style="color: var(--ui-muted);">
                                        Estos campos pueden actualizarse con menor riesgo porque no modifican la identidad
                                        académica base del curso.
                                    </p>
                                </div>

                                <div class="grid gap-5 p-5 md:grid-cols-2">
                                    {{-- ESTADO --}}
                                    <div>
                                        <label class="ui-label">
                                            Estado <span class="text-red-500">*</span>
                                        </label>

                                        <div class="relative curso-select-wrap">
                                            <select wire:model="formEditar.est_cur" data-field="estadoCurso"
                                                class="ui-select pr-10">
                                                <option value="ACTIVO">Activo</option>
                                                <option value="INACTIVO">Inactivo</option>
                                            </select>
                                        </div>

                                        <p x-show="validacion.errores.estadoCurso" x-cloak
                                            x-text="validacion.errores.estadoCurso" class="ui-error"></p>

                                        @error('formEditar.est_cur')
                                            <p class="ui-error">{{ $message }}</p>
                                        @enderror

                                        <p class="ui-help">
                                            Inactivar no elimina información histórica ni relaciones académicas.
                                        </p>
                                    </div>

                                    {{-- CÓDIGO INTERNO SOLO REFERENCIAL --}}
                                    <div>
                                        <label class="ui-label">
                                            Referencia interna
                                        </label>

                                        <div class="flex min-h-[3rem] items-center rounded-2xl border px-4 text-sm font-black"
                                            style="border-color: var(--ui-border); background: var(--ui-surface-soft); color: var(--ui-muted);">
                                            {{ $formEditar['cod_cur'] ?: 'Sin código cargado' }}
                                        </div>

                                        <p class="ui-help">
                                            Este código no se edita desde la interfaz.
                                        </p>
                                    </div>

                                    {{-- DESCRIPCIÓN --}}
                                    <div class="md:col-span-2">
                                        <div class="flex items-center justify-between gap-3">
                                            <label class="ui-label">
                                                Descripción institucional
                                            </label>

                                            <span class="text-xs" style="color: var(--ui-muted);">
                                                Máx. 255 caracteres
                                            </span>
                                        </div>

                                        <textarea wire:model="formEditar.des_cur" maxlength="255" rows="5"
                                            class="ui-textarea"
                                            placeholder="Describe el enfoque académico del curso..."></textarea>

                                        @error('formEditar.des_cur')
                                            <p class="ui-error">{{ $message }}</p>
                                        @enderror

                                        <p class="ui-help">
                                            Esta descripción ayuda a contextualizar el curso en fichas, reportes y paneles
                                            académicos.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        {{-- COLUMNA DERECHA: VISTA PREVIA Y CONTROL --}}
                        <aside class="space-y-5 xl:col-span-5">
                            {{-- VISTA PREVIA --}}
                            <section class="ui-card-soft overflow-hidden rounded-[1.75rem]">
                                <div class="border-b p-5" style="border-color: var(--ui-border);">
                                    <p class="text-xs font-black uppercase tracking-[0.16em]"
                                        style="color: var(--ui-muted);">
                                        Vista previa
                                    </p>

                                    <h4 class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                        Ficha actualizada del curso
                                    </h4>

                                    <p class="mt-1 text-sm leading-6" style="color: var(--ui-muted);">
                                        Revisa cómo se verá el curso después de guardar los cambios.
                                    </p>
                                </div>

                                <div class="p-5">
                                    <div class="relative overflow-hidden rounded-[1.75rem] border p-5"
                                        style="border-color: var(--ui-info-border); background: linear-gradient(135deg, var(--ui-info-soft), var(--ui-surface));">
                                        <div class="absolute -right-12 -top-12 h-32 w-32 rounded-full opacity-40 blur-2xl"
                                            style="background: var(--ui-info);"></div>

                                        <div class="relative">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0">
                                                    <p class="text-xs font-black uppercase tracking-[0.16em]"
                                                        style="color: var(--ui-info);">
                                                        Curso oficial
                                                    </p>

                                                    <h5 class="mt-2 line-clamp-2 text-2xl font-black tracking-tight"
                                                        style="color: var(--ui-text);">
                                                        {{ $formEditar['nom_cur'] ?: 'Curso no definido' }}
                                                    </h5>

                                                    <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                                                        {{ $formEditar['niv_cur'] ?: 'Nivel académico pendiente' }}
                                                    </p>
                                                </div>

                                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-xl font-black"
                                                    style="background: var(--ui-surface); color: var(--ui-info); box-shadow: var(--ui-shadow-sm);">
                                                    {{ $formEditar['ord_cur'] ?: '—' }}
                                                </div>
                                            </div>

                                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                                <div class="rounded-2xl border p-3"
                                                    style="border-color: var(--ui-border); background: var(--ui-surface);">
                                                    <p class="text-xs" style="color: var(--ui-muted);">
                                                        Estado
                                                    </p>

                                                    <p class="mt-1 text-sm font-black" style="color: var(--ui-text);">
                                                        {{ $formEditar['est_cur'] ?: 'ACTIVO' }}
                                                    </p>
                                                </div>

                                                <div class="rounded-2xl border p-3"
                                                    style="border-color: var(--ui-border); background: var(--ui-surface);">
                                                    <p class="text-xs" style="color: var(--ui-muted);">
                                                        Código
                                                    </p>

                                                    <p class="mt-1 text-sm font-black" style="color: var(--ui-text);">
                                                        {{ $formEditar['cod_cur'] ?: '—' }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="mt-4 rounded-2xl border p-4"
                                                style="border-color: var(--ui-border); background: var(--ui-surface);">
                                                <p class="text-xs font-bold uppercase tracking-[0.14em]"
                                                    style="color: var(--ui-muted);">
                                                    Descripción
                                                </p>

                                                <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                                                    {{ $formEditar['des_cur'] ?: 'Sin descripción institucional registrada.' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            {{-- CONTROL DE IMPACTO --}}
                            <section class="ui-card-soft rounded-[1.75rem] p-5">
                                <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                                    Control de impacto
                                </p>

                                <h4 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                                    Antes de guardar
                                </h4>

                                <div class="mt-4 space-y-3">
                                    <div class="flex items-center justify-between gap-3 rounded-2xl border px-4 py-3"
                                        style="border-color: var(--ui-border); background: var(--ui-surface);">
                                        <span class="text-sm" style="color: var(--ui-muted);">
                                            Nombre institucional
                                        </span>

                                        <template x-if="validacion.errores.nombreCurso">
                                            <span class="ui-badge-danger">Revisar</span>
                                        </template>

                                        <template x-if="!validacion.errores.nombreCurso">
                                            <span class="ui-badge-success">Correcto</span>
                                        </template>
                                    </div>

                                    <div class="flex items-center justify-between gap-3 rounded-2xl border px-4 py-3"
                                        style="border-color: var(--ui-border); background: var(--ui-surface);">
                                        <span class="text-sm" style="color: var(--ui-muted);">
                                            Orden académico
                                        </span>

                                        <template x-if="validacion.errores.ordenCurso">
                                            <span class="ui-badge-danger">Revisar</span>
                                        </template>

                                        <template x-if="!validacion.errores.ordenCurso">
                                            <span class="ui-badge-success">Correcto</span>
                                        </template>
                                    </div>

                                    <div class="flex items-center justify-between gap-3 rounded-2xl border px-4 py-3"
                                        style="border-color: var(--ui-border); background: var(--ui-surface);">
                                        <span class="text-sm" style="color: var(--ui-muted);">
                                            Nivel académico
                                        </span>

                                        <template x-if="validacion.errores.nivelCurso">
                                            <span class="ui-badge-danger">Revisar</span>
                                        </template>

                                        <template x-if="!validacion.errores.nivelCurso">
                                            <span class="ui-badge-success">Correcto</span>
                                        </template>
                                    </div>

                                    <div class="flex items-center justify-between gap-3 rounded-2xl border px-4 py-3"
                                        style="border-color: var(--ui-border); background: var(--ui-surface);">
                                        <span class="text-sm" style="color: var(--ui-muted);">
                                            Estado del curso
                                        </span>

                                        <template x-if="validacion.errores.estadoCurso">
                                            <span class="ui-badge-danger">Revisar</span>
                                        </template>

                                        <template x-if="!validacion.errores.estadoCurso">
                                            <span class="ui-badge-success">Correcto</span>
                                        </template>
                                    </div>
                                </div>
                            </section>

                            {{-- REGLAS --}}
                            <section class="ui-card-soft rounded-[1.75rem] p-5">
                                <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                                    Reglas de edición
                                </p>

                                <div class="mt-4 space-y-3">
                                    <div class="flex gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl"
                                            style="background: var(--ui-primary-soft); color: var(--ui-primary);">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4.5 12.75 10.5 18.75 19.5 5.25" />
                                            </svg>
                                        </div>

                                        <p class="text-sm leading-6" style="color: var(--ui-muted);">
                                            Cambiar descripción o estado es una edición administrativa normal.
                                        </p>
                                    </div>

                                    <div class="flex gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl"
                                            style="background: var(--ui-warning-soft); color: var(--ui-warning);">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 9v3.75m0 3.75h.008v.008H12V16.5Z" />
                                            </svg>
                                        </div>

                                        <p class="text-sm leading-6" style="color: var(--ui-muted);">
                                            Cambiar nombre, orden o nivel debe considerarse edición estructural.
                                        </p>
                                    </div>

                                    <div class="flex gap-3">
                                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl"
                                            style="background: var(--ui-info-soft); color: var(--ui-info);">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                            </svg>
                                        </div>

                                        <p class="text-sm leading-6" style="color: var(--ui-muted);">
                                            Toda actualización queda registrada en bitácora para trazabilidad institucional.
                                        </p>
                                    </div>
                                </div>
                            </section>
                        </aside>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="ui-modal-footer flex flex-col gap-3 border-t sm:flex-row sm:items-center sm:justify-end"
                    style="border-color: var(--ui-border);">
                    <p x-show="!validacion.valido" x-cloak class="text-sm font-medium sm:mr-auto"
                        style="color: var(--ui-danger);">
                        Corrige los campos marcados antes de guardar.
                    </p>

                    <p x-show="validacion.valido" x-cloak class="text-sm font-medium sm:mr-auto"
                        style="color: var(--ui-primary);">
                        Datos listos para actualizar.
                    </p>

                    <button type="button" wire:click="cerrarModalEditar" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button" x-on:click="confirmarGuardado()" wire:loading.attr="disabled"
                        wire:target="actualizarCurso" :disabled="!validacion.valido"
                        :class="validacion.valido
                            ? 'ui-btn-primary'
                            : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none dark:bg-slate-700 dark:text-slate-400'"
                        class="rounded-2xl px-5 py-3 text-sm font-semibold transition">
                        <span wire:loading.remove wire:target="actualizarCurso" class="inline-flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75 10.5 18.75 19.5 5.25" />
                            </svg>
                            Guardar cambios
                        </span>

                        <span wire:loading wire:target="actualizarCurso" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z">
                                </path>
                            </svg>
                            Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- DRAWER DETALLE DEL CURSO --}}
    @if ($modalDetalle && $cursoDetalle)
        <div wire:key="drawer-detalle-curso-{{ $cursoDetalle['cod_cur'] ?? 'sin-curso' }}"
            class="fixed inset-0 z-50 flex items-stretch justify-end" x-data="{
                                        tabDetalle: tabDetalle || 'resumen',
                                        vistaHorario: @entangle('horarioVista').live,
                                        imprimirHorario() {
                                            const contenido = document.getElementById('area-horario-institucional');
                                            if (!contenido) {
                                                window.print();
                                                return;
                                            }

                                            window.print();
                                        }
                                    }" x-on:keydown.escape.window="$wire.cerrarModalDetalle()">
            {{-- BACKDROP --}}
            <div class="absolute inset-0 curso-backdrop-diffuse" wire:click="cerrarModalDetalle"></div>

            {{-- DRAWER --}}
            <aside
                class="ui-modal curso-drawer-in relative z-10 h-full w-full max-w-[98rem] overflow-hidden rounded-none border-y-0 border-r-0"
                role="dialog" aria-modal="true" aria-label="Detalle institucional del curso">
                <div class="flex h-full flex-col">

                    {{-- HEADER DEL DRAWER --}}
                    <header class="curso-hero-bg border-b px-5 py-5 sm:px-6 lg:px-7"
                        style="border-color: var(--ui-border);">
                        <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="curso-pill">
                                        Detalle institucional
                                    </span>

                                    @if (($cursoDetalle['est_cur'] ?? 'INACTIVO') === 'ACTIVO')
                                        <span class="ui-badge-success">
                                            <span class="h-2 w-2 rounded-full" style="background: var(--ui-primary);"></span>
                                            Activo
                                        </span>
                                    @else
                                        <span class="ui-badge-danger">
                                            <span class="h-2 w-2 rounded-full" style="background: var(--ui-danger);"></span>
                                            Inactivo
                                        </span>
                                    @endif

                                    <span class="ui-badge-info">
                                        {{ $cursoDetalle['niv_cur'] ?? 'Nivel no definido' }}
                                    </span>

                                    <span class="curso-pill">
                                        Orden {{ $cursoDetalle['ord_cur'] ?? '—' }}
                                    </span>
                                </div>

                                <h3 class="mt-3 max-w-5xl truncate text-2xl font-black tracking-tight md:text-3xl"
                                    style="color: var(--ui-text);">
                                    {{ $cursoDetalle['nom_cur'] ?? 'Curso seleccionado' }}
                                </h3>

                                <p class="mt-2 max-w-5xl text-sm leading-6" style="color: var(--ui-muted);">
                                    {{ $cursoDetalle['des_cur'] ?: 'Sin descripción institucional registrada. Este curso funciona como catálogo académico base; su planificación curricular, técnica y horaria se consulta desde módulos relacionados.' }}
                                </p>
                            </div>

                            <div class="flex flex-wrap items-center gap-2">
                                <button type="button" wire:click="abrirModalEditar(@js($cursoDetalle['cod_cur'] ?? null))"
                                    wire:loading.attr="disabled" wire:target="abrirModalEditar"
                                    class="ui-btn-secondary disabled:cursor-wait disabled:opacity-60">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m16.862 4.487 1.687-1.688a2.25 2.25 0 1 1 3.182 3.182L10.582 17.13a4.5 4.5 0 0 1-1.897 1.13L6 19l.74-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487Z" />
                                    </svg>
                                    Editar curso
                                </button>

                                <button type="button" x-on:click="tabDetalle = 'horarios'" class="ui-btn-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18M4.5 6.75h15A1.5 1.5 0 0 1 21 8.25v10.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A1.5 1.5 0 0 1 4.5 6.75Z" />
                                    </svg>
                                    Ver horario
                                </button>

                                <button type="button" wire:click="cerrarModalDetalle" class="ui-icon-btn"
                                    title="Cerrar detalle">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        {{-- TABS --}}
                        <nav class="mt-5 flex flex-wrap gap-2" aria-label="Secciones del detalle del curso">
                            <button type="button" x-on:click="tabDetalle = 'resumen'"
                                :class="tabDetalle === 'resumen' ? 'curso-tab-active' : ''"
                                class="curso-tab rounded-2xl px-4 py-2 text-sm font-bold">
                                Resumen
                            </button>

                            <button type="button" x-on:click="tabDetalle = 'plan_asignatura'"
                                :class="tabDetalle === 'plan_asignatura' ? 'curso-tab-active' : ''"
                                class="curso-tab rounded-2xl px-4 py-2 text-sm font-bold">
                                Plan Asignatura
                            </button>

                            <button type="button" x-on:click="tabDetalle = 'plan_especialidad'"
                                :class="tabDetalle === 'plan_especialidad' ? 'curso-tab-active' : ''"
                                class="curso-tab rounded-2xl px-4 py-2 text-sm font-bold">
                                Plan Especialidad
                            </button>

                            <button type="button" x-on:click="tabDetalle = 'horarios'"
                                :class="tabDetalle === 'horarios' ? 'curso-tab-active' : ''"
                                class="curso-tab rounded-2xl px-4 py-2 text-sm font-bold">
                                Vista de horarios
                            </button>
                        </nav>
                    </header>

                    {{-- CUERPO --}}
                    <main class="flex-1 overflow-y-auto p-5 sm:p-6 lg:p-7 curso-mini-scroll">

                        {{-- TAB RESUMEN --}}
                        <section x-show="tabDetalle === 'resumen'" x-cloak class="space-y-6 curso-fade-in">
                            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                                <article class="ui-card-soft p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.14em]"
                                        style="color: var(--ui-muted);">
                                        Orden académico
                                    </p>
                                    <p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                        {{ $cursoDetalle['ord_cur'] ?? '—' }}
                                    </p>
                                    <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                                        Secuencia institucional
                                    </p>
                                </article>

                                <article class="ui-card-soft p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.14em]"
                                        style="color: var(--ui-muted);">
                                        Materias
                                    </p>
                                    <p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                        {{ $cursoDetalle['plan_asignatura_count'] ?? 0 }}
                                    </p>
                                    <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                                        Plan curricular
                                    </p>
                                </article>

                                <article class="ui-card-soft p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.14em]"
                                        style="color: var(--ui-muted);">
                                        Especialidades
                                    </p>
                                    <p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                        {{ $cursoDetalle['plan_especialidad_count'] ?? 0 }}
                                    </p>
                                    <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                                        Formación técnica
                                    </p>
                                </article>

                                <article class="ui-card-soft p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.14em]"
                                        style="color: var(--ui-muted);">
                                        Horarios
                                    </p>
                                    <p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                        {{ $cursoDetalle['horarios_count'] ?? 0 }}
                                    </p>
                                    <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                                        Cabeceras creadas
                                    </p>
                                </article>

                                <article class="ui-card-soft p-4">
                                    <p class="text-xs font-bold uppercase tracking-[0.14em]"
                                        style="color: var(--ui-muted);">
                                        Estudiantes
                                    </p>
                                    <p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                        {{ $cursoDetalle['inscritos_count'] ?? 0 }}
                                    </p>
                                    <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                                        Inscritos en gestión
                                    </p>
                                </article>
                            </div>

                            <div class="grid gap-5 xl:grid-cols-12">
                                <section class="ui-card-soft p-5 xl:col-span-7">
                                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                        <div>
                                            <h4 class="text-lg font-black" style="color: var(--ui-text);">
                                                Estado académico del curso
                                            </h4>

                                            <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                                                Este panel resume la cobertura curricular, técnica y organizativa del curso
                                                en la
                                                gestión seleccionada. La cobertura del horario se calcula con los bloques
                                                activos
                                                del turno elegido.
                                            </p>
                                        </div>

                                        <span class="rounded-full px-3 py-1 text-xs font-black ring-1"
                                            style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                                            {{ $cursoDetalle['porcentaje_horario'] ?? 0 }}% cobertura
                                        </span>
                                    </div>

                                    <div class="mt-5 space-y-4">
                                        <div>
                                            <div class="flex items-center justify-between gap-3 text-sm">
                                                <span style="color: var(--ui-muted);">Cobertura de horario</span>
                                                <strong style="color: var(--ui-text);">
                                                    {{ $cursoDetalle['porcentaje_horario'] ?? 0 }}%
                                                </strong>
                                            </div>

                                            <div class="mt-2 h-2 overflow-hidden rounded-full"
                                                style="background: var(--ui-surface-muted);">
                                                <div class="h-full rounded-full transition-all duration-500"
                                                    style="width: {{ $cursoDetalle['porcentaje_horario'] ?? 0 }}%; background: var(--ui-primary);">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid gap-3 md:grid-cols-2">
                                            <div class="rounded-2xl border p-4"
                                                style="border-color: var(--ui-border); background: var(--ui-surface);">
                                                <p class="text-xs" style="color: var(--ui-muted);">Bloques asignados</p>
                                                <p class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                                    {{ $cursoDetalle['bloques_asignados'] ?? 0 }}
                                                </p>
                                            </div>

                                            <div class="rounded-2xl border p-4"
                                                style="border-color: var(--ui-border); background: var(--ui-surface);">
                                                <p class="text-xs" style="color: var(--ui-muted);">Bloques libres</p>
                                                <p class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                                    {{ $cursoDetalle['bloques_libres'] ?? 0 }}
                                                </p>
                                            </div>

                                            <div class="rounded-2xl border p-4"
                                                style="border-color: var(--ui-border); background: var(--ui-surface);">
                                                <p class="text-xs" style="color: var(--ui-muted);">Materias pendientes</p>
                                                <p class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                                    {{ $cursoDetalle['materias_pendientes'] ?? 0 }}
                                                </p>
                                            </div>

                                            <div class="rounded-2xl border p-4"
                                                style="border-color: var(--ui-border); background: var(--ui-surface);">
                                                <p class="text-xs" style="color: var(--ui-muted);">Cruces docentes</p>
                                                <p class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                                    {{ $cursoDetalle['cruces_docentes'] ?? 0 }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="ui-card-soft p-5 xl:col-span-5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h4 class="text-lg font-black" style="color: var(--ui-text);">
                                                Horarios relacionados
                                            </h4>
                                            <p class="mt-1 text-sm" style="color: var(--ui-muted);">
                                                Cabeceras creadas por gestión, paralelo y turno.
                                            </p>
                                        </div>

                                        <button type="button" x-on:click="tabDetalle = 'horarios'" class="ui-icon-btn"
                                            title="Ver matriz">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18M4.5 6.75h15A1.5 1.5 0 0 1 21 8.25v10.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A1.5 1.5 0 0 1 4.5 6.75Z" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="mt-4 space-y-3">
                                        @forelse ($cursoDetalle['horarios_relacionados'] ?? [] as $horarioRelacionado)
                                            <div class="rounded-2xl border p-4 transition hover:-translate-y-0.5"
                                                style="border-color: var(--ui-border); background: var(--ui-surface); box-shadow: var(--ui-shadow-sm);">
                                                <div class="flex items-center justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <p class="truncate text-sm font-black" style="color: var(--ui-text);">
                                                            {{ $horarioRelacionado->paralelo ?? 'Paralelo' }}
                                                            -
                                                            {{ $horarioRelacionado->turno ?? 'Turno' }}
                                                        </p>
                                                        <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                                                            {{ $horarioRelacionado->total_bloques ?? 0 }} clases registradas
                                                        </p>
                                                    </div>

                                                    <span class="ui-badge-success">Configurado</span>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="ui-alert-warning">
                                                Este curso todavía no tiene una cabecera de horario creada. La cabecera se puede
                                                crear automáticamente al registrar la primera clase.
                                            </div>
                                        @endforelse
                                    </div>
                                </section>
                            </div>
                        </section>

                        {{-- TAB PLAN ASIGNATURA --}}
                        <section x-show="tabDetalle === 'plan_asignatura'" x-cloak class="space-y-5 curso-fade-in">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <h4 class="text-xl font-black" style="color: var(--ui-text);">
                                        Planificación curricular
                                    </h4>
                                    <p class="mt-1 text-sm" style="color: var(--ui-muted);">
                                        Materias vinculadas desde Plan de Asignatura. Esta sección es informativa; la
                                        edición formal
                                        debe realizarse desde el módulo correspondiente.
                                    </p>
                                </div>

                                <button type="button" wire:click="irAPlanAsignatura(@js($cursoDetalle['cod_cur'] ?? null))"
                                    class="ui-btn-secondary">
                                    Abrir Plan Asignatura
                                </button>
                            </div>

                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                @forelse ($cursoDetalle['materias'] ?? [] as $materia)
                                    <article class="rounded-[1.5rem] border p-4 transition hover:-translate-y-0.5"
                                        style="border-color: var(--ui-info-border); background: var(--ui-surface); box-shadow: var(--ui-shadow-sm);">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-black" style="color: var(--ui-text);">
                                                    {{ $materia->nombre ?? 'Materia registrada' }}
                                                </p>
                                                <p class="mt-1 truncate text-xs" style="color: var(--ui-muted);">
                                                    {{ trim($materia->docente ?? '') ?: 'Docente asignado' }}
                                                </p>
                                            </div>

                                            <span class="ui-badge-info">
                                                {{ $materia->hor_pas ?? 0 }} h
                                            </span>
                                        </div>
                                    </article>
                                @empty
                                    <div class="ui-alert-warning md:col-span-2 xl:col-span-3">
                                        Este curso aún no tiene materias planificadas para la gestión seleccionada.
                                    </div>
                                @endforelse
                            </div>
                        </section>

                        {{-- TAB PLAN ESPECIALIDAD --}}
                        <section x-show="tabDetalle === 'plan_especialidad'" x-cloak class="space-y-5 curso-fade-in">
                            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <h4 class="text-xl font-black" style="color: var(--ui-text);">
                                        Planificación técnica
                                    </h4>
                                    <p class="mt-1 text-sm" style="color: var(--ui-muted);">
                                        Especialidades vinculadas desde Plan de Especialidad. Para cursos de 4to a 6to, esta
                                        sección ayuda a verificar la organización técnica por turno.
                                    </p>
                                </div>

                                <button type="button"
                                    wire:click="irAPlanEspecialidad(@js($cursoDetalle['cod_cur'] ?? null))"
                                    class="ui-btn-secondary">
                                    Abrir Plan Especialidad
                                </button>
                            </div>

                            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                                @forelse ($cursoDetalle['especialidades'] ?? [] as $especialidad)
                                    <article class="rounded-[1.5rem] border p-4 transition hover:-translate-y-0.5"
                                        style="border-color: var(--ui-violet-border); background: var(--ui-surface); box-shadow: var(--ui-shadow-sm);">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-black" style="color: var(--ui-text);">
                                                    {{ $especialidad->nombre ?? 'Especialidad técnica' }}
                                                </p>
                                                <p class="mt-1 truncate text-xs" style="color: var(--ui-muted);">
                                                    {{ trim($especialidad->docente ?? '') ?: 'Docente asignado' }}
                                                </p>
                                            </div>

                                            <span class="ui-badge-violet">Técnica</span>
                                        </div>
                                    </article>
                                @empty
                                    <div class="ui-alert-warning md:col-span-2 xl:col-span-3">
                                        Este curso aún no tiene especialidad técnica registrada para la gestión seleccionada.
                                    </div>
                                @endforelse
                            </div>
                        </section>

                        {{-- TAB HORARIOS --}}
                        <section x-show="tabDetalle === 'horarios'" x-cloak class="space-y-5 curso-fade-in">
                            @php
                                $visor = $cursoDetalle['visor_horario'] ?? [];
                                $indicadores = $visor['indicadores'] ?? [];
                                $matriz = $visor['matriz'] ?? [];
                                $dias = $visor['dias'] ?? ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES'];
                                $contextoHorario = $visor['contexto'] ?? [];
                                $totalCeldas = (int) ($indicadores['total'] ?? 0);
                                $asignados = (int) ($indicadores['asignados'] ?? 0);
                                $libres = (int) ($indicadores['libres'] ?? 0);
                                $porcentaje = (int) ($indicadores['porcentaje'] ?? 0);
                            @endphp

                            {{-- PANEL DE CONTROL DEL HORARIO --}}
                            <div class="ui-card-soft overflow-hidden rounded-[2rem]">
                                <div class="curso-panel-glow border-b p-5" style="border-color: var(--ui-border);">
                                    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-[0.16em]"
                                                style="color: var(--ui-muted);">
                                                Vista institucional de horarios
                                            </p>

                                            <h4 class="mt-1 text-2xl font-black" style="color: var(--ui-text);">
                                                {{ $cursoDetalle['nom_cur'] ?? 'Curso seleccionado' }}
                                            </h4>

                                            <p class="mt-2 max-w-4xl text-sm leading-6" style="color: var(--ui-muted);">
                                                Consulta la matriz semanal por gestión, paralelo y turno. Cada celda libre
                                                permite
                                                crear una clase; cada celda ocupada representa un registro real de
                                                <strong>horario_detalle</strong>.
                                            </p>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-2">
                                            <button type="button" x-on:click="imprimirHorario()" class="ui-btn-secondary">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6.75 7.5V3.75h10.5V7.5M6.75 17.25H5.25A2.25 2.25 0 0 1 3 15V9.75A2.25 2.25 0 0 1 5.25 7.5h13.5A2.25 2.25 0 0 1 21 9.75V15a2.25 2.25 0 0 1-2.25 2.25h-1.5M6.75 14.25h10.5v6H6.75v-6Z" />
                                                </svg>
                                                Imprimir
                                            </button>

                                            <button type="button" wire:click="limpiarFiltrosHorario"
                                                class="ui-btn-secondary">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M20.985 4.356v4.992m0 0h-4.992m4.992 0-3.181-3.183a8.25 8.25 0 0 0-13.803 3.7" />
                                                </svg>
                                                Restablecer
                                            </button>

                                            <button type="button"
                                                wire:click="irAHorarios(@js($cursoDetalle['cod_cur'] ?? null))"
                                                class="ui-btn-primary">
                                                Abrir módulo horarios
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                {{-- SELECTORES --}}
                                <div class="p-5">
                                    <div class="grid gap-4 lg:grid-cols-4">
                                        <div>
                                            <label class="ui-label text-xs uppercase tracking-[0.14em]">Gestión
                                                académica</label>
                                            <div class="relative curso-select-wrap">
                                                <select wire:model.live="horarioGestion" class="ui-select pr-10">
                                                    <option value="">Seleccionar gestión</option>
                                                    @foreach ($gestiones as $gestion)
                                                        <option value="{{ $gestion->cod_gea }}">
                                                            Gestión {{ $gestion->ani_gea ?? 'sin año' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="ui-label text-xs uppercase tracking-[0.14em]">Paralelo</label>
                                            <div class="relative curso-select-wrap">
                                                <select wire:model.live="horarioParalelo" class="ui-select pr-10">
                                                    <option value="">Seleccionar paralelo</option>
                                                    @foreach ($paralelos as $paralelo)
                                                        <option value="{{ $paralelo->cod_par }}">
                                                            {{ $paralelo->nombre ?? $paralelo->cod_par }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="ui-label text-xs uppercase tracking-[0.14em]">Turno</label>
                                            <div class="relative curso-select-wrap">
                                                <select wire:model.live="horarioTurno" class="ui-select pr-10">
                                                    <option value="">Seleccionar turno</option>
                                                    @foreach ($turnos as $turno)
                                                        <option value="{{ $turno->cod_tur }}">
                                                            {{ $turno->nombre ?? $turno->cod_tur }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="ui-label text-xs uppercase tracking-[0.14em]">Vista</label>
                                            <div class="relative curso-select-wrap">
                                                <select wire:model.live="horarioVista" class="ui-select pr-10">
                                                    <option value="MANANA">Turno mañana</option>
                                                    <option value="TARDE">Turno tarde</option>
                                                </select>
                                            </div>
                                            <p class="ui-help">
                                                La vista “Todos” se puede manejar después con dos matrices separadas.
                                            </p>
                                        </div>
                                    </div>

                                    {{-- INDICADORES --}}
                                    <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                                        <article class="rounded-2xl border p-4"
                                            style="border-color: var(--ui-border); background: var(--ui-surface);">
                                            <p class="text-xs" style="color: var(--ui-muted);">Horario completo</p>
                                            <p class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                                {{ $porcentaje }}%
                                            </p>
                                            <div class="mt-2 h-1.5 overflow-hidden rounded-full"
                                                style="background: var(--ui-surface-muted);">
                                                <div class="h-full rounded-full"
                                                    style="width: {{ $porcentaje }}%; background: var(--ui-primary);"></div>
                                            </div>
                                        </article>

                                        <article class="rounded-2xl border p-4"
                                            style="border-color: var(--ui-border); background: var(--ui-surface);">
                                            <p class="text-xs" style="color: var(--ui-muted);">Bloques asignados</p>
                                            <p class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                                {{ $asignados }}/{{ $totalCeldas }}
                                            </p>
                                        </article>

                                        <article class="rounded-2xl border p-4"
                                            style="border-color: var(--ui-border); background: var(--ui-surface);">
                                            <p class="text-xs" style="color: var(--ui-muted);">Bloques libres</p>
                                            <p class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                                {{ $libres }}
                                            </p>
                                        </article>

                                        <article class="rounded-2xl border p-4"
                                            style="border-color: var(--ui-border); background: var(--ui-surface);">
                                            <p class="text-xs" style="color: var(--ui-muted);">Cruces docentes</p>
                                            <p class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                                {{ $indicadores['cruces_docentes'] ?? 0 }}
                                            </p>
                                        </article>

                                        <article class="rounded-2xl border p-4"
                                            style="border-color: var(--ui-border); background: var(--ui-surface);">
                                            <p class="text-xs" style="color: var(--ui-muted);">Materias pendientes</p>
                                            <p class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                                {{ $indicadores['materias_pendientes'] ?? 0 }}
                                            </p>
                                        </article>
                                    </div>

                                    <div class="ui-alert-info mt-5">
                                        <p class="font-black">Lectura institucional</p>
                                        <p class="mt-1 text-sm leading-6">
                                            Los bloques provienen de <strong>horario_bloque</strong>, la cabecera de
                                            <strong>horario</strong> y cada clase asignada se guarda en
                                            <strong>horario_detalle</strong>. Por eso, al retirar una clase, se elimina el
                                            detalle
                                            de la celda, no la cabecera del horario.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            {{-- MATRIZ SEMANAL --}}
                            <div id="area-horario-institucional" class="ui-card overflow-hidden rounded-[2rem]">
                                <div class="border-b p-5" style="border-color: var(--ui-border);">
                                    <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                                        <div>
                                            <p class="text-xs font-bold uppercase tracking-[0.16em]"
                                                style="color: var(--ui-muted);">
                                                Unidad Educativa Técnico Humanístico Franz Tamayo N.° 3
                                            </p>

                                            <h4 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                                                Matriz semanal del curso
                                            </h4>

                                            <p class="mt-1 text-sm" style="color: var(--ui-muted);">
                                                {{ $cursoDetalle['nom_cur'] ?? 'Curso' }} ·
                                                {{ $contextoHorario['gestion'] ?? 'Gestión' }} ·
                                                {{ $contextoHorario['paralelo'] ?? 'Paralelo' }} ·
                                                {{ $contextoHorario['turno'] ?? 'Turno' }}
                                            </p>
                                        </div>

                                        <div class="flex flex-wrap gap-2">
                                            <span
                                                class="ui-badge-info">{{ $contextoHorario['gestion'] ?? 'Gestión' }}</span>
                                            <span
                                                class="ui-badge-info">{{ $contextoHorario['paralelo'] ?? 'Paralelo' }}</span>
                                            <span class="ui-badge-info">{{ $contextoHorario['turno'] ?? 'Turno' }}</span>

                                            @if (($cursoDetalle['est_cur'] ?? 'INACTIVO') === 'ACTIVO')
                                                <span class="ui-badge-success">Estado activo</span>
                                            @else
                                                <span class="ui-badge-danger">Estado inactivo</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if (empty($horarioGestion) || empty($horarioParalelo) || empty($horarioTurno))
                                    <div class="p-5">
                                        <div class="ui-alert-warning">
                                            Selecciona gestión, paralelo y turno para generar la matriz semanal del curso.
                                        </div>
                                    </div>
                                @elseif (empty($matriz))
                                    <div class="p-5">
                                        <div class="ui-alert-warning">
                                            No existen bloques activos para el turno seleccionado. Verifica el seeder de
                                            <strong>horario_bloque</strong>.
                                        </div>
                                    </div>
                                @else
                                    <div class="overflow-x-auto curso-mini-scroll">
                                        <div class="min-w-[1240px] p-5">
                                            <div class="grid gap-3"
                                                style="grid-template-columns: 160px repeat({{ count($dias) }}, minmax(190px, 1fr));">

                                                {{-- CABECERA --}}
                                                <div class="rounded-2xl border p-3 text-center text-xs font-black uppercase tracking-[0.14em]"
                                                    style="border-color: var(--ui-border); background: var(--ui-surface-soft); color: var(--ui-muted);">
                                                    Bloque
                                                </div>

                                                @foreach ($dias as $dia)
                                                    <div class="rounded-2xl border p-3 text-center text-xs font-black uppercase tracking-[0.14em]"
                                                        style="border-color: var(--ui-border); background: var(--ui-surface-soft); color: var(--ui-text);">
                                                        {{ $dia }}
                                                    </div>
                                                @endforeach

                                                {{-- FILAS --}}
                                                @foreach ($matriz as $fila)
                                                    <div class="rounded-2xl border p-4"
                                                        style="border-color: var(--ui-border); background: var(--ui-surface-soft);">
                                                        <p class="text-sm font-black" style="color: var(--ui-text);">
                                                            {{ $fila['nom_hbl'] ?? ('Bloque ' . ($fila['num_blo_hor'] ?? '—')) }}
                                                        </p>
                                                        <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                                                            {{ $fila['hor_ini_hor'] ?? '--:--' }}
                                                            -
                                                            {{ $fila['hor_fin_hor'] ?? '--:--' }}
                                                        </p>
                                                    </div>

                                                    @foreach ($dias as $dia)
                                                        @php
                                                            $celda = $fila['dias'][$dia] ?? [
                                                                'existe' => false,
                                                                'estado_visual' => 'LIBRE',
                                                                'nombre' => 'Sin asignar',
                                                                'docente' => 'Disponible',
                                                                'aula' => 'Libre',
                                                                'badge' => 'Libre',
                                                            ];

                                                            $estadoVisual = $celda['estado_visual'] ?? 'LIBRE';

                                                            $cellClass = match ($estadoVisual) {
                                                                'ESPECIALIDAD' => 'horario-cell-especialidad',
                                                                'ASIGNADO' => 'horario-cell-materia',
                                                                'PENDIENTE' => 'horario-cell-pendiente',
                                                                default => 'horario-cell-free',
                                                            };

                                                            $tipoLabel = match ($celda['tipo'] ?? 'LIBRE') {
                                                                'ESPECIALIDAD' => 'Técnica',
                                                                'MATERIA' => 'Materia',
                                                                default => 'Libre',
                                                            };
                                                        @endphp

                                                        @if (($celda['existe'] ?? false) === true)
                                                            <div class="horario-cell {{ $cellClass }} group rounded-2xl p-3"
                                                                wire:key="celda-ocupada-{{ $fila['num_blo_hor'] ?? 'bloque' }}-{{ $dia }}-{{ $celda['cod_hde'] ?? 'sin-id' }}">
                                                                <div class="flex h-full flex-col justify-between gap-3">
                                                                    <div>
                                                                        <div class="flex items-start justify-between gap-2">
                                                                            <p class="max-w-[8rem] truncate text-sm font-black"
                                                                                style="color: var(--ui-text);">
                                                                                {{ $celda['codigo_visual'] ?? 'CLASE' }}
                                                                            </p>

                                                                            @if (($celda['tipo'] ?? '') === 'ESPECIALIDAD')
                                                                                <span class="ui-badge-violet">Técnica</span>
                                                                            @else
                                                                                <span class="ui-badge-info">Materia</span>
                                                                            @endif
                                                                        </div>

                                                                        <p class="mt-2 line-clamp-2 text-xs font-bold"
                                                                            style="color: var(--ui-text);">
                                                                            {{ $celda['nombre'] ?? 'Clase asignada' }}
                                                                        </p>

                                                                        <p class="mt-1 line-clamp-1 text-xs"
                                                                            style="color: var(--ui-muted);">
                                                                            {{ $celda['docente'] ?? 'Docente asignado' }}
                                                                        </p>

                                                                        <p class="mt-1 line-clamp-1 text-xs"
                                                                            style="color: var(--ui-muted);">
                                                                            {{ $celda['aula'] ?? 'Aula no definida' }}
                                                                        </p>

                                                                        @if (!empty($celda['observacion']))
                                                                            <p class="mt-2 line-clamp-2 rounded-xl px-2 py-1 text-[0.68rem]"
                                                                                style="background: var(--ui-surface-soft); color: var(--ui-muted);">
                                                                                {{ $celda['observacion'] }}
                                                                            </p>
                                                                        @endif
                                                                    </div>

                                                                    <div class="flex items-center justify-between gap-2">
                                                                        <span class="curso-pill">
                                                                            {{ $celda['badge'] ?? 'Activo' }}
                                                                        </span>

                                                                        <div class="flex items-center gap-1">
                                                                            <span class="rounded-xl px-2 py-1 text-[0.68rem] font-black"
                                                                                style="background: var(--ui-surface-soft); color: var(--ui-muted);">
                                                                                {{ $tipoLabel }}
                                                                            </span>

                                                                            @if (!empty($celda['cod_hde']))
                                                                                <button type="button" x-on:click.prevent="
                                                                                                                                    window.confirmarAccionCurso({
                                                                                                                                        title: '¿Retirar clase?',
                                                                                                                                        text: 'Se retirará esta clase del horario institucional. La cabecera del horario se conservará.',
                                                                                                                                        icon: 'warning',
                                                                                                                                        confirmButtonText: 'Sí, retirar',
                                                                                                                                        confirmButtonColor: '#dc2626',
                                                                                                                                        onConfirm: () => $wire.quitarClaseHorario(@js($celda['cod_hde']))
                                                                                                                                    })
                                                                                                                                "
                                                                                    wire:loading.attr="disabled"
                                                                                    wire:target="quitarClaseHorario"
                                                                                    class="rounded-xl p-2 transition hover:scale-105 disabled:cursor-wait disabled:opacity-60"
                                                                                    style="background: var(--ui-danger-soft); color: var(--ui-danger);"
                                                                                    title="Retirar clase">
                                                                                    <span wire:loading.remove wire:target="quitarClaseHorario">
                                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4"
                                                                                            fill="none" viewBox="0 0 24 24"
                                                                                            stroke="currentColor" stroke-width="1.8">
                                                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                                                d="M6 18 18 6M6 6l12 12" />
                                                                                        </svg>
                                                                                    </span>

                                                                                    <span wire:loading wire:target="quitarClaseHorario">
                                                                                        <svg class="h-4 w-4 animate-spin" fill="none"
                                                                                            viewBox="0 0 24 24">
                                                                                            <circle class="opacity-25" cx="12" cy="12" r="10"
                                                                                                stroke="currentColor" stroke-width="4"></circle>
                                                                                            <path class="opacity-75" fill="currentColor"
                                                                                                d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z">
                                                                                            </path>
                                                                                        </svg>
                                                                                    </span>
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @else
                                                            <button type="button"
                                                                wire:key="celda-libre-{{ $fila['num_blo_hor'] ?? 'bloque' }}-{{ $dia }}"
                                                                wire:click="abrirModalClaseHorario(@js($dia), {{ (int) ($fila['num_blo_hor'] ?? 0) }}, @js($fila['hor_ini_hor'] ?? ''), @js($fila['hor_fin_hor'] ?? ''))"
                                                                wire:loading.attr="disabled" wire:target="abrirModalClaseHorario"
                                                                class="horario-cell horario-cell-free rounded-2xl p-3 text-left disabled:cursor-wait disabled:opacity-60"
                                                                title="Crear clase en {{ $dia }}">
                                                                <div class="flex h-full flex-col items-center justify-center text-center">
                                                                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl transition group-hover:scale-105"
                                                                        style="background: var(--ui-primary-soft); color: var(--ui-primary);">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                                d="M12 4.5v15m7.5-7.5h-15" />
                                                                        </svg>
                                                                    </div>

                                                                    <p class="mt-3 text-sm font-black" style="color: var(--ui-text);">
                                                                        Sin asignar
                                                                    </p>

                                                                    <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                                                                        Crear clase
                                                                    </p>

                                                                    <p class="mt-2 text-[0.68rem]" style="color: var(--ui-muted);">
                                                                        {{ $dia }} · {{ $fila['hor_ini_hor'] ?? '--:--' }}
                                                                    </p>
                                                                </div>
                                                            </button>
                                                        @endif
                                                    @endforeach
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </section>
                    </main>

                    {{-- FOOTER --}}
                    <footer
                        class="ui-modal-footer flex flex-col gap-3 border-t sm:flex-row sm:items-center sm:justify-between"
                        style="border-color: var(--ui-border);">
                        <div class="text-xs leading-5" style="color: var(--ui-muted);">
                            Curso:
                            <strong style="color: var(--ui-text);">
                                {{ $cursoDetalle['nom_cur'] ?? 'Curso seleccionado' }}
                            </strong>
                            · Estructura:
                            <strong style="color: var(--ui-text);">
                                horario / horario_bloque / horario_detalle
                            </strong>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                            <button type="button" wire:click="cerrarModalDetalle" class="ui-btn-secondary">
                                Cerrar
                            </button>

                            <button type="button" x-on:click="tabDetalle = 'horarios'" class="ui-btn-primary">
                                Ver horario institucional
                            </button>
                        </div>
                    </footer>
                </div>
            </aside>
        </div>
    @endif

    {{-- MODAL CREAR CLASE DESDE HORARIO --}}
    @if ($modalClaseHorario)
        <div wire:key="modal-clase-horario-{{ $cursoDetalle['cod_cur'] ?? 'sin-curso' }}-{{ $claseContexto['dia_hor'] ?? 'sin-dia' }}-{{ $claseContexto['num_blo_hor'] ?? 'sin-bloque' }}"
            class="fixed inset-0 z-[60] flex items-center justify-center px-4 py-6 sm:px-6" x-data="{
                                    validacion: { valido: false, errores: {}, lista: [] },

                                    tipoClase: @entangle('formClaseHorario.tipo_plan').live,

                                    recalcular() {
                                        if (typeof window.validarClaseHorarioForm === 'function') {
                                            this.validacion = window.validarClaseHorarioForm(this.$root);
                                            return;
                                        }

                                        this.validacion = {
                                            valido: true,
                                            errores: {},
                                            lista: []
                                        };
                                    },

                                    iconoTipo() {
                                        return this.tipoClase === 'ESPECIALIDAD' ? 'TTE' : 'MAT';
                                    }
                                }" x-init="$nextTick(() => recalcular())" x-effect="$nextTick(() => recalcular())"
            x-on:input.debounce.120ms="recalcular()" x-on:change.debounce.120ms="recalcular()"
            x-on:keydown.escape.window="$wire.cerrarModalClaseHorario()">
            {{-- FONDO DIFUMINADO --}}
            <div class="absolute inset-0 curso-backdrop-diffuse" wire:click="cerrarModalClaseHorario"></div>

            {{-- MODAL --}}
            <div class="ui-modal curso-fade-in relative z-10 w-full max-w-6xl overflow-hidden" role="dialog"
                aria-modal="true" aria-label="Crear clase desde bloque libre">
                {{-- HEADER --}}
                <div class="curso-hero-bg border-b px-5 py-5 sm:px-6" style="border-color: var(--ui-border);">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 ring-1"
                                style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                                <span class="h-2 w-2 rounded-full" style="background: var(--ui-primary);"></span>
                                <span class="text-xs font-black uppercase tracking-[0.18em]">
                                    Nueva clase en horario
                                </span>
                            </div>

                            <h3 class="mt-3 text-2xl font-black tracking-tight md:text-3xl" style="color: var(--ui-text);">
                                Crear clase desde bloque libre
                            </h3>

                            <p class="mt-2 max-w-4xl text-sm leading-6" style="color: var(--ui-muted);">
                                Registra una clase dentro de la matriz semanal institucional. El sistema usará la cabecera
                                de <strong>horario</strong>, el bloque de <strong>horario_bloque</strong> y guardará la
                                celda real en <strong>horario_detalle</strong>.
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarModalClaseHorario" class="ui-icon-btn shrink-0"
                            title="Cerrar modal">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- BODY --}}
                <div class="max-h-[74vh] overflow-y-auto px-5 py-6 sm:px-6 curso-mini-scroll">
                    <div class="grid gap-6 xl:grid-cols-12">

                        {{-- COLUMNA IZQUIERDA: CONTEXTO --}}
                        <aside class="space-y-4 xl:col-span-4">
                            {{-- CONTEXTO DEL BLOQUE --}}
                            <section class="ui-card-soft overflow-hidden rounded-[1.75rem]">
                                <div class="border-b px-5 py-4" style="border-color: var(--ui-border);">
                                    <p class="text-xs font-black uppercase tracking-[0.16em]"
                                        style="color: var(--ui-muted);">
                                        Contexto del bloque
                                    </p>

                                    <h4 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                                        Ubicación institucional
                                    </h4>

                                    <p class="mt-1 text-sm leading-6" style="color: var(--ui-muted);">
                                        Esta información viene desde la celda seleccionada en la matriz.
                                    </p>
                                </div>

                                <div class="space-y-3 p-5">
                                    <div class="flex items-center justify-between gap-3 rounded-2xl border px-4 py-3"
                                        style="border-color: var(--ui-border); background: var(--ui-surface);">
                                        <span class="text-sm font-medium" style="color: var(--ui-muted);">
                                            Curso
                                        </span>
                                        <strong class="max-w-[58%] truncate text-right text-sm"
                                            style="color: var(--ui-text);">
                                            {{ $cursoDetalle['nom_cur'] ?? 'Curso no definido' }}
                                        </strong>
                                    </div>

                                    <div class="flex items-center justify-between gap-3 rounded-2xl border px-4 py-3"
                                        style="border-color: var(--ui-border); background: var(--ui-surface);">
                                        <span class="text-sm font-medium" style="color: var(--ui-muted);">
                                            Gestión
                                        </span>
                                        <strong class="max-w-[58%] truncate text-right text-sm"
                                            style="color: var(--ui-text);">
                                            {{ $cursoDetalle['visor_horario']['contexto']['gestion'] ?? 'Gestión seleccionada' }}
                                        </strong>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="rounded-2xl border p-4"
                                            style="border-color: var(--ui-border); background: var(--ui-surface);">
                                            <p class="text-xs font-bold uppercase tracking-[0.12em]"
                                                style="color: var(--ui-muted);">
                                                Paralelo
                                            </p>
                                            <p class="mt-1 truncate text-sm font-black" style="color: var(--ui-text);">
                                                {{ $cursoDetalle['visor_horario']['contexto']['paralelo'] ?? '—' }}
                                            </p>
                                        </div>

                                        <div class="rounded-2xl border p-4"
                                            style="border-color: var(--ui-border); background: var(--ui-surface);">
                                            <p class="text-xs font-bold uppercase tracking-[0.12em]"
                                                style="color: var(--ui-muted);">
                                                Turno
                                            </p>
                                            <p class="mt-1 truncate text-sm font-black" style="color: var(--ui-text);">
                                                {{ $cursoDetalle['visor_horario']['contexto']['turno'] ?? '—' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="rounded-2xl border p-4"
                                            style="border-color: var(--ui-border); background: var(--ui-surface);">
                                            <p class="text-xs font-bold uppercase tracking-[0.12em]"
                                                style="color: var(--ui-muted);">
                                                Día
                                            </p>
                                            <p class="mt-1 text-sm font-black" style="color: var(--ui-text);">
                                                {{ $claseContexto['dia_hor'] ?: '—' }}
                                            </p>
                                        </div>

                                        <div class="rounded-2xl border p-4"
                                            style="border-color: var(--ui-border); background: var(--ui-surface);">
                                            <p class="text-xs font-bold uppercase tracking-[0.12em]"
                                                style="color: var(--ui-muted);">
                                                Bloque
                                            </p>
                                            <p class="mt-1 text-sm font-black" style="color: var(--ui-text);">
                                                {{ $claseContexto['num_blo_hor'] ?: '—' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="rounded-2xl border p-4"
                                        style="border-color: var(--ui-primary-border); background: var(--ui-primary-soft);">
                                        <p class="text-xs font-bold uppercase tracking-[0.12em]"
                                            style="color: var(--ui-primary);">
                                            Horario del bloque
                                        </p>

                                        <p class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                            {{ $claseContexto['hor_ini_hor'] ?: '--:--' }}
                                            -
                                            {{ $claseContexto['hor_fin_hor'] ?: '--:--' }}
                                        </p>

                                        @if (!empty($claseContexto['cod_hbl']))
                                            <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                                                Bloque institucional activo.
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </section>

                            {{-- VALIDACIÓN CLIENTE --}}
                            <section x-show="!validacion.valido" x-cloak class="ui-alert-danger">
                                <div class="flex gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl"
                                        style="background: var(--ui-danger-soft); color: var(--ui-danger);">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v3.75m0 3.75h.008v.008H12V16.5Zm9-4.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                        </svg>
                                    </div>

                                    <div>
                                        <p class="font-black">
                                            Faltan datos para registrar la clase
                                        </p>

                                        <ul class="mt-2 list-disc space-y-1 pl-5 text-xs leading-5">
                                            <template x-for="error in validacion.lista" :key="error">
                                                <li x-text="error"></li>
                                            </template>
                                        </ul>
                                    </div>
                                </div>
                            </section>

                            {{-- REGLA INSTITUCIONAL --}}
                            <section class="ui-alert-info">
                                <p class="font-black">
                                    Regla institucional
                                </p>
                                <p class="mt-1 text-sm leading-6">
                                    El usuario trabaja con datos humanos: materia, especialidad, docente y aula. El sistema
                                    crea o reutiliza el plan correspondiente y registra la clase en el detalle del horario.
                                </p>
                            </section>

                            {{-- MAPA DE GUARDADO --}}
                            <section class="rounded-[1.75rem] border p-5"
                                style="border-color: var(--ui-border); background: var(--ui-surface);">
                                <p class="text-xs font-black uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                                    Flujo interno
                                </p>

                                <div class="mt-4 space-y-3">
                                    <div class="flex items-center gap-3">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-xl text-xs font-black"
                                            style="background: var(--ui-primary-soft); color: var(--ui-primary);">1</span>
                                        <p class="text-sm" style="color: var(--ui-muted);">
                                            Buscar/crear cabecera en <strong>horario</strong>.
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-xl text-xs font-black"
                                            style="background: var(--ui-info-soft); color: var(--ui-info);">2</span>
                                        <p class="text-sm" style="color: var(--ui-muted);">
                                            Usar bloque de <strong>horario_bloque</strong>.
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-xl text-xs font-black"
                                            style="background: var(--ui-violet-soft); color: var(--ui-violet);">3</span>
                                        <p class="text-sm" style="color: var(--ui-muted);">
                                            Crear/reutilizar plan académico o técnico.
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-xl text-xs font-black"
                                            style="background: var(--ui-warning-soft); color: var(--ui-warning);">4</span>
                                        <p class="text-sm" style="color: var(--ui-muted);">
                                            Guardar clase en <strong>horario_detalle</strong>.
                                        </p>
                                    </div>
                                </div>
                            </section>
                        </aside>

                        {{-- COLUMNA DERECHA: FORMULARIO --}}
                        <section class="xl:col-span-8">
                            <div class="ui-card-soft overflow-hidden rounded-[1.75rem]">
                                {{-- SUBHEADER FORM --}}
                                <div class="border-b p-5" style="border-color: var(--ui-border);">
                                    <div class="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-[0.16em]"
                                                style="color: var(--ui-muted);">
                                                Datos de la clase
                                            </p>

                                            <h4 class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                                Información académica y docente
                                            </h4>

                                            <p class="mt-1 text-sm leading-6" style="color: var(--ui-muted);">
                                                Completa los datos para ubicar la clase en la matriz semanal.
                                            </p>
                                        </div>

                                        <span x-show="tipoClase === 'MATERIA'" class="ui-badge-info shrink-0">
                                            Materia curricular
                                        </span>

                                        <span x-show="tipoClase === 'ESPECIALIDAD'" x-cloak
                                            class="ui-badge-violet shrink-0">
                                            Especialidad técnica
                                        </span>
                                    </div>
                                </div>

                                {{-- CAMPOS --}}
                                <div class="p-5">
                                    <div class="grid gap-5 md:grid-cols-2">
                                        {{-- TIPO DE CLASE --}}
                                        <div>
                                            <label class="ui-label">
                                                Tipo de clase <span class="text-red-500">*</span>
                                            </label>

                                            <div class="grid gap-3 sm:grid-cols-2">
                                                <label
                                                    class="cursor-pointer rounded-2xl border p-4 transition hover:-translate-y-0.5"
                                                    :style="tipoClase === 'MATERIA'
                                                                            ? 'border-color: var(--ui-info-border); background: var(--ui-info-soft); box-shadow: var(--ui-shadow-sm);'
                                                                            : 'border-color: var(--ui-border); background: var(--ui-surface);'">
                                                    <input type="radio" wire:model.live="formClaseHorario.tipo_plan"
                                                        data-field="tipoClaseHorario" value="MATERIA" class="sr-only">

                                                    <div class="flex items-center gap-3">
                                                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl"
                                                            style="background: var(--ui-info-soft); color: var(--ui-info);">
                                                            <span class="text-xs font-black">MAT</span>
                                                        </div>

                                                        <div>
                                                            <p class="text-sm font-black" style="color: var(--ui-text);">
                                                                Materia
                                                            </p>
                                                            <p class="text-xs" style="color: var(--ui-muted);">
                                                                Plan Asignatura
                                                            </p>
                                                        </div>
                                                    </div>
                                                </label>

                                                <label
                                                    class="cursor-pointer rounded-2xl border p-4 transition hover:-translate-y-0.5"
                                                    :style="tipoClase === 'ESPECIALIDAD'
                                                                            ? 'border-color: var(--ui-violet-border); background: var(--ui-violet-soft); box-shadow: var(--ui-shadow-sm);'
                                                                            : 'border-color: var(--ui-border); background: var(--ui-surface);'">
                                                    <input type="radio" wire:model.live="formClaseHorario.tipo_plan"
                                                        data-field="tipoClaseHorario" value="ESPECIALIDAD" class="sr-only">

                                                    <div class="flex items-center gap-3">
                                                        <div class="flex h-10 w-10 items-center justify-center rounded-2xl"
                                                            style="background: var(--ui-violet-soft); color: var(--ui-violet);">
                                                            <span class="text-xs font-black">TTE</span>
                                                        </div>

                                                        <div>
                                                            <p class="text-sm font-black" style="color: var(--ui-text);">
                                                                Técnica
                                                            </p>
                                                            <p class="text-xs" style="color: var(--ui-muted);">
                                                                Plan Especialidad
                                                            </p>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>

                                            <p x-show="validacion.errores.tipoClaseHorario" x-cloak
                                                x-text="validacion.errores.tipoClaseHorario" class="ui-error"></p>

                                            @error('formClaseHorario.tipo_plan')
                                                <p class="ui-error">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        {{-- ESTADO --}}
                                        <div>
                                            <label class="ui-label">
                                                Estado <span class="text-red-500">*</span>
                                            </label>

                                            <div class="relative curso-select-wrap">
                                                <select wire:model="formClaseHorario.est_hor"
                                                    data-field="estadoClaseHorario" class="ui-select pr-10">
                                                    <option value="ACTIVO">Activo</option>
                                                    <option value="INACTIVO">Inactivo</option>
                                                </select>
                                            </div>

                                            <p x-show="validacion.errores.estadoClaseHorario" x-cloak
                                                x-text="validacion.errores.estadoClaseHorario" class="ui-error"></p>

                                            @error('formClaseHorario.est_hor')
                                                <p class="ui-error">{{ $message }}</p>
                                            @enderror

                                            <p class="ui-help">
                                                Estado con el que se guardará el registro en horario_detalle.
                                            </p>
                                        </div>

                                        {{-- MATERIA --}}
                                        <div x-show="tipoClase === 'MATERIA'" x-cloak class="md:col-span-2">
                                            <label class="ui-label">
                                                Materia curricular <span class="text-red-500">*</span>
                                            </label>

                                            <div class="relative curso-select-wrap">
                                                <select wire:model="formClaseHorario.cod_mat" data-field="materiaHorario"
                                                    class="ui-select pr-10">
                                                    <option value="">Seleccionar materia</option>

                                                    @foreach ($materiasHorario as $materia)
                                                        <option value="{{ $materia->codigo }}">
                                                            {{ $materia->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <p x-show="validacion.errores.materiaHorario" x-cloak
                                                x-text="validacion.errores.materiaHorario" class="ui-error"></p>

                                            @error('formClaseHorario.cod_mat')
                                                <p class="ui-error">{{ $message }}</p>
                                            @enderror

                                            @if ($materiasHorario->isEmpty())
                                                <div class="ui-alert-warning mt-3">
                                                    No existen materias o asignaturas activas registradas. Primero registra el
                                                    catálogo académico.
                                                </div>
                                            @endif

                                            <p class="ui-help">
                                                El sistema creará o reutilizará el Plan de Asignatura con curso, gestión,
                                                paralelo,
                                                turno, materia y docente.
                                            </p>
                                        </div>

                                        {{-- ESPECIALIDAD --}}
                                        <div x-show="tipoClase === 'ESPECIALIDAD'" x-cloak class="md:col-span-2">
                                            <label class="ui-label">
                                                Especialidad técnica <span class="text-red-500">*</span>
                                            </label>

                                            <div class="relative curso-select-wrap">
                                                <select wire:model="formClaseHorario.cod_esp"
                                                    data-field="especialidadHorario" class="ui-select pr-10">
                                                    <option value="">Seleccionar especialidad técnica</option>

                                                    @foreach ($especialidadesHorario as $especialidad)
                                                        <option value="{{ $especialidad->codigo }}">
                                                            {{ $especialidad->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <p x-show="validacion.errores.especialidadHorario" x-cloak
                                                x-text="validacion.errores.especialidadHorario" class="ui-error"></p>

                                            @error('formClaseHorario.cod_esp')
                                                <p class="ui-error">{{ $message }}</p>
                                            @enderror

                                            @if ($especialidadesHorario->isEmpty())
                                                <div class="ui-alert-warning mt-3">
                                                    No existen especialidades técnicas activas registradas. Primero registra el
                                                    catálogo técnico.
                                                </div>
                                            @endif

                                            <p class="ui-help">
                                                El sistema creará o reutilizará el Plan de Especialidad con curso, gestión,
                                                paralelo,
                                                turno, especialidad y docente.
                                            </p>
                                        </div>

                                        {{-- DOCENTE --}}
                                        <div class="md:col-span-2">
                                            <label class="ui-label">
                                                Docente responsable <span class="text-red-500">*</span>
                                            </label>

                                            <div class="relative curso-select-wrap">
                                                <select wire:model="formClaseHorario.cod_doc" data-field="docenteHorario"
                                                    class="ui-select pr-10">
                                                    <option value="">Seleccionar docente</option>

                                                    @foreach ($docentesHorario as $docente)
                                                        <option value="{{ $docente->cod_doc }}">
                                                            {{ $docente->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <p x-show="validacion.errores.docenteHorario" x-cloak
                                                x-text="validacion.errores.docenteHorario" class="ui-error"></p>

                                            @error('formClaseHorario.cod_doc')
                                                <p class="ui-error">{{ $message }}</p>
                                            @enderror

                                            @if ($docentesHorario->isEmpty())
                                                <div class="ui-alert-warning mt-3">
                                                    No existen docentes disponibles. Primero registra docentes activos.
                                                </div>
                                            @endif

                                            <p class="ui-help">
                                                Se validará que el docente no tenga cruce en el mismo día, turno y bloque.
                                            </p>
                                        </div>

                                        {{-- CARGA HORARIA --}}
                                        <div>
                                            <label class="ui-label">
                                                Carga horaria <span class="text-red-500">*</span>
                                            </label>

                                            <input type="number" wire:model="formClaseHorario.carga_horaria"
                                                data-field="cargaHorario" min="1" max="80" class="ui-input"
                                                placeholder="Ej. 1">

                                            <p x-show="validacion.errores.cargaHorario" x-cloak
                                                x-text="validacion.errores.cargaHorario" class="ui-error"></p>

                                            @error('formClaseHorario.carga_horaria')
                                                <p class="ui-error">{{ $message }}</p>
                                            @enderror

                                            <p class="ui-help">
                                                Se usará para el plan académico o técnico vinculado.
                                            </p>
                                        </div>

                                        {{-- AULA --}}
                                        <div>
                                            <label class="ui-label">
                                                Aula o laboratorio
                                            </label>

                                            <input type="text" wire:model="formClaseHorario.aul_hor" maxlength="100"
                                                class="ui-input" placeholder="Ej. Aula 4, Lab. Sistemas">

                                            @error('formClaseHorario.aul_hor')
                                                <p class="ui-error">{{ $message }}</p>
                                            @enderror

                                            <p class="ui-help">
                                                Se guardará como ubicación visible de la clase.
                                            </p>
                                        </div>

                                        {{-- OBSERVACIÓN --}}
                                        <div class="md:col-span-2">
                                            <label class="ui-label">
                                                Observación
                                            </label>

                                            <textarea wire:model="formClaseHorario.obs_hor" maxlength="255" rows="3"
                                                class="ui-textarea"
                                                placeholder="Ej. Clase práctica, laboratorio, evaluación, refuerzo académico..."></textarea>

                                            @error('formClaseHorario.obs_hor')
                                                <p class="ui-error">{{ $message }}</p>
                                            @enderror

                                            <p class="ui-help">
                                                Campo opcional. Será visible como observación del detalle de horario.
                                            </p>
                                        </div>
                                    </div>

                                    {{-- RESUMEN VISUAL --}}
                                    <div class="mt-6 rounded-[1.5rem] border p-4"
                                        style="border-color: var(--ui-border); background: var(--ui-surface);">
                                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                                            <div>
                                                <p class="text-xs font-black uppercase tracking-[0.16em]"
                                                    style="color: var(--ui-muted);">
                                                    Resumen de guardado
                                                </p>

                                                <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                                                    Al guardar, se registrará una clase en <strong>horario_detalle</strong>.
                                                    Si es materia, se vinculará mediante <strong>cod_pas</strong>. Si es
                                                    especialidad,
                                                    se vinculará mediante <strong>cod_pes</strong>.
                                                </p>
                                            </div>

                                            <div class="grid shrink-0 grid-cols-2 gap-2 text-xs">
                                                <span class="curso-pill">horario</span>
                                                <span class="curso-pill">bloque</span>
                                                <span class="curso-pill">detalle</span>
                                                <span class="curso-pill">plan</span>
                                            </div>
                                        </div>

                                        <div class="mt-4 grid gap-3 md:grid-cols-3">
                                            <div class="rounded-2xl border p-3"
                                                style="border-color: var(--ui-border); background: var(--ui-surface-soft);">
                                                <p class="text-xs font-bold" style="color: var(--ui-muted);">Destino</p>
                                                <p class="mt-1 text-sm font-black" style="color: var(--ui-text);">
                                                    horario_detalle
                                                </p>
                                            </div>

                                            <div class="rounded-2xl border p-3"
                                                style="border-color: var(--ui-border); background: var(--ui-surface-soft);">
                                                <p class="text-xs font-bold" style="color: var(--ui-muted);">Día y bloque
                                                </p>
                                                <p class="mt-1 text-sm font-black" style="color: var(--ui-text);">
                                                    {{ $claseContexto['dia_hor'] ?: '—' }}
                                                    ·
                                                    Bloque {{ $claseContexto['num_blo_hor'] ?: '—' }}
                                                </p>
                                            </div>

                                            <div class="rounded-2xl border p-3"
                                                style="border-color: var(--ui-border); background: var(--ui-surface-soft);">
                                                <p class="text-xs font-bold" style="color: var(--ui-muted);">Tipo</p>
                                                <p class="mt-1 text-sm font-black" style="color: var(--ui-text);">
                                                    <span
                                                        x-text="tipoClase === 'ESPECIALIDAD' ? 'Especialidad técnica' : 'Materia curricular'"></span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="ui-modal-footer flex flex-col gap-3 border-t sm:flex-row sm:items-center sm:justify-end"
                    style="border-color: var(--ui-border);">
                    <p x-show="!validacion.valido" x-cloak class="text-sm font-medium sm:mr-auto"
                        style="color: var(--ui-danger);">
                        Completa los datos obligatorios antes de guardar.
                    </p>

                    <p x-show="validacion.valido" x-cloak class="text-sm font-medium sm:mr-auto"
                        style="color: var(--ui-primary);">
                        Datos listos para registrar la clase.
                    </p>

                    <button type="button" wire:click="cerrarModalClaseHorario" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button" wire:click="guardarClaseHorario" wire:loading.attr="disabled"
                        wire:target="guardarClaseHorario" :disabled="!validacion.valido"
                        :class="validacion.valido
                                                ? 'ui-btn-primary'
                                                : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none dark:bg-slate-700 dark:text-slate-400'"
                        class="rounded-2xl px-5 py-3 text-sm font-semibold transition">
                        <span wire:loading.remove wire:target="guardarClaseHorario" class="inline-flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75 10.5 18.75 19.5 5.25" />
                            </svg>
                            Guardar clase
                        </span>

                        <span wire:loading wire:target="guardarClaseHorario" class="inline-flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                                </circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z">
                                </path>
                            </svg>
                            Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            (() => {
                /*
                |--------------------------------------------------------------------------
                | GESTIÓN DE CURSOS - SCRIPT UI MEJORADO
                |--------------------------------------------------------------------------
                | Este script controla:
                | - Validación visual de modal crear/editar curso.
                | - Validación visual de modal crear clase desde horario.
                | - Marcado de campos inválidos.
                | - Toasts y alertas SweetAlert.
                | - Gráficos Chart.js adaptados a modo claro/oscuro.
                | - Refresco seguro después de cambios Livewire.
                | - Compatibilidad con la estructura:
                |   horario -> horario_bloque -> horario_detalle
                |--------------------------------------------------------------------------
                */

                const CursoUI = {
                    charts: {
                        horarios: null,
                        planAcademico: null,
                    },

                    timers: {
                        chartRefresh: null,
                        validationRefresh: null,
                    },

                    state: {
                        eventosRegistrados: false,
                        ultimaAccion: null,
                        lockAccion: false,
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | CONFIGURACIÓN
                    |--------------------------------------------------------------------------
                    */

                    config: {
                        validCourseStates: ['ACTIVO', 'INACTIVO'],
                        validClassTypes: ['MATERIA', 'ESPECIALIDAD'],
                        validClassStates: ['ACTIVO', 'INACTIVO'],

                        fieldsCurso: [
                            'nombreCurso',
                            'ordenCurso',
                            'nivelCurso',
                            'estadoCurso',
                        ],

                        fieldsClaseHorario: [
                            'tipoClaseHorario',
                            'materiaHorario',
                            'especialidadHorario',
                            'docenteHorario',
                            'cargaHorario',
                            'estadoClaseHorario',
                        ],

                        chartIds: {
                            horarios: 'chartCursosHorarios',
                            planAcademico: 'chartCursosPlanAcademico',
                        },
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | HELPERS GENERALES
                    |--------------------------------------------------------------------------
                    */

                    normalizarTexto(valor) {
                        return String(valor ?? '')
                            .replace(/\s+/g, ' ')
                            .trim();
                    },

                    limpiarNumero(valor) {
                        return String(valor ?? '')
                            .replace(/[^\d]/g, '')
                            .trim();
                    },

                    esEnteroPositivo(valor) {
                        return /^[0-9]+$/.test(String(valor ?? '').trim());
                    },

                    estaEnLista(valor, permitidos) {
                        return permitidos.includes(String(valor ?? '').trim());
                    },

                    clampNumero(valor, min, max) {
                        const numero = Number(valor);

                        if (Number.isNaN(numero)) {
                            return min;
                        }

                        return Math.min(Math.max(numero, min), max);
                    },

                    debounce(key, callback, delay = 120) {
                        clearTimeout(this.timers[key]);

                        this.timers[key] = setTimeout(() => {
                            callback();
                        }, delay);
                    },

                    requestFrame(callback) {
                        requestAnimationFrame(() => {
                            callback();
                        });
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | HELPERS DOM
                    |--------------------------------------------------------------------------
                    */

                    obtenerCampo(root, field) {
                        if (!root || !field) {
                            return null;
                        }

                        return root.querySelector(`[data-field="${field}"]`);
                    },

                    obtenerCampos(root, field) {
                        if (!root || !field) {
                            return [];
                        }

                        return Array.from(root.querySelectorAll(`[data-field="${field}"]`));
                    },

                    obtenerValor(root, field) {
                        const campos = this.obtenerCampos(root, field);

                        if (!campos.length) {
                            return '';
                        }

                        const primerCampo = campos[0];

                        if (primerCampo.type === 'checkbox') {
                            return primerCampo.checked ? '1' : '';
                        }

                        if (primerCampo.type === 'radio') {
                            const checked = campos.find((campo) => campo.checked);
                            return checked?.value?.trim() ?? '';
                        }

                        return primerCampo.value?.trim() ?? '';
                    },

                    tieneCampo(root, field) {
                        return Boolean(this.obtenerCampo(root, field));
                    },

                    marcarCampo(root, field, tieneError) {
                        const campos = this.obtenerCampos(root, field);

                        campos.forEach((campo) => {
                            campo.classList.toggle('curso-input-invalid', Boolean(tieneError));

                            if (tieneError) {
                                campo.setAttribute('aria-invalid', 'true');
                            } else {
                                campo.removeAttribute('aria-invalid');
                            }
                        });
                    },

                    marcarCampos(root, fields, errores) {
                        fields.forEach((field) => {
                            this.marcarCampo(root, field, Boolean(errores[field]));
                        });
                    },

                    limpiarErroresCampos(root, fields) {
                        fields.forEach((field) => {
                            this.marcarCampo(root, field, false);
                        });
                    },

                    crearResultadoValidacion(errores) {
                        const lista = Object.values(errores).filter(Boolean);

                        return {
                            valido: lista.length === 0,
                            errores,
                            lista,
                        };
                    },

                    enfocarPrimerError(root, errores) {
                        const primerCampoConError = Object.keys(errores)[0];

                        if (!primerCampoConError) {
                            return;
                        }

                        const campo = this.obtenerCampo(root, primerCampoConError);

                        if (campo && typeof campo.focus === 'function') {
                            campo.focus({ preventScroll: false });
                        }
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | VALIDACIÓN: CREAR / EDITAR CURSO
                    |--------------------------------------------------------------------------
                    | Requiere data-field:
                    | - nombreCurso
                    | - ordenCurso
                    | - nivelCurso
                    | - estadoCurso
                    |--------------------------------------------------------------------------
                    */

                    validarCursoForm(root) {
                        const errores = {};

                        const nombre = this.normalizarTexto(this.obtenerValor(root, 'nombreCurso'));
                        const orden = this.obtenerValor(root, 'ordenCurso');
                        const nivel = this.obtenerValor(root, 'nivelCurso');
                        const estado = this.obtenerValor(root, 'estadoCurso');

                        if (nombre === '') {
                            errores.nombreCurso = 'El nombre del curso es obligatorio.';
                        } else if (nombre.length < 3) {
                            errores.nombreCurso = 'El nombre del curso debe tener al menos 3 caracteres.';
                        } else if (nombre.length > 120) {
                            errores.nombreCurso = 'El nombre no debe superar los 120 caracteres.';
                        }

                        if (orden === '') {
                            errores.ordenCurso = 'El orden académico es obligatorio.';
                        } else if (!this.esEnteroPositivo(orden)) {
                            errores.ordenCurso = 'El orden académico debe ser numérico.';
                        } else {
                            const ordenNumero = parseInt(orden, 10);

                            if (ordenNumero < 1 || ordenNumero > 20) {
                                errores.ordenCurso = 'El orden académico debe estar entre 1 y 20.';
                            }
                        }

                        if (nivel === '') {
                            errores.nivelCurso = 'Selecciona el nivel académico.';
                        } else if (nivel.length > 100) {
                            errores.nivelCurso = 'El nivel académico no debe superar los 100 caracteres.';
                        }

                        if (estado === '') {
                            errores.estadoCurso = 'Selecciona el estado del curso.';
                        } else if (!this.estaEnLista(estado, this.config.validCourseStates)) {
                            errores.estadoCurso = 'El estado seleccionado no es válido.';
                        }

                        this.marcarCampos(root, this.config.fieldsCurso, errores);

                        return this.crearResultadoValidacion(errores);
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | VALIDACIÓN: CREAR CLASE DESDE HORARIO
                    |--------------------------------------------------------------------------
                    | Requiere data-field:
                    | - tipoClaseHorario
                    | - materiaHorario
                    | - especialidadHorario
                    | - docenteHorario
                    | - cargaHorario
                    | - estadoClaseHorario
                    |--------------------------------------------------------------------------
                    */

                    validarClaseHorarioForm(root) {
                        const errores = {};

                        const tipo = this.obtenerValor(root, 'tipoClaseHorario');
                        const estado = this.obtenerValor(root, 'estadoClaseHorario');
                        const materia = this.obtenerValor(root, 'materiaHorario');
                        const especialidad = this.obtenerValor(root, 'especialidadHorario');
                        const docente = this.obtenerValor(root, 'docenteHorario');
                        const carga = this.obtenerValor(root, 'cargaHorario');

                        if (tipo === '') {
                            errores.tipoClaseHorario = 'Selecciona el tipo de clase.';
                        } else if (!this.estaEnLista(tipo, this.config.validClassTypes)) {
                            errores.tipoClaseHorario = 'El tipo de clase seleccionado no es válido.';
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Validación contextual
                        |--------------------------------------------------------------------------
                        | Si el tipo es MATERIA, exige materia.
                        | Si el tipo es ESPECIALIDAD, exige especialidad.
                        | Se marca solo el campo correspondiente para evitar errores falsos
                        | cuando el otro select está oculto.
                        |--------------------------------------------------------------------------
                        */

                        if (tipo === 'MATERIA') {
                            if (materia === '') {
                                errores.materiaHorario = 'Selecciona una materia curricular.';
                            }

                            errores.especialidadHorario = null;
                        }

                        if (tipo === 'ESPECIALIDAD') {
                            if (especialidad === '') {
                                errores.especialidadHorario = 'Selecciona una especialidad técnica.';
                            }

                            errores.materiaHorario = null;
                        }

                        if (docente === '') {
                            errores.docenteHorario = 'Selecciona el docente responsable.';
                        }

                        if (carga === '') {
                            errores.cargaHorario = 'Ingresa la carga horaria.';
                        } else if (!this.esEnteroPositivo(carga)) {
                            errores.cargaHorario = 'La carga horaria debe ser numérica.';
                        } else {
                            const cargaNumero = parseInt(carga, 10);

                            if (cargaNumero < 1) {
                                errores.cargaHorario = 'La carga horaria debe ser mayor o igual a 1.';
                            } else if (cargaNumero > 80) {
                                errores.cargaHorario = 'La carga horaria no debe superar 80 horas.';
                            }
                        }

                        if (estado === '') {
                            errores.estadoClaseHorario = 'Selecciona el estado de la clase.';
                        } else if (!this.estaEnLista(estado, this.config.validClassStates)) {
                            errores.estadoClaseHorario = 'El estado seleccionado no es válido.';
                        }

                        const erroresLimpios = Object.fromEntries(
                            Object.entries(errores).filter(([, mensaje]) => Boolean(mensaje))
                        );

                        this.marcarCampos(root, this.config.fieldsClaseHorario, erroresLimpios);

                        return this.crearResultadoValidacion(erroresLimpios);
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | BLOQUEO DE ACCIONES
                    |--------------------------------------------------------------------------
                    | Ayuda a evitar doble click visual en acciones sensibles.
                    | Livewire ya deshabilita con wire:loading, pero esto agrega protección UI.
                    |--------------------------------------------------------------------------
                    */

                    bloquearAccion(nombreAccion, delay = 700) {
                        if (this.state.lockAccion && this.state.ultimaAccion === nombreAccion) {
                            return false;
                        }

                        this.state.lockAccion = true;
                        this.state.ultimaAccion = nombreAccion;

                        setTimeout(() => {
                            this.state.lockAccion = false;
                            this.state.ultimaAccion = null;
                        }, delay);

                        return true;
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | TEMA PARA GRÁFICOS
                    |--------------------------------------------------------------------------
                    */

                    cssVar(nombre, fallback = '') {
                        const valor = getComputedStyle(document.documentElement)
                            .getPropertyValue(nombre)
                            .trim();

                        return valor || fallback;
                    },

                    getChartTheme() {
                        return {
                            text: this.cssVar('--ui-text', '#0f172a'),
                            muted: this.cssVar('--ui-muted', '#64748b'),
                            border: this.cssVar('--ui-border', '#e2e8f0'),
                            surface: this.cssVar('--ui-surface', '#ffffff'),
                            surfaceSoft: this.cssVar('--ui-surface-soft', '#f8fafc'),
                            primary: this.cssVar('--ui-primary', '#059669'),
                            info: this.cssVar('--ui-info', '#0284c7'),
                            violet: this.cssVar('--ui-violet', '#7c3aed'),
                            warning: this.cssVar('--ui-warning', '#d97706'),
                            danger: this.cssVar('--ui-danger', '#dc2626'),
                        };
                    },

                    destruirGraficos() {
                        Object.keys(this.charts).forEach((key) => {
                            if (this.charts[key]) {
                                this.charts[key].destroy();
                                this.charts[key] = null;
                            }
                        });
                    },

                    getChartData() {
                        return {
                            horarios: {
                                labels: @json($datosGraficos['horarios']['labels'] ?? []),
                                data: @json($datosGraficos['horarios']['data'] ?? []),
                            },
                            planAcademico: {
                                labels: @json($datosGraficos['plan_academico']['labels'] ?? []),
                                data: @json($datosGraficos['plan_academico']['data'] ?? []),
                            },
                            inscritos: {
                                labels: @json($datosGraficos['inscritos']['labels'] ?? []),
                                data: @json($datosGraficos['inscritos']['data'] ?? []),
                            },
                        };
                    },

                    tieneDatosValidos(dataset) {
                        if (!dataset || !Array.isArray(dataset.data)) {
                            return false;
                        }

                        return dataset.data.some((valor) => Number(valor) > 0);
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | GRÁFICOS
                    |--------------------------------------------------------------------------
                    */

                    iniciarGraficos() {
                        const horariosCanvas = document.getElementById(this.config.chartIds.horarios);
                        const planCanvas = document.getElementById(this.config.chartIds.planAcademico);

                        if (!window.Chart) {
                            console.warn('[Gestión Cursos] Chart.js no está disponible.');
                            return;
                        }

                        if (!horariosCanvas && !planCanvas) {
                            return;
                        }

                        const theme = this.getChartTheme();
                        const data = this.getChartData();

                        this.destruirGraficos();

                        if (horariosCanvas) {
                            this.iniciarGraficoHorarios(horariosCanvas, data.horarios, theme);
                        }

                        if (planCanvas) {
                            this.iniciarGraficoPlanAcademico(planCanvas, data.planAcademico, theme);
                        }
                    },

                    iniciarGraficoHorarios(canvas, dataset, theme) {
                        const tieneDatos = this.tieneDatosValidos(dataset);

                        const labels = tieneDatos
                            ? dataset.labels
                            : ['Sin horarios', 'Pendiente'];

                        const values = tieneDatos
                            ? dataset.data
                            : [0, 1];

                        this.charts.horarios = new Chart(canvas, {
                            type: 'doughnut',
                            data: {
                                labels,
                                datasets: [{
                                    data: values,
                                    backgroundColor: [
                                        theme.primary,
                                        theme.warning,
                                    ],
                                    borderColor: theme.surface,
                                    borderWidth: 3,
                                    hoverOffset: 8,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '68%',
                                animation: {
                                    animateRotate: true,
                                    animateScale: true,
                                    duration: 650,
                                    easing: 'easeOutQuart',
                                },
                                plugins: {
                                    tooltip: {
                                        enabled: true,
                                        backgroundColor: theme.text,
                                        titleColor: theme.surface,
                                        bodyColor: theme.surface,
                                        padding: 12,
                                        cornerRadius: 12,
                                        displayColors: true,
                                    },
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            color: theme.muted,
                                            usePointStyle: true,
                                            pointStyle: 'circle',
                                            padding: 16,
                                            boxWidth: 8,
                                            boxHeight: 8,
                                            font: {
                                                size: 12,
                                                weight: '600',
                                            },
                                        },
                                    },
                                },
                            },
                        });
                    },

                    iniciarGraficoPlanAcademico(canvas, dataset, theme) {
                        const tieneDatos = this.tieneDatosValidos(dataset);

                        const labels = tieneDatos && dataset.labels?.length
                            ? dataset.labels
                            : ['Plan asignatura', 'Plan especialidad', 'Horarios'];

                        const values = tieneDatos
                            ? dataset.data
                            : [0, 0, 0];

                        this.charts.planAcademico = new Chart(canvas, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [{
                                    label: 'Cursos',
                                    data: values,
                                    backgroundColor: [
                                        theme.info,
                                        theme.violet,
                                        theme.primary,
                                    ],
                                    borderColor: [
                                        theme.info,
                                        theme.violet,
                                        theme.primary,
                                    ],
                                    borderWidth: 1,
                                    borderRadius: 12,
                                    maxBarThickness: 48,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: {
                                    duration: 650,
                                    easing: 'easeOutQuart',
                                },
                                plugins: {
                                    tooltip: {
                                        enabled: true,
                                        backgroundColor: theme.text,
                                        titleColor: theme.surface,
                                        bodyColor: theme.surface,
                                        padding: 12,
                                        cornerRadius: 12,
                                        displayColors: false,
                                    },
                                    legend: {
                                        display: false,
                                    },
                                },
                                scales: {
                                    x: {
                                        ticks: {
                                            color: theme.muted,
                                            font: {
                                                size: 11,
                                                weight: '600',
                                            },
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
                                            stepSize: 1,
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
                    },

                    refrescarGraficos(delay = 120) {
                        clearTimeout(this.timers.chartRefresh);

                        this.timers.chartRefresh = setTimeout(() => {
                            this.requestFrame(() => {
                                this.iniciarGraficos();
                            });
                        }, delay);
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | SWEETALERT / UI HELPERS
                    |--------------------------------------------------------------------------
                    */

                    toast(icon = 'success', title = 'Acción realizada', timer = 2200) {
                        if (window.uiHelpers?.toast) {
                            window.uiHelpers.toast({ icon, title, timer });
                            return;
                        }

                        if (window.Swal) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon,
                                title,
                                showConfirmButton: false,
                                timer,
                                timerProgressBar: true,
                            });
                        }
                    },

                    alert({ icon = 'info', title = 'Aviso', text = '', html = '' }) {
                        if (!window.Swal) {
                            alert(text || title);
                            return;
                        }

                        Swal.fire({
                            icon,
                            title,
                            text: html ? undefined : text,
                            html: html || undefined,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: icon === 'error'
                                ? '#dc2626'
                                : icon === 'warning'
                                    ? '#d97706'
                                    : '#059669',
                        });
                    },

                    confirm({
                        title = '¿Confirmar acción?',
                        text = 'Esta acción modificará información del sistema.',
                        icon = 'warning',
                        confirmButtonText = 'Sí, confirmar',
                        confirmButtonColor = '#059669',
                        onConfirm = null,
                    }) {
                        if (window.uiHelpers?.confirm) {
                            window.uiHelpers.confirm({
                                title,
                                text,
                                icon,
                                confirmButtonText,
                                confirmButtonColor,
                                onConfirm,
                            });
                            return;
                        }

                        if (!window.Swal) {
                            if (confirm(text || title) && typeof onConfirm === 'function') {
                                onConfirm();
                            }

                            return;
                        }

                        Swal.fire({
                            title,
                            text,
                            icon,
                            showCancelButton: true,
                            confirmButtonText,
                            cancelButtonText: 'Cancelar',
                            confirmButtonColor,
                            cancelButtonColor: '#64748b',
                            reverseButtons: true,
                        }).then((result) => {
                            if (result.isConfirmed && typeof onConfirm === 'function') {
                                onConfirm();
                            }
                        });
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | EVENTOS LIVEWIRE / NAVEGACIÓN / TEMA
                    |--------------------------------------------------------------------------
                    */

                    registrarEventos() {
                        if (this.state.eventosRegistrados || window.__gestionCursoEventosRegistrados) {
                            return;
                        }

                        this.state.eventosRegistrados = true;
                        window.__gestionCursoEventosRegistrados = true;

                        document.addEventListener('DOMContentLoaded', () => {
                            this.refrescarGraficos(180);
                        });

                        document.addEventListener('livewire:navigated', () => {
                            this.refrescarGraficos(180);
                        });

                        document.addEventListener('livewire:initialized', () => {
                            this.refrescarGraficos(180);
                        });

                        /*
                        |--------------------------------------------------------------------------
                        | Livewire v3 usa morph/update según contexto.
                        | Registramos varios eventos de forma segura porque algunos proyectos
                        | disparan uno u otro dependiendo de navegación, wire:navigate y morph.
                        |--------------------------------------------------------------------------
                        */

                        document.addEventListener('livewire:update', () => {
                            this.refrescarGraficos(180);
                        });

                        document.addEventListener('livewire:morph.updated', () => {
                            this.refrescarGraficos(180);
                        });

                        window.addEventListener('theme-changed', () => {
                            this.refrescarGraficos(120);
                        });

                        window.addEventListener('actualizar-graficos-cursos', () => {
                            this.refrescarGraficos(120);
                        });

                        window.addEventListener('resize', () => {
                            this.refrescarGraficos(250);
                        });

                        /*
                        |--------------------------------------------------------------------------
                        | Eventos del módulo Curso
                        |--------------------------------------------------------------------------
                        */

                        window.addEventListener('curso-creado', () => {
                            this.toast('success', 'Curso registrado correctamente.');
                            this.refrescarGraficos(180);
                        });

                        window.addEventListener('curso-actualizado', () => {
                            this.toast('success', 'Curso actualizado correctamente.');
                            this.refrescarGraficos(180);
                        });

                        window.addEventListener('curso-desactivado', () => {
                            this.toast('success', 'Curso desactivado correctamente.');
                            this.refrescarGraficos(180);
                        });

                        window.addEventListener('curso-reactivado', () => {
                            this.toast('success', 'Curso reactivado correctamente.');
                            this.refrescarGraficos(180);
                        });

                        /*
                        |--------------------------------------------------------------------------
                        | Eventos del horario normalizado
                        |--------------------------------------------------------------------------
                        | La clase real se guarda en horario_detalle.
                        |--------------------------------------------------------------------------
                        */

                        window.addEventListener('clase-horario-creada', () => {
                            this.toast('success', 'Clase agregada correctamente al horario.');
                            this.refrescarGraficos(180);
                        });

                        window.addEventListener('clase-horario-quitada', () => {
                            this.toast('success', 'Clase retirada correctamente del horario.');
                            this.refrescarGraficos(180);
                        });

                        /*
                        |--------------------------------------------------------------------------
                        | Eventos generales
                        |--------------------------------------------------------------------------
                        */

                        window.addEventListener('error-general', (event) => {
                            const mensaje = event.detail?.mensaje || 'Ocurrió un problema inesperado.';

                            this.alert({
                                icon: 'error',
                                title: 'No se pudo completar la acción',
                                text: mensaje,
                            });
                        });

                        window.addEventListener('warning-general', (event) => {
                            const mensaje = event.detail?.mensaje || 'Revisa la información antes de continuar.';

                            this.alert({
                                icon: 'warning',
                                title: 'Atención',
                                text: mensaje,
                            });
                        });

                        window.addEventListener('success-general', (event) => {
                            const mensaje = event.detail?.mensaje;

                            if (mensaje) {
                                this.toast('success', mensaje);
                            }
                        });
                    },

                    /*
                    |--------------------------------------------------------------------------
                    | INIT
                    |--------------------------------------------------------------------------
                    */

                    init() {
                        this.registrarEventos();
                        this.refrescarGraficos(160);
                    },
                };

                /*
                |--------------------------------------------------------------------------
                | API GLOBAL PARA BLADE / ALPINE
                |--------------------------------------------------------------------------
                | Estas funciones se consumen desde los x-data de los modales.
                |--------------------------------------------------------------------------
                */

                window.CursoUI = CursoUI;

                window.obtenerValorCurso = function (root, field) {
                    return CursoUI.obtenerValor(root, field);
                };

                window.marcarCampoCurso = function (root, field, tieneError) {
                    return CursoUI.marcarCampo(root, field, tieneError);
                };

                window.validarCursoForm = function (root) {
                    return CursoUI.validarCursoForm(root);
                };

                window.validarClaseHorarioForm = function (root) {
                    return CursoUI.validarClaseHorarioForm(root);
                };

                window.getCursoChartTheme = function () {
                    return CursoUI.getChartTheme();
                };

                window.iniciarGraficosGestionCursos = function () {
                    return CursoUI.iniciarGraficos();
                };

                window.refrescarGraficosGestionCursos = function (delay = 120) {
                    return CursoUI.refrescarGraficos(delay);
                };

                window.confirmarAccionCurso = function (options = {}) {
                    return CursoUI.confirm(options);
                };

                CursoUI.init();
            })();
        </script>
    @endpush
</div>