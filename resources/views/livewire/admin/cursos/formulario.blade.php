<div x-show="formulario" x-cloak class="personas-modal docentes-modal cursos-modal" role="dialog" aria-modal="true" aria-labelledby="curso-formulario-titulo" x-on:keydown.escape.window="if(formulario&&procesando!=='guardar') cerrarFormulario()" x-trap.inert.noscroll="formulario">
    <div class="personas-modal-fondo" x-on:click="if(procesando!=='guardar') cerrarFormulario()" aria-hidden="true"></div>
    <section class="personas-modal-panel docentes-horario-panel" x-ref="formularioPanel" x-show="formulario" x-transition.opacity.duration.150ms>
        <header class="personas-modal-cabecera"><div><p class="ui-kicker">Preparación institucional</p><h2 id="curso-formulario-titulo" class="ui-title text-xl font-bold mt-1">{{ ['crear'=>'Registrar grado','editar'=>'Editar grado','desactivar'=>'Desactivar grado','reactivar'=>'Reactivar grado'][$operacion] }}</h2><p class="ui-muted text-sm mt-2">Revisa el grado, su autorización y el motivo del cambio.</p></div><button type="button" class="personas-accion" x-on:click="cerrarFormulario()" :disabled="procesando==='guardar'" aria-label="Cerrar formulario del curso"><i class="ph-duotone ph-x" aria-hidden="true"></i></button></header>
        <x-fases-institucionales :actual="$faseCurso" :pasos="[1=>['titulo'=>'Grado','icono'=>'ph-graduation-cap'],2=>['titulo'=>'Motivo y PDF','icono'=>'ph-file-pdf'],3=>['titulo'=>'Verificar y confirmar','icono'=>'ph-shield-check']]" />
        <form x-on:submit.prevent="@js($faseCurso)===3?guardar():continuarFase()" class="cursos-formulario">
            <div class="personas-modal-contenido">
                @if($modalFormulario)
                @if(!$control['puede'])<section class="ui-alert-warning cursos-bloqueo" role="status"><i class="ph-duotone ph-shield-check" aria-hidden="true"></i><div><h3 class="font-bold">Protegemos la estructura de esta gestión</h3><ul class="mt-2">@foreach($control['bloqueos'] as $bloqueo)<li>{{ $bloqueo }}</li>@endforeach</ul><p class="mt-2 text-xs">Puedes revisar los requisitos. El registro se habilita durante la preparación del ciclo, antes de clases y de las primeras notas.</p></div></section>@endif
                <div wire:key="fase-curso-{{ $faseCurso }}" class="mt-4">
                    @if($faseCurso===1)
                        @include('livewire.admin.cursos.definir')
                    @elseif($faseCurso===2)
                        <div class="cursos-form-grilla"><section>@include('livewire.admin.cursos.respaldar')
                        </section><x-requisitos-respaldo-academico /></div>
                    @else
                        @include('livewire.admin.cursos.confirmar')
                    @endif
                </div>
                <x-input-error for="cambio" /><p x-show="errorProceso" x-cloak class="ui-error mt-3" role="alert" x-text="errorProceso"></p>
                @endif
            </div>
            <footer class="personas-modal-pie"><p class="ui-muted text-xs" role="status"><span x-show="procesando" x-text="mensajeProceso"></span><span x-show="!procesando">Paso {{ $faseCurso }} de 3. Los datos se conservan al volver.</span></p><div class="cursos-acciones"><button type="button" class="ui-btn ui-btn-secondary" x-on:click="cerrarFormulario()" :disabled="procesando==='guardar'">Cancelar</button>@if($faseCurso>1)<button type="button" class="ui-btn ui-btn-secondary" x-on:click="volverFase()" :disabled="!!procesando">Volver</button>@endif
            @if($faseCurso<3)<button type="submit" class="ui-btn ui-btn-primary" :disabled="!!procesando || ($wire.faseCurso===2 && !$wire.pdfEscaneado)" @disabled($faseCurso===2 && !$pdfEscaneado) wire:loading.attr="disabled"><i class="ph-duotone" :class="procesando==='fase'?'ph-spinner-gap cursos-giro':'ph-arrow-right'" aria-hidden="true"></i><span x-text="procesando==='fase'?'Revisando…':@js($faseCurso===1?'Continuar con el respaldo':'Verificar datos y continuar')"></span></button>
            @else<button type="submit" class="ui-btn ui-btn-primary" :disabled="!!procesando || @js(!($control['puede']??false) || !($analisisDocumento['coherente']??false) || !$autenticidadConfirmada || !$confirmarCambio)" @disabled(!($control['puede']??false) || !($analisisDocumento['coherente']??false) || !$autenticidadConfirmada || !$confirmarCambio) wire:loading.attr="disabled"><i class="ph-duotone" :class="procesando==='guardar'?'ph-spinner-gap cursos-giro':'ph-shield-check'" aria-hidden="true"></i><span x-text="procesando==='guardar'?'Registrando…':'Confirmar cambio'"></span></button>@endif</div></footer>
        </form>
    </section>
</div>