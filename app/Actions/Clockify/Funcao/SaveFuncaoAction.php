<?php

namespace App\Actions\Clockify\Funcao;

use App\Models\Funcao;

class SaveFuncaoAction
{
    public function handle(array $data): Funcao
    {
        return Funcao::create(array_merge($data, [
            'empresa_id' => auth()->user()->empresa_id,
            'ativo' => true,
        ]));
    }
}
