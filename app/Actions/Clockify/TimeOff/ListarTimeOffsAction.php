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

        // Escopo de visibilidade: admin ve tudo; lider ve sua equipe;
        // colaborador ve apenas as proprias solicitacoes.
        $idsVisiveis = Auth::user()->idsVisiveis();
        if ($idsVisiveis !== null) {
            $query->whereIn('user_id', $idsVisiveis);
        }

        FiltroQuery::aplicar($query, $filtros, ['user_id', 'tipo', 'status']);

        // Um colaborador nao pode burlar o escopo filtrando por user_id de terceiros.
        if ($idsVisiveis !== null && ! empty($filtros['user_id'])) {
            $query->whereIn('user_id', $idsVisiveis);
        }

        return $query->orderBy('data_inicio', 'desc')->paginate($perPage);
    }
}
