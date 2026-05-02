<div class="px-5 pt-5 sm:px-6 lg:px-8">
    <div class="ui-card rounded-[1.8rem] px-5 py-4 sm:px-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="ui-kicker">
                    {{ $pageKicker ?? 'Sistema institucional' }}
                </p>

                <h1 class="ui-title mt-1 text-2xl font-black tracking-tight sm:text-3xl">
                    {{ $pageTitle ?? 'SAVP – TIS 3' }}
                </h1>

                <p class="ui-muted mt-2 text-sm leading-7">
                    {{ $pageDescription ?? 'Panel de gestión institucional del sistema.' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                {{-- Fecha --}}
                <div class="ui-card-soft hidden px-4 py-3 text-sm font-semibold md:block">
                    {{ now()->format('d/m/Y') }}
                </div>

                {{-- Botón modo claro / oscuro --}}
                <button type="button" onclick="window.themeManager.toggle()" class="ui-icon-btn group h-11 w-11 border"
                    style="border-color: var(--ui-border); background: var(--ui-surface);" title="Cambiar tema">

                    {{-- Icono sol: visible en modo oscuro --}}
                    <svg class="hidden h-5 w-5 dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 3v2.25m0 13.5V21m9-9h-2.25M5.25 12H3m15.364-6.364-1.591 1.591M7.227 16.773l-1.591 1.591m12.728 0-1.591-1.591M7.227 7.227 5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>

                    {{-- Icono luna: visible en modo claro --}}
                    <svg class="h-5 w-5 dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75 9.75 9.75 0 0 1 8.25 6c0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25 9.75 9.75 0 0 0 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                </button>

                {{-- Usuario --}}
                <div class="relative">
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-3 rounded-2xl border px-4 py-2.5 shadow-sm transition"
                                style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-text);">

                                <div class="text-right">
                                    <p class="text-sm font-bold" style="color: var(--ui-text);">
                                        {{ Auth::user()->name ?? Auth::user()->email ?? 'Usuario' }}
                                    </p>

                                    <p class="text-xs" style="color: var(--ui-muted);">
                                        {{ Auth::user()->getRoleNames()->first() ?? 'Sin rol' }}
                                    </p>
                                </div>

                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                    <img class="h-10 w-10 rounded-full object-cover ring-2"
                                        style="--tw-ring-color: var(--ui-border);"
                                        src="{{ Auth::user()->profile_photo_url }}"
                                        alt="{{ Auth::user()->name ?? 'Usuario' }}" />
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold"
                                        style="background: var(--ui-surface-muted); color: var(--ui-text-soft);">
                                        {{ strtoupper(substr(Auth::user()->name ?? Auth::user()->email ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="block px-4 py-2 text-xs" style="color: var(--ui-muted);">
                                Cuenta
                            </div>

                            <x-dropdown-link :href="route('profile.show')">
                                Ver perfil
                            </x-dropdown-link>

                            <div class="border-t" style="border-color: var(--ui-border);"></div>

                            <button type="button" onclick="window.themeManager.toggle()"
                                class="block w-full px-4 py-2 text-left text-sm leading-5 transition"
                                style="color: var(--ui-text-soft);">
                                Cambiar tema
                            </button>

                            <div class="border-t" style="border-color: var(--ui-border);"></div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    Cerrar sesión
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </div>
    </div>
</div>