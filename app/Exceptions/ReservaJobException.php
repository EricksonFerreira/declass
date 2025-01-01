<?php

namespace App\Exceptions;

use Exception;

class ReservaJobException extends Exception
{
    protected $code = 500;

    public function __construct($message = 'Erro ao processar o job de reservas')
    {
        parent::__construct($message, $this->code);
    }
}
