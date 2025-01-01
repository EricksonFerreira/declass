<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\DisponibilidadeInicialVeiculo\StoreDisponibilidadeInicialVeiculoRequest;
use App\Http\Requests\DisponibilidadeInicialVeiculo\UpdateDisponibilidadeInicialVeiculoRequest;
use App\Services\BreadcrumbService;
use App\Services\DisponibilidadeInicialVeiculoService;
use App\Traits\AtualizaFiltrosTrait;
use Illuminate\Http\Request;
use App\Traits\FiltroTrait;

class DisponibilidadeInicialVeiculoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use FiltroTrait;
    use AtualizaFiltrosTrait;
    protected $disponibilidade_inicial_veiculoService;
    protected $breadcrumbService;

    public function __construct(DisponibilidadeInicialVeiculoService $disponibilidade_inicial_veiculoService, BreadcrumbService $breadcrumbService)
    {
        $this->titulo = "DisponibilidadeInicialVeiculos";
        $this->nomeFiltro = 'disponibilidade_inicial_veiculo';
        $this->disponibilidade_inicial_veiculoService = $disponibilidade_inicial_veiculoService;
        $this->breadcrumbService = $breadcrumbService;
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $disponibilidade_inicial_veiculos = $this->disponibilidade_inicial_veiculoService->getAllWithFiltersAndPagination($itensFiltered, $perPage);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('DisponibilidadeInicialVeiculos');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];

        return view('disponibilidade_inicial_veiculo.index', compact(
            'disponibilidade_inicial_veiculos',
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
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('DisponibilidadeInicialVeiculos','disponibilidade_inicial_veiculo.index','Criar');

        [$metodo,$rota] = $this->getFormMethodAndRoute();
        return view('disponibilidade_inicial_veiculo.add-edit', compact('breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function store(StoreDisponibilidadeInicialVeiculoRequest $request)
    {
        $this->disponibilidade_inicial_veiculoService->create($request->all());

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function edit($id)
    {
        $disponibilidade_inicial_veiculo = $this->disponibilidade_inicial_veiculoService->getById($id);
        $status = [
            'S' => 'Ativo',
            'N' => 'Inativo'
        ];
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('DisponibilidadeInicialVeiculos','disponibilidade_inicial_veiculo.index',"Editar: $disponibilidade_inicial_veiculo->nome");
        [$metodo,$rota] = $this->getFormMethodAndRoute($disponibilidade_inicial_veiculo);

        return view('disponibilidade_inicial_veiculo.add-edit', compact('disponibilidade_inicial_veiculo','breadcrumb','status','metodo','rota'))->with('titulo', $this->titulo);
    }

    public function update(UpdateDisponibilidadeInicialVeiculoRequest $request, $id)
    {
        $success = $this->disponibilidade_inicial_veiculoService->update($id,$request->all());
        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function destroy($id)
    {
        $success = $this->disponibilidade_inicial_veiculoService->delete($id);
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
        session()->put("$this->nomeFiltro.urlPadrao", 'disponibilidade_inicial_veiculo');
        session()->put("$this->nomeFiltro.rotaRedirect", 'disponibilidade_inicial_veiculo.index');
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
     * @param  DisponibilidadeInicialVeiculo|null  $disponibilidade_inicial_veiculo
     * @return array
     */
    private function getFormMethodAndRoute($disponibilidade_inicial_veiculo = null)
    {
        if ($disponibilidade_inicial_veiculo) {
            return [
                'PUT',
                route('disponibilidade_inicial_veiculo.update', $disponibilidade_inicial_veiculo->id),
            ];
        }

        return [
            'POST',
            route('disponibilidade_inicial_veiculo.store'),
        ];
    }
}