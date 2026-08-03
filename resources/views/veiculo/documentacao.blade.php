@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[[
        'name' => 'Veículos',
        'url' => route('veiculo.index')
    ], [
        'name' => 'Documentação'
    ]]" />
@stop

@section('content')
    @php
        $tipos = [
            'ipva' => 'IPVA',
            'licenciamento' => 'Licenciamento',
            'seguro' => 'Seguro',
        ];

        $totalDocumentacoes = method_exists($documentacoes, 'total') ? $documentacoes->total() : $documentacoes->count();
        $ativas = $documentacoes->where('status', 'ativo')->count();
        $inativas = $documentacoes->where('status', '!=', 'ativo')->count();
        $comPagas = $documentacoes->filter(fn ($doc) => $doc->parcelas->where('status', 'pago')->count() > 0)->count();
    @endphp

    <div class="container-fluid px-0 vehicle-show-page">
        <div class="module-card mb-3">
            <div class="module-card-header vehicle-hero-compact">
                <div class="module-header-left">
                    <div class="module-header-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <div class="module-header-eyebrow">Veículos · Documentação</div>
                        <h1 class="module-header-title">Documentações cadastradas</h1>
                        <p class="module-header-sub">
                            Controle IPVA, licenciamento e seguro por veículo com parcelas, status e lançamentos vinculados.
                        </p>
                    </div>
                </div>

                <div class="module-header-right">
                    <a href="{{ route('veiculo.documentacao.create') }}" class="btn-xp btn-xp--sm">
                        <i class="fas fa-plus"></i> Nova documentação
                    </a>
                    <a href="{{ route('veiculo.index') }}" class="btn-xp btn-xp--outline-light btn-xp--sm">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-xl-3 col-md-6 mb-2">
                <div class="kpi-card kpi-total">
                    <div class="kpi-icon"><i class="fas fa-file-alt"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Registros</span>
                        <strong class="kpi-value">{{ $totalDocumentacoes }}</strong>
                        <small class="kpi-subtitle">Documentações no período</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-2">
                <div class="kpi-card kpi-available">
                    <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Ativas</span>
                        <strong class="kpi-value">{{ $ativas }}</strong>
                        <small class="kpi-subtitle">Mantidas no fluxo atual</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-2">
                <div class="kpi-card kpi-partial">
                    <div class="kpi-icon"><i class="fas fa-coins"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Com parcelas pagas</span>
                        <strong class="kpi-value">{{ $comPagas }}</strong>
                        <small class="kpi-subtitle">Ao menos uma parcela paga</small>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-2">
                <div class="kpi-card kpi-unavailable">
                    <div class="kpi-icon"><i class="fas fa-ban"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Inativas</span>
                        <strong class="kpi-value">{{ $inativas }}</strong>
                        <small class="kpi-subtitle">Aguardam reativação</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="config-card shadow-sm border-0">
            <div class="vs-panel-head border-bottom">
                <div>
                    <span class="vs-panel-head__eyebrow">Lista operacional</span>
                    <h4 class="vs-panel-head__title mb-1">Documentações por veículo</h4>
                    <p class="vs-panel-head__sub mb-0">Clique em uma linha para expandir as parcelas e acompanhar o status de cada vencimento.</p>
                </div>
                <span class="vs-badge-soft is-warning">
                    <i class="fas fa-list"></i>
                    {{ $totalDocumentacoes }} registro(s)
                </span>
            </div>

            <div class="doc-filters">
                <form method="GET" action="{{ route('veiculo.documentacao') }}" class="doc-filters__form">
                    <div class="row">
                        <div class="col-lg-5 col-md-6 mb-2">
                            <label class="doc-filter-label" for="filter_q">Buscar</label>
                            <input type="text" id="filter_q" name="q" class="form-control doc-filter-control" value="{{ $filtroBusca ?? '' }}" placeholder="Documento, descrição, veículo ou placa">
                        </div>
                        <div class="col-lg-3 col-md-3 mb-2">
                            <label class="doc-filter-label" for="filter_tipo">Tipo</label>
                            <select id="filter_tipo" name="tipo" class="form-control doc-filter-control">
                                <option value="">Todos</option>
                                <option value="ipva" {{ ($filtroTipo ?? '') === 'ipva' ? 'selected' : '' }}>IPVA</option>
                                <option value="licenciamento" {{ ($filtroTipo ?? '') === 'licenciamento' ? 'selected' : '' }}>Licenciamento</option>
                                <option value="seguro" {{ ($filtroTipo ?? '') === 'seguro' ? 'selected' : '' }}>Seguro</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-3 mb-2">
                            <label class="doc-filter-label" for="filter_status">Status</label>
                            <select id="filter_status" name="status" class="form-control doc-filter-control">
                                <option value="">Todos</option>
                                <option value="ativo" {{ ($filtroStatus ?? '') === 'ativo' ? 'selected' : '' }}>Ativo</option>
                                <option value="inativo" {{ ($filtroStatus ?? '') === 'inativo' ? 'selected' : '' }}>Inativo</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-12 mb-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-xp btn-xp--sm w-100 mr-2">
                                <i class="fas fa-filter mr-1"></i> Filtrar
                            </button>
                            <a href="{{ route('veiculo.documentacao') }}" class="btn btn-light btn-sm w-100">
                                Limpar
                            </a>
                        </div>
                    </div>

                    <div class="doc-filters__advanced-toggle">
                        <button
                            class="btn btn-link p-0 doc-filters__advanced-button"
                            type="button"
                            data-toggle="collapse"
                            data-target="#docFiltersAdvanced"
                            aria-expanded="{{ request()->filled('veiculo') || request()->filled('placa') || request()->filled('valor_min') || request()->filled('valor_max') || request()->boolean('somente_vencidas') ? 'true' : 'false' }}"
                            aria-controls="docFiltersAdvanced"
                        >
                            <i class="fas fa-sliders-h mr-1"></i> Filtro avançado
                        </button>
                    </div>

                    <div class="collapse {{ request()->filled('veiculo') || request()->filled('placa') || request()->filled('valor_min') || request()->filled('valor_max') || request()->boolean('somente_vencidas') ? 'show' : '' }}" id="docFiltersAdvanced">
                        <div class="doc-filters__advanced mt-3">
                            <div class="row">
                                <div class="col-lg-4 col-md-6 mb-2">
                                    <label class="doc-filter-label" for="filter_veiculo">Veículo</label>
                                    <input type="text" id="filter_veiculo" name="veiculo" class="form-control doc-filter-control" value="{{ $filtroVeiculo ?? '' }}" placeholder="Nome do veículo">
                                </div>
                                <div class="col-lg-3 col-md-6 mb-2">
                                    <label class="doc-filter-label" for="filter_placa">Placa</label>
                                    <input type="text" id="filter_placa" name="placa" class="form-control doc-filter-control" value="{{ $filtroPlaca ?? '' }}" placeholder="Ex.: ABC1D23">
                                </div>
                                <div class="col-lg-2 col-md-6 mb-2">
                                    <label class="doc-filter-label" for="filter_valor_min">Valor mín.</label>
                                    <input type="text" id="filter_valor_min" name="valor_min" class="form-control doc-filter-control" value="{{ $filtroValorMin ?? '' }}" placeholder="0,00">
                                </div>
                                <div class="col-lg-2 col-md-6 mb-2">
                                    <label class="doc-filter-label" for="filter_valor_max">Valor máx.</label>
                                    <input type="text" id="filter_valor_max" name="valor_max" class="form-control doc-filter-control" value="{{ $filtroValorMax ?? '' }}" placeholder="0,00">
                                </div>
                                <div class="col-lg-1 col-md-12 mb-2 d-flex align-items-end justify-content-lg-end">
                                    <div class="custom-control custom-switch mb-2">
                                        <input type="checkbox" class="custom-control-input" id="filter_somente_vencidas" name="somente_vencidas" value="1" {{ !empty($filtroSomenteVencidas) ? 'checked' : '' }}>
                                        <label class="custom-control-label doc-filter-switch" for="filter_somente_vencidas">Só vencidas</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive vehicle-table-wrap">
                    <table class="table table-hover mb-0 vehicle-list-table">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Veículo</th>
                                <th>Parcelas</th>
                                <th>Valor</th>
                                <th>Status</th>
                                <th class="text-center">Detalhes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($documentacoes as $doc)
                                @php
                                    $collapseId = 'doc-' . $doc->id;
                                    $statusCount = $doc->parcelas->countBy('status');
                                    $statusClass = $doc->status === 'ativo' ? 'status-badge--green' : 'status-badge--gray';
                                @endphp
                                <tr>
                                    <td>
                                        <div class="doc-main-title">{{ $tipos[$doc->tipo] ?? strtoupper((string) $doc->tipo) }}</div>
                                        <div class="doc-subtitle">{{ $doc->descricao ?: 'Sem descrição' }}</div>
                                    </td>
                                    <td>
                                        <div class="vehicle-cell">
                                            <strong>{{ $doc->veiculo?->nome ?? '—' }}</strong>
                                            <small>{{ $doc->veiculo?->placa_licenca ?? 'Sem placa' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="doc-pill">{{ $doc->parcelas_total }} parcela(s)</span>
                                        @if($statusCount->get('pendente'))
                                            <span class="badge badge-warning ml-1">{{ $statusCount->get('pendente') }} pendentes</span>
                                        @endif
                                        @if($statusCount->get('pago'))
                                            <span class="badge badge-success ml-1">{{ $statusCount->get('pago') }} pagas</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="doc-money">R$ {{ number_format((float) $doc->valor_parcela, 2, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <span class="status-badge {{ $statusClass }}">
                                            {{ ucfirst((string) $doc->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-xs btn-outline-primary doc-toggle-btn" type="button" data-toggle="collapse" data-target="#{{ $collapseId }}" aria-expanded="false" aria-controls="{{ $collapseId }}">
                                            <i class="fas fa-chevron-down"></i>
                                        </button>
                                        <a href="{{ route('veiculo.documentacao.edit', $doc->id) }}" class="btn btn-xs btn-outline-secondary ml-1">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    </td>
                                </tr>
                                <tr class="collapse" id="{{ $collapseId }}">
                                    <td colspan="6" class="doc-detail-cell">
                                        <div class="doc-detail-wrap">
                                            <div class="doc-detail-head">
                                                <div>
                                                    <strong>Parcelas do documento</strong>
                                                    <span>Parcelamento e pagamentos vinculados ao fluxo financeiro.</span>
                                                </div>
                                                <span class="vs-badge-soft">
                                                    <i class="fas fa-receipt"></i>
                                                    {{ $doc->parcelas->count() }} parcela(s)
                                                </span>
                                            </div>

                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover mb-0 vehicle-subtable">
                                                    <thead>
                                                        <tr>
                                                            <th>Parcela</th>
                                                            <th>Mês</th>
                                                            <th>Vencimento</th>
                                                            <th>Pagamento</th>
                                                            <th>Status</th>
                                                            <th class="text-right">Ação</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($doc->parcelas as $parcela)
                                                            @php
                                                                $parcelaStatusClass = [
                                                                    'pago' => 'status-badge--green',
                                                                    'atrasado' => 'status-badge--red',
                                                                    'pendente' => 'status-badge--amber',
                                                                ][(string) $parcela->status] ?? 'status-badge--gray';
                                                            @endphp
                                                            <tr>
                                                                <td class="font-weight-bold">{{ $parcela->numero_parcela }}/{{ $doc->parcelas_total }}</td>
                                                                <td>{{ optional($parcela->data_vencimento)->format('m/Y') ?? '-' }}</td>
                                                                <td>{{ optional($parcela->data_vencimento)->format('d/m/Y') ?? '-' }}</td>
                                                                <td>{{ $parcela->data_pagamento ? $parcela->data_pagamento->format('d/m/Y') : '-' }}</td>
                                                                <td>
                                                                    <span class="status-badge {{ $parcelaStatusClass }}">
                                                                        {{ ucfirst((string) $parcela->status) }}
                                                                    </span>
                                                                </td>
                                                                <td class="text-right">
                                                                    @if($parcela->status !== 'pago')
                                                                        <button
                                                                            type="button"
                                                                            class="btn btn-xs btn-xp btn-xp--sm btn-pagar-parcela-documentacao"
                                                                            data-toggle="modal"
                                                                            data-target="#modalPagarParcelaDocumentacao"
                                                                            data-action="{{ route('veiculo.documentacao.parcela.pagar', $parcela->id) }}"
                                                                            data-parcela-label="{{ $doc->descricao ?: ($tipos[$doc->tipo] ?? strtoupper((string) $doc->tipo)) }} - Parcela {{ $parcela->numero_parcela }}/{{ $doc->parcelas_total }}"
                                                                        >
                                                                            <i class="fas fa-credit-card mr-1"></i>Pagar
                                                                        </button>
                                                                    @else
                                                                        <span class="text-muted small">Pago</span>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="6" class="text-center text-muted py-4">Nenhuma parcela gerada.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        Nenhuma documentação cadastrada.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @include('veiculo.documentacao-pagamento-modal')
@stop

@section('css')
<style>
    .vehicle-show-page {
        padding-bottom: 8px;
    }

    .module-card {
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(12, 20, 40, .10);
        background: #fff;
    }

    .module-card-header {
        background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
        padding: 24px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        position: relative;
        overflow: hidden;
    }

    .module-card-header::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255, 255, 255, 0.055) 1px, transparent 1px);
        background-size: 20px 20px;
        pointer-events: none;
    }

    .module-card-header::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4338ca, #0ea5e9, #10b981);
    }

    .module-header-left {
        display: flex;
        align-items: center;
        gap: 16px;
        position: relative;
        z-index: 1;
    }

    .module-header-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #60a5fa;
        flex-shrink: 0;
        backdrop-filter: blur(8px);
    }

    .module-header-eyebrow {
        font-size: .62rem;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: rgba(148, 163, 184, .8);
        display: block;
        margin-bottom: 3px;
    }

    .module-header-title {
        font-size: 1.45rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 3px;
        letter-spacing: -.02em;
    }

    .module-header-sub {
        margin: 0;
        font-size: .82rem;
        color: rgba(148, 163, 184, .9);
    }

    .module-header-right {
        position: relative;
        z-index: 1;
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .btn-xp {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: linear-gradient(135deg, #4338ca, #6366f1);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 9px 16px;
        font-size: .82rem;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
        transition: all .18s;
        letter-spacing: -.01em;
        box-shadow: 0 4px 12px rgba(67, 56, 202, .25);
    }

    .btn-xp:hover {
        opacity: .88;
        transform: translateY(-1px);
        color: #fff;
        text-decoration: none;
    }

    .btn-xp--sm {
        padding: 6px 12px;
        font-size: .78rem;
        border-radius: 8px;
    }

    .btn-xp--outline-light {
        background: rgba(255, 255, 255, .12);
        border: 1px solid rgba(255, 255, 255, .25);
        color: rgba(255, 255, 255, .9);
        box-shadow: none;
    }

    .btn-xp--outline-light:hover {
        background: rgba(255, 255, 255, .2);
        color: #fff;
    }

    .kpi-card {
        width: 100%;
        border: 0;
        border-radius: 16px;
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 16px;
        color: #0f172a;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .08);
        min-height: 96px;
        background: #fff;
    }

    .kpi-card .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex: 0 0 auto;
        color: #fff;
    }

    .kpi-meta .kpi-label {
        display: block;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .03em;
        opacity: .9;
        font-weight: 700;
    }

    .kpi-meta .kpi-value {
        display: block;
        font-size: 28px;
        line-height: 1.05;
        margin-top: 2px;
    }

    .kpi-subtitle {
        display: block;
        color: #475569;
        margin-top: 2px;
    }

    .kpi-total { background: linear-gradient(135deg, #e2e8f0, #cbd5e1); }
    .kpi-total .kpi-icon { background: #94a3b8; }
    .kpi-available { background: linear-gradient(135deg, #dcfce7, #bbf7d0); }
    .kpi-available .kpi-icon { background: #16a34a; }
    .kpi-partial { background: linear-gradient(135deg, #fef3c7, #fde68a); }
    .kpi-partial .kpi-icon { background: #f59e0b; }
    .kpi-unavailable { background: linear-gradient(135deg, #fee2e2, #fecaca); }
    .kpi-unavailable .kpi-icon { background: #dc2626; }

    .config-card {
        background: #fff;
        border: 1px solid #e2e9f4;
        border-radius: 18px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(12, 20, 40, .06);
    }

    .vs-panel-head {
        padding: 18px 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        background: #fff;
    }

    .vs-panel-head__eyebrow {
        display: inline-block;
        margin-bottom: 6px;
        font-size: .62rem;
        font-weight: 800;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: #64748b;
    }

    .vs-panel-head__title {
        margin: 0 0 6px;
        font-size: 1.08rem;
        font-weight: 800;
        color: #0c1428;
    }

    .vs-panel-head__sub {
        margin: 0;
        color: #64748b;
        font-size: .84rem;
        max-width: 680px;
    }

    .vs-badge-soft {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 12px;
        background: #eef2ff;
        color: #3730a3;
        border: 1px solid #c7d2fe;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .doc-filters {
        padding: 16px 20px 6px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }

    .doc-filter-label {
        display: block;
        margin-bottom: 6px;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: #475569;
    }

    .doc-filter-control {
        min-height: 42px;
        border-radius: 10px;
        border-color: #dbe4f0;
        box-shadow: none;
    }

    .doc-filter-control:focus {
        border-color: rgba(67, 56, 202, .45);
        box-shadow: 0 0 0 .2rem rgba(67, 56, 202, .10);
    }

    .doc-filters__advanced-toggle {
        margin-top: 8px;
    }

    .doc-filters__advanced-button {
        font-size: .82rem;
        font-weight: 700;
        color: #4338ca;
        text-decoration: none;
    }

    .doc-filters__advanced-button:hover {
        color: #312e81;
        text-decoration: none;
    }

    .doc-filters__advanced {
        padding: 14px 14px 6px;
        border-radius: 14px;
        background: #fff;
        border: 1px solid #e2e8f0;
    }

    .doc-filter-switch {
        font-size: .76rem;
        font-weight: 700;
        color: #475569;
        padding-top: 2px;
    }

    .vehicle-table-wrap {
        border-top: 1px solid #edf2f7;
        max-height: 74vh;
        overflow: auto;
    }

    .vehicle-list-table {
        border-collapse: separate;
        border-spacing: 0;
    }

    .vehicle-list-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #f8fafc;
        border-bottom: 1px solid #cbd5e1;
        font-size: 11px;
        text-transform: uppercase;
        color: #334155;
        letter-spacing: .02em;
        vertical-align: middle;
    }

    .vehicle-list-table tbody td {
        vertical-align: middle;
        background: #fff;
        border-top: 1px solid #edf2f7;
    }

    .vehicle-list-table tbody tr:hover td {
        background: #f8fafc;
    }

    .doc-main-title {
        font-weight: 800;
        color: #0f172a;
    }

    .doc-subtitle {
        color: #64748b;
        font-size: .78rem;
        margin-top: 2px;
    }

    .vehicle-cell strong {
        display: block;
        color: #0f172a;
    }

    .vehicle-cell small {
        color: #64748b;
    }

    .doc-pill {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        background: #eef2ff;
        color: #3730a3;
        font-size: .75rem;
        font-weight: 700;
    }

    .doc-money {
        color: #0f172a;
        font-weight: 800;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: .73rem;
        font-weight: 700;
        letter-spacing: .02em;
    }

    .status-badge--green { background: #dcfce7; color: #166534; }
    .status-badge--amber { background: #fef3c7; color: #92400e; }
    .status-badge--red { background: #fee2e2; color: #991b1b; }
    .status-badge--gray { background: #f1f5f9; color: #475569; }

    .doc-toggle-btn {
        min-width: 34px;
    }

    .doc-detail-cell {
        background: #f8fafc !important;
        padding: 0 !important;
    }

    .doc-detail-wrap {
        padding: 16px;
        border-top: 1px solid #e2e8f0;
    }

    .doc-detail-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        margin-bottom: 12px;
    }

    .doc-detail-head strong {
        display: block;
        color: #0c1428;
        font-size: .92rem;
        font-weight: 800;
    }

    .doc-detail-head span {
        display: block;
        color: #64748b;
        font-size: .78rem;
        margin-top: 2px;
    }

    .vehicle-subtable thead th {
        font-size: .72rem;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #475569;
        background: #fff;
    }

    .vehicle-subtable tbody tr:hover td {
        background: #f8fafc;
    }

    @media (max-width: 991px) {
        .module-card-header {
            align-items: flex-start;
            flex-direction: column;
        }

        .module-header-right {
            width: 100%;
        }

        .module-header-right .btn-xp {
            flex: 1 1 auto;
            justify-content: center;
        }
    }
</style>
@stop

@section('js')
<script>
    (function () {
        var form = document.getElementById('formPagarParcelaDocumentacao');
        var inputData = document.getElementById('modal_data_pagamento_documentacao');
        var label = document.getElementById('modalPagarParcelaDocumentacaoLabel');

        if (!form || !inputData || !label) {
            return;
        }

        document.querySelectorAll('.btn-pagar-parcela-documentacao').forEach(function (button) {
            button.addEventListener('click', function () {
                var action = button.getAttribute('data-action');
                var parcelaLabel = button.getAttribute('data-parcela-label') || 'Parcela';

                if (action) {
                    form.setAttribute('action', action);
                }

                inputData.value = new Date().toISOString().slice(0, 10);
                label.textContent = parcelaLabel + '. Informe a data do pagamento.';
            });
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            var submitButton = form.querySelector('button[type="submit"]');
            var originalHtml = submitButton ? submitButton.innerHTML : '';

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...';
            }

            fetch(form.getAttribute('action'), {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: new FormData(form)
            })
                .then(function (response) {
                    return response.json().then(function (payload) {
                        return { ok: response.ok, payload: payload };
                    });
                })
                .then(function (result) {
                    if (!result.ok || !result.payload || !result.payload.success) {
                        throw new Error(result.payload?.message || 'Não foi possível registrar o pagamento.');
                    }

                    if (window.jQuery && $('#modalPagarParcelaDocumentacao').modal) {
                        $('#modalPagarParcelaDocumentacao').modal('hide');
                    }

                    var message = result.payload.message || 'Pagamento registrado com sucesso.';

                    if (window.Swal && typeof window.Swal.fire === 'function') {
                        window.Swal.fire({
                            icon: 'success',
                            title: 'Sucesso',
                            text: message,
                            confirmButtonText: 'Recarregar página',
                            allowOutsideClick: false
                        }).then(function () {
                            window.location.reload();
                        });
                    } else {
                        window.location.reload();
                    }
                })
                .catch(function (error) {
                    if (window.Swal && typeof window.Swal.fire === 'function') {
                        window.Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: error.message || 'Falha ao registrar o pagamento.'
                        });
                    } else {
                        alert(error.message || 'Falha ao registrar o pagamento.');
                    }
                })
                .finally(function () {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.innerHTML = originalHtml;
                    }
                });
        });
    })();
</script>
@stop
