<?php

namespace App\Exceptions;

use RuntimeException;

class OperacaoNaoPermitidaException extends RuntimeException
{
    public function __construct(string $message = 'Operação não permitida para este tipo de reserva.', int $code = 422)
    {
        parent::__construct($message, $code);
    }
}
