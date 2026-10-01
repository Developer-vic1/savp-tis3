@extends('layouts.app')
@section('title', 'Reportes administrativos | SAVP')
@section('content')
<div class="space-y-5">
<section class="ui-panel"><nav aria-label="Ruta de navegación"><a class="underline" href="{{ route('secretaria.dashboard') }}">Inicio</a> / Reportes administrativos</nav><h1 class="ui-title mt-3 text-2xl font-black">Reportes administrativos generados</h1><p class="ui-muted mt-2">PDFs disponibles con permiso administrativo e integridad comprobada al descargar.</p></section>
<form class="ui-panel flex flex-wrap items-end gap-3" method="GET"><label class="ui-label flex-1">Código<input class="ui-input" name="search" maxlength="100" value="{{ $filters['search'] ?? '' }}"></label><button type="submit" class="ui-btn-primary">Buscar</button><a class="ui-btn-secondary" href="{{ route('secretaria.reportes') }}">Limpiar</a></form>
<div class="ui-panel overflow-x-auto" tabindex="0" aria-label="Reportes administrativos"><table class="ui-table"><thead><tr><th scope="col">Código</th><th scope="col">Tipo</th><th scope="col">Fecha</th><th scope="col">Acción</th></tr></thead><tbody>
@forelse($rows as $row)<tr><td>{{ $row->codigo }}</td><td>{{ $row->tipo_reporte }}</td><td>{{ $row->created_at?->format('d/m/Y H:i') }}</td><td>@can('view', $row)<a class="ui-btn-secondary" href="{{ route('reportes.historicos.descargar', $row) }}">Descargar PDF</a>@endcan</td></tr>@empty<tr><td colspan="4" class="ui-muted">No hay reportes administrativos generados que coincidan con los filtros.</td></tr>@endforelse
</tbody></table>{{ $rows->links() }}</div>
</div>
@endsection
