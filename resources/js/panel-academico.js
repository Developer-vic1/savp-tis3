// Vistas de consulta: no guardan datos ni sustituyen las reglas del servidor.
window.focoModalAcademico = (event, contenedor) => {
    const elementos = [...contenedor.querySelectorAll('button, input, select, textarea, a[href], [tabindex]')]
        .filter(e => !e.disabled && e.tabIndex >= 0 && e.getClientRects().length && getComputedStyle(e).visibility !== 'hidden');
    const primero = elementos[0], ultimo = elementos.at(-1);
    if (!primero) { event.preventDefault(); return; }
    if (event.shiftKey && (document.activeElement === primero || document.activeElement === contenedor)) {
        event.preventDefault(); ultimo.focus();
    } else if (!event.shiftKey && document.activeElement === ultimo) {
        event.preventDefault(); primero.focus();
    }
};
window.calendarioAcademico = (anio, periodos, eventos, hoy) => ({
    anio: Number(anio), periodos, eventos, hoy, mes: 0, elegida: '',
    meses: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
    init() {
        const inicio = this.periodos.find(p => p.fecha_inicio)?.fecha_inicio;
        this.mes = Number((String(this.anio) === hoy.slice(0,4) ? hoy : inicio || `${anio}-01-01`).slice(5,7)) - 1;
    },
    fecha(dia) { return `${this.anio}-${String(this.mes+1).padStart(2,'0')}-${String(dia).padStart(2,'0')}`; },
    get dias() {
        const inicio = (new Date(Date.UTC(this.anio, this.mes, 1)).getUTCDay()+6)%7;
        const cantidad = new Date(Date.UTC(this.anio, this.mes+1, 0)).getUTCDate();
        return [...Array(inicio).fill(null), ...Array.from({length:cantidad}, (_,i)=>({numero:i+1, fecha:this.fecha(i+1)}))];
    },
    trimestres(fecha) { return this.periodos.filter(p=>p.fecha_inicio && p.fecha_fin && fecha>=p.fecha_inicio.slice(0,10) && fecha<=p.fecha_fin.slice(0,10)); },
    eventosDia(fecha) { return this.eventos.filter(e=>fecha>=e.inicio && fecha<=e.fin); },
    tono(fecha) { const p=this.trimestres(fecha); return p.length>1?'solapado':p.length?String(p[0].orden):'fuera'; },
    descripcion(fecha) {
        return `${fecha.split('-').reverse().join('/')} · ${this.trimestres(fecha).map(p=>p.nombre).join(' y ') || 'Sin trimestre registrado'} · ${this.eventosDia(fecha).length} eventos`;
    },
    mover(delta) { this.mes=Math.max(0,Math.min(11,this.mes+delta)); this.elegida=''; },
    teclado(event, fecha) {
        const desplazamiento={ArrowLeft:-1,ArrowRight:1,ArrowUp:-7,ArrowDown:7}[event.key];
        if (!desplazamiento) return;
        event.preventDefault();
        const siguiente=new Date(`${fecha}T12:00:00Z`);
        siguiente.setUTCDate(siguiente.getUTCDate()+desplazamiento);
        if(siguiente.getUTCFullYear()!==this.anio) return;
        this.mes=siguiente.getUTCMonth();
        this.$nextTick(()=>this.$refs.dias.querySelector(`[data-fecha="${siguiente.toISOString().slice(0,10)}"]`)?.focus());
    },
});

