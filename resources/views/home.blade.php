@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    {{-- <x-breadcrumb :breadcrumbs="$breadcrumb" /> --}}
@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body, .wrapper, .content-wrapper {
        background: #f0f2f7 !important;
    }

    .period-bar {
        background: #fff;
        border: 1px solid #e2e5ef;
        border-radius: 16px;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 16px;
    }

    .period-bar label {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        font-weight: 600;
        color: #4a5068;
        margin: 0;
        white-space: nowrap;
    }

    .period-bar input[type=date] {
        border: 1px solid #e2e5ef;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 12px;
        color: #1a1d2e;
        outline: none;
    }

    .period-bar input[type=date]:focus {
        border-color: #0891b2;
    }

    .btn-filtrar {
        background: #0891b2;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 7px 18px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
    }

    .btn-filtrar:hover {
        background: #0e7490;
    }

    .btn-filtrar:disabled {
        background: #94a3b8;
        cursor: not-allowed;
    }

    .btn-secondary {
        background: #64748b;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: 7px 14px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.15s;
    }

    .btn-secondary:hover { background: #475569; }

    .period-sep {
        color: #8b92a9;
        font-size: 12px;
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .kpi-card {
        background: #fff;
        border: 1px solid #e2e5ef;
        border-radius: 16px;
        padding: 16px 16px 14px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        position: relative;
        overflow: hidden;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--accent);
    }

    .kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }

    .kpi-label {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #64748b;
        margin: 0;
    }

    .kpi-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        letter-spacing: -0.02em;
        margin: 0;
        line-height: 1.1;
    }

    .kpi-sub {
        font-size: 12px;
        color: #8b92a9;
        margin: 0;
    }

    .kpi-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: var(--accent-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: var(--accent);
        flex-shrink: 0;
    }

    .home-details {
        display: none;
    }

    .home-details.is-open {
        display: block;
    }

    .btn-more-cards {
        background: transparent;
        border: 1px dashed #cbd5e1;
        color: #475569;
        border-radius: 999px;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }

    .btn-more-cards:hover {
        background: #f8fafc;
    }

    .charts-grid {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }

    .chart-card {
        background: #fff;
        border: 1px solid #e2e5ef;
        border-radius: 16px;
        padding: 18px 20px 16px;
        position: relative;
        overflow: hidden;
    }

    .chart-card.span-12 { grid-column: span 12; }
    .chart-card.span-8 { grid-column: span 8; }
    .chart-card.span-6 { grid-column: span 6; }
    .chart-card.span-4 { grid-column: span 4; }

    .card-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 14px;
    }

    .card-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 13px;
        font-weight: 700;
        color: #1a1d2e;
        margin: 0;
    }

    .card-sub {
        font-size: 11px;
        color: #8b92a9;
        margin: 3px 0 0;
    }

    .metric-big {
        font-size: 26px;
        font-weight: 700;
        color: #1a1d2e;
        line-height: 1;
        margin: 0;
    }

    .metric-delta {
        font-size: 11px;
        margin-top: 4px;
    }

    .delta-up {
        color: #059669;
    }

    .legend-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 10px;
    }

    .leg-item {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 11px;
        color: #64748b;
    }

    .leg-dot {
        width: 8px;
        height: 8px;
        border-radius: 2px;
        flex-shrink: 0;
    }

    .kpi-inline {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 10px;
    }

    .kpi-pill {
        border: 1px solid #dbe3ef;
        background: #f8fafc;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 11px;
        color: #475569;
        font-weight: 600;
    }

    .kpi-pill strong {
        color: #0f172a;
    }

    .chart-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, 0.88);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 16px;
        z-index: 10;
    }

    .chart-overlay.hidden {
        display: none;
    }

    .spinner {
        width: 32px;
        height: 32px;
        border: 3px solid #e2e5ef;
        border-top-color: #0891b2;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .gauge-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .gauge-row {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .gauge-label {
        font-size: 11px;
        color: #64748b;
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }

    .gauge-label span:last-child {
        font-weight: 700;
        color: #1a1d2e;
    }

    .gauge-bar-bg {
        height: 6px;
        border-radius: 999px;
        background: #f0f2f7;
        overflow: hidden;
    }

    .gauge-bar-fill {
        height: 100%;
        border-radius: 999px;
    }

    .empty-gauge {
        padding: 12px;
        border: 1px dashed #d5deeb;
        border-radius: 10px;
        font-size: 12px;
        color: #64748b;
        background: #f8fafc;
    }

    .chart-canvas {
        position: relative;
        width: 100%;
    }

    .h-220 { height: 220px; }
    .h-210 { height: 210px; }
    .h-200 { height: 200px; }
    .h-190 { height: 190px; }

    .section-label {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #8b92a9;
        margin: 30px 0 14px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e2e5ef;
    }

    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        gap: 14px;
    }

    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 20px 18px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05), 0 4px 16px rgba(0, 0, 0, 0.04);
        text-decoration: none !important;
        color: inherit !important;
        border: 1px solid rgba(255, 255, 255, 0.8);
        position: relative;
        overflow: hidden;
        opacity: 0;
        animation: fadeUp 0.35s ease forwards;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        border-radius: 16px 16px 0 0;
        background: var(--accent);
    }

    .stat-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: var(--accent-soft);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        color: var(--accent);
        flex-shrink: 0;
    }

    .stat-name {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 12.5px;
        font-weight: 600;
        color: #4a5068;
        line-height: 1.3;
        margin: 0;
    }

    .stat-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 26px;
        font-weight: 700;
        color: #1a1d2e;
        line-height: 1.1;
        letter-spacing: -0.02em;
        margin: 0;
    }

    .c-teal { --accent: #0891b2; --accent-soft: #e0f5fa; }
    .c-amber { --accent: #d97706; --accent-soft: #fef3c7; }
    .c-emerald { --accent: #059669; --accent-soft: #d1fae5; }
    .c-rose { --accent: #e11d48; --accent-soft: #ffe4e6; }
    .c-indigo { --accent: #4f46e5; --accent-soft: #e0e7ff; }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .stat-grid .stat-card:nth-child(1) { animation-delay: 0.04s; }
    .stat-grid .stat-card:nth-child(2) { animation-delay: 0.08s; }
    .stat-grid .stat-card:nth-child(3) { animation-delay: 0.12s; }
    .stat-grid .stat-card:nth-child(4) { animation-delay: 0.16s; }
    .stat-grid .stat-card:nth-child(5) { animation-delay: 0.20s; }
    .stat-grid .stat-card:nth-child(6) { animation-delay: 0.24s; }
    .stat-grid .stat-card:nth-child(7) { animation-delay: 0.28s; }
    .stat-grid .stat-card:nth-child(8) { animation-delay: 0.32s; }
    .stat-grid .stat-card:nth-child(9) { animation-delay: 0.36s; }

    @media (max-width: 1200px) {
        .chart-card.span-8,
        .chart-card.span-6,
        .chart-card.span-4 {
            grid-column: span 6;
        }
    }

    @media (max-width: 767px) {
        .period-bar {
            flex-direction: column;
            align-items: flex-start;
        }

        .chart-card.span-12,
        .chart-card.span-8,
        .chart-card.span-6,
        .chart-card.span-4 {
            grid-column: span 12;
        }

        .card-head {
            flex-direction: column;
        }
    }
</style>
@stop

@section('content')
@if ($hasVisibleCharts)
<div class="period-bar">
    <label for="data_inicio">Periodo:</label>
    <input type="date" id="data_inicio" name="data_inicio">
    <span class="period-sep">ate</span>
    <input type="date" id="data_fim" name="data_fim">
    <button class="btn-filtrar" id="btnFiltrar">Aplicar</button>
    <button class="btn-filtrar" id="btnHoje" style="background:#059669;">Hoje</button>
    <button class="btn-filtrar" id="btn12m" style="background:#7c3aed;">Ultimos 12 meses</button>
    <button class="btn-secondary" id="btnToggleDetalhes" type="button">Detalhes</button>
</div>

<div class="kpi-grid">
    <div class="kpi-card c-teal">
        <div class="kpi-top">
            <div>
                <p class="kpi-label">Reservas</p>
                <p class="kpi-value" id="kpiTotalReservas">--</p>
            </div>
            <div class="kpi-icon"><i class="fas fa-calendar-check"></i></div>
        </div>
        <p class="kpi-sub" id="kpiPeriodo">--</p>
    </div>

    <div class="kpi-card c-emerald">
        <div class="kpi-top">
            <div>
                <p class="kpi-label">Receita</p>
                <p class="kpi-value" id="kpiTotalFinanceiro">--</p>
            </div>
            <div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div>
        </div>
        <p class="kpi-sub" id="kpiTicketMedio">--</p>
    </div>

    <div class="kpi-card c-indigo">
        <div class="kpi-top">
            <div>
                <p class="kpi-label">Ocupacao</p>
                <p class="kpi-value" id="kpiOcupacao">--</p>
            </div>
            <div class="kpi-icon"><i class="fas fa-chart-pie"></i></div>
        </div>
        <p class="kpi-sub">Frota no periodo</p>
    </div>

    <div class="kpi-card c-amber">
        <div class="kpi-top">
            <div>
                <p class="kpi-label">Movimentacoes</p>
                <p class="kpi-value" id="kpiMovimentacoes">--</p>
            </div>
            <div class="kpi-icon"><i class="fas fa-exchange-alt"></i></div>
        </div>
        <p class="kpi-sub">Total no periodo</p>
    </div>

    <div class="kpi-card c-rose">
        <div class="kpi-top">
            <div>
                <p class="kpi-label">Veiculos</p>
                <p class="kpi-value" id="kpiVeiculos">{{ $totalVeiculos }}</p>
            </div>
            <div class="kpi-icon"><i class="fas fa-car"></i></div>
        </div>
        <p class="kpi-sub">Cadastrados</p>
    </div>
</div>

<div class="home-details" id="homeDetails">
<div class="charts-grid">
    @if ($homeSections['reservas'])
    <div class="chart-card span-12">
        <div class="chart-overlay" id="overlayReservas"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Reservas ao longo do tempo</p>
                <p class="card-sub" id="subReservas">--</p>
            </div>
            <div class="text-right">
                <p class="metric-big" id="totalReservas">--</p>
                <div class="metric-delta delta-up">reservas no periodo</div>
            </div>
        </div>
        <div class="legend-row">
            <span class="leg-item"><span class="leg-dot" style="background:#0891b2;"></span>Confirmadas</span>
            <span class="leg-item"><span class="leg-dot" style="background:#bae6fd;"></span>Cancelamentos</span>
            <span class="leg-item"><span class="leg-dot" style="background:#06b6d4;"></span>Tendencia</span>
        </div>
        <div class="chart-canvas h-220">
            <canvas id="chartReservas"></canvas>
        </div>
    </div>
    @endif

    @if ($homeSections['movimentacoes'])
    <div class="chart-card span-4">
        <div class="chart-overlay" id="overlayMovStatus"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Movimentacoes por status</p>
                <p class="card-sub">Total: <span id="totalMovimentacoes">--</span></p>
            </div>
        </div>
        <div class="legend-row" id="legMovStatus"></div>
        <div class="chart-canvas h-210">
            <canvas id="chartMovStatus"></canvas>
        </div>
    </div>
    @endif

    @if ($homeSections['frota'])
    <div class="chart-card span-8">
        <div class="chart-overlay" id="overlayFrota"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Veiculos por grupo de frota</p>
                <p class="card-sub">{{ $totalVeiculos }} veiculos cadastrados</p>
            </div>
        </div>
        <div class="chart-canvas h-210">
            <canvas id="chartFrota"></canvas>
        </div>
    </div>
    @endif

    @if ($homeSections['financeiro'])
    <div class="chart-card span-8">
        <div class="chart-overlay" id="overlayFinanceiroLinha"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Financeiro: receita e transacoes</p>
                <p class="card-sub" id="subFinanceiro">--</p>
            </div>
            <div class="text-right">
                <p class="metric-big" id="totalFinanceiro">--</p>
                <div class="metric-delta delta-up" id="ticketMedioFinanceiro">ticket medio --</div>
            </div>
        </div>
        <div class="legend-row">
            <span class="leg-item"><span class="leg-dot" style="background:#0ea5e9;"></span>Receita</span>
            <span class="leg-item"><span class="leg-dot" style="background:#6366f1;"></span>Transacoes</span>
        </div>
        <div class="chart-canvas h-220">
            <canvas id="chartFinanceiroLinha"></canvas>
        </div>
    </div>
    @endif

    @if ($homeSections['ocupacao'])
    <div class="chart-card span-4">
        <div class="chart-overlay" id="overlayOcupacao"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Ocupacao da frota</p>
                <p class="card-sub">por grupo no periodo</p>
            </div>
            <div class="text-right">
                <p class="metric-big" id="ocupacaoGeral">--</p>
                <div class="metric-delta delta-up">ocupacao geral</div>
            </div>
        </div>
        <div class="gauge-list" id="gaugeList"></div>
    </div>
    @endif

    @if ($homeSections['financeiro'])
    <div class="chart-card span-8">
        <div class="chart-overlay" id="overlayFinanceiroGateway"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Receita por gateway</p>
                <p class="card-sub">Transacoes no periodo: <span id="qtdTransacoesFinanceiro">--</span></p>
            </div>
        </div>
        <div class="legend-row" id="legFinGateway"></div>
        <div class="chart-canvas h-200">
            <canvas id="chartFinanceiroGateway"></canvas>
        </div>
    </div>

    <div class="chart-card span-4">
        <div class="chart-overlay" id="overlayFinanceiroStatus"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Performance por status</p>
                <p class="card-sub">Taxa de aprovacao: <span id="taxaAprovacaoFinanceiro">--</span></p>
            </div>
        </div>
        <div class="legend-row" id="legFinStatus"></div>
        <div class="chart-canvas h-200">
            <canvas id="chartFinanceiroStatus"></canvas>
        </div>
    </div>
    @endif
</div>
</div>
@endif

@php
    $colorMap = [
        'bg-info' => 'c-teal',
        'bg-warning' => 'c-amber',
        'bg-success' => 'c-emerald',
        'bg-danger' => 'c-rose',
    ];
@endphp

@if ($hasVisibleItems)
    @php
        $allCards = [];
        foreach ($items as $grupoItems) {
            $colorClass = $colorMap[$grupoItems['backgroundIcone']] ?? 'c-teal';
            foreach (($grupoItems['items'] ?? []) as $card) {
                $card['_colorClass'] = $colorClass;
                $allCards[] = $card;
            }
        }
        $quickCards = array_slice($allCards, 0, 8);
        $moreCards = array_slice($allCards, 8);
    @endphp

    <p class="section-label">Acesso rapido</p>
    <div class="stat-grid">
        @foreach ($quickCards as $i)
            <a href="{{ route($i['route']) }}" class="stat-card {{ $i['_colorClass'] ?? 'c-teal' }}">
                <div class="stat-icon"><i class="{{ $i['icone'] }}"></i></div>
                <div>
                    <p class="stat-name">{{ $i['nome'] }}</p>
                    <p class="stat-value">{{ $i['prefix'] }}{{ $i['valor'] }}{{ $i['suffix'] }}</p>
                </div>
            </a>
        @endforeach
    </div>

    @if (count($moreCards) > 0)
        <div class="mt-3">
            <button type="button" class="btn-more-cards" id="btnMoreCards">Mostrar mais atalhos</button>
        </div>
        <div class="mt-3 d-none" id="moreCards">
            <div class="stat-grid">
                @foreach ($moreCards as $i)
                    <a href="{{ route($i['route']) }}" class="stat-card {{ $i['_colorClass'] ?? 'c-teal' }}">
                        <div class="stat-icon"><i class="{{ $i['icone'] }}"></i></div>
                        <div>
                            <p class="stat-name">{{ $i['nome'] }}</p>
                            <p class="stat-value">{{ $i['prefix'] }}{{ $i['valor'] }}{{ $i['suffix'] }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif
@endif
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
const CHART_DATA_URL = '{{ route('home.chart-data') }}';
const HOME_SECTIONS = @json($homeSections);
const textColor = 'rgba(0,0,0,0.45)';
const gridColor = 'rgba(0,0,0,0.06)';
const overlayIds = [
    'overlayReservas',
    'overlayMovStatus',
    'overlayFrota',
    'overlayOcupacao',
    'overlayFinanceiroLinha',
    'overlayFinanceiroStatus',
    'overlayFinanceiroGateway',
];

const baseOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: { cornerRadius: 8, padding: 10 }
    }
};

let chartReservas = null;
let chartMovStatus = null;
let chartFrota = null;
let chartFinanceiroLinha = null;
let chartFinanceiroStatus = null;
let chartFinanceiroGateway = null;
let simboloMoeda = 'R$';
let lastDashboardData = null;
let detailsOpen = false;

const movColors = ['#0891b2', '#059669', '#d97706', '#e11d48', '#7c3aed', '#0ea5e9', '#f59e0b', '#10b981'];
const finStatusColors = ['#16a34a', '#d97706', '#dc2626'];
const finGatewayColors = ['#0ea5e9', '#6366f1', '#14b8a6', '#f59e0b', '#ec4899', '#84cc16'];

function formatInputDate(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function defaultDates() {
    const hoje = new Date();
    const inicio = new Date(hoje.getFullYear(), hoje.getMonth() - 11, 1);
    return {
        inicio: formatInputDate(inicio),
        fim: formatInputDate(hoje),
    };
}

function formatMoney(value) {
    const n = Number(value || 0);
    return `${simboloMoeda} ${n.toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    })}`;
}

function formatPercent(value) {
    return `${Number(value || 0).toLocaleString('pt-BR', {
        minimumFractionDigits: 1,
        maximumFractionDigits: 1
    })}%`;
}

function formatShortDate(isoDate) {
    if (!isoDate || isoDate.indexOf('-') === -1) {
        return '--';
    }

    const parts = isoDate.split('-');
    const y = Number(parts[0]);
    const m = Number(parts[1]);
    const d = Number(parts[2]);
    const date = new Date(y, m - 1, d);

    return date.toLocaleDateString('pt-BR', { day: '2-digit', month: 'short' });
}

function setDateInputs(inicio, fim) {
    const dataInicio = document.getElementById('data_inicio');
    const dataFim = document.getElementById('data_fim');

    if (!dataInicio || !dataFim) {
        return;
    }

    dataInicio.value = inicio;
    dataFim.value = fim;
}

function setDetailsOpen(open) {
    detailsOpen = !!open;
    const details = document.getElementById('homeDetails');
    const btn = document.getElementById('btnToggleDetalhes');
    if (details) details.classList.toggle('is-open', detailsOpen);
    if (btn) btn.textContent = detailsOpen ? 'Ocultar detalhes' : 'Detalhes';
    localStorage.setItem('homeDetailsOpen', detailsOpen ? '1' : '0');

    if (detailsOpen && lastDashboardData) {
        const dataInicio = document.getElementById('data_inicio')?.value;
        const dataFim = document.getElementById('data_fim')?.value;
        if (dataInicio && dataFim) {
            showOverlays();
            try {
                if (HOME_SECTIONS.reservas) renderReservas(lastDashboardData.dadosReservas, lastDashboardData.totalReservas, dataInicio, dataFim);
                if (HOME_SECTIONS.movimentacoes) renderMovStatus(lastDashboardData.movPorStatus, lastDashboardData.totalMovimentacoes);
                if (HOME_SECTIONS.frota) renderFrota(lastDashboardData.veiculosPorGrupo);
                if (HOME_SECTIONS.ocupacao) renderOcupacao(lastDashboardData.ocupacaoFrota, lastDashboardData.ocupacaoGeral);
                if (HOME_SECTIONS.financeiro) {
                    renderFinanceiroLinha(lastDashboardData.financeiroEvolucao, lastDashboardData.financeiroResumo, dataInicio, dataFim);
                    renderFinanceiroStatus(lastDashboardData.financeiroStatus, lastDashboardData.financeiroResumo);
                    renderFinanceiroGateway(lastDashboardData.financeiroGateway, lastDashboardData.financeiroResumo);
                }
            } finally {
                hideOverlays();
            }
        }
    }
}

function initHomeToggles() {
    const btnDetails = document.getElementById('btnToggleDetalhes');
    if (btnDetails) {
        btnDetails.addEventListener('click', () => setDetailsOpen(!detailsOpen));
    }

    const saved = localStorage.getItem('homeDetailsOpen') === '1';
    setDetailsOpen(saved);

    const btnMore = document.getElementById('btnMoreCards');
    const more = document.getElementById('moreCards');
    if (btnMore && more) {
        btnMore.addEventListener('click', () => {
            const isHidden = more.classList.contains('d-none');
            more.classList.toggle('d-none', !isHidden);
            btnMore.textContent = isHidden ? 'Mostrar menos atalhos' : 'Mostrar mais atalhos';
        });
    }
}

function showOverlays() {
    if (!detailsOpen) return;
    overlayIds.forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
            el.classList.remove('hidden');
        }
    });
}

function hideOverlays() {
    if (!detailsOpen) return;
    overlayIds.forEach((id) => {
        const el = document.getElementById(id);
        if (el) {
            el.classList.add('hidden');
        }
    });
}

function fetchChartData() {
    const dataInicio = document.getElementById('data_inicio');
    const dataFim = document.getElementById('data_fim');
    const btn = document.getElementById('btnFiltrar');

    if (!dataInicio || !dataFim || !btn) {
        return;
    }

    const inicio = dataInicio.value;
    const fim = dataFim.value;

    if (!inicio || !fim || inicio > fim) {
        alert('Selecione um periodo valido.');
        return;
    }

    btn.disabled = true;
    showOverlays();

    const url = `${CHART_DATA_URL}?data_inicio=${encodeURIComponent(inicio)}&data_fim=${encodeURIComponent(fim)}`;

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then((response) => {
            if (!response.ok) {
                throw new Error('Erro ao buscar dados do dashboard.');
            }
            return response.json();
        })
        .then((data) => {
            lastDashboardData = data;
            simboloMoeda = data.simboloMoeda || 'R$';

            renderKpis(data, inicio, fim);

            if (!detailsOpen) {
                return;
            }
            if (HOME_SECTIONS.reservas) {
                renderReservas(data.dadosReservas, data.totalReservas, inicio, fim);
            }
            if (HOME_SECTIONS.movimentacoes) {
                renderMovStatus(data.movPorStatus, data.totalMovimentacoes);
            }
            if (HOME_SECTIONS.frota) {
                renderFrota(data.veiculosPorGrupo);
            }
            if (HOME_SECTIONS.ocupacao) {
                renderOcupacao(data.ocupacaoFrota, data.ocupacaoGeral);
            }
            if (HOME_SECTIONS.financeiro) {
                renderFinanceiroLinha(data.financeiroEvolucao, data.financeiroResumo, inicio, fim);
                renderFinanceiroStatus(data.financeiroStatus, data.financeiroResumo);
                renderFinanceiroGateway(data.financeiroGateway, data.financeiroResumo);
            }
        })
        .catch(() => alert('Erro ao carregar dados. Tente novamente.'))
        .finally(() => {
            btn.disabled = false;
            hideOverlays();
        });
}

function renderKpis(data, inicio, fim) {
    const totalReservas = Number(data?.totalReservas ?? 0);
    const totalMov = Number(data?.totalMovimentacoes ?? 0);
    const ocupacao = Number(data?.ocupacaoGeral ?? 0);
    const totalFin = Number(data?.financeiroResumo?.total ?? 0);
    const ticket = Number(data?.financeiroResumo?.ticket_medio ?? 0);

    const elReservas = document.getElementById('kpiTotalReservas');
    const elMov = document.getElementById('kpiMovimentacoes');
    const elOcup = document.getElementById('kpiOcupacao');
    const elFin = document.getElementById('kpiTotalFinanceiro');
    const elTicket = document.getElementById('kpiTicketMedio');
    const elPeriodo = document.getElementById('kpiPeriodo');

    if (elReservas) elReservas.textContent = totalReservas.toLocaleString('pt-BR');
    if (elMov) elMov.textContent = totalMov.toLocaleString('pt-BR');
    if (elOcup) elOcup.textContent = formatPercent(ocupacao);
    if (elFin) elFin.textContent = formatMoney(totalFin);
    if (elTicket) elTicket.textContent = `Ticket medio: ${formatMoney(ticket)}`;
    if (elPeriodo) elPeriodo.textContent = `${formatShortDate(inicio)} - ${formatShortDate(fim)}`;
}

function renderReservas(dados, total, inicio, fim) {
    if (!document.getElementById('chartReservas')) {
        return;
    }

    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    const confirmadas = dados?.confirmadas?.length ? dados.confirmadas : [0];
    const cancelamentos = dados?.cancelamentos?.length ? dados.cancelamentos : [0];
    const tendencia = dados?.tendencia?.length ? dados.tendencia : [0];

    document.getElementById('totalReservas').textContent = Number(total || 0).toLocaleString('pt-BR');
    document.getElementById('subReservas').textContent = `${formatShortDate(inicio)} - ${formatShortDate(fim)}`;

    if (chartReservas) {
        chartReservas.destroy();
    }

    chartReservas = new Chart(document.getElementById('chartReservas'), {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Confirmadas',
                    data: confirmadas,
                    backgroundColor: '#0891b2',
                    borderRadius: 5,
                    order: 2
                },
                {
                    label: 'Cancelamentos',
                    data: cancelamentos,
                    backgroundColor: '#bae6fd',
                    borderRadius: 5,
                    order: 3
                },
                {
                    label: 'Tendencia',
                    data: tendencia,
                    type: 'line',
                    borderColor: '#06b6d4',
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointRadius: 0,
                    tension: 0.35,
                    order: 1
                }
            ]
        },
        options: {
            ...baseOpts,
            scales: {
                x: {
                    stacked: true,
                    ticks: { color: textColor, font: { size: 11 }, autoSkip: false },
                    grid: { display: false }
                },
                y: {
                    stacked: true,
                    ticks: { color: textColor, font: { size: 11 } },
                    grid: { color: gridColor },
                    border: { display: false }
                }
            }
        }
    });
}

