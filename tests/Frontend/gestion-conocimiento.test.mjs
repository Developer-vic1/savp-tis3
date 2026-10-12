import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import knowledgeGovernance from '../../resources/js/gestion-conocimiento.js';

test('biblioteca agrupa malla y perfil bajo la misma carrera e institución', () => {
    const state = component();
    const manifest = JSON.parse(readFileSync(new URL('../../ai-service/data/sources/sources.json', import.meta.url), 'utf8'));
    state.sources = manifest.sources.map(({ source_id, institution, title, url, verification_status }) => ({ source_id, institution, title, url, verification_status }));
    const ucb = state.libraryInstitutions.find(item => item.name === 'Universidad Católica Boliviana San Pablo, Sede La Paz');
    assert.ok(ucb);
    const sistemas = ucb.careers.filter(career => career.name === 'Ingeniería de Sistemas');
    assert.equal(sistemas.length, 1);
    assert.equal(sistemas[0].documents.length, 2);
    assert.deepEqual(sistemas[0].documents.map(item => item.title), ['Malla Curricular, Gestión 2026', 'Perfil Profesional y Áreas de Estudio']);
    assert.equal(ucb.careers.find(career => career.name === 'Psicología').documents.length, 2);
    const unifranz = state.libraryInstitutions.find(item => item.name === 'Universidad Privada Franz Tamayo - UNIFRANZ, sedes publicadas');
    assert.equal(unifranz.careers.find(career => career.name === 'Psicología').documents.length, 1);
    assert.equal(state.libraryGroups.find(group => group.value === 'UNIVERSITARIAS').count, 14);
    state.sourceSearch = 'sistemas';
    assert.equal(state.libraryGroups.find(group => group.value === 'UNIVERSITARIAS').count, 6);
    assert.equal(state.filteredSources.length, 6);
    state.sourceGroup = 'MINISTERIALES';
    assert.equal(state.filteredSources.length, 0);
    state.sourceSearch = '';
    assert.ok(state.libraryInstitutions.every(item => item.careers.length === 0));
});

test('directorio muestra instituciones y la biblioteca cuenta documentos por separado', async t => {
    const state = component();
    state.directorySummaryUrl = '/conocimiento/directorio-nacional/resumen';
    state.sources = Array.from({ length: 14 }, (_, index) => ({ source_id: `U${index}`, institution: 'Universidad Ejemplo' }));
    const institutions = Array.from({ length: 60 }, (_, index) => ({ name: `Universidad ${index + 1}`, departments: ['La Paz'] }));
    t.mock.method(globalThis, 'fetch', async () => ({ ok: true, json: async () => ({
        institution_count: 60, departments: { 'La Paz': 60 }, institutions,
    }) }));
    await state.loadDirectorySummary();
    assert.equal(state.directorySummary.institution_count, 60);
    assert.equal(state.visibleDirectoryInstitutions.length, 6);
    state.directoryExpanded = true;
    assert.equal(state.visibleDirectoryInstitutions.length, 60);
    assert.deepEqual(state.libraryGroups.find(group => group.value === 'UNIVERSITARIAS'), {
        value: 'UNIVERSITARIAS', label: 'Documentos universitarios', count: 14,
    });
});

test('fuente sin carrera explícita queda como publicación institucional', () => {
    const state = component();
    state.sources = [{ source_id: 'UNO', title: 'Página institucional', institution: 'Universidad Ejemplo', source_type: 'OFFICIAL_UNIVERSITY_PAGE' }];
    const institution = state.libraryInstitutions[0];
    assert.equal(institution.careers.length, 0);
    assert.equal(institution.documents[0].title, 'Página institucional');
});

test('guía de ficha indica la próxima acción según la publicación y campos pendientes', () => {
    const state = component();
    state.values.url = '';
    assert.equal(state.nextPreparationAction.field, 'url');
    state.values.url = 'https://www.upb.edu/';
    assert.equal(state.nextPreparationAction.mode, 'preview');
    state.preview = { can_use: true, requested_url: state.values.url, title: state.values.title, content_type: 'text/html' };
    state.values.justification = '';
    assert.equal(state.nextPreparationAction.field, 'justification');
    state.values.justification = 'Apoya la búsqueda de información académica oficial.';
    assert.equal(state.nextPreparationAction.mode, 'analyze');
});

