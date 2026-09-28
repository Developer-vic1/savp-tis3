@php
    $role = Auth::user() ? app(\App\Services\RoleDashboardResolver::class)->roleFor(Auth::user()) : 'Usuario';
    $dashboard = match ($role) {
        'Director' => 'direccion.dashboard',
        'Secretaria' => 'secretaria.dashboard',
        'Regente' => 'regencia.dashboard',
        default => 'dashboard',
    };
    $sections = match ($role) {
        'Director' => [
            ['label' => 'Orientación', 'area' => 'orientacion', 'permission' => 'orientacion.ver.institucional'],
            ['label' => 'Estudiantes', 'area' => 'estudiantes', 'permission' => 'estudiantes.ver.institucional'],
            ['label' => 'Docentes', 'area' => 'docentes', 'permission' => 'Docentes'],
            ['label' => 'Cursos', 'area' => 'cursos', 'permission' => 'cursos.ver.institucional'],
            ['label' => 'Inscripciones', 'area' => 'inscripciones', 'permission' => 'inscripciones.ver.institucional'],
            ['label' => 'Rendimiento', 'area' => 'rendimiento', 'permission' => 'calificaciones.ver.institucional'],
            ['label' => 'Asistencia', 'area' => 'asistencia', 'permission' => 'asistencia.ver.institucional'],
            ['label' => 'Reportes', 'area' => 'reportes', 'permission' => 'reportes.ver.institucional'],
        ],
        'Secretaria' => [
            ['label' => 'Cuentas operativas', 'route' => 'secretaria.cuentas', 'permission' => 'usuarios.ver.institucional'],
            ['label' => 'Personas', 'route' => 'secretaria.personas', 'permission' => 'Registro_Personas'],
            ['label' => 'Estudiantes', 'route' => 'secretaria.estudiantes', 'permission' => 'Estudiantes'],
            ['label' => 'Inscripciones', 'route' => 'secretaria.inscripciones', 'permission' => 'Inscripciones'],
            ['label' => 'Gestión académica', 'route' => 'secretaria.gestion-academica', 'permission' => 'Gestion_Academica'],
            ['label' => 'Cursos', 'route' => 'secretaria.cursos', 'permission' => 'Cursos'],
            ['label' => 'Paralelos', 'route' => 'secretaria.paralelos', 'permission' => 'Paralelos'],
            ['label' => 'Turnos', 'route' => 'secretaria.turnos', 'permission' => 'Turnos'],
        ],
        'Regente' => [
            ['label' => 'Cursos', 'area' => 'cursos', 'permission' => 'cursos.ver.institucional'],
            ['label' => 'Estudiantes', 'area' => 'estudiantes', 'permission' => 'estudiantes.ver.institucional'],
            ['label' => 'Asistencia', 'area' => 'asistencia', 'permission' => 'asistencia.ver.institucional'],
            ['label' => 'Seguimiento de inscripciones', 'area' => 'inscripciones', 'permission' => 'inscripciones.ver.institucional'],
            ['label' => 'Estado académico', 'area' => 'rendimiento', 'permission' => 'calificaciones.ver.institucional'],
        ],
        default => [],
    };
@endphp

<div class="fixed left-0 top-0 z-40 flex h-screen flex-col border-r shadow-md transition-all duration-300"
    :class="sidebarOpen ? 'w-72' : 'w-20'"
    style="background: var(--ui-surface); border-color: var(--ui-border); color: var(--ui-text);">
    <div class="border-b p-4" style="border-color: var(--ui-border);">
        <a href="{{ route($dashboard) }}" class="flex items-center gap-3">
            <img src="{{ asset('image/LOGO FT3 A.jpg') }}" alt="Logo Franz Tamayo" class="h-11 w-11 rounded-2xl object-contain">
            <span x-show="sidebarOpen" x-cloak class="font-black">SAVP – TIS 3</span>
        </a>
    </div>
    <div class="px-4 pt-4">
        <div class="rounded-2xl p-4 text-white" style="background: var(--ui-primary);">
            <p x-show="sidebarOpen" x-cloak class="text-xs uppercase tracking-widest opacity-80">Espacio actual</p>
            <p class="mt-1 font-black">{{ $role }}</p>
        </div>
    </div>
    <nav class="ui-scrollbar flex-1 space-y-1 overflow-y-auto px-3 py-4">
        <a href="{{ route($dashboard) }}" class="flex items-center gap-3 rounded-2xl px-3 py-3 font-semibold"
            style="background: var(--ui-primary-soft); color: var(--ui-primary);">
            <span aria-hidden="true">⌂</span><span x-show="sidebarOpen" x-cloak>Inicio</span>
        </a>
        @foreach ($sections as $section)
            @if (! isset($section['permission']) || Auth::user()->can($section['permission']))
            <a href="{{ isset($section['area']) ? route($role === 'Regente' ? 'regencia.consulta' : 'direccion.consulta', $section['area']) : (isset($section['route']) ? route($section['route']) : route($dashboard).'#'.str($section['label'])->slug()) }}"
                class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm transition hover:bg-[var(--ui-surface-muted)]"
                style="color: var(--ui-text-soft);">
                <span aria-hidden="true">•</span><span x-show="sidebarOpen" x-cloak>{{ $section['label'] }}</span>
            </a>
            @endif
        @endforeach
    </nav>
    <div class="border-t p-3" style="border-color: var(--ui-border);">
        <a href="{{ route('profile.show') }}" class="flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold" style="color: var(--ui-text-soft);">
            <span aria-hidden="true">○</span><span x-show="sidebarOpen" x-cloak>Mi perfil</span>
        </a>
        <form method="POST" action="{{ route('logout') }}">@csrf
            <button class="mt-1 flex w-full items-center gap-3 rounded-2xl px-3 py-3 text-sm font-semibold" style="color: var(--ui-danger);">
                <span aria-hidden="true">↪</span><span x-show="sidebarOpen" x-cloak>Cerrar sesión</span>
            </button>
        </form>
    </div>
</div>
