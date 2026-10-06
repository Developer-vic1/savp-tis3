@php
    $oferta = $this->distribucionEspecialidades;
    $tecnicas = $oferta->filter(fn($e) => $e->clasificacion['reconocida']);
    $porRevisar = $oferta->count() - $tecnicas->count();
    $familias = $oferta->groupBy(fn($e) => $e->clasificacion['familia']);
@endphp
<header class="ui-card asignaturas-cabecera especialidades-cabecera">
    <div><p class="ui-kicker">Catálogo académico · formación técnica</p><h1 class="ui-title text-2xl font-bold mt-2">Especialidades Técnicas</h1><p class="ui-muted text-sm mt-2">Organiza la oferta BTH y revisa su respaldo institucional.</p></div>
    <button type="button" wire:click="abrirCrear" wire:loading.attr="disabled" class="ui-btn ui-btn-primary"><i class="ph-duotone ph-plus" aria-hidden="true"></i>Incorporar especialidad</button>
</header>
<div class="especialidades-indicadores">
    <button type="button" wire:click="$set('tipoOferta','tecnica')"><i class="ph-duotone ph-wrench" aria-hidden="true"></i><span>Denominación técnica reconocida<small>Según el catálogo revisado</small></span><strong>{{ $tecnicas->count() }}</strong></button>
    <button type="button" wire:click="$set('tipoOferta','revisar')"><i class="ph-duotone ph-warning-circle" aria-hidden="true"></i><span>Requieren revisión<small>Conservan su historia; no validan nuevas altas</small></span><strong>{{ $porRevisar }}</strong></button>
    <button type="button" wire:click="$set('vinculacion','con')"><i class="ph-duotone ph-student" aria-hidden="true"></i><span>Vinculaciones estudiantiles<small>Registros del catálogo, sin filtro de gestión</small></span><strong>{{ $metricas['relacionados'] }}</strong></button>
</div>
<section class="ui-card p-5" aria-label="Panorama de especialidades técnicas" x-data="{grafico:'familias'}">
    <p class="ui-kicker">Panorama de la oferta</p><h2 class="ui-title font-bold mt-2">Disponibilidad y vinculación</h2>
    <div class="asignaturas-acciones mt-3" aria-label="Elegir gráfico">
        <button type="button" class="ui-btn ui-btn-secondary" :aria-pressed="grafico==='familias'" @click="grafico='familias'">Familias técnicas</button>
        <button type="button" class="ui-btn ui-btn-secondary" :aria-pressed="grafico==='vigencia'" @click="grafico='vigencia'">Vigencia</button>
        <button type="button" class="ui-btn ui-btn-secondary" :aria-pressed="grafico==='estudiantes'" @click="grafico='estudiantes'">Estudiantes por especialidad</button>
    </div>
    <div class="especialidades-panorama">
        <div class="especialidades-total">
            <div class="especialidades-anillo"><svg viewBox="0 0 120 120" role="img" aria-label="{{ $tecnicas->count() }} denominaciones técnicas reconocidas de {{ $metricas['total'] }} registros"><circle cx="60" cy="60" r="49" class="especialidades-anillo-base"/><circle cx="60" cy="60" r="49" class="especialidades-anillo-valor" pathLength="100" stroke-dasharray="{{ 100 * $tecnicas->count()/max(1,$metricas['total']) }} 100"/></svg><div><strong>{{ $metricas['total'] }}</strong><span>registros</span></div></div>
            <span>{{ $tecnicas->count() }} técnicas · {{ $porRevisar }} por revisar</span><small>Reconocimiento de denominación; no certifica autorización.</small>
        </div>
        <div class="especialidades-distribucion especialidades-grafico-familias" x-show="grafico==='familias'" x-transition.opacity>
            <p class="ui-muted text-sm">Agrupación orientativa por actividad técnica del catálogo SAVP. No reemplaza el plan aprobado.</p>
            @foreach($familias as $familia=>$grupo)
                <div class="especialidades-barra" style="--grafico-color:{{ $familia==='Revisión requerida'?'var(--ui-warning)':'var(--ui-primary)' }}"><span>{{ $familia }}</span><strong>{{ $grupo->count() }}</strong><span class="especialidades-pista"><span style="--proporcion:{{ $grupo->count()/max(1,$oferta->count()) }}"></span></span></div>
            @endforeach
        </div>
        <div class="especialidades-distribucion" x-show="grafico==='vigencia'" x-transition.opacity>
            @foreach(['activos'=>['Vigentes','var(--ui-primary)'],'inactivos'=>['Retiradas','var(--ui-warning)']] as $clave=>[$etiqueta,$color])
                <button type="button" class="especialidades-barra" wire:click="$set('estado','{{ $clave==='activos'?'ACTIVO':'INACTIVO' }}')" style="--grafico-color:{{ $color }}" aria-label="Filtrar {{ $metricas[$clave] }} especialidades {{ strtolower($etiqueta) }}">
                    <span>{{ $etiqueta }}</span><strong>{{ $metricas[$clave] }}</strong><span class="especialidades-pista"><span style="--proporcion:{{ $metricas[$clave]/max(1,$metricas['total']) }}"></span></span><small>{{ round($metricas[$clave]/max(1,$metricas['total'])*100) }}% de la oferta</small>
                </button>
            @endforeach
            <p class="ui-muted text-sm"><i class="ph-duotone ph-student" aria-hidden="true"></i> {{ $metricas['relacionados'] }} vinculaciones de estudiantes registradas.</p>
        </div>
        <div class="especialidades-distribucion especialidades-grafico-estudiantes" x-show="grafico==='estudiantes'" x-cloak x-transition.opacity>
            @php($distribucion = $this->distribucionEspecialidades)
            @php($maximo = max(1, $distribucion->max('estudiantes_count') ?? 0))
            <p class="ui-muted text-sm">Vinculaciones registradas en todo el catálogo; no representan matrícula de una gestión específica. Selecciona una barra para abrir su ficha.</p>
            @if($distribucion->isNotEmpty() && $distribucion->sum('estudiantes_count') === 0)
                <p class="ui-badge-info">Todavía no hay estudiantes vinculados. Los valores en cero reflejan los registros disponibles.</p>
            @endif
            @forelse($distribucion as $especialidad)
                <button type="button" class="especialidades-barra" wire:key="grafico-especialidad-{{ $especialidad->cod_esp }}" wire:click="abrirDetalle('{{ $especialidad->cod_esp }}')" style="--grafico-color:var(--ui-primary)" aria-label="{{ $especialidad->nom_esp }}: {{ $especialidad->estudiantes_count }} estudiantes vinculados; ver ficha">
                    <span>{{ $especialidad->nom_esp }}</span><strong>{{ $especialidad->estudiantes_count }}</strong>
                    <span class="especialidades-pista"><span style="--proporcion:{{ $especialidad->estudiantes_count / $maximo }}"></span></span>
                </button>
            @empty
                <p class="ui-muted">No hay especialidades registradas para representar.</p>
            @endforelse
        </div>
    </div>
