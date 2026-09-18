<?php

namespace App\Actions\Clockify\Calendario;

use App\Models\ClockifyCalendario;
use Illuminate\Database\Eloquent\Collection;

class ListarCalendarioAction
{
    public function handle(): Collection
    {
        // Escopo de visibilidade: admin ve todas as alocacoes; lider as
        // da sua equipe; colaborador apenas as proprias.
        $idsVisiveis = auth()->user()->idsVisiveis();

        return ClockifyCalendario::with(['user', 'projeto'])
            ->where('empresa_id', auth()->user()->empresa_id)
            ->when($idsVisiveis !== null, fn ($q) => $q->whereIn('user_id', $idsVisiveis))
            ->get();
    }
}
