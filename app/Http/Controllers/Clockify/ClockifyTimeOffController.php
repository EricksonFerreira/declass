<?php

namespace App\Http\Controllers\Clockify;

use App\Actions\Clockify\TimeOff\AprovarTimeOffAction;
use App\Actions\Clockify\TimeOff\DeleteTimeOffAction;
use App\Actions\Clockify\TimeOff\ListarTimeOffsAction;
use App\Actions\Clockify\TimeOff\RejeitarTimeOffAction;
use App\Actions\Clockify\TimeOff\SaveTimeOffAction;
use App\Actions\Clockify\TimeOff\UpdateTimeOffAction;
use App\Http\Controllers\Controller;
use App\Traits\AtualizaFiltrosTrait;
use App\Traits\FiltroTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClockifyTimeOffController extends Controller
{
    use AtualizaFiltrosTrait;
    use FiltroTrait;

    private string $nomeFiltro = 'operacao.timeoff';

    public function index(Request $request, ListarTimeOffsAction $action): View
    {
        $this->configurarSessao();

        $itensFiltered = session("{$this->nomeFiltro}.filtro", []);
        $perPage = session("{$this->nomeFiltro}.perPage", 15);
        $timeoffs = $action->handle($itensFiltered, $perPage);
        $usuarios = app(\App\Services\VisibilidadeService::class)->usuariosVisiveis(Auth::user());

        $filters = $this->obterFiltrosPrincipais($usuarios);
        $additionalFilters = $this->obterFiltrosSecundarios();
        $this->atualizarFiltros($itensFiltered, $filters);
        $this->atualizarFiltros($itensFiltered, $additionalFilters);

        return view('clockify.timeoff', [
            'titulo' => 'Folgas e Férias',
            'timeoffs' => $timeoffs,
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
                ['name' => 'Folgas e Férias', 'active' => true],
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
        session()->put("{$this->nomeFiltro}.urlPadrao", 'timeoff');
        session()->put("{$this->nomeFiltro}.rotaRedirect", 'operacao.timeoff.index');
    }

    private function obterFiltrosPrincipais($usuarios): array
    {
        $filters = [
            ['name' => 'tipo', 'label' => 'Tipo', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Todos', 'selected' => false],
                ['value' => 'ferias', 'label' => 'Férias', 'selected' => false],
                ['value' => 'medico', 'label' => 'Médico', 'selected' => false],
                ['value' => 'pessoal', 'label' => 'Pessoal', 'selected' => false],
                ['value' => 'outro', 'label' => 'Outro', 'selected' => false],
            ]],
            ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'typeWhere' => 'where', 'options' => [
                ['value' => '', 'label' => 'Todos', 'selected' => false],
                ['value' => 'pendente', 'label' => 'Pendente', 'selected' => false],
                ['value' => 'aprovado', 'label' => 'Aprovado', 'selected' => false],
                ['value' => 'rejeitado', 'label' => 'Rejeitado', 'selected' => false],
            ]],
        ];

        $idsVisiveis = Auth::user()->idsVisiveis();
        if ($idsVisiveis === null || count($idsVisiveis) > 1) {
            $options = [['value' => '', 'label' => 'Todos', 'selected' => false]];
            foreach ($usuarios as $usuario) {
                $options[] = ['value' => (string) $usuario->id, 'label' => $usuario->nome, 'selected' => false];
            }

            array_unshift($filters, ['name' => 'user_id', 'label' => 'Colaborador', 'type' => 'select', 'typeWhere' => 'where', 'options' => $options]);
        }

        return $filters;
    }

    private function obterFiltrosSecundarios(): array
    {
        return [];
    }

    public function store(Request $request, SaveTimeOffAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'tipo' => 'required|string|in:ferias,medico,pessoal,outro',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'observacao' => 'nullable|string',
        ]);

        // Somente quem gerencia o usuario alvo (admin ou seu lider) pode
        // registrar folga em nome de terceiros; demais registram para si.
        if (! Auth::user()->podeGerenciarUsuario((int) $validated['user_id'])) {
            $validated['user_id'] = Auth::id();
        }

        $action->handle($validated);

        return redirect()->route('operacao.timeoff.index')
            ->with('success', 'Solicitação de folga ou férias registrada com sucesso!');
    }

    public function update(Request $request, $id, UpdateTimeOffAction $action): RedirectResponse
    {
        $timeoff = \App\Models\ClockifyTimeOff::findOrFail($id);

        $podeEditar = (int) $timeoff->user_id === (int) Auth::id()
            || Auth::user()->podeGerenciarUsuario($timeoff->user_id);

        if (! $podeEditar) {
            return redirect()->route('operacao.timeoff.index')
                ->with('error', 'Você só pode editar solicitações próprias ou da sua equipe.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'tipo' => 'required|string|in:ferias,medico,pessoal,outro',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'observacao' => 'nullable|string',
        ]);

        if (! Auth::user()->podeGerenciarUsuario((int) $validated['user_id'])) {
            $validated['user_id'] = Auth::id();
        }

        $action->handle((int) $id, $validated);

        return redirect()->route('operacao.timeoff.index')
            ->with('success', 'Solicitação de folga ou férias atualizada com sucesso!');
    }

    public function approve(Request $request, $id, AprovarTimeOffAction $action): RedirectResponse
    {
        $timeoff = \App\Models\ClockifyTimeOff::findOrFail($id);

        if (! Auth::user()->podeGerenciarUsuario($timeoff->user_id)) {
            return redirect()->back()->with('error', 'Você só pode aprovar solicitações da sua equipe.');
        }

        $action->handle((int) $id);

        return redirect()->route('operacao.timeoff.index')
            ->with('success', 'Solicitação aprovada com sucesso!');
    }

    public function reject(Request $request, $id, RejeitarTimeOffAction $action): RedirectResponse
    {
        $timeoff = \App\Models\ClockifyTimeOff::findOrFail($id);

        if (! Auth::user()->podeGerenciarUsuario($timeoff->user_id)) {
            return redirect()->back()->with('error', 'Você só pode rejeitar solicitações da sua equipe.');
        }

        $action->handle((int) $id);

        return redirect()->route('operacao.timeoff.index')
            ->with('success', 'Solicitação rejeitada com sucesso!');
    }

    public function destroy($id, DeleteTimeOffAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('operacao.timeoff.index')
            ->with('success', 'Solicitação removida com sucesso!');
    }
}