test('biblioteca distingue fuentes ministeriales de universitarias sin inventar autoridad', () => {
    const state = component();
    state.sources = [
        { source_id: 'M', title: 'Currículo base', institution: 'Ministerio de Educación', authority_tier: 'TIER_1_MINISTRY_NATIONAL_REGULATION' },
        { source_id: 'U', title: 'Malla', institution: 'Universidad Pública', authority_tier: 'TIER_2_OFFICIAL_UNIVERSITY_CURRICULUM' },
        { source_id: 'X', title: 'Referencia ocupacional', institution: 'Referencia externa' },
    ];
    assert.equal(state.sources.filter(source => state.matchesSource(source)).length, 1);
    state.sourceGroup = 'MINISTERIALES';
    assert.equal(state.matchesSource(state.sources[0]), true);
    assert.equal(state.matchesSource(state.sources[1]), false);
    assert.equal(state.libraryGroups.find(group => group.value === 'MINISTERIALES').count, 1);
    assert.equal(state.libraryGroups.find(group => group.value === 'OTRAS').count, 1);
});

test('lenguaje claro conserva hechos y fuentes pero presenta vocabulario cotidiano', () => {
    const state = component();
    const answer = 'El corpus documental contiene 12 materias [BO-U-MALLA,p.2].';
    state.tutorAnswer = { answer, sources: [{ source_id: 'BO-U-MALLA', title: 'Malla oficial', institution: 'Universidad' }] };
    assert.equal(state.technicalDetail, false);
    assert.equal(state.assistantAnswerBlocks[0].text, 'La biblioteca de documentos contiene 12 materias.');
    assert.equal(state.tutorAnswer.answer, answer);
    assert.equal(state.tutorAnswer.sources[0].source_id, 'BO-U-MALLA');
    state.languageMode = 'TECNICO';
    assert.equal(state.assistantAnswerBlocks[0].text, answer);
});

test('las referencias desconocidas nunca se eliminan por presentación', () => {
    const state = component();
    state.tutorAnswer = { answer: 'Texto [BO-NO-CONOCIDA,p.2].', sources: [] };
    assert.equal(state.assistantAnswerBlocks[0].text, 'Texto [BO-NO-CONOCIDA,p.2].');
    state.tutorAnswer.sources = [{ source_id: 'BO-OTRA', title: 'Otra fuente' }];
    assert.equal(state.assistantAnswerBlocks[0].text, 'Texto [BO-NO-CONOCIDA,p.2].');
});

test('una cita conocida no deja un párrafo Fuente vacío y no borra referencias desconocidas', () => {
    const state = component();
    state.tutorAnswer = { answer: 'Materias verificables.\nFuente: [BO-U-MALLA,p1]\nFuente: [BO-DESCONOCIDA,p.1]\nFuente: documento revisado [BO-U-MALLA,p.1]',
        sources: [{ source_id: 'BO-U-MALLA', title: 'Malla oficial', institution: 'Universidad' }] };
    assert.deepEqual(state.assistantAnswerBlocks.map(block => block.text), [
        'Materias verificables.', 'Fuente: [BO-DESCONOCIDA,p.1]', 'Fuente: documento revisado',
    ]);
    assert.equal(state.displayAssistantText('Fuente:'), 'Fuente:');
    state.languageMode = 'TECNICO';
    assert.equal(state.assistantAnswerBlocks[1].text, 'Fuente: [BO-U-MALLA,p1]');
});

test('tipos y estados usan etiquetas humanas pero conservan códigos originales en técnico', () => {
    const state = component();
    assert.equal(state.documentStatusLabel('VALID'), 'Documento revisado');
    assert.equal(state.documentStatusLabel('APROBADA_PENDIENTE_INGESTA'), 'Aprobada, pendiente incorporación');
    assert.equal(state.sourceTypeLabel('OFFICIAL_CURRICULUM_PDF'), 'Malla curricular');
    assert.equal(state.documentStatusLabel('ESTADO_NUEVO_NO_CONOCIDO'), 'ESTADO_NUEVO_NO_CONOCIDO');
    assert.equal(state.sourceTypeLabel('TIPO_NUEVO'), 'TIPO_NUEVO');
    state.languageMode = 'TECNICO';
    assert.equal(state.documentStatusLabel('VALID'), 'VALID');
    assert.equal(state.documentStatusLabel('APROBADA_PENDIENTE_INGESTA'), 'APROBADA_PENDIENTE_INGESTA');
    assert.equal(state.sourceTypeLabel('OFFICIAL_CURRICULUM_PDF'), 'OFFICIAL_CURRICULUM_PDF');
});

