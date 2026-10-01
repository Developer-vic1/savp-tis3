@extends('layouts.app')
@section('title','Calendario académico')
@section('content')
<div class="space-y-6">
    <section class="ui-panel"><nav aria-label="Ruta de navegación" class="ui-muted text-sm"><a href="{{ route($root) }}" class="underline">Inicio</a> / Calendario</nav><h1 class="ui-title mt-3 text-2xl font-black">Calendario académico</h1><p class="ui-muted mt-2">Fechas límite publicadas en tu alcance. Los eventos institucionales requieren su catálogo aprobado; no se generan fechas a partir de períodos sin calendario.</p></section>
    <form method="GET" class="ui-panel grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <label class="ui-label">Título<input class="ui-input" type="search" name="search" maxlength="100" value="{{ $filters['search'] ?? '' }}"></label>
        <label class="ui-label">Desde<input class="ui-input" type="date" name="from" value="{{ $filters['from'] ?? '' }}"></label>
        <label class="ui-label">Hasta<input class="ui-input" type="date" name="to" value="{{ $filters['to'] ?? '' }}"></label>
        <button class="ui-btn-primary self-end" type="submit">Filtrar</button>
        @foreach($errors->all() as $error)<p class="ui-error">{{ $error }}</p>@endforeach
    </form>
    <section class="ui-card overflow-x-auto" tabindex="0" aria-label="Fechas académicas"><table class="ui-table"><thead><tr><th scope="col">Fecha límite</th><th scope="col">Tarea</th><th scope="col">Curso</th><th scope="col">Estado</th><th scope="col">Acción</th></tr></thead><tbody>
        @forelse($rows as $row)<tr><td>{{ $row->fec_lim_tar?->format('d/m/Y H:i') }}</td><td>{{ $row->tit_tar }}</td><td>{{ $row->claseVirtual?->planAsignatura?->curso?->nom_cur }} · {{ $row->claseVirtual?->planAsignatura?->asignatura?->nom_asi }}</td><td>{{ $row->est_tar }}</td><td>
            @if(in_array($actor,['Docente','Estudiante'],true) && auth()->user()->can('Tareas_Aula'))<a class="underline" href="{{ route($actor==='Docente'?'docente.curso':'estudiante.materia',$row->cod_cla) }}?tab=tareas">Ver curso</a>@else<span class="ui-muted">Solo consulta</span>@endif
        </td></tr>@empty<tr><td colspan="5" class="ui-muted p-5">No hay fechas publicadas que coincidan con los filtros y tu alcance.</td></tr>@endforelse
    </tbody></table></section>
    {{ $rows->links() }}
    @if($events)
    <section class="ui-panel"><h2 class="ui-title text-xl font-bold">Eventos institucionales confirmados</h2>
        @forelse($events as $event)<article class="ui-card-soft mt-3 p-4"><h3 class="ui-title font-semibold">{{ $event->nom_cae }}</h3><p class="ui-muted">{{ $event->fii_cae?->format('d/m/Y') }} — {{ $event->ffi_cae?->format('d/m/Y') }} · {{ $event->est_cae }}</p><p>{{ $event->mot_cae }}</p></article>@empty<p class="ui-muted mt-3">No hay eventos confirmados para los filtros y tu alcance.</p>@endforelse
        {{ $events->links() }}
    </section>
    @endif
</div>
@endsection
