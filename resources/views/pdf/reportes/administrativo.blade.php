{!! app(\App\Services\Reportes\GeneradorMpdfService::class)->htmlHeader(get_defined_vars()) !!}

<div class="pdf-title" style="color:#0284c7; border-color:#bae6fd;">🏛 Reporte Administrativo Institucional</div>
<div class="pdf-kicker">Control operativo del sistema · {{ now()->format('d/m/Y') }}</div>

@php $metricas = $metricas ?? []; @endphp

{{-- KPIs principales --}}
<table style="width:100%; border:0; border-collapse:collapse; margin: 10px 0;">
    <tr>
        <td style="width:25%; padding:4px;">
            <div class="kpi-card" style="border-color:#bae6fd; background:#f0f9ff;">
                <div class="kpi-label">Usuarios Activos</div>
                <div class="kpi-value kpi-blue">{{ $metricas['usuarios_activos'] ?? 0 }}</div>
            </div>
        </td>
        <td style="width:25%; padding:4px;">
            <div class="kpi-card" style="border-color:#a7f3d0; background:#ecfdf5;">
                <div class="kpi-label">Estudiantes Activos</div>
                <div class="kpi-value kpi-green">{{ $metricas['estudiantes_activos'] ?? 0 }}</div>
            </div>
        </td>
        <td style="width:25%; padding:4px;">
            <div class="kpi-card" style="border-color:#bae6fd; background:#f0f9ff;">
                <div class="kpi-label">Docentes</div>
                <div class="kpi-value kpi-blue">{{ $metricas['docentes_activos'] ?? 0 }}</div>
            </div>
        </td>
        <td style="width:25%; padding:4px;">
            <div class="kpi-card" style="border-color:#ddd6fe; background:#f5f3ff;">
                <div class="kpi-label">Inscripciones</div>
                <div class="kpi-value kpi-violet">{{ $metricas['inscripciones_totales'] ?? 0 }}</div>
            </div>
        </td>
    </tr>
</table>

{{-- Sección 1: Resumen institucional --}}
<div class="pdf-section">1. Resumen Institucional</div>

<table class="pdf-table pdf-table-adm">
    <thead>
        <tr>
            <th>Módulo / Catálogo</th>
            <th style="text-align:center;">Registros</th>
            <th style="text-align:center;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach([
            ['label' => 'Usuarios registrados',      'key' => 'usuarios_registrados'],
            ['label' => 'Usuarios activos',           'key' => 'usuarios_activos'],
            ['label' => 'Estudiantes registrados',    'key' => 'estudiantes_registrados'],
            ['label' => 'Estudiantes activos',        'key' => 'estudiantes_activos'],
            ['label' => 'Docentes',                   'key' => 'docentes_activos'],
            ['label' => 'Inscripciones',              'key' => 'inscripciones_totales'],
            ['label' => 'Asignaturas',                'key' => 'asignaturas'],
            ['label' => 'Cursos',                     'key' => 'cursos'],
            ['label' => 'Paralelos',                  'key' => 'paralelos'],
            ['label' => 'Turnos',                     'key' => 'turnos'],
            ['label' => 'Especialidades técnicas',    'key' => 'especialidades_tecnicas'],
            ['label' => 'Periodos de evaluación',     'key' => 'periodos_evaluacion'],
            ['label' => 'Instituciones de procedencia','key' => 'instituciones_procedencia'],
            ['label' => 'Tipos de vinculación',       'key' => 'tipos_vinculacion'],
        ] as $row)
        @php $val = $metricas[$row['key']] ?? 0; @endphp
        <tr>
            <td>{{ $row['label'] }}</td>
            <td style="text-align:center; font-weight:bold; color:{{ $val > 0 ? '#059669' : '#d97706' }};">{{ $val }}</td>
            <td style="text-align:center;">
                @if($val > 0)
                    <span class="badge badge-green">Activo</span>
                @else
                    <span class="badge badge-amber">Sin datos</span>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

