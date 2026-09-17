<?php

namespace App\Http\Controllers\Clockify;

use App\Http\Controllers\Controller;
use App\Models\ClockifyCliente;
use App\Models\ClockifyProjeto;
use App\Models\ClockifyProjetoEstimativa;
use App\Models\Funcao;
use App\Services\ConsumoProjetoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsumoRelatorioController extends Controller
{
    /**
     * Aba A01 — resumo: projetos × funções (consumida × estimada × %).
     */
    public function index(Request $request, ConsumoProjetoService $service): View
    {
        $empresaId = $service->empresaId();

        $projetos = ClockifyProjeto::query()
            ->with('cliente')
            ->when($request->filled('busca'), fn ($q) => $q->where('nome', 'like', '%'.trim((string) $request->busca).'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('cliente_id'), fn ($q) => $q->where('clockify_cliente_id', (int) $request->cliente_id))
            ->when($request->filled('ano'), fn ($q) => $q->whereYear('created_at', (int) $request->ano))
            ->orderBy('nome')
            ->get();

        $consumoTodos = $service->consumoPorFuncao($empresaId);
        $estimativasTodos = $service->estimativasPorFuncao($empresaId);
        $funcoes = $service->funcoesEmUso($empresaId, $consumoTodos, $estimativasTodos);

        $linhas = $projetos->map(fn ($p) => [
            'projeto' => $p,
            'resumo' => $service->resumoProjeto($p, $consumoTodos, $estimativasTodos),
        ]);

        $clientes = ClockifyCliente::orderBy('nome')->pluck('nome', 'id');
        $anos = ClockifyProjeto::query()->selectRaw('YEAR(created_at) as ano')->distinct()->orderByDesc('ano')->pluck('ano');

        return view('relatorios.consumo', [
            'titulo' => 'Consumo × Estimado',
            'linhas' => $linhas,
            'funcoes' => $funcoes,
            'clientes' => $clientes,
            'anos' => $anos,
            'statusLabels' => ClockifyProjeto::STATUS_LABELS,
            'filtros' => $request->only('busca', 'status', 'cliente_id', 'ano'),
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Relatórios', 'url' => route('relatorios.index')],
                ['name' => 'Consumo × Estimado', 'active' => true],
            ],
        ]);
    }

    /**
     * Aba A02 — detalhe do projeto: membros, funções e estimativas.
     */
    public function show(int $id, ConsumoProjetoService $service): View
    {
        $projeto = ClockifyProjeto::with(['cliente', 'estimativas.funcao'])->findOrFail($id);

        $linhasFuncao = $service->linhasPorFuncao($projeto);
        $membros = $service->consumoPorMembro($projeto)->map(function ($m) {
            $horas = ((float) $m->segundos) / 3600;

            return (object) [
                'user_id' => (int) $m->user_id,
                'nome' => $m->nome,
                'email' => $m->email,
                'funcao_nome' => $m->funcao_nome ?? 'Sem função',
                'horas' => round($horas, 2),
                'custo_hora' => (float) $m->custo_hora,
                'custo' => round($horas * (float) $m->custo_hora, 2),
            ];
        });

        $totais = [
            'consumida' => round(collect($linhasFuncao)->sum('consumida'), 2),
            'estimada' => round(collect($linhasFuncao)->sum(fn ($l) => $l['estimada'] ?? 0), 2),
            'custo' => round($membros->sum('custo'), 2),
        ];
        $totais['pct'] = $totais['estimada'] > 0 ? round(($totais['consumida'] / $totais['estimada']) * 100, 2) : null;
        $totais['efetividade'] = ($projeto->valor_faturado ?? 0) > 0
            ? round((float) $projeto->valor_faturado - $totais['custo'], 2)
            : null;

        return view('relatorios.consumo_show', [
            'titulo' => 'Consumo × Estimado: '.$projeto->nome,
            'projeto' => $projeto,
            'linhasFuncao' => $linhasFuncao,
            'membros' => $membros,
            'totais' => $totais,
            'funcoes' => $service->funcoesParaSelect(),
            'statusLabels' => ClockifyProjeto::STATUS_LABELS,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Relatórios', 'url' => route('relatorios.index')],
                ['name' => 'Consumo × Estimado', 'url' => route('relatorios.consumo')],
                ['name' => $projeto->nome, 'active' => true],
            ],
        ]);
    }

    public function estimativaStore(Request $request, int $id): RedirectResponse
    {
        $projeto = ClockifyProjeto::findOrFail($id);

        $data = $request->validate([
            'funcao_id' => [
                'nullable',
                'integer',
                \Illuminate\Validation\Rule::exists('funcoes', 'id')
                    ->where(fn ($q) => $q->where('empresa_id', $projeto->empresa_id)),
            ],
            'nova_funcao' => 'nullable|string|max:100|required_without:funcao_id',
            'horas_estimadas' => 'required|numeric|min:0.01',
        ]);

        $funcaoId = (int) ($data['funcao_id'] ?? 0);
        if ($funcaoId <= 0) {
            $funcao = Funcao::firstOrCreate(
                ['empresa_id' => $projeto->empresa_id, 'nome' => trim($data['nova_funcao'])],
                ['ativo' => true]
            );
            $funcaoId = (int) $funcao->id;
        }

        ClockifyProjetoEstimativa::updateOrCreate(
            ['clockify_projeto_id' => $projeto->id, 'funcao_id' => $funcaoId],
            ['empresa_id' => $projeto->empresa_id, 'horas_estimadas' => (float) $data['horas_estimadas']]
        );

        return redirect()->route('relatorios.consumo.show', $projeto->id)
            ->with('success', 'Estimativa salva.');
    }

    public function estimativaDestroy(int $estimativaId): RedirectResponse
    {
        $estimativa = ClockifyProjetoEstimativa::findOrFail($estimativaId);
        $projetoId = (int) $estimativa->clockify_projeto_id;
        $estimativa->delete();

        return redirect()->route('relatorios.consumo.show', $projetoId)
            ->with('success', 'Estimativa removida.');
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $projeto = ClockifyProjeto::findOrFail($id);

        $data = $request->validate([
            'status' => 'required|in:'.implode(',', array_keys(ClockifyProjeto::STATUS_LABELS)),
        ]);

        $projeto->update(['status' => $data['status']]);

        return redirect()->route('relatorios.consumo.show', $projeto->id)
            ->with('success', 'Status do projeto atualizado para '.$projeto->status_label.'.');
    }
}
