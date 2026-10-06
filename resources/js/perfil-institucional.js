window.perfilInstitucional = () => ({
    activo: 'seccion-datos',
    animation: null,
    cambiarCategoria(id, enfocarContenido = false) {
        const section = document.getElementById(id);
        if (!section) return;
        if (this.activo === id) return;
        this.animation?.cancel();
        this.activo = id;
        this.$nextTick(() => {
            if (enfocarContenido) section.querySelector('h2')?.focus({ preventScroll: true });
            if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                this.animation = section.animate([
                    { opacity: .65, transform: 'translateY(8px)' },
                    { opacity: 1, transform: 'translateY(0)' },
                ], { duration: 280, easing: 'cubic-bezier(.2,.8,.2,1)' });
            }
        });
    },
    navegarTeclado(event) {
        if (!['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return;
        const tabs = [...event.currentTarget.querySelectorAll('[role="tab"]')];
        const index = tabs.indexOf(event.target);
        if (index < 0) return;
        event.preventDefault();
        const next = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1
            : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
        tabs[next].click();
        tabs[next].focus({ preventScroll: true });
    },
    abrirEdicionPerfil() { this.cambiarCategoria('seccion-editar-perfil', true); },
    abrirPassword() { this.cambiarCategoria('seccion-password', true); },
    destroy() { this.animation?.cancel(); },
});
