<?php

namespace App\Actions\Clockify\TimeOff;

use App\Models\ClockifyTimeOff;

class AprovarTimeOffAction
{
    public function handle(int $id): bool
    {
        $timeoff = ClockifyTimeOff::findOrFail($id);
        return $timeoff->update(['status' => 'aprovado']);
    }
}
