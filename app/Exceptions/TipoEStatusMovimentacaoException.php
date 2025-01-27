<?php

namespace App\Exceptions;

use Exception;

class TipoEStatusMovimentacaoException extends Exception
{
    /**
     * Construtor personalizado para a exceção.
     *
     * @param string $message A mensagem de erro.
     * @param int $code O código de erro (opcional).
     * @param Exception|null $previous A exceção anterior usada para encadeamento de exceções (opcional).
     */
    public function __construct($message = "Erro ao obter tipo e status da movimentação", $code = 0, Exception $previous = null)
    {
        // Chama o construtor da classe pai
        parent::__construct($message, $code, $previous);
    }
}
