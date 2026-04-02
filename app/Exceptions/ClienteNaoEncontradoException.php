<?php

namespace App\Exceptions;

use Exception;

class ClienteNaoEncontradoException extends Exception
{
    public function __construct(string $message = 'Cliente não encontrado.', int $code = 404, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}