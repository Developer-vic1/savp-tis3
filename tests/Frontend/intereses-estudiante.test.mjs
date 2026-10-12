import test from 'node:test';
import assert from 'node:assert/strict';
import { crearCuestionarioIntereses, crearEstudioIntereses, respuestasValidas } from '../../resources/js/intereses-estudiante.js';

const ids = Array.from({ length: 30 }, (_, i) => i + 1);
const preparar = (respuestas, opciones = {}) => {
    const cuestionario = crearCuestionarioIntereses({ ids, respuestas, ...opciones });
    cuestionario.$el = { noValidate: false, querySelector: () => ({ focus() {} }) };
    cuestionario.$refs = {};
    cuestionario.$nextTick = tarea => tarea();
    cuestionario.init();
    return cuestionario;
};

test('las pendientes y el avance consideran solo opciones válidas del instrumento', () => {
    assert.deepEqual(respuestasValidas([1, 2, 3, 4, 5], { 1: '1', 2: '5', 3: '', 4: 6, 5: 1.5 }), [1, 2]);
    const estado = preparar({ 1: 3, 2: 2, 3: 1, 4: 5, 5: 4, 6: 2 });
    assert.equal(estado.bloque, 1);
    assert.equal(estado.contestadas, 6);
    assert.equal(estado.porcentaje, 20);
    assert.equal(estado.completa, false);
    assert.equal(estado.respondidasBloque(0), 5);
});

test('guardar un borrador parcial confirma las respuestas, conserva el bloque y evita doble envío', async () => {
    const anteriorFetch = globalThis.fetch;
    const anteriorFormData = globalThis.FormData;
    let terminar;
    let destino;
    let formularioEnviado;
    let envios = 0;
    globalThis.FormData = class { constructor(formulario) { formularioEnviado = formulario; } };
    globalThis.fetch = url => { destino = url; envios++; return new Promise(resolve => { terminar = resolve; }); };
    try {
        const estado = preparar({ 1: '4', 6: 2 }, { respuestasGuardadas: {}, urlBorrador: '/estudiante/intereses/borrador' });
        const raizFormulario = { tagName: 'FORM' };
        estado.$root = raizFormulario;
        estado.$el = { tagName: 'BUTTON' };
        assert.equal(estado.cambiosPendientes, true);
        const guardado = estado.guardarBorrador();
        assert.equal(estado.guardandoBorrador, true);
        assert.equal(estado.enviando, true);
        await estado.guardarBorrador();
        assert.equal(envios, 1);
        assert.equal(destino, '/estudiante/intereses/borrador');
        assert.equal(formularioEnviado, raizFormulario);
        terminar({ ok: true, headers: { get: () => 'application/json' }, json: async () => ({ draftSaved: true, answered: 2, responses: { 1: 4, 6: 2 }, savedAt: '2026-10-08T12:00:00Z' }) });
        await guardado;
        assert.equal(estado.cambiosPendientes, false);
        assert.equal(estado.completa, false);
        assert.equal(estado.tieneBorrador, true);
        assert.equal(estado.enviando, false);
        assert.equal(estado.guardandoBorrador, false);
        assert.match(estado.estadoBorrador, /Borrador guardado/);
        estado.respuestas[2] = 5;
        assert.equal(estado.cambiosPendientes, true);
    } finally { globalThis.fetch = anteriorFetch; globalThis.FormData = anteriorFormData; }
});

test('una confirmación incompleta de borrador nunca marca los cambios como guardados', async () => {
    const anteriorFetch = globalThis.fetch;
    const anteriorFormData = globalThis.FormData;
    globalThis.FormData = class {};
    globalThis.fetch = async () => ({ ok: true, headers: { get: () => 'application/json' }, json: async () => ({ draftSaved: true, answered: 1, responses: { 1: 2 }, savedAt: '2026-10-08T12:00:00Z' }) });
    try {
        const estado = preparar({ 1: 4 }, { respuestasGuardadas: {}, urlBorrador: '/estudiante/intereses/borrador' });
        await estado.guardarBorrador();
        assert.equal(estado.cambiosPendientes, true);
        assert.equal(estado.tieneBorrador, false);
        assert.equal(estado.respuestas[1], 4);
        assert.equal(estado.enviando, false);
        assert.ok(estado.aviso);
    } finally { globalThis.fetch = anteriorFetch; globalThis.FormData = anteriorFormData; }
});

test('el aviso de salida protege sólo cambios pendientes y elimina su listener al cerrar el componente', () => {
    const anteriorWindow = globalThis.window;
    let listener;
    let retirado;
    globalThis.window = { addEventListener: (_, callback) => { listener = callback; }, removeEventListener: (_, callback) => { retirado = callback; } };
    try {
        const estado = preparar({ 1: 4 }, { respuestasGuardadas: { 1: '4' }, borradorGuardado: true });
        let avisos = 0;
        listener({ preventDefault() { avisos++; } });
        assert.equal(avisos, 0);
        estado.respuestas[1] = 5;
        listener({ preventDefault() { avisos++; } });
        assert.equal(avisos, 1);
        estado.permitirSalida = true;
        listener({ preventDefault() { avisos++; } });
        assert.equal(avisos, 1);
        estado.destroy();
        assert.equal(retirado, listener);
    } finally { if (anteriorWindow === undefined) delete globalThis.window; else globalThis.window = anteriorWindow; }
});

