<div class="space-y-6">
    <section class="ui-panel">
        <nav aria-label="Ruta de navegación"><a class="ui-muted underline" href="{{ route($dashboardRoute) }}">Inicio</a> / {{ $title }}</nav>
        <h1 class="ui-title mt-3 text-3xl font-black">{{ $title }}</h1>
        <p class="ui-muted mt-2">Consulta institucional de solo lectura. {{ $rows->total() }} registros encontrados.</p>
    </section>
    @if($errors->any())<div class="ui-alert-danger" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <section class="ui-panel flex flex-wrap items-end gap-3" aria-label="Filtros de consulta">
        <label class="ui-label flex-1" for="query-search">Buscar por código o nombre<input class="ui-input mt-2" id="query-search" wire:model.live.debounce.400ms="search" type="search" maxlength="100"></label>
        @if($years->isNotEmpty())<label class="ui-label">Gestión<select wire:model.live="gestion" class="ui-select"><option value="">Todas las autorizadas</option>@foreach($years as $year)<option value="{{ $year->cod_gea }}">{{ $year->ani_gea }}</option>@endforeach</select></label>@endif
        @if(in_array($area, ['estudiantes', 'cursos', 'inscripciones', 'rendimiento', 'asistencia', 'lms'], true))
            <label class="ui-label">Curso<select wire:model.live="course" class="ui-select"><option value="">Todos los autorizados</option>@foreach($courses as $item)<option value="{{ $item->cod_cur }}">{{ $item->nom_cur }}</option>@endforeach</select></label>
        @endif
        @if($workspace === 'secretaria' && $area === 'cursos')
            <label class="ui-label">Nivel registrado<select wire:model.live="level" class="ui-select"><option value="">Todos</option>@foreach($levels as $value)<option value="{{ $value }}">{{ $value }}</option>@endforeach</select></label>
            <label class="ui-label">Paralelo<select wire:model.live="parallel" class="ui-select"><option value="">Todos</option>@foreach($parallels as $item)<option value="{{ $item->cod_par }}">{{ $item->nom_par }}</option>@endforeach</select></label>
            <label class="ui-label">Turno<select wire:model.live="shift" class="ui-select"><option value="">Todos</option>@foreach($shifts as $item)<option value="{{ $item->cod_tur }}">{{ $item->nom_tur }}</option>@endforeach</select></label>
        @endif
        @if($workspace === 'secretaria' && in_array($area,['cursos','turnos'],true))
            <label class="ui-label">Estado<select wire:model.live="state" class="ui-select"><option value="">Todos</option><option value="ACTIVO">Activo</option><option value="INACTIVO">Inactivo</option></select></label>
        @endif
        @if($workspace === 'secretaria' && $area === 'turnos')
            <label class="ui-label">Inicio desde<input type="time" wire:model.live="from" class="ui-input"></label>
            <label class="ui-label">Fin hasta<input type="time" wire:model.live="until" class="ui-input"></label>
        @endif
        <button type="button" wire:click="limpiar" wire:loading.attr="disabled" class="ui-btn-secondary">Limpiar</button>
        <span class="ui-muted" wire:loading role="status">Actualizando consulta…</span>
    </section>
    <div class="ui-card overflow-x-auto" tabindex="0" aria-label="Resultados de la consulta" wire:loading.class="opacity-60">
        <table class="ui-table text-sm"><thead><tr>@foreach($columns as $label)<th scope="col">{{ $label }}</th>@endforeach</tr></thead>
            <tbody>@forelse($rows as $row)<tr>@foreach($columns as $field => $label)<td>{{ data_get($row, $field) ?? 'Sin datos' }}
                @if($field === 'cod_cur' && $workspace !== 'secretaria' && auth()->user()->can('estudiantes.ver.institucional'))<a class="block underline" href="{{ route($workspace.'.consulta', ['area' => 'estudiantes', 'curso' => $row->cod_cur, 'gestion' => $gestion]) }}">Ver estudiantes</a>@endif
                @if($field === 'cod_est' && auth()->user()->can('calificaciones.ver.institucional'))<a class="block underline" href="{{ route($workspace.'.consulta', ['area' => 'rendimiento', 'estudiante' => $row->cod_est, 'gestion' => $gestion, 'curso' => $course]) }}">Ver rendimiento</a>@endif
                @if($field === 'cod_est' && $area === 'estudiantes')<button type="button" class="ui-btn-secondary mt-2" x-data @click="$dispatch('open-student', {id: @js($row->cod_est)})">Ver ficha</button>@endif
                @if($field === 'codigo' && $area === 'reportes')@can('view', $row)<a class="ui-btn-secondary mt-2" href="{{ route('reportes.historicos.descargar', $row) }}">Descargar PDF</a>@endcan @endif
                @if($workspace === 'secretaria' && in_array($field,['cod_cur','cod_tur'],true))<button type="button" class="ui-btn-secondary mt-2" x-data @click="$dispatch('open-institutional-record', {id: @js(data_get($row,$field)), gestion: @js($gestion)})">Ver detalle</button>@endif
            </td>@endforeach</tr>
            @empty<tr><td colspan="{{ count($columns) }}" class="ui-muted">{{ $search !== '' ? 'No hay resultados para la búsqueda.' : 'No existen registros disponibles.' }}</td></tr>@endforelse</tbody>
        </table>
    </div>
    {{ $rows->links() }}
    @if($area === 'estudiantes')<livewire:shared.student-drawer />@endif
    @if($workspace === 'secretaria' && in_array($area,['cursos','turnos'],true))<livewire:shared.institutional-record-drawer :area="$area" />@endif
</div>
