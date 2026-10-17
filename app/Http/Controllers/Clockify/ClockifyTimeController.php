<?php

namespace App\Http\Controllers\Clockify;

use App\Actions\Clockify\Time\DeleteTimeAction;
use App\Actions\Clockify\Time\ListarTimesAction;
use App\Actions\Clockify\Time\SaveTimeAction;
use App\Actions\Clockify\Time\UpdateTimeAction;
use App\Http\Controllers\Controller;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClockifyTimeController extends Controller
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    private string $nomeFiltro = 'operacao.times';

    public function index(Request $request, ListarTimesAction $action): View
    {
        $this->configurarSessao();

        $itensFiltered = session("{$this->nomeFiltro}.filtro", []);
        $perPage = session("{$this->nomeFiltro}.perPage", 15);
        $times = $action->handle($request->search, $itensFiltered, $perPage);
        $usuarios = app(\App\Services\VisibilidadeService::class)->usuariosVisiveis($request->user());

        $filters = $this->obterFiltrosPrincipais();
        $additionalFilters = $this->obterFiltrosSecundarios();
        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        return view('clockify.times', [
            'titulo' => 'Equipes / Times',
            'times' => $times,
            'usuarios' => $usuarios,
            'filters' => $filters,
            'additionalFilters' => $additionalFilters,
            'itensFiltered' => $itensFiltered,
            'nomeFiltro' => $this->nomeFiltro,
            'perPages' => [10, 15, 25, 50, 100],
            'perPage' => $perPage,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Gerenciar'],
                ['name' => 'Equipe', 'active' => true],
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
        session()->put("{$this->nomeFiltro}.urlPadrao", 'times');
        session()->put("{$this->nomeFiltro}.rotaRedirect", 'operacao.times.index');
    }

    private function obterFiltrosPrincipais(): array
    {
        return [
            ['name' => 'nome', 'label' => 'Nome', 'type' => 'text', 'typeWhere' => 'like'],
            ['name' => 'descricao', 'label' => 'Descrição', 'type' => 'text', 'typeWhere' => 'like'],
        ];
    }

    private function obterFiltrosSecundarios(): array
    {
        return [];
    }

    public function store(Request $request, SaveTimeAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'lider_id' => 'nullable|exists:users,id',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        // Quem nao e admin so pode criar time do qual e o lider.
        if (! $request->user()->isAdministrador()) {
            $validated['lider_id'] = $request->user()->id;
        }

        $action->handle([
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'] ?? null,
            'lider_id' => $validated['lider_id'] ?? null,
        ], $request->input('users'));

        return redirect()->route('operacao.times.index')
            ->with('success', 'Time cadastrado com sucesso!');
    }

    public function update(Request $request, $id, UpdateTimeAction $action): RedirectResponse
    {
        $time = \App\Models\ClockifyTime::findOrFail($id);

        $visibilidade = app(\App\Services\VisibilidadeService::class);
        if (! $visibilidade->podeGerenciarTime($request->user(), $time)) {
            return redirect()->route('operacao.times.index')
                ->with('error', 'Você só pode editar times que lidera.');
        }

        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'lider_id' => 'nullable|exists:users,id',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        // Lider nao transfere a lideranca do proprio time.
        if (! $request->user()->isAdministrador()) {
            $validated['lider_id'] = $time->lider_id;
        }

        $action->handle((int) $id, [
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'] ?? null,
            'lider_id' => $validated['lider_id'] ?? null,
        ], $request->input('users'));

        return redirect()->route('operacao.times.index')
            ->with('success', 'Time atualizado com sucesso!');
    }

    public function destroy($id, DeleteTimeAction $action): RedirectResponse
    {
        $time = \App\Models\ClockifyTime::findOrFail($id);

        $visibilidade = app(\App\Services\VisibilidadeService::class);
        if (! $visibilidade->podeGerenciarTime(request()->user(), $time)) {
            return redirect()->route('operacao.times.index')
                ->with('error', 'Você só pode remover times que lidera.');
        }

        $action->handle((int) $id);

        return redirect()->route('operacao.times.index')
            ->with('success', 'Time removido com sucesso!');
    }
}
