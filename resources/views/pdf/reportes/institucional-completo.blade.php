@php
/*
 * Reporte Institucional Completo
 * Motor: mPDF — documento largo con múltiples secciones
 */
$mpdfSvc = app(\App\Services\Reportes\GeneradorMpdfService::class);
$metricas = $metricas ?? [];
$calificaciones = $calificaciones ?? collect([]);
$distribucion = $distribucion ?? collect([]);
$compatibilidades = $compatibilidades ?? collect([]);
$resultados_especialidad = $resultados_especialidad ?? collect([]);
$bitacora = $bitacora ?? collect([]);
$usuarios_por_rol = $usuarios_por_rol ?? [];
$perfiles_riasec = $perfiles_riasec ?? [];
$diagnostico = $diagnostico ?? ['estado' => 'Desconocido', 'completitud' => 0, 'advertencias' => []];
@endphp

{!! $mpdfSvc->htmlHeader(get_defined_vars()) !!}

{{-- ════════════════════════════════════════════════════ --}}
{{-- PORTADA                                              --}}
{{-- ════════════════════════════════════════════════════ --}}
<div style="text-align:center; padding: 30px 0 20px 0;">
    <div style="font-size: 22pt; font-weight: bold; color: #059669; margin-bottom: 8px;">
        Reporte Institucional Completo
    </div>
    <div style="font-size: 12pt; color: #334155;">{{ $institucion ?? '' }}</div>
    <div style="font-size: 10pt; color: #64748b; margin-top: 4px;">{{ $sistema ?? '' }}</div>
    <div style="margin: 16px auto; width: 80px; height: 3px; background: #059669;"></div>
    <div style="font-size: 9pt; color: #64748b;">
        Generado el <strong>{{ now()->format('d \d\e F \d\e Y \a \l\a\s H:i') }}</strong>
    </div>
    <div style="font-size: 8pt; color: #94a3b8; margin-top: 4px;">Código: {{ $codigoReporte ?? '' }}</div>
</div>

{{-- ════════════════════════════════════════════════════ --}}
{{-- ÍNDICE                                               --}}
{{-- ════════════════════════════════════════════════════ --}}
<div class="pdf-section">Índice de Contenidos</div>
<table style="width:100%; border:0; border-collapse:collapse;">
    @foreach([
        ['1.', 'Resumen Ejecutivo'],
        ['2.', 'Datos Institucionales'],
        ['3.', 'Reporte Académico General'],
        ['4.', 'Reporte de Calificaciones'],
        ['5.', 'Estudiantes en Riesgo'],
        ['6.', 'Reporte Administrativo'],
        ['7.', 'Bitácora de Actividad'],
        ['8.', 'Reporte Vocacional RIASEC'],
        ['9.', 'Compatibilidad de Carreras'],
        ['10.', 'Observaciones y Recomendaciones'],
    ] as [$num, $titulo])
    <tr>
        <td style="width:30px; color:#059669; font-weight:bold; padding:3px 0;">{{ $num }}</td>
        <td style="padding:3px 0;">{{ $titulo }}</td>
    </tr>
    @endforeach
</table>

<div class="page-break"></div>

{{-- ════════════════════════════════════════════════════ --}}
{{-- 1. RESUMEN EJECUTIVO                                 --}}
{{-- ════════════════════════════════════════════════════ --}}
<div class="pdf-title">1. Resumen Ejecutivo</div>

<table style="width:100%; border:0; border-collapse:collapse; margin: 10px 0;">
    <tr>
        <td style="width:25%; padding:4px;"><div class="kpi-card"><div class="kpi-label">Usuarios Activos</div><div class="kpi-value kpi-blue">{{ $metricas['usuarios_activos'] ?? 0 }}</div></div></td>
        <td style="width:25%; padding:4px;"><div class="kpi-card"><div class="kpi-label">Estudiantes Activos</div><div class="kpi-value kpi-green">{{ $metricas['estudiantes_activos'] ?? 0 }}</div></div></td>
        <td style="width:25%; padding:4px;"><div class="kpi-card"><div class="kpi-label">Promedio General</div><div class="kpi-value kpi-violet">{{ $promedio_general ?? '0.00' }}</div></div></td>
        <td style="width:25%; padding:4px;"><div class="kpi-card"><div class="kpi-label">En Riesgo</div><div class="kpi-value kpi-red">{{ $en_riesgo ?? 0 }}</div></div></td>
    </tr>