</section>
<section class="ui-card p-4" aria-label="Filtros de especialidades">
    <div class="asignaturas-filtros"><div><label for="especialidades-buscar" class="ui-label">Buscar especialidad</label><input id="especialidades-buscar" class="ui-input" wire:model.live.debounce.350ms="search" placeholder="Nombre o descripción de la especialidad"></div><x-selector-institucional modelo="estado" identificador="especialidades-vigencia" etiqueta="Vigencia" :opciones="[['valor'=>'','etiqueta'=>'Toda la oferta'],['valor'=>'ACTIVO','etiqueta'=>'Vigentes'],['valor'=>'INACTIVO','etiqueta'=>'Retiradas']]" /></div>
    <button type="button" class="ui-btn ui-btn-secondary mt-3" wire:click="limpiarFiltros"><i class="ph-duotone ph-arrow-counter-clockwise" aria-hidden="true"></i>Limpiar filtros</button>
    <div class="asignaturas-filtros mt-4">
        <x-selector-institucional modelo="tipoOferta" identificador="especialidades-tipo" etiqueta="Revisión de la oferta" :opciones="[['valor'=>'','etiqueta'=>'Todos los registros'],['valor'=>'tecnica','etiqueta'=>'Denominación técnica reconocida'],['valor'=>'revisar','etiqueta'=>'Requieren revisión']]" />
        <x-selector-institucional modelo="vinculacion" identificador="especialidades-vinculacion" etiqueta="Vinculación estudiantil" :opciones="[['valor'=>'','etiqueta'=>'Con y sin estudiantes'],['valor'=>'con','etiqueta'=>'Con estudiantes vinculados'],['valor'=>'sin','etiqueta'=>'Sin estudiantes vinculados']]" />
    </div>
