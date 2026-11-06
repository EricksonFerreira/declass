<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\RotaTransporte\StoreRotaTransporteRequest;
use App\Http\Requests\RotaTransporte\UpdateRotaTransporteRequest;
use App\Models\Escola;
use App\Models\Veiculo;
use App\Services\BreadcrumbService;
use App\Services\RotaTransporteService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class RotaTransporteController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected RotaTransporteService $rotaService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Rotas de Transporte';
        $this->nomeFiltro = 'rota_transporte';

        $this->middleware('can:rota_transporte.index')->only('index');
        $this->middleware('can:rota_transporte.create')->only('create');
        $this->middleware('can:rota_transporte.store')->only('store');
        $this->middleware('can:rota_transporte.edit')->only('edit');
        $this->middleware('can:rota_transporte.update')->only('update');
        $this->middleware('can:rota_transporte.destroy')->only('destroy');
        $this->middleware('can:rota_transporte.filtro')->only('filtro');
        $this->middleware('can:rota_transporte.removerFiltro')->only('removerFiltro');
        $this->middleware('can:rota_transporte.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 15);

        $rotas = $this->rotaService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['escola', 'veiculo'], 'nome', 'asc'
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Rotas de Transporte', 'Transporte');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('rota_transporte.index', compact(
            'rotas', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Rotas de Transporte', 'rota_transporte.index', 'Nova Rota', 'Transporte');

        return view('rota_transporte.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreRotaTransporteRequest $request)
    {
        $this->rotaService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Rota cadastrada com sucesso.');
    }

    public function edit($id)
    {
        $rota = $this->rotaService->getById($id, ['escola', 'veiculo', 'paradas']);
        abort_if(! $rota, 404, 'Rota não encontrada.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Rotas de Transporte', 'rota_transporte.index', "Editar: {$rota->nome}", 'Transporte');

        return view('rota_transporte.add-edit', array_merge(compact('rota', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateRotaTransporteRequest $request, $id)
    {
        $rota = $this->rotaService->update($id, $request->validated());
        abort_if(! $rota, 403, 'Sem acesso para editar esta rota.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Rota atualizada com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->rotaService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir esta rota.');

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
        session()->put("$this->nomeFiltro.urlPadrao", 'rota_transporte');
        session()->put("$this->nomeFiltro.rotaRedirect", 'rota_transporte.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
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
                'name' => 'veiculo_id', 'label' => 'Veículo', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->veiculosOpcoesFiltro(),
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
            'veiculos' => Veiculo::query()->where('ativo', 'S')->orderBy('placa')
                ->get(['id', 'placa', 'modelo'])
                ->mapWithKeys(fn ($v) => [$v->id => $v->placa.' — '.$v->modelo])->all(),
        ];
    }

    private function escolasOpcoesFiltro()
    {
        return Escola::query()->orderBy('nome')->get(['id', 'nome'])
            ->map(fn (Escola $escola) => ['value' => $escola->id, 'label' => $escola->nome])
            ->all();
    }

    private function veiculosOpcoesFiltro()
    {
        return Veiculo::query()->orderBy('placa')->get(['id', 'placa'])
            ->map(fn ($v) => ['value' => $v->id, 'label' => $v->placa])->all();
    }
}
