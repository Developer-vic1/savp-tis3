import assert from 'node:assert/strict';
import {readFile} from 'node:fs/promises';
import {test} from 'node:test';
import vm from 'node:vm';

const window={};
class RelojPrueba extends Date {static now(){return Date.parse('2026-10-04T16:00:00Z');}}
vm.runInNewContext(await readFile(new URL('../../resources/js/roles-institucionales.js',import.meta.url),'utf8'),{window,Date:RelojPrueba});
function pantalla(){
    const estado=window.rolesInstitucionales([],[],[],{motivos:{acceso:{APOYO:'Apoyo institucional'}},minimo:'2026-01-01',maximo:'2026-12-31'});
    estado.$wire={tipoAcceso:'PERMISOS',modoVigenciaAcceso:'PERSONALIZADO',rolAcceso:null,usuariosAcceso:['D'],permisosAcceso:['reportes.exportar.institucional'],fechaInicioAcceso:'2026-10-04',horaInicioAcceso:'13',minutoInicioAcceso:'07',fechaFinAcceso:'2026-10-04',horaFinAcceso:'18',minutoFinAcceso:'00',tipoMotivoAcceso:'APOYO',motivoAcceso:''};
    return estado;
}
test('Un día conserva la hora y cruza fin de mes con exactamente 24 horas',()=>{
    const p=pantalla();p.$wire.fechaInicioAcceso='2026-10-31';p.vigenciaUnDia();
    assert.equal(p.$wire.modoVigenciaAcceso,'DIA');assert.equal(p.$wire.fechaFinAcceso,'2026-11-01');
    assert.equal(p.$wire.horaFinAcceso,'13');assert.equal(p.$wire.minutoFinAcceso,'07');assert.equal(p.errorAcceso,'');
});
test('Cambiar el plazo requiere Personalizado e invalida la confirmación anterior',()=>{
    const p=pantalla();p.vigenciaUnDia();p.ultimaRevisionAcceso=p.huellaAcceso;
    p.$wire.minutoFinAcceso='08';assert.match(p.errorAcceso,/exactamente 24 horas/);
    p.vigenciaPersonalizada();assert.equal(p.errorAcceso,'');assert.equal(p.ultimaRevisionAcceso,'');
});
test('Un día recupera un inicio vencido sin cambiar la duración ni usar la zona del navegador',()=>{
    const p=pantalla();p.$wire.horaInicioAcceso='11';p.vigenciaUnDia();
    assert.equal(p.$wire.fechaInicioAcceso,'2026-10-04');assert.equal(p.$wire.horaInicioAcceso,'12');assert.equal(p.$wire.minutoInicioAcceso,'01');
    assert.equal(p.$wire.fechaFinAcceso,'2026-10-05');assert.equal(p.errorAcceso,'');
});
test('Personalizado conserva el máximo de 24 horas para Cursos delegados',()=>{
    const p=pantalla();p.vigenciaUnDia();p.vigenciaPersonalizada();p.$wire.permisosAcceso=['cursos.gestionar.global'];
    p.$wire.minutoFinAcceso='08';assert.match(p.errorAcceso,/como máximo 24 horas/);
});
test('Las fechas inexistentes bloquean la revisión en la interfaz',()=>{
    const p=pantalla();p.$wire.fechaInicioAcceso='2026-02-30';assert.match(p.errorAcceso,/fechas y horas válidas/);
    p.$wire.fechaInicioAcceso='2026-10-04';p.$wire.fechaFinAcceso='2026-11-31';assert.match(p.errorAcceso,/fechas y horas válidas/);
});
test('El horario se presenta en español y Bolivia aunque el navegador use otra zona',()=>{
    const p=pantalla();assert.match(p.fechaVigencia('Inicio'),/domingo.*4.*octubre.*2026.*13:07/);
});
test('El aviso de éxito usa texto seguro, cierre y transición institucional',()=>{
    let recibido;window.Swal={fire:opciones=>{recibido=opciones;}};
    pantalla().avisoAcceso({title:'Acceso programado para Félix',text:'El acceso termina mañana.'});
    assert.equal(recibido.toast,true);assert.equal(recibido.icon,'success');assert.equal(recibido.text,'El acceso termina mañana.');
    assert.equal(recibido.html,undefined);assert.equal(recibido.showCloseButton,true);
    assert.equal(recibido.showClass.popup,'roles-confirmacion-entrada');
});
