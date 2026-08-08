<?php

namespace App\Actions\Clockify\Dashboard;

use App\Models\ClockifyTimeTracker;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class LoadProductivityAction
{
    public function handle(?string $startDateStr = null, ?string $endDateStr = null, ?array $filterUserIds = null): array
    {
        $now = Carbon::now();
        $startDate = $startDateStr ? Carbon::parse($startDateStr)->startOfDay() : $now->copy()->startOfMonth()->startOfDay();
        $endDate = $endDateStr ? Carbon::parse($endDateStr)->endOfDay() : $now->copy()->endOfDay();
        $totalDays = max($startDate->diffInDays($endDate) + 1, 1);

        $baseQuery = ClockifyTimeTracker::whereNotNull('fim')
            ->whereBetween('inicio', [$startDate, $endDate]);
        if (!empty($filterUserIds)) {
            $baseQuery->whereIn('user_id', $filterUserIds);
        }

        $trackers = $baseQuery->get();
        $totalSeconds = $trackers->sum('duration_in_seconds');
        $totalHours = round($totalSeconds / 3600, 1);
        $avgDailyHours = round($totalHours / $totalDays, 1);

        // Previous period comparison
        $prevStart = $startDate->copy()->subDays($totalDays);
        $prevEnd = $startDate->copy()->subDay()->endOfDay();
        $prevQuery = ClockifyTimeTracker::whereNotNull('fim')
            ->whereBetween('inicio', [$prevStart, $prevEnd]);
        if (!empty($filterUserIds)) {
            $prevQuery->whereIn('user_id', $filterUserIds);
        }
        $prevSeconds = $prevQuery->get()->sum('duration_in_seconds');
        $prevHours = round($prevSeconds / 3600, 1);
        $percentChange = $prevHours > 0 ? round((($totalHours - $prevHours) / $prevHours) * 100, 1) : null;


        // Member ranking
        $membersQuery = User::orderBy('nome');
        $members = $membersQuery->get();
        $memberRanking = [];

        foreach ($members as $member) {
            if (!empty($filterUserIds) && !in_array($member->id, $filterUserIds)) {
                continue;
            }

            $memberTrackers = $trackers->where('user_id', $member->id);
            $memberSeconds = $memberTrackers->sum('duration_in_seconds');
            $memberHours = round($memberSeconds / 3600, 1);

            if ($memberHours <= 0) {
                continue;
            }

            $memberProjects = $memberTrackers->pluck('clockify_projeto_id')->unique()->count();
            $memberDays = $memberTrackers->groupBy(fn($t) => $t->inicio->format('Y-m-d'))->count();
            $memberAvgDaily = $memberDays > 0 ? round($memberHours / $memberDays, 1) : 0;

            $memberRanking[] = [
                'id' => $member->id,
                'nome' => $member->nome,
                'total_hours' => $memberHours,
                'avg_daily' => $memberAvgDaily,
                'projects_count' => $memberProjects,
                'active_days' => $memberDays,
            ];
        }

        usort($memberRanking, fn($a, $b) => $b['total_hours'] <=> $a['total_hours']);

        // Weekly heatmap
        $heatmap = array_fill(0, 7, 0.0);
        foreach ($trackers as $tracker) {
            $dow = $tracker->inicio->dayOfWeekIso - 1;
            $heatmap[$dow] += $tracker->duration_in_seconds / 3600;
        }

        $weekDays = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
        $heatmapData = [];
        for ($i = 0; $i < 7; $i++) {
            $heatmapData[] = [
                'day' => $weekDays[$i],
                'total_hours' => round($heatmap[$i], 1),
            ];
        }

        $bestDayIdx = array_keys($heatmap, max($heatmap))[0] ?? 0;
        $bestDay = $weekDays[$bestDayIdx];

        // Daily breakdown for chart
        $period = CarbonPeriod::create($startDate, $endDate);
        $dailyLabels = [];
        $dailyHours = [];
        foreach ($period as $date) {
            $dailyLabels[] = $date->format('d/m');
            $dayHours = $trackers->filter(fn($t) => $t->inicio->format('Y-m-d') === $date->format('Y-m-d'))
                ->sum('duration_in_seconds') / 3600;
            $dailyHours[] = round($dayHours, 2);
        }

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalHours' => $totalHours,
            'avgDailyHours' => $avgDailyHours,
            'totalDays' => $totalDays,
            'prevHours' => $prevHours,
            'percentChange' => $percentChange,
            'bestDay' => $bestDay,
            'memberRanking' => $memberRanking,
            'heatmapData' => $heatmapData,
            'dailyLabels' => $dailyLabels,
            'dailyHours' => $dailyHours,
        ];
    }
}
