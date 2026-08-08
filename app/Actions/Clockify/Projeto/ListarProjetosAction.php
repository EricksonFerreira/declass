<?php

namespace App\Actions\Clockify\Projeto;

use App\Models\ClockifyProjeto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListarProjetosAction
{
    public function handle(?string $search): LengthAwarePaginator
    {
        return ClockifyProjeto::with('cliente')
            ->when($search, function ($query, $search) {
                $query->where('nome', 'like', "%{$search}%");
            })
            ->orderBy('nome')
            ->paginate(15);
    }
}
