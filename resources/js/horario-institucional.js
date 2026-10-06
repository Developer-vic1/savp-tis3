window.horarioInstitucional = (eventos,bloques=[],editable=false) => ({
    eventos,bloques,editable,comprimir:!editable,plantilla:eventos.find(clase=>clase.aplicada)?.plantilla_id||eventos[0]?.plantilla_id||'',turno:'',vista:matchMedia('(max-width:700px)').matches?'dia':'semana',diaActivo:'Lunes',seleccionada:null,
    get filtrados(){return this.eventos.filter(clase=>clase.plantilla_id===this.plantilla&&(!this.turno||clase.turno===this.turno));},
    get visibles(){
        const ordenadas=[...this.filtrados].sort((a,b)=>a.dia.localeCompare(b.dia)||a.inicio.localeCompare(b.inicio)||a.fin.localeCompare(b.fin));
        const grupos=[];
        for(const clase of ordenadas){
            const anterior=grupos.at(-1);
            const mismo=anterior&&anterior.dia===clase.dia&&anterior.asignacion_id===clase.asignacion_id&&anterior.tipo===clase.tipo&&anterior.turno===clase.turno&&anterior.curso===clase.curso&&anterior.paralelo===clase.paralelo&&anterior.nombre===clase.nombre&&anterior.aula===clase.aula;
            const recreo=(clase.recreos||[]).find(pausa=>pausa.inicio===anterior?.fin&&pausa.fin===clase.inicio);
            const consecutiva=anterior?.fin===clase.inicio;
            if(this.comprimir&&mismo&&!anterior.coincide&&!clase.coincide&&(consecutiva||recreo)){
                anterior.fin=clase.fin;
                anterior.bloques.push({inicio:clase.inicio,fin:clase.fin});
                if(recreo)anterior.pausas.push(recreo);
            }else grupos.push({...clase,bloques:[{inicio:clase.inicio,fin:clase.fin}],pausas:[]});
        }
        return grupos;
    },
    elegirDiaDisponible(){if(!this.porDia(this.diaActivo).length)this.diaActivo=this.dias.find(dia=>this.porDia(dia).length)||this.dias[0];},
    get periodo(){return this.eventos.find(clase=>clase.plantilla_id===this.plantilla)?.periodo||'';},
    get dias(){return ['Lunes','Martes','Miércoles','Jueves','Viernes',...['Sábado','Domingo'].filter(dia=>this.filtrados.some(clase=>clase.dia===dia)),...new Set(this.filtrados.map(clase=>clase.dia).filter(dia=>!['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'].includes(dia)))];},
    get filas(){const filas=new Map();this.filtrados.forEach(clase=>{const clave=clase.inicio+'|'+clase.fin;filas.set(clave,{clave,inicio:clase.inicio||'Sin hora',fin:clase.fin});});if(this.editable)this.bloques.forEach(b=>filas.set(b.inicio+'|'+b.fin,{...b,clave:b.inicio+'|'+b.fin}));return [...filas.values()].sort((a,b)=>a.inicio.localeCompare(b.inicio));},
    abrirCelda(fila,dia){if(!this.editable||!fila.codigo)return;this.$dispatch('elegir-bloque-clase',{bloque:fila.codigo,dia:({Lunes:'LUNES',Martes:'MARTES','Miércoles':'MIERCOLES',Jueves:'JUEVES',Viernes:'VIERNES'})[dia]});},
    celda(fila,dia){return this.filtrados.filter(clase=>clase.dia===dia&&(clase.inicio||'Sin hora')===fila.inicio&&clase.fin===fila.fin);},
    porDia(dia){return this.visibles.filter(clase=>clase.dia===dia).sort((a,b)=>a.inicio.localeCompare(b.inicio));},
    init(){this.elegirDiaDisponible();try{if(!this.editable)this.comprimir=localStorage.getItem('horario-institucional-comprimir')!=='false';}catch{}this.$watch('comprimir',valor=>{this.seleccionada=null;try{if(!this.editable)localStorage.setItem('horario-institucional-comprimir',String(valor));}catch{}});this.$watch('plantilla',()=>{this.seleccionada=null;this.turno='';this.elegirDiaDisponible();});this.$watch('turno',()=>{this.seleccionada=null;this.elegirDiaDisponible();});},
});
