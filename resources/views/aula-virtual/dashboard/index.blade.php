@extends('aula-virtual.layouts.app')

@section('title', 'Perfil académico pendiente | SAVP-TIS3')
@section('page-title', 'Perfil académico pendiente')

@section('content')
    @php
        use Illuminate\Support\Facades\Route;

        $user = Auth::user();
        $persona = $user?->persona;

        $nombreCompleto = trim(
            ($persona->nom_per ?? '') . ' ' . ($persona->ape_pat_per ?? '') . ' ' . ($persona->ape_mat_per ?? ''),
        );

        $nombreUsuario = $nombreCompleto ?: $user?->email ?? 'Usuario';

        $inicialUsuario = mb_strtoupper(mb_substr($nombreUsuario, 0, 1));

        $roles = $user?->getRoleNames() ?? collect();

        $tieneRolDocente = $user?->hasRole('Docente') || $user?->can('Aula_Virtual_Docente');

        $tieneRolEstudiante = $user?->hasRole('Estudiante') || $user?->can('Aula_Virtual_Estudiante');

        $tienePerfilAcademico = $tieneRolDocente || $tieneRolEstudiante;
    @endphp


    <section class="mx-auto w-full max-w-5xl py-4 sm:py-6 lg:py-8" aria-labelledby="perfil-pendiente-title">

        {{-- ========================================================= --}}
        {{-- TARJETA PRINCIPAL --}}
        {{-- ========================================================= --}}

        <div class="overflow-hidden rounded-[2rem] border shadow-sm"
            style="
                background: var(--ui-surface);
                border-color: var(--ui-border);
            ">

            {{-- ===================================================== --}}
            {{-- ENCABEZADO --}}
            {{-- ===================================================== --}}

            <div class="relative overflow-hidden border-b px-6 py-8 sm:px-8 lg:px-10"
                style="
                    border-color: var(--ui-border);
                    background:
                        radial-gradient(
                            circle at top right,
                            rgba(217, 119, 6, 0.13),
                            transparent 35%
                        ),
                        radial-gradient(
                            circle at bottom left,
                            rgba(16, 185, 129, 0.10),
                            transparent 32%
                        ),
                        var(--ui-surface);
                ">

                <div class="relative flex flex-col gap-6 sm:flex-row sm:items-center">

                    {{-- Icono principal --}}
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-[1.5rem] border shadow-sm"
                        style="
                            background: var(--ui-warning-soft);
                            border-color: color-mix(
                                in srgb,
                                var(--ui-warning) 25%,
                                var(--ui-border)
                            );
                            color: var(--ui-warning);
                        "
                        aria-hidden="true">
                        <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M12 9v3.75m0 3h.008v.008H12v-.008ZM10.29 3.86 2.82 17.25A1.5 1.5 0 0 0 4.13 19.5h15.74a1.5 1.5 0 0 0 1.31-2.25L13.71 3.86a1.96 1.96 0 0 0-3.42 0Z" />
                        </svg>
                    </div>


                    {{-- Texto --}}
                    <div class="min-w-0 flex-1">

                        <span class="ui-badge-warning">
                            Configuración pendiente
                        </span>

                        <h2 id="perfil-pendiente-title" class="mt-4 text-2xl font-black tracking-tight sm:text-3xl"
                            style="color: var(--ui-text);">
                            Tu cuenta todavía no tiene un perfil académico completo
                        </h2>

                        <p class="mt-3 max-w-3xl text-sm leading-7 sm:text-base" style="color: var(--ui-muted);">
                            Tu cuenta institucional puede acceder al Aula Virtual,
                            pero SAVP no encuentra una vinculación válida como
                            <strong style="color: var(--ui-text);">
                                estudiante
                            </strong>
                            o
                            <strong style="color: var(--ui-text);">
                                docente
                            </strong>.
                            Por seguridad, las funciones académicas permanecerán
                            limitadas hasta que la configuración sea revisada.
                        </p>

                    </div>
                </div>
            </div>


            {{-- ===================================================== --}}
            {{-- CONTENIDO --}}
            {{-- ===================================================== --}}

            <div class="grid gap-6 p-6 sm:p-8 lg:grid-cols-[1fr_0.9fr] lg:p-10">

                {{-- ================================================= --}}
                {{-- COLUMNA IZQUIERDA --}}
                {{-- ================================================= --}}

                <div class="space-y-6">

                    {{-- Cuenta detectada --}}
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                            Cuenta detectada
                        </p>

                        <div class="mt-3 flex items-center gap-4 rounded-2xl border p-4"
                            style="
                                border-color: var(--ui-border);
                                background: var(--ui-bg-soft);
                            ">

                            {{-- Avatar --}}
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-sm font-black text-white shadow-sm"
                                style="background: var(--savp-green);">
                                {{ $inicialUsuario }}
                            </div>

                            <div class="min-w-0 flex-1">

                                <p class="truncate text-sm font-black" style="color: var(--ui-text);">
                                    {{ $nombreUsuario }}
                                </p>

                                <p class="mt-1 truncate text-xs" style="color: var(--ui-muted);">
                                    {{ $user?->email ?? 'Sin correo registrado' }}
                                </p>

                                @if ($roles->isNotEmpty())
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($roles as $role)
                                            <span class="ui-badge-info">
                                                {{ $role }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="mt-2 text-xs font-semibold" style="color: var(--ui-warning);">
                                        No se detectaron roles asignados.
                                    </p>
                                @endif

                            </div>
                        </div>
                    </div>


                    {{-- Qué puede estar ocurriendo --}}
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em]" style="color: var(--ui-muted);">
                            ¿Qué debe revisarse?
                        </p>

                        <div class="mt-3 space-y-3">

                            {{-- Punto 1 --}}
                            <div class="flex gap-3 rounded-2xl border p-4" style="border-color: var(--ui-border);">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl font-black"
                                    style="
                                        background: var(--ui-primary-soft);
                                        color: var(--ui-primary);
                                    ">
                                    1
                                </div>

                                <div>
                                    <p class="text-sm font-bold" style="color: var(--ui-text);">
                                        Rol y permisos
                                    </p>

                                    <p class="mt-1 text-xs leading-5" style="color: var(--ui-muted);">
                                        La cuenta debe tener asignado correctamente
                                        el rol y los permisos correspondientes al
                                        Aula Virtual.
                                    </p>
                                </div>
                            </div>


                            {{-- Punto 2 --}}
                            <div class="flex gap-3 rounded-2xl border p-4" style="border-color: var(--ui-border);">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl font-black"
                                    style="
                                        background: var(--ui-primary-soft);
                                        color: var(--ui-primary);
                                    ">
                                    2
                                </div>

                                <div>
                                    <p class="text-sm font-bold" style="color: var(--ui-text);">
                                        Vinculación académica
                                    </p>

                                    <p class="mt-1 text-xs leading-5" style="color: var(--ui-muted);">
                                        La persona debe estar vinculada a un perfil
                                        de estudiante o docente activo dentro de SAVP.
                                    </p>
                                </div>
                            </div>


                            {{-- Punto 3 --}}
                            <div class="flex gap-3 rounded-2xl border p-4" style="border-color: var(--ui-border);">
                                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl font-black"
                                    style="
                                        background: var(--ui-primary-soft);
                                        color: var(--ui-primary);
                                    ">
                                    3
                                </div>

                                <div>
                                    <p class="text-sm font-bold" style="color: var(--ui-text);">
                                        Asignación académica
                                    </p>

                                    <p class="mt-1 text-xs leading-5" style="color: var(--ui-muted);">
                                        En caso de docentes o estudiantes, también
                                        deben existir las relaciones académicas
                                        correspondientes para acceder a sus cursos.
                                    </p>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- COLUMNA DERECHA --}}
                {{-- ================================================= --}}

                <div class="space-y-5">

                    {{-- Estado --}}
                    <div class="rounded-[1.5rem] border p-5"
                        style="
                            border-color: color-mix(
                                in srgb,
                                var(--ui-warning) 25%,
                                var(--ui-border)
                            );
                            background: var(--ui-warning-soft);
                        ">
                        <div class="flex items-start gap-3">

                            <svg class="mt-0.5 h-5 w-5 shrink-0" style="color: var(--ui-warning);" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="12" cy="12" r="8.25" stroke-width="1.8" />

                                <path stroke-linecap="round" stroke-width="1.8" d="M12 8.25v4.5m0 3h.008" />
                            </svg>

                            <div>
                                <p class="text-sm font-black" style="color: var(--ui-text);">
                                    Acceso académico limitado
                                </p>

                                <p class="mt-2 text-xs leading-6" style="color: var(--ui-muted);">
                                    Esta situación no elimina tu cuenta ni modifica
                                    tus datos. Solo evita mostrar funciones académicas
                                    que todavía no pueden asociarse de forma segura
                                    con tu perfil.
                                </p>
                            </div>
                        </div>
                    </div>


                    {{-- Contacto --}}
                    <div class="rounded-[1.5rem] border p-5"
                        style="
                            border-color: var(--ui-border);
                            background: var(--ui-bg-soft);
                        ">
                        <div class="flex items-start gap-3">

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl"
                                style="
                                    background: var(--ui-primary-soft);
                                    color: var(--ui-primary);
                                ">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M12 3.75a8.25 8.25 0 1 0 8.25 8.25A8.25 8.25 0 0 0 12 3.75Zm0 4.5v4.5m0 3h.008" />
                                </svg>
                            </div>

                            <div>
                                <p class="text-sm font-black" style="color: var(--ui-text);">
                                    ¿Qué debes hacer?
                                </p>

                                <p class="mt-2 text-xs leading-6" style="color: var(--ui-muted);">
                                    Comunícate con la administración de la institución
                                    para verificar tu perfil, rol y vinculación
                                    académica dentro de SAVP.
                                </p>

                                <p class="mt-3 text-xs font-semibold" style="color: var(--ui-text-soft);">
                                    Puedes indicar el correo:
                                </p>

                                <p class="mt-1 break-all text-xs font-black" style="color: var(--ui-primary);">
                                    {{ $user?->email ?? 'No disponible' }}
                                </p>
                            </div>
                        </div>
                    </div>


                    {{-- Acciones --}}
                    <div class="space-y-3">

                        @if (Route::has('welcome'))
                            <a href="{{ route('welcome') }}"
                                class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                                style="background: var(--savp-green);">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M3 10.5 12 3l9 7.5M5.25 9.75V21h13.5V9.75M9 21v-6h6v6" />
                                </svg>

                                Volver al portal principal
                            </a>
                        @endif


                        @if (Route::has('profile.show'))
                            <a href="{{ route('profile.show') }}"
                                class="flex w-full items-center justify-center gap-2 rounded-xl border px-4 py-3 text-sm font-bold transition hover:bg-[var(--ui-surface-muted)]"
                                style="
                                    border-color: var(--ui-border);
                                    color: var(--ui-text-soft);
                                ">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    aria-hidden="true">
                                    <circle cx="12" cy="8" r="3.5" stroke-width="1.8" />

                                    <path stroke-linecap="round" stroke-width="1.8"
                                        d="M5 20c.75-4 3.1-6 7-6s6.25 2 7 6" />
                                </svg>

                                Revisar mi perfil
                            </a>
                        @endif

                    </div>

                </div>
            </div>
        </div>


        {{-- ========================================================= --}}
        {{-- NOTA DE SEGURIDAD --}}
        {{-- ========================================================= --}}

        <div class="mt-5 flex items-start gap-3 rounded-2xl border px-5 py-4"
            style="
                border-color: var(--ui-border);
                background: color-mix(
                    in srgb,
                    var(--ui-surface) 85%,
                    transparent
                );
            ">
            <svg class="mt-0.5 h-5 w-5 shrink-0" style="color: var(--ui-muted);" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M12 3.75 5.25 6.5v5.18c0 4.13 2.74 7.98 6.75 8.82 4.01-.84 6.75-4.69 6.75-8.82V6.5L12 3.75Zm-2.25 8.5 1.5 1.5 3-3" />
            </svg>

            <p class="text-xs leading-6" style="color: var(--ui-muted);">
                SAVP limita automáticamente el acceso cuando no puede
                verificar una relación académica válida. Esto ayuda a evitar
                que una cuenta visualice cursos, estudiantes, calificaciones
                o recursos que no le corresponden.
            </p>
        </div>

    </section>
@endsection
