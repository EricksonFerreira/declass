<?php

namespace App\Notifications;

use App\Models\Empresa;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordCustom extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $empresa = $notifiable->empresa instanceof Empresa ? $notifiable->empresa : null;
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Redefinição de senha')
            ->view('emails.auth.reset-password', [
                'user' => $notifiable,
                'empresa' => $empresa,
                'url' => $url,
            ]);
    }
}
