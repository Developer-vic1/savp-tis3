<div x-data x-on:curso-creado.window="
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
            text: 'La información del curso fue actualizada correctamente.',
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
    " x-on:materia-planificada.window="
        Swal.fire({
            icon: 'success',
            title: 'Materias asignadas',
            text: 'Las materias fueron vinculadas correctamente al curso seleccionado.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " x-on:materia-quitada.window="
        Swal.fire({
            icon: 'success',
            title: 'Materia retirada',
            text: 'La materia fue retirada de la planificación del curso.',
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
        select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: none !important;
        }

        select::-ms-expand {
            display: none;
        }

        [x-cloak] {
            display: none !important;
        }

        .curso-stat-icon {
            background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft));
        }

        .curso-hero-illustration {
            background:
                radial-gradient(circle at 20% 20%, var(--ui-primary-soft), transparent 34%),
                radial-gradient(circle at 80% 30%, var(--ui-info-soft), transparent 34%),
                linear-gradient(135deg, var(--ui-surface), var(--ui-surface-soft));
        }

        .curso-modal-backdrop-diffuse {
            background:
                radial-gradient(circle at 20% 20%, rgba(5, 150, 105, 0.22), transparent 32%),
                radial-gradient(circle at 80% 18%, rgba(2, 132, 199, 0.20), transparent 34%),
                radial-gradient(circle at 50% 85%, rgba(124, 58, 237, 0.16), transparent 38%),
                rgba(15, 23, 42, 0.58);
            backdrop-filter: blur(14px);
        }

        html.dark .curso-modal-backdrop-diffuse {
            background:
                radial-gradient(circle at 20% 20%, rgba(52, 211, 153, 0.18), transparent 32%),
                radial-gradient(circle at 80% 18%, rgba(56, 189, 248, 0.16), transparent 34%),
                radial-gradient(circle at 50% 85%, rgba(167, 139, 250, 0.14), transparent 38%),
                rgba(2, 6, 23, 0.74);
            backdrop-filter: blur(16px);
        }

        .curso-soft-gradient {
            background:
                radial-gradient(circle at 15% 15%, var(--ui-primary-soft), transparent 34%),
                radial-gradient(circle at 85% 25%, var(--ui-info-soft), transparent 34%),
                radial-gradient(circle at 50% 100%, var(--ui-violet-soft), transparent 38%),
                var(--ui-surface);
        }

        .curso-check-card:has(input:checked) {
            border-color: var(--ui-primary-border) !important;
            background: var(--ui-primary-soft) !important;
            box-shadow: 0 0 0 4px var(--ui-ring);
        }
    </style>

    {{-- ENCABEZADO INSTITUCIONAL --}}
    <section class="ui-card overflow-hidden rounded-[2rem]">
        <div class="grid gap-0 xl:grid-cols-12">
            <div class="p-6 xl:col-span-8">
                <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 ring-1"
                    style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                    <span class="h-2 w-2 rounded-full" style="background: var(--ui-primary);"></span>
                    <p class="text-xs font-black uppercase tracking-[0.16em]">
                        Estructura académica institucional
                    </p>
                </div>

                <h2 class="mt-4 text-3xl font-black tracking-tight md:text-4xl" style="color: var(--ui-text);">
                    Gestión de Cursos
                </h2>

                <p class="mt-3 max-w-3xl text-sm leading-6" style="color: var(--ui-muted);">
                    Administra los niveles académicos oficiales de la institución y su relación con la planificación
                    curricular, materias, gestión académica, paralelos, turnos e inscripciones estudiantiles.
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
                        onclick="document.getElementById('seccion-planificacion-cursos')?.scrollIntoView({ behavior: 'smooth', block: 'start' })"
                        class="ui-btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4.5h6m2.25 3H6.75A2.25 2.25 0 0 1 4.5 17.25V6.75A2.25 2.25 0 0 1 6.75 4.5h7.5L19.5 9.75v7.5A2.25 2.25 0 0 1 17.25 19.5Z" />
                        </svg>
                        Ver planificación
                    </button>
                </div>
            </div>

            <div class="curso-hero-illustration flex items-center justify-center border-t p-6 xl:col-span-4 xl:border-l xl:border-t-0"
                style="border-color: var(--ui-border);">
                <div class="w-full max-w-sm">
                    <div class="rounded-[2rem] border p-5 shadow-sm"
                        style="background: var(--ui-surface); border-color: var(--ui-border); box-shadow: var(--ui-shadow-sm);">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.16em]"
                                    style="color: var(--ui-muted);">
                                    Gestión activa
                                </p>

                                <h3 class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                    @if ($gestionActiva)
                                        Gestión {{ $gestionActiva->ani_gea ?? 'actual' }}
                                    @else
                                        Sin gestión activa
                                    @endif
                                </h3>
                            </div>

                            <div class="flex h-14 w-14 items-center justify-center rounded-2xl ring-1"
                                style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 8.25h18M4.5 6.75h15A1.5 1.5 0 0 1 21 8.25v10.5A2.25 2.25 0 0 1 18.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A1.5 1.5 0 0 1 4.5 6.75Z" />
                                </svg>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-2xl border p-3"
                                style="border-color: var(--ui-border); background: var(--ui-surface-soft);">
                                <p class="text-xs" style="color: var(--ui-muted);">Cursos</p>
                                <p class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                    {{ $totalCursos }}
                                </p>
                            </div>

                            <div class="rounded-2xl border p-3"
                                style="border-color: var(--ui-border); background: var(--ui-surface-soft);">
                                <p class="text-xs" style="color: var(--ui-muted);">Inscritos</p>
                                <p class="mt-1 text-xl font-black" style="color: var(--ui-text);">
                                    {{ $totalInscritos }}
                                </p>
                            </div>
                        </div>

                        @unless ($gestionActiva)
                            <div class="ui-alert-warning mt-4">
                                No existe una gestión académica activa. La planificación de materias puede quedar limitada.
                            </div>
                        @endunless
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CARDS RESUMEN --}}
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <div class="ui-card ui-card-hover p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                        Cursos registrados
                    </p>
                    <h3 class="mt-3 text-4xl font-black" style="color: var(--ui-text);">
                        {{ $totalCursos }}
                    </h3>
                    <p class="mt-2 text-sm leading-5" style="color: var(--ui-muted);">
                        Niveles académicos oficiales configurados.
                    </p>
                </div>

                <div class="curso-stat-icon flex h-12 w-12 items-center justify-center rounded-2xl ring-1"
                    style="color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6.75v10.5m-6-8.25h12M4.5 5.25A2.25 2.25 0 0 1 6.75 3h10.5A2.25 2.25 0 0 1 19.5 5.25v13.5A2.25 2.25 0 0 1 17.25 21H6.75A2.25 2.25 0 0 1 4.5 18.75V5.25Z" />
                    </svg>
                </div>
            </div>

            <div class="mt-4">
                <span class="ui-badge-info">Catálogo base</span>
            </div>
        </div>

        <div class="ui-card ui-card-hover p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                        Cursos activos
                    </p>
                    <h3 class="mt-3 text-4xl font-black" style="color: var(--ui-text);">
                        {{ $totalActivos }}
                    </h3>
                    <p class="mt-2 text-sm leading-5" style="color: var(--ui-muted);">
                        Disponibles para inscripción y planificación.
                    </p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-2xl ring-1"
                    style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>

            <div class="mt-4">
                <span class="ui-badge-success">Activos</span>
            </div>
        </div>

        <div class="ui-card ui-card-hover p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                        Con materias
                    </p>
                    <h3 class="mt-3 text-4xl font-black" style="color: var(--ui-text);">
                        {{ $totalConMaterias }}
                    </h3>
                    <p class="mt-2 text-sm leading-5" style="color: var(--ui-muted);">
                        Cursos con estructura curricular definida.
                    </p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-2xl ring-1"
                    style="background: var(--ui-violet-soft); color: var(--ui-violet); --tw-ring-color: var(--ui-violet-border);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M10.125 2.25h3.75A2.25 2.25 0 0 1 16.125 4.5v15a2.25 2.25 0 0 1-2.25 2.25h-3.75A2.25 2.25 0 0 1 7.875 19.5v-15a2.25 2.25 0 0 1 2.25-2.25ZM19.5 5.625v12.75M4.5 5.625v12.75" />
                    </svg>
                </div>
            </div>

            <div class="mt-4">
                <span class="ui-badge-violet">Planificados</span>
            </div>
        </div>

        <div class="ui-card ui-card-hover p-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                        Sin planificación
                    </p>
                    <h3 class="mt-3 text-4xl font-black" style="color: var(--ui-text);">
                        {{ $totalSinMaterias }}
                    </h3>
                    <p class="mt-2 text-sm leading-5" style="color: var(--ui-muted);">
                        Requieren asignación de materias.
                    </p>
                </div>

                <div class="flex h-12 w-12 items-center justify-center rounded-2xl ring-1"
                    style="background: var(--ui-warning-soft); color: var(--ui-warning); --tw-ring-color: var(--ui-warning-border);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m0 3.75h.008v.008H12V16.5Zm9-4.5a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>

            <div class="mt-4">
                <span class="ui-badge-warning">Pendientes</span>
            </div>
        </div>
    </section>

    {{-- ALERTA INSTITUCIONAL --}}
    <section class="ui-alert-info">
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
                <p class="font-black">
                    Los cursos son catálogos académicos base.
                </p>
                <p class="mt-1 text-sm leading-6">
                    La asignación de materias se realiza por gestión académica mediante el plan de asignatura.
                    Esto evita duplicidad, conserva historial institucional y permite cerrar gestiones con mayor
                    control.
                </p>
            </div>
        </div>
    </section>

    {{-- FILTROS --}}
    <section class="ui-card rounded-[2rem] p-5">
        <div class="grid gap-4 xl:grid-cols-12">
            <div class="xl:col-span-3">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">
                    Buscar curso
                </label>

                <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 shadow-sm transition focus-within:ring-4"
                    style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-text); --tw-ring-color: var(--ui-ring);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" style="color: var(--ui-muted);"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                    </svg>

                    <input type="text" wire:model.live.debounce.400ms="search" placeholder="Buscar por curso o nivel..."
                        class="w-full border-0 bg-transparent p-0 text-sm focus:outline-none focus:ring-0"
                        style="color: var(--ui-text);">
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Estado</label>
                <div class="relative">
                    <select wire:model.live="estado" class="ui-select pr-10">
                        <option value="">Todos</option>
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                        style="color: var(--ui-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Nivel académico</label>
                <div class="relative">
                    <select wire:model.live="nivel" class="ui-select pr-10">
                        <option value="">Todos</option>
                        @foreach ($nivelesDisponibles as $nivelItem)
                            <option value="{{ $nivelItem }}">{{ $nivelItem }}</option>
                        @endforeach
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                        style="color: var(--ui-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Gestión</label>
                <div class="relative">
                    <select wire:model.live="gestionFiltro" class="ui-select pr-10">
                        <option value="">Gestión activa</option>
                        @foreach ($gestiones as $gestion)
                            <option value="{{ $gestion->cod_gea }}">
                                Gestión {{ $gestion->ani_gea ?? 'sin año' }} - {{ $gestion->est_gea ?? 'Sin estado' }}
                            </option>
                        @endforeach
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                        style="color: var(--ui-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Planificación</label>
                <div class="relative">
                    <select wire:model.live="planificacion" class="ui-select pr-10">
                        <option value="">Todos</option>
                        <option value="con_materias">Con materias</option>
                        <option value="sin_materias">Sin materias</option>
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                        style="color: var(--ui-muted);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-1">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">Acción</label>
                <button type="button" wire:click="limpiarFiltros" class="ui-btn-secondary w-full px-3"
                    title="Limpiar filtros">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </section>

    {{-- CONTENIDO PRINCIPAL --}}
    <section class="grid gap-6 xl:grid-cols-12">
        {{-- TABLA --}}
        <div class="xl:col-span-8">
            <div class="ui-table-wrap">
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
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="ui-table">
                        <thead>
                            <tr>
                                <th>Curso</th>
                                <th>Nivel académico</th>
                                <th>Orden</th>
                                <th>Materias</th>
                                <th>Inscritos</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($cursos as $curso)
                                <tr wire:key="curso-{{ $curso['cod_cur'] }}">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl font-black ring-1"
                                                style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                                                {{ $curso['ord_cur'] }}°
                                            </div>

                                            <div class="min-w-0">
                                                <p class="truncate text-sm font-black" style="color: var(--ui-text);">
                                                    {{ $curso['nom_cur'] }}
                                                </p>

                                                <p class="mt-0.5 truncate text-xs" style="color: var(--ui-muted);">
                                                    {{ $curso['des_cur'] ?: 'Sin descripción institucional registrada.' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="ui-badge-info">{{ $curso['niv_cur'] }}</span>
                                    </td>

                                    <td>
                                        <p class="text-sm font-black" style="color: var(--ui-text);">
                                            {{ $curso['ord_cur'] }}
                                        </p>
                                    </td>

                                    <td>
                                        @if ($curso['materias_count'] > 0)
                                            <span class="ui-badge-success">{{ $curso['materias_count'] }} materias</span>
                                        @else
                                            <span class="ui-badge-warning">Sin materias</span>
                                        @endif
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
                                                title="Ver detalle">
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

                                            <button type="button" wire:click="abrirModalPlanificar(@js($curso['cod_cur']))"
                                                wire:loading.attr="disabled" wire:target="abrirModalPlanificar"
                                                class="ui-icon-btn disabled:cursor-wait disabled:opacity-60"
                                                title="Planificar materias">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M10.125 2.25h3.75A2.25 2.25 0 0 1 16.125 4.5v15a2.25 2.25 0 0 1-2.25 2.25h-3.75A2.25 2.25 0 0 1 7.875 19.5v-15a2.25 2.25 0 0 1 2.25-2.25ZM19.5 5.625v12.75M4.5 5.625v12.75" />
                                                </svg>
                                            </button>

                                            @if ($curso['est_cur'] === 'ACTIVO')
                                                <button type="button" onclick="window.uiHelpers.confirm({
                                                                title: '¿Desactivar curso?',
                                                                text: 'El curso no será eliminado, solo quedará inactivo para nuevas operaciones.',
                                                                icon: 'warning',
                                                                confirmButtonText: 'Sí, desactivar',
                                                                confirmButtonColor: '#d97706',
                                                                onConfirm: () => $wire.desactivarCurso(@js($curso['cod_cur']))
                                                            })" class="ui-icon-btn" title="Desactivar curso">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M18 12H6m15 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                    </svg>
                                                </button>
                                            @else
                                                <button type="button" onclick="window.uiHelpers.confirm({
                                                                title: '¿Reactivar curso?',
                                                                text: 'El curso volverá a estar disponible para planificación académica.',
                                                                icon: 'question',
                                                                confirmButtonText: 'Sí, reactivar',
                                                                confirmButtonColor: '#059669',
                                                                onConfirm: () => $wire.reactivarCurso(@js($curso['cod_cur']))
                                                            })" class="ui-icon-btn" title="Reactivar curso">
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
                                    <td colspan="7" class="px-6 py-16 text-center">
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
                            Estado curricular
                        </p>
                        <h3 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                            Planificación de cursos
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
                    <canvas id="chartCursosPlanificacion"></canvas>
                </div>
            </div>

            <div class="ui-card rounded-[2rem] p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                            Inscripción
                        </p>
                        <h3 class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                            Estudiantes por curso
                        </h3>
                    </div>

                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl ring-1"
                        style="background: var(--ui-info-soft); color: var(--ui-info); --tw-ring-color: var(--ui-info-border);">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18 18.72a9.094 9.094 0 0 0 3.75-1.32 4.5 4.5 0 0 0-7.5-3.35M6 18.72a9.094 9.094 0 0 1-3.75-1.32 4.5 4.5 0 0 1 7.5-3.35M15 7.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        </svg>
                    </div>
                </div>

                <div wire:ignore class="mt-4 h-64 rounded-2xl p-3" style="background: var(--ui-surface-soft);">
                    <canvas id="chartCursosInscritos"></canvas>
                </div>
            </div>
        </aside>
    </section>

    {{-- SECCIÓN PLANIFICACIÓN --}}
    <section id="seccion-planificacion-cursos" class="ui-card rounded-[2rem] p-5">
        <div class="grid gap-5 xl:grid-cols-12">
            <div class="xl:col-span-7">
                <p class="text-xs font-bold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                    Planificación curricular
                </p>

                <h3 class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                    Asignación múltiple de materias por curso
                </h3>

                <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                    Selecciona un curso y asigna varias materias de una sola vez. Todas quedarán vinculadas mediante
                    el plan de asignatura de la gestión académica seleccionada.
                </p>
            </div>

            <div class="xl:col-span-5">
                <div class="ui-alert-warning">
                    <p class="font-black">Control académico recomendado</p>
                    <p class="mt-1 text-sm leading-5">
                        Si un curso no tiene materias planificadas en la gestión activa, aparecerá como pendiente.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- MODAL CREAR --}}
    @if ($modalCrear)
        <div wire:key="modal-crear-curso" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="absolute inset-0 curso-modal-backdrop-diffuse" wire:click="cerrarModalCrear"></div>

            <div x-data="{ validacion: { valido: false, errores: {}, lista: [] } }"
                x-init="$nextTick(() => validacion = validarCursoForm($root))" @input="validacion = validarCursoForm($root)"
                @change="validacion = validarCursoForm($root)" class="ui-modal w-full max-w-3xl">
                <div class="curso-soft-gradient border-b px-6 py-5" style="border-color: var(--ui-border);">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em]" style="color: var(--ui-primary);">
                                Nuevo curso
                            </p>
                            <h3 class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                Registrar curso
                            </h3>
                            <p class="mt-2 text-sm" style="color: var(--ui-muted);">
                                Define un nivel académico oficial para la estructura institucional.
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarModalCrear" class="ui-icon-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[72vh] overflow-y-auto px-6 py-6 ui-scrollbar">
                    <div x-show="!validacion.valido" x-cloak class="ui-alert-danger mb-5">
                        <p class="font-black">Revisa los datos antes de guardar</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-xs leading-5">
                            <template x-for="error in validacion.lista" :key="error">
                                <li x-text="error"></li>
                            </template>
                        </ul>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="ui-label">Nombre del curso <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="form.nom_cur" data-field="nombreCurso" maxlength="120"
                                autocomplete="off" class="ui-input" placeholder="Ej. 1ro de Secundaria">

                            <p x-show="validacion.errores.nombreCurso" x-cloak x-text="validacion.errores.nombreCurso"
                                class="ui-error"></p>

                            @error('form.nom_cur')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Orden académico <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="form.ord_cur" data-field="ordenCurso" min="1" max="20"
                                autocomplete="off" class="ui-input" placeholder="Ej. 1">

                            <p x-show="validacion.errores.ordenCurso" x-cloak x-text="validacion.errores.ordenCurso"
                                class="ui-error"></p>

                            @error('form.ord_cur')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Nivel académico <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select wire:model="form.niv_cur" data-field="nivelCurso" class="ui-select pr-10">
                                    <option value="">Seleccionar nivel</option>
                                    @foreach ($nivelesDisponibles as $nivelItem)
                                        <option value="{{ $nivelItem }}">{{ $nivelItem }}</option>
                                    @endforeach
                                </select>

                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                                    style="color: var(--ui-muted);">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <p x-show="validacion.errores.nivelCurso" x-cloak x-text="validacion.errores.nivelCurso"
                                class="ui-error"></p>

                            @error('form.niv_cur')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Estado <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select wire:model="form.est_cur" data-field="estadoCurso" class="ui-select pr-10">
                                    <option value="ACTIVO">Activo</option>
                                    <option value="INACTIVO">Inactivo</option>
                                </select>

                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                                    style="color: var(--ui-muted);">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <p x-show="validacion.errores.estadoCurso" x-cloak x-text="validacion.errores.estadoCurso"
                                class="ui-error"></p>

                            @error('form.est_cur')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="ui-label">Descripción institucional</label>
                            <textarea wire:model="form.des_cur" maxlength="255" rows="4" class="ui-textarea"
                                placeholder="Describe brevemente el propósito académico del curso..."></textarea>

                            @error('form.des_cur')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror

                            <p class="ui-help">
                                Este texto ayuda a contextualizar el curso para reportes y seguimiento institucional.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <p x-show="!validacion.valido" x-cloak class="text-sm font-medium sm:mr-auto"
                        style="color: var(--ui-danger);">
                        Corrige los campos marcados antes de guardar.
                    </p>

                    <button type="button" wire:click="cerrarModalCrear" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button" wire:click="guardarCurso" wire:loading.attr="disabled" wire:target="guardarCurso"
                        :disabled="!validacion.valido" :class="validacion.valido
                                ? 'ui-btn-primary'
                                : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none'"
                        class="rounded-2xl px-5 py-3 text-sm font-semibold transition">
                        <span wire:loading.remove wire:target="guardarCurso">Guardar curso</span>
                        <span wire:loading wire:target="guardarCurso">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL EDITAR --}}
    @if ($modalEditar)
        <div wire:key="modal-editar-curso" class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="absolute inset-0 curso-modal-backdrop-diffuse" wire:click="cerrarModalEditar"></div>

            <div x-data="{ validacion: { valido: false, errores: {}, lista: [] } }"
                x-init="$nextTick(() => validacion = validarCursoForm($root))" @input="validacion = validarCursoForm($root)"
                @change="validacion = validarCursoForm($root)" class="ui-modal w-full max-w-3xl">
                <div class="curso-soft-gradient border-b px-6 py-5" style="border-color: var(--ui-border);">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em]" style="color: var(--ui-info);">
                                Edición académica
                            </p>
                            <h3 class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                Editar curso
                            </h3>
                            <p class="mt-2 text-sm" style="color: var(--ui-muted);">
                                Actualiza la información institucional del curso sin modificar códigos internos.
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarModalEditar" class="ui-icon-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[72vh] overflow-y-auto px-6 py-6 ui-scrollbar">
                    <div x-show="!validacion.valido" x-cloak class="ui-alert-danger mb-5">
                        <p class="font-black">Revisa los datos antes de actualizar</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-xs leading-5">
                            <template x-for="error in validacion.lista" :key="error">
                                <li x-text="error"></li>
                            </template>
                        </ul>
                    </div>

                    <div class="ui-alert-warning mb-5">
                        Si el curso ya tiene inscripciones o materias asignadas, modifica su información con criterio
                        institucional para no afectar la interpretación histórica de reportes.
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <label class="ui-label">Nombre del curso <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="formEditar.nom_cur" data-field="nombreCurso" maxlength="120"
                                autocomplete="off" class="ui-input">

                            <p x-show="validacion.errores.nombreCurso" x-cloak x-text="validacion.errores.nombreCurso"
                                class="ui-error"></p>

                            @error('formEditar.nom_cur')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Orden académico <span class="text-red-500">*</span></label>
                            <input type="number" wire:model="formEditar.ord_cur" data-field="ordenCurso" min="1" max="20"
                                autocomplete="off" class="ui-input">

                            <p x-show="validacion.errores.ordenCurso" x-cloak x-text="validacion.errores.ordenCurso"
                                class="ui-error"></p>

                            @error('formEditar.ord_cur')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Nivel académico <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select wire:model="formEditar.niv_cur" data-field="nivelCurso" class="ui-select pr-10">
                                    <option value="">Seleccionar nivel</option>
                                    @foreach ($nivelesDisponibles as $nivelItem)
                                        <option value="{{ $nivelItem }}">{{ $nivelItem }}</option>
                                    @endforeach
                                </select>

                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                                    style="color: var(--ui-muted);">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <p x-show="validacion.errores.nivelCurso" x-cloak x-text="validacion.errores.nivelCurso"
                                class="ui-error"></p>

                            @error('formEditar.niv_cur')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">Estado <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select wire:model="formEditar.est_cur" data-field="estadoCurso" class="ui-select pr-10">
                                    <option value="ACTIVO">Activo</option>
                                    <option value="INACTIVO">Inactivo</option>
                                </select>

                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                                    style="color: var(--ui-muted);">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m19 9-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>

                            <p x-show="validacion.errores.estadoCurso" x-cloak x-text="validacion.errores.estadoCurso"
                                class="ui-error"></p>

                            @error('formEditar.est_cur')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="ui-label">Descripción institucional</label>
                            <textarea wire:model="formEditar.des_cur" maxlength="255" rows="4"
                                class="ui-textarea"></textarea>

                            @error('formEditar.des_cur')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end">
                    <p x-show="!validacion.valido" x-cloak class="text-sm font-medium sm:mr-auto"
                        style="color: var(--ui-danger);">
                        Corrige los campos marcados antes de guardar.
                    </p>

                    <button type="button" wire:click="cerrarModalEditar" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button" wire:click="actualizarCurso" wire:loading.attr="disabled"
                        wire:target="actualizarCurso" :disabled="!validacion.valido" :class="validacion.valido
                                ? 'ui-btn-primary'
                                : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none'"
                        class="rounded-2xl px-5 py-3 text-sm font-semibold transition">
                        <span wire:loading.remove wire:target="actualizarCurso">Guardar cambios</span>
                        <span wire:loading wire:target="actualizarCurso">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL DETALLE --}}
    @if ($modalDetalle && $cursoDetalle)
        <div wire:key="modal-detalle-curso" class="fixed inset-0 z-50 flex items-center justify-end">
            <div class="absolute inset-0 curso-modal-backdrop-diffuse" wire:click="cerrarModalDetalle"></div>

            <aside class="ui-modal relative z-10 h-full w-full max-w-xl rounded-none border-y-0 border-r-0">
                <div class="flex h-full flex-col">
                    <div class="curso-soft-gradient border-b px-6 py-5" style="border-color: var(--ui-border);">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                                    Detalle del curso
                                </p>

                                <h3 class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                    {{ $cursoDetalle['nom_cur'] }}
                                </h3>
                            </div>

                            <button type="button" wire:click="cerrarModalDetalle" class="ui-icon-btn">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6 ui-scrollbar">
                        <div class="ui-card-soft p-4">
                            <div class="flex items-center gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-3xl text-xl font-black ring-1"
                                    style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                                    {{ $cursoDetalle['ord_cur'] }}°
                                </div>

                                <div>
                                    <h4 class="text-lg font-black" style="color: var(--ui-text);">
                                        {{ $cursoDetalle['nom_cur'] }}
                                    </h4>

                                    <p class="mt-1 text-sm" style="color: var(--ui-muted);">
                                        {{ $cursoDetalle['niv_cur'] }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="ui-card-soft p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                    Orden
                                </p>
                                <p class="mt-2 text-xl font-black" style="color: var(--ui-text);">
                                    {{ $cursoDetalle['ord_cur'] }}
                                </p>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                    Estado
                                </p>
                                <p class="mt-2">
                                    @if ($cursoDetalle['est_cur'] === 'ACTIVO')
                                        <span class="ui-badge-success">Activo</span>
                                    @else
                                        <span class="ui-badge-danger">Inactivo</span>
                                    @endif
                                </p>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                    Materias
                                </p>
                                <p class="mt-2 text-xl font-black" style="color: var(--ui-text);">
                                    {{ $cursoDetalle['materias_count'] }}
                                </p>
                            </div>

                            <div class="ui-card-soft p-4">
                                <p class="text-xs font-bold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                    Inscritos
                                </p>
                                <p class="mt-2 text-xl font-black" style="color: var(--ui-text);">
                                    {{ $cursoDetalle['inscritos_count'] }}
                                </p>
                            </div>
                        </div>

                        <div class="ui-card-soft mt-5 p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                                Descripción
                            </p>
                            <p class="mt-2 text-sm leading-6" style="color: var(--ui-text-soft);">
                                {{ $cursoDetalle['des_cur'] ?: 'Sin descripción institucional registrada.' }}
                            </p>
                        </div>

                        <div class="mt-5">
                            <div class="flex items-center justify-between gap-3">
                                <h4 class="font-black" style="color: var(--ui-text);">
                                    Materias asignadas
                                </h4>

                                <span class="ui-badge-info">
                                    {{ $cursoDetalle['materias_count'] }} registros
                                </span>
                            </div>

                            <div class="mt-3 space-y-2">
                                @forelse ($cursoDetalle['materias'] as $materia)
                                    <div class="flex items-center justify-between gap-3 rounded-2xl border px-4 py-3"
                                        style="background: var(--ui-surface); border-color: var(--ui-border);">
                                        <div>
                                            <p class="text-sm font-bold" style="color: var(--ui-text);">
                                                {{ $materia->materia ?? 'Materia registrada' }}
                                            </p>
                                            <p class="mt-0.5 text-xs" style="color: var(--ui-muted);">
                                                {{ trim($materia->docente ?? '') ?: 'Sin docente visible' }}
                                            </p>
                                        </div>

                                        <span class="ui-badge-success">
                                            {{ $materia->est_pas ?? 'Activo' }}
                                        </span>
                                    </div>
                                @empty
                                    <div class="ui-alert-warning">
                                        Este curso aún no tiene materias asignadas para la gestión seleccionada.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="ui-modal-footer flex flex-col gap-3 sm:flex-row sm:justify-end">
                        <button type="button" wire:click="abrirModalEditar(@js($cursoDetalle['cod_cur']))"
                            class="ui-btn-secondary">
                            Editar curso
                        </button>

                        <button type="button" wire:click="abrirModalPlanificar(@js($cursoDetalle['cod_cur']))"
                            class="ui-btn-primary">
                            Planificar materias
                        </button>
                    </div>
                </div>
            </aside>
        </div>
    @endif

    {{-- MODAL PLANIFICAR --}}
    @if ($modalPlanificar && $cursoDetalle)
        <div wire:key="modal-planificar-curso"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="absolute inset-0 curso-modal-backdrop-diffuse" wire:click="cerrarModalPlanificar"></div>

            <div x-data="{ validacion: { valido: false, errores: {}, lista: [] } }"
                x-init="$nextTick(() => validacion = validarPlanCursoForm($root))"
                @input="validacion = validarPlanCursoForm($root)" @change="validacion = validarPlanCursoForm($root)"
                class="ui-modal w-full max-w-6xl">
                <div class="curso-soft-gradient border-b px-6 py-5" style="border-color: var(--ui-border);">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.18em]" style="color: var(--ui-violet);">
                                Planificación curricular
                            </p>
                            <h3 class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                Seleccionar materias del curso
                            </h3>
                            <p class="mt-2 text-sm" style="color: var(--ui-muted);">
                                Curso seleccionado: {{ $cursoDetalle['nom_cur'] }}
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarModalPlanificar" class="ui-icon-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[72vh] overflow-y-auto px-6 py-6 ui-scrollbar">
                    <div class="grid gap-6 xl:grid-cols-12">
                        {{-- FORM PLAN --}}
                        <div class="xl:col-span-5">
                            <div class="ui-card-soft p-4">
                                <h4 class="font-black" style="color: var(--ui-text);">
                                    Asignar materias en bloque
                                </h4>

                                <p class="mt-1 text-sm leading-6" style="color: var(--ui-muted);">
                                    Selecciona varias materias. Todas se asignarán al curso, docente, carga horaria y
                                    gestión elegida.
                                </p>

                                <div x-show="!validacion.valido" x-cloak class="ui-alert-danger mt-4">
                                    <p class="font-black">Completa la planificación</p>
                                    <ul class="mt-2 list-disc space-y-1 pl-5 text-xs leading-5">
                                        <template x-for="error in validacion.lista" :key="error">
                                            <li x-text="error"></li>
                                        </template>
                                    </ul>
                                </div>

                                <div class="mt-5 grid gap-4">
                                    <div>
                                        <label class="ui-label">
                                            Gestión académica <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <select wire:model="formPlan.cod_gea" data-field="gestionPlan"
                                                class="ui-select pr-10">
                                                <option value="">Seleccionar gestión</option>
                                                @foreach ($gestiones as $gestion)
                                                    <option value="{{ $gestion->cod_gea }}">
                                                        Gestión {{ $gestion->ani_gea ?? 'sin año' }} -
                                                        {{ $gestion->est_gea ?? 'Sin estado' }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                                                style="color: var(--ui-muted);">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m19 9-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <p x-show="validacion.errores.gestionPlan" x-cloak
                                            x-text="validacion.errores.gestionPlan" class="ui-error"></p>

                                        @error('formPlan.cod_gea')
                                            <p class="ui-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    {{-- SELECCIÓN MÚLTIPLE DE MATERIAS --}}
                                    <div>
                                        <div class="flex items-center justify-between gap-3">
                                            <label class="ui-label mb-0">
                                                Materias que llevará el curso <span class="text-red-500">*</span>
                                            </label>

                                            <span class="rounded-full px-3 py-1 text-xs font-black ring-1"
                                                style="background: var(--ui-info-soft); color: var(--ui-info); --tw-ring-color: var(--ui-info-border);">
                                                Selección múltiple
                                            </span>
                                        </div>

                                        <div data-field="materiasPlan"
                                            class="mt-3 max-h-72 overflow-y-auto rounded-2xl border p-3 ui-scrollbar"
                                            style="background: var(--ui-surface); border-color: var(--ui-border);">

                                            @forelse ($asignaturas as $asignatura)
                                                <label
                                                    class="curso-check-card group mb-2 flex cursor-pointer items-start gap-3 rounded-2xl border p-3 transition last:mb-0 hover:-translate-y-0.5"
                                                    style="background: var(--ui-surface-soft); border-color: var(--ui-border-soft);">
                                                    <input type="checkbox" wire:model="formPlan.materias"
                                                        value="{{ $asignatura->codigo }}"
                                                        class="mt-1 h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">

                                                    <div class="min-w-0 flex-1">
                                                        <p class="text-sm font-black" style="color: var(--ui-text);">
                                                            {{ $asignatura->nombre }}
                                                        </p>

                                                        <p class="mt-1 text-xs leading-5" style="color: var(--ui-muted);">
                                                            Esta materia será vinculada al curso mediante el plan de asignatura.
                                                        </p>
                                                    </div>

                                                    <div class="hidden h-9 w-9 shrink-0 items-center justify-center rounded-xl ring-1 sm:flex"
                                                        style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M10.125 2.25h3.75A2.25 2.25 0 0 1 16.125 4.5v15a2.25 2.25 0 0 1-2.25 2.25h-3.75A2.25 2.25 0 0 1 7.875 19.5v-15a2.25 2.25 0 0 1 2.25-2.25Z" />
                                                        </svg>
                                                    </div>
                                                </label>
                                            @empty
                                                <div class="ui-alert-warning">
                                                    No existen materias registradas para seleccionar.
                                                </div>
                                            @endforelse
                                        </div>

                                        <p x-show="validacion.errores.materiasPlan" x-cloak
                                            x-text="validacion.errores.materiasPlan" class="ui-error"></p>

                                        @error('formPlan.materias')
                                            <p class="ui-error">{{ $message }}</p>
                                        @enderror

                                        @error('formPlan.materias.*')
                                            <p class="ui-error">{{ $message }}</p>
                                        @enderror

                                        <p class="ui-help">
                                            Puedes seleccionar varias materias. Todas se asignarán al curso, docente, carga
                                            horaria y gestión seleccionados.
                                        </p>
                                    </div>

                                    <div>
                                        <label class="ui-label">
                                            Docente <span class="text-red-500">*</span>
                                        </label>
                                        <div class="relative">
                                            <select wire:model="formPlan.cod_doc" data-field="docentePlan"
                                                class="ui-select pr-10">
                                                <option value="">Seleccionar docente</option>
                                                @foreach ($docentes as $docente)
                                                    <option value="{{ $docente->cod_doc }}">
                                                        {{ trim($docente->nombre ?? '') ?: 'Docente institucional' }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                                                style="color: var(--ui-muted);">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m19 9-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>

                                        <p x-show="validacion.errores.docentePlan" x-cloak
                                            x-text="validacion.errores.docentePlan" class="ui-error"></p>

                                        @error('formPlan.cod_doc')
                                            <p class="ui-error">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="ui-label">
                                                Carga horaria <span class="text-red-500">*</span>
                                            </label>
                                            <input type="number" wire:model="formPlan.hor_pas" data-field="horasPlan"
                                                min="1" max="80" class="ui-input" placeholder="Ej. 6">

                                            <p x-show="validacion.errores.horasPlan" x-cloak
                                                x-text="validacion.errores.horasPlan" class="ui-error"></p>

                                            @error('formPlan.hor_pas')
                                                <p class="ui-error">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div>
                                            <label class="ui-label">Estado</label>
                                            <div class="relative">
                                                <select wire:model="formPlan.est_pas" data-field="estadoPlan"
                                                    class="ui-select pr-10">
                                                    <option value="ACTIVO">Activo</option>
                                                    <option value="PLANIFICADO">Planificado</option>
                                                    <option value="INACTIVO">Inactivo</option>
                                                </select>

                                                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                                                    style="color: var(--ui-muted);">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m19 9-7 7-7-7" />
                                                    </svg>
                                                </div>
                                            </div>

                                            @error('formPlan.est_pas')
                                                <p class="ui-error">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>

                                    <button type="button" wire:click="guardarMateriaPlanificada"
                                        wire:loading.attr="disabled" wire:target="guardarMateriaPlanificada"
                                        :disabled="!validacion.valido" :class="validacion.valido
                                                ? 'ui-btn-primary'
                                                : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none'"
                                        class="rounded-2xl px-5 py-3 text-sm font-semibold transition">
                                        <span wire:loading.remove wire:target="guardarMateriaPlanificada">
                                            Asignar materias seleccionadas
                                        </span>
                                        <span wire:loading wire:target="guardarMateriaPlanificada">
                                            Asignando...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- TABLA PLAN --}}
                        <div class="xl:col-span-7">
                            <div class="ui-table-wrap">
                                <div class="border-b p-5" style="border-color: var(--ui-border);">
                                    <h4 class="font-black" style="color: var(--ui-text);">
                                        Materias asignadas a {{ $cursoDetalle['nom_cur'] }}
                                    </h4>

                                    <p class="mt-1 text-sm" style="color: var(--ui-muted);">
                                        Registros asociados a la gestión académica seleccionada.
                                    </p>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="ui-table">
                                        <thead>
                                            <tr>
                                                <th>Materia</th>
                                                <th>Docente</th>
                                                <th>Horas</th>
                                                <th>Estado</th>
                                                <th class="text-center">Acción</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @forelse ($cursoDetalle['materias'] as $materia)
                                                <tr wire:key="plan-materia-{{ $materia->cod_pas }}">
                                                    <td>
                                                        <p class="text-sm font-bold" style="color: var(--ui-text);">
                                                            {{ $materia->materia ?? 'Materia registrada' }}
                                                        </p>
                                                    </td>

                                                    <td>
                                                        <p class="text-sm" style="color: var(--ui-text-soft);">
                                                            {{ trim($materia->docente ?? '') ?: 'Sin docente visible' }}
                                                        </p>
                                                    </td>

                                                    <td>
                                                        <span class="ui-badge-info">
                                                            {{ $materia->hor_pas ?? 0 }} horas
                                                        </span>
                                                    </td>

                                                    <td>
                                                        @if (($materia->est_pas ?? 'ACTIVO') === 'ACTIVO')
                                                            <span class="ui-badge-success">Activo</span>
                                                        @elseif (($materia->est_pas ?? '') === 'PLANIFICADO')
                                                            <span class="ui-badge-warning">Planificado</span>
                                                        @else
                                                            <span class="ui-badge-danger">Inactivo</span>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        <div class="flex justify-center">
                                                            <button type="button" onclick="window.uiHelpers.confirm({
                                                                            title: '¿Quitar materia?',
                                                                            text: 'La materia será retirada de la planificación del curso seleccionado.',
                                                                            icon: 'warning',
                                                                            confirmButtonText: 'Sí, quitar',
                                                                            confirmButtonColor: '#dc2626',
                                                                            onConfirm: () => $wire.quitarMateriaPlanificada(@js($materia->cod_pas))
                                                                        })" class="ui-icon-btn" title="Quitar materia">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5"
                                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                                    stroke-width="1.8">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M19.228 5.79 18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .563c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                                </svg>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="px-6 py-12 text-center">
                                                        <div class="ui-alert-warning mx-auto max-w-md">
                                                            Este curso aún no tiene materias asignadas para la gestión
                                                            seleccionada.
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="ui-alert-info mt-4">
                                Las materias se asignan mediante el plan de asignatura por gestión académica, no
                                directamente en el catálogo de cursos.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ui-modal-footer text-right">
                    <button type="button" wire:click="cerrarModalPlanificar" class="ui-btn-secondary">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            function obtenerValorCurso(root, field) {
                return root.querySelector(`[data-field="${field}"]`)?.value?.trim() ?? '';
            }

            function marcarCampoCurso(root, field, tieneError) {
                const campo = root.querySelector(`[data-field="${field}"]`);

                if (!campo) return;

                campo.classList.toggle('ring-2', tieneError);
                campo.classList.toggle('ring-red-400', tieneError);
                campo.classList.toggle('border-red-400', tieneError);
            }

            function validarCursoForm(root) {
                const errores = {};

                const nombre = obtenerValorCurso(root, 'nombreCurso');
                const orden = obtenerValorCurso(root, 'ordenCurso');
                const nivel = obtenerValorCurso(root, 'nivelCurso');
                const estado = obtenerValorCurso(root, 'estadoCurso');

                if (nombre === '') {
                    errores.nombreCurso = 'El nombre del curso es obligatorio.';
                } else if (nombre.length < 3) {
                    errores.nombreCurso = 'El nombre del curso debe tener al menos 3 caracteres.';
                }

                if (orden === '') {
                    errores.ordenCurso = 'El orden académico es obligatorio.';
                } else if (!/^[0-9]+$/.test(orden)) {
                    errores.ordenCurso = 'El orden académico debe ser numérico.';
                } else if (parseInt(orden, 10) < 1 || parseInt(orden, 10) > 20) {
                    errores.ordenCurso = 'El orden académico debe estar entre 1 y 20.';
                }

                if (nivel === '') {
                    errores.nivelCurso = 'Selecciona el nivel académico.';
                }

                if (estado === '') {
                    errores.estadoCurso = 'Selecciona el estado del curso.';
                }

                ['nombreCurso', 'ordenCurso', 'nivelCurso', 'estadoCurso'].forEach((field) => {
                    marcarCampoCurso(root, field, Boolean(errores[field]));
                });

                return {
                    valido: Object.keys(errores).length === 0,
                    errores,
                    lista: Object.values(errores),
                };
            }

            function validarPlanCursoForm(root) {
                const errores = {};

                const gestion = obtenerValorCurso(root, 'gestionPlan');
                const materiasSeleccionadas = root.querySelectorAll('input[wire\\:model="formPlan.materias"]:checked').length;
                const docente = obtenerValorCurso(root, 'docentePlan');
                const horas = obtenerValorCurso(root, 'horasPlan');

                if (gestion === '') {
                    errores.gestionPlan = 'Selecciona una gestión académica.';
                }

                if (materiasSeleccionadas === 0) {
                    errores.materiasPlan = 'Selecciona al menos una materia para el curso.';
                }

                if (docente === '') {
                    errores.docentePlan = 'Selecciona un docente.';
                }

                if (horas === '') {
                    errores.horasPlan = 'Ingresa la carga horaria.';
                } else if (!/^[0-9]+$/.test(horas)) {
                    errores.horasPlan = 'La carga horaria debe ser numérica.';
                } else if (parseInt(horas, 10) < 1 || parseInt(horas, 10) > 80) {
                    errores.horasPlan = 'La carga horaria debe estar entre 1 y 80 horas.';
                }

                ['gestionPlan', 'materiasPlan', 'docentePlan', 'horasPlan'].forEach((field) => {
                    marcarCampoCurso(root, field, Boolean(errores[field]));
                });

                return {
                    valido: Object.keys(errores).length === 0,
                    errores,
                    lista: Object.values(errores),
                };
            }

            document.addEventListener('DOMContentLoaded', iniciarGraficosCursos);
            document.addEventListener('livewire:navigated', iniciarGraficosCursos);
            window.addEventListener('theme-changed', iniciarGraficosCursos);
            window.addEventListener('actualizar-graficos-cursos', () => {
                setTimeout(iniciarGraficosCursos, 150);
            });

            let chartCursosPlanificacion = null;
            let chartCursosInscritos = null;

            function getChartThemeCursos() {
                const styles = getComputedStyle(document.documentElement);

                return {
                    text: styles.getPropertyValue('--ui-text').trim(),
                    muted: styles.getPropertyValue('--ui-muted').trim(),
                    border: styles.getPropertyValue('--ui-border').trim(),
                    surface: styles.getPropertyValue('--ui-surface').trim(),
                    primary: styles.getPropertyValue('--ui-primary').trim(),
                    info: styles.getPropertyValue('--ui-info').trim(),
                    warning: styles.getPropertyValue('--ui-warning').trim(),
                };
            }

            function iniciarGraficosCursos() {
                const planificacionCanvas = document.getElementById('chartCursosPlanificacion');
                const inscritosCanvas = document.getElementById('chartCursosInscritos');

                if (!planificacionCanvas || !inscritosCanvas) return;

                if (!window.Chart) {
                    console.warn('Chart.js no está disponible. Verifica resources/js/app.js');
                    return;
                }

                const theme = getChartThemeCursos();

                if (chartCursosPlanificacion) chartCursosPlanificacion.destroy();
                if (chartCursosInscritos) chartCursosInscritos.destroy();

                chartCursosPlanificacion = new Chart(planificacionCanvas, {
                    type: 'doughnut',
                    data: {
                        labels: @json($datosGraficos['planificacion']['labels'] ?? []),
                        datasets: [{
                            data: @json($datosGraficos['planificacion']['data'] ?? []),
                            backgroundColor: [
                                theme.primary,
                                theme.warning,
                            ],
                            borderColor: theme.surface,
                            borderWidth: 3,
                            hoverOffset: 8,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
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
                                }
                            }
                        }
                    }
                });

                chartCursosInscritos = new Chart(inscritosCanvas, {
                    type: 'bar',
                    data: {
                        labels: @json($datosGraficos['inscritos']['labels'] ?? []),
                        datasets: [{
                            label: 'Estudiantes',
                            data: @json($datosGraficos['inscritos']['data'] ?? []),
                            backgroundColor: theme.info,
                            borderRadius: 10,
                            maxBarThickness: 42,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
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
                                }
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
                                }
                            }
                        }
                    }
                });
            }
        </script>
    @endpush
</div>