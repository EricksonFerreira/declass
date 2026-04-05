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
            <form method="GET" action="{{ route('financeiro.reservas') }}">
                <div class="row">
                    <div class="col-md-2">
                        <label>Reserva</label>
                        <input type="number" name="reserva_id" class="form-control form-control-sm" value="{{ $filters['reserva_id'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label>Status</label>
                        <input type="text" name="status" class="form-control form-control-sm" value="{{ $filters['status'] ?? '' }}">
                    </div>
                    <div class="col-md-2">
                        <label>Gateway</label>
                        <input type="text" name="metodo_pagamento" class="form-control form-control-sm" value="{{ $filters['metodo_pagamento'] ?? '' }}">
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
                        <a href="{{ route('financeiro.reservas') }}" class="btn btn-secondary btn-sm">Limpar</a>
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
                    <p>Total faturado (sem canceladas)</p>
                </div>
                <div class="icon"><i class="fas fa-euro-sign"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $qtdReservasFaturadas }}</h3>
                    <p>Reservas com faturamento</p>
                </div>
                <div class="icon"><i class="fas fa-receipt"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $moedaEmpresa }} {{ number_format($ticketMedioPorReserva, 2, ',', '.') }}</h3>
                    <p>Media por reserva</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $moedaEmpresa }} {{ number_format($totalCancelado, 2, ',', '.') }}</h3>
                    <p>Total cancelado no filtro</p>
                </div>
                <div class="icon"><i class="fas fa-ban"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Faturamento por reserva</strong>
            <a href="{{ route('financeiro.index', request()->query()) }}" class="btn btn-outline-secondary btn-xs">Voltar para transacoes</a>
        </div>
        <div class="card-body p-0 table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Reserva</th>
                        <th>Cliente</th>
                        <th class="text-center">Qtd. transacoes</th>
                        <th class="text-right">Total</th>
                        <th>Ultima transacao</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($faturamentoPorReserva as $item)
                        <tr>
                            <td>#{{ $item->reserva_id }}</td>
                            <td>{{ $item->cliente_nome ?? '-' }}</td>
                            <td class="text-center">{{ $item->quantidade_transacoes }}</td>
                            <td class="text-right">{{ $moedaEmpresa }} {{ number_format((float) $item->valor_total, 2, ',', '.') }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($item->ultima_transacao_em)->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Sem dados de faturamento.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $faturamentoPorReserva->links() }}</div>
    </div>
@stop
