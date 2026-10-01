<h1 class="pdf-title">Reporte de grado asignado — SAVP-TIS3</h1>
<p>Gestión: {{ $row->gestionAcademica?->ani_gea }} · Grado: {{ $row->curso?->nom_cur }}</p>
<p>Asignatura: {{ $row->asignatura?->nom_asi }} · Paralelo: {{ $row->paralelo?->nom_par }} · Turno: {{ $row->turno?->nom_tur }}</p>
<p>Fecha de consulta: {{ $fecha->format('d/m/Y H:i') }}</p>
<table class="pdf-table"><thead><tr><th>Dato registrado</th><th>Valor</th></tr></thead><tbody>
<tr><td>Inscripciones vigentes en estas cuatro dimensiones</td><td>{{ $row->inscripciones_vigentes }}</td></tr>
<tr><td>Notas oficiales activas registradas</td><td>{{ $row->notas_registradas ?? 'No disponible' }}</td></tr>
<tr><td>Promedio de notas oficiales registradas</td><td>{{ $row->promedio_notas === null ? 'Sin datos disponibles' : number_format($row->promedio_notas, 2) }}</td></tr>
</tbody></table><p>Consulta limitada al grado y gestión asignados al Regente. No incluye notas históricas sin asignación académica conciliada ni calificaciones de tareas.</p>
