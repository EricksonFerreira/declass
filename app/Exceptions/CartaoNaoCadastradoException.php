<?php

namespace App\Exceptions;

use Exception;

class CartaoNaoCadastradoException extends Exception
{
    protected $code = 404;
    protected $message = 'Cliente não possui cartão cadastrado!';

    public function __construct()
    {
        parent::__construct($this->message, $this->code);
    }
}
