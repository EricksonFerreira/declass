<?php

namespace App\View\Components;

use Illuminate\View\Component;

class DeleteButton extends Component
{
    public $action;

    public $icone;

    public $titulo;

    public $btnClass;

    public $tituloMensagem;

    public $mensagem;

    public $textoConfirmacao;

    public $corConfirmacao;

    public function __construct(
        $action,
        $icone = null,
        $titulo = null,
        $btnClass = null,
        $tituloMensagem = null,
        $mensagem = null,
        $textoConfirmacao = null,
        $corConfirmacao = null
    ) {
        $this->action = $action;
        $this->icone = $icone;
        $this->titulo = $titulo;
        $this->btnClass = $btnClass;
        $this->tituloMensagem = $tituloMensagem;
        $this->mensagem = $mensagem;
        $this->textoConfirmacao = $textoConfirmacao;
        $this->corConfirmacao = $corConfirmacao;
    }

    public function render()
    {
        return view('components.delete-button');
    }
}
