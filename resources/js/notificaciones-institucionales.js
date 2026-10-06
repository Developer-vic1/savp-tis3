window.vigenciaAviso = (datos) => ({
    etiqueta: '', restante: '', porcentaje: 0, intervalo: null,
    desfase: Date.parse(datos.reloj) - Date.now(),
    init() {
        this.actualizar();
        this.$watch('open', abierta => abierta ? this.arrancar() : this.detener());
        if (this.open) this.arrancar();
    },
    arrancar() {
        this.detener(); this.actualizar();
        if (this.etiqueta !== 'Finalizado' && this.etiqueta !== 'Revocado') {
            this.intervalo = setInterval(() => this.actualizar(), 1000);
        }
    },
    detener() { if (this.intervalo !== null) clearInterval(this.intervalo); this.intervalo = null; },
    destroy() { this.detener(); },
    actualizar() {
        const inicio = Date.parse(datos.inicio), fin = Date.parse(datos.fin), ahora = Date.now() + this.desfase;
        this.restante = ''; this.porcentaje = 0;
        if (datos.estado === 'REVOCADA') { this.etiqueta = 'Revocado'; this.detener(); return; }
        if (![inicio, fin, ahora].every(Number.isFinite) || fin <= inicio) { this.etiqueta = 'Vigencia no disponible'; this.detener(); return; }
        if (ahora >= fin) { this.etiqueta = 'Finalizado'; this.detener(); return; }
        this.etiqueta = ahora < inicio ? 'Empieza en' : 'Tiempo restante';
        const segundos = Math.ceil(((ahora < inicio ? inicio : fin) - ahora) / 1000);
        const dias = Math.floor(segundos / 86400), horas = Math.floor(segundos % 86400 / 3600);
        const minutos = Math.floor(segundos % 3600 / 60), resto = segundos % 60;
        this.restante = [dias ? `${dias} d` : '', horas ? `${horas} h` : '', minutos ? `${minutos} min` : '', `${resto} s`].filter(Boolean).join(' ');
        this.porcentaje = ahora < inicio ? 0 : Math.max(0, Math.min(100, (fin - ahora) / (fin - inicio) * 100));
    },
});
