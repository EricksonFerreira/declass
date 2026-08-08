<?php

namespace App\Actions\Clockify\Projeto;

use App\Models\ClockifyProjeto;

class UpdateProjetoAction
{
    public function handle(int $id, array $data): ClockifyProjeto
    {
        $projeto = ClockifyProjeto::findOrFail($id);
        $projeto->update($data);
        return $projeto;
    }
}
