@teleport('body')
<div class="personas-modal docentes-modal" x-data="{}" x-trap.inert.noscroll="true" role="dialog" aria-modal="true" aria-labelledby="docente-horario-titulo" x-on:keydown.escape.window="$wire.cerrarFicha()">
    <div class="personas-modal-fondo" wire:click="cerrarFicha" aria-hidden="true"></div><section class="personas-modal-panel docentes-horario-panel">
    <header class="personas-modal-cabecera"><div><p class="ui-kicker">Organización de clases · Gestión {{ $nombreGestion }}</p><h2 class="ui-title text-xl font-bold mt-1" id="docente-horario-titulo">Horario docente</h2><p class="ui-muted text-sm mt-2">{{ $ficha['nombre'] }}</p></div><button type="button" class="personas-accion" wire:click="cerrarFicha" aria-label="Cerrar horario docente"><i class="ph-duotone ph-x" aria-hidden="true"></i></button></header>
    <div class="personas-modal-contenido"><x-horario-institucional :eventos="$horario" identificador="docentes-horario" /></div>
    <footer class="personas-modal-pie"><p class="ui-muted text-xs">Consulta períodos, turnos y clases sin modificar las asignaciones.</p><button type="button" class="ui-btn ui-btn-secondary" wire:click="cerrarFicha">Cerrar horario</button></footer>
    </section>
</div>
@endteleport