test('actor sin autorización de revisión no consulta configuración al iniciar', t => {
    const state = component();
    state.canReview = false;
    state.updatesUrl = '/updates';
    state.$nextTick = () => {};
    state.$watch = () => {};
    state.checkConnection = () => {};
    let calls = 0;
    state.loadUpdates = () => { calls++; };
    t.mock.method(globalThis, 'setInterval', () => 1);
    state.init();
    assert.equal(calls, 0);
});

test('respuesta documental organiza líneas como datos y conserva texto sin interpretar HTML', () => {
    const state = component();
    state.usuarioNombre = 'Víctor';
    assert.match(state.assistantGreeting, /Hola, Víctor/);
    state.tutorAnswer = { answer: '# Revisión\n- Primera materia\n- <img src=x onerror=alert(1)>\nConsulta el original.' };
    assert.deepEqual(state.assistantAnswerBlocks, [
        { kind: 'heading', text: 'Revisión' }, { kind: 'list', items: ['Primera materia', '<img src=x onerror=alert(1)>'] },
        { kind: 'paragraph', text: 'Consulta el original.' },
    ]);
    assert.equal(state.safeLink('javascript:alert(1)'), null);
});

test('la inicialización comprueba conexión sin modal y carga actualización solo con permiso', t => {
    const state = component();
    state.canReview = true;
    state.updatesUrl = '/updates';
    state.$nextTick = () => {};
    state.$watch = () => {};
    let connectionArgument;
    let updates = 0;
    state.checkConnection = open => { connectionArgument = open; };
    state.loadUpdates = () => { updates++; };
    t.mock.method(globalThis, 'setInterval', () => 1);
    state.init();
    assert.equal(connectionArgument, false);
    assert.equal(state.connectionOpen, false);
    assert.equal(updates, 1);
});

test('comprobar conexión automática no abre diálogo ni considera verificada una respuesta incompleta', async t => {
    const state = component();
    state.connectionUrl = '/connection';
    t.mock.method(globalThis, 'fetch', async () => ({ ok: true, json: async () => ({ sources: 5 }) }));
    await state.checkConnection(false);
    assert.equal(state.connectionOpen, false);
    assert.equal(state.serviceAvailable, false);
    assert.match(state.connectionError, /no se considera verificada/);
});

test('vista previa bloqueada no completa paso de detección aunque la URL sea válida', () => {
    const state = component();
    state.preview = { can_use: false, duplicate: { kind: 'CORPUS' } };
    assert.equal(state.workflowSteps.length, 4);
    assert.equal(state.workflowSteps[0].state, 'kg-step-complete');
    assert.notEqual(state.workflowSteps[1].state, 'kg-step-complete');
    assert.equal(state.workflowSteps[2].state, '');
});

test('fallo del asistente mantiene respuesta e historial confirmados', async t => {
    const state = component();
    state.tutorUrl = '/assistant';
    state.tutorQuestion = 'Revisar la malla';
    state.tutorAnswer = { answer: 'Respuesta anterior' };
    state.tutorHistory = [{ role: 'assistant', content: 'Respuesta anterior' }];
    t.mock.method(globalThis, 'fetch', async () => ({ ok: true, json: async () => ({ sources: [] }) }));
    await state.askTutor();
    assert.equal(state.tutorAnswer.answer, 'Respuesta anterior');
    assert.equal(state.tutorHistory.length, 1);
    assert.match(state.tutorError, /no confirmó una respuesta/);
});

test('guardar cadencia envía CSRF y solo cambia persistencia con snapshot válido', async t => {
    const state = component();
    state.canReview = true;
    state.updatesConfigureUrl = '/updates/configure';
    const source = { source_id: 'U', title: 'Malla', cadence: 'MANUAL', status: 'SIN_REVISAR' };
    state.updates = { sources: [source], cadences: [{ value: 'MANUAL', label: 'Manual' }, { value: 'MENSUAL', label: 'Mensual' }] };
    state.cadenceChoices.U = 'MENSUAL';
    t.mock.method(globalThis, 'fetch', async (url, options) => {
        assert.equal(url, '/updates/configure');
        assert.equal(options.headers['X-CSRF-TOKEN'], 'test');
        assert.deepEqual(JSON.parse(options.body), { source_id: 'U', cadence: 'MENSUAL' });
        assert.equal(state.updates.sources[0].cadence, 'MANUAL');
        return { ok: true, json: async () => ({ cadences: state.updates.cadences, sources: [{ ...source, cadence: 'MENSUAL' }], message: 'Cadencia guardada' }) };
    });
    await state.configureUpdate(source);
    assert.equal(state.updates.sources[0].cadence, 'MENSUAL');
    assert.equal(state.updatesNotice, 'Cadencia guardada');
});

