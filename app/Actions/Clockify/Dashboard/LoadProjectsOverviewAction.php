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
            $project = ClockifyProjeto::with(['cliente', 'users', 'cronogramas.planejamentos'])->find($filterProjectId);

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

                // Budget & Financial stats (real values from DB if set, else simulated)
                $budgetHours = (float) ($project->horas_orcadas ?: 150.0);
                $hourlyRate = (float) ($project->valor_hora ?: 150.0);

                $progressPercent = $budgetHours > 0 ? round(($totalHours / $budgetHours) * 100, 2) : 0;

                $budgetAmount = $budgetHours * $hourlyRate;
                $amountEarned = $billableHours * $hourlyRate;
                $expenses = $totalHours * 15.0; // Simulated R$ 15/h expense
                $remainingAmount = $budgetAmount - $amountEarned;

                // Load individual trackers as tasks/entries to show specific times
                $tasks = [];
                foreach ($trackers as $tracker) {
                    $trackerHours = round($tracker->duration_in_seconds / 3600, 2);
                    $tasks[] = [
                        'nome' => $tracker->descricao ?: 'Sem descrição',
                        'encarregado' => $tracker->user->nome ?? 'Usuário',
                        'despesas' => $trackerHours * 15.0,
                        'valor' => $tracker->faturavel ? ($trackerHours * $hourlyRate) : 0.0,
                        'horas' => $trackerHours,
                        'periodo' => $tracker->inicio->format('d/m/Y H:i').' - '.($tracker->fim ? $tracker->fim->format('H:i') : 'Ativo'),
                        'timestamp' => $tracker->inicio->timestamp,
                    ];
                }

                // Sort by latest entry
                usort($tasks, fn ($a, $b) => $b['timestamp'] <=> $a['timestamp']);

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
                    'valor_faturado' => $project->valor_faturado ?? 0.00,
                    'valor_faturado_etapas' => $project->cronogramas->sum('valor_faturado'),
                    'valor_faturado_diff' => ($project->valor_faturado ?? 0.00) - $project->cronogramas->sum('valor_faturado'),
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

        usort($projectCards, fn ($a, $b) => $b['hours'] <=> $a['hours']);

        // Project × Member matrix
        $topProjectIds = collect($projectCards)->where('hours', '>', 0)->take(10)->pluck('id')->toArray();
        $matrix = [];
        $matrixMembers = collect();

        if (! empty($topProjectIds)) {
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

            $matrixTotals = ['members' => [], 'total' => 0];

            foreach ($topProjectIds as $projectId) {
                $project = $projects->find($projectId);
                if (! $project) {
                    continue;
                }

                $row = [
                    'project_name' => $project->nome,
                    'project_color' => $project->cor ?? '#6c757d',
                    'members' => [],
                ];

                foreach ($matrixMembers as $member) {
                    $entry = $matrixData->first(fn ($d) => $d->clockify_projeto_id === $projectId && $d->user_id === $member->id);
                    $row['members'][$member->id] = $entry ? round($entry->total_seconds / 3600, 1) : 0;
                }

                $row['total'] = round(array_sum($row['members']), 1);
                foreach ($row['members'] as $memberId => $hours) {
                    $matrixTotals['members'][$memberId] = round(($matrixTotals['members'][$memberId] ?? 0) + $hours, 1);
                }
                $matrixTotals['total'] = round($matrixTotals['total'] + $row['total'], 1);

                $matrix[] = $row;
            }
        } else {
            $matrixTotals = ['members' => [], 'total' => 0];
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
            'matrixTotals' => $matrixTotals,
            'totalProjects' => $projects->count(),
            'activeProjects' => count($chartLabels),
        ];
    }
}
