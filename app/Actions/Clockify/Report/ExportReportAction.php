<?php

namespace App\Actions\Clockify\Report;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use App\Models\User;
use App\Models\TimeEntry;
use Carbon\Carbon;

class ExportReportAction
{
    /**
     * Execute the export.
     *
     * @param string|null $startDate   Format: Y-m-d
     * @param string|null $endDate     Format: Y-m-d
     * @param array $userIds           Array of user IDs
     * @param string|null $reportType  Identifier of the report (e.g., 'horas_trabalhadas_por_projeto')
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function execute(?string $startDate, ?string $endDate, array $userIds, ?string $reportType): StreamedResponse
    {
        // Normalise dates
        $start = $startDate ? Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay() : Carbon::now()->subMonth();
        $end   = $endDate ? Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay() : Carbon::now()->endOfDay();

        // Base query for time entries
        $query = TimeEntry::query()->whereBetween('started_at', [$start, $end]);

        if (!empty($userIds)) {
            $query->whereIn('user_id', $userIds);
        }

        $headers = [];
        $rows    = [];

        switch ($reportType) {
            case 'horas_trabalhadas_por_projeto':
                $headers = ['Projeto', 'Usuário', 'Data', 'Horas'];
                $entries = $query->with(['project', 'user'])->get();
                foreach ($entries as $e) {
                    $rows[] = [
                        $e->project->name ?? 'N/A',
                        $e->user->name ?? 'N/A',
                        $e->started_at->format('Y-m-d'),
                        number_format(($e->duration ?? 0) / 3600, 2),
                    ];
                }
                break;
            default:
                // Generic export of all entries
                $headers = ['ID', 'Projeto', 'Usuário', 'Início', 'Fim', 'Duração (s)'];
                $entries = $query->with(['project', 'user'])->get();
                foreach ($entries as $e) {
                    $rows[] = [
                        $e->id,
                        $e->project->name ?? 'N/A',
                        $e->user->name ?? 'N/A',
                        $e->started_at,
                        $e->ended_at,
                        $e->duration,
                    ];
                }
        }

        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        $fileName = 'relatorio_' . ($reportType ?? 'geral') . '_' . now()->format('Ymd_His') . '.csv';
        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
?>
