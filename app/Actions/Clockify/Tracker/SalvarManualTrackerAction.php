<?php

namespace App\Actions\Clockify\Tracker;

use App\Actions\Clockify\Tag\TagsPorProjetoAction;
use App\Models\ClockifyTimeTracker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SalvarManualTrackerAction
{
    public function handle(array $data, ?array $tagIds): ClockifyTimeTracker
    {
        $tracker = ClockifyTimeTracker::create([
            'user_id' => Auth::id(),
            'clockify_projeto_id' => $data['clockify_projeto_id'] ?? null,
            'descricao' => $data['descricao'] ?? null,
            'inicio' => Carbon::parse($data['inicio']),
            'fim' => Carbon::parse($data['fim']),
            'faturavel' => $data['faturavel'] ?? false,
        ]);

        $tagIds = app(TagsPorProjetoAction::class)
            ->filtrar($tagIds, $tracker->clockify_projeto_id ? (int) $tracker->clockify_projeto_id : null);

        if (!empty($tagIds)) {
            $tracker->tags()->sync($tagIds);
        }

        return $tracker;
    }
}
