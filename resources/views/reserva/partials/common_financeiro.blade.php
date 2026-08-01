@php
    $norm = fn ($value) => strtolower(trim((string) $value));
    $infoPagamento = fn ($transacao) => is_array($transacao->info_pagamento ?? null) ? $transacao->info_pagamento : [];

    $isCaucao = function ($transacao) use ($norm, $infoPagamento) {
        return $norm($transacao->metodo_pagamento ?? '') === 'caucao'
            || $norm(data_get($infoPagamento($transacao), 'tipo')) === 'calcao'
            || str_contains($norm(data_get($infoPagamento($transacao), 'acao')), 'caucao');
    };

    $isDevolucaoCaucao = function ($transacao) use ($norm, $isCaucao) {
        if (! $isCaucao($transacao)) {
            return false;
        }

        $status = $norm($transacao->status ?? '');

        return (float) ($transacao->valor ?? 0) < 0
            || in_array($status, ['refunded', 'refund', 'reembolsado', 'cancelled', 'canceled', 'cancelado', 'cancelada', 'devolvido'], true);
    };

    $isRecebimentoCaucao = fn ($transacao) => $isCaucao($transacao) && ! $isDevolucaoCaucao($transacao);
    $isPagamentoReserva = fn ($transacao) => ! $isCaucao($transacao) && (float) ($transacao->valor ?? 0) > 0;

    $metodoLabel = function ($transacao) use ($isCaucao, $isDevolucaoCaucao, $infoPagamento, $norm) {
        if ($isCaucao($transacao)) {
            $metodoReal = $norm(data_get($infoPagamento($transacao), 'metodo_real', data_get($infoPagamento($transacao), 'metodo')));

            return match ($metodoReal) {
                'pix' => 'PIX',
                'dinheiro', 'cash', 'especie', 'espécie' => 'Dinheiro',
                'cartao', 'cartão', 'credit_card', 'debito', 'débito' => 'Cartão',
                'transferencia', 'transferência', 'ted', 'doc' => 'Transferência',
                default => 'PIX',
            };
        }

        return $transacao->metodo_pagamento_label
            ?: ucfirst((string) ($transacao->metodo_pagamento ?? '-'));
    };

    $tipoLabel = function ($transacao) use ($isCaucao, $isDevolucaoCaucao) {
        if ($isDevolucaoCaucao($transacao)) {
            return 'Devolução de caução';
        }

        if ($isCaucao($transacao)) {
            return 'Recebimento de caução';
        }

        return 'Pagamento da reserva';
    };

    $statusLabel = function ($transacao) use ($isCaucao, $isDevolucaoCaucao, $norm) {
        if ($isDevolucaoCaucao($transacao)) {
            return 'Devolvido';
        }

        return match ($norm($transacao->status ?? '')) {
            'approved', 'succeeded', 'paid', 'completed', 'complete', 'confirmed', 'concluido' => $isCaucao($transacao) ? 'Recebido' : 'Recebido',
            'pending', 'processing', 'requires_action', 'requires_payment_method' => 'Pendente',
            'refunded', 'refund', 'reembolsado', 'cancelled', 'canceled', 'cancelado', 'cancelada' => 'Cancelado',
            default => $transacao->status ?? '-',
        };
    };

    $totalPagoReserva = (float) ($transacoesReserva ?? collect())
        ->filter($isPagamentoReserva)
        ->sum('valor');
    $totalCaucaoRecebida = (float) ($transacoesReserva ?? collect())
        ->filter($isRecebimentoCaucao)
        ->sum(fn ($transacao) => abs((float) ($transacao->valor ?? 0)));
    $totalCaucaoDevolvida = (float) ($transacoesReserva ?? collect())
        ->filter($isDevolucaoCaucao)
        ->sum(fn ($transacao) => abs((float) ($transacao->valor ?? 0)));
    $saldoCaucao = max(0, $totalCaucaoRecebida - $totalCaucaoDevolvida);
