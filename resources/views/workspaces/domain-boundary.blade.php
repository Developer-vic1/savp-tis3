@extends('layouts.app')
@section('title',$title)
@section('content')
<div class="space-y-6"><section class="ui-panel"><nav class="ui-muted" aria-label="Ruta de navegación"><a class="underline" href="{{ route($root) }}">Inicio</a> / {{ $title }}</nav><h1 class="ui-title mt-3 text-2xl font-black">{{ $title }}</h1><p class="ui-muted mt-3">{{ $impact }}</p></section>
<section class="ui-panel"><h2 class="ui-title text-xl font-bold">Registro pendiente de habilitación</h2><p class="ui-alert-warning mt-3" role="status">{{ $dependency }}</p><p class="ui-muted mt-3">Este registro todavía no está disponible. No podemos mostrar un historial hasta que se habilite la consulta autorizada.</p></section>
<section class="ui-panel"><h2 class="ui-title text-xl font-bold">Estructura del registro</h2><dl class="mt-4 grid gap-4 sm:grid-cols-2">@foreach($fields as $field)<div class="ui-card-soft p-3"><dt class="font-semibold">{{ $field }}</dt><dd class="ui-muted mt-1">Requiere la regla y el catálogo aprobados para su registro.</dd></div>@endforeach</dl></section>
@if(in_array($domain, ['kardex','seguimientos'], true) && $actor === 'Docente' && auth()->user()->can('kardex.registrar.curso'))
    <livewire:shared.kardex-draft />
@endif
<a class="ui-btn-secondary" href="{{ route($root) }}">Volver a mi espacio de trabajo</a></div>
@endsection
