@php
    $nfse = $reserva->nfseAtual ?? null;
    $nfseStatusRaw = strtolower((string) ($nfse->status ?? ''));
    $nfseStatus = $nfse ? ucfirst($nfse->status ?? 'pendente') : 'Nao emitida';
    $nfseNumero = $nfse->numero ?? '-';
    $nfseBadgeClass = match ($nfseStatusRaw) {
        'emitida' => 'is-success',
        'erro' => 'is-danger',
        'cancelada' => 'is-muted',
        default => 'is-warning',
    };
@endphp

<section class="reservation-actions-card">
    <div class="reservation-actions-card__content">
        <div class="reservation-actions-card__copy">
            <span class="reservation-actions-card__eyebrow">Painel de acoes</span>
            <h4 class="reservation-actions-card__title">Gerencie esta reserva com rapidez</h4>
            <p class="reservation-actions-card__text">
                Edite os dados, siga para pagamento, trabalhe com o contrato e acompanhe a situacao fiscal sem sair desta tela.
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

            @if($reserva->podeEfetuarPagamento())
                <a href="{{ route('reserva.efetuarPagamento', $reserva->id) }}" class="action-pill action-pill--accent">
                    <i class="fas fa-credit-card"></i>
                    <span>Efetuar pagamento</span>
                </a>
            @endif

            @if($reserva->podeCriarContrato() || $reserva->podeVerContrato() || $reserva->podeAssinarContrato())
                <div class="dropdown">
                    <button type="button" class="action-pill action-pill--sun dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-file-signature"></i>
                        <span>Contrato</span>
                    </button>
                    <div class="dropdown-menu reservation-actions-card__menu">
                        @if($reserva->podeCriarContrato())
                            <a class="dropdown-item border-bottom" href="{{ route('reserva.checkin', $reserva->id) }}" id="btnCheckInReserva">
                                <i class="fas fa-plus mr-2"></i> Criar contrato
                            </a>
                        @endif

                        @if($reserva->podeVerContrato())
                            <a class="dropdown-item border-bottom" href="{{ route('reserva.verContratoCheckin', $reserva->id) }}" id="btnVerContratoCheckinReserva" target="_blank" rel="noopener noreferrer">
                                <i class="fas fa-eye mr-2"></i> Ver contrato
                            </a>
                        @endif

                        @if($reserva->podeAssinarContrato())
                            <a class="dropdown-item" href="{{ route('reserva.assinarContratoCheckin', $reserva->id) }}" id="btnAssinarContratoReserva">
                                <i class="fas fa-signature mr-2"></i> Assinar contrato
                            </a>
                        @endif
                    </div>
                </div>
            @endif

            @if($reserva->podeFazerCancelamento())
                <button type="button" class="action-pill action-pill--danger" id="btnCancelarReserva">
                    <i class="fas fa-ban"></i>
                    <span>Cancelar reserva</span>
                </button>
            @endif

            @if($reserva->podeFazerCheckout())
                <a href="{{ route('reserva.checkout.vistoria', $reserva->id) }}" class="action-pill action-pill--success" id="btnCheckOutReserva">
                    <i class="fas fa-undo-alt"></i>
                    <span>Check-out</span>
                </a>
            @endif
        </div>

        <div class="reservation-actions-card__side">
            <div class="reservation-doc-card">
                <div class="reservation-doc-card__header">
                    <div>
                        <span class="reservation-doc-card__eyebrow">Nota fiscal (NFS-e)</span>
                        <div class="reservation-doc-card__number">Numero {{ $nfseNumero }}</div>
                    </div>
                    <span class="reservation-doc-card__badge {{ $nfseBadgeClass }}">{{ $nfseStatus }}</span>
                </div>

                <div class="reservation-doc-card__text">
                    XML e DPS ficam disponiveis aqui quando a emissao estiver concluida.
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
                    'Nao, manter reserva',
                    cancelarReserva
                );
                return;
            }

            if (window.confirm('Tem certeza que deseja cancelar a reserva?')) {
                cancelarReserva();
            }
        });
    })();
</script>
