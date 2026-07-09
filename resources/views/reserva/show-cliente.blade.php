@extends('adminlte::page')

@section('title', 'Minha reserva #' . $reserva->id)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $diasReserva = (int) ($reserva->quantidade_dias ?? 0);
    $tarifaBase = (float) ($reserva->tarifa_base ?? 0);
    $valorTarifaBase = (float) ($reserva->valor_total_tarifa_base ?? ($tarifaBase * $diasReserva));
    $valorOpcionais = (float) ($reserva->valor_opcionais ?? 0);
    if ($valorOpcionais <= 0 && $reserva->opcionais?->isNotEmpty()) {
        $valorOpcionais = (float) $reserva->opcionais->sum(fn ($opcional) => (float) ($opcional->pivot->preco_total ?? 0));
    }
    $taxaColeta = (float) ($taxaColeta ?? 0);
    $taxaEntrega = (float) ($taxaEntrega ?? 0);
    $percentualDesconto = (float) ($reserva->percentual_desconto ?? 0);
    $subtotal = $valorTarifaBase + $valorOpcionais + $taxaColeta + $taxaEntrega;
    $valorDesconto = $subtotal * ($percentualDesconto / 100);
    $valorTotal = max(0, $subtotal - $valorDesconto);
    $totalPago = (float) ($reserva->transacoes ?? collect())
        ->filter(fn ($item) => (float) ($item->valor ?? 0) > 0)
        ->sum('valor');
    $saldoPendente = max(0, $valorTotal - $totalPago);
    $pagamentoEfetuado = $saldoPendente <= 0.009;
    $statusNome = strtoupper((string) ($reserva->statusReserva?->nome ?? 'STATUS'));
    $statusSlug = strtolower((string) ($reserva->statusReserva?->nome ?? ''));
    $statusIcon = match ($statusSlug) {
        'confirmada' => 'fas fa-check-circle',
        'cancelada' => 'fas fa-times-circle',
        'criada', 'pendente' => 'fas fa-clock',
        default => 'fas fa-info-circle',
    };
    $statusClass = match ($statusSlug) {
        'confirmada' => 'is-success',
        'cancelada' => 'is-danger',
        'criada', 'pendente' => 'is-warning',
        default => 'is-neutral',
    };
    $inicioFormatado = $reserva->data_inicio ? \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') : '-';
    $fimFormatado = $reserva->data_fim ? \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') : '-';
    $createdAt = $reserva->created_at ? \Carbon\Carbon::parse($reserva->created_at)->format('d/m/Y H:i') : '-';
    $grupoNome = $reserva->grupoFrota?->nome ?? 'Grupo não informado';
    $modeloNome = $reserva->modelos?->nome ?? 'Modelo não informado';
    $estacaoRetirada = $reserva->estacaoRetirada?->nome ?? 'Não informado';
    $estacaoDevolucao = $reserva->estacaoDevolucao?->nome ?? 'Não informado';
    $cupomCodigo = $reserva->cupomDesconto?->codigo ?? null;
    $clienteNome = $reserva->usuario?->nome ?? auth()->user()?->nome ?? 'Cliente';
    $clienteEmail = $reserva->usuario?->email ?? auth()->user()?->email ?? '-';
    $clienteTelefone = $reserva->usuario?->telefone ?? auth()->user()?->telefone ?? '-';
    $podeCancelarMinhaReserva = method_exists($reserva, 'podeCancelarComoCliente') ? $reserva->podeCancelarComoCliente() : false;
    $podeCancelarReserva = method_exists($reserva, 'podeFazerCancelamento') ? $reserva->podeFazerCancelamento() : false;
    $jaSatisfacao = $reserva->satisfacao()->exists();
    $podeResponderSatisfacao = $reserva->contratoCheckout()->exists() && !$jaSatisfacao;
@endphp

