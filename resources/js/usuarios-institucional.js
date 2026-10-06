window.gestionUsuariosPage = iniciales => {
    const graficos = {};
    const opciones = [{id:'tabla',nombre:'Tabla',icono:'ph-table'}, {id:'directorio',nombre:'Directorio',icono:'ph-list-bullets'}, {id:'galeria',nombre:'Galería',icono:'ph-squares-four'}];
    return {
        ...window.paginacionInstitucional(),
        datos: iniciales, mostrarIndicadores: true, incluirEstudiantes:false, vista:'tabla', opcionesVista:opciones,
        get rolesVisibles() {
            return (this.datos.roles?.labels||[]).map((nombre,i)=>({nombre,cantidad:this.datos.roles.data[i],indice:i}))
                .filter(item=>(this.incluirEstudiantes || item.nombre!=='Estudiante') && item.cantidad>0);
        },
        get mosaicoRoles() {
            const repartir=(items,x,y,ancho,alto)=>{
                if(!items.length)return [];
                if(items.length===1)return [{...items[0],x,y,ancho,alto}];
                const total=items.reduce((suma,item)=>suma+item.cantidad,0);
                let corte=1,suma=items[0].cantidad;
                while(corte<items.length-1 && Math.abs(total/2-suma-items[corte].cantidad)<Math.abs(total/2-suma)){suma+=items[corte++].cantidad;}
                const fraccion=suma/total;
                return ancho>=alto?[...repartir(items.slice(0,corte),x,y,ancho*fraccion,alto),...repartir(items.slice(corte),x+ancho*fraccion,y,ancho*(1-fraccion),alto)]
                    :[...repartir(items.slice(0,corte),x,y,ancho,alto*fraccion),...repartir(items.slice(corte),x,y+alto*fraccion,ancho,alto*(1-fraccion))];
            };
            return repartir(this.rolesVisibles,0,0,1000,600);
        },
        get cuadriculaGenero() {
            const datos=this.datos.generos;
            if(!datos) return [];
            const total=datos.data.reduce((suma,valor)=>suma+valor,0);
            if(!total) return [];
            const grupos=datos.data.map((cantidad,i)=>({i,cantidad,celdas:Math.floor(cantidad/total*100),resto:cantidad/total*100%1}));
            let faltan=100-grupos.reduce((suma,item)=>suma+item.celdas,0);
            [...grupos].sort((a,b)=>b.resto-a.resto).forEach(item=>{if(faltan-->0)item.celdas++;});
            return grupos.flatMap(item=>Array.from({length:item.celdas},()=>({indice:item.i,nombre:datos.labels[item.i],cantidad:item.cantidad})));
        },
        init() {
            try { const anterior=localStorage.getItem('gestion-usuarios-view'); if(opciones.some(opcion=>opcion.id===anterior)) this.vista=anterior; } catch { /* Preferencia opcional. */ }
            this.actualizarTema = () => this.dibujar('none');
            window.addEventListener('theme-changed', this.actualizarTema);
            this.$watch('mostrarIndicadores', visible => { if (visible) this.$nextTick(() => this.dibujar('none')); });
            this.$nextTick(() => this.dibujar());
        },
        cambiarVista(vista) {
            if(!opciones.some(opcion=>opcion.id===vista) || vista===this.vista) return;
            this.vista=vista;
            try {localStorage.setItem('gestion-usuarios-view',vista);} catch { /* Preferencia opcional. */ }
            this.animarResultados();
        },
        async pedirMotivo(titulo, texto) {
            const resultado=await window.Swal.fire({title:titulo,text:texto,icon:'warning',input:'textarea',inputLabel:'Motivo de desactivación',inputPlaceholder:'Explica por qué se desactiva el acceso.',inputAttributes:{maxlength:500},showCancelButton:true,confirmButtonText:'Sí, desactivar',cancelButtonText:'Cancelar',
                preConfirm:valor=>{if((valor||'').trim().length<10){window.Swal.showValidationMessage('Describe el motivo con al menos 10 caracteres.');return false;}return valor;}});
            return resultado.isConfirmed?resultado.value:null;
        },
        async confirmarDesactivacion(codigo) {
            const motivo=await this.pedirMotivo('¿Desactivar esta cuenta?','La persona perderá el acceso. El motivo quedará registrado en bitácora.');
            if(motivo!==null) await this.$wire.desactivarUsuario(codigo,motivo);
        },
        async confirmarLote() {
            if(this.$wire.accionLote==='inactivar') {
                const motivo=await this.pedirMotivo('¿Desactivar las cuentas seleccionadas?','Tu cuenta y el último administrador activo quedan protegidos. Se registrará el motivo y las cuentas afectadas.');
                if(motivo!==null) await this.$wire.aplicarAccionLote(motivo);
            } else {
                const resultado=await window.Swal.fire({title:'¿Activar las cuentas seleccionadas?',icon:'question',showCancelButton:true,confirmButtonText:'Sí, activar',cancelButtonText:'Cancelar'});
                if(resultado.isConfirmed) await this.$wire.aplicarAccionLote();
            }
        },
        async confirmarEdicion(estadoAnterior) {
            if(estadoAnterior==='ACTIVO' && this.$wire.formEditar.est_usu==='INACTIVO') {
                const resultado=await window.Swal.fire({title:'¿Desactivar esta cuenta?',text:'Se guardará el motivo indicado y la persona perderá el acceso.',icon:'warning',showCancelButton:true,confirmButtonText:'Sí, guardar cambio',cancelButtonText:'Cancelar'});
                if(!resultado.isConfirmed) return;
            }
            await this.$wire.guardarEdicionUsuario();
        },
        actualizarIndicadores(datos) { this.datos = datos; this.$nextTick(() => this.dibujar()); },
        repetirAnimacion() { Object.values(graficos).forEach(grafico => grafico.reset()); this.dibujar(); },
        dibujar(modo) {
            if (!window.Chart || !this.$el.isConnected) return;
            const css = getComputedStyle(document.documentElement);
            const token = nombre => css.getPropertyValue(nombre).trim();
            const reducido = matchMedia('(prefers-reduced-motion: reduce)').matches;
            ['edades'].forEach(clave => {
                const canvas = this.$el.querySelector(`#usuarios-grafico-${clave}`);
                if (graficos[clave] && graficos[clave].canvas !== canvas) { graficos[clave].destroy(); delete graficos[clave]; }
                if (!canvas) return;
                const original = this.datos[clave];
                if(!original) return;
                const indices = original.labels.map((_,i)=>i);
                const datos = {labels:indices.map(i=>original.labels[i]),data:indices.map(i=>original.data[i])};
                const puntos=datos.data.map((cantidad,i)=>({x:cantidad,y:i,r:8}));
                const colores = indices.map(i=>token(['--ui-primary','--ui-info','--ui-violet','--ui-warning','--ui-danger','--ui-muted'][i%6]));
                if (graficos[clave]) {
                    graficos[clave].data.labels = [...datos.labels];
                    graficos[clave].data.datasets[0].data = puntos;
                    graficos[clave].data.datasets[0].backgroundColor = colores;
                    graficos[clave].data.datasets[0].borderColor = token('--ui-surface');
                    graficos[clave].options.scales.y.max=datos.labels.length-.5;
                    graficos[clave].options.scales.y.ticks.callback=valor=>datos.labels[valor]||'';
                    graficos[clave].options.animation = reducido ? false : {duration:500,easing:'easeOutQuart'};
                    graficos[clave].update(reducido ? 'none' : modo);
                    return;
                }
                graficos[clave] = new window.Chart(canvas, {
                    type:'bubble',
                    data:{labels:[...datos.labels],datasets:[{label:'Cuentas',data:puntos,backgroundColor:colores,borderColor:token('--ui-surface'),borderWidth:2}]},
                    options:{responsive:true,maintainAspectRatio:false,
                        animation:reducido ? false : {duration:500,easing:'easeOutQuart',delay:ctx=>ctx.type==='data'?ctx.dataIndex*45:0},
                        plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>`${datos.labels[ctx.raw.y]}: ${ctx.raw.x} cuentas`}}},
                        scales:{x:{beginAtZero:true,ticks:{precision:0},grid:{color:token('--ui-border')}},y:{min:-.5,max:datos.labels.length-.5,reverse:true,ticks:{stepSize:1,callback:valor=>datos.labels[valor]||'',font:{size:10}},grid:{color:token('--ui-border')}}},
                    },
                });
            });
        },
        destroy() { this.destruirPaginacion(); window.removeEventListener('theme-changed',this.actualizarTema); Object.values(graficos).forEach(grafico=>grafico.destroy()); },
    };
};
