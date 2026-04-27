<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPasswordNotification extends ResetPassword
{
    use Queueable;

    public function toMail($notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Restablecimiento de contraseña - SAVP TIS 3')
            ->greeting('Hola,')
            ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta en el sistema SAVP – TIS 3.')
            ->action('Restablecer contraseña', $resetUrl)
            ->line('Este enlace de recuperación expirará en 60 minutos.')
            ->line('Si no solicitaste este cambio, puedes ignorar este mensaje. No se realizará ninguna modificación en tu cuenta.')
            ->salutation('Atentamente, Unidad Educativa Franz Tamayo N°3');
    }
}
