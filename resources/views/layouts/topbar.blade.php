@php
    $user = Auth::user();

    $persona = $user?->persona;

    $displayName = $persona
        ? trim(
            collect([$persona->nom_per, $persona->ape_pat_per, $persona->ape_mat_per])
                ->filter()
                ->implode(' '),
        )
        : $user?->email ?? 'Usuario';

    $role = $user?->getRoleNames()->first() ?? 'Sin rol';

    $initials = collect(preg_split('/\s+/', $displayName))
        ->filter()
        ->take(2)
        ->map(fn($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
@endphp

<div class="px-4 pt-4 sm:px-6 lg:px-8">
    <header class="
            ui-card rounded-[1.8rem]
            px-4 py-4 sm:px-5
        ">
        <div
            class="
                grid gap-4
                xl:grid-cols-[minmax(220px,0.85fr)_minmax(380px,1.4fr)_auto]
                xl:items-center
            ">

            {{-- ================================================= --}}
            {{-- CONTEXTO DE PÁGINA --}}
            {{-- ================================================= --}}

            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <span
                        class="
                            inline-flex h-2 w-2
                            rounded-full
                        "
                        style="background: var(--ui-primary);"></span>

                    <p class="ui-kicker">
                        {{ $pageKicker ?? 'Panel administrativo' }}
                    </p>
                </div>

                <h1
                    class="
                        ui-title mt-1 truncate
                        text-xl font-black tracking-tight
                        sm:text-2xl
                    ">
                    {{ $pageTitle ?? 'SAVP – TIS 3' }}
                </h1>

                <p
                    class="
                        ui-muted mt-1 hidden
                        truncate text-xs sm:block
                    ">
                    {{ $pageDescription ?? 'Gestión académica e institucional.' }}
                </p>
            </div>


            {{-- ================================================= --}}
            {{-- BUSCADOR GLOBAL --}}
            {{-- ================================================= --}}

            <div class="order-3 xl:order-none">
                <livewire:admin.global-search />
            </div>


            {{-- ================================================= --}}
            {{-- ACCIONES --}}
            {{-- ================================================= --}}

            <div
                class="
                    flex items-center justify-end gap-2
                    sm:gap-3
                ">
                {{-- Fecha --}}
                <div
                    class="
                        ui-card-soft hidden
                        h-11 items-center gap-2
                        rounded-2xl px-3
                        lg:flex
                    ">
                    <svg class="h-4 w-4" style="color: var(--ui-muted);" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M6.75 3v2.25M17.25 3v2.25M3.75 9h16.5m-15 12h13.5A1.5 1.5 0 0 0 20.25 19.5V6.75a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5V19.5a1.5 1.5 0 0 0 1.5 1.5Z" />
                    </svg>

                    <span class="text-xs font-bold" style="color: var(--ui-text-soft);">
                        {{ now()->format('d/m/Y') }}
                    </span>
                </div>


                {{-- Tema --}}
                <button type="button" onclick="window.themeManager.toggle()" class="ui-icon-btn h-11 w-11 border"
                    style="
                        border-color: var(--ui-border);
                        background: var(--ui-surface);
                    "
                    title="Cambiar tema" aria-label="Cambiar tema">
                    <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.364-6.364-1.591 1.591M7.227 16.773l-1.591 1.591m12.728 0-1.591-1.591M7.227 7.227 5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>

                    <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75 9.75 9.75 0 0 1 8.25 6c0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25 9.75 9.75 0 0 0 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                </button>


                {{-- Usuario --}}
                <x-dropdown align="right" width="64">
                    <x-slot name="trigger">
                        <button type="button"
                            class="
                                flex h-11 items-center gap-3
                                rounded-2xl border px-2
                                transition sm:px-3
                            "
                            style="
                                background: var(--ui-surface);
                                border-color: var(--ui-border);
                                color: var(--ui-text);
                            ">
                            <div class="
                                    flex h-8 w-8 items-center
                                    justify-center rounded-xl
                                    text-xs font-black
                                "
                                style="
                                    background: var(--ui-surface-muted);
                                    color: var(--ui-text-soft);
                                ">
                                {{ $initials ?: 'U' }}
                            </div>

                            <div
                                class="
                                    hidden max-w-[170px]
                                    text-left lg:block
                                ">
                                <p class="truncate text-xs font-black">
                                    {{ $displayName }}
                                </p>

                                <p class="truncate text-[10px]" style="color: var(--ui-muted);">
                                    {{ $role }}
                                </p>
                            </div>

                            <svg class="hidden h-4 w-4 sm:block" style="color: var(--ui-muted);" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="m19.5 9-7.5 7.5L4.5 9" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">

                        <div class="px-4 py-3">
                            <p class="truncate text-sm font-black" style="color: var(--ui-text);">
                                {{ $displayName }}
                            </p>

                            <p class="mt-0.5 truncate text-xs" style="color: var(--ui-muted);">
                                {{ $user?->email }}
                            </p>

                            <p class="mt-1 text-[11px] font-bold" style="color: var(--ui-primary);">
                                {{ $role }}
                            </p>
                        </div>

                        <div class="border-t" style="border-color: var(--ui-border);"></div>

                        <x-dropdown-link :href="route('profile.show')">
                            Ver perfil
                        </x-dropdown-link>

                        <button type="button" onclick="window.themeManager.toggle()"
                            class="
                                block w-full px-4 py-2
                                text-left text-sm transition
                            "
                            style="color: var(--ui-text-soft);">
                            Cambiar tema
                        </button>

                        <div class="border-t" style="border-color: var(--ui-border);"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                onclick="
                                    event.preventDefault();
                                    this.closest('form').submit();
                                ">
                                Cerrar sesión
                            </x-dropdown-link>
                        </form>

                    </x-slot>
                </x-dropdown>
            </div>
        </div>
    </header>
</div>
