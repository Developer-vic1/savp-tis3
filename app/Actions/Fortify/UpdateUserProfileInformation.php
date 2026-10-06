<?php

namespace App\Actions\Fortify;

use App\Models\Oficial\Sistema\User;
use App\Support\Seguridad\VerificadorIdentidadPerfil;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        app(VerificadorIdentidadPerfil::class)->confirmar($user, $input['current_password'] ?? '', bolsa: 'updateProfileInformation');
        Validator::make($input, [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->cod_usu, 'cod_usu'),
            ],
            'tel_per' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]{6,20}$/'],
            'dir_per' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        // Actualizar datos de persona
        if ($user->persona) {
            $user->persona->forceFill([
                'tel_per' => array_key_exists('tel_per', $input) ? $input['tel_per'] : $user->persona->tel_per,
                'dir_per' => array_key_exists('dir_per', $input) ? $input['dir_per'] : $user->persona->dir_per,
            ])->save();
        }

        // Actualizar email del usuario
        if ($input['email'] !== $user->email && $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'email' => $input['email'],
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'email' => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
