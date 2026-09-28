@extends('layouts.app')
@section('title', $title.' | SAVP')
@section('content')
<div class="space-y-6">
    <section class="ui-panel">
        <nav aria-label="Ruta de navegación"><a class="ui-muted underline" href="{{ route($dashboardRoute) }}">Inicio</a> / {{ $title }}</nav>
        <h1 class="ui-title mt-3 text-3xl font-black">{{ $title }}</h1>
        <p class="ui-muted mt-2">Consulta institucional de solo lectura. {{ $rows->total() }} registros encontrados.</p>
    </section>
    <form method="GET" class="ui-panel flex flex-wrap items-end gap-3">
        <label class="ui-label flex-1" for="query-search">Buscar por código o nombre<input class="ui-input mt-2" id="query-search" name="search" type="search" maxlength="100" value="{{ $search }}"></label>
        @if($years->isNotEmpty())<label class="ui-label">Gestión<select name="gestion" class="ui-select"><option value="">Todas las autorizadas</option>@foreach($years as $year)<option value="{{ $year->cod_gea }}" @selected($gestion === $year->cod_gea)>{{ $year->ani_gea }}</option>@endforeach</select></label>@endif
        <button class="ui-btn-primary" type="submit">Buscar</button>
        <a class="ui-btn-secondary" href="{{ url()->current() }}">Limpiar</a>
    </form>
    <div class="ui-card overflow-x-auto" tabindex="0" aria-label="Resultados de la consulta">
        <table class="ui-table text-sm"><thead><tr>@foreach($columns as $label)<th scope="col">{{ $label }}</th>@endforeach</tr></thead>
            <tbody>@forelse($rows as $row)<tr>@foreach($columns as $field => $label)<td>{{ data_get($row, $field) ?? 'Sin datos' }}</td>@endforeach</tr>
            @empty<tr><td colspan="{{ count($columns) }}" class="ui-muted">{{ $search !== '' ? 'No hay resultados para la búsqueda.' : 'No existen registros disponibles.' }}</td></tr>@endforelse</tbody>
        </table>
    </div>
    {{ $rows->links() }}
</div>
@endsection
