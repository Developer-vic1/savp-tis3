<div class="flex flex-wrap gap-2">
    @foreach (['Primer Trimestre', 'Segundo Trimestre', 'Tercer Trimestre'] as $trimestre)
        <button type="button" class="rounded-lg border px-4 py-2 text-sm font-bold" style="border-color: var(--ui-border); color: var(--ui-text-soft);">
            {{ $trimestre }}
        </button>
    @endforeach
</div>
