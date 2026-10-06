<?php

namespace App\Actions\Jetstream;

use App\Models\Oficial\Sistema\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Jetstream\Contracts\DeletesUsers;

class DeleteUser implements DeletesUsers
{
    /**
     * Retira el acceso conservando la cuenta y sus registros institucionales.
     */
    public function delete(User $user): void
    {
        abort_unless(auth()->id() === $user->getKey() && $user->est_usu === 'ACTIVO', 403);
        DB::transaction(function () use ($user) {
            $user->forceFill(['est_usu' => 'INACTIVO', 'remember_token' => null])->save();
            if (Schema::hasTable('personal_access_tokens')) {
                $user->tokens()->delete();
            }
        });
    }
}
