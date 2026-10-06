<?php

namespace App\Livewire\Perfil;

use App\Support\Seguridad\VerificadorIdentidadPerfil;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Laravel\Jetstream\Contracts\DeletesUsers;
use Laravel\Jetstream\Http\Livewire\DeleteUserForm;

class RetirarAcceso extends DeleteUserForm
{
    public string $confirmacion = '';

    public function confirmUserDeletion()
    {
        $this->confirmacion = '';
        parent::confirmUserDeletion();
    }

    public function deleteUser(Request $request, DeletesUsers $deleter, StatefulGuard $auth)
    {
        $this->validate(['confirmacion' => ['required', 'in:RETIRAR']], [
            'confirmacion.required' => 'Escribe RETIRAR para confirmar tu decisión.',
            'confirmacion.in' => 'Escribe RETIRAR exactamente como se indica.',
        ]);
        app(VerificadorIdentidadPerfil::class)->confirmar(auth()->user(), $this->password, 'password');

        return parent::deleteUser($request, $deleter, $auth);
    }
}
