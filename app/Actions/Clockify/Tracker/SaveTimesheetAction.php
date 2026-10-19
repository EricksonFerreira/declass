<?php

namespace App\Actions\Clockify\Tracker;

use App\Models\ClockifyTimeTracker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SaveTimesheetAction
{
    public function handle(array $hoursInput, Carbon $startOfWeek, ?int $userId = null): void
    {
        $userId ??= Auth::id();

        foreach ($hoursInput as $projId => $days) {
            $projId = (int) $projId === 0 ? null : (int) $projId;

            foreach ($days as $dayIndex => $hours) {
                $targetDate = (clone $startOfWeek)->addDays((int) $dayIndex);

                ClockifyTimeTracker::where('user_id', $userId)
                    ->where('descricao', 'Lançado via Timesheet')
                    ->whereDate('inicio', $targetDate->toDateString())
                    ->when(
                        $projId === null,
                        fn ($q) => $q->whereNull('clockify_projeto_id'),
                        fn ($q) => $q->where('clockify_projeto_id', $projId)
                    )
                    ->delete();

                if ($hours === null || $hours === '' || (float) $hours <= 0) {
                    continue;
                }

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