</table>

<div class="alert alert-success">
    <strong>Estado del sistema:</strong> {{ $diagnostico['estado'] }} · Completitud estructural: {{ $diagnostico['completitud'] }}%
</div>

{{-- ════════════════════════════════════════════════════ --}}
{{-- 2. DATOS INSTITUCIONALES                             --}}
{{-- ════════════════════════════════════════════════════ --}}
<div class="pdf-subtitle">2. Datos Institucionales</div>

<table class="pdf-table">
    <tr><td style="width:200px;"><strong>Institución</strong></td><td>Unidad Educativa Técnico Humanístico "Franz Tamayo" N° 3</td></tr>
    <tr><td><strong>Sistema</strong></td><td>SAVP-TIS3 — Sistema Web de Orientación Académico-Vocacional</td></tr>
    <tr><td><strong>Fecha del reporte</strong></td><td>{{ now()->format('d/m/Y H:i:s') }}</td></tr>
    <tr><td><strong>Código</strong></td><td>{{ $codigoReporte ?? '' }}</td></tr>
    <tr><td><strong>Gestión</strong></td><td>{{ now()->format('Y') }}</td></tr>
</table>

<div class="page-break"></div>

{{-- ════════════════════════════════════════════════════ --}}
{{-- 3. REPORTE ACADÉMICO GENERAL                         --}}
{{-- ════════════════════════════════════════════════════ --}}
<div class="pdf-title">3. Reporte Académico General</div>

<table style="width:100%; border:0; border-collapse:collapse; margin: 10px 0;">
    <tr>
        <td style="width:25%; padding:4px;"><div class="kpi-card"><div class="kpi-label">Promedio General</div><div class="kpi-value kpi-green">{{ $promedio_general ?? '0.00' }}</div></div></td>
        <td style="width:25%; padding:4px;"><div class="kpi-card"><div class="kpi-label">Registros</div><div class="kpi-value kpi-blue">{{ $total_registros ?? 0 }}</div></div></td>
        <td style="width:25%; padding:4px;"><div class="kpi-card"><div class="kpi-label">Destacados</div><div class="kpi-value kpi-violet">{{ $destacados ?? 0 }}</div></div></td>
        <td style="width:25%; padding:4px;"><div class="kpi-card"><div class="kpi-label">En Riesgo</div><div class="kpi-value kpi-red">{{ $en_riesgo ?? 0 }}</div></div></td>
    </tr>
</table>

