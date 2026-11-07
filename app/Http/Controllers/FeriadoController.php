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
class FeriadoController extends BaseController
{
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
    }

    public function index(Request $request)
    {
        $feriados = $this->feriadoService->listarComEscola([
            'escola_id' => $request->input('escola_id'),
            'ano' => $request->input('ano'),
        ]);

        $breadcrumb = $this->breadcrumbService->prepararBreadcrumbIndex('Feriados', 'Vida Acadêmica');

        return view('feriado.index', [
            'feriados' => $feriados,
            'escolas' => Escola::query()->orderBy('nome')->pluck('nome', 'id')->all(),
            'anos' => $this->anosDisponiveis(),
            'filtros' => $request->only(['escola_id', 'ano']),
            'breadcrumb' => $breadcrumb,
        ])->with('titulo', $this->titulo);
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
