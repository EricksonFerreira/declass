<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Reserva\StoreReservaRequest;
use App\Http\Requests\Reserva\UpdateReservaRequest;
use App\Services\BreadcrumbService;
use App\Services\EstacaoService;
use App\Services\GrupoFrotaService;
use App\Services\ReservaService;
use App\Services\TipoOpcionalService;
use App\Services\UsuarioService;
use App\Services\VeiculoService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class ReservaController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $reservaService;
    protected $breadcrumbService;
    protected $userService;
    protected $veiculoService;
    protected $estacaoService;
    protected $grupoFrotaService;
    protected $tipoOpcionalService;

    public function __construct(
        ReservaService $reservaService,
        BreadcrumbService $breadcrumbService,
        UsuarioService $userService,
        VeiculoService $veiculoService,
        EstacaoService $estacaoService,
        GrupoFrotaService $grupoFrotaService,
        TipoOpcionalService $tipoOpcionalService
    ) {
        $this->titulo = "Reservas";
        $this->nomeFiltro = 'reserva';
        $this->reservaService = $reservaService;
        $this->breadcrumbService = $breadcrumbService;
        $this->userService = $userService;
        $this->veiculoService = $veiculoService;
        $this->estacaoService = $estacaoService;
        $this->grupoFrotaService = $grupoFrotaService;
        $this->tipoOpcionalService = $tipoOpcionalService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $reservas = $this->reservaService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Reservas');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('reserva.index', compact(
            'reservas',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Reservas', 'reserva.index', 'Criar');

        // Consultando as variáveis necessárias
        $users = $this->userService->getAllForSelect();
        $estacoes = $this->estacaoService->getAllForSelect();
        $gruposVeiculo = $this->grupoFrotaService->getAllForSelect();
        $tiposOpcionais = $this->tipoOpcionalService->getTipoOpcionais('nome','asc');

        [$metodo, $rota] = $this->getFormMethodAndRoute();
        return view('reserva.add-edit', compact(

            'breadcrumb', 'status', 'metodo', 'rota', 'users', 'gruposVeiculo', 'estacoes','tiposOpcionais'))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreReservaRequest $request)
    {
        $this->reservaService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $reserva = $this->reservaService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Reservas', 'reserva.index', "Editar: $reserva->nome");
        [$metodo, $rota] = $this->getFormMethodAndRoute($reserva);

        $users = $this->userService->getAllForSelect();
        $estacoes = $this->estacaoService->getAllForSelect();
        $gruposVeiculo = $this->grupoFrotaService->getAllForSelect();
        $tiposOpcionais = $this->tipoOpcionalService->getTipoOpcionais('nome','asc');

        return view('reserva.add-edit', compact(
            'users','estações','gruposVeiculo','tiposOpcionais',
            'reserva', 'breadcrumb', 'status', 'metodo', 'rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateReservaRequest $request, $id)
    {
        $success = $this->reservaService->update($id, $request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->reservaService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'reserva');
        session()->put("$this->nomeFiltro.rotaRedirect", 'reserva.index');
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
     * @param  Reserva|null  $reserva
     * @return array
     */
    private function getFormMethodAndRoute($reserva = null)
    {
        if ($reserva) {
            return [
                'PUT',
                route('reserva.update', $reserva->id),
            ];
        }

        return [
            'POST',
            route('reserva.store'),
        ];
    }
}
