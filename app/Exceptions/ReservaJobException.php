<?php

namespace App\Exceptions;

use Exception;
use Log;

class ReservaJobException extends Exception
{
    protected $code = 500;

    public function __construct($message = 'Erro ao processar o job de reservas',$mensagemErro = null)
    {
        if($mensagemErro){
            Log::error($mensagemErro);
        }
        parent::__construct($message, $this->code);
    }
}