// Borradores privados del navegador; no registran ni aprueban eventos institucionales.
window.comparativaAcademica = (datos, anio) => ({
    datos, indicador:'inscripciones', seleccion:String(anio), escala:'detalle', vista:'linea', modo:'todas',
    anioBase:String(datos.find(d=>Number(d.anio)===2021)?.anio || datos[0]?.anio || ''),
    anioComparado:String(datos.find(d=>Number(d.anio)===2026)?.anio || datos.at(-1)?.anio || ''),
    gestionesElegidas: [...new Set([String(datos.find(d=>Number(d.anio)===2021)?.anio || datos[0]?.anio || ''), String(datos.find(d=>Number(d.anio)===2026)?.anio || datos.at(-1)?.anio || '')])].filter(Boolean),
    resaltada:'',
    ejes:['inscripciones','planes','planes_tecnicos','docentes','grupos','horarios'],
    nombres:{inscripciones:'Inscripciones',planes:'Planes de asignatura',planes_tecnicos:'Planes técnicos',docentes:'Docentes en planes',grupos:'Grupos académicos',horarios:'Horarios',eventos:'Eventos del calendario'},
    init(){ if(!this.datos.some(d=>String(d.anio)===this.seleccion)) this.seleccion=String(this.datos.at(-1)?.anio || '');
        this.$watch('modo',()=>this.ajustarSeleccion());this.$watch('gestionesElegidas',()=>this.ajustarSeleccion()); },
    numero(valor){return new Intl.NumberFormat('es-BO',{maximumFractionDigits:1}).format(valor);},
    valor(dato, clave=this.indicador){ const v=dato?.[clave]; return v===null || v===undefined || v==='' || !Number.isFinite(Number(v))?null:Number(v); },
    get datosVisibles(){return this.modo==='todas'?this.datos:this.pareja;},
    get actual(){return this.datosVisibles.find(d=>String(d.anio)===this.seleccion);},
    get indiceActual(){return this.datosVisibles.findIndex(d=>String(d.anio)===this.seleccion);},
    get anterior(){return this.datosVisibles[this.indiceActual-1];},
    get valorActual(){return this.valor(this.actual);},
    get variacion(){ const a=this.valorActual,b=this.valor(this.anterior);return a===null || b===null?null:a-b;},
    get porcentaje(){ const b=this.valor(this.anterior);return this.variacion===null || !b?null:this.variacion/b*100;},
    ajustarSeleccion(){if(!this.datosVisibles.some(d=>String(d.anio)===this.seleccion))this.seleccion=String(this.datosVisibles.at(-1)?.anio || '');},
    mover(delta){const i=Math.max(0,Math.min(this.datosVisibles.length-1,this.indiceActual+delta));this.seleccion=String(this.datosVisibles[i]?.anio || '');},
    get limites(){const valores=this.datosVisibles.map(d=>this.valor(d)).filter(v=>v!==null),max=Math.max(0,...valores),min=Math.min(max,...valores);
        if(this.escala==='general') return {min:0,max:Math.max(1,max)};
        const margen=Math.max(1,Math.ceil((max-min)*.2));return {min:Math.max(0,min-margen),max:max+margen};},
    get niveles(){const l=this.limites;return [0,.5,1].map(n=>({valor:l.min+(l.max-l.min)*n,y:218-173*n}));},
    get puntos(){const l=this.limites,d=this.datosVisibles,primero=Number(d[0]?.anio || 0),ultimo=Number(d.at(-1)?.anio || primero);
        return d.map(g=>({anio:g.anio,valor:this.valor(g),x:d.length===1?400:65+685*(Number(g.anio)-primero)/Math.max(1,ultimo-primero),
            y:218-173*((this.valor(g) ?? l.min)-l.min)/(l.max-l.min)})).filter(p=>p.valor!==null);},
    get puntoActual(){return this.puntos.find(p=>String(p.anio)===this.seleccion);},
    get segmentos(){const grupos=[],d=this.datosVisibles;let grupo=[];for(const dato of d){const p=this.puntos.find(p=>Number(p.anio)===Number(dato.anio));
        if(!p){if(grupo.length)grupos.push(grupo);grupo=[];}else grupo.push(p);}if(grupo.length)grupos.push(grupo);
        return grupos.map(g=>{const linea=g.map((p,i)=>(i?'L':'M')+p.x+','+p.y).join(' ');return {linea,area:linea+' L'+g.at(-1).x+',218 L'+g[0].x+',218 Z'};});},
    get pareja(){return this.datos.filter(d=>this.gestionesElegidas.includes(String(d.anio)));},
    get parejaValida(){return this.pareja.length>=2;},
    claseSerie(indice){return 'pa-serie-'+(indice%7);},
    cambioDesdeBase(dato, clave){const a=this.valor(this.pareja[0],clave),b=this.valor(dato,clave);return a===null||b===null?'Sin datos comparables':(b-a>0?'+':'')+this.numero(b-a)+' respecto a '+this.pareja[0].anio;},
    coordenada(indice, radio){const angulo=-Math.PI/2+indice*2*Math.PI/this.ejes.length;return {x:300+radio*Math.cos(angulo),y:223+radio*Math.sin(angulo)};},
    poligono(radio){return this.ejes.map((_,i)=>{const p=this.coordenada(i,radio);return p.x+','+p.y;}).join(' ');},
    get ejesRadiales(){return this.ejes.map((clave,i)=>({clave,nombre:this.nombres[clave],max:Math.max(1,...this.datos.map(d=>this.valor(d,clave) ?? 0)),
        ...this.coordenada(i,190),fin:this.coordenada(i,143)}));},
    get radiales(){return this.pareja.map(d=>({anio:d.anio,completa:this.ejes.every(e=>this.valor(d,e)!==null),puntos:this.ejesRadiales.map((e,i)=>{const p=this.coordenada(i,143*(this.valor(d,e.clave) ?? 0)/e.max);return p.x+','+p.y;}).join(' ')}));},
    get resumen(){return Object.keys(this.nombres).map(clave=>{const a=this.valor(this.pareja[0],clave),b=this.valor(this.pareja[1],clave);return {clave,nombre:this.nombres[clave],a,b,cambio:a===null || b===null?null:b-a};});},
    alternarEje(clave){if(this.ejes.includes(clave)){if(this.ejes.length>3)this.ejes=this.ejes.filter(e=>e!==clave);}else this.ejes=[...this.ejes,clave];},
});

