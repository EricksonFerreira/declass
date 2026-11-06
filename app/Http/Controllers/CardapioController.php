<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Cardapio\StoreCardapioRequest;
use App\Http\Requests\Cardapio\UpdateCardapioRequest;
use App\Models\Cardapio;
use App\Models\Escola;
use App\Services\BreadcrumbService;
use App\Services\CardapioService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class CardapioController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected CardapioService $cardapioService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Cardápios';
        $this->nomeFiltro = 'cardapio';

        $this->middleware('can:cardapio.index')->only('index');
        $this->middleware('can:cardapio.create')->only('create');
        $this->middleware('can:cardapio.store')->only('store');
        $this->middleware('can:cardapio.edit')->only('edit');
        $this->middleware('can:cardapio.update')->only('update');
        $this->middleware('can:cardapio.destroy')->only('destroy');
        $this->middleware('can:cardapio.filtro')->only('filtro');
        $this->middleware('can:cardapio.removerFiltro')->only('removerFiltro');
        $this->middleware('can:cardapio.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        $cardapios = $this->cardapioService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['escola'], 'data', 'desc'
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Cardápios', 'Alimentação');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('cardapio.index', compact(
            'cardapios', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Cardápios', 'cardapio.index', 'Novo Cardápio', 'Alimentação');

        return view('cardapio.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreCardapioRequest $request)
    {
        $this->cardapioService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Cardápio cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $cardapio = $this->cardapioService->getById($id, ['escola']);
        abort_if(! $cardapio, 404, 'Cardápio não encontrado.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Cardápios', 'cardapio.index', "Editar: {$cardapio->tipoLabel()} {$cardapio->data->format('d/m/Y')}", 'Alimentação');

        return view('cardapio.add-edit', array_merge(compact('cardapio', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateCardapioRequest $request, $id)
    {
        $cardapio = $this->cardapioService->update($id, $request->validated());
        abort_if(! $cardapio, 403, 'Sem acesso para editar este cardápio.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Cardápio atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->cardapioService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este cardápio.');

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
        session()->put("$this->nomeFiltro.urlPadrao", 'cardapio');
        session()->put("$this->nomeFiltro.rotaRedirect", 'cardapio.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            [
                'name' => 'tipo', 'label' => 'Tipo de refeição', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Cardapio::TIPOS,
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
                'name' => 'ativo', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Ativo', 'N' => 'Inativo'],
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'tipos' => Cardapio::TIPOS,
        ];
    }
}
