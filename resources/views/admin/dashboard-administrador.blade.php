@extends('layouts.app')
@section('title', 'Panel administrativo | SAVP')

@section('content')
@php
    $nombreSaludo = trim(explode(' ', trim(Auth::user()->persona?->nom_per ?? 'Administrador'))[0]);
    $saludo = $fechaControl->hour < 12 ? 'Buen día' : ($fechaControl->hour < 18 ? 'Buenas tardes' : 'Buenas noches');
    $iconos = ['users' => 'ph-users', 'academic-cap' => 'ph-student', 'user-group' => 'ph-chalkboard-teacher',
        'clipboard-document' => 'ph-clipboard-text', 'wrench-screwdriver' => 'ph-wrench', 'calendar-days' => 'ph-calendar-dots'];
    $rolesOrdenados = collect($chartRoles)->sortDesc()->all();
    $rolesEquipo = collect($rolesOrdenados)->except('Estudiante')->all();
    $vistasRoles = [];
    if (array_sum($rolesEquipo) > 0) {
        $vistasRoles[] = ['etiqueta' => 'Equipo institucional', 'datos' => $rolesEquipo];
    }
    $vistasRoles[] = ['etiqueta' => 'Todos los roles', 'datos' => $rolesOrdenados];
    $vistasInscripciones = [['etiqueta' => 'Por curso', 'datos' => $chartInscripciones]];
    if ($chartTurnos !== []) {
        $vistasInscripciones[] = ['etiqueta' => 'Por turno', 'datos' => $chartTurnos];
    }
    $rangoPeriodo = $periodoActual['rango'] ? explode(' / ', $periodoActual['rango']) : [];
    $fechasPeriodo = count($rangoPeriodo) === 2
        ? \Carbon\Carbon::parse($rangoPeriodo[0])->format('d/m').' — '.\Carbon\Carbon::parse($rangoPeriodo[1])->format('d/m/Y')
        : null;
    $nombrePeriodo = $periodoActual['estado'] === 'SIN_FECHAS' ? 'Calendario no disponible' : $periodoActual['nombre'];
    $graficos = [
        ['id' => 'chartRoles', 'titulo' => 'Roles de la comunidad', 'categoria' => 'Comunidad institucional', 'datos' => $chartRoles, 'vistas' => $vistasRoles,
            'descripcion' => 'Equipo y estudiantes por separado. Una cuenta puede tener varios roles.', 'tipo' => 'bar', 'horizontal' => true, 'color' => '--ui-primary', 'unidad' => 'asignaciones'],
        ['id' => 'chartEspecialidades', 'titulo' => 'Estudiantes por especialidad', 'categoria' => 'Formación técnica', 'datos' => $chartEspecialidades,
            'descripcion' => 'Las seis especialidades con más estudiantes activos.', 'tipo' => 'bar', 'horizontal' => true, 'color' => '--ui-info', 'unidad' => 'estudiantes'],
        ['id' => 'chartInscripciones', 'titulo' => 'Distribución de inscripciones', 'categoria' => 'Gestión '.$gestionActual, 'datos' => $chartInscripciones, 'vistas' => $vistasInscripciones,
            'descripcion' => count($vistasInscripciones) > 1 ? 'Inscripciones activas por curso o turno en esta gestión.' : 'Inscripciones activas por curso en esta gestión.', 'tipo' => 'bar', 'color' => '--ui-violet', 'unidad' => 'inscripciones'],
    ];
