<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\CaracteristicaGrupoFrota\StoreCaracteristicaGrupoFrotaRequest;
use App\Http\Requests\CaracteristicaGrupoFrota\UpdateCaracteristicaGrupoFrotaRequest;
use App\Services\BreadcrumbService;
use App\Services\CaracteristicaGrupoFrotaService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class CaracteristicaGrupoFrotaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $caracteristicaGrupoFrotaService;
    protected $breadcrumbService;

    public function __construct(CaracteristicaGrupoFrotaService $caracteristicaGrupoFrotaService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "CaracteristicaGrupoFrotas";
        $this->nomeFiltro = 'caracteristica grupo frota';
        $this->caracteristicaGrupoFrotaService= $caracteristicaGrupoFrotaService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $caracteristicaGrupoFrotaService = $this->caracteristicaGrupoFrotaService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('CaracteristicaGrupoFrotas');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('caracteristica grupo frota.index', compact(
            'caracteristica grupo frotas',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('CaracteristicaGrupoFrotas','caracteristica grupo frota.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('caracteristica grupo frota.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreCaracteristicaGrupoFrotaRequest $request)
    {
        $this->caracteristicaGrupoFrotaService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $caracteristicaGrupoFrota = $this->caracteristicaGrupoFrotaService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('CaracteristicaGrupoFrotas','caracteristica grupo frota.index',"Editar: $caracteristicaGrupoFrota->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($caracteristicaGrupoFrota);

        return view('caracteristica grupo frota.add-edit', compact('caracteristica grupo frota','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateCaracteristicaGrupoFrotaRequest $request, $id)
    {
        $success = $this->caracteristicaGrupoFrotaService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->caracteristicaGrupoFrotaService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'caracteristica grupo frota');
        session()->put("$this->nomeFiltro.rotaRedirect", 'caracteristica grupo frota.index');
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
     * @param  CaracteristicaGrupoFrota|null  $caracteristica grupo frota
     * @return array
     */
    private function getFormMethodAndRoute($caracteristicaGrupoFrotaService = null)
    {
        if ($caracteristicaGrupoFrotaService) {
            return [
                'PUT',
                route('caracteristica grupo frota.update', $caracteristicaGrupoFrotaService->id),
            ];
        }

        return [
            'POST',
            route('caracteristica grupo frota.store'),
        ];
    }
}
