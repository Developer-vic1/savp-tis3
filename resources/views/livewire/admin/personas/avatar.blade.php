<span class="personas-avatar" x-data="{fallo: false}" role="img" aria-label="Fotografía de {{ $this->nombreCompleto($persona) }}">
    @if($persona->fot_per)<img src="{{ Storage::disk('public')->url($persona->fot_per) }}" alt="" loading="lazy" class="h-full w-full object-cover" x-show="!fallo" x-on:error="fallo = true" />@endif
    <i class="ph-duotone ph-user-circle" aria-hidden="true" @if($persona->fot_per) x-show="fallo" x-cloak @endif></i>
</span>
