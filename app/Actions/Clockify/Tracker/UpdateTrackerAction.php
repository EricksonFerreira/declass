<?php

namespace App\Actions\Clockify\Tracker;

use App\Models\ClockifyTimeTracker;

class UpdateTrackerAction
{
    public function handle(int $id, array $data, ?array $tagIds): ClockifyTimeTracker
    {
        $tracker = ClockifyTimeTracker::findOrFail($id);
        
        $updateData = [];
        
        if (array_key_exists('descricao', $data)) {
            $updateData['descricao'] = $data['descricao'];
        }
        if (array_key_exists('clockify_projeto_id', $data)) {
            $updateData['clockify_projeto_id'] = !empty($data['clockify_projeto_id']) ? $data['clockify_projeto_id'] : null;
        }
        if (array_key_exists('faturavel', $data)) {
            $updateData['faturavel'] = (bool) $data['faturavel'];
        }

        if (!empty($updateData)) {
            $tracker->update($updateData);
        }

        if ($tagIds !== null) {
            $tracker->tags()->sync($tagIds);
        }

        return $tracker;
    }
}
