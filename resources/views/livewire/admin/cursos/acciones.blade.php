<div class="cursos-acciones" role="group" aria-label="Consultar {{ $curso['nombre'] }}">
    @foreach(['ficha'=>['ph-identification-card','Ficha'],'horario'=>['ph-calendar-dots','Horario'],'carga'=>['ph-books','Carga']] as $seccion=>$accion)
    <button type="button" class="ui-btn ui-btn-secondary" title="{{ $accion[1] }} de {{ $curso['nombre'] }}" x-on:click="consultar(@js($curso['cod_cur']),@js($seccion),$event.currentTarget)" :disabled="!!procesando"><i class="ph-duotone" :class="procesando===@js($seccion.'-'.$curso['cod_cur'])?'ph-spinner-gap cursos-giro':@js($accion[0])" aria-hidden="true"></i><span>{{ $accion[1] }}</span></button>
    @endforeach
</div>
