const origenSeguro = valor => {
    if (typeof valor !== 'string' || !valor.trim()) return null;
    try { const url = new URL(valor, location.origin); return url.origin === location.origin && !url.username && !url.password && url.pathname !== location.pathname && !/\/(logout|salir)(\/|$)/.test(url.pathname) ? url.href : null; } catch { return null; }
};
window.errorInstitucional = datos => ({
    regreso: datos.volver, textoSoporte: '', enlaceSoporte: 'https://wa.me/59175836807',
    init() {
        try { this.regreso = origenSeguro(sessionStorage.getItem('savp-ultima-pagina')) || origenSeguro(document.referrer) || datos.volver; } catch {}
        const hora = new Date().getHours();
        const saludo = hora >= 5 && hora < 12 ? 'Buenos días' : hora >= 12 && hora < 19 ? 'Buenas tardes' : 'Buenas noches';
        this.textoSoporte = `${saludo}, equipo de soporte SAVP. Soy ${datos.nombre}, con el rol de ${datos.rol}. Intenté abrir ${datos.pagina} y apareció el error ${datos.estado}: ${datos.tipo}. ¿Podrían ayudarme a continuar? Gracias.`;
        this.enlaceSoporte = 'https://wa.me/59175836807?text=' + encodeURIComponent(this.textoSoporte);
    },
});
// Solo se recuerda una página que cargó correctamente; las páginas de error no sustituyen el regreso.
const recordarPagina = () => { if (!document.body?.dataset.errorInstitucional) { try { sessionStorage.setItem('savp-ultima-pagina', location.origin + location.pathname); } catch {} } };
document.addEventListener('DOMContentLoaded', recordarPagina);
document.addEventListener('livewire:navigated', recordarPagina);

document.addEventListener('DOMContentLoaded',()=>{
    const contenido=document.getElementById('error-datos');if(!contenido)return;
    try {
        const pagina=window.errorInstitucional(JSON.parse(contenido.textContent));pagina.init();
        document.getElementById('error-regreso').href=pagina.regreso;
        const enlace=document.getElementById('error-whatsapp');
        const mensaje=document.getElementById('error-mensaje-soporte');
        const ayuda=document.getElementById('error-mensaje-ayuda');
        mensaje.value=pagina.textoSoporte;
        const actualizar=()=>{const vacio=!mensaje.value.trim();enlace.href='https://wa.me/59175836807?text='+encodeURIComponent(mensaje.value.trim());enlace.setAttribute('aria-disabled',String(vacio));ayuda.hidden=!vacio;};
        mensaje.addEventListener('input',actualizar);
        enlace.addEventListener('click',evento=>{if(!mensaje.value.trim()){evento.preventDefault();mensaje.focus();}});
        actualizar();
    } catch {}
});
