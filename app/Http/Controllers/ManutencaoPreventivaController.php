<?php

namespace App\Http\Controllers;

use App\Models\ExecucaoManutencaoPreventiva;
use App\Models\PendenciaManutencaoPreventiva;
use App\Models\Veiculo;
use App\Support\EmpresaContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManutencaoPreventivaController extends Controller
{
    private function getEmpresaId(): ?int
    {
        return app(EmpresaContext::class)->empresaId() ?? Auth::user()?->empresa_id;
    }

    public function index(Request $request)
    {
        $empresaId = $this->getEmpresaId();

        $pendenciasQuery = PendenciaManutencaoPreventiva::query()
            ->with(['veiculo', 'item', 'usuarioInclusao', 'usuarioAtualizacao'])
            ->where('empresa_id', $empresaId);

        if ($request->filled('veiculo_id')) {
            $pendenciasQuery->where('veiculo_id', (int) $request->input('veiculo_id'));
        }

        if ($request->filled('item_id')) {
            $pendenciasQuery->where('item_id', (int) $request->input('item_id'));
        }

        if ($request->filled('status')) {
            $pendenciasQuery->where('status', $request->input('status'));
        }

        $pendencias = $pendenciasQuery
            ->orderByRaw("CASE WHEN status = 'atrasado' THEN 0 ELSE 1 END")
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $execucoesQuery = ExecucaoManutencaoPreventiva::query()
            ->with(['veiculo', 'item', 'ordemServico', 'usuarioInclusao', 'usuarioAtualizacao'])
            ->where('empresa_id', $empresaId);

        if ($request->filled('veiculo_id')) {
            $execucoesQuery->where('veiculo_id', (int) $request->input('veiculo_id'));
        }

        if ($request->filled('item_id')) {
            $execucoesQuery->where('item_id', (int) $request->input('item_id'));
        }

        $execucoesRecentes = $execucoesQuery
            ->orderByDesc('data_execucao')
            ->limit(20)
            ->get();

        $veiculos = Veiculo::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get(['id', 'nome', 'placa_licenca']);

        $itens = \App\Models\ItemManutencaoPreventiva::query()
            ->where('empresa_id', $empresaId)
            ->orderBy('nome')
            ->get(['id', 'nome']);

        $stats = [
            'pendentes' => PendenciaManutencaoPreventiva::query()->where('empresa_id', $empresaId)->where('status', 'pendente')->count(),
            'atrasadas' => PendenciaManutencaoPreventiva::query()->where('empresa_id', $empresaId)->where('status', 'atrasado')->count(),
            'executadas' => ExecucaoManutencaoPreventiva::query()->where('empresa_id', $empresaId)->count(),
        ];

        $breadcrumb = [];
        $titulo = 'Manutenções Preventivas';

        return view('manutencao.preventivas.index', compact(
            'pendencias',
            'execucoesRecentes',
            'veiculos',
            'itens',
            'stats',
            'breadcrumb',
            'titulo'
        ));
    }
}
