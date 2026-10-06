window.asignaturasInstitucionales = () => ({
    ...window.paginacionInstitucional(), vista:'tabla', movil:matchMedia('(max-width:700px)').matches,
    procesando:'', mensajeProceso:'', errorProceso:'', detalle:false, generacion:0, origen:null,
    media:null, alCambiar:null,
    init(){this.media=matchMedia('(max-width:700px)');this.alCambiar=e=>this.movil=e.matches;this.media.addEventListener('change',this.alCambiar);},
    async ejecutar(clave,mensaje,accion){
        if(this.procesando)return false;
        this.procesando=clave;this.mensajeProceso=mensaje;this.errorProceso='';
        try{await accion();return true;}catch{this.errorProceso='No pudimos completar la consulta. Los datos se conservan; vuelve a intentarlo.';return false;}
        finally{this.procesando='';this.mensajeProceso='';}
    },
    async consultar(id,boton){
        if(this.procesando)return;this.origen=boton;const turno=++this.generacion;
        this.detalle=true;
        const correcto=await this.ejecutar('detalle','Consultando docentes y trayectoria…',()=>this.$wire.abrirModalDetalle(id));
        if(turno!==this.generacion)this.$wire.$set('modalDetalle',false,false);
        else if(correcto)this.$nextTick(()=>this.$refs.detallePanel.querySelector('button')?.focus());
    },
    cerrarDetalle(){this.generacion++;this.detalle=false;this.$wire.$set('modalDetalle',false,false);this.origen?.focus();},
    async abrir(tipo,id,boton){
        if(this.procesando)return;this.origen=boton;
        const acciones={crear:()=>this.$wire.abrirModalCrear(),editar:()=>this.$wire.abrirModalEditar(id),catalogo:()=>this.$wire.abrirModalCatalogo(),retirar:()=>this.$wire.solicitarDesactivar(id),recuperar:()=>this.$wire.solicitarReactivar(id)};
        await this.ejecutar(tipo,'Preparando la información…',acciones[tipo]);
    },
    cerrar(propiedad){this.$wire.$set(propiedad,false,false);this.origen?.focus();},
    async filtrarCampo(clave){await this.ejecutar('campo','Actualizando las asignaturas…',()=>this.$wire.$set('campoEducativo',this.$wire.campoEducativo===clave?'':clave));},
    destroy(){this.media?.removeEventListener('change',this.alCambiar);this.destruirPaginacion();},
});
