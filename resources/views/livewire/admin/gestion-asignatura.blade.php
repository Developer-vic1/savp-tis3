<div class="space-y-5 asignaturas-pagina" x-data="asignaturasInstitucionales()"
    x-on:asignatura-creada.window="avisar('success',$event.detail.mensaje)"
    x-on:asignatura-actualizada.window="avisar('success',$event.detail.mensaje)"
    x-on:asignatura-desactivada.window="avisar('success',$event.detail.mensaje)"
    x-on:asignatura-reactivada.window="avisar('success',$event.detail.mensaje)"
    x-on:advertencia-general.window="avisar('warning',$event.detail.mensaje)"
    x-on:error-general.window="avisar('error',$event.detail.mensaje)">
    @include('livewire.admin.asignaturas.panel')
    @include('livewire.admin.asignaturas.incorporaciones')
    <p x-show="procesando && !detalle" x-cloak class="ui-muted text-sm" role="status"><i class="ph-duotone ph-spinner-gap cursos-giro" aria-hidden="true"></i> <span x-text="mensajeProceso"></span></p>
    <p x-show="errorProceso && !detalle" x-cloak class="ui-error" role="alert" x-text="errorProceso"></p>
    @if($modalCrear || $modalEditar)
        @include('livewire.admin.asignaturas.formulario-documentado')
    @endif

    @include('livewire.admin.asignaturas.detalle')
    @include('livewire.admin.asignaturas.catalogo')
    @include('livewire.admin.asignaturas.cambio')
</div>
