import test from 'node:test';
import assert from 'node:assert/strict';
import coleccionUniversitaria from '../../resources/js/coleccion-universitaria.js';

const snapshot = (status = 'COMPLETADA') => ({
    schema_version: '1.0', institution: 'UNIFRANZ', status,
    careers: [{ name: 'Ingeniería de Sistemas', campuses: ['Cochabamba'] }],
    progress: { completed: 1, total: 1 },
});
const component = () => coleccionUniversitaria({ url: '/coleccion', csrf: 'test-csrf', canCollect: true });
const response = data => ({ ok: true, json: async () => data });

test('consulta inicial no inicia descarga y consulta de nuevo solo mientras hay trabajo', async t => {
    const state = component();
    t.after(() => state.destroy());
    const timers = t.mock.method(globalThis, 'setTimeout', () => 99);
    t.mock.method(globalThis, 'fetch', async (_url, options) => {
        assert.equal(options.method, 'GET');
        return response(snapshot('EN_PROCESO'));
    });
    await state.refresh();
    assert.equal(timers.mock.callCount(), 1);
    assert.equal(timers.mock.calls[0].arguments[1], 4000);
    assert.equal(state.canStart, false);
});

test('colección completa no programa consultas y busca nombres con tildes', async t => {
    const state = component();
    const timers = t.mock.method(globalThis, 'setTimeout', () => 99);
    t.mock.method(globalThis, 'fetch', async () => response(snapshot()));
    await state.refresh();
    assert.equal(timers.mock.callCount(), 0);
    state.search = 'ingenieria';
    assert.equal(state.filteredCareers.length, 1);
    state.search = 'cochabamba';
    assert.equal(state.filteredCareers.length, 1);
});

test('evita dos inicios concurrentes y exige consultar estado si no se confirmó el POST', async t => {
    const state = component();
    let fail;
    const calls = t.mock.method(globalThis, 'fetch', (_url, options) => {
        assert.equal(options.headers['X-CSRF-TOKEN'], 'test-csrf');
        return new Promise((_resolve, reject) => { fail = reject; });
    });
    const pending = state.start();
    await state.start();
    assert.equal(calls.mock.callCount(), 1);
    fail(new TypeError('Failed to fetch'));
    await pending;
    assert.equal(state.mustCheckStatus, true);
    assert.equal(state.canStart, false);
    assert.doesNotMatch(state.error, /Failed to fetch/);
});

test('límite 429 conserva colección y explica espera sin simular avance', async t => {
    const state = component();
    state.collection = snapshot('PARCIAL');
    state.collection.careers[0].status = 'PENDIENTE';
    const original = state.collection;
    t.mock.method(globalThis, 'fetch', async () => ({ ok: false, status: 429, headers: { get: () => '15' } }));
    await state.start();
    assert.equal(state.collection, original);
    assert.match(state.error, /15 segundos/);
    assert.equal(state.canStart, false);
    assert.equal(state.timer, null);
});

test('sesión vencida explica recuperar acceso y conserva documentos', async t => {
    const state = component();
    state.collection = snapshot();
    t.mock.method(globalThis, 'fetch', async () => ({ ok: false, status: 419 }));
    await state.refresh();
    assert.match(state.error, /sesión venció/);
    assert.equal(state.collection.careers.length, 1);
});

test('respuesta incompatible no sustituye un resultado confirmado', async t => {
    const state = component();
    state.collection = snapshot('PARCIAL');
    t.mock.method(globalThis, 'fetch', async () => response({ success: true }));
    await state.refresh();
    assert.equal(state.collection.status, 'PARCIAL');
    assert.match(state.error, /colección válida/);
});

test('destruir aborta petición y no permite que respuesta tardía programe polling', async t => {
    const state = component();
    let resolve;
    let signal;
    const timers = t.mock.method(globalThis, 'setTimeout', () => 99);
    t.mock.method(globalThis, 'fetch', async (_url, options) => {
        signal = options.signal;
        return new Promise(done => { resolve = done; });
    });
    const pending = state.refresh();
    state.destroy();
    assert.equal(signal.aborted, true);
    resolve(response(snapshot('EN_PROCESO')));
    await pending;
    assert.equal(timers.mock.callCount(), 0);
    assert.equal(state.collection, null);
});

