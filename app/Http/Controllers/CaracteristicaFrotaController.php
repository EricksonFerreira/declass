<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\CaracteristicaFrota\StoreCaracteristicaFrotaRequest;
use App\Http\Requests\CaracteristicaFrota\UpdateCaracteristicaFrotaRequest;
use App\Services\BreadcrumbService;
use App\Services\CaracteristicaFrotaService;
use App\Services\TipoValorCaracteristicaFrotaService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class CaracteristicaFrotaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $caracteristicaFrotaService;
    protected $breadcrumbService;
    protected $valorCaracteristicaTipoFrotaService;

    public function __construct(CaracteristicaFrotaService $caracteristicaFrotaService, BreadcrumbService $breadcrumbService, TipoValorCaracteristicaFrotaService $valorCaracteristicaTipoFrotaService)
    {
        $this->titulo = "Características dos Frotas";
        $this->nomeFiltro = 'caracteristica_frota';
        $this->caracteristicaFrotaService = $caracteristicaFrotaService;
        $this->breadcrumbService = $breadcrumbService;
        $this->valorCaracteristicaTipoFrotaService = $valorCaracteristicaTipoFrotaService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $caracteristicaFrotas = $this->caracteristicaFrotaService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Caracteristicas dos Frotas');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('caracteristica_frota.index', compact(
            'caracteristicaFrotas',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Caracteristicas dos Frotas','caracteristica_frota.index','Criar');

        $valorCaracteristicaTipoFrota = $this->valorCaracteristicaTipoFrotaService->getAllValores();

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('caracteristica_frota.add-edit', compact('valorCaracteristicaTipoFrota','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreCaracteristicaFrotaRequest $request)
    {
        $this->caracteristicaFrotaService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $caracteristicaFrota = $this->caracteristicaFrotaService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Caracteristicas dos Frotas','caracteristica_frota.index',"Editar: $caracteristicaFrota->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($caracteristicaFrota);
        $valorCaracteristicaTipoFrota = $this->valorCaracteristicaTipoFrotaService->getAllValores();

        return view('caracteristica_frota.add-edit', compact('valorCaracteristicaTipoFrota','caracteristicaFrota','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateCaracteristicaFrotaRequest $request, $id)
    {
        $this->caracteristicaFrotaService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->caracteristicaFrotaService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'caracteristica_frota');
        session()->put("$this->nomeFiltro.rotaRedirect", 'caracteristica_frota.index');
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
     * @param  CaracteristicaFrota|null  $caracteristicaFrota
     * @return array
     */
    private function getFormMethodAndRoute($caracteristicaFrota = null)
    {
        if ($caracteristicaFrota) {
            return [
                'PUT',
                route('caracteristica_frota.update', $caracteristicaFrota->id),
            ];
        }

        return [
            'POST',
            route('caracteristica_frota.store'),
        ];
    }
}
