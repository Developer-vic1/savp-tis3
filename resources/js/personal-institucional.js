window.personalInstitucionalPage = datos => ({
    ...window.paginacionInstitucional(), datos, indicadores:true, masFiltros:false, vista:'directorio',tipoGrafico:'materias',verTodas:false,detalleCarga:null,
    get horasTotales(){return this.datos.horas_materias+this.datos.horas_especialidades;},
    get porcentajeMateria(){return this.horasTotales?this.datos.horas_materias/this.horasTotales*100:0;},
    get cargasGrafico(){return this.verTodas?this.datos[this.tipoGrafico]:this.datos[this.tipoGrafico].slice(0,6);},
    get maximoGrafico(){return Math.max(1,...this.datos[this.tipoGrafico].map(item=>item.horas));},
    init(){try{const guardada=localStorage.getItem('personal-institucional-vista');this.vista=['directorio','tabla','lista','cargas'].includes(guardada)?guardada:'directorio';}catch{}this.$watch('vista',vista=>{try{localStorage.setItem('personal-institucional-vista',vista);}catch{}this.animarResultados();});this.$watch('datos',()=>this.detalleCarga=null);},
});
window.formCargaInstitucional = (tipo,materia,especialidad,curso,paralelo,horas,estado,disponibles) => ({
    tipo,materia,especialidad,curso,paralelo,horas,estado,disponibles,tocado:false,
    get errorHoras(){if(this.horas==='')return 'Indica las horas de la asignación.';return Number.isInteger(Number(this.horas))&&Number(this.horas)>=1&&Number(this.horas)<=this.disponibles?'':`Introduce un número entero entre 1 y ${this.disponibles} horas.`;},
    get valido(){return !!(this.curso&&this.paralelo&&(this.tipo==='MATERIA'?this.materia:this.especialidad)&&!this.errorHoras&&this.estado);},
    get faltantes(){const campos=[];if(!(this.tipo==='MATERIA'?this.materia:this.especialidad))campos.push(this.tipo==='MATERIA'?'materia':'especialidad');if(!this.curso)campos.push('curso');if(!this.paralelo)campos.push('paralelo');if(this.errorHoras)campos.push('horas válidas');return campos.join(', ');},
});
