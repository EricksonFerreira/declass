@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    <div class="card mb-3">
        <div class="card-header"><strong>Filtros</strong></div>
        <div class="card-body">
            <form method="GET" action="{{ route('financeiro.index') }}">
                <div class="row">
                    <div class="col-md-2">
                        <label>Reserva</label>
                        <input type="number" name="reserva_id" class="form-control form-control-sm" value="{{ $filters['reserva_id'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label>Status</label>
                        <select name="status" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($statusDisponiveis as $status)
                                <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Gateway</label>
                        <select name="metodo_pagamento" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            @foreach($metodosDisponiveis as $metodo)
                                <option value="{{ $metodo }}" {{ ($filters['metodo_pagamento'] ?? '') === $metodo ? 'selected' : '' }}>{{ $metodo }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Data inicio</label>
                        <input type="date" name="data_inicio" class="form-control form-control-sm" value="{{ $filters['data_inicio'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label>Data fim</label>
                        <input type="date" name="data_fim" class="form-control form-control-sm" value="{{ $filters['data_fim'] ?? '' }}">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary btn-sm mr-1" type="submit">Filtrar</button>
                        <a href="{{ route('financeiro.index') }}" class="btn btn-secondary btn-sm">Limpar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $moedaEmpresa }} {{ number_format($totalFaturado, 2, ',', '.') }}</h3>
                    <p>Total geral</p>
                </div>
                <div class="icon"><i class="fas fa-euro-sign"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $moedaEmpresa }} {{ number_format($totalAprovado, 2, ',', '.') }}</h3>
                    <p>Receita aprovada</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $moedaEmpresa }} {{ number_format($totalPendente, 2, ',', '.') }}</h3>
                    <p>Receita pendente</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $moedaEmpresa }} {{ number_format($totalCancelado, 2, ',', '.') }}</h3>
                    <p>Receita cancelada</p>
                    <small class="d-block text-light">Transacoes canceladas: {{ $qtdCanceladas }}</small>
                </div>
                <div class="icon"><i class="fas fa-ban"></i></div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $moedaEmpresa }} {{ number_format($totalFalha, 2, ',', '.') }}</h3>
                    <p>Falhas / nao concluido</p>
                </div>
                <div class="icon"><i class="fas fa-times-circle"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $qtdTransacoes }}</h3>
                    <p>Transacoes</p>
                    <small class="d-block text-light">Taxa aprovacao: {{ number_format($taxaAprovacao, 1, ',', '.') }}%</small>
                </div>
                <div class="icon"><i class="fas fa-exchange-alt"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $qtdReservas }}</h3>
                    <p>Reservas faturadas</p>
                </div>
                <div class="icon"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-dark">
                <div class="inner">
                    <h3>{{ $transferenciasCount }}</h3>
                    <p>Transferencias de frota</p>
                    <small class="d-block text-light">Canceladas: {{ $transferenciasCanceladasCount }}</small>
                </div>
                <div class="icon"><i class="fas fa-truck-moving"></i></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><strong>Por Status</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Status</th><th class="text-center">Qtd</th><th class="text-right">Total</th></tr></thead>
                        <tbody>
                            @forelse($statusBreakdown as $item)
                                <tr>
                                    <td>{{ $item->status }}</td>
                                    <td class="text-center">{{ $item->quantidade }}</td>
                                    <td class="text-right">{{ $moedaEmpresa }} {{ number_format((float)$item->total, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Sem dados</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><strong>Por Gateway</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Gateway</th><th class="text-center">Qtd</th><th class="text-right">Total</th></tr></thead>
                        <tbody>
                            @forelse($gatewayBreakdown as $item)
                                <tr>
                                    <td>{{ $item->gateway }}</td>
                                    <td class="text-center">{{ $item->quantidade }}</td>
                                    <td class="text-right">{{ $moedaEmpresa }} {{ number_format((float)$item->total, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Sem dados</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header"><strong>Top 5 Clientes</strong></div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Cliente</th><th class="text-center">Qtd</th><th class="text-right">Total</th></tr></thead>
                        <tbody>
                            @forelse($topClientes as $item)
                                <tr>
                                    <td>{{ $item->cliente }}</td>
                                    <td class="text-center">{{ $item->quantidade }}</td>
                                    <td class="text-right">{{ $moedaEmpresa }} {{ number_format((float)$item->total, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Sem dados</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Evolucao diaria (30 dias)</strong>
            <a href="{{ route('financeiro.reservas', request()->query()) }}" class="btn btn-outline-primary btn-xs">Ver faturamento por reserva</a>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Dia</th>
                        <th class="text-center">Transacoes</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($evolucaoDiaria as $item)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($item->dia)->format('d/m/Y') }}</td>
                            <td class="text-center">{{ $item->quantidade }}</td>
                            <td class="text-right">{{ $moedaEmpresa }} {{ number_format((float)$item->total, 2, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted">Sem dados no periodo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Transferencias e transacoes detalhadas</strong></div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm table-striped mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Reserva</th>
                        <th>Usuario Reserva</th>
                        <th>Usuario Pagador</th>
                        <th>Gateway</th>
                        <th>Status</th>
                        <th>Tipo</th>
                        <th>Moeda</th>
                        <th class="text-right">Valor</th>
                        <th>Data</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transacoes as $item)
                        <tr>
                            <td>{{ $item->id }}</td>
                            <td>#{{ $item->reserva_id }}</td>
                            <td>{{ $item->usuario_nome ?? '-' }}</td>
                            <td>{{ $item->usuario_pagador_nome ?? '-' }}</td>
                            <td>{{ $item->metodo_pagamento ?? '-' }}</td>
                            @php
                                $statusLower = strtolower($item->status ?? '');
                                $isCancelado = in_array($statusLower, $statusCancelados, true);
                                $isAprovado = in_array($statusLower, $statusAprovados, true);
                                $isPendente = in_array($statusLower, $statusPendentes, true);
                                $badgeClass = $isCancelado ? 'badge-danger' : ($isAprovado ? 'badge-success' : ($isPendente ? 'badge-warning' : 'badge-secondary'));
                            @endphp
                            <td><span class="badge {{ $badgeClass }}">{{ $item->status ?? 'sem_status' }}</span></td>
                            @php
                                $valor = (float) $item->valor;
                                $tipoLabel = $valor < 0 ? 'Estorno' : ($isCancelado ? 'Cancelamento' : 'Pagamento');
                                $tipoClass = $valor < 0 ? 'badge-danger' : ($isCancelado ? 'badge-warning' : 'badge-success');
                            @endphp
                            <td><span class="badge {{ $tipoClass }}">{{ $tipoLabel }}</span></td>
                            <td>{{ strtoupper($item->moeda ?? 'BRL') }}</td>
                            <td class="text-right">{{ strtoupper($item->moeda ?? 'BRL') }} {{ number_format((float) $item->valor, 2, ',', '.') }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center text-muted">Nenhuma transacao encontrada.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $transacoes->links() }}</div>
    </div>
@stop
