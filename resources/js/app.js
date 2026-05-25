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

window.documentoAutocomplete = {
    component() {
        return {
            getSuggestion(el) {
                return (el?.dataset?.suggestion || '').trim();
            },
            syncValue(el, nextValue) {
                el.value = nextValue;
                el.dispatchEvent(new Event('input', { bubbles: true }));
            },
            clearSuggestion(el) {
                if (! el) {
                    return;
                }
                const idx = Number(el.dataset.docIndex || -1);
                el.dataset.suggestion = '';
                // Delay Livewire call to avoid re-render collision while the model
                // debounce is still in flight after syncValue.
                setTimeout(() => {
                    const root = el.closest('[wire\\:id]');
                    const wire = root && window.Livewire ? window.Livewire.find(root.getAttribute('wire:id')) : null;
                    if (wire && Number.isInteger(idx) && idx >= 0 && typeof wire.call === 'function') {
                        wire.call('limpiarSugerenciaObservacion', idx);
                    }
                }, 80);
            },
            acceptNextWord(event) {
                const el = event.target;
                const suggestion = this.getSuggestion(el);
                if (! suggestion) {
                    return;
                }
                event.preventDefault();

                const words = suggestion.trim().split(/\s+/).filter(w => w.length > 0);
                const currentTrimmed = (el.value || '').trim();

                // Find how many words of the suggestion are fully present in el.value.
                // This allows word-by-word reconstruction starting from word 1 of the
                // suggestion, not by appending to whatever the user typed.
                let matched = 0;
                for (let i = 1; i <= words.length; i++) {
                    if (words.slice(0, i).join(' ').toLowerCase() === currentTrimmed.toLowerCase()) {
                        matched = i;
                    }
                }

                const nextCount = matched + 1;
                if (nextCount > words.length) {
                    this.clearSuggestion(el);
                    return;
                }

                this.syncValue(el, words.slice(0, nextCount).join(' '));

                if (nextCount === words.length) {
                    this.clearSuggestion(el);
                }
            },
            acceptAll(event) {
                const el = event.target;
                const suggestion = this.getSuggestion(el);
                if (! suggestion) {
                    return;
                }
                event.preventDefault();
                this.syncValue(el, suggestion);
                this.clearSuggestion(el);
            },
        };
    },
};
