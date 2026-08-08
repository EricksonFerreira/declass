<?php

namespace App\Actions\Clockify\Tracker;

use App\Models\ClockifyTimeTracker;
use Illuminate\Support\Facades\Auth;

class UpdateActiveTrackerAction
{
    public function handle(array $data, ?array $tagIds): ?ClockifyTimeTracker
    {
        $userId = Auth::id();

        $tracker = ClockifyTimeTracker::where('user_id', $userId)
            ->whereNull('fim')
            ->first();

        if ($tracker) {
            $tracker->update([
                'descricao' => $data['descricao'] ?? null,
                'clockify_projeto_id' => $data['clockify_projeto_id'] ?? null,
            ]);

            if ($tagIds !== null) {
                $tracker->tags()->sync($tagIds);
            }
        }

        return $tracker;
    }
}
