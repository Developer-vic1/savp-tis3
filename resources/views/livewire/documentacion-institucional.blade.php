<div class="space-y-5">
    <header class="ui-card p-6">
        <p class="ui-kicker">Respaldo de documentos</p>
        <h1 class="text-2xl font-bold">Documentación institucional</h1>
        <p class="mt-2 text-sm" style="color:var(--ui-muted)">Referencias privadas para comparar documentos. Director vigente: {{ $autoridad['name'] ?? 'Pendiente de configuración' }}.</p>
    </header>
    @if($mensaje)<p class="ui-alert-success" role="status">{{ $mensaje }}</p>@endif
    <div class="grid gap-5 lg:grid-cols-2">
        @foreach(['firma'=>'Firma del Director vigente','sello'=>'Sello de la unidad educativa'] as $campo=>$titulo)
            @php $referencia = $referencias[$campo] ?? null; @endphp
            <section class="ui-card p-5 space-y-4">
                <h2 class="font-bold">{{ $titulo }}</h2>
                @if($referencia)
                    <div class="rounded-xl p-4" style="background:var(--ui-surface-soft)">
                        <img src="{{ route('documentacion.referencia', $referencia->id) }}" alt="{{ $titulo }}" class="mx-auto h-44 max-w-full object-contain rounded-lg">
                        <p class="mt-3 text-sm font-semibold">{{ $referencia->titular }}</p>
                        <p class="text-xs" style="color:var(--ui-muted)">Registrado {{ $referencia->created_at->timezone('America/La_Paz')->format('d/m/Y H:i') }}. Las versiones anteriores se conservan.</p>
                    </div>
                @else
                    <p class="ui-alert-info">Falta registrar esta referencia. Los documentos que la requieren no pueden completar la revisión.</p>
                @endif
                <form wire:submit="guardar('{{ strtoupper($campo) }}')" class="space-y-3">
                    <label class="block text-sm font-semibold" for="referencia-{{ $campo }}">{{ $referencia ? 'Subir nueva versión' : 'Subir imagen' }}</label>
                    <input id="referencia-{{ $campo }}" class="ui-input w-full" type="file" accept="image/png,image/jpeg" wire:model="{{ $campo }}">
                    <p class="text-xs" style="color:var(--ui-muted)">PNG o JPG, hasta 4 MB. Imagen completa, legible y con fondo claro; evita hojas vacías.</p>
                    @error($campo)<p class="text-sm" style="color:var(--ui-danger)" role="alert">{{ $message }}</p>@enderror
                    @error('imagen')<p class="text-sm" style="color:var(--ui-danger)" role="alert">{{ $message }}</p>@enderror
                    @if($this->{$campo})<img src="{{ $this->{$campo}->temporaryUrl() }}" alt="Vista previa de la nueva referencia" class="h-32 max-w-full object-contain">@endif
                    <button class="ui-btn-primary" type="submit" wire:loading.attr="disabled" wire:target="{{ $campo }},guardar">Guardar {{ $campo }}</button>
                    <span wire:loading wire:target="{{ $campo }},guardar" class="text-sm" role="status">Conservando referencia…</span>
                </form>
            </section>
        @endforeach
    </div>
    <section class="ui-card p-5 space-y-2">
        <h2 class="font-bold">Uso y vigencia</h2>
        <p class="text-sm">El sello pertenece a la institución. La firma se vincula al Director vigente y deja de utilizarse cuando cambia la autoridad; el nuevo Director recibe un aviso para registrar la suya.</p>
        <p class="text-sm" style="color:var(--ui-muted)">La comparación visual identifica coincidencias con estas imágenes. La autenticidad y la autorización se confirman con la autoridad emisora. Estas imágenes no constituyen una firma electrónica certificada.</p>
    </section>
</div>
