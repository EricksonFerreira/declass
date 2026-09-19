<?php

namespace App\Http\Controllers;

use App\Services\Ia\IaService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Monitoramento de uso de IA por empresa — somente superadmin.
 * Lê ia_usos sem o escopo de tenant (visão consolidada).
 */
class IaUsoController extends Controller
{
    public function index(Request $request): View
    {
        $inicio = $request->filled('inicio')
            ? Carbon::parse($request->inicio)->startOfDay()
            : now()->subDays(30)->startOfDay();
        $fim = $request->filled('fim')
            ? Carbon::parse($request->fim)->endOfDay()
            : now()->endOfDay();

        $base = fn () => DB::table('ia_usos')
            ->whereBetween('ia_usos.created_at', [$inicio, $fim])
            ->when($request->filled('empresa_id'), fn ($q) => $q->where('ia_usos.empresa_id', (int) $request->empresa_id))
            ->when($request->filled('provedor'), fn ($q) => $q->where('ia_usos.provedor', $request->provedor));

        $totais = $base()->selectRaw('
            count(*) as chamadas,
            coalesce(sum(tokens_entrada),0) as tokens_in,
            coalesce(sum(tokens_saida),0) as tokens_out,
            coalesce(sum(custo_estimado),0) as custo,
            count(distinct empresa_id) as empresas,
            sum(status = "erro") as erros
        ')->first();

        $porEmpresa = $base()
            ->join('empresas', 'empresas.id', '=', 'ia_usos.empresa_id')
            ->groupBy('ia_usos.empresa_id', 'empresas.nome')
            ->selectRaw('
                empresas.nome as empresa, ia_usos.empresa_id,
                count(*) as chamadas,
                sum(tokens_entrada) as tokens_in,
                sum(tokens_saida) as tokens_out,
                coalesce(sum(custo_estimado),0) as custo,
                sum(status = "erro") as erros,
                max(ia_usos.created_at) as ultimo_uso
            ')
            ->orderByDesc('tokens_in')
            ->get();

        $porRecurso = $base()
            ->groupBy('recurso')
            ->selectRaw('recurso, count(*) as chamadas, sum(tokens_entrada + tokens_saida) as tokens, coalesce(sum(custo_estimado),0) as custo')
            ->orderByDesc('chamadas')
            ->get();

        $porDia = $base()
            ->groupByRaw('date(created_at)')
            ->selectRaw('date(created_at) as dia, count(*) as chamadas, sum(tokens_entrada + tokens_saida) as tokens')
            ->orderBy('dia')
            ->get();

        $ultimas = $base()
            ->leftJoin('empresas', 'empresas.id', '=', 'ia_usos.empresa_id')
            ->leftJoin('users', 'users.id', '=', 'ia_usos.user_id')
            ->orderByDesc('ia_usos.created_at')
            ->limit(50)
            ->get([
                'ia_usos.*',
                'empresas.nome as empresa_nome',
                'users.nome as usuario_nome',
            ]);

        $empresas = DB::table('empresas')->orderBy('nome')->pluck('nome', 'id');

        return view('ia.uso', [
            'titulo' => 'Uso de IA por empresa',
            'totais' => $totais,
            'porEmpresa' => $porEmpresa,
            'porRecurso' => $porRecurso,
            'porDia' => $porDia,
            'ultimas' => $ultimas,
            'empresas' => $empresas,
            'provedores' => IaService::provedores(),
            'filtros' => [
                'inicio' => $inicio->format('Y-m-d'),
                'fim' => $fim->format('Y-m-d'),
                'empresa_id' => $request->empresa_id,
                'provedor' => $request->provedor,
            ],
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Configurações'],
                ['name' => 'Uso de IA', 'active' => true],
            ],
        ]);
    }
}
