// La navegación HTTP conserva autorización, formularios e historial del navegador.
export function destinoDePagina(evento, enlace, ubicacion) {
    if (!enlace || evento.defaultPrevented || evento.button !== 0 || evento.ctrlKey || evento.metaKey || evento.shiftKey || evento.altKey) return null;
    if (enlace.hasAttribute('download') || (enlace.target && enlace.target !== '_self') || enlace.hasAttribute('wire:navigate')) return null;
    let destino;
    try { destino = new URL(enlace.href, ubicacion.href); } catch { return null; }
    if (destino.origin !== ubicacion.origin || destino.username || destino.password) return null;
    if (destino.pathname === ubicacion.pathname && destino.search === ubicacion.search) return null;
    if (/\/(logout|salir|pdf|zip|csv|sql)(\/|$)|\.(pdf|zip|csv|sql)(\/|$)/i.test(destino.pathname)) return null;
    if (enlace.closest('[download], [data-sin-transicion]') || /\/(descargar|referencias)(\/|$)/.test(destino.pathname)) return null;
    return destino;
}

let recuperacion;
function restaurar() {
    clearTimeout(recuperacion);
    document.documentElement.classList.remove('savp-navegando');
    document.querySelector('.savp-workspace-main')?.removeAttribute('aria-busy');
}

document.addEventListener('click', evento => {
    const pagina = document.querySelector('.savp-workspace-main');
    const destino = destinoDePagina(evento, evento.target.closest?.('a[href]'), location);
    if (!pagina || !destino) return;
    // Señal inmediata sin retrasar la solicitud ni crear un bloqueo de pantalla.
    document.documentElement.classList.add('savp-navegando');
    pagina.setAttribute('aria-busy', 'true');
    clearTimeout(recuperacion);
    recuperacion = setTimeout(restaurar, 12000);
});

window.addEventListener('pageshow', restaurar);
window.addEventListener('pagehide', restaurar);
document.addEventListener('livewire:navigated', restaurar);
