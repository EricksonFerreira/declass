<?php

namespace App\Actions\Clockify\Time;

use App\Models\ClockifyTime;
use App\Support\FiltroQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListarTimesAction
{
    public function handle(?string $search, array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ClockifyTime::with('users')
            ->when($search, function ($query, $search) {
                $query->where('nome', 'like', "%{$search}%");
            });

        FiltroQuery::aplicar($query, $filtros, ['nome', 'descricao']);

        return $query->orderBy('nome')->paginate($perPage);
    }
}
