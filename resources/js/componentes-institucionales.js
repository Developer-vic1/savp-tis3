window.paginacionInstitucional = () => {
    let animacion = null;
    return {
        cargandoPagina: false,
        animarResultados(desplazar = false) {
            animacion?.cancel();
            this.$nextTick(() => {
                if (!this.$refs.resultados) return;
                if (desplazar) {
                    const cabecera = document.querySelector('.savp-workspace-header');
                    const margen = (cabecera?.getBoundingClientRect().height || 100) + 24;
                    window.scrollTo({ top: this.$refs.resultados.getBoundingClientRect().top + window.scrollY - margen,
                        behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
                }
                if (!matchMedia('(prefers-reduced-motion: reduce)').matches) animacion = this.$refs.resultados.animate([
                    { opacity: .65, transform: 'translateY(8px)' }, { opacity: 1, transform: 'translateY(0)' },
                ], { duration: 260, easing: 'cubic-bezier(.2,.8,.2,1)' });
            });
        },
        async navegarPagina(pagina, nombre) {
            if (pagina < 1 || this.cargandoPagina) return;
            this.cargandoPagina = true;
            try { await this.$wire.gotoPage(pagina, nombre); this.animarResultados(true); }
            catch { this.avisar('error', 'No pudimos cargar esa página. Vuelve a intentarlo.'); }
            finally { this.cargandoPagina = false; }
        },
        async cambiarCantidad(cantidad) {
            if (![10,20,50].includes(cantidad) || this.cargandoPagina || cantidad === Number(this.$wire.perPage)) return;
            this.cargandoPagina = true;
            try { await this.$wire.set('perPage', cantidad); this.animarResultados(true); }
            catch { this.avisar('error', 'No pudimos actualizar la cantidad. Vuelve a intentarlo.'); }
            finally { this.cargandoPagina = false; }
        },
        avisar(type, message) { window.dispatchEvent(new CustomEvent('toast', { detail: { type, message } })); },
        destruirPaginacion() { animacion?.cancel(); },
        destroy() { this.destruirPaginacion(); },
    };
};

window.calendarioInstitucional = (enlace, hoy, minimo, inicio = hoy) => ({
    fecha: enlace, abierto: false, mes: 0, anio: 2000,
    meses: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
    anios: Array.from({length:Number(hoy.slice(0,4))-Number(minimo.slice(0,4))+1}, (_,i)=>Number(hoy.slice(0,4))-i),
    init() { this.sincronizar(); },
    sincronizar() {
        const referencia = inicio < minimo ? minimo : inicio > hoy ? hoy : inicio;
        const base = /^\d{4}-\d{2}-\d{2}$/.test(this.fecha || '') ? this.fecha : referencia;
        this.anio = Number(base.slice(0,4)); this.mes = Number(base.slice(5,7))-1;
    },
    get fechaVisible() {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(this.fecha || '')) return '';
        return `${this.fecha.slice(8,10)}/${this.fecha.slice(5,7)}/${this.fecha.slice(0,4)}`;
    },
    get dias() {
        const primero = new Date(this.anio,this.mes,1,12);
        const huecos = (primero.getDay()+6)%7;
        const cantidad = new Date(this.anio,this.mes+1,0,12).getDate();
        return Array.from({length:42},(_,indice)=> {
            const numero = indice-huecos+1;
            if (numero<1 || numero>cantidad) return null;
            const fecha = `${this.anio}-${String(this.mes+1).padStart(2,'0')}-${String(numero).padStart(2,'0')}`;
            return { numero, fecha, bloqueado: fecha<minimo || fecha>hoy,
                nombre: `${numero} de ${this.meses[this.mes]} de ${this.anio}` };
        });
    },
    abrir() { this.abierto=!this.abierto; if(this.abierto){this.sincronizar();this.$nextTick(()=>this.$refs.mes.querySelector('[role="combobox"]').focus());} },
    puedeMover(sentido) {
        const destino = new Date(this.anio,this.mes+sentido,1,12);
        const periodo = `${destino.getFullYear()}-${String(destino.getMonth()+1).padStart(2,'0')}`;
        return periodo>=minimo.slice(0,7) && periodo<=hoy.slice(0,7);
    },
    mover(sentido) { if(!this.puedeMover(sentido)) return;const destino=new Date(this.anio,this.mes+sentido,1,12);this.anio=destino.getFullYear();this.mes=destino.getMonth(); },
    elegir(fecha) { if(fecha<minimo || fecha>hoy) return;this.fecha=fecha;this.abierto=false;this.$nextTick(()=>{this.$refs.fecha.focus({preventScroll:true});this.$refs.fecha.dispatchEvent(new Event('input',{bubbles:true}));}); },
    moverFoco(evento, fecha) {
        const saltos={ArrowLeft:-1,ArrowRight:1,ArrowUp:-7,ArrowDown:7};
        if(!(evento.key in saltos)) return;
        evento.preventDefault();
        const dia=Number(fecha.slice(8,10))+saltos[evento.key];
        const destino=this.dias.find(item=>item && item.numero===dia && !item.bloqueado);
        if(destino) this.$refs.dias.querySelector(`[data-fecha="${destino.fecha}"]`)?.focus();
    },
});
