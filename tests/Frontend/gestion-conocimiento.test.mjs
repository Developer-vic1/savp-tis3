import test from 'node:test';
import assert from 'node:assert/strict';
import knowledgeGovernance from '../../resources/js/gestion-conocimiento.js';

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

test('changing URL discards old preview and waits ten seconds instead of fetching per key', context => {
    const state = component();
    context.mock.timers.enable({ apis: ['setTimeout'] });
    state.preview = { can_use: true };
    state.previewController = new AbortController();
    const inspect = context.mock.method(state, 'inspectUrl', () => {});
    state.schedulePreview();
    assert.equal(state.preview, null);
    assert.equal(state.previewController.signal.aborted, true);
    context.mock.timers.tick(9999);
    assert.equal(inspect.mock.callCount(), 0);
    state.schedulePreview();
    context.mock.timers.tick(9999);
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
