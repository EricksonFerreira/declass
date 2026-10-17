<?php

namespace App\Actions\Clockify\Tracker;

use App\Actions\Clockify\Tag\TagsPorProjetoAction;
use App\Models\ClockifyTimeTracker;
use Illuminate\Support\Facades\Auth;

class IniciarTrackerAction
{
    public function handle(array $data, ?array $tagIds): ClockifyTimeTracker
    {
        $userId = Auth::id();

        // Stop any running tracker first
        ClockifyTimeTracker::where('user_id', $userId)
            ->whereNull('fim')
            ->update(['fim' => now()]);

        $tracker = ClockifyTimeTracker::create(array_merge($data, [
            'user_id' => $userId,
            'inicio' => now(),
        ]));

        $tagIds = app(TagsPorProjetoAction::class)
            ->filtrar($tagIds, $tracker->clockify_projeto_id ? (int) $tracker->clockify_projeto_id : null);

        if (!empty($tagIds)) {
            $tracker->tags()->sync($tagIds);
        }

        return $tracker;
    }
}
