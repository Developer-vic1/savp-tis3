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

        $rol = $user->getRoleNames()->first() ?? 'Sin rol asignado';

        $genero = match ($persona->gen_per ?? null) {
            'M' => 'Masculino',
            'F' => 'Femenino',
            default => 'No definido',
        };
    @endphp

    <div class="space-y-6">
        {{-- CABECERA DEL PERFIL --}}
        <section class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm">
            <div class="bg-gradient-to-r from-emerald-600 via-emerald-500 to-sky-500 px-6 py-8 sm:px-8">
                <div class="flex flex-col gap-6 md:flex-row md:items-center">
                    <div class="shrink-0">
                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <img class="h-24 w-24 rounded-3xl object-cover ring-4 ring-white/40"
                                src="{{ $user->profile_photo_url }}" alt="{{ $nombreCompleto ?: 'Usuario' }}">
                        @else
                            <div
                                class="flex h-24 w-24 items-center justify-center rounded-3xl bg-white/20 text-3xl font-black text-white ring-4 ring-white/30">
                                {{ strtoupper(substr($persona->nom_per ?? 'U', 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1 text-white">
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-white/80">
                            Perfil institucional
                        </p>

                        <h1 class="mt-2 truncate text-3xl font-black tracking-tight">
                            {{ $nombreCompleto ?: 'Usuario' }}
                        </h1>

                        <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                            <span class="rounded-full bg-white/15 px-3 py-1 font-medium ring-1 ring-white/20">
                                {{ $rol }}
                            </span>

                            <span class="text-white/90">
                                {{ $user->email }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- DATOS PERSONALES + RESUMEN DE CUENTA --}}
        <section class="grid gap-6 xl:grid-cols-2">
            {{-- DATOS PERSONALES --}}
            <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">
                        Datos personales
                    </p>
                    <h2 class="mt-2 text-2xl font-black text-slate-950">
                        Información personal registrada
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Estos datos pertenecen al registro institucional y no se editan desde esta sección.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Nombres</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">{{ $persona->nom_per ?? '—' }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Apellido paterno</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">{{ $persona->ape_pat_per ?? '—' }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Apellido materno</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">{{ $persona->ape_mat_per ?? '—' }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">CI</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">
                            {{ $persona->ci_per ?? '—' }}{{ $persona->com_per ? '-' . $persona->com_per : '' }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Expedición</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">{{ $persona->exp_per ?? '—' }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Fecha de nacimiento</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">
                            {{ !empty($persona->fec_nac_per) ? \Carbon\Carbon::parse($persona->fec_nac_per)->format('d/m/Y') : '—' }}
                        </p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:col-span-2">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Género</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">{{ $genero }}</p>
                    </div>
                </div>
            </div>

            {{-- RESUMEN DE CUENTA + CONTRASEÑA --}}
            <section x-data="{ editandoPassword: false }" x-on:password-actualizado.window="editandoPassword = false"
                class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-6">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em] text-sky-700">
                        Resumen de cuenta
                    </p>
                    <h2 class="mt-2 text-2xl font-black text-slate-950">
                        Información de acceso
                    </h2>
                    <p class="mt-2 text-sm leading-6 text-slate-600">
                        Datos principales asociados a tu cuenta dentro del sistema.
                    </p>
                </div>

                <div class="space-y-4">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Rol actual</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">{{ $rol }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Correo de acceso</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">{{ $user->email ?? '—' }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Teléfono actual</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">{{ $persona->tel_per ?? '—' }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Dirección actual</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">{{ $persona->dir_per ?? '—' }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Contraseña</p>
                                <p class="mt-2 text-sm font-bold tracking-[0.3em] text-slate-900">••••••••••••</p>
                            </div>

                            <div class="flex gap-3">
                                <button type="button" x-show="!editandoPassword" @click="editandoPassword = true"
                                    class="rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20">
                                    Cambiar contraseña
                                </button>

                                <button type="button" x-show="editandoPassword" x-transition
                                    @click="editandoPassword = false"
                                    class="rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                    <div x-show="editandoPassword" x-transition x-cloak class="mt-6">
                        @livewire('profile.update-password-form')
                    </div>
                @endif
            </section>
        </section>

        {{-- DATOS DE CONTACTO EDITABLES --}}
        @if (Laravel\Fortify\Features::canUpdateProfileInformation())
            <section x-data="{ editandoContacto: false }" x-on:perfil-actualizado.window="editandoContacto = false"
                class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">
                            Información de contacto
                        </p>
                        <h2 class="mt-2 text-2xl font-black text-slate-950">
                            Actualizar perfil
                        </h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Puedes modificar tu teléfono, dirección, correo y foto de perfil.
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" x-show="!editandoContacto" @click="editandoContacto = true"
                            class="rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-500/20">
                            Editar información
                        </button>

                        <button type="button" x-show="editandoContacto" x-transition @click="editandoContacto = false"
                            class="rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700">
                            Cancelar
                        </button>
                    </div>
                </div>

                {{-- Vista previa en solo lectura --}}
                <div x-show="!editandoContacto" x-transition class="mt-6 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Correo electrónico</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">{{ $user->email ?? '—' }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Teléfono</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">{{ $persona->tel_per ?? '—' }}</p>
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Dirección</p>
                        <p class="mt-2 text-sm font-bold text-slate-900">{{ $persona->dir_per ?? '—' }}</p>
                    </div>
                </div>

                {{-- Formulario desplegable --}}
                <div x-show="editandoContacto" x-transition x-cloak class="mt-6">
                    @livewire('profile.update-profile-information-form')
                </div>
            </section>
        @endif

        {{-- SEGURIDAD ADICIONAL --}}
        @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
            <section>
                @livewire('profile.two-factor-authentication-form')
            </section>
        @endif

        <section>
            @livewire('profile.logout-other-browser-sessions-form')
        </section>
    </div>
@endsection