{!! app(\App\Services\Reportes\GeneradorMpdfService::class)->htmlHeader(get_defined_vars()) !!}

<div class="pdf-title" style="color:#7c3aed; border-color:#ddd6fe;">🧭 Reporte Vocacional RIASEC</div>
<div class="pdf-kicker">Orientación académico-vocacional institucional · {{ now()->format('d/m/Y') }}</div>

@php
$perfiles     = $perfiles_riasec ?? [];
$distribucion = $distribucion_riasec ?? [];
$resultados   = $resultados_especialidad ?? collect([]);
$hayDatos     = $hay_datos_reales ?? false;
@endphp

@if(!$hayDatos)
<div class="alert alert-warning">
    <strong>Nota:</strong> No se encontraron calificaciones activas vinculadas a especialidades técnicas.
    Los datos vocacionales requieren que los estudiantes tengan especialidad asignada y calificaciones registradas.
</div>
@endif

{{-- Distribución RIASEC Institucional --}}
<div class="pdf-section">1. Perfil RIASEC Institucional</div>

@php
$letras = ['R', 'I', 'A', 'S', 'E', 'C'];
$colores = ['R' => '#059669', 'I' => '#0284c7', 'A' => '#7c3aed', 'S' => '#d97706', 'E' => '#dc2626', 'C' => '#334155'];
$maxDist = max(1, ...array_values($distribucion ?: [1]));
@endphp

<table style="width:100%; border:0; border-collapse:collapse; margin: 10px 0;">
    <tr>
        @foreach($letras as $l)
        @php $val = $distribucion[$l] ?? 0; $color = $colores[$l]; @endphp
        <td style="width:16.6%; padding: 4px; text-align:center;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px;">
                <div style="font-size: 22pt; font-weight: bold; color: {{ $color }};">{{ $l }}</div>
                <div style="font-size: 8pt; font-weight: bold; color: #0f172a;">{{ $perfiles[$l]['nombre'] ?? '' }}</div>
                <div style="font-size: 14pt; font-weight: bold; color: {{ $color }}; margin-top: 4px;">{{ $val }}</div>
                <div class="bar-wrap" style="margin-top: 4px;"><div class="bar-fill" style="width:{{ $maxDist > 0 ? round(($val/$maxDist)*100) : 0 }}%; background: {{ $color }};"></div></div>
            </div>
        </td>
        @endforeach
    </tr>
</table>

<div class="alert alert-info">
    <strong>Perfil institucional:</strong>
    {{ $perfil_institucional ?? 'No determinado' }} ·
    {{ $interpretacion ?? 'Sin interpretación disponible.' }}
</div>

{{-- Carreras más recomendadas --}}
@if(!empty($carreras_recomendadas))
<div class="pdf-section">2. Carreras Más Recomendadas Institucionalmente</div>
<div style="margin: 8px 0;">
    @foreach($carreras_recomendadas as $carrera)
    <span class="badge badge-violet" style="margin: 3px;">{{ $carrera }}</span>
    @endforeach
</div>
@endif

{{-- Resultados por especialidad --}}
<div class="pdf-section">3. Resultados por Especialidad Técnica</div>

@if($resultados->count())
<table class="pdf-table">
    <thead>
        <tr>
            <th>Especialidad</th>
            <th style="text-align:center;">Perfil RIASEC</th>
            <th style="text-align:center;">Estudiantes</th>
            <th style="text-align:center;">Promedio</th>
            <th style="text-align:center;">Compatibilidad</th>
            <th>Carreras Sugeridas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($resultados as $item)
        <tr>
            <td><strong>{{ $item['especialidad'] }}</strong></td>
            <td style="text-align:center; font-size:12pt; font-weight:bold; color:#7c3aed;">{{ $item['perfil_texto'] ?? '—' }}</td>
            <td style="text-align:center;">{{ $item['estudiantes'] }}</td>
            <td style="text-align:center; font-weight:bold; color:#059669;">{{ number_format($item['promedio'], 2) }}</td>
            <td style="text-align:center;">
                <span class="badge {{ $item['compatibilidad'] >= 80 ? 'badge-green' : ($item['compatibilidad'] >= 60 ? 'badge-blue' : 'badge-amber') }}">
                    {{ $item['compatibilidad'] ?? 0 }}%
                </span>
            </td>
            <td style="font-size:7.5pt; color:#334155;">{{ implode(', ', $item['carreras'] ?? []) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">No hay datos de especialidades técnicas con calificaciones registradas.</div>
@endif

{{-- Descripción de perfiles RIASEC --}}
<div class="pdf-section">4. Descripción de Perfiles RIASEC</div>

<table class="pdf-table">
    <thead>
        <tr>
            <th style="width:8%;">Tipo</th>
            <th style="width:12%;">Perfil</th>
            <th>Descripción</th>
            <th>Fortalezas</th>
        </tr>
    </thead>
    <tbody>
        @foreach($perfiles as $letra => $perfil)
        <tr>
            <td style="text-align:center; font-size:16pt; font-weight:bold; color:{{ $colores[$letra] ?? '#334155' }};">{{ $letra }}</td>
            <td style="font-weight:bold; color:#0f172a;">{{ $perfil['nombre'] }}</td>
            <td style="font-size:7.5pt; color:#334155;">{{ $perfil['descripcion'] }}</td>
            <td style="font-size:7.5pt;">
                @foreach($perfil['fortalezas'] ?? [] as $f)
                <span class="badge badge-gray" style="margin:1px;">{{ $f }}</span>
                @endforeach
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Observaciones --}}
<div class="alert alert-success" style="margin-top: 10px;">
    <strong>Observaciones de orientación:</strong> Los perfiles RIASEC se calculan a partir de las especialidades técnicas BTH
    vinculadas a los estudiantes con calificaciones activas. Para una evaluación vocacional individual completa,
    se recomienda aplicar instrumentos psicométricos especializados complementarios.
</div>

{!! app(\App\Services\Reportes\GeneradorMpdfService::class)->htmlFooter($sistema ?? 'SAVP-TIS3') !!}
