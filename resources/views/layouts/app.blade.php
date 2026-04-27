<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'SAVP - TIS 3'))</title>

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

        .topbar-shadow {
            box-shadow:
                0 10px 30px rgba(15, 23, 42, 0.05),
                0 4px 12px rgba(15, 23, 42, 0.03);
        }
    </style>

    @stack('styles')
</head>

<body class="font-sans antialiased text-slate-800">
    <x-banner />

    <div x-data="{ sidebarOpen: true, openUser: false }" class="dashboard-bg relative">
        <div class="relative z-10 flex min-h-screen">

            @include('layouts.sidebar')

            <div class="flex min-h-screen flex-1 flex-col transition-all duration-300"
                :class="sidebarOpen ? 'lg:ml-72' : 'lg:ml-20'">

                @php
                    $persona = Auth::user()->persona;
                    $nombreCompleto = trim(
                        ($persona->nom_per ?? '') . ' ' .
                        ($persona->ape_pat_per ?? '') . ' ' .
                        ($persona->ape_mat_per ?? '')
                    );
                    $inicial = strtoupper(substr($persona->nom_per ?? 'U', 0, 1));
                    $rol = Auth::user()->getRoleNames()->first() ?? 'Sin rol asignado';
                @endphp

                {{-- TOPBAR OPERATIVA --}}
                <header class="sticky top-0 z-30 px-5 pt-5 sm:px-6 lg:px-8">
                    <div class="soft-panel topbar-shadow rounded-[2rem] px-6 py-4 sm:px-7">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-center">

                            {{-- FECHA --}}
                            <div
                                class="rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-medium text-slate-600 shadow-sm">
                                {{ now()->format('d/m/Y') }}
                            </div>

                            {{-- BUSCADOR --}}
                            <div
                                class="flex w-full items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm xl:w-[800px]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 shrink-0"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>

                                <input type="text" placeholder="Buscar módulos, registros o información..."
                                    class="w-full border-none bg-transparent p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-0">
                            </div>

                            {{-- USUARIO --}}
                            <div class="relative">
                                <button @click="openUser = !openUser"
                                    class="flex min-w-[290px] items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm transition hover:bg-slate-50">

                                    <div class="flex items-center gap-3 min-w-0">
                                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                            <img class="h-11 w-11 rounded-full object-cover shrink-0"
                                                src="{{ Auth::user()->profile_photo_url }}"
                                                alt="{{ $nombreCompleto ?: 'Usuario' }}">
                                        @else
                                            <div
                                                class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-200 text-sm font-bold text-slate-700 shrink-0">
                                                {{ $inicial }}
                                            </div>
                                        @endif

                                        <div class="min-w-0 text-left">
                                            <p class="truncate text-sm font-bold text-slate-900">
                                                {{ $nombreCompleto ?: 'Usuario' }}
                                            </p>
                                            <p class="truncate text-xs text-slate-500">
                                                {{ $rol }}
                                            </p>
                                        </div>
                                    </div>

                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4 text-slate-500 transition-transform duration-300 shrink-0"
                                        :class="{ 'rotate-180': openUser }" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </button>

                                {{-- Dropdown usuario --}}
                                <div x-show="openUser" @click.outside="openUser = false" x-transition x-cloak
                                    class="absolute right-0 mt-3 w-72 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">

                                    <div class="border-b border-slate-100 px-4 py-4">
                                        <p class="text-sm font-bold text-slate-900">
                                            {{ $nombreCompleto ?: 'Usuario' }}
                                        </p>
                                        <p class="mt-1 text-xs text-slate-500">
                                            {{ Auth::user()->email ?? 'correo@ejemplo.com' }}
                                        </p>
                                        <p
                                            class="mt-2 inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                            {{ $rol }}
                                        </p>
                                    </div>

                                    <div class="p-2">
                                        <a href="{{ route('profile.show') }}"
                                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50">
                                            <span>👤</span>
                                            <span>Mi perfil</span>
                                        </a>

                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit"
                                                class="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-red-600 transition hover:bg-red-50">
                                                <span>⏻</span>
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