<div class="space-y-6">
    <section class="ui-panel">
        <nav aria-label="Ruta de navegación" class="ui-muted text-sm">
            <a class="underline" href="{{ route($teacher ? 'docente.dashboard' : 'estudiante.dashboard') }}">Inicio</a> /
            <a class="underline" href="{{ route($teacher ? 'docente.cursos' : 'estudiante.materias') }}">{{ $teacher ? 'Mis cursos' : 'Mis materias' }}</a> /
            {{ $class->nom_cla }} / {{ $tabs[$tab][0] }}
        </nav>
        <h1 class="ui-title mt-3 text-2xl font-black">{{ $class->planAsignatura?->asignatura?->nom_asi ?? $class->nom_cla }}</h1>
        <p class="ui-muted mt-2">{{ $class->planAsignatura?->curso?->nom_cur }} · {{ $class->planAsignatura?->paralelo?->nom_par }} · Gestión {{ $class->planAsignatura?->gestionAcademica?->ani_gea }}</p>
    </section>
    <nav aria-label="Secciones del curso" class="ui-panel flex flex-wrap gap-2">
        @foreach ($tabs as $key => $item)
            <button type="button" wire:click="$set('tab', '{{ $key }}')" wire:loading.attr="disabled"
                @if($tab === $key) aria-current="page" @endif class="{{ $tab === $key ? 'ui-btn-primary' : 'ui-btn-secondary' }}">{{ $item[0] }}</button>
        @endforeach
    </nav>
    <div wire:loading class="ui-alert-info" role="status">Cargando información del curso…</div>
    <div wire:loading.remove class="space-y-5">
        @if($tab === 'contenido' && $units)
            <section class="ui-panel"><h2 class="ui-title text-xl font-bold">Unidades del curso</h2>@forelse($units as $unit)<article class="ui-card-soft mt-3 p-4"><h3 class="ui-title font-bold">{{ $unit->orden }}. {{ $unit->titulo }}</h3><p class="ui-muted">{{ $unit->descripcion }}</p><p class="ui-muted">{{ $unit->materiales_count }} materiales publicados · {{ $unit->tareas_count }} actividades publicadas o cerradas</p></article>@empty<p class="ui-muted mt-3">No hay unidades visibles para este curso.</p>@endforelse {{ $units->links() }}</section>
        @endif
        @if ($tab === 'resumen')
            <section class="grid gap-4 sm:grid-cols-3">
                @foreach (['materiales' => 'Materiales publicados', 'tareas_pendientes' => $teacher ? 'Tareas publicadas' : 'Tareas pendientes', 'progreso' => 'Progreso en tareas (%)'] as $key => $label)
                    @if ($teacher && $key === 'progreso') @continue @endif
                    <article class="ui-card p-5"><p class="ui-muted">{{ $label }}</p><p class="mt-2 text-2xl font-black">{{ $summary[$key] ?? 'Sin datos' }}</p></article>
                @endforeach
            </section>
            <p class="ui-muted">{{ $class->des_cla }}</p>
        @elseif ($tab === 'kardex')
            <section class="ui-panel"><h2 class="ui-title text-xl font-bold">Seguimiento formativo</h2><p class="ui-muted mt-3">Las tareas pendientes requieren revisión humana y no generan observaciones automáticamente. El registro necesita su contrato, catálogos y persistencia autorizados.</p><a class="ui-btn-secondary mt-3" href="{{ route('docente.kardex') }}">Ver condiciones y estructura de Kardex</a></section>
        @elseif ($tab === 'notas-oficiales')
            <p class="ui-alert-info">Estas son notas oficiales. Las calificaciones de tareas y su retroalimentación se consultan en {{ $teacher ? 'Entregas' : 'Mis entregas' }}.</p>
            @if ($teacher && $class->est_cla === 'ACTIVA')
                <a class="ui-btn-primary" href="{{ route('docente.cursos.calificaciones', $class->cod_cla) }}">Registro oficial autorizado</a>
            @endif
            @if (!$rows)<p class="ui-alert-warning">El historial oficial por curso aún no está habilitado.</p>@endif
        @elseif ($tab === 'calendario')
            <p class="ui-muted">Fechas límite de las tareas del curso.</p>
        @endif
        @if ($teacher && $class->est_cla === 'ACTIVA' && $tab === 'contenido')
            <livewire:shared.course-content :curso="$class->cod_cla" :key="'content-'.$class->cod_cla" />
        @elseif ($teacher && $class->est_cla === 'ACTIVA' && $tab === 'materiales')
            @if($editing)
            <button type="button" wire:click="cancelarEdicion" class="ui-btn-secondary">Cerrar edición</button>
            <livewire:aula-virtual.materiales.editar-material :curso="$class->cod_cla" :material="$editing" :key="'edit-material-'.$editing" />
            @else
            <livewire:aula-virtual.materiales.crear-material :curso="$class->cod_cla" :key="'material-'.$class->cod_cla" />
            @endif
        @elseif ($teacher && $class->est_cla === 'ACTIVA' && in_array($tab, ['tareas', 'actividades'], true) && auth()->user()->can('Tareas_Aula'))
            @if($editing)
            <button type="button" wire:click="cancelarEdicion" class="ui-btn-secondary">Cerrar edición</button>
            <livewire:aula-virtual.tareas.editar-tarea :curso="$class->cod_cla" :tarea="$editing" :key="'edit-task-'.$editing" />
            @else
            <livewire:aula-virtual.tareas.crear-tarea :curso="$class->cod_cla" :tipo="$tab === 'actividades' ? 'PRACTICA' : 'TAREA'" :key="'task-'.$tab.'-'.$class->cod_cla" />
            @endif
        @elseif ($teacher && $class->est_cla === 'ACTIVA' && $tab === 'asistencia')
            <a class="ui-btn-primary" href="{{ route('aula-virtual.docente.asistencia.registrar', $class->cod_cla) }}">Registrar asistencia</a>
        @endif
        @if ($rows)
            <section class="ui-panel flex flex-wrap items-end gap-4">
                <label class="ui-label flex-1" for="section-search">Buscar en {{ $tabs[$tab][0] }}<input id="section-search" class="ui-input" type="search" maxlength="100" wire:model.live.debounce.350ms="search"></label>
                @if($states)<label class="ui-label" for="section-state">Estado<select id="section-state" class="ui-select" wire:model.live="state"><option value="">Todos los permitidos</option>@foreach($states as $item)<option>{{ $item }}</option>@endforeach</select></label>@endif
                @error('search')<p class="ui-error">{{ $message }}</p>@enderror
                @error('state')<p class="ui-error">{{ $message }}</p>@enderror
            </section>
            <section class="ui-card overflow-x-auto" tabindex="0" aria-label="{{ $tabs[$tab][0] }}">
                <table class="ui-table"><thead><tr>@foreach ($columns as $label)<th scope="col">{{ $label }}</th>@endforeach<th scope="col">Acciones</th></tr></thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr wire:key="{{ $tab.'-'.$row->getKey() }}">
                                @foreach ($columns as $field => $label)<td>{{ data_get($row, $field) ?? 'Sin datos' }}</td>@endforeach
                                <td>
                                    @if ($tab === 'materiales')
                                        @if ($row->rut_mat)<a class="ui-btn-secondary" href="{{ route('aula-virtual.materiales.descargar', $row->cod_mat) }}">Descargar</a>@endif
                                        @if ($row->url_mat && filter_var($row->url_mat, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($row->url_mat, PHP_URL_SCHEME)), ['http', 'https'], true))
                                            <a class="underline" href="{{ $row->url_mat }}" target="_blank" rel="noopener noreferrer">Abrir recurso</a>
                                        @endif
                                        @if ($teacher && $class->est_cla === 'ACTIVA')
                                            <button type="button" class="ui-btn-secondary" wire:click="editar(@js($row->cod_mat))" wire:loading.attr="disabled">Editar</button>
                                            <form method="POST" action="{{ route($row->est_mat === 'ACTIVO' ? 'aula-virtual.materiales.ocultar' : 'aula-virtual.materiales.publicar', $row->cod_mat) }}" data-confirm="El material cambiará su visibilidad para estudiantes.">
                                                @csrf<button type="submit" class="ui-btn-secondary">{{ $row->est_mat === 'ACTIVO' ? 'Ocultar' : 'Publicar' }}</button>
                                            </form>
                                        @endif
                                    @elseif (in_array($tab, ['tareas', 'actividades', 'calendario'], true))
                                        @if($teacher && in_array($tab, ['tareas', 'actividades'], true) && auth()->user()->can('Tareas_Aula') && $class->est_cla === 'ACTIVA' && $row->est_tar !== 'CERRADA')
                                        <button type="button" class="ui-btn-secondary" wire:click="editar(@js($row->cod_tar))" wire:loading.attr="disabled">Editar</button>
                                        @endif
                                        <a class="ui-btn-secondary" href="{{ route($teacher ? 'aula-virtual.docente.tareas.revisar' : 'aula-virtual.estudiante.tareas.entregar', $row->cod_tar) }}">{{ $teacher ? 'Revisar' : 'Abrir tarea' }}</a>
                                    @elseif ($tab === 'entregas' || $tab === 'calificaciones')
                                        <a class="underline" href="{{ route($teacher ? 'aula-virtual.docente.tareas.revisar' : 'aula-virtual.estudiante.tareas.entregar', $row->cod_tar) }}">Detalle</a>
                                    @elseif ($tab === 'estudiantes')
                                        <button type="button" class="ui-btn-secondary" wire:click="$dispatch('open-student', {id: @js($row->cod_est)})">Ver ficha</button>
                                    @else
                                        <span class="ui-muted">Consulta</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ count($columns) + 1 }}" class="ui-muted p-5">No hay registros disponibles en esta sección.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
            {{ $rows->links() }}
        @endif
    </div>
    @if($teacher && $tab === 'estudiantes')<livewire:shared.student-drawer :curso="$class->cod_cla" :key="'student-drawer-'.$class->cod_cla" />@endif
</div>
