<?php

namespace App\Services;

use App\Models\Oficial\Sistema\User;
use App\Notifications\InvitacionAccesoUsuario;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

class EnvioAccesoUsuario
{
    public function bloqueoConfiguracion(): ?string
    {
        if (config('mail.default') !== 'smtp' || ! config('mail.mailers.smtp.host') || ! config('mail.mailers.smtp.username') || ! config('mail.mailers.smtp.password')) {
            return 'El envío institucional todavía no está configurado. La administración debe completar la conexión de correo.';
        }
        $url = (string) config('acceso-usuarios.url_publica');
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (rtrim($url, '/') === 'http://localhost:8000') {
            return null;
        }
        if (! filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https' || in_array($host, ['localhost', '127.0.0.1', '::1']) || (filter_var($host, FILTER_VALIDATE_IP) && ! filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE))) {
            return 'Configura http://localhost:8000 para las pruebas locales o una dirección pública HTTPS de SAVP.';
        }

        return null;
    }

    public function enviar(User $usuario, ?string $correoEntrega = null): array
    {
        $correoEntrega = strtolower(trim($correoEntrega ?? $usuario->email));
        if ($bloqueo = $this->bloqueoConfiguracion()) {
            return ['enviado' => false, 'mensaje' => $bloqueo];
        }
        if ($usuario->est_usu !== 'ACTIVO' || ! filter_var($usuario->email, FILTER_VALIDATE_EMAIL) || ! filter_var($correoEntrega, FILTER_VALIDATE_EMAIL)) {
            return ['enviado' => false, 'mensaje' => 'La cuenta debe estar activa y tener un correo válido para recibir el enlace.'];
        }
        try {
            $estado = Password::broker()->sendResetLink(['email' => $usuario->email], function ($destinatario, $token) use ($correoEntrega) {
                if ($correoEntrega === $destinatario->email) {
                    $destinatario->notify(new InvitacionAccesoUsuario($token, $destinatario->email, $destinatario->persona?->nom_per ?? '', $correoEntrega));
                } else {
                    Notification::route('mail', $correoEntrega)->notify(new InvitacionAccesoUsuario($token, $destinatario->email, $destinatario->persona?->nom_per ?? '', $correoEntrega));
                }
            });
        } catch (\Throwable) {
            Password::broker()->deleteToken($usuario);

            return ['enviado' => false, 'mensaje' => 'No se pudo enviar el correo. La cuenta y su contraseña se conservan. Revisa la conexión de correo y vuelve a intentarlo.'];
        }

        return ['enviado' => $estado === Password::RESET_LINK_SENT, 'mensaje' => match ($estado) {
            Password::RESET_LINK_SENT => 'El servidor de correo aceptó el enlace para '.$correoEntrega.'. Pide al destinatario revisar también spam.',
            Password::RESET_THROTTLED => 'Ya se solicitó un enlace recientemente. Espera un minuto antes de reintentarlo.',
            default => 'No se pudo preparar el enlace. Revisa la cuenta e inténtalo nuevamente.',
        }];
    }
}
