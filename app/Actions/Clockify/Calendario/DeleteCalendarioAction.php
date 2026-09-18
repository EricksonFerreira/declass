<?php

namespace App\Actions\Clockify\Calendario;

use App\Models\ClockifyCalendario;

class DeleteCalendarioAction
{
    public function handle(int $id): bool
    {
        $alocacao = ClockifyCalendario::where('empresa_id', auth()->user()->empresa_id)->findOrFail($id);

        abort_unless(
            (int) $alocacao->user_id === (int) auth()->id() || auth()->user()->podeGerenciarUsuario($alocacao->user_id),
            403,
            'Você só pode remover alocações próprias ou da sua equipe.'
        );

        return $alocacao->delete();
    }
}
