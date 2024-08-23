<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Veiculo\StoreVeiculoRequest;
use App\Http\Requests\Veiculo\UpdateVeiculoRequest;
use App\Services\BreadcrumbService;
use App\Services\GrupoFrotaService;
use App\Services\MarcaService;
use App\Services\ModeloService;
use App\Services\SegmentoService;
use App\Services\StatusVeiculoService;
use App\Services\TipoFrotaService;
use App\Services\VeiculoService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class VeiculoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $veiculoService;
    protected $breadcrumbService;
    protected $tipoFrotaService;
    protected $grupoFrotaService;
    protected $marcaService;
    protected $modeloService;
    protected $segmentoService;
    protected $statusVeiculoService;


    public function __construct(
        VeiculoService $veiculoService,
        BreadcrumbService $breadcrumbService,
        TipoFrotaService $tipoFrotaService,
        GrupoFrotaService $grupoFrotaService,
        MarcaService $marcaService,
        ModeloService $modeloService,
        SegmentoService $segmentoService,
        StatusVeiculoService $statusVeiculoService
    ) {
        $this->titulo = "Veiculos";
        $this->nomeFiltro = 'veiculo';
        $this->veiculoService = $veiculoService;
        $this->breadcrumbService = $breadcrumbService;
        $this->tipoFrotaService = $tipoFrotaService;
        $this->grupoFrotaService = $grupoFrotaService;
        $this->marcaService = $marcaService;
        $this->modeloService = $modeloService;
        $this->segmentoService = $segmentoService;
        $this->statusVeiculoService = $statusVeiculoService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $veiculos = $this->veiculoService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Veiculos');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('veiculo.index', compact(
            'veiculos',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPages',
            'perPage'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Veiculos', 'veiculo.index', 'Criar');

        $tipoFrotas = $this->tipoFrotaService->getAllAtivos();
        $grupoFrotas = $this->grupoFrotaService->getAllAtivos();
        $marcas = $this->marcaService->getAllAtivos();
        $modelos = $this->modeloService->getAllAtivos();
        $segmentos = $this->segmentoService->getAllAtivos();
        $statusVeiculos = $this->statusVeiculoService->getAllAtivos();

        [$metodo, $rota] = $this->getFormMethodAndRoute();
        return view('veiculo.add-edit', compact(
            'tipoFrotas',
            'grupoFrotas',
            'marcas',
            'modelos',
            'segmentos',
            'statusVeiculos',
            'breadcrumb',
            'status',
            'metodo',
            'rota'
        ))->with('titulo', $this->titulo);
    }

    public function store(StoreVeiculoRequest $request)
    {
        $this->veiculoService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $veiculo = $this->veiculoService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Veiculos', 'veiculo.index', "Editar: $veiculo->nome");
        [$metodo, $rota] = $this->getFormMethodAndRoute($veiculo);


        $tipoFrotas = $this->tipoFrotaService->getAllAtivos();
        $grupoFrotas = $this->grupoFrotaService->getAllAtivos();
        $marcas = $this->marcaService->getAllAtivos();
        $modelos = $this->modeloService->getAllAtivos();
        $segmentos = $this->segmentoService->getAllAtivos();
        $statusVeiculos = $this->statusVeiculoService->getAllAtivos();

        return view('veiculo.add-edit', compact(
            'veiculo',
            'tipoFrotas',
            'grupoFrotas',
            'marcas',
            'modelos',
            'segmentos',
            'statusVeiculos',
            'breadcrumb',
            'status',
            'metodo',
            'rota'
        ))->with('titulo', $this->titulo);
    }

    public function update(UpdateVeiculoRequest $request, $id)
    {
        $success = $this->veiculoService->update($id, $request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->veiculoService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'veiculo');
        session()->put("$this->nomeFiltro.rotaRedirect", 'veiculo.index');
    }

    public function obterFiltrosPrincipais()
    {
        $tipoFrota = $this->tipoFrotaService->getAllAtivos();
        $dataTipoFrota = FiltroTrait::formatOptions($tipoFrota, 'Selecione o tipo de Frota');

        $grupoFrota = $this->grupoFrotaService->getAllAtivos();
        $dataGrupoFrota = FiltroTrait::formatOptions($grupoFrota, 'Selecione o grupo de Frota');

        $marca = $this->marcaService->getAllAtivos();
        $dataMarca = FiltroTrait::formatOptions($marca, 'Selecione a marca');

        $modelo = $this->modeloService->getAllAtivos();
        $dataModelo = FiltroTrait::formatOptions($modelo, 'Selecione o modelo');

        $statusVeiculo = $this->statusVeiculoService->getAllAtivos();
        $dataStatusVeiculo = FiltroTrait::formatOptions($statusVeiculo, 'Selecione o status');

        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'tipo_frota_id', 'label'=> 'Tipos de Frotas','type'=>'select','typeWhere' => 'where','options'=> $dataTipoFrota],
            ['name' => 'grupo_frota_id', 'label'=> 'Grupos de Frotas','type'=>'select','typeWhere' => 'where','options'=> $dataGrupoFrota],
            ['name' => 'marca_id', 'label'=> 'Marcas','type'=>'select','typeWhere' => 'where','options'=> $dataMarca],
            ['name' => 'modelo_id', 'label'=> 'Modelos','type'=>'select','typeWhere' => 'where','options'=> $dataModelo],
            ['name' => 'status_veiculo_id', 'label'=> 'Status','type'=>'select','typeWhere' => 'where','options'=> $dataStatusVeiculo],
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
     * @param  Veiculo|null  $veiculo
     * @return array
     */
    private function getFormMethodAndRoute($veiculo = null)
    {
        if ($veiculo) {
            return [
                'PUT',
                route('veiculo.update', $veiculo->id),
            ];
        }

        return [
            'POST',
            route('veiculo.store'),
        ];
    }
}