{{-- Sección 2: Usuarios por Rol --}}
@if(!empty($usuarios_por_rol))
<div class="pdf-section">2. Distribución de Usuarios por Rol</div>
<table class="pdf-table pdf-table-adm">
    <thead>
        <tr>
            <th>Rol</th>
            <th style="text-align:center;">Usuarios</th>
            <th style="width:200px;">Distribución</th>
        </tr>
    </thead>
    <tbody>
        @php $maxRol = max(1, max(array_values($usuarios_por_rol))); @endphp
        @foreach($usuarios_por_rol as $rol => $cant)
        <tr>
            <td><strong>{{ $rol }}</strong></td>
            <td style="text-align:center; color:#0284c7; font-weight:bold;">{{ $cant }}</td>
            <td>
                <div class="bar-wrap"><div class="bar-fill bar-blue" style="width:{{ round(($cant / $maxRol) * 100) }}%;"></div></div>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Sección 3: Estudiantes por Curso --}}
@if(!empty($estudiantes_por_curso))
<div class="pdf-section">3. Distribución de Estudiantes por Curso</div>
<table class="pdf-table pdf-table-adm">
    <thead>
        <tr>
            <th>Curso</th>
            <th style="text-align:center;">Cantidad</th>
        </tr>
    </thead>
    <tbody>
        @foreach($estudiantes_por_curso as $item)
        <tr>
            <td>{{ $item['curso'] }}</td>
            <td style="text-align:center; color:#0284c7; font-weight:bold;">{{ $item['cantidad'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Sección 4: Inscripciones por Estado --}}
@if(!empty($estados_inscripcion))
<div class="pdf-section">4. Inscripciones por Estado</div>
<table class="pdf-table pdf-table-adm">
    <thead>
        <tr><th>Estado</th><th style="text-align:center;">Cantidad</th></tr>
    </thead>
    <tbody>
        @foreach($estados_inscripcion as $estado => $cant)
        <tr>
            <td>{{ $estado ?: 'Sin estado' }}</td>
            <td style="text-align:center; font-weight:bold;">{{ $cant }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- Diagnóstico --}}
<div class="pdf-section">5. Diagnóstico del Sistema</div>

@php $diagnostico = $diagnostico ?? ['estado' => 'Desconocido', 'completitud' => 0, 'advertencias' => []]; @endphp

<table style="width:100%; border:0; border-collapse:collapse;">
    <tr>
        <td style="width:50%; padding:4px;">
            <div class="kpi-card" style="border-color:#bae6fd; background:#f0f9ff;">
                <div class="kpi-label">Estado del Sistema</div>
                <div class="kpi-value kpi-blue">{{ $diagnostico['estado'] }}</div>
            </div>
        </td>
        <td style="width:50%; padding:4px;">
            <div class="kpi-card" style="border-color:#a7f3d0; background:#ecfdf5;">
                <div class="kpi-label">Completitud Estructural</div>
                <div class="kpi-value kpi-green">{{ $diagnostico['completitud'] }}%</div>
            </div>
        </td>
    </tr>
</table>

@if(!empty($diagnostico['advertencias']))
<div class="alert alert-warning" style="margin-top: 8px;">
    <strong>Módulos sin registros:</strong>
    {{ implode(', ', $diagnostico['advertencias']) }}
</div>
@else
<div class="alert alert-success" style="margin-top: 8px;">
    ✓ Todos los módulos evaluados contienen registros. Sistema en estado operativo.
</div>
@endif

<div class="alert alert-info" style="margin-top: 8px;">
    <strong>Observaciones administrativas:</strong> Este reporte refleja el estado actual del sistema SAVP-TIS3.
    Los datos son tomados en tiempo real al momento de la generación.
    Para mayor detalle de actividad, consulte el Reporte de Bitácora.
</div>

{!! app(\App\Services\Reportes\GeneradorMpdfService::class)->htmlFooter($sistema ?? 'SAVP-TIS3') !!}
