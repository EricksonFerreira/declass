<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\OpcionalGrupoVeiculo\StoreOpcionalGrupoVeiculoRequest;
use App\Http\Requests\OpcionalGrupoVeiculo\UpdateOpcionalGrupoVeiculoRequest;
use App\Services\BreadcrumbService;
use App\Services\OpcionalGrupoVeiculoService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class OpcionalGrupoVeiculoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $opcional_grupo_veiculoService;
    protected $breadcrumbService;

    public function __construct(OpcionalGrupoVeiculoService $opcional_grupo_veiculoService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "OpcionalGrupoVeiculos";
        $this->nomeFiltro = 'opcional_grupo_veiculo';
        $this->opcional_grupo_veiculoService = $opcional_grupo_veiculoService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $opcional_grupo_veiculos = $this->opcional_grupo_veiculoService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('OpcionalGrupoVeiculos');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('opcional_grupo_veiculo.index', compact(
            'opcional_grupo_veiculos',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('OpcionalGrupoVeiculos','opcional_grupo_veiculo.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('opcional_grupo_veiculo.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreOpcionalGrupoVeiculoRequest $request)
    {
        $this->opcional_grupo_veiculoService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $opcional_grupo_veiculo = $this->opcional_grupo_veiculoService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('OpcionalGrupoVeiculos','opcional_grupo_veiculo.index',"Editar: $opcional_grupo_veiculo->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($opcional_grupo_veiculo);

        return view('opcional_grupo_veiculo.add-edit', compact('opcional_grupo_veiculo','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateOpcionalGrupoVeiculoRequest $request, $id)
    {
        $success = $this->opcional_grupo_veiculoService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->opcional_grupo_veiculoService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'opcional_grupo_veiculo');
        session()->put("$this->nomeFiltro.rotaRedirect", 'opcional_grupo_veiculo.index');
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
     * @param  OpcionalGrupoVeiculo|null  $opcional_grupo_veiculo
     * @return array
     */
    private function getFormMethodAndRoute($opcional_grupo_veiculo = null)
    {
        if ($opcional_grupo_veiculo) {
            return [
                'PUT',
                route('opcional_grupo_veiculo.update', $opcional_grupo_veiculo->id),
            ];
        }

        return [
            'POST',
            route('opcional_grupo_veiculo.store'),
        ];
    }
}