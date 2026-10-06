@props(['modelo','accion','identificador','aceptado'=>false,'revision'=>[]])
<section class="ui-lector-documento" aria-label="Lectura del respaldo PDF">
    <div class="ui-lector-cabecera"><i class="ph-duotone ph-file-pdf" aria-hidden="true"></i><div><h3 class="ui-title font-bold">Primero, escanea el respaldo</h3><p class="ui-muted text-xs mt-1">PDF legible, sin contraseña · máximo 8 MB. Se comprueban el contenido y las referencias antes de completar datos.</p></div></div>
    <label class="ui-label mt-3 block" for="{{ $identificador }}">Documento de autorización</label>
    <input id="{{ $identificador }}" type="file" wire:model="{{ $modelo }}" accept="application/pdf,.pdf" class="ui-input mt-2" wire:loading.attr="disabled" wire:target="{{ $accion }}" />
    <x-input-error :for="$modelo" />
    <p wire:loading wire:target="{{ $modelo }}" class="ui-muted text-xs mt-2" role="status">Recibiendo el documento…</p>
    <button type="button" class="ui-btn ui-btn-secondary mt-3" wire:click="{{ $accion }}" wire:loading.attr="disabled" wire:target="{{ $modelo }},{{ $accion }}"><i class="ph-duotone ph-scan" aria-hidden="true"></i>Escanear y completar datos</button>
    <div wire:loading.flex wire:target="{{ $accion }}" class="ui-lector-escaneo mt-3" role="status" aria-live="polite" aria-busy="true"><div class="ui-lector-hoja" aria-hidden="true"><i class="ph-duotone ph-file-text"></i><span></span></div><div><strong class="ui-title text-sm">Leyendo el documento…</strong><p class="ui-muted text-xs mt-1">Extrayendo texto y contrastando la autorización, la fecha y las referencias.</p><div class="ui-lector-progreso" aria-hidden="true"><span></span></div></div></div>
    @if($aceptado)<p class="ui-alert-info mt-3 text-sm" role="status"><i class="ph-duotone ph-check-circle" aria-hidden="true"></i>Contenido coherente. Datos detectados completados; revisa el motivo y confirma con la autoridad antes de guardar.</p>
    @elseif($revision && !($revision['coherente']??false))<div class="ui-alert-danger mt-3" role="alert"><strong>Documento rechazado</strong><ul class="mt-2 text-xs space-y-1">@foreach($revision['reglas']??[] as $regla=>$cumple)@if(!$cumple)<li>{{ $regla }}</li>@endif@endforeach</ul><p class="text-xs mt-2">El intento leído quedó en bitácora. Corrige el respaldo y vuelve a escanear; no se creó ningún registro.</p></div>
    @else<p class="ui-muted text-xs mt-3">Escanea para detectar y completar las referencias del documento. La lectura no autentica firmas ni sellos.</p>@endif
</section>
