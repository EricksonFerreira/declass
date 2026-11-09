<?php

namespace App\Http\Controllers;

use App\Http\Requests\Feriado\StoreFeriadoRequest;
use App\Http\Requests\Feriado\UpdateFeriadoRequest;
use App\Models\Escola;
use App\Services\BreadcrumbService;
use App\Services\FeriadoService;
use Illuminate\Http\Request;

/**
 * Feriados e dias sem aula da escola: alimentam o calendario
 * letivo com suspensao (S) ou registro convivente (N).
 */
class FeriadoController extends BaseController implements Interfaces\FiltroInterface
{
    use \App\Traits\AtualizaFiltrosTrait;
    use \App\Traits\FiltroTrait;

    public function __construct(
        protected FeriadoService $feriadoService,
        protected BreadcrumbService $breadcrumbService,
    ) {
        $this->titulo = 'Feriados';
        $this->nomeFiltro = 'feriado';

        $this->middleware('can:feriado.index')->only('index');
        $this->middleware('can:feriado.create')->only(['create', 'store']);
        $this->middleware('can:feriado.edit')->only('edit');
        $this->middleware('can:feriado.update')->only('update');
        $this->middleware('can:feriado.destroy')->only('destroy');
        $this->middleware('can:feriado.filtro')->only('filtro');
        $this->middleware('can:feriado.removerFiltro')->only('removerFiltro');
    }

    public function index(Request $request)
    {
        $this->configurarSessao();

        $itensFiltered = session("$this->nomeFiltro.filtro", []);
        $filtros = collect($itensFiltered)
            ->mapWithKeys(fn ($f) => [$f['name'] => $f['valor']])
            ->all();

        $feriados = $this->feriadoService->listarComEscola($filtros);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Feriados', 'Vida Acadêmica');
        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();

        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        $nomeFiltro = $this->nomeFiltro;

        return view('feriado.index', compact(
            'feriados', 'breadcrumb', 'filters', 'itensFiltered',
            'additionalFilters', 'nomeFiltro'
        ))->with('titulo', $this->titulo);
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

    public function configurarSessao()
    {
        session()->put("$this->nomeFiltro.urlPadrao", 'feriado');
        session()->put("$this->nomeFiltro.rotaRedirect", 'feriado.index');
    }

    public function obterFiltrosPrincipais()
    {
        return [
            [
                'name' => 'escola_id', 'label' => 'Escola', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            ],
            [
                'name' => 'ano', 'label' => 'Ano', 'type' => 'select',
                'typeWhere' => 'where',
                'options' => $this->anosDisponiveis(),
            ],
        ];
    }

    public function obterFiltrosSecundarios()
    {
        return [];
    }

    public function create()
    {
        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbCreate('Feriados', 'feriado.index', 'Novo Feriado', 'Vida Acadêmica');

        return view('feriado.add-edit', [
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
    }

    public function store(StoreFeriadoRequest $request)
    {
        $this->feriadoService->create($request->validated());

        return redirect()->route('feriado.index')->with('success', 'Feriado cadastrado com sucesso.');
    }

    public function edit($id)
    {
        $feriado = $this->feriadoService->getById($id);
        abort_if(! $feriado, 404, 'Feriado não encontrado.');

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbEdit('Feriados', 'feriado.index', "Editar: {$feriado->nome}", 'Vida Acadêmica');

        return view('feriado.add-edit', [
            'feriado' => $feriado,
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
    }

    public function update(UpdateFeriadoRequest $request, $id)
    {
        $feriado = $this->feriadoService->update($id, $request->validated());
        abort_if(! $feriado, 403, 'Sem acesso para editar este feriado.');

        return redirect()->route('feriado.index')->with('success', 'Feriado atualizado com sucesso.');
    }

    public function destroy($id)
    {
        $success = $this->feriadoService->delete($id);
        abort_if(! $success, 403, 'Sem acesso para excluir este feriado.');

        return redirect()->route('feriado.index')->with('success', 'Feriado excluído.');
    }

    private function anosDisponiveis(): array
    {
        return \App\Models\Feriado::query()
            ->selectRaw('DISTINCT YEAR(data_inicio) as ano')
            ->orderByDesc('ano')
            ->pluck('ano')
            ->mapWithKeys(fn ($a) => [$a => $a])
            ->all();
    }
}
