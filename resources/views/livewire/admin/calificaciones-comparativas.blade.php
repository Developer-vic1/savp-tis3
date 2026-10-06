<section class="ui-card p-5 resultados-contexto" aria-labelledby="comparativas-titulo">
    <div><p class="ui-kicker">Panorama de la gestión</p><h2 id="comparativas-titulo" class="ui-title mt-2 font-black">Comparar para orientar el seguimiento</h2><p class="ui-help mt-2">Promedios, cobertura y cierres responden preguntas distintas. Selecciona un grado para explorar su grupo.</p></div>
    <div class="resultados-grados-acceso" role="group" aria-label="Consultar estudiantes por grado">@foreach($grados as $g)<button class="ui-btn-secondary" wire:click="verGrado('{{ $g->cod_cur }}')">{{ $g->nom_cur }} <span class="ui-muted">· {{ $g->total }}</span></button>@endforeach</div>
</section>
<div class="resultados-comparativas">
    <x-grafico-resultados identificador="comparativa-promedios" titulo="Mapa de promedios por grado" descripcion="Radar con un eje por grado y escala común de 0 a 100. Promedios individuales de quienes tienen notas; sin notas, no se dibuja el valor. Con menos de tres grados se usa una comparativa de barras." :datos="$graficos['promedios']" />
    <x-grafico-resultados identificador="comparativa-seguimiento" titulo="Distribución del seguimiento" descripcion="Torta del grupo seleccionado. Cada estudiante aparece en una categoría: notas sobre 50, alguna nota de 50 o menos, o sin notas." :datos="$graficos['seguimiento']" />
    <x-grafico-resultados identificador="comparativa-periodos" titulo="Evolución por período" descripcion="Promedio de las notas registradas. Incluye todos los períodos de la gestión; los períodos sin notas dejan un espacio en la línea." :datos="$graficos['periodos']" />
    <article class="ui-card p-5">
        <p class="ui-kicker">Leer antes de comparar</p><h2 class="ui-title mt-2 font-black">Cuánta evidencia tiene cada período</h2>
        <div class="mt-5 space-y-4">@foreach($trayectoria as $p)<div class="ui-card-soft p-4"><strong>{{ $p->nom_pev }}</strong><div class="resultados-mini mt-3"><div><span>Estudiantes evaluados</span><strong>{{ $p->evaluados }}</strong></div><div><span>Con alguna nota ≤ 50</span><strong>{{ $p->evaluados ? $p->riesgo : 'Sin datos' }}</strong></div></div></div>@endforeach</div>
        <p class="ui-help mt-4">La cobertura y las materias pueden cambiar entre períodos. La diferencia entre promedios no demuestra crecimiento individual ni efecto del aporte.</p>
    </article>
    <x-grafico-resultados class="resultados-grafico-amplio" identificador="comparativa-gestiones" titulo="Trayectoria de promoción y continuidad" descripcion="Estudiantes promovidos, egresados, retenidos y retirados a lo largo de las gestiones. Conserva filtros de grado, búsqueda, período y seguimiento. Sin promoción ni retención registrada, no se dibuja el cierre." :datos="$graficos['gestiones']" />
</div>
<p class="ui-help">Los retiros documentados no equivalen a deserción confirmada. Los cierres pueden estar incompletos y las cohortes cambian entre gestiones.</p>
