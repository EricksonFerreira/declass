<?php

namespace App\Actions\Clockify\Tracker;

use App\Models\ClockifyTimeTracker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SaveTimesheetAction
{
    public function handle(array $hoursInput, Carbon $startOfWeek): void
    {
        $userId = Auth::id();

        foreach ($hoursInput as $projId => $days) {
            $projId = $projId === '0' ? null : (int) $projId;

            foreach ($days as $dayIndex => $hours) {
                if ($hours === null || $hours === '' || (float) $hours <= 0) {
                    continue;
                }

                $targetDate = (clone $startOfWeek)->addDays((int) $dayIndex);
                $hoursFloat = (float) $hours;
                
                $startDateTime = (clone $targetDate)->hour(9)->minute(0);
                $minutes = ($hoursFloat - floor($hoursFloat)) * 60;
                $endDateTime = (clone $startDateTime)->addHours((int) $hoursFloat)->addMinutes((int) $minutes);

                ClockifyTimeTracker::create([
                    'user_id' => $userId,
                    'clockify_projeto_id' => $projId,
                    'descricao' => 'Lançado via Timesheet',
                    'inicio' => $startDateTime,
                    'fim' => $endDateTime,
                    'faturavel' => false,
                ]);
            }
        }
    }
}
