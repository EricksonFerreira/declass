<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Diario\SalvarFrequenciaRequest;
use App\Http\Requests\Diario\StoreDiarioRequest;
use App\Http\Requests\Diario\UpdateDiarioRequest;
use App\Models\Disciplina;
use App\Models\Turma;
use App\Models\User;
use App\Services\BreadcrumbService;
use App\Services\DiarioService;
use App\Services\FrequenciaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class DiarioController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected DiarioService $diarioService,
        protected FrequenciaService $frequenciaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Diário de Classe';
        $this->nomeFiltro = 'diario';

        $this->middleware('can:diario.index')->only('index');
        $this->middleware('can:diario.create')->only('create');
        $this->middleware('can:diario.store')->only('store');
        $this->middleware('can:diario.edit')->only('edit');
        $this->middleware('can:diario.update')->only('update');
        $this->middleware('can:diario.destroy')->only('destroy');
        $this->middleware('can:diario.frequencia')->only(['frequencia', 'salvarFrequencia']);
        $this->middleware('can:diario.frequencia.salvar')->only('salvarFrequencia');
        $this->middleware('can:diario.filtro')->only('filtro');
        $this->middleware('can:diario.removerFiltro')->only('removerFiltro');
        $this->middleware('can:diario.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $diarios = $this->diarioService->paginateComFiltros($itensFiltered, (int) $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Diário de Classe', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('diario.index', compact(
            'diarios', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Diário de Classe', 'diario.index', 'Registrar Aula', 'Vida Acadêmica');

        return view('diario.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreDiarioRequest $request)
    {
        $this->diarioService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Aula registrada com sucesso.');
    }

    public function edit($id)
    {
        $diario = $this->diarioService->getById($id, ['turma', 'disciplina', 'professor']);
        abort_if(! $diario, 404, 'Registro não encontrado.');
        abort_if(! $this->diarioService->podeGerenciar($diario), 403, 'Sem acesso a este diário.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Diário de Classe', 'diario.index', 'Editar Aula', 'Vida Acadêmica');

        return view('diario.add-edit', array_merge(compact('diario', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateDiarioRequest $request, $id)
    {
        $this->diarioService->update($id, $request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Aula atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $this->diarioService->delete($id);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    /** Tela de chamada: lista alunos ativos da turma com status. */
    public function frequencia($id)
    {
        $diario = $this->diarioService->getById($id, [
            'turma.escola', 'turma.anoLetivo', 'disciplina', 'professor',
        ]);
        abort_if(! $diario, 404, 'Registro não encontrado.');
        abort_if(! $this->diarioService->podeGerenciar($diario), 403, 'Sem acesso a este diário.');

        $matriculas = $this->diarioService->matriculasDaTurma($diario);
        $registradas = $this->frequenciaService->porMatricula($diario);
        $statusOpcoes = \App\Models\Frequencia::STATUS;

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow(
            'Diário de Classe', 'diario.index',
            "Chamada — {$diario->turma->nome} / {$diario->disciplina->nome}"
        );

        return view('diario.frequencia', compact(
            'diario', 'matriculas', 'registradas', 'statusOpcoes', 'breadcrumb'
        ))->with('titulo', $this->titulo);
    }

    public function salvarFrequencia(SalvarFrequenciaRequest $request, $id)
    {
        $diario = $this->diarioService->getById($id);
        abort_if(! $diario, 404, 'Registro não encontrado.');
        abort_if(! $this->diarioService->podeGerenciar($diario), 403, 'Sem acesso a este diário.');

        $this->frequenciaService->salvarChamada($diario, $request->validated()['frequencias']);

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Chamada registrada com sucesso.');
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
        session()->put("$this->nomeFiltro.urlPadrao", 'diario');
        session()->put("$this->nomeFiltro.rotaRedirect", 'diario.index');
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
            ['name' => 'data', 'label' => 'Data', 'type' => 'date', 'typeWhere' => 'where'],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'turmas' => $this->turmasOpcoes(),
            'disciplinas' => Disciplina::query()->where('ativo', 'S')->orderBy('nome')
                ->pluck('nome', 'id')->all(),
        ];
    }

    /** Professores so veem turmas onde tem atribuicao ativa. */
    private function turmasOpcoes(): array
    {
        $query = Turma::query()->with(['escola', 'anoLetivo'])->orderBy('nome');

        $user = auth()->user();
        if ($user && \App\Services\DiarioService::ehProfessor($user)) {
            $query->whereHas('atribuicoes', fn ($q) => $q->where('user_id', $user->id)->where('ativo', 'S'));
        }

        return $query->get()
            ->mapWithKeys(fn (Turma $t) => [
                $t->id => $t->nome.' — '.($t->escola->nome ?? '').' — '.($t->anoLetivo->ano ?? ''),
            ])->all();
    }
}
