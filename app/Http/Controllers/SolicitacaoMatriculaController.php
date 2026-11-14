<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\SolicitacaoMatricula\StoreSolicitacaoMatriculaRequest;
use App\Http\Requests\SolicitacaoMatricula\UpdateSolicitacaoMatriculaRequest;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Models\Etapa;
use App\Models\SolicitacaoMatricula;
use App\Models\Turma;
use App\Services\BreadcrumbService;
use App\Services\SolicitacaoMatriculaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class SolicitacaoMatriculaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected SolicitacaoMatriculaService $solicitacaoService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Solicitações de Matrícula';
        $this->nomeFiltro = 'solicitacao_matricula';

        $this->middleware('can:solicitacao_matricula.index')->only(['index', 'show']);
        $this->middleware('can:solicitacao_matricula.create')->only(['create']);
        $this->middleware('can:solicitacao_matricula.store')->only(['store']);
        $this->middleware('can:solicitacao_matricula.edit')->only(['edit']);
        $this->middleware('can:solicitacao_matricula.update')->only(['update']);
        $this->middleware('can:solicitacao_matricula.destroy')->only(['destroy']);
        $this->middleware('can:solicitacao_matricula.aprovar')->only('aprovar');
        $this->middleware('can:solicitacao_matricula.recusar')->only('recusar');
        $this->middleware('can:solicitacao_matricula.cancelar')->only('cancelar');
        $this->middleware('can:solicitacao_matricula.filtro')->only('filtro');
        $this->middleware('can:solicitacao_matricula.removerFiltro')->only('removerFiltro');
        $this->middleware('can:solicitacao_matricula.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        $solicitacoes = $this->solicitacaoService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage,
            ['escola', 'etapa', 'turma.anoLetivo', 'matricula'],
            'id', 'desc'
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Solicitações de Matrícula', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('solicitacao_matricula.index', compact(
            'solicitacoes', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Solicitações de Matrícula', 'solicitacao_matricula.index', 'Nova Solicitação', 'Vida Acadêmica');

        return view('solicitacao_matricula.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreSolicitacaoMatriculaRequest $request)
    {
        $this->solicitacaoService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Solicitação registrada com sucesso.');
    }

    public function show($id)
    {
        $solicitacao = $this->solicitacaoService->getById($id, [
            'escola', 'etapa', 'turma.anoLetivo', 'aluno.pessoa', 'responsavel.pessoa',
            'matricula.turma', 'analista',
        ]);
        abort_if(! $solicitacao, 404, 'Solicitação não encontrada.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbShow('Solicitações de Matrícula', 'solicitacao_matricula.index', $solicitacao->nome_aluno, 'Vida Acadêmica');

        return view('solicitacao_matricula.show', array_merge(compact('solicitacao', 'breadcrumb'), $this->opcoesAprovacao($solicitacao)))
            ->with('titulo', $this->titulo);
    }

    public function edit($id)
    {
        $solicitacao = $this->solicitacaoService->getById($id, ['escola', 'etapa', 'turma']);
        abort_if(! $solicitacao, 404, 'Solicitação não encontrada.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Solicitações de Matrícula', 'solicitacao_matricula.index', "Editar: {$solicitacao->nome_aluno}", 'Vida Acadêmica');

        return view('solicitacao_matricula.add-edit', array_merge(compact('solicitacao', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateSolicitacaoMatriculaRequest $request, $id)
    {
        $solicitacao = $this->solicitacaoService->update($id, $request->validated());
        abort_if(! $solicitacao, 403, 'Sem acesso para editar esta solicitação.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Solicitação atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->solicitacaoService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir esta solicitação.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    /** Aprova: escolhe a turma e gera aluno + matricula + responsavel. */
    public function aprovar(Request $request, $id)
    {
        $solicitacao = $this->solicitacaoService->getById($id);
        abort_if(! $solicitacao, 404, 'Solicitação não encontrada.');

        $data = $request->validate(['turma_id' => ['required', 'integer']]);

        $this->solicitacaoService->aprovar($solicitacao, $data, $request->user());

        return redirect()->route('solicitacao_matricula.show', $solicitacao->id)
            ->with('success', 'Solicitação aprovada — aluno e matrícula criados.');
    }

    public function recusar(Request $request, $id)
    {
        $solicitacao = $this->solicitacaoService->getById($id);
        abort_if(! $solicitacao, 404, 'Solicitação não encontrada.');

        $data = $request->validate(['motivo_recusa' => ['required', 'string', 'max:2000']]);

        $this->solicitacaoService->recusar($solicitacao, $data['motivo_recusa'], $request->user());

        return redirect()->route('solicitacao_matricula.show', $solicitacao->id)
            ->with('success', 'Solicitação recusada.');
    }

    public function cancelar(Request $request, $id)
    {
        $solicitacao = $this->solicitacaoService->getById($id);
        abort_if(! $solicitacao, 404, 'Solicitação não encontrada.');

        $this->solicitacaoService->cancelar($solicitacao, $request->user());

        return redirect()->route('solicitacao_matricula.show', $solicitacao->id)
            ->with('success', 'Solicitação cancelada.');
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
        session()->put("$this->nomeFiltro.urlPadrao", 'solicitacao_matricula');
        session()->put("$this->nomeFiltro.rotaRedirect", 'solicitacao_matricula.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            [
                'name' => 'codigo', 'label' => 'Código', 'type' => 'text',
                'typeWhere' => 'like',
            ],
            [
                'name' => 'nome_aluno', 'label' => 'Nome do aluno', 'type' => 'text',
                'typeWhere' => 'like',
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Escola::query()->orderBy('nome')->get(['id', 'nome'])
                    ->map(fn (Escola $escola) => ['value' => $escola->id, 'label' => $escola->nome])
                    ->all(),
            ],
            [
                'name' => 'status', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => SolicitacaoMatricula::STATUS,
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'anosLetivos' => AnoLetivo::query()->orderBy('ano', 'desc')->get(['id', 'ano']),
            'etapas' => Etapa::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'turmas' => Turma::query()->with('escola')->orderBy('nome')->get(['id', 'nome', 'escola_id']),
            'parentescos' => SolicitacaoMatricula::PARENTESCOS,
            'statusList' => SolicitacaoMatricula::STATUS,
        ];
    }

    /** Turmas da escola da solicitacao para o select de aprovacao. */
    private function opcoesAprovacao(SolicitacaoMatricula $solicitacao): array
    {
        return [
            'turmasAprovacao' => Turma::query()
                ->with('anoLetivo', 'etapa')
                ->where('escola_id', $solicitacao->escola_id)
                ->orderBy('nome')
                ->get(),
        ];
    }
}
