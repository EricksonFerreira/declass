<?php

namespace App\Actions\Clockify\Funcao;

use App\Models\Funcao;
use App\Support\EmpresaContext;

class DeleteFuncaoAction
{
    public function handle(int $id): bool
    {
        $funcao = Funcao::where('empresa_id', app(EmpresaContext::class)->empresaId() ?? auth()->user()->empresa_id)->findOrFail($id);

        return $funcao->delete();
    }
}
