window.cursosInstitucionales = () => ({
    ...window.paginacionInstitucional(), vista:'tabla', movil:matchMedia('(max-width:700px)').matches,
    detalle:false,claseAbierta:false,formulario:false,procesando:'',mensajeProceso:'',errorProceso:'',origen:null,generacion:0,media:null,alCambiar:null,consultaEnCurso:false,seccionConsulta:'ficha',
    init(){this.media=matchMedia('(max-width:700px)');this.alCambiar=e=>this.movil=e.matches;this.media.addEventListener('change',this.alCambiar);},
    async ejecutar(clave,mensaje,accion){
        if(this.procesando)return false;
        this.procesando=clave;this.mensajeProceso=mensaje;this.errorProceso='';
        try{await accion();return true;}catch{this.errorProceso='No pudimos completar la consulta. Conservamos los datos; vuelve a intentarlo.';return false;}
        finally{this.procesando='';this.mensajeProceso='';}
    },
    async consultar(id,seccion,boton){
        if(this.procesando)return;
        this.claseAbierta=false;this.formulario=false;this.origen=boton;const turno=++this.generacion;
        this.seccionConsulta=seccion;this.consultaEnCurso=true;this.detalle=true;
        this.$nextTick(()=>this.$refs.detallePanel.querySelector('button')?.focus());
        await this.ejecutar(seccion+'-'+id,'Cargando '+({ficha:'la ficha',horario:'el horario',carga:'las cargas'}[seccion])+' del grado…',()=>this.$wire.consultarCurso(id,seccion));
        if(turno===this.generacion)this.consultaEnCurso=false;
    },
    async seccion(nombre){await this.ejecutar('seccion','Cargando la información seleccionada…',()=>this.$wire.cambiarSeccion(nombre));},
    cerrarDetalle(){if(this.claseAbierta)return;this.generacion++;this.detalle=false;this.$wire.$set('modalDetalle',false,false);this.origen?.focus();},
    async prepararClase(celda){if(this.procesando||this.formulario||!this.detalle||this.seccionConsulta!=='horario'||!celda.dia||!celda.bloque)return;const turno=this.generacion;if(await this.ejecutar('preparar-clase','Preparando el bloque y los docentes…',()=>this.$wire.crearClaseDesdeBloque(celda.dia,celda.bloque))&&turno===this.generacion&&this.$wire.modalClaseHorario&&!this.formulario){this.claseAbierta=true;this.$nextTick(()=>this.$refs.clasePanel?.querySelector('button')?.focus());}},
    cerrarClase(){if(this.procesando==='guardar-clase')return;this.claseAbierta=false;this.$wire.$set('modalClaseHorario',false,false);this.$refs.detallePanel.querySelector('button')?.focus();},
    async elegirMateria(id,tipo){await this.ejecutar('materia-clase','Revisando materia, docente y disponibilidad…',()=>this.$wire.elegirMateriaClase(id,tipo));},
    async guardarClase(){if(await this.ejecutar('guardar-clase','Revisando cruces y registrando la clase…',()=>this.$wire.guardarClaseHorario())&&!this.$wire.modalClaseHorario)this.claseAbierta=false;},
    async formularioCurso(operacion,id,boton){
        if(this.procesando)return;
        this.claseAbierta=false;this.detalle=false;
        this.origen=boton;const turno=++this.generacion;
        if(await this.ejecutar('formulario','Preparando los requisitos del cambio…',()=>this.$wire.abrirFormularioCurso(operacion,id))&&turno===this.generacion){this.formulario=true;this.$nextTick(()=>this.$refs.formularioPanel.querySelector('button')?.focus());}
    },
    cerrarFormulario(){if(this.procesando==='guardar')return;this.generacion++;this.formulario=false;this.$wire.$set('modalFormulario',false,false);this.origen?.focus();},
    async interpretar(){await this.ejecutar('interpretar','Reconociendo el grado…',()=>this.$wire.interpretarCurso());},
    async elegirGrado(grado){await this.ejecutar('interpretar','Preparando el grado seleccionado…',()=>this.$wire.elegirGrado(grado));},
    async revisarDocumento(){await this.ejecutar('documento','Leyendo el PDF y contrastando los datos…',()=>this.$wire.analizarRespaldo());},
    async continuarFase(){await this.ejecutar('fase','Revisando los requisitos del paso…',()=>this.$wire.continuarFaseCurso());},
    async volverFase(){await this.ejecutar('fase','Volviendo al paso anterior…',()=>this.$wire.volverFaseCurso());},
    async guardar(){if(await this.ejecutar('guardar','Validando y registrando el cambio…',()=>this.$wire.guardarCambioCurso())){if(!this.$wire.modalFormulario){this.formulario=false;this.origen?.focus();}}},
    destroy(){this.media?.removeEventListener('change',this.alCambiar);this.destruirPaginacion();},
});

window.trayectoriaCursos = (datos,actual) => ({
    datos,elegidos:[...new Set(datos.map(d=>d.curso))].slice(0,3),aniosElegidos:[...new Set(datos.map(d=>String(d.anio)))],seleccion:String(actual),
    anios:[],maximo:1,minimo:0,series:[],
    init(){
        this.actualizarGrafico();
        this.$watch('elegidos',()=>this.actualizarGrafico());
        this.$watch('aniosElegidos',()=>this.actualizarGrafico());
    },
    actualizarGrafico(){
        // Cada enlace SVG lee el mismo resultado; recalcular solo al cambiar filtros.
        const anios=[...new Set(this.datos.map(d=>String(d.anio)))].filter(a=>this.aniosElegidos.includes(a)).sort();
        const filas=this.datos.filter(d=>this.elegidos.includes(d.curso)&&anios.includes(String(d.anio)));
        const valores=filas.map(d=>Number(d.estudiantes));
        const maximo=Math.max(1,...valores);
        const minimo=valores.length?Math.max(0,Math.floor(Math.min(...valores)*.85)):0;
        const indice=new Map(this.datos.map(d=>[d.curso+'|'+d.anio,d]));
        const series=this.elegidos.map(id=>({id,nombre:this.datos.find(d=>d.curso===id)?.nombre||'Grado',puntos:anios.flatMap((anio,i)=>{
            const d=indice.get(id+'|'+anio);
            return d?[{anio,valor:Number(d.estudiantes),x:45+i*550/Math.max(1,anios.length-1),y:190-(Number(d.estudiantes)-minimo)/Math.max(1,maximo-minimo)*170}]:[];
        })}));
        this.anios=anios;this.maximo=maximo;this.minimo=minimo;this.series=series;
    },
    x(indice){return 45+indice*550/Math.max(1,this.anios.length-1);},y(valor){return 190-(Number(valor)-this.minimo)/Math.max(1,this.maximo-this.minimo)*170;},
    resumenSerie(s){const punto=s.puntos.find(p=>p.anio===this.seleccion)||s.puntos.at(-1);if(!punto)return 'Sin dato registrado';const anterior=s.puntos[s.puntos.indexOf(punto)-1];const cambio=anterior?punto.valor-anterior.valor:null;return punto.anio+': '+punto.valor+' estudiantes'+(cambio===null?'':' · '+(cambio>0?'+':'')+cambio+' respecto a '+anterior.anio);},
});
