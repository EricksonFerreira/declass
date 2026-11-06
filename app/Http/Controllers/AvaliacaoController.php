<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Avaliacao\SalvarNotasRequest;
use App\Http\Requests\Avaliacao\StoreAvaliacaoRequest;
use App\Http\Requests\Avaliacao\UpdateAvaliacaoRequest;
use App\Models\Avaliacao;
use App\Models\Disciplina;
use App\Models\Turma;
use App\Models\User;
use App\Services\AvaliacaoService;
use App\Services\BreadcrumbService;
use App\Services\NotaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class AvaliacaoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected AvaliacaoService $avaliacaoService,
        protected NotaService $notaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Avaliações';
        $this->nomeFiltro = 'avaliacao';

        $this->middleware('can:avaliacao.index')->only('index');
        $this->middleware('can:avaliacao.create')->only('create');
        $this->middleware('can:avaliacao.store')->only('store');
        $this->middleware('can:avaliacao.edit')->only('edit');
        $this->middleware('can:avaliacao.update')->only('update');
        $this->middleware('can:avaliacao.destroy')->only('destroy');
        $this->middleware('can:avaliacao.notas')->only(['notas', 'salvarNotas']);
        $this->middleware('can:avaliacao.notas.salvar')->only('salvarNotas');
        $this->middleware('can:avaliacao.filtro')->only('filtro');
        $this->middleware('can:avaliacao.removerFiltro')->only('removerFiltro');
        $this->middleware('can:avaliacao.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $avaliacoes = $this->avaliacaoService->paginateComFiltros($itensFiltered, (int) $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Avaliações', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('avaliacao.index', compact(
            'avaliacoes', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Avaliações', 'avaliacao.index', 'Nova Avaliação', 'Vida Acadêmica');

        return view('avaliacao.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreAvaliacaoRequest $request)
    {
        $this->avaliacaoService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Avaliação criada com sucesso.');
    }

    public function edit($id)
    {
        $avaliacao = $this->avaliacaoService->getById($id, ['turma', 'disciplina', 'professor']);
        abort_if(! $avaliacao, 404, 'Avaliação não encontrada.');
        abort_if(! $this->avaliacaoService->podeGerenciar($avaliacao), 403, 'Sem acesso a esta avaliação.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Avaliações', 'avaliacao.index', 'Editar Avaliação', 'Vida Acadêmica');

        return view('avaliacao.add-edit', array_merge(compact('avaliacao', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateAvaliacaoRequest $request, $id)
    {
        $this->avaliacaoService->update($id, $request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Avaliação atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $this->avaliacaoService->delete($id);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    /** Grade de notas: alunos ativos da turma x nota desta avaliacao. */
    public function notas($id)
    {
        $avaliacao = $this->avaliacaoService->getById($id, [
            'turma.escola', 'turma.anoLetivo', 'disciplina', 'professor',
        ]);
        abort_if(! $avaliacao, 404, 'Avaliação não encontrada.');
        abort_if(! $this->avaliacaoService->podeGerenciar($avaliacao), 403, 'Sem acesso a esta avaliação.');

        $matriculas = $this->avaliacaoService->matriculasDaTurma($avaliacao);
        $notas = $this->notaService->porMatricula($avaliacao);
        $medias = $this->avaliacaoService->mediasDaTurmaDisciplina($avaliacao);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow(
            'Avaliações', 'avaliacao.index',
            "Notas — {$avaliacao->nome}"
        );

        return view('avaliacao.notas', compact(
            'avaliacao', 'matriculas', 'notas', 'medias', 'breadcrumb'
        ))->with('titulo', $this->titulo);
    }

    public function salvarNotas(SalvarNotasRequest $request, $id)
    {
        $avaliacao = $this->avaliacaoService->getById($id);
        abort_if(! $avaliacao, 404, 'Avaliação não encontrada.');
        abort_if(! $this->avaliacaoService->podeGerenciar($avaliacao), 403, 'Sem acesso a esta avaliação.');

        $this->notaService->salvarNotas($avaliacao, $request->validated()['notas'] ?? []);

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Notas registradas com sucesso.');
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
        session()->put("$this->nomeFiltro.urlPadrao", 'avaliacao');
        session()->put("$this->nomeFiltro.rotaRedirect", 'avaliacao.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            [
                'name' => 'turma_id', 'label' => 'Turma', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->turmasOpcoes(),
            ],
            [
                'name' => 'disciplina_id', 'label' => 'Disciplina', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Disciplina::query()->where('ativo', 'S')->orderBy('nome')
                    ->pluck('nome', 'id')->all(),
            ],
            [
                'name' => 'tipo', 'label' => 'Tipo', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Avaliacao::TIPOS,
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'user_id', 'label' => 'Professor', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => User::query()->whereNotNull('role_id')->orderBy('nome')
                    ->pluck('nome', 'id')->all(),
            ],
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'periodo', 'label' => 'Período', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'turmas' => $this->turmasOpcoes(),
            'disciplinas' => Disciplina::query()->where('ativo', 'S')->orderBy('nome')
                ->pluck('nome', 'id')->all(),
            'tipos' => Avaliacao::TIPOS,
            'periodosJson' => $this->periodosPorTurmaJson(),
        ];
    }

    /**
     * Mapa turma_id => periodos letivos do ano letivo da turma,
     * usado pelo select dependente no formulario (JSON no Blade).
     */
    private function periodosPorTurmaJson(): string
    {
        $query = Turma::query()->select('id', 'ano_letivo_id');

        $user = auth()->user();
        if ($user && $user->isProfessor()) {
            $query->whereHas('atribuicoes', fn ($q) => $q->where('user_id', $user->id)->where('ativo', 'S'));
        }

        $turmas = $query->get();
        $periodos = \App\Models\PeriodoLetivo::query()
            ->whereIn('ano_letivo_id', $turmas->pluck('ano_letivo_id')->unique())
            ->where('ativo', 'S')
            ->orderBy('ordem')
            ->get(['id', 'ano_letivo_id', 'nome']);

        $map = [];
        foreach ($turmas as $turma) {
            $map[$turma->id] = $periodos
                ->where('ano_letivo_id', $turma->ano_letivo_id)
                ->map(fn ($p) => ['id' => $p->id, 'nome' => $p->nome])
                ->values()
                ->all();
        }

        return json_encode($map);
    }

    /** Professores so veem turmas onde tem atribuicao ativa. */
    private function turmasOpcoes(): array
    {
        $query = Turma::query()->with(['escola', 'anoLetivo'])->orderBy('nome');

        $user = auth()->user();
        if ($user && $user->isProfessor()) {
            $query->whereHas('atribuicoes', fn ($q) => $q->where('user_id', $user->id)->where('ativo', 'S'));
        }

        return $query->get()
            ->mapWithKeys(fn (Turma $t) => [
                $t->id => $t->nome.' — '.($t->escola->nome ?? '').' — '.($t->anoLetivo->ano ?? ''),
            ])->all();
    }
}
