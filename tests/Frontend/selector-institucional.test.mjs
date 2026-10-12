import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';
import { reactive, effect, stop } from '@vue/reactivity';
import knowledgeGovernance from '../../resources/js/gestion-conocimiento.js';

const ventana = { addEventListener() {}, removeEventListener() {} };
runInNewContext(readFileSync(new URL('../../resources/js/selector-institucional.js', import.meta.url), 'utf8'), {
    window: ventana, Event, clearTimeout, setTimeout,
    matchMedia: () => ({ matches: true }),
});

function selector(cantidad, inicial = null) {
    const state = ventana.selectorInstitucional(null, Array.from({ length: cantidad }, (_, i) => ({ valor: String(i), etiqueta: `Opción ${i}` })), 'selector-prueba', false, false, inicial);
    const eventos = [];
    state.$refs = {
        control: { disabled: false, focus() { eventos.push('control'); }, dispatchEvent(e) { eventos.push(e.type); } },
        buscar: { focus() { eventos.push('buscar'); } },
        panel: { showPopover() {}, hidePopover() {}, querySelector() { return null; } },
    };
    state.$nextTick = fn => fn();
    state.$dispatch = (tipo, detalle) => eventos.push([tipo, detalle.valor]);
    state.ubicar = () => {};
    return { state, eventos };
}

test('buscador aparece sólo por encima de diez opciones originales', () => {
    assert.equal(selector(10).state.mostrarBusqueda, false);
    const { state } = selector(11);
    state.busqueda = 'Opción 10';
    assert.equal(state.filtradas.length, 1);
    assert.equal(state.mostrarBusqueda, true);
    state.busqueda = 'ninguna';
    assert.equal(state.filtradas.length, 0);
    assert.equal(state.mostrarBusqueda, true);
});

test('listas cortas conservan todas sus opciones y el foco en el control', () => {
    const { state, eventos } = selector(4, '2');
    state.busqueda = 'texto previo';
    assert.equal(state.filtradas.length, 4);
    state.abrir();
    assert.equal(state.indice, 2);
    assert.equal(eventos.at(-1), 'control');
    const largo = selector(11);
    largo.state.abrir();
    assert.equal(largo.eventos.at(-1), 'buscar');
});

test('teclado omite opciones deshabilitadas y permite seleccionar sin buscador', () => {
    const { state, eventos } = selector(4, '0');
    state.opciones[1].deshabilitada = true;
    state.abrir();
    const tecla = key => state.teclado({ key, target: state.$refs.control, preventDefault() {}, stopPropagation() {} });
    tecla('ArrowDown');
    assert.equal(state.indice, 2);
    tecla('End');
    assert.equal(state.indice, 3);
    tecla('Home');
    assert.equal(state.indice, 0);
    tecla('ArrowDown');
    tecla('Enter');
    assert.equal(state.valor, '2');
    assert.equal(state.abierto, false);
    assert.ok(eventos.some(e => Array.isArray(e) && e[0] === 'cambio-selector' && e[1] === '2'));
});

test('campo de formulario actualiza su valor antes de avisar al formulario', () => {
    const { state } = selector(4, '1');
    const observados = [];
    state.$refs.campo = { value: '1', dispatchEvent(event) { observados.push([event.type, this.value]); } };
    state.elegir(state.opciones[3]);
    assert.equal(state.$refs.campo.value, '3');
    assert.deepEqual(observados, [['input', '3'], ['change', '3']]);
});

test('una búsqueda sin coincidencias no cambia la elección con Enter', () => {
    const { state } = selector(11, '4');
    state.abrir();
    state.busqueda = 'sin coincidencias';
    state.indice = state.primerIndice();
    state.teclado({ key: 'Enter', target: state.$refs.buscar, preventDefault() {} });
    assert.equal(state.valor, '4');
    state.teclado({ key: 'Escape', preventDefault() {}, stopPropagation() {} });
    assert.equal(state.abierto, false);
});

