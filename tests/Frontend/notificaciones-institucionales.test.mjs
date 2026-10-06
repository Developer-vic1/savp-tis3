import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
import {test} from 'node:test';
import vm from 'node:vm';

let reloj = Date.parse('2026-10-05T12:00:00Z'), activos = 0;
class Reloj extends Date {static now() {return reloj;}}
const window = {};
vm.runInNewContext(await readFile(new URL('../../resources/js/notificaciones-institucionales.js', import.meta.url), 'utf8'), {
    window, Date: Reloj, setInterval: () => {activos++; return 1;}, clearInterval: () => {activos--;},
});
function aviso(cambios = {}) {
    reloj = Date.parse('2026-10-05T12:00:00Z'); activos = 0;
    return window.vigenciaAviso({inicio:'2026-10-05T12:01:00Z', fin:'2026-10-06T12:01:00Z',
        reloj:'2026-10-05T12:00:00Z', estado:'AUTORIZADA', ...cambios});
}
test('El contador cambia de programación a vigente y finaliza exactamente', () => {
    const a = aviso(); a.actualizar(); assert.equal(a.etiqueta,'Empieza en'); assert.equal(a.restante,'1 min 0 s');
    reloj += 60000; a.actualizar(); assert.equal(a.etiqueta,'Tiempo restante'); assert.equal(a.restante,'1 d 0 s');
    assert.equal(a.porcentaje,100);
    reloj += 86400000; a.actualizar(); assert.equal(a.etiqueta,'Finalizado'); assert.equal(a.restante,'');
});
test('Una concesión revocada no sigue anunciando tiempo disponible', () => {
    const a=aviso({estado:'REVOCADA'}); a.actualizar(); assert.equal(a.etiqueta,'Revocado'); assert.equal(a.restante,'');
});
test('El reloj de servidor corrige diferencias con el reloj del navegador', () => {
    const a=aviso({reloj:'2026-10-05T12:00:45Z'}); a.actualizar(); assert.equal(a.restante,'15 s');
});
test('Cerrar la campana o destruir la vista libera el temporizador', () => {
    const a=aviso(); let cambio; a.open=false; a.$watch=(_,accion)=>{cambio=accion;}; a.init(); assert.equal(activos,0);
    cambio(true); assert.equal(activos,1); cambio(false); assert.equal(activos,0);
    cambio(true); a.destroy(); assert.equal(activos,0);
});
test('Una fecha inválida no muestra un contador ni habilita el acceso', () => {
    const a=aviso({fin:'incorrecto'}); a.actualizar(); assert.equal(a.etiqueta,'Vigencia no disponible'); assert.equal(a.restante,'');
});
