<?php

namespace App\Exceptions;

use Exception;

class JaExisteClienteComEmailException extends Exception
{
    public function __construct(string $message = 'Já existe um cliente cadastrado com este e-mail.', int $code = 409, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}