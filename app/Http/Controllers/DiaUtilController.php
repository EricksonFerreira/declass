<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\DiaUtil\StoreDiaUtilRequest;
use App\Http\Requests\DiaUtil\UpdateDiaUtilRequest;
use App\Services\BreadcrumbService;
use App\Services\DiaUtilService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class DiaUtilController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $dia_utilService;
    protected $breadcrumbService;

    public function __construct(DiaUtilService $dia_utilService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "Dias Úteis";
        $this->nomeFiltro = 'dia_util';
        $this->dia_utilService = $dia_utilService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $dia_utils = $this->dia_utilService->getAllWithFiltersAndPagination($itensFiltered, $perPage,[],'dia','asc');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('DiaUtils');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('dia_util.index', compact(
            'dia_utils',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('DiaUtils','dia_util.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('dia_util.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreDiaUtilRequest $request)
    {
        $this->dia_utilService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Registro criado com sucesso.');
    }

    public function edit($id)
    {
        $dia_util = $this->dia_utilService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('DiaUtils','dia_util.index',"Editar: $dia_util->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($dia_util);

        return view('dia_util.add-edit', compact('dia_util','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateDiaUtilRequest $request, $id)
    {
        $success = $this->dia_utilService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Registro atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->dia_utilService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'dia_util');
        session()->put("$this->nomeFiltro.rotaRedirect", 'dia_util.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'dia', 'label' => 'Dia', 'type' => 'text', 'typeWhere' => 'where'],
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
     * @param  DiaUtil|null  $dia_util
     * @return array
     */
    private function getFormMethodAndRoute($dia_util = null)
    {
        if ($dia_util) {
            return [
                'PUT',
                route('dia_util.update', $dia_util->id),
            ];
        }

        return [
            'POST',
            route('dia_util.store'),
        ];
    }
}
