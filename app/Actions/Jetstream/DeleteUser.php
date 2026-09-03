<?php

namespace App\Actions\Jetstream;

use App\Models\User;
use Laravel\Jetstream\Contracts\DeletesUsers;
use Laravel\Jetstream\Features;

class DeleteUser implements DeletesUsers
{
    /**
     * Delete the given user.
     */
    public function delete(User $user): void
    {
        $user->deleteProfilePhoto();

        if (Features::hasApiFeatures()) {
            $user->tokens->each->delete();
        }

        $user->delete();
    }
}
