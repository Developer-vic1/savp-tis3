<div class="space-y-6">
    <section class="ui-panel"><a href="{{ route('admin.dashboard') }}" class="ui-muted underline">Inicio Administración</a><h1 class="ui-title mt-3 text-3xl font-black">Asignaciones de Regencia</h1><p class="ui-muted mt-2">Hasta dos grados activos por Regente y gestión. La rotación se registra explícitamente cada año.</p></section>
    @if(!$ready)<p role="status" class="ui-alert-warning">El esquema de asignaciones está pendiente de aplicación autorizada.</p>@else
    @if(session('status'))<p role="status" class="ui-alert-success">{{ session('status') }}</p>@endif
    @if($errors->any())<div role="alert" class="ui-alert-danger">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    <form wire:submit="guardar" wire:confirm="¿Guardar esta asignación y su estado?" class="ui-panel grid gap-4 md:grid-cols-2">
        <label class="ui-label">Regente<select class="ui-select" wire:model="form.cod_reg" required><option value="">Seleccionar</option>@foreach($regents as $regent)<option value="{{ $regent->cod_reg }}">{{ $regent->personalInstitucional?->persona?->nom_per }} {{ $regent->personalInstitucional?->persona?->ape_pat_per }} · {{ $regent->cod_reg }}</option>@endforeach</select></label>
        <label class="ui-label">Gestión<select class="ui-select" wire:model="form.cod_gea" required><option value="">Seleccionar</option>@foreach($years as $year)<option value="{{ $year->cod_gea }}">{{ $year->ani_gea }}</option>@endforeach</select></label>
        <label class="ui-label">Grado<select class="ui-select" wire:model="form.cod_cur" required><option value="">Seleccionar</option>@foreach($courses as $course)<option value="{{ $course->cod_cur }}">{{ $course->nom_cur }}</option>@endforeach</select></label>
        <label class="ui-label">Estado<select class="ui-select" wire:model.boolean="form.activa"><option value="1">Activa</option><option value="0">Inactiva</option></select></label>
        <p class="ui-help">Seleccionar la misma combinación actualiza su estado, sin duplicar la asignación.</p>
        <button class="ui-btn-primary" wire:loading.attr="disabled">Guardar asignación</button>
    </form>
    <label class="ui-label">Consultar gestión<select class="ui-select" wire:model.live="gestion"><option value="">Todas</option>@foreach($years as $year)<option value="{{ $year->cod_gea }}">{{ $year->ani_gea }}</option>@endforeach</select></label>
    <div class="ui-card overflow-x-auto"><table class="ui-table"><thead><tr><th>Regente</th><th>Gestión</th><th>Grado</th><th>Estado</th><th>Último cambio</th><th>Acción</th></tr></thead><tbody>@forelse($assignments as $assignment)<tr><td>{{ $assignment->regente?->personalInstitucional?->persona?->nom_per }} · {{ $assignment->cod_reg }}</td><td>{{ $assignment->gestion?->ani_gea }}</td><td>{{ $assignment->curso?->nom_cur }}</td><td>{{ $assignment->activa ? 'Activa' : 'Inactiva' }}</td><td>{{ $assignment->updated_at?->format('d/m/Y H:i') }}</td><td>@if($assignment->activa)<button class="ui-btn-secondary" wire:click="retirar({{ $assignment->id }})" wire:confirm="¿Retirar este grado? Se conservará el historial en bitácora." wire:loading.attr="disabled">Retirar</button>@else Retirada @endif</td></tr>@empty<tr><td colspan="6">No hay asignaciones para esta consulta.</td></tr>@endforelse</tbody></table></div>
    {{ $assignments->links() }}
    @endif
</div>
