<?php

namespace App\Actions\Clockify\Cliente;

use App\Models\ClockifyCliente;

class UpdateClienteAction
{
    public function handle(int $id, array $data): ClockifyCliente
    {
        $cliente = ClockifyCliente::findOrFail($id);
        $cliente->update($data);
        return $cliente;
    }
}
