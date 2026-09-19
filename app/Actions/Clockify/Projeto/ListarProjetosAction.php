<?php

namespace App\Actions\Clockify\Projeto;

use App\Models\ClockifyProjeto;
use App\Models\User;
use App\Support\FiltroQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListarProjetosAction
{
    public function handle(?string $search, array $filtros = [], int $perPage = 15, ?User $user = null): LengthAwarePaginator
    {
        $query = ClockifyProjeto::with(['cliente', 'users:id'])
            ->when($user, function ($query, $user) {
                // Visibilidade: privado so aparece para admin, responsavel e membros.
                app(\App\Services\VisibilidadeService::class)->projetosVisiveis($user, $query);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nome', 'like', "%{$search}%")
                        ->orWhere('codigo', 'like', "%{$search}%")
                        ->when(is_numeric($search), fn ($n) => $n->orWhere('numero', (int) $search)->orWhere('ano', (int) $search));
                });
            });

        FiltroQuery::aplicar($query, $filtros, ['nome', 'clockify_cliente_id', 'ativo', 'status']);

        return $query->orderBy('nome')->paginate($perPage);
    }
}
