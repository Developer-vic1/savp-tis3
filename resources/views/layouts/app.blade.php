<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'SAVP - TIS 3'))</title>

    {{-- Evita parpadeo visual al cargar el tema --}}
    <script>
        (function () {
            const theme = localStorage.getItem('savp-theme') || 'light';

            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                document.documentElement.dataset.theme = 'dark';
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.dataset.theme = 'light';
            }
        })();
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        [x-cloak] {
            display: none !important;
        }

        .dashboard-bg {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(16, 185, 129, 0.10), transparent 24%),
                radial-gradient(circle at top right, rgba(14, 165, 233, 0.10), transparent 24%),
                linear-gradient(to bottom, var(--ui-bg), var(--ui-bg-soft));
            color: var(--ui-text);
            position: relative;
            transition:
                background-color 180ms ease,
                color 180ms ease;
        }

        .dashboard-bg::before {
            content: "";
            position: absolute;
            inset: 0;
            opacity: .055;
            background-image:
                linear-gradient(var(--ui-muted) 1px, transparent 1px),
                linear-gradient(90deg, var(--ui-muted) 1px, transparent 1px);
            background-size: 28px 28px;
            pointer-events: none;
        }

        .soft-panel {
            background: color-mix(in srgb, var(--ui-surface) 88%, transparent);
            border: 1px solid var(--ui-border);
            color: var(--ui-text);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition:
                background-color 180ms ease,
                border-color 180ms ease,
                color 180ms ease;
        }

        .card-shadow {
            box-shadow: var(--ui-shadow-md);
        }

        .topbar-shadow {
            box-shadow: var(--ui-shadow-sm);
        }

        .topbar-search::placeholder {
            color: var(--ui-muted);
        }
    </style>

    @stack('styles')
</head>

