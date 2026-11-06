<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Matricula;
use App\Models\Responsavel;
use App\Services\AvisoService;
use App\Services\BoletimService;
use Illuminate\Support\Collection;

class PortalController extends Controller
{
    public function __construct(
        private BoletimService $boletimService,
        private AvisoService $avisoService,
    ) {}

    /**
     * Home do portal: lista os alunos vinculados ao usuario logado
     * (como responsavel ou como aluno).
     */
    public function index()
    {
        $user = auth()->user();
        $alunos = $this->alunosDoUsuario($user);

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $matriculas = Matricula::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->where('status', 'ativa')
            ->with('turma.anoLetivo', 'turma.etapa')
            ->get()
            ->keyBy('aluno_id');

        return view('portal.index', [
            'titulo' => 'Portal Familiar',
            'alunos' => $alunos,
            'matriculas' => $matriculas,
        ]);
    }

    /**
     * Boletim do aluno, acessivel apenas se o aluno estiver vinculado
     * a conta logada (responsavel vinculado ou o proprio aluno).
     */
    public function aluno($alunoId)
    {
        $user = auth()->user();
        $aluno = $this->alunosDoUsuario($user)->firstWhere('id', (int) $alunoId);

        abort_if(! $aluno, 403, 'Aluno nao vinculado a esta conta.');

        $dados = $this->boletimService->dadosAluno($aluno);

        abort_if(! $dados || ! $dados['matricula'], 404, 'Aluno sem matricula ativa.');

        return view('portal.aluno', array_merge($dados, [
            'titulo' => 'Boletim - '.$aluno->nome,
            'aluno' => $aluno,
        ]));
    }

    /**
     * Financeiro do portal: mensalidades dos alunos vinculados,
     * agrupadas por aluno com situacao de cada cobranca.
     */
    public function financeiro()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $mensalidades = \App\Models\Mensalidade::query()
            ->whereHas('matricula', fn ($q) => $q->whereIn('aluno_id', $alunos->pluck('id')))
            ->with(['matricula.aluno.pessoa', 'matricula.turma', 'planoMensalidade'])
            ->orderBy('competencia', 'desc')
            ->get()
            ->groupBy(fn ($m) => $m->matricula->aluno_id);

