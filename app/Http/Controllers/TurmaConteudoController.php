<?php

namespace App\Http\Controllers;

use App\Models\Disciplina;
use App\Models\Turma;
use App\Models\TurmaForumPost;
use App\Models\TurmaMaterial;
use App\Services\TurmaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Conteudo da turma postado por professor/coordenacao: materiais
 * (aba "Materiais" da turma) e o forum/orientacoes por disciplina
 * (tela da disciplina). Acesso = podeAcessar(turma); professor so
 * posta nas disciplinas que leciona (disciplinasVisiveisNaTurma).
 */
class TurmaConteudoController extends BaseController
{
    public function __construct(
        protected TurmaService $turmaService,
    ) {
        $this->middleware('can:turma.show');
    }

    /** Publica material de apoio (arquivo ou link) para a turma. */
    public function materialStore(Request $request, $turmaId)
    {
        $turma = $this->turmaAutorizada($turmaId);

        $dados = $request->validate($this->regrasMaterial());

        if ($dados['disciplina_id'] ?? null) {
            abort_unless($turma->disciplinas->contains('id', (int) $dados['disciplina_id']), 422,
                'Disciplina não ofertada por esta turma.');
        }

        $this->criarMaterial($request, $turma, $dados['disciplina_id'] ?? null);

        return redirect()->route('turma.show', $turma->id)
            ->withFragment('tab-materiais')
            ->with('success', 'Material publicado para a turma.');
    }

    /** Publica material ja dentro do contexto da disciplina. */
    public function disciplinaMaterialStore(Request $request, $turmaId, $disciplinaId)
    {
        $turma = $this->turmaAutorizada($turmaId);
        $disciplina = $this->disciplinaAutorizada($turma, $disciplinaId);

        $request->validate($this->regrasMaterial(true));

        $this->criarMaterial($request, $turma, $disciplina->id);

        return redirect()->route('turma.disciplina.show', [$turma->id, $disciplina->id])
            ->withFragment('tab-materiais')
            ->with('success', 'Material publicado na disciplina.');
    }

    /** Remove material (professor da turma ou coordenacao). */
    public function materialDestroy($turmaId, $materialId)
    {
        $turma = $this->turmaAutorizada($turmaId);
        $this->removerMaterial($turma, $materialId);

        return redirect()->route('turma.show', $turma->id)
            ->withFragment('tab-materiais')
            ->with('success', 'Material removido.');
    }

    /** Remove material voltando para a tela da disciplina. */
    public function disciplinaMaterialDestroy($turmaId, $disciplinaId, $materialId)
    {
        $turma = $this->turmaAutorizada($turmaId);
        $disciplina = $this->disciplinaAutorizada($turma, $disciplinaId);

        $material = TurmaMaterial::where('turma_id', $turma->id)
            ->where('disciplina_id', $disciplina->id)
            ->findOrFail($materialId);
        $this->removerMaterial($turma, $material->id);

        return redirect()->route('turma.disciplina.show', [$turma->id, $disciplina->id])
            ->withFragment('tab-materiais')
            ->with('success', 'Material removido.');
    }

    /** Professor/coordenacao abre um topico (orientacao) no forum da disciplina. */
    public function forumStore(Request $request, $turmaId, $disciplinaId)
    {
        $turma = $this->turmaAutorizada($turmaId);
        $disciplina = $this->disciplinaAutorizada($turma, $disciplinaId);
        abort_if($turma->finalizada(), 403, 'Esta turma já foi finalizada — o fórum está somente para consulta.');

        $dados = $request->validate([
            'titulo' => ['nullable', 'string', 'max:200'],
            'mensagem' => ['required', 'string', 'max:3000'],
        ]);

        TurmaForumPost::create([
            'empresa_id' => $turma->empresa_id,
            'escola_id' => $turma->escola_id,
            'turma_id' => $turma->id,
            'disciplina_id' => $disciplina->id,
            'user_id' => auth()->id(),
            'titulo' => $dados['titulo'] ?? null,
            'mensagem' => $dados['mensagem'],
        ]);

        return redirect()->route('turma.disciplina.show', [$turma->id, $disciplina->id])
            ->withFragment('tab-forum')
            ->with('success', 'Orientação publicada no fórum da disciplina.');
    }

    /** Professor/coordenacao responde um topico do forum da disciplina. */
    public function forumComentar(Request $request, $turmaId, $disciplinaId, $postId)
    {
        $turma = $this->turmaAutorizada($turmaId);
        $disciplina = $this->disciplinaAutorizada($turma, $disciplinaId);
        abort_if($turma->finalizada(), 403, 'Esta turma já foi finalizada — o fórum está somente para consulta.');

        $post = $this->topicoDoForum($turma, $disciplina, $postId);
        abort_if($post->estaEncerrada(), 403, 'Esta conversa foi encerrada e não aceita novas respostas.');

        $request->validate(['mensagem' => ['required', 'string', 'max:2000']]);

        TurmaForumPost::create([
            'empresa_id' => $turma->empresa_id,
            'escola_id' => $turma->escola_id,
            'turma_id' => $turma->id,
            'disciplina_id' => $disciplina->id,
            'parent_id' => $post->id,
            'user_id' => auth()->id(),
            'mensagem' => $request->input('mensagem'),
        ]);

        return redirect()->route('turma.disciplina.show', [$turma->id, $disciplina->id])
            ->withFragment('tab-forum')
            ->with('success', 'Resposta publicada no fórum.');
    }

