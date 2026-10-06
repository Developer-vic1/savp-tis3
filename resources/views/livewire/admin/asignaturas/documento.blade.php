<section class="ui-card-soft p-5">
    <h3 class="ui-title font-bold">1. Respaldo de la decisión</h3>
    <p class="ui-muted text-sm mt-2">{{ $operacionCurricular==='crear'?'Incorporación para la siguiente gestión':'Corrección documentada de la oferta existente' }} · gestión {{ $gestionDocumentoCurricular ?: 'sin gestión activa única' }}.</p>
    <p class="ui-muted text-sm mt-2">Director vigente: <strong>{{ $directorCurricular ?: 'No existe una autoridad única configurada' }}</strong>.</p>

    <div class="mt-4"><x-lector-documento-institucional modelo="documentoCurricular" accion="escanearDocumentoCurricular" identificador="curricular-pdf" :aceptado="$revisionDocumentoCurricular['coherente']??false" :revision="$revisionDocumentoCurricular" /></div>
    <x-plegable-institucional class="mt-3" etiqueta="Qué debe contener el documento" descripcion="Comprueba estos datos antes de cargar el PDF." icono="ph-list-checks" :abierto="false">
        <ul class="text-sm space-y-2 ui-muted"><li>Unidad educativa Franz Tamayo N° 3; cargo y nombre completo del Director vigente.</li><li>Referencia y fecha de emisión. Campos explícitos: «Referencia: …», «Fecha: AAAA-MM-DD» y «Gestión: {{ $gestionDocumentoCurricular }}».</li>
        @if($this instanceof \App\Livewire\Admin\EspecialidadesTecnicas)
            <li>Campo obligatorio «Especialidad: Sistemas Informáticos» (ejemplo). Debe identificar una oferta técnica, no una materia. Una carta con «Asignatura: …» o «Materia: …» será rechazada en este módulo.</li>
        @else
            <li>«Asignatura: …», «Sigla: …» y «Horas académicas: …».</li>
        @endif
        <li>«Fundamento: …», con la necesidad educativa real. La decisión debe autorizar expresamente la {{ $operacionCurricular==='crear'?'incorporación':'modificación' }} de esa oferta.</li>@if($operacionCurricular==='editar')<li>Identifica el dato anterior y el nuevo: «Nombre anterior: …», «Sigla anterior: …» u «Horas anteriores: …».</li>@endif<li>Firma y sello visibles. Escribir las palabras «firma» o «sello» no acredita su existencia.</li></ul>
    </x-plegable-institucional>
    @if(!empty($revisionDocumentoCurricular['comparacion']['error']))<p class="mt-3 text-sm" style="color:var(--ui-danger)" role="alert">{{ $revisionDocumentoCurricular['comparacion']['error'] }}</p>@endif
    <p class="mt-3 text-xs ui-muted">Lectura de texto y comparación de imágenes; no certifica autenticidad. Los campos explícitos indicados son requisitos de extracción de SAVP.</p>
</section>
