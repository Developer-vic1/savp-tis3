@extends('layouts.app')
@section('title', 'Mi perfil')
@section('content')
@php
    $user = Auth::user();
    $persona = $user->persona;
    $nombre = trim(($persona?->nom_per ?? '').' '.($persona?->ape_pat_per ?? '').' '.($persona?->ape_mat_per ?? '')) ?: 'Usuario institucional';
    $rol = app(\App\Services\RoleDashboardResolver::class)->roleFor($user) ?? 'Usuario institucional';
    $documento = trim(($persona?->ci_per ?? '').(($persona?->com_per ?? null) ? '-'.$persona->com_per : '').' '.($persona?->exp_per ?? '')) ?: 'Sin registrar';
    $genero = match($persona?->gen_per) { 'M' => 'Masculino', 'F' => 'Femenino', default => 'Sin registrar' };
    $datos = [
        'Nombres' => $persona?->nom_per ?: 'Sin registrar',
        'Apellido paterno' => $persona?->ape_pat_per ?: 'Sin registrar',
        'Apellido materno' => $persona?->ape_mat_per ?: 'Sin registrar',
        'Documento' => $documento,
        'Fecha de nacimiento' => $persona?->fec_nac_per ? \Carbon\Carbon::parse($persona->fec_nac_per)->format('d/m/Y') : 'Sin registrar',
        'Género' => $genero,
    ];
