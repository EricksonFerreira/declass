@extends('adminlte::page')

@section('title', 'Detalhes da Reserva')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $diasReserva = (int) ($reserva->quantidade_dias ?? 0);
    $tarifaBase = (float) ($reserva->tarifa_base ?? 0);
    $valorTarifaBase = $tarifaBase * $diasReserva;
    $valorOpcionais = (float) ($reserva->valor_opcionais ?? 0);

    if ($valorOpcionais <= 0 && $reserva->opcionais?->isNotEmpty()) {
        $valorOpcionais = (float) $reserva->opcionais->sum(function ($opcional) {
            return (float) ($opcional->pivot->preco_total ?? 0);
        });
    }

    $percentualDesconto = (float) ($reserva->percentual_desconto ?? 0);
    $valorDesconto = ($valorTarifaBase + $valorOpcionais) * ($percentualDesconto / 100);
    $valorTotal = max(0, ($valorTarifaBase + $valorOpcionais) - $valorDesconto);

    $createdAt = $reserva->created_at ? \Carbon\Carbon::parse($reserva->created_at)->format('d/m/Y H:i') : '-';
    $inicioFormatado = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') : '-';
    $fimFormatado = $reserva->data_fim ? \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') : '-';

    $statusReservaNome = $reserva->statusReserva->nome ?? 'Status';
    $statusReservaSlug = strtolower((string) $statusReservaNome);
    $statusReservaClass = match ($statusReservaSlug) {
        'confirmada' => 'is-success',
        'cancelada' => 'is-danger',
        'criada', 'pendente' => 'is-warning',
        default => 'is-neutral',
    };

    $clienteNome = $reserva->usuario->nome ?? 'Nao informado';
    $clientePartes = preg_split('/\s+/', trim((string) $clienteNome)) ?: [];
    $clienteIniciais = collect($clientePartes)
        ->filter()
        ->take(2)
        ->map(fn ($parte) => mb_strtoupper(mb_substr($parte, 0, 1)))
        ->implode('');
    $clienteIniciais = $clienteIniciais ?: 'CL';

    $nfseAtual = $reserva->nfseAtual ?? null;
    $nfseStatusRaw = strtolower((string) ($nfseAtual->status ?? ''));
    $nfseStatusLabel = $nfseAtual ? ucfirst($nfseAtual->status ?? 'pendente') : 'Nao emitida';
    $nfseBadgeClass = match ($nfseStatusRaw) {
        'emitida' => 'is-success',
        'erro' => 'is-danger',
        'cancelada' => 'is-neutral',
        default => 'is-warning',
    };
    $nfseMotivo = $nfseAtual ? (app(\App\Services\NfseService::class)->mensagemErro($nfseAtual) ?? null) : null;
    $reservaConfirmada = isset($reserva->status_reserva_id) && (int) $reserva->status_reserva_id === 2;
    $nfseNaoEmitida = !$nfseAtual || ($nfseAtual->status ?? '') !== 'emitida';
    $nfseErroDetalhado = null;

    if ($nfseAtual && ($nfseAtual->status ?? '') === 'erro' && is_array($nfseAtual->response ?? null)) {
        $nfseErroDetalhado = $nfseAtual->response['message'] ?? $nfseAtual->response['error'] ?? $nfseAtual->response['erro'] ?? null;

        if (!$nfseErroDetalhado && !empty($nfseAtual->response['errors'])) {
            $nfseErroDetalhado = implode(' ', (array) $nfseAtual->response['errors']);
        }
    }
@endphp