    /** Professor/coordenacao edita o titulo de qualquer topico da disciplina. */
    public function forumUpdate(Request $request, $turmaId, $disciplinaId, $postId)
    {
        $turma = $this->turmaAutorizada($turmaId);
        $disciplina = $this->disciplinaAutorizada($turma, $disciplinaId);
        $post = $this->topicoDoForum($turma, $disciplina, $postId);

        $dados = $request->validate([
            'titulo' => ['nullable', 'string', 'max:200'],
        ]);

        $post->update(['titulo' => $dados['titulo'] ?? null]);

        return redirect()->route('turma.disciplina.show', [$turma->id, $disciplina->id])
            ->withFragment('tab-forum')
            ->with('success', 'Título do tópico atualizado.');
    }

    /** Professor/coordenacao encerra ou reabre uma conversa do forum. */
    public function forumToggleEncerrada($turmaId, $disciplinaId, $postId)
    {
        $turma = $this->turmaAutorizada($turmaId);
        $disciplina = $this->disciplinaAutorizada($turma, $disciplinaId);
        $post = $this->topicoDoForum($turma, $disciplina, $postId);

        $encerrada = $post->estaEncerrada();
        $post->update([
            'encerrada_em' => $encerrada ? null : now(),
            'encerrada_por' => $encerrada ? null : auth()->id(),
        ]);

        return redirect()->route('turma.disciplina.show', [$turma->id, $disciplina->id])
            ->withFragment('tab-forum')
            ->with('success', $encerrada ? 'Conversa reaberta.' : 'Conversa encerrada — sem novas respostas.');
    }

    /** Professor/coordenacao remove o topico e suas respostas (soft delete). */
    public function forumDestroy($turmaId, $disciplinaId, $postId)
    {
        $turma = $this->turmaAutorizada($turmaId);
        $disciplina = $this->disciplinaAutorizada($turma, $disciplinaId);
        $post = $this->topicoDoForum($turma, $disciplina, $postId);

        $post->respostas()->delete();
        $post->delete();

        return redirect()->route('turma.disciplina.show', [$turma->id, $disciplina->id])
            ->withFragment('tab-forum')
            ->with('success', 'Tópico removido do fórum.');
    }

    private function topicoDoForum(Turma $turma, Disciplina $disciplina, $postId): TurmaForumPost
    {
        return TurmaForumPost::where('turma_id', $turma->id)
            ->where('disciplina_id', $disciplina->id)
            ->whereNull('parent_id')
            ->findOrFail($postId);
    }

    private function regrasMaterial(bool $semDisciplina = false): array
    {
        $regras = [
            'titulo' => ['required', 'string', 'max:200'],
            'descricao' => ['nullable', 'string', 'max:2000'],
            'url' => ['nullable', 'url', 'max:500', 'required_without:arquivo'],
            'arquivo' => ['nullable', 'file', 'max:15360', 'required_without:url',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip,txt'],
        ];

        if (! $semDisciplina) {
            $regras['disciplina_id'] = ['nullable', 'integer'];
        }

        return $regras;
    }

    private function criarMaterial(Request $request, Turma $turma, ?int $disciplinaId): void
    {
        $path = $request->hasFile('arquivo')
            ? $request->file('arquivo')->store("empresas/{$turma->empresa_id}/turmas/{$turma->id}/materiais", 'public')
            : null;

        TurmaMaterial::create([
            'empresa_id' => $turma->empresa_id,
            'escola_id' => $turma->escola_id,
            'turma_id' => $turma->id,
            'disciplina_id' => $disciplinaId,
            'user_id' => auth()->id(),
            'titulo' => $request->input('titulo'),
            'descricao' => $request->input('descricao'),
            'url' => $request->input('url'),
            'arquivo_path' => $path,
        ]);
    }

    private function removerMaterial(Turma $turma, $materialId): void
    {
        $material = TurmaMaterial::where('turma_id', $turma->id)->findOrFail($materialId);
        if ($material->arquivo_path) {
            Storage::disk('public')->delete($material->arquivo_path);
        }
        $material->delete();
    }

    private function turmaAutorizada($turmaId): Turma
    {
        $turma = $this->turmaService->getById($turmaId, ['disciplinas']);
        abort_if(! $turma, 404, 'Turma não encontrada.');
        abort_if(! $this->turmaService->podeAcessar($turma), 403, 'Sem acesso a esta turma.');

        return $turma;
    }

    /**
     * Disciplina precisa ser ofertada pela turma e visivel ao usuario:
     * professor so opera nas que leciona; regente/coordenacao em todas.
     */
    private function disciplinaAutorizada(Turma $turma, $disciplinaId): Disciplina
    {
        $disciplina = $turma->disciplinas->firstWhere('id', (int) $disciplinaId);
        abort_unless($disciplina, 404, 'Disciplina não ofertada por esta turma.');

        $visiveis = $this->turmaService->disciplinasVisiveisNaTurma($turma, auth()->user());
        abort_unless($visiveis->contains('id', $disciplina->id), 422,
            'Disciplina não disponível para você nesta turma.');

        return $disciplina;
    }
}
