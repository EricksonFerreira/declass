@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    .prod-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .prod-kpi {
        background: #fff; border-radius: 14px; padding: 20px 18px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06); border: 1px solid #eef1f6;
        position: relative; overflow: hidden;
    }
    .prod-kpi::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background: var(--accent, #0891b2); }
    .prod-kpi__label { font-family:'Plus Jakarta Sans',sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#64748b; margin:0 0 6px; }
    .prod-kpi__value { font-family:'Plus Jakarta Sans',sans-serif; font-size:26px; font-weight:800; color:#0f172a; line-height:1.1; margin:0; }
    .prod-kpi__sub { font-size:12px; color:#94a3b8; margin:4px 0 0; }
    
    .heatmap-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; margin-top: 14px; }
    .heatmap-day { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; text-align: center; }
    .heatmap-day__name { font-weight: 700; color: #475569; font-size: 13px; margin-bottom: 4px; }
    .heatmap-day__value { font-weight: 800; color: #0f172a; font-size: 16px; }
    .heatmap-day.intensity-high { background: #e0f2fe; border-color: #bae6fd; }
    .heatmap-day.intensity-very-high { background: #bae6fd; border-color: #7dd3fc; }

    .c-teal { --accent:#0891b2; }
    .c-emerald { --accent:#059669; }
    .c-indigo { --accent:#4f46e5; }
    .c-amber { --accent:#d97706; }
</style>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('home.produtividade') }}" method="GET" class="form-inline">
                <div class="form-group mr-3 mb-2">
                    <label for="data_inicio" class="mr-2 text-sm text-bold text-muted">Período de:</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="form-control form-control-sm" value="{{ $selectedStartDate }}">
                </div>
                <div class="form-group mr-3 mb-2">
                    <label for="data_fim" class="mr-2 text-sm text-bold text-muted">Até:</label>
                    <input type="date" name="data_fim" id="data_fim" class="form-control form-control-sm" value="{{ $selectedEndDate }}">
                </div>
                @if (Auth::user()->can('gerenciar-empresa'))
                    <div class="form-group mr-3 mb-2">
                        <label for="user_ids" class="mr-2 text-sm text-bold text-muted">Colaboradores:</label>
                        <select name="user_ids[]" id="user_ids" class="form-control form-control-sm select2" multiple="multiple" style="min-width: 250px;">
                            @foreach ($usuarios as $user)
                                <option value="{{ $user->id }}" @selected(in_array($user->id, (array)$selectedUserIds))>{{ $user->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button type="submit" class="btn btn-sm btn-primary mb-2 font-weight-bold">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
                <a href="{{ route('home.produtividade') }}" class="btn btn-sm btn-default mb-2 ml-2">Limpar</a>
            </form>
        </div>
    </div>

    <!-- KPIs -->
    <div class="prod-grid">
        <div class="prod-kpi c-teal">
            <p class="prod-kpi__label">Total do Período</p>
            <p class="prod-kpi__value">{{ number_format($totalHours, 1) }}h</p>
            <p class="prod-kpi__sub">Horas rastreadas</p>
        </div>
        <div class="prod-kpi c-emerald">
            <p class="prod-kpi__label">Média Diária</p>
            <p class="prod-kpi__value">{{ number_format($avgDailyHours, 1) }}h</p>
            <p class="prod-kpi__sub">Média geral</p>
        </div>
        <div class="prod-kpi c-indigo">
            <p class="prod-kpi__label">Comparativo Período Ant.</p>
            <p class="prod-kpi__value">
                @if($percentChange !== null)
                    {{ $percentChange > 0 ? '+' : '' }}{{ $percentChange }}%
                @else
                    --
                @endif
            </p>
            <p class="prod-kpi__sub">Anterior: {{ number_format($prevHours, 1) }}h</p>
        </div>
        <div class="prod-kpi c-amber">
            <p class="prod-kpi__label">Dia mais produtivo</p>
            <p class="prod-kpi__value">{{ $bestDay }}</p>
            <p class="prod-kpi__sub">Dia da semana</p>
        </div>
    </div>

    <div class="row">
        <!-- Gráfico de horas diárias -->
        <div class="col-md-8">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-sm text-bold"><i class="fas fa-chart-bar mr-1"></i> Horas Diárias no Período</h3>
                </div>
                <div class="card-body">
                    <canvas id="dailyHoursChart" height="230"></canvas>
                </div>
            </div>
        </div>

        <!-- Heatmap semanal -->
        <div class="col-md-4">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-sm text-bold"><i class="fas fa-calendar-alt mr-1"></i> Intensidade por Dia</h3>
                </div>
                <div class="card-body">
                    <p class="text-xs text-muted mb-3">Distribuição total de horas do período pelos dias da semana.</p>
                    <div class="heatmap-grid">
                        @foreach($heatmapData as $day)
                            @php
                                $intensityClass = '';
                                if ($day['total_hours'] > 0) {
                                    $intensityClass = $day['total_hours'] > ($totalHours / 7) * 1.2 ? 'intensity-very-high' : 'intensity-high';
                                }
                            @endphp
                            <div class="heatmap-day {{ $intensityClass }}">
                                <div class="heatmap-day__name">{{ $day['day'] }}</div>
                                <div class="heatmap-day__value">{{ number_format($day['total_hours'], 1) }}h</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ranking de Colaboradores -->
    <div class="card card-outline card-secondary shadow-sm mt-3">
        <div class="card-header">
            <h3 class="card-title text-sm text-bold"><i class="fas fa-trophy mr-1"></i> Ranking de Produtividade dos Colaboradores</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Colaborador</th>
                            <th class="text-center">Total de Horas</th>
                            <th class="text-center">Média Diária</th>
                            <th class="text-center">Projetos Ativos</th>
                            <th class="text-center">Dias Ativos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($memberRanking as $index => $row)
                            <tr>
                                <td class="pl-4 font-weight-bold">
                                    <span class="badge badge-light mr-2">#{{ $index + 1 }}</span>
                                    {{ $row['nome'] }}
                                </td>
                                <td class="text-center font-weight-bold text-primary">{{ number_format($row['total_hours'], 1) }}h</td>
                                <td class="text-center">{{ number_format($row['avg_daily'], 1) }}h/dia</td>
                                <td class="text-center"><span class="badge badge-info">{{ $row['projects_count'] }}</span></td>
                                <td class="text-center"><span class="badge badge-secondary">{{ $row['active_days'] }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Sem dados disponíveis para o período e filtros aplicados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('dailyHoursChart').getContext('2d');
    const labels = {!! json_encode($dailyLabels) !!};
    const data = {!! json_encode($dailyHours) !!};

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Horas Trabalhadas',
                data: data,
                borderColor: '#0891b2',
                backgroundColor: 'rgba(8,145,178,0.08)',
                borderWidth: 2,
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointBackgroundColor: '#0891b2',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { cornerRadius: 8, padding: 10 }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 20 } },
                y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 } }, beginAtZero: true }
            }
        }
    });
});
</script>
@stop