test('enlaces solo aceptan HTTPS oficial sin credenciales ni puertos extra', () => {
    const state = component();
    assert.equal(state.safeUrl('javascript:alert(1)'), null);
    assert.equal(state.safeUrl('https://unifranz.edu.bo/carreras/'), 'https://unifranz.edu.bo/carreras/');
    assert.equal(state.safeUrl('https://media.unifranz.edu.bo/malla.pdf'), 'https://media.unifranz.edu.bo/malla.pdf');
    for (const url of ['http://unifranz.edu.bo/malla.pdf', 'https://unifranz.edu.bo.ejemplo.com/',
        'https://otro.edu.bo/malla.pdf', 'https://usuario:clave@unifranz.edu.bo/', 'https://unifranz.edu.bo:8443/']) {
        assert.equal(state.safeUrl(url), null);
    }
    const exact = 'https://unifranz.edu.bo/malla.pdf?version=2&campus=cbba';
    assert.equal(state.reviewUrl(exact), `/conocimiento/fuentes?url=${encodeURIComponent(exact)}`);
});

test('colección completada permite consultar pero no inicia otro POST', async t => {
    const state = component();
    state.collection = snapshot('COMPLETADA');
    const calls = t.mock.method(globalThis, 'fetch', async (_url, options) => {
        assert.equal(options.method, 'GET');
        return response(snapshot('COMPLETADA'));
    });
    assert.equal(state.canStart, false);
    assert.equal(state.actionLabel, 'Colección guardada');
    await state.start();
    assert.equal(calls.mock.callCount(), 0);
    await state.refresh();
    assert.equal(calls.mock.callCount(), 1);
});

test('lectura parcial sin descargas pendientes permite preparar las imágenes guardadas', async t => {
    const state = component();
    state.collection = snapshot('PARCIAL');
    state.collection.careers[0].status = 'EXTRAIDA_PENDIENTE_REVISION';
    state.collection.careers[0].curricula = [{ status: 'SIN_TEXTO' }, { status: 'PARCIAL' }];
    const calls = t.mock.method(globalThis, 'fetch', async (_url, options) => {
        assert.equal(options.method, 'POST');
        return response(snapshot('PARCIAL'));
    });
    assert.equal(state.pendingDownloads, false);
    assert.equal(state.canStart, true);
    assert.equal(state.actionLabel, 'Preparar lectura');
    await state.start();
    assert.equal(calls.mock.callCount(), 1);
    state.collection.careers[0].curricula = [{ status: 'NO_DISPONIBLE' }];
    assert.equal(state.pendingDownloads, true);
    assert.equal(state.canStart, true);
    assert.equal(state.actionLabel, 'Reanudar descargas pendientes');
});

test('descarga usa solo el identificador validado interno y tiene título humano', () => {
    const state = component();
    const hash = 'a'.repeat(64);
    assert.equal(state.downloadUrl({ document_hash: `sha256:${hash}` }), `/conocimiento/colecciones/unifranz/documento/${hash}/descargar`);
    assert.equal(state.downloadUrl({ document_hash: '../../archivo.pdf' }), null);
    assert.equal(state.downloadUrl({}), null);
    assert.equal(state.documentTitle({ name: 'Medicina' }), 'Malla curricular · Medicina');
    assert.match(state.readingSummary({ pages: 2, digital_pages: 0 }), /reconocer el texto/);
    assert.match(state.readingSummary({ pages: 2, digital_pages: 1 }), /1 de 2 páginas/);
    assert.doesNotMatch(state.humanText('OCR pendiente · SHA256 registrado'), /OCR|SHA256/);
});

test('resumen distingue páginas digitales y reconocidas con revisión pendiente', () => {
    const state = component();
    const summary = state.readingSummary({ status: 'PARCIAL', pages: 4, digital_pages: 1, recognized_pages: 2, readable_pages: 3 });
    assert.match(summary, /3 de 4 páginas/);
    assert.match(summary, /1 con texto del documento y 2 con texto reconocido de imágenes/);
    assert.match(summary, /Quedan páginas sin texto disponible/);
    assert.match(summary, /Confirma las materias/);
    assert.match(state.readingSummary({ status: 'PARCIAL', pages: 2, digital_pages: 0, recognized_pages: 2, readable_pages: 2 }), /requiere revisión visual/);
    assert.doesNotMatch(summary, /OCR/);
    assert.equal(state.pageReadingLabel({ page: 1, method: 'DIGITAL' }), 'Página 1 · Texto del documento');
    assert.equal(state.pageReadingLabel({ page: 2, method: 'OCR', review_required: true }), 'Página 2 · Texto reconocido · Revisar');
});

test('agrupa materias reales por semestre ordenado sin asumir ocho para todas las carreras', () => {
    const state = component();
    const curriculum = { url: 'https://unifranz.edu.bo/malla.pdf', subjects_candidates: [
        { name: 'Psicología social', semester: 3 }, { name: 'Introducción a la psicología', semester: 1 },
        { name: 'Psicología social', semester: 3 }, { name: 'Sin semestre', semester: null },
    ] };
    assert.deepEqual(state.semesters(curriculum).map(item => item.number), [1, 3]);
    assert.equal(state.subjectCount(curriculum), 2);
    assert.equal(state.selectedSemester(curriculum).number, 1);
    state.selectSemester(curriculum, 3);
    assert.equal(state.selectedSemester(curriculum).subjects[0].name, 'Psicología social');
    assert.match(state.structureSummary(curriculum), /2 semestres identificados · 2 materias/);
});

