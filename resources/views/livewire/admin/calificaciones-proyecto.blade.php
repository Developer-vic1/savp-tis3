<section class="ui-card p-5 resultados-proyecto" aria-labelledby="proyecto-titulo">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div><p class="ui-kicker">El aporte en esta gestión</p><h2 id="proyecto-titulo" class="ui-title mt-2 font-black">De la evidencia al seguimiento</h2><p class="ui-help mt-2">Cada gráfico responde a los filtros seleccionados. Los estudios conservan la evidencia de su fecha de lectura.</p></div>
        <i class="ph-duotone ph-path text-3xl" style="color:var(--ui-primary)" aria-hidden="true"></i>
    </div>
    <ol class="resultados-recorrido mt-5" aria-label="Cobertura del proceso">
        @foreach([
            ['evaluados','Evidencia académica','Estudiantes con notas registradas','ph-notebook'],
            ['riasec','Intereses RIASEC','Cuestionarios finalizados','ph-compass'],
            ['analisis','Estudio del aporte','Resultados guardados del análisis','ph-intersect'],
        ] as [$clave,$titulo,$descripcion,$icono])
        <li class="ui-card-soft p-4"><div class="flex items-center justify-between"><span class="ui-kicker">{{ $loop->iteration }} · {{ $titulo }}</span><i class="ph-duotone {{ $icono }} text-xl" aria-hidden="true"></i></div><strong class="block text-3xl mt-3">{{ $clave!=='evaluados' && !$puedeVerOrientacion ? 'Restringido' : (int)$resumen->$clave }}<small class="ui-muted text-sm"> / {{ (int)$resumen->total }}</small></strong><p class="ui-help mt-2">{{ $descripcion }}</p><div class="resultados-pista mt-3"><span class="resultados-barra" style="width:{{ $clave!=='evaluados' && !$puedeVerOrientacion ? 0 : min(100,100*(int)$resumen->$clave/max(1,$resumen->total)) }}%"></span></div></li>
        @endforeach
    </ol>
    <p class="ui-help mt-4">Son coberturas independientes: tener notas o finalizar RIASEC no significa disponer de un estudio completo.</p>
</section>

<div class="resultados-graficos">
    <section class="ui-card p-5" aria-labelledby="proyecto-grados">
        <p class="ui-kicker">Comparativa del proceso</p><h2 id="proyecto-grados" class="ui-title mt-2 font-black">Cobertura por grado</h2>
        <div class="resultados-leyenda mt-3"><span><i style="background:var(--ui-primary)"></i>Notas</span>@if($puedeVerOrientacion)<span><i style="background:var(--ui-info)"></i>RIASEC</span><span><i style="background:var(--ui-violet)"></i>Estudio</span>@endif</div>
        <div class="space-y-4 mt-5">
            @forelse($grados as $g)
                <button wire:click="verGrado('{{ $g->cod_cur }}')" class="resultados-barra-boton" aria-label="Ver estudiantes de {{ $g->nom_cur }}">
                    <span class="flex justify-between gap-3 text-sm"><strong>{{ $g->nom_cur }}</strong><span class="ui-muted">{{ $g->total }} inscritos</span></span>
                    @foreach(['evaluados'=>'primary','riasec'=>'info','analisis'=>'violet'] as $campo=>$color)
                        @if($campo==='evaluados' || $puedeVerOrientacion)<span class="resultados-cobertura-fila"><span class="resultados-pista"><span class="resultados-barra" style="width:{{ min(100,100*(int)$g->$campo/max(1,$g->total)) }}%;background:var(--ui-{{ $color }})"></span></span><span>{{ (int)$g->$campo }} / {{ $g->total }}</span></span>@endif
                    @endforeach
                </button>
            @empty<p class="ui-muted">No hay inscripciones para esta consulta.</p>@endforelse
        </div>
    </section>
    <section class="ui-card p-5" aria-labelledby="proyecto-evidencia">
        <p class="ui-kicker">Qué respalda los resultados</p><h2 id="proyecto-evidencia" class="ui-title mt-2 font-black">Calidad de la evidencia</h2>
        @if(!$puedeVerOrientacion)<p class="ui-help mt-3">Tu cuenta no dispone del permiso de orientación institucional.</p>
        @elseif(!$proyecto['estudios'])<div class="resultados-vacio mt-5"><i class="ph-duotone ph-files text-3xl" aria-hidden="true"></i><strong>El siguiente paso es consolidar los estudios</strong><p class="ui-help">Todavía no hay estudios interpretables en este grupo. Asistencia, actividad, trayectoria e intereses aparecerán aquí cuando exista un resultado guardado.</p></div>
        @else
            <p class="ui-help mt-3">{{ $proyecto['estudios'] }} estudios interpretables · última lectura {{ \Carbon\Carbon::parse($proyecto['ultima_lectura'])->format('d/m/Y') }}.</p>
            <div class="space-y-4 mt-4">@foreach($proyecto['evidencias'] as $evidencia)<article><div class="flex justify-between gap-2 text-sm"><strong>{{ $evidencia['nombre'] }}</strong><span>{{ $evidencia['AVAILABLE'] }} disponibles</span></div><div class="resultados-evidencia mt-2" role="img" aria-label="{{ $evidencia['nombre'] }}: {{ $evidencia['AVAILABLE'] }} disponibles, {{ $evidencia['PARTIAL'] }} parciales, {{ $evidencia['INSUFFICIENT'] }} insuficientes y {{ $evidencia['UNAVAILABLE'] }} sin evidencia">@foreach(['AVAILABLE'=>'primary','PARTIAL'=>'info','INSUFFICIENT'=>'warning','UNAVAILABLE'=>'muted'] as $estado=>$color)<span style="width:{{ 100*$evidencia[$estado]/max(1,$proyecto['estudios']) }}%;background:var(--ui-{{ $color }})"></span>@endforeach</div><p class="ui-help mt-1">{{ $evidencia['PARTIAL'] }} parciales · {{ $evidencia['INSUFFICIENT'] }} insuficientes · {{ $evidencia['UNAVAILABLE'] }} sin evidencia</p></article>@endforeach</div>
        @endif
        @if($puedeVerOrientacion && $proyecto['no_interpretables'])<p class="ui-alert-warning mt-4">{{ $proyecto['no_interpretables'] }} resultados guardados requieren revisar su formato. No se utilizan para estas comparativas.</p>@endif
    </section>
