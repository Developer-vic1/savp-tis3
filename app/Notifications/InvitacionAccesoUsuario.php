<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class InvitacionAccesoUsuario extends Notification
{
    public function __construct(public string $token, public ?string $correoAcceso = null, public ?string $nombre = null, public ?string $correoEntrega = null) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = rtrim((string) config('acceso-usuarios.url_publica'), '/').route('password.reset', [
            'token' => $this->token, 'email' => $this->correoAcceso ?? $notifiable->getEmailForPasswordReset(),
        ], false);
        $minutos = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject('¡Bienvenido a SAVP! Prepara tu acceso')
            ->action('Elegir mi contraseña', $url)
            ->markdown('correos.bienvenida-acceso', [
                'nombre' => $this->nombre ?? $notifiable->persona?->nom_per ?? '',
                'correoAcceso' => $this->correoAcceso ?? $notifiable->email,
                'correoEntrega' => $this->correoEntrega ?? $notifiable->email,
                'urlAcceso' => $url,
                'urlInicio' => rtrim(config('acceso-usuarios.url_publica'), '/').'/login',
                'minutos' => $minutos,
                'esLocal' => rtrim(config('acceso-usuarios.url_publica'), '/') === 'http://localhost:8000',
            ]);
    }
}
