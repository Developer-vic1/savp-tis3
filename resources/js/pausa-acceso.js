// Esta pausa ayuda a usar el formulario; la protección real se verifica en el servidor.
export function crearPausaAcceso(politica, guardado = {}, reloj = () => Date.now()) {
    const limpio = () => ({ fallos: 0, nivel: 0, hasta: 0, actualizado: 0 });
    let datos = limpio();
    if (guardado && ['fallos', 'nivel', 'hasta', 'actualizado'].every(key => Number.isFinite(guardado[key]) && guardado[key] >= 0)) {
        datos = {
            fallos: Math.min(Math.floor(guardado.fallos), politica.intentos - 1),
            nivel: Math.min(Math.floor(guardado.nivel), 20),
            hasta: Math.min(guardado.hasta, reloj() + politica.pausa_maxima * 1000),
            actualizado: Math.min(guardado.actualizado, reloj()),
        };
    }

    function estado() {
        if (datos.actualizado && reloj() - datos.actualizado >= politica.vigencia * 1000) datos = limpio();
        return { ...datos };
    }

    function restantes() {
        return Math.max(0, Math.ceil((estado().hasta - reloj()) / 1000));
    }

    function registrarFallo() {
        estado();
        if (restantes() > 0) return estado();
        datos.fallos++;
        datos.actualizado = reloj();
        if (datos.fallos >= politica.intentos) {
            datos.fallos = 0;
            datos.nivel++;
            const segundos = Math.min(politica.pausa_maxima, politica.pausa_inicial * 2 ** Math.min(datos.nivel - 1, 20));
            datos.hasta = reloj() + segundos * 1000;
        }
        return estado();
    }

    return { estado, restantes, registrarFallo, reiniciar: () => { datos = limpio(); } };
}
