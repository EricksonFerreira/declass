<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\TipoTransacao\StoreTipoTransacaoRequest;
use App\Http\Requests\TipoTransacao\UpdateTipoTransacaoRequest;
use App\Services\BreadcrumbService;
use App\Services\TipoTransacaoService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;

use App\Traits\FiltroTrait;

class TipoTransacaoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $tipotransacaoService;
    protected $breadcrumbService;

    public function __construct(TipoTransacaoService $tipotransacaoService,BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "Tipos de Transações";
        $this->nomeFiltro = 'tipoTransacao';
        $this->tipotransacaoService = $tipotransacaoService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $tipotransacaos = $this->tipotransacaoService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Tipos de Transações');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('tipo_transacao.index', compact(
            'tipotransacaos',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Tipos de Transações','tipotransacao.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('tipo_transacao.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreTipoTransacaoRequest $request)
    {
        $this->tipotransacaoService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $tipotransacao = $this->tipotransacaoService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Tipos de Transações','tipotransacao.index',"Editar: $tipotransacao->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($tipotransacao);

        return view('tipo_transacao.add-edit', compact('tipotransacao','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateTipoTransacaoRequest $request, $id)
    {
        $success = $this->tipotransacaoService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->tipotransacaoService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'tipotransacao');
        session()->put("$this->nomeFiltro.rotaRedirect", 'tipotransacao.index');
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
     * @param  TipoTransacao|null  $tipotransacao
     * @return array
     */
    private function getFormMethodAndRoute($tipotransacao = null)
    {
        if ($tipotransacao) {
            return [
                'PUT',
                route('tipotransacao.update', $tipotransacao->id),
            ];
        }

        return [
            'POST',
            route('tipotransacao.store'),
        ];
    }
}
