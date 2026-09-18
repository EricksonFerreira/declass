<?php

namespace App\Actions\Clockify\Tracker;

use App\Models\ClockifyTimeTracker;

class UpdateTrackerAction
{
    public function handle(int $id, array $data, ?array $tagIds): ClockifyTimeTracker
    {
        $tracker = ClockifyTimeTracker::findOrFail($id);

        // Apontamento de horas so e alterado pelo proprio dono ou admin —
        // lider nao edita horas ja registradas por terceiros.
        abort_unless(
            (int) $tracker->user_id === (int) auth()->id() || auth()->user()->isAdministrador(),
            403,
            'Você só pode editar os seus próprios apontamentos.'
        );

        $updateData = [];

        if (array_key_exists('descricao', $data)) {
            $updateData['descricao'] = $data['descricao'];
        }
        if (array_key_exists('clockify_projeto_id', $data)) {
            $updateData['clockify_projeto_id'] = ! empty($data['clockify_projeto_id']) ? $data['clockify_projeto_id'] : null;
        }
        if (array_key_exists('faturavel', $data)) {
            $updateData['faturavel'] = (bool) $data['faturavel'];
        }

        if (! empty($updateData)) {
            $tracker->update($updateData);
        }

        if ($tagIds !== null) {
            $tracker->tags()->sync($tagIds);
        }

        return $tracker;
    }
}
