<?php

namespace App\Actions\Clockify\Time;

use App\Models\ClockifyTime;

class SaveTimeAction
{
    public function handle(array $data, ?array $userIds): ClockifyTime
    {
        $time = ClockifyTime::create($data);

        if (!empty($userIds)) {
            $time->users()->sync($userIds);
        }

        return $time;
    }
}
