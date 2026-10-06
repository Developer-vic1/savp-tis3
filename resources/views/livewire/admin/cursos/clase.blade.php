@if($modalClaseHorario && !$modalFormulario && !empty($claseContexto['cod_hor']))
<div x-show="claseAbierta && !formulario" x-cloak class="personas-modal docentes-modal cursos-modal cursos-modal-clase" role="dialog" aria-modal="true" aria-labelledby="clase-curso-titulo" x-trap.inert.noscroll="claseAbierta && !formulario" x-on:keydown.escape.window="if(claseAbierta) cerrarClase()">
    <div class="personas-modal-fondo" x-on:click="cerrarClase()" aria-hidden="true"></div>
    <section class="personas-modal-panel docentes-horario-panel" x-ref="clasePanel">
        <header class="personas-modal-cabecera"><div><p class="ui-kicker">Organiza un bloque libre</p><h2 id="clase-curso-titulo" class="ui-title text-xl font-bold mt-1">Materia y docente de la clase</h2><p class="ui-muted text-sm mt-2">{{ $claseContexto['curso']??'' }} · {{ $claseContexto['paralelo']??'' }} · {{ $claseContexto['dia_hor']??'' }} · {{ $claseContexto['hor_ini_hor']??'' }}–{{ $claseContexto['hor_fin_hor']??'' }}</p></div><button type="button" class="personas-accion" x-on:click="cerrarClase()" :disabled="procesando==='guardar-clase'" aria-label="Cerrar preparación de clase"><i class="ph-duotone ph-x" aria-hidden="true"></i></button></header>
        <form x-on:submit.prevent="guardarClase()" class="cursos-formulario">
        <div class="personas-modal-contenido">
        @if($modalClaseHorario)
            <p class="ui-muted text-xs">{{ $claseContexto['turno']??'' }} · {{ $claseContexto['periodo']??'' }}. Se conserva la duración del bloque registrado.</p>
            <div class="cursos-form-grilla mt-4"><section>
                <h3 class="ui-title font-bold">1. Elige la materia</h3><p class="ui-muted text-xs mt-2">Las materias planificadas conservan su docente y su carga.</p>
                @foreach(['MATERIA'=>'materias','ESPECIALIDAD'=>'especialidades'] as $tipo=>$grupo)
                @continue($tipo==='ESPECIALIDAD' && (($claseContexto['orden']??0)<4 || ($claseContexto['orden']??0)>6))
                <x-plegable-institucional class="mt-3" :etiqueta="$tipo==='MATERIA'?'Materias curriculares':'Especialidades técnicas'" :icono="$tipo==='MATERIA'?'ph-books':'ph-wrench'" :abierto="$tipo==='MATERIA'">
                    <div class="cursos-materias-clase">@foreach($opcionesClase[$grupo]??[] as $opcion)
                    @php($elegida=$formClaseHorario['tipo_plan']===$tipo && ($formClaseHorario[$tipo==='MATERIA'?'cod_mat':'cod_esp']??'')===$opcion['valor'])
                    <button type="button" class="ui-card-soft" aria-pressed="{{ $elegida?'true':'false' }}" x-on:click="elegirMateria(@js($opcion['valor']),@js($tipo))" :disabled="!!procesando"><i class="ph-duotone {{ $tipo==='MATERIA'?'ph-book-open':'ph-wrench' }}" aria-hidden="true"></i><strong>{{ $opcion['etiqueta'] }}</strong><small>{{ $opcion['planificado']?'Planificación registrada':'Requiere planificar' }}</small></button>
                    @endforeach</div>
                </x-plegable-institucional>
                @endforeach
                @php($seleccion=collect($opcionesClase[$formClaseHorario['tipo_plan']==='MATERIA'?'materias':'especialidades']??[])->firstWhere('valor',$formClaseHorario[$formClaseHorario['tipo_plan']==='MATERIA'?'cod_mat':'cod_esp']??''))
                @if($seleccion)
                    @php($compatibles=app(\App\Support\Academico\PlanificacionClaseInteligente::class)->docentesDelArea($opcionesClase['docentes']??[], $seleccion['etiqueta'], $formClaseHorario['tipo_plan']==='ESPECIALIDAD'))
                    @php($docentes=collect($verOtrosDocentesClase?($opcionesClase['docentes']??[]):$compatibles)->map(fn($d)=>['valor'=>$d['valor'],'etiqueta'=>$d['etiqueta']])->all())
                    <div class="mt-4"><x-selector-institucional modelo="formClaseHorario.cod_doc" identificador="clase-docente" etiqueta="2. Docente encargado" :requerido="true" :opciones="array_merge([['valor'=>'','etiqueta'=>'Selecciona un docente']],$docentes)" /></div>
                    <div class="cursos-docentes-filtro mt-2"><p class="ui-muted text-xs">{{ count($compatibles) }} docentes con especialidad afín a {{ $seleccion['etiqueta'] }}.</p><button type="button" class="ui-btn ui-btn-secondary" wire:click="alternarOtrosDocentesClase" wire:loading.attr="disabled"><i class="ph-duotone ph-users" aria-hidden="true"></i>{{ $verOtrosDocentesClase?'Mostrar solo docentes del área':'Ver otros docentes' }}</button></div>
                    @if($verOtrosDocentesClase)<p class="ui-muted text-xs mt-2">También se muestran otras especialidades. Una asignación fuera del área necesita un motivo.</p>@elseif(!$compatibles)<p class="ui-alert-warning text-sm mt-2">No hay docentes con esta especialidad registrada. Revisa sus perfiles o selecciona «Ver otros docentes» para una excepción justificada.</p>@endif
                    @if($analisisClase['correspondencia']??false)
                        <div class="cursos-docente-perfil ui-card-soft mt-3"><i class="ph-duotone ph-graduation-cap" aria-hidden="true"></i><div><p class="ui-muted text-xs">Especialidades del docente</p><div class="cursos-especialidad-etiquetas mt-2">@foreach(explode(' · ', $analisisClase['correspondencia']['especialidad'] ?: 'Sin especialidad registrada') as $especialidad)<span class="ui-badge-info">{{ $especialidad }}</span>@endforeach</div></div></div>
                        @if($analisisClase['requiere_motivo'])
                            <div class="ui-alert-warning mt-3"><p class="text-sm">{{ $analisisClase['correspondencia']['mensaje'] }}</p><label class="ui-label block mt-3" for="clase-motivo-especialidad">Motivo de la asignación excepcional *</label><textarea id="clase-motivo-especialidad" class="ui-textarea mt-2" wire:model.live.debounce.400ms="formClaseHorario.motivo_especialidad" rows="3" maxlength="500" placeholder="Ej.: Suplencia temporal autorizada mientras retorna el docente titular."></textarea><p class="text-xs mt-2">Explica la necesidad real en al menos cinco palabras. El motivo se conservará en la bitácora con la materia, el docente y su especialidad.</p><x-input-error for="formClaseHorario.motivo_especialidad" />@if(!$analisisClase['motivo_valido'])<p class="ui-error text-xs mt-2">Falta una explicación suficiente para documentar esta excepción.</p>@endif</div>
                        @else<p class="ui-muted text-xs mt-2">{{ $analisisClase['correspondencia']['mensaje'] }}</p>@endif
                    @endif
                    @if($analisisClase['crear_plan']??false)<div class="ui-alert-warning mt-4"><p class="text-sm">La nueva planificación quedará registrada junto con esta clase.</p><label class="ui-label block mt-3" for="clase-carga">Períodos académicos semanales *</label><input id="clase-carga" class="ui-input mt-2" type="number" min="1" max="80" step="1" wire:model.live.debounce.300ms="formClaseHorario.carga_horaria" /><label class="cursos-confirmar mt-3"><input type="checkbox" wire:model.live="formClaseHorario.confirmar_plan" /><span>La materia, el docente y la carga corresponden a la planificación aprobada del grupo.</span></label></div>@else<p class="ui-muted mt-3 text-xs">Carga registrada: {{ $analisisClase['carga']??$seleccion['horas']??0 }} períodos académicos semanales.</p>@endif
                @endif
                <div class="mt-4"><x-selector-institucional modelo="formClaseHorario.modalidad_aula" identificador="clase-modalidad-aula" etiqueta="3. Lugar de la clase" :opciones="[['valor'=>'curso','etiqueta'=>'Aula del propio curso y paralelo'],['valor'=>'otro','etiqueta'=>'Otro espacio']]" /><p class="ui-muted text-xs mt-2">Aula habitual: {{ $claseContexto['curso'] }} · {{ $claseContexto['paralelo'] }}.</p>
                    @if(($formClaseHorario['modalidad_aula']??'curso')==='otro')<label class="ui-label block mt-3" for="clase-aula">Otro aula o espacio *</label><input id="clase-aula" class="ui-input mt-2" wire:model.live.debounce.350ms="formClaseHorario.aul_hor" maxlength="100" placeholder="Ej.: Laboratorio de Física" required /><x-input-error for="formClaseHorario.aul_hor" />
                    @else<div class="cursos-aula-fija ui-card-soft mt-3"><i class="ph-duotone ph-lock-key" aria-hidden="true"></i><div><p class="ui-muted text-xs">Aula del grupo · ubicación fija</p><p class="ui-title font-bold mt-1">{{ $claseContexto['curso'] }} · {{ $claseContexto['paralelo'] }}</p></div></div><p class="ui-muted text-xs mt-2">Selecciona «Otro espacio» para habilitar una ubicación diferente.</p>@endif
                </div>
                <div class="mt-3"><label class="ui-label" for="clase-observacion">Observación</label><textarea id="clase-observacion" class="ui-textarea mt-2" wire:model="formClaseHorario.obs_hor" rows="2" maxlength="255"></textarea></div>
            </section><aside class="ui-card-soft p-5 h-fit"><h3 class="ui-title font-bold"><i class="ph-duotone ph-shield-check" aria-hidden="true"></i> Revisión de la clase</h3>
                <p wire:loading role="status" class="ui-muted text-xs mt-3">Actualizando disponibilidad…</p>
                @if($analisisClase['docente']??'')<p class="ui-title font-bold text-sm mt-3">{{ $analisisClase['docente'] }}</p><p class="ui-muted text-sm mt-1">{{ $analisisClase['materia'] }}</p>@endif
                @foreach($analisisClase['bloqueos']??[] as $mensaje)<p class="ui-alert-danger text-sm mt-3" role="alert">{{ $mensaje }}</p>@endforeach
                @foreach($analisisClase['advertencias']??[] as $mensaje)<p class="ui-alert-warning text-sm mt-3">{{ $mensaje }}</p>@endforeach
                @if($analisisClase['valido']??false)<p class="ui-alert-success text-sm mt-3">Bloque disponible. La materia y su docente cumplen la revisión.</p>@endif
                <ul class="ui-muted text-xs space-y-2 mt-4">@foreach($analisisClase['reglas']??[] as $regla)<li><i class="ph-duotone {{ ($analisisClase['valido']??false)?'ph-check-circle':'ph-shield-check' }}" aria-hidden="true"></i> {{ $regla }}</li>@endforeach</ul>
                <p class="ui-muted text-xs mt-4">Al guardar se vuelve a comprobar la disponibilidad para evitar cruces entre solicitudes simultáneas.</p>
            </aside></div>
            @foreach($errors->all() as $error)<p class="ui-error mt-2" role="alert">{{ $error }}</p>@endforeach
            <p class="ui-error mt-3" x-show="errorProceso" x-text="errorProceso" role="alert"></p>
        @endif
        </div><footer class="personas-modal-pie"><p class="ui-muted text-xs" x-show="procesando" x-text="mensajeProceso" role="status"></p><div class="cursos-acciones"><button type="button" class="ui-btn ui-btn-secondary" x-on:click="cerrarClase()" :disabled="procesando==='guardar-clase'">Cancelar</button><button type="submit" class="ui-btn ui-btn-primary" :disabled="!!procesando || !$wire.analisisClase.valido" @disabled(!($analisisClase['valido']??false)) wire:loading.attr="disabled"><i class="ph-duotone" :class="procesando==='guardar-clase'?'ph-spinner-gap cursos-giro':'ph-calendar-plus'" aria-hidden="true"></i><span x-text="procesando==='guardar-clase'?'Revisando y guardando…':'Guardar clase'"></span></button></div></footer>
        </form>
    </section>
</div>

@endif