test('estructura del servicio prevalece y conserva semestres sin materias pendientes de revisión', () => {
    const state = component();
    const curriculum = { url: 'https://unifranz.edu.bo/malla.pdf', curricular_structure: {
        semester_count: 8, status: 'PENDIENTE_REVISION', semesters: Array.from({ length: 8 }, (_, index) => ({
            number: index + 1, subjects: index === 0 ? ['Psicología general', { name: 'Biología' }] : [],
        })),
    }, subjects_candidates: [{ semester: 9, name: 'No corresponde a estructura' }] };
    assert.equal(state.semesters(curriculum).length, 8);
    assert.equal(state.subjectCount(curriculum), 2);
    assert.match(state.structureSummary(curriculum), /8 semestres identificados · 2 materias/);
    const partial = { curricular_structure: { semester_count: 8, semesters: [{ number: 1, subjects: ['Biología'] }] } };
    assert.match(state.structureSummary(partial), /1 de 8 semestres/);
});

test('análisis local usa endpoint distinto y preserva colección al fallar', async t => {
    const state = coleccionUniversitaria({ url: '/coleccion', analysisUrl: '/coleccion/analizar', csrf: 'token', canCollect: true });
    state.collection = snapshot('COMPLETADA');
    assert.equal(state.canAnalyze, true);
    const original = state.collection;
    t.mock.method(globalThis, 'fetch', async (url, options) => {
        assert.equal(url, '/coleccion/analizar');
        assert.equal(options.method, 'POST');
        assert.equal(state.analyzing, true);
        assert.equal(state.busy, true);
        assert.equal(options.headers['X-CSRF-TOKEN'], 'token');
        return { ok: false, status: 503 };
    });
    await state.analyze();
    assert.equal(state.collection, original);
    assert.equal(state.analyzing, false);
    assert.equal(state.canAnalyze, false);
});

test('filtrar La Paz excluye Arquitectura sin oferta publicada y conserva búsqueda por sede', () => {
    const state = component();
    const source = 'https://unifranz.edu.bo/carreras/arquitectura/';
    const architecture = { name: 'Arquitectura', campuses: ['Santa Cruz'], campus_evidence: { status: 'OFERTA_IDENTIFICADA', source_url: source },
        campus_availability: [{ campus: 'Santa Cruz', status: 'OFERTADA', source_url: source }, { campus: 'La Paz', status: 'NO_INDICADA', source_url: source }] };
    const psychology = { name: 'Psicología', campuses: ['La Paz'], campus_evidence: { status: 'OFERTA_IDENTIFICADA', source_url: source },
        campus_availability: [{ campus: 'La Paz', status: 'OFERTADA', source_url: source }] };
    state.collection = { ...snapshot(), careers: [architecture, psychology, { name: 'Sede sin verificar', campuses: ['La Paz'], campus_evidence: { status: 'PENDIENTE' } }] };
    state.campusFilter = 'La Paz';
    assert.deepEqual(state.filteredCareers.map(career => career.name), ['Psicología']);
    assert.equal(state.campusFilters.find(filter => filter.value === 'La Paz').count, 1);
    state.search = 'psicologia';
    assert.equal(state.filteredCareers.length, 1);
    state.search = '';
    state.campusFilter = 'PENDIENTES';
    assert.deepEqual(state.filteredCareers.map(career => career.name), ['Sede sin verificar']);
    state.campusFilter = 'TODAS';
    assert.equal(state.filteredCareers.length, 3);
    assert.match(state.campusAvailabilityLabel(architecture.campus_availability[1]), /No indicada en la fuente/);
});

test('una sede mencionada sin fuente oficial y una malla sin sede no afirman oferta', () => {
    const state = component();
    assert.deepEqual(state.confirmedCampuses({ campuses: ['La Paz'] }), []);
    assert.deepEqual(state.confirmedCampuses({ campus_availability: [{ campus: 'La Paz', status: 'OFERTADA', source_url: 'https://ejemplo.com/' }] }), []);
    assert.match(state.curriculumCampusLabel({ campuses: [] }), /pendiente de confirmación/);
    assert.match(state.curriculumCampusLabel({ campuses: ['Cochabamba'] }), /Sedes indicadas en esta malla: Cochabamba/);
});
