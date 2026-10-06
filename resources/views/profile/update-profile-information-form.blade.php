<form wire:submit="updateProfileInformation" class="perfil-formulario" x-data="{ fotoPreview: null, mostrarPassword: false, telefonoInvalido: false, destroy() { if (this.fotoPreview) URL.revokeObjectURL(this.fotoPreview); } }"
    x-on:saved="if (fotoPreview) URL.revokeObjectURL(fotoPreview); fotoPreview = null; window.dispatchEvent(new CustomEvent('perfil-actualizado')); window.dispatchEvent(new CustomEvent('toast', {detail: {type: 'success', message: 'Tu perfil se actualizó correctamente.'}}))">
    @if(Laravel\Jetstream\Jetstream::managesProfilePhotos())
        <div class="perfil-foto-editor ui-card-soft">
            <div class="perfil-foto-preview">
                <x-avatar-institucional :user="$this->user" nombre="Tu perfil" x-show="!fotoPreview" />
                <img x-show="fotoPreview" x-cloak :src="fotoPreview" alt="Vista previa de la fotografía seleccionada" class="perfil-avatar object-cover" />
            </div>
            <div class="min-w-0">
                <h3 class="ui-title font-bold">Tu fotografía</h3>
                <p class="ui-muted mt-2 text-sm leading-6">JPG, PNG o WEBP de hasta 1 MB. La vista previa se guardará al confirmar tus cambios.</p>
                <input type="file" class="sr-only" id="perfil-foto" x-ref="foto" wire:model="photo" accept="image/jpeg,image/png,image/webp"
                    x-on:change="if (fotoPreview) URL.revokeObjectURL(fotoPreview); fotoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null" />
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button" class="ui-btn ui-btn-secondary" x-on:click="$refs.foto.click()"><i class="ph-duotone ph-upload-simple" aria-hidden="true"></i> Elegir foto</button>
                    @if($this->user->profile_photo_path)
                        <button type="button" class="ui-btn ui-btn-secondary" wire:click="deleteProfilePhoto" wire:loading.attr="disabled">Usar avatar</button>
                    @endif
                </div>
                <p class="ui-muted mt-2 text-xs" wire:loading wire:target="photo">Preparando la vista previa…</p>
                <x-input-error for="photo" class="mt-2" />
            </div>
        </div>
    @endif
    <div class="perfil-campos">
        <div>
            <label class="ui-label" for="perfil-email">Correo electrónico</label>
            <input id="perfil-email" type="email" class="ui-input mt-2 w-full" wire:model="state.email" autocomplete="email" required aria-describedby="perfil-email-error" />
            <div id="perfil-email-error"><x-input-error for="email" class="mt-2" /></div>
        </div>
        <div>
            <label class="ui-label" for="perfil-telefono">Teléfono</label>
            <input id="perfil-telefono" type="tel" class="ui-input mt-2 w-full" wire:model="state.tel_per" autocomplete="tel" maxlength="20" pattern="[0-9\+\-\s\(\)]{6,20}" x-on:input="telefonoInvalido = !$el.validity.valid" :aria-invalid="telefonoInvalido" aria-describedby="perfil-telefono-error" />
            <p x-show="telefonoInvalido" x-cloak class="mt-2 text-sm text-[var(--ui-danger)]">Usa de 6 a 20 caracteres: números, espacios, +, guiones o paréntesis.</p>
            <div id="perfil-telefono-error"><x-input-error for="tel_per" class="mt-2" /></div>
        </div>
        <div class="perfil-campo-completo">
            <label class="ui-label" for="perfil-direccion">Dirección</label>
            <textarea id="perfil-direccion" class="ui-textarea mt-2 w-full" wire:model="state.dir_per" rows="3" maxlength="255" autocomplete="street-address" aria-describedby="perfil-direccion-error"></textarea>
            <div id="perfil-direccion-error"><x-input-error for="dir_per" class="mt-2" /></div>
        </div>
    </div>
    @if($this->user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $this->user->hasVerifiedEmail())
        <div class="ui-alert-info text-sm">Tu correo aún no está verificado. <button type="button" class="font-semibold underline" wire:click="sendEmailVerification">Enviar enlace de verificación</button>
            @if($this->verificationLinkSent)<p class="mt-2">Enlace enviado. Revisa tu correo.</p>@endif
        </div>
    @endif
    <div class="ui-card-soft perfil-confirmar-identidad">
        <span class="perfil-icono"><i class="ph-duotone ph-shield-check" aria-hidden="true"></i></span>
        <div class="min-w-0">
            <h3 class="ui-title font-bold">Confirma que eres tú</h3>
            <p class="ui-muted mt-1 text-sm">Ingresa tu contraseña actual para guardar la foto y los datos de contacto.</p>
            <label class="ui-label mt-4 block" for="perfil-password">Contraseña actual</label>
            <div class="relative mt-2">
                <input id="perfil-password" :type="mostrarPassword ? 'text' : 'password'" class="ui-input w-full pr-12" wire:model="state.current_password" autocomplete="current-password" required aria-describedby="perfil-password-error" @error('current_password') aria-invalid="true" @enderror />
                <button type="button" class="perfil-ver-password" x-on:click="mostrarPassword = !mostrarPassword" :aria-label="mostrarPassword ? 'Ocultar contraseña actual' : 'Mostrar contraseña actual'" :aria-pressed="mostrarPassword" aria-controls="perfil-password"><i class="ph-duotone" :class="mostrarPassword ? 'ph-eye-slash' : 'ph-eye'" aria-hidden="true"></i></button>
            </div>
            <div id="perfil-password-error"><x-input-error for="current_password" class="mt-2" /></div>
        </div>
    </div>
    <div class="perfil-formulario-pie"><p class="ui-muted text-xs">Tus datos de identidad se mantienen en el registro institucional.</p><button type="submit" class="ui-btn ui-btn-primary" wire:loading.attr="disabled" wire:target="updateProfileInformation,photo"><i class="ph-duotone ph-check-circle" aria-hidden="true"></i><span wire:loading.remove wire:target="updateProfileInformation">Guardar cambios</span><span wire:loading wire:target="updateProfileInformation">Guardando…</span></button></div>
</form>
