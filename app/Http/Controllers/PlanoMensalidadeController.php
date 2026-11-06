<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\PlanoMensalidade\StorePlanoMensalidadeRequest;
use App\Http\Requests\PlanoMensalidade\UpdatePlanoMensalidadeRequest;
use App\Services\BreadcrumbService;
use App\Services\PlanoMensalidadeService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class PlanoMensalidadeController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected PlanoMensalidadeService $service,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Planos de Mensalidade';
        $this->nomeFiltro = 'plano_mensalidade';

        $this->middleware('can:plano_mensalidade.index')->only('index');
        $this->middleware('can:plano_mensalidade.create')->only('create');
        $this->middleware('can:plano_mensalidade.store')->only('store');
        $this->middleware('can:plano_mensalidade.edit')->only('edit');
        $this->middleware('can:plano_mensalidade.update')->only('update');
        $this->middleware('can:plano_mensalidade.destroy')->only('destroy');
        $this->middleware('can:plano_mensalidade.filtro')->only('filtro');
        $this->middleware('can:plano_mensalidade.removerFiltro')->only('removerFiltro');
        $this->middleware('can:plano_mensalidade.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $planos = $this->service->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Planos de Mensalidade', 'Financeiro');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('plano_mensalidade.index', compact(
            'planos', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Planos de Mensalidade', 'plano_mensalidade.index', 'Novo Plano', 'Financeiro');

        return view('plano_mensalidade.add-edit', compact('breadcrumb'))
            ->with('titulo', $this->titulo);
    }

    public function store(StorePlanoMensalidadeRequest $request)
    {
        $this->service->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Plano de mensalidade cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $plano = $this->service->getById($id);
        abort_if(! $plano, 404, 'Plano não encontrado.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Planos de Mensalidade', 'plano_mensalidade.index', 'Editar: '.$plano->nome, 'Financeiro');

        return view('plano_mensalidade.add-edit', compact('plano', 'breadcrumb'))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdatePlanoMensalidadeRequest $request, $id)
    {
        $this->service->update($id, $request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Plano de mensalidade atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $this->service->delete($id);

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Plano de mensalidade excluído com sucesso.');
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
        session()->put("$this->nomeFiltro.urlPadrao", 'plano-mensalidade');
        session()->put("$this->nomeFiltro.rotaRedirect", 'plano_mensalidade.index');
    }

    public function obterFiltrosPrincipais(): array
    {
        return [
            [
                'name' => 'nome', 'label' => 'Nome', 'type' => 'text',
                'typeWhere' => 'like',
            ],
        ];
    }

    public function obterFiltrosSecundarios(): array
    {
        return [
            [
                'name' => 'valor', 'label' => 'Valor', 'type' => 'text',
                'typeWhere' => 'where',
            ],
        ];
    }
}
