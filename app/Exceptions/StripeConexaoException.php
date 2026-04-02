<?php

namespace App\Exceptions;

use Exception;

class StripeConexaoException extends Exception
{
    public function __construct(string $message = 'Erro ao conectar com a API do Stripe.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 503, $previous);
    }
}
