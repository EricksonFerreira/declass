<?php

namespace App\Http\Controllers\Clockify;

use App\Http\Controllers\Controller;
use App\Models\ClockifyCliente;
use App\Models\ClockifyProjeto;
use App\Models\ClockifyTime;
use App\Models\ClockifyCalendario;
use App\Actions\Clockify\Projeto\ListarProjetosAction;
use App\Actions\Clockify\Projeto\SaveProjetoAction;
use App\Actions\Clockify\Projeto\UpdateProjetoAction;
use App\Actions\Clockify\Projeto\DeleteProjetoAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClockifyProjetoController extends Controller
{
    public function index(Request $request, ListarProjetosAction $action): View
    {
        $projetos = $action->handle($request->search);
        $clientes = ClockifyCliente::where('ativo', true)->orderBy('nome')->get();

        return view('clockify.projetos', [
            'titulo' => 'Projetos',
            'projetos' => $projetos,
            'clientes' => $clientes,
            'selectedProjectId' => null,
            'selectedStartDate' => null,
            'selectedEndDate' => null,
            'isSingleProject' => false,
            'inactiveProjects' => [],
            'chartLabels' => [],
            'chartData' => [],
            'chartColors' => [],
            'projectCards' => [],
            'matrix' => [],
            'matrixMembers' => [],
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Painel', 'url' => route('home')],
                ['name' => 'Projetos', 'active' => true],
            ],
        ]);
    }

    public function store(Request $request, SaveProjetoAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'clockify_cliente_id' => 'nullable|exists:clockify_clientes,id',
            'descricao' => 'nullable|string',
            'cor' => 'nullable|string|max:7',
            'publico' => 'nullable|boolean',
            'valor_faturado' => 'nullable|numeric|min:0',
            'data_entrega_previsao' => 'nullable|date',
            'horas_diarias_planejamento' => 'nullable|numeric|min:0',
        ]);

        $action->handle(array_merge($validated, [
            'publico' => $request->has('publico') ? (bool) $request->publico : true,
        ]));

        return redirect()->route('clockify.projetos.index')
            ->with('success', 'Projeto criado com sucesso!');
    }

    public function update(Request $request, $id, UpdateProjetoAction $action): RedirectResponse
    {
        $validated = $request->validate([
            'nome' => 'required|string|max:255',
            'clockify_cliente_id' => 'nullable|exists:clockify_clientes,id',
            'descricao' => 'nullable|string',
            'cor' => 'nullable|string|max:7',
            'publico' => 'nullable|boolean',
            'ativo' => 'nullable|boolean',

            'valor_faturado' => 'nullable',
            'data_entrega_previsao' => 'nullable|date',
            'horas_diarias_planejamento' => 'nullable|numeric|min:0',
        ]);

        $action->handle((int) $id, array_merge($validated, [
            'publico' => $request->has('publico') ? (bool) $request->publico : true,
            'ativo' => $request->has('ativo') ? (bool) $request->ativo : true,
        ]));

        return redirect()->route('clockify.projetos.show', $id)
            ->with('success', 'Projeto atualizado com sucesso!');
    }

    public function destroy($id, DeleteProjetoAction $action): RedirectResponse
    {
        $action->handle((int) $id);

        return redirect()->route('clockify.projetos.index')
            ->with('success', 'Projeto removido com sucesso!');
    }

    public function planejamento(Request $request, $id): View
    {
        $projeto = ClockifyProjeto::with('cronogramas.planejamentos')->findOrFail((int) $id);
        
        $selectedCronogramaId = $request->input('cronograma_id');
        $selectedCronograma = null;
        if ($selectedCronogramaId) {
            $selectedCronograma = $projeto->cronogramas->firstWhere('id', (int) $selectedCronogramaId);
        }
        if (!$selectedCronograma) {
            $selectedCronograma = $projeto->cronograma_ativo ?: $projeto->cronogramas->first();
        }

        $existingPlanning = [];
        if ($selectedCronograma) {
            foreach ($selectedCronograma->planejamentos as $plan) {
                $formattedDate = $plan->data->format('Y-m-d');
                $existingPlanning[$formattedDate] = [
                    'horas' => (float) $plan->horas,
                    'eh_extra' => (bool) $plan->eh_extra,
                ];
            }
        }

        return view('clockify.projetos_planejamento', [
            'titulo' => 'Planejamento de Entrega: ' . $projeto->nome,
            'projeto' => $projeto,
            'selectedCronograma' => $selectedCronograma,
            'existingPlanning' => $existingPlanning,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Painel', 'url' => route('home')],
                ['name' => 'Projetos', 'url' => route('clockify.projetos.index')],
                ['name' => $projeto->nome, 'url' => route('clockify.projetos.show', $projeto->id)],
                ['name' => 'Planejamento', 'active' => true],
            ],
        ]);
    }

    public function planejamentoStore(Request $request, $id): RedirectResponse
    {
        $projeto = ClockifyProjeto::findOrFail((int) $id);
        $request->validate([
            'cronograma_id' => 'required|exists:clockify_projeto_cronogramas,id',
            'planejamento' => 'required|array',
            'planejamento.*.horas' => 'required|numeric|min:0',
            'planejamento.*.eh_extra' => 'nullable|boolean',
            'valor_hora' => 'nullable|numeric|min:0',
            'valor_faturado' => 'nullable|numeric|min:0',
            'status' => 'required|in:valido,invalido,rascunho',
        ]);

        $cronogramaId = (int) $request->input('cronograma_id');
        $cronograma = \App\Models\ClockifyProjetoCronograma::findOrFail($cronogramaId);
        $planejamentosData = $request->input('planejamento');
        $valorHora = $request->input('valor_hora');
        $valorFaturado = $request->input('valor_faturado');
        $status = $request->input('status');

        \DB::transaction(function () use ($projeto, $cronograma, $planejamentosData, $valorHora, $valorFaturado, $status) {
            $cronograma->planejamentos()->delete();

            $insertData = [];
            $minDate = null;
            $maxDate = null;
            foreach ($planejamentosData as $dateStr => $data) {
                $horasFloat = (float) ($data['horas'] ?? 0.0);
                if ($horasFloat > 0) {
                    $insertData[] = [
                        'clockify_projeto_cronograma_id' => $cronograma->id,
                        'data' => $dateStr,
                        'horas' => $horasFloat,
                        'eh_extra' => isset($data['eh_extra']) && $data['eh_extra'] == '1',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                    if (is_null($minDate) || $dateStr < $minDate) {
                        $minDate = $dateStr;
                    }
                    if (is_null($maxDate) || $dateStr > $maxDate) {
                        $maxDate = $dateStr;
                    }
                }
            }

            if (!empty($insertData)) {
                \App\Models\ClockifyProjetoPlanejamento::insert($insertData);
            }

            if ($status === 'valido') {
                $projeto->cronogramas()->where('id', '!=', $cronograma->id)->where('status', 'valido')->update(['status' => 'rascunho']);
            }

            $cronograma->update([
                'data_inicio' => $minDate,
                'data_fim' => $maxDate,
                'valor_hora' => $valorHora,
                'valor_faturado' => $valorFaturado ?? 0.00,
                'status' => $status,
            ]);

            if ($status === 'valido') {
                $projeto->update([
                    'data_entrega_previsao' => $maxDate,
                    'valor_hora' => $valorHora,
                ]);
            }
        });

        return redirect()->route('clockify.projetos.planejamento', [$projeto->id, 'cronograma_id' => $cronograma->id])
            ->with('success', 'Planejamento de entrega salvo com sucesso!');
    }

    public function cronogramaStore(Request $request, $id): RedirectResponse
    {
        $projeto = ClockifyProjeto::findOrFail((int) $id);
        $request->validate([
            'nome' => 'required|string|max:255',
            'status' => 'required|in:valido,invalido,rascunho',
            'horas_orcadas' => 'nullable|numeric|min:0',
            'valor_hora' => 'nullable|numeric|min:0',
        ]);

        \DB::transaction(function () use ($projeto, $request) {
            if ($request->input('status') === 'valido') {
                $projeto->cronogramas()->where('status', 'valido')->update(['status' => 'rascunho']);
            }

            $cronograma = $projeto->cronogramas()->create([
                'nome' => $request->input('nome'),
                'status' => $request->input('status'),
                'horas_orcadas' => $request->input('horas_orcadas') ?? $projeto->horas_orcadas,
                'valor_hora' => $request->input('valor_hora') ?? $projeto->valor_hora,
            ]);

            if ($request->input('status') === 'valido') {
                $projeto->update([
                    'valor_hora' => $cronograma->valor_hora,
                ]);
            }
        });

        return redirect()->route('clockify.projetos.planejamento', $projeto->id)
            ->with('success', 'Nova versão de planejamento criada!');
    }

    public function cronogramaNovo($id): RedirectResponse
    {
        $projeto = ClockifyProjeto::findOrFail((int) $id);
        
        $nextNum = $projeto->cronogramas()->count() + 1;
        $nome = 'Versão ' . $nextNum;
        
        $cronograma = $projeto->cronogramas()->create([
            'clockify_projeto_id' => $projeto->id,
            'nome' => $nome,
            'status' => 'rascunho',
            'horas_orcadas' => $projeto->horas_orcadas,
            'valor_hora' => $projeto->valor_hora,
        ]);
        
        return redirect()->route('clockify.projetos.planejamento', [
            $projeto->id,
            'cronograma_id' => $cronograma->id
        ])->with('success', 'Nova versão criada: ' . $nome);
    }

    public function show($id): View
    {
        $projeto = ClockifyProjeto::with(['cliente', 'cronogramas.planejamentos'])->findOrFail((int) $id);
        $clientes = ClockifyCliente::where('ativo', true)->orderBy('nome')->get();

        return view('clockify.projetos_show', [
            'titulo' => 'Detalhes do Projeto: ' . $projeto->nome,
            'projeto' => $projeto,
            'clientes' => $clientes,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Painel', 'url' => route('home')],
                ['name' => 'Projetos', 'url' => route('clockify.projetos.index')],
                ['name' => 'Detalhes', 'active' => true],
            ],
        ]);
    }

    public function cronogramaDestroy($id): RedirectResponse
    {
        $cronograma = \App\Models\ClockifyProjetoCronograma::findOrFail((int) $id);
        $projetoId = $cronograma->clockify_projeto_id;
        
        \DB::transaction(function () use ($cronograma) {
            $cronograma->planejamentos()->delete();
            $cronograma->delete();
        });

        return redirect()->route('clockify.projetos.show', $projetoId)
            ->with('success', 'Versão de planejamento excluída com sucesso!');
    }

    public function planejarEquipe($id): View
    {
        $projeto = ClockifyProjeto::with(['cliente', 'cronogramas'])->findOrFail((int) $id);
        $times = ClockifyTime::with('users')->orderBy('nome')->get();

        $alocacoes = ClockifyCalendario::with('user')
            ->where('clockify_projeto_id', $projeto->id)
            ->get()
            ->groupBy('user_id');

        // Compute overall date range across all cronogramas with dates set
        $cronogramasComDatas = $projeto->cronogramas->filter(
            fn($c) => $c->data_inicio && $c->data_fim
        );
        $dataInicioGlobal = $cronogramasComDatas->min('data_inicio');
        $dataFimGlobal    = $cronogramasComDatas->max('data_fim');

        return view('clockify.projetos_planejar_equipe', [
            'titulo'            => 'Planejar Equipe: ' . $projeto->nome,
            'projeto'           => $projeto,
            'times'             => $times,
            'cronograma'        => $projeto->cronograma_ativo,
            'cronogramas'       => $projeto->cronogramas,
            'dataInicioGlobal'  => $dataInicioGlobal ? $dataInicioGlobal->format('Y-m-d') : '',
            'dataFimGlobal'     => $dataFimGlobal    ? $dataFimGlobal->format('Y-m-d')    : '',
            'alocacoes'         => $alocacoes,
            'breadcrumb'        => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Painel', 'url' => route('home')],
                ['name' => 'Projetos', 'url' => route('clockify.projetos.index')],
                ['name' => $projeto->nome, 'url' => route('clockify.projetos.show', $projeto->id)],
                ['name' => 'Planejar Equipe', 'active' => true],
            ],
        ]);
    }

    public function planejarEquipeStore(Request $request, $id): RedirectResponse
    {
        $projeto = ClockifyProjeto::findOrFail((int) $id);

        $request->validate([
            'alocacoes'                          => 'nullable|array',
            'alocacoes.*.user_id'                => 'required|exists:users,id',
            'alocacoes.*.clockify_projeto_cronograma_id' => 'nullable|exists:clockify_projeto_cronogramas,id',
            'alocacoes.*.observacao'             => 'nullable|string',
            'alocacoes.*.horas_por_dia'          => 'nullable|numeric|min:0',
            'alocacoes.*.data_inicio'            => 'required_with:alocacoes.*.checked|nullable|date',
            'alocacoes.*.data_fim'               => 'required_with:alocacoes.*.checked|nullable|date|after_or_equal:alocacoes.*.data_inicio',
        ]);

        $alocacoesData = $request->input('alocacoes', []);

        \DB::transaction(function () use ($projeto, $alocacoesData) {
            ClockifyCalendario::where('clockify_projeto_id', $projeto->id)->delete();

            foreach ($alocacoesData as $data) {
                if (isset($data['checked']) && $data['checked'] == '1') {
                    ClockifyCalendario::create([
                        'empresa_id'                     => session('empresa_id') ?? 1,
                        'user_id'                        => $data['user_id'],
                        'clockify_projeto_id'            => $projeto->id,
                        'clockify_projeto_cronograma_id' => $data['clockify_projeto_cronograma_id'] ?? null,
                        'data_inicio'                    => $data['data_inicio'],
                        'data_fim'                       => $data['data_fim'],
                        'horas_por_dia'                  => $data['horas_por_dia'] ?? 8,
                        'observacao'                     => $data['observacao'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('clockify.projetos.show', $projeto->id)
            ->with('success', 'Planejamento de equipe salvo com sucesso!');
    }

    public function planejarEquipeGeral(Request $request): View
    {
        $projetoId = $request->query('projeto_id');
        $projetos = ClockifyProjeto::where('ativo', true)->orderBy('nome')->get();
        
        $projeto = null;
        $times = [];
        $alocacoes = [];

        if ($projetoId) {
            $projeto = ClockifyProjeto::with('cliente')->findOrFail((int) $projetoId);
            $times = ClockifyTime::with('users')->orderBy('nome')->get();
            $alocacoes = ClockifyCalendario::with('user')
                ->where('clockify_projeto_id', $projeto->id)
                ->get()
                ->groupBy('user_id');
        }

        return view('clockify.projetos_planejar_equipe', [
            'titulo' => 'Planejar Equipe' . ($projeto ? ': ' . $projeto->nome : ''),
            'projeto' => $projeto,
            'projetos' => $projetos,
            'times' => $times,
            'alocacoes' => $alocacoes,
            'breadcrumb' => [
                ['name' => 'Home', 'url' => route('home')],
                ['name' => 'Painel', 'url' => route('home')],
                ['name' => 'Projetos', 'url' => route('clockify.projetos.index')],
                ['name' => 'Planejar Equipe', 'active' => true],
            ],
        ]);
    }

    public function planejarEquipeGeralStore(Request $request): RedirectResponse
    {
        $request->validate([
            'projeto_id' => 'required|exists:clockify_projetos,id',
            'alocacoes' => 'nullable|array',
            'alocacoes.*.user_id' => 'required|exists:users,id',
            'alocacoes.*.observacao' => 'nullable|string',
            'alocacoes.*.horas_por_dia' => 'nullable|numeric|min:0',
            'alocacoes.*.data_inicio' => 'required_with:alocacoes.*.checked|nullable|date',
            'alocacoes.*.data_fim' => 'required_with:alocacoes.*.checked|nullable|date|after_or_equal:alocacoes.*.data_inicio',
        ]);

        $projetoId = $request->input('projeto_id');
        $alocacoesData = $request->input('alocacoes', []);

        \DB::transaction(function () use ($projetoId, $alocacoesData) {
            // Delete existing allocations for this project
            ClockifyCalendario::where('clockify_projeto_id', $projetoId)->delete();

            foreach ($alocacoesData as $data) {
                if (isset($data['checked']) && $data['checked'] == '1') {
                    ClockifyCalendario::create([
                        'empresa_id' => session('empresa_id') ?? 1,
                        'user_id' => $data['user_id'],
                        'clockify_projeto_id' => $projetoId,
                        'data_inicio' => $data['data_inicio'],
                        'data_fim' => $data['data_fim'],
                        'horas_por_dia' => $data['horas_por_dia'] ?? 8,
                        'observacao' => $data['observacao'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('clockify.projetos.show', $projetoId)
            ->with('success', 'Planejamento de equipe salvo com sucesso!');
    }
}
