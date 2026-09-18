<?php

namespace App\Http\Controllers\Clockify;

use App\Http\Controllers\Controller;
use App\Actions\Clockify\Cliente\ListarClientesAction;
use App\Actions\Clockify\Cliente\SaveClienteAction;
use App\Actions\Clockify\Cliente\UpdateClienteAction;
use App\Actions\Clockify\Cliente\DeleteClienteAction;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClockifyClienteController extends Controller
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    private string $nomeFiltro = 'clockify.clientes';

    public function index(Request $request, ListarClientesAction $action): View
    {
        $this->configurarSessao();

        $itensFiltered = session("{$this->nomeFiltro}.filtro", []);
        $perPage = session("{$this->nomeFiltro}.perPage", 15);
        $clientes = $action->handle($request->search, $itensFiltered, $perPage);

        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();
        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        return view('clockify.clientes', [
            'titulo' => 'Clientes',
            'clientes' => $clientes,
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $this->nomeFiltro,
            'perPages' => [10, 15, 25, 50, 100],
            'perPage' => $perPage,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Gerenciar'],
                ['name' => 'Clientes', 'active' => true],
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
        session()->put("{$this->nomeFiltro}.urlPadrao", 'clientes');
        session()->put("{$this->nomeFiltro}.rotaRedirect", 'clockify.clientes.index');
    }

    private function obterFiltrosPrincipais(): array
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'email', 'label' => 'E-mail', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'telefone', 'label' => 'Telefone', 'type' => 'text', 'typeWhere' => 'like'],
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

    public function store(Request $request, SaveClienteAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:50',
            'endereco' => 'nullable|string',
        ]);

        $action->handle($validated);

        return redirect()->route('clockify.clientes.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function update(Request $request, $id, UpdateClienteAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefone' => 'nullable|string|max:50',
            'endereco' => 'nullable|string',
            'ativo' => 'nullable|boolean',
        ]);

        $action->handle((int) $id, array_merge($validated, [
            'ativo' => $request->has('ativo') ? (bool) $request->ativo : true,
        ]));

        return redirect()->route('clockify.clientes.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy($id, DeleteClienteAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('clockify.clientes.index')
            ->with('success', 'Cliente removido com sucesso!');
    }
}
