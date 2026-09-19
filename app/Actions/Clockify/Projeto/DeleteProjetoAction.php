<?php

namespace App\Actions\Clockify\Projeto;

use App\Models\ClockifyProjeto;

class DeleteProjetoAction
{
    public function handle(int $id): bool
    {
        $projeto = ClockifyProjeto::findOrFail($id);

        return $projeto->delete();
    }
}