@endphp
<div class="admin-dashboard" data-admin-dashboard>
    <section class="ui-card admin-hero" aria-labelledby="admin-title">
        <div>
            <p class="ui-kicker">Panel administrativo</p>
            <h1 id="admin-title" class="ui-title admin-hero-title mt-2 font-extrabold">{{ $saludo }}, {{ $nombreSaludo }}</h1>
            <p class="ui-muted mt-3 text-sm leading-6">Una mirada a la comunidad educativa, su actividad y los pendientes que necesitan tu atención.</p>
        </div>
        <dl class="admin-hero-context">
            <div class="ui-card-soft p-4">
                <dt class="ui-muted flex items-center gap-2 text-xs font-semibold"><i class="ph-duotone ph-calendar-blank text-lg" aria-hidden="true"></i> Gestión académica</dt>
                <dd class="ui-title mt-2 font-bold">{{ $gestionActual }}</dd>
                @if($gestion)<dd class="ui-muted mt-1 text-xs">{{ \Carbon\Carbon::parse($gestion->fii_gea)->format('d/m') }} — {{ \Carbon\Carbon::parse($gestion->ffi_gea)->format('d/m/Y') }}</dd>@endif
            </div>
            <div class="ui-card-soft p-4">
                <dt class="ui-muted flex items-center gap-2 text-xs font-semibold"><i class="ph-duotone ph-clock text-lg" aria-hidden="true"></i> Periodo académico</dt>
                <dd class="ui-title mt-2 font-bold">{{ $nombrePeriodo }}</dd>
                <dd class="ui-muted mt-1 text-xs">{{ $fechasPeriodo ?? 'Revisa las fechas de la gestión' }}</dd>
            </div>
        </dl>
    </section>

    @if($resumenPersonas)<section class="ui-card admin-personas-resumen" aria-label="Personas y cuentas de acceso"><div><i class="ph-duotone ph-identification-card" aria-hidden="true"></i><p><strong>{{ number_format($resumenPersonas['total'],0,',','.') }}</strong> personas registradas <span>{{ number_format($resumenPersonas['activas'],0,',','.') }} registros de persona vigentes</span></p></div><p><strong>{{ $resumenPersonas['con_cuenta'] }}</strong> con cuenta · <strong>{{ $resumenPersonas['sin_cuenta'] }}</strong> sin cuenta de acceso</p><a href="{{ route('admin.gestion-personas') }}" class="ui-btn ui-btn-secondary">Consultar personas</a></section>@endif
    <section class="admin-metrics" aria-label="Indicadores institucionales">
        @forelse($resumen as $item)
            <article class="ui-card ui-card-hover admin-metric">
                <div class="flex items-center justify-between gap-2">
                    <h2 class="ui-muted text-xs font-semibold">{{ $item['label'] }}</h2>
                    <i class="ph-duotone {{ $iconos[$item['icon']] ?? 'ph-chart-bar' }} text-2xl" style="color: var(--ui-primary)" aria-hidden="true"></i>
                </div>
                <p class="ui-title mt-3 text-3xl font-extrabold tabular-nums">{{ is_numeric($item['value']) ? number_format($item['value'], 0, ',', '.') : $item['value'] }}</p>
                <p class="ui-muted metric-description mt-2 text-xs leading-5">{{ $item['desc'] }}</p>
                <a href="{{ route($item['route']) }}" class="mt-4 inline-flex min-h-8 items-center gap-1 text-xs font-semibold" style="color: var(--ui-primary)">Ver {{ mb_strtolower($item['label']) }} <i class="ph-duotone ph-arrow-up-right" aria-hidden="true"></i></a>
            </article>
        @empty
            <p class="ui-alert-info">No hay indicadores disponibles con los permisos de esta cuenta.</p>
        @endforelse
    </section>

    <section aria-labelledby="distribucion-title">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
            <h2 id="distribucion-title" class="ui-title text-lg font-bold">Así se distribuye la comunidad</h2>
            <button type="button" class="admin-chart-replay" data-replay-charts><i class="ph-duotone ph-play-circle text-lg" aria-hidden="true"></i> Repetir animación</button>
        </div>
        <div class="admin-charts">
            @foreach($graficos as $grafico)
                <article class="ui-card admin-chart-card" aria-labelledby="{{ $grafico['id'] }}-title">
                    <div class="admin-chart-heading">
                        <p class="ui-kicker" style="color: var({{ $grafico['color'] }})">{{ $grafico['categoria'] }}</p>
                        <h3 id="{{ $grafico['id'] }}-title" class="ui-title mt-2 text-lg font-bold">{{ $grafico['titulo'] }}</h3>
                        <p class="ui-muted mt-2 text-sm leading-5">{{ $grafico['descripcion'] }}</p>
                    </div>
                    <div class="admin-chart-controls" @if(count($grafico['vistas'] ?? []) > 1) role="group" aria-label="Vista de {{ $grafico['titulo'] }}" @endif>
                        @if(count($grafico['vistas'] ?? []) > 1)
                            @foreach($grafico['vistas'] as $indice => $vista)
                                <button type="button" data-chart-view="{{ $grafico['id'] }}" data-view-index="{{ $indice }}" aria-controls="{{ $grafico['id'] }}" aria-pressed="{{ $indice === 0 ? 'true' : 'false' }}">{{ $vista['etiqueta'] }}</button>
                            @endforeach
                        @endif
                    </div>
                    @if(array_sum($grafico['datos']) > 0)
                        <div class="admin-chart-plot">
                            <canvas id="{{ $grafico['id'] }}" role="img" aria-label="{{ $grafico['titulo'] }}. Los valores están disponibles debajo del gráfico."></canvas>
                        </div>
                        <x-plegable-institucional class="admin-chart-details" icono="ph-list-numbers" :compacto="true"><x-slot:titulo>Consultar valores</x-slot:titulo>
                            @foreach($grafico['vistas'] ?? [['etiqueta' => $grafico['titulo'], 'datos' => $grafico['datos']]] as $indice => $vista)
                                <dl class="admin-chart-values" data-chart-values="{{ $grafico['id'] }}" data-view-index="{{ $indice }}" @if($indice !== 0) hidden @endif aria-label="{{ $vista['etiqueta'] }}">
                                    @foreach($vista['datos'] as $nombre => $cantidad)<div><dt>{{ $nombre }}</dt><dd>{{ number_format($cantidad, 0, ',', '.') }}</dd></div>@endforeach
                                </dl>
                            @endforeach
                        </x-plegable-institucional>
                    @else
                        <div class="admin-chart-plot admin-chart-empty"><i class="ph-duotone ph-chart-bar" aria-hidden="true"></i><p class="ui-muted text-sm">Sin datos disponibles en tu ámbito.</p></div>
                    @endif
                    @if($grafico['id'] === 'chartRoles' && isset($chartRoles['Estudiante']))
                        <p class="admin-chart-student-total"><span>Rol estudiantil</span><strong>{{ number_format($chartRoles['Estudiante'], 0, ',', '.') }} asignaciones</strong></p>
                    @endif
                </article>
            @endforeach
        </div>
    </section>

    <section class="admin-activity-grid" aria-label="Actividad y seguimiento">
        <div class="ui-card rounded-2xl p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div><p class="ui-kicker">Actividad reciente</p><h2 class="ui-title mt-2 text-lg font-bold">Lo último en la institución</h2></div>
                @can('Bitacora')<a href="{{ route('admin.bitacora') }}" class="text-sm font-semibold" style="color: var(--ui-primary)">Ver bitácora <i class="ph-duotone ph-arrow-up-right" aria-hidden="true"></i></a>@endcan
            </div>
            <ol class="mt-3">
                @forelse($actividadReciente as $item)
                    <li class="admin-activity-item">
                        <span class="admin-activity-icon {{ $item['color'] }}"><i class="ph-duotone {{ $item['icono'] }} text-xl" aria-hidden="true"></i></span>
                        <div class="min-w-0 flex-1">
                            <p class="ui-title text-sm font-semibold break-words">{{ $item['titulo'] }}</p>
                            <p class="ui-muted mt-1 text-sm leading-5 break-words">{{ $item['detalle'] }}</p>
                            <div class="ui-muted mt-2 flex flex-wrap items-center gap-2 text-xs"><span class="{{ $item['color'] }}">{{ $item['resultado'] }}</span><time title="{{ $item['fecha_completa'] }}">{{ $item['fecha'] }}</time></div>
                        </div>
                    </li>
                @empty
                    <li class="ui-card-soft mt-3 p-4 ui-muted text-sm">No hay actividad disponible para esta cuenta. Los próximos eventos aparecerán aquí.</li>
                @endforelse
            </ol>
        </div>
        <div class="space-y-5">
            <div class="ui-card rounded-2xl p-5">
                <p class="ui-kicker" style="color: var(--ui-warning)">Seguimiento institucional</p>
                <h2 class="ui-title mt-2 text-lg font-bold">Pendientes de revisión</h2>
                <div class="mt-4 space-y-3">
                    @forelse($alertas as $alerta)
                        <div class="ui-card-soft p-4">
                            <div class="flex items-start justify-between gap-3"><p class="ui-title text-sm font-semibold">{{ $alerta['titulo'] }}</p><span class="ui-badge-warning shrink-0 tabular-nums">{{ $alerta['valor'] }}</span></div>
                            <p class="ui-muted mt-2 text-sm leading-5">{{ $alerta['descripcion'] }}</p>
                        </div>
                    @empty
                        <p class="ui-muted text-sm">No hay indicadores de seguimiento disponibles para esta cuenta.</p>
                    @endforelse
                </div>
            </div>
            <div class="ui-card admin-state rounded-2xl p-5">
                <p class="ui-kicker">Contexto de la gestión</p>
                <h2 class="ui-title mt-2 text-lg font-bold">{{ $gestionActual }}</h2>
                <div class="admin-state-row"><span>Periodo académico</span><strong>{{ $nombrePeriodo }}</strong></div>
                <p class="ui-muted mt-2 text-xs leading-5">{{ $fechasPeriodo ? 'Calendario de la gestión: '.$fechasPeriodo.'.' : 'Revisa el calendario y las fechas de la gestión.' }}</p>
                <div class="admin-state-row"><span>Sesión</span><strong>{{ $estadoSistema }}</strong></div>
                <div class="admin-state-row"><span>Fecha de consulta</span><strong>{{ $fechaControl->format('d/m/Y') }}</strong></div>
                @can('Gestion_Academica')<a class="mt-3 inline-flex items-center gap-2 text-sm font-semibold" style="color: var(--ui-primary)" href="{{ route('admin.gestion-academica') }}">Revisar planificación <i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a>@endcan
            </div>
        </div>
    </section>

    <section class="ui-card rounded-2xl p-5" aria-labelledby="estructura-title">
        <p class="ui-kicker">Organización académica</p>
        <h2 id="estructura-title" class="ui-title mt-2 text-lg font-bold">La base de la planificación</h2>
        <p class="ui-muted mt-2 text-sm">Catálogos activos que sostienen las inscripciones y los horarios. Cada acceso permite revisar su configuración.</p>
        <div class="admin-structure mt-4">
            @forelse($estructuraAcademica as $item)
                <a href="{{ route($item['route']) }}" class="ui-card-soft ui-card-hover">
                    <div class="flex items-center justify-between gap-2"><h3 class="ui-title text-sm font-semibold">{{ $item['label'] }}</h3><i class="ph-duotone {{ $item['icono'] }} text-xl" style="color: var(--ui-primary)" aria-hidden="true"></i></div>
                    <p class="ui-title mt-2 text-2xl font-extrabold tabular-nums">{{ $item['value'] }}</p>
                    <p class="ui-muted mt-2 text-xs leading-5">{{ $item['value'] > 0 ? $item['descripcion'] : 'Sin registros activos. Revisa este catálogo.' }}</p>
                    <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold" style="color: var(--ui-primary)">Revisar <i class="ph-duotone ph-arrow-up-right" aria-hidden="true"></i></span>
                </a>
            @empty
                <p class="ui-muted text-sm">No hay catálogos disponibles con los permisos de esta cuenta.</p>
            @endforelse
        </div>
    </section>
    <script type="application/json" data-admin-chart-config>@json($graficos)</script>
</div>
@endsection
