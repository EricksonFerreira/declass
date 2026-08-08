<?php

namespace App\Http\Controllers\Clockify;

use App\Http\Controllers\Controller;
use App\Models\ClockifyCliente;
use App\Actions\Clockify\Projeto\ListarProjetosAction;
use App\Actions\Clockify\Projeto\SaveProjetoAction;
use App\Actions\Clockify\Projeto\UpdateProjetoAction;
use App\Actions\Clockify\Projeto\DeleteProjetoAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClockifyProjetoController extends Controller
{
    public function index(Request $request, ListarProjetosAction $action): View
    {
        $projetos = $action->handle($request->search);
        $clientes = ClockifyCliente::where('ativo', true)->orderBy('nome')->get();

        return view('clockify.projetos', [
            'titulo' => 'Projetos',
            'projetos' => $projetos,
            'clientes' => $clientes,
            'selectedProjectId' => null,
            'selectedStartDate' => null,
            'selectedEndDate' => null,
            'isSingleProject' => false,
            'inactiveProjects' => [],
            'chartLabels' => [],
            'chartData' => [],
            'chartColors' => [],
            'projectCards' => [],
            'matrix' => [],
            'matrixMembers' => [],
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Projetos', 'active' => true],
            ],
        ]);
    }

    public function store(Request $request, SaveProjetoAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'clockify_cliente_id' => 'nullable|exists:clockify_clientes,id',
            'descricao' => 'nullable|string',
            'cor' => 'nullable|string|max:7',
            'publico' => 'nullable|boolean',
        ]);

        $action->handle(array_merge($validated, [
            'publico' => $request->has('publico') ? (bool) $request->publico : true,
        ]));

        return redirect()->route('clockify.projetos.index')
            ->with('success', 'Projeto criado com sucesso!');
    }

    public function update(Request $request, $id, UpdateProjetoAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'clockify_cliente_id' => 'nullable|exists:clockify_clientes,id',
            'descricao' => 'nullable|string',
            'cor' => 'nullable|string|max:7',
            'publico' => 'nullable|boolean',
            'ativo' => 'nullable|boolean',
        ]);

        $action->handle((int) $id, array_merge($validated, [
            'publico' => $request->has('publico') ? (bool) $request->publico : true,
            'ativo' => $request->has('ativo') ? (bool) $request->ativo : true,
        ]));

        return redirect()->route('clockify.projetos.index')
            ->with('success', 'Projeto atualizado com sucesso!');
    }

    public function destroy($id, DeleteProjetoAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('clockify.projetos.index')
            ->with('success', 'Projeto removido com sucesso!');
    }
}
