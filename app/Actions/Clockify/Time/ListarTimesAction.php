<?php

namespace App\Actions\Clockify\Time;

use App\Models\ClockifyTime;
use App\Support\FiltroQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListarTimesAction
{
    public function handle(?string $search, array $filtros = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ClockifyTime::with(['users', 'lider'])
            ->when($search, function ($query, $search) {
                $query->where('nome', 'like', "%{$search}%");
            });

        // Escopo: admin ve todos os times; demais veem apenas os times
        // que lideram ou dos quais sao membros.
        $user = \Illuminate\Support\Facades\Auth::user();
        if (! $user->isAdministrador()) {
            $query->where(function ($q) use ($user) {
                $q->where('lider_id', $user->id)
                    ->orWhereHas('users', fn ($u) => $u->where('users.id', $user->id));
            });
        }

        FiltroQuery::aplicar($query, $filtros, ['nome', 'descricao']);

        return $query->orderBy('nome')->paginate($perPage);
    }
}
