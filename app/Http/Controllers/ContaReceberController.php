<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\ContaReceber\StoreContaReceberRequest;
use App\Http\Requests\ContaReceber\UpdateContaReceberRequest;
use App\Models\Aluno;
use App\Models\ContaReceber;
use App\Models\Escola;
use App\Services\BreadcrumbService;
use App\Services\ContaReceberService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class ContaReceberController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected ContaReceberService $service,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Contas a Receber';
        $this->nomeFiltro = 'conta_receber';

        $this->middleware('can:conta_receber.index')->only('index');
        $this->middleware('can:conta_receber.create')->only('create');
        $this->middleware('can:conta_receber.store')->only('store');
        $this->middleware('can:conta_receber.edit')->only(['edit', 'receber', 'cancelar']);
        $this->middleware('can:conta_receber.update')->only('update');
        $this->middleware('can:conta_receber.destroy')->only('destroy');
        $this->middleware('can:conta_receber.filtro')->only('filtro');
        $this->middleware('can:conta_receber.removerFiltro')->only('removerFiltro');
        $this->middleware('can:conta_receber.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        $contas = $this->service->getAllWithFiltersAndPagination(
            $itensFiltered, (int) $perPage, ['escola', 'aluno.pessoa'], 'data_vencimento', 'desc'
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Contas a Receber', 'Financeiro');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('conta_receber.index', compact(
            'contas', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Contas a Receber', 'conta_receber.index', 'Nova Conta a Receber', 'Financeiro');

        return view('conta_receber.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreContaReceberRequest $request)
    {
        $this->service->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Conta a receber registrada com sucesso.');
    }

    public function edit($id)
    {
        $conta = $this->service->getById($id, ['escola', 'aluno']);
        abort_if(! $conta, 404, 'Conta a receber não encontrada.');
        abort_if($conta->status !== 'prevista', 403, 'Apenas contas previstas podem ser editadas.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Contas a Receber', 'conta_receber.index', 'Editar: '.$conta->descricao, 'Financeiro');

        return view('conta_receber.add-edit', array_merge(compact('conta', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateContaReceberRequest $request, $id)
    {
        $conta = $this->service->getById($id);
        abort_if(! $conta || $conta->status !== 'prevista', 403, 'Apenas contas previstas podem ser editadas.');

        $this->service->update($id, $request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Conta a receber atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $conta = $this->service->getById($id);
        abort_if(! $conta, 404);

        // conta recebida/cancelada nao e excluida — preserva o historico financeiro
        if ($conta->status !== 'prevista') {
            return back()->with('error', 'Apenas contas previstas podem ser excluídas.');
        }

        $this->service->delete($id);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    /** Baixa do contas a receber. */
    public function receber(Request $request, $id)
    {
        $conta = $this->service->getById($id);
        abort_if(! $conta, 404);

        $request->validate([
            'forma_recebimento' => ['nullable', 'string', 'in:'.implode(',', array_keys(ContaReceber::FORMAS_RECEBIMENTO))],
            'data_recebimento' => ['nullable', 'date'],
        ]);

        try {
            $this->service->receber($conta, $request->input('forma_recebimento'), $request->input('data_recebimento'));
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Conta baixada como recebida.');
    }

    /** Cancela uma conta prevista. */
    public function cancelar($id)
    {
        $conta = $this->service->getById($id);
        abort_if(! $conta, 404);

        try {
            $this->service->cancelar($conta);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Conta a receber cancelada.');
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
        session()->put("$this->nomeFiltro.urlPadrao", 'conta-receber');
        session()->put("$this->nomeFiltro.rotaRedirect", 'conta_receber.index');
    }

    public function obterFiltrosPrincipais(): array
    {
        return [
            ['name' => 'descricao', 'label' => 'Descrição', 'type' => 'text', 'typeWhere' => 'like'],
            [
                'name' => 'categoria', 'label' => 'Categoria', 'type' => 'select',
                'typeWhere' => 'where', 'options' => ContaReceber::CATEGORIAS,
            ],
            [
                'name' => 'aluno_id', 'label' => 'Aluno', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->alunosOpcoesFiltro(),
            ],
        ];
    }

    public function obterFiltrosSecundarios(): array
    {
        return [
            [
                'name' => 'status', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where', 'options' => ContaReceber::STATUS,
            ],
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'alunos' => Aluno::query()->with('pessoa')->orderBy('id')->get()
                ->mapWithKeys(fn ($a) => [$a->id => ($a->pessoa->nome ?? 'Aluno #'.$a->id).' — '.$a->registro_academico])
                ->all(),
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'categorias' => ContaReceber::CATEGORIAS,
            'formasRecebimento' => ContaReceber::FORMAS_RECEBIMENTO,
        ];
    }

    private function alunosOpcoesFiltro()
    {
        return Aluno::query()->with('pessoa')->orderBy('id')->limit(500)->get()
            ->map(fn ($a) => ['value' => $a->id, 'label' => ($a->pessoa->nome ?? 'Aluno #'.$a->id).' — '.$a->registro_academico])
            ->all();
    }
}
