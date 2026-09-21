@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" subtitle="Consumo e limite mensal de IA da empresa" />
@stop

@push('css')
    @include('partials.ux-index-styles')
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-8">
            {{-- KPIs do mês --}}
            <div class="row">
                <div class="col-md-3 col-6">
                    <div class="small-box bg-primary shadow-sm">
                        <div class="inner"><h3>{{ number_format($totaisMes->chamadas ?? 0, 0, ',', '.') }}</h3><p>Chamadas no mês</p></div>
                        <div class="icon"><i class="fas fa-bolt"></i></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="small-box bg-info shadow-sm">
                        <div class="inner"><h3>{{ number_format($totaisMes->tokens_in ?? 0, 0, ',', '.') }}</h3><p>Tokens (entrada)</p></div>
                        <div class="icon"><i class="fas fa-sign-in-alt"></i></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="small-box bg-success shadow-sm">
                        <div class="inner"><h3>{{ number_format($totaisMes->tokens_out ?? 0, 0, ',', '.') }}</h3><p>Tokens (saída)</p></div>
                        <div class="icon"><i class="fas fa-sign-out-alt"></i></div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="small-box {{ ($totaisMes->erros ?? 0) > 0 ? 'bg-danger' : 'bg-secondary' }} shadow-sm">
                        <div class="inner"><h3>{{ number_format($totaisMes->erros ?? 0, 0, ',', '.') }}</h3><p>Erros</p></div>
                        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                    </div>
                </div>
            </div>

            {{-- Gráfico por dia --}}
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header"><h3 class="card-title text-bold text-sm"><i class="fas fa-chart-line mr-1"></i> Uso por dia (últimos 30 dias)</h3></div>
                <div class="card-body">
                    @if ($porDia->isEmpty())
                        <p class="text-muted text-center py-4 mb-0">Nenhuma chamada de IA registrada no período.</p>
                    @else
                        <canvas id="usoChart" style="max-height: 260px;"></canvas>
                    @endif
                </div>
            </div>

            {{-- Por recurso --}}
            @if ($porRecurso->isNotEmpty())
                <div class="card card-outline card-secondary shadow-sm">
                    <div class="card-header"><h3 class="card-title text-bold text-sm"><i class="fas fa-puzzle-piece mr-1"></i> Uso por recurso no mês</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Recurso</th><th class="text-center">Chamadas</th><th class="text-right">Tokens</th><th class="text-right">Custo est.</th></tr></thead>
                            <tbody>
                                @foreach ($porRecurso as $r)
                                    <tr>
                                        <td class="small">{{ $r->recurso }}</td>
                                        <td class="text-center small">{{ number_format($r->chamadas, 0, ',', '.') }}</td>
                                        <td class="text-right small">{{ number_format($r->tokens, 0, ',', '.') }}</td>
                                        <td class="text-right small">US$ {{ number_format((float) $r->custo, 4, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- Limite mensal --}}
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header"><h3 class="card-title text-bold text-sm"><i class="fas fa-tachometer-alt mr-1"></i> Limite mensal</h3></div>
                <div class="card-body">
                    @if ($usoMes['limite'] !== null)
                        @php $pct = $usoMes['limite'] > 0 ? min(100, round($usoMes['usado'] / $usoMes['limite'] * 100)) : 0; @endphp
                        <div class="d-flex justify-content-between small mb-1">
                            <span>{{ number_format($usoMes['usado'], 0, ',', '.') }} usados</span>
                            <span class="text-muted">{{ number_format($usoMes['limite'], 0, ',', '.') }} tokens</span>
                        </div>
                        <div class="progress" style="height:10px;">
                            <div class="progress-bar bg-{{ $pct >= 90 ? 'danger' : ($pct >= 70 ? 'warning' : 'success') }}" style="width:{{ $pct }}%"></div>
                        </div>
                        <div class="text-muted small mt-2">
                            Restam <strong>{{ number_format($usoMes['restante'], 0, ',', '.') }}</strong> tokens neste mês.
                            @if ($pct >= 90)<br><span class="text-danger">Cota quase esgotada — fale com o suporte para ampliar.</span>@endif
                        </div>
                    @else
                        <p class="text-muted small mb-0"><i class="fas fa-infinity mr-1"></i> Sem limite configurado — uso livre dentro do plano.</p>
                    @endif
                </div>
            </div>

        </div>
    </div>
@stop

@push('js')
    @if ($porDia->isNotEmpty())
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const dias = @json($porDia->pluck('dia'));
            new Chart(document.getElementById('usoChart'), {
                data: {
                    labels: dias.map(d => d.split('-').reverse().slice(0, 2).join('/')),
                    datasets: [
                        {
                            type: 'line', label: 'Tokens',
                            data: @json($porDia->pluck('tokens')),
                            borderColor: '#0d6efd', backgroundColor: 'rgba(13,110,253,.12)',
                            fill: true, tension: .3, yAxisID: 'y',
                        },
                        {
                            type: 'bar', label: 'Chamadas',
                            data: @json($porDia->pluck('chamadas')),
                            backgroundColor: '#6c757d', yAxisID: 'y1',
                        },
                    ],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    scales: {
                        y: { position: 'left', beginAtZero: true, title: { display: true, text: 'Tokens' } },
                        y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, title: { display: true, text: 'Chamadas' } },
                    },
                    plugins: { legend: { position: 'bottom' } },
                },
            });
        </script>
    @endif
@endpush
