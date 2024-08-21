<?php

namespace App\Services;

use App\Models\Permissao;
use App\Models\Regra;

class RegraPermissaoService
{
    public function atribuirPermissoesARole($regraId, array $permissionIds)
    {
        $regra = Regra::findOrFail($regraId);
        $permissoes = Permissao::whereIn('id', $permissionIds)->get();

        $regra->permissoes()->sync($permissoes);
    }

    public function obterPermissoesPorRole($regraId)
    {
        $regra = Regra::findOrFail($regraId);
        return $regra->permissoes;
    }
}
