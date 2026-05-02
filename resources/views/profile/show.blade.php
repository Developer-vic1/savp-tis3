@extends('layouts.app')

@section('title', 'Mi perfil')

@section('content')
    @php
        $user = Auth::user();
        $persona = $user->persona;

        $nombreCompleto = trim(
            ($persona->nom_per ?? '') . ' ' .
            ($persona->ape_pat_per ?? '') . ' ' .
            ($persona->ape_mat_per ?? '')
        );

        $nombreVisible = $nombreCompleto !== '' ? $nombreCompleto : ($user->name ?? 'Usuario institucional');
        $rol = $user->getRoleNames()->first() ?? 'Sin rol asignado';

        $genero = match ($persona->gen_per ?? null) {
            'M' => 'Masculino',
            'F' => 'Femenino',
            default => 'No definido',
        };

        $documento = trim(
            ($persona->ci_per ?? '') .
            (($persona?->com_per ?? null) ? '-' . $persona->com_per : '') .
            ' ' .
            ($persona->exp_per ?? '')
        );

        $documento = $documento !== '' ? $documento : '—';

        $iniciales = collect(explode(' ', $nombreVisible))
            ->filter()
            ->take(2)
            ->map(fn($parte) => mb_substr($parte, 0, 1))
            ->implode('');

        $iniciales = mb_strtoupper($iniciales ?: 'U');

        $fechaNacimiento = !empty($persona->fec_nac_per)
            ? \Carbon\Carbon::parse($persona->fec_nac_per)->format('d/m/Y')
            : '—';

        $tieneFotoPersonalizada = Laravel\Jetstream\Jetstream::managesProfilePhotos()
            && !empty($user->profile_photo_path);
    @endphp

    <div x-data="{
                editarPerfil: false,
                editarPassword: false,

                abrirEdicionPerfil() {
                    this.editarPerfil = true;

                    this.$nextTick(() => {
                        document.getElementById('seccion-editar-perfil')?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    });
                },

                abrirPassword() {
                    this.editarPassword = true;

                    this.$nextTick(() => {
                        document.getElementById('seccion-password')?.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    });
                }
            }" x-on:perfil-actualizado.window="editarPerfil = false"
        x-on:password-actualizado.window="editarPassword = false" class="space-y-6">

        {{-- ============================================================
        CABECERA INSTITUCIONAL
        ============================================================ --}}
        <section
            class="relative overflow-hidden rounded-[2rem] border border-[var(--ui-border)] bg-[var(--ui-card)] shadow-sm">

            <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-emerald-400/20 blur-3xl">
            </div>
            <div class="pointer-events-none absolute left-1/3 top-0 h-72 w-72 rounded-full bg-sky-400/15 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-28 left-10 h-72 w-72 rounded-full bg-violet-400/10 blur-3xl">
            </div>

            <div
                class="relative border-b border-white/10 bg-gradient-to-br from-emerald-700 via-sky-700 to-slate-950 px-6 py-8 sm:px-8 dark:from-slate-950 dark:via-slate-900 dark:to-emerald-950">

                <div class="flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-center">

                        {{-- FOTO PRINCIPAL --}}
                        <div class="relative shrink-0">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <img class="h-28 w-28 rounded-[1.9rem] object-cover ring-4 ring-white/30 shadow-2xl"
                                    src="{{ $user->profile_photo_url }}" alt="{{ $nombreVisible }}">

                                <button type="button" x-on:click="abrirEdicionPerfil()"
                                    class="absolute -bottom-3 -right-3 inline-flex h-11 w-11 items-center justify-center rounded-2xl border border-white/30 bg-white text-slate-800 shadow-xl transition hover:-translate-y-0.5 hover:bg-emerald-50 hover:text-emerald-700 dark:bg-slate-950 dark:text-slate-100 dark:hover:text-emerald-300"
                                    title="Editar foto de perfil">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18A2.25 2.25 0 0 0 4.5 20.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316A2.25 2.25 0 0 0 14.44 3.75H9.56a2.25 2.25 0 0 0-1.912 1.059l-.821 1.316Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M15 13.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                            @else
                                <div
                                    class="flex h-28 w-28 items-center justify-center rounded-[1.9rem] bg-white/15 text-3xl font-black text-white ring-4 ring-white/25 shadow-2xl">
                                    {{ $iniciales }}
                                </div>
                            @endif
                        </div>

                        {{-- INFO PRINCIPAL --}}
                        <div class="min-w-0 flex-1 text-white">
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-black uppercase tracking-[0.16em] text-white/85">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 0 1 15 0" />
                                    </svg>
                                    Perfil institucional
                                </span>

                                <span
                                    class="inline-flex items-center rounded-full border border-emerald-200/30 bg-emerald-300/15 px-3 py-1 text-xs font-bold text-emerald-100">
                                    Cuenta activa
                                </span>

                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                    <span
                                        class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-bold text-white/85">
                                        {{ $tieneFotoPersonalizada ? 'Foto personalizada' : 'Foto por defecto' }}
                                    </span>
                                @endif
                            </div>

                            <h1 class="mt-3 truncate text-3xl font-black tracking-tight sm:text-4xl">
                                {{ $nombreVisible }}
                            </h1>

                            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                                <span class="rounded-full bg-white/15 px-3 py-1 font-semibold ring-1 ring-white/20">
                                    {{ $rol }}
                                </span>

                                <span class="text-white/85">
                                    {{ $user->email ?? 'Sin correo registrado' }}
                                </span>
                            </div>

                            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                                <div class="mt-5 flex flex-wrap gap-3">
                                    <button type="button" x-on:click="abrirEdicionPerfil()"
                                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-white px-5 py-3 text-sm font-black text-slate-900 shadow-xl shadow-black/10 transition hover:-translate-y-0.5 hover:bg-emerald-50 hover:text-emerald-700">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18A2.25 2.25 0 0 0 4.5 20.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316A2.25 2.25 0 0 0 14.44 3.75H9.56a2.25 2.25 0 0 0-1.912 1.059l-.821 1.316Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M15 13.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        Editar foto y datos
                                    </button>

                                    @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                                        <button type="button" x-on:click="abrirPassword()"
                                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-5 py-3 text-sm font-black text-white backdrop-blur transition hover:-translate-y-0.5 hover:bg-white/15">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5A2.25 2.25 0 0 0 19.5 19.5v-7.5a2.25 2.25 0 0 0-2.25-2.25H6.75A2.25 2.25 0 0 0 4.5 12v7.5A2.25 2.25 0 0 0 6.75 21.75Z" />
                                            </svg>
                                            Cambiar contraseña
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- MINI CARDS CABECERA --}}
                    <div class="grid gap-3 sm:grid-cols-3 xl:min-w-[520px]">
                        <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-white/60">
                                Rol
                            </p>
                            <p class="mt-1 truncate text-sm font-black text-white">
                                {{ $rol }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-white/60">
                                Acceso
                            </p>
                            <p class="mt-1 text-sm font-black text-white">
                                Institucional
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-3 backdrop-blur">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-white/60">
                                Foto
                            </p>
                            <p class="mt-1 text-sm font-black text-white">
                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                    {{ $tieneFotoPersonalizada ? 'Personalizada' : 'Por defecto' }}
                                @else
                                    No habilitada
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RESUMEN RÁPIDO --}}
            <div class="relative grid gap-3 px-6 py-5 sm:grid-cols-2 lg:grid-cols-4 sm:px-8">
                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                        Documento
                    </p>
                    <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                        {{ $documento }}
                    </p>
                </div>

                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                        Teléfono
                    </p>
                    <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                        {{ $persona->tel_per ?? '—' }}
                    </p>
                </div>

                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                        Género
                    </p>
                    <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                        {{ $genero }}
                    </p>
                </div>

                <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                        Nacimiento
                    </p>
                    <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                        {{ $fechaNacimiento }}
                    </p>
                </div>
            </div>
        </section>

        {{-- ============================================================
        RESUMEN PRINCIPAL
        ============================================================ --}}
        <section class="grid gap-6 xl:grid-cols-2">

            {{-- DATOS PERSONALES --}}
            <article class="rounded-[2rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-6 shadow-sm sm:p-8">
                <div class="mb-6">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-[var(--ui-primary)]">
                        Datos personales
                    </p>
                    <h2 class="mt-2 text-2xl font-black text-[var(--ui-text)]">
                        Información registrada
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-[var(--ui-muted)]">
                        Datos principales asociados al registro institucional del usuario.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                            Nombres
                        </p>
                        <p class="mt-2 text-sm font-black text-[var(--ui-text)]">
                            {{ $persona->nom_per ?? '—' }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                            Apellido paterno
                        </p>
                        <p class="mt-2 text-sm font-black text-[var(--ui-text)]">
                            {{ $persona->ape_pat_per ?? '—' }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                            Apellido materno
                        </p>
                        <p class="mt-2 text-sm font-black text-[var(--ui-text)]">
                            {{ $persona->ape_mat_per ?? '—' }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                            Documento
                        </p>
                        <p class="mt-2 text-sm font-black text-[var(--ui-text)]">
                            {{ $documento }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                            Fecha de nacimiento
                        </p>
                        <p class="mt-2 text-sm font-black text-[var(--ui-text)]">
                            {{ $fechaNacimiento }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                            Género
                        </p>
                        <p class="mt-2 text-sm font-black text-[var(--ui-text)]">
                            {{ $genero }}
                        </p>
                    </div>
                </div>
            </article>

            {{-- CUENTA --}}
            <article class="rounded-[2rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-6 shadow-sm sm:p-8">
                <div class="mb-6">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-sky-600 dark:text-sky-300">
                        Cuenta institucional
                    </p>
                    <h2 class="mt-2 text-2xl font-black text-[var(--ui-text)]">
                        Acceso y contacto
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-[var(--ui-muted)]">
                        Información usada para identificar y contactar al usuario dentro del sistema.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                            Rol actual
                        </p>
                        <p class="mt-2 text-sm font-black text-[var(--ui-text)]">
                            {{ $rol }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                            Correo de acceso
                        </p>
                        <p class="mt-2 break-words text-sm font-black text-[var(--ui-text)]">
                            {{ $user->email ?? '—' }}
                        </p>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                                Teléfono
                            </p>
                            <p class="mt-2 text-sm font-black text-[var(--ui-text)]">
                                {{ $persona->tel_per ?? '—' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                                Estado
                            </p>
                            <p class="mt-2 text-sm font-black text-emerald-600 dark:text-emerald-300">
                                Activo
                            </p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                            Dirección
                        </p>
                        <p class="mt-2 text-sm font-black text-[var(--ui-text)]">
                            {{ $persona->dir_per ?? '—' }}
                        </p>
                    </div>
                </div>
            </article>
        </section>

        {{-- ============================================================
        EDICIÓN DE FOTO Y CONTACTO
        ============================================================ --}}
        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
            <section id="seccion-editar-perfil"
                class="rounded-[2rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-6 shadow-sm sm:p-8">

                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-[var(--ui-primary)]">
                            Información editable
                        </p>
                        <h2 class="mt-2 text-2xl font-black text-[var(--ui-text)]">
                            Actualizar foto y datos de contacto
                        </h2>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-[var(--ui-muted)]">
                            Aquí puedes actualizar tu fotografía de perfil, correo, teléfono y dirección. La fotografía
                            se mostrará en la cabecera del sistema y en tu menú de usuario.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" x-show="!editarPerfil" x-on:click="abrirEdicionPerfil()"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18A2.25 2.25 0 0 0 4.5 20.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316A2.25 2.25 0 0 0 14.44 3.75H9.56a2.25 2.25 0 0 0-1.912 1.059l-.821 1.316Z" />
                            </svg>
                            Editar foto y datos
                        </button>

                        <button type="button" x-show="editarPerfil" x-cloak x-transition x-on:click="editarPerfil = false"
                            class="inline-flex items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-5 py-3 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)]">
                            Cancelar
                        </button>
                    </div>
                </div>

                {{-- Vista previa cerrada --}}
                <div x-show="!editarPerfil" x-transition class="mt-6 grid gap-4 lg:grid-cols-[280px,1fr]">
                    <div class="rounded-[1.6rem] border border-[var(--ui-border)] bg-[var(--ui-soft)] p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                            Foto actual
                        </p>

                        <div class="mt-4 flex items-center gap-4">
                            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                <img src="{{ $user->profile_photo_url }}" alt="{{ $nombreVisible }}"
                                    class="h-20 w-20 rounded-2xl object-cover ring-4 ring-[var(--ui-border)]">
                            @else
                                <div
                                    class="flex h-20 w-20 items-center justify-center rounded-2xl bg-[var(--ui-primary-soft)] text-xl font-black text-[var(--ui-primary)]">
                                    {{ $iniciales }}
                                </div>
                            @endif

                            <div>
                                <p class="text-sm font-black text-[var(--ui-text)]">
                                    {{ Laravel\Jetstream\Jetstream::managesProfilePhotos()
                ? ($tieneFotoPersonalizada ? 'Foto personalizada' : 'Imagen por defecto')
                : 'Fotos no habilitadas' }}
                                </p>
                                <p class="mt-1 text-xs leading-5 text-[var(--ui-muted)]">
                                    Presiona “Editar foto y datos” para cambiarla.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                                Correo electrónico
                            </p>
                            <p class="mt-2 break-words text-sm font-black text-[var(--ui-text)]">
                                {{ $user->email ?? '—' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                                Teléfono
                            </p>
                            <p class="mt-2 text-sm font-black text-[var(--ui-text)]">
                                {{ $persona->tel_per ?? '—' }}
                            </p>
                        </div>

                        <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-4">
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                                Dirección
                            </p>
                            <p class="mt-2 text-sm font-black text-[var(--ui-text)]">
                                {{ $persona->dir_per ?? '—' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Formulario abierto --}}
                <div x-show="editarPerfil" x-transition x-cloak class="mt-6">
                    @livewire('profile.update-profile-information-form')
                </div>
            </section>
        @endif

        {{-- ============================================================
        CAMBIO DE CONTRASEÑA
        ============================================================ --}}
        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
            <section id="seccion-password"
                class="rounded-[2rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-6 shadow-sm sm:p-8">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-sky-600 dark:text-sky-300">
                            Seguridad de acceso
                        </p>
                        <h2 class="mt-2 text-2xl font-black text-[var(--ui-text)]">
                            Actualizar contraseña
                        </h2>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-[var(--ui-muted)]">
                            Cambia tu contraseña institucional usando validación de seguridad y confirmación de coincidencia.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="button" x-show="!editarPassword" x-on:click="abrirPassword()"
                            class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                            Cambiar contraseña
                        </button>

                        <button type="button" x-show="editarPassword" x-cloak x-transition x-on:click="editarPassword = false"
                            class="inline-flex items-center justify-center rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-5 py-3 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-soft)]">
                            Cancelar
                        </button>
                    </div>
                </div>

                <div x-show="!editarPassword" x-transition
                    class="mt-6 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] p-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ui-muted)]">
                                Contraseña actual
                            </p>
                            <p class="mt-2 text-sm font-black tracking-[0.35em] text-[var(--ui-text)]">
                                ••••••••••••
                            </p>
                            <p class="mt-1 text-xs text-[var(--ui-muted)]">
                                La contraseña no se muestra por seguridad.
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                            Acceso protegido
                        </div>
                    </div>
                </div>

                <div x-show="editarPassword" x-transition x-cloak class="mt-6">
                    @livewire('profile.update-password-form')
                </div>
            </section>
        @endif

        {{-- ============================================================
        SEGURIDAD ADICIONAL
        ============================================================ --}}
        <section class="space-y-6">
            <div class="rounded-[2rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-6 shadow-sm sm:p-8">
                <div class="mb-6">
                    <p class="text-sm font-black uppercase tracking-[0.18em] text-violet-600 dark:text-violet-300">
                        Seguridad adicional
                    </p>
                    <h2 class="mt-2 text-2xl font-black text-[var(--ui-text)]">
                        Protección de la cuenta
                    </h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-[var(--ui-muted)]">
                        Administra mecanismos de protección, autenticación reforzada y sesiones activas.
                    </p>
                </div>

                <div class="space-y-6">
                    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                        @livewire('profile.two-factor-authentication-form')
                    @endif

                    @livewire('profile.logout-other-browser-sessions-form')
                </div>
            </div>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
                <div
                    class="rounded-[2rem] border border-rose-200 bg-rose-50/60 p-6 shadow-sm dark:border-rose-400/20 dark:bg-rose-400/10 sm:p-8">
                    <div class="mb-6">
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-rose-600 dark:text-rose-300">
                            Zona crítica
                        </p>
                        <h2 class="mt-2 text-2xl font-black text-rose-950 dark:text-rose-100">
                            Gestión sensible de cuenta
                        </h2>
                        <p class="mt-2 max-w-3xl text-sm leading-6 text-rose-800/80 dark:text-rose-100/80">
                            Acciones de alto impacto sobre la cuenta institucional. Úsalas solo si corresponde.
                        </p>
                    </div>

                    @livewire('profile.delete-user-form')
                </div>
            @endif
        </section>
    </div>
@endsection