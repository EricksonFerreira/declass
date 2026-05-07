@php
    $nfse = $reserva->nfseAtual ?? null;
    $nfseStatusRaw = strtolower((string) ($nfse->status ?? ''));
    $nfseStatus = $nfse ? ucfirst($nfse->status ?? 'pendente') : 'Não emitida';
    $nfseNumero = $nfse->numero ?? '-';
    $nfseBadgeClass = match ($nfseStatusRaw) {
        'emitida' => 'is-success',
        'erro' => 'is-danger',
        'cancelada' => 'is-muted',
        default => 'is-warning',
    };

    $valorTotalReserva = (float) ($valorTotal ?? $reserva->valor_total_final ?? $reserva->valor_total_com_desconto ?? $reserva->valor_total_tarifa_base ?? 0);
    $statusPagamentoAprovadoReserva = ['succeeded', 'approved', 'paid', 'completed', 'complete', 'confirmed', 'concluido'];
    $totalPagoReserva = (float) ($totalPagoAprovado ?? $reserva->transacoes
        ->filter(fn ($item) => (float) ($item->valor ?? 0) > 0
            && in_array(strtolower((string) ($item->status ?? '')), $statusPagamentoAprovadoReserva, true))
        ->sum('valor'));
    $saldoPendenteReserva = max(0, $valorTotalReserva - $totalPagoReserva);
    $pagamentoEmDia = $saldoPendenteReserva <= 0.009;
@endphp

