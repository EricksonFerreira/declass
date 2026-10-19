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

        // Escopo: admin ou quem tem escopo.todos ve todos os times; demais
        // veem apenas os times que lideram ou dos quais sao membros.
        // Empresas com visao ampla (visao_restrita = false) liberam todos.
        $user = \Illuminate\Support\Facades\Auth::user();
        $visaoAmpla = ! app(\App\Services\VisibilidadeService::class)->visaoRestrita($user)
            || $user->hasPermissao('escopo.todos');
        if (! $user->isAdministrador() && ! $visaoAmpla) {
            $query->where(function ($q) use ($user) {
                $q->where('lider_id', $user->id)
                    ->orWhereHas('users', fn ($u) => $u->where('users.id', $user->id));
            });
        }

        FiltroQuery::aplicar($query, $filtros, ['nome', 'descricao']);

        return $query->orderBy('nome')->paginate($perPage);
    }
}
