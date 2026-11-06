<?php

namespace App\Http\Controllers;

use App\Http\Requests\Turma\StoreAtribuicaoRequest;
use App\Http\Requests\Turma\UpdateAtribuicaoRequest;
use App\Models\Atribuicao;
use App\Models\Turma;
use App\Services\AtribuicaoService;
use App\Services\TurmaService;

/**
 * Gestao das atribuicoes de professores de uma turma.
 */
class AtribuicaoController extends BaseController
{
    public function __construct(
        protected TurmaService $turmaService,
        protected AtribuicaoService $atribuicaoService,
    ) {
        $this->middleware('can:turma.atribuicao.store')->only('store');
        $this->middleware('can:turma.atribuicao.update')->only('update');
        $this->middleware('can:turma.atribuicao.destroy')->only('destroy');
    }

    public function store(StoreAtribuicaoRequest $request, $turmaId)
    {
        $turma = $this->turmaOu404($turmaId);

        $this->atribuicaoService->create($request->validated() + ['turma_id' => $turma->id]);

        return redirect()->route('turma.show', $turma->id)
            ->with('success', 'Professor atribuído com sucesso.');
    }

    public function update(UpdateAtribuicaoRequest $request, $turmaId, $atribuicaoId)
    {
        $turma = $this->turmaOu404($turmaId);
        $atribuicao = $this->atribuicaoDaTurma($turma, $atribuicaoId);

        $this->atribuicaoService->update($atribuicao->id, $request->validated());

        return redirect()->route('turma.show', $turma->id)
            ->with('success', 'Atribuição atualizada com sucesso.');
    }

    public function destroy($turmaId, $atribuicaoId)
    {
        $turma = $this->turmaOu404($turmaId);
        $atribuicao = $this->atribuicaoDaTurma($turma, $atribuicaoId);

        $this->atribuicaoService->delete($atribuicao->id);

        return redirect()->route('turma.show', $turma->id)
            ->with('success', 'Atribuição removida com sucesso.');
    }

    private function turmaOu404($id): Turma
    {
        $turma = $this->turmaService->getById($id);
        abort_if(! $turma, 404, 'Turma não encontrada.');

        return $turma;
    }

    private function atribuicaoDaTurma(Turma $turma, $atribuicaoId): Atribuicao
    {
        $atribuicao = Atribuicao::query()
            ->where('turma_id', $turma->id)
            ->find($atribuicaoId);

        abort_if(! $atribuicao, 404, 'Atribuição não encontrada.');

        return $atribuicao;
    }
}
