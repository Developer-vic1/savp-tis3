@php
    $user = auth()->user();
    $resolver = app(\App\Services\RoleDashboardResolver::class);
    $navigation = app(\App\Support\WorkspaceNavigation::class);
    $role = $user ? $resolver->roleFor($user) : null;
    $dashboard = $user ? $resolver->routeFor($user) : null;
    $links = $user ? collect($navigation->for($user))->groupBy('group') : collect();
    $activeGroup = $links->keys()->first(fn ($group) => $links[$group]->contains(fn ($section) => $navigation->isActive($section)));
    $activeGroup ??= $links->keys()->first();
    $homeActive = $dashboard && (request()->routeIs($dashboard)
        || (in_array($role, ['Docente', 'Estudiante'], true) && request()->routeIs('aula-virtual.inicio')));
@endphp

<div x-show="mobileSidebar" x-cloak x-transition.opacity.duration.200ms
    class="fixed inset-0 z-40 bg-slate-950/55 backdrop-blur-sm lg:hidden"
    @click="mobileSidebar = false" aria-hidden="true"></div>

<aside id="workspace-sidebar" aria-label="Navegación de {{ $role ?? 'SAVP' }}"
    x-data="{
        openGroup: @js($activeGroup),
        tooltip: '',
        tooltipY: 0,
        showTip(label, event) {
            const bounds = event.currentTarget.getBoundingClientRect();
            this.tooltip = label;
            this.tooltipY = bounds.top + bounds.height / 2;
        }
    }"
    x-init="(() => {
        try {
            const saved = localStorage.getItem('savp-sidebar-open');
            if (saved !== null) sidebarOpen = saved === 'true';
        } catch (error) {}
        $watch('sidebarOpen', value => {
            tooltip = '';
            try { localStorage.setItem('savp-sidebar-open', String(value)); } catch (error) {}
        });
    })()"
    class="savp-sidebar fixed left-0 top-0 z-50 flex h-screen flex-col transition-[width,transform] duration-200"
    :class="[sidebarOpen ? 'lg:w-72' : 'lg:w-20', mobileSidebar ? 'translate-x-0 w-72' : '-translate-x-full w-72 lg:translate-x-0']">

    <header class="savp-sidebar-brand">
        <a href="{{ $dashboard ? route($dashboard) : route('dashboard') }}"
            class="savp-sidebar-brand-link" title="Inicio SAVP-TIS3"
            @click="mobileSidebar = false"
            @mouseenter="showTip('Inicio SAVP-TIS3', $event)" @mouseleave="tooltip = ''"
            @focus="showTip('Inicio SAVP-TIS3', $event)" @blur="tooltip = ''">
            <span class="savp-sidebar-logo">
                <img src="{{ asset('image/LOGO FT3 A.jpg') }}" alt="Logo de la Unidad Educativa Franz Tamayo N°3">
            </span>
            <span x-show="sidebarOpen || mobileSidebar" x-cloak class="min-w-0">
                <span class="savp-sidebar-brand-name">Franz Tamayo N°3</span>
                <span class="savp-sidebar-brand-subtitle">SAVP · TIS 3</span>
            </span>
        </a>
        <button type="button" class="savp-sidebar-toggle hidden lg:inline-flex"
            @click="sidebarOpen = !sidebarOpen; tooltip = ''"
            :aria-expanded="sidebarOpen"
            :aria-label="sidebarOpen ? 'Contraer menú' : 'Expandir menú'"
            :title="sidebarOpen ? 'Contraer menú' : 'Expandir menú'"
            aria-controls="workspace-sidebar">
            <i class="ph-duotone ph-sidebar-simple" aria-hidden="true"></i>
        </button>
        <button type="button" class="savp-sidebar-mobile-close lg:hidden"
            @click="mobileSidebar = false" aria-label="Cerrar menú" title="Cerrar menú">
            <i class="ph-duotone ph-x" aria-hidden="true"></i>
        </button>
    </header>

    <div class="savp-sidebar-actor">
        <span class="savp-sidebar-actor-icon"><i class="ph-duotone ph-shield-check" aria-hidden="true"></i></span>
        <span x-show="sidebarOpen || mobileSidebar" x-cloak class="min-w-0">
            <span class="savp-sidebar-actor-name">{{ $role ?? 'SAVP-TIS3' }}</span>
            <span class="savp-sidebar-actor-caption">Espacio de trabajo institucional</span>
        </span>
    </div>

    <nav class="savp-sidebar-nav ui-scrollbar flex-1 overflow-y-auto overflow-x-hidden" aria-label="Módulos autorizados">
        @if ($dashboard)
            <div class="savp-sidebar-nav-label" x-show="sidebarOpen || mobileSidebar" x-cloak>Principal</div>
            <a href="{{ route($dashboard) }}"
                class="savp-sidebar-link savp-sidebar-tone-emerald {{ $homeActive ? 'is-active' : '' }}"
                title="Inicio" @if ($homeActive) aria-current="page" @endif
                @click="mobileSidebar = false"
                @mouseenter="showTip('Inicio', $event)" @mouseleave="tooltip = ''"
                @focus="showTip('Inicio', $event)" @blur="tooltip = ''">
                <span class="savp-sidebar-link-icon"><i class="ph-duotone ph-house" aria-hidden="true"></i></span>
                <span x-show="sidebarOpen || mobileSidebar" x-cloak class="savp-sidebar-link-label">Inicio</span>
            </a>
        @endif

        @foreach ($links as $group => $sections)
            @php
                $groupIcon = \App\Support\WorkspaceNavigation::groupIcon($group);
                $groupTone = \App\Support\WorkspaceNavigation::groupTone($group);
            @endphp
            <section class="savp-sidebar-section" aria-label="{{ $group }}">
                <button type="button" id="workspace-group-button-{{ $loop->index }}"
                    class="savp-sidebar-group savp-sidebar-tone-{{ $groupTone }}"
                    title="{{ $group }}" aria-label="Grupo {{ $group }}"
                    aria-controls="workspace-group-{{ $loop->index }}"
                    :aria-expanded="(sidebarOpen || mobileSidebar) && openGroup === @js($group)"
                    @click="
                        if (!sidebarOpen && !mobileSidebar) {
                            sidebarOpen = true;
                            openGroup = @js($group);
                        } else {
                            openGroup = openGroup === @js($group) ? null : @js($group);
                        }
                        tooltip = '';
                    "
                    @mouseenter="showTip(@js($group), $event)" @mouseleave="tooltip = ''"
                    @focus="showTip(@js($group), $event)" @blur="tooltip = ''">
                    <span class="savp-sidebar-group-icon"><i class="ph-duotone {{ $groupIcon }}" aria-hidden="true"></i></span>
                    <span x-show="sidebarOpen || mobileSidebar" x-cloak class="savp-sidebar-group-label">{{ $group }}</span>
                    <i x-show="sidebarOpen || mobileSidebar" x-cloak
                        class="ph-duotone ph-caret-down savp-sidebar-group-caret"
                        :class="{ 'rotate-180': openGroup === @js($group) }" aria-hidden="true"></i>
                </button>
                <div id="workspace-group-{{ $loop->index }}" role="group"
                    aria-labelledby="workspace-group-button-{{ $loop->index }}"
                    x-show="(sidebarOpen || mobileSidebar) && openGroup === @js($group)" x-cloak
                    x-transition:enter="transition duration-200 ease-out"
                    x-transition:enter-start="opacity-0 -translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition duration-150 ease-in"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 -translate-y-1"
                    class="savp-sidebar-group-items">
                    @foreach ($sections as $section)
                        @php($active = $navigation->isActive($section))
                        <a href="{{ route($section['route'], $section['params']) }}"
                            class="savp-sidebar-link savp-sidebar-child savp-sidebar-tone-{{ $section['tone'] }} {{ $active ? 'is-active' : '' }}"
                            title="{{ $section['label'] }}"
                            @if ($active) aria-current="page" @endif
                            @click="mobileSidebar = false">
                            <span class="savp-sidebar-link-icon"><i class="ph-duotone {{ $section['icon'] }}" aria-hidden="true"></i></span>
                            <span class="savp-sidebar-link-label">{{ $section['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endforeach
    </nav>

    <footer class="savp-sidebar-footer">
        <a href="{{ route('profile.show') }}" class="savp-sidebar-link savp-sidebar-tone-sky {{ request()->routeIs('profile.show') ? 'is-active' : '' }}"
            title="Mi perfil" @if (request()->routeIs('profile.show')) aria-current="page" @endif
            @click="mobileSidebar = false"
            @mouseenter="showTip('Mi perfil', $event)" @mouseleave="tooltip = ''"
            @focus="showTip('Mi perfil', $event)" @blur="tooltip = ''">
            <span class="savp-sidebar-link-icon"><i class="ph-duotone ph-user-circle" aria-hidden="true"></i></span>
            <span x-show="sidebarOpen || mobileSidebar" x-cloak class="savp-sidebar-link-label">Mi perfil</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="savp-sidebar-link savp-sidebar-logout"
                title="Cerrar sesión" aria-label="Cerrar sesión"
                @mouseenter="showTip('Cerrar sesión', $event)" @mouseleave="tooltip = ''"
                @focus="showTip('Cerrar sesión', $event)" @blur="tooltip = ''">
                <span class="savp-sidebar-link-icon"><i class="ph-duotone ph-sign-out" aria-hidden="true"></i></span>
                <span x-show="sidebarOpen || mobileSidebar" x-cloak class="savp-sidebar-link-label">Cerrar sesión</span>
            </button>
        </form>
    </footer>

    <div x-show="!sidebarOpen && !mobileSidebar && tooltip" x-cloak
        class="savp-sidebar-tooltip hidden lg:block" :style="'top: ' + tooltipY + 'px'"
        x-text="tooltip" aria-hidden="true"></div>
</aside>
