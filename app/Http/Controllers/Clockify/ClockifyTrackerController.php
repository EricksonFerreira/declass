<?php

namespace App\Http\Controllers\Clockify;

use App\Actions\Clockify\Tracker\DeleteTrackerAction;
use App\Actions\Clockify\Tracker\IniciarTrackerAction;
use App\Actions\Clockify\Tracker\PararTrackerAction;
use App\Actions\Clockify\Tracker\SalvarManualTrackerAction;
use App\Actions\Clockify\Tracker\SaveTimesheetAction;
use App\Actions\Clockify\Tracker\UpdateActiveTrackerAction;
use App\Actions\Clockify\Tracker\UpdateTrackerAction;
use App\Http\Controllers\Controller;
use App\Models\ClockifyProjeto;
use App\Models\ClockifyTag;
use App\Models\ClockifyTimeTracker;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ClockifyTrackerController extends Controller
{
    public function index(Request $request): View
    {
        $userId = Auth::id();

        $activeTracker = ClockifyTimeTracker::with(['projeto', 'tags'])
            ->where('user_id', $userId)
            ->whereNull('fim')
            ->first();

        // Build base query for past trackers
        $pastTrackersQuery = ClockifyTimeTracker::with(['projeto', 'tags'])
            ->where('user_id', $userId)
            ->whereNotNull('fim');

        // Filter by project
        if ($request->filled('project_id')) {
            $pastTrackersQuery->where('clockify_projeto_id', $request->input('project_id'));
        }

        // Filter by tags (trackers that have any of the selected tags)
        if ($request->filled('tags')) {
            $pastTrackersQuery->whereHas('tags', function ($q) use ($request) {
                $q->whereIn('clockify_tags.id', $request->input('tags'));
            });
        }

        // Filter by start date (records starting on or after)
        if ($request->filled('start_date')) {
            $pastTrackersQuery->whereDate('inicio', '>=', $request->input('start_date'));
        }

        // Filter by end date (records ending on or before)
        if ($request->filled('end_date')) {
            $pastTrackersQuery->whereDate('fim', '<=', $request->input('end_date'));
        }

        // Keyword search in description or project name
        if ($request->filled('search')) {
            $search = $request->input('search');
            $pastTrackersQuery->where(function ($q) use ($search) {
                $q->where('descricao', 'like', "%$search%")
                    ->orWhereHas('projeto', function ($q2) use ($search) {
                        $q2->where('nome', 'like', "%$search%");
                    });
            });
        }

        $pastTrackers = $pastTrackersQuery->orderBy('inicio', 'desc')->paginate(20);

        $projetos = app(\App\Services\VisibilidadeService::class)
            ->projetosVisiveis(Auth::user(), ClockifyProjeto::where('ativo', true))
            ->orderBy('nome')->get();
        $tags = ClockifyTag::where('ativo', true)->orderBy('nome')->get();

        return view('clockify.tracker', [
            'titulo' => 'Time Tracker (Rastreador de Tempo)',
            'tagsPorProjeto' => app(\App\Actions\Clockify\Tag\TagsPorProjetoAction::class)->ativo(),
            'activeTracker' => $activeTracker,
            'pastTrackers' => $pastTrackers,
            'projetos' => $projetos,
            'tags' => $tags,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Registrar Tempo'],
                ['name' => 'Rastreador', 'active' => true],
            ],
        ]);
    }

    public function start(Request $request, IniciarTrackerAction $action): RedirectResponse
    {
        $this->abortUnlessPodeUsarProjeto($request, $request->input('clockify_projeto_id'));

        $action->handle([
            'clockify_projeto_id' => $request->input('clockify_projeto_id'),
            'descricao' => $request->input('descricao'),
            'faturavel' => $request->has('faturavel') ? (bool) $request->faturavel : false,
        ], $request->input('tags'));

        return redirect()->route('clockify.tracker.index')
            ->with('success', 'Rastreador iniciado!');
    }

    public function stop(Request $request, PararTrackerAction $action): RedirectResponse
    {
        $this->abortUnlessPodeUsarProjeto($request, $request->input('clockify_projeto_id'));

        $action->handle(
            $request->input('descricao'),
            $request->input('clockify_projeto_id') ? (int) $request->input('clockify_projeto_id') : null,
            $request->input('tags')
        );

        return redirect()->route('clockify.tracker.index')
            ->with('success', 'Rastreador parado com sucesso!');
    }

    public function manualStore(Request $request, SalvarManualTrackerAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'clockify_projeto_id' => 'required|exists:clockify_projetos,id',
            'descricao' => 'required|string|max:255',
            'inicio' => 'required|date_format:Y-m-d\TH:i',
            'fim' => 'required|date_format:Y-m-d\TH:i|after:inicio',
            'faturavel' => 'nullable|boolean',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:clockify_tags,id',
        ]);

        $this->abortUnlessPodeUsarProjeto($request, $validated['clockify_projeto_id']);

        $action->handle($validated, $request->input('tags'));

        return redirect()->route('clockify.tracker.index')
            ->with('success', 'Registro manual inserido com sucesso!');
    }

    public function update(Request $request, $id, UpdateTrackerAction $action)
    {
        $validated = $request->validate([
            'descricao' => 'nullable|string',
            'clockify_projeto_id' => 'nullable', // Aceita null para remover projeto
            'faturavel' => 'nullable|boolean',
            'tags' => 'nullable|array',
        ]);

        // Trata campos nulos de projeto
        if ($request->has('clockify_projeto_id') && empty($request->input('clockify_projeto_id'))) {
            $validated['clockify_projeto_id'] = null;
        }

        $this->abortUnlessPodeUsarProjeto($request, $validated['clockify_projeto_id'] ?? null);

        $action->handle((int) $id, $validated, $request->input('tags'));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Registro de tempo atualizado com sucesso!',
            ]);
        }

        return redirect()->route('clockify.tracker.index')
            ->with('success', 'Registro de tempo atualizado com sucesso!');
    }

    public function updateActive(Request $request, UpdateActiveTrackerAction $action): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'descricao' => 'nullable|string',
            'clockify_projeto_id' => 'nullable|exists:clockify_projetos,id',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:clockify_tags,id',
        ]);

        $tracker = $action->handle($validated, $request->input('tags'));

        return response()->json([
            'success' => true,
            'message' => 'Rastreador ativo atualizado com sucesso!',
            'tracker' => $tracker,
        ]);
    }

    public function destroy($id, DeleteTrackerAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('clockify.tracker.index')
            ->with('success', 'Registro removido com sucesso!');
    }

    public function timesheet(Request $request): View
    {
        $weekOffset = (int) $request->input('week', 0);
        $startOfWeek = Carbon::now()->startOfWeek()->addWeeks($weekOffset);
        $endOfWeek = (clone $startOfWeek)->endOfWeek();

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDays[] = (clone $startOfWeek)->addDays($i);
        }

        $userId = Auth::id();
        $projetos = app(\App\Services\VisibilidadeService::class)
            ->projetosVisiveis(Auth::user(), ClockifyProjeto::where('ativo', true))
            ->orderBy('nome')->get();

        $trackers = ClockifyTimeTracker::where('user_id', $userId)
            ->whereBetween('inicio', [$startOfWeek->startOfDay(), $endOfWeek->endOfDay()])
            ->get();

        $prevStart = (clone $startOfWeek)->subWeek();
        $prevTrackers = ClockifyTimeTracker::where('user_id', $userId)
            ->where('descricao', 'Lançado via Timesheet')
            ->whereBetween('inicio', [$prevStart->startOfDay(), (clone $prevStart)->endOfWeek()->endOfDay()])
            ->get();

        $matrix = [];
        $tsMatrix = [];
        $prevTsMatrix = [];
        foreach ($projetos as $proj) {
            $matrix[$proj->id] = [
                'projeto' => $proj,
                'days' => array_fill(0, 7, 0.0),
            ];
            $tsMatrix[$proj->id] = array_fill(0, 7, 0.0);
            $prevTsMatrix[$proj->id] = array_fill(0, 7, 0.0);
        }

        $matrix[0] = [
            'projeto' => null,
            'days' => array_fill(0, 7, 0.0),
        ];
        $tsMatrix[0] = array_fill(0, 7, 0.0);
        $prevTsMatrix[0] = array_fill(0, 7, 0.0);

        foreach ($trackers as $tracker) {
            $projId = $tracker->clockify_projeto_id ?: 0;
            $dayIndex = $tracker->inicio->dayOfWeekIso - 1;

            if ($dayIndex >= 0 && $dayIndex <= 6) {
                $hours = $tracker->duration_in_seconds / 3600.0;
                if (isset($matrix[$projId])) {
                    $matrix[$projId]['days'][$dayIndex] += round($hours, 2);
                    if ($tracker->descricao === 'Lançado via Timesheet') {
                        $tsMatrix[$projId][$dayIndex] += round($hours, 2);
                    }
                }
            }
        }

        foreach ($prevTrackers as $tracker) {
            $projId = $tracker->clockify_projeto_id ?: 0;
            $dayIndex = $tracker->inicio->dayOfWeekIso - 1;

            if ($dayIndex >= 0 && $dayIndex <= 6 && isset($prevTsMatrix[$projId])) {
                $prevTsMatrix[$projId][$dayIndex] += round($tracker->duration_in_seconds / 3600.0, 2);
            }
        }

        return view('clockify.timesheet', [
            'titulo' => 'Planilha de Horas (Timesheet)',
            'startOfWeek' => $startOfWeek,
            'endOfWeek' => $endOfWeek,
            'weekDays' => $weekDays,
            'weekOffset' => $weekOffset,
            'matrix' => $matrix,
            'tsMatrix' => $tsMatrix,
            'prevTsMatrix' => $prevTsMatrix,
            'projetos' => $projetos,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Registrar Tempo'],
                ['name' => 'Timesheet', 'active' => true],
            ],
        ]);
    }

    public function timesheetSave(Request $request, SaveTimesheetAction $action): RedirectResponse
    {
        $weekOffset = (int) $request->input('week', 0);
        $startOfWeek = Carbon::now()->startOfWeek()->addWeeks($weekOffset);

        $hoursInput = $request->input('hours', []);
        $visibilidade = app(\App\Services\VisibilidadeService::class);
        $idsPermitidos = $visibilidade
            ->projetosVisiveis($request->user(), ClockifyProjeto::query())
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        foreach (array_keys($hoursInput) as $projId) {
            if ((int) $projId !== 0 && ! in_array((int) $projId, $idsPermitidos, true)) {
                abort(403);
            }
        }

        $action->handle($hoursInput, $startOfWeek);

        return redirect()->route('clockify.timesheet', ['week' => $weekOffset])
            ->with('success', 'Planilha de horas salva com sucesso!');
    }

    /**
     * Projeto privado so pode receber apontamentos de admin, responsavel
     * ou membro; projeto publico e livre para todos do tenant.
     */
    private function abortUnlessPodeUsarProjeto(Request $request, $projetoId): void
    {
        if (! $projetoId) {
            return;
        }

        $projeto = ClockifyProjeto::find($projetoId);

        if ($projeto
            && ! app(\App\Services\VisibilidadeService::class)->podeUsarProjeto($request->user(), $projeto)) {
            abort(403);
        }
    }
}
