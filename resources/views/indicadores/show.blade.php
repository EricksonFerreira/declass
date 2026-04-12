@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    body, .wrapper, .content-wrapper { background: #f0f2f7 !important; }

    .period-bar {
        background: #fff;
        border: 1px solid #e2e5ef;
        border-radius: 16px;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .period-bar label { font-family: 'Plus Jakarta Sans',sans-serif; font-size: 12px; font-weight: 600; color: #4a5068; margin:0; white-space:nowrap; }
    .period-bar input[type=date] { border:1px solid #e2e5ef; border-radius:8px; padding:6px 10px; font-size:12px; color:#1a1d2e; outline:none; }
    .period-bar select { border:1px solid #e2e5ef; border-radius:8px; padding:6px 10px; font-size:12px; color:#1a1d2e; outline:none; min-width:200px; background:#fff; }
    .btn-filtrar { background:#0891b2; color:#fff; border:none; border-radius:8px; padding:7px 18px; font-family:'Plus Jakarta Sans',sans-serif; font-size:12px; font-weight:600; cursor:pointer; transition:background 0.15s; }
    .btn-filtrar:hover { background:#0e7490; }
    .btn-filtrar:disabled { background:#94a3b8; cursor:not-allowed; }
    .btn-secondary { background:#64748b; color:#fff; border:none; border-radius:8px; padding:7px 14px; font-family:'Plus Jakarta Sans',sans-serif; font-size:12px; font-weight:700; cursor:pointer; }
    .btn-secondary:hover { background:#475569; }
    .period-sep { color:#8b92a9; font-size:12px; }

    .charts-grid { display:grid; grid-template-columns:repeat(12, minmax(0,1fr)); gap:16px; margin-bottom:16px; }
    .chart-card { background:#fff; border:1px solid #e2e5ef; border-radius:16px; padding:18px 20px 16px; position:relative; overflow:hidden; }
    .chart-card.span-12 { grid-column:span 12; }
    .chart-card.span-8  { grid-column:span 8; }
    .chart-card.span-6  { grid-column:span 6; }
    .chart-card.span-4  { grid-column:span 4; }

    .card-head { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; margin-bottom:14px; }
    .card-title { font-family:'Plus Jakarta Sans',sans-serif; font-size:13px; font-weight:700; color:#1a1d2e; margin:0; }
    .card-sub { font-size:11px; color:#8b92a9; margin:3px 0 0; }
    .metric-big { font-size:26px; font-weight:700; color:#1a1d2e; line-height:1; margin:0; }
    .metric-delta { font-size:11px; margin-top:4px; }
    .delta-up { color:#059669; }

    .chart-overlay { position:absolute; inset:0; background:rgba(255,255,255,.88); display:flex; align-items:center; justify-content:center; border-radius:16px; z-index:10; }
    .chart-overlay.hidden { display:none; }
    .spinner { width:32px; height:32px; border:3px solid #e2e5ef; border-top-color:#0891b2; border-radius:50%; animation:spin 0.7s linear infinite; }
    @keyframes spin { to { transform:rotate(360deg); } }

    .legend-row { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:10px; }
    .leg-item { display:flex; align-items:center; gap:5px; font-size:11px; color:#64748b; }
    .leg-dot { width:8px; height:8px; border-radius:2px; flex-shrink:0; }

    .kpi-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(190px,1fr)); gap:14px; margin-bottom:16px; }
    .kpi-card { background:#fff; border:1px solid #e2e5ef; border-radius:16px; padding:16px 16px 14px; display:flex; flex-direction:column; gap:10px; position:relative; overflow:hidden; }
    .kpi-card::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; background:var(--accent); }
    .kpi-top { display:flex; align-items:center; justify-content:space-between; gap:10px; }
    .kpi-label { font-family:'Plus Jakarta Sans',sans-serif; font-size:11px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; color:#64748b; margin:0; }
    .kpi-value { font-family:'Plus Jakarta Sans',sans-serif; font-size:22px; font-weight:800; color:#0f172a; letter-spacing:-.02em; margin:0; line-height:1.1; }
    .kpi-sub { font-size:12px; color:#8b92a9; margin:0; }
    .kpi-icon { width:40px; height:40px; border-radius:12px; background:var(--accent-soft); display:flex; align-items:center; justify-content:center; font-size:16px; color:var(--accent); flex-shrink:0; }
    .c-teal    { --accent:#0891b2; --accent-soft:#e0f5fa; }
    .c-emerald { --accent:#059669; --accent-soft:#d1fae5; }
    .c-indigo  { --accent:#4f46e5; --accent-soft:#e0e7ff; }
    .c-amber   { --accent:#d97706; --accent-soft:#fef3c7; }
    .c-rose    { --accent:#e11d48; --accent-soft:#ffe4e6; }
    .c-purple  { --accent:#7c3aed; --accent-soft:#ede9fe; }

    .kpi-inline { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:10px; }
    .kpi-pill { border:1px solid #dbe3ef; background:#f8fafc; border-radius:999px; padding:4px 10px; font-size:11px; color:#475569; font-weight:600; }
    .kpi-pill strong { color:#0f172a; }

    .gauge-list { display:flex; flex-direction:column; gap:10px; }
    .gauge-row { display:flex; flex-direction:column; gap:3px; }
    .gauge-label { font-size:11px; color:#64748b; display:flex; justify-content:space-between; gap:10px; }
    .gauge-label span:last-child { font-weight:700; color:#1a1d2e; }
    .gauge-bar-bg { height:6px; border-radius:999px; background:#f0f2f7; overflow:hidden; }
    .gauge-bar-fill { height:100%; border-radius:999px; }
    .empty-gauge { padding:12px; border:1px dashed #d5deeb; border-radius:10px; font-size:12px; color:#64748b; background:#f8fafc; }

    .analysis-table td,.analysis-table th { white-space:nowrap; }
    .recommendation-list { display:flex; flex-direction:column; gap:10px; }
    .recommendation-item { border:1px solid #e2e5ef; border-radius:14px; padding:12px 14px; background:#f8fafc; display:flex; flex-direction:column; gap:4px; }
    .recommendation-item.is-danger  { background:#fff1f2; border-color:#fecdd3; }
    .recommendation-item.is-warning { background:#fffbeb; border-color:#fde68a; }
    .recommendation-item.is-success { background:#f0fdf4; border-color:#bbf7d0; }
    .recommendation-item.is-info    { background:#eff6ff; border-color:#bfdbfe; }
    .recommendation-title { font-size:12px; font-weight:800; color:#0f172a; margin:0; }
    .recommendation-desc  { font-size:12px; color:#334155; margin:0; }
    .status-chip { display:inline-flex; align-items:center; padding:3px 8px; border-radius:999px; font-size:11px; font-weight:700; }
    .status-chip.is-danger  { background:#fee2e2; color:#b91c1c; }
    .status-chip.is-warning { background:#fef3c7; color:#b45309; }
    .status-chip.is-success { background:#dcfce7; color:#166534; }
    .status-chip.is-info    { background:#dbeafe; color:#1d4ed8; }

    .chart-canvas { position:relative; width:100%; }
    .h-220 { height:220px; } .h-210 { height:210px; } .h-200 { height:200px; }
    .analysis-note { font-size:12px; color:#64748b; margin:0; }

    @media(max-width:1200px) { .chart-card.span-8,.chart-card.span-6,.chart-card.span-4 { grid-column:span 6; } }
    @media(max-width:767px)  { .chart-card.span-12,.chart-card.span-8,.chart-card.span-6,.chart-card.span-4 { grid-column:span 12; } }
</style>
@stop

@section('content')
{{-- Period bar --}}
<div class="period-bar">
    <label>Período:</label>
    <input type="date" id="data_inicio">
    <span class="period-sep">até</span>
    <input type="date" id="data_fim">
    <label style="margin-left:8px;">Estação:</label>
    <select id="estacao_id">
        <option value="0">Todas as estações</option>
        @foreach($estacoesFiltro as $est)
            <option value="{{ $est['id'] }}">{{ $est['nome'] }}</option>
        @endforeach
    </select>
    <button class="btn-filtrar" id="btnFiltrar">Aplicar</button>
    <button class="btn-filtrar" id="btnHoje" style="background:#059669;">Hoje</button>
    <button class="btn-filtrar" id="btn12m" style="background:#7c3aed;">Últimos 12 meses</button>
    <a href="{{ route('indicadores.index') }}" class="btn-secondary ml-auto" style="text-decoration:none;">
        <i class="fas fa-th mr-1"></i> Todos os indicadores
    </a>
</div>

{{-- ======================== RESERVAS ======================== --}}
@if($indicador['slug'] === 'reservas')
<div class="kpi-grid">
    <div class="kpi-card c-teal">
        <div class="kpi-top"><div><p class="kpi-label">Total de reservas</p><p class="kpi-value" id="kpiTotalReservas">--</p></div><div class="kpi-icon"><i class="fas fa-calendar-check"></i></div></div>
        <p class="kpi-sub" id="kpiPeriodo">--</p>
    </div>
    <div class="kpi-card c-indigo">
        <div class="kpi-top"><div><p class="kpi-label">Origem (Site %)</p><p class="kpi-value" id="kpiReservasOrigem">--</p></div><div class="kpi-icon"><i class="fas fa-user-tie"></i></div></div>
        <p class="kpi-sub" id="kpiReservasOrigemSub">Site vs colaborador</p>
    </div>
</div>
<div class="charts-grid">
    <div class="chart-card span-12">
        <div class="chart-overlay" id="overlayReservas"><div class="spinner"></div></div>
        <div class="card-head">
            <div><p class="card-title">Reservas ao longo do tempo</p><p class="card-sub" id="subReservas">--</p></div>
            <div class="text-right"><p class="metric-big" id="totalReservas">--</p><div class="metric-delta delta-up">reservas no período</div></div>
        </div>
        <div class="legend-row">
            <span class="leg-item"><span class="leg-dot" style="background:#0891b2;"></span>Confirmadas</span>
            <span class="leg-item"><span class="leg-dot" style="background:#bae6fd;"></span>Cancelamentos</span>
            <span class="leg-item"><span class="leg-dot" style="background:#06b6d4;"></span>Tendência</span>
        </div>
        <div class="chart-canvas h-220"><canvas id="chartReservas"></canvas></div>
    </div>
    <div class="chart-card span-4">
        <div class="chart-overlay" id="overlayReservasOrigem"><div class="spinner"></div></div>
        <div class="card-head"><div><p class="card-title">Origem das reservas</p><p class="card-sub" id="subReservasOrigem">Site, colaborador e sem origem</p></div></div>
        <div class="legend-row" id="legReservasOrigem"></div>
        <div class="chart-canvas h-220"><canvas id="chartReservasOrigem"></canvas></div>
    </div>
    <div class="chart-card span-8">
        <div class="chart-overlay" id="overlayReservasColaboradores"><div class="spinner"></div></div>
        <div class="card-head">
            <div><p class="card-title">Top colaboradores</p><p class="card-sub">Reservas registradas no período</p></div>
            <div class="text-right"><p class="metric-big" id="totalReservasColaboradores">--</p><div class="metric-delta delta-up" id="subTopColaborador">--</div></div>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light"><tr><th>Colaborador</th><th class="text-center">Reservas</th></tr></thead>
                <tbody id="reservasColaboradoresBody"><tr><td colspan="2" class="text-center text-muted py-4">Sem dados no período.</td></tr></tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ======================== FINANCEIRO ======================== --}}
@if($indicador['slug'] === 'financeiro')
<div class="kpi-grid">
    <div class="kpi-card c-emerald">
        <div class="kpi-top"><div><p class="kpi-label">Receita Total</p><p class="kpi-value" id="kpiTotalFinanceiro">--</p></div><div class="kpi-icon"><i class="fas fa-dollar-sign"></i></div></div>
        <p class="kpi-sub" id="kpiTicketMedio">--</p>
    </div>
    <div class="kpi-card c-teal">
        <div class="kpi-top"><div><p class="kpi-label">Transações</p><p class="kpi-value" id="kpiQtdTransacoes">--</p></div><div class="kpi-icon"><i class="fas fa-exchange-alt"></i></div></div>
        <p class="kpi-sub" id="kpiTaxaAprovacao">--</p>
    </div>
</div>
<div class="charts-grid">
    <div class="chart-card span-8">
        <div class="chart-overlay" id="overlayFinanceiroLinha"><div class="spinner"></div></div>
        <div class="card-head">
            <div><p class="card-title">Financeiro: receita e transações</p><p class="card-sub" id="subFinanceiro">--</p></div>
            <div class="text-right"><p class="metric-big" id="totalFinanceiro">--</p><div class="metric-delta delta-up" id="ticketMedioFinanceiro">ticket médio --</div></div>
        </div>
        <div class="legend-row">
            <span class="leg-item"><span class="leg-dot" style="background:#0ea5e9;"></span>Receita</span>
            <span class="leg-item"><span class="leg-dot" style="background:#6366f1;"></span>Transações</span>
        </div>
        <div class="chart-canvas h-220"><canvas id="chartFinanceiroLinha"></canvas></div>
    </div>
    <div class="chart-card span-4">
        <div class="chart-overlay" id="overlayFinanceiroStatus"><div class="spinner"></div></div>
        <div class="card-head"><div><p class="card-title">Performance por status</p><p class="card-sub">Taxa de aprovação: <span id="taxaAprovacaoFinanceiro">--</span></p></div></div>
        <div class="legend-row" id="legFinStatus"></div>
        <div class="chart-canvas h-200"><canvas id="chartFinanceiroStatus"></canvas></div>
    </div>
    <div class="chart-card span-8">
        <div class="chart-overlay" id="overlayFinanceiroGateway"><div class="spinner"></div></div>
        <div class="card-head"><div><p class="card-title">Receita por gateway</p><p class="card-sub">Transações no período: <span id="qtdTransacoesFinanceiro">--</span></p></div></div>
        <div class="legend-row" id="legFinGateway"></div>
        <div class="chart-canvas h-200"><canvas id="chartFinanceiroGateway"></canvas></div>
    </div>
</div>
@endif

{{-- ======================== FROTA & OCUPAÇÃO ======================== --}}
@if($indicador['slug'] === 'frota')
<div class="kpi-grid">
    <div class="kpi-card c-indigo">
        <div class="kpi-top"><div><p class="kpi-label">Ocupação Geral</p><p class="kpi-value" id="kpiOcupacao">--</p></div><div class="kpi-icon"><i class="fas fa-chart-pie"></i></div></div>
        <p class="kpi-sub">Frota no período</p>
    </div>
</div>
<div class="charts-grid">
    <div class="chart-card span-8">
        <div class="chart-overlay" id="overlayFrota"><div class="spinner"></div></div>
        <div class="card-head"><div><p class="card-title">Veículos por grupo de frota</p></div></div>
        <div class="chart-canvas h-210"><canvas id="chartFrota"></canvas></div>
    </div>
    <div class="chart-card span-4">
        <div class="chart-overlay" id="overlayOcupacao"><div class="spinner"></div></div>
        <div class="card-head">
            <div><p class="card-title">Ocupação da frota</p><p class="card-sub">por grupo no período</p></div>
            <div class="text-right"><p class="metric-big" id="ocupacaoGeral">--</p><div class="metric-delta delta-up">ocupação geral</div></div>
        </div>
        <div class="gauge-list" id="gaugeList"></div>
    </div>
</div>
@endif

{{-- ======================== MOVIMENTAÇÕES ======================== --}}
@if($indicador['slug'] === 'movimentacoes')
<div class="kpi-grid">
    <div class="kpi-card c-amber">
        <div class="kpi-top"><div><p class="kpi-label">Total Movimentações</p><p class="kpi-value" id="kpiMovimentacoes">--</p></div><div class="kpi-icon"><i class="fas fa-exchange-alt"></i></div></div>
        <p class="kpi-sub" id="kpiPeriodo">--</p>
    </div>
</div>
<div class="charts-grid">
    <div class="chart-card span-6">
        <div class="chart-overlay" id="overlayMovStatus"><div class="spinner"></div></div>
        <div class="card-head"><div><p class="card-title">Movimentações por status</p><p class="card-sub">Total: <span id="totalMovimentacoes">--</span></p></div></div>
        <div class="legend-row" id="legMovStatus"></div>
        <div class="chart-canvas h-210"><canvas id="chartMovStatus"></canvas></div>
    </div>
</div>
@endif

{{-- ======================== SATISFAÇÃO ======================== --}}
@if($indicador['slug'] === 'satisfacao')
<div class="kpi-grid">
    <div class="kpi-card c-teal">
        <div class="kpi-top"><div><p class="kpi-label">Satisfação</p><p class="kpi-value" id="kpiSatisfacao">--</p></div><div class="kpi-icon"><i class="fas fa-smile"></i></div></div>
        <p class="kpi-sub" id="kpiSatisfacaoSub">CSAT médio</p>
    </div>
    <div class="kpi-card c-indigo">
        <div class="kpi-top"><div><p class="kpi-label">NPS</p><p class="kpi-value" id="kpiNps">--</p></div><div class="kpi-icon"><i class="fas fa-thumbs-up"></i></div></div>
        <p class="kpi-sub" id="kpiNpsSub">Promotores vs detratores</p>
    </div>
</div>
<div class="charts-grid">
    <div class="chart-card span-8">
        <div class="chart-overlay" id="overlaySatisfacaoLinha"><div class="spinner"></div></div>
        <div class="card-head">
            <div><p class="card-title">Satisfação ao longo do tempo</p><p class="card-sub" id="subSatisfacao">--</p></div>
            <div class="text-right"><p class="metric-big" id="totalSatisfacao">--</p><div class="metric-delta delta-up" id="mediaFacilidadeSatisfacao">facilidade média --</div></div>
        </div>
        <div class="legend-row">
            <span class="leg-item"><span class="leg-dot" style="background:#0891b2;"></span>CSAT</span>
            <span class="leg-item"><span class="leg-dot" style="background:#7c3aed;"></span>Facilidade</span>
            <span class="leg-item"><span class="leg-dot" style="background:#16a34a;"></span>NPS médio</span>
        </div>
        <div class="chart-canvas h-220"><canvas id="chartSatisfacaoLinha"></canvas></div>
    </div>
    <div class="chart-card span-4">
        <div class="chart-overlay" id="overlaySatisfacaoDistribuicao"><div class="spinner"></div></div>
        <div class="card-head"><div><p class="card-title">Distribuição NPS</p><p class="card-sub">Promotores, neutros e detratores</p></div></div>
        <div class="legend-row" id="legSatisfacaoNps"></div>
        <div class="chart-canvas h-220"><canvas id="chartSatisfacaoDistribuicao"></canvas></div>
    </div>
    <div class="chart-card span-6">
        <div class="chart-overlay" id="overlaySatisfacaoEstacoes"><div class="spinner"></div></div>
        <div class="card-head"><div><p class="card-title">Satisfação por estação</p><p class="card-sub">Média de satisfação geral</p></div></div>
        <div class="chart-canvas h-210"><canvas id="chartSatisfacaoEstacoes"></canvas></div>
    </div>
    <div class="chart-card span-6">
        <div class="chart-overlay" id="overlaySatisfacaoGrupos"><div class="spinner"></div></div>
        <div class="card-head"><div><p class="card-title">Satisfação por grupo</p><p class="card-sub">Média de satisfação geral</p></div></div>
        <div class="chart-canvas h-210"><canvas id="chartSatisfacaoGrupos"></canvas></div>
    </div>
    <div class="chart-card span-12">
        <div class="card-head"><div><p class="card-title">Rankings de satisfação</p><p class="card-sub">Veículos, marcas, modelos e respondentes</p></div></div>
        <div class="legend-row mb-3">
            <button type="button" class="btn-secondary js-sat-tab" data-sat-tab="veiculos">Veículos</button>
            <button type="button" class="btn-secondary js-sat-tab" data-sat-tab="marcas">Marcas</button>
            <button type="button" class="btn-secondary js-sat-tab" data-sat-tab="modelos">Modelos</button>
            <button type="button" class="btn-secondary js-sat-tab" data-sat-tab="responsaveis">Respondentes</button>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light"><tr><th>Dimensão</th><th class="text-center">Respostas</th><th class="text-center">CSAT</th><th class="text-center">Facilidade</th><th class="text-center">NPS</th></tr></thead>
                <tbody id="satisfacaoRankingBody"><tr><td colspan="5" class="text-center text-muted py-4">Selecione uma dimensão.</td></tr></tbody>
            </table>
        </div>
    </div>
</div>
@endif

{{-- ======================== ANÁLISE AVANÇADA ======================== --}}
@if($indicador['slug'] === 'analise_avancada')
<div class="kpi-grid">
    <div class="kpi-card c-emerald">
        <div class="kpi-top"><div><p class="kpi-label">Saúde média</p><p class="kpi-value" id="kpiAnaliseSaude">--</p></div><div class="kpi-icon"><i class="fas fa-heartbeat"></i></div></div>
        <p class="kpi-sub">Índice composto da frota</p>
    </div>
    <div class="kpi-card c-teal">
        <div class="kpi-top"><div><p class="kpi-label">Eficiência média</p><p class="kpi-value" id="kpiAnaliseEficiencia">--</p></div><div class="kpi-icon"><i class="fas fa-bolt"></i></div></div>
        <p class="kpi-sub">Receita vs custo real</p>
    </div>
    <div class="kpi-card c-rose">
        <div class="kpi-top"><div><p class="kpi-label">Prejuízo</p><p class="kpi-value" id="kpiAnalisePrejuizo">--</p></div><div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div></div>
        <p class="kpi-sub">Veículos com lucro real negativo</p>
    </div>
    <div class="kpi-card c-indigo">
        <div class="kpi-top"><div><p class="kpi-label">Proteção FIPE</p><p class="kpi-value" id="kpiAnaliseProtecao">--</p></div><div class="kpi-icon"><i class="fas fa-shield-alt"></i></div></div>
        <p class="kpi-sub">Comprados abaixo da FIPE</p>
    </div>
    <div class="kpi-card c-amber">
        <div class="kpi-top"><div><p class="kpi-label">Custo real</p><p class="kpi-value" id="kpiAnaliseCustoReal">--</p></div><div class="kpi-icon"><i class="fas fa-sack-dollar"></i></div></div>
        <p class="kpi-sub">Operacional + depreciação</p>
    </div>
    <div class="kpi-card c-teal">
        <div class="kpi-top"><div><p class="kpi-label">Lucro real</p><p class="kpi-value" id="kpiAnaliseLucroReal">--</p></div><div class="kpi-icon"><i class="fas fa-chart-line"></i></div></div>
        <p class="kpi-sub">Receita menos custo real</p>
    </div>
</div>
<p class="analysis-note mb-3">Os indicadores abaixo usam receita, multas, sinistros, danos e depreciação estimada para mostrar o desempenho real de cada veículo.</p>
<div class="charts-grid">
    <div class="chart-card span-6">
        <div class="chart-overlay" id="overlayAnaliseLucrativos"><div class="spinner"></div></div>
        <div class="card-head"><div><p class="card-title">Veículos mais lucrativos</p><p class="card-sub">Baseado em lucro real</p></div></div>
        <div class="chart-canvas h-220"><canvas id="chartAnaliseLucrativos"></canvas></div>
    </div>
    <div class="chart-card span-6">
        <div class="chart-overlay" id="overlayAnalisePrejuizo"><div class="spinner"></div></div>
        <div class="card-head"><div><p class="card-title">Veículos com prejuízo</p><p class="card-sub">Lucro real negativo no período</p></div></div>
        <div class="chart-canvas h-220"><canvas id="chartAnalisePrejuizo"></canvas></div>
    </div>
    <div class="chart-card span-4">
        <div class="chart-overlay" id="overlayAnaliseProtecao"><div class="spinner"></div></div>
        <div class="card-head"><div><p class="card-title">Proteção contra depreciação</p><p class="card-sub">Comprado abaixo da FIPE</p></div></div>
        <div class="legend-row">
            <span class="leg-item"><span class="leg-dot" style="background:#16a34a;"></span>Protegidos</span>
            <span class="leg-item"><span class="leg-dot" style="background:#94a3b8;"></span>Sem proteção</span>
        </div>
        <div class="chart-canvas h-220"><canvas id="chartAnaliseProtecao"></canvas></div>
    </div>
    <div class="chart-card span-8">
        <div class="card-head"><div><p class="card-title">Relatório automático com recomendações</p><p class="card-sub">Sinalizações geradas a partir dos números do período</p></div></div>
        <div class="recommendation-list" id="analiseRecomendacoes">
            <div class="recommendation-item is-info"><p class="recommendation-title">Carregando recomendações</p><p class="recommendation-desc">A análise será preenchida após aplicar o filtro de período.</p></div>
        </div>
    </div>
    <div class="chart-card span-12">
        <div class="card-head"><div><p class="card-title">Diagnóstico detalhado por veículo</p><p class="card-sub">Receita, custo real, lucro real, saúde e proteção FIPE</p></div></div>
        <div class="table-responsive">
            <table class="table table-sm table-hover mb-0 analysis-table">
                <thead class="thead-light">
                    <tr>
                        <th>Veículo</th><th>Grupo</th>
                        <th class="text-right">Receita</th><th class="text-right">Custo operacional</th>
                        <th class="text-right">Depreciação</th><th class="text-right">Custo real</th>
                        <th class="text-right">Lucro real</th><th class="text-center">Eficiência</th>
                        <th class="text-center">Saúde</th><th class="text-center">FIPE</th><th>Recomendação</th>
                    </tr>
                </thead>
                <tbody id="analiseDetalhadaBody">
                    <tr><td colspan="11" class="text-center text-muted py-4">Aplique o filtro para visualizar o diagnóstico.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.js"></script>
<script>
const CHART_DATA_URL = '{{ route('home.chart-data') }}';
const HOME_SECTIONS  = @json($homeSections);
const IND_SLUG       = '{{ $indicador['slug'] }}';

const textColor = 'rgba(0,0,0,0.45)';
const gridColor = 'rgba(0,0,0,0.06)';
const movColors = ['#0891b2','#059669','#d97706','#e11d48','#7c3aed','#0ea5e9','#f59e0b','#10b981'];
const finStatusColors  = ['#16a34a','#d97706','#dc2626'];
const finGatewayColors = ['#0ea5e9','#6366f1','#14b8a6','#f59e0b','#ec4899','#84cc16'];

const baseOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false }, tooltip: { cornerRadius: 8, padding: 10 } }
};

let simboloMoeda = 'R$';
let satisfacaoRankings = {};
let satisfacaoRankingAtivo = 'veiculos';

// ---- Chart refs ----
let chartReservas = null, chartReservasOrigem = null;
let chartMovStatus = null, chartFrota = null;
let chartFinanceiroLinha = null, chartFinanceiroStatus = null, chartFinanceiroGateway = null;
let chartSatisfacaoLinha = null, chartSatisfacaoDistribuicao = null;
let chartSatisfacaoEstacoes = null, chartSatisfacaoGrupos = null;
let chartAnaliseLucrativos = null, chartAnalisePrejuizo = null, chartAnaliseProtecao = null;

function formatInputDate(date) {
    const y = date.getFullYear(), m = String(date.getMonth()+1).padStart(2,'0'), d = String(date.getDate()).padStart(2,'0');
    return `${y}-${m}-${d}`;
}
function defaultDates() {
    const hoje = new Date();
    return { inicio: formatInputDate(new Date(hoje.getFullYear(), hoje.getMonth()-11, 1)), fim: formatInputDate(hoje) };
}
function formatMoney(v) {
    return `${simboloMoeda} ${Number(v||0).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2})}`;
}
function formatPercent(v) {
    return `${Number(v||0).toLocaleString('pt-BR',{minimumFractionDigits:1,maximumFractionDigits:1})}%`;
}
function formatShortDate(iso) {
    if(!iso||iso.indexOf('-')===-1) return '--';
    const [y,m,d] = iso.split('-').map(Number);
    return new Date(y,m-1,d).toLocaleDateString('pt-BR',{day:'2-digit',month:'short'});
}
function escapeHtml(v) {
    return String(v??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}
function analiseStatusClass(s) {
    return {prejuizo:'danger',critico:'danger',atencao:'warning',saudavel:'success'}[s] || 'info';
}
function analiseStatusLabel(s) {
    return {prejuizo:'Prejuízo',critico:'Crítico',atencao:'Atenção',saudavel:'Saudável'}[s] || 'Indefinido';
}

function showOverlays() {
    document.querySelectorAll('.chart-overlay').forEach(el => el.classList.remove('hidden'));
}
function hideOverlays() {
    document.querySelectorAll('.chart-overlay').forEach(el => el.classList.add('hidden'));
}

function fetchChartData() {
    const inicio = document.getElementById('data_inicio')?.value;
    const fim    = document.getElementById('data_fim')?.value;
    const btn    = document.getElementById('btnFiltrar');
    const estacaoId = document.getElementById('estacao_id')?.value || '0';

    if (!inicio || !fim || inicio > fim) { alert('Selecione um período válido.'); return; }
    if (btn) btn.disabled = true;
    showOverlays();

    const url = `${CHART_DATA_URL}?data_inicio=${encodeURIComponent(inicio)}&data_fim=${encodeURIComponent(fim)}&estacao_id=${encodeURIComponent(estacaoId)}`;
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => { if(!r.ok) throw new Error(); return r.json(); })
        .then(data => {
            simboloMoeda = data.simboloMoeda || 'R$';
            renderAll(data, inicio, fim);
        })
        .catch(() => alert('Erro ao carregar dados. Tente novamente.'))
        .finally(() => { if(btn) btn.disabled = false; hideOverlays(); });
}

function renderAll(data, inicio, fim) {
    if (IND_SLUG === 'reservas') {
        renderKpisReservas(data, inicio, fim);
        if(HOME_SECTIONS.reservas) {
            renderReservas(data.dadosReservas, data.totalReservas, inicio, fim);
            renderReservasOrigem(data.reservasOrigemDistribuicao, data.reservasOrigemResumo);
            renderReservasColaboradores(data.reservasColaboradoresRanking);
        }
    } else if (IND_SLUG === 'financeiro') {
        renderKpisFinanceiro(data);
        if(HOME_SECTIONS.financeiro) {
            renderFinanceiroLinha(data.financeiroEvolucao, data.financeiroResumo, inicio, fim);
            renderFinanceiroStatus(data.financeiroStatus, data.financeiroResumo);
            renderFinanceiroGateway(data.financeiroGateway, data.financeiroResumo);
        }
    } else if (IND_SLUG === 'frota') {
        renderKpisFrota(data);
        if(HOME_SECTIONS.frota)    renderFrota(data.veiculosPorGrupo);
        if(HOME_SECTIONS.ocupacao) renderOcupacao(data.ocupacaoFrota, data.ocupacaoGeral);
    } else if (IND_SLUG === 'movimentacoes') {
        renderKpisMov(data, inicio, fim);
        if(HOME_SECTIONS.movimentacoes) renderMovStatus(data.movPorStatus, data.totalMovimentacoes);
    } else if (IND_SLUG === 'satisfacao') {
        renderKpisSatisfacao(data);
        if(HOME_SECTIONS.satisfacao) renderSatisfacao(data, inicio, fim);
    } else if (IND_SLUG === 'analise_avancada') {
        if(HOME_SECTIONS.analise_avancada) renderAnaliseAvancada(data);
    }
}

// ---- KPIs por seção ----
function renderKpisReservas(data, inicio, fim) {
    const el = (id) => document.getElementById(id);
    const r  = data?.reservasOrigemResumo || {};
    if(el('kpiTotalReservas'))    el('kpiTotalReservas').textContent   = Number(data?.totalReservas||0).toLocaleString('pt-BR');
    if(el('kpiPeriodo'))          el('kpiPeriodo').textContent          = `${formatShortDate(inicio)} - ${formatShortDate(fim)}`;
    if(el('kpiReservasOrigem'))   el('kpiReservasOrigem').textContent   = `${Number(r?.percentual_site||0).toLocaleString('pt-BR',{minimumFractionDigits:1,maximumFractionDigits:1})}%`;
    if(el('kpiReservasOrigemSub'))el('kpiReservasOrigemSub').textContent = `Site ${Number(r?.site||0).toLocaleString('pt-BR')} • Colaborador ${Number(r?.colaborador||0).toLocaleString('pt-BR')}`;
}
function renderKpisFinanceiro(data) {
    const el = (id) => document.getElementById(id);
    const r  = data?.financeiroResumo || {};
    if(el('kpiTotalFinanceiro')) el('kpiTotalFinanceiro').textContent = formatMoney(r?.total||0);
    if(el('kpiTicketMedio'))     el('kpiTicketMedio').textContent     = `Ticket médio: ${formatMoney(r?.ticket_medio||0)}`;
    if(el('kpiQtdTransacoes'))   el('kpiQtdTransacoes').textContent   = Number(r?.qtd_transacoes||0).toLocaleString('pt-BR');
    if(el('kpiTaxaAprovacao'))   el('kpiTaxaAprovacao').textContent   = `Taxa de aprovação: ${formatPercent(r?.taxa_aprovacao||0)}`;
}
function renderKpisFrota(data) {
    const el = document.getElementById('kpiOcupacao');
    if(el) el.textContent = formatPercent(data?.ocupacaoGeral||0);
}
function renderKpisMov(data, inicio, fim) {
    const el = (id) => document.getElementById(id);
    if(el('kpiMovimentacoes')) el('kpiMovimentacoes').textContent = Number(data?.totalMovimentacoes||0).toLocaleString('pt-BR');
    if(el('kpiPeriodo'))       el('kpiPeriodo').textContent       = `${formatShortDate(inicio)} - ${formatShortDate(fim)}`;
}
function renderKpisSatisfacao(data) {
    const el  = (id) => document.getElementById(id);
    const sat = data?.satisfacaoResumo || {};
    if(el('kpiSatisfacao'))    el('kpiSatisfacao').textContent    = sat.total_respostas ? Number(sat.media_geral||0).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2}) : '--';
    if(el('kpiSatisfacaoSub')) el('kpiSatisfacaoSub').textContent = sat.total_respostas ? `${Number(sat.total_respostas).toLocaleString('pt-BR')} respostas` : 'Sem respostas no período';
    if(el('kpiNps'))           el('kpiNps').textContent           = sat.total_respostas ? Number(sat.nps||0).toLocaleString('pt-BR',{minimumFractionDigits:1,maximumFractionDigits:1}) : '--';
    if(el('kpiNpsSub'))        el('kpiNpsSub').textContent        = sat.total_respostas ? `${Number(sat.promotores||0).toLocaleString('pt-BR')} prom. • ${Number(sat.detratores||0).toLocaleString('pt-BR')} detr.` : '--';
}

// ---- Render Reservas ----
function renderReservas(dados, total, inicio, fim) {
    const canvas = document.getElementById('chartReservas'); if(!canvas) return;
    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    const conf = dados?.confirmadas?.length ? dados.confirmadas : [0];
    const canc = dados?.cancelamentos?.length ? dados.cancelamentos : [0];
    const tend = dados?.tendencia?.length ? dados.tendencia : [0];
    document.getElementById('totalReservas').textContent = Number(total||0).toLocaleString('pt-BR');
    document.getElementById('subReservas').textContent = `${formatShortDate(inicio)} - ${formatShortDate(fim)}`;
    if(chartReservas) chartReservas.destroy();
    chartReservas = new Chart(canvas, { type:'bar', data:{ labels, datasets:[
        {label:'Confirmadas', data:conf, backgroundColor:'#0891b2', borderRadius:5, order:2},
        {label:'Cancelamentos', data:canc, backgroundColor:'#bae6fd', borderRadius:5, order:3},
        {label:'Tendência', data:tend, type:'line', borderColor:'#06b6d4', backgroundColor:'transparent', borderWidth:2, pointRadius:0, tension:0.35, order:1}
    ]}, options:{...baseOpts, scales:{x:{stacked:true, ticks:{color:textColor,font:{size:11},autoSkip:false}, grid:{display:false}}, y:{stacked:true, ticks:{color:textColor,font:{size:11}}, grid:{color:gridColor}, border:{display:false}}}} });
}
function renderReservasOrigem(dados, resumo) {
    const canvas = document.getElementById('chartReservasOrigem'); if(!canvas) return;
    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    const valores = dados?.data?.length ? dados.data : [0];
    const palette = ['#0ea5e9','#6366f1','#94a3b8'];
    const leg = document.getElementById('legReservasOrigem');
    if(leg) leg.innerHTML = labels.map((l,i) => `<span class="leg-item"><span class="leg-dot" style="background:${palette[i%palette.length]};"></span>${escapeHtml(l)} ${Number(valores[i]||0).toLocaleString('pt-BR')}</span>`).join('');
    const sub = document.getElementById('subReservasOrigem');
    if(sub && resumo) sub.textContent = `Site ${Number(resumo.site||0).toLocaleString('pt-BR')} • Colaborador ${Number(resumo.colaborador||0).toLocaleString('pt-BR')} • Sem origem ${Number(resumo.sem_origem||0).toLocaleString('pt-BR')}`;
    if(chartReservasOrigem) chartReservasOrigem.destroy();
    chartReservasOrigem = new Chart(canvas, { type:'doughnut', data:{labels, datasets:[{data:valores, backgroundColor:palette, borderWidth:0, hoverOffset:8}]}, options:{...baseOpts, cutout:'68%', layout:{padding:4}} });
}
function renderReservasColaboradores(dados) {
    const tbody = document.getElementById('reservasColaboradoresBody'); if(!tbody) return;
    const rows = Array.isArray(dados?.rows) ? dados.rows : [];
    const total = rows.reduce((a,r) => a+Number(r?.total||0), 0);
    const top = rows[0]||null;
    const elTotal = document.getElementById('totalReservasColaboradores');
    if(elTotal) elTotal.textContent = Number(total||0).toLocaleString('pt-BR');
    const elTop = document.getElementById('subTopColaborador');
    if(elTop) elTop.textContent = top ? `${escapeHtml(top.label||'--')} • ${Number(top.total||0).toLocaleString('pt-BR')} reservas` : 'Sem dados no período';
    if(!rows.length) { tbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted py-4">Sem dados no período.</td></tr>'; return; }
    tbody.innerHTML = rows.map((row,i) => `<tr><td><strong>${escapeHtml(row?.label||'--')}</strong>${i===0?'<span class="badge badge-success ml-2">Top</span>':''}</td><td class="text-center">${Number(row?.total||0).toLocaleString('pt-BR')}</td></tr>`).join('');
}

// ---- Render Movimentações ----
function renderMovStatus(dados, total) {
    const canvas = document.getElementById('chartMovStatus'); if(!canvas) return;
    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    const valores = dados?.data?.length ? dados.data : [0];
    document.getElementById('totalMovimentacoes').textContent = Number(total||0).toLocaleString('pt-BR');
    const leg = document.getElementById('legMovStatus');
    if(leg) leg.innerHTML = labels.map((l,i) => `<span class="leg-item"><span class="leg-dot" style="background:${movColors[i%movColors.length]};"></span>${l} ${Number(valores[i]||0).toLocaleString('pt-BR')}</span>`).join('');
    if(chartMovStatus) chartMovStatus.destroy();
    chartMovStatus = new Chart(canvas, { type:'doughnut', data:{labels, datasets:[{data:valores, backgroundColor:labels.map((_,i)=>movColors[i%movColors.length]), borderWidth:0, hoverOffset:8}]}, options:{...baseOpts, cutout:'68%', layout:{padding:4}} });
}

// ---- Render Frota ----
function renderFrota(dados) {
    const canvas = document.getElementById('chartFrota'); if(!canvas) return;
    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    const valores = dados?.data?.length ? dados.data : [0];
    const colors = ['#0891b2','#059669','#d97706','#7c3aed','#e11d48','#0ea5e9'];
    if(chartFrota) chartFrota.destroy();
    chartFrota = new Chart(canvas, { type:'bar', data:{labels, datasets:[{data:valores, backgroundColor:labels.map((_,i)=>colors[i%colors.length]), borderRadius:5, borderSkipped:false}]}, options:{...baseOpts, indexAxis:'y', scales:{x:{ticks:{color:textColor,font:{size:11},stepSize:1}, grid:{color:gridColor}, border:{display:false}}, y:{ticks:{color:textColor,font:{size:11}}, grid:{display:false}}}} });
}
function renderOcupacao(frota, geral) {
    const elGeral = document.getElementById('ocupacaoGeral'), list = document.getElementById('gaugeList');
    if(elGeral) elGeral.textContent = `${Number(geral||0)}%`;
    if(!list) return;
    if(!Array.isArray(frota)||!frota.length) { list.innerHTML='<div class="empty-gauge">Sem dados de ocupação.</div>'; return; }
    list.innerHTML = frota.map(item => `<div class="gauge-row"><div class="gauge-label"><span>${item.label}</span><span>${item.pct}%</span></div><div class="gauge-bar-bg"><div class="gauge-bar-fill" style="width:${item.pct}%;background:${item.color};"></div></div></div>`).join('');
}

// ---- Render Financeiro ----
function renderFinanceiroLinha(dados, resumo, inicio, fim) {
    const canvas = document.getElementById('chartFinanceiroLinha'); if(!canvas) return;
    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    const valores = dados?.valores?.length ? dados.valores : [0];
    const quantidades = dados?.quantidades?.length ? dados.quantidades : [0];
    document.getElementById('totalFinanceiro').textContent = formatMoney(resumo?.total||0);
    document.getElementById('ticketMedioFinanceiro').textContent = `ticket médio ${formatMoney(resumo?.ticket_medio||0)}`;
    document.getElementById('subFinanceiro').textContent = `${formatShortDate(inicio)} - ${formatShortDate(fim)}`;
    if(chartFinanceiroLinha) chartFinanceiroLinha.destroy();
    chartFinanceiroLinha = new Chart(canvas, { data:{labels, datasets:[
        {type:'bar', label:'Receita', data:valores, yAxisID:'y', backgroundColor:'#0ea5e9', borderRadius:5, borderSkipped:false},
        {type:'line', label:'Transações', data:quantidades, yAxisID:'y1', borderColor:'#6366f1', backgroundColor:'transparent', pointBackgroundColor:'#6366f1', pointRadius:2, borderWidth:2, tension:0.35}
    ]}, options:{...baseOpts, scales:{x:{ticks:{color:textColor,font:{size:11},autoSkip:false}, grid:{display:false}}, y:{position:'left', ticks:{color:textColor,font:{size:11}, callback:v=>formatMoney(v)}, grid:{color:gridColor}, border:{display:false}}, y1:{position:'right', ticks:{color:textColor,font:{size:11}}, grid:{display:false}, border:{display:false}}}} });
}
function renderFinanceiroStatus(dados, resumo) {
    const canvas = document.getElementById('chartFinanceiroStatus'); if(!canvas) return;
    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    const valores = dados?.data?.length ? dados.data : [0];
    document.getElementById('taxaAprovacaoFinanceiro').textContent = formatPercent(resumo?.taxa_aprovacao||0);
    const leg = document.getElementById('legFinStatus');
    if(leg) leg.innerHTML = labels.map((l,i) => `<span class="leg-item"><span class="leg-dot" style="background:${finStatusColors[i%finStatusColors.length]};"></span>${l} ${formatMoney(valores[i]||0)}</span>`).join('');
    if(chartFinanceiroStatus) chartFinanceiroStatus.destroy();
    chartFinanceiroStatus = new Chart(canvas, { type:'doughnut', data:{labels, datasets:[{data:valores, backgroundColor:finStatusColors.slice(0,labels.length), borderWidth:0, hoverOffset:8}]}, options:{...baseOpts, cutout:'66%', layout:{padding:4}} });
}
function renderFinanceiroGateway(dados, resumo) {
    const canvas = document.getElementById('chartFinanceiroGateway'); if(!canvas) return;
    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    const valores = dados?.data?.length ? dados.data : [0];
    document.getElementById('qtdTransacoesFinanceiro').textContent = Number(resumo?.qtd_transacoes||0).toLocaleString('pt-BR');
    const leg = document.getElementById('legFinGateway');
    if(leg) leg.innerHTML = labels.map((l,i) => `<span class="leg-item"><span class="leg-dot" style="background:${finGatewayColors[i%finGatewayColors.length]};"></span>${l} ${formatMoney(valores[i]||0)}</span>`).join('');
    if(chartFinanceiroGateway) chartFinanceiroGateway.destroy();
    chartFinanceiroGateway = new Chart(canvas, { type:'bar', data:{labels, datasets:[{data:valores, backgroundColor:finGatewayColors.slice(0,labels.length), borderRadius:5, borderSkipped:false}]}, options:{...baseOpts, scales:{x:{ticks:{color:textColor,font:{size:11}}, grid:{display:false}}, y:{ticks:{color:textColor,font:{size:11}, callback:v=>formatMoney(v)}, grid:{color:gridColor}, border:{display:false}}}} });
}

// ---- Render Satisfação ----
function renderSatisfacao(data, inicio, fim) {
    renderSatisfacaoLinha(data?.satisfacaoEvolucao, data?.satisfacaoResumo, inicio, fim);
    renderSatisfacaoDistribuicao(data?.satisfacaoDistribuicao);
    renderSatisfacaoRankingTabs(data);
    renderSatisfacaoBar('chartSatisfacaoEstacoes', data?.satisfacaoEstacoes, (c) => { chartSatisfacaoEstacoes = c; }, chartSatisfacaoEstacoes);
    renderSatisfacaoBar('chartSatisfacaoGrupos', data?.satisfacaoGrupos, (c) => { chartSatisfacaoGrupos = c; }, chartSatisfacaoGrupos);
}
function renderSatisfacaoLinha(dados, resumo, inicio, fim) {
    const canvas = document.getElementById('chartSatisfacaoLinha'); if(!canvas) return;
    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    if(chartSatisfacaoLinha) chartSatisfacaoLinha.destroy();
    document.getElementById('totalSatisfacao').textContent = Number(resumo?.total_respostas||0).toLocaleString('pt-BR');
    document.getElementById('subSatisfacao').textContent = `${formatShortDate(inicio)} - ${formatShortDate(fim)}`;
    document.getElementById('mediaFacilidadeSatisfacao').textContent = `facilidade média ${Number(resumo?.media_facilidade||0).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2})}`;
    chartSatisfacaoLinha = new Chart(canvas, { data:{labels, datasets:[
        {type:'line', label:'CSAT', data:dados?.satisfacao_geral||[0], borderColor:'#0891b2', backgroundColor:'transparent', borderWidth:2, pointRadius:2, tension:0.35, yAxisID:'y'},
        {type:'line', label:'Facilidade', data:dados?.facilidade_reserva||[0], borderColor:'#7c3aed', backgroundColor:'transparent', borderWidth:2, pointRadius:2, tension:0.35, yAxisID:'y'},
        {type:'line', label:'NPS', data:dados?.nps||[0], borderColor:'#16a34a', backgroundColor:'transparent', borderWidth:2, pointRadius:2, tension:0.35, yAxisID:'y1'}
    ]}, options:{...baseOpts, scales:{x:{ticks:{color:textColor,font:{size:11}}, grid:{display:false}}, y:{position:'left', ticks:{color:textColor,font:{size:11}}, grid:{color:gridColor}, border:{display:false}}, y1:{position:'right', ticks:{color:textColor,font:{size:11}}, grid:{display:false}, border:{display:false}}}} });
}
function renderSatisfacaoDistribuicao(dados) {
    const canvas = document.getElementById('chartSatisfacaoDistribuicao'); if(!canvas) return;
    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    const valores = dados?.data?.length ? dados.data : [0];
    const colors = ['#16a34a','#f59e0b','#dc2626'];
    const leg = document.getElementById('legSatisfacaoNps');
    if(leg) leg.innerHTML = labels.map((l,i) => `<span class="leg-item"><span class="leg-dot" style="background:${colors[i%colors.length]};"></span>${l} ${Number(valores[i]||0).toLocaleString('pt-BR')}</span>`).join('');
    if(chartSatisfacaoDistribuicao) chartSatisfacaoDistribuicao.destroy();
    chartSatisfacaoDistribuicao = new Chart(canvas, { type:'doughnut', data:{labels, datasets:[{data:valores, backgroundColor:labels.map((_,i)=>colors[i%colors.length]), borderWidth:0, hoverOffset:8}]}, options:{...baseOpts, cutout:'66%', layout:{padding:4}} });
}
function renderSatisfacaoBar(canvasId, dados, setRef, ref) {
    const canvas = document.getElementById(canvasId); if(!canvas) return;
    const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
    const rows = Array.isArray(dados?.data) ? dados.data : [];
    const valores = rows.length ? rows.map(r => Number(r.media_geral||0)) : [0];
    const colors = labels.map((_,i) => ['#0891b2','#059669','#7c3aed','#d97706','#e11d48'][i%5]);
    if(ref) ref.destroy();
    setRef(new Chart(canvas, { type:'bar', data:{labels, datasets:[{label:'Satisfação média', data:valores, backgroundColor:colors, borderRadius:6, borderSkipped:false}]}, options:{...baseOpts, indexAxis:'y', scales:{x:{ticks:{color:textColor,font:{size:11}}, grid:{color:gridColor}, border:{display:false}}, y:{ticks:{color:textColor,font:{size:11}}, grid:{display:false}}}} }));
}
function renderSatisfacaoRankingTabs(data) {
    satisfacaoRankings = { veiculos: data?.satisfacaoVeiculos?.data||[], marcas: data?.satisfacaoMarcas?.data||[], modelos: data?.satisfacaoModelos?.data||[], responsaveis: data?.satisfacaoResponsaveis?.data||[] };
    renderSatisfacaoRankingTable(satisfacaoRankingAtivo);
}
function renderSatisfacaoRankingTable(tab) {
    satisfacaoRankingAtivo = tab;
    const tbody = document.getElementById('satisfacaoRankingBody'); if(!tbody) return;
    const rows = satisfacaoRankings[tab] || [];
    if(!rows.length) { tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">Sem respostas para esta dimensão.</td></tr>'; return; }
    tbody.innerHTML = rows.map(r => `<tr><td>${escapeHtml(r.label)}</td><td class="text-center">${Number(r.respostas||0).toLocaleString('pt-BR')}</td><td class="text-center">${Number(r.media_geral||0).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2})}</td><td class="text-center">${Number(r.media_facilidade||0).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2})}</td><td class="text-center">${Number(r.media_nps||0).toLocaleString('pt-BR',{minimumFractionDigits:2,maximumFractionDigits:2})}</td></tr>`).join('');
}

// ---- Render Análise Avançada ----
function renderAnaliseAvancada(data) {
    const el = (id) => document.getElementById(id);
    const r  = data?.analiseAvancadaResumo || {};
    if(el('kpiAnaliseSaude'))       el('kpiAnaliseSaude').textContent       = formatPercent(r?.saude_media||0);
    if(el('kpiAnaliseEficiencia'))  el('kpiAnaliseEficiencia').textContent  = formatPercent(r?.eficiencia_media||0);
    if(el('kpiAnalisePrejuizo'))    el('kpiAnalisePrejuizo').textContent    = Number(r?.veiculos_prejuizo||0).toLocaleString('pt-BR');
    if(el('kpiAnaliseProtecao'))    el('kpiAnaliseProtecao').textContent    = Number(r?.veiculos_protegidos||0).toLocaleString('pt-BR');
    if(el('kpiAnaliseCustoReal'))   el('kpiAnaliseCustoReal').textContent   = formatMoney(r?.custo_real_total||0);
    if(el('kpiAnaliseLucroReal'))   el('kpiAnaliseLucroReal').textContent   = formatMoney(r?.lucro_real_total||0);

    function renderBar(canvasId, ref, dados, color) {
        const canvas = document.getElementById(canvasId); if(!canvas) return ref;
        const labels = dados?.labels?.length ? dados.labels : ['Sem dados'];
        const valores = dados?.data?.length ? dados.data : [0];
        if(ref) ref.destroy();
        return new Chart(canvas, { type:'bar', data:{labels, datasets:[{data:valores, backgroundColor:color, borderRadius:6, borderSkipped:false}]}, options:{...baseOpts, indexAxis:'y', scales:{x:{ticks:{color:textColor,font:{size:11}, callback:v=>formatMoney(v)}, grid:{color:gridColor}, border:{display:false}}, y:{ticks:{color:textColor,font:{size:11}}, grid:{display:false}}}} });
    }
    chartAnaliseLucrativos = renderBar('chartAnaliseLucrativos', chartAnaliseLucrativos, data?.analiseAvancadaLucrativos, '#059669');
    chartAnalisePrejuizo   = renderBar('chartAnalisePrejuizo',   chartAnalisePrejuizo,   data?.analiseAvancadaPrejuizo,   '#e11d48');

    const canvasProt = document.getElementById('chartAnaliseProtecao');
    if(canvasProt) {
        const dp = data?.analiseAvancadaProtecao || {};
        if(chartAnaliseProtecao) chartAnaliseProtecao.destroy();
        chartAnaliseProtecao = new Chart(canvasProt, { type:'doughnut', data:{labels:dp?.labels||['Sem dados'], datasets:[{data:dp?.data||[0], backgroundColor:['#16a34a','#94a3b8'], borderWidth:0, hoverOffset:8}]}, options:{...baseOpts, cutout:'68%', layout:{padding:4}} });
    }

    // Recomendações
    const cont = document.getElementById('analiseRecomendacoes');
    if(cont) {
        const rows = Array.isArray(data?.analiseAvancadaRecomendacoes) ? data.analiseAvancadaRecomendacoes : [];
        cont.innerHTML = rows.length ? rows.map(item => `<div class="recommendation-item is-${item?.tipo||'info'}"><p class="recommendation-title">${escapeHtml(item?.titulo||'')}</p><p class="recommendation-desc">${escapeHtml(item?.descricao||'')}</p></div>`).join('') : '<div class="recommendation-item is-info"><p class="recommendation-title">Sem recomendações</p><p class="recommendation-desc">Não há alertas para o período selecionado.</p></div>';
    }

    // Tabela detalhada
    const tbody = document.getElementById('analiseDetalhadaBody');
    if(tbody) {
        const rows = Array.isArray(data?.analiseAvancadaDetalhes) ? data.analiseAvancadaDetalhes : [];
        if(!rows.length) { tbody.innerHTML = '<tr><td colspan="11" class="text-center text-muted py-4">Nenhum veículo encontrado.</td></tr>'; return; }
        tbody.innerHTML = rows.map(row => `<tr>
            <td><div class="font-weight-bold">${escapeHtml(row?.label||'--')}</div><small class="text-muted">${escapeHtml(row?.sub_label||'')}</small></td>
            <td>${escapeHtml(row?.grupo||'--')}</td>
            <td class="text-right">${formatMoney(row?.receita||0)}</td>
            <td class="text-right">${formatMoney(row?.custo_operacional||0)}</td>
            <td class="text-right">${formatMoney(row?.depreciacao||0)}</td>
            <td class="text-right">${formatMoney(row?.custo_real||0)}</td>
            <td class="text-right">${formatMoney(row?.lucro_real||0)}</td>
            <td class="text-center">${formatPercent(row?.eficiencia||0)}</td>
            <td class="text-center">${formatPercent(row?.saude||0)}</td>
            <td class="text-center">${Number(row?.protecao_percentual||0)>0?`<span class="status-chip is-success">${formatPercent(row?.protecao_percentual||0)}</span>`:'<span class="status-chip is-warning">0,0%</span>'}</td>
            <td><span class="status-chip is-${analiseStatusClass(row?.status)}">${escapeHtml(analiseStatusLabel(row?.status))}</span><br><small class="text-muted">${escapeHtml(row?.recomendacao||'--')}</small></td>
        </tr>`).join('');
    }
}

// ---- Init ----
document.getElementById('btnFiltrar')?.addEventListener('click', fetchChartData);
document.getElementById('btnHoje')?.addEventListener('click', () => {
    const hoje = formatInputDate(new Date());
    document.getElementById('data_inicio').value = hoje;
    document.getElementById('data_fim').value = hoje;
    fetchChartData();
});
document.getElementById('btn12m')?.addEventListener('click', () => {
    const d = defaultDates();
    document.getElementById('data_inicio').value = d.inicio;
    document.getElementById('data_fim').value = d.fim;
    fetchChartData();
});
document.getElementById('estacao_id')?.addEventListener('change', fetchChartData);

document.querySelectorAll('.js-sat-tab').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.js-sat-tab').forEach(el => el.classList.remove('active'));
        btn.classList.add('active');
        renderSatisfacaoRankingTable(btn.getAttribute('data-sat-tab')||'veiculos');
    });
});
const firstSatTab = document.querySelector('.js-sat-tab[data-sat-tab="veiculos"]');
if(firstSatTab) firstSatTab.classList.add('active');

// Auto-load
const d = defaultDates();
document.getElementById('data_inicio').value = d.inicio;
document.getElementById('data_fim').value = d.fim;
fetchChartData();
</script>
@stop