test('revisión fallida conserva fechas y estado; sin permiso no inicia petición', async t => {
    const state = component();
    state.updatesReviewUrl = '/updates/review';
    const source = { source_id: 'U', cadence: 'MANUAL', status: 'SIN_CAMBIOS', last_checked_at: '2026-10-10' };
    state.updates = { sources: [source], cadences: [] };
    const fetch = t.mock.method(globalThis, 'fetch', async () => ({ ok: false, status: 429 }));
    await state.reviewUpdate(source);
    assert.equal(fetch.mock.callCount(), 0);
    state.canReview = true;
    await state.reviewUpdate(source);
    assert.equal(state.updates.sources[0], source);
    assert.match(state.updatesError, /consulta el estado/);
    assert.equal(state.updatingSource, '');
    assert.equal(state.updatesNeedsCheck, true);
    await state.reviewUpdate(source);
    assert.equal(fetch.mock.callCount(), 1);
});

test('saved extraction requests the existing snapshot without starting a new download', async (t) => {
    const state = component();
    state.detailKind = 'proposal';
    state.detail = { proposal_id: 'KGI-ABCDEF123456', status: 'APROBADA_PENDIENTE_INGESTA' };
    state.extractionUrlTemplate = '/fuentes/__PROPOSAL__/extraccion';
    const response = { proposal_id: state.detail.proposal_id, document_hash: 'sha256:reviewed',
        embeddings: { status: 'GENERADOS_PENDIENTES_PUBLICACION', vectors: 31 } };
    const fetch = t.mock.method(globalThis, 'fetch', async (_url, options) => {
        assert.equal(options.method, 'GET');
        assert.equal(options.body, undefined);
        return { ok: true, json: async () => response };
    });
    await state.extractApproved(true);
    assert.equal(fetch.mock.callCount(), 1);
    assert.equal(state.extraction.document_hash, response.document_hash);
    assert.equal(state.extraction.embeddings.vectors, 31);
});

test('extraction rate limit explains the server wait and preserves the approved proposal', async (t) => {
    const state = component();
    state.detailKind = 'proposal';
    state.detail = { proposal_id: 'KGI-ABCDEF123456', status: 'APROBADA_PENDIENTE_INGESTA' };
    state.extractionUrlTemplate = '/fuentes/__PROPOSAL__/extraccion';
    t.mock.method(globalThis, 'fetch', async () => ({
        ok: false, status: 429, headers: { get: () => '25' },
        json: async () => ({ message: 'Too Many Attempts.' }),
    }));
    await state.extractApproved();
    assert.match(state.extractionError, /25 segundos/);
    assert.doesNotMatch(state.extractionError, /Too Many/);
    assert.equal(state.detail.status, 'APROBADA_PENDIENTE_INGESTA');
    assert.equal(state.extracting, false);
});

function component() {
    const constraints = {
        title: [3, 240], declared_institution: [3, 240], source_type: [0, 100],
        url: [12, 2000], scope: [10, 500], campus: [2, 160], city: [2, 120],
        version: [1, 120], justification: [20, 1000], publication_date: [0, 10],
        limitations_text: [0, 2000],
    };
    const values = {
        title: 'Página institucional universitaria', declared_institution: 'UPB',
        source_type: 'OFFICIAL_UNIVERSITY_PAGE', url: 'https://www.upb.edu/',
        scope: 'Documentación académica oficial.', campus: 'Nacional', city: 'La Paz',
        version: 'Por verificar', justification: 'Apoya la búsqueda de información académica oficial.',
        publication_date: '', limitations_text: '',
    };
    const elements = Object.entries(constraints).map(([name, limits]) => ({
        name, value: values[name], minLength: limits[0], maxLength: limits[1],
        required: !['publication_date', 'limitations_text'].includes(name), focus() {},
    }));
    for (const element of elements) elements[element.name] = element;
    const state = knowledgeGovernance({
        trustedDomains: ['upb.edu'], universities: [{ domain: 'upb.edu', institution: 'Universidad Privada Boliviana', aliases: ['UPB'] }],
        sources: [], serviceAvailable: true, analysisUrl: '/analysis', previewUrl: '/preview', csrf: 'test',
    });
    state.$refs = { sourceForm: { elements } };
    state.refreshValues();
    return state;
}