<body class="font-sans antialiased ui-page">
    <x-banner />

    <div x-data="{ sidebarOpen: true, openUser: false }" class="dashboard-bg relative">
        <div class="relative z-10 flex min-h-screen">

            @include('layouts.sidebar')

            <div class="flex min-h-screen flex-1 flex-col transition-all duration-300"
                :class="sidebarOpen ? 'lg:ml-72' : 'lg:ml-20'">

                @php
                    $user = Auth::user();
                    $persona = $user?->persona;

                    $nombreCompleto = trim(
                        ($persona->nom_per ?? '') . ' ' .
                        ($persona->ape_pat_per ?? '') . ' ' .
                        ($persona->ape_mat_per ?? '')
                    );

                    $nombreUsuario = $nombreCompleto ?: ($user->name ?? $user->email ?? 'Usuario');
                    $correoUsuario = $user->email ?? 'correo@ejemplo.com';
                    $inicial = strtoupper(substr($persona->nom_per ?? $user->name ?? $user->email ?? 'U', 0, 1));
                    $rol = $user?->getRoleNames()->first() ?? 'Sin rol asignado';
                @endphp

                {{-- TOPBAR OPERATIVA --}}
                <header class="sticky top-0 z-30 px-5 pt-5 sm:px-6 lg:px-8">
                    <div class="soft-panel topbar-shadow rounded-[2rem] px-6 py-4 sm:px-7">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-center">

                            {{-- FECHA --}}
                            <div class="ui-card-soft px-5 py-3 text-sm font-medium">
                                <span class="ui-muted">{{ now()->format('d/m/Y') }}</span>
                            </div>

                            {{-- BUSCADOR --}}
                            <div class="flex w-full items-center gap-3 rounded-2xl border px-4 py-3 shadow-sm xl:w-[800px]"
                                style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-text);">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0"
                                    style="color: var(--ui-muted);" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>

                                <input type="text" placeholder="Buscar módulos, registros o información..."
                                    class="topbar-search w-full border-none bg-transparent p-0 text-sm focus:outline-none focus:ring-0"
                                    style="color: var(--ui-text);">
                            </div>

                            {{-- BOTÓN MODO CLARO / OSCURO --}}
                            <button type="button" onclick="window.themeManager.toggle()"
                                class="inline-flex h-[50px] w-[50px] shrink-0 items-center justify-center rounded-2xl border shadow-sm transition hover:-translate-y-0.5"
                                style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-text);"
                                title="Cambiar tema">

                                {{-- Luna: visible en modo claro --}}
                                <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75 9.75 9.75 0 0 1 8.25 6c0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25 9.75 9.75 0 0 0 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                                </svg>

                                {{-- Sol: visible en modo oscuro --}}
                                <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.364-6.364-1.591 1.591M7.227 16.773l-1.591 1.591m12.728 0-1.591-1.591M7.227 7.227 5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                                </svg>
                            </button>

                            {{-- USUARIO --}}
                            <div class="relative">
                                <button type="button" @click="openUser = !openUser"
                                    class="flex min-w-[290px] items-center justify-between gap-4 rounded-2xl border px-4 py-3 shadow-sm transition hover:-translate-y-0.5"
                                    style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-text);">

                                    <div class="flex min-w-0 items-center gap-3">
                                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                            <img class="h-11 w-11 shrink-0 rounded-full object-cover ring-2"
                                                style="--tw-ring-color: var(--ui-border);"
                                                src="{{ $user->profile_photo_url }}" alt="{{ $nombreUsuario }}">
                                        @else
                                            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full text-sm font-bold"
                                                style="background: var(--ui-surface-muted); color: var(--ui-text-soft);">
                                                {{ $inicial }}
                                            </div>
                                        @endif

                                        <div class="min-w-0 text-left">
                                            <p class="truncate text-sm font-bold" style="color: var(--ui-text);">
                                                {{ $nombreUsuario }}
                                            </p>
                                            <p class="truncate text-xs" style="color: var(--ui-muted);">
                                                {{ $rol }}
                                            </p>
                                        </div>
                                    </div>

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 shrink-0 transition-transform duration-300"
                                        style="color: var(--ui-muted);" :class="{ 'rotate-180': openUser }" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                {{-- Dropdown usuario --}}
                                <div x-show="openUser" @click.outside="openUser = false" x-transition x-cloak
                                    class="absolute right-0 mt-3 w-72 overflow-hidden rounded-2xl border shadow-xl"
                                    style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-text);">

                                    <div class="border-b px-4 py-4" style="border-color: var(--ui-border);">
                                        <p class="text-sm font-bold" style="color: var(--ui-text);">
                                            {{ $nombreUsuario }}
                                        </p>

                                        <p class="mt-1 text-xs" style="color: var(--ui-muted);">
                                            {{ $correoUsuario }}
                                        </p>

                                        <p class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1"
                                            style="background: var(--ui-primary-soft); color: var(--ui-primary); --tw-ring-color: var(--ui-primary-border);">
                                            {{ $rol }}
                                        </p>
                                    </div>

                                    <div class="p-2">
                                        <a href="{{ route('profile.show') }}"
                                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition hover:bg-[var(--ui-surface-muted)]"
                                            style="color: var(--ui-text-soft);">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a8.25 8.25 0 1 1 15 0" />
                                            </svg>
                                            <span>Mi perfil</span>
                                        </a>

                                        <button type="button" onclick="window.themeManager.toggle()"
                                            class="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition hover:bg-[var(--ui-surface-muted)]"
                                            style="color: var(--ui-text-soft);">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path class="dark:hidden" stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.8"
                                                    d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75 9.75 9.75 0 0 1 8.25 6c0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25 9.75 9.75 0 0 0 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                                                <path class="hidden dark:block" stroke-linecap="round"
                                                    stroke-linejoin="round" stroke-width="1.8"
                                                    d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.364-6.364-1.591 1.591M7.227 16.773l-1.591 1.591m12.728 0-1.591-1.591M7.227 7.227 5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                                            </svg>
                                            <span>Cambiar tema</span>
                                        </button>

                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf

                                            <button type="submit"
                                                class="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition"
                                                style="color: var(--ui-danger);"
                                                onmouseover="this.style.background='var(--ui-danger-soft)'"
                                                onmouseout="this.style.background='transparent'">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                                                </svg>
                                                <span>Cerrar sesión</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                <main class="flex-1 px-5 py-5 sm:px-6 lg:px-8">
                    @yield('content')
                </main>
            </div>
        </div>
    </div>

    @stack('modals')
    @livewireScripts
    @stack('scripts')

    <script>
        window.addEventListener('swal:success', event => {
            Swal.fire({
                icon: 'success',
                title: event.detail.title ?? 'Operación exitosa',
                text: event.detail.text ?? 'Los cambios se guardaron correctamente.',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#059669'
            });
        });

        window.addEventListener('swal:error', event => {
            Swal.fire({
                icon: 'error',
                title: event.detail.title ?? 'Ocurrió un error',
                text: event.detail.text ?? 'No se pudo completar la operación.',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#dc2626'
            });
        });

        window.addEventListener('swal:warning', event => {
            Swal.fire({
                icon: 'warning',
                title: event.detail.title ?? 'Atención',
                text: event.detail.text ?? 'Revisa la información ingresada.',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#d97706'
            });
        });

        window.addEventListener('swal:info', event => {
            Swal.fire({
                icon: 'info',
                title: event.detail.title ?? 'Información',
                text: event.detail.text ?? 'Se realizó una acción en el sistema.',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#2563eb'
            });
        });
    </script>
</body>

</html>