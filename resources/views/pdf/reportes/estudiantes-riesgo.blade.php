{!! app(\App\Services\Reportes\GeneradorMpdfService::class)->htmlHeader(get_defined_vars()) !!}

<div class="pdf-title" style="color:#dc2626; border-color:#fecdd3;">⚠ Reporte de Estudiantes en Riesgo</div>
<div class="pdf-kicker">Seguimiento académico prioritario · {{ now()->format('d/m/Y') }}</div>

@php
$estudiantesRiesgo = $estudiantes_riesgo ?? collect([]);
$total = $estudiantesRiesgo->count();
$critico = $estudiantesRiesgo->where('nivel_riesgo', 'Crítico')->count();
$alto    = $estudiantesRiesgo->where('nivel_riesgo', 'Alto')->count();
$medio   = $estudiantesRiesgo->where('nivel_riesgo', 'Medio')->count();
@endphp

{{-- KPIs de riesgo --}}
<table style="width:100%; border:0; border-collapse:collapse; margin: 10px 0;">
    <tr>
        <td style="width:25%; padding:4px;">
            <div class="kpi-card" style="border-color:#fecdd3; background:#fff1f2;">
                <div class="kpi-label">Total en Riesgo</div>
                <div class="kpi-value kpi-red">{{ $total }}</div>
            </div>
        </td>
        <td style="width:25%; padding:4px;">
            <div class="kpi-card" style="border-color:#fecdd3; background:#fff1f2;">
                <div class="kpi-label">Riesgo Crítico</div>
                <div class="kpi-value kpi-red">{{ $critico }}</div>
            </div>
        </td>
        <td style="width:25%; padding:4px;">
            <div class="kpi-card" style="border-color:#fde68a; background:#fffbeb;">
                <div class="kpi-label">Riesgo Alto</div>
                <div class="kpi-value kpi-amber">{{ $alto }}</div>
            </div>
        </td>
        <td style="width:25%; padding:4px;">
            <div class="kpi-card" style="border-color:#fde68a; background:#fffbeb;">
                <div class="kpi-label">Riesgo Medio</div>
                <div class="kpi-value kpi-amber">{{ $medio }}</div>
            </div>
        </td>
    </tr>
</table>

@if($total === 0)
<div class="alert alert-success">
    <strong>✓ Sin estudiantes en riesgo.</strong> No se encontraron estudiantes con calificaciones en estado de riesgo académico para los filtros actuales.
</div>
@else

<div class="alert alert-danger">
    <strong>Atención:</strong> Se han identificado <strong>{{ $total }}</strong> estudiantes en situación de riesgo académico.
    Se recomienda activar protocolos de intervención pedagógica inmediata para los casos críticos.
</div>

{{-- Tabla de estudiantes en riesgo --}}
<div class="pdf-section">Listado de Estudiantes en Riesgo</div>

<table class="pdf-table pdf-table-danger">
    <thead>
        <tr>
            <th>#</th>
            <th>Estudiante</th>
            <th>Especialidad</th>
            <th style="text-align:center;">Promedio</th>
            <th style="text-align:center;">Nivel</th>
            <th>Asignaturas Críticas</th>
            <th>Recomendación</th>
        </tr>
    </thead>
    <tbody>
        @foreach($estudiantesRiesgo as $i => $item)
        <tr>
            <td style="color:#64748b; font-size:7.5pt;">{{ $i + 1 }}</td>
            <td><strong>{{ $item['nombre'] ?: 'Sin nombre' }}</strong></td>
            <td style="font-size:7.5pt; color:#334155;">{{ $item['especialidad'] ?? '—' }}</td>
            <td style="text-align:center; font-weight:bold; color:#dc2626; font-size:11pt;">
                {{ number_format($item['promedio'] ?? 0, 2) }}
            </td>
            <td style="text-align:center;">
                @php $nivel = $item['nivel_riesgo'] ?? 'Bajo'; @endphp
                @if($nivel === 'Crítico')
                    <span class="badge badge-red">Crítico</span>
                @elseif($nivel === 'Alto')
                    <span class="badge badge-red">Alto</span>
                @else
                    <span class="badge badge-amber">Medio</span>
                @endif
            </td>
            <td style="font-size:7.5pt; color:#334155;">
                {{ implode(', ', $item['asignaturas_criticas'] ?? []) ?: '—' }}
            </td>
            <td style="font-size:7.5pt; color:#64748b;">
                @if(($item['promedio'] ?? 0) < 40)
                    Intervención pedagógica inmediata. Reunión con familia y tutor.
                @elseif(($item['promedio'] ?? 0) < 51)
                    Seguimiento semanal. Apoyo extraclase y tutoría especializada.
                @else
                    Monitoreo quincenal. Refuerzo en asignaturas con bajo rendimiento.
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Observaciones de seguimiento --}}
<div class="pdf-section">Observaciones de Seguimiento</div>

<div class="alert alert-warning">
    <strong>Protocolo recomendado:</strong>
    <br>• <strong>Riesgo Crítico (promedio &lt; 40):</strong> Notificación inmediata a familias, plan de contingencia académica y derivación a orientación vocacional.
    <br>• <strong>Riesgo Alto (40–50):</strong> Tutoría personalizada, seguimiento semanal y ajuste de estrategias pedagógicas.
    <br>• <strong>Riesgo Medio (51–60):</strong> Monitoreo quincenal, apoyo extraclase y motivación académica.
</div>
@endif

{!! app(\App\Services\Reportes\GeneradorMpdfService::class)->htmlFooter($sistema ?? 'SAVP-TIS3') !!}
