@php
    $value = max(0, min(100, (int) ($value ?? 0)));
    $label = $label ?? 'Progreso';
@endphp

<div>
    <div class="mb-2 flex items-center justify-between text-xs font-bold">
        <span class="ui-muted">{{ $label }}</span>
        <span class="ui-title">{{ $value }}%</span>
    </div>
    <div class="h-2 overflow-hidden rounded-full bg-[var(--ui-surface-muted)]">
        <div class="h-full rounded-full bg-[var(--savp-green)]" style="width: {{ $value }}%"></div>
    </div>
</div>
