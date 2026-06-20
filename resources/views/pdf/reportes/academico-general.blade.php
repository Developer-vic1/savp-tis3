@php
/*
 * Vista PDF Académico General
 * Código: REP-ACA
 * Motor: mPDF
 */
use App\Support\Evaluacion\CalificacionInteligente;
$clasificador = app(CalificacionInteligente::class);
@endphp

{!! app(\App\Services\Reportes\GeneradorMpdfService::class)->htmlHeader($datos ?? get_defined_vars()) !!}

<div class="pdf-title">📊 Reporte Académico General</div>
<div class="pdf-kicker">Gestión académico-vocacional · {{ now()->format('Y') }}</div>

@if(isset($filtros_aplicados) && count($filtros_aplicados))
<div class="alert alert-info">
    <strong>Filtros aplicados:</strong> {{ implode(' | ', $filtros_aplicados) }}
</div>
@endif

{{-- KPIs principales --}}
<table class="kpi-grid" style="width:100%; border:0; border-collapse:collapse; margin: 12px 0;">
    <tr>
        <td style="width:25%; padding: 4px;">
            <div class="kpi-card">
                <div class="kpi-label">Promedio General</div>
                <div class="kpi-value kpi-green">{{ $promedio_general ?? '0.00' }}</div>
            </div>
        </td>
        <td style="width:25%; padding: 4px;">
            <div class="kpi-card">
                <div class="kpi-label">Registros Analizados</div>
                <div class="kpi-value kpi-blue">{{ $total_registros ?? 0 }}</div>
            </div>
        </td>
        <td style="width:25%; padding: 4px;">
            <div class="kpi-card">
                <div class="kpi-label">En Riesgo</div>
                <div class="kpi-value kpi-red">{{ $en_riesgo ?? 0 }}</div>
            </div>
        </td>
        <td style="width:25%; padding: 4px;">
            <div class="kpi-card">
                <div class="kpi-label">Destacados</div>
                <div class="kpi-value kpi-violet">{{ $destacados ?? 0 }}</div>
            </div>
        </td>
    </tr>
</table>

{{-- Sección 1: Rendimiento por Asignatura --}}
<div class="pdf-section">1. Rendimiento por Asignatura</div>

@if(isset($rendimiento_asignatura) && count($rendimiento_asignatura))
<table class="pdf-table">
    <thead>
        <tr>
            <th>Asignatura</th>
            <th style="text-align:center;">Promedio</th>
            <th style="text-align:center;">Registros</th>
            <th style="text-align:center;">En Riesgo</th>
            <th style="text-align:center;">Nota Máx.</th>
            <th style="text-align:center;">Nota Mín.</th>
            <th style="text-align:center;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rendimiento_asignatura as $item)
        <tr>
            <td><strong>{{ $item['nombre'] }}</strong></td>
            <td style="text-align:center; font-weight:bold; color:#059669;">{{ number_format($item['promedio'], 2) }}</td>
            <td style="text-align:center;">{{ $item['registros'] }}</td>
            <td style="text-align:center; color:#dc2626;">{{ $item['riesgo'] }}</td>
            <td style="text-align:center;">{{ number_format($item['max'] ?? 0, 1) }}</td>
            <td style="text-align:center;">{{ number_format($item['min'] ?? 0, 1) }}</td>
            <td style="text-align:center;">
                @php $prom = $item['promedio']; @endphp
                @if($prom >= 90)
                    <span class="badge badge-violet">Destacado</span>
                @elseif($prom >= 70)
                    <span class="badge badge-green">Aprobado</span>
                @elseif($prom >= 51)
                    <span class="badge badge-amber">Seguimiento</span>
                @else
                    <span class="badge badge-red">En riesgo</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">No se encontraron registros de calificaciones para los filtros seleccionados.</div>
@endif

{{-- Sección 2: Distribución Cualitativa --}}
<div class="pdf-section">2. Distribución Cualitativa del Desempeño</div>

@php
$dist = $distribucion ?? collect([]);
$total = max(1, collect($dist)->sum());
@endphp

