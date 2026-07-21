<?php

namespace App\Actions\Reserva\Checkout\Finalizar;

use App\Models\ReservaMotoristaApp;

class FinalizarCheckoutMotoristaAppAction
{
    public function handle(ReservaMotoristaApp $reserva, string $assinaturaBase64): void
    {
        // Motorista App checkout is completed on form submission. No separate finalization step needed.
    }
}
