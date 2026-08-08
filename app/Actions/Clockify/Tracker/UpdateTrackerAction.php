<?php

namespace App\Actions\Clockify\Tracker;

use App\Models\ClockifyTimeTracker;

class UpdateTrackerAction
{
    public function handle(int $id, array $data, ?array $tagIds): ClockifyTimeTracker
    {
        $tracker = ClockifyTimeTracker::findOrFail($id);
        
        $tracker->update([
            'descricao' => $data['descricao'] ?? null,
            'clockify_projeto_id' => $data['clockify_projeto_id'] ?? null,
            'faturavel' => $data['faturavel'] ?? false,
        ]);

        $tracker->tags()->sync($tagIds ?? []);

        return $tracker;
    }
}
