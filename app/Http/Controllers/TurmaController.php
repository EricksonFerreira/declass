<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Turma\GerarTurmasRequest;
use App\Http\Requests\Turma\ReplicarOrganizacaoRequest;
use App\Http\Requests\Turma\StoreTurmaRequest;
use App\Http\Requests\Turma\UpdateTurmaRequest;
use App\Models\AnoLetivo;
use App\Models\Atribuicao;
use App\Models\Disciplina;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\Turma;
use App\Models\User;
use App\Services\BreadcrumbService;
use App\Services\TurmaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class TurmaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected TurmaService $turmaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Turmas';
        $this->nomeFiltro = 'turma';

        $this->middleware('can:turma.index')->only('index');
        $this->middleware('can:turma.show')->only('show');
        $this->middleware('can:turma.create')->only('create');
        $this->middleware('can:turma.store')->only(['store', 'gerarLote']);
        $this->middleware('can:turma.replicar_organizacao')->only('replicarOrganizacao');
        $this->middleware('can:turma.edit')->only('edit');
        $this->middleware('can:turma.update')->only('update');
        $this->middleware('can:turma.destroy')->only('destroy');
        $this->middleware('can:turma.filtro')->only('filtro');
        $this->middleware('can:turma.removerFiltro')->only('removerFiltro');
        $this->middleware('can:turma.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $turmas = $this->turmaService->paginateComFiltros($itensFiltered, (int) $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Turmas', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('turma.index', compact(
            'turmas', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Turmas', 'turma.index', 'Nova Turma', 'Vida Acadêmica');

        return view('turma.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreTurmaRequest $request)
    {
        $this->turmaService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Turma criada com sucesso.');
    }

    /**
     * Geracao em lote: etapas x sufixos x turnos a partir de um
     * ano letivo. Nomes repetidos no ano sao pulados e reportados.
     */
    public function gerarLote(GerarTurmasRequest $request)
    {
        $data = $request->validated();

        $anoLetivo = AnoLetivo::query()->findOrFail($data['ano_letivo_id']);
        $data['escola_id'] = $anoLetivo->escola_id;

        $resultado = $this->turmaService->gerarLote($data);

        $mensagem = "{$resultado['criadas']} turma(s) criada(s).";
        if ($resultado['puladas'] !== []) {
            $mensagem .= ' '.count($resultado['puladas']).' já existiam e foram puladas: '.implode(', ', array_slice($resultado['puladas'], 0, 10)).(count($resultado['puladas']) > 10 ? '…' : '');
        }

        return redirect()->back()->with('success', $mensagem);
    }

    /**
     * Replica a organizacao da turma de origem (disciplinas
     * ofertadas e, opcionalmente, professores atribuidos) para as
     * turmas de destino. Usado pelo planejador do ano letivo para
     * replicar a mesma organizacao entre turmas irmas (A, B, C...).
     */
    public function replicarOrganizacao(ReplicarOrganizacaoRequest $request)
    {
        $data = $request->validated();

        $resultado = $this->turmaService->replicarOrganizacao(
            (int) $data['turma_origem_id'],
            array_map('intval', $data['turmas_destino']),
            $request->boolean('incluir_professores', true)
        );

        $mensagem = "Organização replicada para {$resultado['turmas']} turma(s): ";
        $mensagem .= "{$resultado['disciplinas']} disciplina(s)";
        if ($resultado['cargas_atualizadas'] > 0) {
            $mensagem .= " ({$resultado['cargas_atualizadas']} carga(s) horária(s) atualizada(s))";
        }
        if ($request->boolean('incluir_professores', true)) {
            $mensagem .= ", {$resultado['professores_criados']} professor(es) atribuído(s)";
            if ($resultado['professores_atualizados'] > 0) {
                $mensagem .= " ({$resultado['professores_atualizados']} atualizado(s))";
            }
        }
        $mensagem .= '.';

        return redirect()->route('ano_letivo.show', $resultado['ano_letivo_id'])
            ->with('success', $mensagem);
    }

    public function show($id)
    {
        $turma = $this->turmaService->getById($id, [
            'escola', 'anoLetivo', 'etapa',
            'disciplinas', 'atribuicoes.user', 'atribuicoes.disciplina',
            'matriculas.aluno.pessoa',
        ]);
        abort_if(! $turma, 404, 'Turma não encontrada.');
        abort_if(! $this->turmaService->podeAcessar($turma), 403, 'Sem acesso a esta turma.');

        $disciplinasOfertadasIds = $turma->disciplinas->pluck('id')->all();
        $disciplinasOpcoes = Disciplina::query()
            ->where('ativo', 'S')
            ->whereNotIn('id', $disciplinasOfertadasIds)
            ->orderBy('nome')
            ->pluck('nome', 'id')
            ->all();
        $disciplinasOfertadas = $turma->disciplinas->mapWithKeys(
            fn (Disciplina $d) => [$d->id => $d->nome]
        )->all();

        $professoresOpcoes = User::query()
            ->whereNotNull('role_id')
            ->orderBy('nome')
            ->pluck('nome', 'id')
            ->all();

        $papeis = Atribuicao::PAPEIS;
        $turnos = Turma::TURNOS;

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow('Turmas', 'turma.index', $turma->nome);

        return view('turma.show', compact(
            'turma', 'disciplinasOpcoes', 'disciplinasOfertadas',
            'professoresOpcoes', 'papeis', 'turnos', 'breadcrumb'
        ))->with('titulo', $turma->nome);
    }

    public function edit($id)
    {
        $turma = $this->turmaService->getById($id);
        abort_if(! $turma, 404, 'Turma não encontrada.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Turmas', 'turma.index', "Editar: {$turma->nome}", 'Vida Acadêmica');

        return view('turma.add-edit', array_merge(compact('turma', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateTurmaRequest $request, $id)
    {
        $turma = $this->turmaService->update($id, $request->validated());
        abort_if(! $turma, 403, 'Sem acesso para editar esta turma.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Turma atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->turmaService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir esta turma.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'turma');
        session()->put("$this->nomeFiltro.rotaRedirect", 'turma.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Escola::query()->orderBy('nome')->get(['id', 'nome'])
                    ->map(fn (Escola $e) => ['value' => $e->id, 'label' => $e->nome])->all(),
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'ano_letivo_id', 'label' => 'Ano letivo', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->anosLetivosOpcoes(),
            ],
            [
                'name' => 'etapa_id', 'label' => 'Etapa', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->etapasOpcoes(),
            ],
            [
                'name' => 'turno', 'label' => 'Turno', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Turma::TURNOS,
            ],
            [
                'name' => 'ativo', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Ativa', 'N' => 'Inativa'],
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'anosLetivos' => $this->anosLetivosOpcoesForm(),
            'etapas' => Etapa::query()->where('ativo', 'S')->orderBy('ordem')->orderBy('nome')
                ->pluck('nome', 'id')->all(),
            'turnos' => Turma::TURNOS,
        ];
    }

    private function anosLetivosOpcoes(): array
    {
        return AnoLetivo::query()->with('escola')->orderBy('ano', 'desc')->get()
            ->mapWithKeys(fn (AnoLetivo $a) => [
                $a->id => $a->ano.' — '.($a->escola->nome ?? ''),
            ])->all();
    }

    private function anosLetivosOpcoesForm(): array
    {
        return $this->anosLetivosOpcoes();
    }

    private function etapasOpcoes(): array
    {
        return Etapa::query()->where('ativo', 'S')->orderBy('ordem')->orderBy('nome')
            ->pluck('nome', 'id')->all();
    }
}
