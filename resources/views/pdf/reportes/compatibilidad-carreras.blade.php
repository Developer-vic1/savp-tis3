{!! app(\App\Services\Reportes\GeneradorMpdfService::class)->htmlHeader(get_defined_vars()) !!}

<div class="pdf-title" style="color:#7c3aed; border-color:#ddd6fe;">🎓 Reporte de Compatibilidad de Carreras</div>
<div class="pdf-kicker">Análisis vocacional y proyección académica · {{ now()->format('d/m/Y') }}</div>

@php
$compatibilidades = $compatibilidades ?? collect([]);
@endphp

{{-- Resumen --}}
<table style="width:100%; border:0; border-collapse:collapse; margin: 10px 0;">
    <tr>
        <td style="width:33%; padding:4px;">
            <div class="kpi-card" style="border-color:#ddd6fe; background:#f5f3ff;">
                <div class="kpi-label">Especialidades Analizadas</div>
                <div class="kpi-value kpi-violet">{{ $compatibilidades->count() }}</div>
            </div>
        </td>
        <td style="width:33%; padding:4px;">
            <div class="kpi-card" style="border-color:#a7f3d0; background:#ecfdf5;">
                <div class="kpi-label">Perfil Institucional</div>
                <div class="kpi-value kpi-green" style="font-size:14pt;">{{ $perfil_institucional ?? '—' }}</div>
            </div>
        </td>
        <td style="width:33%; padding:4px;">
            <div class="kpi-card" style="border-color:#bae6fd; background:#f0f9ff;">
                <div class="kpi-label">Total Estudiantes</div>
                <div class="kpi-value kpi-blue">{{ $total_estudiantes ?? 0 }}</div>
            </div>
        </td>
    </tr>
</table>

{{-- Tabla de compatibilidades --}}
<div class="pdf-section">1. Análisis de Compatibilidad por Especialidad</div>

@if($compatibilidades->count())
<table class="pdf-table">
    <thead>
        <tr>
            <th>Especialidad</th>
            <th style="text-align:center;">Perfil</th>
            <th>Área Profesional</th>
            <th style="text-align:center;">Promedio</th>
            <th style="text-align:center;">Compat.</th>
            <th style="text-align:center;">Riesgo</th>
            <th>Carreras Recomendadas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($compatibilidades as $item)
        @php
        $compat = $item['compatibilidad_pct'] ?? 0;
        $riesgo = $item['riesgo_academico'] ?? 'Bajo';
        @endphp
        <tr>
            <td><strong>{{ $item['especialidad'] }}</strong></td>
            <td style="text-align:center; font-size:12pt; font-weight:bold; color:#7c3aed;">{{ $item['perfil_riasec'] ?? '—' }}</td>
            <td style="color:#0284c7; font-size:7.5pt;">{{ $item['area_profesional'] ?? '—' }}</td>
            <td style="text-align:center; font-weight:bold; color:#059669;">{{ number_format($item['promedio'] ?? 0, 2) }}</td>
            <td style="text-align:center;">
                <span class="badge {{ $compat >= 80 ? 'badge-green' : ($compat >= 60 ? 'badge-blue' : 'badge-amber') }}">
                    {{ $compat }}%
                </span>
            </td>
            <td style="text-align:center;">
                <span class="badge {{ $riesgo === 'Crítico' || $riesgo === 'Alto' ? 'badge-red' : ($riesgo === 'Medio' ? 'badge-amber' : 'badge-green') }}">
                    {{ $riesgo }}
                </span>
            </td>
            <td style="font-size:7.5pt; color:#334155;">
                {{ implode(', ', array_slice($item['carreras'] ?? [], 0, 3)) }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">No se encontraron datos de compatibilidad. Se requieren calificaciones activas vinculadas a especialidades técnicas.</div>
@endif

{{-- Detalle por especialidad --}}
<div class="pdf-section">2. Observaciones por Especialidad</div>

@if($compatibilidades->count())
@foreach($compatibilidades as $item)
<div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:8px; margin:6px 0;">
    <table style="width:100%; border:0; border-collapse:collapse;">
        <tr>
            <td style="width:60%;">
                <strong style="color:#0f172a; font-size:10pt;">{{ $item['especialidad'] }}</strong>
                <span class="badge badge-violet" style="margin-left:6px;">{{ $item['perfil_riasec'] ?? '' }}</span>
                <span class="badge badge-blue" style="margin-left:4px;">{{ $item['area_profesional'] ?? '' }}</span>
            </td>
            <td style="text-align:right;">
                <span class="badge {{ ($item['riesgo_academico'] ?? '') === 'Bajo' ? 'badge-green' : 'badge-amber' }}">
                    Riesgo {{ $item['riesgo_academico'] ?? '' }}
                </span>
            </td>
        </tr>
    </table>
    <div style="font-size:7.5pt; color:#334155; margin-top:5px;">{{ $item['observacion'] ?? '' }}</div>
    @if(!empty($item['fortalezas']))
    <div style="margin-top:5px;">
        <span style="font-size:7pt; color:#64748b; font-weight:bold;">Fortalezas: </span>
        @foreach($item['fortalezas'] as $f)
        <span class="badge badge-gray" style="margin:1px;">{{ $f }}</span>
        @endforeach
    </div>
    @endif
</div>
@endforeach
@endif

{{-- Carreras globales --}}
@if(!empty($carreras_recomendadas))
<div class="pdf-section">3. Carreras con Mayor Afinidad Institucional</div>
<div style="margin: 8px 0;">
    @foreach($carreras_recomendadas as $carrera)
    <span class="badge badge-violet" style="margin: 3px 3px 3px 0;">{{ $carrera }}</span>
    @endforeach
</div>
@endif

<div class="alert alert-info" style="margin-top: 10px;">
    <strong>Recomendación:</strong> La compatibilidad de carreras es calculada a partir del perfil RIASEC de cada especialidad
    técnica BTH combinado con el rendimiento académico. Se recomienda complementar con entrevistas y test psicométricos formales.
</div>

{!! app(\App\Services\Reportes\GeneradorMpdfService::class)->htmlFooter($sistema ?? 'SAVP-TIS3') !!}