test('blocked embedding uses a safe text reading tied to the current inspected document', () => {
    const state = component();
    state.preview = { requested_url: state.values.url, can_use: true, document_hash: 'sha256:' + 'a'.repeat(64), excerpt: 'Alcance académico publicado.', document_kind: 'PUBLIC_DOCUMENT', embedding: { allowed: false, reason: 'La universidad impide mostrar su página aquí.' } };
    state.abrirPublicacion();
    assert.equal(state.muestraLecturaPublicacion, true);
    assert.equal(state.lecturaPublicacion.excerpt, 'Alcance académico publicado.');
    assert.match(state.motivoLecturaPublicacion, /impide/);
    state.values.url = 'https://www.upb.edu/otro-documento';
    assert.equal(state.lecturaPublicacion, null);
    assert.equal(state.muestraLecturaPublicacion, false);
});

test('academic page selection inspects only a same-university link offered by the current response', () => {
    const state = component();
    const pagina = { url: 'https://www.upb.edu/carreras/sistemas', title: 'Carrera de Sistemas', kind: 'carrera' };
    state.preview = { can_use: true, requested_url: state.values.url, academic_pages: [pagina] };
    let checks = 0;
    state.schedulePreview = () => { checks++; };
    state.elegirPaginaAcademica(pagina);
    assert.equal(state.values.url, pagina.url);
    assert.equal(state.values.source_type, 'OFFICIAL_CAREER_HTML');
    assert.equal(state.entradaTipo, 'pagina');
    assert.equal(state.analysisToken, '');
    assert.equal(checks, 1);
});

test('switching publications removes only unchanged autofill and preserves manual or confirmed data', () => {
    const state = component();
    for (const name of ['title', 'scope', 'publication_date']) state.$refs.sourceForm.elements[name].value = '';
    state.refreshValues();
    state.preview = {can_use:true, requested_url:state.values.url, suggested_fields:{title:'Página institucional', scope:'Plan de estudios publicado para la carrera.', publication_date:'2025-03'}};
    state.applyDetectedFields(false);
    state.$refs.sourceForm.elements.scope.value = 'Alcance corregido manualmente con evidencia.';
    state.invalidateAnalysis({target:{name:'scope'}});
    state.$refs.sourceForm.elements.url.value = 'https://www.upb.edu/carreras/sistemas';
    state.previewUrl = null;
    state.schedulePreview();
    assert.equal(state.values.title, '');
    assert.equal(state.values.publication_date, '');
    assert.equal(state.values.scope, 'Alcance corregido manualmente con evidencia.');
    assert.equal(state.values.campus, 'Nacional');
    assert.deepEqual(state.camposDetectados, {});
});

test('academic page selection rejects invented, foreign, stale and portal candidates', () => {
    for (const url of ['https://www.umsa.bo/carreras/sistemas', 'https://www.upb.edu/login', 'https://www.upb.edu/estudiante/solicitud', 'https://www.upb.edu/carrera.pdf']) {
        const state = component();
        const original = state.values.url;
        const pagina = {url, title:'Carrera de Sistemas', kind:'carrera'};
        state.preview = { can_use: true, requested_url: original, academic_pages: [pagina] };
        state.elegirPaginaAcademica(pagina);
        assert.equal(state.values.url, original, url);
    }
    const state = component();
    const pagina = {url:'https://www.upb.edu/carreras/sistemas', kind:'carrera'};
    state.preview = {can_use:true, requested_url:state.values.url, academic_pages:[]};
    state.elegirPaginaAcademica(pagina);
    assert.equal(state.values.url, 'https://www.upb.edu/');
    state.preview.academic_pages = [pagina];
    state.preview.requested_url = 'https://www.upb.edu/otra-pagina';
    state.elegirPaginaAcademica(pagina);
    assert.equal(state.values.url, 'https://www.upb.edu/');
});

test('student portals cannot expose an academic reading even when a payload includes an excerpt', () => {
    const state = component();
    state.preview = { requested_url: state.values.url, can_use: false, document_kind: 'STUDENT_PORTAL', document_hash: 'sha256:' + 'b'.repeat(64), excerpt: 'Datos que no son una publicación académica.', message: 'Use una publicación académica pública.' };
    state.abrirPublicacion();
    assert.equal(state.muestraLecturaPublicacion, true);
    assert.equal(state.lecturaPublicacion, null);
    assert.equal(state.motivoLecturaPublicacion, 'Use una publicación académica pública.');
});

