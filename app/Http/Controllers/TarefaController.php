<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Tarefa\StoreTarefaRequest;
use App\Http\Requests\Tarefa\UpdateTarefaRequest;
use App\Models\Disciplina;
use App\Models\EntregaTarefa;
use App\Models\Matricula;
use App\Models\Tarefa;
use App\Services\BoletimService;
use App\Services\BreadcrumbService;
use App\Services\TarefaService;
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

        $tarefas = $this->tarefaService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['turma.escola', 'turma.anoLetivo', 'disciplina', 'autor'], 'data_entrega', 'desc',
            $turmasIds ? fn ($q) => $q->whereIn('turma_id', $turmasIds) : null
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Tarefas', 'tarefa.index', 'Nova Tarefa', 'Vida Acadêmica');

        return view('tarefa.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreTarefaRequest $request)
    {
        $turma = \App\Models\Turma::findOrFail($request->validated('turma_id'));
        $this->tarefaService->validarAcessoTurma($turma, auth()->user());

        $dados = $request->validated();
        $dados['user_id'] = auth()->id();

        $this->tarefaService->create($dados);

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Tarefa criada com sucesso.');
    }

    public function edit($id)
    {
        $tarefa = $this->tarefaService->getById($id, ['turma', 'disciplina', 'autor']);
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
        $tarefa = $this->tarefaService->getById($id, ['turma.escola', 'disciplina', 'autor']);
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
                'name' => 'ativo', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Ativa', 'N' => 'Inativa'],
            ],
        ];
    }

    private function opcoesFormulario(?int $turmaId = null): array
    {
        $turmas = $this->boletimService->turmasVisiveis(auth()->user());

        return [
            'turmas' => $turmas->mapWithKeys(fn ($t) => [$t->id => $t->nome.' — '.($t->escola->nome ?? '')])->all(),
            'disciplinasPorTurma' => $turmas->mapWithKeys(fn ($t) => [
                $t->id => $t->disciplinas->mapWithKeys(fn ($d) => [$d->id => $d->nome])->all(),
            ])->toJson(),
        ];
    }
}
