<?php

namespace App\Actions\Clockify\Tracker;

use App\Actions\Clockify\Tag\TagsPorProjetoAction;
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

        $tagsPorProjeto = app(TagsPorProjetoAction::class);
        if ($tagsPorProjeto->ativo()) {
            // Projeto trocado sem novas tags: remove tags que nao pertencem
            // ao novo projeto (mantem as globais).
            $tagIds ??= $tracker->tags()->pluck('clockify_tags.id')->all();
            $tagIds = $tagsPorProjeto->filtrar(
                $tagIds,
                $tracker->clockify_projeto_id ? (int) $tracker->clockify_projeto_id : null
            );
        }

        if ($tagIds !== null) {
            $tracker->tags()->sync($tagIds);
        }

        return $tracker;
    }
}
