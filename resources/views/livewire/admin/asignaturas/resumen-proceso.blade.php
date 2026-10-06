<aside class="curricular-guia ui-card-soft">
    <span class="curricular-guia-icono"><i class="ph-duotone ph-shield-check" aria-hidden="true"></i></span>
    <p class="ui-kicker mt-4">Tu proceso</p>
    <h3 class="ui-title text-xl font-bold mt-2" x-text="fase===1?'Empieza por el documento':fase===2?'Comprueba lo autorizado':'Confirma antes de guardar'"></h3>
    <p class="ui-muted mt-3" x-text="fase===1?'Carga la carta completa. El sistema leerá los datos y comparará la firma y el sello registrados.':fase===2?'Los datos vienen del documento aprobado. Revisa el nombre y su alcance antes de continuar.':'Explica el alcance y cómo contrastaste la autorización con Dirección.'"></p>
    <dl class="curricular-guia-datos">
        <div><dt>Gestión de aplicación</dt><dd>{{ $gestionDocumentoCurricular ?: 'Por confirmar' }}</dd></div>
        <div><dt>Autoridad vigente</dt><dd>{{ $directorCurricular ?: 'Sin Director único' }}</dd></div>
        <div><dt>Lectura del respaldo</dt><dd>{{ ($revisionDocumentoCurricular['coherente']??false)?'Contenido revisado':($revisionDocumentoCurricular?'Requiere corrección':'Pendiente de cargar PDF') }}</dd></div>
    </dl>
    <p class="ui-muted text-xs mt-4">Cada lectura rechazada queda en bitácora. La autenticidad se comprueba con la autoridad emisora.</p>
</aside>
