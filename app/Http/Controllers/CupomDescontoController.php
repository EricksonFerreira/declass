<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\CupomDesconto\StoreCupomDescontoRequest;
use App\Http\Requests\CupomDesconto\UpdateCupomDescontoRequest;
use App\Services\BreadcrumbService;
use App\Services\CupomDescontoService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class CupomDescontoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $cupom_descontoService;
    protected $breadcrumbService;

    public function __construct(CupomDescontoService $cupom_descontoService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "Cupom de Descontos";
        $this->nomeFiltro = 'cupom_desconto';
        $this->cupom_descontoService = $cupom_descontoService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $cupom_descontos = $this->cupom_descontoService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Cupom de Descontos');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('cupom_desconto.index', compact(
            'cupom_descontos',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPages',
            'perPage'
        ))->with('titulo', $this->titulo);
    }

    public function create(){
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Cupom de Descontos','cupom_desconto.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('cupom_desconto.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreCupomDescontoRequest $request)
    {
        $this->cupom_descontoService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $cupom_desconto = $this->cupom_descontoService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Cupom de Descontos','cupom_desconto.index',"Editar: $cupom_desconto->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($cupom_desconto);

        return view('cupom_desconto.add-edit', compact('cupom_desconto','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateCupomDescontoRequest $request, $id)
    {
        $success = $this->cupom_descontoService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->cupom_descontoService->delete($id);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function filtro(Request $request)
    {
        $this->criarFiltros($request, $this->nomeFiltro);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function removerFiltro(Request $request)
    {
        $atributo = $request->input('atributo') ?? null;
        $this->removeSessao($this->nomeFiltro, $atributo);
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function setPerPage(Request $request)
    {
        // Salva o valor de paginação na sessão
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));

        // Redireciona de volta para a página de listagem
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'cupom_desconto');
        session()->put("$this->nomeFiltro.rotaRedirect", 'cupom_desconto.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'ativo', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Selecione o status', 'selected' => false],
                ['value' => 'S', 'label' => 'Ativo', 'selected' => false],
                ['value' => 'N', 'label' => 'Inativo', 'selected' => false]
            ]]
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            // Adicione filtros secundários aqui, se necessário
        ];
    }

    public function verificarCupom(Request $request)
    {
        $cupom = $this->cupom_descontoService->verificarCupom($request->input('cupom'));
        return response()->json($cupom);
    }

    /**
     * Função para obter o método e a rota para o formulário.
     *
     * @param  CupomDesconto|null  $cupom_desconto
     * @return array
     */
    private function getFormMethodAndRoute($cupom_desconto = null)
    {
        if ($cupom_desconto) {
            return [
                'PUT',
                route('cupom_desconto.update', $cupom_desconto->id),
            ];
        }

        return [
            'POST',
            route('cupom_desconto.store'),
        ];
    }
}
