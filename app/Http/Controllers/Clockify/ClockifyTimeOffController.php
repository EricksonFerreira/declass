<?php

namespace App\Http\Controllers\Clockify;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Actions\Clockify\TimeOff\ListarTimeOffsAction;
use App\Actions\Clockify\TimeOff\SaveTimeOffAction;
use App\Actions\Clockify\TimeOff\AprovarTimeOffAction;
use App\Actions\Clockify\TimeOff\RejeitarTimeOffAction;
use App\Actions\Clockify\TimeOff\DeleteTimeOffAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClockifyTimeOffController extends Controller
{
    public function index(Request $request, ListarTimeOffsAction $action): View
    {
        $timeoffs = $action->handle();
        $usuarios = User::orderBy('nome')->get();

        return view('clockify.timeoff', [
            'titulo' => 'Time Off (Folgas e Férias)',
            'timeoffs' => $timeoffs,
            'usuarios' => $usuarios,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Time Off', 'active' => true],
            ],
        ]);
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

        if (!Auth::user()->can('gerenciar-empresa')) {
            $validated['user_id'] = Auth::id();
        }

        $action->handle($validated);

        return redirect()->route('clockify.timeoff.index')
            ->with('success', 'Solicitação de Time Off registrada com sucesso!');
    }

    public function approve(Request $request, $id, AprovarTimeOffAction $action): RedirectResponse
    {
        if (!Auth::user()->can('gerenciar-empresa')) {
            return redirect()->back()->with('error', 'Apenas administradores podem aprovar folgas.');
        }

        $action->handle((int) $id);

        return redirect()->route('clockify.timeoff.index')
            ->with('success', 'Time Off aprovado com sucesso!');
    }

    public function reject(Request $request, $id, RejeitarTimeOffAction $action): RedirectResponse
    {
        if (!Auth::user()->can('gerenciar-empresa')) {
            return redirect()->back()->with('error', 'Apenas administradores podem rejeitar folgas.');
        }

        $action->handle((int) $id);

        return redirect()->route('clockify.timeoff.index')
            ->with('success', 'Time Off rejeitado com sucesso!');
    }

    public function destroy($id, DeleteTimeOffAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('clockify.timeoff.index')
            ->with('success', 'Time Off removido com sucesso!');
    }
}
