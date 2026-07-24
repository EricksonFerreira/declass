<?php

namespace App\Actions\Reserva\Renovacao;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

interface RenovacaoStrategyInterface
{
    /**
     * Executa a lógica de renovação para um tipo de reserva.
     *
     * @param  Request  $request
     * @param  mixed    $reserva  ReservaMotoristaApp ou Reserva
     * @return mixed
     */
    public function handle(Request $request, mixed $reserva): mixed;
}
