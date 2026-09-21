<?php

namespace App\Actions\Clockify\Funcao;

use App\Models\Funcao;
use App\Support\EmpresaContext;
use App\Support\FiltroQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListarFuncoesAction
{
    public function handle(?string $search, array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Funcao::query()
            ->where('empresa_id', app(EmpresaContext::class)->empresaId() ?? auth()->user()->empresa_id)
            ->withCount('users')
            ->when($search, function ($query, $search) {
                $query->where('nome', 'like', "%{$search}%");
            });

        FiltroQuery::aplicar($query, $filtros, ['nome', 'ativo']);

        return $query->orderBy('nome')->paginate($perPage);
    }
}
