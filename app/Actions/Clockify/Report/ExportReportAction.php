<?php

namespace App\Actions\Clockify\Report;

use App\Models\ClockifyTimeTracker;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportReportAction
{
    /**
     * Execute the export.
     *
     * @param  string|null  $startDate  Format: Y-m-d
     * @param  string|null  $endDate  Format: Y-m-d
     * @param  array  $userIds  Array of user IDs
     * @param  string|null  $reportType  Identifier of the report (e.g., 'horas_trabalhadas_por_projeto')
     */
    public function execute(?string $startDate, ?string $endDate, array $userIds, ?string $reportType): StreamedResponse
    {
        // Normalise dates
        $start = $startDate ? Carbon::createFromFormat('Y-m-d', $startDate)->startOfDay() : Carbon::now()->subMonth();
        $end = $endDate ? Carbon::createFromFormat('Y-m-d', $endDate)->endOfDay() : Carbon::now()->endOfDay();

        $empresaId = auth()->user()->empresa_id ?? null;

        // Base query for time entries, sempre escopada pela empresa
        $query = ClockifyTimeTracker::query()
            ->whereBetween('inicio', [$start, $end])
            ->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId));

        if (! empty($userIds)) {
            $query->whereIn('user_id', $userIds);
        }

        $headers = ['Projeto', 'Usuário', 'Data', 'Início', 'Fim', 'Horas'];
        $rows = [];

        $entries = $query->with(['projeto', 'user'])->orderBy('inicio')->get();
        foreach ($entries as $e) {
            $rows[] = [
                $e->projeto->nome ?? 'N/A',
                $e->user->name ?? 'N/A',
                $e->inicio->format('Y-m-d'),
                $e->inicio->format('H:i'),
                $e->fim?->format('H:i') ?? '',
                number_format($e->duration_in_seconds / 3600, 2, ',', '.'),
            ];
        }

        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers, ';');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        };

        $fileName = 'relatorio_'.($reportType ?? 'geral').'_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
