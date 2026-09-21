<?php

namespace App\Actions\Clockify\Funcao;

use App\Models\Funcao;
use App\Support\EmpresaContext;

class SaveFuncaoAction
{
    public function handle(array $data): Funcao
    {
        return Funcao::create(array_merge($data, [
            'empresa_id' => app(EmpresaContext::class)->empresaId() ?? auth()->user()->empresa_id,
            'ativo' => true,
        ]));
    }
}