test('manual iframe fallback needs a verified document hash and does not synthesize a reading', () => {
    const state = component();
    state.publicacionLectura = true;
    state.preview = { requested_url: state.values.url, can_use: true, excerpt: 'Texto sin documento identificado.' };
    assert.equal(state.muestraLecturaPublicacion, true);
    assert.equal(state.lecturaPublicacion, null);
    state.preview.document_hash = 'c'.repeat(64);
    assert.equal(state.lecturaPublicacion, null);
    state.preview.document_hash = 'sha256:' + 'c'.repeat(64);
    assert.equal(state.lecturaPublicacion.excerpt, state.preview.excerpt);
    state.abrirPublicacion();
    assert.equal(state.publicacionLectura, false);
    assert.equal(state.muestraLecturaPublicacion, false);
});

test('draft preview exposes pending fields without saving or inventing values', () => {
    const state = component();
    state.$refs.sourceForm.elements.campus.value = '';
    state.openFichaPreview();
    assert.equal(state.fichaPreviewOpen, true);
    assert.equal(state.fichaFields.find(field => field.name === 'campus').value, '');
    assert.ok(state.fichaFields.find(field => field.name === 'campus').error);
    assert.equal(state.analysisToken, '');
});

test('suggestions replace only explicitly selected documented fields and invalidate analysis', () => {
    const state = component();
    state.preview = { can_use: true, requested_url: state.values.url, title: 'Título oficial detectado', suggested_fields: { title: 'Título oficial detectado', scope: 'Alcance extraído del documento.', campus: 'Dato no autorizado' } };
    state.analysisToken = 'previous-analysis';
    state.openFichaPreview();
    assert.equal(state.selectedSuggestionCount, 0);
    state.suggestionSelections = { title: true, campus: true };
    state.applySelectedSuggestions();
    assert.equal(state.values.title, 'Título oficial detectado');
    assert.equal(state.values.scope, 'Documentación académica oficial.');
    assert.equal(state.values.campus, 'Nacional');
    assert.equal(state.analysisToken, '');
    assert.match(state.suggestionMessage, /1 sugerencias/);
});

test('stale, blocked, unauthorized and oversized suggestions cannot be applied', () => {
    for (const variation of ['stale', 'blocked', 'unauthorized', 'oversized']) {
        const state = component();
        state.preview = { can_use: variation !== 'blocked', requested_url: variation === 'stale' ? 'https://www.upb.edu/other' : state.values.url, suggested_fields: { title: variation === 'oversized' ? 'a'.repeat(241) : 'Nuevo título' } };
        if (variation === 'unauthorized') state.canPropose = false;
        state.suggestionSelections.title = true;
        state.applySelectedSuggestions();
        assert.equal(state.values.title, 'Página institucional universitaria', variation);
    }
});

test('allows recognized Bolivian university outside .bo without automatic ingestion', () => {
    const state = component();
    assert.equal(state.validationScore, 100);
    assert.equal(state.validateForm(), true);
    assert.equal(state.analysisToken, '');
});

test('rejects foreign, local, credentialed and deceptive domains', () => {
    for (const url of ['https://www.berkeley.edu/', 'http://www.upb.edu/', 'https://127.0.0.1/', 'https://[::1]/', 'https://user:pass@upb.edu/', 'https://upb.edu.evil.com/', 'https://upb.edu:8080/']) {
        const state = component();
        state.values.url = url;
        assert.notEqual(state.urlIssue, '', url);
    }
});

test('rejects encoded controls and malformed URL delimiters', () => {
    for (const url of ['https://www.upb.edu/%0d%0aHost:localhost', 'https://www.upb.edu/%5cpath', 'https://www.upb.edu/\npath', 'https://www.upb.edu\\@evil.bo/']) {
        const state = component();
        state.values.url = url;
        assert.notEqual(state.urlIssue, '', url);
    }
});

test('line endings cannot bypass limitation count validation', () => {
    const state = component();
    for (const separator of ['\n', '\r', '\u2028']) {
        state.values.limitations_text = Array(11).fill('No informa costos.').join(separator);
        assert.match(state.fieldError('limitations_text'), /10/);
    }
});

test('unknown .bo requires human verification and does not imply university recognition', () => {
    const state = component();
    state.values.url = 'https://desconocida.edu.bo/';
    assert.equal(state.urlIssue, '');
    assert.equal(state.matchedUniversity, undefined);
    assert.match(state.urlMessage, /pendiente/);
    assert.ok(state.validationScore < 100);
});

