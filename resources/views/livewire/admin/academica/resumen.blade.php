<div class="ga-centro">
    @if($gestionSeleccionada)
    <article class="ui-card"><header><div><p class="ui-kicker">El año, de un vistazo</p><h2 class="ui-title mt-1">Distribución de los trimestres</h2><p class="ui-muted text-sm mt-2">Cada color acompaña al mismo trimestre en la línea anual y en el calendario.</p></div><button type="button" class="ui-btn ui-btn-secondary" wire:click="cambiarVista('periodos')"><i class="ph-duotone ph-calendar-check" aria-hidden="true"></i>Organizar trimestres</button></header>
        <x-distribucion-trimestres :periodos="$periodos" :anio="$gestionSeleccionada['anio']" />
    </article>
    <div class="pa-dashboard">
        <article class="ui-card"><header><div><p class="ui-kicker">Agenda conectada</p><h2 class="ui-title mt-1">Calendario académico</h2></div><button type="button" class="ui-btn ui-btn-secondary" wire:click="cambiarVista('calendario')" aria-label="Abrir calendario e impacto"><i class="ph-duotone ph-arrow-up-right" aria-hidden="true"></i></button></header>
            <x-calendario-academico :anio="$gestionSeleccionada['anio']" :periodos="$periodos" :eventos="$panelAcademico['eventos']" />
        </article>
        <article class="ui-card"><header><div><p class="ui-kicker">Todo pertenece al mismo expediente</p><h2 class="ui-title mt-1">Conexiones de la gestión</h2><p class="ui-muted text-sm mt-2">Selecciona un hexágono para consultar sus registros.</p></div></header>
            <x-recorrido-academico :nodos="[
                ['nombre'=>'Inscripciones','valor'=>$gestionSeleccionada['estudiantes'],'detalle'=>'Registradas','icono'=>'ph-student','vista'=>'inscripciones','tono'=>'primary'],
                ['nombre'=>'Planes','valor'=>$gestionSeleccionada['planes_asignatura'],'detalle'=>'De asignatura','icono'=>'ph-books','vista'=>'estructura','tono'=>'info'],
                ['nombre'=>'Trimestres','valor'=>count($periodos),'detalle'=>'De evaluación','icono'=>'ph-calendar-check','vista'=>'periodos','tono'=>'violet'],
                ['nombre'=>'Calendario','valor'=>count($panelAcademico['eventos']),'detalle'=>'Eventos registrados','icono'=>'ph-calendar-dots','vista'=>'calendario','tono'=>'info'],
                ['nombre'=>'Trayectorias','valor'=>count($panelAcademico['movimientos']),'detalle'=>'Movimientos registrados','icono'=>'ph-path','vista'=>'seguimiento','tono'=>'primary'],
                ['nombre'=>'Alertas','valor'=>count($panelAcademico['alertas'])+count($avisosSesion),'detalle'=>'Puntos por revisar','icono'=>'ph-warning-circle','vista'=>'alertas','tono'=>'warning'],
            ]" />
            <div class="ga-progreso"><div><span class="ui-muted text-xs">Recorrido temporal</span><strong class="ui-title">{{ $gestionSeleccionada['progreso'] }}%</strong></div><div class="ga-progreso-pista" role="progressbar" aria-label="Progreso temporal de la gestión" aria-valuenow="{{ $gestionSeleccionada['progreso'] }}" aria-valuemin="0" aria-valuemax="100"><span style="width:{{ $gestionSeleccionada['progreso'] }}%"></span></div><div><time class="ui-muted text-xs">{{ $formatDate($gestionSeleccionada['fecha_inicio']) }}</time><time class="ui-muted text-xs">{{ $formatDate($gestionSeleccionada['fecha_fin']) }}</time></div><p class="ui-muted text-xs mt-3">Avance por fechas. La revisión de cierre confirma las condiciones académicas.</p></div>
            <button type="button" class="ui-btn ui-btn-secondary mt-4" wire:click="abrirDetalle('{{ $gestionSeleccionada['id'] }}')">Consultar expediente <i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></button>
        </article>
    </div>
    <article class="ui-card"><header><div><p class="ui-kicker">Evolución entre gestiones</p><h2 class="ui-title mt-1">Comparar antes de planificar</h2><p class="ui-muted text-sm mt-2">Cambia de indicador y consulta la variación de cada año.</p></div><button type="button" class="ui-btn ui-btn-secondary" wire:click="cambiarVista('comparativas')">Ver distribución y trayectoria <i class="ph-duotone ph-arrow-right" aria-hidden="true"></i></button></header><x-comparativa-academica :datos="$panelAcademico['comparativas']" :anio="$gestionSeleccionada['anio']" /></article>
    @else
    <article class="ui-card ga-vacio"><i class="ph-duotone ph-calendar-plus" aria-hidden="true"></i><h2 class="ui-title">Selecciona una gestión para abrir su expediente</h2><p>El calendario, sus conexiones y las comparativas aparecerán con los datos del año elegido.</p><button type="button" class="ui-btn ui-btn-primary" wire:click="abrirNuevaGestion">Preparar nueva gestión</button></article>
    @endif
</div>
