<form wire:submit="preguntar" class="ui-panel space-y-4">
    <h2 class="ui-title text-xl font-bold">Asistente de estudio</h2>
    <p class="ui-muted">Escribe una pregunta o solicita una explicación, un resumen o ejercicios. Evita incluir datos personales.</p>
    <label class="ui-label block" for="study-question">Consulta<textarea id="study-question" class="ui-textarea" wire:model="question" rows="5" minlength="5" maxlength="2000" required></textarea></label>
    @error('question')<p class="ui-error">{{ $message }}</p>@enderror
    <button type="submit" class="ui-btn-primary" wire:loading.attr="disabled" wire:target="preguntar"><span wire:loading.remove wire:target="preguntar">Consultar</span><span wire:loading wire:target="preguntar">Consultando…</span></button>
    @if($statusMessage)<p class="ui-alert-info" role="status">{{ $statusMessage }}</p>@endif
    @if($answer)<article class="ui-card-soft whitespace-pre-wrap p-4">{{ $answer }}</article>@endif
    @foreach($sources as $source)<article class="ui-card-soft p-3"><h3 class="font-bold">{{ $source['title'] }}</h3><p class="ui-muted">{{ $source['institution'] }} · {{ $source['publication_date']??'Fecha no informada' }}</p><p class="ui-muted">Referencia: {{ $source['reference'] }}</p></article>@endforeach
    @if($statusMessage && !$answer && auth()->user()->can('Materiales_Aula'))<a class="underline" href="{{ route('estudiante.area',['area'=>'fuentes']) }}">Continuar con las fuentes y materiales disponibles</a>@endif
</form>
