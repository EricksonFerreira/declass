<?php

namespace App\Services;

use App\Models\Permissao;
use App\Models\Regra;

class RegraPermissaoService
{
    public function atribuirPermissoesARole($regraId, array $permissionIds)
    {
        $regra = Regra::query()
            ->findOrFail($regraId);

        // Permissoes internas nao sao tratadas por perfil — sao
        // descartadas mesmo que cheguem no payload.
        $permissoes = Permissao::query()
            ->whereIn('id', $permissionIds)
            ->get()
            ->reject(fn (Permissao $p) => Permissao::isNomeInterno($p->name));

        $regra->permissoes()->sync($permissoes);
    }

    public function obterPermissoesPorRole($regraId)
    {
        $regra = Regra::query()
            ->findOrFail($regraId);

        return $regra->permissoes;
    }
}
