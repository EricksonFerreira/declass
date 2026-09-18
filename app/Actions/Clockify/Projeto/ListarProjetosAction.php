<?php

namespace App\Actions\Clockify\Projeto;

use App\Models\ClockifyProjeto;
use App\Support\FiltroQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListarProjetosAction
{
    public function handle(?string $search, array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ClockifyProjeto::with('cliente')
            ->when($search, function ($query, $search) {
                $query->where('nome', 'like', "%{$search}%");
            });

        FiltroQuery::aplicar($query, $filtros, ['nome', 'clockify_cliente_id', 'ativo', 'status']);

        return $query->orderBy('nome')->paginate($perPage);
    }
}
