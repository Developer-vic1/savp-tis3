window.docentesInstitucionalPage = vista => ({
    ...window.paginacionInstitucional(), vista, indicadores:false, masFiltros:false, grafico:"mosaico",
    init(){ this.$watch('vista',()=>this.animarResultados()); },
    destroy(){ this.destruirPaginacion(); },
});
