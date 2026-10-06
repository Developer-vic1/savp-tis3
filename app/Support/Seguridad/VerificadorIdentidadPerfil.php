<?php

namespace App\Support\Seguridad;

use App\Models\Oficial\Sistema\User;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class VerificadorIdentidadPerfil
{
    public function __construct(private LimitadorIntentosAcceso $limitador) {}

    public function confirmar(User $usuario, mixed $password, string $campo = 'current_password', string $bolsa = 'default'): void
    {
        abort_unless(auth()->id() === $usuario->getKey() && $usuario->est_usu === 'ACTIVO', 403);
        $clave = 'perfil:'.hash('sha256', $usuario->getKey().'|'.request()->ip());
        try {
            $this->limitador->serializar($clave, function () use ($clave, $usuario, $password, $campo, $bolsa) {
                $estado = $this->limitador->estado($clave);
                if ($estado['restantes'] > 0) {
                    $this->rechazar($campo, $bolsa, "Espera {$estado['restantes']} segundos antes de volver a verificar tu identidad.");
                }
                if (! is_string($password) || $password === '' || strlen($password) > 1024 || ! Hash::check($password, $usuario->password)) {
                    $estado = $this->limitador->registrarFallo($clave);
                    $mensaje = $estado['restantes'] > 0
                        ? "Espera {$estado['restantes']} segundos antes de volver a verificar tu identidad."
                        : 'Ingresa tu contraseña actual para confirmar. Tus cambios siguen en el formulario.';
                    $this->rechazar($campo, $bolsa, $mensaje);
                }
                $this->limitador->limpiar($clave);
            });
        } catch (LockTimeoutException) {
            $this->rechazar($campo, $bolsa, 'Espera unos segundos antes de volver a verificar tu identidad.');
        }
    }

    private function rechazar(string $campo, string $bolsa, string $mensaje): never
    {
        throw ValidationException::withMessages([$campo => $mensaje])->errorBag($bolsa);
    }
}
