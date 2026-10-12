window.selectorInstitucional = (enlace, opciones, identificador, numerico=false, multiple=false, valorInicial=null) => ({
    valor:enlace ?? valorInicial, multiple, opciones, identificador, abierto:false, busqueda:'', indice:0, posicion:'',
    get mostrarBusqueda(){return this.opciones.length > 10;},
    get filtradas(){const normalizar=texto=>String(texto).normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();return this.opciones.filter(opcion=>!this.mostrarBusqueda || normalizar(opcion.etiqueta).includes(normalizar(this.busqueda)));},
    estaElegida(opcion){return multiple ? (Array.isArray(this.valor)?this.valor:[]).map(String).includes(String(opcion.valor)) : String(this.valor)===String(opcion.valor);},
    get elegidas(){return this.opciones.filter(opcion=>this.estaElegida(opcion));},
    get seleccionada(){if(multiple)return this.elegidas.length ? `${this.elegidas.length} seleccionados` : 'Seleccionar uno o varios';return this.opciones.find(opcion=>String(opcion.valor)===String(this.valor))?.etiqueta||'Seleccionar';},
    init(){this.reubicar=()=>{if(this.abierto)this.ubicar();};window.addEventListener('resize',this.reubicar);window.addEventListener('scroll',this.reubicar,true);},
    ubicar(){const r=this.$refs.control.getBoundingClientRect();const ancho=Math.min(Math.max(r.width,240),innerWidth-24);const espacioAbajo=innerHeight-r.bottom-12;const arriba=espacioAbajo<180 && r.top>espacioAbajo;const alto=Math.max(100,Math.min(320,arriba?r.top-20:espacioAbajo));this.posicion=`left:${Math.max(12,Math.min(r.left,innerWidth-ancho-12))}px;width:${ancho}px;max-height:${alto}px;${arriba?`bottom:${innerHeight-r.top+6}px`:`top:${r.bottom+6}px`}`;},
    primerIndice(){return this.filtradas.findIndex(opcion=>!opcion.deshabilitada);},
    abrir(){if(this.$refs.control.disabled)return;if(this.abierto){this.cerrar();return;}clearTimeout(this.cierre);this.busqueda='';const elegido=this.opciones.findIndex(opcion=>String(opcion.valor)===String(this.valor)&&!opcion.deshabilitada);this.indice=elegido>=0?elegido:this.primerIndice();this.abierto=true;this.$nextTick(()=>{this.ubicar();this.$refs.panel.showPopover?.();this.verOpcion();(this.mostrarBusqueda?this.$refs.buscar:this.$refs.control)?.focus({preventScroll:true});});},
    cerrar(){this.abierto=false;clearTimeout(this.cierre);const ocultar=()=>this.$refs.panel.hidePopover?.();if(matchMedia('(prefers-reduced-motion: reduce)').matches)ocultar();else this.cierre=setTimeout(ocultar,140);},
    elegir(opcion){if(!opcion||opcion.deshabilitada)return;if(multiple){const valores=Array.isArray(this.valor)?[...this.valor]:[];this.valor=this.estaElegida(opcion)?valores.filter(v=>String(v)!==String(opcion.valor)):[...valores,String(opcion.valor)];}else{this.valor=numerico?Number(opcion.valor):String(opcion.valor);this.cerrar();}const campo=this.$refs.campo||this.$refs.control;if(this.$refs.campo)this.$refs.campo.value=this.valor;campo.dispatchEvent(new Event('input',{bubbles:true}));campo.dispatchEvent(new Event('change',{bubbles:true}));this.$dispatch?.('cambio-selector',{valor:this.valor});this.$refs.control.focus({preventScroll:true});},
    verOpcion(){this.$refs.panel.querySelector(`[data-indice="${this.indice}"]`)?.scrollIntoView({block:'nearest'});},
    teclado(evento){
        if(evento.key==='Escape' && this.abierto){evento.preventDefault();evento.stopPropagation();this.cerrar();this.$refs.control.focus();return;}
        if(['ArrowDown','ArrowUp','Home','End'].includes(evento.key)){
            evento.preventDefault();if(!this.abierto){this.abrir();return;}
            const disponibles=this.filtradas.map((opcion,i)=>opcion.deshabilitada?-1:i).filter(i=>i>=0);const actual=disponibles.indexOf(this.indice);
            this.indice=evento.key==='Home'?disponibles[0]:evento.key==='End'?disponibles.at(-1):disponibles[Math.max(0,Math.min(disponibles.length-1,actual+(evento.key==='ArrowDown'?1:-1)))];
            this.$nextTick(()=>this.verOpcion());return;
        }
        if((evento.key==='Enter'||(evento.key===' '&&evento.target===this.$refs.control)) && this.abierto){evento.preventDefault();this.elegir(this.filtradas[this.indice]);}
        if(evento.key==='Tab')this.cerrar();
    },
    destroy(){clearTimeout(this.cierre);this.$refs.panel.hidePopover?.();window.removeEventListener('resize',this.reubicar);window.removeEventListener('scroll',this.reubicar,true);},
});
