<?php

namespace App\Actions\Reserva\Pagamento;

use App\DTO\Reserva\ReservaResolvida;

class PagamentoReservaComumAction
{
    public function handle(ReservaResolvida $reserva): mixed
    {
        return redirect()->route('reserva.efetuarPagamento', $reserva->codigo);
    }
}
