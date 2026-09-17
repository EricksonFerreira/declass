<?php

namespace App\Http\Controllers\Clockify;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClockifyProjeto;
use App\Actions\Clockify\Dashboard\LoadDashboardStatsAction;
use App\Actions\Clockify\Dashboard\GetMemberStatsAction;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

use App\Actions\Clockify\Dashboard\LoadProductivityAction;
use App\Actions\Clockify\Dashboard\LoadTeamOverviewAction;
use App\Actions\Clockify\Dashboard\LoadProjectsOverviewAction;


class ClockifyDashboardController extends Controller
{
    public function index(Request $request, LoadDashboardStatsAction $statsAction, GetMemberStatsAction $memberStatsAction): View
    {
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        // Accept multiple user IDs (e.g., user_ids[]=1&user_ids[]=2)
        $filterUserIds = $request->input('user_ids') ? $request->input('user_ids') : null;
        // For backward compatibility, also accept single user_id
        if (is_null($filterUserIds) && $request->filled('user_id')) {
            $filterUserIds = [(int) $request->input('user_id')];
        }

        // Load generic dashboard stats (uses first user if needed)
        $primaryUserId = $filterUserIds ? $filterUserIds[0] : null;
        $stats = $statsAction->handle($dataInicio, $dataFim, $primaryUserId);
        $usuarios = User::orderBy('nome')->get();

        // Active trackers come from $stats['activeTrackers']
        $activeTrackers = $stats['activeTrackers'];
        // Calculate elapsed time for each active tracker
        $activeTrackers->each(function ($tracker) {
            $seconds = Carbon::now()->diffInSeconds($tracker->inicio);
            $hours = intdiv($seconds, 3600);
            $minutes = intdiv($seconds % 3600, 60);
            $seconds = $seconds % 60;
            $tracker->tempoFormato = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        });

        // Load member-specific daily stats
        $memberData = $memberStatsAction->handle(
            $stats['startDate'], // Carbon instance from stats
            $stats['endDate'],   // Carbon instance from stats
            $filterUserIds
        );

        return view('clockify.dashboard', array_merge([
            'titulo' => 'Dashboard de Produtividade',
            'usuarios' => $usuarios,
            'selectedUserIds' => $filterUserIds,
            'selectedStartDate' => $stats['startDate']->toDateString(),
            'selectedEndDate' => $stats['endDate']->toDateString(),
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Análise'],
                ['name' => 'Painel', 'url' => route('dashboard')],
                ['name' => 'Visão Geral', 'active' => true],
            ],
            // Member stats for chart
            'memberStats' => $memberData['memberStats'],
            'dateLabels' => $memberData['dateLabels'],
            'selectedMembers' => $memberData['selectedMembers'],
        ], $stats));
    }

    public function produtividade(Request $request, LoadProductivityAction $action): View
    {
        $data = $action->handle(
            $request->input('data_inicio'),
            $request->input('data_fim'),
            $request->input('user_ids')
        );

        $usuarios = User::orderBy('nome')->get();

        return view('clockify.produtividade', array_merge($data, [
            'titulo' => 'Produtividade',
            'usuarios' => $usuarios,
            'selectedUserIds' => $request->input('user_ids'),
            'selectedStartDate' => $data['startDate']->toDateString(),
            'selectedEndDate' => $data['endDate']->toDateString(),
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Análise'],
                ['name' => 'Painel', 'url' => route('dashboard')],
                ['name' => 'Produtividade', 'active' => true],
            ],
        ]));
    }

    public function equipe(Request $request, LoadTeamOverviewAction $action)
    {
        $data = $action->handle(
            $request->input('data_inicio'),
            $request->input('data_fim')
        );

        if ($request->ajax()) {
            // Retorna formato limpo com os dados de trackers formatados para exibição
            $trackersFormatted = [];
            foreach ($data['activeTrackers'] as $tracker) {
                $trackersFormatted[] = [
                    'tempoFormato' => $tracker->tempoFormato,
                    'descricao' => $tracker->descricao ?: 'Sem descrição',
                    'projeto_nome' => $tracker->projeto ? $tracker->projeto->nome : 'Sem Projeto',
                    'projeto_cor' => $tracker->projeto ? $tracker->projeto->cor : '#6c757d',
                    'user_nome' => $tracker->user->nome,
                ];
            }
            $data['activeTrackersFormatted'] = $trackersFormatted;
            return response()->json($data);
        }

        return view('clockify.equipe', array_merge($data, [
            'titulo' => 'Equipe',
            'selectedStartDate' => $data['startDate']->toDateString(),
            'selectedEndDate' => $data['endDate']->toDateString(),
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Análise'],
                ['name' => 'Painel', 'url' => route('dashboard')],
                ['name' => 'Equipe', 'active' => true],
            ],
        ]));
    }


    public function projetos(Request $request, LoadProjectsOverviewAction $action): View
    {
        $projectId = $request->input('projeto_id');
        $data = $action->handle(
            $request->input('data_inicio'),
            $request->input('data_fim'),
            $projectId ? (int) $projectId : null
        );

        $projetos = ClockifyProjeto::orderBy('nome')->get();

        return view('clockify.projetos_overview', array_merge($data, [
            'titulo' => 'Projetos',
            'projetos' => $projetos,
            'selectedProjectId' => $projectId,
            'selectedStartDate' => $data['startDate']->toDateString(),
            'selectedEndDate' => $data['endDate']->toDateString(),
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Análise'],
                ['name' => 'Painel', 'url' => route('dashboard')],
                ['name' => 'Projetos', 'active' => true],
            ],
        ]));
    }

}

