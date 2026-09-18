<?php

namespace App\Actions\Clockify\TimeOff;

use App\Models\ClockifyTimeOff;
use App\Support\FiltroQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ListarTimeOffsAction
{
    public function handle(array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ClockifyTimeOff::with('user');

        if (!Auth::user()->can('gerenciar-empresa')) {
            $query->where('user_id', Auth::id());
        }

        FiltroQuery::aplicar($query, $filtros, ['user_id', 'tipo', 'status']);

        return $query->orderBy('data_inicio', 'desc')->paginate($perPage);
    }
}
