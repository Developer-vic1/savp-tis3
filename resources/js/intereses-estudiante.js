/** Progreso del cuestionario; no calcula ni anticipa perfiles vocacionales. */
export function respuestasValidas(ids, respuestas) {
    return ids.filter(id => Number.isInteger(Number(respuestas[id])) && Number(respuestas[id]) >= 1 && Number(respuestas[id]) <= 5);
}

function fotoRespuestas(ids, respuestas) {
    return JSON.stringify(Object.fromEntries(respuestasValidas(ids, respuestas).map(id => [id, Number(respuestas[id])])));
}

export function crearCuestionarioIntereses(datos) {
    return {
        ids: datos.ids, respuestas: { ...datos.respuestas }, bloque: 0, tamanio: 5,
        enviando: false, guardandoBorrador: false, aviso: null, errores: {}, revisando: false,
        urlBorrador: datos.urlBorrador, tieneBorrador: !!datos.borradorGuardado,
        guardadoEn: datos.guardadoEn || null, borradorBase: '', permitirSalida: false, avisoSalida: null,
        init() {
            this.$el.noValidate = true;
            const pendiente = this.ids.findIndex(id => !respuestasValidas([id], this.respuestas).length);
            this.bloque = pendiente >= 0 ? Math.floor(pendiente / this.tamanio) : 0;
            this.borradorBase = fotoRespuestas(this.ids, datos.respuestasGuardadas || this.respuestas);
            this.avisoSalida = evento => {
                if (this.cambiosPendientes && !this.permitirSalida) {
                    evento.preventDefault();
                    evento.returnValue = '';
                }
            };
            if (typeof window !== 'undefined') window.addEventListener?.('beforeunload', this.avisoSalida);
        },
        destroy() {
            if (typeof window !== 'undefined') window.removeEventListener?.('beforeunload', this.avisoSalida);
        },
        get cambiosPendientes() { return fotoRespuestas(this.ids, this.respuestas) !== this.borradorBase; },
        get fechaGuardado() {
            const fecha = new Date(this.guardadoEn);
            return this.guardadoEn && !Number.isNaN(fecha.getTime()) ? fecha.toLocaleString('es-BO', { dateStyle: 'short', timeStyle: 'short' }) : '';
        },
        get estadoBorrador() {
            if (this.guardandoBorrador) return 'Guardando tu borrador en tu cuenta…';
            if (this.cambiosPendientes) return 'Tienes respuestas nuevas sin guardar. Guarda el borrador antes de salir.';
            if (this.tieneBorrador) return `Borrador guardado${this.fechaGuardado ? ' el ' + this.fechaGuardado : ''}. Puedes retomarlo otro día.`;
            if (!this.contestadas) return 'Responde al menos una actividad para guardar tu primer borrador.';
            return 'Puedes guardar lo que avances y continuar otro día.';
        },
        get contestadas() { return respuestasValidas(this.ids, this.respuestas).length; },
        get totalBloques() { return Math.ceil(this.ids.length / this.tamanio); },
        get porcentaje() { return Math.round(this.contestadas / this.ids.length * 100); },
        get completa() { return this.contestadas === this.ids.length; },
        visible(indice) { return this.revisando || Math.floor(indice / this.tamanio) === this.bloque; },
        respondidasBloque(bloque) { return respuestasValidas(this.ids.slice(bloque * this.tamanio, (bloque + 1) * this.tamanio), this.respuestas).length; },
        ir(bloque) {
            this.revisando = false;
            this.bloque = Math.max(0, Math.min(this.totalBloques - 1, bloque));
            this.$nextTick(() => {
                this.$refs.tituloBloque?.focus({ preventScroll: true });
                this.$refs.tituloBloque?.scrollIntoView({ block: 'start', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
            });
        },
        revisarPendientes() {
            const indice = this.ids.findIndex(id => !respuestasValidas([id], this.respuestas).length);
            if (indice >= 0) {
                this.ir(Math.floor(indice / this.tamanio));
                this.$nextTick(() => this.$el.querySelector(`[name="responses[${this.ids[indice]}]"]`)?.focus({ preventScroll: true }));
            }
        },
        async guardar(evento) {
            if (this.enviando) return;
            this.errores = {};
            if (!this.completa) {
                this.aviso = { title: 'Faltan actividades por responder', message: `Has respondido ${this.contestadas} de ${this.ids.length}. Elige una opción para cada actividad.` };
                this.revisarPendientes();
                return;
            }
            await enviarOrientacion(this, evento.currentTarget);
        },
        async guardarBorrador() {
            if (this.enviando || !this.contestadas || !this.urlBorrador) return;
            this.guardandoBorrador = true;
            await enviarOrientacion(this, this.$root || this.$el.closest?.('form') || this.$el, { borrador: true, destino: this.urlBorrador });
            this.guardandoBorrador = false;
        },
    };
}

export function crearEstudioIntereses() {
    return {
        enviando: false, aviso: null, errores: {},
        async analizar(evento) {
            if (this.enviando) return;
            await enviarOrientacion(this, evento.currentTarget);
        },
    };
}

async function enviarOrientacion(estado, formulario, opciones = {}) {
    const respuestasEnviadas = estado.ids ? fotoRespuestas(estado.ids, estado.respuestas) : null;
    estado.enviando = true;
    estado.aviso = null;
    estado.errores = {};
    try {
        const respuesta = await fetch(opciones.destino || formulario.action, {
            method: 'POST', body: new FormData(formulario), credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const tipo = respuesta.headers.get('content-type') || '';
        const contenido = tipo.includes('application/json') ? await respuesta.json() : {};
        if (respuesta.ok && opciones.borrador && contenido.draftSaved === true) {
            if (fotoRespuestas(estado.ids, contenido.responses || {}) !== respuestasEnviadas || contenido.answered !== Object.keys(JSON.parse(respuestasEnviadas)).length) throw new Error('Borrador sin confirmación de las respuestas');
            estado.borradorBase = respuestasEnviadas;
            estado.tieneBorrador = true;
            estado.guardadoEn = contenido.savedAt;
            return;
        }
        if (respuesta.ok && contenido.redirect) {
            const destino = new URL(contenido.redirect, window.location.origin);
            if (destino.origin !== window.location.origin) throw new Error('Destino no válido');
            estado.permitirSalida = true;
            const actual = new URL(window.location.href || window.location.origin);
            window.location.assign(destino.href);
            // Un cambio de ancla no vuelve a consultar el estudio recién guardado.
            if (destino.pathname === actual.pathname && destino.search === actual.search) window.location.reload();
            return;
        }
        estado.errores = contenido.errors || {};
        estado.aviso = contenido.notice || {
            title: respuesta.status === 419 || respuesta.status === 401 ? 'Tu sesión necesita actualizarse' : opciones.borrador ? 'No pudimos confirmar el borrador' : 'No pudimos completar el estudio',
            message: respuesta.status === 419 || respuesta.status === 401
                ? 'Abre esta página en otra pestaña e inicia sesión. Mantén esta pestaña abierta para conservar tus respuestas.'
                : Object.values(estado.errores).flat().join(' ') || 'No recibimos confirmación del resultado. Recarga para comprobar si se guardó antes de volver a enviarlo.',
            preserved: 'Las respuestas de este formulario permanecen en pantalla.',
        };
        const itemConError = Object.keys(estado.errores).find(clave => /^responses\.\d+$/.test(clave));
        if (itemConError && estado.ids) {
            const indice = estado.ids.indexOf(Number(itemConError.split('.')[1]));
            if (indice >= 0) estado.ir(Math.floor(indice / estado.tamanio));
        }
    } catch {
        estado.aviso = {
            title: opciones.borrador ? 'No pudimos confirmar el borrador' : 'Se interrumpió la conexión',
            message: 'No recibimos una confirmación válida. Conserva esta pestaña y revisa en otra si se guardó antes de volver a enviarlo.',
            preserved: 'Tus respuestas permanecen en pantalla mientras esta pestaña siga abierta.',
        };
    } finally {
        estado.enviando = false;
        if (estado.aviso) estado.$nextTick?.(() => {
            estado.$refs?.aviso?.focus({ preventScroll: true });
            estado.$refs?.aviso?.scrollIntoView({ block: 'center', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
        });
    }
}

if (typeof window !== 'undefined') {
    window.crearCuestionarioIntereses = crearCuestionarioIntereses;
    window.crearEstudioIntereses = crearEstudioIntereses;
}
