<?php

namespace App\Actions\Clockify\TimeOff;

use App\Models\ClockifyTimeOff;
use Illuminate\Support\Facades\Auth;

class DeleteTimeOffAction
{
    public function handle(int $id): bool
    {
        $timeoff = ClockifyTimeOff::findOrFail($id);

        if ((int) $timeoff->user_id !== (int) Auth::id() && ! Auth::user()->podeGerenciarUsuario($timeoff->user_id)) {
            abort(403, 'Ação não autorizada.');
        }

        return $timeoff->delete();
    }
}
