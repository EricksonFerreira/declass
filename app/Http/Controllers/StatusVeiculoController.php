<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\StatusVeiculo\StoreStatusVeiculoRequest;
use App\Http\Requests\StatusVeiculo\UpdateStatusVeiculoRequest;
use App\Services\BreadcrumbService;
use App\Services\StatusVeiculoService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class StatusVeiculoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;

    protected $status_veiculoService;
    protected $breadcrumbService;

    public function __construct(StatusVeiculoService $status_veiculoService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = 'Status dos Veículos';
        $this->nomeFiltro = 'status_veiculo';
        $this->status_veiculoService = $status_veiculoService;
        $this->breadcrumbService = $breadcrumbService;
        $this->middleware('can:acessar-superadmin')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $status_veiculos = $this->status_veiculoService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Status dos Veículos');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('status_veiculo.index', compact(
            'status_veiculos',
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
            'N' => 'Inativo',
        ];
        $opcoesCor = $this->opcoesCor();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Status dos Veículos', 'status_veiculo.index', 'Criar');

        [$metodo, $rota] = $this->getFormMethodAndRoute();

        return view('status_veiculo.add-edit', compact('breadcrumb', 'status', 'metodo', 'rota', 'opcoesCor'))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreStatusVeiculoRequest $request)
    {
        $this->status_veiculoService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $status_veiculo = $this->status_veiculoService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo',
        ];
        $opcoesCor = $this->opcoesCor();
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit(
            'Status dos Veículos',
            'status_veiculo.index',
            "Editar: $status_veiculo->nome"
        );
        [$metodo, $rota] = $this->getFormMethodAndRoute($status_veiculo);

        return view('status_veiculo.add-edit', compact('status_veiculo', 'breadcrumb', 'status', 'metodo', 'rota', 'opcoesCor'))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateStatusVeiculoRequest $request, $id)
    {
        $this->status_veiculoService->update($id, $request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $this->status_veiculoService->delete($id);

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
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'status_veiculo');
        session()->put("$this->nomeFiltro.rotaRedirect", 'status_veiculo.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'ativo', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Selecione o status', 'selected' => false],
                ['value' => 'S', 'label' => 'Ativo', 'selected' => false],
                ['value' => 'N', 'label' => 'Inativo', 'selected' => false],
            ]],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [];
    }

    private function getFormMethodAndRoute($status_veiculo = null)
    {
        if ($status_veiculo) {
            return [
                'PUT',
                route('status_veiculo.update', $status_veiculo->id),
            ];
        }

        return [
            'POST',
            route('status_veiculo.store'),
        ];
    }

    private function opcoesCor(): array
    {
        return [
            'success' => 'Verde',
            'info' => 'Azul claro',
            'primary' => 'Azul',
            'warning' => 'Âmbar',
            'orange' => 'Laranja',
            'danger' => 'Vermelho',
            'secondary' => 'Cinza',
            'gray' => 'Cinza',
            'grey' => 'Cinza escuro',
            'purple' => 'Roxo',
            'teal' => 'Verde azulado',
        ];
    }
}
