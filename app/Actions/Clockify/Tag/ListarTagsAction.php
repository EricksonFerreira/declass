<?php

namespace App\Actions\Clockify\Tag;

use App\Models\ClockifyTag;
use App\Support\FiltroQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListarTagsAction
{
    public function handle(?string $search, array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ClockifyTag::query()
            ->with('projeto')
            ->when($search, function ($query, $search) {
                $query->where('nome', 'like', "%{$search}%");
            });

        FiltroQuery::aplicar($query, $filtros, ['nome', 'ativo']);

        return $query->orderBy('nome')->paginate($perPage);
    }
}
