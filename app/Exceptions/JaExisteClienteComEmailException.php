<?php

namespace App\Exceptions;

use Exception;

class JaExisteClienteComEmailException extends Exception
{
    // Definindo o código HTTP adequado
    protected $code = 400;

    // Mensagem de erro mais detalhada e explicativa
    protected $message = 'O e-mail fornecido já está associado a um cliente. Por favor, verifique os dados cadastrados.';

    public function __construct()
    {
        parent::__construct($this->message, $this->code);
    }
}
