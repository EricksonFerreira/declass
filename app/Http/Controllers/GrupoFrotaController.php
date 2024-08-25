<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\GrupoFrota\StoreGrupoFrotaRequest;
use App\Http\Requests\GrupoFrota\UpdateGrupoFrotaRequest;
use App\Models\TipoOpcional;
use App\Services\BreadcrumbService;
use App\Services\CaracteristicaFrotaService;
use App\Services\GrupoFrotaService;
use App\Services\ModeloService;
use App\Services\TipoFrotaService;
use App\Services\TipoOpcionalService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class GrupoFrotaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $grupoFrotaService;
    protected $breadcrumbService;
    protected $tipoFrotaService;
    protected $modeloService;
    protected $caracteristicaFrotaService;
    protected $tipoOpcionalService;

    public function __construct(
        GrupoFrotaService $grupoFrotaService,
        BreadcrumbService $breadcrumbService,
        TipoFrotaService $tipoFrotaService,
        ModeloService $modeloService,
        CaracteristicaFrotaService $caracteristicaFrotaService,
        TipoOpcionalService $tipoOpcionalService
    ) {
        $this->titulo = "Grupos de Frotas";
        $this->nomeFiltro = 'grupo_frota';
        $this->grupoFrotaService = $grupoFrotaService;
        $this->breadcrumbService = $breadcrumbService;
        $this->tipoFrotaService = $tipoFrotaService;
        $this->modeloService = $modeloService;
        $this->caracteristicaFrotaService = $caracteristicaFrotaService;
        $this->tipoOpcionalService = $tipoOpcionalService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $grupoFrotas = $this->grupoFrotaService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Grupos de Frotas');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('grupo_frota.index', compact(
            'grupoFrotas',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Grupos de Frotas', 'grupo_frota.index', 'Criar');

        [$metodo, $rota] = $this->getFormMethodAndRoute();

        $tipoFrotas = $this->tipoFrotaService->getAllAtivos('id','nome','nome');
        $marcas = $this->modeloService->getAllAtivosConcatenado('id','nome');
        $caracteristicas = $this->caracteristicaFrotaService->getAllAtivos('id','nome','nome');
        $tiposOpcionais = $this->tipoOpcionalService->getTipoOpcionais('nome','asc');
        $modelos = $this->modeloService->getAllAtivos('id','nome','nome');

        return view('grupo_frota.add-edit', compact('modelos','caracteristicas','tiposOpcionais','marcas', 'tipoFrotas', 'breadcrumb', 'status', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreGrupoFrotaRequest $request)
    {
        $this->grupoFrotaService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $grupoFrota = $this->grupoFrotaService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Grupos de Frotas', 'grupo_frota.index', "Editar: $grupoFrota->nome");
        [$metodo, $rota] = $this->getFormMethodAndRoute($grupoFrota);
        $tipoFrotas = $this->tipoFrotaService->getAllAtivos('id','nome','nome');
        $modelos = $this->modeloService->getAllAtivosConcatenado('id','nome');
        $caracteristicas = $this->caracteristicaFrotaService->getAllAtivos('id','nome','nome');
        $tiposOpcionais = $this->tipoOpcionalService->getTipoOpcionais('nome','asc');
        $modelos = $this->modeloService->getAllAtivos('id','nome','nome');

        $grupoFrota->opcionais = $grupoFrota->opcionais->pluck('id')->toArray();
        $grupoFrota->modelos = $grupoFrota->modelos->pluck('id')->toArray();
        $grupoFrota->caracteristicas = $grupoFrota->caracteristicas->map(function ($caracteristica) {
            return [
                'id' => $caracteristica->id,
                'valor' => $caracteristica->pivot->valor,
            ];
        });

        return view('grupo_frota.add-edit', compact('modelos','tiposOpcionais','caracteristicas','tipoFrotas',  'grupoFrota', 'breadcrumb', 'status', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateGrupoFrotaRequest $request, $id)
    {
        // dd($request->all());
        $this->grupoFrotaService->update($id, $request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $this->grupoFrotaService->delete($id);
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


    public function getModelosPorGrupoFrota(Request $request)
    {
        $grupoFrotaId = $request->input('grupo_frota_id');
        $modelos = $this->grupoFrotaService->getModelosPorGrupoFrota($grupoFrotaId);
        $opcionais = $this->grupoFrotaService->getTiposOpcionaisComOpcionais($grupoFrotaId);
        $tipoOpcionais = TipoOpcional::all();



        return response()->json([
            'modelo'=>$modelos,
            'tipo_opcional'=>$opcionais
        ]);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'grupo_frota');
        session()->put("$this->nomeFiltro.rotaRedirect", 'grupo_frota.index');
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
     * @param  GrupoFrota|null  $grupoFrota
     * @return array
     */
    private function getFormMethodAndRoute($grupoFrota = null)
    {
        if ($grupoFrota) {
            return [
                'PUT',
                route('grupo_frota.update', $grupoFrota->id),
            ];
        }

        return [
            'POST',
            route('grupo_frota.store'),
        ];
    }
}
