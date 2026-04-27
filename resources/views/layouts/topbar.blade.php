<div class="px-5 pt-5 sm:px-6 lg:px-8">
    <div class="soft-panel card-shadow rounded-[1.8rem] px-5 py-4 sm:px-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.18em] text-emerald-700">
                    {{ $pageKicker ?? 'Sistema institucional' }}
                </p>
                <h1 class="mt-1 text-2xl font-black tracking-tight text-slate-950 sm:text-3xl">
                    {{ $pageTitle ?? 'SAVP – TIS 3' }}
                </h1>
                <p class="mt-2 text-sm leading-7 text-slate-600">
                    {{ $pageDescription ?? 'Panel de gestión institucional del sistema.' }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <div
                    class="hidden rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-600 md:block">
                    {{ now()->format('d/m/Y') }}
                </div>

                <div class="relative">
                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button
                                class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-2.5 shadow-sm transition hover:bg-slate-50">
                                <div class="text-right">
                                    <p class="text-sm font-bold text-slate-900">{{ Auth::user()->name ?? 'Usuario' }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ Auth::user()->getRoleNames()->first() ?? 'Sin rol' }}
                                    </p>
                                </div>

                                @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                                    <img class="h-10 w-10 rounded-full object-cover"
                                        src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
                                @else
                                    <div
                                        class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-200 text-sm font-bold text-slate-700">
                                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="block px-4 py-2 text-xs text-slate-500">
                                Cuenta
                            </div>

                            <x-dropdown-link :href="route('profile.show')">
                                Ver perfil
                            </x-dropdown-link>

                            <div class="border-t border-slate-200"></div>

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