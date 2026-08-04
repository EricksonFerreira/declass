@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[[ 'name' => 'Manutenções Preventivas' ]]" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="manutencao-preventiva-shell">
        <div class="manutencao-preventiva-hero card shadow-sm border-0 mb-3">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center" style="gap:1rem;">
                    <div>
                        <div class="manutencao-preventiva-eyebrow">Manutenção preventiva</div>
                        <h3 class="mb-1">Painel de decisões rápidas</h3>
                        <p class="text-muted mb-0">Monitore pendências, priorize atrasos e acompanhe o que foi executado recentemente.</p>
                    </div>
                    <div class="d-flex flex-wrap" style="gap:.5rem;">
                        <a href="{{ route('manutencao.planos.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-clipboard-list mr-1"></i> Planos
                        </a>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 col-md-4 mb-3 mb-md-0">
                        <div class="manutencao-kpi manutencao-kpi--warning">
                            <div class="manutencao-kpi__label">Pendências</div>
                            <div class="manutencao-kpi__value">{{ number_format((int) ($stats['pendentes'] ?? 0), 0, ',', '.') }}</div>
                            <div class="manutencao-kpi__hint">Itens aguardando ação</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4 mb-3 mb-md-0">
                        <div class="manutencao-kpi manutencao-kpi--danger">
                            <div class="manutencao-kpi__label">Atrasadas</div>
                            <div class="manutencao-kpi__value">{{ number_format((int) ($stats['atrasadas'] ?? 0), 0, ',', '.') }}</div>
                            <div class="manutencao-kpi__hint">Prioridade alta para decisão</div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="manutencao-kpi manutencao-kpi--success">
                            <div class="manutencao-kpi__label">Execuções</div>
                            <div class="manutencao-kpi__value">{{ number_format((int) ($stats['executadas'] ?? 0), 0, ',', '.') }}</div>
                            <div class="manutencao-kpi__hint">Histórico recente concluído</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 manutencao-card mb-3">
            <div class="card-header manutencao-card__header">
                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:.75rem;">
                    <div>
                        <div class="manutencao-card__eyebrow">Filtros</div>
                        <h5 class="mb-0">Refine a análise</h5>
                    </div>
                    <span class="text-muted small">Use os filtros para encontrar o item certo mais rápido.</span>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('manutencao.preventivas.index') }}">
                    <div class="row">
                        <div class="col-12 col-md-5 mb-3">
                            <label class="small mb-1">Veículo</label>
                            <select name="veiculo_id" class="form-control">
                                <option value="">Todos os veículos</option>
                                @foreach($veiculos as $veiculo)
                                    <option value="{{ $veiculo->id }}" {{ (int) request('veiculo_id') === (int) $veiculo->id ? 'selected' : '' }}>
                                        {{ $veiculo->nome }} - {{ $veiculo->placa_licenca ?? 'sem placa' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4 mb-3">
                            <label class="small mb-1">Item</label>
                            <select name="item_id" class="form-control">
                                <option value="">Todos os itens</option>
                                @foreach($itens as $item)
                                    <option value="{{ $item->id }}" {{ (int) request('item_id') === (int) $item->id ? 'selected' : '' }}>
                                        {{ $item->nome }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-2 mb-3">
                            <label class="small mb-1">Status</label>
                            <select name="status" class="form-control">
                                <option value="">Todos</option>
                                <option value="pendente" {{ request('status') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                                <option value="atrasado" {{ request('status') === 'atrasado' ? 'selected' : '' }}>Atrasado</option>
                                <option value="concluida" {{ request('status') === 'concluida' ? 'selected' : '' }}>Concluída</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-1 mb-3 d-flex align-items-end">
                            <button class="btn btn-primary btn-block" type="submit"><i class="fas fa-filter"></i></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-xl-7 mb-3">
                <div class="card shadow-sm border-0 manutencao-card h-100">
                    <div class="card-header manutencao-card__header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:.75rem;">
                            <div>
                                <div class="manutencao-card__eyebrow">Pendências</div>
                                <h5 class="mb-0">O que exige atenção agora</h5>
                            </div>
                            <span class="badge badge-light">{{ $pendencias->count() }} na página</span>
                        </div>
                    </div>
                    <div class="card-body p-0 table-responsive manutencao-table-wrap">
                        <table class="table table-hover table-striped table-bordered table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Veículo</th>
                                    <th>Item</th>
                                    <th>Previsão</th>
                                    <th>KM</th>
                                    <th>Status</th>
                                    <th>Criado por</th>
                                    <th>Atualizado por</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendencias as $pendencia)
                                    <tr class="{{ $pendencia->status === 'atrasado' ? 'table-danger-soft' : 'table-warning-soft' }}">
                                        <td>
                                            @if($pendencia->veiculo)
                                                <a href="{{ route('veiculo.show', $pendencia->veiculo->id) }}" class="font-weight-bold d-block">
                                                    {{ $pendencia->veiculo->nome }}
                                                </a>
                                                <small class="text-muted">{{ $pendencia->veiculo->placa_licenca ?? 'sem placa' }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-weight-semibold">{{ $pendencia->item?->nome ?? ('Item #' . $pendencia->item_id) }}</div>
                                        </td>
                                        <td>{{ $pendencia->data_prevista ? \Carbon\Carbon::parse($pendencia->data_prevista)->format('d/m/Y') : '—' }}</td>
                                        <td>{{ $pendencia->km_previsto !== null ? number_format((float) $pendencia->km_previsto, 0, ',', '.') : '—' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $pendencia->status === 'atrasado' ? 'danger' : 'warning' }}">
                                                {{ ucfirst($pendencia->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $pendencia->usuarioInclusao?->nome ?? '—' }}</td>
                                        <td>{{ $pendencia->usuarioAtualizacao?->nome ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Nenhuma pendência encontrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($pendencias->hasPages())
                        <div class="card-footer d-flex justify-content-end">
                            {{ $pendencias->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-12 col-xl-5 mb-3">
                <div class="card shadow-sm border-0 manutencao-card h-100">
                    <div class="card-header manutencao-card__header">
                        <div class="manutencao-card__eyebrow">Execuções recentes</div>
                        <h5 class="mb-0">Histórico concluído</h5>
                    </div>
                    <div class="card-body p-0 table-responsive manutencao-table-wrap">
                        <table class="table table-hover table-striped table-bordered table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Veículo</th>
                                    <th>Item</th>
                                    <th>Data</th>
                                    <th>KM</th>
                                    <th>OS</th>
                                    <th>Criado por</th>
                                    <th>Atualizado por</th>
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
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $execucao->item?->nome ?? ('Item #' . $execucao->item_id) }}</td>
                                        <td>{{ $execucao->data_execucao ? \Carbon\Carbon::parse($execucao->data_execucao)->format('d/m/Y') : '—' }}</td>
                                        <td>{{ $execucao->km_execucao !== null ? number_format((float) $execucao->km_execucao, 0, ',', '.') : '—' }}</td>
                                        <td>
                                            <span class="badge badge-light">
                                                {{ $execucao->ordemServico?->numero ?? ($execucao->ordem_servico_id ? ('OS #' . $execucao->ordem_servico_id) : '—') }}
                                            </span>
                                        </td>
                                        <td>{{ $execucao->usuarioInclusao?->nome ?? '—' }}</td>
                                        <td>{{ $execucao->usuarioAtualizacao?->nome ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">Nenhuma execução recente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .manutencao-preventiva-shell { display: flex; flex-direction: column; gap: 1rem; }
    .manutencao-preventiva-hero,
    .manutencao-card { border-radius: 16px; overflow: hidden; }
    .manutencao-preventiva-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f5f8ff 55%, #eef4ff 100%);
        border: 1px solid #e8edf5;
    }
    .manutencao-preventiva-eyebrow,
    .manutencao-card__eyebrow {
        font-size: .76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #5b6b7f;
        margin-bottom: .25rem;
    }
    .manutencao-kpi {
        height: 100%;
        border-radius: 16px;
        padding: 1rem 1.1rem;
        background: #fff;
        border: 1px solid #dfe7f1;
        box-shadow: 0 8px 24px rgba(18, 38, 58, .04);
    }
    .manutencao-kpi__label { font-size: .82rem; font-weight: 700; color: #5b6b7f; text-transform: uppercase; letter-spacing: .06em; }
    .manutencao-kpi__value { font-size: 2rem; line-height: 1.1; font-weight: 800; margin: .35rem 0; color: #12263a; }
    .manutencao-kpi__hint { font-size: .88rem; color: #6b7c90; }
    .manutencao-kpi--warning { border-left: 4px solid #f0ad4e; }
    .manutencao-kpi--danger { border-left: 4px solid #dc3545; }
    .manutencao-kpi--success { border-left: 4px solid #28a745; }
    .manutencao-card__header {
        background: linear-gradient(135deg, #f7fafc 0%, #eef4ff 55%, #f7fbff 100%);
        border-bottom: 1px solid #e8edf5;
        padding: 1rem 1.25rem;
    }
    .manutencao-table-wrap .table {
        min-width: 1080px;
    }
    .table-danger-soft { background-color: #fff5f5; }
    .table-warning-soft { background-color: #fffaf0; }
    .font-weight-semibold { font-weight: 600; }
    .card .badge-light { background: #eef3f8; color: #3e4c5a; }
</style>
@endsection
