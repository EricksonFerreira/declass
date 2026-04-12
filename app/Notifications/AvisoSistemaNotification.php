<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AvisoSistemaNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $titulo,
        protected string $mensagem,
        protected ?string $url = null,
        protected string $icone = 'fas fa-bell',
        protected string $cor = 'warning',
        protected ?string $chave = null,
        protected string $categoria = 'sistema',
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'categoria' => $this->categoria,
            'chave' => $this->chave,
            'titulo' => $this->titulo,
            'mensagem' => $this->mensagem,
            'url' => $this->url,
            'icone' => $this->icone,
            'cor' => $this->cor,
        ];
    }
}
