<?php

namespace App\Actions\Clockify\Funcao;

use App\Models\Funcao;
use App\Support\EmpresaContext;

class UpdateFuncaoAction
{
    public function handle(int $id, array $data): Funcao
    {
        $funcao = Funcao::where('empresa_id', app(EmpresaContext::class)->empresaId() ?? auth()->user()->empresa_id)->findOrFail($id);
        $funcao->update($data);

        return $funcao;
    }
}
