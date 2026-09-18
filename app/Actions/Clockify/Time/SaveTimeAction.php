<?php

namespace App\Actions\Clockify\Time;

use App\Models\ClockifyTime;
use App\Services\VisibilidadeService;

class SaveTimeAction
{
    public function handle(array $data, ?array $userIds): ClockifyTime
    {
        $time = ClockifyTime::create($data);

        if (! empty($userIds)) {
            $time->users()->sync($userIds);
        }

        app(VisibilidadeService::class)->promoverLiderDeTime($time->lider_id);

        return $time;
    }
}
