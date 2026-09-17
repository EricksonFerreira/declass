<?php

namespace App\Actions\Clockify\Funcao;

use App\Models\Funcao;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListarFuncoesAction
{
    public function handle(?string $search): LengthAwarePaginator
    {
        return Funcao::query()
            ->where('empresa_id', auth()->user()->empresa_id)
            ->withCount('users')
            ->when($search, function ($query, $search) {
                $query->where('nome', 'like', "%{$search}%");
            })
            ->orderBy('nome')
            ->paginate(15);
    }
}