@endphp

<div class="rs-card mb-4">
    <div class="rs-card__header rs-card__header--between">
        <div class="rs-card__header-left">
            <i class="fas fa-file-invoice-dollar rs-card__icon"></i>
            <h2>Resumo financeiro</h2>
        </div>
    </div>
    <div class="rs-card__body">
        <div class="rs-finance-kpis">
            <div class="rs-fkpi rs-fkpi--default">
                <i class="fas fa-tag"></i>
                <div>
                    <strong>R$ {{ number_format($tarifaBase, 2, ',', '.') }}</strong>
                    <span>Tarifa diária</span>
                </div>
            </div>
            <div class="rs-fkpi rs-fkpi--default">
                <i class="fas fa-calendar-day"></i>
                <div>
                    <strong>R$ {{ number_format($valorTarifaBase, 2, ',', '.') }}</strong>
                    <span>{{ $diasReserva }} {{ $diasReserva === 1 ? 'dia' : 'dias' }} &times; tarifa</span>
                </div>
            </div>
            @if($valorOpcionais > 0)
                <div class="rs-fkpi rs-fkpi--default">
                    <i class="fas fa-puzzle-piece"></i>
                    <div>
                        <strong>R$ {{ number_format($valorOpcionais, 2, ',', '.') }}</strong>
                        <span>Opcionais</span>
                    </div>
                </div>
            @endif
            @if($taxaColeta > 0)
                <div class="rs-fkpi rs-fkpi--amber">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>R$ {{ number_format($taxaColeta, 2, ',', '.') }}</strong>
                        <span>Retirada fora expediente</span>
                    </div>
                </div>
            @endif
            @if($taxaEntrega > 0)
                <div class="rs-fkpi rs-fkpi--amber">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>R$ {{ number_format($taxaEntrega, 2, ',', '.') }}</strong>
                        <span>Devolução fora expediente</span>
                    </div>
                </div>
            @endif
            @if($percentualDesconto > 0)
                <div class="rs-fkpi rs-fkpi--green">
                    <i class="fas fa-percent"></i>
                    <div>
                        <strong>&minus; R$ {{ number_format($valorDesconto, 2, ',', '.') }}</strong>
                        <span>Desconto ({{ number_format($percentualDesconto, 0) }}%)@if($cupomCodigo) &middot; {{ $cupomCodigo }}@endif</span>
                    </div>
                </div>
            @endif
            <div class="rs-fkpi rs-fkpi--total">
                <i class="fas fa-coins"></i>
                <div>
                    <strong>R$ {{ number_format($valorTotal, 2, ',', '.') }}</strong>
                    <span>Total da reserva</span>
                </div>
            </div>
            <div class="rs-fkpi rs-fkpi--green">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>R$ {{ number_format($totalPagoReserva, 2, ',', '.') }}</strong>
                    <span>Pago pela reserva</span>
                </div>
            </div>
            @if(!empty($calcao))
                <div class="rs-fkpi rs-fkpi--amber">
                    <i class="fas fa-shield-alt"></i>
                    <div>
                        <strong>R$ {{ number_format($totalCaucaoRecebida, 2, ',', '.') }}</strong>
                        <span>Caução recebida</span>
                    </div>
                </div>
                <div class="rs-fkpi rs-fkpi--default">
                    <i class="fas fa-undo-alt"></i>
                    <div>
                        <strong>R$ {{ number_format($totalCaucaoDevolvida, 2, ',', '.') }}</strong>
                        <span>Caução devolvida</span>
                    </div>
                </div>
                <div class="rs-fkpi rs-fkpi--default">
                    <i class="fas fa-balance-scale"></i>
                    <div>
                        <strong>R$ {{ number_format($saldoCaucao, 2, ',', '.') }}</strong>
                        <span>Saldo da caução</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="rs-card">
    <div class="rs-card__header">
        <i class="fas fa-exchange-alt rs-card__icon"></i>
        <h2>Movimentações financeiras</h2>
        <span class="rs-card__badge">{{ $transacoesReserva->count() }}</span>
    </div>
    <div class="rs-card__body p-0">
        @if($transacoesReserva->isEmpty())
            <div class="rs-card__body">
                <div class="rs-empty">
                    <i class="fas fa-inbox"></i>
                    <span>Nenhuma movimentação financeira vinculada.</span>
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table class="rs-table">
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Tipo</th>
                            <th>Método</th>
                            <th>Valor</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transacoesReserva as $transacao)
                            @php
                                $valorTx = (float) ($transacao->valor ?? 0);
                                $statusTx = strtolower((string) ($transacao->status ?? ''));
                                $isCaucaoTx = $isCaucao($transacao);
                                $isDevolucaoTx = $isDevolucaoCaucao($transacao);
                                $isPendente = in_array($statusTx, ['pending', 'processing', 'requires_action', 'requires_payment_method', 'pendente', 'aguardando'], true);
                                $jaConfirmado = !empty($transacao->confirmado_em);
                                $podeConfirmar = strtolower((string) ($transacao->metodo_pagamento ?? '')) === 'dinheiro' && $isPendente && !$jaConfirmado;
                            @endphp
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ \Illuminate\Support\Carbon::parse($transacao->created_at)->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <div class="font-weight-bold">{{ $tipoLabel($transacao) }}</div>
                                </td>
                                <td>{{ $metodoLabel($transacao) }}</td>
                                <td>
                                    <div class="font-weight-bold {{ $isDevolucaoTx ? 'text-warning' : ($isCaucaoTx ? 'text-info' : 'text-success') }}">
                                        R$ {{ number_format($valorTx, 2, ',', '.') }}
                                    </div>
                                </td>
                                <td>
                                    @php $badgeClass = $isDevolucaoTx ? 'warning' : ($isPendente ? 'warning' : 'success'); @endphp
                                    <span class="badge badge-{{ $badgeClass }}">{{ $statusLabel($transacao) }}</span>
                                    @if($jaConfirmado)
                                        <div class="small text-success mt-1">
                                            <i class="fas fa-check-circle"></i>
                                            {{ \Carbon\Carbon::parse($transacao->confirmado_em)->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($podeConfirmar)
                                        <button type="button"
                                            class="btn btn-xs btn-success btn-confirmar-dinheiro"
                                            data-transacao-id="{{ $transacao->id }}"
                                            data-reserva-id="{{ $reserva->id }}"
                                            title="Confirmar recebimento em dinheiro">
                                            <i class="fas fa-check mr-1"></i>Confirmar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

@if(!empty($calcao))
<div class="rs-card mt-4">
    <div class="rs-card__header rs-card__header--between">
        <div class="rs-card__header-left">
            <i class="fas fa-shield-alt rs-card__icon"></i>
            <h2>Caução</h2>
        </div>
        @php
            $statusCalcao = strtolower((string) ($calcao->status ?? ''));
            $labelCalcao = match ($statusCalcao) {
                'devolvida' => 'DEVOLVIDA',
                'retida_integralmente' => 'RETIDA',
                'parcialmente_retida', 'parcialmente_capturado', 'capturado' => 'RETIDA PARCIALMENTE',
                default => strtoupper((string) ($calcao->label_status ?? $calcao->status ?? '-')),
            };
        @endphp
        <span class="rs-status-badge rs-status-badge--sm {{ in_array($statusCalcao, ['autorizado', 'capturado', 'parcialmente_capturado', 'devolvida', 'retida_integralmente', 'parcialmente_retida'], true) ? 'rs-status-badge--green' : 'rs-status-badge--amber' }}">
            {{ $labelCalcao }}
        </span>
    </div>
    <div class="rs-card__body">
        <div class="rs-finance-kpis">
            <div class="rs-fkpi rs-fkpi--default">
                <i class="fas fa-calculator"></i>
                <div>
                    <strong>R$ {{ number_format((float) ($calcao->valor_calculado ?? 0), 2, ',', '.') }}</strong>
                    <span>Valor calculado</span>
                </div>
            </div>
            <div class="rs-fkpi rs-fkpi--green">
                <i class="fas fa-lock"></i>
                <div>
                    <strong>R$ {{ number_format((float) ($calcao->valor_capturado ?? $calcao->valor_autorizado ?? 0), 2, ',', '.') }}</strong>
                    <span>Valor da caução</span>
                </div>
            </div>
            @if(!empty($calcao->data_hora_captura))
                <div class="rs-fkpi rs-fkpi--default">
                    <i class="fas fa-clock"></i>
                    <div>
                        <strong>{{ \Illuminate\Support\Carbon::parse($calcao->data_hora_captura)->format('d/m/Y H:i') }}</strong>
                        <span>Capturada em</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- @if($moduloNfse)
<div class="rs-card mt-4">
    <div class="rs-card__header rs-card__header--between">
        <div class="rs-card__header-left">
            <i class="fas fa-file-alt rs-card__icon"></i>
            <h2>Nota Fiscal (NFS-e)</h2>
        </div>
        <span class="rs-status-badge rs-status-badge--sm {{ $nfseBadgeClass }}">{{ $nfseStatusLabel }}</span>
    </div>
    <div class="rs-card__body">
        @if($nfseAtual)
            <div class="rs-list">
                <div class="rs-list__item">
                    <div class="rs-list__label">Número</div>
                    <div class="rs-list__value">{{ $nfseAtual->numero ?? '-' }}</div>
                </div>
                <div class="rs-list__item">
                    <div class="rs-list__label">Ambiente</div>
                    <div class="rs-list__value">{{ $nfseAtual->ambiente ?? '-' }}</div>
                </div>
            </div>

            @if($nfseErroDetalhado || $nfseMotivo)
                <div class="rs-nfse-error mt-3">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>{{ $nfseErroDetalhado ?? $nfseMotivo }}</p>
                </div>
            @endif

            @if(!empty($nfseAtual->nfse_id) && (auth()->user()?->hasPermissao('nfse.download') || auth()->user()?->isAdministrador()))
                <div class="rs-nfse-downloads mt-3">
                    <a href="{{ route('nfse.download', ['id' => $nfseAtual->id, 'tipo' => 'xml']) }}" class="rs-btn rs-btn--outline">
                        <i class="fas fa-download"></i>Baixar XML
                    </a>
                    <a href="{{ route('nfse.download', ['id' => $nfseAtual->id, 'tipo' => 'dps']) }}" class="rs-btn rs-btn--outline">
                        <i class="fas fa-file-alt"></i>Baixar DPS
                    </a>
                </div>
            @endif

            @if(($nfseAtual->status ?? '') !== 'emitida')
                <form method="POST" action="{{ route('reserva.emitirNfse', $reserva->id) }}" class="js-nfse-form mt-3">
                    @csrf
                    <button type="submit" class="rs-btn rs-btn--ghost rs-btn--full js-nfse-btn">
                        <i class="fas fa-redo"></i>Tentar emitir novamente
                    </button>
                </form>
            @endif
        @else
            <div class="rs-empty">
                <i class="fas fa-file-times"></i>
                <span>Nenhuma nota vinculada ainda.</span>
            </div>
            <form method="POST" action="{{ route('reserva.emitirNfse', $reserva->id) }}" class="js-nfse-form mt-3">
                @csrf
                <button type="submit" class="rs-btn rs-btn--ghost rs-btn--full js-nfse-btn">
                    <i class="fas fa-redo"></i>Emitir nota fiscal
                </button>
            </form>
        @endif
    </div>
</div>
@endif --}}