<table style="width:100%; border:0; border-collapse:collapse; margin: 8px 0;">
@foreach(['Destacado' => ['#7c3aed', 'badge-violet'], 'Aprobado' => ['#059669', 'badge-green'], 'En seguimiento' => ['#d97706', 'badge-amber'], 'En riesgo' => ['#dc2626', 'badge-red']] as $estado => [$color, $badge])
@php $cant = $dist[$estado] ?? 0; $pct = round(($cant / $total) * 100, 1); @endphp
<tr>
    <td style="width: 130px; padding: 4px 8px;"><span class="badge {{ $badge }}">{{ $estado }}</span></td>
    <td style="padding: 4px 8px;">
        <div class="bar-wrap"><div class="bar-fill" style="width:{{ $pct }}%; background: {{ $color }};"></div></div>
    </td>
    <td style="width: 80px; text-align:right; padding: 4px 8px; font-weight:bold; color:{{ $color }};">{{ $cant }} ({{ $pct }}%)</td>
</tr>
@endforeach
</table>

{{-- Sección 3: Compatibilidad por Especialidad --}}
<div class="pdf-section">3. Compatibilidad por Especialidad Técnica</div>

@if(isset($compatibilidad) && count($compatibilidad))
<table class="pdf-table">
    <thead>
        <tr>
            <th>Especialidad</th>
            <th>Área Profesional</th>
            <th style="text-align:center;">Estudiantes</th>
            <th style="text-align:center;">Promedio</th>
            <th style="text-align:center;">%</th>
            <th>Carreras Sugeridas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($compatibilidad as $item)
        <tr>
            <td><strong>{{ $item['especialidad'] }}</strong></td>
            <td style="color:#0284c7;">{{ $item['area'] }}</td>
            <td style="text-align:center;">{{ $item['estudiantes'] }}</td>
            <td style="text-align:center; font-weight:bold; color:#059669;">{{ number_format($item['promedio'], 2) }}</td>
            <td style="text-align:center;"><span class="badge badge-blue">{{ $item['porcentaje'] }}%</span></td>
            <td style="font-size:7.5pt; color:#334155;">{{ implode(', ', $item['carreras']) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">No hay datos de especialidades vinculadas a calificaciones.</div>
@endif

{{-- Sección 4: Rendimiento por Periodo --}}
<div class="pdf-section">4. Rendimiento por Periodo de Evaluación</div>

@if(isset($rendimiento_periodo) && count($rendimiento_periodo))
<table class="pdf-table">
    <thead>
        <tr>
            <th>Periodo</th>
            <th style="text-align:center;">Promedio</th>
            <th style="text-align:center;">Registros</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rendimiento_periodo as $item)
        <tr>
            <td>{{ $item['nombre'] }}</td>
            <td style="text-align:center; font-weight:bold; color:#059669;">{{ number_format($item['promedio'], 2) }}</td>
            <td style="text-align:center;">{{ $item['registros'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">No hay datos de periodos de evaluación.</div>
@endif

{{-- Interpretación y observaciones --}}
<div class="pdf-section">5. Interpretación Académica</div>

<div class="alert alert-info">
    <strong>Análisis general:</strong>
    El promedio institucional de <strong>{{ $promedio_general ?? '0.00' }}</strong> puntos indica
    @if(($promedio_general ?? 0) >= 70)
        un nivel académico <strong>satisfactorio</strong>. Se recomienda mantener las estrategias pedagógicas actuales y potenciar a los estudiantes destacados.
    @elseif(($promedio_general ?? 0) >= 51)
        un nivel <strong>en proceso de consolidación</strong>. Se recomienda refuerzo pedagógico focalizado en las asignaturas con mayor índice de riesgo.
    @else
        un nivel académico <strong>que requiere atención prioritaria</strong>. Se recomienda activar protocolos de intervención pedagógica inmediata.
    @endif
</div>

<div class="alert alert-success" style="margin-top: 8px;">
    <strong>Observaciones institucionales:</strong> Este reporte fue generado automáticamente con datos del sistema SAVP-TIS3.
    Los datos corresponden a calificaciones activas registradas. Para mayor detalle por estudiante, consulte el Reporte de Calificaciones.
</div>

{!! app(\App\Services\Reportes\GeneradorMpdfService::class)->htmlFooter($sistema ?? 'SAVP-TIS3') !!}
