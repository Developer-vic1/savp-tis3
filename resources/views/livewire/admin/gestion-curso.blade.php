<div class="cursos-pagina" x-data="cursosInstitucionales()"
    x-on:success-general.window="avisar('success',$event.detail.mensaje)">
    <header class="ui-card cursos-cabecera">
        <div><p class="ui-kicker">Organización académica</p><h1 class="ui-title text-2xl font-bold mt-2">Gestión de Cursos</h1><p class="ui-muted mt-2 text-sm">Grados, paralelos y clases de nuestra comunidad educativa.</p></div>
        <div class="cursos-cabecera-acciones"><span class="ui-badge-info">{{ $gestion ? 'Gestión '.$gestion->ani_gea : 'Elige una gestión' }}</span><button type="button" class="ui-btn ui-btn-primary" x-on:click="formularioCurso('crear',null,$event.currentTarget)" :disabled="!!procesando"><i class="ph-duotone" :class="procesando==='formulario'?'ph-spinner-gap cursos-giro':'ph-plus-circle'" aria-hidden="true"></i><span x-text="procesando==='formulario'?'Preparando…':'Registrar grado'"></span></button></div>
    </header>
    <section class="ui-card cursos-panel-graficos" aria-label="Distribución y trayectoria de cursos">
        <header class="cursos-graficos-cabecera"><div class="cursos-identidad"><span class="cursos-grado"><i class="ph-duotone ph-chart-line-up" aria-hidden="true"></i></span><div><p class="ui-kicker">Nuestra comunidad, en cifras</p><h2 class="ui-title font-bold mt-1">Distribución y trayectoria</h2></div></div><div class="cursos-gestion-graficos"><x-selector-institucional modelo="gestionFiltro" identificador="cursos-gestion-graficos" etiqueta="Gestión a consultar" :opciones="$gestiones->map(fn($g)=>['valor'=>$g->cod_gea,'etiqueta'=>(string)$g->ani_gea.($g->est_gea==='ACTIVO'?' · Actual':' · '.$g->est_gea)])->all()" /></div></header>
        <div class="cursos-graficos">
            <section><h2 class="ui-title font-bold">¿Qué grados tienen más estudiantes?</h2><p class="ui-muted text-xs mt-2">Inscripciones de la gestión {{ $gestion?->ani_gea }}. Cada estudiante se cuenta una vez.</p><ol class="cursos-ranking mt-4">@foreach($resumen->sortByDesc('estudiantes') as $curso)<li><button type="button" x-on:click="consultar(@js($curso['cod_cur']),'ficha',$event.currentTarget)" :disabled="!!procesando"><span>{{ $curso['nombre'] }}</span><strong>{{ $curso['estudiantes'] }}</strong><i aria-hidden="true" style="--curso-proporcion:{{ $resumen->max('estudiantes') ? 100*$curso['estudiantes']/$resumen->max('estudiantes') : 0 }}%"></i></button></li>@endforeach</ol></section>
            <section class="cursos-trayectoria" x-data="trayectoriaCursos(@js($trayectoria),@js((string)($gestion?->ani_gea)))" wire:key="trayectoria-cursos-{{ $gestionFiltro }}"><h2 class="ui-title font-bold">¿Siempre fue así?</h2><p class="ui-muted text-xs mt-2">Inscripciones anuales, incluidos quienes después se retiraron. Compara años y grados sin volver a consultar.</p>
                <x-plegable-institucional class="mt-3" etiqueta="Personalizar trayectoria" icono="ph-sliders-horizontal" :compacto="true"><div class="cursos-filtros-principales"><x-selector-institucional enlace="elegidos" identificador="cursos-trayectoria-grados" etiqueta="Grados" :multiple="true" :opciones="$resumen->map(fn($c)=>['valor'=>$c['cod_cur'],'etiqueta'=>$c['nombre']])->all()" /><x-selector-institucional enlace="aniosElegidos" identificador="cursos-trayectoria-anios" etiqueta="Gestiones" :multiple="true" :opciones="collect($trayectoria)->unique('anio')->map(fn($d)=>['valor'=>(string)$d['anio'],'etiqueta'=>(string)$d['anio']])->values()->all()" /></div></x-plegable-institucional>
                <div class="cursos-leyenda mt-3"><template x-for="(s,i) in series" :key="s.id"><span :class="'curso-serie-'+(i%6)"><i aria-hidden="true"></i><span x-text="s.nombre"></span></span></template></div>
                <p class="ui-muted text-xs mt-3" x-show="anios.length<2||!series.length">Elige al menos un grado y dos gestiones.</p>
                <svg x-show="anios.length>=2&&series.length" class="cursos-linea mt-3" viewBox="0 0 620 225" role="img" aria-label="Evolución de estudiantes inscritos por grado">
                    @foreach(['minimo','Math.round((minimo+maximo)/2)','maximo'] as $valor)<g><line x1="38" x2="605" :y1="y({{ $valor }})" :y2="y({{ $valor }})" class="cursos-rejilla"/><text x="30" :y="y({{ $valor }})+4" text-anchor="end" x-text="{{ $valor }}"></text></g>@endforeach
                    @for($i=0;$i<$resumen->count();$i++)<g x-show="series[{{ $i }}]" class="curso-serie-{{ $i%6 }}"><polyline :points="series[{{ $i }}]?.puntos.map(p=>p.x+','+p.y).join(' ')||''" class="cursos-trazo"/>@for($p=0;$p<$gestiones->count();$p++)<g x-show="series[{{ $i }}]?.puntos[{{ $p }}]"><circle x-on:click="seleccion=series[{{ $i }}]?.puntos[{{ $p }}]?.anio" x-on:keydown.enter.prevent="seleccion=series[{{ $i }}]?.puntos[{{ $p }}]?.anio" role="button" tabindex="0" :aria-label="series[{{ $i }}]?.nombre+' en '+series[{{ $i }}]?.puntos[{{ $p }}]?.anio+': '+series[{{ $i }}]?.puntos[{{ $p }}]?.valor+' estudiantes'" :cx="series[{{ $i }}]?.puntos[{{ $p }}]?.x||0" :cy="series[{{ $i }}]?.puntos[{{ $p }}]?.y||0" r="5"/><title x-text="series[{{ $i }}]?.nombre+' · '+series[{{ $i }}]?.puntos[{{ $p }}]?.anio+': '+series[{{ $i }}]?.puntos[{{ $p }}]?.valor+' estudiantes'"></title></g>@endfor</g>@endfor
                    @foreach($gestiones as $g)<text x-show="anios.includes(@js((string)$g->ani_gea))" :x="x(anios.indexOf(@js((string)$g->ani_gea)))" y="217" text-anchor="middle">{{ $g->ani_gea }}</text>@endforeach
                </svg>
                <div class="cursos-anios mt-2"><template x-for="anio in anios" :key="anio"><button type="button" :aria-pressed="seleccion===anio" x-on:click="seleccion=anio" x-text="anio"></button></template></div>
                <div class="cursos-variaciones mt-3" role="status"><template x-for="s in series" :key="s.id"><p><strong x-text="s.nombre"></strong><span x-text="resumenSerie(s)"></span></p></template></div>
            </section>
        </div>
    </section>

    <section class="cursos-cifras ui-card" aria-label="Resumen de la gestión seleccionada">
        <div><i class="ph-duotone ph-graduation-cap" aria-hidden="true"></i><strong>{{ $resumen->count() }}</strong><span>Grados registrados</span></div>
        <div><i class="ph-duotone ph-student" aria-hidden="true"></i><strong>{{ $resumen->sum('estudiantes') }}</strong><span>Estudiantes inscritos</span></div>
        <div><i class="ph-duotone ph-users-three" aria-hidden="true"></i><strong>{{ $resumen->sum('grupos') }}</strong><span>Grupos por turno</span></div>
        <div><i class="ph-duotone ph-calendar-check" aria-hidden="true"></i><strong>{{ $resumen->where('horarios','>',0)->count() }} / {{ $resumen->count() }}</strong><span>Grados con clases registradas</span></div>
    </section>
    <section class="ui-card cursos-filtros" aria-label="Buscar y organizar cursos">
        <div class="cursos-filtros-principales">
            <div><label class="ui-label" for="cursos-buscar">Buscar grado</label><input id="cursos-buscar" class="ui-input mt-2" type="search" wire:model.live.debounce.450ms="search" placeholder="Nombre o etapa formativa" autocomplete="off" /></div>
            <x-selector-institucional modelo="ordenar" identificador="cursos-ordenar" etiqueta="Ordenar por" :opciones="[['valor'=>'grado','etiqueta'=>'Orden de grados'],['valor'=>'estudiantes_desc','etiqueta'=>'Más estudiantes'],['valor'=>'estudiantes_asc','etiqueta'=>'Menos estudiantes'],['valor'=>'nombre','etiqueta'=>'Nombre A–Z']]" />
        </div>
        <div class="cursos-herramientas mt-4"><div class="personas-tipos-vista" role="group" aria-label="Vista de cursos">
            <button type="button" x-on:click="vista='tabla'" :aria-pressed="vista==='tabla'"><i class="ph-duotone ph-table" aria-hidden="true"></i>Tabla</button>
            <button type="button" x-on:click="vista='tarjetas'" :aria-pressed="vista==='tarjetas'"><i class="ph-duotone ph-squares-four" aria-hidden="true"></i>Tarjetas</button>
            <button type="button" x-on:click="vista='horarios'" :aria-pressed="vista==='horarios'"><i class="ph-duotone ph-calendar-dots" aria-hidden="true"></i>Horarios</button>
        </div><button type="button" class="ui-btn ui-btn-secondary" wire:click="limpiarFiltros" wire:loading.attr="disabled">Limpiar filtros</button></div>
        <x-plegable-institucional class="mt-4" etiqueta="Afinar la búsqueda" descripcion="Combina paralelos, turnos y disponibilidad de clases." icono="ph-funnel" :compacto="true">
            <x-slot:contador>{{ count($filtroParalelos)+count($filtroTurnos)+($estado!==''?1:0)+($nivel!==''?1:0)+($filtroHorario!==''?1:0)+($filtroPlanAsignatura!==''?1:0)+($filtroPlanEspecialidad!==''?1:0) }} filtros</x-slot:contador>
            <div class="cursos-filtros-extra">
                <x-selector-institucional modelo="filtroParalelos" identificador="cursos-paralelos" etiqueta="Paralelos" :multiple="true" :opciones="$paralelos->map(fn($p)=>['valor'=>$p->cod_par,'etiqueta'=>$p->nom_par])->all()" />
                <x-selector-institucional modelo="filtroTurnos" identificador="cursos-turnos" etiqueta="Turnos" :multiple="true" :opciones="$turnos->map(fn($t)=>['valor'=>$t->cod_tur,'etiqueta'=>$t->nom_tur])->all()" />
                <x-selector-institucional modelo="nivel" identificador="cursos-etapa" etiqueta="Etapa formativa" :opciones="[['valor'=>'','etiqueta'=>'Todas'],['valor'=>'general','etiqueta'=>'Formación general · 1.º a 3.º'],['valor'=>'tecnica','etiqueta'=>'Especialización técnica · 4.º a 6.º']]" />
                <x-selector-institucional modelo="filtroHorario" identificador="cursos-clases" etiqueta="Clases registradas" :opciones="[['valor'=>'','etiqueta'=>'Todos'],['valor'=>'con','etiqueta'=>'Con clases en horario'],['valor'=>'sin','etiqueta'=>'Sin clases en horario']]" />
                <x-selector-institucional modelo="estado" identificador="cursos-estado" etiqueta="Catálogo" :opciones="[['valor'=>'','etiqueta'=>'Todos los grados'],['valor'=>'ACTIVO','etiqueta'=>'Habilitados'],['valor'=>'INACTIVO','etiqueta'=>'Deshabilitados']]" />
                <x-selector-institucional modelo="filtroPlanAsignatura" identificador="cursos-planes-curriculares" etiqueta="Planes de asignatura" :opciones="[['valor'=>'','etiqueta'=>'Todos'],['valor'=>'con','etiqueta'=>'Con planes asignados'],['valor'=>'sin','etiqueta'=>'Sin planes asignados']]" />
                <x-selector-institucional modelo="filtroPlanEspecialidad" identificador="cursos-planes-tecnicos" etiqueta="Planes técnicos" :opciones="[['valor'=>'','etiqueta'=>'Todos'],['valor'=>'con','etiqueta'=>'Con planes técnicos'],['valor'=>'sin','etiqueta'=>'Sin planes técnicos']]" />
            </div>
        </x-plegable-institucional>
        <p wire:loading.delay role="status" class="ui-muted text-xs mt-3"><i class="ph-duotone ph-spinner-gap cursos-giro" aria-hidden="true"></i> Actualizando la información…</p>
        <p x-show="procesando" x-cloak role="status" class="ui-muted text-xs mt-3" x-text="mensajeProceso"></p>
        <p x-show="errorProceso" x-cloak class="ui-error text-sm mt-3" role="alert" x-text="errorProceso"></p>
    </section>

    <section x-ref="resultados" class="ui-card cursos-resultados" aria-label="Cursos encontrados">
        <header class="cursos-herramientas"><div><h2 class="ui-title font-bold">{{ $cursos->total() }} {{ $cursos->total()===1?'grado encontrado':'grados encontrados' }}</h2><p class="ui-muted text-xs mt-2" x-text="vista==='horarios'?'Elige un grado para consultar sus clases, paralelos y períodos.':'Ficha, horario y carga abren información diferente.'"></p></div><span class="ui-muted text-xs">{{ $gestion?->ani_gea }}</span></header>
        @if($cursos->isEmpty())<div class="cursos-vacio"><i class="ph-duotone ph-magnifying-glass" aria-hidden="true"></i><h3 class="ui-title font-semibold">No encontramos grados con estos filtros</h3><p class="ui-muted text-sm mt-2">Prueba otro nombre o limpia los filtros.</p></div>
        @else
        <div class="cursos-tabla-wrap mt-4" x-show="vista==='tabla'"><table class="cursos-tabla"><caption class="sr-only">Cursos y organización de la gestión seleccionada</caption><thead><tr><th>Grado y comunidad</th><th>Estudiantes</th><th>Organización académica</th><th>Explorar</th></tr></thead><tbody>
        @foreach($cursos as $curso)
            <tr wire:key="curso-tabla-{{ $curso['cod_cur'] }}">
                <th scope="row"><div class="cursos-identidad"><span class="cursos-grado" aria-hidden="true">{{ $curso['orden'] }}°</span><div><strong>{{ $curso['nombre'] }}</strong><span class="cursos-etapa">{{ $curso['etapa'] }}</span></div></div><div class="cursos-grupos-chips" aria-label="Paralelos y turnos">@foreach($curso['paralelos'] as $paralelo)<span>{{ $paralelo['etiqueta'] }}</span>@endforeach<span class="cursos-turno"><i class="ph-duotone ph-clock" aria-hidden="true"></i>{{ implode(' / ',$curso['turnos']) ?: 'Sin turno' }}</span></div></th>
                <td><div class="cursos-inscritos"><i class="ph-duotone ph-student" aria-hidden="true"></i><strong>{{ $curso['estudiantes'] }}</strong></div><span>inscritos</span><div class="cursos-peso" aria-hidden="true"><i style="width:{{ $resumen->max('estudiantes') ? 100*$curso['estudiantes']/$resumen->max('estudiantes') : 0 }}%"></i></div></td>
                <td><div class="cursos-datos-academicos"><span><i class="ph-duotone ph-books" aria-hidden="true"></i><strong>{{ $curso['materias'] }}</strong> materias</span><span><i class="ph-duotone ph-wrench" aria-hidden="true"></i><strong>{{ $curso['especialidades'] }}</strong> especialidades</span></div><span class="cursos-estado-horario"><i class="ph-duotone {{ $curso['horarios']?'ph-calendar-check':'ph-calendar-blank' }}" aria-hidden="true"></i>{{ $curso['horarios']?'Horario disponible':'Horario pendiente' }}</span>@if($curso['estado']==='INACTIVO')<small class="ui-error">Grado deshabilitado</small>@endif</td>
                <td>@include('livewire.admin.cursos.acciones',['curso'=>$curso])</td>
            </tr>
        @endforeach
        </tbody></table></div>
        <div class="cursos-tarjetas mt-4" :class="{'cursos-tarjetas-tabla':vista==='tabla'}" x-show="vista!=='tabla'||movil">
            @foreach($cursos as $curso)<article class="ui-card-soft cursos-tarjeta" wire:key="curso-tarjeta-{{ $curso['cod_cur'] }}"><header><span class="cursos-grado" aria-hidden="true">{{ $curso['orden'] }}°</span><div><h3 class="ui-title font-bold">{{ $curso['nombre'] }}</h3><p class="ui-muted text-xs mt-1">{{ $curso['etapa'] }}</p></div></header><div class="cursos-tarjeta-cifras"><div><strong>{{ $curso['estudiantes'] }}</strong><span>Estudiantes</span></div><div><strong>{{ count($curso['paralelos']) }}</strong><span>Paralelos</span></div><div><strong>{{ $curso['materias'] }}</strong><span>Materias</span></div></div><p class="ui-muted text-xs">{{ collect($curso['paralelos'])->pluck('etiqueta')->join(' · ') ?: 'Sin paralelos' }} · {{ implode(' / ',$curso['turnos']) ?: 'Sin turno' }}</p><p class="ui-muted text-xs mt-2">{{ $curso['bloques'] }} bloques registrados en {{ $curso['horarios'] }} horarios del año.</p>@if($curso['estado']==='INACTIVO')<p class="ui-error text-xs mt-2">Grado deshabilitado</p>@endif<div class="mt-4" x-show="vista!=='horarios'">@include('livewire.admin.cursos.acciones',['curso'=>$curso])</div><button type="button" class="ui-btn ui-btn-primary mt-4" x-show="vista==='horarios'" x-on:click="consultar(@js($curso['cod_cur']),'horario',$event.currentTarget)" :disabled="!!procesando"><i class="ph-duotone ph-calendar-dots" aria-hidden="true"></i>Ver horario del grado</button></article>@endforeach
        </div>
        @endif
    </section>
    <x-paginacion-institucional :paginador="$cursos" :elementos="[$cursos->getUrlRange(1,$cursos->lastPage())]" :cantidad="$perPage" entidad="grados" singular="grado" />

    @include('livewire.admin.cursos.detalle')
    @include('livewire.admin.cursos.formulario')
    @include('livewire.admin.cursos.clase')
</div>
