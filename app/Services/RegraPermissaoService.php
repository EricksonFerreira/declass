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

        $permissoes = Permissao::query()
            ->whereIn('id', $permissionIds)
            ->get();

        $regra->permissoes()->sync($permissoes);
    }

    public function obterPermissoesPorRole($regraId)
    {
        $regra = Regra::query()
            ->findOrFail($regraId);

        return $regra->permissoes;
    }
}
