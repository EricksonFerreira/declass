<?php

namespace App\Http\Controllers\Clockify;

use App\Actions\Clockify\TimeOff\AprovarTimeOffAction;
use App\Actions\Clockify\TimeOff\DeleteTimeOffAction;
use App\Actions\Clockify\TimeOff\ListarTimeOffsAction;
use App\Actions\Clockify\TimeOff\RejeitarTimeOffAction;
use App\Actions\Clockify\TimeOff\SaveTimeOffAction;
use App\Actions\Clockify\TimeOff\UpdateTimeOffAction;
use App\Http\Controllers\Controller;
use App\Models\User;
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
            'titulo' => 'Folgas e Férias',
            'timeoffs' => $timeoffs,
            'usuarios' => $usuarios,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Gerenciar'],
                ['name' => 'Folgas e Férias', 'active' => true],
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

        if (! Auth::user()->can('gerenciar-empresa')) {
            $validated['user_id'] = Auth::id();
        }

        $action->handle($validated);

        return redirect()->route('clockify.timeoff.index')
            ->with('success', 'Solicitação de folga ou férias registrada com sucesso!');
    }

    public function update(Request $request, $id, UpdateTimeOffAction $action): RedirectResponse
    {
        $timeoff = \App\Models\ClockifyTimeOff::findOrFail($id);

        if (! Auth::user()->can('gerenciar-empresa') && $timeoff->user_id !== Auth::id()) {
            return redirect()->route('clockify.timeoff.index')
                ->with('error', 'Você só pode editar suas próprias solicitações.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'tipo' => 'required|string|in:ferias,medico,pessoal,outro',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'observacao' => 'nullable|string',
        ]);

        if (! Auth::user()->can('gerenciar-empresa')) {
            $validated['user_id'] = Auth::id();
        }

        $action->handle((int) $id, $validated);

        return redirect()->route('clockify.timeoff.index')
            ->with('success', 'Solicitação de folga ou férias atualizada com sucesso!');
    }

    public function approve(Request $request, $id, AprovarTimeOffAction $action): RedirectResponse
    {
        if (! Auth::user()->can('gerenciar-empresa')) {
            return redirect()->back()->with('error', 'Apenas administradores podem aprovar folgas.');
        }

        $action->handle((int) $id);

        return redirect()->route('clockify.timeoff.index')
            ->with('success', 'Solicitação aprovada com sucesso!');
    }

    public function reject(Request $request, $id, RejeitarTimeOffAction $action): RedirectResponse
    {
        if (! Auth::user()->can('gerenciar-empresa')) {
            return redirect()->back()->with('error', 'Apenas administradores podem rejeitar folgas.');
        }

        $action->handle((int) $id);

        return redirect()->route('clockify.timeoff.index')
            ->with('success', 'Solicitação rejeitada com sucesso!');
    }

    public function destroy($id, DeleteTimeOffAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('clockify.timeoff.index')
            ->with('success', 'Solicitação removida com sucesso!');
    }
}
