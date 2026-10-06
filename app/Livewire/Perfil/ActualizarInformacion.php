<?php

namespace App\Livewire\Perfil;

use App\Support\Seguridad\VerificadorIdentidadPerfil;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm;

class ActualizarInformacion extends UpdateProfileInformationForm
{
    public function mount()
    {
        $usuario = auth()->user();
        $this->state = [
            'email' => $usuario->email,
            'tel_per' => $usuario->persona?->tel_per ?? '',
            'dir_per' => $usuario->persona?->dir_per ?? '',
            'current_password' => '',
        ];
    }

    public function updateProfileInformation(UpdatesUserProfileInformation $updater)
    {
        $resultado = parent::updateProfileInformation($updater);
        $this->state['current_password'] = '';

        return $resultado;
    }

    public function deleteProfilePhoto()
    {
        app(VerificadorIdentidadPerfil::class)->confirmar(auth()->user(), $this->state['current_password'] ?? '', bolsa: 'updateProfileInformation');
        parent::deleteProfilePhoto();
        $this->state['current_password'] = '';
    }
}
