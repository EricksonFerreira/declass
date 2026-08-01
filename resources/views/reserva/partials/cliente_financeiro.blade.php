<div class="rs-card mb-3">
    <div class="rs-card__head">
        <div class="rs-card__icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <div>
            <div class="rs-card__title">Valores da reserva</div>
            <div class="rs-card__sub">Resumo financeiro simples para você acompanhar</div>
        </div>
    </div>
    <div class="rs-card__body">
        @php
            $calcaoAtual = $reserva->calcao ?? null;
            $valorCalcao = $calcaoAtual
                ? (float) ($calcaoAtual->valor_capturado ?? $calcaoAtual->valor_autorizado ?? $calcaoAtual->valor_calculado ?? 0)
                : 0;
            $calcaoPendente = $calcaoAtual && (float) ($calcaoAtual->valor_capturado ?? 0) <= 0 && (float) ($calcaoAtual->valor_autorizado ?? 0) <= 0;
        @endphp
        <div class="rs-price">
            <div class="rs-price__box">
                <div class="rs-price__label">Tarifa base</div>
                <div class="rs-price__value">R$ {{ number_format($valorTarifaBase, 2, ',', '.') }}</div>
            </div>
            <div class="rs-price__box">
                <div class="rs-price__label">Opcionais</div>
                <div class="rs-price__value">R$ {{ number_format($valorOpcionais, 2, ',', '.') }}</div>
            </div>
            <div class="rs-price__box">
                <div class="rs-price__label">Total</div>
                <div class="rs-price__value">R$ {{ number_format($valorTotal, 2, ',', '.') }}</div>
            </div>
            @if($calcaoAtual)
            <div class="rs-price__box">
                <div class="rs-price__label">Calção</div>
                <div class="rs-price__value {{ $calcaoPendente ? 'text-warning' : 'text-success' }}">
                    R$ {{ number_format($valorCalcao, 2, ',', '.') }}
                </div>
                <div class="small text-muted mt-1">
                    {{ $calcaoPendente ? 'Pendente' : 'Registrado' }}
                </div>
            </div>
            @endif
        </div>
        <div style="margin-top:12px" class="rs-list">
            <div class="rs-list__item">
                <div class="rs-list__label">Pagamento</div>
                <div class="rs-list__value">{{ $pagamentoEfetuado ? 'Pago' : 'Pendente' }}</div>
            </div>
            @if($calcaoAtual)
            <div class="rs-list__item">
                <div class="rs-list__label">Calção</div>
                <div class="rs-list__value">{{ $calcaoPendente ? 'Pendente' : 'Registrado' }}</div>
            </div>
            @endif
            <div class="rs-list__item">
                <div class="rs-list__label">Saldo pendente</div>
                <div class="rs-list__value">R$ {{ number_format($saldoPendente, 2, ',', '.') }}</div>
            </div>
            @if($percentualDesconto > 0)
                <div class="rs-list__item">
                    <div class="rs-list__label">Desconto</div>
                    <div class="rs-list__value">{{ number_format($percentualDesconto, 0) }}% @if($cupomCodigo) - Cupom {{ $cupomCodigo }} @endif</div>
                </div>
            @endif
        </div>
        @if(!$pagamentoEfetuado)
            <div class="rs-actions" style="margin-top:14px;">
                <a href="{{ route('reserva.pagamento.efetuar', ['id' => $reserva->codigoReserva?->codigo ?? ('RES-' . $reserva->id)]) }}" class="rs-btn rs-btn--primary">
                    <i class="fas fa-credit-card"></i> Pagar
                </a>
            </div>
        @endif
    </div>
</div>

<div class="rs-card">
    <div class="rs-card__head">
        <div class="rs-card__icon"><i class="fas fa-receipt"></i></div>
        <div>
            <div class="rs-card__title">Histórico de pagamentos</div>
            <div class="rs-card__sub">Transações financeiras da reserva</div>
        </div>
    </div>
    <div class="rs-card__body p-0">
        @php $transacoes = $reserva->transacoes ?? collect(); @endphp
        @if($transacoes->isEmpty())
            <div class="rs-card__body">
                <div class="rs-note">Nenhuma transação registrada para esta reserva.</div>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th class="pl-3">Data</th>
                            <th>Valor</th>
                            <th>Método</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transacoes as $t)
                            @php
                                $statusTransacao = strtolower((string) ($t->status ?? ''));
                                $badgeClass = match ($statusTransacao) {
                                    'confirmado', 'pago', 'aprovado' => 'badge-success',
                                    'pendente', 'aguardando' => 'badge-warning',
                                    'cancelado', 'recusado' => 'badge-danger',
                                    default => 'badge-secondary',
                                };
                            @endphp
                            <tr>
                                <td class="pl-3">{{ $t->created_at ? \Carbon\Carbon::parse($t->created_at)->format('d/m/Y H:i') : '-' }}</td>
                                <td class="font-weight-bold">R$ {{ number_format((float) ($t->valor ?? 0), 2, ',', '.') }}</td>
                                <td>{{ $t->metodo_pagamento ?: ($t->metodo_pagamento_label ?? '-') }}</td>
                                <td><span class="badge {{ $badgeClass }}">{{ ucfirst($statusTransacao ?: 'N/I') }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
