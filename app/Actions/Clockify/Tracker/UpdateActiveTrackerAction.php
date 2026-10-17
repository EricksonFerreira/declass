<?php

namespace App\Actions\Clockify\Tracker;

use App\Actions\Clockify\Tag\TagsPorProjetoAction;
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

            $tagsPorProjeto = app(TagsPorProjetoAction::class);
            if ($tagsPorProjeto->ativo()) {
                $tagIds ??= $tracker->tags()->pluck('clockify_tags.id')->all();
                $tagIds = $tagsPorProjeto->filtrar(
                    $tagIds,
                    $tracker->clockify_projeto_id ? (int) $tracker->clockify_projeto_id : null
                );
            }

            if ($tagIds !== null) {
                $tracker->tags()->sync($tagIds);
            }
        }

        return $tracker;
    }
}
