<?php

namespace App\Actions\Clockify\Tag;

use App\Models\ClockifyTag;
use App\Models\Empresa;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\Auth;

/**
 * Modo "tags por projeto" da empresa ativa:
 * quando ligado, tags podem ser vinculadas a um projeto e o tracker so
 * aceita tags globais (sem projeto) ou do projeto selecionado.
 */
class TagsPorProjetoAction
{
    public function ativo(): bool
    {
        $empresaId = app(EmpresaContext::class)->empresaId() ?? Auth::user()?->empresa_id;

        if (! $empresaId) {
            return false;
        }

        return (bool) Empresa::query()->whereKey($empresaId)->value('tags_por_projeto');
    }

    /**
     * Mantem apenas tags globais ou do projeto informado.
     * Sem a flag ativa, retorna os ids intactos.
     */
    public function filtrar(?array $tagIds, ?int $projetoId): ?array
    {
        if ($tagIds === null || ! $this->ativo()) {
            return $tagIds;
        }

        return ClockifyTag::query()
            ->whereIn('id', $tagIds)
            ->where(function ($q) use ($projetoId) {
                $q->whereNull('clockify_projeto_id');

                if ($projetoId) {
                    $q->orWhere('clockify_projeto_id', $projetoId);
                }
            })
            ->pluck('id')
            ->all();
    }
}