function fuenteReactiva(inicial = '') {
    const vista = readFileSync(new URL('../../resources/views/workspaces/conocimiento-universitario.blade.php', import.meta.url), 'utf8');
    const componente = readFileSync(new URL('../../resources/views/components/selector-institucional.blade.php', import.meta.url), 'utf8');
    const expresion = vista.match(/identificador="fuente-tipo-documental"[\s\S]*?x-effect="([^"]+)"/)[1];
    const enlace = componente.match(/x-ref="campo"[^>]*:value="([^"]+)"/)[1];
    const campo = { name: 'source_type', value: inicial, required: true };
    const url = { name: 'url', value: 'https://www.upb.edu/carrera' };
    const elementos = [campo, url];
    elementos.source_type = campo;
    elementos.url = url;
    const conocimiento = reactive(knowledgeGovernance({
        canPropose: true, universities: [{ domain: 'upb.edu', institution: 'UPB' }], trustedDomains: ['upb.edu'], trustedInstitutions: ['UPB'], sources: [], serviceAvailable: true,
    }));
    conocimiento.$refs = { sourceForm: { elements: elementos } };
    conocimiento.schedulePreview = () => {};
    const opciones = ['','OFFICIAL_UNIVERSITY_PAGE','OFFICIAL_CURRICULUM_PDF','OFFICIAL_REGULATION_PDF','OFFICIAL_CAREER_HTML'].map(valor => ({ valor, etiqueta: valor || 'Seleccione' }));
    const seleccion = reactive(ventana.selectorInstitucional(null, opciones, 'fuente-tipo-documental', false, false, inicial));
    seleccion.$refs = {
        campo: { get value() { return campo.value; }, set value(value) { campo.value = value; }, dispatchEvent(event) { conocimiento.invalidateAnalysis({ target: campo }); } },
        control: { focus() {} }, panel: { hidePopover() {} },
    };
    const scope = {
        get values() { return conocimiento.values; },
        get valor() { return seleccion.valor; }, set valor(value) { seleccion.valor = value; },
    };
    const sincronizar = new Function('scope', `with(scope){${expresion}}`);
    const valorEnlazado = new Function('scope', `with(scope){return (${enlace})}`);
    const sincronizacion = effect(() => sincronizar(scope));
    const escritura = effect(() => { campo.value = valorEnlazado(scope); });
    return {
        conocimiento, seleccion, campo,
        datos() { const form = new FormData(); elementos.forEach(e => form.append(e.name, e.value)); return form; },
        terminar() { stop(sincronizacion); stop(escritura); },
    };
}

test('ficha conserva tipo inicial y serializa el tipo elegido con sus bindings reactivos reales', () => {
    const ficha = fuenteReactiva('OFFICIAL_CAREER_HTML');
    try {
        ficha.conocimiento.refreshValues();
        assert.equal(ficha.seleccion.valor, 'OFFICIAL_CAREER_HTML');
        assert.equal(ficha.datos().get('source_type'), 'OFFICIAL_CAREER_HTML');
        ficha.seleccion.elegir(ficha.seleccion.opciones.find(o => o.valor === 'OFFICIAL_REGULATION_PDF'));
        assert.equal(ficha.conocimiento.values.source_type, 'OFFICIAL_REGULATION_PDF');
        assert.equal(ficha.datos().get('source_type'), 'OFFICIAL_REGULATION_PDF');
    } finally { ficha.terminar(); }
});

test('autocompletado y selección de PDF sincronizan etiqueta, estado y dato enviado sin deriva', () => {
    const ficha = fuenteReactiva();
    try {
        const state = ficha.conocimiento;
        state.refreshValues();
        state.preview = { can_use: true, requested_url: state.values.url, suggested_fields: { source_type: 'OFFICIAL_CAREER_HTML' } };
        state.applyDetectedFields(false);
        assert.equal(ficha.seleccion.valor, 'OFFICIAL_CAREER_HTML');
        assert.equal(ficha.datos().get('source_type'), 'OFFICIAL_CAREER_HTML');
        state.elegirDocumento({ url: 'https://www.upb.edu/malla.pdf', kind: 'malla' });
        assert.equal(state.values.source_type, 'OFFICIAL_CURRICULUM_PDF');
        assert.equal(ficha.seleccion.valor, 'OFFICIAL_CURRICULUM_PDF');
        assert.equal(ficha.datos().get('source_type'), 'OFFICIAL_CURRICULUM_PDF');
        assert.equal(ficha.datos().get('url'), 'https://www.upb.edu/malla.pdf');
        state.preview = { can_use: true, requested_url: state.values.url, suggested_fields: { source_type: 'OFFICIAL_REGULATION_PDF' } };
        state.suggestionSelections = { source_type: true };
        state.applySelectedSuggestions();
        assert.equal(ficha.seleccion.valor, 'OFFICIAL_REGULATION_PDF');
        assert.equal(ficha.datos().get('source_type'), 'OFFICIAL_REGULATION_PDF');
    } finally { ficha.terminar(); }
});
