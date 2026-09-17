<?php

namespace App\Actions\Clockify\TimeOff;

use App\Models\ClockifyTimeOff;

class UpdateTimeOffAction
{
    public function handle(int $id, array $data): ClockifyTimeOff
    {
        $timeoff = ClockifyTimeOff::findOrFail($id);
        $timeoff->update($data);

        return $timeoff;
    }
}
