import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

const ventana = {};
runInNewContext(readFileSync(new URL('../../resources/js/componentes-institucionales.js', import.meta.url), 'utf8'), { window: ventana });

test('calendario conserva fechas registradas y acepta años bisiestos', () => {
    const calendario = ventana.calendarioInstitucional('2012-02-29', '2026-10-04', '1906-10-04');
    calendario.init();
    assert.equal(calendario.fechaVisible, '29/02/2012');
    assert.equal(calendario.dias.filter(Boolean).length, 29);
    assert.equal(calendario.dias.find(dia => dia?.fecha === '2012-02-29').bloqueado, false);
    calendario.anio = 2013;
    assert.equal(calendario.dias.filter(Boolean).length, 28);
});

test('calendario limita fechas y navegación sin inventar una fecha vacía', () => {
    const calendario = ventana.calendarioInstitucional('', '2026-10-04', '2026-09-15', '2010-01-01');
    calendario.init();
    assert.equal(calendario.fecha, '');
    assert.equal(calendario.anio, 2026);
    assert.equal(calendario.mes, 8);
    assert.equal(calendario.puedeMover(-1), false);
    assert.equal(calendario.dias.find(dia => dia?.numero === 14).bloqueado, true);
    calendario.mover(1);
    assert.equal(calendario.puedeMover(1), false);
    assert.equal(calendario.dias.find(dia => dia?.numero === 5).bloqueado, true);
    calendario.elegir('2027-01-01');
    assert.equal(calendario.fecha, '');
});

test('paginación evita solicitudes repetidas mientras carga', async () => {
    let resolver;
    const pendiente = new Promise(resolve => { resolver = resolve; });
    const llamadas = [];
    const paginacion = ventana.paginacionInstitucional();
    paginacion.$wire = { perPage: 10, set: async (...args) => { llamadas.push(args); await pendiente; } };
    paginacion.animarResultados = () => {};
    const primera = paginacion.cambiarCantidad(50);
    assert.equal(paginacion.cargandoPagina, true);
    await paginacion.cambiarCantidad(50);
    await paginacion.navegarPagina(2, 'page');
    assert.equal(llamadas.length, 1);
    resolver();
    await primera;
    assert.equal(paginacion.cargandoPagina, false);
});

test('error de carga libera controles y permite reintentar', async () => {
    const paginacion = ventana.paginacionInstitucional();
    const avisos = [];
    paginacion.$wire = { gotoPage: async () => { throw new Error('Error de conexión'); } };
    paginacion.avisar = (...args) => avisos.push(args);
    await paginacion.navegarPagina(2, 'page');
    assert.equal(paginacion.cargandoPagina, false);
    assert.equal(avisos.length, 1);
    assert.equal(avisos[0][0], 'error');
});

test('cantidades inválidas y página anterior al inicio no generan consultas', async () => {
    const paginacion = ventana.paginacionInstitucional();
    paginacion.$wire = { perPage: 10 };
    await paginacion.cambiarCantidad(999);
    await paginacion.cambiarCantidad(10);
    await paginacion.navegarPagina(0, 'page');
    assert.equal(paginacion.cargandoPagina, false);
});