<section class="reservation-actions-card">
    <div class="reservation-actions-card__content">
        <div class="reservation-actions-card__copy">
            <span class="reservation-actions-card__eyebrow">Painel de ações</span>
            <h4 class="reservation-actions-card__title">Gerencie esta reserva com rapidez</h4>
            <p class="reservation-actions-card__text">
                Edite os dados, siga para pagamento, trabalhe com o contrato e acompanhe a situação fiscal sem sair desta tela.
            </p>
        </div>

        <div class="reservation-actions-card__toolbar">
            <a href="{{ route('reserva.index') }}" class="action-pill action-pill--ghost">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>

            @if($reserva->podeEditar())
                <a href="{{ route('reserva.edit', $reserva->id) }}" class="action-pill action-pill--primary">
                    <i class="fas fa-pen"></i>
                    <span>Editar</span>
                </a>
            @endif

            @can('reserva.editar_no_show')
                @if(!$reserva->podeEditar() && !in_array((int)$reserva->status_reserva_id, [3,4,5]))
                    <a href="{{ route('reserva.edit', $reserva->id) }}" class="action-pill action-pill--primary">
                        <i class="fas fa-pen"></i>
                        <span>Editar reserva</span>
                    </a>
                @endif
            @endcan

            @if($reserva->podeEfetuarPagamento() || (!$pagamentoEmDia && $saldoPendenteReserva > 0))
                <a href="{{ route('reserva.efetuarPagamento', $reserva->id) }}" class="action-pill action-pill--accent">
                    <i class="fas fa-credit-card"></i>
                    <span>{{ $saldoPendenteReserva > 0 ? 'Pagar saldo pendente' : 'Efetuar pagamento' }}</span>
                    @if($saldoPendenteReserva > 0)
                        <small style="display:block;font-size:9px;opacity:0.9;">R$ {{ number_format($saldoPendenteReserva, 2, ',', '.') }}</small>
                    @endif
                </a>
            @endif

            @if(!$pagamentoEmDia && $saldoPendenteReserva > 0)
                <div class="reservation-actions-card__alert" style="background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:10px 14px;margin-top:8px;">
                    <div style="display:flex;align-items:flex-start;gap:10px;">
                        <i class="fas fa-exclamation-circle" style="color:#dc2626;font-size:18px;margin-top:2px;"></i>
                        <div>
                            <strong style="color:#991b1b;font-size:13px;">Pagamento pendente</strong><br>
                            <span style="color:#7f1d1d;font-size:12px;">
                                Restam <strong>R$ {{ number_format($saldoPendenteReserva, 2, ',', '.') }}</strong> a pagar.
                                Quite o saldo para liberar a assinatura do contrato.
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Calção: disponível após contrato assinado --}}
            @if($reserva->contratoCheckin && $reserva->contratoCheckin->assinado === 'S')
                @can('calcao.autorizar')
                @php $calcaoAtual = $reserva->calcao ?? null; @endphp
                @if(!$calcaoAtual || $calcaoAtual->status === 'falhou')
                <a href="{{ route('reserva.calcao', $reserva->id) }}" class="action-pill" style="background:linear-gradient(135deg,#6f42c1,#9061f9)">
                    <i class="fas fa-shield-alt"></i>
                    <span>Calção no cartão</span>
                </a>
                @endif
                @endcan
            @endif

            {{-- Check-in --}}
            @if(!$reserva->contratoCheckin && ($pagamentoEfetuado ?? false))
                <a href="{{ route('reserva.checkin', $reserva->id) }}" class="action-pill action-pill--success">
                    <i class="fas fa-file-signature"></i>
                    <span>Iniciar check-in</span>
                </a>
            @elseif($reserva->contratoCheckin && $reserva->contratoCheckin->assinado !== 'S')
                @if($pagamentoEmDia)
                    <a href="{{ route('reserva.assinarContratoCheckin', $reserva->id) }}" class="action-pill action-pill--success">
                        <i class="fas fa-pen"></i>
                        <span>Assinar contrato</span>
                    </a>
                @else
                    <span class="action-pill action-pill--disabled"
                          data-toggle="tooltip"
                          title="Pagamento insuficiente. Valor atual: R$ {{ number_format($valorTotalReserva, 2, ',', '.') }}. Pago: R$ {{ number_format($totalPagoReserva, 2, ',', '.') }}. Restante: R$ {{ number_format($saldoPendenteReserva, 2, ',', '.') }}.">
                        <i class="fas fa-lock"></i>
                        <span>Assinar contrato</span>
                    </span>
                    <a href="{{ route('reserva.efetuarPagamento', $reserva->id) }}" class="action-pill action-pill--accent" style="background:linear-gradient(135deg,#dc2626,#f97316);">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span>Pagar saldo pendente</span>
                        <small style="display:block;font-size:9px;opacity:0.9;">R$ {{ number_format($saldoPendenteReserva, 2, ',', '.') }}</small>
                    </a>
                @endif
            @endif

            {{-- Check-out --}}
            @php
                $checkoutJaFeito   = $reserva->contratoCheckout()->exists();
                $checkinAssinadoCo = $reserva->contratoCheckin && $reserva->contratoCheckin->assinado === 'S';
                $checkoutUrl       = route('reserva.checkout.vistoria', $reserva->id);
                $dataFim           = $reserva->data_fim ? \Carbon\Carbon::parse($reserva->data_fim) : null;
                $isAntesDaDataFim  = $dataFim && now()->lt($dataFim);

                // Calção: ok se não há regra configurada, ou se já foi autorizado/capturado
                $calcaoOkCo = true;
                try {
                    $calculo = app(\App\Services\CalcaoService::class)->calcularValor($reserva);
                    if ((float)($calculo['valor_calculado'] ?? 0) > 0) {
                        $calcaoStatus = $reserva->calcao?->status ?? '';
                        $calcaoOkCo = in_array($calcaoStatus, ['autorizado','capturado','parcialmente_capturado'], true);
                    }
                } catch (\Throwable $e) { $calcaoOkCo = true; }

                $podeCheckout = !$checkoutJaFeito && ($pagamentoEfetuado ?? false) && $checkinAssinadoCo && $calcaoOkCo;

                $motivosBloqueio = [];
                if (!($pagamentoEfetuado ?? false))   $motivosBloqueio[] = 'Pagamento não efetuado';
                if (!$checkinAssinadoCo)              $motivosBloqueio[] = 'Check-in não realizado ou contrato não assinado';
                if (!$calcaoOkCo)                     $motivosBloqueio[] = 'Calção pendente — realize a pré-autorização antes do check-out';
            @endphp

            @if($checkoutJaFeito)
                @php $jaSatisfacaoBtn = $reserva->satisfacao()->exists(); @endphp
                <button type="button" class="action-pill action-pill--success"
                        onclick="$('#modalSatisfacao').modal('show')">
                    <i class="fas fa-{{ $jaSatisfacaoBtn ? 'check-circle' : 'star' }}"></i>
                    <span>{{ $jaSatisfacaoBtn ? 'Ver avaliação' : 'Avaliar atendimento' }}</span>
                </button>
            @elseif($checkinAssinadoCo)
                {{-- Check-in feito: mostra botão ativo ou bloqueado --}}
                @if($podeCheckout)
                    <button type="button" id="btn-iniciar-checkout"
                            class="action-pill action-pill--checkout"
                            data-url="{{ $checkoutUrl }}"
                            data-antes-fim="{{ $isAntesDaDataFim ? '1' : '0' }}"
                            data-data-fim="{{ $dataFim ? $dataFim->format('d/m/Y H:i') : '' }}">
                        <i class="fas fa-flag-checkered"></i>
                        <span>Fazer check-out</span>
                    </button>
                @else
                    <span class="action-pill action-pill--disabled checkout-bloqueado"
                          data-motivos="{{ implode('|', $motivosBloqueio) }}">
                        <i class="fas fa-lock"></i>
                        <span>Check-out</span>
                    </span>
                @endif
            @endif
            {{-- Sem checkin: nenhum botão de checkout exibido --}}

            @if($reserva->podeFazerCancelamento())
                <button type="button" class="action-pill action-pill--danger" id="btnCancelarReserva">
                    <i class="fas fa-ban"></i>
                    <span>Cancelar reserva</span>
                </button>
            @endif

        </div>

        @if($moduloNfse ?? false)
        <div class="reservation-actions-card__side">
            <div class="reservation-doc-card">
                <div class="reservation-doc-card__header">
                    <div>
                        <span class="reservation-doc-card__eyebrow">Nota fiscal (NFS-e)</span>
                        <div class="reservation-doc-card__number">Número {{ $nfseNumero }}</div>
                    </div>
                    <span class="reservation-doc-card__badge {{ $nfseBadgeClass }}">{{ $nfseStatus }}</span>
                </div>

                <div class="reservation-doc-card__text">
                    XML e DPS ficam disponíveis aqui quando a emissão estiver concluída.
                </div>

                @if($nfse && !empty($nfse->nfse_id) && (auth()->user()?->hasPermissao('nfse.download') || auth()->user()?->isAdministrador()))
                    <div class="reservation-doc-card__actions">
                        <a class="action-mini action-mini--primary" href="{{ route('nfse.download', ['id' => $nfse->id, 'tipo' => 'xml']) }}">
                            <i class="fas fa-download"></i>
                            <span>Baixar XML</span>
                        </a>
                        <a class="action-mini action-mini--secondary" href="{{ route('nfse.download', ['id' => $nfse->id, 'tipo' => 'dps']) }}">
                            <i class="fas fa-file-alt"></i>
                            <span>Baixar DPS</span>
                        </a>
                    </div>
                @endif
            </div>
        </div>
        @endif
    </div>