test('institution must match exactly including supported aliases', () => {
    const state = component();
    state.values.declared_institution = 'Universidad Mayor de San Andrés';
    assert.match(state.institutionIssue, /Corrija/);
    state.values.declared_institution = 'Universidad Privada Boliviana falsa';
    assert.notEqual(state.institutionIssue, '');
    state.values.declared_institution = 'Universidad Privada Boliviana';
    assert.equal(state.institutionIssue, '');
});

test('calendar checks reject impossible dates and accept leap years and partial dates', () => {
    const state = component();
    for (const value of ['2026-02-29', '2026-00', '2026-13', '2026-04-31', '0000', '26/03/2026']) {
        state.values.publication_date = value;
        assert.notEqual(state.dateIssue, '', value);
    }
    for (const value of ['', '2026', '2026-03', '2024-02-29']) {
        state.values.publication_date = value;
        assert.equal(state.dateIssue, '', value);
    }
});

test('limitations and required text respect server-compatible bounds', () => {
    const state = component();
    state.values.limitations_text = Array(11).fill('Una limitación.').join('\n');
    assert.match(state.fieldError('limitations_text'), /10/);
    state.values.limitations_text = 'ab';
    assert.match(state.fieldError('limitations_text'), /3 y 500/);
    state.values.title = 'ab';
    assert.match(state.fieldError('title'), /3/);
    state.values.url = `https://www.upb.edu/${'a'.repeat(2000)}`;
    assert.match(state.fieldError('url'), /2000/);
    state.values.declared_institution = 'A'.repeat(241);
    assert.match(state.fieldError('declared_institution'), /240/);
});

test('editing cancels pending requests and invalidates previously issued tokens', () => {
    const state = component();
    state.analysisToken = 'token';
    state.requestController = new AbortController();
    state.invalidateAnalysis();
    assert.equal(state.requestController.signal.aborted, true);
    assert.equal(state.analysisToken, '');
    assert.equal(state.analysisInvalidated, true);
});

test('form changes while parsing an analysis response discard stale results', async context => {
    const state = component();
    let completeJson;
    context.mock.method(globalThis, 'FormData', function TestFormData() { this.delete = () => {}; });
    context.mock.method(globalThis, 'fetch', async () => ({
        ok: true, json: () => new Promise(resolve => { completeJson = resolve; }),
    }));
    const pending = state.analyze();
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(typeof completeJson, 'function', state.serverMessage);
    state.invalidateAnalysis();
    completeJson({ can_submit: true, analysis_token: 'outdated' });
    await pending;
    assert.equal(state.analysisToken, '');
    assert.equal(state.analysisOpen, false);
});

