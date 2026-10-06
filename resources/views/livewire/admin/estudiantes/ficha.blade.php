@php($personaFicha=$estudianteDetalle->persona)
@php($nombreFicha=$this->nombreCompleto($personaFicha))
@php($inscripcionFicha=$this->inscripcionActual($estudianteDetalle))
@teleport('body')
<div class="personas-modal estudiantes-drawer" role="dialog" aria-modal="true" aria-labelledby="estudiante-ficha-titulo"
    x-data="{}" x-trap.inert.noscroll="true" x-on:keydown.escape.window="$wire.cerrarPanelDetalle()">
    <div class="personas-modal-fondo" wire:click="cerrarPanelDetalle" aria-hidden="true"></div>
    <section class="personas-modal-panel estudiantes-modal-panel estudiantes-drawer-panel">
        <header class="personas-modal-cabecera">
            <div class="estudiantes-identidad"><x-avatar-institucional :user="$personaFicha?->usuario" :nombre="$nombreFicha" />
                <div><p class="ui-kicker">Ficha del estudiante</p><h2 id="estudiante-ficha-titulo" class="ui-title text-xl font-bold mt-1">{{ $nombreFicha }}</h2></div>
            </div>
            <button type="button" class="personas-accion" wire:click="cerrarPanelDetalle" aria-label="Cerrar ficha del estudiante"><i class="ph-duotone ph-x" aria-hidden="true"></i></button>
        </header>
        <div class="personas-modal-contenido estudiantes-ficha-contenido">
            <div class="estudiantes-ficha-resumen">
                <i class="ph-duotone ph-student" aria-hidden="true"></i>
                <div><p class="ui-kicker">Gestión {{ $nombreGestionActual }}</p>
                    <strong>{{ $inscripcionFicha?->curso?->nom_cur ?: 'Sin inscripción en esta gestión' }}{{ $inscripcionFicha?->paralelo ? ' · '.$inscripcionFicha->paralelo->nom_par : '' }}</strong>
                    <p>RUDE {{ $estudianteDetalle->rud_est ?: 'sin registro' }}</p>
                </div>
                <span class="ui-badge-info">{{ $this->estadoEstudianteLabel($estudianteDetalle->est_est) }}</span>
            </div>
            <x-desplegable-institucional identificador="ficha-trayecto" titulo="Trayecto académico" icono="graduation-cap"
                :resumen="$estudianteDetalle->especialidad?->nom_esp ?: 'Sin especialidad registrada'" :abierto="true">
                <dl class="estudiantes-ficha-datos">
                    <div><dt>Vinculación</dt><dd>{{ $estudianteDetalle->tipoVinculacion?->nom_tve ?: 'Por revisar' }}</dd></div>
                    <div><dt>Especialidad registrada</dt><dd>{{ $estudianteDetalle->especialidad?->nom_esp ?: 'Sin especialidad registrada' }}</dd></div>
                    <div><dt>Institución de procedencia</dt><dd>{{ mb_strtoupper($estudianteDetalle->institucionProcedencia?->nom_ipe ?: 'Sin procedencia registrada') }}</dd></div>
                    <div><dt>Situación de la inscripción actual</dt><dd>{{ $inscripcionFicha ? ucfirst(mb_strtolower($inscripcionFicha->est_ins)) : 'Sin inscripción en esta gestión' }}</dd></div>
                    <div><dt>Fecha de inscripción</dt><dd>{{ $inscripcionFicha?->fei_ins?->format('d/m/Y') ?: 'Sin fecha registrada' }}</dd></div>
                </dl>
            </x-desplegable-institucional>
            <x-desplegable-institucional identificador="ficha-historial" titulo="Historial completo" icono="path"
                :resumen="$estudianteDetalle->inscripciones->count().' '.($estudianteDetalle->inscripciones->count()===1?'inscripción registrada':'inscripciones registradas')" :abierto="true">
                @include('livewire.admin.estudiantes.trayectoria')
            </x-desplegable-institucional>
            <x-desplegable-institucional identificador="ficha-persona" titulo="Persona y contacto" icono="user-circle" resumen="Identidad, correo y contacto registrado">
                <dl class="estudiantes-ficha-datos">
                    <div><dt>Documento de identidad</dt><dd>{{ $this->ciCompleto($personaFicha) }}</dd></div>
                    <div><dt>Edad</dt><dd>{{ $this->edad($personaFicha)!==null ? $this->edad($personaFicha).' años' : 'Sin fecha válida' }}</dd></div>
                    <div><dt>Correo personal</dt><dd><x-contacto-institucional tipo="correo" :correo="$personaFicha?->ema_per" /></dd></div>
                    <div><dt>Teléfono</dt><dd><x-contacto-institucional :telefono="$personaFicha?->tel_per" /></dd></div>
                    <div><dt>Dirección registrada</dt><dd>{{ $personaFicha?->dir_per ?: 'Sin dirección registrada' }}</dd></div>
                </dl>
                <p class="ui-muted text-xs mt-4">Estos datos proceden del registro de Personas.</p>
            </x-desplegable-institucional>
            <x-desplegable-institucional identificador="ficha-acceso" titulo="Acceso al sistema" icono="shield-check"
                :resumen="$personaFicha?->usuario ? ($personaFicha->usuario->est_usu==='ACTIVO'?'Cuenta activa':'Cuenta inactiva') : 'Sin cuenta vinculada'">
                <dl class="estudiantes-ficha-datos">
                    <div><dt>Cuenta de acceso</dt><dd><x-contacto-institucional tipo="correo" :correo="$personaFicha?->usuario?->email" /></dd></div>
                    <div><dt>Estado del usuario</dt><dd>{{ $personaFicha?->usuario ? ($personaFicha->usuario->est_usu==='ACTIVO'?'Acceso activo':'Acceso inactivo') : 'Sin cuenta vinculada' }}</dd></div>
                </dl>
                <p class="ui-muted text-xs mt-4">La trayectoria permanece en el registro aunque cambie el acceso de la cuenta.</p>
            </x-desplegable-institucional>
        </div>
        <footer class="personas-modal-pie">
            <button type="button" wire:click="cerrarPanelDetalle" class="ui-btn ui-btn-secondary">Cerrar</button>
            <button type="button" class="ui-btn ui-btn-secondary" wire:click="abrirModalEditar(@js($estudianteDetalle->getKey()))">Editar datos académicos</button>
            @can('Inscripciones')<a class="ui-btn ui-btn-primary" href="{{ route($rutaInscripciones) }}"><i class="ph-duotone ph-arrow-up-right" aria-hidden="true"></i>Ir a inscripciones</a>@endcan
        </footer>
    </section>
</div>
@endteleport
