window.rolesInstitucionales = (permisos, roles, seleccion, soporte, ventanas=[], catalogo=permisos, autoridad={permisos:[],roles:[]},apartado='roles') => ({
        autoridad,
        get rolActual(){return this.roles.find(r=>r.id===Number(this.$wire.selectedRoleId));},
        bloqueoAsignacion(p){
            if(p.guard!=='web')return 'Este permiso pertenece a otro entorno de autenticación.';
            if(this.seleccion.includes(p.name))return this.autoridad.roles.includes(Number(this.$wire.selectedRoleId))?'No puedes retirar permisos de un rol de tu propia cuenta.':'';
            if(!this.autoridad.permisos.includes(p.name))return 'No puedes conceder un permiso que tu cuenta no tiene.';
            if((p.critical||p.name.endsWith('.global'))&&this.rolActual?.nombre!=='Administrador')return 'Este permiso elevado no puede asignarse permanentemente a este rol.';
            return '';
        },
        ventanas, catalogo, ventanaElegida:'', buscarCatalogo:'', rolCatalogo:'', espacioCatalogo:'', permisoDetalle:null,
        get ventanasRol(){return this.ventanas.filter(v=>v.actor===this.roles.find(r=>r.id===Number(this.$wire.selectedRoleId))?.nombre);},
        alternativas(p,actor){if(p==='roles-permisos.ver'&&actor==='Administrador')return ['roles-permisos.gestionar','Gestion_Roles_Permisos'];return [p,...(this.permisos.find(t=>t.name===p)?.consultas_compatibles?.[actor]?[this.permisos.find(t=>t.name===p).consultas_compatibles[actor]]:[])];},
        estadoVentana(v){const actor=this.roles.find(r=>r.id===Number(this.$wire.selectedRoleId))?.nombre;return v.actor===actor&&v.requisitos.every(p=>this.alternativas(p,actor).some(n=>this.seleccion.includes(n)));},
        get registrosCatalogo(){const s=this.buscarCatalogo.trim().toLocaleLowerCase('es');return this.catalogo.filter(p=>(!s||(p.name+' '+p.label+' '+p.roles.join(' ')+' '+[...p.ventanas,...p.operaciones].map(v=>v.label+' '+v.url).join(' ')).toLocaleLowerCase('es').includes(s))&&(!this.rolCatalogo||p.roles.includes(this.rolCatalogo))&&(!this.espacioCatalogo||p.espacio===this.espacioCatalogo));},
        permisos, roles, seleccion, soporte, vista: 'lectura', apartado, espacio:'administrativo',tipoAula:'',buscarTareaUsuario:'',espacioUsuario:'administrativo', busqueda: '', dominio: '', estado: '', pagina: 1,
        editarRevisionAcceso:false, ultimaRevisionAcceso:'', proceso: '', solicitudVisible: false, accesoVisible: false, mensaje: '',
        get huellaAcceso(){const w=this.$wire;return JSON.stringify([w.tipoAcceso,w.modoVigenciaAcceso,w.rolAcceso,[...w.usuariosAcceso].sort(),[...w.permisosAcceso].sort(),w.fechaInicioAcceso,w.horaInicioAcceso,w.minutoInicioAcceso,w.fechaFinAcceso,w.horaFinAcceso,w.minutoFinAcceso,w.tipoMotivoAcceso,w.motivoAcceso]);},
        fechaLocalValida(fecha){if(!/^\d{4}-\d{2}-\d{2}$/.test(fecha))return false;const valor=new Date(fecha+'T00:00:00Z');return !Number.isNaN(valor.getTime())&&valor.toISOString().slice(0,10)===fecha;},
        async revisarAcceso(){const huella=this.huellaAcceso;this.editarRevisionAcceso=false;this.ultimaRevisionAcceso='';await this.actuar('analisis',()=>this.$wire.analizarAcceso());if(huella===this.huellaAcceso&&this.$wire.analisisAcceso?.valido)this.ultimaRevisionAcceso=huella;},
        errorTexto(valor,minimo=20,nombre=false){
            const texto=String(valor??'').trim().replace(/\s+/gu,' ');
            if(texto.length<minimo)return nombre?'Escribe un nombre claro de al menos 4 letras.':`Explica la necesidad con al menos ${minimo} caracteres y cuatro palabras.`;
            if(nombre&&!/^[\p{L}\p{M} ]{4,80}$/u.test(texto))return 'Utiliza solo letras y espacios para el nombre del rol.';
            const palabras=texto.normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().split(/\s+/);
            if(!nombre&&new Set(palabras).size<4)return 'Usa al menos cuatro palabras diferentes para explicar la necesidad.';
            for(const p of palabras){const letras=p.replace(/[^a-z]/g,'');if(letras.length>=6&&(/(.)\1{3,}/.test(letras)||/[bcdfghjklmnpqrstvwxz]{6,}/.test(letras)||letras.length>24))return 'El texto parece una secuencia de letras. Escribe palabras completas y comprensibles.';if(letras.length>=8){const proporcion=(letras.match(/[aeiou]/g)||[]).length/letras.length;if(proporcion<(letras.length>=12?.2:.15)||proporcion>.85)return 'Revisa el texto: no parece una palabra comprensible.';}}
            if(/<[^>]*>|https?:\/\/|[\x00-\x08\x0b\x0c\x0e-\x1f]/iu.test(texto))return 'Escribe texto sencillo, sin enlaces ni etiquetas.';
            return '';
        },
        errorMotivo(contexto,tipo,detalle){if(!this.soporte.motivos[contexto]?.[tipo])return 'Selecciona un motivo institucional.';return tipo==='OTRO'?this.errorTexto(detalle,contexto==='solicitud'?30:20):'';},
        get errorSolicitud(){return this.errorTexto(this.$wire.requestedName,4,true)||this.errorMotivo('solicitud',this.$wire.tipoMotivoSolicitud,this.$wire.institutionalReason)||(!this.soporte.ambitos.includes(this.$wire.requestedScope)?'Selecciona el ámbito de trabajo.':'')||(!this.$wire.responsabilidadesSolicitud.length?'Selecciona al menos una responsabilidad.':'');},
        vigenciaUnDia(){
            const w=this.$wire,inicio=new Date(`${w.fechaInicioAcceso}T${w.horaInicioAcceso}:${w.minutoInicioAcceso}:00-04:00`);
            if(Number.isNaN(inicio.getTime())||inicio.getTime()<Math.floor(Date.now()/60000)*60000){
                const partes=new Intl.DateTimeFormat('en-CA',{timeZone:'America/La_Paz',year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit',hourCycle:'h23'}).formatToParts(new Date(Date.now()+60000));
                const valor=tipo=>partes.find(p=>p.type===tipo).value;
                w.fechaInicioAcceso=`${valor('year')}-${valor('month')}-${valor('day')}`;w.horaInicioAcceso=valor('hour');w.minutoInicioAcceso=valor('minute');
            }
            const fecha=new Date(w.fechaInicioAcceso+'T00:00:00Z');fecha.setUTCDate(fecha.getUTCDate()+1);
            w.fechaFinAcceso=fecha.toISOString().slice(0,10);w.horaFinAcceso=w.horaInicioAcceso;w.minutoFinAcceso=w.minutoInicioAcceso;w.modoVigenciaAcceso='DIA';this.ultimaRevisionAcceso='';
        },
        vigenciaPersonalizada(){this.$wire.modoVigenciaAcceso='PERSONALIZADO';this.ultimaRevisionAcceso='';},
        fechaVigencia(extremo){const w=this.$wire,fecha=new Date(`${w[`fecha${extremo}Acceso`]}T${w[`hora${extremo}Acceso`]}:${w[`minuto${extremo}Acceso`]}:00-04:00`);if(Number.isNaN(fecha.getTime()))return 'Elige una fecha válida';return new Intl.DateTimeFormat('es-BO',{dateStyle:'full',timeStyle:'short',hourCycle:'h23',timeZone:'America/La_Paz'}).format(fecha);},
        avisoAcceso(detalle){window.Swal.fire({toast:true,position:'top-end',icon:'success',title:detalle.title,text:detalle.text,showConfirmButton:false,showCloseButton:true,timer:7000,timerProgressBar:true,customClass:{popup:'roles-confirmacion'},showClass:{popup:'roles-confirmacion-entrada'},hideClass:{popup:'roles-confirmacion-salida'},didOpen:elemento=>{elemento.addEventListener('mouseenter',()=>window.Swal.stopTimer());elemento.addEventListener('mouseleave',()=>window.Swal.resumeTimer());}});},
        get errorAcceso(){
            const w=this.$wire;const motivo=this.errorMotivo('acceso',w.tipoMotivoAcceso,w.motivoAcceso);if(motivo)return motivo;
            if(!w.usuariosAcceso.length)return 'Selecciona las cuentas destinatarias.';
            if(w.tipoAcceso==='PERMISOS'&&!w.permisosAcceso.length||w.tipoAcceso==='ROL'&&!w.rolAcceso)return 'Selecciona las tareas o el rol complementario.';
            if(!['DIA','PERSONALIZADO'].includes(w.modoVigenciaAcceso))return 'Selecciona Un día o Personalizado para este acceso temporal.';
            const hora=/^(?:[01][0-9]|2[0-3])$/,minuto=/^[0-5][0-9]$/;
            if(!this.fechaLocalValida(w.fechaInicioAcceso)||!this.fechaLocalValida(w.fechaFinAcceso)||!hora.test(w.horaInicioAcceso)||!hora.test(w.horaFinAcceso)||!minuto.test(w.minutoInicioAcceso)||!minuto.test(w.minutoFinAcceso))return 'Completa fechas y horas válidas.';
            const inicio=`${w.fechaInicioAcceso}T${w.horaInicioAcceso}:${w.minutoInicioAcceso}`,fin=`${w.fechaFinAcceso}T${w.horaFinAcceso}:${w.minutoFinAcceso}`;
            if(w.modoVigenciaAcceso==='DIA'&&new Date(fin+'-04:00')-new Date(inicio+'-04:00')!==86400000)return 'Un día requiere exactamente 24 horas. Cambia a Personalizado para ajustar las fechas.';
            if(w.permisosAcceso.includes('cursos.gestionar.global')&&new Date(fin+'-04:00')-new Date(inicio+'-04:00')>86400000)return 'La edición delegada de Cursos dura como máximo 24 horas.';
            if(fin<=inicio)return 'La fecha y hora de fin deben ser posteriores al inicio.';
            if(w.fechaInicioAcceso<this.soporte.minimo||w.fechaFinAcceso>this.soporte.maximo)return 'La vigencia debe quedar dentro de la gestión activa.';
            return '';
        },
        get filtrados() {
            const texto=this.busqueda.trim().toLocaleLowerCase('es');
            return this.permisos.filter(p => (!texto || (p.label+' '+p.domain+' '+p.scope_label).toLocaleLowerCase('es').includes(texto))
                && (!this.ventanaElegida||[...p.ventanas,...p.operaciones].some(v=>v.id===this.ventanaElegida))
                && (this.ventanaElegida||p.espacio===this.espacio) && (this.ventanaElegida||this.espacio!=='aula'||!this.tipoAula||p.tipo_aula===this.tipoAula)
                && (!this.dominio || p.domain===this.dominio) && (!this.estado || (this.estado==='consulta'?this.nivelPermiso(p).startsWith('Consulta'):this.estado==='edicion'?this.nivelPermiso(p)==='Edición':this.estado==='entrada'?this.nivelPermiso(p)==='Entrada al módulo':this.estado==='operacion'?this.nivelPermiso(p)==='Operación autorizada':this.nivelPermiso(p)==='Sin permiso')));
        },
        get paginas(){return Math.max(1,Math.ceil(this.filtrados.length/12));},
        get visibles(){return this.filtrados.slice((Math.min(this.pagina,this.paginas)-1)*12,Math.min(this.pagina,this.paginas)*12);},
        get cambios(){const base=this.roles.find(r=>r.id===Number(this.$wire.selectedRoleId))?.permisos??[];return {agregados:this.seleccion.filter(p=>!base.includes(p)),retirados:base.filter(p=>!this.seleccion.includes(p))};},
        icono(d){const n=d.toLowerCase();return n.includes('rol')?'ph-shield-check':n.includes('aula')?'ph-chalkboard-teacher':n.includes('reporte')?'ph-chart-bar':n.includes('usuario')?'ph-users':n.includes('calific')?'ph-exam':n.includes('orient')?'ph-compass':n.includes('asisten')?'ph-user-check':'ph-squares-four';},
        limpiar(){this.busqueda='';this.dominio='';this.tipoAula='';this.estado='';this.ventanaElegida='';this.pagina=1;},
        cambiarEspacio(espacio){this.espacio=espacio;this.limpiar();this.apartado='roles';},
        marcar(n){if(!this.$wire.edicionPermisos||this.proceso)return;const p=this.catalogo.find(p=>p.name===n);const bloqueo=p?this.bloqueoAsignacion(p):'El permiso no existe en el catálogo.';if(bloqueo){this.mensaje=bloqueo;return;}this.seleccion=this.seleccion.includes(n)?this.seleccion.filter(p=>p!==n):[...this.seleccion,n];},
        nivelPermiso(p,id=Number(this.$wire.selectedRoleId)){
            const rol=this.roles.find(r=>r.id===id),nombres=id===Number(this.$wire.selectedRoleId)?this.seleccion:rol?.permisos??[];
            if(nombres.includes(p.name))return p.capacidad==='entrada'?'Entrada al módulo':p.capacidad==='consulta'?'Consulta':p.capacidad==='edicion'?'Edición':'Operación autorizada';
            if(p.consultas_compatibles?.[rol?.nombre]&&nombres.includes(p.consultas_compatibles[rol.nombre]))return 'Consulta compatible';
            return 'Sin permiso';
        },
        async actuar(nombre, accion){
            if(this.proceso)return;this.proceso=nombre;this.mensaje='';
            try {await accion();} catch(e) {this.mensaje='No pudimos completar la operación. Conservamos tus datos; revisa el mensaje y vuelve a intentarlo.';}
            finally {this.proceso='';}
        },
        async seleccionarRol(id){if(this.cambios.agregados.length||this.cambios.retirados.length){this.mensaje='Guarda o descarta la selección antes de consultar otro rol.';return;}this.ventanaElegida='';await this.actuar('rol',()=>this.$wire.set('selectedRoleId',id));},
        descartar(){this.seleccion=[...(this.roles.find(r=>r.id===Number(this.$wire.selectedRoleId))?.permisos??[])];this.mensaje='';},
        async elegirCuenta(id){if(this.$wire.edicionUsuario){this.mensaje='Termina o descarta la edición antes de cambiar de persona.';return;}await this.actuar('cuenta',()=>this.$wire.seleccionarCuenta(id));},
        async accesoPersonal(permiso=null){this.editarRevisionAcceso=false;this.accesoVisible=true;await this.actuar('abrir-acceso',()=>this.$wire.accesoParaCuenta());if(!this.$wire.modalAcceso)this.accesoVisible=false;else if(permiso){this.$wire.permisosAcceso=[permiso];this.vigenciaUnDia();}},
        async abrirSolicitud(){this.solicitudVisible=true;await this.actuar('abrir',()=>this.$wire.openRequest());if(!this.$wire.showRequestModal)this.solicitudVisible=false;},
        cerrarSolicitud(){if(this.proceso==='guardar-solicitud')return;this.solicitudVisible=false;this.$wire.closeRequest();},
        async abrirAcceso(){this.editarRevisionAcceso=false;this.accesoVisible=true;await this.actuar('abrir-acceso',()=>this.$wire.abrirAcceso());if(!this.$wire.modalAcceso)this.accesoVisible=false;},
        cerrarAcceso(){if(this.proceso==='guardar-acceso')return;this.accesoVisible=false;this.$wire.set('modalAcceso',false);},
});
