window.gestionPersonasPage = (iniciales) => {
    const graficos = {};
    const opciones = [
        { id: 'directorio', nombre: 'Directorio', icono: 'ph-list-bullets' },
        { id: 'tabla', nombre: 'Tabla', icono: 'ph-table' },
        { id: 'galeria', nombre: 'Galería', icono: 'ph-squares-four' },
    ];
    return {
        ...window.paginacionInstitucional(),
        vista: 'directorio', mostrarIndicadores: true, opcionesVista: opciones, datos: iniciales, cargandoPagina: false,
        init() {
            try {
                const anterior = localStorage.getItem('gestion-personas-view');
                this.vista = ['directorio', 'tabla', 'galeria'].includes(anterior) ? anterior : anterior === 'fotos' ? 'galeria' : 'directorio';
            } catch { /* La vista funciona aunque el almacenamiento esté deshabilitado. */ }
            this.actualizarTema = () => this.dibujar('none');
            window.addEventListener('theme-changed', this.actualizarTema);
            this.$nextTick(() => this.dibujar());
            this.$watch('mostrarIndicadores', visible => { if (visible) this.$nextTick(() => this.dibujar()); });
        },
        cambiarVista(vista) {
            if (!opciones.some(opcion => opcion.id === vista) || vista === this.vista) return;
            this.vista = vista;
            try { localStorage.setItem('gestion-personas-view', vista); } catch { /* Preferencia opcional. */ }
            this.animarResultados();
        },
        actualizarIndicadores(datos) { this.datos = datos; this.$nextTick(() => this.dibujar()); },
        repetirAnimacion() { Object.values(graficos).forEach(grafico => grafico.reset()); this.dibujar(); },
        dibujar(modo) {
            if (!window.Chart || !this.$el.isConnected) return;
            const css = getComputedStyle(document.documentElement);
            const token = nombre => css.getPropertyValue(nombre).trim();
            const reducido = matchMedia('(prefers-reduced-motion: reduce)').matches;
            ['contacto', 'edades', 'generos'].forEach((clave, indice) => {
                const canvas = this.$el.querySelector(`#personas-grafico-${clave}`);
                if (!canvas) return;
                const datos = this.datos[clave];
                const color = token(['--ui-primary', '--ui-info', '--ui-violet'][indice]);
                if (graficos[clave]) {
                    graficos[clave].data.labels = [...datos.labels];
                    graficos[clave].data.datasets[0].data = [...datos.data];
                    graficos[clave].data.datasets[0].backgroundColor = color;
                    if (clave === 'contacto') graficos[clave].options.scales.x.max = Math.max(1, this.datos.total);
                    graficos[clave].options.animation = reducido ? false : { duration: 650, easing: 'easeOutQuart' };
                    graficos[clave].update(reducido ? 'none' : modo);
                    return;
                }
                graficos[clave] = new window.Chart(canvas, {
                    type: 'bar',
                    data: { labels: [...datos.labels], datasets: [{ label: 'Personas', data: [...datos.data], backgroundColor: color, borderRadius: 5, borderSkipped: false, maxBarThickness: 24 }] },
                    options: {
                        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                        animation: reducido ? false : { duration: 650, easing: 'easeOutQuart', delay: ctx => ctx.type === 'data' ? ctx.dataIndex * 70 : 0 },
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ...(clave === 'contacto' ? { max: Math.max(1, this.datos.total) } : {}), ticks: { precision: 0, font: { size: 11 } }, grid: { color: token('--ui-border') }, border: { display: false } }, y: { ticks: { font: { size: 11 } }, grid: { display: false }, border: { display: false } } },
                    },
                });
            });
        },
        avisar(type, message) { window.dispatchEvent(new CustomEvent('toast', { detail: { type, message } })); },
        destroy() { this.destruirPaginacion(); window.removeEventListener('theme-changed', this.actualizarTema); Object.values(graficos).forEach(grafico => grafico.destroy()); },
    };
};

window.formularioPersona = (modo) => ({
    errores: {}, tocado: {},
    init() { this.$nextTick(() => this.$el.querySelector('input:not([type="file"])')?.focus({ preventScroll: true })); },
    revisar(campo) {
        if (!campo?.name || !campo.validity) return;
        const validez = campo.validity;
        this.errores[campo.id] = validez.valid ? '' : validez.valueMissing ? 'Completa este dato.'
            : validez.rangeOverflow ? 'La fecha no puede estar en el futuro.'
            : validez.rangeUnderflow ? 'Revisa la fecha de nacimiento.'
            : validez.tooShort ? 'Ingresa al menos dos caracteres.'
            : validez.typeMismatch ? 'Ingresa un correo electrónico válido.'
            : validez.patternMismatch && ['nom_per','ape_pat_per','ape_mat_per'].includes(campo.name) ? 'Usa letras, espacios, apóstrofes o guiones. No se admiten números.'
            : validez.patternMismatch && campo.name === 'ci_per' ? 'El CI debe tener entre 4 y 12 números.'
            : validez.patternMismatch && campo.name === 'tel_per' ? 'Usa números, espacios, + o guiones.'
            : 'Revisa el formato de este dato.';
    },
    tocar(campo) { if (!campo?.name) return; this.tocado[campo.id] = true; this.revisar(campo); },
    async enviar() {
        const campos = [...this.$el.querySelectorAll('input[name], select[name], textarea[name]')];
        campos.forEach(campo => this.tocar(campo));
        const invalido = campos.find(campo => !campo.validity.valid);
        if (invalido) {
            invalido.focus();
            if (!matchMedia('(prefers-reduced-motion: reduce)').matches) invalido.animate([
                {transform:'translateX(0)'}, {transform:'translateX(-4px)'}, {transform:'translateX(4px)'}, {transform:'translateX(0)'},
            ], {duration:240});
            return;
        }
        await this.$wire[modo === 'editar' ? 'actualizarPersona' : 'guardarPersona']();
    },
});

