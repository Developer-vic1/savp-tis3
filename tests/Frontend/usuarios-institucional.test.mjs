import test from 'node:test';
import assert from 'node:assert/strict';
import {readFileSync} from 'node:fs';
import {runInNewContext} from 'node:vm';

function entorno(reducido=false, memoria='tabla') {
    const instancias=[];
    const canvas={roles:{},edades:{},generos:{}};
    const listeners=new Map();
    const storage=new Map([['gestion-usuarios-view',memoria]]);
    const ventana={paginacionInstitucional:()=>({animarResultados(){this.animado=true;},destruirPaginacion(){this.limpio=true;}}),
        addEventListener:(nombre,fn)=>listeners.set(nombre,fn),removeEventListener:nombre=>listeners.delete(nombre),
        Chart:class {constructor(elemento,config){this.canvas=elemento;Object.assign(this,config);instancias.push(this);} update(modo){this.modo=modo;}reset(){}destroy(){this.destruido=true;}}};
    runInNewContext(readFileSync(new URL('../../resources/js/usuarios-institucional.js',import.meta.url),'utf8'),{
        window:ventana,localStorage:{getItem:k=>storage.get(k),setItem:(k,v)=>storage.set(k,v)},
        document:{documentElement:{}},getComputedStyle:()=>({getPropertyValue:nombre=>nombre}),matchMedia:()=>({matches:reducido}),
    });
    const datos={roles:{labels:['Estudiante','Docente','Administrador'],data:[500,70,5]},edades:{labels:['Menores','Adultos'],data:[30,80]},generos:{labels:['F','M'],data:[70,40]}};
    const pagina=ventana.gestionUsuariosPage(datos);
    Object.assign(pagina,{$el:{isConnected:true,querySelector:selector=>canvas[selector.replace('#usuarios-grafico-','')]},$nextTick:fn=>fn(),$watch:()=>{}});
    pagina.init();
    return {pagina,instancias,canvas,listeners,storage,datos};
}

test('indicadores respetan alcance y conservan porcentajes completos sin alterar datos',()=>{
    const {pagina,datos}=entorno();
    assert.deepEqual(Array.from(pagina.rolesVisibles,item=>item.cantidad),[70,5]);
    assert.equal(pagina.cuadriculaGenero.length,100);
    assert.equal(pagina.cuadriculaGenero.filter(item=>item.nombre==='F').length,64);
    pagina.incluirEstudiantes=true;
    assert.deepEqual(Array.from(pagina.rolesVisibles,item=>item.cantidad),[500,70,5]);
    assert.deepEqual(datos.roles.data,[500,70,5]);
});

test('filtros vacíos destruyen los canvas retirados y restauran los nuevos',()=>{
    const {pagina,instancias,canvas,datos}=entorno();
    const anterior=instancias[0];delete canvas.edades;pagina.actualizarIndicadores(datos);
    assert.equal(anterior.destruido,true);
    canvas.edades={};pagina.actualizarIndicadores(datos);
    assert.equal(instancias.length,2);assert.equal(instancias[1].canvas,canvas.edades);
    pagina.destroy();assert.ok(instancias.every(g=>g.destruido));
});

test('vista conserva preferencia válida y respeta movimiento reducido',()=>{
    const {pagina,instancias,storage,listeners}=entorno(true,'galeria');
    assert.equal(pagina.vista,'galeria');pagina.cambiarVista('directorio');
    assert.equal(storage.get('gestion-usuarios-view'),'directorio');pagina.cambiarVista('inventada');
    assert.equal(pagina.vista,'directorio');pagina.dibujar();
    assert.ok(instancias.every(g=>g.options.animation===false && g.modo==='none'));
    pagina.destroy();assert.equal(listeners.size,0);assert.equal(pagina.limpio,true);
});
