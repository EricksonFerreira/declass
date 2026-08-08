<?php

namespace App\Mail;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BoasVindasAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Empresa $empresa,
        public User $admin,
        public string $senhaTemporaria,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Suas credenciais de acesso ao DECLOCK',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.cadastro.boas-vindas-admin',
        );
    }
}
