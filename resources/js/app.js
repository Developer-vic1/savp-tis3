import './bootstrap';

import Swal from 'sweetalert2';
import Chart from 'chart.js/auto';

window.Swal = Swal;
window.Chart = Chart;

/*
|--------------------------------------------------------------------------
| THEME MANAGER
|--------------------------------------------------------------------------
| Maneja modo claro/oscuro sin depender de Alpine.
| Guarda preferencia en localStorage.
|--------------------------------------------------------------------------
*/

window.themeManager = {
    storageKey: 'savp-theme',

    getSystemPreference() {
        return window.matchMedia &&
            window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';
    },

    getSavedTheme() {
        return localStorage.getItem(this.storageKey);
    },

    getCurrentTheme() {
        if (document.documentElement.classList.contains('dark')) {
            return 'dark';
        }

        return 'light';
    },

    apply(theme) {
        const selectedTheme = theme || this.getSavedTheme() || 'light';

        if (selectedTheme === 'dark') {
            document.documentElement.classList.add('dark');
            document.documentElement.dataset.theme = 'dark';
        } else {
            document.documentElement.classList.remove('dark');
            document.documentElement.dataset.theme = 'light';
        }

        localStorage.setItem(this.storageKey, selectedTheme);

        window.dispatchEvent(new CustomEvent('theme-changed', {
            detail: {
                theme: selectedTheme,
            },
        }));
    },

    toggle() {
        const currentTheme = this.getCurrentTheme();
        const nextTheme = currentTheme === 'dark' ? 'light' : 'dark';

        this.apply(nextTheme);

        return nextTheme;
    },

    init() {
        const savedTheme = this.getSavedTheme();

        if (savedTheme) {
            this.apply(savedTheme);
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Por ahora dejamos claro como modo inicial.
        | Si luego quieres seguir el sistema operativo:
        | this.apply(this.getSystemPreference());
        |--------------------------------------------------------------------------
        */
        this.apply('light');
    },
};

window.themeManager.init();

/*
|--------------------------------------------------------------------------
| LIVEWIRE ACTION LOCK
|--------------------------------------------------------------------------
| Evita doble click / doble ejecución en acciones Livewire + Alpine.
|--------------------------------------------------------------------------
*/

window.livewireActionLock = {
    locked: false,

    run(callback, delay = 700) {
        if (this.locked) {
            return;
        }

        this.locked = true;

        try {
            callback();
        } finally {
            setTimeout(() => {
                this.locked = false;
            }, delay);
        }
    },
};

/*
|--------------------------------------------------------------------------
| HELPERS UI
|--------------------------------------------------------------------------
*/

window.uiHelpers = {
    confirm({
        title = '¿Confirmar acción?',
        text = 'Esta acción modificará la información del sistema.',
        icon = 'warning',
        confirmButtonText = 'Sí, confirmar',
        cancelButtonText = 'Cancelar',
        confirmButtonColor = '#059669',
        cancelButtonColor = '#64748b',
        onConfirm = null,
    }) {
        Swal.fire({
            title,
            text,
            icon,
            showCancelButton: true,
            confirmButtonText,
            cancelButtonText,
            confirmButtonColor,
            cancelButtonColor,
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed && typeof onConfirm === 'function') {
                onConfirm();
            }
        });
    },

    toast({
        icon = 'success',
        title = 'Acción realizada',
        timer = 2200,
    }) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon,
            title,
            showConfirmButton: false,
            timer,
            timerProgressBar: true,
        });
    },
};