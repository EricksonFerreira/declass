<?php

namespace App\Actions\Clockify\Calendario;

use App\Models\ClockifyCalendario;

class SaveCalendarioAction
{
    public function handle(array $data): ClockifyCalendario
    {
        return ClockifyCalendario::create($data);
    }
}
