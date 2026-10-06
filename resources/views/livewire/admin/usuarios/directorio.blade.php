<div class="usuarios-directorio" :class="vista==='galeria' ? 'usuarios-galeria' : ''" x-show="vista!=='tabla'" x-cloak x-transition.opacity.duration.160ms aria-label="Directorio de cuentas">
@forelse($usuarios as $cuenta)
    @php
        $nombre = trim(($cuenta->persona?->nom_per ?? '').' '.($cuenta->persona?->ape_pat_per ?? '').' '.($cuenta->persona?->ape_mat_per ?? '')) ?: 'Persona sin vincular';
        $actoresCuenta = $cuenta->roles->whereIn('name', app(\App\Support\InstitutionalRoleGovernance::class)->rolesInstitucionales())->pluck('name');
        $rolCuenta = $actoresCuenta->count() === 1 ? $actoresCuenta->first() : 'Revisión requerida';
        $activo = $cuenta->est_usu === 'ACTIVO';
    @endphp
    <article class="ui-card usuarios-tarjeta" wire:key="tarjeta-usuario-{{ $cuenta->cod_usu }}"><div class="usuarios-tarjeta-identidad"><x-avatar-institucional :user="$cuenta" :nombre="$nombre" /><div><h2 class="ui-title font-bold personas-texto-largo">{{ $nombre }}</h2></div><input type="checkbox" wire:model.live="selected" value="{{ $cuenta->cod_usu }}" @disabled($cuenta->cod_usu === auth()->user()->cod_usu) aria-label="Seleccionar cuenta de {{ $nombre }}" /></div>
    <div class="personas-acciones"><span class="ui-badge">{{ $rolCuenta }}</span><span class="{{ $activo ? 'ui-badge-success' : 'ui-badge-warning' }}">{{ $activo ? 'Activo' : 'Inactivo' }}</span></div><p class="ui-muted text-sm personas-texto-largo"><i class="ph-duotone ph-envelope-simple" aria-hidden="true"></i> <x-contacto-institucional tipo="correo" :correo="$cuenta->email" /></p><p class="ui-muted text-xs">{{ $cuenta->email_verified_at ? 'Correo verificado' : 'Correo sin verificar' }} · No indica entrega del enlace.</p>
    @if($activacion = $activacionesPendientes->get($cuenta->cod_usu))<p class="ui-muted text-xs">Activación: {{ $activacion->fecha_activacion->timezone('America/La_Paz')->format('d/m/Y') }}</p>@endif
    <footer class="usuarios-tarjeta-pie"><button type="button" class="ui-btn ui-btn-secondary" wire:click="abrirModalVer('{{ $cuenta->cod_usu }}')"><i class="ph-duotone ph-eye" aria-hidden="true"></i>Ver ficha</button><div class="personas-acciones">
    <button type="button" class="personas-accion" wire:click="abrirModalEditar('{{ $cuenta->cod_usu }}')" aria-label="Editar cuenta de {{ $nombre }}"><i class="ph-duotone ph-pencil-simple" aria-hidden="true"></i></button>
    @if($activo)@can('usuarios.reset_password')<button type="button" class="personas-accion" wire:click="prepararInvitacion('{{ $cuenta->cod_usu }}')" aria-label="Enviar enlace de acceso a {{ $cuenta->email }}"><i class="ph-duotone ph-envelope-simple" aria-hidden="true"></i></button>@endcan
    @endif</div></footer></article>
@empty
    <div class="ui-card personas-vacio"><span class="personas-icono"><i class="ph-duotone ph-users" aria-hidden="true"></i></span><h2 class="ui-title font-bold mt-4">No se encontraron usuarios</h2><p class="ui-muted mt-2 text-sm">Revisa los filtros o limpia la búsqueda.</p></div>
@endforelse
</div>
