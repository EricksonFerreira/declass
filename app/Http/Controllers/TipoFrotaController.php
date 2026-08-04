<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\TipoFrota\StoreTipoFrotaRequest;
use App\Http\Requests\TipoFrota\UpdateTipoFrotaRequest;
use App\Services\BreadcrumbService;
use App\Services\CaracteristicaFrotaService;
use App\Services\TipoFrotaService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class TipoFrotaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $tipo_frotaService;
    protected $breadcrumbService;
    protected $caracteristicaFrotaService;
    public function __construct(TipoFrotaService $tipo_frotaService, BreadcrumbService $breadcrumbService, CaracteristicaFrotaService $caracteristicaFrotaService)
    {
        $this->titulo = "Tipos de Frotas";
        $this->nomeFiltro = 'tipo_frota';
        $this->tipo_frotaService = $tipo_frotaService;
        $this->breadcrumbService = $breadcrumbService;
        $this->caracteristicaFrotaService = $caracteristicaFrotaService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $tipo_frotas = $this->tipo_frotaService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Tipos de Frotas');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();
        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('tipo_frota.index', compact(
            'tipo_frotas',
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
        $tipo_frota = (object) ['ativo' => 'S'];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Tipos de Frotas','tipo_frota.index','Criar');
        $caracteristicas = $this->caracteristicaFrotaService->getAll();

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('tipo_frota.add-edit', compact('caracteristicas','breadcrumb','status','metodo','rota','tipo_frota'))->with('titulo', $this->titulo);
    }

    public function store(StoreTipoFrotaRequest $request)
    {
        $this->tipo_frotaService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $tipo_frota = $this->tipo_frotaService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Tipos de Frotas','tipo_frota.index',"Editar: $tipo_frota->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($tipo_frota);
        $caracteristicas = $this->caracteristicaFrotaService->getAll();
        $caracteristicasTipoFrota = $tipo_frota->caracteristicas->pluck('id')->toArray();

        return view('tipo_frota.add-edit', compact('caracteristicasTipoFrota','caracteristicas','tipo_frota','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateTipoFrotaRequest $request, $id)
    {
        $success = $this->tipo_frotaService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->tipo_frotaService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'tipo_frota');
        session()->put("$this->nomeFiltro.rotaRedirect", 'tipo_frota.index');
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
     * @param  TipoFrota|null  $tipo_frota
     * @return array
     */
    private function getFormMethodAndRoute($tipo_frota = null)
    {
        if ($tipo_frota) {
            return [
                'PUT',
                route('tipo_frota.update', $tipo_frota->id),
            ];
        }

        return [
            'POST',
            route('tipo_frota.store'),
        ];
    }
}
