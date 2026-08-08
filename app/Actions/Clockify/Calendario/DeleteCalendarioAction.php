<?php

namespace App\Actions\Clockify\Calendario;

use App\Models\ClockifyCalendario;

class DeleteCalendarioAction
{
    public function handle(int $id): bool
    {
        $alocacao = ClockifyCalendario::findOrFail($id);
        return $alocacao->delete();
    }
}
