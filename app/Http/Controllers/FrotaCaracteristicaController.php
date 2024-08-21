<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\FrotaCaracteristica\StoreFrotaCaracteristicaRequest;
use App\Http\Requests\FrotaCaracteristica\UpdateFrotaCaracteristicaRequest;
use App\Services\BreadcrumbService;
use App\Services\FrotaCaracteristicaService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class FrotaCaracteristicaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $frotaCaracteristicaService;
    protected $breadcrumbService;

    public function __construct(FrotaCaracteristicaService $frotaCaracteristicaService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "FrotaCaracteristicas";
        $this->nomeFiltro = 'frota caracteristica';
        $this->frotaCaracteristicaService = $frotaCaracteristicaService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $frotaCaracteristicas = $this->frotaCaracteristicaService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('FrotaCaracteristicas');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('frota caracteristica.index', compact(
            'frota caracteristicas',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('FrotaCaracteristicas','frota caracteristica.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('frota caracteristica.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreFrotaCaracteristicaRequest $request)
    {
        $this->frotaCaracteristicaService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $frotaCaracteristicas = $this->frotaCaracteristicaService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('FrotaCaracteristicas','frota caracteristica.index',"Editar: $frotaCaracteristicas->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($frotaCaracteristicas);

        return view('frota caracteristica.add-edit', compact('frota caracteristica','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateFrotaCaracteristicaRequest $request, $id)
    {
        $success = $this->frotaCaracteristicaService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->frotaCaracteristicaService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'frota caracteristica');
        session()->put("$this->nomeFiltro.rotaRedirect", 'frota caracteristica.index');
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
     * @param  FrotaCaracteristica|null  $frotaCaracteristicas
     * @return array
     */
    private function getFormMethodAndRoute($frotaCaracteristicas = null)
    {
        if ($frotaCaracteristicas) {
            return [
                'PUT',
                route('frota caracteristica.update', $frotaCaracteristicas->id),
            ];
        }

        return [
            'POST',
            route('frota caracteristica.store'),
        ];
    }
}
