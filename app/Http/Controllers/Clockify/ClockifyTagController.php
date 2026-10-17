<?php

namespace App\Http\Controllers\Clockify;

use App\Http\Controllers\Controller;
use App\Actions\Clockify\Tag\DeleteTagAction;
use App\Actions\Clockify\Tag\ListarTagsAction;
use App\Actions\Clockify\Tag\SaveTagAction;
use App\Actions\Clockify\Tag\TagsPorProjetoAction;
use App\Actions\Clockify\Tag\UpdateTagAction;
use App\Models\ClockifyProjeto;
use App\Support\EmpresaContext;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClockifyTagController extends Controller
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    private string $nomeFiltro = 'operacao.tags';

    public function index(Request $request, ListarTagsAction $action): View
    {
        $this->configurarSessao();

        $itensFiltered = session("{$this->nomeFiltro}.filtro", []);
        $perPage = session("{$this->nomeFiltro}.perPage", 15);
        $tags = $action->handle($request->search, $itensFiltered, $perPage);
        $tagsPorProjeto = app(TagsPorProjetoAction::class)->ativo();
        $projetos = $tagsPorProjeto
            ? ClockifyProjeto::where('ativo', true)->orderBy('nome')->get()
            : collect();

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
            'tagsPorProjeto' => $tagsPorProjeto,
            'projetos' => $projetos,
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
        session()->put("{$this->nomeFiltro}.rotaRedirect", 'operacao.tags.index');
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
            'clockify_projeto_id' => ['nullable', $this->projetoDaEmpresaRule()],
        ]);

        if (! app(TagsPorProjetoAction::class)->ativo()) {
            $validated['clockify_projeto_id'] = null;
        }

        $action->handle($validated);

        return redirect()->route('operacao.tags.index')
            ->with('success', 'Tag criada com sucesso!');
    }

    public function update(Request $request, $id, UpdateTagAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'ativo' => 'nullable|boolean',
            'clockify_projeto_id' => ['nullable', $this->projetoDaEmpresaRule()],
        ]);

        if (! app(TagsPorProjetoAction::class)->ativo()) {
            $validated['clockify_projeto_id'] = null;
        }

        $action->handle((int) $id, array_merge($validated, [
            'ativo' => $request->has('ativo') ? (bool) $request->ativo : true,
        ]));

        return redirect()->route('operacao.tags.index')
            ->with('success', 'Tag atualizada com sucesso!');
    }

    /**
     * Restringe o projeto vinculado a tag aos projetos do tenant atual.
     */
    private function projetoDaEmpresaRule(): \Illuminate\Validation\Rules\Exists
    {
        $empresaId = app(EmpresaContext::class)->empresaId() ?? auth()->user()?->empresa_id;

        $rule = Rule::exists('clockify_projetos', 'id');

        if ($empresaId) {
            $rule->where('empresa_id', $empresaId);
        }

        return $rule;
    }

    public function destroy($id, DeleteTagAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('operacao.tags.index')
            ->with('success', 'Tag removida com sucesso!');
    }
}
