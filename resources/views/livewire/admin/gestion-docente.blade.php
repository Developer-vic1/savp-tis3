<div class="docentes-pagina" x-data="docentesInstitucionalPage($wire.entangle('vistaActiva').live)">
    <header class="ui-card docentes-cabecera">
        <div class="docentes-identidad"><span class="docentes-emblema"><i class="ph-duotone ph-chalkboard-teacher" aria-hidden="true"></i></span><div><p class="ui-kicker">Comunidad docente · Gestión {{ $nombreGestion }}</p><h1 class="ui-title text-2xl font-extrabold mt-2">Docentes</h1><p class="ui-muted text-sm mt-2">Conoce quién enseña cada materia, cómo se distribuyen sus horas y dónde desarrolla sus clases.</p></div></div>
        <div class="personas-acciones"><button type="button" class="ui-btn ui-btn-secondary" x-on:click="indicadores=!indicadores" :aria-expanded="indicadores"><i class="ph-duotone ph-chart-pie-slice" aria-hidden="true"></i>Indicadores</button>@can('Personal_Institucional')<a class="ui-btn ui-btn-primary" href="{{ route('admin.personal-institucional') }}"><i class="ph-duotone ph-user-gear" aria-hidden="true"></i>Gestionar personal</a>@endcan</div>
    </header>
    <section class="ui-card docentes-cifras" aria-label="Resumen académico de la gestión">@foreach([['chalkboard-teacher',$resumen['total'],'Docentes registrados'],['books',$resumen['con_carga'],'Con carga asignada'],['clock',$resumen['horas'],'Horas académicas'],['users-three',$resumen['grupos'],'Cursos y paralelos atendidos']] as [$icono,$valor,$titulo])<div><i class="ph-duotone ph-{{ $icono }}" aria-hidden="true"></i><strong>{{ $valor }}</strong><span>{{ $titulo }}</span></div>@endforeach</section>
    @include('livewire.admin.docentes.indicadores')
    @include('livewire.admin.docentes.filtros')
    <section class="docentes-resultados" x-ref="resultados" aria-label="Directorio docente"><x-estado-carga-institucional objetivo="search,gestion,materia,curso,carga,acceso,perfil,orden,perPage,gotoPage,limpiarFiltros" mensaje="Actualizando docentes…" /><div class="docentes-resultados-titulo"><div><h2 class="ui-title font-bold">{{ $materia ?: 'Directorio docente' }}</h2><p class="ui-muted text-xs mt-1" role="status" aria-live="polite">{{ $docentes->total() }} {{ $docentes->total()===1?'docente coincide':'docentes coinciden' }} · Gestión {{ $nombreGestion }}</p></div><span class="ui-muted text-xs">{{ $curso }}</span></div>
    @include('livewire.admin.docentes.resultados')
    </section>
    {{ $docentes->onEachSide(1)->links('vendor.livewire.paginacion-institucional',['cantidad'=>$perPage,'entidad'=>'docentes','singular'=>'docente']) }}
    <p wire:loading wire:target="abrirFicha" class="ui-muted text-sm" role="status">Cargando información docente…</p>
    @if($ficha)
        @if($seccion==='horario') @include('livewire.admin.docentes.horario')
        @elseif($seccion==='asignaciones') @include('livewire.admin.docentes.carga')
        @else @include('livewire.admin.docentes.ficha') @endif
    @endif
    @if($modalEditar && $docenteDetalle)@include('livewire.admin.personal.editar-perfil')@endif
</div>
