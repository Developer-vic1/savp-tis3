<section class="ui-card p-5" aria-labelledby="destacados-titulo">
    <div class="flex items-start justify-between gap-3"><div><p class="ui-kicker">Mayor promedio observado</p><h2 id="destacados-titulo" class="ui-title mt-2 font-black">Dos trayectorias para explorar</h2><p class="ui-help mt-2">Un hombre y una mujer según el género registrado, dentro de los filtros actuales. Solo se consideran estudiantes con notas.</p></div><i class="ph-duotone ph-medal text-3xl" style="color:var(--ui-primary)" aria-hidden="true"></i></div>
    <div class="resultados-destacados mt-5">
        @forelse($destacados as $destacado)
        @php($e=$destacado['registro'])
        <article class="ui-card-soft p-5 resultados-destacado">
            <div class="resultados-medalla" aria-hidden="true"><i class="ph-duotone ph-medal"></i><span>01</span></div>
            <div class="flex flex-wrap justify-between items-center gap-3"><span class="ui-badge-info">{{ $destacado['etiqueta'] }} · mayor promedio</span><strong class="text-3xl" style="color:var(--ui-primary)">{{ number_format($e->promedio,2) }}<small class="ui-muted text-sm"> / 100</small></strong></div>
            <h3 class="font-black mt-4">{{ trim($e->nom_per.' '.$e->ape_pat_per.' '.$e->ape_mat_per) }}</h3><p class="ui-help mt-1">{{ $e->nom_cur }} · {{ $e->nom_par ?? 'Sin paralelo' }} · {{ $e->notas }} notas registradas</p>
            <div class="resultados-trayectoria-destacado mt-4" wire:key="destacado-linea-{{ $e->cod_ins }}-{{ md5(json_encode($destacado['trayectoria'])) }}" x-data="graficoResultados(@js($destacado['trayectoria']))">
                <div class="resultados-lienzo-destacado" wire:ignore><canvas x-ref="canvas" role="img" aria-label="Trayectoria del promedio de {{ $e->nom_per }} en los períodos con notas de la gestión {{ $e->ani_gea }}"></canvas></div>
                <p class="ui-help mt-2">Promedios por período de esta gestión; puede cambiar la cobertura de materias.</p>
                <p x-cloak x-show="fallo" class="ui-help">Gráfico no disponible. Consulta las notas en la trayectoria.</p>
            </div>
            @if($destacado['carreras'])
                <div class="mt-4"><p class="ui-kicker">Opciones de carrera analizadas</p>@foreach($destacado['carreras'] as $carrera)<div class="mt-3"><strong>{{ $carrera['career_name'] }}</strong><p class="ui-help">{{ $carrera['university'] }}</p></div>@endforeach<p class="ui-help mt-3">Análisis guardado el {{ $destacado['fecha']->format('d/m/Y') }}. Opciones documentadas, sin orden de preferencia; no certifican una carrera ideal única.</p></div>
            @endif
            <button class="ui-btn-secondary mt-4" wire:click="verEstudiante('{{ $e->cod_ins }}')" aria-label="Ver trayectoria de {{ trim($e->nom_per.' '.$e->ape_pat_per.' '.$e->ape_mat_per) }}">Ver trayectoria</button>
        </article>
        @empty<p class="ui-muted">No hay notas y género registrado para mostrar estos casos con los filtros actuales.</p>@endforelse
    </div>
    <p class="ui-help mt-4">Si hay empate, se muestra un caso por código de inscripción. El promedio no determina la elección vocacional. Sin un análisis válido guardado, la sección de carreras permanece oculta.</p>
</section>