<style>
    :root {
        --p: {{ $reserva->empresa?->cor_primaria ?: '#1f4f7a' }};
        --s: {{ $reserva->empresa?->cor_secundaria ?: '#14324d' }};
        --line: #dbe4ee;
        --ink: #0f172a;
        --muted: #64748b;
    }
    .rs-client-page { padding-bottom: 2rem; }
    .rs-client-hero {
        position: relative;
        border-radius: 20px;
        overflow: hidden;
        background: linear-gradient(135deg, #0f172a 0%, #172a45 45%, var(--p) 100%);
        color: #fff;
        box-shadow: 0 18px 40px rgba(15, 23, 42, .15);
        margin-bottom: 18px;
    }
    .rs-client-hero__top { padding: 24px 26px 20px; display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; }
    .rs-client-hero__title { margin: 0 0 6px; font-size: 1.7rem; font-weight: 800; }
    .rs-client-hero__sub { margin: 0; color: rgba(255,255,255,.78); }
    .rs-client-hero__meta { display:flex; flex-wrap:wrap; gap: 10px; margin-top: 14px; }
    .rs-chip {
        display:inline-flex; align-items:center; gap:6px;
        padding: 7px 12px; border-radius: 999px;
        border: 1px solid rgba(255,255,255,.16);
        background: rgba(255,255,255,.08);
        color: #fff; font-size: .8rem; font-weight: 700;
    }
    .rs-client-grid { display:grid; grid-template-columns: 1.15fr .85fr; gap: 14px; }
    .rs-card {
        background:#fff; border:1px solid var(--line); border-radius:18px;
        box-shadow:0 10px 24px rgba(15,23,42,.06); overflow:hidden;
    }
    .rs-card__head {
        padding: 14px 18px; border-bottom:1px solid #eef2f7;
        background: linear-gradient(120deg,#f8fbff,#f1f6ff);
        display:flex; align-items:center; gap:10px;
    }
    .rs-card__icon {
        width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center;
        background: rgba(31,79,122,.12); color: var(--p);
    }
    .rs-card__title { font-size: 1rem; font-weight: 800; color: var(--ink); }
    .rs-card__sub { font-size: .78rem; color: var(--muted); }
    .rs-card__body { padding: 18px; }
    .rs-summary {
        display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap: 12px;
    }
    .rs-info {
        border:1px solid var(--line); border-radius:14px; padding:12px 14px; background:#f8fbff;
    }
    .rs-info__label { font-size:.68rem; font-weight:800; color:#7483a0; text-transform:uppercase; letter-spacing:.08em; margin-bottom:4px; }
    .rs-info__value { font-size:.94rem; font-weight:700; color:var(--ink); line-height:1.35; }
    .rs-route {
        display:grid; grid-template-columns:1fr auto 1fr; gap:10px; align-items:center;
        border:1px solid var(--line); border-radius:16px; padding:16px; background: linear-gradient(135deg,#f8fbff,#f1f6ff);
    }
    .rs-stop { display:flex; align-items:flex-start; gap:10px; }
    .rs-stop--right { flex-direction:row-reverse; text-align:right; }
    .rs-dot { width:11px; height:11px; border-radius:50%; margin-top:4px; flex-shrink:0; }
    .rs-dot--a { background:#10b981; box-shadow:0 0 0 4px rgba(16,185,129,.15); }
    .rs-dot--b { background:#ef4444; box-shadow:0 0 0 4px rgba(239,68,68,.14); }
    .rs-label { font-size:.68rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; }
    .rs-place { font-size:.98rem; font-weight:800; color:var(--ink); margin-top:3px; }
    .rs-date { font-size:.82rem; color:#1d4ed8; font-weight:700; margin-top:2px; }
    .rs-mid { display:flex; flex-direction:column; align-items:center; gap:4px; }
    .rs-mid__line { width:1px; height:16px; background:linear-gradient(180deg,#c7d7f4,#93c5fd); }
    .rs-mid__pill { background:#1e40af; color:#fff; border-radius:999px; font-size:.68rem; font-weight:800; padding:4px 9px; }
    .rs-vehicle {
        display:grid; grid-template-columns: 140px 1fr; gap: 16px; align-items:start;
    }
    .rs-vehicle__img {
        border-radius:16px; overflow:hidden; border:1px solid var(--line); background:#f8fbff;
        min-height: 110px; display:flex; align-items:center; justify-content:center;
    }
    .rs-vehicle__img img { width:100%; height:100%; object-fit:cover; display:block; }
    .rs-vehicle__badges { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:12px; }
    .rs-badge {
        display:inline-flex; align-items:center; gap:6px;
        border-radius:999px; padding:6px 11px; font-size:.78rem; font-weight:800;
        background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;
    }
    .rs-badge--green { background:#f0fdf4; color:#166534; border-color:#bbf7d0; }
    .rs-badge--blue { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
    .rs-badge--amber { background:#fffbeb; color:#b45309; border-color:#fde68a; }
    .rs-price {
        display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 12px;
        margin-top: 14px;
    }
    .rs-price__box {
        border:1px solid var(--line); border-radius:14px; background:#f8fbff; padding:12px 14px;
    }
    .rs-price__label { font-size:.68rem; font-weight:800; color:#7483a0; text-transform:uppercase; letter-spacing:.08em; margin-bottom:4px; }
    .rs-price__value { font-size:1.02rem; font-weight:900; color:var(--ink); }
    .rs-actions { display:flex; flex-wrap:wrap; gap:10px; margin-top:16px; }
    .rs-btn {
        display:inline-flex; align-items:center; gap:8px; border-radius:999px;
        padding:10px 16px; font-weight:800; text-decoration:none; border:1px solid var(--line);
    }
    .rs-btn--primary { background: var(--p); color:#fff; border-color: var(--p); }
    .rs-btn--light { background:#fff; color:var(--ink); }
    .rs-note {
        border:1px solid #dbeafe; background:#f8fbff; color:#334155;
        border-radius:14px; padding:14px 16px; font-size:.92rem; line-height:1.6;
    }
    .rs-list { display:grid; gap:10px; }
    .rs-list__item {
        border:1px solid var(--line); border-radius:14px; background:#fff; padding:12px 14px;
    }
    .rs-list__label { font-size:.68rem; font-weight:800; color:#7483a0; text-transform:uppercase; letter-spacing:.08em; margin-bottom:3px; }
    .rs-list__value { font-size:.92rem; color:var(--ink); font-weight:700; line-height:1.45; }
    .rs-status {
        display:inline-flex; align-items:center; gap:8px;
        border-radius:999px; padding:8px 13px; font-size:.76rem; font-weight:900;
        text-transform:uppercase; letter-spacing:.06em;
    }
    .rs-status.is-success { background:rgba(16,185,129,.12); color:#047857; border:1px solid rgba(16,185,129,.22); }
    .rs-status.is-warning { background:rgba(245,158,11,.14); color:#b45309; border:1px solid rgba(245,158,11,.26); }
    .rs-status.is-danger { background:rgba(239,68,68,.12); color:#b91c1c; border:1px solid rgba(239,68,68,.22); }
    .rs-status.is-neutral { background:rgba(100,116,139,.12); color:#475569; border:1px solid rgba(100,116,139,.18); }
    .rs-survey { display:grid; gap:14px; }
    .rs-survey__group { display:grid; gap:8px; }
    .rs-survey__label { font-size:.92rem; font-weight:700; color:var(--ink); }
    .rs-survey__options { display:flex; flex-wrap:wrap; gap:8px; }
    .rs-survey__option { position:relative; display:inline-flex; }
    .rs-survey__option input { position:absolute; opacity:0; pointer-events:none; }
    .rs-survey__option span {
        min-width:42px; height:40px; padding:0 10px; border-radius:999px;
        border:1px solid #cbd5e1; display:inline-flex; align-items:center; justify-content:center;
        font-weight:800; background:#fff; color:#0f172a; transition:all .15s ease;
        box-shadow:0 1px 2px rgba(15,23,42,.04);
    }
    .rs-survey__option input:checked + span { transform:translateY(-1px) scale(1.03); border-color:rgba(15,23,42,.2); box-shadow:0 10px 18px rgba(15,23,42,.08); }
    .rs-survey__textarea {
        width:100%; min-height:84px; border-radius:12px; border:1px solid #cbd5e1; background:#fff;
        color:#0f172a; padding:10px 12px; resize:vertical; outline:none;
    }
    .rs-nps--red { background:#B91C1C !important; color:#fff !important; border-color:#991B1B !important; }
    .rs-nps--orange { background:#F59E0B !important; color:#fff !important; border-color:#D97706 !important; }
    .rs-nps--yellow { background:#FACC15 !important; color:#111827 !important; border-color:#EAB308 !important; }
    .rs-nps--lightgreen { background:#4ADE80 !important; color:#064E3B !important; border-color:#22C55E !important; }
    .rs-nps--green { background:#16A34A !important; color:#fff !important; border-color:#15803D !important; }
    .rs-nps--darkgreen { background:#166534 !important; color:#fff !important; border-color:#14532D !important; }
    @media (max-width: 992px) {
        .rs-client-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .rs-summary { grid-template-columns: 1fr; }
        .rs-route { grid-template-columns: 1fr; }
        .rs-mid { display:none; }
        .rs-stop--right { flex-direction:row; text-align:left; }
        .rs-vehicle { grid-template-columns: 1fr; }
        .rs-price { grid-template-columns: 1fr; }
    }
</style>

<div class="rs-client-page">
    <div class="rs-client-hero">
        <div class="rs-client-hero__top">
            <div>
                <h1 class="rs-client-hero__title">Minha reserva #{{ $reserva->id }}</h1>
                <p class="rs-client-hero__sub">Resumo simples com as informações mais importantes da sua viagem.</p>
                <div class="rs-client-hero__meta">
                    <span class="rs-chip"><i class="{{ $statusIcon }}"></i> {{ $statusNome }}</span>
                    <span class="rs-chip"><i class="fas fa-calendar-plus"></i> Criada em {{ $createdAt }}</span>
                    <span class="rs-chip"><i class="fas fa-user"></i> {{ $clienteNome }}</span>
                </div>
            </div>
            <div class="rs-status {{ $statusClass }}">
                <i class="{{ $statusIcon }}"></i>
                {{ $reserva->statusReserva?->nome ?? 'Status não informado' }}
            </div>
        </div>
    </div>

    <div class="rs-client-grid">
        <div class="rs-main">
            <div class="rs-card mb-3">
                <div class="rs-card__head">
                    <div class="rs-card__icon"><i class="fas fa-route"></i></div>
                    <div>
                        <div class="rs-card__title">Sua viagem</div>
                        <div class="rs-card__sub">Retirada, devolução e período contratado</div>
                    </div>
                </div>
                <div class="rs-card__body">
                    <div class="rs-route">
                        <div class="rs-stop">
                            <div class="rs-dot rs-dot--a"></div>
                            <div>
                                <div class="rs-label">Retirada</div>
                                <div class="rs-place">{{ $estacaoRetirada }}</div>
                                <div class="rs-date">{{ $inicioFormatado }}</div>
                            </div>
                        </div>
                        <div class="rs-mid">
                            <div class="rs-mid__line"></div>
                            <div class="rs-mid__pill">{{ $diasReserva }} {{ $diasReserva === 1 ? 'dia' : 'dias' }}</div>
                            <div class="rs-mid__line"></div>
                        </div>
                        <div class="rs-stop rs-stop--right">
                            <div class="rs-dot rs-dot--b"></div>
                            <div>
                                <div class="rs-label">Devolução</div>
                                <div class="rs-place">{{ $estacaoDevolucao }}</div>
                                <div class="rs-date">{{ $fimFormatado }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="rs-actions">
                        <a href="{{ route('reserva.minhas') }}" class="rs-btn rs-btn--light">
                            <i class="fas fa-arrow-left"></i> Voltar para minhas reservas
                        </a>
                        @if($podeCancelarMinhaReserva)
                            <form method="POST" action="{{ route('reserva.cancelar', $reserva->id) }}" class="m-0" onsubmit="return confirm('Deseja realmente cancelar esta reserva?');">
                                @csrf
                                <button type="submit" class="rs-btn" style="background:#fee2e2;color:#991b1b;border-color:#fecaca;">
                                    <i class="fas fa-ban"></i> Cancelar reserva
                                </button>
                            </form>
                        @elseif($podeCancelarReserva)
                            <span class="rs-btn" data-toggle="tooltip" title="O cancelamento só fica disponível até 24 horas após a criação da reserva." style="opacity:.5;cursor:not-allowed;">
                                <i class="fas fa-ban"></i> Cancelar reserva
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rs-card mb-3">
                <div class="rs-card__head">
                    <div class="rs-card__icon"><i class="fas fa-car"></i></div>
                    <div>
                        <div class="rs-card__title">Veículo reservado</div>
                        <div class="rs-card__sub">Grupo, modelo e informações essenciais</div>
                    </div>
                </div>
                <div class="rs-card__body">
                    <div class="rs-vehicle">
                        <div class="rs-vehicle__img">
                            <img src="{{ $reserva->grupoFrota?->imagem_url ?: asset('assets/imgs/logo-sem-texto.png') }}" alt="{{ $grupoNome }}">
                        </div>
                        <div>
                            <div class="rs-vehicle__badges">
                                <span class="rs-badge rs-badge--blue"><i class="fas fa-layer-group"></i> {{ $grupoNome }}</span>
                                <span class="rs-badge"><i class="fas fa-car-side"></i> {{ $modeloNome }}</span>
                                @if(!empty($reserva->modelos?->combustivel))
                                    <span class="rs-badge rs-badge--green"><i class="fas fa-gas-pump"></i> {{ $reserva->modelos->combustivel }}</span>
                                @endif
                                @if(!empty($reserva->modelos?->transmissao))
                                    <span class="rs-badge rs-badge--amber"><i class="fas fa-cogs"></i> {{ $reserva->modelos->transmissao }}</span>
                                @endif
                            </div>
                            <div class="rs-note">
                                A imagem acima representa o grupo/modelo reservado. Se houver substituição de veículo, a equipe pode informar no balcão de atendimento.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rs-card">
                <div class="rs-card__head">
                    <div class="rs-card__icon"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div>
                        <div class="rs-card__title">Valores da reserva</div>
                        <div class="rs-card__sub">Resumo financeiro simples para você acompanhar</div>
                    </div>
                </div>
                <div class="rs-card__body">
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
                    </div>
                    <div style="margin-top:12px" class="rs-list">
                        <div class="rs-list__item">
                            <div class="rs-list__label">Pagamento</div>
                            <div class="rs-list__value">{{ $pagamentoEfetuado ? 'Pago' : 'Pendente' }}</div>
                        </div>
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
                </div>
            </div>

            <div class="rs-card mt-3">
                <div class="rs-card__head">
                    <div class="rs-card__icon"><i class="fas fa-star"></i></div>
                    <div>
                        <div class="rs-card__title">Sua avaliação</div>
                        <div class="rs-card__sub">Pode ser preenchida apenas uma vez</div>
                    </div>
                </div>
                <div class="rs-card__body">
                    @if($podeResponderSatisfacao)
                        <form method="POST" action="{{ route('reserva.satisfacao.store', $reserva->id) }}" class="rs-survey">
                            @csrf
                            <div class="rs-survey__group">
                                <label class="rs-survey__label">1. Como foi sua experiência geral?</label>
                                <div class="rs-survey__options">
                                    @for($i = 1; $i <= 5; $i++)
                                        <label class="rs-survey__option">
                                            <input type="radio" name="satisfacao_geral" value="{{ $i }}" required>
                                            <span>{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>

                            <div class="rs-survey__group">
                                <label class="rs-survey__label">2. Foi fácil concluir a reserva?</label>
                                <div class="rs-survey__options">
                                    @for($i = 1; $i <= 5; $i++)
                                        <label class="rs-survey__option">
                                            <input type="radio" name="facilidade_reserva" value="{{ $i }}" required>
                                            <span>{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>

                            <div class="rs-survey__group">
                                <label class="rs-survey__label">3. De 0 a 10, quanto você recomendaria a empresa?</label>
                                <div class="rs-survey__options rs-survey__options--nps">
                                    @for($i = 0; $i <= 10; $i++)
                                        @php
                                            $npsClass = match (true) {
                                                $i <= 2 => 'rs-nps rs-nps--red',
                                                $i <= 5 => 'rs-nps rs-nps--orange',
                                                $i <= 7 => 'rs-nps rs-nps--yellow',
                                                $i === 8 => 'rs-nps rs-nps--lightgreen',
                                                $i === 9 => 'rs-nps rs-nps--green',
                                                default => 'rs-nps rs-nps--darkgreen',
                                            };
                                        @endphp
                                        <label class="rs-survey__option">
                                            <input type="radio" name="nps_nota" value="{{ $i }}" required>
                                            <span class="{{ $npsClass }}">{{ $i }}</span>
                                        </label>
                                    @endfor
                                </div>
                            </div>

                            <div class="rs-survey__group mb-0">
                                <label class="rs-survey__label" for="comentario">O que podemos melhorar? <span style="font-weight:400;">(opcional)</span></label>
                                <textarea id="comentario" name="comentario" class="rs-survey__textarea" placeholder="Deixe uma observação rápida."></textarea>
                            </div>

                            <div class="rs-actions" style="margin-top:14px;">
                                <button type="submit" class="rs-btn rs-btn--primary">
                                    <i class="fas fa-paper-plane"></i> Enviar avaliação
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="rs-note">
                            @if($jaSatisfacao)
                                Obrigado! Sua avaliação já foi enviada e não pode ser alterada.
                            @else
                                A avaliação ficará disponível após a conclusão da reserva.
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <aside class="rs-sidebar">
            <div class="rs-card mb-3">
                <div class="rs-card__head">
                    <div class="rs-card__icon"><i class="fas fa-user"></i></div>
                    <div>
                        <div class="rs-card__title">Seus dados</div>
                        <div class="rs-card__sub">Informações do titular da reserva</div>
                    </div>
                </div>
                <div class="rs-card__body">
                    <div class="rs-list">
                        <div class="rs-list__item">
                            <div class="rs-list__label">Nome</div>
                            <div class="rs-list__value">{{ $clienteNome }}</div>
                        </div>
                        <div class="rs-list__item">
                            <div class="rs-list__label">E-mail</div>
                            <div class="rs-list__value">{{ $clienteEmail }}</div>
                        </div>
                        <div class="rs-list__item">
                            <div class="rs-list__label">Telefone</div>
                            <div class="rs-list__value">{{ $clienteTelefone }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rs-card mb-3">
                <div class="rs-card__head">
                    <div class="rs-card__icon"><i class="fas fa-list-check"></i></div>
                    <div>
                        <div class="rs-card__title">Resumo rápido</div>
                        <div class="rs-card__sub">As informações que mais importam</div>
                    </div>
                </div>
                <div class="rs-card__body">
                    <div class="rs-list">
                        <div class="rs-list__item">
                            <div class="rs-list__label">Reserva</div>
                            <div class="rs-list__value">#{{ $reserva->id }}</div>
                        </div>
                        <div class="rs-list__item">
                            <div class="rs-list__label">Criada em</div>
                            <div class="rs-list__value">{{ $createdAt }}</div>
                        </div>
                        <div class="rs-list__item">
                            <div class="rs-list__label">Duração</div>
                            <div class="rs-list__value">{{ $diasReserva }} {{ $diasReserva === 1 ? 'dia' : 'dias' }}</div>
                        </div>
                        <div class="rs-list__item">
                            <div class="rs-list__label">Status</div>
                            <div class="rs-list__value">{{ $reserva->statusReserva?->nome ?? 'Não informado' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>
@stop
