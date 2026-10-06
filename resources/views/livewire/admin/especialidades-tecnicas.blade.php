<div class="asignaturas-pagina space-y-5" x-data="asignaturasInstitucionales()">
@include('livewire.admin.asignaturas.panel-especialidades')
@include('livewire.admin.partials.catalogo-institucional')
@include('livewire.admin.asignaturas.incorporaciones')
@if($modalFormulario)@include('livewire.admin.asignaturas.especialidad-documentada')@endif
@if($modalDetalle)
<template x-teleport="body"><div class="personas-modal" x-data="{}" x-trap.inert.noscroll="true" role="dialog" aria-modal="true" aria-labelledby="especialidad-ficha-titulo">
    <div class="personas-modal-fondo" wire:click="cerrarDetalle"></div>
    <section class="personas-modal-panel" style="max-width:52rem">
        <header class="personas-modal-cabecera"><div><p class="ui-kicker">Ficha de especialidad técnica</p><h2 id="especialidad-ficha-titulo" class="ui-title text-xl font-bold mt-2">{{ $detalle['nom_esp']??'' }}</h2></div><button type="button" class="personas-accion" wire:click="cerrarDetalle" aria-label="Cerrar ficha de especialidad"><i class="ph-duotone ph-x" aria-hidden="true"></i></button></header>
        @php($revisionTecnica = app(\App\Support\Academico\EspecialidadTecnicaInteligente::class)->orientacion($detalle['nom_esp']??''))
        <div class="px-5 pt-5"><span class="{{ $revisionTecnica['reconocida']?'ui-badge-info':'ui-badge-warning' }}">{{ $revisionTecnica['familia'] }}</span>@if(!$revisionTecnica['reconocida'])<p class="ui-alert-warning mt-3">Este registro histórico requiere revisión de su denominación. No se admite como nueva especialidad técnica.</p>@endif</div>
        <div class="personas-modal-contenido space-y-5"><span class="{{ ($detalle['est_esp']??'')==='ACTIVO'?'ui-badge-success':'ui-badge-warning' }}">{{ ($detalle['est_esp']??'')==='ACTIVO'?'Vigente':'Retirada' }}</span><section class="ui-card-soft p-5"><h3 class="ui-title font-bold">Alcance académico</h3><p class="ui-muted text-sm mt-3">{{ $detalle['des_esp']??'Pendiente de documentación' }}</p></section><section class="ui-card-soft p-5"><h3 class="ui-title font-bold">Vinculación estudiantil</h3><p class="ui-muted text-sm mt-3">{{ $detalle['estudiantes_count']??0 }} estudiantes vinculados a esta especialidad.</p></section><p class="ui-muted text-xs">Las correcciones de la oferta requieren respaldo documental y conservan su trayectoria académica.</p></div>
        <div class="px-5 pb-5"><p class="ui-muted text-sm">{{ $detalle['planes_especialidad_count']??0 }} planes de especialidad vinculados. La autorización debe comprobarse en el respaldo institucional.</p></div>
        <footer class="personas-modal-pie"><button type="button" class="ui-btn ui-btn-secondary" wire:click="cerrarDetalle">Cerrar</button></footer>
    </section>
</div></template>
@endif
</div>
