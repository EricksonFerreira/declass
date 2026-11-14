<?php

namespace App\Mail;

use App\Models\SolicitacaoMatricula;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * E-mail ao responsavel com o resultado da analise da
 * pre-matricula (aprovada ou recusada).
 */
class AnaliseMatriculaMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SolicitacaoMatricula $solicitacao,
    ) {}

    public function envelope(): Envelope
    {
        $situacao = $this->solicitacao->status === 'aprovada' ? 'aprovada' : 'não aprovada';

        return new Envelope(
            subject: "Pré-matrícula #{$this->solicitacao->codigo} {$situacao}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.matricula.analise',
        );
    }
}
