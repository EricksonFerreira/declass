<?php

namespace App\Actions\Clockify\Dashboard;

use App\Models\ClockifyTimeTracker;
use App\Models\User;
use Carbon\Carbon;

class GetMemberStatsAction
{
    /**
     * Retrieve daily work hours per selected members within a period.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array|null $userIds
     * @return array
     */
    public function handle(Carbon $startDate, Carbon $endDate, ?array $userIds = null): array
    {
        // Fetch trackers in range
        $query = ClockifyTimeTracker::whereBetween('inicio', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->whereNotNull('fim');

        if (!empty($userIds)) {
            $query->whereIn('user_id', $userIds);
        }

        $trackers = $query->with('user')->get();

        // Prepare date labels (inclusive)
        $dateLabels = [];
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $dateLabels[] = $date->format('Y-m-d');
        }

        // Initialise member stats
        $memberStats = [];
        $selectedMembers = [];
        if (!empty($userIds)) {
            $selectedMembers = User::whereIn('id', $userIds)->orderBy('nome')->get();
        } else {
            $selectedMembers = User::orderBy('nome')->get();
        }

        foreach ($selectedMembers as $member) {
            $memberStats[$member->id] = [
                'name' => $member->nome,
                'color' => $this->colorFromString($member->nome), // deterministic color
                'daily' => array_fill_keys($dateLabels, 0.0),
                'total' => 0.0,
            ];
        }

        foreach ($trackers as $tracker) {
            $uid = $tracker->user_id;
            $date = $tracker->inicio->format('Y-m-d');
            $hours = $tracker->duration_in_seconds / 3600.0;
            if (isset($memberStats[$uid])) {
                $memberStats[$uid]['daily'][$date] += round($hours, 2);
                $memberStats[$uid]['total'] += round($hours, 2);
            }
        }

        // Ensure each daily array has values in same order as $dateLabels
        foreach ($memberStats as &$m) {
            $ordered = [];
            foreach ($dateLabels as $d) {
                $ordered[] = $m['daily'][$d] ?? 0.0;
            }
            $m['dailyOrdered'] = $ordered;
        }
        unset($m);

        return [
            'dateLabels' => $dateLabels,
            'memberStats' => $memberStats,
            'selectedMembers' => $selectedMembers,
        ];
    }

    /**
     * Generate a deterministic pastel color from a string.
     */
    private function colorFromString(string $text): string
    {
        $hash = crc32($text);
        $r = ($hash & 0xFF0000) >> 16;
        $g = ($hash & 0x00FF00) >> 8;
        $b = $hash & 0x0000FF;
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }
}
?>
