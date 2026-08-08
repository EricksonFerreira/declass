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
            $updateData = [];
            
            if (array_key_exists('descricao', $data)) {
                $updateData['descricao'] = $data['descricao'];
            }
            if (array_key_exists('clockify_projeto_id', $data)) {
                $updateData['clockify_projeto_id'] = !empty($data['clockify_projeto_id']) ? $data['clockify_projeto_id'] : null;
            }
            
            if (!empty($updateData)) {
                $tracker->update($updateData);
            }

            if ($tagIds !== null) {
                $tracker->tags()->sync($tagIds);
            }
        }

        return $tracker;
    }
}
