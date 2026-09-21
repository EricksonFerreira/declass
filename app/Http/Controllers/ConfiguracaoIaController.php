<?php

namespace App\Http\Controllers;

use App\Services\Ia\IaService;
use App\Support\EmpresaContext;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Painel de uso de IA da empresa (somente leitura): consumo do mês,
 * cota mensal e série diária. A configuração de provedor/chave é
 * gerenciada globalmente pelo superadmin.
 */
class ConfiguracaoIaController extends Controller
{
    public function edit(IaService $ia): View
    {
        $empresaId = $this->empresaId();

        $porDia = DB::table('ia_usos')
            ->where('empresa_id', $empresaId)
            ->where('created_at', '>=', now()->subDays(30)->startOfDay())
            ->groupByRaw('date(created_at)')
            ->selectRaw('date(created_at) as dia, count(*) as chamadas, sum(tokens_entrada + tokens_saida) as tokens')
            ->orderBy('dia')
            ->get();

        $porRecurso = DB::table('ia_usos')
            ->where('empresa_id', $empresaId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->groupBy('recurso')
            ->selectRaw('recurso, count(*) as chamadas, sum(tokens_entrada + tokens_saida) as tokens, coalesce(sum(custo_estimado),0) as custo')
            ->orderByDesc('tokens')
            ->get();

        $totaisMes = DB::table('ia_usos')
            ->where('empresa_id', $empresaId)
            ->where('created_at', '>=', now()->startOfMonth())
            ->selectRaw('
                count(*) as chamadas,
                coalesce(sum(tokens_entrada),0) as tokens_in,
                coalesce(sum(tokens_saida),0) as tokens_out,
                coalesce(sum(custo_estimado),0) as custo,
                sum(status = "erro") as erros
            ')
            ->first();

        return view('configuracoes.ia', [
            'titulo' => 'Uso de IA',
            'usoMes' => $ia->usoMes($empresaId),
            'totaisMes' => $totaisMes,
            'porDia' => $porDia,
            'porRecurso' => $porRecurso,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Gerenciar'],
                ['name' => 'Uso de IA', 'active' => true],
            ],
        ]);
    }

    private function empresaId(): int
    {
        return (int) (app(EmpresaContext::class)->empresaId() ?? auth()->user()->empresa_id);
    }
}
