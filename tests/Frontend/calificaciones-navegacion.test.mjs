import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {runInNewContext} from 'node:vm';

function preparar(cambiarVista) {
    const avisos = [];
    const window = {paginacionInstitucional: () => ({avisar: (...args) => avisos.push(args)})};
    runInNewContext(readFileSync(new URL('../../resources/js/calificaciones-comparativas.js', import.meta.url), 'utf8'), {window});
    const componente = window.calificacionesNavegacion();
    componente.$wire = {vista: 'honor', cambiarVista};
    return {componente, avisos};
}

test('ignora la pestaña actual y bloquea clics rápidos mientras espera la respuesta', async () => {
    let terminar;
    const llamadas = [];
    const {componente} = preparar(vista => {
        llamadas.push(vista);
        return new Promise(resolve => {terminar = resolve;});
    });
    await componente.irVista('honor');
    assert.equal(llamadas.length, 0);
    const carga = componente.irVista('comparativas');
    assert.equal(componente.cambiandoVista, true);
    assert.equal(componente.vistaSolicitada, 'comparativas');
    await componente.irVista('estudiantes');
    assert.deepEqual(llamadas, ['comparativas']);
    terminar();
    await carga;
    assert.equal(componente.cambiandoVista, false);
    assert.equal(componente.vistaSolicitada, '');
});

test('un fallo informa y libera la navegación para volver a intentarlo', async () => {
    let intentos = 0;
    const {componente, avisos} = preparar(async () => {
        if (++intentos === 1) throw new Error('Red interrumpida');
    });
    await componente.irVista('estudiantes');
    assert.equal(avisos.length, 1);
    assert.equal(avisos[0][0], 'error');
    assert.match(avisos[0][1], /filtros se conservan/);
    assert.equal(componente.cambiandoVista, false);
    assert.equal(componente.vistaSolicitada, '');
    await componente.irVista('estudiantes');
    assert.equal(intentos, 2);
    assert.equal(avisos.length, 1);
});
