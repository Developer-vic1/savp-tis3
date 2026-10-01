<h1 class="pdf-title">Consolidado de curso — SAVP-TIS3</h1>
<p>Curso: {{ $reporte['curso']->nom_cla }}</p>
<p>Asignatura: {{ $reporte['curso']->planAsignatura?->asignatura?->nom_asi ?? 'Sin asignatura registrada' }}</p>
<p>Gestión: {{ $reporte['curso']->planAsignatura?->gestionAcademica?->ani_gea ?? 'Sin gestión registrada' }}</p>
<p>Fecha de consulta: {{ $fecha->format('d/m/Y H:i') }}</p>
<table class="pdf-table"><thead><tr><th>Registro</th><th>Cantidad</th></tr></thead><tbody>
<tr><td>Estudiantes con vínculo e inscripción vigentes</td><td>{{ $reporte['estudiantes'] }}</td></tr>
<tr><td>Materiales publicados</td><td>{{ $reporte['materiales'] }}</td></tr>
<tr><td>Tareas publicadas o cerradas</td><td>{{ $reporte['tareas'] }}</td></tr>
<tr><td>Sesiones de asistencia cerradas</td><td>{{ $reporte['asistencias'] }}</td></tr>
</tbody></table>
<p>Estos conteos corresponden al curso autorizado del docente. No representan calificaciones oficiales, alertas disciplinarias ni un diagnóstico vocacional.</p>
