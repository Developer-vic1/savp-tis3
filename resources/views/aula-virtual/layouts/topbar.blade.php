@php
    $user = Auth::user();
    $persona = $user?->persona;
    $nombreCompleto = trim(($persona->nom_per ?? '') . ' ' . ($persona->ape_pat_per ?? '') . ' ' . ($persona->ape_mat_per ?? ''));
    $nombreUsuario = $nombreCompleto ?: ($user->name ?? $user->email ?? 'Usuario');
    $rol = $user?->hasRole('Docente') ? 'Docente' : ($user?->hasRole('Estudiante') ? 'Estudiante' : ($user?->getRoleNames()->first() ?? 'Aula Virtual'));
@endphp

<header class="sticky top-0 z-30 px-5 pt-5 sm:px-6 lg:px-8">
    <div class="aula-shell rounded-[1.8rem] border px-5 py-4 shadow-sm sm:px-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <button type="button" class="ui-icon-btn border lg:hidden"
                    style="border-color: var(--ui-border); background: var(--ui-surface);"
                    @click="mobileSidebar = true" title="Abrir menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <div>
                    <p class="ui-kicker">SAVP-TIS3 Aula Virtual</p>
                    <h1 class="ui-title mt-1 text-xl font-black tracking-tight sm:text-2xl">@yield('page-title', 'Inicio')</h1>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <span class="ui-badge-info">{{ now()->format('d/m/Y') }}</span>

                <button type="button" onclick="window.themeManager.toggle()" class="ui-icon-btn h-11 w-11 border"
                    style="border-color: var(--ui-border); background: var(--ui-surface);" title="Cambiar tema">
                    <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75 9.75 9.75 0 0 1 8.25 6c0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25 9.75 9.75 0 0 0 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                    <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.364-6.364-1.591 1.591M7.227 16.773l-1.591 1.591m12.728 0-1.591-1.591M7.227 7.227 5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </button>

                <div class="relative">
                    <button type="button" @click="openUser = !openUser"
                        class="flex min-w-[240px] items-center justify-between gap-3 rounded-2xl border px-4 py-3 text-left shadow-sm transition hover:-translate-y-0.5"
                        style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-text);">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black">{{ $nombreUsuario }}</p>
                            <p class="ui-muted truncate text-xs">{{ $rol }}</p>
                        </div>
                        <span class="ui-badge-success">{{ strtoupper(substr($nombreUsuario, 0, 1)) }}</span>
                    </button>

                    <div x-show="openUser" @click.outside="openUser = false" x-transition x-cloak
                        class="absolute right-0 mt-3 w-72 overflow-hidden rounded-2xl border shadow-xl"
                        style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-text);">
                        <div class="border-b px-4 py-4" style="border-color: var(--ui-border);">
                            <p class="text-sm font-black">{{ $nombreUsuario }}</p>
                            <p class="ui-muted mt-1 text-xs">{{ $user->email ?? '' }}</p>
                            <p class="mt-2"><span class="ui-badge-info">{{ $rol }}</span></p>
                        </div>

                        <div class="p-2">
                            <a href="{{ route('welcome') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition hover:bg-[var(--ui-surface-muted)]"
                                style="color: var(--ui-text-soft);">Volver al welcome</a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="mt-1 flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition"
                                    style="color: var(--ui-danger);"
                                    onmouseover="this.style.background='var(--ui-danger-soft)'"
                                    onmouseout="this.style.background='transparent'">
                                    Cerrar sesion
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
