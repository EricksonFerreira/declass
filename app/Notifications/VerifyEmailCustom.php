<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailCustom extends VerifyEmail
{
    protected function buildMailMessage($url)
    {
        return (new MailMessage)
            ->subject('Confirmação de e-mail')
            ->greeting('Olá!')
            ->line('Obrigado por se cadastrar na DECARS.')
            ->line('Para ativar sua conta, confirme seu endereço de e-mail clicando no botão abaixo.')
            ->action('Confirmar e-mail', $url)
            ->line('Se você não criou essa conta, pode ignorar este e-mail com segurança.')
            ->salutation('Atenciosamente, DECARS');
    }
}