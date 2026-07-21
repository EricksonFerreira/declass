<?php

namespace App\Exceptions;

use RuntimeException;

class ReservaNaoEncontradaException extends RuntimeException
{
    public function __construct(string $message = 'Reserva não encontrada.', int $code = 404)
    {
        parent::__construct($message, $code);
    }
}
