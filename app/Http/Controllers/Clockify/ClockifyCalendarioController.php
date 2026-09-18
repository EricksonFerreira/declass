<?php

namespace App\Http\Controllers\Clockify;

use App\Actions\Clockify\Calendario\DeleteCalendarioAction;
use App\Actions\Clockify\Calendario\ListarCalendarioAction;
use App\Actions\Clockify\Calendario\MoverCalendarioAction;
use App\Actions\Clockify\Calendario\SaveCalendarioAction;
use App\Http\Controllers\Controller;
use App\Models\ClockifyProjeto;
use App\Models\ClockifyTimeTracker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClockifyCalendarioController extends Controller
{
    public function index(Request $request, ListarCalendarioAction $action): View
    {
        $empresaId = $request->user()->empresa_id;

        $visibilidade = app(\App\Services\VisibilidadeService::class);
        $idsVisiveis = $request->user()->idsVisiveis();

        $alocacoes = $action->handle();
        $projetos = $visibilidade
            ->projetosVisiveis($request->user(), ClockifyProjeto::where('empresa_id', $empresaId)->where('ativo', true))
            ->orderBy('nome')->get();
        $usuarios = $visibilidade->usuariosVisiveis($request->user())
            ->filter(fn ($u) => (int) $u->getRawOriginal('tipo_usuario_id') !== 1)
            ->sortBy('nome')
            ->values();

        $events = [];
        foreach ($alocacoes as $aloc) {
            $events[] = [
                'id' => $aloc->id,
                'title' => ($aloc->user->nome ?? 'Usuário').' - '.($aloc->projeto->nome ?? 'Projeto'),
                'start' => $aloc->data_inicio->toDateString(),
                'end' => $aloc->data_fim->copy()->addDay()->toDateString(),
                'allDay' => true,
                'color' => $aloc->projeto->cor ?? '#007bff',
                'description' => $aloc->observacao ?? '',
                'extendedProps' => [
                    'type' => 'alocacao',
                    'user_id' => $aloc->user_id,
                    'user_name' => $aloc->user->nome ?? 'Usuário',
                    'project_name' => $aloc->projeto->nome ?? 'Projeto',
                    'project_color' => $aloc->projeto->cor ?? '#007bff',
                    'hours' => (float) $aloc->horas_por_dia,
                ],
            ];
        }

        // Add logged time trackers to calendar (escopo de visibilidade)
        $trackers = ClockifyTimeTracker::with(['projeto', 'user'])
            ->where('empresa_id', $empresaId)
            ->whereNotNull('fim')
            ->whereNotNull('inicio')
            ->when($idsVisiveis !== null, fn ($q) => $q->whereIn('user_id', $idsVisiveis))
            ->get();

        foreach ($trackers as $tracker) {
            $events[] = [
                'id' => 'tracker-'.$tracker->id,
                'title' => ($tracker->user->nome ?? 'Usuário').': '.($tracker->descricao ?: 'Sem descrição').' ('.$tracker->formatted_duration.')',
                'start' => $tracker->inicio->toIso8601String(),
                'end' => $tracker->fim->toIso8601String(),
                'allDay' => false,
                'color' => $tracker->projeto->cor ?? '#28a745',
                'description' => ($tracker->projeto->nome ?? 'Sem Projeto').' - '.($tracker->descricao ?: 'Sem descrição'),
                'extendedProps' => [
                    'type' => 'tracker',
                    'user_id' => $tracker->user_id,
                    'user_name' => $tracker->user->nome ?? 'Usuário',
                    'project_name' => $tracker->projeto->nome ?? 'Sem Projeto',
                    'project_color' => $tracker->projeto->cor ?? '#28a745',
                    'duration_seconds' => $tracker->duration_in_seconds,
                ],
            ];
        }

        return view('clockify.calendario', [
            'titulo' => 'Calendário e Alocações',
            'events' => json_encode($events, JSON_INVALID_UTF8_SUBSTITUTE),
            'projetos' => $projetos,
            'usuarios' => $usuarios,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Registrar Tempo'],
                ['name' => 'Calendário', 'active' => true],
            ],
        ]);
    }

    public function store(Request $request, SaveCalendarioAction $action): RedirectResponse
    {
        $empresaId = $request->user()->empresa_id;

        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')->where('empresa_id', $empresaId),
            ],
            'clockify_projeto_id' => [
                'required',
                Rule::exists('clockify_projetos', 'id')->where('empresa_id', $empresaId),
            ],
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'horas_por_dia' => 'nullable|numeric|min:0',
            'observacao' => 'nullable|string',
        ]);

        // Alocar em nome de terceiros so para quem voce gerencia
        // (admin ou lider do membro); demais alocam para si mesmos.
        if (! $request->user()->podeGerenciarUsuario((int) $validated['user_id'])) {
            $validated['user_id'] = $request->user()->id;
        }

        // Projeto privado so recebe alocacao de quem pode usa-lo
        // (admin, responsavel ou membro do projeto).
        $projeto = ClockifyProjeto::find($validated['clockify_projeto_id']);
        $alocado = \App\Models\User::find((int) $validated['user_id']);
        if ($projeto && $alocado
            && ! app(\App\Services\VisibilidadeService::class)->podeUsarProjeto($alocado, $projeto)) {
            abort(403);
        }

        $action->handle($validated);

        return redirect()->route('clockify.calendario.index')
            ->with('success', 'Alocação agendada no calendário com sucesso!');
    }

    public function mover(Request $request, $id, MoverCalendarioAction $action): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:alocacao,tracker',
            'inicio' => 'required|date',
            'fim' => 'nullable|date|after_or_equal:inicio',
        ]);

        $action->handle((int) $id, $validated['type'], $validated);

        return response()->json([
            'success' => true,
            'message' => 'Registro movido com sucesso!',
        ]);
    }

    public function destroy($id, DeleteCalendarioAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('clockify.calendario.index')
            ->with('success', 'Alocação removida com sucesso!');
    }
}
