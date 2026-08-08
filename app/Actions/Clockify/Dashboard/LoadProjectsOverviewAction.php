<?php

namespace App\Actions\Clockify\Dashboard;

use App\Models\ClockifyProjeto;
use App\Models\ClockifyTimeTracker;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LoadProjectsOverviewAction
{
    public function handle(?string $startDateStr = null, ?string $endDateStr = null, ?int $filterProjectId = null): array
    {
        $now = Carbon::now();
        $startDate = $startDateStr ? Carbon::parse($startDateStr)->startOfDay() : $now->copy()->startOfMonth()->startOfDay();
        $endDate = $endDateStr ? Carbon::parse($endDateStr)->endOfDay() : $now->copy()->endOfDay();

        $projects = ClockifyProjeto::orderBy('nome')->get();

        // 1. If a single project is selected, load detailed "Project Status" view data
        if ($filterProjectId) {
            $project = ClockifyProjeto::with('cliente')->find($filterProjectId);
            
            if ($project) {
                // Get trackers for this project
                $trackers = ClockifyTimeTracker::with('user')
                    ->where('clockify_projeto_id', $project->id)
                    ->whereNotNull('fim')
                    ->whereBetween('inicio', [$startDate, $endDate])
                    ->get();

                $totalSeconds = $trackers->sum('duration_in_seconds');
                $totalHours = round($totalSeconds / 3600, 1);

                $billableSeconds = $trackers->where('faturavel', true)->sum('duration_in_seconds');
                $billableHours = round($billableSeconds / 3600, 1);
                $nonBillableHours = round(($totalSeconds - $billableSeconds) / 3600, 1);

                // Simulation: Budget & Financial stats
                $budgetHours = 150.0; // Simulated target
                $progressPercent = $budgetHours > 0 ? round(($totalHours / $budgetHours) * 100, 2) : 0;

                $budgetAmount = $budgetHours * 150.0; // Simulated R$ 150/h billing
                $amountEarned = $billableHours * 150.0;
                $expenses = $totalHours * 15.0; // Simulated R$ 15/h expense
                $remainingAmount = $budgetAmount - $amountEarned;

                // Group trackers by description (simulating "Tasks" within the project)
                $tasks = [];
                $grouped = $trackers->groupBy(fn($t) => $t->descricao ?: 'Geral');

                foreach ($grouped as $taskDesc => $taskTrackers) {
                    $taskSeconds = $taskTrackers->sum('duration_in_seconds');
                    $taskHours = round($taskSeconds / 3600, 1);
                    
                    // Most active user for this task
                    $userGroup = $taskTrackers->groupBy('user_id');
                    $mostActiveUserId = null;
                    $maxUserSeconds = 0;
                    foreach ($userGroup as $uId => $uTrackers) {
                        $uSec = $uTrackers->sum('duration_in_seconds');
                        if ($uSec > $maxUserSeconds) {
                            $maxUserSeconds = $uSec;
                            $mostActiveUserId = $uId;
                        }
                    }
                    $assignee = User::find($mostActiveUserId)?->nome ?: 'Qualquer um';

                    $taskBillableSec = $taskTrackers->where('faturavel', true)->sum('duration_in_seconds');
                    $taskBillableHours = round($taskBillableSec / 3600, 1);

                    $tasks[] = [
                        'nome' => $taskDesc,
                        'encarregado' => $assignee,
                        'despesas' => $taskHours * 15.0,
                        'valor' => $taskBillableHours * 150.0,
                        'horas' => $taskHours,
                    ];
                }

                usort($tasks, fn($a, $b) => $b['horas'] <=> $a['horas']);

                return [
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'isSingleProject' => true,
                    'project' => $project,
                    'totalHours' => $totalHours,
                    'billableHours' => $billableHours,
                    'nonBillableHours' => $nonBillableHours,
                    'budgetHours' => $budgetHours,
                    'progressPercent' => $progressPercent,
                    'budgetAmount' => $budgetAmount,
                    'amountEarned' => $amountEarned,
                    'expenses' => $expenses,
                    'remainingAmount' => $remainingAmount,
                    'tasks' => $tasks,
                ];
            }
        }

        // 2. Default: Load general macro view of all projects
        $projectStats = ClockifyTimeTracker::select(
                'clockify_projeto_id',
                DB::raw('SUM(TIMESTAMPDIFF(SECOND, inicio, fim)) as total_seconds'),
                DB::raw('COUNT(*) as entries_count'),
                DB::raw('COUNT(DISTINCT user_id) as members_count'),
                DB::raw('MAX(inicio) as last_activity')
            )
            ->whereNotNull('fim')
            ->whereBetween('inicio', [$startDate, $endDate])
            ->groupBy('clockify_projeto_id')
            ->orderByDesc('total_seconds')
            ->get()
            ->keyBy('clockify_projeto_id');

        $projectCards = [];
        $chartLabels = [];
        $chartData = [];
        $chartColors = [];
        $inactiveProjects = [];

        foreach ($projects as $project) {
            $stats = $projectStats->get($project->id);
            $hours = $stats ? round($stats->total_seconds / 3600, 1) : 0;

            $card = [
                'id' => $project->id,
                'nome' => $project->nome,
                'cor' => $project->cor ?? '#6c757d',
                'hours' => $hours,
                'members_count' => $stats ? $stats->members_count : 0,
                'entries_count' => $stats ? $stats->entries_count : 0,
                'last_activity' => $stats ? Carbon::parse($stats->last_activity) : null,
                'last_activity_human' => $stats ? Carbon::parse($stats->last_activity)->diffForHumans() : 'Sem atividade',
            ];

            $projectCards[] = $card;

            if ($hours > 0) {
                $chartLabels[] = $project->nome;
                $chartData[] = $hours;
                $chartColors[] = $project->cor ?? '#6c757d';
            } else {
                $inactiveProjects[] = $card;
            }
        }

        usort($projectCards, fn($a, $b) => $b['hours'] <=> $a['hours']);

        // Project × Member matrix
        $topProjectIds = collect($projectCards)->where('hours', '>', 0)->take(10)->pluck('id')->toArray();
        $matrix = [];
        $matrixMembers = collect();

        if (!empty($topProjectIds)) {
            $matrixData = ClockifyTimeTracker::select(
                    'clockify_projeto_id',
                    'user_id',
                    DB::raw('SUM(TIMESTAMPDIFF(SECOND, inicio, fim)) as total_seconds')
                )
                ->whereNotNull('fim')
                ->whereBetween('inicio', [$startDate, $endDate])
                ->whereIn('clockify_projeto_id', $topProjectIds)
                ->groupBy('clockify_projeto_id', 'user_id')
                ->get();

            $memberIds = $matrixData->pluck('user_id')->unique()->toArray();
            $matrixMembers = User::whereIn('id', $memberIds)->orderBy('nome')->get();

            foreach ($topProjectIds as $projectId) {
                $project = $projects->find($projectId);
                if (!$project) {
                    continue;
                }

                $row = [
                    'project_name' => $project->nome,
                    'project_color' => $project->cor ?? '#6c757d',
                    'members' => [],
                ];

                foreach ($matrixMembers as $member) {
                    $entry = $matrixData->first(fn($d) => $d->clockify_projeto_id === $projectId && $d->user_id === $member->id);
                    $row['members'][$member->id] = $entry ? round($entry->total_seconds / 3600, 1) : 0;
                }

                $matrix[] = $row;
            }
        }

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'isSingleProject' => false,
            'projectCards' => $projectCards,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'chartColors' => $chartColors,
            'inactiveProjects' => $inactiveProjects,
            'matrix' => $matrix,
            'matrixMembers' => $matrixMembers,
            'totalProjects' => $projects->count(),
            'activeProjects' => count($chartLabels),
        ];
    }
}
