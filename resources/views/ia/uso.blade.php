@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Consumo de IA consolidado entre empresas — tokens e custo estimado" />
@stop

@push('css')
    @include('partials.ux-index-styles')
@endpush

@section('content')
    @if (session('success'))
        <div class="alert alert-success shadow-sm">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Filtros --}}
    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-body py-2">
            <form method="GET" class="form-inline flex-wrap" style="gap:.5rem;">
                <label class="text-muted small mb-0">Período</label>
                <input type="date" name="inicio" class="form-control form-control-sm" value="{{ $filtros['inicio'] }}">
                <span class="text-muted">até</span>
                <input type="date" name="fim" class="form-control form-control-sm" value="{{ $filtros['fim'] }}">
                <select name="empresa_id" class="form-control form-control-sm">
                    <option value="">Todas as empresas</option>
                    @foreach ($empresas as $id => $nome)
                        <option value="{{ $id }}" @selected((string) $filtros['empresa_id'] === (string) $id)>{{ $nome }}</option>
                    @endforeach
                </select>
                <select name="provedor" class="form-control form-control-sm">
                    <option value="">Todos os provedores</option>
                    @foreach ($provedores as $slug => $nome)
                        <option value="{{ $slug }}" @selected($filtros['provedor'] === $slug)>{{ $nome }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i>Filtrar</button>
            </form>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="row">
        @php
            $cards = [
                ['label' => 'Chamadas de IA', 'valor' => number_format($totais->chamadas, 0, ',', '.'), 'icon' => 'fa-bolt', 'cor' => 'primary'],
                ['label' => 'Tokens (entrada)', 'valor' => number_format($totais->tokens_in, 0, ',', '.'), 'icon' => 'fa-sign-in-alt', 'cor' => 'info'],
                ['label' => 'Tokens (saída)', 'valor' => number_format($totais->tokens_out, 0, ',', '.'), 'icon' => 'fa-sign-out-alt', 'cor' => 'teal'],
                ['label' => 'Custo estimado', 'valor' => 'US$ '.number_format($totais->custo, 4, ',', '.'), 'icon' => 'fa-dollar-sign', 'cor' => 'success'],
                ['label' => 'Empresas usando', 'valor' => $totais->empresas, 'icon' => 'fa-building', 'cor' => 'warning'],
                ['label' => 'Erros', 'valor' => $totais->erros, 'icon' => 'fa-exclamation-triangle', 'cor' => 'danger'],
            ];
        @endphp
        @foreach ($cards as $c)
            <div class="col-6 col-md-4 col-xl-2">
                <div class="small-box bg-{{ $c['cor'] }}">
                    <div class="inner">
                        <h4 class="mb-0">{{ $c['valor'] }}</h4>
                        <p class="mb-0" style="font-size:.8rem;">{{ $c['label'] }}</p>
                    </div>
                    <div class="icon"><i class="fas {{ $c['icon'] }}"></i></div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Gráfico por dia --}}
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header">
            <h3 class="card-title text-bold text-sm"><i class="fas fa-chart-line mr-1"></i> Uso por dia</h3>
        </div>
        <div class="card-body">
            <canvas id="usoChart" style="max-height: 240px;"></canvas>
        </div>
    </div>

    <div class="row">
        {{-- Ranking por empresa --}}
        <div class="col-lg-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm"><i class="fas fa-building mr-1"></i> Por empresa</h3>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th class="text-right">Chamadas</th>
                                <th class="text-right">Tokens entrada</th>
                                <th class="text-right">Tokens saída</th>
                                <th class="text-right">Custo est.</th>
                                <th class="text-right">Limite/mês</th>
                                <th class="text-right">Erros</th>
                                <th>Último uso</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($porEmpresa as $e)
                                <tr>
                                    <td class="font-weight-bold">{{ $e->empresa }}</td>
                                    <td class="text-right">{{ number_format($e->chamadas, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($e->tokens_in, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($e->tokens_out, 0, ',', '.') }}</td>
                                    <td class="text-right">US$ {{ number_format($e->custo, 4, ',', '.') }}</td>
                                    <td class="text-right" style="min-width:150px;">
                                        <form method="POST" action="{{ route('ia-uso.limite') }}" class="d-inline-flex align-items-center" style="gap:.25rem;">
                                            @csrf
                                            <input type="hidden" name="empresa_id" value="{{ $e->empresa_id }}">
                                            <input type="hidden" name="inicio" value="{{ $filtros['inicio'] }}">
                                            <input type="hidden" name="fim" value="{{ $filtros['fim'] }}">
                                            <input type="hidden" name="provedor" value="{{ $filtros['provedor'] }}">
                                            <input type="number" name="ia_limite_tokens" min="0" placeholder="∞"
                                                   class="form-control form-control-sm text-right" style="width:100px;"
                                                   value="{{ $e->ia_limite_tokens }}" title="Tokens/mês — vazio = ilimitado">
                                            <button type="submit" class="btn btn-xs btn-outline-primary" title="Salvar limite"><i class="fas fa-check"></i></button>
                                        </form>
                                    </td>
                                    <td class="text-right">
                                        @if ($e->erros)
                                            <span class="badge badge-danger">{{ $e->erros }}</span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ \Carbon\Carbon::parse($e->ultimo_uso)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">Nenhuma chamada de IA no período.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Por recurso --}}
        <div class="col-lg-4">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-bold text-sm"><i class="fas fa-puzzle-piece mr-1"></i> Por recurso</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr><th>Recurso</th><th class="text-right">Chamadas</th><th class="text-right">Tokens</th><th class="text-right">Custo</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($porRecurso as $r)
                                <tr>
                                    <td><code>{{ $r->recurso }}</code></td>
                                    <td class="text-right">{{ number_format($r->chamadas, 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format($r->tokens, 0, ',', '.') }}</td>
                                    <td class="text-right">US$ {{ number_format($r->custo, 4, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">—</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Últimas chamadas --}}
    <div class="card card-outline card-secondary shadow-sm">
        <div class="card-header">
            <h3 class="card-title text-bold text-sm"><i class="fas fa-history mr-1"></i> Últimas chamadas</h3>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Empresa</th>
                        <th>Usuário</th>
                        <th>Recurso</th>
                        <th>Provedor / Modelo</th>
                        <th class="text-right">Tokens (in/out)</th>
                        <th class="text-right">Custo</th>
                        <th class="text-right">Duração</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ultimas as $u)
                        <tr>
                            <td class="text-nowrap">{{ \Carbon\Carbon::parse($u->created_at)->format('d/m/Y H:i') }}</td>
                            <td>{{ $u->empresa_nome }}</td>
                            <td class="text-muted">{{ $u->usuario_nome ?: '—' }}</td>
                            <td><code>{{ $u->recurso }}</code></td>
                            <td class="small">{{ $u->provedor }} · {{ $u->modelo }}</td>
                            <td class="text-right text-nowrap">{{ number_format($u->tokens_entrada, 0, ',', '.') }} / {{ number_format($u->tokens_saida, 0, ',', '.') }}</td>
                            <td class="text-right">{{ $u->custo_estimado !== null ? 'US$ '.number_format($u->custo_estimado, 5, ',', '.') : '—' }}</td>
                            <td class="text-right text-muted">{{ $u->duracao_ms ? number_format($u->duracao_ms / 1000, 1, ',', '.').'s' : '—' }}</td>
                            <td>
                                @if ($u->status === 'ok')
                                    <span class="badge badge-success">ok</span>
                                @else
                                    <span class="badge badge-danger" title="{{ $u->erro }}">erro</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted py-4">Nenhuma chamada registrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const dias = @json($porDia->pluck('dia'));
        new Chart(document.getElementById('usoChart'), {
            data: {
                labels: dias.map(d => d.split('-').reverse().slice(0, 2).join('/')),
                datasets: [
                    {
                        type: 'line',
                        label: 'Tokens',
                        data: @json($porDia->pluck('tokens')),
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13,110,253,.12)',
                        fill: true,
                        tension: .3,
                        yAxisID: 'y',
                    },
                    {
                        type: 'bar',
                        label: 'Chamadas',
                        data: @json($porDia->pluck('chamadas')),
                        backgroundColor: '#6c757d',
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { position: 'left', beginAtZero: true, title: { display: true, text: 'Tokens' } },
                    y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, title: { display: true, text: 'Chamadas' } },
                },
                plugins: { legend: { position: 'bottom' } },
            },
        });
    </script>
@endpush
