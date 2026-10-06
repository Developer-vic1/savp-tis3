<div class="resultados-pagina space-y-5" x-data="paginacionInstitucional()">
    <section class="ui-card resultados-cabecera">
        <div><p class="ui-kicker">Resultados y seguimiento institucional</p><h1 class="ui-title mt-2 text-3xl font-black">Calificaciones y comparativas</h1><p class="ui-muted mt-2 text-sm">Compara grados, períodos y gestiones. Consulta la trayectoria individual cuando necesites profundizar.</p></div>
        <div class="flex flex-wrap items-center gap-3"><span class="ui-badge-info"><i class="ph-duotone ph-eye" aria-hidden="true"></i> Consulta académica</span><button class="ui-btn-secondary" wire:click="descargarReporte" wire:loading.attr="disabled"><i class="ph-duotone ph-download-simple" aria-hidden="true"></i> Exportar seguimiento</button></div>
    </section>
    @if($resumen->sinteticas)<div class="ui-alert-warning" role="note"><strong>Datos de validación identificados.</strong> {{ (int)$resumen->sinteticas }} notas de esta consulta están marcadas como sintéticas en sus observaciones. Los gráficos incluyen esos registros de prueba y no acreditan resultados oficiales de estudiantes.</div>@endif
    <section class="resultados-indicadores" aria-label="Resumen de estudiantes">
        @foreach([['total','Estudiantes inscritos','ph-student','primary'],['evaluados','Con notas registradas','ph-notebook','info'],['promedio','Promedio observado / 100','ph-chart-bar','primary'],['riesgo','Con alguna nota ≤ 50','ph-warning','danger']] as [$clave,$titulo,$icono,$color])
        <article class="ui-card p-5"><div class="flex items-center justify-between gap-2"><span class="ui-muted text-xs font-bold">{{ $titulo }}</span><i class="ph-duotone {{ $icono }} text-xl" style="color:var(--ui-{{ $color }})" aria-hidden="true"></i></div><strong class="mt-3 block text-3xl" style="color:var(--ui-{{ $color }})">{{ $clave==='promedio' ? ($resumen->promedio === null ? 'Sin datos' : number_format($resumen->promedio,2)) : (int)$resumen->$clave }}</strong></article>
        @endforeach
    </section>
    <section class="ui-card p-5" aria-label="Filtros del seguimiento">
        <div class="resultados-filtros">
            <x-selector-institucional modelo="gestionFiltro" identificador="resultados-gestion" etiqueta="Gestión" :opciones="$years->map(fn($g)=>['valor'=>$g->cod_gea,'etiqueta'=>'Gestión '.$g->ani_gea.' · '.$g->est_gea])->all()" />
            <x-selector-institucional modelo="gradoFiltro" identificador="resultados-grado" etiqueta="Grado" :opciones="array_merge([['valor'=>'','etiqueta'=>'Todos los grados']],$cursos->map(fn($c)=>['valor'=>$c->cod_cur,'etiqueta'=>$c->nom_cur])->all())" />
            <x-selector-institucional modelo="periodoFiltro" identificador="resultados-periodo" etiqueta="Período evaluado" :opciones="array_merge([['valor'=>'','etiqueta'=>'Todos los períodos']],$periodos->map(fn($p)=>['valor'=>$p->cod_pev,'etiqueta'=>$p->nom_pev])->all())" />
            <x-selector-institucional modelo="seguimiento" identificador="resultados-seguimiento" etiqueta="Prioridad de seguimiento" :opciones="[['valor'=>'','etiqueta'=>'Todos los estudiantes'],['valor'=>'riesgo','etiqueta'=>'Con notas de 50 o menos'],['valor'=>'sin_notas','etiqueta'=>'Sin notas registradas'],...($puedeVerOrientacion ? [['valor'=>'sin_analisis','etiqueta'=>'Sin estudio del aporte']] : []),['valor'=>'retirados','etiqueta'=>'Inscripción retirada']]" />
        </div>
        <div class="mt-4 flex flex-wrap items-end gap-3"><label class="flex-1 min-w-0"><span class="ui-label">Buscar estudiante · filtra todas las vistas</span><input class="ui-input mt-2" wire:model.live.debounce.350ms="search" placeholder="Ej.: apellido o nombre del estudiante" maxlength="120" /></label><button class="ui-btn-secondary" wire:click="limpiarFiltros">Limpiar filtros</button></div>
        <p class="ui-help mt-3">{{ (int)$resumen->evaluados }} estudiantes con notas de {{ (int)$resumen->total }} inscritos. Promedio de promedios individuales: {{ $resumen->promedio === null ? 'sin datos' : number_format($resumen->promedio,2).' / 100' }}. Una nota baja requiere seguimiento; no acredita reprobación anual.</p>
    </section>
    <nav class="resultados-vistas ui-card p-3" aria-label="Vistas de resultados">
        @foreach(['comparativas'=>['ph-chart-bar','Comparativas','Grados, períodos y gestiones'], 'proyecto'=>['ph-path','Proyecto y estudios','Cobertura y evidencia del aporte'], 'estudiantes'=>['ph-magnifying-glass','Consulta de estudiantes','Buscar un caso y ver su trayectoria']] as $key=>[$icono,$titulo,$descripcion])
        <button class="resultados-vista {{ $vista===$key ? 'resultados-vista-activa' : '' }}" wire:click="$set('vista','{{ $key }}')" aria-pressed="{{ $vista===$key ? 'true':'false' }}"><i class="ph-duotone {{ $icono }} text-2xl" aria-hidden="true"></i><span><strong>{{ $titulo }}</strong><small>{{ $descripcion }}</small></span></button>
        @endforeach
    </nav>
    @include('livewire.admin.calificaciones-destacados')
    @if($vista==='comparativas')
        @include('livewire.admin.calificaciones-comparativas')
    @elseif($vista==='proyecto')
        @include('livewire.admin.calificaciones-proyecto')
    @endif
    @if($puedeVerOrientacion)
    <x-plegable-institucional etiqueta="Intereses RIASEC del grupo" descripcion="Distribución de perfiles registrados; no es un ranking de aptitud" icono="ph-compass">
        <div class="resultados-periodos">@forelse($perfiles as $perfil)<article><p class="flex justify-between text-sm"><strong>{{ $perfil->perfil_predominante }}</strong><span>{{ $perfil->estudiantes }} estudiantes</span></p><div class="resultados-pista mt-2"><span class="resultados-barra" style="width:{{ 100*$perfil->estudiantes/max(1,$perfiles->sum('estudiantes')) }}%;background:var(--ui-info)"></span></div></article>@empty<p class="ui-muted">Sin perfiles RIASEC consolidados en esta consulta.</p>@endforelse</div><p class="ui-help mt-4">R: Realista · I: Investigador · A: Artístico · S: Social · E: Emprendedor · C: Convencional. Cada estudiante se cuenta una vez, con el último intento de esta gestión.</p>
    </x-plegable-institucional>
    @endif
    <section class="ui-card p-5" aria-labelledby="consulta-estudiantes-titulo">
        <div class="flex flex-wrap justify-between gap-3"><div><p class="ui-kicker">Consulta específica</p><h2 id="consulta-estudiantes-titulo" class="ui-title mt-2 font-black">Estudiantes de esta consulta</h2><p class="ui-help mt-2">Usa la búsqueda y los filtros superiores. Abre una trayectoria para revisar notas y estudios guardados.</p></div><span class="ui-badge-info">{{ (int)$resumen->total }} inscripciones</span></div>
        <div class="overflow-x-auto mt-5" tabindex="0" role="region" aria-label="Consulta de estudiantes">
            <table class="w-full text-sm resultados-tabla resultados-tabla-consulta"><caption class="sr-only">Seguimiento individual de la gestión seleccionada</caption><thead><tr><th scope="col">Estudiante</th><th scope="col">Grado / paralelo</th><th scope="col">Promedio / 100</th><th scope="col">Notas ≤ 50</th>@if($puedeVerOrientacion)<th scope="col">Estudio del aporte</th>@endif<th scope="col">Cierre oficial</th><th scope="col">Trayectoria</th></tr></thead><tbody>
            @forelse($estudiantes as $e)<tr wire:key="resultado-{{ $e->cod_ins }}"><td><strong>{{ trim($e->nom_per.' '.$e->ape_pat_per.' '.$e->ape_mat_per) }}</strong><p class="ui-help">{{ $e->cod_est }} · {{ $e->est_ins }}</p></td><td>{{ $e->nom_cur }}<p class="ui-help">{{ $e->nom_par ?? 'Sin paralelo' }}</p></td><td>{{ $e->promedio===null ? 'Sin notas' : number_format($e->promedio,2) }}<p class="ui-help">{{ $e->notas }} notas</p></td><td><span class="{{ $e->bajas ? 'ui-badge-warning' : 'ui-badge' }}">{{ $e->notas ? $e->bajas : 'Sin notas' }}</span></td>@if($puedeVerOrientacion)<td>{{ $e->analisis ? 'Guardado' : ($e->riasec ? 'RIASEC completo · estudio pendiente' : 'RIASEC pendiente') }}</td>@endif<td>{{ $e->retenido ? 'Retenido' : ($e->promovido ? 'Promovido / egresado' : 'Sin cierre registrado') }}</td><td><button class="ui-btn-secondary" wire:click="verEstudiante('{{ $e->cod_ins }}')" aria-label="Ver trayectoria de {{ trim($e->nom_per.' '.$e->ape_pat_per.' '.$e->ape_mat_per) }}">Ver trayectoria</button></td></tr>@empty<tr><td colspan="{{ $puedeVerOrientacion ? 7 : 6 }}" class="py-8">No hay estudiantes con estos filtros. Ajusta la gestión o limpia la búsqueda.</td></tr>@endforelse
            </tbody></table>
        </div>
        <div class="mt-5">{{ $estudiantes->onEachSide(1)->links('vendor.livewire.paginacion-institucional', ['cantidad'=>$perPage,'entidad'=>'estudiantes','singular'=>'estudiante']) }}</div>
    </section>
    <div wire:loading.delay class="ui-alert-info" role="status">Actualizando resultados…</div>
    @if($detalle)
        @include('livewire.admin.calificaciones-trayectoria')
    @endif
</div>
