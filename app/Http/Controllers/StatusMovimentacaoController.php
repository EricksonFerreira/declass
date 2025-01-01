<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\StatusMovimentacao\StoreStatusMovimentacaoRequest;
use App\Http\Requests\StatusMovimentacao\UpdateStatusMovimentacaoRequest;
use App\Services\BreadcrumbService;
use App\Services\StatusMovimentacaoService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class StatusMovimentacaoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $status_movimentacaoService;
    protected $breadcrumbService;

    public function __construct(StatusMovimentacaoService $status_movimentacaoService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "Status das Movimentações";
        $this->nomeFiltro = 'status_movimentacao';
        $this->status_movimentacaoService = $status_movimentacaoService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $status_movimentacaos = $this->status_movimentacaoService->getAllWithFiltersAndPagination($itensFiltered, $perPage,[],'nome','asc');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Status das Movimentações');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('status_movimentacao.index', compact(
            'status_movimentacaos',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Status das Movimentações','status_movimentacao.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('status_movimentacao.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreStatusMovimentacaoRequest $request)
    {
        $this->status_movimentacaoService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $status_movimentacao = $this->status_movimentacaoService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Status das Movimentações','status_movimentacao.index',"Editar: $status_movimentacao->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($status_movimentacao);

        return view('status_movimentacao.add-edit', compact('status_movimentacao','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateStatusMovimentacaoRequest $request, $id)
    {
        $success = $this->status_movimentacaoService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->status_movimentacaoService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'status_movimentacao');
        session()->put("$this->nomeFiltro.rotaRedirect", 'status_movimentacao.index');
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

    /**
     * Função para obter o método e a rota para o formulário.
     *
     * @param  StatusMovimentacao|null  $status_movimentacao
     * @return array
     */
    private function getFormMethodAndRoute($status_movimentacao = null)
    {
        if ($status_movimentacao) {
            return [
                'PUT',
                route('status_movimentacao.update', $status_movimentacao->id),
            ];
        }

        return [
            'POST',
            route('status_movimentacao.store'),
        ];
    }
}
