@extends('adminlte::page')

@section('title', 'Consumo × Estimado')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $fmtHora  = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.').'h';
    $fmtPct   = fn ($v) => $v === null ? '—' : number_format($v, 2, ',', '.').'%';
    $fmtMoney = fn ($v) => $v === null ? '—' : 'R$ '.number_format($v, 2, ',', '.');
    $corPct   = fn ($v) => $v === null ? 'secondary' : ($v > 100 ? 'danger' : 'success');
    $maxHora  = max(collect($linhasFuncao)->max(fn ($l) => max($l['consumida'], $l['estimada'] ?? 0)), 1);
@endphp

@if (session('success'))
    <div class="alert alert-success"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach
    </div>
@endif

{{-- Cabeçalho do projeto --}}
<div class="card">
    <div class="card-body d-flex flex-wrap align-items-center justify-content-between" style="gap:1rem;">
        <div>
            <h4 class="mb-1 font-weight-bold">
                {{ $projeto->nome }}
                @if ($projeto->ano || $projeto->codigo)
                    <span class="badge badge-secondary align-middle" style="font-size:.65em;" title="{{ $projeto->identificacao_completa }}">{{ $projeto->ano ? $projeto->ano.'.'.str_pad((string) $projeto->numero, 3, '0', STR_PAD_LEFT).'-' : '' }}{{ $projeto->codigo }}</span>
                @endif
            </h4>
            <div class="text-muted" style="font-size:.85rem;">
                {{ $projeto->cliente?->nome ?? 'Sem cliente' }}
                <span class="mx-2">·</span>
                <span class="badge badge-{{ $projeto->status_cor }}">{{ $projeto->status_label }}</span>
            </div>
        </div>
        <div class="d-flex align-items-center" style="gap:.5rem;">
            <form method="POST" action="{{ route('relatorios.consumo.status', $projeto->id) }}" class="d-flex align-items-center" style="gap:.4rem;">
                @csrf @method('PATCH')
                <select name="status" class="form-control form-control-sm">
                    @foreach ($statusLabels as $key => $label)
                        <option value="{{ $key }}" {{ $projeto->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-outline-primary">Atualizar status</button>
            </form>
            <a href="{{ route('operacao.projetos.show', $projeto->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-project-diagram mr-1"></i> Ver projeto
            </a>
        </div>
    </div>
</div>

{{-- Alerta de estouro de estimativa --}}
@if ($previsao)
    @if ($previsao['estourado'])
        <div class="alert alert-danger d-flex align-items-center" style="gap:.6rem;">
            <i class="fas fa-exclamation-triangle fa-lg"></i>
            <div>
                <strong>Estimativa esgotada.</strong>
                O projeto já consumiu {{ $fmtHora($totais['consumida']) }} de {{ $fmtHora($totais['estimada']) }} estimadas
                ({{ $fmtPct($totais['pct']) }}). Novas horas aumentam o estouro.
            </div>
        </div>
    @elseif (($totais['pct'] ?? 0) >= 80 || ($previsao['dias'] ?? 99) <= 10)
        <div class="alert alert-warning d-flex align-items-center" style="gap:.6rem;">
            <i class="fas fa-exclamation-triangle fa-lg"></i>
            <div>
                <strong>Atenção: {{ $fmtPct($totais['pct']) }} da estimativa consumida.</strong>
                Na velocidade atual (~{{ number_format($previsao['media_diaria'], 1, ',', '.') }}h/dia),
                a estimativa esgota em <strong>~{{ $previsao['dias'] }} {{ $previsao['dias'] === 1 ? 'dia' : 'dias' }}</strong>.
            </div>
        </div>
    @endif
@endif

{{-- KPIs --}}
<div class="row mb-3">
    <div class="col-lg-2 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">HH Consumida</div>
            <div class="h4 font-weight-bold mb-0">{{ $fmtHora($totais['consumida']) }}</div>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">HH Estimada</div>
            <div class="h4 font-weight-bold mb-0">{{ $totais['estimada'] > 0 ? $fmtHora($totais['estimada']) : '—' }}</div>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">% Consumido</div>
            <div class="h4 font-weight-bold mb-0">
                @if ($totais['pct'] !== null)
                    <span class="badge badge-{{ $corPct($totais['pct']) }} p-2">{{ $fmtPct($totais['pct']) }}</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">Custo consumido</div>
            <div class="h4 font-weight-bold mb-0">{{ $totais['custo'] > 0 ? $fmtMoney($totais['custo']) : '—' }}</div>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">Valor do contrato</div>
            <div class="h4 font-weight-bold mb-0">{{ $fmtMoney($projeto->valor_faturado) }}</div>
        </div>
    </div>
    <div class="col-lg-2 col-sm-4 col-6 mb-2">
        <div class="card p-3 h-100">
            <div class="text-xs text-muted text-uppercase font-weight-bold">Efetividade</div>
            <div class="h4 font-weight-bold mb-0 {{ ($totais['efetividade'] ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                {{ $totais['efetividade'] !== null ? $fmtMoney($totais['efetividade']) : '—' }}
            </div>
        </div>
    </div>
</div>

{{-- Evolução do consumo (curva acumulada × estimativa) --}}
@if (count($evolucao['labels']) > 0)
<div class="card">
    <div class="card-header">
        <h3 class="card-title font-weight-bold"><i class="fas fa-chart-line mr-1"></i> Evolução do consumo</h3>
    </div>
    <div class="card-body">
        <canvas id="evolucaoChart" style="max-height: 260px;"></canvas>
    </div>
</div>
@endif

<div class="row">
    {{-- Funções: tabela + gráfico + editor de estimativas --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-chart-bar mr-1"></i> Consumo por função</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="pl-3">Função</th>
                            <th class="text-right">HH Consumida</th>
                            <th class="text-right">HH Estimada</th>
                            <th class="text-center">% Consumido</th>
                            <th class="text-center pr-3" style="width:90px;">Estimativa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($linhasFuncao as $l)
                            @php $estimativa = $projeto->estimativas->firstWhere('funcao_id', $l['funcao_id']); @endphp
                            <tr>
                                <td class="pl-3 font-weight-bold">{{ $l['nome'] }}</td>
                                <td class="text-right">{{ $fmtHora($l['consumida']) }}</td>
                                <td class="text-right">{{ $fmtHora($l['estimada']) }}</td>
                                <td class="text-center">
                                    @if ($l['pct'] !== null)
                                        <span class="badge badge-{{ $corPct($l['pct']) }}">{{ $fmtPct($l['pct']) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center pr-3">
                                    @if ($estimativa)
                                        <form method="POST" action="{{ route('relatorios.consumo.estimativas.destroy', $estimativa->id) }}"
                                              onsubmit="return confirm('Remover a estimativa de {{ $l['nome'] }}?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-outline-danger" title="Remover estimativa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Nenhuma hora registrada neste projeto ainda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Gráfico consumida × estimada --}}
            <div class="card-body border-top">
                <div class="text-xs text-muted text-uppercase font-weight-bold mb-3">
                    <span class="mr-3"><i class="fas fa-square" style="color:#f59e0b;"></i> Consumida</span>
                    <span><i class="fas fa-square" style="color:#60a5fa;"></i> Estimada</span>
                </div>
                @foreach ($linhasFuncao as $l)
                    <div class="mb-3">
                        <div class="text-xs font-weight-bold mb-1">{{ $l['nome'] }}</div>
                        <div class="d-flex align-items-center mb-1" style="gap:.5rem;">
                            <div style="height:14px;width:{{ round(($l['consumida'] / $maxHora) * 100) }}%;background:#f59e0b;border-radius:3px;min-width:2px;"></div>
                            <span class="text-xs">{{ $fmtHora($l['consumida']) }}</span>
                        </div>
                        <div class="d-flex align-items-center" style="gap:.5rem;">
                            <div style="height:14px;width:{{ round((($l['estimada'] ?? 0) / $maxHora) * 100) }}%;background:#60a5fa;border-radius:3px;min-width:2px;"></div>
                            <span class="text-xs text-muted">{{ $fmtHora($l['estimada']) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Nova estimativa --}}
            <div class="card-footer">
                <form method="POST" action="{{ route('relatorios.consumo.estimativas.store', $projeto->id) }}" class="form-inline flex-wrap" style="gap:.5rem;">
                    @csrf
                    <select name="funcao_id" class="form-control form-control-sm" style="width:180px;">
                        <option value="">— Nova função —</option>
                        @foreach ($funcoes as $id => $nome)
                            <option value="{{ $id }}">{{ $nome }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="nova_funcao" class="form-control form-control-sm" placeholder="Ou digite a função" style="width:160px;">
                    <input type="number" name="horas_estimadas" class="form-control form-control-sm" placeholder="Horas" step="0.01" min="0.01" style="width:110px;" required>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-plus mr-1"></i> Adicionar estimativa</button>
                </form>
                <small class="text-muted d-block mt-2">
                    Se a função já existir no projeto, o valor é atualizado. Funções novas ficam disponíveis para todos os projetos.
                </small>
            </div>
        </div>
    </div>

    {{-- Membros --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold"><i class="fas fa-users mr-1"></i> Horas por colaborador</h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th class="pl-3">Colaborador</th>
                            <th>Função</th>
                            <th class="text-right">HH</th>
                            <th class="text-right">Custo/h</th>
                            <th class="text-right pr-3">Custo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($membros as $m)
                            <tr>
                                <td class="pl-3">
                                    <div class="font-weight-bold">{{ $m->nome }}</div>
                                    <small class="text-muted">{{ $m->email }}</small>
                                </td>
                                <td>{{ $m->funcao_nome }}</td>
                                <td class="text-right">{{ $fmtHora($m->horas) }}</td>
                                <td class="text-right">{{ $m->custo_hora > 0 ? $fmtMoney($m->custo_hora) : '—' }}</td>
                                <td class="text-right pr-3">{{ $m->custo > 0 ? $fmtMoney($m->custo) : '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">Nenhum lançamento de horas neste projeto.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($membros->isNotEmpty() && $membros->sum('custo') <= 0)
                <div class="card-footer text-muted" style="font-size:.78rem;">
                    <i class="fas fa-info-circle mr-1"></i>
                    Defina o custo/hora de cada função para medir a efetividade do contrato.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('js')
@if (count($evolucao['labels']) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    new Chart(document.getElementById('evolucaoChart'), {
        type: 'line',
        data: {
            labels: @json($evolucao['labels']),
            datasets: [
                {
                    label: 'HH acumulada',
                    data: @json($evolucao['acumulada']),
                    borderColor: '#f59e0b',
                    backgroundColor: 'rgba(245, 158, 11, .12)',
                    fill: true,
                    tension: .25,
                    pointRadius: 2
                },
                {
                    label: 'HH por dia',
                    data: @json($evolucao['diaria']),
                    type: 'bar',
                    backgroundColor: 'rgba(96, 165, 250, .55)',
                    borderRadius: 3
                }
                @if (($totais['estimada'] ?? 0) > 0)
                ,{
                    label: 'HH estimada',
                    data: @json(array_fill(0, count($evolucao['labels']), $totais['estimada'])),
                    borderColor: '#ef4444',
                    borderDash: [6, 4],
                    fill: false,
                    pointRadius: 0
                }
                @endif
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Horas' } }
            },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ' ' + ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('pt-BR') + 'h'
                    }
                }
            }
        }
    });
</script>
@endif
@endsection
