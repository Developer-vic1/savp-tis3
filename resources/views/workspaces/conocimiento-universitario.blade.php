@extends('layouts.app')

@section('title', 'Gestión del conocimiento')

@php
    $overview = $result->available ? $result->data : [];
    $sources = data_get($overview, 'sources', []);
    $proposals = data_get($overview, 'proposals', []);
    $trustedUniversities = data_get($overview, 'trusted_universities', []) ?: data_get($soporteFuente ?? [], 'universidades', []);
    $pending = collect($proposals)->whereIn('status', ['PENDIENTE_REVISION', 'PENDIENTE_VERIFICACION_DE_DOMINIO'])->count();
@endphp

@section('content')
    <div class="knowledge-governance space-y-6"
        @keydown.window="trapDialog($event)"
        x-data="knowledgeGovernance({
            analysisUrl: @js(route('conocimiento.fuentes.analizar')),
            previewUrl: @js(route('conocimiento.fuentes.comprobar')),
            connectionUrl: @js(route('conocimiento.conexion')),
            tutorUrl: @js(route('conocimiento.tutor.probar')),
            usuarioNombre: @js($usuarioNombre ?? ''),
            canReview: @js($canReview),
            updatesUrl: @js(route('conocimiento.actualizaciones.index')),
            updatesConfigureUrl: @js(route('conocimiento.actualizaciones.configurar')),
            updatesReviewUrl: @js(route('conocimiento.actualizaciones.revisar')),
            canPropose: @js($canPropose),
            initialSource: @js($fuenteInicial ?? []),
            csrf: @js(csrf_token()),
            trustedDomains: @js(collect($trustedUniversities)->pluck('domain')->values()),
            trustedInstitutions: @js(collect($trustedUniversities)->pluck('institution')->values()),
            universities: @js($trustedUniversities),
            fieldConstraints: @js(data_get($soporteFuente ?? [], 'campos', [])),
            sources: @js($sources),
            directorySummaryUrl: @js(route('conocimiento.directorio-resumen')),
            directoryUrl: @js(route('conocimiento.directorio-nacional')),
            serviceAvailable: @js($result->available),
            reviewUrlTemplate: @js(route('conocimiento.fuentes.revisar', ['proposalId' => '__PROPOSAL__'])),
            extractionUrlTemplate: @js(route('conocimiento.fuentes.extraer', ['proposalId' => '__PROPOSAL__'])),
        })">
        <section class="ui-card card-shadow rounded-[2rem] p-5 sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <div class="mb-2 flex items-center gap-3"><x-avatar-documental :compacto="true" /><span class="ui-muted text-sm">Documentos que ayudan a orientar a nuestros estudiantes</span></div>
                    <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-primary);">Aporte Ingenieril · {{ $actor }}</p>
                    <h1 class="ui-title mt-2 text-2xl font-black sm:text-3xl">Gestión del conocimiento</h1>
                    <p class="ui-muted mt-3">Analiza páginas universitarias bolivianas antes de incorporarlas. El sistema revisa dominio, institución, finalidad académica, formato, fecha y duplicidad; cada fuente necesita revisión antes de incorporarse.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('conocimiento.directorio-nacional') }}" class="ui-btn-secondary"><i class="ph-duotone ph-map-trifold" aria-hidden="true"></i> Directorio nacional</a>
                    <button type="button" class="ui-btn-secondary" @click="tutorOpen = true"><i class="ph-duotone ph-chats" aria-hidden="true"></i> Asistente documental</button>
                    <button type="button" class="ui-btn-secondary" @click="helpOpen = true"><i class="ph-duotone ph-question" aria-hidden="true"></i> Cómo funciona</button>
                    <div class="flex flex-wrap gap-2" role="group" aria-label="Forma de explicar la información"><button type="button" class="min-h-[44px]" :class="!technicalDetail ? 'ui-btn-primary' : 'ui-btn-secondary'" :aria-pressed="!technicalDetail" @click="languageMode = 'CLARO'">Lenguaje claro</button><button type="button" class="min-h-[44px]" :class="technicalDetail ? 'ui-btn-primary' : 'ui-btn-secondary'" :aria-pressed="technicalDetail" @click="languageMode = 'TECNICO'">Detalle técnico</button></div>
                </div>
            </div>
            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                <article class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs font-semibold uppercase tracking-wider">Fuentes activas</p><p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">{{ $result->available ? data_get($overview, 'source_count', 'No disponible') : 'No disponible' }}</p></article>
                <article class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs font-semibold uppercase tracking-wider">En revisión</p><p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">{{ $result->available ? $pending : 'No disponible' }}</p></article>
                <article class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs font-semibold uppercase tracking-wider">Dominios web contrastados</p><p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">{{ $result->available ? count($trustedUniversities) : 'No disponible' }}</p><p class="ui-muted mt-1 text-xs">Identifican sitios; no acreditan vigencia universitaria.</p></article>
            </div>
            <div class="mt-5 flex flex-wrap gap-2 text-xs">
                <button type="button" class="min-h-[44px]" :class="checkingConnection ? 'ui-badge-info' : connection?.connected && !connectionError ? 'ui-badge-success' : 'ui-badge-warning'" @click="connectionOpen = true" :aria-busy="checkingConnection"><i class="ph-duotone" :class="checkingConnection ? 'ph-circle-notch animate-spin motion-reduce:animate-none' : 'ph-plugs-connected'" aria-hidden="true"></i><span x-text="connectionLabel">Verificando el servicio documental…</span></button>
                <span class="ui-badge-info"><i class="ph-duotone ph-shield-check" aria-hidden="true"></i> Revisión obligatoria</span>
                <span class="ui-badge-muted"><i class="ph-duotone ph-database" aria-hidden="true"></i> Incorporación supervisada</span>
                <span class="ui-badge-info"><i class="ph-duotone ph-books" aria-hidden="true"></i> Biblioteca universitaria y normativa</span>
            </div>
        </section>

        <section class="ui-card card-shadow rounded-[2rem] p-5 sm:p-6" aria-label="Progreso de incorporación">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <template x-for="step in workflowSteps" :key="step.number">
                    <article class="kg-step" :class="step.state">
                        <span class="kg-step-number" x-text="step.number"></span>
                        <div>
                            <p class="font-black" x-text="step.title"></p>
                            <p class="ui-muted mt-1 text-xs leading-5" x-text="step.help"></p>
                        </div>
                    </article>
                </template>
            </div>
        </section>

        @if ($errors->any())
            <section class="ui-alert-warning" role="alert">
                <p class="font-bold">Revise el formulario</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </section>
        @endif

        <div class="grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1.55fr)_minmax(16rem,0.65fr)]">
            <section class="ui-card card-shadow rounded-[2rem] p-6 sm:p-8">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="ui-kicker">Nueva fuente</p>
                        <h2 class="ui-title mt-2 text-2xl font-black">Ficha documental</h2>
                        <p class="ui-muted mt-2 text-sm">Empiece por la URL: detectamos datos publicados y posibles duplicados. Después confirme la ficha y analice.</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <span class="ui-badge-info">País fijo: Bolivia</span>
                        <button type="button" class="ui-btn-secondary" @click="examplesOpen = true" :disabled="!canPropose">
                            <i class="ph-duotone ph-flask" aria-hidden="true"></i> Ver casos de prueba
                        </button>
                    </div>
                </div>

                <div x-show="serverMessage" x-cloak class="ui-alert-danger mt-5" role="alert" aria-live="assertive">
                    <p class="font-bold">No se pudo completar el análisis</p>
                    <p class="mt-1 text-sm" x-text="serverMessage"></p>
                </div>

                <form x-ref="sourceForm" method="POST" action="{{ route('conocimiento.fuentes.guardar') }}"
                    :aria-busy="analyzing || submitting"
                    class="mt-6 grid gap-5 md:grid-cols-2" @input="invalidateAnalysis($event)"
                    @change="invalidateAnalysis($event)" @submit="guardSubmission($event)" novalidate>
                    @if (! $canPropose)<p class="ui-alert-warning md:col-span-2">Su acceso es de consulta. No tiene permiso para proponer nuevas fuentes.</p>@endif
                    @csrf
                    <input type="hidden" name="analysis_token" :value="analysisToken">

                    <section class="kg-preparacion ui-card-soft md:col-span-2" aria-labelledby="kg-preparacion-titulo">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="ui-kicker">Guía de esta ficha</p>
                                <h3 id="kg-preparacion-titulo" class="mt-1 font-black">Prepare una fuente para revisión</h3>
                                <p class="ui-muted mt-1 text-sm">Esta ficha identifica qué publica una institución, a qué carrera o ámbito aplica y qué aporta. El análisis ayuda a detectar errores antes de enviarla a revisión.</p>
                            </div>
                        </div>
                        <div class="kg-preparacion-siguiente mt-3" aria-live="polite">
                            <div><p class="text-xs font-bold uppercase tracking-wide" style="color: var(--ui-primary);">Siguiente paso</p><p class="mt-1 font-semibold" x-text="nextPreparationAction.label"></p><p class="ui-muted mt-1 text-sm" x-text="nextPreparationAction.help"></p></div>
                            <button type="button" class="ui-btn-secondary min-h-[44px]" @click="goToPreparationAction()" x-text="nextPreparationAction.button" :disabled="!canPropose"></button>
                        </div>
                        <details class="kg-preparacion-detalles mt-3"><summary class="font-semibold">Qué comprueban los controles de la ficha</summary>
                            <ul class="mt-3 grid gap-2 sm:grid-cols-2">
                                <template x-for="item in frontendChecks" :key="item.label"><li class="flex gap-2 text-sm"><i class="ph-duotone mt-0.5" :class="item.ok ? 'ph-check-circle' : 'ph-circle-dashed'" :style="`color: ${item.ok ? 'var(--ui-success)' : 'var(--ui-muted)'}`" aria-hidden="true"></i><span><strong x-text="item.label"></strong><span class="ui-muted block text-xs" x-text="item.help"></span></span></li></template>
                            </ul>
                        </details>
                    </section>

                    <x-selector-institucional class="md:col-span-2" identificador="fuente-modalidad" etiqueta="¿Qué publicación quiere incorporar?" enlace="entradaTipo"
                        :opciones="[['valor'=>'pagina','etiqueta'=>'Página universitaria'],['valor'=>'malla','etiqueta'=>'PDF de malla o plan curricular'],['valor'=>'reglamento','etiqueta'=>'PDF de reglamento']]"
                        @cambio-selector="entradaTipo = $event.detail.valor; cambiarEntradaTipo()" />
                    <label class="block md:col-span-2">
                        <span class="ui-label" x-text="entradaTipo === 'malla' ? 'URL HTTPS del PDF de malla o plan curricular *' : entradaTipo === 'reglamento' ? 'URL HTTPS del reglamento PDF *' : 'URL HTTPS oficial *'">URL HTTPS oficial *</span>
                        <input name="url" type="url" value="{{ old('url') }}" required maxlength="2000" pattern="https://.*"
                            class="ui-input mt-2 w-full" :class="fieldClass('url')" :aria-invalid="fieldClass('url') === 'kg-input-error'" @blur="touch('url')"
                            aria-describedby="url-help" placeholder="https://universidad.edu.bo/carrera-o-documento.pdf">
                        <span id="url-help" aria-live="polite" class="kg-field-help" :class="fieldHelpClass('url')" x-text="urlMessage"></span>
                        <span class="ui-help mt-2 block" x-show="entradaTipo !== 'pagina'">Pegue el enlace público al PDF publicado por la universidad. No necesita subir un archivo.</span>
                        @error('url')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <section class="ui-card-soft rounded-2xl p-4 md:col-span-2" aria-label="Comprobación de la publicación" :aria-busy="previewChecking">
                        <div x-show="documentBusy" x-cloak class="kg-lectura-en-curso mb-4" role="status" aria-live="polite"><span class="kg-escaneo-documento" aria-hidden="true"><i class="ph-duotone ph-file-search"></i></span><div><p class="font-bold" x-text="documentBusyLabel"></p><p class="ui-muted mt-1 text-sm">El resultado aparecerá cuando termine la comprobación. La fuente se conserva y todavía no se publica.</p></div></div>
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h3 class="font-bold"><i class="ph-duotone ph-detective" aria-hidden="true"></i> Comprobar la publicación</h3>
                            <button type="button" class="ui-btn-secondary" @click="inspectUrl()" :disabled="!canPropose || previewChecking || !values.url || !!urlIssue">
                                <i class="ph-duotone ph-magnifying-glass" aria-hidden="true"></i>
                                <span x-text="previewChecking ? 'Comprobando…' : 'Comprobar URL y detectar datos'"></span>
                            </button>
                            <button type="button" class="ui-btn-secondary" @click="abrirPublicacion()" :disabled="!publicacionUrl"><i class="ph-duotone ph-browser" aria-hidden="true"></i> Ver publicación</button>
                        </div>
                        <p class="ui-muted mt-2 text-sm" x-show="!previewChecking && !preview" x-text="previewWaitingMessage"></p>
                        <p class="ui-muted mt-2 text-sm" x-show="previewChecking" x-cloak role="status">Verificando dominio, respuesta del servidor, título y duplicidad. No se guardará ni activará la fuente.</p>
                        <p class="ui-alert-danger mt-3 text-sm" x-show="previewError" x-cloak role="alert" x-text="previewError"></p>
                        <div x-show="preview" x-cloak class="mt-3 space-y-3">
                            <div :class="previewBannerClass" role="status">
                                <p class="font-bold" x-text="humanize(preview?.status)"></p>
                                <p class="mt-1 text-sm" x-text="preview?.message"></p>
                            </div>
                            <div x-show="preview?.duplicate" class="text-sm">
                                <p class="font-bold" x-text="preview?.duplicate?.title"></p>
                                <p class="ui-muted" x-text="`${humanize(preview?.duplicate?.status)} · ${preview?.duplicate?.kind === 'CORPUS' ? 'Ya incorporada' : 'Ya existe en estudio o revisión'}`"></p>
                            </div>
                            <div x-show="preview?.title && !preview?.duplicate" class="text-sm space-y-2">
                                <p class="ui-muted">Título detectado en la publicación:</p>
                                <p class="font-bold" x-text="preview?.title"></p>
                                <p class="ui-muted" x-text="preview?.suggested_fields?.declared_institution"></p>
                                <x-plegable-institucional x-show="preview?.excerpt" class="ui-muted" icono="ph-file-text"><x-slot:titulo>Ver extracto del documento</x-slot:titulo>
                                    <p class="mt-2 leading-6" x-text="preview?.excerpt"></p>
                                </x-plegable-institucional>
                                <button type="button" class="ui-btn-primary" @click="openFichaPreview()" :disabled="!preview?.can_use">Revisar ficha y sugerencias</button>
                                <x-plegable-institucional x-show="preview?.reading_fragments?.length" class="ui-card-soft p-4" icono="ph-stack"><x-slot:titulo>Fragmentos preparados para revisar <span x-text="preview?.reading_fragments?.length || 0"></span></x-slot:titulo>
                                    <p class="ui-muted text-xs mt-2">Lectura preliminar del extracto publicado. La incorporación completa requiere revisión, aprobación y procesamiento de la fuente.</p>
                                    <div class="grid gap-3 mt-3 sm:grid-cols-2"><template x-for="(fragmento,indice) in preview?.reading_fragments || []" :key="fragmento.id"><article class="ui-card p-4"><p class="ui-kicker" x-text="'Fragmento '+(indice+1)"></p><p class="ui-muted text-sm mt-2 leading-6" x-text="fragmento.text"></p></article></template></div>
                                </x-plegable-institucional>
                                <p class="ui-muted text-xs">Se completan automáticamente sólo los campos vacíos. Revise la ficha y elija qué sugerencias reemplazan sus datos; no inventamos sede, ciudad, versión ni justificación.</p>
                            </div>
                            <template x-for="warning in preview?.warnings || []" :key="warning"><p class="ui-muted text-xs" x-text="warning"></p></template>
                            <section x-show="preview?.documents?.length" class="kg-documentos space-y-3" aria-label="PDF publicados en la página">
                                <h4 class="font-bold">Documentos PDF encontrados en la publicación</h4>
                                <p class="ui-muted text-sm">Elija el documento que necesita. Se comprobará sólo el enlace seleccionado.</p>
                                <template x-for="documento in preview?.documents || []" :key="documento.url">
                                    <article class="ui-card-soft kg-documento">
                                        <div><p class="font-semibold" x-text="documento.title || 'Documento PDF publicado'"></p><a class="ui-muted text-xs underline" :href="safeLink(documento.url)" target="_blank" rel="noopener noreferrer" x-text="documento.url"></a></div>
                                        <button type="button" class="ui-btn-secondary" @click="elegirDocumento(documento)" :disabled="previewChecking || !canPropose">Usar este PDF</button>
                                    </article>
                                </template>
                            </section>
                            <section x-show="preview?.academic_pages?.length" class="kg-documentos space-y-3" aria-label="Páginas académicas enlazadas">
                                <h4 class="font-bold">Páginas académicas para revisar</h4>
                                <p class="ui-muted text-sm">Estos enlaces aparecen en la publicación. Elija uno para comprobar su contenido; todavía no está verificado.</p>
                                <template x-for="pagina in preview?.academic_pages || []" :key="pagina.url">
                                    <article class="ui-card-soft kg-documento">
                                        <div><p class="font-semibold" x-text="pagina.title"></p><a class="ui-muted text-xs underline" :href="safeLink(pagina.url)" target="_blank" rel="noopener noreferrer" x-text="pagina.url"></a></div>
                                        <button type="button" class="ui-btn-secondary" @click="elegirPaginaAcademica(pagina)" :disabled="previewChecking || !canPropose">Comprobar esta página</button>
                                    </article>
                                </template>
                            </section>
                            <p class="ui-muted text-xs" x-text="preview?.http_status ? `Respuesta HTTP ${preview.http_status} · Comprobación: ${preview.checked_at}` : 'La duplicidad o el bloqueo pueden detectarse sin descargar la página.'"></p>
                            <a x-show="preview?.reachable === true && preview?.http_status === 200 && safeLink(preview?.final_url)" :href="safeLink(preview?.final_url)" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold underline">Abrir publicación oficial en otra pestaña</a>
                        </div>
                        <p class="ui-muted mt-3 text-xs">La comprobación ayuda a preparar la ficha. Revise los datos detectados antes de enviarla a revisión.</p>
                    </section>

                    <div class="kg-manual-opcion md:col-span-2" x-show="!preview?.can_use && !fichaManual">
                        <p class="ui-muted text-sm">También puede preparar la ficha con datos que ya haya comprobado en la publicación.</p>
                        <button type="button" class="ui-btn-secondary" @click="fichaManual = true; fichaExpanded = true" :disabled="!canPropose"><i class="ph-duotone ph-pencil-simple" aria-hidden="true"></i> Completar ficha manualmente</button>
                    </div>
                    <section class="kg-ficha-desplegable md:col-span-2" x-show="fichaManual || preview?.can_use" x-cloak aria-label="Datos de la ficha documental">
                        <button type="button" class="kg-ficha-toggle" @click="fichaExpanded = !fichaExpanded" :aria-expanded="fichaExpanded" aria-controls="kg-ficha-campos">
                            <span><i class="ph-duotone ph-file-text" aria-hidden="true"></i><strong>Datos de la ficha</strong><small>Revise y complete los datos de la publicación</small></span>
                            <i class="ph-duotone ph-caret-down" :class="fichaExpanded && 'kg-flecha-abierta'" aria-hidden="true"></i>
                        </button>
                        <div id="kg-ficha-campos" x-show="fichaExpanded" class="kg-ficha-campos grid gap-5 md:grid-cols-2">
                    <label class="block md:col-span-2">
                        <span class="ui-label">Título oficial de la fuente *</span>
                        <input name="title" value="{{ old('title') }}" required minlength="3" maxlength="240"
                            class="ui-input mt-2 w-full" :class="fieldClass('title')" :aria-invalid="fieldClass('title') === 'kg-input-error'" @blur="touch('title')"
                            aria-describedby="title-help" placeholder="Ej. Ingeniería de Sistemas — Malla Curricular 2026">
                        <span id="title-help" class="kg-field-help" :class="fieldHelpClass('title')" x-text="fieldMessage('title', 'Use el título publicado por la universidad.')"></span>
                        @error('title')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="ui-label">Universidad declarada *</span>
                        <input name="declared_institution" value="{{ old('declared_institution') }}" required minlength="3" maxlength="240"
                            class="ui-input mt-2 w-full" :class="fieldClass('declared_institution')" :aria-invalid="fieldClass('declared_institution') === 'kg-input-error'" @blur="touch('declared_institution')"
                            list="trusted-universities" aria-describedby="institution-help" placeholder="Seleccione una universidad verificada">
                        <datalist id="trusted-universities">
                            @foreach ($trustedUniversities as $university)<option value="{{ data_get($university, 'institution') }}"></option>@endforeach
                        </datalist>
                        <span id="institution-help" class="kg-field-help" :class="fieldHelpClass('declared_institution')" x-text="institutionMessage"></span>
                        @error('declared_institution')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <div class="block">
                        <x-selector-institucional identificador="fuente-tipo-documental" etiqueta="Tipo documental" nombre="source_type" :requerido="true" :valor-inicial="old('source_type', '')"
                            campo-validacion="source_type" descripcion-id="fuente-tipo-documental-ayuda"
                            x-effect="if (values.source_type !== undefined) valor = values.source_type"
                            :opciones="[['valor'=>'','etiqueta'=>'Seleccione'],['valor'=>'OFFICIAL_CURRICULUM_PDF','etiqueta'=>'Malla o plan curricular PDF'],['valor'=>'OFFICIAL_CAREER_HTML','etiqueta'=>'Página oficial de carrera'],['valor'=>'OFFICIAL_REGULATION_PDF','etiqueta'=>'Reglamento oficial PDF'],['valor'=>'OFFICIAL_UNIVERSITY_PAGE','etiqueta'=>'Otra página universitaria oficial']]" />
                        <span id="fuente-tipo-documental-ayuda" class="kg-field-help" :class="fieldHelpClass('source_type')" x-text="fieldMessage('source_type', 'El tipo debe coincidir con el documento enlazado.')"></span>
                        @error('source_type')<span class="ui-error">{{ $message }}</span>@enderror
                    </div>

                    <label class="block md:col-span-2">
                        <span class="ui-label">Alcance académico *</span>
                        <textarea name="scope" required minlength="10" maxlength="500" rows="3" class="ui-input mt-2 w-full"
                            :class="fieldClass('scope')" :aria-invalid="fieldClass('scope') === 'kg-input-error'" @blur="touch('scope')"
                            placeholder="Carrera, programa, requisitos, perfil o malla que cubre la fuente">{{ old('scope') }}</textarea>
                        <div class="kg-field-row"><span class="kg-field-help" :class="fieldHelpClass('scope')" x-text="fieldMessage('scope', 'Indique carrera, malla, perfil o requisito cubierto.')"></span><span class="kg-counter" x-text="characterCount('scope', 500)"></span></div>
                        @error('scope')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="ui-label">Sede o campus *</span>
                        <input name="campus" value="{{ old('campus') }}" required minlength="2" maxlength="160" class="ui-input mt-2 w-full" :class="fieldClass('campus')" :aria-invalid="fieldClass('campus') === 'kg-input-error'" @blur="touch('campus')" placeholder="La Paz, Cochabamba, Nacional...">
                        <span class="kg-field-help" :class="fieldHelpClass('campus')" x-text="fieldMessage('campus', 'Indique la sede a la que aplica la fuente.')"></span>
                        @error('campus')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="ui-label">Ciudad *</span>
                        <input name="city" value="{{ old('city') }}" required minlength="2" maxlength="120" class="ui-input mt-2 w-full" :class="fieldClass('city')" :aria-invalid="fieldClass('city') === 'kg-input-error'" @blur="touch('city')" placeholder="La Paz">
                        <span class="kg-field-help" :class="fieldHelpClass('city')" x-text="fieldMessage('city', 'Ciudad boliviana asociada a la publicación.')"></span>
                        @error('city')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="ui-label">Fecha o gestión de publicación</span>
                        <input name="publication_date" value="{{ old('publication_date') }}" maxlength="10" pattern="\d{4}(-\d{2}(-\d{2})?)?"
                            class="ui-input mt-2 w-full" :class="fieldClass('publication_date')" :aria-invalid="fieldClass('publication_date') === 'kg-input-error'" @blur="touch('publication_date')" placeholder="2026 o 2026-03">
                        <span class="kg-field-help" :class="fieldHelpClass('publication_date')" x-text="dateMessage"></span>
                        @error('publication_date')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="ui-label">Versión o gestión *</span>
                        <input name="version" value="{{ old('version') }}" required maxlength="120" class="ui-input mt-2 w-full" :class="fieldClass('version')" :aria-invalid="fieldClass('version') === 'kg-input-error'" @blur="touch('version')" placeholder="Gestión 2026">
                        <span class="kg-field-help" :class="fieldHelpClass('version')" x-text="fieldMessage('version', 'Ejemplo: Gestión 2026, versión 2 o vigente.')"></span>
                        @error('version')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block md:col-span-2">
                        <span class="ui-label">¿Qué conocimiento aportará? *</span>
                        <textarea name="justification" required minlength="20" maxlength="1000" rows="4" class="ui-input mt-2 w-full"
                            :class="fieldClass('justification')" :aria-invalid="fieldClass('justification') === 'kg-input-error'" @blur="touch('justification')"
                            placeholder="Explique cómo mejora la investigación, comparación o preparación académica">{{ old('justification') }}</textarea>
                        <div class="kg-field-row"><span class="kg-field-help" :class="fieldHelpClass('justification')" x-text="fieldMessage('justification', 'Explique el valor concreto para investigación o acompañamiento.')"></span><span class="kg-counter" x-text="characterCount('justification', 1000)"></span></div>
                        @error('justification')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block md:col-span-2">
                        <span class="ui-label">Limitaciones conocidas</span>
                        <textarea name="limitations_text" maxlength="2000" rows="3" class="ui-input mt-2 w-full"
                            :class="fieldClass('limitations_text')" :aria-invalid="fieldClass('limitations_text') === 'kg-input-error'" @blur="touch('limitations_text')"
                            placeholder="Una limitación por línea: no incluye costos, no define admisión, vigencia por confirmar...">{{ old('limitations_text') }}</textarea>
                        <div class="kg-field-row"><span class="kg-field-help" :class="fieldHelpClass('limitations_text')" x-text="fieldMessage('limitations_text', 'Una limitación por línea; máximo 10 líneas útiles.')"></span><span class="kg-counter" x-text="characterCount('limitations_text', 2000)"></span></div>
                        @error('limitations_text')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <div class="kg-prevencion md:col-span-2 space-y-3" aria-live="polite">
                        <div x-show="preventionChecks.bloqueos.length" class="ui-alert-danger" x-cloak><p class="font-semibold text-sm">Datos por corregir</p><ul class="mt-2 list-disc pl-4 text-xs space-y-2"><template x-for="bloqueo in preventionChecks.bloqueos" :key="bloqueo.name"><li x-text="bloqueo.message"></li></template></ul></div>
                        <div x-show="preventionChecks.advertencias.length" class="ui-alert-warning" x-cloak><p class="font-semibold text-sm">Revisión necesaria</p><ul class="mt-2 list-disc pl-4 text-xs space-y-2"><template x-for="advertencia in preventionChecks.advertencias" :key="advertencia"><li x-text="advertencia"></li></template></ul></div>
                        <div x-show="preventionChecks.sugerencias.length && values.url" class="ui-card-soft rounded-xl p-3" x-cloak><p class="font-semibold text-sm">Para continuar</p><template x-for="sugerencia in preventionChecks.sugerencias" :key="sugerencia"><p class="ui-muted mt-2 text-xs" x-text="sugerencia"></p></template></div>
                    </div>
                    <div class="flex flex-col-reverse gap-3 md:col-span-2 sm:flex-row sm:items-center sm:justify-end">
                        <div class="mr-auto text-sm" aria-live="polite">
                            <p class="ui-muted" x-show="analysisInvalidated" x-cloak><i class="ph-duotone ph-warning-circle" aria-hidden="true"></i> El formulario cambió. Ejecute nuevamente el análisis.</p>
                            <p class="font-semibold" style="color: var(--ui-success);" x-show="analysisToken" x-cloak><i class="ph-duotone ph-check-circle" aria-hidden="true"></i> <span x-text="`Análisis vigente: ${Math.max(0, Math.ceil((expiresAt - clockNow) / 60000))} min restantes.`"></span></p>
                        </div>
                        <button type="button" class="ui-btn-secondary" @click="resetForm()">Limpiar</button>
                        <button type="button" class="ui-btn-secondary" @click="openFichaPreview()">Vista previa de la ficha</button>
                        <button type="button" class="ui-btn-primary" @click="analyze()" :disabled="!!analysisBlockReason || submitting" aria-describedby="analisis-bloqueo">
                            <span x-show="!analyzing"><i class="ph-duotone ph-magnifying-glass" aria-hidden="true"></i> Analizar fuente</span>
                            <span x-show="analyzing" x-cloak>Analizando...</span>
                        </button>
                        <button type="submit" class="ui-btn-primary" :disabled="!analysisToken || submitting || !canPropose"
                            :class="(!analysisToken || submitting) && 'cursor-not-allowed opacity-60'">
                            <span x-text="submitting ? 'Enviando...' : 'Enviar a revisión'"></span>
                        </button>
                    </div>
                    <p id="analisis-bloqueo" x-show="analysisBlockReason" x-text="analysisBlockReason" class="ui-muted text-sm md:col-span-2"></p>
                        </div>
                    </section>
                </form>
            </section>

            <aside class="space-y-5">
                <section class="ui-card card-shadow rounded-[2rem] p-6">
                    <p class="ui-kicker">Antes de analizar</p>
                    <h2 class="ui-title mt-2 text-lg font-black">Qué debe ocurrir</h2>
                    <ul class="mt-4 space-y-3 text-sm">
                        <li class="flex gap-3"><i class="ph-duotone ph-check-circle mt-0.5" style="color: var(--ui-success);" aria-hidden="true"></i><span class="ui-muted">La universidad y el dominio deben coincidir exactamente.</span></li>
                        <li class="flex gap-3"><i class="ph-duotone ph-x-circle mt-0.5" style="color: var(--ui-danger);" aria-hidden="true"></i><span class="ui-muted">Una fuente extranjera, IP o URL con credenciales debe bloquearse.</span></li>
                        <li class="flex gap-3"><i class="ph-duotone ph-clock-countdown mt-0.5" style="color: var(--ui-warning);" aria-hidden="true"></i><span class="ui-muted">El resultado no incorpora datos: sólo habilita revisión humana.</span></li>
                    </ul>
                </section>

                <section class="ui-card card-shadow rounded-[2rem] p-6">
                    <h2 class="ui-title text-lg font-black">Flujo de incorporación</h2>
                        <p class="ui-muted mt-3 text-sm">La vista previa comprueba y lee el documento con límites de seguridad; el análisis revisa la ficha. Ninguno acredita por sí solo autenticidad o vigencia. La aprobación queda pendiente de incorporación revisada.</p>
                    <ol class="mt-4 space-y-3 text-sm">
                        @foreach (data_get($overview, 'workflow', []) as $step)
                            <li class="flex gap-3"><span class="ui-badge-info">{{ $loop->iteration }}</span><span class="ui-muted leading-6">{{ $step }}</span></li>
                        @endforeach
                    </ol>
                </section>
            </aside>
        </div>

        @if (! $result->available)
            <section class="ui-card card-shadow rounded-[2rem] p-6 sm:p-8"><p class="ui-alert-warning">El aporte documental no está disponible. No se mostró ni registró información parcial.</p></section>
        @else
            <section class="ui-card card-shadow rounded-[2rem] p-6 sm:p-8">
                <div class="ui-card-soft rounded-2xl p-4 sm:p-5" :aria-busy="directoryLoading">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="max-w-2xl">
                            <p class="ui-kicker">Instituciones de Bolivia</p>
                            <h2 class="ui-title mt-1 text-xl font-black">Directorio nacional</h2>
                            <p class="ui-muted mt-2 text-sm">Consulta las instituciones registradas y sus departamentos. Es un registro histórico: estar aquí no confirma vigencia actual ni carreras aprobadas.</p>
                        </div>
                        <a href="{{ route('conocimiento.directorio-nacional') }}" class="ui-btn-primary min-h-[44px]"><i class="ph-duotone ph-map-trifold" aria-hidden="true"></i> Explorar instituciones</a>
                    </div>
                    <p class="ui-muted mt-4 text-sm" role="status" x-show="directoryLoading" x-cloak>Consultando el directorio nacional…</p>
                    <div class="mt-4" x-show="directorySummary" x-cloak>
                        <p class="flex flex-wrap items-baseline gap-2"><strong class="text-3xl font-black" style="color: var(--ui-text);" x-text="directorySummary?.institution_count"></strong><span class="ui-muted text-sm">instituciones en el directorio · consulta actual</span></p>
                        <div class="mt-3 flex flex-wrap gap-2" aria-label="Explorar por departamento">
                            <template x-for="department in Object.keys(directorySummary?.departments || {})" :key="department">
                                <a class="ui-badge-info inline-flex min-h-[44px] items-center gap-1" :href="`${directoryUrl}?departamento=${encodeURIComponent(department)}`"><span x-text="department"></span> <span class="font-bold" x-text="directorySummary.departments[department]"></span></a>
                            </template>
                        </div>
                        <p class="ui-muted mt-2 text-xs">Una institución puede figurar en varios departamentos; estos subtotales no se suman.</p>
                        <div class="mt-5 grid gap-2 sm:grid-cols-2" aria-label="Instituciones del directorio nacional">
                            <template x-for="institution in visibleDirectoryInstitutions" :key="institution.name">
                                <a class="ui-card flex min-h-[64px] items-center justify-between gap-3 rounded-xl p-3 transition-shadow hover:shadow-sm focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2" :href="`${directoryUrl}?buscar=${encodeURIComponent(institution.name)}`">
                                    <span class="min-w-0"><strong class="block text-sm" x-text="institution.name"></strong><span class="ui-muted mt-1 block text-xs" x-text="institution.departments.join(' · ') || 'Departamento por verificar'"></span></span>
                                    <i class="ph-duotone ph-arrow-up-right shrink-0" aria-hidden="true"></i>
                                </a>
                            </template>
                        </div>
                        <button type="button" class="ui-btn-secondary mt-4 min-h-[44px]" x-show="directorySummary?.institution_count > 6" :aria-expanded="directoryExpanded" @click="directoryExpanded = !directoryExpanded" x-text="directoryExpanded ? 'Mostrar menos instituciones' : `Ver las ${directorySummary?.institution_count} instituciones aquí`"></button>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-3" x-show="directoryError" x-cloak><p class="ui-alert-warning text-sm" role="alert" x-text="directoryError"></p><button type="button" class="ui-btn-secondary min-h-[44px]" @click="loadDirectorySummary()">Reintentar</button></div>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div class="mt-7"><p class="ui-kicker">Biblioteca documental</p><h2 class="ui-title mt-2 text-2xl font-black">Documentos revisados</h2></div>
                    <input aria-label="Buscar documentos revisados" type="search" x-model="sourceSearch" class="ui-input w-full sm:max-w-sm" placeholder="Buscar institución, carrera o documento">
                </div>
                <p class="ui-muted mt-3 text-sm">Aquí se muestran publicaciones incorporadas, agrupadas por institución y carrera. Los filtros cuentan documentos, no universidades; el directorio de arriba muestra el registro nacional.</p>
                <div class="mt-5 flex flex-wrap gap-2" role="group" aria-label="Tipo de fuente documental"><template x-for="group in libraryGroups" :key="group.value"><button type="button" class="min-h-[44px]" :class="sourceGroup === group.value ? 'ui-btn-primary' : 'ui-btn-secondary'" :aria-pressed="sourceGroup === group.value" @click="sourceGroup = group.value"><span x-text="group.label"></span><span class="font-bold" x-text="group.count"></span></button></template></div>
                <p class="ui-muted mt-3 text-sm" role="status" aria-live="polite" x-text="`${filteredSources.length} de ${sources.length} documentos visibles`"></p>
                <p class="ui-muted mt-4 text-sm" x-show="!filteredSources.length" x-cloak x-text="sourceSearch ? 'No hay documentos que coincidan. Pruebe otro término o seleccione Todas.' : 'No hay documentos en este tipo de fuente.'"></p>
                <div class="kg-biblioteca mt-5 space-y-4" x-show="filteredSources.length" x-cloak>
                    <template x-for="institution in libraryInstitutions" :key="institution.key">
                        <section class="kg-institucion ui-card-soft" :aria-label="institution.name">
                            <div class="flex flex-wrap items-start justify-between gap-2"><div><p class="ui-kicker" x-text="institution.category === 'MINISTERIALES' ? 'Documentos ministeriales' : institution.category === 'UNIVERSITARIAS' ? 'Universidad / sede' : 'Institución' "></p><h3 class="mt-1 text-lg font-black" x-text="institution.name"></h3></div><span class="ui-badge-info" x-text="`${institution.count} ${institution.count === 1 ? 'documento' : 'documentos'}`"></span></div>
                            <div class="mt-4 grid gap-3 lg:grid-cols-2">
                                <template x-for="career in institution.careers" :key="career.key">
                                    <section class="kg-carrera ui-card" :aria-label="career.name">
                                        <div class="flex flex-wrap items-start justify-between gap-2"><div><p class="ui-muted text-xs font-semibold uppercase tracking-wide">Carrera documentada</p><h4 class="mt-1 font-black" x-text="career.name"></h4></div><span class="ui-badge-muted" x-text="`${career.documents.length} ${career.documents.length === 1 ? 'documento' : 'documentos'}`"></span></div>
                                        <ul class="mt-3 space-y-2"><template x-for="document in career.documents" :key="document.source.source_id || document.source.url"><li class="kg-documento-biblioteca"><div><p class="font-semibold" x-text="document.title"></p><p class="ui-muted mt-1 text-xs" x-show="document.source.source_type" x-text="sourceTypeLabel(document.source.source_type)"></p></div><div class="flex flex-wrap items-center gap-2"><span :class="['VALID', 'VERIFIED'].includes(document.source.verification_status) ? 'ui-badge-success' : 'ui-badge-warning'" x-text="documentStatusLabel(document.source.verification_status)"></span><button type="button" class="ui-btn-secondary min-h-[44px]" @click="openDetail('source', document.source)" :aria-label="`Ver trazabilidad de ${document.source.title || document.title}`">Trazabilidad</button></div></li></template></ul>
                                    </section>
                                </template>
                                <template x-if="institution.documents.length"><section class="kg-carrera ui-card lg:col-span-2" aria-label="Otras publicaciones de la institución"><h4 class="font-black" x-text="institution.category === 'MINISTERIALES' ? 'Publicaciones normativas' : 'Otras publicaciones de la institución'"></h4><ul class="mt-3 grid gap-2 lg:grid-cols-2"><template x-for="document in institution.documents" :key="document.source.source_id || document.source.url"><li class="kg-documento-biblioteca"><div><p class="font-semibold" x-text="document.title"></p><p class="ui-muted mt-1 text-xs" x-show="document.source.source_type" x-text="sourceTypeLabel(document.source.source_type)"></p></div><div class="flex flex-wrap items-center gap-2"><span :class="['VALID', 'VERIFIED'].includes(document.source.verification_status) ? 'ui-badge-success' : 'ui-badge-warning'" x-text="documentStatusLabel(document.source.verification_status)"></span><button type="button" class="ui-btn-secondary min-h-[44px]" @click="openDetail('source', document.source)" :aria-label="`Ver trazabilidad de ${document.source.title || document.title}`">Trazabilidad</button></div></li></template></ul></section></template>
                            </div>
                        </section>
                    </template>
                </div>
            </section>

            @if($canReview)
                <section class="ui-card card-shadow rounded-[2rem] p-5 sm:p-6" :aria-busy="updatesLoading || !!updatingSource">
                    <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="ui-kicker">Vigencia documental</p><h2 class="ui-title mt-2 text-xl font-black">Revisión y actualización de fuentes</h2><p class="ui-muted mt-2 text-sm">Configura cuándo revisar cada publicación. El primer control establece una referencia; los siguientes buscan cambios que necesitan revisión antes de incorporarse.</p></div><button type="button" class="ui-btn-secondary min-h-[44px]" @click="loadUpdates()" :disabled="updatesLoading || !!updatingSource">Consultar estado guardado</button></div>
                    <p x-show="updatesLoading" x-cloak class="ui-muted mt-4 text-sm" role="status">Recuperando las cadencias y últimas revisiones…</p>
                    <p x-show="updatesError" x-cloak class="ui-alert-warning mt-4 text-sm" role="alert" x-text="updatesError"></p>
                    <p x-show="updatesNotice" x-cloak class="ui-alert-info mt-4 text-sm" role="status" x-text="updatesNotice"></p>
                    <p x-show="updates && !updates.sources.length" x-cloak class="ui-muted mt-4 text-sm">No hay fuentes disponibles para configurar revisión.</p>
                    <div class="mt-4 space-y-3"><template x-for="source in updates?.sources || []" :key="source.source_id">
                        <x-plegable-institucional icono="ph-calendar-check" :compacto="true">
                            <x-slot:titulo><span x-text="source.title"></span></x-slot:titulo>
                            <x-slot:contador><span :class="source.status === 'SIN_CAMBIOS' ? 'ui-badge-success' : 'ui-badge-warning'" x-text="updateStatusLabel(source.status)"></span></x-slot:contador>
                            <p class="ui-muted text-sm" x-text="source.institution"></p>
                            <p class="ui-muted mt-2 text-sm">Frecuencia confirmada: <strong x-text="cadenceLabel(source.cadence)"></strong></p>
                            <fieldset class="mt-3"><legend class="ui-label">Frecuencia de revisión</legend><div class="mt-2 flex flex-wrap gap-2"><template x-for="cadence in updates.cadences" :key="cadence.value"><button type="button" class="min-h-[44px]" :class="cadenceChoices[source.source_id] === cadence.value ? 'ui-btn-primary' : 'ui-btn-secondary'" :aria-pressed="cadenceChoices[source.source_id] === cadence.value" @click="cadenceChoices[source.source_id] = cadence.value" :disabled="!!updatingSource"><span x-text="cadence.label"></span></button></template></div></fieldset>
                            <dl class="ui-muted mt-3 grid gap-2 text-sm sm:grid-cols-2"><div><dt class="font-semibold">Último intento de revisión</dt><dd x-text="updateDate(source.last_checked_at)"></dd></div><div><dt class="font-semibold">Próxima revisión indicada</dt><dd x-text="source.cadence === 'MANUAL' ? 'A solicitud de Administración' : updateDate(source.next_check_at)"></dd></div></dl>
                            <p x-show="source.message" class="ui-muted mt-3 text-sm" x-text="source.message"></p>
                            <div class="mt-4 flex flex-wrap gap-2"><button type="button" class="ui-btn-primary min-h-[44px]" @click="configureUpdate(source)" :disabled="updatesNeedsCheck || !!updatingSource || updatesLoading || cadenceChoices[source.source_id] === source.cadence">Guardar frecuencia</button><button type="button" class="ui-btn-secondary min-h-[44px]" @click="reviewUpdate(source)" :disabled="updatesNeedsCheck || !!updatingSource || updatesLoading"><i class="ph-duotone" :class="updatingSource === source.source_id ? 'ph-circle-notch animate-spin motion-reduce:animate-none' : 'ph-magnifying-glass'" aria-hidden="true"></i><span x-text="updatingSource === source.source_id ? 'Procesando solicitud…' : 'Revisar publicación ahora'"></span></button></div>
                            <p x-show="cadenceChoices[source.source_id] !== source.cadence" class="ui-muted mt-2 text-xs">La selección todavía no está guardada.</p>
                        </x-plegable-institucional>
                    </template></div>
                </section>
            @endif

            <section class="ui-card card-shadow rounded-[2rem] p-6 sm:p-8">
                <div><p class="ui-kicker">Gobierno documental</p><h2 class="ui-title mt-2 text-2xl font-black">Cola de incorporación</h2></div>
                <div class="mt-6 overflow-x-auto">
                    <table class="ui-table min-w-full">
                        <thead><tr><th>Fuente</th><th>Universidad</th><th>Tipo</th><th>Estado</th><th>Acción</th></tr></thead>
                        <tbody>
                            @forelse ($proposals as $proposal)
                                <tr>
                                    <td><p class="font-bold">{{ data_get($proposal, 'title') }}</p></td>
                                    <td>{{ data_get($proposal, 'declared_institution') }}</td>
                                    <td x-text="sourceTypeLabel(@js(data_get($proposal, 'source_type')))"></td>
                                    <td><span class="ui-badge" x-text="documentStatusLabel(@js(data_get($proposal, 'status')))"></span></td>
                                    <td><button type="button" class="ui-btn-secondary" @click="openDetail('proposal', @js($proposal))">Revisar detalle</button></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="ui-muted py-8 text-center">No hay fuentes nuevas en revisión.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        <template x-teleport="body">
        <div x-show="fichaPreviewOpen" x-cloak class="knowledge-governance ui-modal-backdrop kg-dialog-backdrop" @keydown.escape.window="fichaPreviewOpen = false">
            <button type="button" class="absolute inset-0" aria-label="Cerrar vista previa" @click="fichaPreviewOpen = false"></button>
            <section class="ui-modal kg-ficha-modal relative z-10 w-full max-w-5xl rounded-[2rem]" data-dialogo-conocimiento role="dialog" aria-modal="true" aria-labelledby="ficha-preview-title">
                <div class="ui-modal-header"><div><p class="ui-kicker">Borrador · no guardado ni aprobado</p><h2 id="ficha-preview-title" class="ui-title mt-2 text-2xl font-black">Vista previa de la ficha</h2></div><button type="button" class="ui-btn-secondary" @click="fichaPreviewOpen = false">Cerrar</button></div>
                <div class="kg-dialog-cuerpo space-y-5 p-6">
                    <p class="ui-muted text-sm">Borrador del formulario. Complete los datos pendientes con información de la publicación antes de enviarlo a revisión.</p>
                    <div class="ui-card-soft rounded-2xl p-4"><p class="font-bold" x-text="values.title || 'Título oficial pendiente'"></p><p class="ui-muted mt-1 text-sm" x-text="values.declared_institution || 'Universidad pendiente'"></p><p class="ui-muted mt-2 text-xs" x-text="preview?.requested_url === values.url ? `Comprobación de URL: ${humanize(preview?.status)}` : 'URL aún no comprobada'"></p></div>
                    <dl class="kg-ficha-resumen grid gap-3 md:grid-cols-2">
                        <template x-for="field in fichaFields" :key="field.name"><div class="ui-card-soft rounded-2xl p-4"><dt class="ui-label" x-text="field.label"></dt><dd class="mt-2 whitespace-pre-line text-sm" x-text="field.name === 'source_type' && field.value ? sourceTypeLabel(field.value) : field.value || 'Sin completar'"></dd><p class="ui-muted mt-2 text-xs" x-show="fieldClass(field.name) === 'kg-input-error'" x-text="field.error"></p></div></template>
                    </dl>
                    <x-plegable-institucional etiqueta="Sugerencias de la publicación" descripcion="Elija qué datos detectados aplicar a su ficha" icono="ph-sparkle" :abierto="true">

                        <p class="ui-muted text-sm">Comparamos lo escrito con los datos extraídos. Los campos ya completados no se reemplazan sin su selección. El alcance sugerido es un extracto que debe revisar.</p>
                        <p x-show="!suggestionRows.length" class="ui-muted text-sm">Compruebe una URL válida para obtener sugerencias. No se generan datos sin evidencia documental.</p>
                        <template x-for="suggestion in suggestionRows" :key="suggestion.name"><label class="ui-card-soft block rounded-2xl p-4"><span class="flex items-center gap-3"><input type="checkbox" x-model="suggestionSelections[suggestion.name]" :disabled="!suggestion.changed || canPropose === false || submitting || previewChecking" :aria-label="`Aplicar sugerencia: ${suggestion.label}`"><span class="font-bold" x-text="suggestion.label"></span><span class="ui-badge ml-auto" x-show="!suggestion.changed">Ya aplicado</span></span><span class="ui-muted mt-2 block text-xs" x-show="suggestion.changed" x-text="`Actual: ${suggestion.value || 'Sin completar'}`"></span><span class="mt-2 block whitespace-pre-line text-sm" x-text="suggestion.name === 'source_type' ? sourceTypeLabel(suggestion.suggested) : suggestion.suggested"></span></label></template>
                        <p x-show="suggestionMessage" class="ui-alert-success text-sm" role="status" x-text="suggestionMessage"></p>
                    </x-plegable-institucional>
                </div>
                <div class="ui-modal-footer flex flex-wrap gap-3"><button type="button" class="ui-btn-secondary" @click="fichaPreviewOpen = false">Volver al formulario</button><button type="button" class="ui-btn-primary" @click="applySelectedSuggestions()" :disabled="!selectedSuggestionCount || canPropose === false || submitting || previewChecking" x-text="`Aplicar seleccionadas (${selectedSuggestionCount})`"></button></div>
            </section>
        </div>
        </template>

        <template x-teleport="body">
        <div x-show="connectionOpen" x-cloak class="knowledge-governance ui-modal-backdrop kg-dialog-backdrop" @keydown.escape.window="connectionOpen = false">
            <button type="button" class="absolute inset-0" aria-label="Cerrar conexión" @click="connectionOpen = false"></button>
            <section class="ui-modal relative z-10 max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-[2rem]" data-dialogo-conocimiento role="dialog" aria-modal="true" aria-labelledby="connection-title">
                <div class="ui-modal-header"><h2 id="connection-title" class="ui-title text-2xl font-black">Conexión del sistema</h2><button type="button" class="ui-btn-secondary" @click="connectionOpen = false">Cerrar</button></div>
                <div class="space-y-4 p-6" aria-live="polite">
                    <p class="ui-muted" x-show="checkingConnection" role="status">Comprobando servidor, autenticación interna, fuentes y asistente documental…</p>
                    <p x-show="connectionError" class="ui-alert-danger" x-text="connectionError"></p>
                    <button x-show="connectionError || (connection && !connection.connected)" type="button" class="ui-btn-secondary min-h-[44px]" @click="checkConnection()" :disabled="checkingConnection">Reintentar verificación</button>
                    <template x-for="check in connection?.checks || []" :key="check.label"><div class="ui-card-soft flex items-center gap-3 rounded-2xl p-4"><i class="ph-duotone text-xl" :class="check.available ? 'ph-check-circle' : 'ph-warning-circle'" aria-hidden="true"></i><p class="font-bold" x-text="check.label"></p><span class="ml-auto" :class="check.available ? 'ui-badge-success' : 'ui-badge-warning'" x-text="check.available ? 'Conectado' : 'Pendiente'"></span></div></template>
                    <p x-show="connection" class="ui-muted" x-text="`Fuentes disponibles: ${connection?.source_count ?? 'No disponible'}`"></p>
                    <p x-show="connection" :class="connection?.connected ? 'ui-alert-success' : 'ui-alert-warning'" x-text="connection?.message"></p>
                    <p class="ui-muted text-sm">Esta comprobación consulta la disponibilidad de los servicios. No registra propuestas ni cambia información institucional.</p>
                </div>
                <div class="ui-modal-footer"><button type="button" class="ui-btn-primary" @click="checkConnection()" :disabled="checkingConnection">Comprobar nuevamente</button></div>
            </section>
        </div>
        </template>

        <template x-teleport="body">
        <div x-show="tutorOpen" x-cloak class="knowledge-governance ui-modal-backdrop kg-dialog-backdrop" @keydown.escape.window="tutorOpen = false">
            <button type="button" class="absolute inset-0" aria-label="Cerrar asistente documental" @click="tutorOpen = false"></button>
            <section class="ui-modal relative z-10 max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-[2rem]" data-dialogo-conocimiento role="dialog" aria-modal="true" aria-labelledby="tutor-test-title">
                <div class="ui-modal-header"><div><p class="ui-kicker">Administración · apoyo con fuentes</p><h2 id="tutor-test-title" class="ui-title mt-2 text-2xl font-black">Asistente documental</h2></div><button type="button" class="ui-btn-secondary" @click="tutorOpen = false">Cerrar</button></div>
                <div class="space-y-4 p-6">
                    <div class="flex items-center gap-4"><x-avatar-documental x-bind:class="{'kg-avatar-consultando': askingTutor, 'kg-avatar-escuchando': !askingTutor}" /><div><p class="font-semibold" x-text="assistantGreeting"></p><p class="ui-muted mt-1 text-sm" x-text="askingTutor ? 'Consultando las publicaciones disponibles…' : 'Escribe qué necesitas revisar en los documentos.'"></p></div></div>
                    <div class="flex flex-wrap gap-2" role="group" aria-label="Forma de explicar la respuesta"><button type="button" class="min-h-[44px]" :class="!technicalDetail ? 'ui-btn-primary' : 'ui-btn-secondary'" :aria-pressed="!technicalDetail" @click="languageMode = 'CLARO'">Lenguaje claro</button><button type="button" class="min-h-[44px]" :class="technicalDetail ? 'ui-btn-primary' : 'ui-btn-secondary'" :aria-pressed="technicalDetail" @click="languageMode = 'TECNICO'">Detalle técnico</button></div>
                    <p class="ui-muted text-sm">Puedes consultar publicaciones y sus evidencias. Esta conversación dura mientras la página esté abierta; no conserva memoria al volver ni incorpora información por sí sola.</p>
                    <div class="flex flex-wrap gap-2"><template x-for="example in ['¿Qué universidades tienen información documental disponible?', '¿Cómo compruebo si una fuente ya está incorporada?', '¿Qué debo revisar antes de usar una malla curricular?']"><button type="button" class="ui-btn-secondary min-h-[44px] text-sm" @click="tutorQuestion = example" x-text="example"></button></template></div>
                    <form @submit.prevent="askTutor()" class="space-y-3">
                        <label class="block"><span class="ui-label">Consulta documental</span><textarea x-model="tutorQuestion" minlength="2" maxlength="2000" required rows="3" class="ui-input mt-2 w-full" placeholder="Indica la universidad, carrera o documento que quieres revisar"></textarea></label>
                        <div class="flex justify-end"><button type="submit" class="ui-btn-primary" :disabled="askingTutor || tutorQuestion.trim().length < 2" x-text="askingTutor ? 'Consultando la evidencia…' : 'Consultar asistente'"></button></div>
                    </form>
                    <p x-show="tutorError" class="ui-alert-danger" role="alert" x-text="tutorError"></p>
                    <div x-show="askingTutor" x-cloak class="kg-lectura-en-curso" role="status"><span class="kg-escaneo-documento" aria-hidden="true"><i class="ph-duotone ph-books"></i></span><p class="font-bold">Preparando una respuesta con las fuentes disponibles…</p></div>
                    <div x-show="tutorAnswer?.insufficient_evidence === true" class="ui-alert-warning text-sm"><strong>Falta información para responder con seguridad.</strong> Revisa las fuentes disponibles o incorpora una publicación oficial que respalde esta consulta.</div>
                    <article x-show="tutorAnswer" class="ui-card-soft rounded-2xl p-5" aria-live="polite"><h3 class="font-black" x-text="askingTutor ? 'Última respuesta confirmada' : 'Respuesta del asistente'"></h3><div class="kg-respuesta-documental mt-3 space-y-3"><template x-for="(block, blockIndex) in assistantAnswerBlocks" :key="blockIndex"><div><template x-if="block.kind === 'heading'"><h4 class="font-bold" x-text="block.text"></h4></template><template x-if="block.kind === 'paragraph'"><p class="text-sm leading-7" x-text="block.text"></p></template><template x-if="block.kind === 'list'"><ul class="list-disc space-y-2 pl-5 text-sm leading-7"><template x-for="(item, index) in block.items" :key="index"><li x-text="item"></li></template></ul></template></div></template></div><div class="mt-4 space-y-3"><h4 x-show="tutorAnswer?.sources?.length" class="font-bold" x-text="(tutorAnswer?.sources?.length || 0) === 1 ? 'Fuente de esta respuesta' : 'Fuentes de esta respuesta'"></h4><template x-for="(source, index) in tutorAnswer?.sources || []" :key="source.chunk_id || index"><div class="ui-card rounded-xl p-3"><p class="font-semibold text-sm" x-text="source.title || 'Documento de referencia'"></p><p class="ui-muted mt-1 text-sm" x-text="source.institution"></p><p x-show="technicalDetail && (source.source_id || source.chunk_id)" class="ui-muted mt-1 break-all text-xs" x-text="source.source_id || source.chunk_id"></p><a x-show="safeLink(source.reference)" class="ui-btn-secondary mt-2 min-h-[44px] text-sm" :href="safeLink(source.reference)" target="_blank" rel="noopener noreferrer">Ver publicación oficial <i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i></a></div></template></div><x-plegable-institucional x-show="technicalDetail" class="mt-4" icono="ph-code" :compacto="true"><x-slot:titulo>Cómo se preparó esta respuesta</x-slot:titulo><dl class="space-y-2 text-sm"><div x-show="tutorAnswer?.version_provider"><dt class="font-semibold">Versión y proveedor informados</dt><dd class="ui-muted break-all" x-text="tutorAnswer?.version_provider"></dd></div><div x-show="typeof tutorAnswer?.insufficient_evidence === 'boolean'"><dt class="font-semibold">Comprobación de evidencia</dt><dd class="ui-muted" x-text="tutorAnswer?.insufficient_evidence ? 'El servicio señaló evidencia insuficiente para esta consulta.' : 'El servicio no señaló evidencia insuficiente; revise igualmente las publicaciones citadas.'"></dd></div></dl></x-plegable-institucional></article>
                </div>
            </section>
        </div>
        </template>

        <template x-teleport="body">
        <div x-show="analysisOpen" x-cloak class="knowledge-governance ui-modal-backdrop kg-dialog-backdrop" @keydown.escape.window="analysisOpen = false">
            <button type="button" class="absolute inset-0" aria-label="Cerrar análisis" @click="analysisOpen = false"></button>
            <section class="ui-modal relative z-10 max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-[2rem]" data-dialogo-conocimiento role="dialog" aria-modal="true" aria-labelledby="analysis-title">
                <div class="ui-modal-header"><div><p class="ui-kicker">Resultado del aporte</p><h2 id="analysis-title" class="ui-title mt-2 text-2xl font-black">Análisis de la fuente</h2></div><button type="button" class="ui-btn-secondary" @click="analysisOpen = false">Cerrar</button></div>
                <div class="p-6">
                    <div class="mb-5 rounded-2xl border p-4" :class="analysisBannerClass" role="status" aria-live="polite">
                        <div class="flex items-start gap-3">
                            <i class="ph-duotone text-2xl" :class="analysis?.can_submit ? 'ph-check-circle' : analysis?.readiness === 'BLOQUEADA' ? 'ph-x-circle' : 'ph-warning-circle'" aria-hidden="true"></i>
                            <div><p class="font-black" x-text="analysisHeadline"></p><p class="mt-1 text-sm" x-text="analysis?.assessment?.message"></p></div>
                        </div>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs">Preparación</p><p class="mt-2 font-black" x-text="humanize(analysis?.readiness)"></p></div>
                        <div class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs">Riesgo</p><p class="mt-2 font-black" x-text="analysis?.risk_level"></p></div>
                        <div class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs">Dominio</p><p class="mt-2 break-all font-black" x-text="analysis?.assessment?.host || 'No válido'"></p></div>
                    </div>
                    <div class="mt-5 space-y-3">
                        <template x-for="check in analysis?.checks || []" :key="check.code">
                            <article class="ui-card-soft rounded-2xl border p-4" :class="checkCardClass(check.status)">
                                <div class="flex items-start justify-between gap-3"><div class="flex gap-2"><i class="ph-duotone mt-0.5" :class="checkIcon(check.status)" aria-hidden="true"></i><p class="font-bold" x-text="check.label"></p></div><span :class="checkBadgeClass(check.status)" x-text="check.status"></span></div>
                                <p class="ui-muted mt-2 text-sm" x-text="check.message"></p>
                            </article>
                        </template>
                    </div>
                    <div x-show="analysis?.suggestions?.length" class="ui-alert-warning mt-5"><p class="font-bold">Recomendaciones</p><ul class="mt-2 list-disc space-y-1 pl-5 text-sm"><template x-for="suggestion in analysis?.suggestions || []"><li x-text="suggestion"></li></template></ul></div>
                </div>
                <div class="ui-modal-footer"><p class="ui-muted mr-auto text-sm" x-text="analysis?.can_submit ? 'Puede enviarse a revisión humana.' : 'Corrija los bloqueos antes de enviar.'"></p><button type="button" class="ui-btn-primary" @click="analysisOpen = false" x-text="analysis?.can_submit ? 'Continuar' : 'Corregir formulario'"></button></div>
            </section>
        </div>
        </template>

        <template x-teleport="body">
        <div x-show="examplesOpen" x-cloak class="knowledge-governance ui-modal-backdrop kg-dialog-backdrop" @keydown.escape.window="examplesOpen = false">
            <button type="button" class="absolute inset-0" aria-label="Cerrar casos de prueba" @click="examplesOpen = false"></button>
            <section class="ui-modal relative z-10 max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-[2rem]" data-dialogo-conocimiento role="dialog" aria-modal="true" aria-labelledby="examples-title">
                <div class="ui-modal-header"><div><p class="ui-kicker">Validaciones de muestra</p><h2 id="examples-title" class="ui-title mt-2 text-2xl font-black">Pruebe cómo responde el sistema</h2></div><button type="button" class="ui-btn-secondary" @click="examplesOpen = false">Cerrar</button></div>
                <div class="grid gap-4 p-6 md:grid-cols-2">
                    <article class="kg-example-card">
                        <span class="ui-badge-info">Revisión obligatoria</span>
                        <h3 class="mt-3 font-black">Dominio universitario reconocido</h3>
                        <p class="ui-muted mt-2 text-sm leading-6">Carga la página institucional de UPB. Comprueba el dominio sin afirmar que su contenido ya fue analizado o incorporado.</p>
                        <button type="button" class="ui-btn-secondary mt-4 w-full justify-center" @click="loadExample('review')">Cargar prueba</button>
                    </article>
                    <article class="kg-example-card">
                        <span class="ui-badge-warning">Debe bloquear</span>
                        <h3 class="mt-3 font-black">Fuente duplicada</h3>
                        <p class="ui-muted mt-2 text-sm leading-6">Carga una página oficial boliviana que ya existe en las fuentes activas. El dominio cumple, pero la duplicidad impide enviarla.</p>
                        <button type="button" class="ui-btn-secondary mt-4 w-full justify-center" @click="loadExample('duplicate')">Cargar prueba</button>
                    </article>
                    <article class="kg-example-card">
                        <span class="ui-badge-danger">Debe rechazar</span>
                        <h3 class="mt-3 font-black">Universidad extranjera</h3>
                        <p class="ui-muted mt-2 text-sm leading-6">Simula una fuente de California. El formulario debe señalar el dominio extranjero e impedir el análisis y el envío.</p>
                        <button type="button" class="ui-btn-secondary mt-4 w-full justify-center" @click="loadExample('foreign')">Cargar prueba</button>
                    </article>
                    <article class="kg-example-card">
                        <span class="ui-badge-danger">Debe rechazar</span>
                        <h3 class="mt-3 font-black">Institución inconsistente</h3>
                        <p class="ui-muted mt-2 text-sm leading-6">Usa un dominio de UPB declarando otra universidad. La coincidencia institucional debe bloquearse.</p>
                        <button type="button" class="ui-btn-secondary mt-4 w-full justify-center" @click="loadExample('mismatch')">Cargar prueba</button>
                    </article>
                </div>
                <div class="ui-modal-footer"><p class="ui-muted mr-auto text-sm">Los casos sólo completan la ficha; usted decide cuándo ejecutar el análisis.</p><button type="button" class="ui-btn-primary" @click="examplesOpen = false">Volver al formulario</button></div>
            </section>
        </div>
        </template>

        <template x-teleport="body">
        <div x-show="detailOpen" x-cloak class="knowledge-governance ui-modal-backdrop kg-dialog-backdrop" @keydown.escape.window="detailOpen = false">
            <button type="button" class="absolute inset-0" aria-label="Cerrar detalle" @click="detailOpen = false"></button>
            <section class="ui-modal relative z-10 max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-[2rem]" data-dialogo-conocimiento role="dialog" aria-modal="true" aria-labelledby="detail-title">
                <div class="ui-modal-header"><div><p class="ui-kicker" x-text="detailKind === 'source' ? 'Fuente activa' : 'Propuesta'"></p><h2 id="detail-title" class="ui-title mt-2 text-2xl font-black" x-text="detail?.title"></h2></div><button type="button" class="ui-btn-secondary" @click="detailOpen = false">Cerrar</button></div>
                <div class="grid gap-4 p-6 sm:grid-cols-2">
                    <div class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs">Institución</p><p class="mt-2 font-bold" x-text="detail?.institution || detail?.declared_institution"></p></div>
                    <div class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs">Estado</p><p class="mt-2 font-bold" x-text="documentStatusLabel(detail?.verification_status || detail?.status)"></p></div>
                    <div x-show="detail?.campus || detail?.city" class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs">Sede y ciudad</p><p class="mt-2 text-sm" x-text="[detail?.campus, detail?.city].filter(Boolean).join(' · ')"></p></div>
                    <div class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs">Publicación / versión</p><p class="mt-2 text-sm" x-text="[detail?.publication_date || 'Fecha no informada', detail?.version].filter(Boolean).join(' · ')"></p></div>
                    <div x-show="detail?.scope" class="ui-card-soft rounded-2xl p-4 sm:col-span-2"><p class="ui-muted text-xs">Alcance</p><p class="mt-2 text-sm" x-text="detail?.scope"></p></div>
                    <div x-show="detail?.justification" class="ui-card-soft rounded-2xl p-4 sm:col-span-2"><p class="ui-muted text-xs">Aporte esperado</p><p class="mt-2 text-sm" x-text="detail?.justification"></p></div>
                    <div x-show="detail?.limitations?.length" class="ui-card-soft rounded-2xl p-4 sm:col-span-2"><p class="ui-muted text-xs">Limitaciones</p><ul class="mt-2 list-disc space-y-1 pl-5 text-sm"><template x-for="limitation in detail?.limitations || []"><li x-text="limitation"></li></template></ul></div>
                    <div x-show="detail?.assessment?.message" class="ui-alert-warning sm:col-span-2"><p class="font-bold">Validación de procedencia</p><p class="mt-2 text-sm" x-text="detail?.assessment?.message"></p></div>
                    <div x-show="detail?.reviewed_at" class="ui-card-soft rounded-2xl p-4 sm:col-span-2"><p class="ui-muted text-xs">Decisión administrativa</p><p class="mt-2 font-bold" x-text="detail?.reviewed_by_role"></p><p class="mt-2 whitespace-pre-wrap text-sm" x-text="detail?.review_note"></p></div>
                    <div x-show="detail?.status === 'APROBADA_PENDIENTE_INGESTA' && !detailPublished" class="ui-alert-warning sm:col-span-2"><p class="font-bold">La fuente todavía no está activa</p><p class="mt-2 text-sm">La fuente debe verificarse e incorporarse a la biblioteca antes de estar disponible para consultas.</p></div>
                    <div x-show="detailPublished" class="ui-alert-success sm:col-span-2"><p class="font-bold" x-text="technicalDetail ? 'Fuente incorporada al corpus documental' : 'Documento incorporado a la biblioteca'"></p><p class="mt-2 text-sm">La biblioteca puede consultar esta evidencia revisada.</p><p x-show="technicalDetail" class="mt-2 text-sm">La publicación de vectores se comprueba por separado en la extracción guardada.</p></div>
                    <a x-show="safeLink(detail?.url)" class="ui-btn-secondary justify-center break-all sm:col-span-2" :href="safeLink(detail?.url)" target="_blank" rel="noopener noreferrer">Abrir fuente oficial</a>
                </div>
                @if ($canReview)
                    <div x-show="detailKind === 'proposal' && detail?.status === 'APROBADA_PENDIENTE_INGESTA'" class="border-t p-6" style="border-color: var(--ui-border);">
                        <p class="ui-kicker">Lectura de prueba</p>
                        <h3 class="ui-title mt-2 text-lg font-black">Extraer datos del documento aprobado</h3>
                        <p class="ui-muted mt-2 text-sm" x-text="detailPublished ? 'Consulta la instantánea revisada que respalda el catálogo. Los datos nuevos de otra extracción requieren una revisión adicional.' : 'Comprueba materias, carrera y duración que aparezcan expresamente en la fuente. Esta lectura queda pendiente de revisión; todavía no alimenta la comparación del estudiante.'"></p>
                        <div class="mt-4 flex flex-wrap gap-3">
                            <button type="button" class="ui-btn-primary" :disabled="extracting" @click="extractApproved()" x-text="extracting ? 'Leyendo documento…' : 'Extraer vista previa'"></button>
                            <button type="button" class="ui-btn-secondary" :disabled="extracting" @click="extractApproved(true)">Ver extracción guardada</button>
                        </div>
                        <p class="ui-muted mt-2 text-xs" x-text="technicalDetail ? 'La consulta guardada conserva la instantánea y sus vectores. Volver a extraer descarga una versión nueva y exige revisar sus cambios.' : 'Se conserva la versión revisada del documento. Leerlo de nuevo puede encontrar cambios que necesitan una nueva revisión.'"></p>
                        <p x-show="extracting" class="ui-muted mt-3 text-sm" role="status">La lectura puede tardar unos segundos. Conserva abierta esta ventana.</p>
                        <p x-show="extractionError" x-text="extractionError" class="ui-alert-warning mt-4" role="alert"></p>
                        <div x-show="extraction" class="mt-5 space-y-4" aria-live="polite">
                            <div class="ui-card-soft rounded-2xl p-4">
                                <p class="font-black" x-text="extraction?.status === 'EXTRAIDA_PENDIENTE_REVISION' ? 'Lectura pendiente de revisión' : 'Documento no disponible para extracción'"></p>
                                <p x-show="technicalDetail" class="ui-muted mt-1 text-sm">Identificador del documento: <span class="break-all" x-text="extraction?.document_hash || 'Sin huella verificable'"></span></p>
                                <p x-show="extraction?.coverage?.fetched_documents" class="ui-muted mt-1 text-sm" x-text="`Se leyó ${extraction?.coverage?.fetched_documents} documento; los enlaces relacionados todavía son candidatos.`"></p>
                                <div class="mt-3 flex flex-wrap gap-2 text-xs">
                                    <span class="ui-badge-info" x-text="extraction?.content_type === 'application/pdf' ? `${extraction?.metrics?.pages ?? 0} páginas con texto del PDF` : 'Documento web'"></span>
                                    <span class="ui-badge-info" x-text="`${extraction?.metrics?.chunks ?? 0} fragmentos`"></span>
                                    <span class="ui-badge-info" x-text="`${extraction?.metrics?.facts ?? 0} datos detectados`"></span>
                                </div>
                            </div>
                            <x-plegable-institucional x-show="technicalDetail" icono="ph-code" :compacto="true"><x-slot:titulo>Detalle técnico de preparación para búsquedas</x-slot:titulo><div class="ui-alert-warning text-sm"><strong>Vectores:</strong> <span x-text="extraction?.embeddings?.reason || 'No se generaron embeddings.'"></span></div><p x-show="extraction?.embeddings?.status === 'GENERADOS_PENDIENTES_PUBLICACION'" class="ui-muted mt-2 text-sm" x-text="`${extraction?.embeddings?.vectors} vectores guardados y verificados para esta instantánea.`"></p></x-plegable-institucional>
                            <template x-if="extraction?.warnings?.length"><ul class="ui-alert-warning list-disc space-y-1 pl-6 text-sm"><template x-for="(warning, index) in extraction.warnings" :key="index"><li x-text="displayDocumentText(warning)"></li></template></ul></template>
                            <div x-show="extraction?.facts?.length">
                                <h4 class="font-black">Datos encontrados con respaldo</h4>
                                <div class="mt-3 space-y-3">
                                    <template x-for="(fact, index) in (extraction?.facts || []).slice(0, showAllFacts ? undefined : 12)" :key="index">
                                        <article class="ui-card-soft rounded-2xl p-4 text-sm">
                                            <p class="ui-kicker" x-text="humanize(fact.kind)"></p>
                                            <p class="mt-1 font-bold" x-text="fact.value"></p>
                                            <p class="ui-muted mt-2" x-text="fact.evidence"></p>
                                            <p x-show="fact.page || fact.section" class="ui-muted mt-2 text-xs" x-text="[fact.page ? `Página ${fact.page}` : null, fact.section].filter(Boolean).join(' · ')"></p>
                                        </article>
                                    </template>
                                </div>
                                <button x-show="(extraction?.facts?.length || 0) > 12" type="button" class="ui-btn-secondary mt-3" @click="showAllFacts = !showAllFacts" x-text="showAllFacts ? 'Mostrar menos' : `Ver los ${extraction?.facts?.length || 0} datos encontrados`"></button>
                            </div>
                            <div x-show="extraction?.chunks?.length" class="ui-card-soft rounded-2xl p-4 text-sm">
                                <h4 class="font-black">Fragmentos de muestra</h4>
                                <p class="ui-muted mt-1">Lectura parcial para revisar la extracción. El recuento completo aparece arriba.</p>
                                <template x-for="(chunk, index) in extraction?.chunks || []" :key="index"><p class="mt-3 border-t pt-3 whitespace-pre-wrap" style="border-color: var(--ui-border);" x-text="chunk.text"></p></template>
                            </div>
                            <div x-show="extraction?.linked_sources?.length" class="ui-card-soft rounded-2xl p-4 text-sm">
                                <h4 class="font-black">Otras publicaciones detectadas</h4>
                                <p class="ui-muted mt-1">Estos enlaces todavía no fueron leídos. Comprueba y propón cada documento por separado.</p>
                                <ul class="mt-3 space-y-2"><template x-for="(linked, index) in extraction?.linked_sources || []" :key="index"><li><a :href="safeLink(linked.url)" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary w-full justify-between" x-text="`${linked.title} · ${humanize(linked.kind)}`"></a></li></template></ul>
                            </div>
                        </div>
                    </div>
                @endif
                @if ($canReview)
                    <form x-show="detailKind === 'proposal' && isPending(detail?.status)" method="POST" :action="reviewUrl(detail?.proposal_id)" class="border-t p-6" style="border-color: var(--ui-border);">
                        @csrf
                        <label class="block"><span class="ui-label">Fundamento de la decisión *</span><textarea name="review_note" required minlength="3" maxlength="800" rows="3" class="ui-input mt-2 w-full"></textarea></label>
                        <div class="mt-4 flex flex-wrap justify-end gap-3"><button name="approved" value="0" class="ui-btn-secondary" type="submit">Rechazar</button><button name="approved" value="1" class="ui-btn-primary" type="submit" :disabled="detail?.assessment?.status !== 'ACEPTADA'">Aprobar incorporación</button></div>
                    </form>
                @endif
            </section>
        </div>
        </template>

        <template x-teleport="body">
        <div x-show="helpOpen" x-cloak class="knowledge-governance ui-modal-backdrop kg-dialog-backdrop" @keydown.escape.window="helpOpen = false">
            <button type="button" class="absolute inset-0" aria-label="Cerrar ayuda" @click="helpOpen = false"></button>
            <section class="ui-modal relative z-10 w-full max-w-2xl rounded-[2rem]" data-dialogo-conocimiento role="dialog" aria-modal="true" aria-labelledby="help-title">
                <div class="ui-modal-header"><h2 id="help-title" class="ui-title text-2xl font-black">Criterios de incorporación</h2><button type="button" class="ui-btn-secondary" @click="helpOpen = false">Cerrar</button></div>
                <div class="space-y-4 p-6 text-sm leading-6"><p>Primero se revisa si la fuente pertenece a una universidad boliviana y si aporta información académica comprobable.</p><p>Dirección y Administración pueden proponer. Solo Administración aprueba. Después se verifica e incorpora el documento a la biblioteca antes de que el tutor pueda consultarlo.</p><p class="ui-alert-warning">Una URL extranjera o una universidad que no coincide con su dominio queda bloqueada. Un dominio boliviano desconocido permanece en revisión y no puede aprobarse hasta verificarlo.</p></div>
            </section>
        </div>
        </template>

        <template x-teleport="body">
            <div x-show="publicacionOpen" x-cloak class="knowledge-governance ui-modal-backdrop kg-dialog-backdrop kg-publicacion-backdrop" @keydown.escape.window="publicacionOpen = false">
                <button type="button" class="absolute inset-0" aria-label="Cerrar publicación" @click="publicacionOpen = false"></button>
                <section class="ui-modal kg-publicacion-modal relative z-10 w-full rounded-[2rem]" :class="{'kg-publicacion-sin-extracto': muestraLecturaPublicacion && !lecturaPublicacion}" data-dialogo-conocimiento role="dialog" aria-modal="true" aria-labelledby="publicacion-title">
                    <div class="ui-modal-header"><div><p class="ui-kicker">Página oficial de la universidad</p><h2 id="publicacion-title" class="ui-title text-xl font-black">Publicación universitaria</h2></div><button x-ref="cerrarPublicacion" type="button" class="ui-btn-secondary" @click="publicacionOpen = false">Cerrar</button></div>
                    <div class="kg-publicacion-aviso">
                        <p class="ui-muted text-sm" x-text="muestraLecturaPublicacion ? motivoLecturaPublicacion : 'Si la página no aparece, cambie a la lectura del documento o abra la publicación oficial.'"></p>
                        <button type="button" class="ui-btn-secondary" x-show="!muestraLecturaPublicacion" @click="publicacionLectura = true; $nextTick(() => $refs.cerrarPublicacion.focus())"><i class="ph-duotone ph-file-text" aria-hidden="true"></i> Ver lectura del documento</button>
                        <a :href="publicacionUrl" target="_blank" rel="noopener noreferrer" class="ui-btn-secondary"><i class="ph-duotone ph-arrow-square-out" aria-hidden="true"></i> Abrir publicación oficial</a>
                    </div>
                    <template x-if="publicacionOpen && !muestraLecturaPublicacion">
                        <iframe class="kg-publicacion-marco" :src="publicacionUrl" x-on:error="publicacionLectura = true" sandbox="allow-scripts" referrerpolicy="no-referrer" title="Publicación universitaria"></iframe>
                    </template>
                    <div x-show="muestraLecturaPublicacion" class="kg-dialog-cuerpo kg-publicacion-lectura">
                        <template x-if="lecturaPublicacion">
                            <article>
                                <p class="ui-kicker">Extracto del documento comprobado</p>
                                <h3 class="ui-title text-xl font-black mt-2" x-text="lecturaPublicacion.title"></h3>
                                <p class="ui-muted mt-2" x-text="lecturaPublicacion.assessment?.recognized_institution"></p>
                                <p class="kg-publicacion-extracto mt-6" x-text="lecturaPublicacion.excerpt"></p>
                                <p class="ui-muted text-sm mt-6">El extracto puede omitir secciones, imágenes y tablas. Consulte la publicación completa para verificar su alcance y vigencia.</p>
                            </article>
                        </template>
                        <div x-show="!lecturaPublicacion" class="ui-card-soft p-6 rounded-2xl">
                            <i class="ph-duotone ph-file-magnifying-glass text-3xl" aria-hidden="true"></i>
                            <h3 class="ui-title font-black mt-3">No hay un extracto académico disponible</h3>
                            <p class="ui-muted mt-3" x-text="publicacionComprobada?.message || previewError || 'Compruebe la publicación para obtener su texto. Puede abrir el enlace oficial mientras tanto.'"></p>
                            <button type="button" class="ui-btn-primary mt-5" x-show="!publicacionComprobada && serviceAvailable" @click="inspectUrl()" :disabled="previewChecking || canPropose === false" x-text="previewChecking ? 'Comprobando publicación…' : 'Comprobar publicación'"></button>
                        </div>
                    </div>
                </section>
            </div>
        </template>
    </div>
@endsection
