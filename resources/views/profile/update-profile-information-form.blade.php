<div x-data="{
        telefono: @entangle('state.tel_per').live,
        direccion: @entangle('state.dir_per').live,
        email: @entangle('state.email').live,

        get emailValido() {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email || '');
        },

        get telefonoValido() {
            if (!this.telefono) return true;
            return /^[0-9+\-\s()]{6,20}$/.test(this.telefono);
        },

        get direccionValida() {
            if (!this.direccion) return true;
            return this.direccion.length <= 255;
        },

        get puedeGuardar() {
            return this.emailValido && this.telefonoValido && this.direccionValida;
        }
    }" x-on:saved.window="
        if (window.Swal) {
            Swal.fire({
                icon: 'success',
                title: 'Perfil actualizado',
                text: 'Tu información institucional se actualizó correctamente.',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#059669'
            });
        }

        window.dispatchEvent(new CustomEvent('perfil-actualizado'));
    ">
    <form wire:submit.prevent="updateProfileInformation"
        class="relative overflow-hidden rounded-[1.8rem] border border-[var(--ui-border)] bg-[var(--ui-card)] p-5 shadow-sm sm:p-6">

        {{-- Fondos suaves --}}
        <div class="pointer-events-none absolute -right-24 -top-24 h-64 w-64 rounded-full bg-emerald-400/10 blur-3xl">
        </div>
        <div class="pointer-events-none absolute -bottom-24 left-10 h-64 w-64 rounded-full bg-sky-400/10 blur-3xl">
        </div>
        <div class="pointer-events-none absolute bottom-0 right-1/3 h-52 w-52 rounded-full bg-violet-400/10 blur-3xl">
        </div>

        <div class="relative space-y-6">

            {{-- ============================================================
            CABECERA GENERAL
            ============================================================ --}}
            <section class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                <div class="max-w-3xl">
                    <div class="flex flex-wrap items-center gap-2">
                        <span
                            class="inline-flex items-center gap-2 rounded-full border border-sky-200/70 bg-sky-50 px-3 py-1 text-xs font-black uppercase tracking-[0.14em] text-sky-700 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-300">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                    d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0l-7.5-4.615a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                            </svg>
                            Perfil institucional
                        </span>

                        <span
                            class="inline-flex rounded-full border border-[var(--ui-border)] bg-[var(--ui-soft)] px-3 py-1 text-xs font-bold text-[var(--ui-muted)]">
                            Foto y contacto
                        </span>
                    </div>

                    <h3 class="mt-4 text-xl font-black tracking-tight text-[var(--ui-text)]">
                        Actualización de perfil institucional
                    </h3>

                    <p class="mt-2 text-sm leading-7 text-[var(--ui-muted)]">
                        Modifica tu fotografía de perfil y los datos de contacto autorizados. Los datos de
                        identificación
                        personal se conservan bajo control administrativo.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 xl:min-w-[320px]">
                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                            Correo
                        </p>

                        <p class="mt-1 text-2xl font-black"
                            :class="emailValido ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300'">
                            <span x-text="emailValido ? 'OK' : '!'"></span>
                        </p>

                        <p class="mt-1 text-xs text-[var(--ui-muted)]">
                            Validación activa
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                            Foto
                        </p>

                        @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                            <p
                                class="mt-1 text-2xl font-black {{ $this->user->profile_photo_path ? 'text-emerald-600 dark:text-emerald-300' : 'text-amber-600 dark:text-amber-300' }}">
                                {{ $this->user->profile_photo_path ? 'Sí' : 'No' }}
                            </p>

                            <p class="mt-1 text-xs text-[var(--ui-muted)]">
                                {{ $this->user->profile_photo_path ? 'Imagen personalizada' : 'Imagen por defecto' }}
                            </p>
                        @else
                            <p class="mt-1 text-2xl font-black text-rose-600 dark:text-rose-300">
                                Off
                            </p>

                            <p class="mt-1 text-xs text-[var(--ui-muted)]">
                                Fotos no habilitadas
                            </p>
                        @endif
                    </div>
                </div>
            </section>

            {{-- ============================================================
            FOTO DE PERFIL
            ============================================================ --}}
            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                <section x-data="{
                            photoName: null,
                            photoPreview: null,
                            hasSelectedPhoto: false,

                            clearSelection() {
                                this.photoName = null;
                                this.photoPreview = null;
                                this.hasSelectedPhoto = false;

                                if (this.$refs.photo) {
                                    this.$refs.photo.value = null;
                                }
                            },

                            selectPhoto(event) {
                                const file = event.target.files[0];

                                if (!file) {
                                    this.clearSelection();
                                    return;
                                }

                                this.photoName = file.name;
                                this.hasSelectedPhoto = true;

                                const reader = new FileReader();

                                reader.onload = (e) => {
                                    this.photoPreview = e.target.result;
                                };

                                reader.readAsDataURL(file);
                            }
                        }" class="overflow-hidden rounded-[1.6rem] border border-[var(--ui-border)] bg-[var(--ui-soft)]">

                    {{-- Encabezado interno --}}
                    <div class="border-b border-[var(--ui-border)] bg-[var(--ui-card)] px-5 py-4">
                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full border border-emerald-200/70 bg-emerald-50 px-3 py-1 text-xs font-black uppercase tracking-[0.14em] text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18A2.25 2.25 0 0 0 4.5 20.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316A2.25 2.25 0 0 0 14.44 3.75H9.56a2.25 2.25 0 0 0-1.912 1.059l-.821 1.316Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M15 13.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        Imagen institucional
                                    </span>

                                    @if ($this->user->profile_photo_path)
                                        <span
                                            class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 dark:border-emerald-400/20 dark:bg-emerald-400/10 dark:text-emerald-300">
                                            Foto personalizada activa
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-300">
                                            Usando imagen por defecto
                                        </span>
                                    @endif

                                    <span x-show="hasSelectedPhoto" x-cloak
                                        class="inline-flex rounded-full border border-sky-200 bg-sky-50 px-3 py-1 text-xs font-bold text-sky-700 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-300">
                                        Nueva foto seleccionada
                                    </span>
                                </div>

                                <h4 class="mt-3 text-lg font-black text-[var(--ui-text)]">
                                    Foto de perfil
                                </h4>

                                <p class="mt-1 max-w-3xl text-sm leading-6 text-[var(--ui-muted)]">
                                    Puedes cambiar tu foto institucional. La imagen seleccionada se guardará cuando
                                    presiones
                                    <span class="font-bold text-[var(--ui-text)]">Guardar cambios</span>.
                                </p>
                            </div>

                            <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-4 py-3">
                                <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                    Estado actual
                                </p>

                                <p
                                    class="mt-1 text-sm font-black {{ $this->user->profile_photo_path ? 'text-emerald-600 dark:text-emerald-300' : 'text-amber-600 dark:text-amber-300' }}">
                                    {{ $this->user->profile_photo_path ? 'Foto cargada' : 'Sin foto personalizada' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <input type="file" id="photo" class="hidden" wire:model.live="photo" x-ref="photo"
                            accept="image/png,image/jpeg,image/jpg,image/webp" x-on:change="selectPhoto($event)" />

                        <div class="grid gap-6 xl:grid-cols-[240px,1fr] xl:items-start">

                            {{-- Preview --}}
                            <div class="flex flex-col items-center">
                                <div class="relative">
                                    {{-- Foto actual --}}
                                    <div x-show="!photoPreview">
                                        <img src="{{ $this->user->profile_photo_url }}" alt="Foto actual de perfil"
                                            class="h-44 w-44 rounded-[2rem] object-cover ring-4 ring-[var(--ui-border)] shadow-xl">
                                    </div>

                                    {{-- Nueva foto seleccionada --}}
                                    <div x-show="photoPreview" x-cloak>
                                        <span
                                            class="block h-44 w-44 rounded-[2rem] bg-cover bg-center bg-no-repeat ring-4 ring-emerald-300 shadow-xl dark:ring-emerald-400/50"
                                            x-bind:style="'background-image: url(' + photoPreview + ')'">
                                        </span>
                                    </div>

                                    {{-- Indicador --}}
                                    <div
                                        class="absolute -bottom-3 left-1/2 flex -translate-x-1/2 items-center gap-1 rounded-full border border-[var(--ui-border)] bg-[var(--ui-card)] px-3 py-1 text-xs font-black shadow-sm">
                                        <span
                                            class="h-2 w-2 rounded-full {{ $this->user->profile_photo_path ? 'bg-emerald-500' : 'bg-amber-500' }}">
                                        </span>

                                        <span x-show="!hasSelectedPhoto" class="text-[var(--ui-text)]">
                                            {{ $this->user->profile_photo_path ? 'Actual' : 'Defecto' }}
                                        </span>

                                        <span x-show="hasSelectedPhoto" x-cloak
                                            class="text-emerald-600 dark:text-emerald-300">
                                            Nueva
                                        </span>
                                    </div>
                                </div>

                                <div wire:loading wire:target="photo"
                                    class="mt-6 rounded-2xl border border-sky-200 bg-sky-50 px-4 py-2 text-xs font-bold text-sky-700 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-300">
                                    Cargando imagen...
                                </div>
                            </div>

                            {{-- Contenido --}}
                            <div class="space-y-4">
                                <div class="grid gap-3 md:grid-cols-3">
                                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-4 py-3">
                                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                            Formatos
                                        </p>
                                        <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                                            JPG, PNG, WEBP
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-4 py-3">
                                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                            Uso
                                        </p>
                                        <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                                            Identificación
                                        </p>
                                    </div>

                                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-4 py-3">
                                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                                            Guardado
                                        </p>
                                        <p class="mt-1 text-sm font-black text-[var(--ui-text)]">
                                            Al confirmar
                                        </p>
                                    </div>
                                </div>

                                <div
                                    class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm leading-6 text-sky-800 dark:border-sky-400/20 dark:bg-sky-400/10 dark:text-sky-200">
                                    Selecciona una imagen clara, preferentemente frontal. Si eliges una nueva fotografía,
                                    revisa la vista previa antes de guardar.
                                </div>

                                <template x-if="photoName">
                                    <div
                                        class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-4 py-3 text-sm text-[var(--ui-muted)]">
                                        Archivo seleccionado:
                                        <span class="font-black text-[var(--ui-text)]" x-text="photoName"></span>
                                    </div>
                                </template>

                                <x-input-error for="photo" class="mt-3" />

                                <div class="flex flex-wrap gap-3">
                                    <button type="button" x-on:click.prevent="$refs.photo.click()"
                                        class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:-translate-y-0.5 hover:shadow-xl">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M4.5 19.5h15A2.25 2.25 0 0 0 21.75 17.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                        </svg>
                                        {{ $this->user->profile_photo_path ? 'Cambiar foto' : 'Subir foto' }}
                                    </button>

                                    <button type="button" x-show="hasSelectedPhoto" x-cloak x-on:click="clearSelection()"
                                        class="inline-flex items-center justify-center gap-2 rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-soft)] px-5 py-3 text-sm font-bold text-[var(--ui-text)] transition hover:bg-[var(--ui-card)]">
                                        Cancelar selección
                                    </button>

                                    @if ($this->user->profile_photo_path)
                                        <button type="button" wire:click="deleteProfilePhoto" wire:loading.attr="disabled"
                                            wire:target="deleteProfilePhoto"
                                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-3 text-sm font-bold text-rose-700 transition hover:bg-rose-100 disabled:cursor-not-allowed disabled:opacity-60 dark:border-rose-400/20 dark:bg-rose-400/10 dark:text-rose-300 dark:hover:bg-rose-400/15">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                                    d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166M18.16 19.673A2.25 2.25 0 0 1 15.916 21H8.084a2.25 2.25 0 0 1-2.244-1.827L4.772 5.79m14.456 0A48.108 48.108 0 0 0 12 5.25c-2.497 0-4.913.19-7.228.54" />
                                            </svg>
                                            Quitar foto actual
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            @else
                {{-- ALERTA SI JETSTREAM NO TIENE FOTOS ACTIVADAS --}}
                <section
                    class="rounded-[1.6rem] border border-amber-200 bg-amber-50 p-5 text-amber-900 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-sm font-black uppercase tracking-[0.16em]">
                                Foto de perfil no habilitada
                            </p>

                            <h4 class="mt-2 text-lg font-black">
                                Activa las fotos de perfil en Jetstream
                            </h4>

                            <p class="mt-2 max-w-3xl text-sm leading-7">
                                La sección para subir una nueva foto está oculta porque Jetstream no tiene habilitada la
                                característica de fotos de perfil. Activa <span
                                    class="font-black">Features::profilePhotos()</span>
                                en <span class="font-black">config/jetstream.php</span>.
                            </p>
                        </div>

                        <div
                            class="rounded-2xl border border-amber-300/70 bg-white/70 px-4 py-3 text-sm font-black text-amber-800 dark:border-amber-400/20 dark:bg-slate-950/40 dark:text-amber-200">
                            Requiere configuración
                        </div>
                    </div>
                </section>
            @endif

            {{-- ============================================================
            DATOS DE CONTACTO
            ============================================================ --}}
            <section class="rounded-[1.6rem] border border-[var(--ui-border)] bg-[var(--ui-soft)] p-5">
                <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h4 class="text-lg font-black text-[var(--ui-text)]">
                            Datos de contacto
                        </h4>

                        <p class="mt-2 max-w-3xl text-sm leading-7 text-[var(--ui-muted)]">
                            Mantén actualizados estos datos para comunicaciones administrativas, académicas o de
                            seguridad
                            dentro del sistema.
                        </p>
                    </div>

                    <div class="rounded-2xl border border-[var(--ui-border)] bg-[var(--ui-card)] px-4 py-3">
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-[var(--ui-muted)]">
                            Validación
                        </p>

                        <p class="mt-1 text-sm font-black"
                            :class="puedeGuardar ? 'text-emerald-600 dark:text-emerald-300' : 'text-rose-600 dark:text-rose-300'">
                            <span x-text="puedeGuardar ? 'Lista para guardar' : 'Revisar campos'"></span>
                        </p>
                    </div>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    {{-- Correo --}}
                    <div>
                        <label for="email" class="block text-sm font-bold text-[var(--ui-text)]">
                            Correo electrónico
                        </label>

                        <div class="relative mt-2">
                            <input id="email" type="email" class="ui-input block w-full pr-11"
                                wire:model.live="state.email" x-model="email" required autocomplete="username"
                                placeholder="correo@institucion.com" />

                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                <svg x-show="emailValido" class="h-5 w-5 text-emerald-600 dark:text-emerald-300"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>

                                <svg x-show="!emailValido" class="h-5 w-5 text-rose-500 dark:text-rose-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM10.29 3.86 1.82 18a1.875 1.875 0 0 0 1.607 2.84h17.146A1.875 1.875 0 0 0 22.18 18L13.71 3.86a1.875 1.875 0 0 0-3.42 0Z" />
                                </svg>
                            </div>
                        </div>

                        <p x-show="!emailValido" x-cloak
                            class="mt-2 text-xs font-semibold text-rose-600 dark:text-rose-300">
                            Ingresa un correo electrónico válido.
                        </p>

                        <x-input-error for="email" class="mt-2" />

                        @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && !$this->user->hasVerifiedEmail())
                            <div
                                class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-6 text-amber-800 dark:border-amber-400/20 dark:bg-amber-400/10 dark:text-amber-200">
                                Tu correo electrónico aún no está verificado.

                                <button type="button" class="ml-1 font-black underline"
                                    wire:click.prevent="sendEmailVerification">
                                    Reenviar verificación
                                </button>
                            </div>

                            @if ($this->verificationLinkSent)
                                <p class="mt-3 text-sm font-bold text-emerald-700 dark:text-emerald-300">
                                    Se envió un nuevo enlace de verificación a tu correo electrónico.
                                </p>
                            @endif
                        @endif
                    </div>

                    {{-- Teléfono --}}
                    <div>
                        <label for="telefono" class="block text-sm font-bold text-[var(--ui-text)]">
                            Teléfono
                        </label>

                        <div class="relative mt-2">
                            <input id="telefono" type="text" class="ui-input block w-full pr-11"
                                wire:model.live="state.tel_per" x-model="telefono" autocomplete="tel"
                                placeholder="Ejemplo: 76543210" />

                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                <svg x-show="telefonoValido" class="h-5 w-5 text-emerald-600 dark:text-emerald-300"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>

                                <svg x-show="!telefonoValido" class="h-5 w-5 text-rose-500 dark:text-rose-300"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM10.29 3.86 1.82 18a1.875 1.875 0 0 0 1.607 2.84h17.146A1.875 1.875 0 0 0 22.18 18L13.71 3.86a1.875 1.875 0 0 0-3.42 0Z" />
                                </svg>
                            </div>
                        </div>

                        <p x-show="!telefonoValido" x-cloak
                            class="mt-2 text-xs font-semibold text-rose-600 dark:text-rose-300">
                            El teléfono solo debe contener números, espacios, guiones o paréntesis.
                        </p>

                        <x-input-error for="state.tel_per" class="mt-2" />
                    </div>

                    {{-- Dirección --}}
                    <div class="lg:col-span-2">
                        <label for="direccion" class="block text-sm font-bold text-[var(--ui-text)]">
                            Dirección
                        </label>

                        <textarea id="direccion" wire:model.live="state.dir_per" x-model="direccion" rows="4"
                            class="ui-input mt-2 block w-full resize-none" maxlength="255"
                            placeholder="Ingresa tu dirección actual"></textarea>

                        <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                            <p x-show="!direccionValida" x-cloak
                                class="text-xs font-semibold text-rose-600 dark:text-rose-300">
                                La dirección no debe superar los 255 caracteres.
                            </p>

                            <p class="text-xs font-semibold text-[var(--ui-muted)] sm:ml-auto">
                                <span x-text="(direccion || '').length"></span>/255 caracteres
                            </p>
                        </div>

                        <x-input-error for="state.dir_per" class="mt-2" />
                    </div>
                </div>
            </section>

            {{-- ============================================================
            ACCIONES
            ============================================================ --}}
            <div
                class="flex flex-col gap-3 border-t border-[var(--ui-border)] pt-5 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-black text-[var(--ui-text)]">
                        Guardar cambios del perfil
                    </p>
                    <p class="mt-1 text-xs leading-5 text-[var(--ui-muted)]">
                        La nueva foto y los datos editados se aplicarán al confirmar la actualización.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <x-action-message class="text-sm font-bold text-emerald-700 dark:text-emerald-300" on="saved">
                        Información actualizada correctamente.
                    </x-action-message>

                    <button type="submit" x-bind:disabled="!puedeGuardar" wire:loading.attr="disabled"
                        wire:target="photo,updateProfileInformation"
                        x-bind:class="puedeGuardar
                            ? 'bg-gradient-to-r from-emerald-600 to-sky-600 text-white shadow-lg shadow-emerald-500/20 hover:-translate-y-0.5 hover:shadow-xl'
                            : 'border border-[var(--ui-border)] bg-[var(--ui-soft)] text-[var(--ui-muted)] cursor-not-allowed'"
                        class="inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-bold transition disabled:cursor-not-allowed disabled:opacity-60">

                        <svg wire:loading.remove wire:target="photo,updateProfileInformation" class="h-4 w-4"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>

                        <svg wire:loading wire:target="photo,updateProfileInformation" class="h-4 w-4 animate-spin"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z">
                            </path>
                        </svg>

                        <span wire:loading.remove wire:target="photo,updateProfileInformation">
                            Guardar cambios
                        </span>

                        <span wire:loading wire:target="photo,updateProfileInformation">
                            Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>