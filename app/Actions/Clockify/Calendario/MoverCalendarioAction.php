<?php

namespace App\Actions\Clockify\Calendario;

use App\Models\ClockifyCalendario;
use App\Models\ClockifyTimeTracker;

class MoverCalendarioAction
{
    public function handle(int $id, string $type, array $data): void
    {
        $empresaId = auth()->user()->empresa_id;

        if ($type === 'tracker') {
            $tracker = ClockifyTimeTracker::where('empresa_id', $empresaId)->findOrFail($id);

            // Apontamento de horas so e movido pelo proprio dono ou admin —
            // lider nao altera horas ja registradas por terceiros.
            abort_unless(
                (int) $tracker->user_id === (int) auth()->id() || auth()->user()->isAdministrador(),
                403,
                'Você só pode mover os seus próprios apontamentos.'
            );

            $tracker->update([
                'inicio' => $data['inicio'],
                'fim' => $data['fim'],
            ]);

            return;
        }

        $alocacao = ClockifyCalendario::where('empresa_id', $empresaId)->findOrFail($id);

        abort_unless(
            (int) $alocacao->user_id === (int) auth()->id() || auth()->user()->podeGerenciarUsuario($alocacao->user_id),
            403,
            'Você só pode mover alocações próprias ou da sua equipe.'
        );

        $alocacao->update([
            'data_inicio' => $data['inicio'],
            'data_fim' => $data['fim'],
        ]);
    }
}
