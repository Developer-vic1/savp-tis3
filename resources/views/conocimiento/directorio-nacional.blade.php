@extends($estudiante ? 'aula-virtual.layouts.app' : 'layouts.app')

@section('title', 'Directorio universitario nacional | SAVP-TIS3')
@section('page-title', 'Directorio universitario nacional')

@section('content')
@php
    $opcionesDepartamentos = collect($departamentos)->map(fn ($valor) => ['valor' => $valor, 'etiqueta' => $valor])->prepend(['valor' => '', 'etiqueta' => 'Todos los departamentos'])->all();
    $opcionesRegimenes = collect($regimenes)->map(fn ($etiqueta, $valor) => ['valor' => $valor, 'etiqueta' => $etiqueta])->prepend(['valor' => '', 'etiqueta' => 'Todos los regímenes'])->values()->all();
    $opcionesDuraciones = collect($duraciones)->map(fn ($valor) => ['valor' => $valor, 'etiqueta' => $valor])->prepend(['valor' => '', 'etiqueta' => 'Todas, incluida por verificar'])->all();
    $registroCompleto = collect($directorio['institutions'] ?? []);
    $totalRegistro = $registroCompleto->count();
    $webContrastadas = $registroCompleto->filter(fn ($item) => ($item['website_verification_status'] ?? '') === 'OFFICIAL_DOMAIN_VERIFIED' && ($item['ingestion_status'] ?? '') !== 'QUARANTINE_SITE_REVIEW_REQUIRED')->count();
    $webCuarentena = $registroCompleto->filter(fn ($item) => ($item['ingestion_status'] ?? '') === 'QUARANTINE_SITE_REVIEW_REQUIRED')->count();
    $webPendientes = max(0, $totalRegistro - $webContrastadas - $webCuarentena);
    $porcentajeWeb = $totalRegistro > 0 ? (int) round(100 * $webContrastadas / $totalRegistro) : 0;
    $distribucion = collect($departamentos)->map(fn ($departamento) => ['nombre' => $departamento, 'cantidad' => $registroCompleto->filter(fn ($item) => in_array($departamento, $item['departments'] ?? [], true))->count()])->sortByDesc('cantidad')->values();
    $maximoDepartamento = max(1, (int) $distribucion->max('cantidad'));
    $filtrosExtra = ($filtros['duracion'] ?? '') !== '' || ($filtros['economia'] ?? '') !== '' || ($filtros['orden'] ?? 'nombre') !== 'nombre';
    $todasInstituciones = $todasInstituciones ?? $instituciones;
    $institucionesIniciales = collect($instituciones)->take(12)->pluck('institution_id')->all();
    $datosReactivos = \App\Support\Conocimiento\DirectorioNacional::datosReactivos($todasInstituciones);
