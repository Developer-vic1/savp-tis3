@extends('layouts.app')

@section('title', 'Gestión del conocimiento')

@php
    $overview = $result->available ? $result->data : [];
    $sources = data_get($overview, 'sources', []);
    $proposals = data_get($overview, 'proposals', []);
    $trustedUniversities = data_get($overview, 'trusted_universities', []);
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
            canPropose: @js($canPropose),
            initialSource: @js($fuenteInicial ?? []),
            csrf: @js(csrf_token()),
            trustedDomains: @js(collect($trustedUniversities)->pluck('domain')->values()),
            trustedInstitutions: @js(collect($trustedUniversities)->pluck('institution')->values()),
            universities: @js($trustedUniversities),
            sources: @js($sources),
            serviceAvailable: @js($result->available),
            reviewUrlTemplate: @js(route('conocimiento.fuentes.revisar', ['proposalId' => '__PROPOSAL__'])),
        })">
        <section class="ui-card card-shadow rounded-[2rem] p-5 sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.18em]" style="color: var(--ui-primary);">Aporte Ingenieril · {{ $actor }}</p>
                    <h1 class="ui-title mt-2 text-2xl font-black sm:text-3xl">Gestión inteligente del conocimiento</h1>
                    <p class="ui-muted mt-3">Analiza páginas universitarias bolivianas antes de incorporarlas. El sistema revisa dominio, institución, finalidad académica, formato, fecha y duplicidad; ninguna fuente pasa directamente al corpus.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="ui-btn-primary" @click="checkConnection()"><i class="ph-duotone ph-plugs-connected" aria-hidden="true"></i> Comprobar conexión</button>
                    <button type="button" class="ui-btn-secondary" @click="tutorOpen = true"><i class="ph-duotone ph-chats" aria-hidden="true"></i> Probar tutor</button>
                    <button type="button" class="ui-btn-secondary" @click="helpOpen = true"><i class="ph-duotone ph-question" aria-hidden="true"></i> Cómo funciona</button>
                </div>
            </div>
            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                <article class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs font-semibold uppercase tracking-wider">Fuentes activas</p><p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">{{ $result->available ? data_get($overview, 'source_count', 'No disponible') : 'No disponible' }}</p></article>
                <article class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs font-semibold uppercase tracking-wider">En revisión</p><p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">{{ $result->available ? $pending : 'No disponible' }}</p></article>
                <article class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs font-semibold uppercase tracking-wider">Universidades verificadas</p><p class="mt-2 text-2xl font-black" style="color: var(--ui-text);">{{ $result->available ? count($trustedUniversities) : 'No disponible' }}</p></article>
            </div>
            <div class="mt-5 flex flex-wrap gap-2 text-xs">
                <span class="{{ $result->available ? 'ui-badge-success' : 'ui-badge-warning' }}">{{ $result->available ? 'Laravel conectado a FastAPI' : 'Conexión no disponible' }}</span>
                <span class="ui-badge-success"><i class="ph-duotone ph-lock-key" aria-hidden="true"></i> Acceso restringido</span>
                <span class="ui-badge-info"><i class="ph-duotone ph-shield-check" aria-hidden="true"></i> Revisión obligatoria</span>
                <span class="ui-badge-muted"><i class="ph-duotone ph-database" aria-hidden="true"></i> Incorporación supervisada</span>
                <span class="ui-badge-info"><i class="ph-duotone ph-books" aria-hidden="true"></i> Conocimiento local · Sin modelos de IA</span>
            </div>
        </section>

        <section class="ui-card card-shadow rounded-[2rem] p-5 sm:p-6" aria-label="Progreso de incorporación">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
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

                    <label class="block md:col-span-2">
                        <span class="ui-label">URL HTTPS oficial *</span>
                        <input name="url" type="url" value="{{ old('url') }}" required maxlength="2000" pattern="https://.*"
                            class="ui-input mt-2 w-full" :class="fieldClass('url')" @blur="touch('url')"
                            aria-describedby="url-help" placeholder="https://universidad.edu.bo/carrera-o-documento.pdf">
                        <span id="url-help" class="kg-field-help" :class="fieldHelpClass('url')" x-text="urlMessage"></span>
                        @error('url')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <section class="ui-card-soft rounded-2xl p-4 md:col-span-2" aria-label="Support inteligente de la fuente" :aria-busy="previewChecking">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <h3 class="font-bold"><i class="ph-duotone ph-detective" aria-hidden="true"></i> Support inteligente · Documento oficial</h3>
                            <button type="button" class="ui-btn-secondary" @click="inspectUrl()" :disabled="!canPropose || previewChecking || !values.url || !!urlIssue">
                                <i class="ph-duotone ph-magnifying-glass" aria-hidden="true"></i>
                                <span x-text="previewChecking ? 'Comprobando…' : 'Comprobar URL y detectar datos'"></span>
                            </button>
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
                                <p class="ui-muted" x-text="`${preview?.duplicate?.id || ''} · ${humanize(preview?.duplicate?.status)} · ${preview?.duplicate?.kind === 'CORPUS' ? 'Ya incorporada' : 'Ya existe en estudio o revisión'}`"></p>
                            </div>
                            <div x-show="preview?.title && !preview?.duplicate" class="text-sm space-y-2">
                                <p class="ui-muted">Título detectado en la publicación:</p>
                                <p class="font-bold" x-text="preview?.title"></p>
                                <p class="ui-muted" x-text="preview?.suggested_fields?.declared_institution"></p>
                                <x-plegable-institucional x-show="preview?.excerpt" class="ui-muted" icono="ph-file-text"><x-slot:titulo>Ver extracto del documento</x-slot:titulo>
                                    <p class="mt-2 leading-6" x-text="preview?.excerpt"></p>
                                </x-plegable-institucional>
                                <button type="button" class="ui-btn-primary" @click="openFichaPreview()" :disabled="!preview?.can_use">Revisar sugerencias y vista previa</button>
                                <x-plegable-institucional x-show="preview?.reading_fragments?.length" class="ui-card-soft p-4" icono="ph-stack"><x-slot:titulo>Fragmentos preparados para revisar <span x-text="preview?.reading_fragments?.length || 0"></span></x-slot:titulo>
                                    <p class="ui-muted text-xs mt-2">Lectura preliminar del extracto publicado. La incorporación completa requiere revisión, aprobación y procesamiento de la fuente.</p>
                                    <div class="grid gap-3 mt-3 sm:grid-cols-2"><template x-for="(fragmento,indice) in preview?.reading_fragments || []" :key="fragmento.id"><article class="ui-card p-4"><p class="ui-kicker" x-text="'Fragmento '+(indice+1)"></p><p class="ui-muted text-sm mt-2 leading-6" x-text="fragmento.text"></p></article></template></div>
                                </x-plegable-institucional>
                                <p class="ui-muted text-xs">Se completan automáticamente sólo los campos vacíos. Revise la ficha y elija qué sugerencias reemplazan sus datos; no inventamos sede, ciudad, versión ni justificación.</p>
                            </div>
                            <template x-for="warning in preview?.warnings || []" :key="warning"><p class="ui-muted text-xs" x-text="warning"></p></template>
                            <p class="ui-muted text-xs" x-text="preview?.http_status ? `Respuesta HTTP ${preview.http_status} · Comprobación: ${preview.checked_at}` : 'La duplicidad o el bloqueo pueden detectarse sin descargar la página.'"></p>
                            <a x-show="preview?.reachable === true && preview?.http_status === 200 && safeLink(preview?.final_url)" :href="safeLink(preview?.final_url)" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold underline">Abrir publicación oficial en otra pestaña</a>
                        </div>
                        <p class="ui-muted mt-3 text-xs">Reglas y extracción documental, sin Llama, LM Studio ni modelos de IA. Vista previa en texto; no ejecutamos código de la página. Confirmar no equivale a publicar.</p>
                    </section>

                    <label class="block md:col-span-2">
                        <span class="ui-label">Título oficial de la fuente *</span>
                        <input name="title" value="{{ old('title') }}" required minlength="3" maxlength="240"
                            class="ui-input mt-2 w-full" :class="fieldClass('title')" @blur="touch('title')"
                            aria-describedby="title-help" placeholder="Ej. Ingeniería de Sistemas — Malla Curricular 2026">
                        <span id="title-help" class="kg-field-help" :class="fieldHelpClass('title')" x-text="fieldMessage('title', 'Use el título publicado por la universidad.')"></span>
                        @error('title')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="ui-label">Universidad declarada *</span>
                        <input name="declared_institution" value="{{ old('declared_institution') }}" required minlength="3" maxlength="240"
                            class="ui-input mt-2 w-full" :class="fieldClass('declared_institution')" @blur="touch('declared_institution')"
                            list="trusted-universities" aria-describedby="institution-help" placeholder="Seleccione una universidad verificada">
                        <datalist id="trusted-universities">
                            @foreach ($trustedUniversities as $university)<option value="{{ data_get($university, 'institution') }}"></option>@endforeach
                        </datalist>
                        <span id="institution-help" class="kg-field-help" :class="fieldHelpClass('declared_institution')" x-text="institutionMessage"></span>
                        @error('declared_institution')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="ui-label">Tipo documental *</span>
                        <select name="source_type" required class="ui-input mt-2 w-full" :class="fieldClass('source_type')" @blur="touch('source_type')">
                            <option value="">Seleccione</option>
                            <option value="OFFICIAL_CURRICULUM_PDF" @selected(old('source_type') === 'OFFICIAL_CURRICULUM_PDF')>Malla o plan curricular PDF</option>
                            <option value="OFFICIAL_CAREER_HTML" @selected(old('source_type') === 'OFFICIAL_CAREER_HTML')>Página oficial de carrera</option>
                            <option value="OFFICIAL_REGULATION_PDF" @selected(old('source_type') === 'OFFICIAL_REGULATION_PDF')>Reglamento oficial PDF</option>
                            <option value="OFFICIAL_UNIVERSITY_PAGE" @selected(old('source_type') === 'OFFICIAL_UNIVERSITY_PAGE')>Otra página universitaria oficial</option>
                        </select>
                        <span class="kg-field-help" :class="fieldHelpClass('source_type')" x-text="fieldMessage('source_type', 'El tipo debe coincidir con el documento enlazado.')"></span>
                        @error('source_type')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block md:col-span-2">
                        <span class="ui-label">Alcance académico *</span>
                        <textarea name="scope" required minlength="10" maxlength="500" rows="3" class="ui-input mt-2 w-full"
                            :class="fieldClass('scope')" @blur="touch('scope')"
                            placeholder="Carrera, programa, requisitos, perfil o malla que cubre la fuente">{{ old('scope') }}</textarea>
                        <div class="kg-field-row"><span class="kg-field-help" :class="fieldHelpClass('scope')" x-text="fieldMessage('scope', 'Indique carrera, malla, perfil o requisito cubierto.')"></span><span class="kg-counter" x-text="characterCount('scope', 500)"></span></div>
                        @error('scope')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="ui-label">Sede o campus *</span>
                        <input name="campus" value="{{ old('campus') }}" required minlength="2" maxlength="160" class="ui-input mt-2 w-full" :class="fieldClass('campus')" @blur="touch('campus')" placeholder="La Paz, Cochabamba, Nacional...">
                        <span class="kg-field-help" :class="fieldHelpClass('campus')" x-text="fieldMessage('campus', 'Indique la sede a la que aplica la fuente.')"></span>
                        @error('campus')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="ui-label">Ciudad *</span>
                        <input name="city" value="{{ old('city') }}" required minlength="2" maxlength="120" class="ui-input mt-2 w-full" :class="fieldClass('city')" @blur="touch('city')" placeholder="La Paz">
                        <span class="kg-field-help" :class="fieldHelpClass('city')" x-text="fieldMessage('city', 'Ciudad boliviana asociada a la publicación.')"></span>
                        @error('city')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="ui-label">Fecha o gestión de publicación</span>
                        <input name="publication_date" value="{{ old('publication_date') }}" maxlength="10" pattern="\d{4}(-\d{2}(-\d{2})?)?"
                            class="ui-input mt-2 w-full" :class="fieldClass('publication_date')" @blur="touch('publication_date')" placeholder="2026 o 2026-03">
                        <span class="kg-field-help" :class="fieldHelpClass('publication_date')" x-text="dateMessage"></span>
                        @error('publication_date')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block">
                        <span class="ui-label">Versión o gestión *</span>
                        <input name="version" value="{{ old('version') }}" required maxlength="120" class="ui-input mt-2 w-full" :class="fieldClass('version')" @blur="touch('version')" placeholder="Gestión 2026">
                        <span class="kg-field-help" :class="fieldHelpClass('version')" x-text="fieldMessage('version', 'Ejemplo: Gestión 2026, versión 2 o vigente.')"></span>
                        @error('version')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block md:col-span-2">
                        <span class="ui-label">¿Qué conocimiento aportará? *</span>
                        <textarea name="justification" required minlength="20" maxlength="1000" rows="4" class="ui-input mt-2 w-full"
                            :class="fieldClass('justification')" @blur="touch('justification')"
                            placeholder="Explique cómo mejora la investigación, comparación o preparación académica">{{ old('justification') }}</textarea>
                        <div class="kg-field-row"><span class="kg-field-help" :class="fieldHelpClass('justification')" x-text="fieldMessage('justification', 'Explique el valor concreto para investigación o acompañamiento.')"></span><span class="kg-counter" x-text="characterCount('justification', 1000)"></span></div>
                        @error('justification')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <label class="block md:col-span-2">
                        <span class="ui-label">Limitaciones conocidas</span>
                        <textarea name="limitations_text" maxlength="2000" rows="3" class="ui-input mt-2 w-full"
                            :class="fieldClass('limitations_text')" @blur="touch('limitations_text')"
                            placeholder="Una limitación por línea: no incluye costos, no define admisión, vigencia por confirmar...">{{ old('limitations_text') }}</textarea>
                        <div class="kg-field-row"><span class="kg-field-help" :class="fieldHelpClass('limitations_text')" x-text="fieldMessage('limitations_text', 'Una limitación por línea; máximo 10 líneas útiles.')"></span><span class="kg-counter" x-text="characterCount('limitations_text', 2000)"></span></div>
                        @error('limitations_text')<span class="ui-error">{{ $message }}</span>@enderror
                    </label>

                    <div class="flex flex-col-reverse gap-3 md:col-span-2 sm:flex-row sm:items-center sm:justify-end">
                        <div class="mr-auto text-sm" aria-live="polite">
                            <p class="ui-muted" x-show="analysisInvalidated" x-cloak><i class="ph-duotone ph-warning-circle" aria-hidden="true"></i> El formulario cambió. Ejecute nuevamente el análisis.</p>
                            <p class="font-semibold" style="color: var(--ui-success);" x-show="analysisToken" x-cloak><i class="ph-duotone ph-check-circle" aria-hidden="true"></i> <span x-text="`Análisis vigente: ${Math.max(0, Math.ceil((expiresAt - clockNow) / 60000))} min restantes.`"></span></p>
                        </div>
                        <button type="button" class="ui-btn-secondary" @click="resetForm()">Limpiar</button>
                        <button type="button" class="ui-btn-secondary" @click="openFichaPreview()">Vista previa de la ficha</button>
                        <button type="button" class="ui-btn-primary" @click="analyze()" :disabled="analyzing || previewChecking || (preview && !preview.can_use) || !serviceAvailable || submitting || !canPropose">
                            <span x-show="!analyzing"><i class="ph-duotone ph-magnifying-glass" aria-hidden="true"></i> Analizar fuente</span>
                            <span x-show="analyzing" x-cloak>Analizando...</span>
                        </button>
                        <button type="submit" class="ui-btn-primary" :disabled="!analysisToken || submitting || !canPropose"
                            :class="(!analysisToken || submitting) && 'cursor-not-allowed opacity-60'">
                            <span x-text="submitting ? 'Enviando...' : 'Enviar a revisión'"></span>
                        </button>
                    </div>
                </form>
            </section>

            <aside class="space-y-5">
                <section class="ui-card card-shadow rounded-[2rem] p-6">
                    <p class="ui-kicker">Autorización del servidor</p>
                    <h2 class="ui-title mt-2 text-xl font-black">Tus permisos · {{ $actor }}</h2>
                    <ul class="mt-4 space-y-3 text-sm">
                        @foreach ($accessPermissions as $label => $allowed)
                            <li class="flex items-center justify-between gap-3"><span class="ui-muted">{{ $label }}</span><span class="{{ $allowed ? 'ui-badge-success' : 'ui-badge-warning' }}">{{ $allowed ? 'Permitido' : 'Sin permiso' }}</span></li>
                        @endforeach
                    </ul>
                    <p class="ui-muted mt-4 text-xs">El rol y el permiso se comprueban en cada solicitud. Docentes y estudiantes no tienen acceso a esta gestión.</p>
                </section>
                <section class="ui-card card-shadow rounded-[2rem] p-6">
                    <p class="ui-kicker">Soporte inteligente</p>
                    <h2 class="ui-title mt-2 text-xl font-black">Revisión en tiempo real</h2>
                    <div class="mt-4" aria-live="polite">
                        <div class="flex items-center justify-between text-xs font-bold"><span>Preparación de la ficha</span><span x-text="`${validationScore}%`"></span></div>
                        <div class="kg-progress mt-2"><span :style="`width: ${validationScore}%`"></span></div>
                        <p class="ui-muted mt-2 text-xs" x-text="`${passedChecks} de ${frontendChecks.length} controles listos`"></p>
                    </div>
                    <div class="mt-5 space-y-3">
                        <template x-for="item in frontendChecks" :key="item.label">
                            <div class="ui-card-soft flex gap-3 rounded-2xl p-4" :class="item.ok ? 'kg-check-ok' : 'kg-check-pending'">
                                <i class="ph-duotone mt-0.5 text-lg" :class="item.ok ? 'ph-check-circle' : 'ph-circle-dashed'" :style="`color: ${item.ok ? 'var(--ui-success)' : 'var(--ui-warning)'}`" aria-hidden="true"></i>
                                <div><p class="text-sm font-bold" style="color: var(--ui-text);" x-text="item.label"></p><p class="ui-muted mt-1 text-xs leading-5" x-text="item.help"></p></div>
                            </div>
                        </template>
                    </div>
                </section>

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
                        <p class="ui-muted mt-3 text-sm">La vista previa comprueba y lee el documento con límites de seguridad; el análisis revisa la ficha. Ninguno acredita por sí solo autenticidad o vigencia. La aprobación queda pendiente de ingesta controlada.</p>
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
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div><p class="ui-kicker">Corpus validado</p><h2 class="ui-title mt-2 text-2xl font-black">Fuentes activas</h2></div>
                    <input aria-label="Buscar fuentes activas" type="search" x-model="sourceSearch" class="ui-input w-full sm:max-w-sm" placeholder="Buscar universidad o fuente">
                </div>
                <p class="ui-muted mt-4 text-sm" x-show="!sources.some(source => matchesSource(source))" x-cloak>No hay fuentes que coincidan con la búsqueda.</p>
                <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($sources as $source)
                        <article class="ui-card-soft rounded-2xl p-5" x-show="matchesSource(@js($source))">
                            <span class="ui-badge-success">{{ data_get($source, 'verification_status') }}</span>
                            <h3 class="mt-3 font-black" style="color: var(--ui-text);">{{ data_get($source, 'title') }}</h3>
                            <p class="ui-muted mt-2 text-sm">{{ data_get($source, 'institution') }}</p>
                            <button type="button" class="ui-btn-secondary mt-4 w-full justify-center" @click="openDetail('source', @js($source))">Ver trazabilidad</button>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="ui-card card-shadow rounded-[2rem] p-6 sm:p-8">
                <div><p class="ui-kicker">Gobierno documental</p><h2 class="ui-title mt-2 text-2xl font-black">Cola de incorporación</h2></div>
                <div class="mt-6 overflow-x-auto">
                    <table class="ui-table min-w-full">
                        <thead><tr><th>Fuente</th><th>Universidad</th><th>Tipo</th><th>Estado</th><th>Acción</th></tr></thead>
                        <tbody>
                            @forelse ($proposals as $proposal)
                                <tr>
                                    <td><p class="font-bold">{{ data_get($proposal, 'title') }}</p><p class="ui-muted text-xs">{{ data_get($proposal, 'proposal_id') }}</p></td>
                                    <td>{{ data_get($proposal, 'declared_institution') }}</td>
                                    <td>{{ str(data_get($proposal, 'source_type', 'Sin tipo'))->replace('_', ' ')->lower()->ucfirst() }}</td>
                                    <td><span class="ui-badge">{{ str(data_get($proposal, 'status'))->replace('_', ' ') }}</span></td>
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

        <div x-show="fichaPreviewOpen" x-cloak class="ui-modal-backdrop fixed inset-0 z-[70] flex items-center justify-center p-4" @keydown.escape.window="fichaPreviewOpen = false">
            <button type="button" class="absolute inset-0" aria-label="Cerrar vista previa" @click="fichaPreviewOpen = false"></button>
            <section class="ui-modal relative z-10 max-h-[90vh] w-full max-w-5xl overflow-y-auto rounded-[2rem]" role="dialog" aria-modal="true" aria-labelledby="ficha-preview-title">
                <div class="ui-modal-header"><div><p class="ui-kicker">Borrador · no guardado ni aprobado</p><h2 id="ficha-preview-title" class="ui-title mt-2 text-2xl font-black">Vista previa de la ficha</h2></div><button type="button" class="ui-btn-secondary" @click="fichaPreviewOpen = false">Cerrar</button></div>
                <div class="space-y-5 p-6">
                    <p class="ui-alert-warning text-sm">Esta es la ficha del formulario, no una publicación. Los campos pendientes deben completarse con información comprobable antes del análisis.</p>
                    <div class="ui-card-soft rounded-2xl p-4"><p class="font-bold" x-text="values.title || 'Título oficial pendiente'"></p><p class="ui-muted mt-1 text-sm" x-text="values.declared_institution || 'Universidad pendiente'"></p><p class="ui-muted mt-2 text-xs" x-text="preview?.requested_url === values.url ? `Comprobación de URL: ${humanize(preview?.status)}` : 'URL aún no comprobada'"></p></div>
                    <dl class="grid gap-4 md:grid-cols-2">
                        <template x-for="field in fichaFields" :key="field.name"><div class="ui-card-soft rounded-2xl p-4"><dt class="ui-label" x-text="field.label"></dt><dd class="mt-2 whitespace-pre-line text-sm" x-text="field.name === 'source_type' && field.value ? sourceTypeLabel(field.value) : field.value || 'Sin completar'"></dd><p class="ui-muted mt-2 text-xs" x-show="field.error" x-text="field.error"></p></div></template>
                    </dl>
                    <section class="space-y-3" aria-label="Sugerencias documentales">
                        <h3 class="ui-title text-lg font-bold">Sugerencias de la publicación</h3>
                        <p class="ui-muted text-sm">Comparamos lo escrito con los datos extraídos. Los campos ya completados no se reemplazan sin su selección. El alcance sugerido es un extracto que debe revisar.</p>
                        <p x-show="!suggestionRows.length" class="ui-muted text-sm">Compruebe una URL válida para obtener sugerencias. No se generan datos sin evidencia documental.</p>
                        <template x-for="suggestion in suggestionRows" :key="suggestion.name"><label class="ui-card-soft block rounded-2xl p-4"><span class="flex items-center gap-3"><input type="checkbox" x-model="suggestionSelections[suggestion.name]" :disabled="!suggestion.changed || canPropose === false || submitting || previewChecking" :aria-label="`Aplicar sugerencia: ${suggestion.label}`"><span class="font-bold" x-text="suggestion.label"></span><span class="ui-badge ml-auto" x-show="!suggestion.changed">Ya aplicado</span></span><span class="ui-muted mt-2 block text-xs" x-show="suggestion.changed" x-text="`Actual: ${suggestion.value || 'Sin completar'}`"></span><span class="mt-2 block whitespace-pre-line text-sm" x-text="suggestion.name === 'source_type' ? sourceTypeLabel(suggestion.suggested) : suggestion.suggested"></span></label></template>
                        <p x-show="suggestionMessage" class="ui-alert-success text-sm" role="status" x-text="suggestionMessage"></p>
                    </section>
                </div>
                <div class="ui-modal-footer flex flex-wrap gap-3"><button type="button" class="ui-btn-secondary" @click="fichaPreviewOpen = false">Volver al formulario</button><button type="button" class="ui-btn-primary" @click="applySelectedSuggestions()" :disabled="!selectedSuggestionCount || canPropose === false || submitting || previewChecking" x-text="`Aplicar seleccionadas (${selectedSuggestionCount})`"></button></div>
            </section>
        </div>

        <div x-show="connectionOpen" x-cloak class="ui-modal-backdrop fixed inset-0 z-[70] flex items-center justify-center p-4" @keydown.escape.window="connectionOpen = false">
            <button type="button" class="absolute inset-0" aria-label="Cerrar conexión" @click="connectionOpen = false"></button>
            <section class="ui-modal relative z-10 max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-[2rem]" role="dialog" aria-modal="true" aria-labelledby="connection-title">
                <div class="ui-modal-header"><h2 id="connection-title" class="ui-title text-2xl font-black">Conexión del sistema</h2><button type="button" class="ui-btn-secondary" @click="connectionOpen = false">Cerrar</button></div>
                <div class="space-y-4 p-6" aria-live="polite">
                    <p class="ui-muted" x-show="checkingConnection">Comprobando servidor, autenticación interna, fuentes y tutor...</p>
                    <p x-show="connectionError" class="ui-alert-danger" x-text="connectionError"></p>
                    <template x-for="check in connection?.checks || []" :key="check.label"><div class="ui-card-soft flex items-center gap-3 rounded-2xl p-4"><i class="ph-duotone text-xl" :class="check.available ? 'ph-check-circle' : 'ph-warning-circle'" aria-hidden="true"></i><p class="font-bold" x-text="check.label"></p><span class="ml-auto" :class="check.available ? 'ui-badge-success' : 'ui-badge-warning'" x-text="check.available ? 'Conectado' : 'Pendiente'"></span></div></template>
                    <p x-show="connection" class="ui-muted" x-text="`Fuentes disponibles: ${connection?.source_count ?? 'No disponible'}`"></p>
                    <p x-show="connection" :class="connection?.connected ? 'ui-alert-success' : 'ui-alert-warning'" x-text="connection?.message"></p>
                    <p class="ui-muted text-sm">La comprobación usa Laravel y la credencial interna del servidor. La clave nunca se entrega al navegador. No crea propuestas ni modifica información institucional.</p>
                </div>
                <div class="ui-modal-footer"><button type="button" class="ui-btn-primary" @click="checkConnection()" :disabled="checkingConnection">Comprobar nuevamente</button></div>
            </section>
        </div>

        <div x-show="tutorOpen" x-cloak class="ui-modal-backdrop fixed inset-0 z-[70] flex items-center justify-center p-4" @keydown.escape.window="tutorOpen = false">
            <button type="button" class="absolute inset-0" aria-label="Cerrar tutor" @click="tutorOpen = false"></button>
            <section class="ui-modal relative z-10 max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-[2rem]" role="dialog" aria-modal="true" aria-labelledby="tutor-test-title">
                <div class="ui-modal-header"><div><p class="ui-kicker">Consulta de prueba · sin datos de estudiantes</p><h2 id="tutor-test-title" class="ui-title mt-2 text-2xl font-black">Tutor conectado</h2></div><button type="button" class="ui-btn-secondary" @click="tutorOpen = false">Cerrar</button></div>
                <div class="space-y-4 p-6">
                    <p class="ui-muted text-sm">La respuesta viene del mismo servicio que utiliza SAVP. El historial de esta prueba se conserva sólo mientras esta página permanece abierta.</p>
                    <div class="flex flex-wrap gap-2"><template x-for="example in ['Hola', 'Gracias', '¿Qué materias tiene el primer semestre de Sistemas UCB?']"><button type="button" class="ui-btn-secondary text-sm" @click="tutorQuestion = example" x-text="example"></button></template></div>
                    <form @submit.prevent="askTutor()" class="space-y-3">
                        <label class="block"><span class="ui-label">Pregunta de prueba</span><textarea x-model="tutorQuestion" minlength="2" maxlength="2000" required rows="3" class="ui-input mt-2 w-full" placeholder="Escribe un saludo o una pregunta académica"></textarea></label>
                        <div class="flex justify-end"><button type="submit" class="ui-btn-primary" :disabled="askingTutor || tutorQuestion.trim().length < 2" x-text="askingTutor ? 'Consultando...' : 'Consultar tutor'"></button></div>
                    </form>
                    <p x-show="tutorError" class="ui-alert-danger" role="alert" x-text="tutorError"></p>
                    <article x-show="tutorAnswer" class="ui-card-soft rounded-2xl p-5" aria-live="polite"><h3 class="font-black">Respuesta del tutor</h3><p class="mt-3 whitespace-pre-wrap text-sm leading-7" x-text="tutorAnswer?.answer"></p><div class="mt-4 space-y-2"><template x-for="source in tutorAnswer?.sources || []" :key="source.chunk_id"><a class="ui-btn-secondary flex text-sm" :href="safeLink(source.reference)" target="_blank" rel="noopener noreferrer" x-text="`${source.title} · ${source.institution}`"></a></template></div></article>
                </div>
            </section>
        </div>

        <div x-show="analysisOpen" x-cloak class="ui-modal-backdrop fixed inset-0 z-[70] flex items-center justify-center p-4" @keydown.escape.window="analysisOpen = false">
            <button type="button" class="absolute inset-0" aria-label="Cerrar análisis" @click="analysisOpen = false"></button>
            <section class="ui-modal relative z-10 max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-[2rem]" role="dialog" aria-modal="true" aria-labelledby="analysis-title">
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

        <div x-show="examplesOpen" x-cloak class="ui-modal-backdrop fixed inset-0 z-[70] flex items-center justify-center p-4" @keydown.escape.window="examplesOpen = false">
            <button type="button" class="absolute inset-0" aria-label="Cerrar casos de prueba" @click="examplesOpen = false"></button>
            <section class="ui-modal relative z-10 max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-[2rem]" role="dialog" aria-modal="true" aria-labelledby="examples-title">
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
                        <p class="ui-muted mt-2 text-sm leading-6">Carga una página oficial boliviana que ya existe en el corpus. El dominio cumple, pero la duplicidad impide enviarla.</p>
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

        <div x-show="detailOpen" x-cloak class="ui-modal-backdrop fixed inset-0 z-[70] flex items-center justify-center p-4" @keydown.escape.window="detailOpen = false">
            <button type="button" class="absolute inset-0" aria-label="Cerrar detalle" @click="detailOpen = false"></button>
            <section class="ui-modal relative z-10 max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-[2rem]" role="dialog" aria-modal="true" aria-labelledby="detail-title">
                <div class="ui-modal-header"><div><p class="ui-kicker" x-text="detailKind === 'source' ? 'Fuente activa' : 'Propuesta'"></p><h2 id="detail-title" class="ui-title mt-2 text-2xl font-black" x-text="detail?.title"></h2></div><button type="button" class="ui-btn-secondary" @click="detailOpen = false">Cerrar</button></div>
                <div class="grid gap-4 p-6 sm:grid-cols-2">
                    <div class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs">Institución</p><p class="mt-2 font-bold" x-text="detail?.institution || detail?.declared_institution"></p></div>
                    <div class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs">Estado</p><p class="mt-2 font-bold" x-text="humanize(detail?.verification_status || detail?.status)"></p></div>
                    <div x-show="detail?.campus || detail?.city" class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs">Sede y ciudad</p><p class="mt-2 text-sm" x-text="[detail?.campus, detail?.city].filter(Boolean).join(' · ')"></p></div>
                    <div class="ui-card-soft rounded-2xl p-4"><p class="ui-muted text-xs">Publicación / versión</p><p class="mt-2 text-sm" x-text="[detail?.publication_date || 'Fecha no informada', detail?.version].filter(Boolean).join(' · ')"></p></div>
                    <div x-show="detail?.scope" class="ui-card-soft rounded-2xl p-4 sm:col-span-2"><p class="ui-muted text-xs">Alcance</p><p class="mt-2 text-sm" x-text="detail?.scope"></p></div>
                    <div x-show="detail?.justification" class="ui-card-soft rounded-2xl p-4 sm:col-span-2"><p class="ui-muted text-xs">Aporte esperado</p><p class="mt-2 text-sm" x-text="detail?.justification"></p></div>
                    <div x-show="detail?.limitations?.length" class="ui-card-soft rounded-2xl p-4 sm:col-span-2"><p class="ui-muted text-xs">Limitaciones</p><ul class="mt-2 list-disc space-y-1 pl-5 text-sm"><template x-for="limitation in detail?.limitations || []"><li x-text="limitation"></li></template></ul></div>
                    <div x-show="detail?.assessment?.message" class="ui-alert-warning sm:col-span-2"><p class="font-bold">Validación de procedencia</p><p class="mt-2 text-sm" x-text="detail?.assessment?.message"></p></div>
                    <div x-show="detail?.reviewed_at" class="ui-card-soft rounded-2xl p-4 sm:col-span-2"><p class="ui-muted text-xs">Decisión administrativa</p><p class="mt-2 font-bold" x-text="detail?.reviewed_by_role"></p><p class="mt-2 whitespace-pre-wrap text-sm" x-text="detail?.review_note"></p></div>
                    <div x-show="detail?.status === 'APROBADA_PENDIENTE_INGESTA'" class="ui-alert-warning sm:col-span-2"><p class="font-bold">La fuente todavía no está activa</p><p class="mt-2 text-sm">Falta revisar la instantánea oficial, verificar SHA-256, reconstruir el corpus y comprobar sus citas. Esta pantalla no descarga ni publica automáticamente el contenido.</p></div>
                    <a x-show="safeLink(detail?.url)" class="ui-btn-secondary justify-center break-all sm:col-span-2" :href="safeLink(detail?.url)" target="_blank" rel="noopener noreferrer">Abrir fuente oficial</a>
                </div>
                @if ($canReview)
                    <form x-show="detailKind === 'proposal' && isPending(detail?.status)" method="POST" :action="reviewUrl(detail?.proposal_id)" class="border-t p-6" style="border-color: var(--ui-border);">
                        @csrf
                        <label class="block"><span class="ui-label">Fundamento de la decisión *</span><textarea name="review_note" required minlength="3" maxlength="800" rows="3" class="ui-input mt-2 w-full"></textarea></label>
                        <div class="mt-4 flex flex-wrap justify-end gap-3"><button name="approved" value="0" class="ui-btn-secondary" type="submit">Rechazar</button><button name="approved" value="1" class="ui-btn-primary" type="submit" :disabled="detail?.assessment?.status !== 'ACEPTADA'">Aprobar para ingesta</button></div>
                    </form>
                @endif
            </section>
        </div>

        <div x-show="helpOpen" x-cloak class="ui-modal-backdrop fixed inset-0 z-[70] flex items-center justify-center p-4" @keydown.escape.window="helpOpen = false">
            <button type="button" class="absolute inset-0" aria-label="Cerrar ayuda" @click="helpOpen = false"></button>
            <section class="ui-modal relative z-10 w-full max-w-2xl rounded-[2rem]" role="dialog" aria-modal="true" aria-labelledby="help-title">
                <div class="ui-modal-header"><h2 id="help-title" class="ui-title text-2xl font-black">Criterios de incorporación</h2><button type="button" class="ui-btn-secondary" @click="helpOpen = false">Cerrar</button></div>
                <div class="space-y-4 p-6 text-sm leading-6"><p>El análisis no “entrena” automáticamente el sistema. Primero determina si la fuente pertenece a una universidad boliviana y si su contenido declarado aporta evidencia académica.</p><p>Dirección y Administración pueden proponer. Solo Administración aprueba. Después se genera una instantánea local, se verifica su hash y se reconstruye el corpus antes de que el tutor pueda citarla.</p><p class="ui-alert-warning">Una URL extranjera o una universidad que no coincide con su dominio queda bloqueada. Un dominio boliviano desconocido permanece en revisión y no puede aprobarse hasta verificarlo.</p></div>
            </section>
        </div>
    </div>
@endsection
