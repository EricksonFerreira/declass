@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="[[ 'name' => 'Manutenções Preventivas' ]]" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="d-flex justify-content-end mb-3">
        <a href="{{ route('manutencao.planos.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-clipboard-list mr-1"></i> Plano de Manutenção Preventiva
        </a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted text-xs">Pendências</div>
                    <div class="h3 mb-0">{{ number_format((int) ($stats['pendentes'] ?? 0), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted text-xs">Atrasadas</div>
                    <div class="h3 mb-0 text-danger">{{ number_format((int) ($stats['atrasadas'] ?? 0), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted text-xs">Execuções</div>
                    <div class="h3 mb-0 text-success">{{ number_format((int) ($stats['executadas'] ?? 0), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('manutencao.preventivas.index') }}" class="row align-items-end">
                <div class="col-md-4 mb-2">
                    <label class="small mb-1">Veículo</label>
                    <select name="veiculo_id" class="form-control">
                        <option value="">Todos</option>
                        @foreach($veiculos as $veiculo)
                            <option value="{{ $veiculo->id }}" {{ (int) request('veiculo_id') === (int) $veiculo->id ? 'selected' : '' }}>
                                {{ $veiculo->nome }} - {{ $veiculo->placa_licenca ?? 'sem placa' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="small mb-1">Item</label>
                    <select name="item_id" class="form-control">
                        <option value="">Todos</option>
                        @foreach($itens as $item)
                            <option value="{{ $item->id }}" {{ (int) request('item_id') === (int) $item->id ? 'selected' : '' }}>
                                {{ $item->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <label class="small mb-1">Status</label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="pendente" {{ request('status') === 'pendente' ? 'selected' : '' }}>Pendente</option>
                        <option value="atrasado" {{ request('status') === 'atrasado' ? 'selected' : '' }}>Atrasado</option>
                        <option value="concluida" {{ request('status') === 'concluida' ? 'selected' : '' }}>Concluída</option>
                    </select>
                </div>
                <div class="col-md-2 mb-2">
                    <button class="btn btn-primary btn-block" type="submit"><i class="fas fa-filter mr-1"></i>Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Pendências</strong>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th>Veículo</th>
                        <th>Item</th>
                        <th>Previsão</th>
                        <th>KM</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendencias as $pendencia)
                        <tr>
                            <td>
                                @if($pendencia->veiculo)
                                    <a href="{{ route('veiculo.show', $pendencia->veiculo->id) }}" class="font-weight-bold">
                                        {{ $pendencia->veiculo->nome }}
                                    </a>
                                    <div class="text-muted text-xs">{{ $pendencia->veiculo->placa_licenca ?? 'sem placa' }}</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $pendencia->item?->nome ?? ('Item #' . $pendencia->item_id) }}</td>
                            <td>{{ $pendencia->data_prevista ? \Carbon\Carbon::parse($pendencia->data_prevista)->format('d/m/Y') : '—' }}</td>
                            <td>{{ $pendencia->km_previsto !== null ? number_format((float) $pendencia->km_previsto, 0, ',', '.') : '—' }}</td>
                            <td>
                                <span class="badge badge-{{ $pendencia->status === 'atrasado' ? 'danger' : 'warning' }}">
                                    {{ ucfirst($pendencia->status) }}
                                </span>
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
        @if($pendencias->hasPages())
            <div class="card-footer d-flex justify-content-end">
                {{ $pendencias->links() }}
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <strong>Execuções recentes</strong>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped table-bordered table-sm mb-0">
                <thead>
                    <tr>
                        <th>Veículo</th>
                        <th>Item</th>
                        <th>Data</th>
                        <th>KM</th>
                        <th>OS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($execucoesRecentes as $execucao)
                        <tr>
                            <td>
                                @if($execucao->veiculo)
                                    <a href="{{ route('veiculo.show', $execucao->veiculo->id) }}" class="font-weight-bold">
                                        {{ $execucao->veiculo->nome }}
                                    </a>
                                    <div class="text-muted text-xs">{{ $execucao->veiculo->placa_licenca ?? 'sem placa' }}</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $execucao->item?->nome ?? ('Item #' . $execucao->item_id) }}</td>
                            <td>{{ $execucao->data_execucao ? \Carbon\Carbon::parse($execucao->data_execucao)->format('d/m/Y') : '—' }}</td>
                            <td>{{ $execucao->km_execucao !== null ? number_format((float) $execucao->km_execucao, 0, ',', '.') : '—' }}</td>
                            <td>{{ $execucao->ordemServico?->numero ?? ($execucao->ordem_servico_id ? ('OS #' . $execucao->ordem_servico_id) : '—') }}</td>
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
@stop
