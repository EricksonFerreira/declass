<?php

namespace App\Http\Controllers\Clockify;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Actions\Clockify\Time\ListarTimesAction;
use App\Actions\Clockify\Time\SaveTimeAction;
use App\Actions\Clockify\Time\UpdateTimeAction;
use App\Actions\Clockify\Time\DeleteTimeAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClockifyTimeController extends Controller
{
    public function index(Request $request, ListarTimesAction $action): View
    {
        $times = $action->handle($request->search);
        $usuarios = User::orderBy('nome')->get();

        return view('clockify.times', [
            'titulo' => 'Equipes / Times',
            'times' => $times,
            'usuarios' => $usuarios,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Painel', 'url' => route('home')],
                ['name' => 'Equipes / Times', 'active' => true],
            ],
        ]);
    }

    public function store(Request $request, SaveTimeAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        $action->handle([
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'] ?? null,
        ], $request->input('users'));

        return redirect()->route('clockify.times.index')
            ->with('success', 'Time cadastrado com sucesso!');
    }

    public function update(Request $request, $id, UpdateTimeAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
        ]);

        $action->handle((int) $id, [
            'nome' => $validated['nome'],
            'descricao' => $validated['descricao'] ?? null,
        ], $request->input('users'));

        return redirect()->route('clockify.times.index')
            ->with('success', 'Time atualizado com sucesso!');
    }

    public function destroy($id, DeleteTimeAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('clockify.times.index')
            ->with('success', 'Time removido com sucesso!');
    }
}
