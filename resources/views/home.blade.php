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

    .period-bar input[type=date]:focus,
    .period-bar select:focus {
        border-color: #0891b2;
    }

    .period-bar select {
        border: 1px solid #e2e5ef;
        border-radius: 8px;
        padding: 6px 10px;
        font-size: 12px;
        color: #1a1d2e;
        outline: none;
        min-width: 220px;
        background: #fff;
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

    .analysis-table td,
    .analysis-table th {
        white-space: nowrap;
    }

    .analysis-note {
        font-size: 12px;
        color: #64748b;
        margin: 0;
    }

    .recommendation-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .recommendation-item {
        border: 1px solid #e2e5ef;
        border-radius: 14px;
        padding: 12px 14px;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .recommendation-item.is-danger {
        background: #fff1f2;
        border-color: #fecdd3;
    }

    .recommendation-item.is-warning {
        background: #fffbeb;
        border-color: #fde68a;
    }

    .recommendation-item.is-success {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .recommendation-item.is-info {
        background: #eff6ff;
        border-color: #bfdbfe;
    }

    .recommendation-title {
        font-size: 12px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }

    .recommendation-desc {
        font-size: 12px;
        color: #334155;
        margin: 0;
    }

    .status-chip {
        display: inline-flex;
        align-items: center;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .status-chip.is-danger { background: #fee2e2; color: #b91c1c; }
    .status-chip.is-warning { background: #fef3c7; color: #b45309; }
    .status-chip.is-success { background: #dcfce7; color: #166534; }
    .status-chip.is-info { background: #dbeafe; color: #1d4ed8; }

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

{{-- ══ ALERTAS DA EMPRESA ══ --}}
@if(isset($alertas) && $alertas->isNotEmpty())
<div id="painelAlertas" style="margin-bottom:16px;">
    @foreach($alertas as $alerta)
    <div class="alert alert-{{ $alerta->nivel_class }} alert-dismissible d-flex align-items-start"
         id="alerta-{{ $alerta->id }}"
         style="border-left:4px solid {{ $alerta->nivel === 'critico' ? '#dc3545' : ($alerta->nivel === 'aviso' ? '#f59e0b' : '#0ea5e9') }};border-radius:8px;padding:12px 16px;margin-bottom:8px;">
        <i class="{{ $alerta->nivel_icone }} mr-3 mt-1 flex-shrink-0" style="font-size:1.1rem;"></i>
        <div class="flex-grow-1">
            <strong style="font-size:.9rem;">{{ $alerta->titulo }}</strong>
            <div class="mt-1" style="font-size:.82rem;">{{ $alerta->mensagem }}</div>
        </div>
        <button type="button"
                class="btn btn-sm ml-3 flex-shrink-0"
                style="background:rgba(0,0,0,.08);border:none;border-radius:6px;font-size:.75rem;white-space:nowrap;"
                onclick="resolverAlerta({{ $alerta->id }}, this)">
            <i class="fas fa-check mr-1"></i> Marcar como resolvido
        </button>
    </div>
    @endforeach
</div>
@endif

<x-onboarding-widget />
@if ($hasVisibleCharts)
<div class="period-bar">
    <label for="data_inicio">Periodo:</label>
    <input type="date" id="data_inicio" name="data_inicio">
    <span class="period-sep">ate</span>
    <input type="date" id="data_fim" name="data_fim">
    <label for="estacao_id" style="margin-left:8px;">Estação:</label>
    <select id="estacao_id" name="estacao_id">
        <option value="0">Todas as estações</option>
        @foreach($estacoesFiltro as $estacao)
            <option value="{{ $estacao['id'] }}">{{ $estacao['nome'] }}</option>
        @endforeach
    </select>
    <button class="btn-filtrar" id="btnFiltrar">Aplicar</button>
    <button class="btn-filtrar" id="btnHoje" style="background:#059669;">Hoje</button>
    <button class="btn-filtrar" id="btn12m" style="background:#7c3aed;">Ultimos 12 meses</button>
    <a href="{{ route('indicadores.index') }}" class="btn-secondary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
        <i class="fas fa-chart-bar"></i> Indicadores detalhados
    </a>
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

    <div class="kpi-card c-indigo">
        <div class="kpi-top">
            <div>
                <p class="kpi-label">Origem</p>
                <p class="kpi-value" id="kpiReservasOrigem">--</p>
            </div>
            <div class="kpi-icon"><i class="fas fa-user-tie"></i></div>
        </div>
        <p class="kpi-sub" id="kpiReservasOrigemSub">Site vs colaborador</p>
    </div>

    @if ($homeSections['satisfacao'])
    <div class="kpi-card c-teal">
        <div class="kpi-top">
            <div>
                <p class="kpi-label">Satisfacao</p>
                <p class="kpi-value" id="kpiSatisfacao">--</p>
            </div>
            <div class="kpi-icon"><i class="fas fa-smile"></i></div>
        </div>
        <p class="kpi-sub" id="kpiSatisfacaoSub">CSAT medio</p>
    </div>

    <div class="kpi-card c-indigo">
        <div class="kpi-top">
            <div>
                <p class="kpi-label">NPS</p>
                <p class="kpi-value" id="kpiNps">--</p>
            </div>
            <div class="kpi-icon"><i class="fas fa-thumbs-up"></i></div>
        </div>
        <p class="kpi-sub" id="kpiNpsSub">Promotores vs detratores</p>
    </div>
    @endif
</div>

<div class="home-details d-none" id="homeDetails">
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

    <div class="chart-card span-4">
        <div class="chart-overlay" id="overlayReservasOrigem"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Origem das reservas</p>
                <p class="card-sub" id="subReservasOrigem">Site, colaborador e sem origem</p>
            </div>
        </div>
        <div class="legend-row" id="legReservasOrigem"></div>
        <div class="chart-canvas h-220">
            <canvas id="chartReservasOrigem"></canvas>
        </div>
    </div>

    <div class="chart-card span-8">
        <div class="chart-overlay" id="overlayReservasColaboradores"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Top colaboradores</p>
                <p class="card-sub" id="subReservasColaboradores">Reservas registradas no periodo</p>
            </div>
            <div class="text-right">
                <p class="metric-big" id="totalReservasColaboradores">--</p>
                <div class="metric-delta delta-up" id="subTopColaborador">--</div>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Colaborador</th>
                        <th class="text-center">Reservas</th>
                    </tr>
                </thead>
                <tbody id="reservasColaboradoresBody">
                    <tr><td colspan="2" class="text-center text-muted py-4">Sem dados no periodo.</td></tr>
                </tbody>
            </table>
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

    @if ($homeSections['satisfacao'])
    <div class="chart-card span-8">
        <div class="chart-overlay" id="overlaySatisfacaoLinha"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Satisfacao ao longo do tempo</p>
                <p class="card-sub" id="subSatisfacao">--</p>
            </div>
            <div class="text-right">
                <p class="metric-big" id="totalSatisfacao">--</p>
                <div class="metric-delta delta-up" id="mediaFacilidadeSatisfacao">facilidade media --</div>
            </div>
        </div>
        <div class="legend-row">
            <span class="leg-item"><span class="leg-dot" style="background:#0891b2;"></span>CSAT</span>
            <span class="leg-item"><span class="leg-dot" style="background:#7c3aed;"></span>Facilidade</span>
            <span class="leg-item"><span class="leg-dot" style="background:#16a34a;"></span>NPS medio</span>
        </div>
        <div class="chart-canvas h-220">
            <canvas id="chartSatisfacaoLinha"></canvas>
        </div>
    </div>

    <div class="chart-card span-4">
        <div class="chart-overlay" id="overlaySatisfacaoDistribuicao"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Distribuicao NPS</p>
                <p class="card-sub">Promotores, neutros e detratores</p>
            </div>
        </div>
        <div class="legend-row" id="legSatisfacaoNps"></div>
        <div class="chart-canvas h-220">
            <canvas id="chartSatisfacaoDistribuicao"></canvas>
        </div>
    </div>

    <div class="chart-card span-6">
        <div class="chart-overlay" id="overlaySatisfacaoEstacoes"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Satisfacao por estacao</p>
                <p class="card-sub">Media de satisfacao geral</p>
            </div>
        </div>
        <div class="chart-canvas h-210">
            <canvas id="chartSatisfacaoEstacoes"></canvas>
        </div>
    </div>

    <div class="chart-card span-6">
        <div class="chart-overlay" id="overlaySatisfacaoGrupos"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Satisfacao por grupo</p>
                <p class="card-sub">Media de satisfacao geral</p>
            </div>
        </div>
        <div class="chart-canvas h-210">
            <canvas id="chartSatisfacaoGrupos"></canvas>
        </div>
    </div>

    <div class="chart-card span-12">
        <div class="card-head">
            <div>
                <p class="card-title">Rankings de satisfacao</p>
                <p class="card-sub">Veiculos, marcas, modelos e respondentes</p>
            </div>
        </div>
        <div class="legend-row mb-3">
            <button type="button" class="btn-secondary js-sat-tab" data-sat-tab="veiculos">Veiculos</button>
            <button type="button" class="btn-secondary js-sat-tab" data-sat-tab="marcas">Marcas</button>
            <button type="button" class="btn-secondary js-sat-tab" data-sat-tab="modelos">Modelos</button>
            <button type="button" class="btn-secondary js-sat-tab" data-sat-tab="responsaveis">Respondentes</button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Dimensao</th>
                        <th class="text-center">Respostas</th>
                        <th class="text-center">CSAT</th>
                        <th class="text-center">Facilidade</th>
                        <th class="text-center">NPS</th>
                    </tr>
                </thead>
                <tbody id="satisfacaoRankingBody">
                    <tr><td colspan="5" class="text-center text-muted py-4">Selecione uma dimensao para ver os dados.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if ($homeSections['analise_avancada'])
    <div class="chart-card span-12">
        <div class="card-head">
            <div>
                <p class="card-title">Análise avançada da frota</p>
                <p class="card-sub">Saúde, eficiência, custo real, lucro real e proteção FIPE no período</p>
            </div>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card c-emerald">
                <div class="kpi-top">
                    <div>
                        <p class="kpi-label">Saúde média</p>
                        <p class="kpi-value" id="kpiAnaliseSaude">--</p>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-heartbeat"></i></div>
                </div>
                <p class="kpi-sub">Índice composto da frota</p>
            </div>

            <div class="kpi-card c-teal">
                <div class="kpi-top">
                    <div>
                        <p class="kpi-label">Eficiência média</p>
                        <p class="kpi-value" id="kpiAnaliseEficiencia">--</p>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-bolt"></i></div>
                </div>
                <p class="kpi-sub">Receita vs custo real</p>
            </div>

            <div class="kpi-card c-rose">
                <div class="kpi-top">
                    <div>
                        <p class="kpi-label">Prejuízo</p>
                        <p class="kpi-value" id="kpiAnalisePrejuizo">--</p>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-triangle-exclamation"></i></div>
                </div>
                <p class="kpi-sub">Veículos com lucro real negativo</p>
            </div>

            <div class="kpi-card c-indigo">
                <div class="kpi-top">
                    <div>
                        <p class="kpi-label">Proteção FIPE</p>
                        <p class="kpi-value" id="kpiAnaliseProtecao">--</p>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-shield-heart"></i></div>
                </div>
                <p class="kpi-sub">Comprados abaixo da FIPE</p>
            </div>

            <div class="kpi-card c-amber">
                <div class="kpi-top">
                    <div>
                        <p class="kpi-label">Custo real</p>
                        <p class="kpi-value" id="kpiAnaliseCustoReal">--</p>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-sack-dollar"></i></div>
                </div>
                <p class="kpi-sub">Operacional + depreciação</p>
            </div>

            <div class="kpi-card c-teal">
                <div class="kpi-top">
                    <div>
                        <p class="kpi-label">Lucro real</p>
                        <p class="kpi-value" id="kpiAnaliseLucroReal">--</p>
                    </div>
                    <div class="kpi-icon"><i class="fas fa-chart-line"></i></div>
                </div>
                <p class="kpi-sub">Receita menos custo real</p>
            </div>
        </div>

        <p class="analysis-note mt-3">Os indicadores abaixo usam receita, multas, sinistros, danos e depreciação estimada para mostrar o desempenho real de cada veículo.</p>
    </div>

    <div class="chart-card span-6">
        <div class="chart-overlay" id="overlayAnaliseLucrativos"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Veículos mais lucrativos</p>
                <p class="card-sub">Baseado em lucro real</p>
            </div>
        </div>
        <div class="chart-canvas h-220">
            <canvas id="chartAnaliseLucrativos"></canvas>
        </div>
    </div>

    <div class="chart-card span-6">
        <div class="chart-overlay" id="overlayAnalisePrejuizo"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Veículos com prejuízo</p>
                <p class="card-sub">Lucro real negativo no período</p>
            </div>
        </div>
        <div class="chart-canvas h-220">
            <canvas id="chartAnalisePrejuizo"></canvas>
        </div>
    </div>

    <div class="chart-card span-4">
        <div class="chart-overlay" id="overlayAnaliseProtecao"><div class="spinner"></div></div>
        <div class="card-head">
            <div>
                <p class="card-title">Proteção contra depreciação</p>
                <p class="card-sub">Comprado abaixo da FIPE</p>
            </div>
        </div>
        <div class="legend-row">
            <span class="leg-item"><span class="leg-dot" style="background:#16a34a;"></span>Protegidos</span>
            <span class="leg-item"><span class="leg-dot" style="background:#94a3b8;"></span>Sem proteção</span>
        </div>
        <div class="chart-canvas h-220">
            <canvas id="chartAnaliseProtecao"></canvas>
        </div>
    </div>

    <div class="chart-card span-8">
        <div class="card-head">
            <div>
                <p class="card-title">Relatório automático com recomendações</p>
                <p class="card-sub">Sinalizações geradas a partir dos números do período</p>
            </div>
        </div>
        <div class="recommendation-list" id="analiseRecomendacoes">
            <div class="recommendation-item is-info">
                <p class="recommendation-title">Carregando recomendações</p>
                <p class="recommendation-desc">A análise será preenchida após aplicar o filtro de período.</p>
            </div>
        </div>
    </div>

    <div class="chart-card span-12">
        <div class="card-head">
            <div>
                <p class="card-title">Diagnóstico detalhado por veículo</p>
                <p class="card-sub">Receita, custo real, lucro real, saúde e proteção FIPE</p>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 analysis-table">
                <thead class="thead-light">
                    <tr>
                        <th>Veículo</th>
                        <th>Grupo</th>
                        <th class="text-right">Receita</th>
                        <th class="text-right">Custo operacional</th>
                        <th class="text-right">Depreciação</th>
                        <th class="text-right">Custo real</th>
                        <th class="text-right">Lucro real</th>
                        <th class="text-center">Eficiência</th>
                        <th class="text-center">Saúde</th>
                        <th class="text-center">FIPE</th>
                        <th>Recomendação</th>
                    </tr>
                </thead>
                <tbody id="analiseDetalhadaBody">
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">Aplique o filtro para visualizar o diagnóstico detalhado.</td>
                    </tr>
                </tbody>
            </table>
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
function resolverAlerta(id, btn) {
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span> Resolvendo…';
    fetch(`/admin/alerta/${id}/resolver`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            const el = document.getElementById('alerta-' + id);
            el.style.transition = 'opacity .4s';
            el.style.opacity = '0';
            setTimeout(() => {
                el.remove();
                const painel = document.getElementById('painelAlertas');
                if (painel && !painel.querySelector('[id^="alerta-"]')) painel.remove();
            }, 400);
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check mr-1"></i> Marcar como resolvido';
    });
}

const CHART_DATA_URL = '{{ route('home.chart-data') }}';
const HOME_SECTIONS = @json($homeSections);
const textColor = 'rgba(0,0,0,0.45)';
const gridColor = 'rgba(0,0,0,0.06)';
const overlayIds = [
    'overlayReservas',
    'overlayReservasOrigem',
    'overlayReservasColaboradores',
    'overlayMovStatus',
    'overlayFrota',
    'overlayOcupacao',
    'overlayFinanceiroLinha',
    'overlayFinanceiroStatus',
    'overlayFinanceiroGateway',
    'overlaySatisfacaoLinha',
    'overlaySatisfacaoDistribuicao',
    'overlaySatisfacaoEstacoes',
    'overlaySatisfacaoGrupos',
    'overlayAnaliseLucrativos',
    'overlayAnalisePrejuizo',
    'overlayAnaliseProtecao',
];

const baseOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: { cornerRadius: 8, padding: 10 }
    }
};

let simboloMoeda = 'R$';
let lastDashboardData = null;

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

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function analiseStatusClass(status) {
    switch (status) {
        case 'prejuizo':
        case 'critico':
            return 'danger';
        case 'atencao':
            return 'warning';
        case 'saudavel':
            return 'success';
        default:
            return 'info';
    }
}

function analiseStatusLabel(status) {
    switch (status) {
        case 'prejuizo':
            return 'Prejuízo';
        case 'critico':
            return 'Crítico';
        case 'atencao':
            return 'Atenção';
        case 'saudavel':
            return 'Saudável';
        default:
            return 'Indefinido';
    }
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

function getEstacaoSelecionada() {
    const estacao = document.getElementById('estacao_id');
    return estacao ? (estacao.value || '0') : '0';
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

    const estacaoId = getEstacaoSelecionada();
    const url = `${CHART_DATA_URL}?data_inicio=${encodeURIComponent(inicio)}&data_fim=${encodeURIComponent(fim)}&estacao_id=${encodeURIComponent(estacaoId)}`;

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
        })
        .catch(() => alert('Erro ao carregar dados. Tente novamente.'))
        .finally(() => {
            btn.disabled = false;
        });
}

function renderKpis(data, inicio, fim) {
    const totalReservas = Number(data?.totalReservas ?? 0);
    const totalMov = Number(data?.totalMovimentacoes ?? 0);
    const ocupacao = Number(data?.ocupacaoGeral ?? 0);
    const totalFin = Number(data?.financeiroResumo?.total ?? 0);
    const ticket = Number(data?.financeiroResumo?.ticket_medio ?? 0);
    const satResumo = data?.satisfacaoResumo || {};
    const reservasOrigem = data?.reservasOrigemResumo || {};

    const elReservas = document.getElementById('kpiTotalReservas');
    const elMov = document.getElementById('kpiMovimentacoes');
    const elOcup = document.getElementById('kpiOcupacao');
    const elFin = document.getElementById('kpiTotalFinanceiro');
    const elTicket = document.getElementById('kpiTicketMedio');
    const elPeriodo = document.getElementById('kpiPeriodo');
    const elReservasOrigem = document.getElementById('kpiReservasOrigem');
    const elReservasOrigemSub = document.getElementById('kpiReservasOrigemSub');
    const elSatisfacao = document.getElementById('kpiSatisfacao');
    const elSatisfacaoSub = document.getElementById('kpiSatisfacaoSub');
    const elNps = document.getElementById('kpiNps');
    const elNpsSub = document.getElementById('kpiNpsSub');
    const estacaoNome = data?.estacaoSelecionadaNome || 'Todas as estações';

    if (elReservas) elReservas.textContent = totalReservas.toLocaleString('pt-BR');
    if (elMov) elMov.textContent = totalMov.toLocaleString('pt-BR');
    if (elOcup) elOcup.textContent = formatPercent(ocupacao);
    if (elFin) elFin.textContent = formatMoney(totalFin);
    if (elTicket) elTicket.textContent = `Ticket medio: ${formatMoney(ticket)}`;
    if (elPeriodo) elPeriodo.textContent = `${formatShortDate(inicio)} - ${formatShortDate(fim)} • ${estacaoNome}`;
    if (elReservasOrigem) elReservasOrigem.textContent = `${Number(reservasOrigem?.percentual_site ?? 0).toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 })}%`;
    if (elReservasOrigemSub) {
        elReservasOrigemSub.textContent = `Site ${Number(reservasOrigem?.site ?? 0).toLocaleString('pt-BR')} • Colaborador ${Number(reservasOrigem?.colaborador ?? 0).toLocaleString('pt-BR')} • Sem origem ${Number(reservasOrigem?.sem_origem ?? 0).toLocaleString('pt-BR')}`;
    }
    if (elSatisfacao) elSatisfacao.textContent = satResumo.total_respostas ? Number(satResumo.media_geral || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '--';
    if (elSatisfacaoSub) elSatisfacaoSub.textContent = satResumo.total_respostas ? `CSAT medio • ${Number(satResumo.total_respostas || 0).toLocaleString('pt-BR')} respostas` : 'Sem respostas no periodo';
    if (elNps) elNps.textContent = satResumo.total_respostas ? Number(satResumo.nps || 0).toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 1 }) : '--';
    if (elNpsSub) elNpsSub.textContent = satResumo.total_respostas ? `${Number(satResumo.promotores || 0).toLocaleString('pt-BR')} promotores • ${Number(satResumo.detratores || 0).toLocaleString('pt-BR')} detratores` : 'Sem respostas no periodo';
}

if (Object.values(HOME_SECTIONS).some(Boolean)) {
    // Tabs de satisfação (se existirem no DOM)
    document.querySelectorAll('.js-sat-tab').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.js-sat-tab').forEach((el) => el.classList.remove('active'));
            btn.classList.add('active');
        });
    });

    const btnMore = document.getElementById('btnMoreCards');
    const more = document.getElementById('moreCards');
    if (btnMore && more) {
        btnMore.addEventListener('click', () => {
            const isHidden = more.classList.contains('d-none');
            more.classList.toggle('d-none', !isHidden);
            btnMore.textContent = isHidden ? 'Mostrar menos atalhos' : 'Mostrar mais atalhos';
        });
    }

    const estacaoSelect = document.getElementById('estacao_id');
    if (estacaoSelect) {
        estacaoSelect.addEventListener('change', () => { fetchChartData(); });
    }

    const btnFiltrar = document.getElementById('btnFiltrar');
    const btnHoje = document.getElementById('btnHoje');
    const btn12m = document.getElementById('btn12m');

    if (btnFiltrar) { btnFiltrar.addEventListener('click', fetchChartData); }
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
