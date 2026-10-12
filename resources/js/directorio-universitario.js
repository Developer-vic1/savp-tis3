const ENTEROS = (value, fallback) => Number.isInteger(Number(value)) && Number(value) >= 0 ? Number(value) : fallback;
const NORMALIZAR = value => String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
const CAMPOS = ['buscar', 'departamento', 'regimen', 'duracion', 'economia', 'orden'];
const ETIQUETAS = { buscar: 'Búsqueda', departamento: 'Departamento', regimen: 'Régimen', duracion: 'Duración', economia: 'Economía' };

export default function directorioUniversitario(config = {}) {
    const iniciales = config.filtros ?? {};
    return {
        instituciones: Array.isArray(config.instituciones) ? config.instituciones : Array.from({ length: ENTEROS(config.total, 0) }, (_, indice) => ({ indice, nombre: '', sigla: '', departamentos: [], regimen: '', programas: [] })),
        regimenes: config.regimenes ?? {},
        buscar: iniciales.buscar ?? '',
        departamento: iniciales.departamento ?? '',
        regimen: iniciales.regimen ?? '',
        duracion: iniciales.duracion ?? '',
        economia: iniciales.economia ?? '',
        orden: iniciales.orden ?? 'nombre',
        porPagina: 12,
        pagina: 1,
        filtrosAmpliados: !!config.filtrosAmpliados,
        init() {
            for (const campo of CAMPOS) this.$watch(campo, () => this.cambioFiltros());
        },
        get resultados() {
            const busqueda = NORMALIZAR(this.buscar);
            const visibles = this.instituciones.filter(item => {
                if (this.departamento && !item.departamentos.includes(this.departamento)) return false;
                if (this.regimen && item.regimen !== this.regimen) return false;
                const programas = item.programas ?? [];
                const compatibles = programas.filter(programa => this.coincidePrograma(programa));
                if ((this.duracion || this.economia === 'verificados' || (this.economia === 'pendientes' && programas.length)) && !compatibles.length) return false;
                if (!busqueda) return true;
                return NORMALIZAR([item.nombre, item.sigla, ...(item.departamentos ?? []), ...programas.map(programa => programa.nombre), ...(item.ofertaPublicada ?? [])].join(' ')).includes(busqueda);
            });
            if (this.orden === 'departamento') visibles.sort((a, b) => NORMALIZAR([...(a.departamentos ?? []), a.nombre].join(' ')).localeCompare(NORMALIZAR([...(b.departamentos ?? []), b.nombre].join(' ')), 'es', { numeric: true }));
            return visibles;
        },
        coincidePrograma(programa) {
            return (!this.duracion || programa.duracion === this.duracion)
                && (this.economia !== 'verificados' || programa.costosVerificados === true)
                && (this.economia !== 'pendientes' || programa.costosVerificados !== true);
        },
        programasVisibles(indice) {
            return (this.instituciones[indice]?.programas ?? []).filter(programa => this.coincidePrograma(programa)).length;
        },
        programaVisible(indice, programa) {
            return this.coincidePrograma(this.instituciones[indice]?.programas?.[programa] ?? {});
        },
        ofertaVisible(indice, nombre) {
            const busqueda = NORMALIZAR(this.buscar);
            if (!busqueda) return true;
            const institucion = this.instituciones[indice] ?? {};
            const contexto = [institucion.nombre, institucion.sigla, ...(institucion.departamentos ?? []), ...(institucion.programas ?? []).map(programa => programa.nombre)];
            return NORMALIZAR(contexto.join(' ')).includes(busqueda) || NORMALIZAR(nombre).includes(busqueda);
        },
        get total() { return this.resultados.length; },
        get paginas() { return Math.max(1, Math.ceil(this.total / this.porPagina)); },
        get inicio() { return this.total ? (this.pagina - 1) * this.porPagina + 1 : 0; },
        get fin() { return Math.min(this.pagina * this.porPagina, this.total); },
        get resumen() { return `${this.total} ${this.total === 1 ? 'institución' : 'instituciones'} en esta consulta`; },
        get filtrosActivos() {
            return CAMPOS.filter(campo => campo !== 'orden' && String(this[campo] ?? '').trim() !== '').map(campo => ({
                campo,
                etiqueta: ETIQUETAS[campo],
                valor: campo === 'regimen' ? (this.regimenes[this.regimen] ?? this.regimen) : campo === 'economia' ? (this.economia === 'verificados' ? 'Montos verificados' : 'Costos pendientes') : this[campo],
            }));
        },
        posicion(indice) { return this.resultados.findIndex(item => item.indice === indice); },
        paginaVisible(indice) {
            if (!Number.isInteger(indice)) return false;
            const posicion = this.posicion(indice);
            return posicion >= (this.pagina - 1) * this.porPagina && posicion < this.pagina * this.porPagina;
        },
        cambioFiltros() {
            this.pagina = 1;
            if (typeof window === 'undefined' || !window.history?.replaceState) return;
            const url = new URL(window.location.href);
            for (const campo of CAMPOS) {
                const valor = String(this[campo] ?? '').trim();
                if (valor && !(campo === 'orden' && valor === 'nombre')) url.searchParams.set(campo, valor);
                else url.searchParams.delete(campo);
            }
            window.history.replaceState(window.history.state, '', url);
        },
        quitarFiltro(campo) { if (ETIQUETAS[campo]) this[campo] = ''; },
        limpiarFiltros() {
            for (const campo of CAMPOS) this[campo] = campo === 'orden' ? 'nombre' : '';
        },
        seleccionarDepartamento(nombre) {
            this.departamento = this.departamento === nombre ? '' : nombre;
            this.$nextTick?.(() => this.$refs?.resultados?.scrollIntoView({ behavior: 'auto', block: 'start' }));
        },
        cambiarPagina(value) {
            if (!Number.isInteger(Number(value))) return;
            const siguiente = Math.min(this.paginas, Math.max(1, Number(value)));
            if (siguiente === this.pagina) return;
            this.pagina = siguiente;
            this.$nextTick?.(() => {
                const heading = this.$refs?.resultados;
                if (!heading) return;
                heading.focus({ preventScroll: true });
                heading.scrollIntoView({ behavior: 'auto', block: 'start' });
            });
        },
    };
}
