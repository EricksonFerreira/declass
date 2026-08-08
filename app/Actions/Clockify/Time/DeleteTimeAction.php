<?php

namespace App\Actions\Clockify\Time;

use App\Models\ClockifyTime;

class DeleteTimeAction
{
    public function handle(int $id): bool
    {
        $time = ClockifyTime::findOrFail($id);
        $time->users()->detach();
        return $time->delete();
    }
}
