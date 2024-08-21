<?php

namespace App\Http\Controllers;

use App\Services\PowerBIService;
use Illuminate\Http\Request;
use DB;

class PowerBIController extends Controller
{
    protected $powerBI;

    public function __construct(PowerBIService $powerBI)
    {
        $this->powerBI = $powerBI;
    }

    public function listReports()
    {
        // $grupos = $this->powerBI->listGroups();
        // // dd($grupos);
        // if (isset($grupos['value'])) {

        //     foreach ($grupos['value'] as $grupo) {
        //         // dump($grupo);
        //         $groupId = $grupo['id'];
        //         $reports = $this->powerBI->listReports($groupId);
        //         // dd($reports);

        //         $this->criarTabelaGruposCasoNecessario();
        //         $this->atualizaGrupos($grupo);
        //         $this->criarTabelaRelatoriosCasoNecessario();
        //         $this->atualizaRelatorios($reports, $groupId);
        //     }
        // }
        // dd();

        $reports = DB::table('powerbi_reports')->join('powerbi_groups', 'powerbi_reports.grupo_id', '=', 'powerbi_groups.id')
        ->select('powerbi_reports.*', 'powerbi_groups.name as group_name')
        ->orderBy('powerbi_groups.name', 'asc')
        ->get();
        return view('powerbi.reports', compact('reports'));
    }

    public function showReport($reportId)
    {
        $report = DB::table('powerbi_reports')->where('id', $reportId)->first();
        $embedUrl = $report->embed_url;
        $accessToken = $this->powerBI->embedToken($report->grupo_id, $report->id); // Supondo que você tenha um método para obter o token de acesso
        if(isset($accessToken->token)){
            $accessToken = $accessToken->token;
        } else {
            return redirect()->back()->with('error', 'Token de acesso inválido.');
        }
        $reportId = $report->id;
        return view('powerbi.report', compact('reportId','embedUrl', 'accessToken'));
    }

    private function criarTabelaGruposCasoNecessario()
    {
        // Verificar e criar a tabela se não existir
        DB::statement("
            CREATE TABLE IF NOT EXISTS powerbi_groups (
                id VARCHAR(36) PRIMARY KEY,
                is_read_only BOOLEAN NOT NULL,
                is_on_dedicated_capacity BOOLEAN NOT NULL,
                capacity_id VARCHAR(36) NOT NULL,
                default_dataset_storage_format VARCHAR(50) NOT NULL,
                type VARCHAR(50) NOT NULL,
                name VARCHAR(255) NOT NULL,
                created_at TIMESTAMP NULL DEFAULT NULL,
                updated_at TIMESTAMP NULL DEFAULT NULL
            )
        ");
    }

    private function criarTabelaRelatoriosCasoNecessario()
    {
        // Verificar e criar a tabela se não existir
        DB::statement("
        CREATE TABLE IF NOT EXISTS powerbi_reports (
            id VARCHAR(36) PRIMARY KEY,
            report_type VARCHAR(50) NOT NULL,
            grupo_id VARCHAR(36),
            name VARCHAR(255) NOT NULL,
            web_url TEXT NOT NULL,
            embed_url TEXT NOT NULL,
            is_from_pbix BOOLEAN NOT NULL,
            is_owned_by_me BOOLEAN NOT NULL,
            dataset_id VARCHAR(36) NOT NULL,
            dataset_workspace_id VARCHAR(36) NOT NULL,
            created_at TIMESTAMP NULL DEFAULT NULL,
            updated_at TIMESTAMP NULL DEFAULT NULL,
            FOREIGN KEY (grupo_id) REFERENCES powerbi_groups(id)
        )
    ");
    }

    private function atualizaRelatorios($relatorios, $grupoId)
    {
        // Registrar o relatório usando DB::
        foreach ($relatorios['value'] as $relatorio) {
            DB::table('powerbi_reports')->updateOrInsert(
                ['id' => $relatorio['id']],
                [
                    'grupo_id' => $grupoId,
                    'report_type' => $relatorio['reportType'],
                    'name' => $relatorio['name'],
                    'web_url' => $relatorio['webUrl'],
                    'embed_url' => $relatorio['embedUrl'],
                    'is_from_pbix' => $relatorio['isFromPbix'],
                    'is_owned_by_me' => $relatorio['isOwnedByMe'],
                    'dataset_id' => $relatorio['datasetId'],
                    'dataset_workspace_id' => $relatorio['datasetWorkspaceId'],
                    'updated_at' => now()
                ]
            );
        }
    }

    private function atualizaGrupos($grupo)
    {
        // Registrar o grupo usando DB::
        DB::table('powerbi_groups')->updateOrInsert(
            [
                'id' => $grupo['id'],
            ],
            [
                'is_read_only' => $grupo['isReadOnly'],
                'is_on_dedicated_capacity' => $grupo['isOnDedicatedCapacity'],
                'capacity_id' => $grupo['capacityId'] ?? '',
                'default_dataset_storage_format' => $grupo['defaultDatasetStorageFormat'] ?? '',
                'type' => $grupo['type'],
                'name' => $grupo['name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
