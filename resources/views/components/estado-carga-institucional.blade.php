@props(['mensaje' => 'Cargando registros…'])
<div class="ui-aviso-carga" x-show="cargandoPagina" x-cloak role="status" aria-live="polite">
    <i class="ph-duotone ph-spinner-gap" aria-hidden="true"></i>
    <div><strong>{{ $mensaje }}</strong><p>Espera un momento; conservamos tus filtros.</p></div>
</div>
