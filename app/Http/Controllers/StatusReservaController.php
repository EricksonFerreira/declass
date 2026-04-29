<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\StatusReserva\StoreStatusReservaRequest;
use App\Http\Requests\StatusReserva\UpdateStatusReservaRequest;
use App\Services\BreadcrumbService;
use App\Services\StatusReservaService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class StatusReservaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $status_reservaService;
    protected $breadcrumbService;

    public function __construct(StatusReservaService $status_reservaService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "Status da Reservas";
        $this->nomeFiltro = 'status_reserva';
        $this->status_reservaService = $status_reservaService;
        $this->breadcrumbService = $breadcrumbService;
        $this->middleware('can:acessar-superadmin')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $status_reservas = $this->status_reservaService->getAllWithFiltersAndPagination($itensFiltered, $perPage,[],'nome','asc');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Status da Reservas');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('status_reserva.index', compact(
            'status_reservas',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Status da Reservas','status_reserva.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('status_reserva.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreStatusReservaRequest $request)
    {
        $this->status_reservaService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $status_reserva = $this->status_reservaService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Status da Reservas','status_reserva.index',"Editar: $status_reserva->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($status_reserva);

        return view('status_reserva.add-edit', compact('status_reserva','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateStatusReservaRequest $request, $id)
    {
        $success = $this->status_reservaService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->status_reservaService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'status_reserva');
        session()->put("$this->nomeFiltro.rotaRedirect", 'status_reserva.index');
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
     * @param  StatusReserva|null  $status_reserva
     * @return array
     */
    private function getFormMethodAndRoute($status_reserva = null)
    {
        if ($status_reserva) {
            return [
                'PUT',
                route('status_reserva.update', $status_reserva->id),
            ];
        }

        return [
            'POST',
            route('status_reserva.store'),
        ];
    }
}
