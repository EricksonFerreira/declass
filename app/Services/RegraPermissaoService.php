<?php

namespace App\Services;

use App\Models\Permissao;
use App\Models\Regra;
use App\Support\EmpresaContext;

class RegraPermissaoService
{
    public function atribuirPermissoesARole($regraId, array $permissionIds)
    {
        $empresaId = app(EmpresaContext::class)->empresaId();

        $regra = Regra::query()
            ->where('empresa_id', $empresaId)
            ->findOrFail($regraId);

        $permissoes = Permissao::query()
            ->where('empresa_id', $empresaId)
            ->whereIn('id', $permissionIds)
            ->get();

        $regra->permissoes()->sync($permissoes);
    }

    public function obterPermissoesPorRole($regraId)
    {
        $empresaId = app(EmpresaContext::class)->empresaId();
        $regra = Regra::query()
            ->where('empresa_id', $empresaId)
            ->findOrFail($regraId);

        return $regra->permissoes;
    }
}
