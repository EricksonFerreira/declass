<?php

namespace App\Actions\Clockify\Projeto;

use App\Models\ClockifyProjeto;

class SaveProjetoAction
{
    public function handle(array $data): ClockifyProjeto
    {
        return ClockifyProjeto::create(array_merge($data, [
            'ativo' => true,
        ]));
    }
}