function renderMovStatus(dados, total) {
    if (!document.getElementById('chartMovStatus')) {
        return;
    }

    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    const valores = dados?.data?.length ? dados.data : [0];
    const palette = labels.map((_, index) => movColors[index % movColors.length]);

    document.getElementById('totalMovimentacoes').textContent = Number(total || 0).toLocaleString('pt-BR');

    const leg = document.getElementById('legMovStatus');
    leg.innerHTML = '';
    labels.forEach((label, index) => {
        leg.innerHTML += `<span class="leg-item"><span class="leg-dot" style="background:${movColors[index % movColors.length]};"></span>${label} ${Number(valores[index] || 0).toLocaleString('pt-BR')}</span>`;
    });

    if (chartMovStatus) {
        chartMovStatus.destroy();
    }

    chartMovStatus = new Chart(document.getElementById('chartMovStatus'), {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: valores,
                backgroundColor: palette,
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: { ...baseOpts, cutout: '68%', layout: { padding: 4 } }
    });
}

function renderFrota(dados) {
    if (!document.getElementById('chartFrota')) {
        return;
    }

    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    const valores = dados?.data?.length ? dados.data : [0];
    const colors = ['#0891b2', '#059669', '#d97706', '#7c3aed', '#e11d48', '#0ea5e9'];
    const palette = labels.map((_, index) => colors[index % colors.length]);

    if (chartFrota) {
        chartFrota.destroy();
    }

    chartFrota = new Chart(document.getElementById('chartFrota'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: valores,
                backgroundColor: palette,
                borderRadius: 5,
                borderSkipped: false
            }]
        },
        options: {
            ...baseOpts,
            indexAxis: 'y',
            scales: {
                x: {
                    ticks: { color: textColor, font: { size: 11 }, stepSize: 1 },
                    grid: { color: gridColor },
                    border: { display: false }
                },
                y: {
                    ticks: { color: textColor, font: { size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });
}

function renderOcupacao(frota, geral) {
    if (!document.getElementById('ocupacaoGeral') || !document.getElementById('gaugeList')) {
        return;
    }

    document.getElementById('ocupacaoGeral').textContent = `${Number(geral || 0)}%`;

    const list = document.getElementById('gaugeList');
    list.innerHTML = '';

    if (!Array.isArray(frota) || frota.length === 0) {
        list.innerHTML = '<div class="empty-gauge">Sem dados de ocupacao para o periodo selecionado.</div>';
        return;
    }

    frota.forEach((item) => {
        list.innerHTML += `
            <div class="gauge-row">
                <div class="gauge-label"><span>${item.label}</span><span>${item.pct}%</span></div>
                <div class="gauge-bar-bg"><div class="gauge-bar-fill" style="width:${item.pct}%;background:${item.color};"></div></div>
            </div>
        `;
    });
}

function renderFinanceiroLinha(dados, resumo, inicio, fim) {
    if (!document.getElementById('chartFinanceiroLinha')) {
        return;
    }

    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    const valores = dados?.valores?.length ? dados.valores : [0];
    const quantidades = dados?.quantidades?.length ? dados.quantidades : [0];

    document.getElementById('totalFinanceiro').textContent = formatMoney(resumo?.total ?? 0);
    document.getElementById('ticketMedioFinanceiro').textContent = `ticket medio ${formatMoney(resumo?.ticket_medio ?? 0)}`;
    document.getElementById('subFinanceiro').textContent = `${formatShortDate(inicio)} - ${formatShortDate(fim)}`;

    if (chartFinanceiroLinha) {
        chartFinanceiroLinha.destroy();
    }

    chartFinanceiroLinha = new Chart(document.getElementById('chartFinanceiroLinha'), {
        data: {
            labels,
            datasets: [
                {
                    type: 'bar',
                    label: 'Receita',
                    data: valores,
                    yAxisID: 'y',
                    backgroundColor: '#0ea5e9',
                    borderRadius: 5,
                    borderSkipped: false
                },
                {
                    type: 'line',
                    label: 'Transacoes',
                    data: quantidades,
                    yAxisID: 'y1',
                    borderColor: '#6366f1',
                    backgroundColor: 'transparent',
                    pointBackgroundColor: '#6366f1',
                    pointRadius: 2,
                    borderWidth: 2,
                    tension: 0.35
                }
            ]
        },
        options: {
            ...baseOpts,
            scales: {
                x: {
                    ticks: { color: textColor, font: { size: 11 }, autoSkip: false },
                    grid: { display: false }
                },
                y: {
                    position: 'left',
                    ticks: {
                        color: textColor,
                        font: { size: 11 },
                        callback: (value) => formatMoney(value)
                    },
                    grid: { color: gridColor },
                    border: { display: false }
                },
                y1: {
                    position: 'right',
                    ticks: { color: textColor, font: { size: 11 } },
                    grid: { display: false },
                    border: { display: false }
                }
            }
        }
    });
}

function renderFinanceiroGateway(dados, resumo) {
    if (!document.getElementById('chartFinanceiroGateway')) {
        return;
    }

    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    const valores = dados?.data?.length ? dados.data : [0];
    const chartColors = labels.length ? finGatewayColors.slice(0, labels.length) : ['#cbd5e1'];

    document.getElementById('qtdTransacoesFinanceiro').textContent = Number(resumo?.qtd_transacoes || 0).toLocaleString('pt-BR');

    const leg = document.getElementById('legFinGateway');
    leg.innerHTML = '';
    labels.forEach((label, index) => {
        leg.innerHTML += `<span class="leg-item"><span class="leg-dot" style="background:${finGatewayColors[index % finGatewayColors.length]};"></span>${label} ${formatMoney(valores[index] || 0)}</span>`;
    });

    if (chartFinanceiroGateway) {
        chartFinanceiroGateway.destroy();
    }

    chartFinanceiroGateway = new Chart(document.getElementById('chartFinanceiroGateway'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                data: valores,
                backgroundColor: chartColors,
                borderRadius: 5,
                borderSkipped: false
            }]
        },
        options: {
            ...baseOpts,
            scales: {
                x: {
                    ticks: { color: textColor, font: { size: 11 } },
                    grid: { display: false }
                },
                y: {
                    ticks: {
                        color: textColor,
                        font: { size: 11 },
                        callback: (value) => formatMoney(value)
                    },
                    grid: { color: gridColor },
                    border: { display: false }
                }
            }
        }
    });
}