@endphp
<div class="perfil-institucional" x-data="perfilInstitucional()">
    <section class="ui-card perfil-hero" aria-labelledby="perfil-titulo">
        <div class="perfil-identidad">
            <x-avatar-institucional :user="$user" :nombre="$nombre" class="perfil-avatar-principal" />
            <div class="min-w-0">
                <p class="ui-kicker">Perfil institucional</p>
                <h1 id="perfil-titulo" class="ui-title perfil-nombre mt-2 font-extrabold">{{ $nombre }}</h1>
                <div class="perfil-identidad-detalle"><span class="ui-badge-success">{{ $rol }}</span><span class="ui-muted">{{ $user->email }}</span></div>
            </div>
        </div>
        <div class="perfil-hero-acciones">
            <span class="ui-badge-success"><i class="ph-duotone ph-check-circle" aria-hidden="true"></i> Cuenta activa</span>
            @if(Laravel\Fortify\Features::canUpdateProfileInformation())
                <button class="ui-btn ui-btn-primary" type="button" x-on:click="abrirEdicionPerfil()"><i class="ph-duotone ph-camera" aria-hidden="true"></i> Editar foto y contacto</button>
            @endif
            @if(Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <button class="ui-btn ui-btn-secondary" type="button" x-on:click="abrirPassword()"><i class="ph-duotone ph-lock-key" aria-hidden="true"></i> Cambiar contraseña</button>
            @endif
        </div>
    </section>

    @php
        $categorias = ['seccion-datos' => ['Mis datos', 'identification-card']];
        if (Laravel\Fortify\Features::canUpdateProfileInformation()) $categorias['seccion-editar-perfil'] = ['Foto y contacto', 'user-circle'];
        if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords())) $categorias['seccion-password'] = ['Contraseña', 'lock-key'];
        $categorias['seccion-actividad'] = ['Acceso y actividad', 'clock-counter-clockwise'];
        $categorias['seccion-seguridad'] = ['Seguridad', 'shield-check'];
    @endphp
    <nav class="perfil-navegacion" role="tablist" aria-label="Categorías de mi perfil" x-on:keydown="navegarTeclado($event)">
        @foreach($categorias as $id => [$etiqueta, $icono])
            <button id="categoria-{{ $id }}" type="button" role="tab" aria-controls="{{ $id }}"
                :aria-selected="activo === '{{ $id }}'" :tabindex="activo === '{{ $id }}' ? 0 : -1"
                x-on:click="cambiarCategoria('{{ $id }}')"><i class="ph-duotone ph-{{ $icono }}" aria-hidden="true"></i> {{ $etiqueta }}</button>
        @endforeach
    </nav>

    <div class="perfil-contenido">
        <section id="seccion-datos" role="tabpanel" aria-labelledby="categoria-seccion-datos" x-show="activo === 'seccion-datos'" x-cloak class="ui-card perfil-seccion">
            <p class="ui-kicker">Datos personales</p>
            <h2 id="perfil-datos-titulo" tabindex="-1" class="ui-title mt-2 text-xl font-bold">Tu información institucional</h2>
            <p class="ui-muted mt-2 text-sm">Estos datos identifican tu registro en la institución.</p>
            <dl class="perfil-datos mt-5">
                @foreach($datos as $etiqueta => $valor)
                    <div class="ui-card-soft"><dt class="ui-muted text-xs">{{ $etiqueta }}</dt><dd class="ui-title mt-1 text-sm font-semibold">{{ $valor }}</dd></div>
                @endforeach
            </dl>
            <p class="ui-muted mt-4 text-xs leading-5">Para corregir datos de identidad, comunícate con administración.</p>
        </section>
        <section id="seccion-actividad" role="tabpanel" aria-labelledby="categoria-seccion-actividad" x-show="activo === 'seccion-actividad'" x-cloak class="ui-card perfil-seccion">
            <p class="ui-kicker">Acceso y actividad</p>
            <h2 id="perfil-actividad-titulo" tabindex="-1" class="ui-title mt-2 text-xl font-bold">Tu cuenta en SAVP</h2>
            <dl class="perfil-actividad mt-5">
                <div><i class="ph-duotone ph-envelope" aria-hidden="true"></i><dt>Correo de acceso</dt><dd>{{ $user->email }}</dd></div>
                <div><i class="ph-duotone ph-phone" aria-hidden="true"></i><dt>Teléfono</dt><dd>{{ $persona?->tel_per ?: 'Sin registrar' }}</dd></div>
                <div><i class="ph-duotone ph-map-pin" aria-hidden="true"></i><dt>Dirección</dt><dd>{{ $persona?->dir_per ?: 'Sin registrar' }}</dd></div>
                <div><i class="ph-duotone ph-clock" aria-hidden="true"></i><dt>Último acceso registrado</dt><dd>{{ $user->last_login_at?->timezone('America/La_Paz')->format('d/m/Y · H:i') ?? 'Sin fecha registrada' }}</dd></div>
            </dl>
        </section>

    @if(Laravel\Fortify\Features::canUpdateProfileInformation())
        <section id="seccion-editar-perfil" role="tabpanel" aria-labelledby="categoria-seccion-editar-perfil" x-show="activo === 'seccion-editar-perfil'" x-cloak class="ui-card perfil-seccion">
            <div class="perfil-seccion-cabecera">
                <div><p class="ui-kicker">Foto y contacto</p><h2 tabindex="-1" class="ui-title mt-2 text-xl font-bold">Mantén tus datos al día</h2><p class="ui-muted mt-2 text-sm">Revisa la fotografía y los datos antes de guardar. Confirmaremos tu identidad con la contraseña actual.</p></div>
            </div>
            <div class="mt-5">@livewire('perfil.actualizar-informacion')</div>
        </section>
    @endif
    @if(Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
        <section id="seccion-password" role="tabpanel" aria-labelledby="categoria-seccion-password" x-show="activo === 'seccion-password'" x-cloak class="ui-card perfil-seccion">
            <div class="perfil-seccion-cabecera">
                <div><p class="ui-kicker">Contraseña</p><h2 tabindex="-1" class="ui-title mt-2 text-xl font-bold">Protege tu acceso</h2><p class="ui-muted mt-2 text-sm">Necesitarás tu contraseña actual y confirmar la nueva.</p></div>
            </div>
            <div class="mt-5">@livewire('profile.update-password-form')</div>
        </section>
    @endif
    <section id="seccion-seguridad" role="tabpanel" aria-labelledby="categoria-seccion-seguridad" x-show="activo === 'seccion-seguridad'" x-cloak class="ui-card perfil-seccion">
        <p class="ui-kicker">Seguridad</p>
        <h2 tabindex="-1" class="ui-title mt-2 text-xl font-bold">Tú controlas tu cuenta</h2>
        <p class="ui-muted mt-2 text-sm">Refuerza tu acceso y revisa los dispositivos conectados.</p>
        <div class="mt-5 space-y-5">
            @if(Laravel\Fortify\Features::canManageTwoFactorAuthentication()) @livewire('profile.two-factor-authentication-form') @endif
            @livewire('profile.logout-other-browser-sessions-form')
        </div>
    @if(Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())
        <div class="mt-5 border-t border-[var(--ui-border)] pt-5">
            @livewire('perfil.retirar-acceso')
        </div>
    @endif
    </section>
    </div>
</div>
@endsection
