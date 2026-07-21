<?php

namespace App\Actions\ReservaMotoristaApp;

use App\Models\ReservaMotoristaApp;

class CancelarMotoristaAppAction
{
    public function handle(ReservaMotoristaApp $reserva): ReservaMotoristaApp
    {
        $reserva->update([
            'status' => 'cancelada',
        ]);

        return $reserva;
    }
}
