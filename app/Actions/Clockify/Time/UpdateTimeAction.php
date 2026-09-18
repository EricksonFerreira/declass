<?php

namespace App\Actions\Clockify\Time;

use App\Models\ClockifyTime;
use App\Services\VisibilidadeService;

class UpdateTimeAction
{
    public function handle(int $id, array $data, ?array $userIds): ClockifyTime
    {
        $time = ClockifyTime::findOrFail($id);
        $time->update($data);

        $time->users()->sync($userIds ?? []);

        app(VisibilidadeService::class)->promoverLiderDeTime($time->lider_id);

        return $time;
    }
}
