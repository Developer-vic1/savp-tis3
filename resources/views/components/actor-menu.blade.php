@php
    $user = auth()->user();
    $resolver = app(\App\Services\RoleDashboardResolver::class);
    $role = $user ? $resolver->roleFor($user) : null;
    $dashboard = $user ? $resolver->routeFor($user) : null;
    $links = $user ? collect(app(\App\Support\WorkspaceNavigation::class)->for($user))->groupBy('group') : collect();
@endphp
<div x-show="mobileSidebar" x-cloak class="fixed inset-0 z-40 bg-slate-950/50 backdrop-blur-sm lg:hidden"
    @click="mobileSidebar = false" aria-hidden="true"></div>
<aside id="workspace-sidebar" aria-label="Navegación de {{ $role ?? 'SAVP' }}"
    class="fixed left-0 top-0 z-50 flex h-screen flex-col border-r shadow-md transition-all duration-300"
    :class="[sidebarOpen ? 'lg:w-72' : 'lg:w-20', mobileSidebar ? 'translate-x-0 w-72' : '-translate-x-full w-72 lg:translate-x-0']"
    style="background: color-mix(in srgb, var(--ui-surface) 94%, transparent); border-color: var(--ui-border); color: var(--ui-text);">
    <header class="flex items-center justify-between border-b p-4" style="border-color: var(--ui-border);">
        <a href="{{ $dashboard ? route($dashboard) : route('dashboard') }}" class="flex min-w-0 items-center gap-3">
            <img src="{{ asset('image/LOGO FT3 A.jpg') }}" alt="Logo Franz Tamayo 3" class="h-11 w-11 shrink-0 rounded-2xl object-contain">
            <span x-show="sidebarOpen || mobileSidebar" x-cloak class="min-w-0">
                <span class="block truncate text-sm font-black">Franz Tamayo N°3</span>
                <span class="ui-muted block text-xs">SAVP – TIS 3</span>
            </span>
        </a>
        <button type="button" class="ui-icon-btn lg:hidden" @click="mobileSidebar = false" aria-label="Cerrar menú">×</button>
    </header>
    <div class="p-3">
        <button type="button" class="ui-btn-secondary hidden w-full lg:flex" @click="sidebarOpen = !sidebarOpen"
            :aria-expanded="sidebarOpen" aria-controls="workspace-sidebar" aria-label="Expandir o contraer menú">
            <span aria-hidden="true">☰</span><span x-show="sidebarOpen" x-cloak class="ml-2">{{ $role }}</span>
        </button>
        <p x-show="mobileSidebar" x-cloak class="font-bold lg:hidden">{{ $role }}</p>
    </div>
    <nav class="ui-scrollbar flex-1 space-y-3 overflow-y-auto px-3 pb-4">
        @if ($dashboard)
            <a href="{{ route($dashboard) }}" @click="mobileSidebar = false" title="Inicio"
                class="flex items-center gap-3 rounded-xl px-3 py-3 font-semibold hover:bg-[var(--ui-primary-soft)]">
                <span aria-hidden="true">⌂</span><span x-show="sidebarOpen || mobileSidebar" x-cloak>Inicio</span>
            </a>
        @endif
        @foreach ($links as $group => $sections)
            <section>
                <h2 x-show="sidebarOpen || mobileSidebar" x-cloak class="ui-muted px-3 py-2 text-xs font-semibold uppercase">{{ $group }}</h2>
                @foreach ($sections as $section)
                    @php($active = request()->routeIs($section['route']) && collect($section['params'])->every(fn ($value, $key) => request()->route($key) === $value))
                    <a href="{{ route($section['route'], $section['params']) }}" @click="mobileSidebar = false"
                        title="{{ $section['label'] }}" @if($active) aria-current="page" @endif
                        class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition hover:bg-[var(--ui-primary-soft)]"
                        style="{{ $active ? 'background: var(--ui-primary-soft); color: var(--ui-primary);' : 'color: var(--ui-text-soft);' }}">
                        <span aria-hidden="true">•</span><span x-show="sidebarOpen || mobileSidebar" x-cloak>{{ $section['label'] }}</span>
                    </a>
                @endforeach
            </section>
        @endforeach
    </nav>
    <footer class="border-t p-3" style="border-color: var(--ui-border);">
        <a href="{{ route('profile.show') }}" title="Mi perfil" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm">
            <span aria-hidden="true">○</span><span x-show="sidebarOpen || mobileSidebar" x-cloak>Mi perfil</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button type="submit" title="Cerrar sesión" class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-sm" style="color: var(--ui-danger);">
                <span aria-hidden="true">↪</span><span x-show="sidebarOpen || mobileSidebar" x-cloak>Cerrar sesión</span>
            </button>
        </form>
    </footer>
</aside>
