<?php

namespace App\Actions\Clockify\Funcao;

use App\Models\Funcao;

class UpdateFuncaoAction
{
    public function handle(int $id, array $data): Funcao
    {
        $funcao = Funcao::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);
        $funcao->update($data);

        return $funcao;
    }
}
