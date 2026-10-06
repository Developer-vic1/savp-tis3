@props(['user', 'nombre' => 'Usuario institucional', 'alternativa' => 'icono'])
@php($iniciales = collect(preg_split('/\s+/u', trim($nombre)))->filter()->take(2)->map(fn($parte)=>mb_strtoupper(mb_substr($parte,0,1)))->implode(''))
<span {{ $attributes->class('perfil-avatar') }} x-data="{ fotoFallida: false }" role="img" aria-label="Avatar de {{ $nombre }}">
    @if($user?->profile_photo_path)
        <img src="{{ $user->profile_photo_url }}" alt="" x-show="!fotoFallida" x-on:error="fotoFallida = true" class="h-full w-full object-cover" />
    @endif
    @if($alternativa === 'iniciales')
        <span class="perfil-avatar-iniciales" aria-hidden="true" @if($user?->profile_photo_path) x-show="fotoFallida" x-cloak @endif>{{ $iniciales ?: '·' }}</span>
    @else
        <i class="ph-duotone ph-user-circle" aria-hidden="true" @if($user?->profile_photo_path) x-show="fotoFallida" x-cloak @endif></i>
    @endif
</span>
