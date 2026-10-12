<nav class="intereses-ruta" aria-label="Tu recorrido de orientación">
    @foreach([
        ['clave' => 'responder', 'titulo' => 'Responder', 'descripcion' => 'Cuestionario de intereses', 'ruta' => 'estudiante.intereses'],
        ['clave' => 'comprender', 'titulo' => 'Comprender', 'descripcion' => 'Mi perfil y sus motivos', 'ruta' => 'estudiante.analisis'],
        ['clave' => 'explorar', 'titulo' => 'Explorar', 'descripcion' => 'Carreras y universidades', 'ruta' => 'estudiante.futuro'],
        ['clave' => 'prepararme', 'titulo' => 'Prepararme', 'descripcion' => 'Una meta, un primer paso', 'ruta' => 'estudiante.plan'],
    ] as $paso)
        <a href="{{ route($paso['ruta']) }}" @if($etapa === $paso['clave']) aria-current="step" @endif><span class="intereses-ruta-numero">{{ $loop->iteration }}</span><span><strong>{{ $paso['titulo'] }}</strong><small>{{ $paso['descripcion'] }}</small></span></a>
    @endforeach
</nav>
