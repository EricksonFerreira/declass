<?php

namespace App\Actions\Reserva\Pagamento;

use App\DTO\Reserva\ReservaResolvida;

class PagamentoMotoristaAppAction
{
    public function handle(ReservaResolvida $reserva): mixed
    {
        return redirect()->route('reserva.minhas.pagamento.efetuar', [
            'id' => $reserva->codigo,
        ]);
    }
}
