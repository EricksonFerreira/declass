<?php

namespace App\Actions\Clockify\Calendario;

use App\Models\ClockifyCalendario;
use App\Models\ClockifyTimeTracker;

class MoverCalendarioAction
{
    public function handle(int $id, string $type, array $data): void
    {
        if ($type === 'tracker') {
            $tracker = ClockifyTimeTracker::findOrFail($id);
            $tracker->update([
                'inicio' => $data['inicio'],
                'fim' => $data['fim'],
            ]);

            return;
        }

        $alocacao = ClockifyCalendario::findOrFail($id);
        $alocacao->update([
            'data_inicio' => $data['inicio'],
            'data_fim' => $data['fim'],
        ]);
    }
}
