<?php

namespace App\Actions\Licitacao;

use App\Models\Licitacao;

class SaveLicitacaoAction
{
    public function handle(array $data): Licitacao
    {
        return Licitacao::create(array_merge($data, [
            'status' => 'cadastrada',
            'usuinc' => auth()->id(),
            'datainc' => now(),
        ]));
    }
}
