<?php

namespace App\Actions\Clockify\Cliente;

use App\Models\ClockifyCliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListarClientesAction
{
    public function handle(?string $search): LengthAwarePaginator
    {
        return ClockifyCliente::query()
            ->when($search, function ($query, $search) {
                $query->where('nome', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('nome')
            ->paginate(15);
    }
}