test('cambiar de bloque conserva las respuestas y limita navegación', () => {
    const estado = preparar({ 1: 4 });
    estado.ir(99);
    assert.equal(estado.bloque, 5);
    assert.equal(estado.visible(29), true);
    assert.equal(estado.visible(0), false);
    estado.ir(-1);
    assert.equal(estado.bloque, 0);
    assert.equal(estado.respuestas[1], 4);
    estado.revisando = true;
    assert.equal(estado.visible(29), true);
});

test('un cuestionario incompleto nunca se envía y regresa a la primera pendiente', async () => {
    const anterior = globalThis.fetch;
    let envios = 0;
    globalThis.fetch = async () => { envios++; };
    try {
        const estado = preparar({ 1: 4 });
        estado.bloque = 5;
        await estado.guardar({ currentTarget: {} });
        assert.equal(envios, 0);
        assert.equal(estado.bloque, 0);
        assert.match(estado.aviso.message, /1 de 30/);
    } finally { globalThis.fetch = anterior; }
});

test('la carga bloquea doble envío y un fallo conserva datos y permite recuperar', async () => {
    const anteriorFetch = globalThis.fetch;
    const anteriorFormData = globalThis.FormData;
    let terminar;
    let envios = 0;
    globalThis.FormData = class {};
    globalThis.fetch = () => { envios++; return new Promise(resolve => { terminar = resolve; }); };
    try {
        const estado = preparar(Object.fromEntries(ids.map(id => [id, '4'])));
        const pendiente = estado.guardar({ currentTarget: { action: '/estudiante/intereses' } });
        assert.equal(estado.enviando, true);
        await estado.guardar({ currentTarget: {} });
        assert.equal(envios, 1);
        terminar({ ok: false, status: 503, headers: { get: () => 'application/json' }, json: async () => ({ notice: { title: 'Servicio no disponible', message: 'Reintenta después.' } }) });
        await pendiente;
        assert.equal(estado.enviando, false);
        assert.equal(estado.completa, true);
        assert.equal(estado.aviso.title, 'Servicio no disponible');
    } finally { globalThis.fetch = anteriorFetch; globalThis.FormData = anteriorFormData; }
});

test('sesión expirada explica cómo recuperar sin perder respuestas del formulario', async () => {
    const anteriorFetch = globalThis.fetch;
    const anteriorFormData = globalThis.FormData;
    globalThis.FormData = class {};
    globalThis.fetch = async () => ({ ok: false, status: 419, headers: { get: () => 'text/html' } });
    try {
        const estudio = crearEstudioIntereses();
        await estudio.analizar({ currentTarget: { action: '/estudiante/intereses/analizar' } });
        assert.equal(estudio.enviando, false);
        assert.equal(estudio.aviso.title, 'Tu sesión necesita actualizarse');
        assert.match(estudio.aviso.message, /otra pestaña/);
    } finally { globalThis.fetch = anteriorFetch; globalThis.FormData = anteriorFormData; }
});

test('actualizar el estudio recarga el mismo documento aunque la redirección solo cambie el ancla', async () => {
    const anteriorWindow = globalThis.window;
    const anteriorFetch = globalThis.fetch;
    const anteriorFormData = globalThis.FormData;
    const destinos = [];
    let recargas = 0;
    globalThis.window = { location: { origin: 'https://savp.example', href: 'https://savp.example/estudiante/analisis#mi-perfil', assign: url => destinos.push(url), reload: () => { recargas++; } } };
    globalThis.FormData = class {};
    globalThis.fetch = async () => ({ ok: true, headers: { get: () => 'application/json' }, json: async () => ({ redirect: '/estudiante/analisis#mi-analisis' }) });
    try {
        const estado = crearEstudioIntereses();
        await estado.analizar({ currentTarget: { action: '/estudiante/intereses/analizar' } });
        assert.equal(recargas, 1);
        assert.deepEqual(destinos, ['https://savp.example/estudiante/analisis#mi-analisis']);
        assert.equal(estado.aviso, null);
        globalThis.window.location.href = 'https://savp.example/estudiante/intereses';
        await estado.analizar({ currentTarget: { action: '/estudiante/intereses' } });
        assert.equal(recargas, 1);
    } finally {
        if (anteriorWindow === undefined) delete globalThis.window; else globalThis.window = anteriorWindow;
        globalThis.fetch = anteriorFetch;
        globalThis.FormData = anteriorFormData;
    }
});
