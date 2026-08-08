<?php

namespace App\Actions\Clockify\Cliente;

use App\Models\ClockifyCliente;

class SaveClienteAction
{
    public function handle(array $data): ClockifyCliente
    {
        return ClockifyCliente::create(array_merge($data, ['ativo' => true]));
    }
}
