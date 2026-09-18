<?php

namespace App\Actions\Clockify\Dashboard;

use App\Models\ClockifyCalendario;
use App\Models\ClockifyProjeto;
use App\Models\ClockifyTimeOff;
use App\Models\ClockifyTimeTracker;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class LoadTeamOverviewAction
{
    public function handle(?string $startDateStr = null, ?string $endDateStr = null): array
    {
        $now = Carbon::now();
        $startDate = $startDateStr ? Carbon::parse($startDateStr)->startOfDay() : $now->copy()->startOfMonth()->startOfDay();
        $endDate = $endDateStr ? Carbon::parse($endDateStr)->endOfDay() : $now->copy()->endOfDay();

        // Limit planner to a maximum of 31 days to keep it readable, default to current month
        $totalDays = $startDate->diffInDays($endDate) + 1;
        if ($totalDays > 31) {
            $endDate = $startDate->copy()->addDays(30)->endOfDay();
        }

        // Escopo de visibilidade: admin ve a equipe toda; lider ve os
        // membros dos times que lidera; colaborador ve apenas a si mesmo.
        $idsVisiveis = \Illuminate\Support\Facades\Auth::user()->idsVisiveis();

        $members = User::orderBy('nome')
            ->when($idsVisiveis !== null, fn ($q) => $q->whereIn('id', $idsVisiveis))
            ->get();

        // Active trackers
        $activeTrackers = ClockifyTimeTracker::with(['user', 'projeto'])
            ->whereNull('fim')
            ->when($idsVisiveis !== null, fn ($q) => $q->whereIn('user_id', $idsVisiveis))
            ->orderBy('inicio', 'desc')
            ->get();

        $activeTrackers->each(function ($tracker) use ($now) {
            $seconds = $now->diffInSeconds($tracker->inicio);
            $tracker->tempoFormato = sprintf('%02d:%02d:%02d', intdiv($seconds, 3600), intdiv($seconds % 3600, 60), $seconds % 60);
        });

        $activeUserIds = $activeTrackers->pluck('user_id')->unique()->toArray();

        // Load Time Offs for this period
        $timeOffs = ClockifyTimeOff::when($idsVisiveis !== null, fn ($q) => $q->whereIn('user_id', $idsVisiveis))
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('data_inicio', [$startDate, $endDate])
                    ->orWhereBetween('data_fim', [$startDate, $endDate])
                    ->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->where('data_inicio', '<=', $startDate)
                            ->where('data_fim', '>=', $endDate);
                    });
            })
            ->get();

        // Load Planned Allocations (ClockifyCalendario) for this period
        $allocations = ClockifyCalendario::with('projeto')
            ->when($idsVisiveis !== null, fn ($q) => $q->whereIn('user_id', $idsVisiveis))
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('data_inicio', [$startDate, $endDate])
                    ->orWhereBetween('data_fim', [$startDate, $endDate])
                    ->orWhere(function ($sub) use ($startDate, $endDate) {
                        $sub->where('data_inicio', '<=', $startDate)
                            ->where('data_fim', '>=', $endDate);
                    });
            })
            ->get();

        // Member status and sum total seconds in period
        $memberStatus = [];
        $totalPeriodSeconds = 0;
        $memberHoursMap = [];

        // Trackers within period
        $periodTrackers = ClockifyTimeTracker::whereNotNull('fim')
            ->whereBetween('inicio', [$startDate, $endDate])
            ->get();

        // Build Planner Days
        $plannerDays = [];
        $period = CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $plannerDays[] = [
                'date_str' => $date->format('Y-m-d'),
                'day_num' => $date->day,
                'day_name' => $date->translatedFormat('D'),
            ];
        }

        $plannerData = [];

        foreach ($members as $member) {
            $isTracking = in_array($member->id, $activeUserIds);

            $lastTracker = ClockifyTimeTracker::where('user_id', $member->id)
                ->whereNotNull('fim')
                ->orderBy('fim', 'desc')
                ->first();

            $lastActivity = $lastTracker?->fim;
            $daysSince = $lastActivity ? (int) $lastActivity->diffInDays($now) : null;

            $memberTrackers = $periodTrackers->where('user_id', $member->id);
            $periodSeconds = $memberTrackers->sum('duration_in_seconds');

            $totalPeriodSeconds += $periodSeconds;
            $periodHours = round($periodSeconds / 3600, 1);
            $memberHoursMap[$member->nome] = $periodHours;

            $status = 'offline';
            if ($isTracking) {
                $status = 'tracking';
            } elseif ($lastActivity && $daysSince <= 1) {
                $status = 'recent';
            } elseif ($daysSince !== null && $daysSince > 7) {
                $status = 'inactive';
            }

            $memberStatus[] = [
                'id' => $member->id,
                'nome' => $member->nome,
                'status' => $status,
                'is_tracking' => $isTracking,
                'last_activity' => $lastActivity,
                'days_since_activity' => $daysSince,
                'period_hours' => $periodHours,
            ];

            // Build Planner Member Days
            $memberPlannerDays = [];
            $memberAllocations = $allocations->where('user_id', $member->id);
            $memberTimeOffs = $timeOffs->where('user_id', $member->id);

            foreach ($plannerDays as $pDay) {
                $dayDate = Carbon::parse($pDay['date_str']);

                // 1. Check for Time Off
                $dayTimeOff = $memberTimeOffs->first(fn ($off) => $dayDate->between($off->data_inicio->startOfDay(), $off->data_fim->endOfDay()));

                // 2. Check for Planned Allocations
                $dayAlloc = $memberAllocations->first(fn ($al) => $dayDate->between($al->data_inicio->startOfDay(), $al->data_fim->endOfDay()));

                if ($dayTimeOff) {
                    $memberPlannerDays[$pDay['date_str']] = [
                        'type' => 'off',
                        'label' => $dayTimeOff->tipo ?: 'Folga',
                        'color_class' => 'pink',
                        'hours' => 0,
                    ];
                } elseif ($dayAlloc) {
                    $hoursAlloc = (float) $dayAlloc->horas_por_dia;
                    $color = 'green';
                    $label = 'Completo';
                    if ($hoursAlloc > 8.0) {
                        $color = 'red';
                        $label = 'Excesso';
                    } elseif ($hoursAlloc < 8.0) {
                        $color = 'green-light';
                        $label = $hoursAlloc.'h aloc.';
                    }

                    $memberPlannerDays[$pDay['date_str']] = [
                        'type' => 'alloc',
                        'label' => $label,
                        'color_class' => $color,
                        'hours' => $hoursAlloc,
                        'projeto_nome' => $dayAlloc->projeto ? $dayAlloc->projeto->nome : 'Sem Projeto',
                        'projeto_cor' => $dayAlloc->projeto ? $dayAlloc->projeto->cor : '#6c757d',
                        'alloc_id' => $dayAlloc->id,
                        'observacao' => $dayAlloc->observacao ?: '',
                    ];
                } else {
                    // 3. Fallback to real logged hours
                    $daySeconds = $memberTrackers->filter(fn ($t) => $t->inicio->format('Y-m-d') === $pDay['date_str'])
                        ->sum('duration_in_seconds');
                    $dayHours = round($daySeconds / 3600, 1);

                    if ($dayHours > 0) {
                        if ($dayHours >= 8.0) {
                            $overHours = $dayHours - 8.0;
                            $memberPlannerDays[$pDay['date_str']] = [
                                'type' => 'work',
                                'label' => $overHours > 0 ? "+{$overHours}h" : 'Completo',
                                'color_class' => $overHours > 0 ? 'red' : 'green',
                                'hours' => $dayHours,
                            ];
                        } else {
                            $memberPlannerDays[$pDay['date_str']] = [
                                'type' => 'work',
                                'label' => "{$dayHours}h",
                                'color_class' => 'green-light',
                                'hours' => $dayHours,
                            ];
                        }
                    } else {
                        $memberPlannerDays[$pDay['date_str']] = [
                            'type' => 'empty',
                            'label' => '',
                            'color_class' => '',
                            'hours' => 0,
                        ];
                    }
                }
            }

            // Initials for avatar
            $nameParts = explode(' ', $member->nome);
            $initials = strtoupper(substr($nameParts[0], 0, 1).(isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));

            // Format allocations sub-list for UI timeline projects expansion
            $formattedAllocations = [];
            foreach ($memberAllocations as $alloc) {
                $formattedAllocations[] = [
                    'id' => $alloc->id,
                    'projeto_id' => $alloc->clockify_projeto_id,
                    'projeto_nome' => $alloc->projeto ? $alloc->projeto->nome : 'Sem Projeto',
                    'projeto_cor' => $alloc->projeto ? $alloc->projeto->cor : '#6c757d',
                    'data_inicio' => $alloc->data_inicio->toDateString(),
                    'data_fim' => $alloc->data_fim->toDateString(),
                    'horas_por_dia' => (float) $alloc->horas_por_dia,
                    'observacao' => $alloc->observacao ?: '',
                ];
            }

            $plannerData[] = [
                'id' => $member->id,
                'nome' => $member->nome,
                'initials' => $initials,
                'total_hours' => $periodHours,
                'days' => $memberPlannerDays,
                'allocations' => $formattedAllocations,
            ];
        }

        $statusOrder = ['tracking' => 0, 'recent' => 1, 'offline' => 2, 'inactive' => 3];
        usort($memberStatus, function ($a, $b) use ($statusOrder) {
            $diff = ($statusOrder[$a['status']] ?? 4) <=> ($statusOrder[$b['status']] ?? 4);

            return $diff !== 0 ? $diff : $b['period_hours'] <=> $a['period_hours'];
        });

        // Top Member
        arsort($memberHoursMap);
        $topMemberName = ! empty($memberHoursMap) && max($memberHoursMap) > 0 ? array_key_first($memberHoursMap) : 'Nenhum';
        $topMemberHours = ! empty($memberHoursMap) ? max($memberHoursMap) : 0;

        // Top Project
        $topProjectStats = ClockifyTimeTracker::select('clockify_projeto_id', DB::raw('SUM(TIMESTAMPDIFF(SECOND, inicio, fim)) as total_seconds'))
            ->whereNotNull('fim')
            ->whereBetween('inicio', [$startDate, $endDate])
            ->groupBy('clockify_projeto_id')
            ->orderByDesc('total_seconds')
            ->first();

        $topProjectName = 'Nenhum';
        if ($topProjectStats && $topProjectStats->total_seconds > 0) {
            $proj = ClockifyProjeto::find($topProjectStats->clockify_projeto_id);
            $topProjectName = $proj ? $proj->nome : 'Sem Projeto';
        }

        // Distribution for chart
        $distribution = collect($memberStatus)
            ->filter(fn ($m) => $m['period_hours'] > 0)
            ->map(fn ($m) => ['nome' => $m['nome'], 'hours' => $m['period_hours']])
            ->sortByDesc('hours')
            ->values()
            ->toArray();

        // Daily member stats for stacked bar chart
        $memberStatsAction = app(GetMemberStatsAction::class);
        $memberStatsData = $memberStatsAction->handle($startDate, $endDate);

        // Recent activities
        $recentActivities = ClockifyTimeTracker::with(['user', 'projeto'])
            ->whereNotNull('fim')
            ->whereBetween('inicio', [$startDate, $endDate])
            ->orderBy('inicio', 'desc')
            ->take(20)
            ->get();

        $inactiveMembers = collect($memberStatus)->filter(fn ($m) => $m['status'] === 'inactive')->values()->toArray();

        // Convert total time
        $totalHoursDecimal = $totalPeriodSeconds / 3600;
        $totalHoursWhole = floor($totalHoursDecimal);
        $totalMinutes = round(($totalHoursDecimal - $totalHoursWhole) * 60);
        $totalTimeFormatted = sprintf('%02d:%02d', $totalHoursWhole, $totalMinutes);

        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'activeTrackers' => $activeTrackers,
            'memberStatus' => $memberStatus,
            'distribution' => $distribution,
            'recentActivities' => $recentActivities,
            'inactiveMembers' => $inactiveMembers,
            'totalTracking' => count($activeUserIds),
            'totalMembers' => $members->count(),
            'totalTimeFormatted' => $totalTimeFormatted,
            'topMemberName' => $topMemberName,
            'topMemberHours' => $topMemberHours,
            'topProjectName' => $topProjectName,
            'dateLabels' => $memberStatsData['dateLabels'],
            'memberStats' => $memberStatsData['memberStats'],
            'plannerDays' => $plannerDays,
            'plannerData' => $plannerData,
        ];
    }
}
