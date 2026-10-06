window.estudiantesInstitucionalPage = (datos,vista) => {
    let observador;
    return {
    ...window.paginacionInstitucional(), datos, vista, indicadores:true, masFiltros:false, explorador:false, cargandoGrupo:false, compacto:false,
    get otrosRegistros(){return Math.max(0,this.datos.total-this.datos.inscritos-this.datos.sin_inscripcion);},
    get porcentajeInscritos(){return this.datos.total?100*this.datos.inscritos/this.datos.total:0;},
    get porcentajeConRegistro(){return this.datos.total?100*(this.datos.total-this.datos.sin_inscripcion)/this.datos.total:0;},
    get especialidadesConDatos(){return this.datos.especialidades.filter(e=>e.total>0).sort((a,b)=>b.total-a.total);},
    get mosaico(){
        const resultado=[];
        const dividir=(grupos,x,y,ancho,alto)=>{
            if(!grupos.length)return;
            if(grupos.length===1){resultado.push({...grupos[0],estilo:`left:${x}%;top:${y}%;width:${ancho}%;height:${alto}%`});return;}
            const total=grupos.reduce((s,g)=>s+g.total,0);let corte=1,suma=grupos[0].total;
            while(corte<grupos.length-1 && Math.abs(suma+grupos[corte].total-total/2)<Math.abs(suma-total/2)){suma+=grupos[corte++].total;}
            const proporcion=suma/total;
            if(ancho*2>alto){dividir(grupos.slice(0,corte),x,y,ancho*proporcion,alto);dividir(grupos.slice(corte),x+ancho*proporcion,y,ancho*(1-proporcion),alto);}
            else{dividir(grupos.slice(0,corte),x,y,ancho,alto*proporcion);dividir(grupos.slice(corte),x,y+alto*proporcion,ancho,alto*(1-proporcion));}
        };
        dividir(this.especialidadesConDatos,0,0,100,100);return resultado;
    },
    get maximoGrupo(){return Math.max(1,...this.datos.cursos.flatMap(c=>c.paralelos.map(p=>p.total)));},
    tamanoBurbuja(total){return total?1.4+1.5*Math.sqrt(total/this.maximoGrupo):1.4;},
    async seleccionarGrupo(metodo,...argumentos){
        if(this.cargandoGrupo || !['seleccionarCursoCarpeta','filtrarCursoParalelo','seleccionarEspecialidadCarpeta','set'].includes(metodo))return;
        this.cargandoGrupo=true;
        try{
            await this.$wire[metodo](...argumentos);
            this.animarResultados(true);
            this.$nextTick(()=>this.$refs.tituloResultados?.focus({preventScroll:true}));
        }catch{
            this.avisar('error','No pudimos abrir el grupo. Vuelve a intentarlo.');
        }finally{this.cargandoGrupo=false;}
    },
    init(){
        this.$watch('vista',()=>this.animarResultados());
        this.compacto=this.$el.clientWidth<=672;
        observador=new ResizeObserver(([entrada])=>{this.compacto=entrada.contentRect.width<=672;});
        observador.observe(this.$el);
    },
    destroy(){observador?.disconnect();this.destruirPaginacion();},
    };
};
