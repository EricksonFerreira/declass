<?php

namespace App\Actions\Clockify\Cliente;

use App\Models\ClockifyCliente;

class DeleteClienteAction
{
    public function handle(int $id): bool
    {
        $cliente = ClockifyCliente::findOrFail($id);
        return $cliente->delete();
    }
}
