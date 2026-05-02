<div x-data x-on:docente-actualizado.window="
        Swal.fire({
            icon: 'success',
            title: 'Docente actualizado',
            text: 'La información académica del docente fue actualizada correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " x-on:asignacion-creada.window="
        Swal.fire({
            icon: 'success',
            title: 'Carga registrada',
            text: 'La carga académica fue asignada correctamente al docente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " x-on:docente-desactivado.window="
        Swal.fire({
            icon: 'success',
            title: 'Docente desactivado',
            text: 'El docente fue desactivado correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " x-on:docente-reactivado.window="
        Swal.fire({
            icon: 'success',
            title: 'Docente reactivado',
            text: 'El docente fue reactivado correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
    " x-on:error-general.window="
        Swal.fire({
            icon: 'warning',
            title: 'Acción no permitida',
            text: $event.detail.mensaje ?? 'No se pudo completar la acción.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#f59e0b'
        });
    " class="space-y-6">

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>

    {{-- ENCABEZADO INFORMATIVO --}}
    <section class="ui-card overflow-hidden rounded-[2rem]">
        <div class="relative px-6 py-6" style="background:
                radial-gradient(circle at top right, color-mix(in srgb, var(--ui-info) 16%, transparent), transparent 32%),
                radial-gradient(circle at bottom left, color-mix(in srgb, var(--ui-primary) 16%, transparent), transparent 34%),
                linear-gradient(135deg, var(--ui-surface), var(--ui-surface-soft));">

            <div class="absolute right-0 top-0 h-32 w-32 rounded-full blur-3xl"
                style="background: color-mix(in srgb, var(--ui-info) 18%, transparent);"></div>

            <div class="absolute bottom-0 left-1/3 h-28 w-28 rounded-full blur-3xl"
                style="background: color-mix(in srgb, var(--ui-primary) 18%, transparent);"></div>

            <div class="absolute bottom-0 right-1/4 h-24 w-24 rounded-full blur-3xl"
                style="background: color-mix(in srgb, var(--ui-violet) 14%, transparent);"></div>

            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full px-3 py-1 ring-1"
                        style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                        <span class="h-2 w-2 rounded-full" style="background: var(--ui-primary);"></span>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em]">
                            Gestión académica docente
                        </p>
                    </div>

                    <h2 class="ui-title mt-3 text-2xl font-black tracking-tight">
                        Designación de materias y especialidades
                    </h2>

                    <p class="ui-muted mt-2 max-w-3xl text-sm leading-6">
                        Las materias curriculares se asignan automáticamente al turno de la mañana y las especialidades
                        técnicas al turno de la tarde.
                        La gestión académica activa se toma por defecto para evitar errores de asignación.
                    </p>
                </div>

                <div class="grid gap-2 sm:grid-cols-2 lg:min-w-[390px]">
                    <div class="rounded-2xl border px-4 py-3 shadow-sm backdrop-blur"
                        style="background: color-mix(in srgb, var(--ui-surface) 82%, transparent); border-color: var(--ui-info-border);">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4" style="color: var(--ui-info);" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 6.75v10.5M6.75 12h10.5M4 5h16v14H4V5Z" />
                            </svg>

                            <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-info);">
                                Materia
                            </p>
                        </div>

                        <p class="mt-1 text-sm font-bold" style="color: var(--ui-text);">
                            {{ $nombreTurnoManana }}
                        </p>

                        <p class="mt-0.5 text-xs" style="color: var(--ui-muted);">
                            Turno automático
                        </p>
                    </div>

                    <div class="rounded-2xl border px-4 py-3 shadow-sm backdrop-blur"
                        style="background: color-mix(in srgb, var(--ui-surface) 82%, transparent); border-color: var(--ui-violet-border);">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4" style="color: var(--ui-violet);" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M12 6.75 21 12l-9 5.25L3 12l9-5.25Zm0 10.5V21" />
                            </svg>

                            <p class="text-xs font-semibold uppercase tracking-[0.14em]"
                                style="color: var(--ui-violet);">
                                Especialidad
                            </p>
                        </div>

                        <p class="mt-1 text-sm font-bold" style="color: var(--ui-text);">
                            {{ $nombreTurnoTarde }}
                        </p>

                        <p class="mt-0.5 text-xs" style="color: var(--ui-muted);">
                            Turno automático
                        </p>
                    </div>

                    <div class="rounded-2xl border px-4 py-3 shadow-sm backdrop-blur sm:col-span-2"
                        style="background: color-mix(in srgb, var(--ui-surface) 82%, transparent); border-color: var(--ui-primary-border);">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4" style="color: var(--ui-primary);" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M6.75 3v2.25m10.5-2.25v2.25M3.75 8.25h16.5M5.25 5.25h13.5A1.5 1.5 0 0 1 20.25 6.75v12A1.5 1.5 0 0 1 18.75 20.25H5.25A1.5 1.5 0 0 1 3.75 18.75v-12A1.5 1.5 0 0 1 5.25 5.25Z" />
                            </svg>

                            <p class="text-xs font-semibold uppercase tracking-[0.14em]"
                                style="color: var(--ui-primary);">
                                Gestión predeterminada
                            </p>
                        </div>

                        <p class="mt-1 text-sm font-bold" style="color: var(--ui-text);">
                            {{ $nombreGestionActual }}
                        </p>

                        <p class="mt-0.5 text-xs" style="color: var(--ui-muted);">
                            Configurada desde la gestión académica activa
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- RESUMEN SUPERIOR --}}
    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
        {{-- TOTAL DOCENTES --}}
        <div class="ui-card ui-card-hover rounded-[1.6rem] p-5">
            <div class="w-fit rounded-2xl p-3 ring-1"
                style="background: var(--ui-info-soft); color: var(--ui-info); --tw-ring-color: var(--ui-info-border);">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M17 20h5V4H2v16h5m10 0v-4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4" />
                </svg>
            </div>

            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                Total docentes
            </p>

            <h3 class="mt-2 text-3xl font-black" style="color: var(--ui-text);">
                {{ $totalDocentes }}
            </h3>

            <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                Docentes registrados.
            </p>
        </div>

        {{-- ACTIVOS --}}
        <div class="ui-card ui-card-hover rounded-[1.6rem] p-5">
            <div class="w-fit rounded-2xl p-3 ring-1"
                style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>

            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                Activos
            </p>

            <h3 class="mt-2 text-3xl font-black" style="color: var(--ui-text);">
                {{ $docentesActivos }}
            </h3>

            <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                Disponibles para asignación.
            </p>
        </div>

        {{-- MATERIAS --}}
        <div class="ui-card ui-card-hover rounded-[1.6rem] p-5">
            <div class="w-fit rounded-2xl p-3 ring-1"
                style="background: var(--ui-warning-soft); color: var(--ui-warning); --tw-ring-color: var(--ui-warning-border);">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 6.75v10.5M6.75 12h10.5M4 5h16v14H4V5Z" />
                </svg>
            </div>

            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                Materias mañana
            </p>

            <h3 class="mt-2 text-3xl font-black" style="color: var(--ui-text);">
                {{ $totalMateriasAsignadas }}
            </h3>

            <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                {{ $totalHorasMaterias }} horas curriculares.
            </p>
        </div>

        {{-- ESPECIALIDADES --}}
        <div class="ui-card ui-card-hover rounded-[1.6rem] p-5">
            <div class="w-fit rounded-2xl p-3 ring-1"
                style="background: var(--ui-violet-soft); color: var(--ui-violet); --tw-ring-color: var(--ui-violet-border);">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 6.75 21 12l-9 5.25L3 12l9-5.25Zm0 10.5V21" />
                </svg>
            </div>

            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                Especialidades tarde
            </p>

            <h3 class="mt-2 text-3xl font-black" style="color: var(--ui-text);">
                {{ $totalEspecialidadesAsignadas }}
            </h3>

            <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                {{ $totalHorasEspecialidades }} horas técnicas.
            </p>
        </div>

        {{-- SIN ASIGNACIÓN --}}
        <div class="ui-card ui-card-hover rounded-[1.6rem] p-5">
            <div class="w-fit rounded-2xl p-3 ring-1"
                style="background: var(--ui-surface-muted); color: var(--ui-muted); --tw-ring-color: var(--ui-border);">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M18 12H6m15 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>

            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                Sin asignación
            </p>

            <h3 class="mt-2 text-3xl font-black" style="color: var(--ui-text);">
                {{ $docentesSinAsignacion }}
            </h3>

            <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                Docentes sin carga activa.
            </p>
        </div>

        {{-- SOBRECARGA --}}
        <div class="ui-card ui-card-hover rounded-[1.6rem] p-5">
            <div class="w-fit rounded-2xl p-3 ring-1"
                style="background: var(--ui-danger-soft); color: var(--ui-danger); --tw-ring-color: var(--ui-danger-border);">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                        d="M12 9v3.75m0 3.75h.007M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                </svg>
            </div>

            <p class="mt-4 text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--ui-muted);">
                Sobrecarga
            </p>

            <h3 class="mt-2 text-3xl font-black" style="color: var(--ui-text);">
                {{ $docentesSobrecargados }}
            </h3>

            <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                Más de 18 horas.
            </p>
        </div>
    </section>

    {{-- FILTROS --}}
    <section class="ui-card rounded-[2rem] p-5">
        <div class="grid gap-4 xl:grid-cols-12">
            <div class="xl:col-span-4">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">
                    Buscar docente
                </label>

                <div class="flex items-center gap-3 rounded-2xl border px-4 py-3 shadow-sm transition focus-within:ring-4"
                    style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-text); --tw-ring-color: var(--ui-ring);">
                    <svg class="h-5 w-5 shrink-0" style="color: var(--ui-muted);" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                    </svg>

                    <input type="text" wire:model.live.debounce.400ms="search"
                        placeholder="Buscar por docente, CI, correo, materia o especialidad..."
                        class="w-full border-0 bg-transparent p-0 text-sm focus:outline-none focus:ring-0"
                        style="color: var(--ui-text);">
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">
                    Estado
                </label>

                <div class="relative">
                    <select wire:model.live="estado" class="ui-select pr-10">
                        <option value="">Todos</option>
                        <option value="ACTIVO">Activo</option>
                        <option value="INACTIVO">Inactivo</option>
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                        style="color: var(--ui-muted);">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">
                    Tipo de carga
                </label>

                <div class="relative">
                    <select wire:model.live="tipoCargaFiltro" class="ui-select pr-10">
                        <option value="">Todas</option>
                        <option value="MATERIA">Materias</option>
                        <option value="ESPECIALIDAD">Especialidades</option>
                        <option value="AMBAS">Ambas</option>
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                        style="color: var(--ui-muted);">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">
                    Carga académica
                </label>

                <div class="relative">
                    <select wire:model.live="carga" class="ui-select pr-10">
                        <option value="">Todas</option>
                        <option value="SIN_ASIGNACION">Sin asignación</option>
                        <option value="NORMAL">Normal: 1 a 10 horas</option>
                        <option value="MEDIA">Media: 11 a 18 horas</option>
                        <option value="CRITICA">Crítica: 19+ horas</option>
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                        style="color: var(--ui-muted);">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2">
                <label class="ui-label text-xs uppercase tracking-[0.14em]">
                    Vista
                </label>

                <div class="relative">
                    <select wire:model.live="perPage" class="ui-select pr-10">
                        <option value="10">10 por página</option>
                        <option value="15">15 por página</option>
                        <option value="20">20 por página</option>
                        <option value="30">30 por página</option>
                    </select>

                    <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center"
                        style="color: var(--ui-muted);">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m19 9-7 7-7-7" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="ui-card-soft mt-4 flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm" style="color: var(--ui-muted);">
                Carga máxima recomendada:
                <span class="font-bold" style="color: var(--ui-text);">
                    {{ $maxHorasDocente }} horas
                </span>.
                Modificaciones permitidas:
                <span class="font-bold" style="color: var(--ui-text);">
                    {{ $maxModificaciones }}
                </span>.
            </p>

            <button type="button" wire:click="limpiarFiltros" wire:loading.attr="disabled" wire:target="limpiarFiltros"
                class="ui-btn-secondary px-4 py-2.5 disabled:cursor-wait disabled:opacity-60">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18 18 6M6 6l12 12" />
                </svg>
                Limpiar filtros
            </button>
        </div>
    </section>

    {{-- TABLA --}}
    <section class="ui-table-wrap">
        <div class="overflow-x-auto">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>Docente</th>
                        <th>Perfil profesional</th>
                        <th>Materias mañana</th>
                        <th>Especialidades tarde</th>
                        <th>Carga total</th>
                        <th>Usuario</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($docentes as $docente)
                        @php
                            $personal = $docente->personalInstitucional;
                            $persona = $personal?->persona;
                            $usuario = $persona?->usuario;

                            $nombreCompleto = trim(($persona?->nom_per ?? '') . ' ' . ($persona?->ape_pat_per ?? '') . ' ' . ($persona?->ape_mat_per ?? ''));
                            $inicial = strtoupper(substr($persona?->nom_per ?? 'D', 0, 1));

                            $materiasActivas = ($docente->planAsignaturas ?? collect())->where('est_pas', 'ACTIVO');
                            $especialidadesActivas = ($docente->planEspecialidades ?? collect())->where('est_pes', 'ACTIVO');

                            $totalMateriasDocente = (int) ($docente->total_materias ?? 0);
                            $totalEspecialidadesDocente = (int) ($docente->total_especialidades ?? 0);

                            $horasMateriasDocente = (int) ($docente->total_horas_materias ?? 0);
                            $horasEspecialidadesDocente = (int) ($docente->total_horas_especialidades ?? 0);
                            $totalHorasDocente = $horasMateriasDocente + $horasEspecialidadesDocente;
                            $totalAsignacionesDocente = $totalMateriasDocente + $totalEspecialidadesDocente;

                            $nivelCarga = $this->nivelCarga($totalHorasDocente);

                            $badgeCarga = match ($nivelCarga) {
                                'SIN_ASIGNACION' => 'ui-badge-muted',
                                'NORMAL' => 'ui-badge-success',
                                'MEDIA' => 'ui-badge-warning',
                                'CRITICA' => 'ui-badge-danger',
                                default => 'ui-badge-muted',
                            };

                            $textoCarga = match ($nivelCarga) {
                                'SIN_ASIGNACION' => 'Sin asignación',
                                'NORMAL' => 'Carga normal',
                                'MEDIA' => 'Carga media',
                                'CRITICA' => 'Carga crítica',
                                default => 'Sin datos',
                            };

                            $estadoActivo = $docente->est_doc === 'ACTIVO';
                            $edicionesRestantes = max($maxModificaciones - (int) $docente->num_mod_doc, 0);
                            $porcentajeTabla = min(($totalHorasDocente / max($maxHorasDocente, 1)) * 100, 100);

                            $colorBarraTabla = match ($nivelCarga) {
                                'SIN_ASIGNACION' => 'var(--ui-muted)',
                                'NORMAL' => 'var(--ui-primary)',
                                'MEDIA' => 'var(--ui-warning)',
                                'CRITICA' => 'var(--ui-danger)',
                                default => 'var(--ui-muted)',
                            };
                        @endphp

                        <tr wire:key="docente-{{ $docente->cod_doc }}"
                            class="transition {{ !$estadoActivo ? 'opacity-75' : '' }}">

                            {{-- DOCENTE --}}
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="h-11 w-11 overflow-hidden rounded-2xl ring-1"
                                        style="--tw-ring-color: var(--ui-border);">
                                        @if ($persona?->fot_per)
                                            <img src="{{ asset('storage/' . $persona->fot_per) }}"
                                                class="h-full w-full object-cover" alt="Foto del docente">
                                        @else
                                            <div class="flex h-full w-full items-center justify-center text-sm font-bold"
                                                style="background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft)); color: var(--ui-text-soft);">
                                                {{ $inicial }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold" style="color: var(--ui-text);">
                                            {{ $nombreCompleto ?: 'Docente sin nombre' }}
                                        </p>

                                        <p class="mt-0.5 truncate text-xs" style="color: var(--ui-muted);">
                                            {{ $persona?->ci_per ? 'CI ' . $persona->ci_per : 'Sin CI registrado' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- PERFIL --}}
                            <td>
                                <p class="max-w-[220px] truncate text-sm font-semibold" style="color: var(--ui-text-soft);">
                                    {{ $docente->esp_doc ?: 'Sin perfil registrado' }}
                                </p>

                                <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                                    Cambios disponibles: {{ $edicionesRestantes }}
                                </p>
                            </td>

                            {{-- MATERIAS --}}
                            <td>
                                @if ($materiasActivas->count() > 0)
                                    <div class="flex max-w-[240px] flex-wrap gap-1.5">
                                        @foreach ($materiasActivas->take(2) as $plan)
                                            <span class="ui-badge-info">
                                                {{ $plan->asignatura?->nom_asi ?? 'Materia' }}
                                            </span>
                                        @endforeach

                                        @if ($materiasActivas->count() > 2)
                                            <span class="ui-badge-muted">
                                                +{{ $materiasActivas->count() - 2 }}
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                                        {{ $horasMateriasDocente }} h · mañana
                                    </p>
                                @else
                                    <span class="ui-badge-muted">
                                        Sin materias
                                    </span>
                                @endif
                            </td>

                            {{-- ESPECIALIDADES --}}
                            <td>
                                @if ($especialidadesActivas->count() > 0)
                                    <div class="flex max-w-[240px] flex-wrap gap-1.5">
                                        @foreach ($especialidadesActivas->take(2) as $plan)
                                            <span class="ui-badge-violet">
                                                {{ $plan->especialidad?->nom_esp ?? 'Especialidad' }}
                                            </span>
                                        @endforeach

                                        @if ($especialidadesActivas->count() > 2)
                                            <span class="ui-badge-muted">
                                                +{{ $especialidadesActivas->count() - 2 }}
                                            </span>
                                        @endif
                                    </div>

                                    <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                                        {{ $horasEspecialidadesDocente }} h · tarde
                                    </p>
                                @else
                                    <span class="ui-badge-muted">
                                        Sin especialidades
                                    </span>
                                @endif
                            </td>

                            {{-- CARGA --}}
                            <td>
                                <div class="space-y-2">
                                    <span class="{{ $badgeCarga }}">
                                        {{ $textoCarga }}
                                    </span>

                                    <p class="text-xs" style="color: var(--ui-muted);">
                                        {{ $totalAsignacionesDocente }} carga(s) · {{ $totalHorasDocente }} h
                                    </p>

                                    <div class="h-2 w-36 overflow-hidden rounded-full"
                                        style="background: var(--ui-surface-muted);">
                                        <div class="h-full rounded-full"
                                            style="width: {{ $porcentajeTabla }}%; background: {{ $colorBarraTabla }};">
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- USUARIO --}}
                            <td>
                                @if ($usuario)
                                    <span class="ui-badge-success">
                                        Con cuenta
                                    </span>

                                    <p class="mt-1 max-w-[180px] truncate text-xs" style="color: var(--ui-muted);">
                                        {{ $usuario->email }}
                                    </p>
                                @else
                                    <span class="ui-badge-danger">
                                        Sin cuenta
                                    </span>
                                @endif
                            </td>

                            {{-- ESTADO --}}
                            <td>
                                @if ($estadoActivo)
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
                            </td>

                            {{-- ACCIONES --}}
                            <td>
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" wire:click="abrirModalVer('{{ $docente->cod_doc }}')"
                                        wire:loading.attr="disabled" wire:target="abrirModalVer('{{ $docente->cod_doc }}')"
                                        class="ui-icon-btn disabled:cursor-wait disabled:opacity-60" title="Ver detalle">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M2.036 12.322a1 1 0 0 1 0-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.01 9.963 7.178a1 1 0 0 1 0 .644C20.577 16.49 16.639 19.5 12 19.5c-4.638 0-8.573-3.01-9.964-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                    </button>

                                    <button type="button" wire:click="abrirModalEditar('{{ $docente->cod_doc }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="abrirModalEditar('{{ $docente->cod_doc }}')"
                                        class="ui-icon-btn disabled:cursor-wait disabled:opacity-60"
                                        title="Editar perfil profesional">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="m16.862 4.487 1.687-1.688a2.25 2.25 0 1 1 3.182 3.182L10.582 17.13a4.5 4.5 0 0 1-1.897 1.13L6 19l.74-2.685a4.5 4.5 0 0 1 1.13-1.897L16.862 4.487Z" />
                                        </svg>
                                    </button>

                                    <button type="button" wire:click="abrirModalAsignar('{{ $docente->cod_doc }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="abrirModalAsignar('{{ $docente->cod_doc }}')"
                                        class="ui-icon-btn disabled:cursor-wait disabled:opacity-60"
                                        title="Asignar carga académica">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M12 6.75v10.5M6.75 12h10.5M4 5h16v14H4V5Z" />
                                        </svg>
                                    </button>

                                    @if ($estadoActivo)
                                        <button type="button" x-on:click="
                                                        Swal.fire({
                                                            title: '¿Desactivar docente?',
                                                            text: 'El docente no podrá recibir nuevas asignaciones mientras esté inactivo.',
                                                            icon: 'warning',
                                                            showCancelButton: true,
                                                            confirmButtonText: 'Sí, desactivar',
                                                            cancelButtonText: 'Cancelar',
                                                            confirmButtonColor: '#dc2626',
                                                            cancelButtonColor: '#64748b',
                                                            reverseButtons: true
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                $wire.cambiarEstado(@js($docente->cod_doc), 'INACTIVO');
                                                            }
                                                        });
                                                    " class="ui-icon-btn" style="color: var(--ui-danger);"
                                            title="Desactivar docente">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M18 12H6" />
                                                <circle cx="12" cy="12" r="9" />
                                            </svg>
                                        </button>
                                    @else
                                        <button type="button" x-on:click="
                                                        Swal.fire({
                                                            title: '¿Reactivar docente?',
                                                            text: 'El docente volverá a estar disponible.',
                                                            icon: 'question',
                                                            showCancelButton: true,
                                                            confirmButtonText: 'Sí, reactivar',
                                                            cancelButtonText: 'Cancelar',
                                                            confirmButtonColor: '#059669',
                                                            cancelButtonColor: '#64748b',
                                                            reverseButtons: true
                                                        }).then((result) => {
                                                            if (result.isConfirmed) {
                                                                $wire.cambiarEstado(@js($docente->cod_doc), 'ACTIVO');
                                                            }
                                                        });
                                                    " class="ui-icon-btn" style="color: var(--ui-primary);"
                                            title="Reactivar docente">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M12 6v12m6-6H6" />
                                                <circle cx="12" cy="12" r="9" />
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-14 text-center">
                                <div class="mx-auto max-w-md">
                                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[1.5rem]"
                                        style="background: var(--ui-surface-muted); color: var(--ui-muted);">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M17 20h5V4H2v16h5m10 0v-4a2 2 0 0 0-2-2H9a2 2 0 0 0-2 2v4" />
                                        </svg>
                                    </div>

                                    <h3 class="mt-5 text-lg font-bold" style="color: var(--ui-text);">
                                        No se encontraron docentes
                                    </h3>

                                    <p class="mt-2 text-sm leading-6" style="color: var(--ui-muted);">
                                        Ajusta los filtros o registra docentes desde el módulo correspondiente.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex flex-col gap-4 border-t px-6 py-4 lg:flex-row lg:items-center lg:justify-between"
            style="border-color: var(--ui-border);">
            <p class="text-sm" style="color: var(--ui-muted);">
                Mostrando
                <span class="font-semibold" style="color: var(--ui-text);">
                    {{ $docentes->firstItem() ?? 0 }}
                </span>
                -
                <span class="font-semibold" style="color: var(--ui-text);">
                    {{ $docentes->lastItem() ?? 0 }}
                </span>
                de
                <span class="font-semibold" style="color: var(--ui-text);">
                    {{ $docentes->total() }}
                </span>
                docentes
            </p>

            <div>
                {{ $docentes->links() }}
            </div>
        </div>
    </section>

    {{-- MODAL VER DETALLE DOCENTE --}}
    @if ($modalVer && $docenteDetalle)
        @php
            $personalDetalle = $docenteDetalle->personalInstitucional;
            $personaDetalle = $personalDetalle?->persona;
            $usuarioDetalle = $personaDetalle?->usuario;

            $nombreDetalle = trim(($personaDetalle?->nom_per ?? '') . ' ' . ($personaDetalle?->ape_pat_per ?? '') . ' ' . ($personaDetalle?->ape_mat_per ?? ''));
            $inicialDetalle = strtoupper(substr($personaDetalle?->nom_per ?? 'D', 0, 1));

            $materiasDetalle = $docenteDetalle->planAsignaturas ?? collect();
            $especialidadesDetalle = $docenteDetalle->planEspecialidades ?? collect();

            $materiasActivasDetalle = $materiasDetalle->where('est_pas', 'ACTIVO');
            $especialidadesActivasDetalle = $especialidadesDetalle->where('est_pes', 'ACTIVO');

            $horasMateriasDetalle = (int) $materiasActivasDetalle->sum('hor_pas');
            $horasEspecialidadesDetalle = (int) $especialidadesActivasDetalle->sum('hor_pes');
            $totalHorasDetalle = $horasMateriasDetalle + $horasEspecialidadesDetalle;

            $totalMateriasDetalle = $materiasActivasDetalle->count();
            $totalEspecialidadesDetalle = $especialidadesActivasDetalle->count();
            $totalAsignacionesDetalle = $totalMateriasDetalle + $totalEspecialidadesDetalle;

            $nivelCargaDetalle = $this->nivelCarga($totalHorasDetalle);
            $porcentajeCarga = min(($totalHorasDetalle / max($maxHorasDocente, 1)) * 100, 100);

            $badgeCargaDetalle = match ($nivelCargaDetalle) {
                'SIN_ASIGNACION' => 'ui-badge-muted',
                'NORMAL' => 'ui-badge-success',
                'MEDIA' => 'ui-badge-warning',
                'CRITICA' => 'ui-badge-danger',
                default => 'ui-badge-muted',
            };

            $textoCargaDetalle = match ($nivelCargaDetalle) {
                'SIN_ASIGNACION' => 'Sin asignación',
                'NORMAL' => 'Carga normal',
                'MEDIA' => 'Carga media',
                'CRITICA' => 'Carga crítica',
                default => 'Sin datos',
            };

            $mensajeCarga = match ($nivelCargaDetalle) {
                'SIN_ASIGNACION' => 'Este docente aún no tiene cargas activas. Puede recibir materias de mañana o especialidades de tarde.',
                'NORMAL' => 'La carga académica se encuentra dentro del rango recomendado.',
                'MEDIA' => 'La carga académica es moderada. Conviene revisar el equilibrio antes de asignar más carga.',
                'CRITICA' => 'El docente presenta una carga alta. Evita nuevas asignaciones salvo autorización institucional.',
                default => 'No se pudo determinar el nivel de carga académica.',
            };

            $alertaCargaClase = match ($nivelCargaDetalle) {
                'SIN_ASIGNACION' => 'ui-alert',
                'NORMAL' => 'ui-alert-success',
                'MEDIA' => 'ui-alert-warning',
                'CRITICA' => 'ui-alert-danger',
                default => 'ui-alert-info',
            };

            $colorBarra = match ($nivelCargaDetalle) {
                'SIN_ASIGNACION' => 'var(--ui-muted)',
                'NORMAL' => 'var(--ui-primary)',
                'MEDIA' => 'var(--ui-warning)',
                'CRITICA' => 'var(--ui-danger)',
                default => 'var(--ui-muted)',
            };

            $telefonoLimpio = preg_replace('/\D/', '', $personaDetalle?->tel_per ?? '');
            $whatsappNumero = $telefonoLimpio ? (str_starts_with($telefonoLimpio, '591') ? $telefonoLimpio : '591' . $telefonoLimpio) : null;
            $correoContacto = $personaDetalle?->ema_per ?: $usuarioDetalle?->email;

            $edicionesUsadas = (int) ($docenteDetalle->num_mod_doc ?? 0);
            $edicionesRestantes = max($maxModificaciones - $edicionesUsadas, 0);
            $puedeEditar = $edicionesRestantes > 0;
            $puedeAsignar = $docenteDetalle->est_doc === 'ACTIVO' && $totalHorasDetalle < $maxHorasDocente;
        @endphp

        <div wire:key="modal-ver-docente-{{ $docenteDetalle->cod_doc }}"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="ui-modal-backdrop" wire:click="cerrarModalVer"></div>

            <div class="ui-modal w-full max-w-6xl">
                <div class="ui-modal-header">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="h-20 w-20 overflow-hidden rounded-[1.6rem] shadow-sm ring-1"
                                style="--tw-ring-color: var(--ui-border);">
                                @if ($personaDetalle?->fot_per)
                                    <img src="{{ asset('storage/' . $personaDetalle->fot_per) }}"
                                        class="h-full w-full object-cover" alt="Foto del docente">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-2xl font-black"
                                        style="background: linear-gradient(135deg, var(--ui-primary-soft), var(--ui-info-soft)); color: var(--ui-text-soft);">
                                        {{ $inicialDetalle }}
                                    </div>
                                @endif
                            </div>

                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-info);">
                                    Detalle del docente
                                </p>

                                <h3 class="mt-1 text-2xl font-black" style="color: var(--ui-text);">
                                    {{ $nombreDetalle ?: 'Docente sin nombre registrado' }}
                                </h3>

                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <span class="ui-badge-info">
                                        {{ $docenteDetalle->esp_doc ?: 'Sin perfil profesional registrado' }}
                                    </span>

                                    <span class="{{ $badgeCargaDetalle }}">
                                        {{ $textoCargaDetalle }}
                                    </span>

                                    <span class="ui-badge-muted">
                                        Ediciones usadas: {{ $edicionesUsadas }}/{{ $maxModificaciones }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <button type="button" wire:click="cerrarModalVer" class="ui-icon-btn">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[74vh] overflow-y-auto px-6 py-6 ui-scrollbar">
                    <div class="grid gap-5 xl:grid-cols-3">
                        {{-- CONTACTO --}}
                        <div class="ui-panel">
                            <div class="flex items-center gap-3">
                                <div class="rounded-2xl p-3 ring-1"
                                    style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25A2.25 2.25 0 0 0 21.75 19.5v-1.372a1.125 1.125 0 0 0-.852-1.091l-4.423-1.106a1.125 1.125 0 0 0-1.173.417l-.97 1.293a1.125 1.125 0 0 1-1.21.38 12.035 12.035 0 0 1-7.143-7.143 1.125 1.125 0 0 1 .38-1.21l1.293-.97a1.125 1.125 0 0 0 .417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" />
                                    </svg>
                                </div>

                                <div>
                                    <h4 class="text-sm font-black" style="color: var(--ui-text);">
                                        Contactar docente
                                    </h4>

                                    <p class="text-xs" style="color: var(--ui-muted);">
                                        Medios rápidos de comunicación.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 grid grid-cols-3 gap-3">
                                @if ($correoContacto)
                                    <a href="mailto:{{ $correoContacto }}"
                                        class="group rounded-2xl border px-3 py-4 text-center transition"
                                        style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                        <svg class="mx-auto h-5 w-5 transition" style="color: var(--ui-info);" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0-9.75 6.75L2.25 6.75" />
                                        </svg>
                                        <span class="mt-2 block text-xs font-semibold" style="color: var(--ui-text-soft);">
                                            Correo
                                        </span>
                                    </a>
                                @else
                                    <div class="rounded-2xl border px-3 py-4 text-center opacity-50"
                                        style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                        <span class="text-xs font-semibold" style="color: var(--ui-muted);">
                                            Correo
                                        </span>
                                    </div>
                                @endif

                                @if ($whatsappNumero)
                                    <a href="https://wa.me/{{ $whatsappNumero }}" target="_blank" rel="noopener noreferrer"
                                        class="group rounded-2xl border px-3 py-4 text-center transition"
                                        style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                        <svg class="mx-auto h-5 w-5 transition" style="color: var(--ui-primary);" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M8.625 12.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M21 12c0 4.556-4.03 8.25-9 8.25a9.77 9.77 0 0 1-3.756-.75L3 21l1.5-4.5A7.78 7.78 0 0 1 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                                        </svg>
                                        <span class="mt-2 block text-xs font-semibold" style="color: var(--ui-text-soft);">
                                            WhatsApp
                                        </span>
                                    </a>

                                    <a href="tel:{{ $telefonoLimpio }}"
                                        class="group rounded-2xl border px-3 py-4 text-center transition"
                                        style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                        <svg class="mx-auto h-5 w-5 transition" style="color: var(--ui-violet);" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25A2.25 2.25 0 0 0 21.75 19.5v-1.372" />
                                        </svg>
                                        <span class="mt-2 block text-xs font-semibold" style="color: var(--ui-text-soft);">
                                            Llamar
                                        </span>
                                    </a>
                                @else
                                    <div class="rounded-2xl border px-3 py-4 text-center opacity-50"
                                        style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                        <span class="text-xs font-semibold" style="color: var(--ui-muted);">
                                            WhatsApp
                                        </span>
                                    </div>

                                    <div class="rounded-2xl border px-3 py-4 text-center opacity-50"
                                        style="background: var(--ui-surface-soft); border-color: var(--ui-border);">
                                        <span class="text-xs font-semibold" style="color: var(--ui-muted);">
                                            Llamar
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- DATOS PERSONALES --}}
                        <div class="ui-panel xl:col-span-2">
                            <div class="flex items-center gap-3">
                                <div class="rounded-2xl p-3 ring-1"
                                    style="background: var(--ui-info-soft); color: var(--ui-info); --tw-ring-color: var(--ui-info-border);">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 1 1 15 0" />
                                    </svg>
                                </div>

                                <div>
                                    <h4 class="text-sm font-black" style="color: var(--ui-text);">
                                        Datos personales
                                    </h4>

                                    <p class="text-xs" style="color: var(--ui-muted);">
                                        Información de contacto y referencia.
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                                <div class="ui-card-soft px-4 py-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.12em]"
                                        style="color: var(--ui-muted);">
                                        CI
                                    </p>
                                    <p class="mt-1 text-sm font-bold" style="color: var(--ui-text);">
                                        {{ $personaDetalle?->ci_per ?? 'No registrado' }}
                                        @if ($personaDetalle?->exp_per)
                                            <span style="color: var(--ui-muted);">· {{ $personaDetalle->exp_per }}</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="ui-card-soft px-4 py-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.12em]"
                                        style="color: var(--ui-muted);">
                                        Teléfono
                                    </p>
                                    <p class="mt-1 text-sm font-bold" style="color: var(--ui-text);">
                                        {{ $personaDetalle?->tel_per ?? 'No registrado' }}
                                    </p>
                                </div>

                                <div class="ui-card-soft px-4 py-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.12em]"
                                        style="color: var(--ui-muted);">
                                        Correo personal
                                    </p>
                                    <p class="mt-1 break-all text-sm font-bold" style="color: var(--ui-text);">
                                        {{ $personaDetalle?->ema_per ?? 'No registrado' }}
                                    </p>
                                </div>

                                <div class="ui-card-soft px-4 py-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.12em]"
                                        style="color: var(--ui-muted);">
                                        Nacimiento
                                    </p>
                                    <p class="mt-1 text-sm font-bold" style="color: var(--ui-text);">
                                        {{ $personaDetalle?->fec_nac_per ? \Carbon\Carbon::parse($personaDetalle->fec_nac_per)->format('d/m/Y') : 'No registrado' }}
                                    </p>
                                </div>

                                <div class="ui-card-soft px-4 py-3 sm:col-span-2">
                                    <p class="text-xs font-semibold uppercase tracking-[0.12em]"
                                        style="color: var(--ui-muted);">
                                        Dirección
                                    </p>
                                    <p class="mt-1 text-sm font-bold" style="color: var(--ui-text);">
                                        {{ $personaDetalle?->dir_per ?? 'No registrada' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- CUENTA + RESUMEN CARGA --}}
                    <div class="mt-5 grid gap-5 xl:grid-cols-3">
                        <div class="ui-panel">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h4 class="text-sm font-black" style="color: var(--ui-text);">
                                        Cuenta de usuario
                                    </h4>
                                    <p class="text-xs" style="color: var(--ui-muted);">
                                        Acceso al sistema.
                                    </p>
                                </div>

                                @if ($usuarioDetalle)
                                    <span class="ui-badge-success">Con cuenta</span>
                                @else
                                    <span class="ui-badge-danger">Sin cuenta</span>
                                @endif
                            </div>

                            <div class="ui-card-soft mt-5 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-[0.12em]"
                                    style="color: var(--ui-muted);">
                                    Email de acceso
                                </p>
                                <p class="mt-1 break-all text-sm font-bold" style="color: var(--ui-text);">
                                    {{ $usuarioDetalle?->email ?? 'No tiene cuenta asociada' }}
                                </p>
                            </div>
                        </div>

                        <div class="ui-panel xl:col-span-2">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h4 class="text-sm font-black" style="color: var(--ui-text);">
                                        Resumen de carga académica
                                    </h4>
                                    <p class="text-xs" style="color: var(--ui-muted);">
                                        Materias de mañana + especialidades técnicas de tarde.
                                    </p>
                                </div>

                                <span class="{{ $badgeCargaDetalle }}">
                                    {{ $textoCargaDetalle }}
                                </span>
                            </div>

                            <div class="mt-5 grid gap-4 sm:grid-cols-4">
                                <div class="rounded-2xl px-4 py-4 ring-1"
                                    style="background: var(--ui-info-soft); --tw-ring-color: var(--ui-info-border);">
                                    <p class="text-xs font-semibold uppercase tracking-[0.12em]"
                                        style="color: var(--ui-info);">
                                        Materias
                                    </p>
                                    <p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                        {{ $totalMateriasDetalle }}
                                    </p>
                                    <p class="text-xs" style="color: var(--ui-muted);">
                                        {{ $horasMateriasDetalle }} h mañana
                                    </p>
                                </div>

                                <div class="rounded-2xl px-4 py-4 ring-1"
                                    style="background: var(--ui-violet-soft); --tw-ring-color: var(--ui-violet-border);">
                                    <p class="text-xs font-semibold uppercase tracking-[0.12em]"
                                        style="color: var(--ui-violet);">
                                        Especialidades
                                    </p>
                                    <p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                        {{ $totalEspecialidadesDetalle }}
                                    </p>
                                    <p class="text-xs" style="color: var(--ui-muted);">
                                        {{ $horasEspecialidadesDetalle }} h tarde
                                    </p>
                                </div>

                                <div class="rounded-2xl px-4 py-4 ring-1"
                                    style="background: var(--ui-warning-soft); --tw-ring-color: var(--ui-warning-border);">
                                    <p class="text-xs font-semibold uppercase tracking-[0.12em]"
                                        style="color: var(--ui-warning);">
                                        Total horas
                                    </p>
                                    <p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                        {{ $totalHorasDetalle }}
                                    </p>
                                    <p class="text-xs" style="color: var(--ui-muted);">
                                        {{ $totalAsignacionesDetalle }} carga(s)
                                    </p>
                                </div>

                                <div class="rounded-2xl px-4 py-4 ring-1"
                                    style="background: var(--ui-primary-soft); --tw-ring-color: var(--ui-primary-border);">
                                    <p class="text-xs font-semibold uppercase tracking-[0.12em]"
                                        style="color: var(--ui-primary);">
                                        Capacidad
                                    </p>
                                    <p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">
                                        {{ round($porcentajeCarga) }}%
                                    </p>
                                    <p class="text-xs" style="color: var(--ui-muted);">
                                        del límite permitido
                                    </p>
                                </div>
                            </div>

                            <div class="mt-5">
                                <div class="mb-2 flex items-center justify-between text-xs font-semibold"
                                    style="color: var(--ui-muted);">
                                    <span>0 horas</span>
                                    <span>{{ $maxHorasDocente }} horas máximas</span>
                                </div>

                                <div class="h-3 overflow-hidden rounded-full" style="background: var(--ui-surface-muted);">
                                    <div class="h-full rounded-full"
                                        style="width: {{ $porcentajeCarga }}%; background: {{ $colorBarra }};"></div>
                                </div>

                                <div class="{{ $alertaCargaClase }} mt-4">
                                    {{ $mensajeCarga }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- DETALLE DE MATERIAS --}}
                    <div class="ui-panel mt-5">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-black" style="color: var(--ui-text);">
                                    Materias curriculares de la mañana
                                </h4>
                                <p class="text-xs" style="color: var(--ui-muted);">
                                    Asignaciones registradas en plan_asignatura.
                                </p>
                            </div>

                            <span class="ui-badge-info">
                                {{ $totalMateriasDetalle }} registro(s)
                            </span>
                        </div>

                        @if ($materiasDetalle->count() > 0)
                            <div class="overflow-hidden rounded-2xl border" style="border-color: var(--ui-border);">
                                <div class="overflow-x-auto">
                                    <table class="ui-table">
                                        <thead>
                                            <tr>
                                                <th>Materia</th>
                                                <th>Curso / Paralelo</th>
                                                <th>Turno</th>
                                                <th>Gestión</th>
                                                <th>Horas</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($materiasDetalle as $plan)
                                                <tr>
                                                    <td>
                                                        <p class="text-sm font-bold" style="color: var(--ui-text);">
                                                            {{ $plan->asignatura?->nom_asi ?? 'Materia no registrada' }}
                                                        </p>

                                                        @if ($plan->asignatura?->sig_asi)
                                                            <span class="ui-badge-info mt-1">
                                                                {{ $plan->asignatura->sig_asi }}
                                                            </span>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        <p class="text-sm font-semibold" style="color: var(--ui-text-soft);">
                                                            {{ $plan->curso?->nom_cur ?? '—' }}
                                                        </p>
                                                        <p class="text-xs" style="color: var(--ui-muted);">
                                                            Paralelo {{ $plan->paralelo?->nom_par ?? '—' }}
                                                        </p>
                                                    </td>

                                                    <td>
                                                        <span class="ui-badge-info">
                                                            {{ $plan->turno?->nom_tur ?? '—' }}
                                                        </span>
                                                    </td>

                                                    <td>
                                                        <p class="text-sm font-semibold" style="color: var(--ui-text-soft);">
                                                            {{ $plan->gestionAcademica?->ani_gea ?? '—' }}
                                                        </p>
                                                    </td>

                                                    <td>
                                                        <p class="text-sm font-bold" style="color: var(--ui-text);">
                                                            {{ $plan->hor_pas ?? 0 }} h
                                                        </p>
                                                    </td>

                                                    <td>
                                                        @if ($plan->est_pas === 'ACTIVO')
                                                            <span class="ui-badge-success">Activa</span>
                                                        @else
                                                            <span class="ui-badge-muted">Inactiva</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="ui-alert-info">
                                Este docente aún no tiene materias curriculares asignadas.
                            </div>
                        @endif
                    </div>

                    {{-- DETALLE DE ESPECIALIDADES --}}
                    <div class="ui-panel mt-5">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-black" style="color: var(--ui-text);">
                                    Especialidades técnicas de la tarde
                                </h4>
                                <p class="text-xs" style="color: var(--ui-muted);">
                                    Asignaciones registradas en plan_especialidad.
                                </p>
                            </div>

                            <span class="ui-badge-violet">
                                {{ $totalEspecialidadesDetalle }} registro(s)
                            </span>
                        </div>

                        @if ($especialidadesDetalle->count() > 0)
                            <div class="overflow-hidden rounded-2xl border" style="border-color: var(--ui-border);">
                                <div class="overflow-x-auto">
                                    <table class="ui-table">
                                        <thead>
                                            <tr>
                                                <th>Especialidad</th>
                                                <th>Curso / Paralelo</th>
                                                <th>Turno</th>
                                                <th>Gestión</th>
                                                <th>Horas</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach ($especialidadesDetalle as $plan)
                                                <tr>
                                                    <td>
                                                        <p class="text-sm font-bold" style="color: var(--ui-text);">
                                                            {{ $plan->especialidad?->nom_esp ?? 'Especialidad no registrada' }}
                                                        </p>

                                                        @if ($plan->especialidad?->des_esp)
                                                            <p class="mt-1 max-w-[280px] truncate text-xs"
                                                                style="color: var(--ui-muted);">
                                                                {{ $plan->especialidad->des_esp }}
                                                            </p>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        <p class="text-sm font-semibold" style="color: var(--ui-text-soft);">
                                                            {{ $plan->curso?->nom_cur ?? '—' }}
                                                        </p>
                                                        <p class="text-xs" style="color: var(--ui-muted);">
                                                            Paralelo {{ $plan->paralelo?->nom_par ?? '—' }}
                                                        </p>
                                                    </td>

                                                    <td>
                                                        <span class="ui-badge-violet">
                                                            {{ $plan->turno?->nom_tur ?? '—' }}
                                                        </span>
                                                    </td>

                                                    <td>
                                                        <p class="text-sm font-semibold" style="color: var(--ui-text-soft);">
                                                            {{ $plan->gestionAcademica?->ani_gea ?? '—' }}
                                                        </p>
                                                    </td>

                                                    <td>
                                                        <p class="text-sm font-bold" style="color: var(--ui-text);">
                                                            {{ $plan->hor_pes ?? 0 }} h
                                                        </p>
                                                    </td>

                                                    <td>
                                                        @if ($plan->est_pes === 'ACTIVO')
                                                            <span class="ui-badge-success">Activa</span>
                                                        @else
                                                            <span class="ui-badge-muted">Inactiva</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="ui-alert-info">
                                Este docente aún no tiene especialidades técnicas asignadas.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="ui-modal-footer flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <button type="button" wire:click="cerrarModalVer" wire:loading.attr="disabled"
                        wire:target="cerrarModalVer" class="ui-btn-secondary">
                        Cerrar
                    </button>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button type="button" wire:click="abrirModalEditar('{{ $docenteDetalle->cod_doc }}')"
                            wire:loading.attr="disabled" wire:target="abrirModalEditar('{{ $docenteDetalle->cod_doc }}')"
                            @disabled(!$puedeEditar)
                            class="{{ $puedeEditar ? 'ui-btn-secondary' : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none' }}">
                            Editar perfil profesional
                        </button>

                        <button type="button" wire:click="abrirModalAsignar('{{ $docenteDetalle->cod_doc }}')"
                            wire:loading.attr="disabled" wire:target="abrirModalAsignar('{{ $docenteDetalle->cod_doc }}')"
                            @disabled(!$puedeAsignar)
                            class="{{ $puedeAsignar ? 'ui-btn-primary' : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none' }}">
                            Asignar carga académica
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL ASIGNAR CARGA ACADÉMICA --}}
    @if ($modalAsignar && $docenteDetalle)
        @php
            $personaAsignar = $docenteDetalle->personalInstitucional?->persona;
            $nombreAsignar = trim(($personaAsignar?->nom_per ?? '') . ' ' . ($personaAsignar?->ape_pat_per ?? '') . ' ' . ($personaAsignar?->ape_mat_per ?? ''));

            $materiasAsignar = ($docenteDetalle->planAsignaturas ?? collect())->where('est_pas', 'ACTIVO');
            $especialidadesAsignar = ($docenteDetalle->planEspecialidades ?? collect())->where('est_pes', 'ACTIVO');

            $horasMateriasAsignar = (int) $materiasAsignar->sum('hor_pas');
            $horasEspecialidadesAsignar = (int) $especialidadesAsignar->sum('hor_pes');
            $horasActualesAsignar = $horasMateriasAsignar + $horasEspecialidadesAsignar;
            $horasDisponibles = max($maxHorasDocente - $horasActualesAsignar, 0);
        @endphp

        <div wire:key="modal-asignar-docente-{{ $docenteDetalle->cod_doc }}"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="ui-modal-backdrop" wire:click="cerrarModalAsignar"></div>

            <div x-data="{
                        tipo: @entangle('formAsignacion.tipo_carga').live,
                        materia: @entangle('formAsignacion.cod_asi').live,
                        especialidad: @entangle('formAsignacion.cod_esp').live,
                        curso: @entangle('formAsignacion.cod_cur').live,
                        paralelo: @entangle('formAsignacion.cod_par').live,
                        turno: @entangle('formAsignacion.cod_tur').live,
                        gestion: @entangle('formAsignacion.cod_gea').live,
                        horas: @entangle('formAsignacion.hor_car').live,
                        estado: @entangle('formAsignacion.est_car').live,
                        maxHoras: {{ $horasDisponibles }},
                        get cargaSeleccionada() {
                            return this.tipo === 'MATERIA' ? this.materia : this.especialidad;
                        },
                        get valido() {
                            return this.tipo
                                && this.cargaSeleccionada
                                && this.curso
                                && this.paralelo
                                && this.turno
                                && this.gestion
                                && this.estado
                                && Number(this.horas) > 0
                                && Number(this.horas) <= this.maxHoras;
                        }
                    }" class="ui-modal w-full max-w-3xl">

                <div class="bg-gradient-to-r from-emerald-600 to-sky-600 px-6 py-5 text-white">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/80">
                                Designación académica
                            </p>

                            <h3 class="mt-2 text-2xl font-black">
                                Asignar carga académica
                            </h3>

                            <p class="mt-1 text-sm text-white/85">
                                {{ $nombreAsignar ?: 'Docente seleccionado' }} · disponibles: {{ $horasDisponibles }} h
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarModalAsignar"
                            class="rounded-2xl bg-white/10 p-2 text-white transition hover:bg-white/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="max-h-[72vh] overflow-y-auto px-6 py-6 ui-scrollbar">
                    <div class="mb-5 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl px-4 py-3 ring-1"
                            style="background: var(--ui-info-soft); --tw-ring-color: var(--ui-info-border);">
                            <p class="text-xs font-semibold uppercase tracking-[0.12em]" style="color: var(--ui-info);">
                                Mañana
                            </p>
                            <p class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                                {{ $horasMateriasAsignar }} h
                            </p>
                            <p class="text-xs" style="color: var(--ui-muted);">
                                Materias curriculares
                            </p>
                        </div>

                        <div class="rounded-2xl px-4 py-3 ring-1"
                            style="background: var(--ui-violet-soft); --tw-ring-color: var(--ui-violet-border);">
                            <p class="text-xs font-semibold uppercase tracking-[0.12em]" style="color: var(--ui-violet);">
                                Tarde
                            </p>
                            <p class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                                {{ $horasEspecialidadesAsignar }} h
                            </p>
                            <p class="text-xs" style="color: var(--ui-muted);">
                                Especialidades técnicas
                            </p>
                        </div>

                        <div class="rounded-2xl px-4 py-3 ring-1"
                            style="background: var(--ui-primary-soft); --tw-ring-color: var(--ui-primary-border);">
                            <p class="text-xs font-semibold uppercase tracking-[0.12em]" style="color: var(--ui-primary);">
                                Disponible
                            </p>
                            <p class="mt-1 text-lg font-black" style="color: var(--ui-text);">
                                {{ $horasDisponibles }} h
                            </p>
                            <p class="text-xs" style="color: var(--ui-muted);">
                                Máximo {{ $maxHorasDocente }} h
                            </p>
                        </div>
                    </div>

                    <div class="ui-alert-info mb-5">
                        La materia se asignará al turno <strong>{{ $nombreTurnoManana }}</strong>.
                        La especialidad técnica se asignará al turno <strong>{{ $nombreTurnoTarde }}</strong>.
                        La gestión usada será <strong>{{ $nombreGestionActual }}</strong>.
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="ui-label">
                                Tipo de carga
                            </label>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="cursor-pointer rounded-2xl border p-4 transition" :style="tipo === 'MATERIA'
                                            ? 'border-color: var(--ui-info-border); background: var(--ui-info-soft); box-shadow: 0 0 0 4px var(--ui-ring);'
                                            : 'border-color: var(--ui-border); background: var(--ui-surface);'">
                                    <input type="radio" value="MATERIA" wire:model.live="formAsignacion.tipo_carga"
                                        class="sr-only">

                                    <div class="flex items-center gap-3">
                                        <div class="rounded-xl p-2"
                                            style="background: var(--ui-info-soft); color: var(--ui-info);">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M12 6.75v10.5M6.75 12h10.5M4 5h16v14H4V5Z" />
                                            </svg>
                                        </div>

                                        <div>
                                            <p class="text-sm font-black" style="color: var(--ui-text);">
                                                Materia curricular
                                            </p>
                                            <p class="text-xs" style="color: var(--ui-muted);">
                                                Turno mañana automático.
                                            </p>
                                        </div>
                                    </div>
                                </label>

                                <label class="cursor-pointer rounded-2xl border p-4 transition" :style="tipo === 'ESPECIALIDAD'
                                            ? 'border-color: var(--ui-violet-border); background: var(--ui-violet-soft); box-shadow: 0 0 0 4px var(--ui-ring);'
                                            : 'border-color: var(--ui-border); background: var(--ui-surface);'">
                                    <input type="radio" value="ESPECIALIDAD" wire:model.live="formAsignacion.tipo_carga"
                                        class="sr-only">

                                    <div class="flex items-center gap-3">
                                        <div class="rounded-xl p-2"
                                            style="background: var(--ui-violet-soft); color: var(--ui-violet);">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M12 6.75 21 12l-9 5.25L3 12l9-5.25Zm0 10.5V21" />
                                            </svg>
                                        </div>

                                        <div>
                                            <p class="text-sm font-black" style="color: var(--ui-text);">
                                                Especialidad técnica
                                            </p>
                                            <p class="text-xs" style="color: var(--ui-muted);">
                                                Turno tarde automático.
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            @error('formAsignacion.tipo_carga')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="tipo === 'MATERIA'" x-cloak class="md:col-span-2">
                            <label class="ui-label">
                                Materia curricular
                            </label>

                            <select wire:model.live="formAsignacion.cod_asi" class="ui-select">
                                <option value="">Seleccionar materia</option>
                                @foreach ($asignaturas as $asignatura)
                                    <option value="{{ $asignatura->cod_asi }}">
                                        {{ $asignatura->nom_asi }}{{ $asignatura->sig_asi ? ' · ' . $asignatura->sig_asi : '' }}
                                    </option>
                                @endforeach
                            </select>

                            @error('formAsignacion.cod_asi')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div x-show="tipo === 'ESPECIALIDAD'" x-cloak class="md:col-span-2">
                            <label class="ui-label">
                                Especialidad técnica
                            </label>

                            <select wire:model.live="formAsignacion.cod_esp" class="ui-select">
                                <option value="">Seleccionar especialidad técnica</option>
                                @foreach ($especialidadesTecnicas as $especialidad)
                                    <option value="{{ $especialidad->cod_esp }}">{{ $especialidad->nom_esp }}</option>
                                @endforeach
                            </select>

                            @error('formAsignacion.cod_esp')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">
                                Curso
                            </label>

                            <select wire:model.live="formAsignacion.cod_cur" class="ui-select">
                                <option value="">Seleccionar curso</option>
                                @foreach ($cursos as $curso)
                                    <option value="{{ $curso->cod_cur }}">{{ $curso->nom_cur }}</option>
                                @endforeach
                            </select>

                            @error('formAsignacion.cod_cur')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">
                                Paralelo
                            </label>

                            <select wire:model.live="formAsignacion.cod_par" class="ui-select">
                                <option value="">Seleccionar paralelo</option>
                                @foreach ($paralelos as $paralelo)
                                    <option value="{{ $paralelo->cod_par }}">{{ $paralelo->nom_par }}</option>
                                @endforeach
                            </select>

                            @error('formAsignacion.cod_par')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">
                                Turno asignado automáticamente
                            </label>

                            <div class="ui-field-readonly">
                                <span x-show="tipo === 'MATERIA'">{{ $nombreTurnoManana }}</span>
                                <span x-show="tipo === 'ESPECIALIDAD'" x-cloak>{{ $nombreTurnoTarde }}</span>
                                <span x-show="!tipo" x-cloak>Selecciona primero el tipo de carga</span>
                            </div>

                            <input type="hidden" wire:model.live="formAsignacion.cod_tur">

                            @error('formAsignacion.cod_tur')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">
                                Gestión académica
                            </label>

                            <div class="ui-field-readonly">
                                {{ $nombreGestionActual }}
                            </div>

                            <input type="hidden" wire:model.live="formAsignacion.cod_gea">

                            @error('formAsignacion.cod_gea')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">
                                Horas
                            </label>

                            <input type="number" min="1" max="{{ $horasDisponibles }}"
                                wire:model.live="formAsignacion.hor_car" class="ui-input">

                            @error('formAsignacion.hor_car')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">
                                Estado
                            </label>

                            <select wire:model.live="formAsignacion.est_car" class="ui-select">
                                <option value="ACTIVO">Activo</option>
                                <option value="INACTIVO">Inactivo</option>
                            </select>

                            @error('formAsignacion.est_car')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <p x-show="!valido" x-cloak class="mt-4 text-sm font-medium" style="color: var(--ui-danger);">
                        Completa todos los campos y respeta las horas disponibles.
                    </p>
                </div>

                <div class="ui-modal-footer flex justify-end gap-3">
                    <button type="button" wire:click="cerrarModalAsignar" wire:loading.attr="disabled"
                        wire:target="cerrarModalAsignar" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button" wire:click="guardarAsignacion" wire:loading.attr="disabled"
                        wire:target="guardarAsignacion" :disabled="!valido" :class="valido
                                ? 'ui-btn-primary'
                                : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none'"
                        class="rounded-2xl px-5 py-3 text-sm font-semibold transition">
                        Guardar carga académica
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL EDITAR DOCENTE --}}
    @if ($modalEditar && $docenteDetalle)
        @php
            $personaEditar = $docenteDetalle->personalInstitucional?->persona;
            $nombreEditar = trim(($personaEditar?->nom_per ?? '') . ' ' . ($personaEditar?->ape_pat_per ?? '') . ' ' . ($personaEditar?->ape_mat_per ?? ''));
            $edicionesUsadasEditar = (int) ($docenteDetalle->num_mod_doc ?? 0);
            $edicionesRestantesEditar = max($maxModificaciones - $edicionesUsadasEditar, 0);
        @endphp

        <div wire:key="modal-editar-docente-{{ $docenteDetalle->cod_doc }}"
            class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
            <div class="ui-modal-backdrop" wire:click="cerrarModalEditar"></div>

            <div x-data="{
                        especialidad: @entangle('formEditar.esp_doc').live,
                        estado: @entangle('formEditar.est_doc').live,
                        get valido() {
                            return this.especialidad && this.especialidad.trim().length >= 3 && this.estado;
                        }
                    }" class="ui-modal w-full max-w-xl">

                <div class="bg-gradient-to-r from-emerald-600 to-sky-600 px-6 py-5 text-white">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-white/80">
                                Edición académica
                            </p>
                            <h3 class="mt-2 text-2xl font-black">
                                Editar perfil docente
                            </h3>
                            <p class="mt-1 text-sm text-white/85">
                                {{ $nombreEditar ?: 'Docente seleccionado' }}
                            </p>
                        </div>

                        <button type="button" wire:click="cerrarModalEditar"
                            class="rounded-2xl bg-white/10 p-2 text-white transition hover:bg-white/20">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="px-6 py-6">
                    <div class="ui-alert-warning mb-5">
                        Ediciones usadas: {{ $edicionesUsadasEditar }}/{{ $maxModificaciones }}.
                        Restantes: {{ $edicionesRestantesEditar }}.
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="ui-label">
                                Perfil profesional del docente
                            </label>

                            <input type="text" wire:model.live="formEditar.esp_doc"
                                placeholder="Ej. Matemática y Física, Sistemas Informáticos, Electrónica" class="ui-input">

                            <p class="ui-help">
                                Este campo describe el perfil profesional del docente, no la especialidad técnica asignada.
                            </p>

                            @error('formEditar.esp_doc')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="ui-label">
                                Estado del docente
                            </label>

                            <select wire:model.live="formEditar.est_doc" class="ui-select">
                                <option value="ACTIVO">Activo</option>
                                <option value="INACTIVO">Inactivo</option>
                            </select>

                            @error('formEditar.est_doc')
                                <p class="ui-error">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <p x-show="!valido" x-cloak class="mt-4 text-sm font-medium" style="color: var(--ui-danger);">
                        El perfil profesional debe tener al menos 3 caracteres.
                    </p>
                </div>

                <div class="ui-modal-footer flex justify-end gap-3">
                    <button type="button" wire:click="cerrarModalEditar" wire:loading.attr="disabled"
                        wire:target="cerrarModalEditar" class="ui-btn-secondary">
                        Cancelar
                    </button>

                    <button type="button" wire:click="actualizarDocente" wire:loading.attr="disabled"
                        wire:target="actualizarDocente" :disabled="!valido" :class="valido
                                ? 'ui-btn-primary'
                                : 'ui-btn cursor-not-allowed bg-slate-300 text-slate-500 shadow-none'"
                        class="rounded-2xl px-5 py-3 text-sm font-semibold transition">
                        Guardar cambios
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>