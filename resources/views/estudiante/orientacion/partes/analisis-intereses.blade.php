<section class="intereses-panel intereses-estudio" id="mi-analisis" aria-labelledby="analisis-titulo">
    <div class="intereses-cabecera"><div><p class="intereses-etiqueta">Tu estudio, tu punto de partida</p><h2 id="analisis-titulo" class="mt-2">Mi análisis</h2><p class="intereses-texto mt-2">Descubre cómo se relacionan tus intereses con las opciones que quieres estudiar. Después elige una meta y prepara tu siguiente paso.</p></div>
        <form method="POST" action="{{ route('estudiante.intereses.analizar') }}" x-data="crearEstudioIntereses()" @submit.prevent="analizar($event)" :aria-busy="enviando" class="w-full lg:w-auto">
            @csrf
            <button class="ui-btn-primary" type="submit" @disabled(!$profileReady) :disabled="enviando || {{ $profileReady ? 'false' : 'true' }}"><i class="ph-duotone ph-sparkle" aria-hidden="true"></i><span x-text="enviando ? 'Preparando tu estudio…' : '{{ $analysis ? 'Actualizar mi análisis' : 'Generar mi análisis' }}'">{{ $analysis ? 'Actualizar mi análisis' : 'Generar mi análisis' }}</span></button>
            <div class="intereses-carga" x-show="enviando" x-cloak role="status" aria-live="polite"><span class="intereses-spinner" aria-hidden="true"></span><div><h3>Tu estudio está en curso</h3><p class="intereses-texto">Estamos revisando tus respuestas y la información disponible. El resultado aparecerá cuando esté guardado.</p></div></div>
            <div class="intereses-aviso mt-3" x-ref="aviso" tabindex="-1" x-show="aviso" x-cloak role="alert"><h3 x-text="aviso?.title"></h3><p x-text="aviso?.message"></p><p x-text="aviso?.preserved"></p><p x-text="aviso?.next_step"></p></div>
        </form>
    </div>
    @if(!$profileReady)
        <div class="intereses-aviso mt-4"><h3>Esto falta para generar tu análisis</h3><ul class="mt-2 list-inside list-disc">@foreach($experiencia['bloqueos'] as $bloqueo)<li>{{ $bloqueo['label'] }}: {{ $bloqueo['label'] === 'Cuestionario RIASEC oficial' ? 'completa y guarda el cuestionario actual.' : 'la institución necesita completar o verificar este dato.' }}</li>@endforeach</ul>@if(!$storageReady)<p>Todavía no podemos guardar tu estudio. La administración necesita habilitar esta función.</p>@endif</div>
    @endif
    @if($analysis)
        <div class="intereses-estudio-confirmado">
            <div><p class="intereses-chip" role="status"><i class="ph-duotone ph-check-circle" aria-hidden="true"></i>Estudio guardado @if($activity?->analysis_completed_at) · {{ $activity->analysis_completed_at->timezone('America/La_Paz')->format('d/m/Y H:i') }} (Bolivia) @endif</p><h3 class="mt-3">Tus resultados acompañan tu elección</h3><p class="intereses-texto mt-2">Puedes explorar una carrera aunque no comparta tus áreas destacadas. Tus metas importan; el plan te ayudará a decidir qué aprender y qué reforzar.</p></div>
            <div class="intereses-estudio-perfil"><span class="intereses-etiqueta">Tus áreas destacadas</span><strong>{{ $comparacion['perfil']['codigo'] }}</strong><p>{{ collect($comparacion['perfil']['top'])->map(fn ($codigo) => \App\Support\Orientacion\ExperienciaIntereses::AREAS[$codigo]['nombre'])->join(' · ') ?: 'Hay áreas empatadas; revisa el perfil completo.' }}</p></div>
        </div>
        @if($comparacion['perfil']['estado'] === 'TIED')<p class="intereses-texto mt-3">Las seis áreas tienen el mismo puntaje. Mostramos el perfil completo porque ninguna destaca.</p>@elseif($comparacion['perfil']['empatadas_corte'] ?? false)<p class="intereses-texto mt-3">Hay áreas empatadas: incluimos las {{ count($comparacion['perfil']['top']) }} áreas destacadas para no dejar ninguna fuera.</p>@endif
        <x-plegable-institucional class="mt-4" etiqueta="Qué significa esta coincidencia" descripcion="Entiende qué podemos comparar y qué información falta." icono="ph-info" :compacto="true"><p class="intereses-texto">Contamos áreas compartidas entre tus intereses destacados y los códigos de ocupaciones relacionadas con cada carrera. Partimos de las tres áreas más altas y conservamos todas las empatadas. Una referencia puede tener una, dos o tres áreas. Este conteo no mide posibilidades de ingreso ni éxito y no equivale a un porcentaje de compatibilidad personal.</p><p class="intereses-texto mt-2">Para comparar perfiles completos con el método O*NET se necesitan seis puntajes de ambas partes. Conservamos la diferencia entre una carrera universitaria y una ocupación.</p><a href="{{ $comparacion['fuente_metodo'] }}" class="ui-btn-secondary mt-3" target="_blank" rel="noopener noreferrer">Consultar el método O*NET<span class="sr-only">(abre otra pestaña)</span><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a></x-plegable-institucional>
    @endif
