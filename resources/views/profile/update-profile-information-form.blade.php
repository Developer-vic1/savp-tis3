<div x-data x-on:saved.window="
        Swal.fire({
            icon: 'success',
            title: 'Perfil actualizado',
            text: 'Tu información de contacto se actualizó correctamente.',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#059669'
        });
        window.dispatchEvent(new CustomEvent('perfil-actualizado'));
    ">
    <x-form-section submit="updateProfileInformation">
        <x-slot name="title">
            Información de contacto
        </x-slot>

        <x-slot name="description">
            Actualiza tu correo electrónico, teléfono, dirección y foto de perfil.
        </x-slot>

        <x-slot name="form">
            {{-- FOTO DE PERFIL --}}
            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                <div x-data="{ photoName: null, photoPreview: null }" class="col-span-6">
                    <div class="rounded-[1.8rem] border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-5 md:flex-row md:items-center">

                            {{-- Input oculto --}}
                            <input type="file" id="photo" class="hidden" wire:model.live="photo" x-ref="photo" x-on:change="
                                        photoName = $refs.photo.files[0].name;
                                        const reader = new FileReader();
                                        reader.onload = (e) => {
                                            photoPreview = e.target.result;
                                        };
                                        reader.readAsDataURL($refs.photo.files[0]);
                                    " />

                            {{-- Vista actual --}}
                            <div class="shrink-0">
                                <div class="mt-2" x-show="!photoPreview">
                                    <img src="{{ $this->user->profile_photo_url }}" alt="Foto de perfil"
                                        class="h-24 w-24 rounded-3xl object-cover ring-2 ring-slate-200">
                                </div>

                                {{-- Vista previa --}}
                                <div class="mt-2" x-show="photoPreview" style="display: none;">
                                    <span
                                        class="block h-24 w-24 rounded-3xl bg-cover bg-center bg-no-repeat ring-2 ring-slate-200"
                                        x-bind:style="'background-image: url(' + photoPreview + ')'">
                                    </span>
                                </div>
                            </div>

                            {{-- Controles --}}
                            <div class="flex-1">
                                <x-label for="photo" value="Foto de perfil" />

                                <p class="mt-2 text-sm text-slate-600">
                                    Puedes seleccionar una nueva imagen para identificar tu cuenta dentro del sistema.
                                </p>

                                <div class="mt-4 flex flex-wrap gap-3">
                                    <x-secondary-button type="button" class="rounded-2xl"
                                        x-on:click.prevent="$refs.photo.click()">
                                        Seleccionar nueva foto
                                    </x-secondary-button>

                                    @if ($this->user->profile_photo_path)
                                        <x-secondary-button type="button" class="rounded-2xl" wire:click="deleteProfilePhoto">
                                            Quitar foto
                                        </x-secondary-button>
                                    @endif
                                </div>

                                <template x-if="photoName">
                                    <p class="mt-3 text-sm text-slate-500">
                                        Archivo seleccionado:
                                        <span class="font-medium text-slate-700" x-text="photoName"></span>
                                    </p>
                                </template>

                                <x-input-error for="photo" class="mt-3" />
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- CORREO --}}
            <div class="col-span-6 sm:col-span-3">
                <x-label for="email" value="Correo electrónico" />
                <x-input id="email" type="email" class="mt-2 block w-full rounded-2xl border-slate-300"
                    wire:model="state.email" required autocomplete="username" />

                <x-input-error for="email" class="mt-2" />

                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && !$this->user->hasVerifiedEmail())
                    <div class="mt-3 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        Tu correo electrónico aún no está verificado.

                        <button type="button" class="ml-1 font-semibold underline"
                            wire:click.prevent="sendEmailVerification">
                            Reenviar verificación
                        </button>
                    </div>

                    @if ($this->verificationLinkSent)
                        <p class="mt-3 text-sm font-medium text-emerald-700">
                            Se envió un nuevo enlace de verificación a tu correo electrónico.
                        </p>
                    @endif
                @endif
            </div>

            {{-- TELÉFONO --}}
            <div class="col-span-6 sm:col-span-3">
                <x-label for="telefono" value="Teléfono" />
                <x-input id="telefono" type="text" class="mt-2 block w-full rounded-2xl border-slate-300"
                    wire:model="state.tel_per" autocomplete="tel" />

                <x-input-error for="state.tel_per" class="mt-2" />
            </div>

            {{-- DIRECCIÓN --}}
            <div class="col-span-6">
                <x-label for="direccion" value="Dirección" />

                <textarea id="direccion" wire:model="state.dir_per" rows="4"
                    class="mt-2 block w-full rounded-2xl border-slate-300 text-sm text-slate-700 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                    placeholder="Ingresa tu dirección actual"></textarea>

                <x-input-error for="state.dir_per" class="mt-2" />
            </div>
        </x-slot>

        <x-slot name="actions">
            <x-action-message class="me-3 text-emerald-700" on="saved">
                Información actualizada correctamente.
            </x-action-message>

            <x-button wire:loading.attr="disabled" wire:target="photo"
                class="rounded-2xl bg-gradient-to-r from-emerald-600 to-sky-600 px-5 py-3 text-white shadow-lg">
                Guardar cambios
            </x-button>
        </x-slot>
    </x-form-section>
</div>