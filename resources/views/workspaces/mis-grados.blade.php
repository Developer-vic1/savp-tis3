@extends('layouts.app')
@section('title','Mis grados asignados')
@section('content')
<section class="ui-panel space-y-4"><nav aria-label="Ruta de navegación"><a class="underline" href="{{ route('regencia.dashboard') }}">Inicio</a> / Mis grados</nav><h1 class="ui-title text-2xl font-black">Mis grados asignados</h1><p class="ui-muted">Asignaciones activas por gestión y grado. El alcance no concede edición pedagógica.</p>
@if(!$available)<p class="ui-alert-warning">La persistencia de asignaciones requiere aplicación autorizada de su estructura. Las consultas permanecen sin acceso global.</p>
@else
@forelse($rows as $row)<article class="ui-card-soft p-4"><h2 class="ui-title font-bold">{{ $row->curso?->nom_cur }} · {{ $row->gestion?->ani_gea }}</h2><a class="underline" href="{{ route('regencia.consulta',['area'=>'estudiantes','curso'=>$row->cod_cur,'gestion'=>$row->cod_gea]) }}">Ver estudiantes de este grado</a></article>@empty<p class="ui-muted">No tienes grados asignados en una gestión activa. Solicita a Administración revisar tus asignaciones.</p>@endforelse
{{ $rows->links() }}
@endif
</section>
@endsection
