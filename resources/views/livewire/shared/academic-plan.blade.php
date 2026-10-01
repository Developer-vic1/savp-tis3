<section class="ui-panel space-y-4">
    <h2 class="ui-title text-xl font-bold">Mis objetivos y acciones</h2>
    @if(!$available)<p class="ui-alert-warning">El registro de metas personales está pendiente de habilitación institucional. Puedes seguir trabajando en las actividades de tus materias.</p>
    @endif
    <form wire:submit="{{ $available ? 'save' : 'validateDraft' }}" class="grid gap-4 sm:grid-cols-2">
        <label class="ui-label">Título<input class="ui-input" wire:model="titulo" maxlength="180" required>@error('titulo')<span class="ui-error">{{ $message }}</span>@enderror</label>
        <label class="ui-label">Fecha objetivo<input class="ui-input" type="date" wire:model="fecha">@error('fecha_objetivo')<span class="ui-error">{{ $message }}</span>@enderror</label>
        <label class="ui-label">Objetivo<textarea class="ui-textarea" wire:model="objetivo" maxlength="4000" required></textarea>@error('objetivo')<span class="ui-error">{{ $message }}</span>@enderror</label>
        <label class="ui-label">Acción personal<textarea class="ui-textarea" wire:model="accion" maxlength="4000"></textarea>@error('accion')<span class="ui-error">{{ $message }}</span>@enderror</label>
        <label class="ui-label">Estado declarado<select class="ui-select" wire:model="estado">@foreach($editing ? ['BORRADOR','ACTIVA','COMPLETADA','CANCELADA'] : ['BORRADOR','ACTIVA'] as $option)<option>{{ $option }}</option>@endforeach</select>@error('estado')<span class="ui-error">{{ $message }}</span>@enderror</label>
        @if($editing)<label class="ui-label">Motivo del cambio<textarea class="ui-textarea" wire:model="motivo" required maxlength="2000"></textarea>@error('motivo')<span class="ui-error">{{ $message }}</span>@enderror</label>@endif
        <div class="flex gap-3"><button type="submit" class="ui-btn-primary" wire:loading.attr="disabled" wire:target="save,validateDraft">{{ !$available ? 'Revisar borrador sin guardar' : ($editing ? 'Guardar revisión' : 'Guardar meta') }}</button><button type="button" class="ui-btn-secondary" wire:click="cancel">{{ $editing ? 'Cancelar edición' : 'Limpiar borrador' }}</button></div>
        <p wire:loading wire:target="save,validateDraft" class="ui-muted" role="status">{{ $available ? 'Guardando tu meta…' : 'Revisando tu borrador…' }}</p>
    </form>
    @if($draftValid)<p class="ui-alert-info" role="status">El borrador cumple las validaciones. No se guardó; necesitarás registrarlo cuando se habiliten las metas personales.</p>@endif
    <p class="ui-muted">Los estados expresan tu planificación personal; no calculan rendimiento, afinidad profesional ni cumplimiento de tus tareas.</p>
    @if($available)
    @forelse($goals as $goal)<article class="ui-card-soft p-4" wire:key="goal-{{ $goal->id }}"><h3 class="ui-title font-bold">{{ $goal->titulo }}</h3><p class="ui-muted">{{ $goal->estado }} · {{ $goal->fecha_objetivo?->format('d/m/Y') ?? 'Sin fecha objetivo' }}</p><p class="whitespace-pre-wrap">{{ $goal->objetivo }}</p><p class="ui-muted whitespace-pre-wrap">{{ $goal->accion }}</p><button class="ui-btn-secondary mt-3" type="button" wire:click="edit(@js($goal->id))" wire:loading.attr="disabled">Editar con motivo</button></article>@empty<p class="ui-muted">Todavía no registraste metas personales.</p>@endforelse
    {{ $goals->links() }}
    @endif
</section>
