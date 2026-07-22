@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
    <x-page-title :title="$titulo" />
@stop

@section('content')
    @include('financeiro.fluxo_caixa._styles')

    @php
        $prefixoMoeda = $moedaEmpresa === 'BRL' ? 'R$' : $moedaEmpresa;
    @endphp

    <div class="fc-page">
        <div class="card fc-card fc-filter mb-4">
            <div class="card-body py-3">
                <form method="GET" action="{{ route('financeiro.reservas') }}" class="row align-items-end">
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label for="reserva_id" class="fc-label">Reserva</label>
                        <input type="number" name="reserva_id" id="reserva_id" class="form-control" placeholder="Ex: 50" value="{{ $filters['reserva_id'] ?? '' }}">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label for="status" class="fc-label">Status</label>
                        <input type="text" name="status" id="status" class="form-control" placeholder="Pendente, aprovado..." value="{{ $filters['status'] ?? '' }}">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label for="metodo_pagamento" class="fc-label">Gateway</label>
                        <input type="text" name="metodo_pagamento" id="metodo_pagamento" class="form-control" placeholder="Stripe, pix..." value="{{ $filters['metodo_pagamento'] ?? '' }}">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label for="data_inicio" class="fc-label">Data inicio</label>
                        <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="{{ $filters['data_inicio'] ?? '' }}">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2">
                        <label for="data_fim" class="fc-label">Data fim</label>
                        <input type="date" name="data_fim" id="data_fim" class="form-control" value="{{ $filters['data_fim'] ?? '' }}">
                    </div>
                    <div class="col-lg-2 col-md-4 mb-2 fc-actions">
                        <button type="submit" class="btn btn-primary fc-btn flex-grow-1">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                        <a href="{{ route('financeiro.reservas') }}" class="btn btn-outline-secondary fc-btn">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--receita h-100">
                    <div>
                        <span>Total faturado</span>
                        <strong class="text-success">{{ $prefixoMoeda }} {{ number_format($totalFaturado, 2, ',', '.') }}</strong>
                    </div>
                    <small>Sem reservas canceladas</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--saldo h-100">
                    <div>
                        <span>Reservas faturadas</span>
                        <strong class="text-info">{{ $qtdReservasFaturadas }}</strong>
                    </div>
                    <small>Com faturamento no periodo</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--projetado h-100">
                    <div>
                        <span>Ticket medio</span>
                        <strong class="text-primary">{{ $prefixoMoeda }} {{ number_format($ticketMedioPorReserva, 2, ',', '.') }}</strong>
                    </div>
                    <small>Media por reserva faturada</small>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-12 mb-4">
                <div class="fc-kpi fc-kpi--despesa h-100">
                    <div>
                        <span>Cancelamentos</span>
                        <strong class="text-danger">{{ $prefixoMoeda }} {{ number_format($totalCancelado, 2, ',', '.') }}</strong>
                    </div>
                    <small>Total devolvido ou estornado</small>
                </div>
            </div>
        </div>

        <div class="card fc-card">
            <div class="card-header py-3">
                <div class="fc-head">
                    <h5 class="fc-title">
                        <i class="fas fa-receipt"></i>
                        <span>Resumo por reserva<span class="fc-subtitle">Faturamento consolidado e transacoes vinculadas</span></span>
                    </h5>
                    <a href="{{ route('financeiro.index') }}" class="btn btn-outline-secondary fc-btn">
                        <i class="fas fa-arrow-left"></i> Voltar ao financeiro
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table fc-table">
                    <thead>
                        <tr>
                            <th class="px-3">Reserva</th>
                            <th>Cliente</th>
                            <th class="text-center">Transacoes</th>
                            <th class="text-right">Faturamento total</th>
                            <th class="text-center">Acoes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($faturamentoPorReserva as $item)
                            <tr data-toggle="collapse" data-target="#col-res-{{ $item->reserva_id }}" style="cursor:pointer;">
                                <td class="px-3 font-weight-bold">
                                    <span class="fc-badge bg-success-light">
                                        <i class="fas fa-hashtag"></i>RES-{{ str_pad($item->reserva_id, 4, '0', STR_PAD_LEFT) }}
                                    </span>
                                </td>
                                <td class="font-weight-bold">
                                    {{ $item->cliente_nome ?? 'Nao informado' }}
                                    <small class="d-block text-muted">Ultima transacao: {{ $item->ultima_transacao_em ? \Illuminate\Support\Carbon::parse($item->ultima_transacao_em)->format('d/m/Y H:i') : '-' }}</small>
                                </td>
                                <td class="text-center">
                                    <span class="fc-badge fc-badge--pendente">{{ (int) $item->quantidade_transacoes }} mov.</span>
                                </td>
                                <td class="text-right font-weight-bold {{ (float) $item->valor_total >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $prefixoMoeda }} {{ number_format((float) $item->valor_total, 2, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary fc-btn">
                                        <i class="fas fa-eye"></i> Detalhes
                                    </button>
                                </td>
                            </tr>
                            <tr class="collapse" id="col-res-{{ $item->reserva_id }}">
                                <td colspan="5" class="p-0">
                                    <div class="p-3" style="background:#f8fbff;">
                                        <div class="card fc-card mb-0">
                                            <div class="card-header py-2">
                                                <div class="fc-head">
                                                    <h6 class="fc-title mb-0">
                                                        <i class="fas fa-list"></i>
                                                        <span>Historico de transacoes<span class="fc-subtitle">Reserva #{{ $item->reserva_id }}</span></span>
                                                    </h6>
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table fc-table mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th class="px-3 text-center">ID</th>
                                                            <th>Cliente / Pagador</th>
                                                            <th>Metodo</th>
                                                            <th>Status</th>
                                                            <th class="text-right px-3">Valor</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $transacoesReserva = $detalhes->get($item->reserva_id) ?? collect();
                                                        @endphp
                                                        @forelse($transacoesReserva as $transacao)
                                                            @php
                                                                $statusString = strtolower($transacao->status ?? '');
                                                                $isAprovada = in_array($statusString, $statusAprovados, true);
                                                                $isCancelado = in_array($statusString, $statusCancelados, true);
                                                                $badgeClass = $isAprovada ? 'fc-badge--pago' : ($isCancelado ? 'fc-badge--cancelado' : 'fc-badge--pendente');
                                                                $valor = (float) ($transacao->valor ?? 0);
                                                                $clienteNome = $transacao->user->nome ?? 'Cliente nao informado';

                                                                if ($transacao->pagoPor && $transacao->pago_por_user_id !== $transacao->user_id) {
                                                                    $clienteNome .= ' <br><small class="text-muted"><i class="fas fa-user-edit mr-1"></i>Pagador: ' . e($transacao->pagoPor->nome) . '</small>';
                                                                }

                                                                $info = is_array($transacao->info_pagamento) ? $transacao->info_pagamento : @json_decode((string) $transacao->info_pagamento, true);
                                                                $tipoTransacao = is_array($info) && isset($info['tipo']) ? ucfirst((string) $info['tipo']) : 'Reserva';
                                                            @endphp
                                                            <tr>
                                                                <td class="px-3 text-center font-weight-bold">
                                                                    TRA-{{ $transacao->id }}
                                                                    <small class="d-block text-muted">{{ \Illuminate\Support\Carbon::parse($transacao->created_at)->format('d/m/y H:i') }}</small>
                                                                </td>
                                                                <td>{!! $clienteNome !!}</td>
                                                                <td>
                                                                    <span class="font-weight-bold d-block">{{ $tipoTransacao }}</span>
                                                                    <small class="text-muted"><i class="fas fa-cash-register mr-1"></i>{{ data_get($transacao, 'metodo_pagamento_label') ?? data_get($transacao, 'metodo_pagamento') ?? 'Nao classificado' }}</small>
                                                                </td>
                                                                <td>
                                                                    <span class="fc-badge {{ $badgeClass }}">{{ $transacao->status ?? 'sem status' }}</span>
                                                                </td>
                                                                <td class="text-right px-3 font-weight-bold {{ $valor >= 0 ? 'text-success' : 'text-danger' }}">
                                                                    {{ strtoupper($transacao->moeda ?? $moedaEmpresa) }} {{ number_format(abs($valor), 2, ',', '.') }}
                                                                    <small class="d-block text-muted">{{ $valor >= 0 ? 'Entrada' : 'Estorno' }}</small>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" class="text-center text-muted py-4">Sem transacoes vinculadas.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-5">
                                    <i class="fas fa-receipt d-block mb-3" style="font-size: 2.4rem; opacity: .18;"></i>
                                    Nenhum faturamento encontrado para os filtros informados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white d-flex justify-content-end">
                {{ $faturamentoPorReserva->links() }}
            </div>
        </div>
    </div>
@stop
