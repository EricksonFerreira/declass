<?php

namespace App\Exceptions;

use Exception;

class ClienteNaoEncontradoException extends Exception
{
    // Definindo o código HTTP adequado
    protected $code = 400;

    // Mensagem de erro mais detalhada e explicativa
    protected $message = 'Não foi possível encontrar um cliente com o e-mail fornecido.';

    public function __construct()
    {
        parent::__construct($this->message, $this->code);
    }
}

