<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Modelo\StoreModeloRequest;
use App\Http\Requests\Modelo\UpdateModeloRequest;
use App\Services\BreadcrumbService;
use App\Services\MarcaService;
use App\Services\ModeloService;
use App\Services\SegmentoService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class ModeloController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $modeloService;
    protected $breadcrumbService;

    protected $marcaService;

    protected $segmentoService;

    public function __construct(ModeloService $modeloService, BreadcrumbService $breadcrumbService, MarcaService $marcaService, SegmentoService $segmentoService)
    {
        $this->titulo = "Modelos";
        $this->nomeFiltro = 'modelo';
        $this->modeloService = $modeloService;
        $this->breadcrumbService = $breadcrumbService;
        $this->marcaService = $marcaService;
        $this->segmentoService = $segmentoService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $modelos = $this->modeloService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Modelos');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('modelo.index', compact(
            'modelos',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Modelos','modelo.index','Criar');
        $marcas = $this->marcaService->getAllAtivos();
        $segmentos = $this->segmentoService->getAllAtivos();


        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('modelo.add-edit', compact('marcas','segmentos','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreModeloRequest $request)
    {
        $this->modeloService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $modelo = $this->modeloService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Modelos','modelo.index',"Editar: $modelo->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($modelo);
        $marcas = $this->marcaService->getAllAtivos();
        $segmentos = $this->segmentoService->getAllAtivos();


        return view('modelo.add-edit', compact('marcas','segmentos','modelo','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateModeloRequest $request, $id)
    {
        $this->modeloService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $this->modeloService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'modelo');
        session()->put("$this->nomeFiltro.rotaRedirect", 'modelo.index');
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
     * @param  Modelo|null  $modelo
     * @return array
     */
    private function getFormMethodAndRoute($modelo = null)
    {
        if ($modelo) {
            return [
                'PUT',
                route('modelo.update', $modelo->id),
            ];
        }

        return [
            'POST',
            route('modelo.store'),
        ];
    }
}