</section>
@if($analysis)
    <section class="intereses-panel" aria-labelledby="carreras-titulo">
        <div class="intereses-cabecera"><div><p class="intereses-etiqueta">Primero la carrera, después dónde estudiarla</p><h2 id="carreras-titulo" class="mt-2">Explora carreras desde tus intereses</h2><p class="intereses-texto mt-2">Conoce la relación de tus áreas con cada opción. Después compara las universidades que cuentan con información disponible, sus mallas y lo que necesitas para estudiarla.</p></div><a class="ui-btn-secondary" href="{{ route('estudiante.futuro', ['explore' => 'compare']) }}"><i class="ph-duotone ph-scales" aria-hidden="true"></i>Comparar otras carreras</a></div>
        <div class="intereses-carreras">
            @forelse($carrerasExploracion as $carrera)
                @php
                    $referenciaPrincipal = $carrera['referencia_principal'];
                    $cantidadCompartida = data_get($referenciaPrincipal, 'cantidad');
                @endphp
                <article class="intereses-carrera intereses-carrera-explicada">
                    <div class="intereses-carrera-titulo"><i class="ph-duotone ph-graduation-cap" aria-hidden="true"></i><span>Carrera para explorar</span></div><h3>{{ $carrera['nombre'] }}</h3>
                    <div class="intereses-coincidencia">
                        @if($cantidadCompartida !== null)
                            <strong>{{ $cantidadCompartida }}<small> de {{ data_get($referenciaPrincipal, 'denominador_estudiante') }} áreas</small></strong><span>De tus áreas destacadas aparecen en una referencia ocupacional de esta carrera</span>
                            <div class="intereses-areas-compartidas">@foreach(data_get($referenciaPrincipal, 'compartidas', []) as $codigoCompartido)<span><b>{{ $codigoCompartido }}</b>{{ \App\Support\Orientacion\ExperienciaIntereses::AREAS[$codigoCompartido]['nombre'] }}</span>@endforeach</div>
                            @if($cantidadCompartida === 0)<p>En este resumen no hay áreas compartidas. Puedes explorar sus actividades y ver si te atraen.</p>@endif
                        @else
                            <strong>{{ $comparacion['perfil']['estado'] === 'TIED' ? 'Perfil con empate' : 'Coincidencia por revisar' }}</strong><p>{{ $carrera['nota'] }}</p>
                        @endif
                    </div>
                    @include('estudiante.orientacion.partes.mapa-intereses-carrera', ['perfilComparacion' => $comparacion['perfil'], 'referenciaIntereses' => $referenciaPrincipal])
                    <div class="intereses-ofertas" aria-label="Universidades consultadas para {{ $carrera['nombre'] }}">
                        <div class="intereses-ofertas-cabecera"><i class="ph-duotone ph-buildings" aria-hidden="true"></i><div><h4>Universidades para investigar</h4><p>Cada sede tiene su propia oferta, malla y condiciones de ingreso. Revisa sus fuentes antes de elegir.</p></div></div>
                        @if($carrera['ofertas'])
                            <ul class="intereses-ofertas-lista">
                                @foreach($carrera['ofertas'] as $oferta)
                                    @php($programaOferta = collect($carrera['programas'])->firstWhere('clave', $oferta['clave']))
                                    <li class="intereses-oferta">
                                        <div class="intereses-oferta-encabezado"><strong>{{ $oferta['universidad'] }}</strong><span>{{ $oferta['estado'] === 'DOCUMENTADA' ? 'Programa documentado en el estudio' : 'Referencia de universidad; oferta por confirmar' }}</span></div>
                                        @if($programaOferta)
                                            <x-plegable-institucional etiqueta="Ver malla y preparación de esta sede" descripcion="Materias y fuentes de este programa universitario." icono="ph-books" :compacto="true">
                                                @if($programaOferta['materias'])
                                                    <p class="intereses-texto">Materias documentadas de {{ $oferta['universidad'] }}. Otra universidad puede enseñarlas en distinto orden o tener un plan diferente.</p>
                                                    <ul class="intereses-materias mt-2">@foreach(array_slice($programaOferta['materias'], 0, 6) as $materiaPrograma)<li><i class="ph-duotone ph-book-open-text" aria-hidden="true"></i>{{ \Illuminate\Support\Str::ucfirst($materiaPrograma) }}</li>@endforeach</ul>
                                                @else
                                                    <p class="intereses-texto">Aún no contamos con materias detalladas de esta sede. Revisa su malla oficial.</p>
                                                @endif
                                                @if($programaOferta['refuerzos'])
                                                    <h5 class="intereses-oferta-subtitulo">Contenidos para prepararte</h5>
                                                    <p class="intereses-texto">El estudio propone reforzarlos a partir de este programa; consulta con tu orientador cómo incorporarlos a tu plan.</p>
                                                    <ul class="intereses-materias mt-2">@foreach(array_slice($programaOferta['refuerzos'], 0, 3) as $refuerzoPrograma)<li><i class="ph-duotone ph-brain" aria-hidden="true"></i>{{ \Illuminate\Support\Str::ucfirst(data_get($refuerzoPrograma, 'competency', 'Contenido por revisar')) }}</li>@endforeach</ul>
                                                @endif
                                                @foreach(array_slice($programaOferta['fuentes'], 0, 3) as $fuentePrograma)
                                                    <a class="intereses-oferta-fuente" href="{{ $fuentePrograma['reference'] }}" target="_blank" rel="noopener noreferrer">{{ data_get($fuentePrograma, 'title', 'Consultar fuente académica') }}<span class="sr-only"> (abre otra pestaña)</span><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a>
                                                @endforeach
                                            </x-plegable-institucional>
                                        @endif
                                        @if($oferta['fuentes_economicas'])<div class="intereses-oferta-economia"><span>Ingreso y ayudas</span>@foreach($oferta['fuentes_economicas'] as $fuenteEconomica)<a href="{{ $fuenteEconomica['url'] }}" target="_blank" rel="noopener noreferrer">{{ $fuenteEconomica['titulo'] }}<span class="sr-only"> (abre otra pestaña)</span><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a>@endforeach</div>@endif
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="intereses-texto">Todavía no contamos con una oferta universitaria verificable para esta carrera.</p>
                        @endif
                        <p class="intereses-ofertas-aviso">El costo total depende de aranceles, becas, materiales y transporte. Aún no tenemos cifras verificadas para comparar precios. Consulta las condiciones vigentes de cada sede con tu familia.</p>
                    </div>
                    <x-plegable-institucional etiqueta="Qué conecta esta opción contigo" descripcion="Mira las áreas compartidas y la referencia que las respalda." icono="ph-link" :compacto="true">
                        @if($cantidadCompartida !== null)<p class="intereses-texto">Tus {{ count($comparacion['perfil']['top']) }} áreas destacadas son <strong>{{ implode(', ', $comparacion['perfil']['top']) }}</strong>. Esta referencia tiene <strong>{{ data_get($referenciaPrincipal, 'denominador_referencia') }} área(s): {{ data_get($referenciaPrincipal, 'codigo') }}</strong>. Comparten {{ $cantidadCompartida }} área(s), sin valorar el orden de las letras.</p>@endif
                        @foreach($carrera['referencias'] as $referencia)<div class="intereses-referencia"><h4>{{ $referencia['codigo'] ?: 'Sin código verificable' }} · {{ $referencia['titulo'] }}</h4>@if(data_get($referencia, 'universidades_origen'))<p class="intereses-texto">Referencia incluida en {{ implode(', ', $referencia['universidades_origen']) }}.</p>@endif @if($referencia['hipotesis'])<p class="intereses-texto">La relación entre esta carrera y la ocupación necesita comprobación adicional.</p>@endif @foreach($referencia['fuentes'] as $fuente)<a href="{{ $fuente['reference'] }}" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary mt-2">{{ data_get($fuente, 'title', 'Consultar la referencia') }}<span class="sr-only">(abre otra pestaña)</span><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a>@endforeach</div>@endforeach
                        @if(!$carrera['referencias'])<p class="intereses-texto">Actualiza el estudio para consultar las referencias disponibles. Si todavía faltan, podrás seguir comparando materias y fuentes.</p>@endif
                        <p class="intereses-texto mt-2">Este conteo describe la referencia indicada. No es un porcentaje de compatibilidad de todas las universidades que ofrecen la carrera.</p>
                    </x-plegable-institucional>
                    <div class="intereses-acciones"><a class="ui-btn-primary" href="{{ route('estudiante.futuro', ['explore' => 'all', 'career' => $carrera['clave']]) }}">Ver universidades y mallas<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a></div>
                </article>
            @empty
                <div class="intereses-aviso lg:col-span-3"><h3>Todavía no hay carreras con información suficiente</h3><p>Tu perfil sigue guardado. Puedes consultar el catálogo y sus fuentes antes de decidir tu meta.</p></div>
            @endforelse
        </div>
        <div class="intereses-alternativa"><i class="ph-duotone ph-compass" aria-hidden="true"></i><div><h3>¿No aparece la carrera que imaginabas?</h3><p class="intereses-texto">Compara otras carreras del catálogo. Revisa qué quieres aprender y qué necesitarías preparar; tu objetivo también guía el plan.</p></div><a class="ui-btn-secondary" href="{{ route('estudiante.futuro', ['explore' => 'compare']) }}">Elegir otras opciones<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></a></div>
    </section>
    <section class="intereses-panel" aria-labelledby="siguiente-titulo"><p class="intereses-etiqueta">De la elección a una acción</p><h2 id="siguiente-titulo" class="mt-2">Tu siguiente paso tiene un propósito</h2><p class="intereses-texto mt-2">Elige lo que necesitas resolver hoy. Puedes volver a explorar antes de comprometerte con una carrera.</p>
        <div class="intereses-pasos-accion">
            <a href="{{ route('estudiante.futuro', ['explore' => 'compare']) }}"><i class="ph-duotone ph-scales" aria-hidden="true"></i><strong>Comparar para decidir</strong><span>Contrasta intereses, materias y preparación de otras opciones.</span><b>Ver comparación<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></b></a>
            <a href="{{ route('estudiante.plan') }}"><i class="ph-duotone ph-target" aria-hidden="true"></i><strong>Construir mi meta</strong><span>Elige una carrera y una acción concreta. Guarda el plan y revisa tu avance.</span><b>Abrir Mi plan<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></b></a>
            <a href="{{ route('estudiante.preparacion') }}"><i class="ph-duotone ph-brain" aria-hidden="true"></i><strong>Prepararme con apoyo</strong><span>Revisa tus pendientes y lleva el plan a tu docente u orientador para acordar apoyo.</span><b>Ver Mi preparación<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></b></a>
        </div>
    </section>
@endif
