<?php

namespace App\Actions\Licitacao;

use App\Models\Licitacao;

class DeleteLicitacaoAction
{
    public function handle(int $id): void
    {
        Licitacao::findOrFail($id)->delete();
    }
}
