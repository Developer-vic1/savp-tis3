<form class="ui-panel space-y-4" wire:submit="guardar">
    <h2 class="ui-title text-xl font-bold">{{ $gradeId ? 'Revisar nota oficial' : 'Registrar nota oficial' }}</h2>
    @if(!$ready)<p class="ui-alert-warning">El registro por gestión está pendiente de aplicación autorizada.</p>
    @else
        @if($errors->any())<div role="alert" class="ui-alert-danger">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <div class="grid gap-4 md:grid-cols-2">
            <label class="ui-label">Estudiante inscrito<select wire:model.live="form.cod_est" class="ui-select" required @disabled($gradeId)><option value="">Seleccionar estudiante</option>@foreach($students as $student)<option value="{{ $student->cod_est }}">{{ $student->persona?->nom_per }} {{ $student->persona?->ape_pat_per }} · {{ $student->cod_est }}</option>@endforeach</select></label>
            <label class="ui-label">Periodo<select wire:model.live="form.cod_pev" class="ui-select" required @disabled($gradeId)><option value="">Seleccionar periodo</option>@foreach($periods as $period)<option value="{{ $period->cod_pev }}">{{ $period->nom_pev }}</option>@endforeach</select></label>
            <label class="ui-label">Nota sobre 100<input wire:model.live.debounce.500ms="form.not_cal" type="number" min="0" max="100" step="0.01" class="ui-input" required></label>
            <label class="ui-label">Observación<input wire:model.live.debounce.500ms="form.obs_cal" maxlength="255" class="ui-input"></label>
        </div>
        @if($analisis)
            <x-asistencia-inteligente :analisis="$analisis" titulo="Asistencia de nota oficial" />
            @if(is_numeric($form['not_cal'] ?? null) && $form['not_cal']>=0 && $form['not_cal']<=100)
                <div class="ui-card-soft p-4"><p class="ui-title">{{ $analisis['desempeno'] ?? '' }}</p><p class="ui-muted mt-2">Observación sugerida: {{ $analisis['datos']['obs_cal'] ?? '' }}</p><button wire:click="aplicarObservacion" wire:loading.attr="disabled" type="button" class="ui-btn-secondary mt-3">Usar observación sugerida</button></div>
            @endif
        @endif
        <span wire:loading role="status" class="ui-muted">Revisando nota…</span>
        @if($gradeId)<button type="button" class="ui-btn-secondary" wire:click="cancelarEdicion">Cancelar revisión</button>@endif
        <button class="ui-btn-primary" type="submit" wire:loading.attr="disabled" @disabled($analisis && !($analisis['puede_guardar'] ?? false))>{{ $gradeId ? 'Guardar revisión' : 'Registrar nota' }}</button>
    @endif
</form>
