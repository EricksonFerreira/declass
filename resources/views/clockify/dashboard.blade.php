@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />
<style>
    /* =====================================================
       PADRÃO VISUAL DO SISTEMA (mesmo da tela de projeto)
    ===================================================== */
    body { background-color: #f4f6f9; }

    .dash-card {
        border-radius: 12px;
        border: none;
        box-shadow: 0 4px 18px rgba(0,0,0,0.05);
    }
    .dash-card > .card-header:first-child { border-radius: 12px 12px 0 0; }
    .dash-card > .card-header { border-bottom: 1px solid #eef1f6; }

    .dash-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .dash-kpi {
        background: #fff; border-radius: 12px; padding: 18px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: none;
        position: relative; overflow: hidden; transition: transform .2s, box-shadow .2s;
    }
    .dash-kpi:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.08); }
    .dash-kpi__label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#64748b; margin:0 0 6px; }
    .dash-kpi__value { font-size:26px; font-weight:800; color:#0f172a; line-height:1.1; margin:0; letter-spacing:-.02em; }
    .dash-kpi__sub { font-size:12px; color:#94a3b8; margin:4px 0 0; }
    .dash-kpi__icon { position:absolute; top:16px; right:16px; width:40px; height:40px; border-radius:10px; background:var(--accent-soft, #e0f5fa); display:flex; align-items:center; justify-content:center; font-size:16px; color:var(--accent, #0891b2); }
    .c-teal { --accent:#0891b2; --accent-soft:#e0f5fa; }
    .c-emerald { --accent:#059669; --accent-soft:#d1fae5; }
    .c-amber { --accent:#d97706; --accent-soft:#fef3c7; }
    .c-indigo { --accent:#4f46e5; --accent-soft:#e0e7ff; }
    .c-violet { --accent:#7c3aed; --accent-soft:#ede9fe; }


    .tracker-row { transition: background .15s; }
    .tracker-row:hover { background-color: #f8fafc; }

    /* Select2 compacto para alinhar com form-control-sm */
    .select2-container--bootstrap4 .select2-selection--multiple { min-height: 31px; }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered { padding: 0 8px; }
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice { margin-top: 3px; font-size: 12px; }
    .select2-container { max-width: 100%; }
</style>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card dash-card mb-4">
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
                        <select name="user_ids[]" id="user_ids" class="form-control form-control-sm select2" multiple="multiple" style="width: 300px; max-width: 100%;">
                            @foreach ($usuarios as $user)
                                <option value="{{ $user->id }}" @selected(in_array($user->id, (array)$selectedUserIds))>{{ $user->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <button type="submit" class="btn btn-sm btn-primary mb-2 font-weight-bold shadow-sm">
                    <i class="fas fa-filter mr-1"></i> Filtrar
                </button>
                <a href="{{ route('home') }}" class="btn btn-sm btn-default mb-2 ml-2 font-weight-bold shadow-sm">Limpar</a>
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

    <div class="row">
        <!-- Rastreadores Ativos -->
        <div class="col-md-6">
            <div class="card dash-card">
                <div class="card-header bg-white py-3 border-bottom">
                    <h3 class="card-title text-bold text-dark mb-0">
                        <i class="fas fa-spinner fa-pulse text-danger mr-2"></i> Rastreadores Ativos
                        <span class="badge badge-danger ml-1">{{ $activeTrackers->count() }}</span>
                    </h3>
                </div>
                <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                    <ul class="nav nav-pills flex-column">
                        @forelse ($activeTrackers as $active)
                            <li class="nav-item border-bottom p-3 tracker-row">
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
            <div class="card dash-card">
                <div class="card-header bg-white py-3 border-bottom">
                    <h3 class="card-title text-bold text-dark mb-0">
                        <i class="fas fa-chart-bar text-primary mr-2"></i> Horas por dia no período
                    </h3>
                </div>
                <div class="card-body">
                    <div style="position: relative; height: 180px;">
                        <canvas id="sparklineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    if ($.fn.select2) {
        $('#user_ids').select2({
            theme: 'bootstrap4',
            width: 'resolve',
            placeholder: 'Todos os colaboradores',
            allowClear: true,
            closeOnSelect: false
        });
    }
});
</script>
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
