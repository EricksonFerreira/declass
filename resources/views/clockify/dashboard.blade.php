@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    .dash-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .dash-kpi {
        background: #fff; border-radius: 14px; padding: 20px 18px;
        box-shadow: 0 1px 4px rgba(0,0,0,.06); border: 1px solid #eef1f6;
        position: relative; overflow: hidden; transition: transform .2s, box-shadow .2s;
    }
    .dash-kpi:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.08); }
    .dash-kpi::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background: var(--accent, #0891b2); }
    .dash-kpi__label { font-family:'Plus Jakarta Sans',sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#64748b; margin:0 0 6px; }
    .dash-kpi__value { font-family:'Plus Jakarta Sans',sans-serif; font-size:26px; font-weight:800; color:#0f172a; line-height:1.1; margin:0; letter-spacing:-.02em; }
    .dash-kpi__sub { font-size:12px; color:#94a3b8; margin:4px 0 0; }
    .dash-kpi__icon { position:absolute; top:16px; right:16px; width:40px; height:40px; border-radius:10px; background:var(--accent-soft, #e0f5fa); display:flex; align-items:center; justify-content:center; font-size:16px; color:var(--accent, #0891b2); }
    .c-teal { --accent:#0891b2; --accent-soft:#e0f5fa; }
    .c-emerald { --accent:#059669; --accent-soft:#d1fae5; }
    .c-amber { --accent:#d97706; --accent-soft:#fef3c7; }
    .c-indigo { --accent:#4f46e5; --accent-soft:#e0e7ff; }

    .dash-nav-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .dash-nav-card {
        display: flex; align-items: center; gap: 16px; padding: 20px;
        background: linear-gradient(135deg, #0f172a, #1e293b 60%, var(--accent, #0891b2));
        color: #fff; border-radius: 16px; text-decoration: none !important;
        box-shadow: 0 8px 24px rgba(15,23,42,.14); transition: transform .18s, box-shadow .18s;
        position: relative; overflow: hidden;
    }
    .dash-nav-card::before { content:''; position:absolute; inset:0; background:radial-gradient(circle at top right, rgba(255,255,255,.18), transparent 40%); pointer-events:none; }
    .dash-nav-card:hover { transform:translateY(-3px); box-shadow:0 12px 32px rgba(15,23,42,.2); color:#fff; }
    .dash-nav-card__icon { width:48px; height:48px; border-radius:14px; background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.2); display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
    .dash-nav-card__text h3 { font-family:'Plus Jakarta Sans',sans-serif; font-size:16px; font-weight:700; margin:0 0 2px; }
    .dash-nav-card__text p { margin:0; font-size:12px; color:rgba(255,255,255,.75); }
    .dash-nav-card__arrow { margin-left:auto; font-size:16px; color:rgba(255,255,255,.7); }

    .dash-section-title { font-family:'Plus Jakarta Sans',sans-serif; font-size:11px; font-weight:700; letter-spacing:.12em; text-transform:uppercase; color:#94a3b8; margin:28px 0 14px; display:flex; align-items:center; gap:10px; }
    .dash-section-title::after { content:''; flex:1; height:1px; background:#e2e5ef; }

    .dash-sparkline-wrap { background:#fff; border-radius:14px; padding:20px; box-shadow:0 1px 4px rgba(0,0,0,.06); border:1px solid #eef1f6; margin-bottom:24px; }
    .dash-sparkline-title { font-family:'Plus Jakarta Sans',sans-serif; font-size:13px; font-weight:700; color:#1a1d2e; margin:0 0 12px; }
</style>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('home') }}" method="GET" class="form-inline">
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
                <a href="{{ route('home') }}" class="btn btn-sm btn-default mb-2 ml-2">Limpar</a>
            </form>
        </div>
    </div>

    <!-- KPIs Principais -->
    <div class="dash-grid">
        <div class="dash-kpi c-teal">
            <div class="dash-kpi__icon"><i class="fas fa-stopwatch"></i></div>
            <p class="dash-kpi__label">Horas Hoje</p>
            <p class="dash-kpi__value">{{ number_format($hoursToday, 1) }}h</p>
            <p class="dash-kpi__sub">Tempo rastreado</p>
        </div>
        <div class="dash-kpi c-emerald">
            <div class="dash-kpi__icon"><i class="fas fa-calendar-day"></i></div>
            <p class="dash-kpi__label">Esta Semana</p>
            <p class="dash-kpi__value">{{ number_format($hoursThisWeek, 1) }}h</p>
            <p class="dash-kpi__sub">Semana atual</p>
        </div>
        <div class="dash-kpi c-indigo">
            <div class="dash-kpi__icon"><i class="fas fa-chart-line"></i></div>
            <p class="dash-kpi__label">Este Mês</p>
            <p class="dash-kpi__value">{{ number_format($hoursThisMonth, 1) }}h</p>
            <p class="dash-kpi__sub">Mês atual</p>
        </div>
        <div class="dash-kpi c-amber">
            <div class="dash-kpi__icon"><i class="fas fa-hourglass-half"></i></div>
            <p class="dash-kpi__label">Período</p>
            <p class="dash-kpi__value">{{ number_format($hoursInPeriod, 1) }}h</p>
            <p class="dash-kpi__sub">Total selecionado</p>
        </div>
    </div>

    <!-- Navegação Rápida para Sub-telas -->
    <p class="dash-section-title">Análises Detalhadas</p>
    <div class="dash-nav-grid">
        <a href="{{ route('home.produtividade', request()->query()) }}" class="dash-nav-card" style="--accent:#0891b2;">
            <div class="dash-nav-card__icon"><i class="fas fa-chart-line"></i></div>
            <div class="dash-nav-card__text">
                <h3>Produtividade</h3>
                <p>Ranking, horas por dia, heatmap semanal</p>
            </div>
            <span class="dash-nav-card__arrow"><i class="fas fa-chevron-right"></i></span>
        </a>
        <a href="{{ route('home.equipe', request()->query()) }}" class="dash-nav-card" style="--accent:#059669;">
            <div class="dash-nav-card__icon"><i class="fas fa-users"></i></div>
            <div class="dash-nav-card__text">
                <h3>Equipe</h3>
                <p>Quem está rastreando, status, alertas</p>
            </div>
            <span class="dash-nav-card__arrow"><i class="fas fa-chevron-right"></i></span>
        </a>
        <a href="{{ route('home.projetos', request()->query()) }}" class="dash-nav-card" style="--accent:#7c3aed;">
            <div class="dash-nav-card__icon"><i class="fas fa-project-diagram"></i></div>
            <div class="dash-nav-card__text">
                <h3>Projetos</h3>
                <p>Saúde, horas por projeto, matrix</p>
            </div>
            <span class="dash-nav-card__arrow"><i class="fas fa-chevron-right"></i></span>
        </a>
    </div>

    <div class="row">
        <!-- Rastreadores Ativos -->
        <div class="col-md-6">
            <div class="card card-outline card-danger shadow-sm">
                <div class="card-header">
                    <h3 class="card-title text-sm text-bold"><i class="fas fa-spinner fa-pulse text-danger mr-1"></i> Rastreadores Ativos</h3>
                    <div class="card-tools">
                        <span class="badge badge-danger">{{ $activeTrackers->count() }}</span>
                    </div>
                </div>
                <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                    <ul class="nav nav-pills flex-column">
                        @forelse ($activeTrackers as $active)
                            <li class="nav-item border-bottom p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-bold text-dark">{{ $active->user->nome }}</span>
                                        <span class="text-muted text-sm">em</span>
                                        <span class="text-bold" style="color: {{ $active->projeto->cor ?? '#6c757d' }}">{{ $active->projeto->nome ?? 'Sem Projeto' }}</span>
                                        <br>
                                        <span class="text-muted text-xs"><i class="far fa-edit"></i> {{ $active->descricao ?: 'Sem descrição' }}</span>
                                    </div>
                                    <span class="badge badge-success px-2 py-1 text-sm font-weight-bold">
                                        <i class="far fa-clock mr-1"></i> {{ $active->tempoFormato }}
                                    </span>
                                </div>
                            </li>
                        @empty
                            <li class="p-4 text-center text-muted text-sm">Ninguém rastreando tempo no momento.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <!-- Mini Gráfico de Horas -->
        <div class="col-md-6">
            <div class="dash-sparkline-wrap">
                <p class="dash-sparkline-title"><i class="fas fa-chart-bar mr-2 text-primary"></i> Horas por dia no período</p>
                <canvas id="sparklineChart" height="180"></canvas>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('sparklineChart').getContext('2d');
    const labels = {!! json_encode($dateLabels) !!};
    const rawStats = {!! json_encode($memberStats) !!};

    // Aggregate all members into a single daily total
    const dailyTotals = new Array(labels.length).fill(0);
    Object.values(rawStats).forEach(member => {
        member.dailyOrdered.forEach((val, i) => { dailyTotals[i] += val; });
    });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels.map(d => {
                const parts = d.split('-');
                return parts[2] + '/' + parts[1];
            }),
            datasets: [{
                label: 'Horas',
                data: dailyTotals,
                backgroundColor: 'rgba(8,145,178,0.7)',
                borderRadius: 6,
                borderSkipped: false,
                maxBarThickness: 20,
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
                x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 15 } },
                y: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 } }, beginAtZero: true }
            }
        }
    });
});
</script>
@stop
