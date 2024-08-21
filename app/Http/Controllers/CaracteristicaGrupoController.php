<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\CaracteristicaGrupo\StoreCaracteristicaGrupoRequest;
use App\Http\Requests\CaracteristicaGrupo\UpdateCaracteristicaGrupoRequest;
use App\Services\BreadcrumbService;
use App\Services\CaracteristicaGrupoService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class CaracteristicaGrupoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $caracteristicaGrupoService;
    protected $breadcrumbService;

    public function __construct(CaracteristicaGrupoService $caracteristicaGrupoService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "CaracteristicaGrupos";
        $this->nomeFiltro = 'caracteristica grupo';
        $this->caracteristicaGrupoService = $caracteristicaGrupoService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $caracteristicaGrupos = $this->caracteristicaGrupoService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('CaracteristicaGrupos');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('caracteristica grupo.index', compact(
            'caracteristica grupos',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('CaracteristicaGrupos', 'caracteristica grupo.index', 'Criar');

        [$metodo, $rota] = $this->getFormMethodAndRoute();
        return view('caracteristica grupo.add-edit', compact('breadcrumb', 'status', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreCaracteristicaGrupoRequest $request)
    {
        $this->caracteristicaGrupoService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $caracteristicaGrupo = $this->caracteristicaGrupoService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('CaracteristicaGrupos', 'caracteristica grupo.index', "Editar: caracteristicaGrupo->nome");
        [$metodo, $rota] = $this->getFormMethodAndRoute($caracteristicaGrupo);

        return view('caracteristica grupo.add-edit', compact('caracteristica grupo', 'breadcrumb', 'status', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateCaracteristicaGrupoRequest $request, $id)
    {
        $success = $this->caracteristicaGrupoService->update($id, $request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->caracteristicaGrupoService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'caracteristica grupo');
        session()->put("$this->nomeFiltro.rotaRedirect", 'caracteristica grupo.index');
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
     * @param  CaracteristicaGrupo|null  caracteristicaGrupo
     * @return array
     */
    private function getFormMethodAndRoute($caracteristicaGrupo = null)
    {
        if ($caracteristicaGrupo) {
            return [
                'PUT',
                route('caracteristica grupo.update', $caracteristicaGrupo->id),
            ];
        }

        return [
            'POST',
            route('caracteristica grupo.store'),
        ];
    }
}
