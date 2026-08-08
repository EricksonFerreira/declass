<?php

namespace App\Actions\Clockify\Tracker;

use App\Models\ClockifyTimeTracker;
use Illuminate\Support\Facades\Auth;

class DeleteTrackerAction
{
    public function handle(int $id): bool
    {
        $tracker = ClockifyTimeTracker::findOrFail($id);

        if ($tracker->user_id !== Auth::id()) {
            abort(403);
        }

        $tracker->tags()->detach();
        return $tracker->delete();
    }
}
