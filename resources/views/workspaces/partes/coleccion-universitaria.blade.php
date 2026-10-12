<section id="coleccion-universitaria" class="ui-card card-shadow rounded-[2rem] p-5 sm:p-6"
    x-data="coleccionUniversitaria({ url: @js(route('conocimiento.unifranz.coleccion')), analysisUrl: @js(route('conocimiento.unifranz.analizar')), csrf: @js(csrf_token()), canCollect: @js(auth()->user()->can('conocimiento.revisar')) })"
    :aria-busy="busy">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="max-w-3xl">
            <p class="ui-kicker">Colección documental oficial</p>
            <h2 class="ui-title mt-2 text-xl font-black sm:text-2xl">Extraer carreras y mallas de UNIFRANZ</h2>
            <p class="ui-muted mt-2 text-sm leading-6">El Aporte Ingenieril descubre las fichas publicadas, abre sus mallas y guarda la extracción con su fuente. Revisa aquí qué logró leer y qué necesita atención antes de incorporarlo al estudio vocacional.</p>
        </div>
        <span class="shrink-0" :class="badgeClass(collection?.status)" x-text="statusLabel(collection?.status || 'SIN_INICIAR')"></span>
    </div>

    <ol class="mt-5 grid gap-3 md:grid-cols-3" aria-label="Proceso de extracción">
        <li class="ui-card-soft flex gap-3 rounded-2xl p-4"><i class="ph-duotone ph-magnifying-glass mt-1 text-xl" style="color: var(--ui-primary)" aria-hidden="true"></i><div><p class="font-bold">1. Descubrir carreras</p><p class="ui-muted mt-1 text-sm">Recorrer el catálogo oficial y conservar el enlace de cada ficha.</p></div></li>
        <li class="ui-card-soft flex gap-3 rounded-2xl p-4"><i class="ph-duotone ph-file-pdf mt-1 text-xl" style="color: var(--ui-primary)" aria-hidden="true"></i><div><p class="font-bold">2. Leer las mallas</p><p class="ui-muted mt-1 text-sm">Extraer los documentos disponibles y registrar límites de lectura.</p></div></li>
        <li class="ui-card-soft flex gap-3 rounded-2xl p-4"><i class="ph-duotone ph-hard-drives mt-1 text-xl" style="color: var(--ui-primary)" aria-hidden="true"></i><div><p class="font-bold">3. Guardar para revisión</p><p class="ui-muted mt-1 text-sm">Conservar contenido y trazabilidad para su revisión institucional.</p></div></li>
    </ol>

    <div class="ui-alert-info mt-4 text-sm"><strong>Guardado local y pendiente de revisión.</strong> Extraer no publica estas carreras para estudiantes. La cobertura refleja lo encontrado en la web oficial; las sedes o mallas ausentes requieren verificación.</div>
    <div x-show="collection?.status === 'PARCIAL' && !pendingDownloads" x-cloak x-transition:enter="motion-safe:transition-opacity motion-safe:duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="motion-safe:transition-opacity motion-safe:duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="ui-alert-warning mt-4 text-sm motion-reduce:transition-none"><strong>Reconocer texto de páginas escaneadas.</strong> Los documentos ya están guardados. Prepara la lectura de sus imágenes para revisar las materias; no necesitas descargarlos de nuevo.</div>
    <div class="mt-4 flex flex-wrap items-center gap-3">
        @can('conocimiento.revisar')
            <button type="button" class="ui-btn-primary min-h-[44px]" @click="start()" :disabled="!canStart"><i class="ph-duotone" :class="starting || running ? 'ph-circle-notch animate-spin motion-reduce:animate-none' : (collection?.status === 'PARCIAL' && !pendingDownloads ? 'ph-text-align-left' : 'ph-download-simple')" aria-hidden="true"></i><span x-text="actionLabel"></span></button>
            <button type="button" class="ui-btn-secondary min-h-[44px]" @click="analyze()" :disabled="!canAnalyze"><i class="ph-duotone ph-folders" aria-hidden="true"></i><span x-text="analyzing ? 'Organizando la malla…' : 'Analizar mallas guardadas'"></span></button>
        @else
            <p class="ui-muted text-sm">La extracción requiere permiso de revisión del conocimiento.</p>
        @endcan
        <button type="button" class="ui-btn-secondary min-h-[44px]" @click="refresh()" :disabled="loading || starting || analyzing"><i class="ph-duotone ph-arrows-clockwise" aria-hidden="true"></i><span x-text="loading ? 'Consultando estado…' : 'Consultar estado guardado'"></span></button>
        <a x-show="safeUrl(collection?.source_url)" x-cloak :href="safeUrl(collection?.source_url)" class="ui-btn-secondary min-h-[44px]" target="_blank" rel="noopener noreferrer"><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i> Catálogo oficial</a>
    </div>
    <div x-show="busy" x-cloak class="cu-lectura mt-4" role="status" aria-live="polite">
        <span class="cu-escaneo" aria-hidden="true"><i class="ph-duotone ph-file-pdf"></i></span>
        <div><p class="font-bold" x-text="phaseLabel"></p><p class="ui-muted mt-1 text-sm">Reconocer → Analizar → Organizar. El avance se confirma desde el servicio; conservamos tu colección mientras trabaja.</p></div>
    </div>

    <div x-show="error" x-cloak x-transition:enter="motion-safe:transition-opacity motion-safe:duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="motion-safe:transition-opacity motion-safe:duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="ui-alert-warning mt-4 motion-reduce:transition-none" role="alert"><p class="font-bold">La consulta necesita atención</p><p class="mt-1 text-sm" x-text="error"></p><p x-show="collection" class="mt-2 text-sm">Conservamos el último resultado recibido. Consulta el estado para comprobar si la extracción continuó.</p></div>
    <div x-show="downloadNotice" x-cloak x-transition:enter="motion-safe:transition-opacity motion-safe:duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="motion-safe:transition-opacity motion-safe:duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="ui-alert-info mt-4 text-sm motion-reduce:transition-none" role="status"><p><strong>Descarga solicitada:</strong> <span x-text="downloadNotice"></span>. El navegador gestiona el archivo; revisa su sección Descargas para comprobar que terminó.</p><button type="button" class="ui-btn-secondary mt-2 min-h-[44px]" @click="downloadNotice = ''">Cerrar aviso</button></div>
    <div x-show="running" x-cloak x-transition:enter="motion-safe:transition-opacity motion-safe:duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="motion-safe:transition-opacity motion-safe:duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="ui-card-soft mt-4 rounded-2xl p-4 motion-reduce:transition-none" role="status" aria-live="polite"><p class="font-bold">El servicio sigue trabajando</p><p class="ui-muted mt-1 text-sm"><span x-text="collection?.progress?.completed ?? 0"></span> tareas terminadas de <span x-text="collection?.progress?.total ?? 'total aún no informado'"></span>. <span x-text="humanText(collection?.progress?.current) || 'Esperando el siguiente avance del servicio.'"></span></p><p class="ui-muted mt-1 text-xs">Se consulta el avance cada 4 segundos. Puedes salir y recuperar el estado guardado al volver.</p></div>

    <template x-if="collection">
        <div class="mt-5 space-y-4">
            <dl class="grid gap-3 sm:grid-cols-3">
                <div class="ui-card-soft rounded-2xl p-4"><dt class="ui-muted text-sm">Carreras encontradas</dt><dd class="mt-1 text-2xl font-black" x-text="collection.statistics?.careers ?? collection.careers.length"></dd></div>
                <div class="ui-card-soft rounded-2xl p-4"><dt class="ui-muted text-sm">Mallas encontradas</dt><dd class="mt-1 text-2xl font-black" x-text="collection.statistics?.curricula ?? 'No informado'"></dd></div>
                <div class="ui-card-soft rounded-2xl p-4"><dt class="ui-muted text-sm">Documentos extraídos</dt><dd class="mt-1 text-2xl font-black" x-text="collection.statistics?.extracted_documents ?? 'No informado'"></dd></div>
            </dl>
            <p class="ui-muted text-xs">Última confirmación: <span x-text="formatDate(collection.updated_at)"></span> · Bolivia</p>
            <div x-show="collection.warnings?.length" class="ui-alert-warning"><p class="font-bold">Aspectos pendientes del recorrido</p><ul class="mt-2 list-disc space-y-1 pl-5 text-sm"><template x-for="(warning, index) in collection.warnings || []" :key="index"><li x-text="humanText(warning)"></li></template></ul></div>
            <fieldset>
                <legend class="ui-label">Revisar oferta publicada por sede</legend>
                <div class="mt-3 flex flex-wrap gap-2"><template x-for="filter in campusFilters" :key="filter.value"><button type="button" class="min-h-[44px]" :class="campusFilter === filter.value ? 'ui-btn-primary' : 'ui-btn-secondary'" :aria-pressed="campusFilter === filter.value" @click="campusFilter = filter.value"><span x-text="filter.label"></span><span class="font-bold" x-text="filter.count"></span></button></template></div>
                <p class="ui-muted mt-2 text-sm">Los conteos por sede incluyen únicamente carreras con oferta identificada en una fuente oficial. «No indicada» describe ese catálogo consultado; no confirma que la carrera nunca se ofrezca allí.</p>
            </fieldset>
            <label class="block"><span class="ui-label">Buscar dentro de la oferta seleccionada</span><input type="search" x-model="search" class="ui-input mt-2 w-full" placeholder="Por ejemplo: Medicina o Psicología"></label>
            <p class="ui-muted text-sm" x-show="!collection.careers.length">Todavía no hay fichas guardadas. Inicia la extracción para descubrir las carreras publicadas por UNIFRANZ.</p>
            <p class="ui-muted text-sm" x-show="collection.careers.length && !filteredCareers.length">No hay carreras con estos filtros y evidencia disponible. Prueba otro nombre o revisa todas las carreras y las sedes pendientes de verificar.</p>
            <div class="space-y-3">
                <template x-for="(career, careerIndex) in filteredCareers" :key="career.url || careerIndex">
                    <x-plegable-institucional icono="ph-graduation-cap" :compacto="true">
                        <x-slot:titulo><span x-text="career.name"></span></x-slot:titulo>
                        <x-slot:contador><span :class="badgeClass(career.status)" x-text="statusLabel(career.status)"></span></x-slot:contador>
                        <div class="space-y-4">
                            <div class="flex flex-wrap gap-2 text-sm"><span class="ui-badge-info" x-text="confirmedCampuses(career).length ? 'Oferta publicada: ' + confirmedCampuses(career).join(' · ') : 'Sedes pendientes de verificación'"></span><span class="ui-badge-muted" x-text="career.duration || 'Duración no informada'"></span></div>
                            <x-plegable-institucional x-show="career.campus_availability?.length || career.campus_evidence?.source_url" icono="ph-map-pin" :compacto="true">
                                <x-slot:titulo>Comprobar oferta por sede</x-slot:titulo>
                                <ul class="space-y-3"><template x-for="(availability, availabilityIndex) in career.campus_availability || []" :key="availability.campus || availabilityIndex"><li><span :class="availability.status === 'OFERTADA' ? 'ui-badge-success' : 'ui-badge-muted'" x-text="campusAvailabilityLabel(availability)"></span><p class="ui-muted mt-1 text-sm" x-text="campusEvidenceExplanation(availability)"></p><a x-show="safeUrl(availability.source_url)" :href="safeUrl(availability.source_url)" class="ui-btn-secondary mt-2 min-h-[44px]" target="_blank" rel="noopener noreferrer">Ver ficha que respalda este estado <i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a></li></template></ul>
                                <p x-show="career.campus_evidence?.retrieved_at" class="ui-muted mt-3 text-xs">Oferta consultada: <span x-text="formatDate(career.campus_evidence?.retrieved_at)"></span> · Bolivia</p>
                            </x-plegable-institucional>
                            <a x-show="safeUrl(career.url)" :href="safeUrl(career.url)" class="ui-btn-secondary min-h-[44px]" target="_blank" rel="noopener noreferrer"><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i> Ver ficha oficial</a>
                            <a x-show="reviewUrl(career.url)" :href="reviewUrl(career.url)" class="ui-btn-secondary min-h-[44px]"><i class="ph-duotone ph-clipboard-text" aria-hidden="true"></i> Revisar esta fuente</a>
                            <x-plegable-institucional x-show="career.summary" icono="ph-student" :compacto="true">
                                <x-slot:titulo>Perfil publicado de esta carrera</x-slot:titulo>
                                <p class="ui-muted whitespace-pre-line text-sm leading-6" x-text="career.summary"></p>
                            </x-plegable-institucional>
                            <ul x-show="career.warnings?.length" class="ui-muted list-disc space-y-1 pl-5 text-sm"><template x-for="(warning, index) in career.warnings || []" :key="index"><li x-text="humanText(warning)"></li></template></ul>
                            <p class="ui-muted text-sm" x-show="!career.curricula?.length">No se identificó una malla descargable en esta ficha. Revisa la fuente oficial antes de completar sus materias.</p>
                            <template x-for="(curriculum, curriculumIndex) in career.curricula || []" :key="curriculum.url || curriculumIndex">
                                <article class="ui-card-soft min-w-0 rounded-xl p-4">
                                    <div class="flex flex-wrap items-start justify-between gap-2"><h3 class="font-bold" x-text="documentTitle(career)"></h3><span :class="badgeClass(curriculum.status)" x-text="statusLabel(curriculum.status)"></span></div>
                                    <p class="ui-muted mt-2 text-sm" x-text="curriculumCampusLabel(curriculum)"></p>
                                    <p class="ui-muted mt-3 text-sm leading-6" x-text="structureSummary(curriculum)"></p>
                                    <p x-show="curriculum.curricular_structure?.pending_regions" class="ui-alert-warning mt-3 text-sm"><span x-text="curriculum.curricular_structure?.pending_regions"></span> nombres todavía requieren comprobarse en el documento original. Conservamos las materias que sí pudimos organizar.</p>
                                    <div x-show="semesters(curriculum).length" class="mt-4">
                                        <div class="cu-semestres" role="group" :aria-label="'Semestres de ' + career.name">
                                            <template x-for="semester in semesters(curriculum)" :key="semester.number">
                                                <button type="button" class="cu-carpeta" @click="selectSemester(curriculum, semester.number)" :aria-pressed="selectedSemester(curriculum)?.number === semester.number">
                                                    <i class="ph-duotone" :class="selectedSemester(curriculum)?.number === semester.number ? 'ph-folder-open' : 'ph-folder'" aria-hidden="true"></i>
                                                    <span class="font-bold" x-text="'Semestre ' + semester.number"></span>
                                                    <span class="ui-muted text-sm" x-text="subjectCountLabel(semester.subjects.length)"></span>
                                                </button>
                                            </template>
                                        </div>
                                        <div class="mt-4" aria-live="polite">
                                            <h4 class="font-bold" x-text="'Materias del semestre ' + (selectedSemester(curriculum)?.number || '')"></h4>
                                            <p class="ui-muted mt-1 text-sm">Confirma estos nombres y su semestre con la malla oficial antes de incorporarlos a un plan.</p>
                                            <ul class="cu-materias mt-3"><template x-for="(subject, subjectIndex) in selectedSemester(curriculum)?.subjects || []" :key="subject.name + subjectIndex"><li class="cu-materia"><i class="ph-duotone ph-book-open" aria-hidden="true"></i><span x-text="subject.name"></span></li></template></ul>
                                            <p x-show="selectedSemester(curriculum) && !selectedSemester(curriculum).subjects.length" class="ui-muted mt-3 text-sm">Este semestre está identificado, pero sus materias todavía necesitan revisión.</p>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <a x-show="safeUrl(curriculum.url)" :href="safeUrl(curriculum.url)" class="ui-btn-secondary min-h-[44px]" target="_blank" rel="noopener noreferrer"><i class="ph-duotone ph-file-pdf" aria-hidden="true"></i> Abrir documento oficial</a>
                                        <a x-show="downloadUrl(curriculum)" :href="downloadUrl(curriculum)" download class="ui-btn-primary min-h-[44px]" @click="downloadNotice = documentTitle(career)"><i class="ph-duotone ph-download-simple" aria-hidden="true"></i> Descargar al ordenador</a>
                                    </div>
                                    <p x-show="downloadUrl(curriculum)" class="ui-muted mt-2 text-xs">Se descarga la copia guardada de esta malla. El navegador la coloca en Descargas o en la carpeta que elijas.</p>
                                    <a x-show="reviewUrl(curriculum.url)" :href="reviewUrl(curriculum.url)" class="ui-btn-secondary mt-3 min-h-[44px]"><i class="ph-duotone ph-clipboard-text" aria-hidden="true"></i> Revisar esta fuente</a>
                                    <x-plegable-institucional class="mt-4" icono="ph-file-magnifying-glass" :compacto="true">
                                        <x-slot:titulo>Comprobar documento original</x-slot:titulo>
                                        <p class="ui-muted text-sm leading-6" x-text="readingSummary(curriculum)"></p>
                                        <ul x-show="curriculum.warnings?.length" class="ui-muted mt-2 list-disc space-y-1 pl-5 text-sm"><template x-for="(warning, index) in curriculum.warnings || []" :key="index"><li x-text="humanText(warning)"></li></template></ul>
                                        <p x-show="curriculum.excerpt" class="ui-muted mt-3 max-h-80 overflow-auto whitespace-pre-line text-sm leading-6" x-text="curriculum.excerpt"></p>
                                    </x-plegable-institucional>
                                </article>
                            </template>
                        </div>
                    </x-plegable-institucional>
                </template>
            </div>
        </div>
    </template>
</section>
