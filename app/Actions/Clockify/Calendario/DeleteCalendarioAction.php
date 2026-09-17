<?php

namespace App\Actions\Clockify\Calendario;

use App\Models\ClockifyCalendario;

class DeleteCalendarioAction
{
    public function handle(int $id): bool
    {
        $alocacao = ClockifyCalendario::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        return $alocacao->delete();
    }
}
