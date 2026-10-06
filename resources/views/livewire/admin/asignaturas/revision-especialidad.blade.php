<section class="especialidades-revision mt-5" aria-live="polite">
    <p class="ui-kicker">Revisión de la oferta técnica</p>
    <h3 class="ui-title font-bold mt-2">{{ $analisis['sugerencias']['nombre'] ?? 'Comprueba la denominación del plan' }}</h3>
    <div class="mt-3">
        <span class="{{ ($analisis['sugerencias']['reconocida']??false)?'ui-badge-success':'ui-badge-warning' }}">
            {{ match($analisis['sugerencias']['tipo']??'') {'especialidad'=>'Denominación técnica reconocida','asignatura'=>'Materia curricular: no corresponde a este módulo',default=>'Identificación pendiente'} }}
        </span>
    </div>
    <dl class="especialidades-revision-datos mt-4">
        <div><dt>Familia técnica orientativa</dt><dd>{{ $analisis['sugerencias']['familia']??'Pendiente de lectura' }}</dd></div>
        <div><dt>Campo educativo</dt><dd>{{ $analisis['sugerencias']['area']??'Pendiente de lectura' }}</dd></div>
    </dl>
    <p class="ui-muted text-sm mt-3">{{ $analisis['sugerencias']['descripcion']??'' }}</p>
    @foreach($analisis['bloqueos']??[] as $bloqueo)<p class="ui-alert-danger mt-3">{{ $bloqueo }}</p>@endforeach
    <p class="ui-muted text-xs mt-3">{{ $analisis['sugerencias']['fuente']??'' }} El alcance profesional y las carreras relacionadas requieren el contenido del plan aprobado.</p>
</section>
