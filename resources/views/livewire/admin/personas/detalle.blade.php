@teleport('body')
<div class="personas-modal" x-data x-on:keydown.escape.window="$wire.cerrarModalVer()" role="dialog" aria-modal="true" aria-labelledby="persona-detalle-titulo" x-trap.inert.noscroll="true">
    <div class="personas-modal-fondo" aria-hidden="true" wire:click="cerrarModalVer"></div>
    <section class="personas-modal-panel personas-modal-detalle">
        <header class="personas-modal-cabecera"><div><p class="ui-kicker">Ficha institucional</p><h2 id="persona-detalle-titulo" class="ui-title mt-2 text-xl font-bold">Información de la persona</h2></div><button type="button" class="personas-accion" wire:click="cerrarModalVer" aria-label="Cerrar ficha"><i class="ph-duotone ph-x" aria-hidden="true"></i></button></header>
        <div class="personas-modal-contenido"><div class="personas-identidad">@include('livewire.admin.personas.avatar', ['persona'=>$persona])<div><h3 class="ui-title text-xl font-bold">{{ $this->nombreCompleto($persona) }}</h3><p class="ui-muted mt-2 text-sm">{{ $persona->usuario ? 'Cuenta de acceso vinculada' : 'Sin cuenta de acceso' }}</p>@if(!$persona->est_per)<span class="ui-badge-warning mt-2">Registro inactivo</span>@endif</div></div>
            <dl class="personas-detalle-datos mt-6">
                @foreach(['Identificación' => trim($persona->ci_per.($persona->com_per ? '-'.$persona->com_per : '').' · '.$persona->exp_per), 'Fecha de nacimiento' => $persona->fec_nac_per?->format('d/m/Y') ?: 'Sin registrar', 'Edad' => $this->edadPersona($persona->fec_nac_per) !== null ? $this->edadPersona($persona->fec_nac_per).' años' : 'Sin fecha registrada', 'Género' => $persona->gen_per === 'M' ? 'Masculino' : ($persona->gen_per === 'F' ? 'Femenino' : 'Sin registrar'), 'Teléfono' => $persona->tel_per ?: 'Sin registrar', 'Correo' => $persona->ema_per ?: 'Sin registrar'] as $etiqueta=>$valor)<div class="ui-card-soft"><dt class="ui-muted text-xs">{{ $etiqueta }}</dt><dd class="ui-title mt-2 text-sm font-semibold personas-texto-largo"> @if($etiqueta==='Teléfono')<x-contacto-institucional :telefono="$persona->tel_per" />@elseif($etiqueta==='Correo')<x-contacto-institucional tipo="correo" :correo="$persona->ema_per" />@else{{ $valor }}@endif</dd></div>@endforeach
                <div class="ui-card-soft personas-campo-completo"><dt class="ui-muted text-xs">Dirección</dt><dd class="ui-title mt-2 text-sm font-semibold personas-texto-largo">{{ $persona->dir_per ?: 'Sin registrar' }}</dd></div>
            </dl>
        </div>
        <footer class="personas-modal-pie"><p class="ui-muted text-xs">Datos del registro personal.</p><button type="button" class="ui-btn ui-btn-secondary" wire:click="cerrarModalVer">Cerrar</button></footer>
    </section>
</div>
@endteleport
