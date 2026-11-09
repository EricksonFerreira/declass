<?php

namespace App\Http\Controllers;

use App\Http\Requests\Turma\StoreTurmaDisciplinaRequest;
use App\Http\Requests\Turma\UpdateTurmaDisciplinaRequest;
use App\Models\Turma;
use App\Models\TurmaForumPost;
use App\Models\TurmaMaterial;
use App\Services\BreadcrumbService;
use App\Services\TurmaService;

/**
 * Gestao das disciplinas ofertadas por uma turma (pivot
 * turma_disciplina). Sempre opera dentro do contexto da turma.
 * O show e a "sala" da disciplina no admin: forum e materiais
 * daquela disciplina para o professor/coordenacao.
 */
class TurmaDisciplinaController extends BaseController
{
    public function __construct(
        protected TurmaService $turmaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->middleware('can:turma.show')->only('show');
        $this->middleware('can:turma.disciplina.store')->only('store');
        $this->middleware('can:turma.disciplina.update')->only('update');
        $this->middleware('can:turma.disciplina.destroy')->only('destroy');
    }

    /**
     * Pagina da disciplina dentro da turma (admin): forum de
     * orientacoes/respostas e materiais da disciplina. Professor
     * so entra nas disciplinas que leciona (regente ve todas).
     */
    public function show($turmaId, $disciplinaId)
    {
        $turma = $this->turmaService->getById($turmaId, ['disciplinas', 'escola', 'anoLetivo', 'etapa']);
        abort_if(! $turma, 404, 'Turma não encontrada.');
        abort_if(! $this->turmaService->podeAcessar($turma), 403, 'Sem acesso a esta turma.');

        $disciplina = $turma->disciplinas->firstWhere('id', (int) $disciplinaId);
        abort_unless($disciplina, 404, 'Disciplina não ofertada por esta turma.');

        $visiveis = $this->turmaService->disciplinasVisiveisNaTurma($turma, auth()->user());
        abort_unless($visiveis->contains('id', $disciplina->id), 403,
            'Disciplina não atribuída a você nesta turma.');

        $posts = TurmaForumPost::query()
            ->where('turma_id', $turma->id)
            ->where('disciplina_id', $disciplina->id)
            ->whereNull('parent_id')
            ->with('autor:id,nome', 'respostas.autor:id,nome')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $materiais = TurmaMaterial::query()
            ->where('turma_id', $turma->id)
            ->where('disciplina_id', $disciplina->id)
            ->with('autor:id,nome')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCustom([
            ['Home', route('home')],
            ['Turmas', route('turma.index')],
            [$turma->nome, route('turma.show', $turma->id)],
            [$disciplina->nome, null],
        ]);

        return view('turma.disciplina', compact('turma', 'disciplina', 'posts', 'materiais', 'breadcrumb'))
            ->with('titulo', $disciplina->nome.' — '.$turma->nome);
    }

    public function store(StoreTurmaDisciplinaRequest $request, $turmaId)
    {
        $turma = $this->turmaOu404($turmaId);
        $data = $request->validated();

        $turma->disciplinas()->syncWithoutDetaching([
            $data['disciplina_id'] => [
                'carga_horaria_semanal' => $data['carga_horaria_semanal'] ?? null,
                'media_aprovacao' => $data['media_aprovacao'] ?? null,
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
            'media_aprovacao' => $request->validated()['media_aprovacao'] ?? null,
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