</section>
<section class="ui-card p-5" aria-label="Catálogo de especialidades" x-ref="resultados" x-data="{vista:'tarjetas'}">
    <div class="asignaturas-herramientas"><div><h2 class="ui-title font-bold">Oferta técnica institucional</h2><p class="ui-muted text-xs mt-2">Consulta su alcance o solicita una corrección documentada.</p></div><span class="ui-badge-info">{{ $registros->total() }} resultados</span></div>
    <div class="especialidades-vistas mt-4" aria-label="Tipo de vista">
        @foreach(['tarjetas'=>['Tarjetas','ph-squares-four'],'lista'=>['Lista','ph-list'],'familias'=>['Por familia','ph-tree-structure']] as $valor=>[$titulo,$icono])
            <button type="button" class="ui-btn ui-btn-secondary" @click="vista='{{ $valor }}'" :aria-pressed="vista==='{{ $valor }}'"><i class="ph-duotone {{ $icono }}" aria-hidden="true"></i>{{ $titulo }}</button>
        @endforeach
    </div>
    <div class="especialidades-catalogo mt-4" :class="{'especialidades-lista':vista==='lista'}" x-show="vista!=='familias'" x-transition.opacity>
        @forelse($registros as $registro)
            <article class="especialidades-tarjeta" wire:key="especialidad-{{ $registro->getKey() }}">
                @php($clasificacion = $oferta->firstWhere('cod_esp',$registro->getKey())?->clasificacion ?? [])
                <div class="especialidades-tarjeta-cabecera"><span class="especialidades-total-icono"><i class="ph-duotone ph-wrench" aria-hidden="true"></i></span><span class="{{ $registro->est_esp==='ACTIVO'?'ui-badge-success':'ui-badge-warning' }}">{{ $registro->est_esp==='ACTIVO'?'Vigente':'Retirada' }}</span></div>
                <h3 class="ui-title font-bold mt-3">{{ $registro->nom_esp }}</h3><p class="ui-muted text-sm mt-2">{{ $registro->des_esp ?: 'El alcance académico requiere documentación institucional.' }}</p>
                <span class="{{ ($clasificacion['reconocida']??false)?'ui-badge-info':'ui-badge-warning' }} mt-3">{{ $clasificacion['familia']??'Revisión requerida' }}</span>
                @if(!($clasificacion['reconocida']??false))<p class="ui-muted text-xs mt-2">La denominación requiere revisión. Su presencia histórica no habilita una nueva incorporación.</p>@endif
                <p class="ui-muted text-xs mt-3"><i class="ph-duotone ph-student" aria-hidden="true"></i> {{ $registro->estudiantes_count ?? 0 }} estudiantes vinculados</p>
                <div class="especialidades-tarjeta-acciones"><button type="button" class="ui-btn ui-btn-secondary" wire:click="abrirDetalle('{{ $registro->getKey() }}')">Ver ficha</button><button type="button" class="ui-btn ui-btn-secondary" wire:click="abrirEditar('{{ $registro->getKey() }}')"><i class="ph-duotone ph-pencil-simple" aria-hidden="true"></i>Corregir con respaldo</button></div>
            </article>
        @empty
            <div class="asignaturas-vacio"><i class="ph-duotone ph-magnifying-glass" aria-hidden="true"></i><strong>No hay especialidades para esta consulta</strong><p>Limpia los filtros o busca otro nombre.</p></div>
        @endforelse
    </div>
    <div class="especialidades-familias mt-4" x-show="vista==='familias'" x-cloak x-transition.opacity>
        <p class="ui-muted text-xs">Agrupación de los resultados de esta página.</p>
        @forelse($registros->getCollection()->groupBy(fn($e)=>$oferta->firstWhere('cod_esp',$e->cod_esp)?->clasificacion['familia']??'Revisión requerida') as $familia=>$grupo)
            <section class="ui-card-soft p-4"><h3 class="ui-title font-bold">{{ $familia }} <span class="ui-badge-info">{{ $grupo->count() }}</span></h3>
                @foreach($grupo as $registro)<button type="button" class="especialidades-familia-fila" wire:click="abrirDetalle('{{ $registro->cod_esp }}')"><span>{{ $registro->nom_esp }}<small>{{ $registro->est_esp==='ACTIVO'?'Vigente':'Retirada' }} · {{ $registro->estudiantes_count }} estudiantes vinculados</small></span><i class="ph-duotone ph-arrow-up-right" aria-hidden="true"></i></button>@endforeach
            </section>
        @empty<p class="ui-muted">No hay resultados para agrupar. Limpia los filtros.</p>@endforelse
    </div>
</section>
<x-paginacion-institucional :paginador="$registros" :elementos="[$registros->getUrlRange(1,$registros->lastPage())]" :cantidad="$perPage" entidad="especialidades" singular="especialidad" />
