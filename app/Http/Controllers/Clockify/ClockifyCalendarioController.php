<?php

namespace App\Http\Controllers\Clockify;

use App\Http\Controllers\Controller;
use App\Models\ClockifyProjeto;
use App\Models\User;
use App\Actions\Clockify\Calendario\ListarCalendarioAction;
use App\Actions\Clockify\Calendario\SaveCalendarioAction;
use App\Actions\Clockify\Calendario\DeleteCalendarioAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClockifyCalendarioController extends Controller
{
    public function index(Request $request, ListarCalendarioAction $action): View
    {
        $alocacoes = $action->handle();
        $projetos = ClockifyProjeto::where('ativo', true)->orderBy('nome')->get();
        $usuarios = User::orderBy('nome')->get();

        $events = [];
        foreach ($alocacoes as $aloc) {
            $events[] = [
                'id' => $aloc->id,
                'title' => ($aloc->user->nome ?? 'Usuário') . ' - ' . ($aloc->projeto->nome ?? 'Projeto'),
                'start' => $aloc->data_inicio->toDateString(),
                'end' => $aloc->data_fim->addDay()->toDateString(),
                'color' => $aloc->projeto->cor ?? '#007bff',
                'description' => $aloc->observacao ?? '',
                'extendedProps' => [
                    'hours' => $aloc->horas_por_dia,
                    'type' => 'alocacao',
                ],
            ];
        }

        // Add logged time trackers to calendar
        $trackers = \App\Models\ClockifyTimeTracker::with(['projeto', 'user'])
            ->whereNotNull('fim')
            ->get();

        foreach ($trackers as $tracker) {
            $events[] = [
                'id' => 'tracker-' . $tracker->id,
                'title' => ($tracker->user->nome ?? 'Usuário') . ': ' . ($tracker->descricao ?: 'Sem descrição') . ' (' . $tracker->formatted_duration . ')',
                'start' => $tracker->inicio->toIso8601String(),
                'end' => $tracker->fim ? $tracker->fim->toIso8601String() : null,
                'color' => $tracker->projeto->cor ?? '#28a745',
                'description' => ($tracker->projeto->nome ?? 'Sem Projeto') . ' - ' . ($tracker->descricao ?: 'Sem descrição'),
                'extendedProps' => [
                    'type' => 'tracker',
                ],
            ];
        }

        return view('clockify.calendario', [
            'titulo' => 'Calendário e Alocações',
            'events' => json_encode($events),
            'alocacoes' => $alocacoes,
            'projetos' => $projetos,
            'usuarios' => $usuarios,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Painel', 'url' => route('home')],
                ['name' => 'Calendário', 'active' => true],
            ],
        ]);
    }

    public function store(Request $request, SaveCalendarioAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'clockify_projeto_id' => 'required|exists:clockify_projetos,id',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'horas_por_dia' => 'nullable|numeric|min:0',
            'observacao' => 'nullable|string',
        ]);

        $action->handle($validated);

        return redirect()->route('clockify.calendario.index')
            ->with('success', 'Alocação agendada no calendário com sucesso!');
    }

    public function destroy($id, DeleteCalendarioAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('clockify.calendario.index')
            ->with('success', 'Alocação removida com sucesso!');
    }
}
