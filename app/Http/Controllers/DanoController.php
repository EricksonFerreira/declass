<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Dano\DanosDetectadosRequest;
use App\Http\Requests\Dano\DanosExistentesRequest;
use App\Http\Requests\Dano\StoreDanoRequest;
use App\Http\Requests\Dano\UpdateDanoRequest;
use App\Services\BreadcrumbService;
use App\Services\CategoriaDanoService;
use App\Services\DanoService;
use App\Services\VeiculoDanoService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class DanoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $danoService;
    protected $breadcrumbService;
    protected $categoriaDanoService;
    protected $veiculoDanoService;

    public function __construct(DanoService $danoService, BreadcrumbService $breadcrumbService, CategoriaDanoService $categoriaDanoService,
    VeiculoDanoService $veiculoDanoService)
    {
        $this->titulo = "Danos";
        $this->nomeFiltro = 'dano';
        $this->danoService = $danoService;
        $this->breadcrumbService = $breadcrumbService;
        $this->categoriaDanoService = $categoriaDanoService;
        $this->veiculoDanoService = $veiculoDanoService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $danos = $this->danoService->getAllWithFiltersAndPagination($itensFiltered, $perPage,['categoria']);
        $categorias = $this->categoriaDanoService->getAllForSelect();

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Danos');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('dano.index', compact(
            'danos',
            'breadcrumb',
            'filters',
            'itensFiltered',
            'additionalFilters',
            'perPages',
            'perPage',
            'categorias'
        ))->with('titulo', $this->titulo);
    }

    public function create(){
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Danos','dano.index','Criar');
        $categorias = $this->categoriaDanoService->getAllForSelect();

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('dano.add-edit', compact('breadcrumb','categorias','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreDanoRequest $request)
    {
        $this->danoService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $dano = $this->danoService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $categorias = $this->categoriaDanoService->getAllForSelect();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Danos','dano.index',"Editar: $dano->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($dano);

        return view('dano.add-edit', compact('dano','categorias','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateDanoRequest $request, $id)
    {
        $success = $this->danoService->update($id,$request->validated());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->danoService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'dano');
        session()->put("$this->nomeFiltro.rotaRedirect", 'dano.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'categoria_id', 'label' => 'Categoria', 'type' => 'select', 'typeWhere' => 'where', 'options' => $this->categoriaDanoService->getAllForSelect()],
            ['name' => 'localizacao', 'label' => 'Localização', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Selecione a localização', 'selected' => false],
                ['value' => 'interior', 'label' => 'Interior', 'selected' => false],
                ['value' => 'exterior', 'label' => 'Exterior', 'selected' => false],
            ]],
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

    public function obterDanosPorCategoriaELocalizacao(Request $request){
        if($request->has('categoria') && $request->has('localizacao')){
            $danos = $this->danoService->getDanosPorCategoriaELocalizacao($request->categoria, $request->localizacao);
            return response()->json($danos);
        }
    }

    /**
     * Função para obter o método e a rota para o formulário.
     *
     * @param  Dano|null  $dano
     * @return array
     */
    private function getFormMethodAndRoute($dano = null)
    {
        if ($dano) {
            return [
                'PUT',
                route('dano.update', $dano->id),
            ];
        }

        return [
            'POST',
            route('dano.store'),
        ];
    }

    public function obterDanosExistentes(DanosExistentesRequest $request, \App\Actions\Reserva\Checkout\Dano\ObterExistentes\ObterDanosExistentesAction $action)
    {
        $veiculoId = (int) $request->input('veiculo_id');
        $codigo = $request->getCodigo();

        return $action->handle($veiculoId, $codigo);
    }

    public function obterDanosDetectados(DanosDetectadosRequest $request, \App\Actions\Reserva\Checkout\Dano\ObterDetectados\ObterDanosDetectadosAction $action)
    {
        $veiculoId = (int) $request->input('veiculo_id');
        $codigo = $request->getCodigo();

        return $action->handle($veiculoId, $codigo);
    }
}
