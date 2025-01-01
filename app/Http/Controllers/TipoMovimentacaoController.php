<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\TipoMovimentacao\StoreTipoMovimentacaoRequest;
use App\Http\Requests\TipoMovimentacao\UpdateTipoMovimentacaoRequest;
use App\Services\BreadcrumbService;
use App\Services\TipoMovimentacaoService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class TipoMovimentacaoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $tipo_movimentacaoService;
    protected $breadcrumbService;

    public function __construct(TipoMovimentacaoService $tipo_movimentacaoService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "Tipos de Movimentações";
        $this->nomeFiltro = 'tipo_movimentacao';
        $this->tipo_movimentacaoService = $tipo_movimentacaoService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $tipo_movimentacaos = $this->tipo_movimentacaoService->getAllWithFiltersAndPagination($itensFiltered, $perPage,[],'nome','asc');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Tipos de Movimentações');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('tipo_movimentacao.index', compact(
            'tipo_movimentacaos',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Tipos de Movimentações','tipo_movimentacao.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('tipo_movimentacao.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreTipoMovimentacaoRequest $request)
    {
        $this->tipo_movimentacaoService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $tipo_movimentacao = $this->tipo_movimentacaoService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Tipos de Movimentações','tipo_movimentacao.index',"Editar: $tipo_movimentacao->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($tipo_movimentacao);

        return view('tipo_movimentacao.add-edit', compact('tipo_movimentacao','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateTipoMovimentacaoRequest $request, $id)
    {
        $success = $this->tipo_movimentacaoService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->tipo_movimentacaoService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'tipo_movimentacao');
        session()->put("$this->nomeFiltro.rotaRedirect", 'tipo_movimentacao.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'descricao', 'label' => 'Descrição', 'type' => 'text', 'typeWhere' => 'like'],
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
     * @param  TipoMovimentacao|null  $tipo_movimentacao
     * @return array
     */
    private function getFormMethodAndRoute($tipo_movimentacao = null)
    {
        if ($tipo_movimentacao) {
            return [
                'PUT',
                route('tipo_movimentacao.update', $tipo_movimentacao->id),
            ];
        }

        return [
            'POST',
            route('tipo_movimentacao.store'),
        ];
    }
}
