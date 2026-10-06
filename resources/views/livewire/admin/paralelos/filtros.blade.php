@php
    $gradosFiltro = collect($mapaGrupos)->unique('cod_cur');
    $turnosFiltro = collect($mapaGrupos)->unique('cod_tur');
    $cantidadFiltros = count(array_filter([$estado, $usoAcademico, $impacto, $filtroGrado, $filtroTurno, $filtroCapacidad, $filtroDocumentacion]));
@endphp
<section class="ui-card p-4" aria-label="Buscar y filtrar paralelos">
    <div class="grid items-end gap-3 sm:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_auto]">
        <div><label for="paralelos-buscar" class="ui-label">Buscar paralelo</label><input id="paralelos-buscar" type="search" wire:model.live.debounce.300ms="search" class="ui-input" placeholder="Letra o nombre del paralelo" maxlength="30"></div>
        <div><x-selector-institucional modelo="filtroGrado" identificador="paralelos-grado" etiqueta="Grado" :opciones="array_merge([['valor'=>'','etiqueta'=>'Todos los grados']], $gradosFiltro->map(fn($g)=>['valor'=>$g['cod_cur'],'etiqueta'=>$g['curso']])->values()->all())" /></div>
        <div><x-selector-institucional modelo="filtroTurno" identificador="paralelos-turno" etiqueta="Turno" :opciones="array_merge([['valor'=>'','etiqueta'=>'Todos los turnos']], $turnosFiltro->map(fn($g)=>['valor'=>$g['cod_tur'],'etiqueta'=>$g['turno']])->values()->all())" /></div>
        <button type="button" wire:click="limpiarFiltros" class="ui-btn-secondary">Limpiar filtros</button>
    </div>
    <x-plegable-institucional class="mt-4" etiqueta="Afinar la búsqueda" descripcion="Disponibilidad, capacidad, uso y respaldo registrado." icono="ph-funnel" :contador="$cantidadFiltros" :abierto="$cantidadFiltros > 0">
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <div><x-selector-institucional modelo="estado" identificador="paralelos-estado" etiqueta="Estado del catálogo" :opciones="array_merge([['valor'=>'','etiqueta'=>'Todos']], collect($estadosDisponibles)->map(fn($texto,$valor)=>['valor'=>$valor,'etiqueta'=>$texto])->values()->all())" /></div>
            <div><x-selector-institucional modelo="usoAcademico" identificador="paralelos-uso" etiqueta="Uso académico" :opciones="collect($opcionesUsoAcademico)->map(fn($texto,$valor)=>['valor'=>$valor,'etiqueta'=>$texto])->values()->all()" /></div>
            <div><x-selector-institucional modelo="impacto" identificador="paralelos-impacto" etiqueta="Consecuencia del cambio" :opciones="collect($opcionesImpacto)->map(fn($texto,$valor)=>['valor'=>$valor,'etiqueta'=>$texto])->values()->all()" /></div>
            <div><x-selector-institucional modelo="filtroCapacidad" identificador="paralelos-capacidad" etiqueta="Capacidad de los grupos" :opciones="[['valor'=>'','etiqueta'=>'Todos'],['valor'=>'excedida','etiqueta'=>'Capacidad registrada excedida'],['valor'=>'plazas','etiqueta'=>'Con plazas según el registro'],['valor'=>'vacio','etiqueta'=>'Con grupos sin miembros']]" /></div>
            <div><x-selector-institucional modelo="filtroDocumentacion" identificador="paralelos-documentos" etiqueta="Última actuación del catálogo" :opciones="[['valor'=>'','etiqueta'=>'Todas'],['valor'=>'con','etiqueta'=>'Con expediente registrado'],['valor'=>'sin','etiqueta'=>'Sin expediente registrado']]" /><p class="ui-muted mt-1 text-xs">No determina la legalidad de documentos anteriores.</p></div>
            <div><x-selector-institucional modelo="perPage" identificador="paralelos-pagina" etiqueta="Resultados por página" :opciones="collect([10,20,50])->map(fn($n)=>['valor'=>$n,'etiqueta'=>(string)$n])->all()" :numerico="true" /></div>
        </div>
        <p class="ui-muted mt-3 text-xs">Grado, turno y capacidad se evalúan sobre el mismo grupo. Los totales de cada paralelo siguen mostrando toda la gestión vigente.</p>
    </x-plegable-institucional>
</section>
