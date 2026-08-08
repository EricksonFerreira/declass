<?php

namespace App\Actions\Clockify\TimeOff;

use App\Models\ClockifyTimeOff;
use Illuminate\Support\Facades\Auth;

class DeleteTimeOffAction
{
    public function handle(int $id): bool
    {
        $timeoff = ClockifyTimeOff::findOrFail($id);
        
        if ($timeoff->user_id !== Auth::id() && !Auth::user()->can('gerenciar-empresa')) {
            abort(403, 'Ação não autorizada.');
        }

        return $timeoff->delete();
    }
}
