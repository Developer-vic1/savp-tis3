window.selectorInstitucional = (enlace, opciones, identificador, numerico=false, multiple=false) => ({
    valor:enlace, multiple, opciones, identificador, abierto:false, busqueda:'', indice:0, posicion:'',
    get filtradas(){const normalizar=texto=>String(texto).normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase();return this.opciones.filter(opcion=>normalizar(opcion.etiqueta).includes(normalizar(this.busqueda)));},
    estaElegida(opcion){return multiple ? (Array.isArray(this.valor)?this.valor:[]).map(String).includes(String(opcion.valor)) : String(this.valor)===String(opcion.valor);},
    get elegidas(){return this.opciones.filter(opcion=>this.estaElegida(opcion));},
    get seleccionada(){if(multiple)return this.elegidas.length ? `${this.elegidas.length} seleccionados` : 'Seleccionar uno o varios';return this.opciones.find(opcion=>String(opcion.valor)===String(this.valor))?.etiqueta||'Seleccionar';},
    init(){this.reubicar=()=>{if(this.abierto)this.ubicar();};window.addEventListener('resize',this.reubicar);window.addEventListener('scroll',this.reubicar,true);},
    ubicar(){const r=this.$refs.control.getBoundingClientRect();const ancho=Math.min(Math.max(r.width,240),innerWidth-24);const espacioAbajo=innerHeight-r.bottom-12;const arriba=espacioAbajo<180 && r.top>espacioAbajo;const alto=Math.max(100,Math.min(320,arriba?r.top-20:espacioAbajo));this.posicion=`left:${Math.max(12,Math.min(r.left,innerWidth-ancho-12))}px;width:${ancho}px;max-height:${alto}px;${arriba?`bottom:${innerHeight-r.top+6}px`:`top:${r.bottom+6}px`}`;},
    abrir(){if(this.$refs.control.disabled)return;if(this.abierto){this.cerrar();return;}clearTimeout(this.cierre);this.busqueda='';this.indice=Math.max(0,this.opciones.findIndex(opcion=>String(opcion.valor)===String(this.valor)));this.abierto=true;this.$nextTick(()=>{this.ubicar();this.$refs.panel.showPopover?.();this.verOpcion();});},
    cerrar(){this.abierto=false;clearTimeout(this.cierre);const ocultar=()=>this.$refs.panel.hidePopover?.();if(matchMedia('(prefers-reduced-motion: reduce)').matches)ocultar();else this.cierre=setTimeout(ocultar,140);},
    elegir(opcion){if(!opcion||opcion.deshabilitada)return;if(multiple){const valores=Array.isArray(this.valor)?[...this.valor]:[];this.valor=this.estaElegida(opcion)?valores.filter(v=>String(v)!==String(opcion.valor)):[...valores,String(opcion.valor)];}else{this.valor=numerico?Number(opcion.valor):String(opcion.valor);this.cerrar();}this.$refs.control.dispatchEvent(new Event('input',{bubbles:true}));this.$refs.control.focus({preventScroll:true});},
    verOpcion(){this.$refs.panel.querySelector(`[data-indice="${this.indice}"]`)?.scrollIntoView({block:'nearest'});},
    teclado(evento){
        if(evento.key==='Escape' && this.abierto){evento.preventDefault();evento.stopPropagation();this.cerrar();this.$refs.control.focus();return;}
        if(['ArrowDown','ArrowUp','Home','End'].includes(evento.key)){
            evento.preventDefault();if(!this.abierto){this.abrir();return;}
            this.indice=evento.key==='Home'?0:evento.key==='End'?this.filtradas.length-1:Math.max(0,Math.min(this.filtradas.length-1,this.indice+(evento.key==='ArrowDown'?1:-1)));
            this.$nextTick(()=>this.verOpcion());return;
        }
        if((evento.key==='Enter'||(evento.key===' '&&evento.target===this.$refs.control)) && this.abierto){evento.preventDefault();this.elegir(this.filtradas[this.indice]);}
        if(evento.key==='Tab')this.cerrar();
    },
    destroy(){clearTimeout(this.cierre);this.$refs.panel.hidePopover?.();window.removeEventListener('resize',this.reubicar);window.removeEventListener('scroll',this.reubicar,true);},
});
