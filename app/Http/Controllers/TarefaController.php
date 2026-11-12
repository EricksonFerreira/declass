<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Tarefa\StoreTarefaRequest;
use App\Http\Requests\Tarefa\UpdateTarefaRequest;
use App\Models\Disciplina;
use App\Models\EntregaTarefa;
use App\Models\Escola;
use App\Models\Matricula;
use App\Models\Tarefa;
use App\Models\User;
use App\Services\BoletimService;
use App\Services\BreadcrumbService;
use App\Services\TarefaService;
use App\Services\TurmaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TarefaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected TarefaService $tarefaService,
        protected BreadcrumbService $breadcrumbService,
        protected BoletimService $boletimService,
        protected TurmaService $turmaService,
    ) {
        $this->titulo = 'Tarefas';
        $this->nomeFiltro = 'tarefa';

        $this->middleware('can:tarefa.index')->only('index');
        $this->middleware('can:tarefa.create')->only('create');
        $this->middleware('can:tarefa.store')->only('store');
        $this->middleware('can:tarefa.edit')->only('edit');
        $this->middleware('can:tarefa.update')->only('update');
        $this->middleware('can:tarefa.destroy')->only('destroy');
        $this->middleware('can:tarefa.entregas')->only('entregas');
        $this->middleware('can:tarefa.entregas.salvar')->only('salvarEntregas');
        $this->middleware('can:tarefa.filtro')->only('filtro');
        $this->middleware('can:tarefa.removerFiltro')->only('removerFiltro');
        $this->middleware('can:tarefa.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        // Professor ve apenas tarefas das turmas que leciona
        $turmasIds = auth()->user()->isProfessor()
            ? $this->boletimService->turmasVisiveis(auth()->user())->pluck('id')
            : null;
        $progresso = $itensFiltered['progresso']['valor'] ?? null;

        $tarefas = $this->tarefaService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['turma.escola', 'turma.anoLetivo', 'disciplina', 'autor'], 'data_entrega', 'desc',
            function ($q) use ($turmasIds, $progresso) {
                if ($turmasIds) {
                    $q->whereIn('turma_id', $turmasIds);
                }
                if ($progresso === 'vencida') {
                    $q->whereDate('data_entrega', '<', now()->toDateString());
                } elseif ($progresso === 'pendente') {
                    $q->whereHas('entregas', fn ($e) => $e->whereIn('status', ['pendente', 'entregue']));
                } elseif ($progresso === 'concluida') {
                    $q->whereHas('entregas')
                        ->whereDoesntHave('entregas', fn ($e) => $e->whereIn('status', ['pendente', 'entregue']));
                }
            }
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Tarefas', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('tarefa.index', compact(
            'tarefas', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $turmaRota = request()->route('turma');
        $turmaPreId = (int) request()->query('turma_id', 0) ?: ($turmaRota ? (int) $turmaRota : null);
        $turma = $turmaPreId ? \App\Models\Turma::find($turmaPreId) : null;

        $breadcrumb = $turmaRota && $turma
            ? $this->breadcrumbService->prepararBreadcrumbCustom([
                ['Turmas', route('turma.index')],
                [$turma->nome, route('turma.show', $turma->id)],
                ['Nova Tarefa', null],
            ])
            : $this->breadcrumbService->prepararBreadcrumbCreate('Tarefas', 'tarefa.index', 'Nova Tarefa', 'Vida Acadêmica');

        return view('tarefa.add-edit', array_merge(
            compact('breadcrumb'), $this->opcoesFormulario(), ['turmaPreId' => $turmaPreId]
        ))->with('titulo', $this->titulo);
    }

    public function store(StoreTarefaRequest $request)
    {
        $turma = \App\Models\Turma::findOrFail($request->validated('turma_id'));
        $this->tarefaService->validarAcessoTurma($turma, auth()->user());

        $dados = $request->validated();
        $dados['user_id'] = auth()->id();

        // Multi-select: uma tarefa por disciplina marcada; nenhuma = turma toda.
        $disciplinaIds = $dados['disciplina_ids'] ?? [];
        unset($dados['disciplina_ids']);
        if ($disciplinaIds === []) {
            $disciplinaIds = [null];
        }

        foreach ($disciplinaIds as $disciplinaId) {
            $this->tarefaService->create($dados + ['disciplina_id' => $disciplinaId]);
        }

        $n = count($disciplinaIds);

        return $this->redirecionaRotaRedirect($this->nomeFiltro,
            $n > 1 ? "Tarefa criada para {$n} disciplinas." : 'Tarefa criada com sucesso.');
    }

    public function edit($id)
    {
        $tarefa = $this->tarefaService->getById($id, ['turma', 'disciplina', 'autor', 'questoes']);
        abort_if(! $tarefa, 404, 'Tarefa não encontrada.');

        $this->autorizarTarefa($tarefa);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Tarefas', 'tarefa.index', "Editar: {$tarefa->titulo}", 'Vida Acadêmica');

        return view('tarefa.add-edit', array_merge(compact('tarefa', 'breadcrumb'), $this->opcoesFormulario($tarefa->turma_id)))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateTarefaRequest $request, $id)
    {
        $tarefa = $this->tarefaService->getById($id);
        abort_if(! $tarefa, 404, 'Tarefa não encontrada.');

        $this->autorizarTarefa($tarefa);

        $this->tarefaService->update($id, $request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Tarefa atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->tarefaService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir esta tarefa.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    /**
     * Lista de entregas da tarefa: uma linha por aluno da turma
     * com status, nota e data — correcao em lote.
     */
    public function entregas($id)
    {
        $tarefa = $this->tarefaService->getById($id, ['turma.escola', 'disciplina', 'autor', 'questoes']);
        abort_if(! $tarefa, 404, 'Tarefa não encontrada.');

        $this->autorizarTarefa($tarefa);

        $matriculas = Matricula::query()
            ->where('turma_id', $tarefa->turma_id)
            ->where('status', 'ativa')
            ->with('aluno.pessoa')
            ->orderBy('numero_chamada')
            ->get();

        $entregas = EntregaTarefa::query()
            ->where('tarefa_id', $tarefa->id)
            ->with(['respostas.questao:id,enunciado,ordem', 'anexos'])
            ->get()
            ->keyBy('matricula_id');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex(
            'Entregas: '.$tarefa->titulo, 'tarefa.index', 'Entregas', 'Vida Acadêmica'
        );

        return view('tarefa.entregas', compact('tarefa', 'matriculas', 'entregas', 'breadcrumb'))
            ->with('titulo', $this->titulo);
    }

    /**
     * Salva status/nota/data das entregas em lote (uma por aluno).
     */
    public function salvarEntregas(Request $request, $id)
    {
        $tarefa = $this->tarefaService->getById($id);
        abort_if(! $tarefa, 404, 'Tarefa não encontrada.');

        $this->autorizarTarefa($tarefa);

        $request->validate([
            'entregas' => ['required', 'array'],
            'entregas.*.status' => ['required', Rule::in(Tarefa::STATUS_ENTREGA)],
            'entregas.*.nota' => ['nullable', 'numeric', 'min:0', 'max:'.($tarefa->nota_maxima ?? 100)],
            'entregas.*.data_entrega' => ['nullable', 'date'],
            'entregas.*.observacoes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($request, $tarefa) {
            foreach ($request->input('entregas', []) as $matriculaId => $dados) {
                EntregaTarefa::updateOrCreate(
                    ['tarefa_id' => $tarefa->id, 'matricula_id' => (int) $matriculaId],
                    [
                        'empresa_id' => $tarefa->empresa_id,
                        'status' => $dados['status'],
                        'nota' => $dados['status'] === 'avaliada' ? ($dados['nota'] ?? null) : null,
                        'data_entrega' => $dados['data_entrega'] ?? null,
                        'observacoes' => $dados['observacoes'] ?? null,
                    ]
                );
            }
        });

        return redirect()->route('tarefa.entregas', $tarefa->id)
            ->with('success', 'Entregas atualizadas com sucesso.');
    }

    /** Professor so gerencia tarefas de turmas que leciona. */
    private function autorizarTarefa(Tarefa $tarefa): void
    {
        $user = auth()->user();
        if ($user->isProfessor()) {
            $this->boletimService->podeVerTurma($tarefa->turma, $user)
                ?: abort(403, 'Tarefa de turma que você não leciona.');
        }
    }

    public function filtro(Request $request)
    {
        $this->criarFiltros($request, $this->nomeFiltro);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function removerFiltro(Request $request)
    {
        $this->removeSessao($this->nomeFiltro, $request->input('atributo'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function setPerPage(Request $request)
    {
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'tarefa');
        session()->put("$this->nomeFiltro.rotaRedirect", 'tarefa.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'titulo', 'label' => 'Título', 'type' => 'text', 'typeWhere' => 'like'],
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Todas', 'selected' => false]],
                    Escola::query()->orderBy('nome')->get(['id', 'nome'])
                        ->map(fn ($e) => ['value' => $e->id, 'label' => $e->nome, 'selected' => false])->all()
                ),
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'turma_id', 'label' => 'Turma', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->boletimService->turmasVisiveis(auth()->user())
                    ->map(fn ($t) => ['value' => $t->id, 'label' => $t->nome.' — '.($t->escola->nome ?? '')])
                    ->all(),
            ],
            [
                'name' => 'disciplina_id', 'label' => 'Disciplina', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Disciplina::query()->orderBy('nome')->get(['id', 'nome'])
                    ->map(fn ($d) => ['value' => $d->id, 'label' => $d->nome])->all(),
            ],
            [
                'name' => 'user_id', 'label' => 'Professor', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => array_merge(
                    [['value' => '', 'label' => 'Todos', 'selected' => false]],
                    User::query()->whereIn('id', Tarefa::query()->select('user_id')->distinct()->pluck('user_id'))
                        ->orderBy('name')->get(['id', 'name'])
                        ->map(fn ($u) => ['value' => $u->id, 'label' => $u->name, 'selected' => false])->all()
                ),
            ],
            [
                'name' => 'progresso', 'label' => 'Progresso', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => [
                    ['value' => '', 'label' => 'Todos', 'selected' => false],
                    ['value' => 'pendente', 'label' => 'Pendentes', 'selected' => false],
                    ['value' => 'concluida', 'label' => 'Concluídas', 'selected' => false],
                    ['value' => 'vencida', 'label' => 'Vencidas', 'selected' => false],
                ],
            ],
            [
                'name' => 'ativo', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Ativa', 'N' => 'Inativa'],
            ],
        ];
    }

    private function opcoesFormulario(?int $turmaId = null): array
    {
        $user = auth()->user();
        $turmas = $this->boletimService->turmasVisiveis($user);
        if ($user && $user->isProfessor()) {
            $turmas->loadMissing('atribuicoes');
        }

        return [
            'turmas' => $turmas->mapWithKeys(fn ($t) => [$t->id => $t->nome.' — '.($t->escola->nome ?? '')])->all(),
            'turmasPicker' => $turmas->map(fn ($t) => [
                'id' => $t->id,
                'rotulo' => $t->nome.' — '.($t->escola->nome ?? '').' — '.($t->anoLetivo->ano ?? ''),
                'nome' => $t->nome,
                'detalhe' => trim(($t->etapa->nome ?? '').' · '.($t->anoLetivo->ano ?? '').' · '.($t->escola->nome ?? ''), ' ·'),
                'escola_id' => $t->escola_id,
                'escola' => $t->escola->nome ?? '',
                'ano_letivo_id' => $t->ano_letivo_id,
                'ano' => $t->anoLetivo?->ano,
            ])->values()->all(),
            // Professor: por turma, somente as disciplinas visiveis
            // (regente ve todas as ofertadas — ver TurmaService).
            'disciplinasPorTurma' => $turmas->mapWithKeys(function ($t) use ($user) {
                return [$t->id => $this->turmaService->disciplinasVisiveisNaTurma($t, $user)
                    ->mapWithKeys(fn ($d) => [$d->id => $d->nome])->all()];
            })->toJson(),
        ];
    }
}
