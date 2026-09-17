<?php

namespace App\Actions\Clockify\Calendario;

use App\Models\ClockifyCalendario;

class SaveCalendarioAction
{
    public function handle(array $data): ClockifyCalendario
    {
        $data['empresa_id'] = auth()->user()->empresa_id;

        return ClockifyCalendario::create($data);
    }
}