<section class="reservation-minimal">
    <div class="reservation-minimal__hero">
        <div>
            <div class="reservation-minimal__eyebrow">Reserva #{{ $reserva->id }}</div>
            <div class="reservation-minimal__title-row">
                <h1 class="reservation-minimal__title">{{ $titulo }}</h1>
                <span class="reservation-badge {{ $statusReservaClass }}">{{ strtoupper($statusReservaNome) }}</span>
            </div>
            <div class="reservation-minimal__meta">
                <span>{{ $reserva->empresa->nome ?? 'Empresa' }}</span>
                <span>{{ $createdAt }}</span>
            </div>
        </div>

        <div class="reservation-minimal__hero-stats">
            <div class="mini-stat">
                <span>Total</span>
                <strong>R$ {{ number_format($valorTotal, 2, ',', '.') }}</strong>
            </div>
            <div class="mini-stat">
                <span>Duracao</span>
                <strong>{{ $diasReserva }} dias</strong>
            </div>
        </div>
    </div>

    @include('reserva.components.botoes_acoes_show_reserva')

    @if($reservaConfirmada && $nfseNaoEmitida)
        <div class="reservation-inline-alert">
            <div>
                <strong>NFS-e pendente</strong>
                <p>{{ $nfseMotivo ?? $nfseErroDetalhado ?? 'A nota fiscal ainda nao foi emitida para esta reserva.' }}</p>
            </div>
            <form method="POST" action="{{ route('reserva.emitirNfse', $reserva->id) }}" class="js-nfse-form">
                @csrf
                <button type="submit" class="js-nfse-btn inline-action-btn">
                    <i class="fas fa-redo"></i>
                    <span>Tentar emitir</span>
                </button>
            </form>
        </div>
    @endif

    <div class="reservation-minimal__grid">
        <div class="reservation-minimal__main">
            <article class="minimal-card minimal-card--trip">
                <div class="minimal-card__header">
                    <h2>Viagem</h2>
                </div>
                <div class="trip-compact">
                    <div class="trip-compact__item">
                        <span class="trip-compact__label">Retirada</span>
                        <strong>{{ $reserva->estacaoRetirada->nome ?? 'Nao informado' }}</strong>
                        <small>{{ $inicioFormatado }}</small>
                    </div>
                    <div class="trip-compact__divider"></div>
                    <div class="trip-compact__item">
                        <span class="trip-compact__label">Devolucao</span>
                        <strong>{{ $reserva->estacaoDevolucao->nome ?? 'Nao informado' }}</strong>
                        <small>{{ $fimFormatado }}</small>
                    </div>
                </div>
            </article>

            <article class="minimal-card minimal-card--vehicle">
                <div class="minimal-card__header">
                    <h2>Veiculo</h2>
                </div>
                <div class="vehicle-compact">
                    <div class="vehicle-compact__media">
                        <img src="{{ $reserva->modelos->imagem_url ?? asset('img/no-image.jpg') }}" alt="{{ $reserva->modelos->nome ?? 'Veiculo reservado' }}">
                    </div>
                    <div class="vehicle-compact__content">
                        <h3>{{ $reserva->modelos->nome ?? 'Nao informado' }}</h3>
                        <p>{{ $reserva->modelos->marca->nome ?? 'Marca nao informada' }}</p>
                        <div class="vehicle-compact__chips">
                            <span>{{ $reserva->grupoFrota->nome ?? 'Grupo nao informado' }}</span>
                            @if(!empty($reserva->modelos->transmissao))
                                <span>{{ $reserva->modelos->transmissao }}</span>
                            @endif
                            @if(!empty($reserva->modelos->combustivel))
                                <span>{{ $reserva->modelos->combustivel }}</span>
                            @endif
                        </div>
                        <div class="vehicle-compact__specs">
                            <div><span>Ano</span><strong>{{ $reserva->modelos->ano ?? '-' }}</strong></div>
                            <div><span>Capacidade</span><strong>{{ $reserva->modelos->capacidade ? $reserva->modelos->capacidade . ' pessoas' : '-' }}</strong></div>
                        </div>
                    </div>
                </div>
            </article>

            @if($reserva->opcionais->isNotEmpty())
                <article class="minimal-card">
                    <div class="minimal-card__header">
                        <h2>Opcionais</h2>
                    </div>
                    <div class="optional-list-minimal">
                        @foreach($reserva->opcionais as $opcional)
                            @php
                                $quantidadeOpcional = (int) ($opcional->pivot->quantidade ?? 0);
                                $precoOpcional = (float) ($opcional->pivot->preco_total ?? 0);
                                $totalOpcional = $quantidadeOpcional * $precoOpcional;
                            @endphp
                            <div class="optional-row-minimal">
                                <div>
                                    <strong>{{ $opcional->nome }}</strong>
                                    <small>{{ $opcional->tipo_cobranca == 'por_dia' ? 'Diario' : 'Integral' }} � {{ $quantidadeOpcional }}x</small>
                                </div>
                                <span>R$ {{ number_format($totalOpcional, 2, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endif
        </div>

        <aside class="reservation-minimal__sidebar">
            <article class="minimal-card">
                <div class="minimal-card__header">
                    <h2>Cliente</h2>
                </div>
                <div class="customer-minimal">
                    <div class="customer-minimal__avatar">{{ $clienteIniciais }}</div>
                    <div>
                        <strong>{{ $clienteNome }}</strong>
                        <p>{{ $reserva->usuario->email ?? 'E-mail nao informado' }}</p>
                    </div>
                </div>
                <div class="mini-info-list">
                    <div><span>Telefone</span><strong>{{ $reserva->usuario->telefone ?? '-' }}</strong></div>
                    <div><span>Documento</span><strong>{{ $reserva->usuario->numero_identificacao ?? '-' }}</strong></div>
                    <div><span>Nascimento</span><strong>{{ $reserva->usuario->data_nascimento ? \Carbon\Carbon::parse($reserva->usuario->data_nascimento)->format('d/m/Y') : '-' }}</strong></div>
                </div>
            </article>

            <article class="minimal-card">
                <div class="minimal-card__header">
                    <h2>Resumo</h2>
                </div>
                <div class="mini-info-list mini-info-list--spaced">
                    <div><span>Tarifa diaria</span><strong>R$ {{ number_format($tarifaBase, 2, ',', '.') }}</strong></div>
                    <div><span>Tarifa base</span><strong>R$ {{ number_format($valorTarifaBase, 2, ',', '.') }}</strong></div>
                    <div><span>Opcionais</span><strong>R$ {{ number_format($valorOpcionais, 2, ',', '.') }}</strong></div>
                    <div><span>Desconto</span><strong>{{ number_format($percentualDesconto, 0) }}%</strong></div>
                    <div class="mini-total"><span>Total</span><strong>R$ {{ number_format($valorTotal, 2, ',', '.') }}</strong></div>
                </div>
            </article>

            <article class="minimal-card">
                <div class="minimal-card__header minimal-card__header--between">
                    <h2>NFS-e</h2>
                    <span class="reservation-badge {{ $nfseBadgeClass }}">{{ $nfseStatusLabel }}</span>
                </div>

                @if($nfseAtual)
                    <div class="mini-info-list mini-info-list--spaced">
                        <div><span>Numero</span><strong>{{ $nfseAtual->numero ?? '-' }}</strong></div>
                        <div><span>Ambiente</span><strong>{{ $nfseAtual->ambiente ?? '-' }}</strong></div>
                    </div>

                    @if($nfseErroDetalhado || $nfseMotivo)
                        <div class="nfse-feedback-minimal">
                            {{ $nfseErroDetalhado ?? $nfseMotivo }}
                        </div>
                    @endif

                    @if(!empty($nfseAtual->nfse_id) && (auth()->user()?->hasPermissao('nfse.download') || auth()->user()?->isAdministrador()))
                        <div class="nfse-download-actions">
                            <a href="{{ route('nfse.download', ['id' => $nfseAtual->id, 'tipo' => 'xml']) }}" class="inline-secondary-btn">XML</a>
                            <a href="{{ route('nfse.download', ['id' => $nfseAtual->id, 'tipo' => 'dps']) }}" class="inline-secondary-btn">DPS</a>
                        </div>
                    @endif

                    @if(($nfseAtual->status ?? '') !== 'emitida')
                        <form method="POST" action="{{ route('reserva.emitirNfse', $reserva->id) }}" class="js-nfse-form mt-3">
                            @csrf
                            <button type="submit" class="js-nfse-btn inline-action-btn inline-action-btn--ghost">
                                <i class="fas fa-redo"></i>
                                <span>Tentar emitir</span>
                            </button>
                        </form>
                    @endif
                @else
                    <p class="nfse-empty-minimal">Nenhuma nota vinculada ainda.</p>
                    <form method="POST" action="{{ route('reserva.emitirNfse', $reserva->id) }}" class="js-nfse-form mt-3">
                        @csrf
                        <button type="submit" class="js-nfse-btn inline-action-btn inline-action-btn--ghost">
                            <i class="fas fa-redo"></i>
                            <span>Tentar emitir</span>
                        </button>
                    </form>
                @endif
            </article>
        </aside>
    </div>
</section>
@stop

@section('css')
<style>
    .content-wrapper {
        background: linear-gradient(180deg, #f6f8fb 0%, #eef3f8 100%);
    }
    .reservation-minimal {
        display: grid;
        gap: 18px;
        padding-bottom: 24px;
    }
    .reservation-minimal__hero,
    .minimal-card {
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid #dde5ef;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        backdrop-filter: blur(10px);
    }
    .reservation-minimal__hero {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 18px;
        padding: 22px 24px;
        border-radius: 28px;
    }
    .reservation-minimal__eyebrow {
        display: block;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 6px;
    }
    .reservation-minimal__title-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
    }
    .reservation-minimal__title {
        margin: 0;
        font-size: 2rem;
        font-weight: 800;
        letter-spacing: -.04em;
        color: #0f172a;
    }
    .reservation-minimal__meta {
        display: flex;
        flex-wrap: wrap;
        gap: 10px 16px;
        margin-top: 10px;
        color: #64748b;
        font-size: .92rem;
    }
    .reservation-minimal__hero-stats {
        display: grid;
        grid-template-columns: repeat(2, minmax(120px, 1fr));
        gap: 12px;
        width: min(100%, 320px);
    }
    .mini-stat {
        padding: 16px;
        border-radius: 20px;
        background: linear-gradient(145deg, #0f172a 0%, #1e293b 100%);
        color: #fff;
    }
    .mini-stat span {
        display: block;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .14em;
        color: rgba(226, 232, 240, 0.72);
        margin-bottom: 6px;
    }
    .mini-stat strong {
        display: block;
        font-size: 1.15rem;
        font-weight: 800;
    }
    .reservation-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }
    .reservation-badge.is-success {
        background: rgba(5, 150, 105, 0.12);
        color: #047857;
    }
    .reservation-badge.is-danger {
        background: rgba(220, 38, 38, 0.12);
        color: #b91c1c;
    }
    .reservation-badge.is-warning {
        background: rgba(245, 158, 11, 0.15);
        color: #b45309;
    }
    .reservation-badge.is-neutral {
        background: rgba(100, 116, 139, 0.14);
        color: #475569;
    }
    .reservation-inline-alert {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 18px;
        border-radius: 22px;
        background: #fff7ed;
        border: 1px solid #fed7aa;
    }
    .reservation-inline-alert strong {
        display: block;
        color: #9a3412;
        margin-bottom: 4px;
    }
    .reservation-inline-alert p {
        margin: 0;
        color: #9a3412;
    }
    .inline-action-btn,
    .inline-secondary-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 14px;
        border: 0;
        text-decoration: none;
        font-weight: 700;
    }
    .inline-action-btn {
        background: #0f172a;
        color: #fff;
    }
    .inline-action-btn--ghost {
        width: 100%;
        background: #f8fafc;
        color: #0f172a;
        border: 1px solid #d4dde8;
    }
    .inline-secondary-btn {
        background: #f8fafc;
        color: #0f172a;
        border: 1px solid #d4dde8;
    }
    .inline-action-btn:hover,
    .inline-action-btn:focus,
    .inline-secondary-btn:hover,
    .inline-secondary-btn:focus {
        text-decoration: none;
        color: inherit;
    }
    .reservation-minimal__grid {
        display: grid;
        grid-template-columns: minmax(0, 1.4fr) minmax(300px, .8fr);
        gap: 18px;
        align-items: start;
    }
    .reservation-minimal__main,
    .reservation-minimal__sidebar {
        display: grid;
        gap: 18px;
    }
    .minimal-card {
        padding: 22px;
        border-radius: 26px;
    }
    .minimal-card__header {
        margin-bottom: 16px;
    }
    .minimal-card__header--between {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .minimal-card__header h2 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
    }
    .trip-compact {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
        gap: 18px;
        align-items: center;
    }
    .trip-compact__item {
        display: grid;
        gap: 6px;
    }
    .trip-compact__label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .16em;
        color: #64748b;
    }
    .trip-compact__item strong {
        color: #0f172a;
    }
    .trip-compact__item small {
        color: #64748b;
    }
    .trip-compact__divider {
        width: 44px;
        height: 1px;
        background: linear-gradient(90deg, #cbd5e1 0%, #94a3b8 100%);
    }
    .vehicle-compact {
        display: grid;
        grid-template-columns: 220px minmax(0, 1fr);
        gap: 18px;
        align-items: center;
    }
    .vehicle-compact__media {
        padding: 18px;
        border-radius: 22px;
        background: linear-gradient(145deg, #f8fafc 0%, #eef2ff 100%);
        border: 1px solid #dbe4f0;
    }
    .vehicle-compact__media img {
        width: 100%;
        max-height: 180px;
        object-fit: contain;
        filter: drop-shadow(0 18px 26px rgba(15, 23, 42, 0.14));
    }
    .vehicle-compact__content h3 {
        margin: 0;
        font-size: 1.35rem;
        color: #0f172a;
    }
    .vehicle-compact__content p {
        margin: 6px 0 0;
        color: #64748b;
    }
    .vehicle-compact__chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
    }
    .vehicle-compact__chips span {
        padding: 7px 11px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: .82rem;
        font-weight: 700;
    }
    .vehicle-compact__specs {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-top: 16px;
    }
    .vehicle-compact__specs div,
    .mini-info-list div,
    .optional-row-minimal {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: center;
    }
    .vehicle-compact__specs span,
    .mini-info-list span {
        color: #64748b;
    }
    .vehicle-compact__specs strong,
    .mini-info-list strong,
    .optional-row-minimal strong,
    .optional-row-minimal span {
        color: #0f172a;
    }
    .optional-list-minimal,
    .mini-info-list {
        display: grid;
        gap: 12px;
    }
    .optional-row-minimal,
    .mini-info-list div {
        padding-bottom: 12px;
        border-bottom: 1px solid #ecf1f6;
    }
    .optional-row-minimal:last-child,
    .mini-info-list div:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }
    .optional-row-minimal small {
        display: block;
        color: #64748b;
        margin-top: 3px;
    }
    .customer-minimal {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 14px;
        align-items: center;
        margin-bottom: 16px;
    }
    .customer-minimal__avatar {
        width: 58px;
        height: 58px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
        color: #fff;
        font-size: 1rem;
        font-weight: 800;
    }
    .customer-minimal strong {
        display: block;
        color: #0f172a;
    }
    .customer-minimal p,
    .nfse-empty-minimal {
        margin: 4px 0 0;
        color: #64748b;
    }
    .mini-info-list--spaced {
        gap: 14px;
    }
    .mini-total {
        margin-top: 4px;
        padding-top: 12px;
        border-top: 1px solid #e2e8f0;
    }
    .mini-total span {
        font-weight: 600;
    }
    .mini-total strong {
        font-size: 1.08rem;
        font-weight: 800;
    }
    .nfse-feedback-minimal {
        margin-top: 14px;
        padding: 12px 14px;
        border-radius: 16px;
        background: rgba(255, 237, 213, 0.70);
        color: #9a3412;
        line-height: 1.5;
    }
    .nfse-download-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 14px;
    }
    @media (max-width: 1199px) {
        .reservation-minimal__grid {
            grid-template-columns: 1fr;
        }
        .reservation-minimal__hero {
            flex-direction: column;
        }
        .reservation-minimal__hero-stats {
            width: 100%;
            max-width: none;
        }
    }
    @media (max-width: 767px) {
        .reservation-minimal__hero,
        .minimal-card {
            padding: 18px;
            border-radius: 22px;
        }
        .reservation-minimal__title {
            font-size: 1.55rem;
        }
        .reservation-inline-alert,
        .trip-compact,
        .vehicle-compact {
            grid-template-columns: 1fr;
            display: grid;
        }
        .reservation-inline-alert {
            align-items: flex-start;
        }
        .trip-compact__divider {
            width: 100%;
        }
        .vehicle-compact__specs {
            grid-template-columns: 1fr;
        }
        .mini-stat {
            padding: 14px;
        }
        .vehicle-compact__media {
            max-width: 100%;
        }
        .vehicle-compact__specs div,
        .mini-info-list div,
        .optional-row-minimal {
            flex-direction: column;
            align-items: flex-start;
        }
        .inline-action-btn,
        .inline-secondary-btn {
            width: 100%;
        }
        .nfse-download-actions {
            display: grid;
        }
    }
</style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            const forms = document.querySelectorAll('.js-nfse-form');

            if (!forms.length) {
                return;
            }

            const showToast = (title, text, icon) => {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title,
                        text,
                        icon,
                        timer: 4200,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                    return;
                }

                alert(text || title);
            };

            forms.forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const btn = form.querySelector('.js-nfse-btn');

                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Emitindo...</span>';
                    }

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                            }
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'Falha ao emitir a NFS-e.');
                        }

                        const icon = data.status === 'success' ? 'success' : 'warning';
                        showToast('NFS-e', data.message || 'Processo concluido.', icon);

                        if (data.status === 'success') {
                            setTimeout(() => {
                                window.location.reload();
                            }, 1300);
                        }
                    } catch (error) {
                        showToast('NFS-e', error.message || 'Falha ao emitir a NFS-e.', 'error');
                    } finally {
                        if (btn) {
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-redo"></i><span>Tentar emitir</span>';
                        }
                    }
                });
            });
        })();
    </script>
@stop
