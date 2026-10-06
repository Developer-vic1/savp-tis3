<div class="rendimiento-pagina" x-data="paginacionInstitucional()">
    <header class="ui-card rendimiento-cabecera">
        <div><p class="ui-kicker">Académico · seguimiento y orientación</p><h1 class="ui-title">Rendimiento de estudiantes</h1><p class="ui-muted">Respuestas, trayectoria académica y resultados del Aporte Ingenieril SAVP, vinculados a cada estudiante.</p></div>
        <span class="ui-badge-info"><i class="ph-duotone ph-student" aria-hidden="true"></i> Evidencia para acompañar</span>
    </header>

    <section class="rendimiento-indicadores" aria-label="Resumen de la gestión y los filtros">
        @foreach([['total','Estudiantes','ph-student','Inscripciones de la gestión seleccionada'],['evaluados','Con notas registradas','ph-exam','Notas vigentes y rectificadas'],['riesgo','Necesitan seguimiento','ph-warning','Alguna nota de 50 o menos'],['analisis','Con estudio guardado','ph-compass','Último intento de esta gestión']] as [$campo,$titulo,$icono,$ayuda])
            <article class="ui-card"><i class="ph-duotone {{ $icono }}" aria-hidden="true"></i><p>{{ $titulo }}</p><strong>{{ $campo === 'analisis' && !$puedeVerOrientacion ? 'Restringido' : (int)$resumen->$campo }}</strong><small class="ui-muted">{{ $ayuda }}</small></article>
        @endforeach
    </section>
    @if($resumen->sinteticas)<div class="ui-alert-warning" role="note"><strong>Registros de validación identificados.</strong> {{ (int)$resumen->sinteticas }} notas están marcadas como sintéticas en sus observaciones; están incluidas en esta consulta.</div>@endif

    <section class="ui-card rendimiento-filtros" aria-label="Filtros del rendimiento">
        <div class="rendimiento-filtros-principales">
            <x-selector-institucional modelo="gestion" identificador="rendimiento-gestion" etiqueta="Gestión académica" :opciones="array_merge([['valor'=>'','etiqueta'=>'Selecciona una gestión']], $years->map(fn($g)=>['valor'=>$g->cod_gea,'etiqueta'=>$g->ani_gea.' · '.$g->est_gea])->all())" />
            <x-selector-institucional modelo="curso" identificador="rendimiento-grado" etiqueta="Grado" :opciones="array_merge([['valor'=>'','etiqueta'=>'Todos los grados']],$cursos->map(fn($c)=>['valor'=>$c->cod_cur,'etiqueta'=>$c->nom_cur])->all())" />
            <x-selector-institucional modelo="periodo" identificador="rendimiento-periodo" etiqueta="Período de las notas" :opciones="array_merge([['valor'=>'','etiqueta'=>'Todos los períodos']],$periodos->map(fn($p)=>['valor'=>$p->cod_pev,'etiqueta'=>$p->nom_pev])->all())" />
            <x-selector-institucional modelo="seguimiento" identificador="rendimiento-seguimiento" etiqueta="Necesidad de seguimiento" :opciones="array_merge([['valor'=>'','etiqueta'=>'Todos los estudiantes'],['valor'=>'riesgo','etiqueta'=>'Con notas de 50 o menos'],['valor'=>'sin_notas','etiqueta'=>'Sin notas registradas'],['valor'=>'retirados','etiqueta'=>'Inscripción retirada']], $puedeVerOrientacion ? [['valor'=>'sin_analisis','etiqueta'=>'Sin estudio guardado']] : [])" />
        </div>
        <div class="rendimiento-busqueda"><label for="rendimiento-buscar"><span class="ui-label">Buscar estudiante</span><input id="rendimiento-buscar" type="search" class="ui-input" wire:model.live.debounce.350ms="search" maxlength="100" placeholder="Nombre, apellidos o código del estudiante" /><x-input-error for="search" /></label><button type="button" class="ui-btn-secondary" wire:click="limpiarFiltros" wire:loading.attr="disabled">Limpiar filtros</button></div>
        <details class="rendimiento-filtros-adicionales"><summary>Más filtros del grupo</summary><div class="rendimiento-filtros-principales">
            <x-selector-institucional modelo="nivel" identificador="rendimiento-nivel" etiqueta="Nivel educativo" :opciones="array_merge([['valor'=>'','etiqueta'=>'Todos los niveles']],$niveles->map(fn($n)=>['valor'=>$n,'etiqueta'=>$n])->all())" />
            <x-selector-institucional modelo="paralelo" identificador="rendimiento-paralelo" etiqueta="Paralelo" :opciones="array_merge([['valor'=>'','etiqueta'=>'Todos los paralelos']],$paralelos->map(fn($p)=>['valor'=>$p->cod_par,'etiqueta'=>$p->nom_par])->all())" />
            <x-selector-institucional modelo="turno" identificador="rendimiento-turno" etiqueta="Turno" :opciones="array_merge([['valor'=>'','etiqueta'=>'Todos los turnos']],$turnos->map(fn($t)=>['valor'=>$t->cod_tur,'etiqueta'=>$t->nom_tur])->all())" />
            <label for="rendimiento-codigo"><span class="ui-label">Código exacto de estudiante</span><input id="rendimiento-codigo" class="ui-input" wire:model.live.debounce.350ms="estudiante" maxlength="30" /><x-input-error for="estudiante" /></label>
        </div></details>
        <p class="ui-help">El período filtra las notas observadas; los estudios conservan la evidencia de la fecha en que se generaron. Una nota baja requiere revisión, sin determinar el cierre anual.</p>
        @if($gestion === '')<p class="ui-alert-info">Selecciona una gestión para consultar estudiantes. La gestión activa se elige al ingresar cuando está registrada.</p>@endif
    </section>

    <nav class="rendimiento-vistas" aria-label="Vista del seguimiento">
        <button type="button" class="{{ $vista==='estudiantes' ? 'ui-btn-primary' : 'ui-btn-secondary' }}" wire:click="$set('vista','estudiantes')" aria-pressed="{{ $vista==='estudiantes' ? 'true':'false' }}"><i class="ph-duotone ph-users-three" aria-hidden="true"></i> Por estudiante</button>
        <button type="button" class="{{ $vista==='grupo' ? 'ui-btn-primary' : 'ui-btn-secondary' }}" wire:click="$set('vista','grupo')" aria-pressed="{{ $vista==='grupo' ? 'true':'false' }}"><i class="ph-duotone ph-chart-bar" aria-hidden="true"></i> Análisis del grupo</button>
        <p class="ui-help" role="status">{{ (int)$resumen->total }} estudiantes en esta consulta</p>
    </nav>
    <div wire:loading.delay class="ui-alert-info" role="status">Actualizando el seguimiento…</div>

    @if($vista === 'estudiantes')
        <section class="rendimiento-estudiantes" aria-label="Rendimiento y aporte por estudiante" wire:loading.class="rendimiento-actualizando" wire:target="search,gestion,curso,periodo,seguimiento,nivel,paralelo,turno,estudiante,gotoPage,perPage">
            @forelse($estudiantes as $e)
                @php($guardado=$estudios->get($e->cod_est))
                <article class="ui-card rendimiento-estudiante" wire:key="rendimiento-{{ $e->cod_ins }}">
                    <div class="rendimiento-estudiante-cabecera"><span class="rendimiento-avatar"><i class="ph-duotone ph-student" aria-hidden="true"></i></span><div><h2>{{ trim($e->nom_per.' '.$e->ape_pat_per.' '.$e->ape_mat_per) }}</h2><p class="ui-help">{{ $e->cod_est }} · {{ $e->nom_cur }} · paralelo {{ $e->nom_par ?? 'sin referencia' }} · {{ $e->ani_gea }}</p></div></div>
                    <dl class="rendimiento-medidas"><div><dt>Promedio observado</dt><dd>{{ $e->promedio === null ? 'Sin notas' : number_format($e->promedio,2).' / 100' }}</dd></div><div><dt>Notas de 50 o menos</dt><dd>{{ (int)$e->bajas }} <small>de {{ (int)$e->notas }} notas</small></dd></div></dl>
                    <p><span class="{{ $e->bajas ? 'ui-badge-warning' : 'ui-badge-info' }}">{{ $e->bajas ? 'Requiere seguimiento académico' : ($e->notas ? 'Notas disponibles' : 'Evidencia académica pendiente') }}</span></p>
                    <div class="rendimiento-aporte"><h3>Respuestas y análisis</h3>
                        @if(!$puedeVerOrientacion)<p class="ui-help">Orientación: acceso restringido.</p>
                        @else<p class="ui-help">{{ $e->riasec ? 'RIASEC finalizado' : 'Cuestionario pendiente' }} · {{ (int)$e->avance }}% de avance</p>
                            @if(data_get($guardado,'estado') === 'contrato_valido')<p class="font-bold">{{ ['COMPLETE'=>'Análisis completo','PARTIAL'=>'Análisis parcial','INSUFFICIENT'=>'Evidencia insuficiente'][data_get($guardado,'datos.analysis_status')] }}</p><p class="ui-help">Estudio guardado el {{ $guardado['actividad']->analysis_completed_at->format('d/m/Y') }} · {{ count($guardado['datos']['career_evidence_profiles']) }} opciones documentadas</p>
                            @elseif(data_get($guardado,'estado') === 'requiere_revision')<p class="ui-help">El estudio guardado necesita revisar su formato o vinculación antes de interpretarlo.</p>
                            @else<p class="ui-help">Sin análisis guardado para el último intento de esta gestión.</p>@endif
                        @endif
                    </div>
                    <p class="ui-help">{{ $e->retenido ? 'Retenido · cierre oficial' : ($e->promovido ? 'Promovido / egresado · cierre oficial' : 'Cierre anual sin resultado vigente') }} · inscripción {{ mb_strtolower($e->est_ins) }}</p>
                    <button type="button" class="ui-btn-secondary" wire:click="verEstudiante('{{ $e->cod_ins }}')" wire:loading.attr="disabled" aria-label="Ver notas, respuestas y estudio de {{ trim($e->nom_per.' '.$e->ape_pat_per.' '.$e->ape_mat_per) }}">Ver notas, respuestas y estudio <i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></button>
                </article>
            @empty<div class="ui-card rendimiento-vacio"><i class="ph-duotone ph-magnifying-glass" aria-hidden="true"></i><h2>No hay estudiantes con estos filtros</h2><p class="ui-help">Revisa la gestión, el grado o la búsqueda. La ausencia de notas o estudios no se interpreta como un rendimiento de cero.</p><button type="button" class="ui-btn-secondary" wire:click="limpiarFiltros">Limpiar filtros del grupo</button></div>@endforelse
        </section>
        {{ $estudiantes->onEachSide(1)->links('vendor.livewire.paginacion-institucional', ['cantidad'=>$perPage,'entidad'=>'estudiantes','singular'=>'estudiante']) }}
    @else
        @include('livewire.admin.rendimiento-grupo')
    @endif

    @if($puedeVerOrientacion)
        <section class="ui-card rendimiento-conexion" aria-labelledby="rendimiento-conexion-titulo"><div><p class="ui-kicker">Aporte Ingenieril SAVP</p><h2 id="rendimiento-conexion-titulo">Conexión del análisis académico y vocacional</h2><p class="ui-help">Laravel vincula las respuestas con los hechos escolares. El aporte en Python analiza esa evidencia y documenta las relaciones con opciones de estudio.</p></div><div class="rendimiento-conexion-accion"><span role="status" class="{{ $conexion==='no_disponible' ? 'ui-badge-warning' : 'ui-badge-info' }}">{{ ['sin_comprobar'=>'Conexión sin comprobar','disponible'=>'Servicio disponible','no_disponible'=>'Servicio no disponible'][$conexion] }}</span>@if($comprobada)<p class="ui-help">Comprobado: {{ $comprobada }}</p>@endif<button type="button" class="ui-btn-secondary" wire:click="comprobarConexion" wire:loading.attr="disabled" wire:target="comprobarConexion"><span wire:loading.remove wire:target="comprobarConexion">Comprobar conexión</span><span wire:loading wire:target="comprobarConexion">Comprobando…</span></button></div><p class="ui-help rendimiento-conexion-nota">La comprobación consulta la disponibilidad del servicio. Los estudios guardados se pueden leer aunque la conexión falle. Las opciones de carrera son orientativas y no garantizan un desempeño futuro.</p></section>
    @endif
    @if($detalle) @include('livewire.admin.rendimiento-detalle') @endif
    @include('livewire.admin.rendimiento-estilos')
</div>
