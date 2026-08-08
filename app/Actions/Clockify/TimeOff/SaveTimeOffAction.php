<?php

namespace App\Actions\Clockify\TimeOff;

use App\Models\ClockifyTimeOff;

class SaveTimeOffAction
{
    public function handle(array $data): ClockifyTimeOff
    {
        return ClockifyTimeOff::create(array_merge($data, [
            'status' => 'pendente',
        ]));
    }
}
