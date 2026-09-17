<?php

namespace App\Actions\Clockify\Funcao;

use App\Models\Funcao;

class DeleteFuncaoAction
{
    public function handle(int $id): bool
    {
        $funcao = Funcao::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        return $funcao->delete();
    }
}