        return view('portal.financeiro', [
            'titulo' => 'Financeiro',
            'alunos' => $alunos,
            'mensalidades' => $mensalidades,
        ]);
    }

    /**
     * Avisos/comunicados das escolas dos alunos vinculados:
     * gerais da escola + direcionados as turmas em que estao.
     */
    public function avisos()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $avisos = $this->avisoService->avisosParaAlunos($alunos);

        return view('portal.avisos', [
            'titulo' => 'Avisos',
            'alunos' => $alunos,
            'avisos' => $avisos,
        ]);
    }

    /**
     * Transporte dos alunos vinculados: rota, parada e veiculo
     * de cada um, quando houver vinculo ativo.
     */
    public function transporte()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $vinculos = \App\Models\TransporteAluno::query()
            ->where('ativo', 'S')
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->with(['aluno.pessoa', 'rota.escola', 'rota.veiculo', 'parada'])
            ->get()
            ->keyBy('aluno_id');

        return view('portal.transporte', [
            'titulo' => 'Transporte Escolar',
            'alunos' => $alunos,
            'vinculos' => $vinculos,
        ]);
    }

    /**
     * Cardapio das escolas onde os alunos vinculados estao
     * matriculados: proximas refeicoes, da data atual em diante.
     */
    public function cardapio()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $escolaIds = Matricula::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->where('status', 'ativa')
            ->whereHas('turma', fn ($q) => $q->whereNotNull('escola_id'))
            ->with('turma:id,escola_id')
            ->get()
            ->pluck('turma.escola_id')
            ->filter()
            ->unique();

        $cardapios = \App\Models\Cardapio::query()
            ->whereIn('escola_id', $escolaIds)
            ->where('ativo', 'S')
            ->whereDate('data', '>=', now()->toDateString())
            ->with('escola')
            ->orderBy('data')
            ->orderBy('tipo')
            ->limit(60)
            ->get()
            ->groupBy(fn ($c) => $c->data->toDateString());

        return view('portal.cardapio', [
            'titulo' => 'Cardápio',
            'alunos' => $alunos,
            'cardapios' => $cardapios,
        ]);
    }

    /**
     * Tarefas das turmas onde os alunos vinculados estao
     * matriculados, com o status de entrega de cada um.
     */
    public function tarefas()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $matriculas = Matricula::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->where('status', 'ativa')
            ->with(['turma:id,escola_id', 'aluno:id,pessoa_id'])
            ->get();

        $turmaIds = $matriculas->pluck('turma_id')->unique();

        $tarefas = \App\Models\Tarefa::query()
            ->whereIn('turma_id', $turmaIds)
            ->where('ativo', 'S')
            ->with(['turma:id,nome,escola_id', 'disciplina:id,nome', 'entregas' => fn ($q) => $q
                ->whereIn('matricula_id', $matriculas->pluck('id')),
                'entregas.tarefa:id,data_entrega'])
            ->orderBy('data_entrega')
            ->limit(60)
            ->get();

        // Entrega de cada aluno por tarefa
        $entregasPorAluno = $tarefas->flatMap->entregas->keyBy(fn ($e) => $e->tarefa_id.'|'.$e->matricula_id);
        $matriculaPorAlunoTurma = $matriculas->keyBy(fn ($m) => $m->aluno_id.'|'.$m->turma_id);

        return view('portal.tarefas', [
            'titulo' => 'Tarefas',
            'alunos' => $alunos,
            'tarefas' => $tarefas,
            'matriculas' => $matriculas,
            'entregasPorAluno' => $entregasPorAluno,
            'matriculaPorAlunoTurma' => $matriculaPorAlunoTurma,
        ]);
    }

    /**
     * Emprestimos de livros dos alunos vinculados a conta:
     * ativos primeiro (com atraso derivado), depois historico.
     */
    public function emprestimos()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $emprestimos = \App\Models\EmprestimoLivro::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->with(['livro:id,titulo,autor', 'aluno.pessoa:id,nome'])
            ->orderByRaw("CASE WHEN status = 'ativo' THEN 0 ELSE 1 END")
            ->orderBy('data_devolucao_prevista')
            ->limit(100)
            ->get()
            ->groupBy('aluno_id');

        return view('portal.emprestimos', [
            'titulo' => 'Meus Empréstimos',
            'alunos' => $alunos,
            'emprestimos' => $emprestimos,
        ]);
    }

    /**
     * Agenda escolar: eventos com publico "todos" das escolas
     * onde os alunos vinculados estudam (ou da rede toda).
     */
    public function agenda()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $eventos = app(\App\Services\EventoService::class)->eventosParaAlunos($alunos);

        return view('portal.agenda', [
            'titulo' => 'Agenda Escolar',
            'alunos' => $alunos,
            'eventos' => $eventos,
        ]);
    }

    /**
     * Pesquisas abertas para as familias: pesquisa da escola
     * dos alunos vinculados (ou da rede toda) dentro do periodo
     * e ainda nao respondida pelo usuario.
     */
    public function pesquisas()
    {
        $user = auth()->user();
        $alunos = $this->alunosDoUsuario($user);

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $pesquisas = app(\App\Services\PesquisaService::class)
            ->pesquisasParaUsuario($user, $alunos);

        return view('portal.pesquisas', [
            'titulo' => 'Pesquisas',
            'alunos' => $alunos,
            'pesquisas' => $pesquisas,
        ]);
    }

    /** Formulario de resposta de uma pesquisa aberta. */
    public function pesquisaResponder($id)
    {
        $user = auth()->user();
        $alunos = $this->alunosDoUsuario($user);

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $pesquisa = \App\Models\Pesquisa::query()
            ->with('perguntas')
            ->where('status', 'aberta')
            ->findOrFail($id);

        // So responde pesquisa da escola dos alunos (ou rede toda)
        $escolaIds = $alunos->pluck('escola_id')->filter()->unique();
        abort_if($pesquisa->escola_id && ! $escolaIds->contains($pesquisa->escola_id), 403);
        abort_unless($pesquisa->estaAberta(), 403, 'Esta pesquisa não está aberta.');
        abort_if(
            \App\Models\PesquisaResposta::where('pesquisa_id', $pesquisa->id)->where('user_id', $user->id)->exists(),
            403, 'Você já respondeu esta pesquisa.'
        );

        return view('portal.pesquisa-responder', [
            'titulo' => 'Responder Pesquisa',
            'alunos' => $alunos,
            'pesquisa' => $pesquisa,
        ]);
    }

    /** Salva a resposta da pesquisa. */
    public function pesquisaSalvar(\Illuminate\Http\Request $request, $id)
    {
        $user = auth()->user();
        $alunos = $this->alunosDoUsuario($user);

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $pesquisa = \App\Models\Pesquisa::query()->findOrFail($id);

        $escolaIds = $alunos->pluck('escola_id')->filter()->unique();
        abort_if($pesquisa->escola_id && ! $escolaIds->contains($pesquisa->escola_id), 403);

        // aluno_id opcional: o primeiro aluno vinculado como contexto
        $alunoId = $request->filled('aluno_id') && $alunos->contains('id', (int) $request->input('aluno_id'))
            ? (int) $request->input('aluno_id')
            : $alunos->first()?->id;

        app(\App\Services\PesquisaService::class)->responder(
            $pesquisa, $user, $request->input('respostas', []), $alunoId
        );

        return redirect()->route('portal.pesquisas')->with('success', 'Resposta enviada com sucesso!');
    }

    /**
     * Fichas de saude dos alunos vinculados: alergias,
     * medicamentos, condicoes e contato de emergencia.
     */
    public function saude()
    {
        $alunos = $this->alunosDoUsuario(auth()->user());

        abort_if($alunos->isEmpty(), 403, 'Nenhum aluno vinculado a esta conta.');

        $fichas = \App\Models\SaudeAluno::query()
            ->whereIn('aluno_id', $alunos->pluck('id'))
            ->with('aluno.pessoa:id,nome')
            ->get()
            ->keyBy('aluno_id');

        return view('portal.saude', [
            'titulo' => 'Saúde dos Alunos',
            'alunos' => $alunos,
            'fichas' => $fichas,
        ]);
    }

    /**
     * Alunos acessiveis pela conta: filhos de responsaveis vinculados
     * + o proprio aluno quando a conta e de aluno.
     */
    private function alunosDoUsuario($user): Collection
    {
        $ids = collect();

        $responsavel = Responsavel::query()->where('user_id', $user->id)->first();
        if ($responsavel) {
            $ids = $ids->merge($responsavel->alunos()->pluck('alunos.id'));
        }

        $proprio = Aluno::query()->where('user_id', $user->id)->first();
        if ($proprio) {
            $ids->push($proprio->id);
        }

        if ($ids->isEmpty()) {
            return collect();
        }

        return Aluno::query()->with('pessoa')->whereIn('id', $ids->unique())->get();
    }
}
