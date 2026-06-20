@php
    $name = $name ?? 'archivo';
    $label = $label ?? 'Adjuntar archivo';
@endphp

<label class="block rounded-lg border border-dashed p-4" style="border-color: var(--ui-border); background: var(--ui-surface-soft);">
    <span class="ui-title block text-sm font-black">{{ $label }}</span>
    <span class="ui-muted mt-1 block text-xs">PDF, documento, presentación, imagen, texto o archivo comprimido.</span>
    <input type="file" name="{{ $name }}" class="mt-3 block w-full text-sm">
</label>
