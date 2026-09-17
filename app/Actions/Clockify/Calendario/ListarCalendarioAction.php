<?php

namespace App\Actions\Clockify\Calendario;

use App\Models\ClockifyCalendario;
use Illuminate\Database\Eloquent\Collection;

class ListarCalendarioAction
{
    public function handle(): Collection
    {
        return ClockifyCalendario::with(['user', 'projeto'])
            ->where('empresa_id', auth()->user()->empresa_id)
            ->get();
    }
}
