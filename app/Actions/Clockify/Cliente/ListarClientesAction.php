<?php

namespace App\Actions\Clockify\Cliente;

use App\Models\ClockifyCliente;
use App\Support\FiltroQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListarClientesAction
{
    public function handle(?string $search, array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ClockifyCliente::query()
            ->when($search, function ($query, $search) {
                $query->where('nome', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });

        FiltroQuery::aplicar($query, $filtros, ['nome', 'email', 'telefone', 'ativo']);

        return $query->orderBy('nome')->paginate($perPage);
    }
}