test('invalid submission stays enabled for correction rather than locking the form', () => {
    const state = component();
    let prevented = false;
    state.guardSubmission({ preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
    assert.equal(state.submitting, false);
});

test('document suggestions fill blanks only and never invent campus or version', () => {
    const state = component();
    state.$refs.sourceForm.elements.title.value = '';
    state.preview = { requested_url: state.values.url, can_use: true, title: 'UPB | Inicio', suggested_fields: { title: 'UPB | Inicio', scope: 'Publicado en la universidad', campus: 'Inventado' } };
    state.applyDetectedFields(false);
    assert.equal(state.values.title, 'UPB | Inicio');
    assert.equal(state.values.scope, 'Documentación académica oficial.');
    assert.equal(state.values.campus, 'Nacional');
    state.applyDetectedFields();
    assert.equal(state.values.scope, 'Publicado en la universidad');
    state.values.title = 'nsjkandkjanskdnas';
    assert.match(state.fieldError('title'), /detectado/);
    state.values.title = 'UPB Inicio';
    assert.match(state.fieldError('title'), /detectado/);
    state.values.title = 'UPB | INICIO';
    assert.equal(state.fieldError('title'), '');
});

test('changing URL discards old preview and checks once after a short pause instead of fetching per key', context => {
    const state = component();
    context.mock.timers.enable({ apis: ['setTimeout'] });
    state.preview = { can_use: true };
    state.previewController = new AbortController();
    const inspect = context.mock.method(state, 'inspectUrl', () => {});
    state.schedulePreview();
    assert.equal(state.preview, null);
    assert.equal(state.previewController.signal.aborted, true);
    context.mock.timers.tick(1199);
    assert.equal(inspect.mock.callCount(), 0);
    state.schedulePreview();
    context.mock.timers.tick(1199);
    assert.equal(inspect.mock.callCount(), 0);
    context.mock.timers.tick(1);
    assert.equal(inspect.mock.callCount(), 1);
});

test('preview response arriving after URL changes cannot autofill another document', async context => {
    const state = component();
    let finish;
    context.mock.method(globalThis, 'fetch', async () => ({ ok: true, json: () => new Promise(resolve => { finish = resolve; }) }));
    const request = state.inspectUrl();
    await new Promise(resolve => setImmediate(resolve));
    state.$refs.sourceForm.elements.url.value = 'https://www.upb.edu/otra';
    state.invalidateAnalysis({ target: { name: 'url' } });
    clearTimeout(state.previewTimer);
    finish({ requested_url: 'https://www.upb.edu/', title: 'Título desactualizado', can_use: true });
    await request;
    assert.equal(state.preview, null);
    assert.equal(state.values.title, 'Página institucional universitaria');
});

test('invalid and offline URL edits validate immediately without remote requests', context => {
    const state = component();
    context.mock.timers.enable({ apis: ['setTimeout'] });
    const inspect = context.mock.method(state, 'inspectUrl', () => {});
    state.$refs.sourceForm.elements.url.value = 'https://www.berkeley.edu/';
    state.invalidateAnalysis({ target: { name: 'url' } });
    assert.equal(state.touched.url, true);
    assert.match(state.urlIssue, /extranjera/);
    assert.equal(state.previewDueAt, 0);
    state.serviceAvailable = false;
    state.$refs.sourceForm.elements.url.value = 'https://www.upb.edu/';
    state.invalidateAnalysis({ target: { name: 'url' } });
    context.mock.timers.tick(5000);
    assert.equal(inspect.mock.callCount(), 0);
    assert.match(state.previewWaitingMessage, /Validación local activa/);
});

test('local and server cooldown prevent repeated remote checks without clearing fields', async context => {
    const state = component();
    state.previewAttempts = Array(6).fill(Date.now());
    const fetch = context.mock.method(globalThis, 'fetch', async () => ({ ok: false, status: 429, headers: { get: () => '25' }, json: async () => ({}) }));
    await state.inspectUrl();
    assert.equal(fetch.mock.callCount(), 0);
    assert.ok(state.previewRetryAt > Date.now());
    state.previewAttempts = [];
    state.previewRetryAt = 0;
    await state.inspectUrl();
    assert.equal(fetch.mock.callCount(), 1);
    assert.ok(state.previewRetryAt >= Date.now() + 24000);
    assert.equal(state.values.title, 'Página institucional universitaria');
    assert.equal(state.previewChecking, false);
});

test('publication modal permits recognized URLs and never foreign or manipulated redirects', () => {
    const state = component();
    assert.equal(state.publicacionUrl, 'https://www.upb.edu/');
    state.preview = { requested_url: state.values.url, can_use: true, final_url: 'https://evil.bo/' };
    assert.equal(state.publicacionUrl, null);
    state.preview = null;
    state.values.url = 'https://upb.edu.evil.com/';
    assert.equal(state.publicacionUrl, null);
});

test('PDF selection uses one canonical source URL and rejects unrelated documents', () => {
    const state = component();
    state.elegirDocumento({ url: 'https://evil.bo/malla.pdf' });
    assert.equal(state.values.url, 'https://www.upb.edu/');
    state.elegirDocumento({ url: 'https://www.upb.edu/malla.pdf', kind: 'malla' });
    clearTimeout(state.previewTimer);
    assert.equal(state.values.url, 'https://www.upb.edu/malla.pdf');
    assert.equal(state.values.source_type, 'OFFICIAL_CURRICULUM_PDF');
    assert.equal(state.fichaManual, true);
    assert.equal(state.analysisToken, '');
});

test('manipulated types, hidden controls and double submission cannot bypass prevention', () => {
    const state = component();
    state.values.source_type = 'ARBITRARY';
    assert.match(state.fieldError('source_type'), /tipos/);
    state.values.title = 'Título\u202eoculto';
    assert.match(state.fieldError('title'), /ocultos/);
    state.submitting = true;
    let prevented = false;
    state.guardSubmission({ preventDefault() { prevented = true; } });
    assert.equal(prevented, true);
});

test('analysis eligibility explains missing verification and invalid metadata in real time', () => {
    const state = component();
    assert.match(state.analysisBlockReason, /Compruebe/);
    state.preview = { can_use: true, requested_url: state.values.url, title: state.values.title, content_type: 'text/html' };
    assert.equal(state.analysisBlockReason, '');
    state.values.declared_institution = 'Otra universidad';
    assert.match(state.analysisBlockReason, /corrija/);
    state.serviceAvailable = false;
    assert.match(state.analysisBlockReason, /no está disponible/);
});
