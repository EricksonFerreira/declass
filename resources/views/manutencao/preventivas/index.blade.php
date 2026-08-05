@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[[ 'name' => 'Veículos', 'url' => route('veiculo.index') ], [ 'name' => 'Manutenção preventiva' ]]" />
@stop

@section('content')
    @php
        $totalPendencias = (int) ($stats['pendentes'] ?? 0);
        $totalAtrasadas = (int) ($stats['atrasadas'] ?? 0);
        $totalExecutadas = (int) ($stats['executadas'] ?? 0);
    @endphp

    <div class="container-fluid px-0 vehicle-show-page manutencao-preventiva-shell">
        <div class="module-card mb-3">
            <div class="module-card-header manutencao-hero-compact">
                <div class="module-header-left">
                    <div class="module-header-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div>
                        <div class="module-header-eyebrow">Veículos · Manutenção preventiva</div>
                        <h1 class="module-header-title">Manutenções preventivas</h1>
                        <p class="module-header-sub">Monitore pendências, priorize atrasos e acompanhe o histórico recente de execução.</p>
                    </div>
                </div>

                <div class="module-header-right">
                    <a href="{{ route('manutencao.planos.index') }}" class="btn-xp btn-xp--sm">
                        <i class="fas fa-clipboard-list"></i> Planos
                    </a>
                    {{-- <a href="{{ route('veiculo.index') }}" class="btn-xp btn-xp--outline-light btn-xp--sm">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a> --}}
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-xl-4 col-md-6 mb-2">
                <div class="kpi-card kpi-total">
                    <div class="kpi-icon"><i class="fas fa-clipboard-check"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Pendências</span>
                        <strong class="kpi-value">{{ number_format($totalPendencias, 0, ',', '.') }}</strong>
                        <small class="kpi-subtitle">Itens aguardando ação</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-2">
                <div class="kpi-card kpi-unavailable">
                    <div class="kpi-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Atrasadas</span>
                        <strong class="kpi-value">{{ number_format($totalAtrasadas, 0, ',', '.') }}</strong>
                        <small class="kpi-subtitle">Prioridade máxima</small>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-6 mb-2">
                <div class="kpi-card kpi-available">
                    <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                    <div class="kpi-meta">
                        <span class="kpi-label">Execuções</span>
                        <strong class="kpi-value">{{ number_format($totalExecutadas, 0, ',', '.') }}</strong>
                        <small class="kpi-subtitle">Histórico concluído</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="config-card shadow-sm border-0 mb-3">
            <div class="vs-panel-head border-bottom">
                <div>
                    <span class="vs-panel-head__eyebrow">Filtros operacionais</span>
                    <h4 class="vs-panel-head__title mb-1">Refinar acompanhamento</h4>
                    <p class="vs-panel-head__sub mb-0">Filtre por veículo, item e status para encontrar o próximo ponto de ação com mais rapidez.</p>
                </div>
                <span class="vs-badge-soft is-warning">
                    <i class="fas fa-filter"></i>
                    Filtro rápido
                </span>
            </div>
            <div class="doc-filters">
                <form method="GET" action="{{ route('manutencao.preventivas.index') }}" class="doc-filters__form">
                    <div class="row">
                        <div class="col-lg-5 col-md-6 col-12 mb-2">
                            <label class="doc-filter-label" for="filter_veiculo">Veículo</label>
                            <select id="filter_veiculo" name="veiculo_id" class="form-control doc-filter-control">
                                <option value="">Todos os veículos</option>
                                @foreach($veiculos as $veiculo)
                                    <option value="{{ $veiculo->id }}" {{ (int) request('veiculo_id') === (int) $veiculo->id ? 'selected' : '' }}>
                                        {{ $veiculo->nome }} - {{ $veiculo->placa_licenca ?? 'sem placa' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6 col-12 mb-2">
                            <label class="doc-filter-label" for="filter_item">Item</label>
                            <select id="filter_item" name="item_id" class="form-control doc-filter-control">
                                <option value="">Todos os itens</option>
                                @foreach($itens as $item)
                                    <option value="{{ $item->id }}" {{ (int) request('item_id') === (int) $item->id ? 'selected' : '' }}>
                                        {{ $item->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6 col-12 mb-2">
                            <label class="doc-filter-label" for="filter_status">Status</label>
                            <select id="filter_status" name="status" class="form-control doc-filter-control">
                                <option value="">Todos</option>
                                <option value="pendente" {{ request('status') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                                <option value="atrasado" {{ request('status') === 'atrasado' ? 'selected' : '' }}>Atrasado</option>
                                <option value="concluida" {{ request('status') === 'concluida' ? 'selected' : '' }}>Concluída</option>
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6 col-12 mb-2 d-flex align-items-end">
                            <button class="btn btn-xp btn-xp--sm w-100" type="submit">
                                <i class="fas fa-filter"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-xl-6 mb-3 mb-xl-0">
                <div class="config-card shadow-sm border-0 h-100">
                    <div class="vs-panel-head border-bottom">
                        <div>
                            <span class="vs-panel-head__eyebrow">Lista operacional</span>
                            <h4 class="vs-panel-head__title mb-1">Pendências</h4>
                            <p class="vs-panel-head__sub mb-0">Itens aguardando ação, com os valores agrupados para leitura rápida.</p>
                        </div>
                        <span class="vs-badge-soft">
                            <i class="fas fa-list"></i>
                            {{ $pendencias->count() }} na página
                        </span>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive vehicle-table-wrap">
                            <table class="table table-hover mb-0 vehicle-list-table">
                                <thead>
                                    <tr>
                                        <th>Veículo</th>
                                        <th>Item</th>
                                        <th>Valores</th>
                                        <th>Status</th>
                                        <th class="d-none d-lg-table-cell">Responsáveis</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendencias as $pendencia)
                                        <tr>
                                            <td>
                                                @if($pendencia->veiculo)
                                                    <a href="{{ route('veiculo.show', $pendencia->veiculo->id) }}" class="font-weight-bold d-block">
                                                        {{ $pendencia->veiculo->nome }}
                                                    </a>
                                                    <small class="text-muted">{{ $pendencia->veiculo->placa_licenca ?? 'sem placa' }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $pendencia->item?->nome ?? ('Item #' . $pendencia->item_id) }}</td>
                                            <td>
                                                <div class="table-value-stack">
                                                    <span>{{ $pendencia->data_prevista ? \Carbon\Carbon::parse($pendencia->data_prevista)->format('d/m/Y') : '-' }}</span>
                                                    <small>KM {{ $pendencia->km_previsto !== null ? number_format((float) $pendencia->km_previsto, 0, ',', '.') : '-' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="status-badge {{ $pendencia->status === 'atrasado' ? 'status-badge--red' : 'status-badge--amber' }}">
                                                    {{ ucfirst($pendencia->status) }}
                                                </span>
                                            </td>
                                            <td class="d-none d-lg-table-cell">
                                                <div class="table-value-stack">
                                                    <span>{{ $pendencia->usuarioInclusao?->nome ?? '-' }}</span>
                                                    <small>{{ $pendencia->usuarioAtualizacao?->nome ?? '-' }}</small>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Nenhuma pendência encontrada.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if($pendencias->hasPages())
                        <div class="card-footer d-flex justify-content-end">
                            {{ $pendencias->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-xl-6">
                <div class="config-card shadow-sm border-0 h-100">
                    <div class="vs-panel-head border-bottom">
                        <div>
                            <span class="vs-panel-head__eyebrow">Histórico recente</span>
                            <h4 class="vs-panel-head__title mb-1">Execuções realizadas</h4>
                            <p class="vs-panel-head__sub mb-0">Registros já concluídos, com os campos de valor agrupados em uma única coluna.</p>
                        </div>
                        <span class="vs-badge-soft">
                            <i class="fas fa-history"></i>
                            Últimos lançamentos
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive vehicle-table-wrap">
                            <table class="table table-hover mb-0 vehicle-list-table">
                                <thead>
                                    <tr>
                                        <th>Veículo</th>
                                        <th>Item</th>
                                        <th>Valores</th>
                                        <th>OS</th>
                                        <th class="d-none d-lg-table-cell">Responsáveis</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($execucoesRecentes as $execucao)
                                        <tr>
                                            <td>
                                                @if($execucao->veiculo)
                                                    <a href="{{ route('veiculo.show', $execucao->veiculo->id) }}" class="font-weight-bold d-block">
                                                        {{ $execucao->veiculo->nome }}
                                                    </a>
                                                    <small class="text-muted">{{ $execucao->veiculo->placa_licenca ?? 'sem placa' }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $execucao->item?->nome ?? ('Item #' . $execucao->item_id) }}</td>
                                            <td>
                                                <div class="table-value-stack">
                                                    <span>{{ $execucao->data_execucao ? \Carbon\Carbon::parse($execucao->data_execucao)->format('d/m/Y') : '-' }}</span>
                                                    <small>KM {{ $execucao->km_execucao !== null ? number_format((float) $execucao->km_execucao, 0, ',', '.') : '-' }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $execucao->ordemServico?->numero ?? ($execucao->ordem_servico_id ? ('OS #' . $execucao->ordem_servico_id) : '-') }}</td>
                                            <td class="d-none d-lg-table-cell">
                                                <div class="table-value-stack">
                                                    <span>{{ $execucao->usuarioInclusao?->nome ?? '-' }}</span>
                                                    <small>{{ $execucao->usuarioAtualizacao?->nome ?? '-' }}</small>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Nenhuma execução recente.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .manutencao-preventiva-shell { padding-bottom: 8px; }
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
    .config-card {
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        border: 1px solid #e2e9f4;
        box-shadow: 0 2px 12px rgba(12, 20, 40, .06);
    }
    .vs-panel-head {
        background: #fff;
        padding: 18px 20px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        border-bottom: 1px solid #e7edf6;
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
        padding: 16px 20px 12px;
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
    .vehicle-table-wrap {
        border-top: 1px solid #edf2f7;
        max-height: 74vh;
        overflow: auto;
    }
    .vehicle-list-table {
        border-collapse: separate;
        border-spacing: 0;
    }
    .btn-xp--outline-light {
    background: rgba(255, 255, 255, .12); !important;
    border: 1px solid rgba(255, 255, 255, .25);
    color: rgba(255, 255, 255, .9);
    box-shadow: none;
}
.btn-xp--sm {
    padding: 6px 12px;
    font-size: .78rem;
    border-radius: 8px;
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
    .kpi-unavailable { background: linear-gradient(135deg, #fee2e2, #fecaca); }
    .kpi-unavailable .kpi-icon { background: #dc2626; }
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
    .table-value-stack {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .table-value-stack span {
        font-weight: 700;
        color: #0f172a;
    }
    .table-value-stack small {
        color: #64748b;
        font-size: .76rem;
    }
    @media (max-width: 991.98px) {
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
        .vs-panel-head,
        .doc-filters { padding-left: 14px; padding-right: 14px; }
        .doc-filters { padding-top: .9rem; padding-bottom: .9rem; }
        .vehicle-list-table thead th,
        .vehicle-list-table tbody td { font-size: .86rem; }
    }
</style>
@endsection
