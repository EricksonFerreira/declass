<?php

namespace App\Actions\Clockify\Dashboard;

use App\Models\ClockifyProjeto;
use App\Models\ClockifyTimeTracker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoadDashboardStatsAction
{
    public function handle(?string $startDateStr = null, ?string $endDateStr = null, ?int $filterUserId = null): array
    {
        $currentUserId = Auth::id();

        // Filtro por usuario respeita o escopo de visibilidade:
        // admin qualquer um; lider os da sua equipe; colaborador so a si.
        $visibilidade = app(\App\Services\VisibilidadeService::class);
        $idsVisiveis = Auth::user()->idsVisiveis();

        $targetUserId = $filterUserId;
        if (! $targetUserId || ! $visibilidade->podeVerUsuario(Auth::user(), $targetUserId)) {
            $targetUserId = $currentUserId;
        }

        $now = Carbon::now();
        $todayStart = (clone $now)->startOfDay();
        $weekStart = (clone $now)->startOfWeek();
        $monthStart = (clone $now)->startOfMonth();

        // 1. Absolute KPIs for the target user
        $getLoggedHours = function ($start, $end) use ($targetUserId) {
            $seconds = ClockifyTimeTracker::where('user_id', $targetUserId)
                ->whereNotNull('fim')
                ->where('inicio', '>=', $start)
                ->where('fim', '<=', $end)
                ->get()
                ->sum('duration_in_seconds');

            return round($seconds / 3600.0, 2);
        };

        $hoursToday = $getLoggedHours($todayStart, (clone $now)->endOfDay());
        $hoursThisWeek = $getLoggedHours($weekStart, (clone $now)->endOfWeek());
        $hoursThisMonth = $getLoggedHours($monthStart, (clone $now)->endOfMonth());

        // 2. Parse Date filters
        $startDate = $startDateStr ? Carbon::parse($startDateStr)->startOfDay() : (clone $now)->startOfMonth()->startOfDay();
        $endDate = $endDateStr ? Carbon::parse($endDateStr)->endOfDay() : (clone $now)->endOfDay();

        // 3. Tracked Time inside the specific period
        $secondsInPeriod = ClockifyTimeTracker::where('user_id', $targetUserId)
            ->whereNotNull('fim')
            ->whereBetween('inicio', [$startDate, $endDate])
            ->get()
            ->sum('duration_in_seconds');
        $hoursInPeriod = round($secondsInPeriod / 3600.0, 1);

        // 4. Projects breakdown inside the specific period
        $projectStats = ClockifyTimeTracker::select('clockify_projeto_id', DB::raw('SUM(TIMESTAMPDIFF(SECOND, inicio, fim)) as total_seconds'))
            ->where('user_id', $targetUserId)
            ->whereNotNull('fim')
            ->whereBetween('inicio', [$startDate, $endDate])
            ->groupBy('clockify_projeto_id')
            ->orderBy('total_seconds', 'desc')
            ->get();

        $topProjects = [];
        foreach ($projectStats as $stat) {
            $proj = ClockifyProjeto::find($stat->clockify_projeto_id);
            $hours = round($stat->total_seconds / 3600.0, 1);
            if ($hours > 0) {
                $topProjects[] = [
                    'name' => $proj ? $proj->nome : 'Sem Projeto',
                    'color' => $proj ? $proj->cor : '#6c757d',
                    'hours' => $hours,
                ];
            }
        }

        // 5. Activity stream inside the specific period
        $teamActivitiesQuery = ClockifyTimeTracker::with(['user', 'projeto'])
            ->whereNotNull('fim')
            ->whereBetween('inicio', [$startDate, $endDate]);

        // A lista de atividades respeita o escopo de visibilidade;
        // um filtro explicito restringe ao usuario escolhido.
        if ($idsVisiveis !== null) {
            $teamActivitiesQuery->whereIn('user_id', $idsVisiveis);
        }
        if ($filterUserId) {
            $teamActivitiesQuery->where('user_id', $filterUserId);
        }

        $teamActivities = $teamActivitiesQuery->orderBy('inicio', 'desc')
            ->take(15)
            ->get();

        // 6. Get currently active trackers
        $activeTrackersQuery = ClockifyTimeTracker::with(['user', 'projeto'])
            ->whereNull('fim');

        if ($filterUserId) {
            $activeTrackersQuery->where('user_id', $filterUserId);
        }

        $activeTrackers = $activeTrackersQuery->orderBy('inicio', 'desc')->get();

        return [
            'hoursToday' => $hoursToday,
            'hoursThisWeek' => $hoursThisWeek,
            'hoursThisMonth' => $hoursThisMonth,
            'hoursInPeriod' => $hoursInPeriod,
            'topProjects' => $topProjects,
            'teamActivities' => $teamActivities,
            'activeTrackers' => $activeTrackers,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'filterUserId' => $filterUserId,
        ];
    }
}
