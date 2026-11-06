<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Despesa\StoreDespesaRequest;
use App\Http\Requests\Despesa\UpdateDespesaRequest;
use App\Models\Despesa;
use App\Models\Escola;
use App\Services\BreadcrumbService;
use App\Services\DespesaService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class DespesaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected DespesaService $service,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Contas a Pagar';
        $this->nomeFiltro = 'despesa';

        $this->middleware('can:despesa.index')->only('index');
        $this->middleware('can:despesa.create')->only('create');
        $this->middleware('can:despesa.store')->only('store');
        $this->middleware('can:despesa.edit')->only(['edit', 'pagar', 'cancelar']);
        $this->middleware('can:despesa.update')->only('update');
        $this->middleware('can:despesa.destroy')->only('destroy');
        $this->middleware('can:despesa.filtro')->only('filtro');
        $this->middleware('can:despesa.removerFiltro')->only('removerFiltro');
        $this->middleware('can:despesa.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        $despesas = $this->service->getAllWithFiltersAndPagination(
            $itensFiltered, (int) $perPage, ['escola'], 'data_vencimento', 'desc'
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Contas a Pagar', 'Financeiro');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('despesa.index', compact(
            'despesas', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Contas a Pagar', 'despesa.index', 'Nova Despesa', 'Financeiro');

        return view('despesa.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreDespesaRequest $request)
    {
        $this->service->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Despesa registrada com sucesso.');
    }

    public function edit($id)
    {
        $despesa = $this->service->getById($id, ['escola']);
        abort_if(! $despesa, 404, 'Despesa não encontrada.');
        abort_if($despesa->status !== 'prevista', 403, 'Apenas despesas previstas podem ser editadas.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Contas a Pagar', 'despesa.index', 'Editar: '.$despesa->descricao, 'Financeiro');

        return view('despesa.add-edit', array_merge(compact('despesa', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateDespesaRequest $request, $id)
    {
        $despesa = $this->service->getById($id);
        abort_if(! $despesa || $despesa->status !== 'prevista', 403, 'Apenas despesas previstas podem ser editadas.');

        $this->service->update($id, $request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Despesa atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $despesa = $this->service->getById($id);
        abort_if(! $despesa, 404);

        // despesa paga/cancelada nao e excluida — preserva o historico financeiro
        if ($despesa->status !== 'prevista') {
            return back()->with('error', 'Apenas despesas previstas podem ser excluídas.');
        }

        $this->service->delete($id);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    /** Baixa do contas a pagar. */
    public function pagar(Request $request, $id)
    {
        $despesa = $this->service->getById($id);
        abort_if(! $despesa, 404);

        $request->validate([
            'forma_pagamento' => ['nullable', 'string', 'in:'.implode(',', array_keys(Despesa::FORMAS_PAGAMENTO))],
            'data_pagamento' => ['nullable', 'date'],
        ]);

        try {
            $this->service->pagar($despesa, $request->input('forma_pagamento'), $request->input('data_pagamento'));
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Despesa baixada como paga.');
    }

    /** Cancela uma despesa prevista. */
    public function cancelar($id)
    {
        $despesa = $this->service->getById($id);
        abort_if(! $despesa, 404);

        try {
            $this->service->cancelar($despesa);
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Despesa cancelada.');
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
        session()->put("$this->nomeFiltro.urlPadrao", 'despesa');
        session()->put("$this->nomeFiltro.rotaRedirect", 'despesa.index');
    }

    public function obterFiltrosPrincipais(): array
    {
        return [
            ['name' => 'descricao', 'label' => 'Descrição', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'fornecedor', 'label' => 'Fornecedor', 'type' => 'text', 'typeWhere' => 'like'],
            [
                'name' => 'categoria', 'label' => 'Categoria', 'type' => 'select',
                'typeWhere' => 'where', 'options' => Despesa::CATEGORIAS,
            ],
        ];
    }

    public function obterFiltrosSecundarios(): array
    {
        return [
            [
                'name' => 'status', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where', 'options' => Despesa::STATUS,
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
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'categorias' => Despesa::CATEGORIAS,
            'formasPagamento' => Despesa::FORMAS_PAGAMENTO,
        ];
    }
}