</section>

<style>
    .reservation-actions-card {
        position: relative;
        overflow: hidden;
        border-radius: 28px;
        background:
            radial-gradient(circle at top right, rgba(245, 158, 11, 0.12), transparent 28%),
            linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
        border: 1px solid #dbe4f0;
        box-shadow: 0 28px 70px rgba(15, 23, 42, 0.10);
    }
    .reservation-actions-card__content {
        display: grid;
        grid-template-columns: minmax(0, 1.1fr) minmax(0, 1.4fr) minmax(280px, .8fr);
        gap: 20px;
        padding: 24px 26px;
        align-items: center;
    }
    .reservation-actions-card__copy {
        display: grid;
        gap: 8px;
    }
    .reservation-actions-card__eyebrow {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .18em;
        text-transform: uppercase;
        color: #64748b;
    }
    .reservation-actions-card__title {
        margin: 0;
        font-size: 1.35rem;
        font-weight: 700;
        color: #0f172a;
    }
    .reservation-actions-card__text {
        margin: 0;
        color: #475569;
        line-height: 1.6;
        max-width: 38rem;
    }
    .reservation-actions-card__toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }
    .action-pill {
        appearance: none;
        border: 1px solid transparent;
        background: #0f172a;
        color: #fff;
        border-radius: 999px;
        padding: 12px 18px;
        font-weight: 700;
        font-size: .92rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        text-decoration: none;
        box-shadow: 0 12px 26px rgba(15, 23, 42, 0.12);
        transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
        cursor: pointer;
    }
    .action-pill:hover,
    .action-pill:focus {
        color: #fff;
        text-decoration: none;
        transform: translateY(-1px);
        box-shadow: 0 18px 28px rgba(15, 23, 42, 0.16);
    }
    .action-pill:disabled {
        opacity: .7;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    .action-pill--ghost {
        background: #fff;
        color: #0f172a;
        border-color: #cbd5e1;
        box-shadow: none;
    }
    .action-pill--ghost:hover,
    .action-pill--ghost:focus {
        color: #0f172a;
        border-color: #94a3b8;
        box-shadow: 0 10px 24px rgba(148, 163, 184, 0.18);
    }
    .action-pill--primary {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
    }
    .action-pill--accent {
        background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
    }
    .action-pill--sun {
        background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
        color: #1f2937;
    }
    .action-pill--sun:hover,
    .action-pill--sun:focus {
        color: #1f2937;
    }
    .action-pill--danger {
        background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
    }
    .action-pill--success {
        background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    }
    .action-pill--checkout {
        background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
    }
    .action-pill--disabled {
        background: #cbd5e1;
        color: #64748b;
        cursor: pointer;
        box-shadow: none;
        transform: none !important;
        position: relative;
    }
    .action-pill--disabled:hover {
        background: #b0bec5;
        transform: none !important;
        box-shadow: none !important;
    }
    .reservation-actions-card__menu {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.16);
        overflow: hidden;
        padding: 8px 0;
    }
    .reservation-actions-card__menu .dropdown-item {
        padding: 10px 16px;
        font-weight: 600;
        color: #334155;
    }
    .reservation-actions-card__menu .dropdown-item:hover {
        background: #f8fafc;
        color: #0f172a;
    }
    .reservation-actions-card__side {
        display: flex;
        justify-content: flex-end;
    }
    .reservation-doc-card {
        width: 100%;
        max-width: 320px;
        padding: 18px;
        border-radius: 22px;
        background: linear-gradient(160deg, #f8fafc 0%, #eef2ff 100%);
        border: 1px solid #d8e0ee;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
        display: grid;
        gap: 12px;
    }
    .reservation-doc-card__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
    }
    .reservation-doc-card__eyebrow {
        display: block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 4px;
    }
    .reservation-doc-card__number {
        font-size: .95rem;
        font-weight: 700;
        color: #0f172a;
    }
    .reservation-doc-card__badge {
        padding: 8px 12px;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .reservation-doc-card__badge.is-success {
        background: rgba(5, 150, 105, 0.12);
        color: #047857;
    }
    .reservation-doc-card__badge.is-danger {
        background: rgba(220, 38, 38, 0.12);
        color: #b91c1c;
    }
    .reservation-doc-card__badge.is-warning {
        background: rgba(245, 158, 11, 0.16);
        color: #b45309;
    }
    .reservation-doc-card__badge.is-muted {
        background: rgba(100, 116, 139, 0.14);
        color: #475569;
    }
    .reservation-doc-card__text {
        color: #475569;
        line-height: 1.5;
        font-size: .92rem;
    }
    .reservation-doc-card__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }
    .action-mini {
        border-radius: 14px;
        padding: 10px 12px;
        font-size: .86rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    .action-mini:hover,
    .action-mini:focus {
        text-decoration: none;
    }
    .action-mini--primary {
        background: rgba(37, 99, 235, 0.10);
        color: #1d4ed8;
    }
    .action-mini--secondary {
        background: rgba(15, 23, 42, 0.08);
        color: #334155;
    }
    @media (max-width: 1279px) {
        .reservation-actions-card__content {
            grid-template-columns: 1fr;
        }
        .reservation-actions-card__side {
            justify-content: flex-start;
        }
        .reservation-doc-card {
            max-width: none;
        }
    }
    @media (max-width: 767px) {
        .reservation-actions-card {
            border-radius: 22px;
        }
        .reservation-actions-card__content {
            padding: 18px;
        }
        .reservation-actions-card__title {
            font-size: 1.15rem;
        }
        .reservation-actions-card__toolbar {
            flex-direction: column;
            align-items: stretch;
        }
        .action-pill,
        .dropdown,
        .dropdown .action-pill,
        .action-mini {
            width: 100%;
            justify-content: center;
        }
        .reservation-doc-card__header {
            flex-direction: column;
            align-items: flex-start;
        }
    }
</style>

<script src="{{ asset('js/sweetAlert.min.js') }}"></script>
<script src="{{ asset('js/alertConfirm.js') }}"></script>
<script src="{{ asset('js/alertConfirmPequena.js') }}"></script>
<script>
    /* ── Cancelar ── */
    (function () {
        const btnCancelar = document.getElementById('btnCancelarReserva');

        if (!btnCancelar) {
            return;
        }

        const cancelarReserva = async () => {
            const originalContent = btnCancelar.innerHTML;
            btnCancelar.disabled = true;
            btnCancelar.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Cancelando...</span>';

            try {
                const response = await fetch('{{ route('reserva.cancelar', $reserva->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
                    },
                    body: new URLSearchParams({
                        _token: '{{ csrf_token() }}'
                    }).toString()
                });

                let data = {};

                try {
                    data = await response.json();
                } catch (e) {
                    data = {};
                }

                if (!response.ok) {
                    throw new Error(data.message || 'A reserva nao foi cancelada.');
                }

                if (typeof alertConfirmPequena === 'function') {
                    alertConfirmPequena('Cancelada!', data.message || 'A reserva foi cancelada com sucesso.', 'success');
                } else {
                    alert(data.message || 'A reserva foi cancelada com sucesso.');
                }

                setTimeout(() => {
                    window.location.reload();
                }, 1200);
            } catch (error) {
                btnCancelar.disabled = false;
                btnCancelar.innerHTML = originalContent;

                if (typeof alertConfirmPequena === 'function') {
                    alertConfirmPequena('Problema ao cancelar reserva!', error.message || 'A reserva nao foi cancelada.', 'error');
                } else {
                    alert(error.message || 'A reserva nao foi cancelada.');
                }
            }
        };

        btnCancelar.addEventListener('click', function () {
            if (typeof alertConfirm === 'function') {
                alertConfirm(
                    'Cancelar Reserva',
                    'Tem certeza que deseja cancelar a reserva?',
                    'warning',
                    'Sim, cancelar reserva!',
                    'Não, manter reserva',
                    cancelarReserva
                );
                return;
            }

            if (window.confirm('Tem certeza que deseja cancelar a reserva?')) {
                cancelarReserva();
            }
        });
    })();

    /* ── Check-out bloqueado: mostra motivos via SweetAlert ── */
    (function () {
        document.querySelectorAll('.checkout-bloqueado').forEach(function (el) {
            el.addEventListener('click', function () {
                const motivos = (el.dataset.motivos || '').split('|').filter(Boolean);
                const listaHtml = motivos.map(m =>
                    `<li style="text-align:left;padding:4px 0;color:#374151;">
                        <i class="fas fa-times-circle" style="color:#ef4444;margin-right:6px;"></i>${m}
                    </li>`
                ).join('');

                Swal.fire({
                    title: 'Check-out indisponível',
                    html: `<p style="color:#475569;margin-bottom:10px;">Para realizar o check-out, os seguintes requisitos precisam ser atendidos:</p>
                           <ul style="list-style:none;padding:0;margin:0">${listaHtml}</ul>`,
                    icon: 'info',
                    iconColor: '#0891b2',
                    confirmButtonText: 'Entendido',
                    confirmButtonColor: '#0891b2',
                });
            });
        });
    })();

    /* ── Iniciar Check-out ── */
    (function () {
        const btn = document.getElementById('btn-iniciar-checkout');
        if (!btn) return;

        btn.addEventListener('click', function () {
            const url        = btn.dataset.url;
            const antesFim   = btn.dataset.antesFim === '1';
            const dataFim    = btn.dataset.dataFim;

            if (antesFim) {
                // Fora da data prevista — avisa sobre sem estorno
                Swal.fire({
                    title: 'Check-out antecipado',
                    html: `<p style="color:#374151;line-height:1.6">A data prevista de devolução é <strong>${dataFim}</strong>.<br><br>
                           Ao realizar o check-out agora, <strong>não haverá estorno</strong> dos dias restantes que já foram cobrados.<br><br>
                           Deseja continuar mesmo assim?</p>`,
                    icon: 'warning',
                    iconColor: '#f59e0b',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-flag-checkered mr-1"></i> Sim, fazer check-out',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#0891b2',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                    customClass: {
                        popup: 'swal-rounded',
                    },
                }).then(result => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            } else {
                window.location.href = url;
            }
        });
    })();
</script>
