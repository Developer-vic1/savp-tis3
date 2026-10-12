import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {runInNewContext} from 'node:vm';

function entorno() {
    const listeners = new Map(); const clases = new Set(); const busy = new Set();
    const estado = {pagina: true, temporizador: null};
    const document = {addEventListener: (nombre, listener) => listeners.set(nombre,listener),
        documentElement: {classList: {add: clase=>clases.add(clase),remove:clase=>clases.delete(clase)}},
        querySelector: ()=>estado.pagina ? {setAttribute: nombre=>busy.add(nombre),removeAttribute:nombre=>busy.delete(nombre)} : null};
    const window = {addEventListener:(nombre,listener)=>listeners.set(nombre,listener)};
    const context = {document,window,URL,location:{href:'http://local.test/admin',origin:'http://local.test',pathname:'/admin',search:''},
        clearTimeout:()=>{estado.temporizador=null;},setTimeout:callback=>{estado.temporizador=callback; return 1;}};
    runInNewContext(readFileSync(new URL('../../resources/js/navegacion-institucional.js',import.meta.url),'utf8').replace('export function','function'),context);
    const enlace=(href,atributos={})=>({href,target:atributos.target||'',hasAttribute:nombre=>Boolean(atributos[nombre]),closest:()=>atributos.excluido ? {} : null});
    const evento=(link,extras={})=>({button:0,target:{closest:()=>link},...extras});
    return {context,clases,busy,listeners,estado,enlace,evento};
}

test('navegación interna muestra progreso sin cancelar la solicitud ni bloquear otra acción',()=>{
    const e=entorno(); let cancelado=false;
    e.listeners.get('click')(e.evento(e.enlace('http://local.test/admin/gestion-cursos'),{preventDefault:()=>{cancelado=true;}}));
    assert.equal(cancelado,false); assert.equal(e.clases.has('savp-navegando'),true); assert.equal(e.busy.has('aria-busy'),true);
    e.listeners.get('pageshow')(); assert.equal(e.clases.size,0); assert.equal(e.busy.size,0);
});

test('descargas, externos, anclas, nuevas pestañas y clics modificados conservan su comportamiento',()=>{
    const e=entorno();
    for (const [href,atributos,extras] of [
        ['https://otra.test/admin',{},{}],['http://local.test/admin#notas',{},{}],
        ['http://local.test/admin/reporte.pdf',{},{}],['http://local.test/admin/archivo',{download:true},{}],
        ['http://local.test/admin/reportes/bitacora/pdf',{},{}],
        ['http://local.test/admin/cursos',{target:'_blank'},{}],['http://local.test/admin/cursos',{}, {ctrlKey:true}],
        ['http://local.test/admin/cursos',{}, {defaultPrevented:true}],['http://local.test/logout',{},{}],
        ['http://local.test/documentacion-institucional/referencias/id',{},{}],['http://local.test/admin/cursos',{'wire:navigate':true},{}],
    ]) {
        assert.equal(e.context.destinoDePagina(e.evento(null,extras),e.enlace(href,atributos),e.context.location),null);
    }
});

test('pageshow y recuperación de petición interrumpida retiran el estado pendiente',()=>{
    const e=entorno(); e.listeners.get('click')(e.evento(e.enlace('http://local.test/admin/calificaciones')));
    e.estado.temporizador(); assert.equal(e.clases.size,0); assert.equal(e.busy.size,0);
    e.estado.pagina=false; e.listeners.get('click')(e.evento(e.enlace('http://local.test/admin/calificaciones'))); assert.equal(e.clases.size,0);
});
