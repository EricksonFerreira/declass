<?php

namespace App\Http\Controllers\Clockify;

use App\Http\Controllers\Controller;
use App\Actions\Clockify\Tag\ListarTagsAction;
use App\Actions\Clockify\Tag\SaveTagAction;
use App\Actions\Clockify\Tag\UpdateTagAction;
use App\Actions\Clockify\Tag\DeleteTagAction;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClockifyTagController extends Controller
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    private string $nomeFiltro = 'clockify.tags';

    public function index(Request $request, ListarTagsAction $action): View
    {
        $this->configurarSessao();

        $itensFiltered = session("{$this->nomeFiltro}.filtro", []);
        $perPage = session("{$this->nomeFiltro}.perPage", 15);
        $tags = $action->handle($request->search, $itensFiltered, $perPage);

        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();
        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        return view('clockify.tags', [
            'titulo' => 'Tags',
            'tags' => $tags,
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $this->nomeFiltro,
            'perPages' => [10, 15, 25, 50, 100],
            'perPage' => $perPage,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Gerenciar'],
                ['name' => 'Tags', 'active' => true],
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
        session()->put("{$this->nomeFiltro}.urlPadrao", 'tags');
        session()->put("{$this->nomeFiltro}.rotaRedirect", 'clockify.tags.index');
    }

    private function obterFiltrosPrincipais(): array
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    private function obterFiltrosSecundarios(): array
    {
        return [
            ['name' => 'ativo', 'label' => 'Ativo', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Todos', 'selected' => false],
                ['value' => '1', 'label' => 'Sim', 'selected' => false],
                ['value' => '0', 'label' => 'Não', 'selected' => false],
            ]],
        ];
    }

    public function store(Request $request, SaveTagAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
        ]);

        $action->handle($validated);

        return redirect()->route('clockify.tags.index')
            ->with('success', 'Tag criada com sucesso!');
    }

    public function update(Request $request, $id, UpdateTagAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'ativo' => 'nullable|boolean',
        ]);

        $action->handle((int) $id, array_merge($validated, [
            'ativo' => $request->has('ativo') ? (bool) $request->ativo : true,
        ]));

        return redirect()->route('clockify.tags.index')
            ->with('success', 'Tag atualizada com sucesso!');
    }

    public function destroy($id, DeleteTagAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('clockify.tags.index')
            ->with('success', 'Tag removida com sucesso!');
    }
}