@if(isset($rendimiento_asignatura) && count($rendimiento_asignatura))
<table class="pdf-table">
    <thead><tr><th>Asignatura</th><th>Promedio</th><th>Registros</th><th>En Riesgo</th></tr></thead>
    <tbody>
        @foreach($rendimiento_asignatura as $item)
        <tr>
            <td>{{ $item['nombre'] }}</td>
            <td style="font-weight:bold; color:#059669;">{{ number_format($item['promedio'], 2) }}</td>
            <td>{{ $item['registros'] }}</td>
            <td style="color:#dc2626;">{{ $item['riesgo'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">Sin datos académicos.</div>
@endif

<div class="page-break"></div>

{{-- ════════════════════════════════════════════════════ --}}
{{-- 4. CALIFICACIONES                                    --}}
{{-- ════════════════════════════════════════════════════ --}}
<div class="pdf-title">4. Reporte de Calificaciones</div>

@if($calificaciones->count())
<table class="pdf-table">
    <thead>
        <tr><th>Estudiante</th><th>Asignatura</th><th>Periodo</th><th style="text-align:center;">Nota</th><th>Desempeño</th></tr>
    </thead>
    <tbody>
        @foreach($calificaciones->take(50) as $item)
        @php
        $persona = $item->estudiante?->persona;
        $nombre = trim(($persona?->nom_per ?? '') . ' ' . ($persona?->ape_pat_per ?? ''));
        $desemp = $item->desempeno ?? '—';
        @endphp
        <tr>
            <td>{{ $nombre ?: 'Sin nombre' }}</td>
            <td style="font-size:7.5pt;">{{ $item->asignatura?->nom_asi ?? '—' }}</td>
            <td style="font-size:7.5pt; color:#64748b;">{{ $item->periodoEvaluacion?->nom_pev ?? '—' }}</td>
            <td style="text-align:center; font-weight:bold; color:{{ $desemp === 'En riesgo' ? '#dc2626' : '#059669' }};">{{ number_format($item->not_cal, 2) }}</td>
            <td><span class="badge {{ $desemp === 'En riesgo' ? 'badge-red' : ($desemp === 'Destacado' ? 'badge-violet' : 'badge-green') }}">{{ $desemp }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
@if($calificaciones->count() > 50)
<div class="alert alert-info">Se muestran los primeros 50 registros. Descargue el Reporte de Calificaciones completo para ver todos.</div>
@endif
@else
<div class="pdf-empty">Sin calificaciones registradas.</div>
@endif

<div class="page-break"></div>

{{-- ════════════════════════════════════════════════════ --}}
{{-- 5. ESTUDIANTES EN RIESGO                             --}}
{{-- ════════════════════════════════════════════════════ --}}
<div class="pdf-title">5. Estudiantes en Riesgo</div>

@php $riesgoList = $estudiantes_riesgo ?? collect([]); @endphp

@if($riesgoList->count())
<table class="pdf-table pdf-table-danger">
    <thead>
        <tr><th>Estudiante</th><th>Especialidad</th><th>Promedio</th><th>Nivel</th></tr>
    </thead>
    <tbody>
        @foreach($riesgoList as $item)
        <tr>
            <td>{{ $item['nombre'] }}</td>
            <td>{{ $item['especialidad'] }}</td>
            <td style="font-weight:bold; color:#dc2626;">{{ number_format($item['promedio'], 2) }}</td>
            <td><span class="badge badge-red">{{ $item['nivel_riesgo'] }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="alert alert-success">✓ No se identificaron estudiantes en situación de riesgo.</div>
@endif

<div class="page-break"></div>

{{-- ════════════════════════════════════════════════════ --}}
{{-- 6. REPORTE ADMINISTRATIVO                            --}}
{{-- ════════════════════════════════════════════════════ --}}
<div class="pdf-title">6. Reporte Administrativo</div>

<table class="pdf-table pdf-table-adm">
    <thead><tr><th>Módulo</th><th style="text-align:center;">Registros</th></tr></thead>
    <tbody>
        @foreach([
            'usuarios_registrados' => 'Usuarios registrados',
            'estudiantes_activos' => 'Estudiantes activos',
            'docentes_activos' => 'Docentes',
            'inscripciones_totales' => 'Inscripciones',
            'asignaturas' => 'Asignaturas',
            'cursos' => 'Cursos',
            'paralelos' => 'Paralelos',
            'periodos_evaluacion' => 'Periodos de evaluación',
        ] as $key => $label)
        <tr>
            <td>{{ $label }}</td>
            <td style="text-align:center; font-weight:bold; color:#0284c7;">{{ $metricas[$key] ?? 0 }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="page-break"></div>

{{-- ════════════════════════════════════════════════════ --}}
{{-- 7. BITÁCORA                                          --}}
{{-- ════════════════════════════════════════════════════ --}}
<div class="pdf-title">7. Bitácora de Actividad Reciente</div>

@if($bitacora->count())
<table class="pdf-table pdf-table-adm">
    <thead>
        <tr><th>Fecha</th><th>Módulo</th><th>Acción</th><th>Estado</th></tr>
    </thead>
    <tbody>
        @foreach($bitacora->take(30) as $item)
        <tr>
            <td style="font-size:7.5pt; white-space:nowrap;">{{ $item->fec_bit?->format('d/m/Y H:i') }}</td>
            <td style="font-size:7.5pt; color:#0284c7;">{{ $item->mod_bit ?? 'General' }}</td>
            <td style="font-size:7.5pt;">{{ $item->acc_bit }}</td>
            <td><span class="badge {{ in_array($item->res_bit, ['FALLIDO', 'BLOQUEADO']) ? 'badge-red' : 'badge-green' }}">{{ $item->res_bit ?? 'OK' }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">Sin registros de bitácora.</div>
@endif

<div class="page-break"></div>

{{-- ════════════════════════════════════════════════════ --}}
{{-- 8. VOCACIONAL RIASEC                                 --}}
{{-- ════════════════════════════════════════════════════ --}}
<div class="pdf-title">8. Reporte Vocacional RIASEC</div>

<div class="alert alert-info">
    <strong>Perfil institucional:</strong> {{ $perfil_institucional ?? 'No determinado' }} ·
    {{ $interpretacion ?? 'Se requieren datos de especialidades técnicas con calificaciones.' }}
</div>

@if($resultados_especialidad->count())
<table class="pdf-table">
    <thead><tr><th>Especialidad</th><th>Perfil RIASEC</th><th>Promedio</th><th>Compatibilidad</th></tr></thead>
    <tbody>
        @foreach($resultados_especialidad as $item)
        <tr>
            <td>{{ $item['especialidad'] }}</td>
            <td style="font-size:12pt; font-weight:bold; color:#7c3aed;">{{ $item['perfil_texto'] }}</td>
            <td style="font-weight:bold; color:#059669;">{{ number_format($item['promedio'], 2) }}</td>
            <td><span class="badge badge-violet">{{ $item['compatibilidad'] }}%</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">Sin datos vocacionales disponibles.</div>
@endif

<div class="page-break"></div>

{{-- ════════════════════════════════════════════════════ --}}
{{-- 9. COMPATIBILIDAD DE CARRERAS                        --}}
{{-- ════════════════════════════════════════════════════ --}}
<div class="pdf-title">9. Compatibilidad de Carreras</div>

@if($compatibilidades->count())
<table class="pdf-table">
    <thead><tr><th>Especialidad</th><th>Área</th><th>Compatibilidad</th><th>Riesgo</th><th>Carreras</th></tr></thead>
    <tbody>
        @foreach($compatibilidades as $item)
        <tr>
            <td>{{ $item['especialidad'] }}</td>
            <td style="color:#0284c7; font-size:7.5pt;">{{ $item['area_profesional'] }}</td>
            <td><span class="badge {{ $item['compatibilidad_pct'] >= 80 ? 'badge-green' : 'badge-amber' }}">{{ $item['compatibilidad_pct'] }}%</span></td>
            <td><span class="badge {{ $item['riesgo_academico'] === 'Bajo' ? 'badge-green' : 'badge-red' }}">{{ $item['riesgo_academico'] }}</span></td>
            <td style="font-size:7.5pt;">{{ implode(', ', array_slice($item['carreras'] ?? [], 0, 2)) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@else
<div class="pdf-empty">Sin datos de compatibilidad disponibles.</div>
@endif

{{-- ════════════════════════════════════════════════════ --}}
{{-- 10. OBSERVACIONES Y RECOMENDACIONES                  --}}
{{-- ════════════════════════════════════════════════════ --}}
<div class="pdf-title">10. Observaciones Institucionales y Recomendaciones</div>

<div class="alert alert-info">
    <strong>Observaciones académicas:</strong> El sistema SAVP-TIS3 ha procesado los datos académicos, administrativos y vocacionales
    disponibles a la fecha de generación. Se recomienda revisar periódicamente el módulo de reportes para mantener un seguimiento actualizado del desempeño institucional.
</div>

<div class="alert alert-success" style="margin-top: 8px;">
    <strong>Recomendaciones:</strong>
    <br>• Implementar seguimiento personalizado para estudiantes en riesgo académico.
    <br>• Reforzar la orientación vocacional basada en el perfil RIASEC institucional.
    <br>• Mantener actualizada la bitácora de actividad para garantizar la trazabilidad.
    <br>• Revisar mensualmente los indicadores de desempeño por asignatura y periodo.
</div>

<div class="alert alert-warning" style="margin-top: 8px;">
    <strong>Nota técnica:</strong> Este reporte fue generado automáticamente por SAVP-TIS3.
    Los datos son reales al momento de la generación. No se deben considerar como evaluación definitiva sin revisión docente.
</div>

{!! $mpdfSvc->htmlFooter($sistema ?? 'SAVP-TIS3') !!}