</div>

<section class="ui-card p-5" aria-labelledby="proyecto-oportunidades">
    <div class="flex flex-wrap justify-between gap-3"><div><p class="ui-kicker">Resultados y posibilidades de estudio</p><h2 id="proyecto-oportunidades" class="ui-title mt-2 font-black">Carreras presentes en los análisis</h2></div><span class="ui-badge-info">Relaciones documentadas</span></div>
    @if($puedeVerOrientacion && $proyecto['carreras'])
        <div class="resultados-tarjetas mt-5">@foreach($proyecto['carreras'] as $carrera)<article class="ui-card-soft p-4 resultados-estudiante"><i class="ph-duotone ph-graduation-cap text-2xl" style="color:var(--ui-info)" aria-hidden="true"></i><h3 class="font-black mt-3">{{ $carrera['nombre'] }}</h3><p class="ui-help">{{ $carrera['universidad'] }}</p><strong class="block mt-3">{{ $carrera['estudios'] }} estudios la incluyen</strong><p class="ui-help">{{ $carrera['con_notas'] }} con calificaciones como evidencia.</p></article>@endforeach</div>
        <p class="ui-help mt-4">Hasta ocho carreras con mayor presencia en los estudios del grupo. Un estudiante puede tener varias opciones; estos conteos no son probabilidades de éxito ni decisiones de admisión.</p>
    @else<div class="resultados-vacio mt-5"><i class="ph-duotone ph-compass text-3xl" aria-hidden="true"></i><strong>{{ $puedeVerOrientacion ? 'Las opciones aparecerán al guardar los estudios' : 'Consulta de orientación restringida' }}</strong><p class="ui-help">La proyección debe apoyarse en intereses, preparación y fuentes de carreras. No se completa con datos supuestos.</p></div>@endif
</section>

@if($puedeVerOrientacion)
<section class="ui-card resultados-conexion p-5" aria-labelledby="conexion-titulo">
    <div><p class="ui-kicker">Motor del estudio</p><h2 id="conexion-titulo" class="ui-title mt-2 font-black">Conexión con el servicio Python</h2><p class="ui-help mt-2">El servicio cruza los intereses y la evidencia académica con sus fuentes. Este panel reúne los resultados guardados para el seguimiento institucional.</p></div>
    <div class="resultados-conexion-estado" role="status" aria-live="polite"><span class="{{ $conexionEstudio==='disponible' ? 'ui-badge-info' : ($conexionEstudio==='no_disponible' ? 'ui-badge-warning' : 'ui-badge') }}">{{ ['sin_comprobar'=>'Conexión sin comprobar','disponible'=>'Servicio disponible','no_disponible'=>'Servicio no disponible'][$conexionEstudio] ?? 'Conexión sin comprobar' }}</span>@if($conexionComprobada)<p class="ui-help">Comprobado: {{ $conexionComprobada }}</p>@endif<button wire:click="comprobarConexion" wire:loading.attr="disabled" wire:target="comprobarConexion" class="ui-btn-secondary"><i class="ph-duotone ph-plugs-connected" aria-hidden="true"></i><span wire:loading.remove wire:target="comprobarConexion">Comprobar conexión</span><span wire:loading wire:target="comprobarConexion">Comprobando…</span></button></div>
    <p class="ui-help resultados-conexion-nota">Esta comprobación consulta la disponibilidad; no genera análisis ni envía información del estudiante. Si el servicio falla, los resultados guardados siguen disponibles.</p>
</section>
@endif
