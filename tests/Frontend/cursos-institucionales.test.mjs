import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import { test } from 'node:test';
import vm from 'node:vm';

const window = {};
window.paginacionInstitucional = () => ({});
vm.runInNewContext(await readFile(new URL('../../resources/js/cursos-institucionales.js', import.meta.url), 'utf8'), { window, matchMedia: () => ({ matches: false }) });

function trayectoria(datos) {
    const grafico = window.trayectoriaCursos(datos, '2026');
    const cambios = {};
    grafico.$watch = (campo, accion) => { cambios[campo] = accion; };
    grafico.init();
    return { grafico, cambios };
}

function pantallaCursos(wire = {}) {
    const pantalla = window.cursosInstitucionales();
    pantalla.$wire = wire;
    pantalla.$nextTick = accion => accion();
    pantalla.$refs = { formularioPanel: { querySelector: () => ({ focus() {} }) }, clasePanel: { querySelector: () => ({ focus() {} }) } };
    return pantalla;
}

test('registrar grado cierra una preparación de clase previa', async () => {
    const pantalla = pantallaCursos({ abrirFormularioCurso: async () => {} });
    pantalla.claseAbierta = true;
    pantalla.detalle = true;
    await pantalla.formularioCurso('crear', null, null);
    assert.equal(pantalla.formulario, true);
    assert.equal(pantalla.claseAbierta, false);
    assert.equal(pantalla.detalle, false);
});

test('una clase requiere el horario y confirmación del contexto del servidor', async () => {
    let llamadas = 0;
    const wire = { modalClaseHorario: false, crearClaseDesdeBloque: async () => { llamadas++; } };
    const pantalla = pantallaCursos(wire);
    pantalla.detalle = true;
    pantalla.formulario = true;
    pantalla.seccionConsulta = 'horario';
    await pantalla.prepararClase({ dia: 'LUNES', bloque: 'bloque' });
    assert.equal(llamadas, 0);
    pantalla.formulario = false;
    await pantalla.prepararClase({ dia: 'LUNES', bloque: 'bloque' });
    assert.equal(pantalla.claseAbierta, false);
    wire.modalClaseHorario = true;
    await pantalla.prepararClase({ dia: 'LUNES', bloque: 'bloque' });
    assert.equal(pantalla.claseAbierta, true);
});

test('la trayectoria reutiliza el cálculo al leer puntos, escalas y selección', () => {
    const { grafico } = trayectoria([
        { curso: 'A', nombre: 'Primero', anio: 2025, estudiantes: 40 },
        { curso: 'A', nombre: 'Primero', anio: 2026, estudiantes: 50 },
        { curso: 'B', nombre: 'Segundo', anio: 2026, estudiantes: 0 },
    ]);
    const series = grafico.series;
    for (let i = 0; i < 100; i++) {
        assert.equal(grafico.series, series);
        assert.equal(grafico.maximo, 50);
        assert.equal(grafico.series[0].puntos[1].y, grafico.y(50));
    }
    assert.match(grafico.resumenSerie(series[0]), /2026: 50 estudiantes.*\+10 respecto a 2025/);
    grafico.seleccion = '2025';
    assert.match(grafico.resumenSerie(series[0]), /2025: 40 estudiantes/);
    assert.equal(grafico.series, series);
});

test('los filtros recalculan la escala y conservan años sin registros como ausencia', () => {
    const { grafico, cambios } = trayectoria([
        { curso: 'A', nombre: 'Primero', anio: 2025, estudiantes: 40 },
        { curso: 'A', nombre: 'Primero', anio: 2026, estudiantes: 50 },
        { curso: 'B', nombre: 'Segundo', anio: 2026, estudiantes: 0 },
    ]);
    grafico.elegidos = ['B'];
    cambios.elegidos();
    assert.equal(grafico.series.length, 1);
    assert.equal(grafico.series[0].puntos.length, 1);
    assert.equal(grafico.series[0].puntos[0].valor, 0);
    assert.equal(grafico.series[0].puntos[0].x, 595);
    grafico.aniosElegidos = ['2025'];
    cambios.aniosElegidos();
    assert.equal(grafico.series[0].puntos.length, 0);
    assert.equal(grafico.resumenSerie(grafico.series[0]), 'Sin dato registrado');
    grafico.elegidos = [];
    cambios.elegidos();
    assert.equal(grafico.series.length, 0);
    assert.equal(grafico.minimo, 0);
    assert.equal(grafico.maximo, 1);
});
