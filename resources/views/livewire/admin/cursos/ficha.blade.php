@php
    $planesFicha = collect($asignaciones);
    $planesPorGrupo = $planesFicha->groupBy('cod_gac');
    $contextosFicha = collect($cursoDetalle['contextos'])->sortBy(fn ($g) => $g['nom_par'].' '.$g['nom_tur']);
@endphp

<div class="cursos-ficha">
    <section class="cursos-ficha-identidad" aria-label="Identidad y comunidad del grado">
        <div class="cursos-identidad">
            <span class="cursos-ficha-emblema" aria-hidden="true">{{ $cursoDetalle['orden'] }}<sup>°</sup></span>
            <div>
                <p class="ui-kicker">{{ $cursoDetalle['etapa'] }}</p>
                <h3 class="ui-title text-xl font-bold mt-2">{{ $cursoDetalle['nombre'] }}</h3>
                <div class="cursos-ficha-etiquetas mt-3">
                    <span class="{{ $cursoDetalle['estado']==='ACTIVO' ? 'ui-badge-info' : 'ui-badge-warning' }}">{{ $cursoDetalle['estado']==='ACTIVO' ? 'Grado habilitado' : 'Grado deshabilitado' }}</span>
                    <span class="ui-muted text-xs">{{ $cursoDetalle['nivel'] }} · Gestión {{ $gestion?->ani_gea }}</span>
                </div>
                @if($cursoDetalle['descripcion'] ?? '')<p class="ui-muted text-sm mt-3">{{ $cursoDetalle['descripcion'] }}</p>@endif
            </div>
        </div>
        <div class="cursos-ficha-comunidad"><i class="ph-duotone ph-student" aria-hidden="true"></i><strong>{{ $cursoDetalle['estudiantes'] }}</strong><span>estudiantes inscritos</span><small>Cada estudiante se cuenta una vez.</small></div>
    </section>

    <dl class="cursos-ficha-indicadores">
        @foreach([
            ['ph-users-three',$cursoDetalle['grupos'],'Grupos por turno'],
            ['ph-books',$cursoDetalle['materias'],'Materias curriculares'],
            ['ph-wrench',$cursoDetalle['especialidades'],'Especialidades técnicas'],
            ['ph-clock',count($cursoDetalle['turnos']),'Turnos del grado'],
        ] as [$icono,$cantidad,$etiqueta])
            <div><dt><i class="ph-duotone {{ $icono }}" aria-hidden="true"></i>{{ $etiqueta }}</dt><dd>{{ $cantidad }}</dd></div>
        @endforeach
    </dl>

    <section class="cursos-ficha-organizacion" aria-labelledby="curso-organizacion-titulo">
        <header class="cursos-herramientas"><div><h3 class="ui-title font-bold" id="curso-organizacion-titulo">Organización por paralelo y turno</h3><p class="ui-muted text-xs mt-2">Planes activos de los grupos habilitados en esta gestión.</p></div><span class="ui-badge-info">{{ count($cursoDetalle['paralelos']) }} paralelos · {{ count($cursoDetalle['turnos']) }} turnos</span></header>
        <div class="cursos-ficha-grupos mt-4">
            @forelse($contextosFicha as $grupo)
                @php
                    $planesGrupo = $planesPorGrupo->get($grupo['cod_gac'], collect());
                    $materiasGrupo = $planesGrupo->where('tipo','Materia curricular')->unique('nombre')->count();
                    $tecnicasGrupo = $planesGrupo->where('tipo','Especialidad técnica')->unique('nombre')->count();
                @endphp
                <article class="cursos-ficha-grupo">
                    <header><span class="cursos-grado" aria-hidden="true">{{ $grupo['nom_par'] }}</span><div><h4 class="ui-title font-bold">Paralelo {{ $grupo['nom_par'] }}</h4><p class="ui-muted text-xs mt-1"><i class="ph-duotone ph-clock" aria-hidden="true"></i> {{ $grupo['nom_tur'] }}</p></div></header>
                    <dl><div><dt>Materias curriculares</dt><dd>{{ $materiasGrupo }}</dd></div><div><dt>Especialidades técnicas</dt><dd>{{ $tecnicasGrupo }}</dd></div></dl>
                    @if($planesGrupo->isEmpty())<p class="ui-muted text-xs mt-3">Sin planes activos registrados.</p>@endif
                </article>
            @empty
                <p class="ui-muted text-sm">Este grado aún no tiene grupos habilitados en la gestión consultada.</p>
            @endforelse
        </div>
        <p class="ui-muted text-xs mt-3">Un mismo paralelo puede tener grupos en distintos turnos para la formación curricular y técnica.</p>
    </section>

    <div>
        <section class="cursos-ficha-bloque" aria-labelledby="curso-areas-titulo">
            <h3 class="ui-title font-bold" id="curso-areas-titulo">Áreas de aprendizaje</h3>
            <p class="ui-muted text-xs mt-2">Asignaturas y especialidades con planes activos en la gestión.</p>
            @forelse($planesFicha->groupBy('tipo') as $tipo=>$planesTipo)
                <h4 class="ui-muted text-xs font-semibold mt-4">{{ $tipo==='Materia curricular' ? 'Formación curricular' : 'Formación técnica' }}</h4>
                <ul class="cursos-ficha-areas mt-2">@foreach($planesTipo->unique('nombre') as $plan)<li><i class="ph-duotone {{ $tipo==='Materia curricular' ? 'ph-book-open' : 'ph-wrench' }}" aria-hidden="true"></i>{{ $plan['nombre'] }}</li>@endforeach</ul>
            @empty
                <p class="ui-muted text-sm mt-4">No hay materias ni especialidades asignadas en esta gestión.</p>
            @endforelse
        </section>
    </div>
</div>
