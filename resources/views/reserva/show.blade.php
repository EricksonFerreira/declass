@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
@php
    $pagamentoEfetuado = ($reserva->pagamento_efetuado ?? 'N') === 'S';
    $valorTotal = (float) ($reserva->valor_total_final ?? $reserva->valor_total_com_desconto ?? $reserva->valor_total_tarifa_base ?? 0);
    $totalPago = (float) $reserva->transacoes
        ->whereIn('status', ['succeeded', 'approved', 'paid', 'confirmed', 'concluido'])
        ->sum('valor');
    $saldoPendente = max(0, $valorTotal - $totalPago);
    $pagamentoEmDia = $saldoPendente <= 0;
@endphp

<div class="row">
    <div class="col-md-12">
        @include('reserva.components.botoes_acoes_show_reserva')
    </div>
</div>

<div class="row mt-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user mr-2"></i>Cliente</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <tr><td style="width:40%"><strong>Nome</strong></td><td>{{ $reserva->usuario->nome ?? '-' }}</td></tr>
                    <tr><td><strong>CPF / NIF</strong></td><td>{{ $reserva->usuario->nif ?? '-' }}</td></tr>
                    <tr><td><strong>Telefone</strong></td><td>{{ $reserva->usuario->telefone ?? '-' }}</td></tr>
                    <tr><td><strong>E-mail</strong></td><td>{{ $reserva->usuario->email ?? '-' }}</td></tr>
                    <tr><td><strong>CNH</strong></td><td>{{ $reserva->usuario->numero_carta_conducao ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-car mr-2"></i>Veículo</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <tr><td style="width:40%"><strong>Grupo</strong></td><td>{{ $reserva->grupoFrota?->nome ?? '-' }}</td></tr>
                    <tr><td><strong>Modelo</strong></td><td>{{ $reserva->modelos?->nome ?? '-' }}</td></tr>
                    <tr><td><strong>Placa</strong></td><td>{{ $reserva->contratoCheckin?->veiculo?->placa_licenca ?? '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calendar mr-2"></i>Datas</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <tr><td style="width:40%"><strong>Criada em</strong></td><td>{{ $reserva->created_at ? $reserva->created_at->format('d/m/Y H:i') : '-' }}</td></tr>
                    <tr><td><strong>Retirada</strong></td><td>{{ $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') : '-' }}</td></tr>
                    <tr><td><strong>Devolução</strong></td><td>{{ $reserva->data_fim ? \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') : '-' }}</td></tr>
                    <tr><td><strong>Contrato assinado em</strong></td><td>{{ $reserva->contratoCheckin?->data_assinatura ? \Carbon\Carbon::parse($reserva->contratoCheckin->data_assinatura)->format('d/m/Y H:i') : 'Não assinado' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-dollar-sign mr-2"></i>Financeiro</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0">
                    <tr><td style="width:40%"><strong>Total da locação</strong></td><td>R$ {{ number_format($valorTotal, 2, ',', '.') }}</td></tr>
                    <tr><td><strong>Total pago</strong></td><td class="text-success">R$ {{ number_format($totalPago, 2, ',', '.') }}</td></tr>
                    <tr class="{{ $saldoPendente > 0 ? 'table-warning' : '' }}">
                        <td><strong>Saldo pendente</strong></td>
                        <td class="{{ $saldoPendente > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">
                            R$ {{ number_format($saldoPendente, 2, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Status financeiro</strong></td>
                        <td>
                            @if($pagamentoEmDia)
                                <span class="badge badge-success">Pago</span>
                            @elseif($totalPago > 0)
                                <span class="badge badge-warning">Parcialmente pago</span>
                            @else
                                <span class="badge badge-danger">Em aberto</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @if(!$pagamentoEmDia)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <strong>Pagamento pendente!</strong>
                Restam <strong>R$ {{ number_format($saldoPendente, 2, ',', '.') }}</strong> a pagar.
                Quite o saldo para liberar a assinatura do contrato.
                <a href="{{ route('reserva.efetuarPagamento', $reserva->id) }}" class="btn btn-warning btn-sm ml-2">
                    <i class="fas fa-credit-card mr-1"></i> Pagar agora
                </a>
            </div>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-contract mr-2"></i>Contrato de Locação</h3>
                <div class="card-tools">
                    @if($reserva->contratoCheckin)
                        <a href="{{ route('reserva.verContratoCheckin', $reserva->id) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt mr-1"></i> Abrir contrato completo
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
@stop

@section('js')
@stop
