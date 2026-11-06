<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Interfaces\FiltroInterface;
use App\Http\Controllers\Interfaces\ResourceInterface;
use App\Http\Requests\AnoLetivo\StoreAnoLetivoRequest;
use App\Http\Requests\AnoLetivo\UpdateAnoLetivoRequest;
use App\Models\AnoLetivo;
use App\Models\Escola;
use App\Services\AnoLetivoService;
use App\Services\BreadcrumbService;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\Request;

class AnoLetivoController extends BaseController implements FiltroInterface, ResourceInterface
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    public function __construct(
        protected AnoLetivoService $anoLetivoService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Anos Letivos';
        $this->nomeFiltro = 'ano_letivo';

        $this->middleware('can:ano_letivo.index')->only('index');
        $this->middleware('can:ano_letivo.create')->only('create');
        $this->middleware('can:ano_letivo.store')->only('store');
        $this->middleware('can:ano_letivo.edit')->only('edit');
        $this->middleware('can:ano_letivo.update')->only('update');
        $this->middleware('can:ano_letivo.destroy')->only('destroy');
        $this->middleware('can:ano_letivo.filtro')->only('filtro');
        $this->middleware('can:ano_letivo.removerFiltro')->only('removerFiltro');
        $this->middleware('can:ano_letivo.setPerPage')->only('setPerPage');
    }

    public function index()
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $perPage = session()->get("$this->nomeFiltro.perPage", 10);

        $anosLetivos = $this->anoLetivoService->getAllWithFiltersAndPagination(
            $itensFiltered, $perPage, ['escola']
        );

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Anos Letivos', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $perPages = [5, 10, 15, 25, 50, 100];
        $nomeFiltro = $this->nomeFiltro;

        return view('ano_letivo.index', compact(
            'anosLetivos', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'perPages', 'perPage', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Anos Letivos', 'ano_letivo.index', 'Novo Ano Letivo', 'Vida Acadêmica');

        return view('ano_letivo.add-edit', array_merge(compact('breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function store(StoreAnoLetivoRequest $request)
    {
        $this->anoLetivoService->create($request->validated());

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Ano letivo criado com sucesso.');
    }

    public function edit($id)
    {
        $anoLetivo = $this->anoLetivoService->getById($id);
        abort_if(! $anoLetivo, 404, 'Ano letivo não encontrado.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Anos Letivos', 'ano_letivo.index', "Editar: {$anoLetivo->ano}", 'Vida Acadêmica');

        return view('ano_letivo.add-edit', array_merge(compact('anoLetivo', 'breadcrumb'), $this->opcoesFormulario()))
            ->with('titulo', $this->titulo);
    }

    public function update(UpdateAnoLetivoRequest $request, $id)
    {
        $anoLetivo = $this->anoLetivoService->update($id, $request->validated());
        abort_if(! $anoLetivo, 403, 'Sem acesso para editar este ano letivo.');

        return $this->redirecionaRotaRedirect($this->nomeFiltro, 'Ano letivo atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->anoLetivoService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este ano letivo.');

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
        session()->put("$this->nomeFiltro.urlPadrao", 'ano-letivo');
        session()->put("$this->nomeFiltro.rotaRedirect", 'ano_letivo.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            ['name' => 'ano', 'label' => 'Ano', 'type' => 'number', 'typeWhere' => 'where'],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Escola::query()->orderBy('nome')->get(['id', 'nome'])
                    ->map(fn (Escola $e) => ['value' => $e->id, 'label' => $e->nome])->all(),
            ],
            [
                'name' => 'status', 'label' => 'Status', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => AnoLetivo::STATUS,
            ],
            [
                'name' => 'ativo', 'label' => 'Situação', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => ['S' => 'Ativo', 'N' => 'Inativo'],
            ],
        ];
    }

    private function opcoesFormulario(): array
    {
        return [
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'status' => AnoLetivo::STATUS,
        ];
    }
}
