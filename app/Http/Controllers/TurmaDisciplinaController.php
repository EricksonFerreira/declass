<?php

namespace App\Http\Controllers;

use App\Http\Requests\Turma\StoreTurmaDisciplinaRequest;
use App\Http\Requests\Turma\UpdateTurmaDisciplinaRequest;
use App\Models\Turma;
use App\Services\TurmaService;

/**
 * Gestao das disciplinas ofertadas por uma turma (pivot
 * turma_disciplina). Sempre opera dentro do contexto da turma.
 */
class TurmaDisciplinaController extends BaseController
{
    public function __construct(
        protected TurmaService $turmaService,
    ) {
        $this->middleware('can:turma.disciplina.store')->only('store');
        $this->middleware('can:turma.disciplina.update')->only('update');
        $this->middleware('can:turma.disciplina.destroy')->only('destroy');
    }

    public function store(StoreTurmaDisciplinaRequest $request, $turmaId)
    {
        $turma = $this->turmaOu404($turmaId);
        $data = $request->validated();

        $turma->disciplinas()->syncWithoutDetaching([
            $data['disciplina_id'] => [
                'carga_horaria_semanal' => $data['carga_horaria_semanal'] ?? null,
            ],
        ]);

        return redirect()->route('turma.show', $turma->id)
            ->with('success', 'Disciplina ofertada com sucesso.');
    }

    public function update(UpdateTurmaDisciplinaRequest $request, $turmaId, $disciplinaId)
    {
        $turma = $this->turmaOu404($turmaId);

        abort_if(! $turma->disciplinas()->where('disciplinas.id', $disciplinaId)->exists(), 404, 'Oferta não encontrada.');

        $turma->disciplinas()->updateExistingPivot($disciplinaId, [
            'carga_horaria_semanal' => $request->validated()['carga_horaria_semanal'] ?? null,
        ]);

        return redirect()->route('turma.show', $turma->id)
            ->with('success', 'Oferta atualizada com sucesso.');
    }

    public function destroy($turmaId, $disciplinaId)
    {
        $turma = $this->turmaOu404($turmaId);

        $turma->disciplinas()->detach($disciplinaId);

        return redirect()->route('turma.show', $turma->id)
            ->with('success', 'Disciplina removida da turma.');
    }

    private function turmaOu404($id): Turma
    {
        $turma = $this->turmaService->getById($id);
        abort_if(! $turma, 404, 'Turma não encontrada.');

        return $turma;
    }
}
