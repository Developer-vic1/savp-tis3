@if (session('google_auth_error') || session('aula_virtual_access_error'))
    @php
        $esCuentaNoRegistrada = session('google_auth_error');
        $titulo = $esCuentaNoRegistrada ? 'Cuenta no registrada' : 'Acceso no habilitado';
        $mensaje = $esCuentaNoRegistrada
            ? 'Tu cuenta de Google fue verificada correctamente, pero no se encuentra registrada en el sistema institucional. Para solicitar tu habilitacion, contacta con soporte.'
            : 'Tu cuenta existe en el sistema, pero no tiene permisos habilitados para el Aula Virtual. Contacta con administracion.';
    @endphp

    <div x-data="{ open: true }" x-show="open" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6">
        <div class="ui-modal-backdrop fixed inset-0" @click="open = false"></div>

        <section class="ui-modal w-full max-w-lg">
            <div class="ui-modal-header text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl"
                    style="background: var(--ui-danger-soft); color: var(--ui-danger);">
                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 9v3.75m0 3.75h.008M4.5 19.5h15L12 4.5 4.5 19.5Z" />
                    </svg>
                </div>

                <h2 class="ui-title mt-4 text-2xl font-black">{{ $titulo }}</h2>
                <p class="ui-subtitle mt-3 text-sm leading-7">{{ $mensaje }}</p>
            </div>

            <div class="px-6 py-5 text-center">
                <p class="ui-muted text-sm">Soporte institucional</p>
                <p class="mt-1 text-lg font-black" style="color: var(--ui-primary);">+591 75836807</p>
            </div>

            <div class="ui-modal-footer flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="https://wa.me/59175836807?text=Hola%2C%20necesito%20habilitar%20mi%20cuenta%20para%20acceder%20al%20Aula%20Virtual%20SAVP-TIS3"
                    target="_blank" rel="noopener noreferrer" class="ui-btn-primary">
                    Contactar por WhatsApp
                </a>

                <button type="button" class="ui-btn-secondary" @click="open = false">
                    Cerrar
                </button>
            </div>
        </section>
    </div>
@endif
