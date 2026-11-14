<?php

namespace App\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Ponto unico de envio de e-mails transacionais.
 * Falha de SMTP so registra log e retorna false — nunca
 * derruba o fluxo chamador (a operacao de negocio ja foi
 * persistida). Destinatario vazio e no-op.
 */
class EmailService
{
    /**
     * @param  string|null  $contexto  Texto curto para o log (ex.: "pre-matricula #ABC123")
     * @return bool true quando o e-mail foi aceito pelo mailer
     */
    public function enviar(?string $destinatario, Mailable $mailable, ?string $contexto = null): bool
    {
        if (! $destinatario) {
            return false;
        }

        try {
            Mail::to($destinatario)->send($mailable);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Falha ao enviar e-mail'
                .($contexto ? " ({$contexto})" : '')
                ." para {$destinatario}: {$e->getMessage()}");

            return false;
        }
    }
}
