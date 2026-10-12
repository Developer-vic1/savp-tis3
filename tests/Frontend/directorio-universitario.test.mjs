import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import directorioUniversitario from '../../resources/js/directorio-universitario.js';

test('paginación conserva todos los resultados y delimita una última página incompleta', () => {
    const ui = directorioUniversitario({ total: 25 });
    assert.equal(ui.paginas, 3);
    assert.equal(ui.inicio, 1);
    assert.equal(ui.fin, 12);
    assert.equal(ui.paginaVisible(11), true);
    assert.equal(ui.paginaVisible(12), false);
    ui.cambiarPagina(3);
    assert.equal(ui.inicio, 25);
    assert.equal(ui.fin, 25);
    assert.equal(ui.paginaVisible(24), true);
    assert.equal(ui.paginaVisible(25), false);
});

test('consulta vacía y páginas fuera de rango no fabrican resultados', () => {
    const empty = directorioUniversitario({ total: 0 });
    assert.equal(empty.inicio, 0);
    assert.equal(empty.fin, 0);
    const ui = directorioUniversitario({ total: 13 });
    ui.cambiarPagina(999);
    assert.equal(ui.pagina, 2);
    ui.cambiarPagina(-1);
    assert.equal(ui.pagina, 1);
    for (const invalid of ['<script>alert(1)</script>', NaN, Infinity, 1.5]) ui.cambiarPagina(invalid);
    assert.equal(ui.pagina, 1);
    assert.equal(ui.paginaVisible(-1), false);
    assert.equal(ui.paginaVisible('0'), false);
});

test('cambio de página devuelve foco al título sin animación ni peticiones', () => {
    const ui = directorioUniversitario({ total: 30 });
    const calls = [];
    ui.$refs = { resultados: { focus: options => calls.push(['focus', options]), scrollIntoView: options => calls.push(['scroll', options]) } };
    ui.$nextTick = callback => callback();
    ui.cambiarPagina(2);
    assert.deepEqual(calls, [['focus', { preventScroll: true }], ['scroll', { behavior: 'auto', block: 'start' }]]);
    ui.cambiarPagina(2);
    assert.equal(calls.length, 2);
});

test('filtros combinados cambian fichas, programas y contador sin nueva petición', () => {
    const ui = directorioUniversitario({ instituciones: [
        { indice: 0, nombre: 'Universidad Pública del Altiplano', sigla: 'UPA', departamentos: ['La Paz'], regimen: 'PUBLICA_AUTONOMA', programas: [{ nombre: 'Ingeniería de Sistemas', duracion: '8 semestres', costosVerificados: false }, { nombre: 'Medicina', duracion: '10 semestres', costosVerificados: false }] },
        { indice: 1, nombre: 'Universidad Privada del Oriente', sigla: 'UPO', departamentos: ['Beni'], regimen: 'PRIVADA', programas: [{ nombre: 'Psicología', duracion: '8 semestres', costosVerificados: true }] },
        { indice: 2, nombre: 'Instituto de la Amazonía', sigla: 'IA', departamentos: ['Beni'], regimen: 'INDIGENA', programas: [] },
    ], regimenes: { PRIVADA: 'Privada' } });
    assert.equal(ui.total, 3);
    ui.departamento = 'Beni';
    assert.equal(ui.total, 2);
    ui.regimen = 'PRIVADA';
    assert.equal(ui.total, 1);
    ui.buscar = 'psicologia';
    assert.equal(ui.total, 1);
    ui.duracion = '8 semestres';
    assert.equal(ui.total, 1);
    ui.economia = 'verificados';
    assert.equal(ui.total, 1);
    assert.equal(ui.programasVisibles(1), 1);
    assert.deepEqual(ui.filtrosActivos.map(chip => chip.valor), ['psicologia', 'Beni', 'Privada', '8 semestres', 'Montos verificados']);
    ui.economia = 'pendientes';
    assert.equal(ui.total, 0);
    ui.regimen = '';
    ui.duracion = '';
    ui.buscar = '';
    assert.equal(ui.total, 1); // institución sin programas: costo por verificar
    ui.departamento = '';
    assert.equal(ui.total, 2);
    ui.limpiarFiltros();
    assert.equal(ui.total, 3);
    assert.equal(ui.filtrosActivos.length, 0);
    assert.equal(ui.programasVisibles(0), 2);
});

