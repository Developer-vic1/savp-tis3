<div class="ui-card-soft p-4">
    <x-selector-institucional :modelo="$modeloEstado" :identificador="$prefijo.'-estado'" etiqueta="Estado de la cuenta" :requerido="true" :deshabilitado="$programarActivacion" :opciones="[['valor'=>'ACTIVO','etiqueta'=>'Activo'],['valor'=>'INACTIVO','etiqueta'=>'Inactivo']]" />
    <label class="ui-muted mt-4 flex items-start gap-2 text-sm"><input type="checkbox" wire:model.live="programarActivacion" /><span>Activar en una fecha de incorporación</span></label>
    @if($programarActivacion)
    <div class="mt-4"><x-calendario-institucional modelo="fechaActivacion" :identificador="$prefijo.'-activacion'" etiqueta="Fecha de activación" :minimo="now('America/La_Paz')->addDay()->toDateString()" :maximo="now('America/La_Paz')->addYears(5)->toDateString()" :inicio="now('America/La_Paz')->addDay()->toDateString()" :requerido="true" /></div>
    <p class="ui-muted mt-3 text-xs">Permanecerá inactiva hasta esa fecha. Se activará a las 00:00, hora de Bolivia.</p>
    <p class="ui-alert-warning mt-3 text-xs">Puedes revisar la fecha. La programación de incorporaciones todavía no está disponible para confirmar.</p>
    @else
    <p class="ui-muted mt-3 text-xs">{{ $modalCrear ? 'Si la cuenta está activa, enviaremos la bienvenida al crearla. Si queda inactiva, podrás entregar el acceso cuando la actives.' : 'El estado determina si la persona puede iniciar sesión.' }}</p>
    @endif
</div>