function renderFinanceiroStatus(dados, resumo) {
    if (!document.getElementById('chartFinanceiroStatus')) {
        return;
    }

    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    const valores = dados?.data?.length ? dados.data : [0];
    const chartColors = labels.length ? finStatusColors.slice(0, labels.length) : ['#cbd5e1'];

    document.getElementById('taxaAprovacaoFinanceiro').textContent = formatPercent(resumo?.taxa_aprovacao ?? 0);

    const leg = document.getElementById('legFinStatus');
    leg.innerHTML = '';
    labels.forEach((label, index) => {
        leg.innerHTML += `<span class="leg-item"><span class="leg-dot" style="background:${finStatusColors[index % finStatusColors.length]};"></span>${label} ${formatMoney(valores[index] || 0)}</span>`;
    });

    if (chartFinanceiroStatus) {
        chartFinanceiroStatus.destroy();
    }

    chartFinanceiroStatus = new Chart(document.getElementById('chartFinanceiroStatus'), {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: valores,
                backgroundColor: chartColors,
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: { ...baseOpts, cutout: '66%', layout: { padding: 4 } }
    });
}

if (Object.values(HOME_SECTIONS).some(Boolean)) {
    initHomeToggles();

    const btnFiltrar = document.getElementById('btnFiltrar');
    const btnHoje = document.getElementById('btnHoje');
    const btn12m = document.getElementById('btn12m');

    if (btnFiltrar) {
        btnFiltrar.addEventListener('click', fetchChartData);
    }

    if (btnHoje) {
        btnHoje.addEventListener('click', () => {
            const hoje = formatInputDate(new Date());
            setDateInputs(hoje, hoje);
            fetchChartData();
        });
    }

    if (btn12m) {
        btn12m.addEventListener('click', () => {
            const periodo = defaultDates();
            setDateInputs(periodo.inicio, periodo.fim);
            fetchChartData();
        });
    }

    const defaults = defaultDates();
    setDateInputs(defaults.inicio, defaults.fim);
    fetchChartData();
}
</script>
@stop