test('búsqueda con acentos, orden, URL y paginación se mantienen al cambiar filtros', () => {
    const instituciones = Array.from({ length: 14 }, (_, indice) => ({ indice, nombre: `Institución ${indice + 1}`, sigla: `I${indice + 1}`, departamentos: indice < 2 ? ['Beni'] : ['La Paz'], regimen: 'PRIVADA', programas: [] }));
    instituciones[0].programas = [{ nombre: 'Ingeniería Ambiental', duracion: '8 semestres', costosVerificados: false }];
    const ui = directorioUniversitario({ instituciones, filtros: { departamento: 'Beni', orden: 'nombre' } });
    assert.equal(ui.total, 2);
    ui.departamento = '';
    assert.equal(ui.total, 14); // quitar Beni recupera todos los registros ya cargados
    ui.cambiarPagina(2);
    assert.equal(ui.pagina, 2);
    const previo = globalThis.window;
    const rutas = [];
    globalThis.window = { location: { href: 'http://localhost/estudiante/directorio-nacional?departamento=Beni#instituciones-directorio' }, history: { state: null, replaceState: (_, __, url) => rutas.push(String(url)) } };
    try {
        ui.buscar = 'ingenieria';
        ui.cambioFiltros();
        assert.equal(ui.pagina, 1);
        assert.equal(ui.total, 1);
        assert.equal(ui.resultados[0].indice, 0);
        assert.match(rutas.at(-1), /buscar=ingenieria/);
        assert.doesNotMatch(rutas.at(-1), /departamento=Beni/);
        ui.orden = 'departamento';
        ui.cambioFiltros();
        assert.match(rutas.at(-1), /orden=departamento/);
    } finally { globalThis.window = previo; }
});

test('la búsqueda incluye carreras del índice oficial sin tratarlas como mallas revisadas', () => {
    const ui = directorioUniversitario({ instituciones: [
        { indice: 0, nombre: 'UNIFRANZ', sigla: 'UNIFRANZ', departamentos: ['La Paz'], regimen: 'PRIVADA', programas: [], ofertaPublicada: ['Medicina', 'Odontología'] },
    ] });
    ui.buscar = 'odontologia';
    assert.equal(ui.total, 1);
    assert.equal(ui.programasVisibles(0), 0);
    assert.equal(ui.ofertaVisible(0, 'Odontología'), true);
    assert.equal(ui.ofertaVisible(0, 'Medicina'), false);
    ui.duracion = '10 semestres';
    assert.equal(ui.total, 0);
});

test('directorio conserva filtros y componentes institucionales, enlaces comprobados y contenido escapado', () => {
    const source = fs.readFileSync(new URL('../../resources/views/conocimiento/directorio-nacional.blade.php', import.meta.url), 'utf8');
    assert.doesNotMatch(source, /\{!!|x-html=|innerHTML|onclick=|<select\b/);
    assert.match(source, /DirectorioNacional::enlacePublico/);
    assert.match(source, /rel="noopener noreferrer"/);
    assert.match(source, /@can\('conocimiento\.revisar'\)/);
    assert.match(source, /!\$estudiante/);
    assert.match(source, /http_build_query\(array_merge\(\$filtros/);
    assert.match(source, /Vigencia legal por verificar/);
    assert.match(source, /Sedes del registro histórico/);
    for (const field of ['buscar', 'departamento', 'regimen', 'duracion', 'economia', 'orden']) {
        assert.match(source, new RegExp(`(?:name|nombre)="${field}"`));
    }
});

test('gráficos tienen valores textuales y estilos con tema y movimiento reducido', () => {
    const css = fs.readFileSync(new URL('../../resources/css/directorio-universitario.css', import.meta.url), 'utf8');
    const source = fs.readFileSync(new URL('../../resources/views/conocimiento/directorio-nacional.blade.php', import.meta.url), 'utf8');
    assert.match(source, /figcaption/);
    assert.match(source, /aria-label="Consultar/);
    assert.match(source, /du-anillo[^>]+aria-hidden="true"/);
    assert.match(css, /prefers-reduced-motion: no-preference/);
    assert.doesNotMatch(css, /#[0-9a-f]{3,8}\b/i);
    assert.match(css, /var\(--ui-primary\)/);
});
