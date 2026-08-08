<?php

namespace App\Actions\Clockify\Tracker;

use App\Models\ClockifyTimeTracker;
use Illuminate\Support\Facades\Auth;

class PararTrackerAction
{
    public function handle(?string $descricao, ?int $projetoId, ?array $tagIds): ?ClockifyTimeTracker
    {
        $userId = Auth::id();

        $tracker = ClockifyTimeTracker::where('user_id', $userId)
            ->whereNull('fim')
            ->first();

        if ($tracker) {
            $tracker->update([
                'fim' => now(),
                'descricao' => $descricao,
                'clockify_projeto_id' => $projetoId,
            ]);

            if ($tagIds !== null) {
                $tracker->tags()->sync($tagIds);
            }
        }

        return $tracker;
    }
}
