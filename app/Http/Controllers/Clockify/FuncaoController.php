<?php

namespace App\Http\Controllers\Clockify;

use App\Actions\Clockify\Funcao\DeleteFuncaoAction;
use App\Actions\Clockify\Funcao\ListarFuncoesAction;
use App\Actions\Clockify\Funcao\SaveFuncaoAction;
use App\Actions\Clockify\Funcao\UpdateFuncaoAction;
use App\Http\Controllers\Controller;
use App\Support\EmpresaContext;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FuncaoController extends Controller
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    private string $nomeFiltro = 'clockify.funcoes';

    public function index(Request $request, ListarFuncoesAction $action): View
    {
        $this->configurarSessao();

        $itensFiltered = session("{$this->nomeFiltro}.filtro", []);
        $perPage = session("{$this->nomeFiltro}.perPage", 15);
        $funcoes = $action->handle($request->search, $itensFiltered, $perPage);

        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();
        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        return view('clockify.funcoes', [
            'titulo' => 'Funções',
            'funcoes' => $funcoes,
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $this->nomeFiltro,
            'perPages' => [10, 15, 25, 50, 100],
            'perPage' => $perPage,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Gerenciar'],
                ['name' => 'Funções', 'active' => true],
            ],
        ]);
    }

    public function filtro(Request $request): RedirectResponse
    {
        $this->criarFiltros($request, $this->nomeFiltro);

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function removerFiltro(Request $request): RedirectResponse
    {
        $this->removeSessao($this->nomeFiltro, $request->input('atributo'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    public function setPerPage(Request $request): RedirectResponse
    {
        $request->session()->put("{$this->nomeFiltro}.perPage", $request->input('perPage'));

        return $this->redirecionaRotaRedirect($this->nomeFiltro);
    }

    private function configurarSessao(): void
    {
        session()->put("{$this->nomeFiltro}.urlPadrao", 'funcoes');
        session()->put("{$this->nomeFiltro}.rotaRedirect", 'clockify.funcoes.index');
    }

    private function obterFiltrosPrincipais(): array
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'ativo', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Selecione o status', 'selected' => false],
                ['value' => '1', 'label' => 'Ativa', 'selected' => false],
                ['value' => '0', 'label' => 'Inativa', 'selected' => false],
            ]],
        ];
    }

    private function obterFiltrosSecundarios(): array
    {
        return [];
    }

    public function store(Request $request, SaveFuncaoAction $action): RedirectResponse
    {
        $empresaId = app(EmpresaContext::class)->empresaId() ?? $request->user()->empresa_id;

        $validated = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('funcoes', 'nome')->where('empresa_id', $empresaId),
            ],
            'custo_hora' => 'nullable|numeric|min:0|max:999999',
        ]);

        $action->handle($validated);

        return redirect()->route('clockify.funcoes.index')
            ->with('success', 'Função criada com sucesso!');
    }

    public function update(Request $request, $id, UpdateFuncaoAction $action): RedirectResponse
    {
        $empresaId = app(EmpresaContext::class)->empresaId() ?? $request->user()->empresa_id;

        $validated = $request->validate([
            'nome' => [
                'required',
                'string',
                'max:255',
                Rule::unique('funcoes', 'nome')->where('empresa_id', $empresaId)->ignore($id),
            ],
            'custo_hora' => 'nullable|numeric|min:0|max:999999',
            'ativo' => 'nullable|boolean',
        ]);

        $action->handle((int) $id, array_merge($validated, [
            'ativo' => $request->has('ativo') ? (bool) $request->ativo : true,
        ]));

        return redirect()->route('clockify.funcoes.index')
            ->with('success', 'Função atualizada com sucesso!');
    }

    public function destroy($id, DeleteFuncaoAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('clockify.funcoes.index')
            ->with('success', 'Função removida com sucesso!');
    }
}
