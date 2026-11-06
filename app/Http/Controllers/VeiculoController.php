<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\Veiculo\StoreVeiculoRequest;
use App\Http\Requests\Veiculo\UpdateVeiculoRequest;
use App\Models\Escola;
use App\Services\BreadcrumbService;
use App\Services\VeiculoService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class VeiculoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected VeiculoService $veiculoService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Veículos';
        $this->nomeFiltro = 'veiculo';

        $this->middleware('can:veiculo.index')->only('index');
        $this->middleware('can:veiculo.create')->only('create');
        $this->middleware('can:veiculo.store')->only('store');
        $this->middleware('can:veiculo.edit')->only('edit');
        $this->middleware('can:veiculo.update')->only('update');
        $this->middleware('can:veiculo.destroy')->only('destroy');
        $this->middleware('can:veiculo.filtro')->only('filtro');
        $this->middleware('can:veiculo.removerFiltro')->only('removerFiltro');
        $this->middleware('can:veiculo.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        $veiculos = $this->veiculoService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['escola'], 'placa', 'asc'
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Veículos', 'Transporte');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('veiculo.index', compact(
            'veiculos', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Veículos', 'veiculo.index', 'Novo Veículo', 'Transporte');

        return view('veiculo.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreVeiculoRequest $request)
    {
        $this->veiculoService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Veículo cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $veiculo = $this->veiculoService->getById($id, ['escola']);
        abort_if(! $veiculo, 404, 'Veículo não encontrado.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Veículos', 'veiculo.index', "Editar: {$veiculo->placa}", 'Transporte');

        return view('veiculo.add-edit', array_merge(compact('veiculo', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateVeiculoRequest $request, $id)
    {
        $veiculo = $this->veiculoService->update($id, $request->validated());
        abort_if(! $veiculo, 403, 'Sem acesso para editar este veículo.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Veículo atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->veiculoService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este veículo.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function filtro(Request $request)
    {
        $this->criarFiltros($request, $this->nomeFiltro);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function removerFiltro(Request $request)
    {
        $this->removeSessao($this->nomeFiltro, $request->input('atributo'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function setPerPage(Request $request)
    {
        $request->session()->put("$this->nomeFiltro.perPage", $request->input('perPage'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'veiculo');
        session()->put("$this->nomeFiltro.rotaRedirect", 'veiculo.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'placa', 'label' => 'Placa', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'modelo', 'label' => 'Modelo', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'motorista_nome', 'label' => 'Motorista', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->escolasOpcoesFiltro(),
            ],
            [
                'name' => 'ativo', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Ativo', 'N' => 'Inativo'],
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
        ];
    }

    private function escolasOpcoesFiltro()
    {
        return Escola::query()->orderBy('nome')->get(['id', 'nome'])
            ->map(fn (Escola $escola) => ['value' => $escola->id, 'label' => $escola->nome])
            ->all();
    }
}
