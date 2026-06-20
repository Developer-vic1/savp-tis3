{!! app(\App\Services\Reportes\GeneradorMpdfService::class)->htmlHeader(get_defined_vars()) !!}

<div class="pdf-title">📋 Reporte de Calificaciones</div>
<div class="pdf-kicker">Listado académico detallado · {{ now()->format('d/m/Y') }}</div>

{{-- Métricas resumen --}}
@php
$cals = $calificaciones ?? collect([]);
$totalCals = $cals->count();
$aprobados = $cals->filter(fn($c) => in_array($c->desempeno ?? '', ['Aprobado', 'Destacado']))->count();
$enRiesgo = $cals->where('desempeno', 'En riesgo')->count();
$notaMax = $cals->max('not_cal') ?? 0;
$notaMin = $totalCals > 0 ? $cals->min('not_cal') : 0;
@endphp

<table style="width:100%; border:0; border-collapse:collapse; margin: 10px 0;">
    <tr>
        <td style="width:20%; padding:4px;">
            <div class="kpi-card"><div class="kpi-label">Total Registros</div><div class="kpi-value kpi-blue">{{ $totalCals }}</div></div>
        </td>
        <td style="width:20%; padding:4px;">
            <div class="kpi-card"><div class="kpi-label">Aprobados</div><div class="kpi-value kpi-green">{{ $aprobados }}</div></div>
        </td>
        <td style="width:20%; padding:4px;">
            <div class="kpi-card"><div class="kpi-label">En Riesgo</div><div class="kpi-value kpi-red">{{ $enRiesgo }}</div></div>
        </td>
        <td style="width:20%; padding:4px;">
            <div class="kpi-card"><div class="kpi-label">Nota Máxima</div><div class="kpi-value kpi-violet">{{ number_format($notaMax, 1) }}</div></div>
        </td>
        <td style="width:20%; padding:4px;">
            <div class="kpi-card"><div class="kpi-label">Nota Mínima</div><div class="kpi-value kpi-amber">{{ number_format($notaMin, 1) }}</div></div>
        </td>
    </tr>
</table>

{{-- Listado completo --}}
<div class="pdf-section">Listado de Calificaciones</div>

@if($cals->count())
<table class="pdf-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Estudiante</th>
            <th>Especialidad</th>
            <th>Asignatura</th>
            <th>Periodo</th>
            <th style="text-align:center;">Nota</th>
            <th style="text-align:center;">Desempeño</th>
            <th>Observación</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cals as $i => $item)
        @php
        $persona = $item->estudiante?->persona;
        $nombre = trim(($persona?->nom_per ?? '') . ' ' . ($persona?->ape_pat_per ?? '') . ' ' . ($persona?->ape_mat_per ?? ''));
        $desemp = $item->desempeno ?? 'Sin clasificar';
        @endphp
        <tr>
            <td style="color:#64748b; font-size:7.5pt;">{{ $i + 1 }}</td>
            <td><strong>{{ $nombre ?: 'Sin nombre' }}</strong></td>
            <td style="font-size:7.5pt; color:#334155;">{{ $item->estudiante?->especialidad?->nom_esp ?? '—' }}</td>
            <td>{{ $item->asignatura?->nom_asi ?? '—' }}</td>
            <td style="font-size:7.5pt; color:#64748b;">{{ $item->periodoEvaluacion?->nom_pev ?? '—' }}</td>
            <td style="text-align:center; font-weight:bold; font-size:11pt;
                color:{{ $desemp === 'En riesgo' ? '#dc2626' : ($desemp === 'Destacado' ? '#7c3aed' : '#059669') }}">
                {{ number_format($item->not_cal, 2) }}
            </td>
            <td style="text-align:center;">
                @if($desemp === 'Destacado')
                    <span class="badge badge-violet">Destacado</span>
                @elseif($desemp === 'Aprobado')
                    <span class="badge badge-green">Aprobado</span>
                @elseif($desemp === 'En seguimiento')
                    <span class="badge badge-amber">Seguimiento</span>
                @else
                    <span class="badge badge-red">En riesgo</span>
                @endif
            </td>
            <td style="font-size:7.5pt; color:#64748b;">{{ $item->obs_cal ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">No se encontraron calificaciones activas registradas en el sistema.</div>
@endif

{{-- Resumen final --}}
<div class="alert alert-info" style="margin-top: 10px;">
    <strong>Observación del periodo:</strong> El presente reporte incluye únicamente calificaciones con estado ACTIVO.
    Para reportes filtrados por periodo, asignatura o estudiante, use los filtros del sistema y regenere el reporte.
</div>

{!! app(\App\Services\Reportes\GeneradorMpdfService::class)->htmlFooter($sistema ?? 'SAVP-TIS3') !!}
