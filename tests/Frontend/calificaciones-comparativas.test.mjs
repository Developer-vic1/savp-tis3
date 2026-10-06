import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {runInNewContext} from 'node:vm';

function preparar(conectado = true, falla = false, tipo = 'line', reducido = false) {
    let instancia;
    let tema = 'claro';
    const eventos = new Map();
    const ventana = {
        matchMedia: () => ({matches: reducido, addEventListener() {}, removeEventListener() {}}),
        Chart: class {
            constructor(canvas, config) {
                if (falla) throw new Error('No disponible');
                Object.assign(this, config);
                instancia = this;
            }
            update() { this.actualizaciones = (this.actualizaciones || 0) + 1; }
            destroy() { this.destruido = true; }
        },
        addEventListener: (nombre, listener) => eventos.set(nombre, listener),
        removeEventListener: nombre => eventos.delete(nombre),
    };
    runInNewContext(readFileSync(new URL('../../resources/js/calificaciones-comparativas.js', import.meta.url), 'utf8'), {
        window: ventana, document: {documentElement: {}},
        getComputedStyle: () => ({getPropertyValue: nombre => `${tema}:${nombre}`}),
    });
    const componente = ventana.graficoResultados({labels: tipo === 'radar' ? ['Primero', 'Segundo', 'Tercero'] : ['Primero', 'Segundo'], tipo, unidad: 'puntos', maximo: 100,
        series: [{label: 'Notas', token: 'primary', ...(tipo === 'doughnut' ? {tokens: ['primary','danger']} : {}), data: [0, null]}]});
    componente.$refs = {canvas: {isConnected: conectado}};
    componente.$nextTick = callback => callback();
    return {componente, eventos, instancia: () => instancia, cambiarTema: () => {tema = 'oscuro'; eventos.get('theme-changed')();}};
}

test('mantiene cero, deja huecos por falta de datos y actualiza colores al cambiar tema', () => {
    const entorno = preparar();
    entorno.componente.init();
    const grafico = entorno.instancia();
    assert.deepEqual(grafico.data.datasets[0].data, [0, null]);
    assert.equal(grafico.data.datasets[0].spanGaps, false);
    assert.equal(grafico.options.scales.y.max, 100);
    entorno.cambiarTema();
    assert.equal(grafico.data.datasets[0].borderColor, 'oscuro:--ui-primary');
    entorno.componente.destroy();
    assert.equal(grafico.destruido, true);
    assert.equal(entorno.eventos.size, 0);
});

test('no crea instancias en canvas retirados durante una actualización Livewire', () => {
    const entorno = preparar(false);
    entorno.componente.init();
    assert.equal(entorno.instancia(), undefined);
});

test('un fallo del gráfico habilita la explicación y conserva la alternativa de valores', () => {
    const entorno = preparar(true, true);
    entorno.componente.init();
    assert.equal(entorno.componente.fallo, true);
    entorno.componente.destroy();
    assert.equal(entorno.eventos.size, 0);
});

test('el radar conserva escala radial y el tema actualiza sus etiquetas y fondo', () => {
    const entorno = preparar(true, false, 'radar');
    entorno.componente.init();
    assert.equal(entorno.instancia().type, 'radar');
    assert.equal(entorno.instancia().options.scales.r.max, 100);
    entorno.cambiarTema();
    assert.equal(entorno.instancia().data.datasets[0].backgroundColor, 'oscuro:--ui-primary-soft');
    assert.equal(entorno.instancia().options.scales.r.pointLabels.color, 'oscuro:--ui-text');
});

test('la torta usa colores por categoría y desactiva animación con movimiento reducido', () => {
    const entorno = preparar(true, false, 'doughnut', true);
    entorno.componente.init();
    assert.equal(entorno.instancia().type, 'doughnut');
    assert.equal(entorno.instancia().options.animation, false);
    entorno.cambiarTema();
    assert.equal(entorno.instancia().data.datasets[0].backgroundColor[1], 'oscuro:--ui-danger');
    assert.equal(entorno.instancia().options.scales.x, undefined);
});