window.baseBorradoresCalendario = () => new Promise((resolve, reject) => {
    const solicitud=indexedDB.open('savp-borradores-calendario',2);
    solicitud.onupgradeneeded=()=>{for(const nombre of ['casos','plantillas'])if(!solicitud.result.objectStoreNames.contains(nombre))solicitud.result.createObjectStore(nombre);};
    solicitud.onsuccess=()=>resolve(solicitud.result);solicitud.onerror=()=>reject(solicitud.error);
});
window.feriadosAnuales = clave => ({
    clave, plantillas:[], decisiones:{}, mensaje:'',
    async init(){try{const db=await window.baseBorradoresCalendario();const registros=await new Promise((resolve,reject)=>{const q=db.transaction('plantillas').objectStore('plantillas').getAll();q.onsuccess=()=>resolve(q.result);q.onerror=()=>reject(q.error);});db.close();this.plantillas=registros.filter(r=>r.propietario===this.clave);for(const p of this.plantillas)this.decisiones[p.id]='PENDIENTE';}catch{this.mensaje='No pudimos consultar las propuestas guardadas en este navegador.';}},
    fechaPara(fecha){const propuesta=String(this.anio)+fecha.slice(4);const d=new Date(propuesta+'T12:00:00Z');return Number.isFinite(d.getTime())&&d.toISOString().slice(0,10)===propuesta?propuesta:'';},
    incorporar(){const propuestas=this.plantillas.filter(p=>this.decisiones[p.id]!=='OMITIR').map(p=>p.nombre+': '+(this.fechaPara(p.inicio)||'fecha por revisar')+' a '+(this.fechaPara(p.fin)||'fecha por revisar')+' ('+(this.decisiones[p.id]==='PROPONER'?'propuesta anual, confirmar disposición':'pendiente de edición')+')');if(!propuestas.length){this.mensaje='No se incorporarán propuestas anuales.';return;}const nota='Feriados por revisar: '+propuestas.join('; ')+'.';if((this.descripcion+' '+nota).trim().length>500){this.mensaje='La descripción admite 500 caracteres. Resume las propuestas o selecciona menos feriados.';return;}this.descripcion=(this.descripcion+' '+nota).trim();this.mensaje='La revisión de feriados se añadió a la descripción. Las fechas quedan pendientes de confirmación.';},
});
window.borradorCalendario = (clave, clavePlantillas) => ({
    clave, clavePlantillas, borrador: null, pdf: null, errorPdf: '', leyendoPdf: false, mensajeBorrador: '',
    async base() {
        return window.baseBorradoresCalendario();
    },
    async init() {
        try {
            const db = await this.base();
            const registro = await new Promise((resolve, reject) => {
                const q = db.transaction('casos').objectStore('casos').get(this.clave);
                q.onsuccess = () => resolve(q.result); q.onerror = () => reject(q.error);
            });
            db.close(); this.borrador = registro || null;
        } catch { this.mensajeBorrador = 'Este navegador no permite conservar borradores. Puedes descargar la propuesta.'; }
    },
    async elegirPdf(event) {
        this.errorPdf = ''; this.pdf = null;
        const archivo = event.target.files?.[0];
        if (!archivo) return;
        this.leyendoPdf = true;
        try {
            const cabecera = new TextDecoder().decode(await archivo.slice(0, 5).arrayBuffer());
            if (!/\.pdf$/i.test(archivo.name) || archivo.size > 10 * 1024 * 1024 || archivo.size < 5 || cabecera !== '%PDF-') {
                this.errorPdf = 'Elige un PDF válido de hasta 10 MB.'; event.target.value = ''; return;
            }
            this.pdf = {nombre: archivo.name, archivo, tamano: archivo.size};
        } catch { this.errorPdf = 'No pudimos leer el PDF. Selecciónalo nuevamente.'; }
        finally { this.leyendoPdf = false; }
    },
    async guardar(caso) {
        if (this.errorPdf || this.leyendoPdf) { this.mensajeBorrador = 'Revisa el PDF antes de guardar el borrador.'; return; }
        try {
            const registro = {caso: JSON.parse(JSON.stringify(caso)), fecha: new Date().toISOString(),
                pdf: this.pdf ? {nombre: this.pdf.nombre, archivo: this.pdf.archivo, tamano: this.pdf.tamano} : null};
            const db = await this.base();
            await new Promise((resolve, reject) => {
                const tx = db.transaction(['casos','plantillas'], 'readwrite'); tx.objectStore('casos').put(registro, this.clave);
                if(caso.tipo==='FERIADO' && caso.anual==='SI') {
                    const id=this.clavePlantillas+'|'+caso.inicio.slice(5)+'|'+caso.fin.slice(5)+'|'+caso.alcance+'|'+[...(caso.turnos||[]),...(caso.grupos||[])].sort().join(',');
                    tx.objectStore('plantillas').put({id,propietario:this.clavePlantillas,nombre:caso.documento||caso.motivo.slice(0,100),inicio:caso.inicio,fin:caso.fin,alcance:caso.alcance,fecha:registro.fecha,pdf:registro.pdf},id);
                }
                tx.oncomplete = resolve; tx.onerror = () => reject(tx.error); tx.onabort = () => reject(tx.error);
            });
            db.close(); this.borrador = registro;
            this.mensajeBorrador = this.pdf ? 'Tu borrador y su PDF se guardaron en este navegador. Puedes retomarlos después.' : 'Tu borrador se guardó en este navegador. Puedes retomarlo después.';
            this.$dispatch('toast', {type:'success', message:this.mensajeBorrador});
        } catch { this.mensajeBorrador = 'No pudimos guardar en este navegador. Tus campos siguen disponibles; descarga la propuesta.'; }
    },
    restaurar() {
        if (!this.borrador) return;
        this.pdf = this.borrador.pdf || null; this.errorPdf = '';
        this.$wire.restaurarBorrador(this.borrador.caso);
        this.mensajeBorrador = 'Borrador recuperado. Revisa nuevamente el impacto antes de decidir.';
    },
    descargarPdf() {
        if (!this.pdf?.archivo) return;
        const url = URL.createObjectURL(this.pdf.archivo), enlace = document.createElement('a');
        enlace.href = url; enlace.download = this.pdf.nombre; enlace.click();
        setTimeout(() => URL.revokeObjectURL(url), 1000);
    },
});
window.validarFranjaRecuperacion = (inicio,fin,minimo,maximo) => {
    if(!inicio || !fin) return '';
    const minutos=h=>Number(h.slice(0,2))*60+Number(h.slice(3,5));
    if(minimo && (inicio<minimo || fin>maximo)) return 'Usa el horario escolar: '+minimo+' a '+maximo+'.';
    if(minutos(fin)-minutos(inicio)<60) return 'La recuperación debe durar al menos una hora y terminar después de su inicio.';
    return '';
};

