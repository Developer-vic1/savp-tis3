@php $edicion=$modalEditar; @endphp
@php $modelo=$edicion?'formEditar':'form'; @endphp
@php $analisis=$edicion?$analisisEditar:$analisisCrear; @endphp
<template x-teleport="body">
<div class="curricular-modal personas-modal" x-data="{fase:1,leido:false}" x-effect="const ok=$wire.revisionDocumentoCurricular.coherente===true; if(ok&&!leido) fase=2; if(!ok) fase=1; leido=ok" role="dialog" aria-modal="true" aria-labelledby="curricular-titulo" x-trap.inert.noscroll="true">
    <section class="curricular-panel">
        <header class="personas-modal-cabecera"><div><p class="ui-kicker">Decisión curricular documentada</p><h2 id="curricular-titulo" class="ui-title text-xl font-bold mt-2">{{ $edicion?'Editar asignatura':'Incorporar asignatura' }}</h2><p class="ui-muted text-sm mt-2">Respaldo · Datos autorizados · Comprobación</p></div><button type="button" class="personas-accion" wire:click="{{ $edicion?'cerrarModalEditar':'cerrarModalCrear' }}" aria-label="Cerrar formulario de asignatura"><i class="ph-duotone ph-x" aria-hidden="true"></i></button></header>
        @include('livewire.admin.asignaturas.fases-documentadas')
        <div class="curricular-contenido">
            <div class="curricular-fase-cuerpo">
            <div x-show="fase===1" x-transition.opacity>@include('livewire.admin.asignaturas.documento')</div>
            <div x-show="fase===2" x-cloak x-transition.opacity>
                <section class="ui-card-soft p-5"><h3 class="ui-title font-bold">2. Datos autorizados</h3><p class="ui-muted text-sm mt-2">El PDF completa estos datos. Si cambia la autorización, vuelve a escanear.</p>
                <fieldset @disabled(!($revisionDocumentoCurricular['coherente']??false))>
                    <label class="ui-label block mt-4" for="curricular-nombre">Nombre de la asignatura *</label><input id="curricular-nombre" class="ui-input mt-2" wire:model.live.debounce.500ms="{{ $modelo }}.nom_asi" maxlength="150" placeholder="Ej.: Programación"><x-input-error :for="$modelo.'.nom_asi'" />
                    <div class="grid sm:grid-cols-2 gap-4 mt-4"><div><label class="ui-label" for="curricular-sigla">Sigla *</label><input id="curricular-sigla" class="ui-input mt-2" wire:model.live="{{ $modelo }}.sig_asi" maxlength="10" placeholder="Ej.: PRO"><x-input-error :for="$modelo.'.sig_asi'" /></div><div><label class="ui-label" for="curricular-horas">Períodos académicos semanales *</label><input id="curricular-horas" class="ui-input mt-2" type="number" min="1" max="80" wire:model.live="{{ $modelo }}.hor_asi"><x-input-error :for="$modelo.'.hor_asi'" /></div></div>
                </fieldset>
                @if(!empty($analisis['nombre']))<p class="ui-muted text-xs mt-3">Sigla del catálogo para {{ $analisis['nombre'] }}: <strong>{{ $analisis['sigla'] }}</strong>.</p>@if(!\App\Support\Academico\AsignaturaInteligente::siglaCompatible($this->{$modelo}['nom_asi'],$this->{$modelo}['sig_asi']))<p class="ui-error text-sm mt-2">La sigla no corresponde al nombre; corrige el respaldo antes de continuar.</p>@endif @endif
                </section>
                <section class="ui-card-soft p-5 mt-4"><p class="ui-kicker">Vista previa académica</p><h3 class="ui-title font-bold mt-2">{{ $analisis['nombre']?:'Primero, lee el documento' }}</h3><p class="ui-muted text-sm mt-3">{{ !empty($analisis['nombre'])?($analisis['mensaje']??''):'El documento completa los datos. Cárgalo y escanéalo en la primera fase.' }}</p>
                @if(!empty($analisis['nombre']))<dl class="curricular-clasificacion mt-4"><div><dt>Campo educativo</dt><dd>{{ $analisis['area']??'Pendiente de revisión' }}</dd></div><div><dt>Formación</dt><dd>{{ $analisis['nivel']??'Pendiente de revisión' }}</dd></div><div><dt>Tipo</dt><dd>{{ $analisis['tipo']??'Pendiente de revisión' }}</dd></div></dl><p class="ui-muted text-sm mt-4">{{ $analisis['descripcion']??'' }}</p>@endif
                @if(!empty($analisis['carreras_relacionadas']) && ($analisis['es_catalogo']??false))<h4 class="ui-title font-bold mt-4">Ámbitos profesionales relacionados</h4><p class="ui-muted text-xs mt-2">Relaciones orientativas del catálogo; no acreditan admisión ni equivalencia de estudios.</p><div class="flex flex-wrap gap-2 mt-3">@foreach($analisis['carreras_relacionadas'] as $carrera)<span class="ui-badge-info">{{ $carrera }}</span>@endforeach</div>@endif
                <p class="ui-muted text-xs mt-4">Clasificación del catálogo SAVP. <a class="underline" href="{{ \App\Support\Academico\DocumentoCambioCurricular::FUENTE }}" target="_blank" rel="noopener">Consultar currículo del Ministerio</a>.</p></section>
            </div>
            <div x-show="fase===3" x-cloak x-transition.opacity>@include('livewire.admin.asignaturas.comprobacion')</div>
            </div>
            @include('livewire.admin.asignaturas.resumen-proceso')
        </div>
        <footer class="personas-modal-pie"><p class="ui-muted text-xs">{{ $edicion?'Se conservan las notas y la identidad histórica.':'La oferta actual se conserva. La incorporación se programa para la siguiente gestión.' }}</p><div class="flex gap-3"><button type="button" class="ui-btn ui-btn-secondary" x-show="fase>1" x-on:click="fase--">Anterior</button><button type="button" class="ui-btn ui-btn-primary" x-show="fase<3" x-on:click="fase++" @disabled(!($revisionDocumentoCurricular['coherente']??false))>Continuar</button><button type="button" class="ui-btn ui-btn-secondary" wire:click="{{ $edicion?'cerrarModalEditar':'cerrarModalCrear' }}">Cancelar</button><button type="button" class="ui-btn ui-btn-primary" x-show="fase===3" wire:click="{{ $edicion?'guardarEdicionAsignatura':'guardarAsignatura' }}" wire:loading.attr="disabled" @disabled(!($revisionDocumentoCurricular['coherente']??false)||!$firmaSelloComprobados||mb_strlen($canalComprobacion)<15||!($analisis['valido']??false)||($analisis['duplicado']??false)||!\App\Support\Academico\AsignaturaInteligente::siglaCompatible($this->{$modelo}['nom_asi'],$this->{$modelo}['sig_asi']))>{{ $edicion?'Guardar cambio documentado':'Programar incorporación' }}</button></div></footer>
    </section>
</div>
</template>
