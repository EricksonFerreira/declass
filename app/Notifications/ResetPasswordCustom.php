<?php

namespace App\Notifications;

use App\Models\Empresa;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Storage;

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
                'logoInline' => $this->buildInlineLogo($empresa),
            ]);
    }

    private function buildInlineLogo(?Empresa $empresa): ?string
    {
        $disk = Storage::disk('public');
        $logoPath = is_string($empresa?->logo_path ?? null) ? trim((string) $empresa->logo_path) : '';

        if ($logoPath !== '' && $disk->exists($logoPath)) {
            $mimeType = $disk->mimeType($logoPath) ?: 'image/png';

            return 'data:'.$mimeType.';base64,'.base64_encode($disk->get($logoPath));
        }

        $fallbackPath = public_path('assets/imgs/logo-sem-texto.png');
        if (is_file($fallbackPath)) {
            $mimeType = mime_content_type($fallbackPath) ?: 'image/png';

            return 'data:'.$mimeType.';base64,'.base64_encode((string) file_get_contents($fallbackPath));
        }

        return null;
    }
}