@endphp
<div class="du-espacio" x-data="directorioUniversitario({ instituciones: @js($datosReactivos), filtros: @js($filtros), regimenes: @js($regimenes), filtrosAmpliados: @js($filtrosExtra) })">
    <section class="du-hero" aria-labelledby="directorio-titulo">
        <div class="du-hero-contexto"><i class="ph-duotone ph-map-trifold" aria-hidden="true"></i><span>Exploración universitaria · Bolivia</span><span aria-hidden="true">/</span><span>Registro y evidencia documental</span></div>
        <div class="du-titulo-fila">
            <div class="du-presentacion">
                <x-avatar-documental class="du-avatar" />
                <div class="min-w-0 max-w-3xl">
                <h1 id="directorio-titulo" class="ui-title text-2xl font-black sm:text-3xl">{{ $estudiante ? 'Encuentra dónde investigar tu futuro' : 'Organiza el conocimiento de cada universidad' }}</h1>
                <p class="ui-subtitle mt-3 leading-7">Explora universidades, ubica sus sedes registradas y revisa qué carreras tienen documentación. Cada ficha te muestra lo comprobado y el siguiente dato que necesitas confirmar.</p>
                </div>
            </div>
            <a href="{{ $volverUrl }}" class="ui-btn-secondary"><i class="ph-duotone ph-arrow-left" aria-hidden="true"></i>{{ $estudiante ? 'Mi futuro académico' : 'Gestión del conocimiento' }}</a>
        </div>
        <ol class="du-hero-pasos" aria-label="Cómo explorar este directorio">
            <li><span class="du-paso">1</span>Ubica una institución</li><li><span class="du-paso">2</span>Consulta programas y sedes</li><li><span class="du-paso">3</span>Contrasta la fuente oficial</li>
        </ol>
    </section>

    @if (!$resultado->available)
        <section class="du-seccion du-vacio" role="status"><i class="ph-duotone ph-cloud-warning" aria-hidden="true"></i><h2 class="ui-title text-xl font-bold">El directorio no está disponible temporalmente</h2><p class="ui-subtitle">No pudimos consultar el servicio documental. Conservamos los filtros y tus estudios guardados. Vuelve a consultar en un momento.</p><a href="{{ $consultaUrl }}?{{ http_build_query($filtros) }}" class="ui-btn-primary"><i class="ph-duotone ph-arrow-clockwise" aria-hidden="true"></i>Volver a consultar</a></section>
    @else
        <dl class="du-cifras" aria-label="Datos del registro completo, antes de aplicar filtros">
            @foreach ([['institutions', 'Instituciones en el registro', 'ph-buildings', 'Universo nominal histórico'], ['domains_verified', 'Identidades web contrastadas', 'ph-shield-check', 'No acredita vigencia legal'], ['documented_programs', 'Programas documentados', 'ph-graduation-cap', 'Cobertura académica parcial'], ['extracted_documents', 'Documentos con texto', 'ph-file-text', 'Pendientes de revisión académica'], ['pending_urls', 'Enlaces por revisar', 'ph-hourglass-medium', 'No son carreras confirmadas']] as [$clave, $etiqueta, $icono, $ayuda])
                <div class="du-cifra"><dt><i class="ph-duotone {{ $icono }}" aria-hidden="true"></i>{{ $etiqueta }}</dt><dd>{{ data_get($directorio, 'statistics.'.$clave) }}</dd><small>{{ $ayuda }}</small></div>
            @endforeach
        </dl>
        <section class="du-seccion space-y-4" aria-labelledby="cobertura-nacional">
            <div class="du-titulo-fila"><div><h2 id="cobertura-nacional" class="du-titulo-icono"><i class="ph-duotone ph-chart-bar" aria-hidden="true"></i>Qué información tenemos para explorar</h2><p class="ui-subtitle mt-2 text-sm">Los gráficos describen el registro completo. Los filtros de abajo cambian las fichas de la consulta.</p></div><a class="ui-btn-secondary text-sm" href="#filtros-directorio"><i class="ph-duotone ph-funnel" aria-hidden="true"></i>Buscar una universidad</a></div>
            <div class="du-cobertura">
                <figure class="du-grafico" aria-labelledby="du-identidad-titulo">
                    <figcaption id="du-identidad-titulo">Identidad del sitio universitario</figcaption><p class="du-grafico-ayuda">Comprobar el dominio ayuda a encontrar la fuente correcta. No confirma autorización, matrícula ni toda la oferta actual.</p>
                    @if ($totalRegistro > 0)
                        <div class="du-anillo-fila"><div class="du-anillo" style="--du-porcentaje: {{ $porcentajeWeb }}%" aria-hidden="true"><strong>{{ $porcentajeWeb }}%</strong></div><ul class="du-leyenda flex-1"><li><span class="du-marca du-marca--web" aria-hidden="true"></span><span>Web contrastada y sin cuarentena</span><strong>{{ $webContrastadas }}</strong></li><li><span class="du-marca du-marca--revision" aria-hidden="true"></span><span>Identidad pendiente de contraste</span><strong>{{ $webPendientes }}</strong></li><li><span class="du-marca du-marca--bloqueo" aria-hidden="true"></span><span>Sitio en cuarentena</span><strong>{{ $webCuarentena }}</strong></li></ul></div><p class="du-grafico-ayuda">{{ $webContrastadas }} de {{ $totalRegistro }} instituciones del registro tienen web contrastada sin bloqueo documental.</p>
                    @else
                        <p class="ui-subtitle mt-4">El registro todavía no tiene instituciones para calcular esta cobertura.</p>
                    @endif
                    <p class="du-cobertura-nota">El registro parte de una guía ministerial de 2016. Su presencia aquí necesita revisión de vigencia actual; el tamaño del registro no es un censo de universidades vigentes.</p>
                </figure>
                <figure class="du-grafico" aria-labelledby="du-departamentos-titulo">
                    <figcaption id="du-departamentos-titulo">Presencia departamental en el registro</figcaption><p class="du-grafico-ayuda">Elige un departamento para consultar sus instituciones. Una universidad puede aparecer en varios; las barras no se suman como universidades diferentes.</p>
                    <div class="du-distribucion">
                        @forelse ($distribucion as $dato)
                            <a class="du-departamento" href="{{ $consultaUrl }}?{{ http_build_query(array_merge($filtros, ['departamento' => $dato['nombre']])) }}#instituciones-directorio" aria-label="Consultar {{ $dato['cantidad'] }} instituciones registradas en {{ $dato['nombre'] }}" :aria-current="departamento === @js($dato['nombre']) ? 'true' : null" @click.prevent="seleccionarDepartamento(@js($dato['nombre']))"><span>{{ $dato['nombre'] }}</span><span class="du-barra" aria-hidden="true"><span style="width: {{ (int) round(100 * $dato['cantidad'] / $maximoDepartamento) }}%"></span></span><strong>{{ $dato['cantidad'] }}</strong></a>
                        @empty
                            <p class="ui-subtitle mt-3 text-sm">Todavía no hay departamentos registrados para esta comparación.</p>
                        @endforelse
                    </div>
                </figure>
            </div>
            <p class="ui-subtitle text-xs">{{ $fechaRecoleccion ? 'Última recolección documental: '.$fechaRecoleccion : 'Todavía no hay una recolección documental registrada.' }} Extraer texto de una página no equivale a aprobar una carrera.</p>
            <x-plegable-institucional etiqueta="Origen del registro y límites de cobertura" descripcion="Consulta las fuentes, las diferencias de conteo y lo que todavía falta verificar." icono="ph-books" :compacto="true">
                <div class="space-y-3"><p class="ui-subtitle text-sm leading-6">{{ $directorio['coverage_note'] }}</p>@foreach ($directorio['coverage_discrepancies'] ?? [] as $diferencia)<p class="ui-subtitle text-sm">{{ $diferencia['detail'] ?? '' }}</p>@endforeach<ul class="space-y-2">@foreach ($directorio['evidence_sources'] ?? [] as $fuente)@if ($enlace = \App\Support\Conocimiento\DirectorioNacional::enlacePublico($fuente['url'] ?? null))<li><a href="{{ $enlace }}" class="ui-btn-secondary inline-flex text-sm" target="_blank" rel="noopener noreferrer">{{ $fuente['title'] ?? 'Fuente del registro' }}<span class="sr-only"> (nueva pestaña)</span><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a></li>@endif @endforeach</ul></div>
            </x-plegable-institucional>
        </section>
    @endif

    <section class="du-seccion" aria-labelledby="filtros-directorio">
        <div class="du-titulo-fila"><div><h2 id="filtros-directorio" class="du-titulo-icono"><i class="ph-duotone ph-magnifying-glass" aria-hidden="true"></i>Busca instituciones y programas</h2><p class="ui-subtitle mt-2 text-sm">Combina nombre, departamento y régimen. La duración y los costos se filtran solo cuando hay documentación. Los resultados cambian al elegir cada filtro.</p></div>@if($resultado->available)<span class="du-filtros-contador" role="status" aria-live="polite" aria-atomic="true" x-text="total + (total === 1 ? ' coincidencia' : ' coincidencias') + ' · {{ $totalRegistro }} en el registro'">{{ count($instituciones) }} {{ count($instituciones) === 1 ? 'coincidencia' : 'coincidencias' }} · {{ $totalRegistro }} en el registro</span>@endif</div>
        <form method="GET" action="{{ $consultaUrl }}" class="du-filtros" @submit.prevent>
            @if($errors->any())<div class="ui-alert-warning" role="alert" tabindex="-1"><p class="font-bold">Revisa los filtros antes de consultar</p><ul class="mt-2 text-sm">@foreach($errors->all() as $mensaje)<li>{{ $mensaje }}</li>@endforeach</ul></div>@endif
            <div class="du-filtros-principales">
                <div><label class="ui-label" for="directorio-buscar">Institución o programa</label><input id="directorio-buscar" name="buscar" type="search" maxlength="180" value="{{ $filtros['buscar'] ?? '' }}" x-model.debounce.220ms="buscar" class="ui-input mt-2 w-full" placeholder="Nombre o sigla, por ejemplo UNIFRANZ">@error('buscar')<p class="ui-subtitle mt-2 text-sm" role="alert">{{ $message }}</p>@enderror</div>
                <div><x-selector-institucional identificador="directorio-departamento" etiqueta="Departamento registrado" nombre="departamento" enlace="departamento" :opciones="$opcionesDepartamentos" :valorInicial="$filtros['departamento'] ?? ''" />@error('departamento')<p class="ui-subtitle mt-2 text-sm" role="alert">{{ $message }}</p>@enderror</div>
                <div><x-selector-institucional identificador="directorio-regimen" etiqueta="Régimen institucional" nombre="regimen" enlace="regimen" :opciones="$opcionesRegimenes" :valorInicial="$filtros['regimen'] ?? ''" />@error('regimen')<p class="ui-subtitle mt-2 text-sm" role="alert">{{ $message }}</p>@enderror</div>
            </div>
            <x-plegable-institucional etiqueta="Duración, información económica y orden" descripcion="Afina la consulta con los datos que sí estén documentados." icono="ph-sliders-horizontal" :compacto="true" :abierto="$filtrosExtra">
                <div class="du-filtros-extra">
                    <div><x-selector-institucional identificador="directorio-duracion" etiqueta="Duración documentada" nombre="duracion" enlace="duracion" :opciones="$opcionesDuraciones" :valorInicial="$filtros['duracion'] ?? ''" descripcionId="directorio-duracion-ayuda" /><p id="directorio-duracion-ayuda" class="ui-subtitle mt-2 text-xs">Al elegir una duración se muestran solamente los programas que la documentan.</p>@error('duracion')<p class="ui-subtitle mt-2 text-sm" role="alert">{{ $message }}</p>@enderror</div>
                    <div><x-selector-institucional identificador="directorio-economia" etiqueta="Información económica" nombre="economia" enlace="economia" :opciones="[['valor' => '', 'etiqueta' => 'Todas'], ['valor' => 'verificados', 'etiqueta' => 'Con montos verificados'], ['valor' => 'pendientes', 'etiqueta' => 'Costos pendientes']]" :valorInicial="$filtros['economia'] ?? ''" descripcionId="directorio-economia-ayuda" /><p id="directorio-economia-ayuda" class="ui-subtitle mt-2 text-xs">Los montos requieren fuente, sede y gestión. Un costo pendiente no equivale a cero.</p>@error('economia')<p class="ui-subtitle mt-2 text-sm" role="alert">{{ $message }}</p>@enderror</div>
                    <div><x-selector-institucional identificador="directorio-orden" etiqueta="Orden de la consulta" nombre="orden" enlace="orden" :opciones="[['valor' => 'nombre', 'etiqueta' => 'Nombre de institución'], ['valor' => 'departamento', 'etiqueta' => 'Departamento y nombre']]" :valorInicial="$filtros['orden'] ?? 'nombre'" />@error('orden')<p class="ui-subtitle mt-2 text-sm" role="alert">{{ $message }}</p>@enderror</div>
                </div>
            </x-plegable-institucional>
            <div class="du-filtros-acciones"><a class="ui-btn-secondary" href="{{ $consultaUrl }}" @click.prevent="limpiarFiltros()"><i class="ph-duotone ph-broom" aria-hidden="true"></i>Limpiar filtros</a><noscript><button class="ui-btn-primary" type="submit">Aplicar filtros</button></noscript></div>
        </form>
        <nav class="du-chips" aria-label="Filtros aplicados; cada botón quita un filtro" x-show="filtrosActivos.length > 0" x-cloak><template x-for="chip in filtrosActivos" :key="chip.campo"><button type="button" class="du-chip" :aria-label="'Quitar filtro '+chip.etiqueta+': '+chip.valor" @click="quitarFiltro(chip.campo)"><span x-text="chip.etiqueta+': '+chip.valor"></span><i class="ph-duotone ph-x" aria-hidden="true"></i></button></template></nav>
    </section>

    @if ($resultado->available)
        <section class="space-y-4" aria-labelledby="instituciones-directorio">
            <div class="du-titulo-fila"><div><h2 id="instituciones-directorio" class="du-titulo-icono du-resultados-titulo" tabindex="-1" x-ref="resultados"><i class="ph-duotone ph-buildings" aria-hidden="true"></i><span x-text="resumen">{{ count($instituciones) }} instituciones en esta consulta</span></h2><p class="ui-subtitle mt-2 text-sm">Abre una ficha para revisar programas, sedes del registro y fuentes. La vigencia legal actual sigue pendiente de contraste.</p></div><span class="ui-badge-muted" x-show="total > porPagina" x-cloak x-text="'Mostrando '+inicio+'–'+fin+' de '+total"></span></div>
            <div class="du-grid">
                @foreach ($todasInstituciones as $institucion)
                    @php
                        $cuarentena = ($institucion['ingestion_status'] ?? '') === 'QUARANTINE_SITE_REVIEW_REQUIRED';
                        $webConfirmada = ($institucion['website_verification_status'] ?? '') === 'OFFICIAL_DOMAIN_VERIFIED' && !$cuarentena;
                        $sedesRegistradas = collect($institucion['campuses'] ?? [])->filter(fn ($sede) => is_array($sede))->values();
                        $siglaInstitucion = $institucion['acronym'] ?? 'UNI';
                        $monogramaInstitucion = \Illuminate\Support\Str::length($siglaInstitucion) > 5 ? \Illuminate\Support\Str::substr($siglaInstitucion, 0, 4) : $siglaInstitucion;
                    @endphp
                    <article class="du-universidad" aria-labelledby="du-institucion-{{ $loop->index }}" x-show="paginaVisible({{ $loop->index }})" :style="{ order: posicion({{ $loop->index }}) }" @unless(in_array($institucion['institution_id'] ?? null, $institucionesIniciales, true)) style="display:none" @endunless>
                        <div class="du-identidad"><span class="du-monograma" aria-hidden="true">{{ $monogramaInstitucion }}</span><div class="min-w-0"><p class="ui-kicker text-xs">{{ $institucion['regimen_label'] }}</p><h3 id="du-institucion-{{ $loop->index }}">{{ $institucion['name'] }}</h3><p>Institución del registro nominal histórico</p></div></div>
                        <div class="du-departamentos">@forelse($institucion['departments'] ?? [] as $departamento)<span><i class="ph-duotone ph-map-pin" aria-hidden="true"></i>{{ $departamento }}</span>@empty<span>Departamento por verificar</span>@endforelse</div>
                        <div class="du-etiquetas"><span class="du-etiqueta {{ $cuarentena ? 'du-etiqueta--bloqueada' : ($webConfirmada ? 'du-etiqueta--web' : 'du-etiqueta--pendiente') }}"><i class="ph-duotone {{ $cuarentena ? 'ph-warning-octagon' : ($webConfirmada ? 'ph-shield-check' : 'ph-hourglass-medium') }}" aria-hidden="true"></i>{{ $cuarentena ? 'Sitio en cuarentena' : ($webConfirmada ? 'Identidad web contrastada' : 'Identidad web por contrastar') }}</span><span class="du-etiqueta">Vigencia legal por verificar</span></div>
                        <dl class="du-minicifras"><div><dd x-text="programasVisibles({{ $loop->index }})">{{ count(collect($institucion['programs'] ?? [])->filter(fn ($programa) => \App\Support\Conocimiento\DirectorioNacional::programaCoincide($programa, $filtros))) }}</dd><dt>Programas con ficha revisada</dt></div><div><dd>{{ data_get($institucion, 'collection.valid', 0) }}</dd><dt>Documentos con texto extraído</dt></div><div><dd>{{ data_get($institucion, 'collection.pending', 0) }}</dd><dt>Enlaces pendientes de lectura</dt></div></dl>
                        <p class="du-estado"><i class="ph-duotone ph-info" aria-hidden="true"></i><span>{{ $institucion['recoleccion_label'] }}. La revisión documental y la vigencia institucional se comprueban por separado.</span></p>
                        @if(!empty($institucion['institutional_note']))<p class="du-cobertura-nota">{{ $institucion['institutional_note'] }}</p>@endif
                        <div class="du-acciones">
                            @if (!$cuarentena && ($enlace = \App\Support\Conocimiento\DirectorioNacional::enlacePublico($institucion['official_url'] ?? null)))<a href="{{ $enlace }}" class="ui-btn-secondary" target="_blank" rel="noopener noreferrer"><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i>Portal institucional<span class="sr-only"> de {{ $institucion['name'] }} (nueva pestaña)</span></a>@elseif($cuarentena)<span class="ui-subtitle text-xs">La consulta del sitio requiere revisión del contenido ajeno detectado.</span>@else<span class="ui-subtitle text-xs">Enlace institucional por verificar</span>@endif
                            @if(!$estudiante && ($institucion['acronym'] ?? '') === 'UNIFRANZ')@can('conocimiento.revisar')<a href="{{ route('conocimiento.unifranz.index') }}" class="ui-btn-primary"><i class="ph-duotone ph-folders" aria-hidden="true"></i>Revisar carreras y mallas</a>@endcan @endif
                        </div>
                        <div class="du-ficha-detalles">
                            @if(!empty(data_get($institucion, 'published_offer.careers')))
                                <x-plegable-institucional :etiqueta="'Carreras del índice oficial · '.count(data_get($institucion, 'published_offer.careers', []))" descripcion="Nombres publicados por la universidad; verifica sede, malla y costos en cada ficha." icono="ph-list-checks" :compacto="true">
                                    <p class="ui-subtitle text-sm">Esta lista muestra la oferta del índice institucional. Los programas con documentación académica revisada aparecen en el apartado siguiente.</p>
                                    <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                                        @foreach(data_get($institucion, 'published_offer.careers', []) as $carreraPublicada)
                                            <li class="ui-card-soft flex min-w-0 items-center justify-between gap-3 rounded-xl p-3 text-sm" x-show="ofertaVisible({{ $loop->parent->index }}, @js($carreraPublicada['name']))"><span class="font-semibold">{{ $carreraPublicada['name'] }}</span>@if($enlace = \App\Support\Conocimiento\DirectorioNacional::enlacePublico($carreraPublicada['url'] ?? null))<a class="ui-btn-secondary shrink-0 text-xs" href="{{ $enlace }}" target="_blank" rel="noopener noreferrer" aria-label="Ver la publicación oficial de {{ $carreraPublicada['name'] }}"><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a>@endif</li>
                                        @endforeach
                                    </ul>
                                    @if($enlace = \App\Support\Conocimiento\DirectorioNacional::enlacePublico(data_get($institucion, 'published_offer.source_url')))<a class="ui-btn-secondary mt-3 text-sm" href="{{ $enlace }}" target="_blank" rel="noopener noreferrer">Ver índice oficial completo <i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a>@endif
                                </x-plegable-institucional>
                            @endif
                            <x-plegable-institucional etiqueta="Programas, duración y mallas" descripcion="Explora la documentación publicada para esta institución." icono="ph-graduation-cap" :compacto="true">
                                <div class="space-y-3">@foreach ($institucion['programs'] ?? [] as $programa)
                                    <section class="du-programa" x-show="programaVisible({{ $loop->parent->index }}, {{ $loop->index }})" @unless(\App\Support\Conocimiento\DirectorioNacional::programaCoincide($programa, $filtros)) style="display:none" @endunless><h4>{{ $programa['name'] }}</h4><p class="mt-1">{{ $programa['campus'] ?? 'Sede por verificar' }} · {{ $programa['city'] ?? 'Ciudad por verificar' }}</p><dl><div><dt>Grado y duración</dt><dd>{{ $programa['degree'] ?? 'Grado por verificar' }} · {{ \App\Support\Conocimiento\DirectorioNacional::duracion($programa) ?? 'Duración por verificar' }}</dd></div><div><dt>Costos y matrícula</dt><dd>Pendientes de verificación por sede y gestión</dd></div></dl><p>{{ ($programa['curriculum_status'] ?? '') === 'MATERIAS_DOCUMENTADAS' ? 'Materias iniciales documentadas; consulta la malla completa en la fuente.' : 'La malla completa requiere verificación en la fuente oficial.' }}</p>@if(!empty($programa['initial_subjects']))<p class="mt-2">{{ implode(' · ', $programa['initial_subjects']) }}</p>@endif<div class="du-acciones mt-3">@foreach($programa['sources'] ?? [] as $fuente)@if($enlace = \App\Support\Conocimiento\DirectorioNacional::enlacePublico($fuente['url'] ?? null))<a class="ui-btn-secondary" href="{{ $enlace }}" target="_blank" rel="noopener noreferrer">{{ $fuente['title'] ?? 'Fuente del programa' }}<span class="sr-only"> (nueva pestaña)</span><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a>@endif @endforeach</div></section>
                                @endforeach
                                    <div class="du-vacio" x-show="programasVisibles({{ $loop->index }}) === 0" @if(count(collect($institucion['programs'] ?? [])->filter(fn ($programa) => \App\Support\Conocimiento\DirectorioNacional::programaCoincide($programa, $filtros))) > 0) style="display:none" @endif><i class="ph-duotone ph-graduation-cap" aria-hidden="true"></i><p class="ui-subtitle text-sm" x-text="duracion ? 'No hay programas documentados con esta duración.' : 'Todavía no hay programas publicados con evidencia suficiente en este directorio. Consulta el portal oficial; la ausencia aquí no significa que la institución carezca de carreras.'">{{ ($filtros['duracion'] ?? '') !== '' ? 'No hay programas documentados con esta duración.' : 'Todavía no hay programas publicados con evidencia suficiente en este directorio. Consulta el portal oficial; la ausencia aquí no significa que la institución carezca de carreras.' }}</p></div>
                                </div>
                            </x-plegable-institucional>
                            <x-plegable-institucional etiqueta="Sedes del registro histórico" descripcion="Ubicaciones documentadas; su funcionamiento actual necesita confirmación." icono="ph-map-pin-line" :compacto="true">
                                <ul class="du-sedes">@forelse($sedesRegistradas as $sede)<li class="du-sede"><strong>{{ $sede['name'] ?? 'Sede registrada' }}</strong><p>{{ $sede['city'] ?? 'Ciudad por verificar' }} · {{ $sede['department'] ?? 'Departamento por verificar' }}</p><p>{{ isset($sede['evidence_year']) ? 'Fuente: '.$sede['evidence_year'].' · Vigencia actual por verificar' : 'Fecha de la sede por verificar' }}</p>@if($enlace = \App\Support\Conocimiento\DirectorioNacional::enlacePublico($sede['evidence_url'] ?? null))<a class="ui-btn-secondary mt-2 text-xs" href="{{ $enlace }}" target="_blank" rel="noopener noreferrer">Consultar registro de la sede<span class="sr-only"> (nueva pestaña)</span><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a>@endif</li>@empty<li class="ui-subtitle text-sm">No hay una sede con documentación registrada para esta ficha. Confírmala en la fuente institucional.</li>@endforelse</ul><p class="ui-subtitle text-xs">La presencia de una sede en la guía no demuestra que todos los programas de esta institución se oferten allí.</p>
                            </x-plegable-institucional>
                            <x-plegable-institucional etiqueta="Procedencia y limitaciones" descripcion="Revisa qué acredita cada fuente y qué permanece pendiente." icono="ph-books" :compacto="true">
                                <ul class="space-y-2 text-sm ui-subtitle">@foreach($institucion['limitations'] ?? [] as $limitacion)<li>{{ $limitacion }}</li>@endforeach</ul><ul class="mt-3 space-y-2">@foreach($institucion['evidence'] ?? [] as $evidencia)@if($enlace = \App\Support\Conocimiento\DirectorioNacional::enlacePublico($evidencia['url'] ?? null))<li><a class="ui-btn-secondary inline-flex text-xs" href="{{ $enlace }}" target="_blank" rel="noopener noreferrer">{{ $evidencia['scope'] ?? 'Fuente institucional' }}<span class="sr-only"> (nueva pestaña)</span><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a></li>@endif @endforeach</ul>
                            </x-plegable-institucional>
                        </div>
                    </article>
                @endforeach
                    <div class="du-seccion du-vacio lg:col-span-2" x-show="total === 0" @if(count($instituciones) > 0) style="display:none" @endif><i class="ph-duotone ph-magnifying-glass" aria-hidden="true"></i><h3 class="ui-title font-bold">No hay coincidencias con estos filtros</h3><p class="ui-subtitle"><span x-text="economia === 'verificados' ? 'No hay montos publicados con revisión y fuente suficientes para esta consulta. No se interpretan los costos pendientes como cero.' : 'Amplía la búsqueda o limpia los filtros.'">{{ ($filtros['economia'] ?? '') === 'verificados' ? 'No hay montos publicados con revisión y fuente suficientes para esta consulta. No se interpretan los costos pendientes como cero.' : 'Amplía la búsqueda o limpia los filtros.' }}</span> La cobertura publicada todavía es parcial; no permite concluir que no exista esa oferta universitaria.</p><a class="ui-btn-secondary" href="{{ $consultaUrl }}" @click.prevent="limpiarFiltros()">Ver todas las instituciones registradas</a></div>
            </div>
            <nav class="du-paginacion" aria-label="Páginas de instituciones" x-show="total > porPagina" x-cloak><p x-text="'Mostrando '+inicio+'–'+fin+' de '+total+' instituciones · Página '+pagina+' de '+paginas"></p><div><button type="button" class="ui-btn-secondary" :disabled="pagina === 1" @click="cambiarPagina(pagina-1)"><i class="ph-duotone ph-arrow-left" aria-hidden="true"></i>Anterior</button><button type="button" class="ui-btn-secondary" :disabled="pagina === paginas" @click="cambiarPagina(pagina+1)">Siguiente<i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></button></div></nav>
        </section>
    @endif
</div>
@endsection
