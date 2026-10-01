@extends('layouts.app')
@section('title','Documentación de inscripciones')
@section('content')
<div class="space-y-6">
    <section class="ui-panel"><nav aria-label="Ruta de navegación" class="ui-muted text-sm"><a class="underline" href="{{ route(app(\App\Services\RoleDashboardResolver::class)->routeFor(auth()->user())) }}">Inicio</a> / Documentación</nav><h1 class="ui-title mt-3 text-2xl font-black">Documentación de inscripciones</h1><p class="ui-muted mt-2">Revisa el estado documental y las descargas privadas. El archivo no constituye una afirmación de autenticidad.</p></section>
    <form class="ui-panel flex flex-wrap gap-4" method="GET"><label class="ui-label flex-1">Documento o estudiante<input class="ui-input" type="search" name="search" maxlength="100" value="{{ $filters['search']??'' }}"></label><label class="ui-label">Estado<select class="ui-select" name="estado"><option value="">Todos</option>@foreach(['PENDIENTE','PRESENTADO','VALIDADO','OBSERVADO','VENCIDO','NO_APLICA','ANULADO'] as $state)<option @selected(($filters['estado']??'')===$state)>{{ $state }}</option>@endforeach</select></label><button type="submit" class="ui-btn-primary self-end">Filtrar</button>@foreach($errors->all() as $error)<p class="ui-error">{{ $error }}</p>@endforeach</form>
    <section class="ui-card overflow-x-auto" tabindex="0" aria-label="Documentos registrados"><table class="ui-table"><thead><tr><th scope="col">Estudiante</th><th scope="col">Documento</th><th scope="col">Estado</th><th scope="col">Vencimiento</th><th scope="col">Acción</th></tr></thead><tbody>
    @forelse($rows as $row)<tr><td>{{ $row->inscripcion?->estudiante?->persona?->nom_per }} {{ $row->inscripcion?->estudiante?->persona?->ape_pat_per }}</td><td>{{ $row->nom_die }}</td><td>{{ $row->est_die }}</td><td>{{ $row->fec_lim_die?->format('d/m/Y')??'Sin plazo' }}</td><td>
        @can('view',$row)
            @if(\App\Support\PrivateFilePath::valid($row->rut_die,'inscripciones-privadas'))<a class="underline" href="{{ route('documentos.inscripciones.descargar',$row->cod_die) }}">Descargar archivo privado</a>@elseif($row->rut_die)<span class="ui-muted">Archivo histórico pendiente de traslado privado autorizado</span>@else<span class="ui-muted">Sin archivo adjunto</span>@endif
        @endcan
    </td></tr>@empty<tr><td colspan="5" class="ui-muted">No hay documentos registrados que coincidan con estos filtros.</td></tr>@endforelse
    </tbody></table></section>{{ $rows->links() }}
    <a class="ui-btn-secondary" href="{{ route(auth()->user()->hasRole('Administrador')?'admin.gestion-inscripciones':'secretaria.inscripciones') }}">Gestionar inscripción y documentación</a>
</div>
@endsection
