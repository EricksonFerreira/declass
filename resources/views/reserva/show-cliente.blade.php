@extends('adminlte::page')

@section('title', 'Minha reserva #' . $reserva->codigoReserva->codigo)

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
    $danosVeiculo = $reserva->veiculo?->veiculoDanos ?? collect();
    $podeCancelarMinhaReserva = method_exists($reserva, 'podeCancelarComoCliente') ? $reserva->podeCancelarComoCliente() : false;
    $podeCancelarReserva = method_exists($reserva, 'podeFazerCancelamento') ? $reserva->podeFazerCancelamento() : false;
    $usuarioEhCriador = auth()->check() && (int) auth()->id() === (int) ($reserva->user_id ?? 0);
    $podeExibirBotaoCancelar = ($usuarioEhCriador || $podeCancelarReserva) && !($reserva->contratoCheckin && $reserva->contratoCheckin->assinado === 'S');
    $textoBotaoCancelar = ($usuarioEhCriador && !$podeCancelarMinhaReserva)
        ? 'O cancelamento so fica disponivel ate 24 horas apos a criacao da reserva.'
        : 'Cancelar esta reserva.';
    $jaSatisfacao = $reserva->satisfacao()->exists();
    $checkoutAssinado = $reserva->contratoCheckout()->exists()
        && (
            !empty($reserva->contratoCheckout?->assinatura)
            || !empty($reserva->contratoCheckout?->data_assinatura)
            || (($reserva->contratoCheckout?->assinado ?? 'N') === 'S')
        );
    $podeResponderSatisfacao = $checkoutAssinado && !$jaSatisfacao;
    $checkinExiste = (bool) $reserva->contratoCheckin;
    $checkinAssinado = $checkinExiste && ($reserva->contratoCheckin->assinado ?? '') === 'S';
    $checkoutExiste = (bool) $reserva->contratoCheckout;
    $checkoutAssinado = $checkoutExiste && (
        !empty($reserva->contratoCheckout->assinatura)
        || !empty($reserva->contratoCheckout->data_assinatura)
        || (($reserva->contratoCheckout->assinado ?? 'N') === 'S')
    );
    $checkoutPrecisaAssinar = $checkoutExiste && ! $checkoutAssinado;
    $souDonoDaReserva = auth()->check() && (int) auth()->id() === (int) ($reserva->user_id ?? $reserva->usuario_id ?? 0);
    $precisaAssinarCheckin = $checkinExiste && ! $checkinAssinado;
    $totalMultas = $reserva->multas?->count() ?? 0;
    $totalSinistros = $reserva->sinistros?->count() ?? 0;
    $moduloMultas = true;
    $moduloNfse = (bool) ($reserva->empresa?->planoAtivo?->plano?->modulo_nfse ?? false);
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
    .rs-card__header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 18px;
        padding-bottom: 14px;
        border-bottom: 1px solid #f1f5f9;
    }
    .rs-card__header h2 {
        margin: 0;
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--ink);
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
    .rs-btn--success { background: #047857; color:#fff; border-color: var(--p); }
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
    .rs-survey__option input:checked + span {
        transform: translateY(-2px) scale(1.05);
        border: 3px solid #000000 !important;
        box-shadow: 0 10px 20px rgba(0,0,0,0.25);
    }
    .rs-survey__textarea {
        width:100%; min-height:84px; border-radius:12px; border:1px solid #cbd5e1; background:#fff;
        color:#0f172a; padding:10px 12px; resize:vertical; outline:none;
    }
    .rs-tabs-wrapper {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 18px;
        box-shadow: 0 10px 24px rgba(15,23,42,.05);
        overflow: hidden;
        min-width: 0;
    }
    .rs-nav-tabs.nav-tabs {
        border-bottom: 1px solid #e5eef8;
        padding: 0 10px;
        gap: 6px;
        overflow-x: auto;
        overflow-y: hidden;
        flex-wrap: nowrap;
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .rs-nav-tabs.nav-tabs::-webkit-scrollbar { display: none; }
    .rs-nav-tabs .nav-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 0;
        border-bottom: 3px solid transparent;
        color: #475569;
        font-weight: 700;
        padding: 14px 16px;
        white-space: nowrap;
        border-radius: 0;
        flex: 0 0 auto;
    }
    .rs-nav-tabs .nav-link.active {
        color: var(--p);
        border-bottom-color: var(--p);
        background: transparent;
    }
    .rs-tab-badge {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width: 20px;
        height: 20px;
        padding: 0 7px;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 800;
        margin-left: 2px;
    }
    .rs-tab-badge--green { background: #dcfce7; color: #15803d; }
    .rs-tab-badge--amber { background: #fef3c7; color: #b45309; }
    .rs-tab-badge--red { background: #fee2e2; color: #dc2626; }
    .rs-tab-badge--slate { background: #f1f5f9; color: #475569; }
    .rs-tab-content { padding: 18px; background: #f8fbff; }
    .rs-nps--red { background:#B91C1C !important; color:#fff !important; border-color:#991B1B !important; }
    .rs-nps--orange { background:#F59E0B !important; color:#fff !important; border-color:#D97706 !important; }
    .rs-nps--yellow { background:#FACC15 !important; color:#111827 !important; border-color:#EAB308 !important; }
    .rs-nps--lightgreen { background:#4ADE80 !important; color:#064E3B !important; border-color:#22C55E !important; }
    .rs-nps--green { background:#16A34A !important; color:#fff !important; border-color:#15803D !important; }
    .rs-nps--darkgreen { background:#166534 !important; color:#fff !important; border-color:#14532D !important; }
    @media (max-width: 992px) {
        .rs-client-grid { grid-template-columns: 1fr; }
        .rs-client-hero__top { padding: 20px 18px 18px; }
        .rs-client-hero__title { font-size: 1.45rem; }
        .rs-client-hero .rs-chip { width: 100%; justify-content: center; }
        .rs-tabs-wrapper { border-radius: 16px; }
        .rs-nav-tabs.nav-tabs {
            padding: 0 8px;
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x proximity;
        }
        .rs-nav-tabs .nav-link {
            scroll-snap-align: start;
            padding: 12px 12px;
        }
        .rs-card__body { padding: 16px; }
    }
    @media (max-width: 768px) {
        .rs-summary { grid-template-columns: 1fr; }
        .rs-route { grid-template-columns: 1fr; }
        .rs-mid { display:none; }
        .rs-stop--right { flex-direction:row; text-align:left; }
        .rs-vehicle { grid-template-columns: 1fr; }
        .rs-price { grid-template-columns: 1fr; }
        .rs-client-hero__top { padding: 18px 16px 16px; }
        .rs-client-hero__title { font-size: 1.28rem; line-height: 1.15; }
        .rs-client-hero__sub { font-size: .92rem; }
        .rs-chip { width: 100%; justify-content: center; }
        .rs-card__head { padding: 12px 14px; align-items:flex-start; }
        .rs-card__icon { width:36px; height:36px; border-radius:10px; }
        .rs-card__body { padding: 14px; }
        .rs-info__value, .rs-list__value { word-break: break-word; }
        .rs-vehicle__img { min-height: 180px; }
        .rs-price__value { font-size: .98rem; }
        .rs-nav-tabs .nav-link span { font-size: .84rem; }
        .rs-tab-badge { font-size: .66rem; padding: 3px 8px; }
    }
    @media (max-width: 576px) {
        .rs-client-page { padding-left: 0; padding-right: 0; }
        .rs-client-hero { border-radius: 16px; }
        .rs-client-grid { gap: 12px; }
        .rs-card, .rs-tabs-wrapper { border-radius: 14px; }
        .rs-nav-tabs.nav-tabs { gap: 4px; }
        .rs-nav-tabs .nav-link {
            min-height: 48px;
            padding: 10px 10px;
        }
        .rs-nav-tabs .nav-link i { font-size: .92rem; }
        .rs-nav-tabs .nav-link span { font-size: .8rem; }
        .rs-summary,
        .rs-price,
        .rs-vehicle {
            gap: 10px;
        }
        .rs-actions { flex-direction: column; }
        .rs-btn {
            width: 100%;
            justify-content: center;
        }
    }

    /* Checkin/checkout single column layout */
    .rs-checkin-layout {
        display: grid;
        gap: 20px;
    }

    /* ── Card Contrato de Check-in ── */
    .rs-ci-card {
        border-radius: 18px;
        border: 1px solid rgba(0,0,0,.07);
        padding: 20px 24px;
        box-shadow: 0 4px 18px rgba(15,23,42,.07);
        margin-bottom: 20px;
    }
    .rs-ci-card__top {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 16px;
    }
    .rs-ci-card__icon {
        width: 44px; height: 44px;
        border-radius: 12px;
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.1rem; flex-shrink: 0;
    }
    .rs-ci-card__info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .rs-ci-card__title {
        font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0;
    }
    .rs-ci-card__badge {
        display: inline-flex; align-items: center;
        padding: 4px 12px; border-radius: 999px;
        font-size: .76rem; font-weight: 700;
        width: fit-content;
    }
    .rs-ci-card__details {
        display: flex; flex-wrap: wrap; gap: 6px 0;
        margin-bottom: 16px;
        border-top: 1px solid rgba(0,0,0,.07);
        padding-top: 14px;
    }
    .rs-ci-detail {
        width: 50%; display: flex; flex-direction: column;
        padding: 4px 12px 4px 0;
    }
    .rs-ci-detail span { font-size: .75rem; color: #64748b; margin-bottom: 2px; }
    .rs-ci-detail strong { font-size: .88rem; color: #0f172a; }
    .rs-ci-card__actions {
        display: flex; flex-wrap: wrap; gap: 8px;
        border-top: 1px solid rgba(0,0,0,.07);
        padding-top: 14px;
    }
    .rs-ci-btn {
        display: inline-flex; align-items: center; gap: 7px;
        border: none; border-radius: 10px;
        padding: 9px 16px; font-size: .83rem; font-weight: 700;
        cursor: pointer; text-decoration: none;
        transition: opacity .15s, transform .15s;
    }
    .rs-ci-btn:hover { opacity:.82; transform:translateY(-1px); text-decoration:none; }
    .rs-ci-btn--view   { background:rgba(59,130,246,.13);  color:#1d4ed8; }
    .rs-ci-btn--sign   { background:rgba(16,185,129,.13);  color:#047857; }
    .rs-ci-btn--edit   { background:rgba(245,158,11,.14);  color:#92400e; }
    .rs-ci-btn--delete { background:rgba(239,68,68,.11);   color:#b91c1c; }
    .rs-ci-btn--disabled { background:rgba(100,116,139,.13); color:#64748b; cursor:not-allowed; }
    .rs-ci-btn--disabled:hover { opacity:1; transform:none; }

    /* ── Info Rows ── */
    .rs-info-rows {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 14px;
    }
    .rs-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 8px;
        border-bottom: 1px solid #f1f5f9;
    }
    .rs-info-row span { font-size: 0.82rem; color: #64748b; }
    .rs-info-row strong { font-size: 0.88rem; color: #0f172a; }

    /* ── Empty State ── */
    .rs-empty {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 20px;
        color: #94a3b8;
        text-align: center;
    }
    .rs-empty i {
        font-size: 1.8rem;
    }
    .rs-empty span {
        font-size: 0.8rem;
    }

    /* ── Card adjustment for admin-style headers ── */
    .rs-card:has(.rs-card__header) {
        padding: 20px 22px;
        overflow: visible;
    }

    /* ── Lightbox ── */
    .rs-lightbox {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .rs-lightbox__backdrop {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.88);
        cursor: pointer;
    }
    .rs-lightbox__inner {
        position: relative;
        z-index: 1;
        max-width: 90vw;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }
    .rs-lightbox__img {
        max-width: 90vw;
        max-height: 80vh;
        border-radius: 16px;
        box-shadow: 0 24px 60px rgba(0,0,0,0.5);
        object-fit: contain;
    }
    .rs-lightbox__label {
        color: #fff;
        font-size: 0.9rem;
        font-weight: 600;
        letter-spacing: 0.06em;
    }
    .rs-lightbox__close {
        position: absolute;
        top: -44px;
        right: 0;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(255,255,255,0.15);
        border: none;
        color: #fff;
        font-size: 1rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s;
    }
    .rs-lightbox__close:hover {
        background: rgba(255,255,255,0.3);
    }
    .rs-photo-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin: 10px 0;
    }
    .rs-photo-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }
    .rs-photo-frame {
        position: relative;
        cursor: pointer;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--line);
        background: #f8fbff;
        width: 100px;
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .rs-photo-thumb {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .rs-photo-zoom {
        position: absolute;
        inset: 0;
        background: rgba(15,23,42,0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        opacity: 0;
        transition: opacity 0.15s;
    }
    .rs-photo-frame:hover .rs-photo-zoom {
        opacity: 1;
    }
    .rs-photo-placeholder {
        width: 100px;
        height: 100px;
        border-radius: 12px;
        border: 1px dashed var(--line);
        background: #f8fbff;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: var(--muted);
        font-size: 0.8rem;
        gap: 4px;
    }
    .rs-photo-label {
        font-size: 0.72rem;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
    }

    /* ── Histórico ── */
    .rs-historico-card .rs-card__header { display:flex; align-items:center; gap:10px; user-select:none; }
    .rs-timeline { padding: 1rem 1.5rem; }
    .rs-tl-item {
        display: flex;
        gap: 14px;
        padding-bottom: 1.4rem;
        position: relative;
    }
    .rs-tl-item:not(:last-child)::before {
        content:'';
        position:absolute;
        left: 17px;
        top: 36px;
        bottom: 0;
        width: 2px;
        background: #e2e8f0;
    }
    .rs-tl-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: .85rem;
        flex-shrink: 0;
        color: #fff;
    }
    .rs-tl-icon--info    { background: #3b82f6; }
    .rs-tl-icon--success { background: #10b981; }
    .rs-tl-icon--warning { background: #f59e0b; }
    .rs-tl-icon--danger  { background: #ef4444; }
    .rs-tl-body { flex:1; min-width:0; }
    .rs-tl-acao {
        font-size: .88rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 2px;
    }
    .rs-tl-meta {
        font-size: .76rem;
        color: #64748b;
        margin-bottom: 4px;
    }
    .rs-tl-detalhes {
        font-size: .78rem;
        color: #475569;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 6px 10px;
        margin-top: 4px;
    }
    .rs-tl-table { width:100%; border-collapse:collapse; }
    .rs-tl-table tr + tr td { border-top: 1px solid #f1f5f9; }
    .rs-tl-table td { padding: 3px 8px 3px 0; vertical-align:top; font-size:.78rem; }
    .rs-tl-table__key { color:#94a3b8; white-space:nowrap; font-weight:600; width:35%; padding-right:10px; }
    .rs-tl-bloco { margin-top:6px; }
    .rs-tl-bloco__title { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin-bottom:3px; padding-bottom:2px; border-bottom:1px solid #e2e8f0; }
    .rs-tl-pre { margin:0; font-size:.72rem; background:#f1f5f9; border-radius:4px; padding:3px 6px; white-space:pre-wrap; word-break:break-all; }
    .rs-tl-item--clickable { cursor:pointer; transition:background .15s ease; border-radius:8px; margin-left:-8px; padding-left:8px; padding-right:8px; }
    .rs-tl-item--clickable:hover { background:#f1f5f9; }
    .rs-tl-chevron { display:flex; align-items:center; color:#94a3b8; font-size:.75rem; flex-shrink:0; padding-left:4px; }
    .rs-tl-details-badge { display:inline-flex; align-items:center; color:#3b82f6; font-weight:600; }
</style>

<div class="rs-client-page">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 12px; margin-bottom: 18px;">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 12px; margin-bottom: 18px;">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="rs-client-hero">
        <div class="rs-client-hero__top">
            <div>
                <h1 class="rs-client-hero__title">Minha reserva #{{ $reserva->codigoReserva->codigo }}</h1>
                <p class="rs-client-hero__sub">Resumo simples com as informações mais importantes da sua viagem.</p>
                <div class="rs-client-hero__meta">
                    <span class="rs-chip"><i class="{{ $statusIcon }}"></i> {{ $statusNome }}</span>
                    <span class="rs-chip"><i class="fas fa-calendar-plus"></i> Criada em {{ $createdAt }}</span>
                    <span class="rs-chip"><i class="fas fa-user"></i> {{ $clienteNome }}</span>
                    @if($podeResponderSatisfacao)
                        <a href="#card-satisfacao" class="rs-chip" style="background: #eab308; border-color: #eab308; cursor: pointer; text-decoration: none;" onclick="document.getElementById('card-satisfacao').scrollIntoView({behavior: 'smooth'}); return false;">
                            <i class="fas fa-exclamation-circle text-dark"></i>
                            <strong style="color: #000;">Avaliação Pendente - Clique para responder</strong>
                        </a>
                    @elseif($jaSatisfacao)
                        <span class="rs-chip" style="background: rgba(16,185,129,.2); border-color: #10b981; color: #dcfce7;">
                            <i class="fas fa-check-circle text-success"></i>
                            <strong>Avaliação Enviada</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="rs-status {{ $statusClass }}">
                <i class="{{ $statusIcon }}"></i>
                {{ $reserva->statusReserva?->nome ?? 'Status não informado' }}
            </div>
        </div>
    </div>

    <div class="rs-card mb-3">
        <div class="rs-card__head">
            <div class="rs-card__icon"><i class="fas fa-stream"></i></div>
            <div>
                <div class="rs-card__title">Etapas da reserva</div>
                <div class="rs-card__sub">Acompanhe o progresso do pagamento até a devolução</div>
            </div>
        </div>
        <div class="rs-card__body">
            <x-reserva.etapas :reserva="$reserva" />
        </div>
    </div>

    @if($precisaAssinarCheckin)
        <div class="rs-card mb-3" style="border-left:4px solid #f59e0b;">
            <div class="rs-card__head">
                <div class="rs-card__icon"><i class="fas fa-file-signature"></i></div>
                <div>
                    <div class="rs-card__title">Assinatura pendente do check-in</div>
                    <div class="rs-card__sub">
                        {{ $souDonoDaReserva ? 'Você precisa confirmar o termo de check-in para concluir esta etapa.' : 'O contrato de check-in ainda não foi assinado.' }}
                    </div>
                </div>
            </div>
            <div class="rs-card__body">
                <div class="rs-note" style="margin-bottom:12px;">
                    O check-in já foi criado. Para seguir com a reserva, o contrato precisa ser assinado e, se você for o titular, o aceite do termo será exigido na tela de assinatura.
                </div>
                <div class="rs-actions">
                    <a href="{{ route('reserva.minhas.assinar-checkin', $reserva->codigoReserva?->codigo ?? $reserva->id) }}" class="rs-btn rs-btn--success">
                        <i class="fas fa-signature"></i>
                        <span>Assinar check-in</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    @if($checkoutPrecisaAssinar)
        <div class="rs-card mb-3" style="border-left:4px solid #1d4ed8;">
            <div class="rs-card__head">
                <div class="rs-card__icon"><i class="fas fa-file-signature"></i></div>
                <div>
                    <div class="rs-card__title">Check-out precisa de assinatura</div>
                    <div class="rs-card__sub">O check-out já foi gerado, mas ainda não foi assinado.</div>
                </div>
            </div>
            <div class="rs-card__body">
                <div class="rs-note">
                    Assim que a assinatura do check-out for concluída, a etapa será marcada como assinada e o fluxo seguirá normalmente.
                </div>
                  <div class="rs-actions">
                    <a href="{{ route('reserva.minhas.assinar-checkout', $reserva->codigoReserva?->codigo ?? $reserva->id) }}" class="rs-btn rs-btn--success">
                        <i class="fas fa-signature"></i>
                        <span>Assinar check-out</span>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="rs-client-grid">
        <div class="rs-main">
            <div class="rs-tabs-wrapper">
                <ul class="nav nav-tabs rs-nav-tabs" id="reservaMainTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="tab-visao-geral" data-toggle="tab" href="#pane-visao-geral" role="tab">
                            <i class="fas fa-home"></i>
                            <span>Visão Geral</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-checkin" data-toggle="tab" href="#pane-checkin" role="tab">
                            <i class="fas fa-file-signature"></i>
                            <span>Check-in</span>
                            @if($reserva->contratoCheckin)
                                @php $ciAssinadoTab = $reserva->contratoCheckin->assinado === 'S'; @endphp
                                <span class="rs-tab-badge {{ $ciAssinadoTab ? 'rs-tab-badge--green' : 'rs-tab-badge--amber' }}">
                                    {{ $ciAssinadoTab ? 'Assinado' : 'Pendente' }}
                                </span>
                            @endif
                            </a>
                        </li >
                    @if($reserva->contratoCheckin)
                    <li class="nav-item">
                        <a class="nav-link" id="tab-checkout" data-toggle="tab" href="#pane-checkout" role="tab">
                            <i class="fas fa-flag-checkered"></i>
                            <span>Check-out</span>
                            @if($checkoutExiste)
                                <span class="rs-tab-badge {{ $checkoutAssinado ? 'rs-tab-badge--green' : 'rs-tab-badge--amber' }}">
                                    {{ $checkoutAssinado ? 'Assinado' : 'Precisa assinar' }}
                                </span>
                            @else
                                <span class="rs-tab-badge rs-tab-badge--amber">Pendente</span>
                            @endif
                        </a>
                    </li>
                    @endif

                    <li class="nav-item">
                        <a class="nav-link" id="tab-financeiro" data-toggle="tab" href="#pane-financeiro" role="tab">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Financeiro</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-danos" data-toggle="tab" href="#pane-danos" role="tab">
                            <i class="fas fa-car-crash"></i>
                            <span>Danos</span>
                            @if($danosVeiculo->count() > 0)
                                <span class="rs-tab-badge rs-tab-badge--red">{{ $danosVeiculo->count() }}</span>
                            @endif
                        </a>
                    </li>
                    @if($moduloMultas)
                    <li class="nav-item">
                        <a class="nav-link" id="tab-multas" data-toggle="tab" href="#pane-multas" role="tab">
                            <i class="fas fa-circle-exclamation"></i>
                            <span>Multas</span>
                            @if($totalMultas > 0)
                                <span class="rs-tab-badge rs-tab-badge--red">{{ $totalMultas }}</span>
                            @endif
                        </a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" id="tab-sinistros" data-toggle="tab" href="#pane-sinistros" role="tab">
                            <i class="fas fa-car-crash"></i>
                            <span>Sinistros</span>
                            @if($totalSinistros > 0)
                                <span class="rs-tab-badge rs-tab-badge--red">{{ $totalSinistros }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="tab-historico" data-toggle="tab" href="#pane-historico" role="tab">
                            <i class="fas fa-history"></i>
                            <span>Histórico</span>
                            <span class="rs-tab-badge rs-tab-badge--slate d-none" id="historicoCount"></span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content rs-tab-content" id="reservaMainTabContent">
                    {{-- TAB 1 — VISÃO GERAL --}}
                    <div class="tab-pane fade show active" id="pane-visao-geral" role="tabpanel">
                        @include('reserva.partials.cliente_visao_geral')
                    </div>

                    {{-- TAB 2 — CHECK-IN --}}
                    <div class="tab-pane fade" id="pane-checkin" role="tabpanel">
                        @include('reserva.partials.cliente_checkin')
                    </div>

                    {{-- TAB 3 — CHECK-OUT --}}
                    <div class="tab-pane fade" id="pane-checkout" role="tabpanel">
                        @include('reserva.partials.cliente_checkout')
                    </div>

                    {{-- TAB 4 — FINANCEIRO --}}
                    <div class="tab-pane fade" id="pane-financeiro" role="tabpanel">
                        @include('reserva.partials.cliente_financeiro')
                    </div>

                    {{-- TAB 5 — DANOS --}}
                    <div class="tab-pane fade" id="pane-danos" role="tabpanel">
                        @include('reserva.partials.cliente_danos')
                    </div>

                    {{-- TAB 6 — MULTAS --}}
                    @if($moduloMultas)
                    <div class="tab-pane fade" id="pane-multas" role="tabpanel">
                        @include('reserva.partials.cliente_multas')
                    </div>
                    @endif

                    {{-- TAB 7 — SINISTROS --}}
                    <div class="tab-pane fade" id="pane-sinistros" role="tabpanel">
                        @include('reserva.partials.cliente_sinistros')
                    </div>

                    {{-- TAB 8 — HISTÓRICO --}}
                    <div class="tab-pane fade" id="pane-historico" role="tabpanel">
                        @include('reserva.partials.cliente_historico')
                    </div>
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
                    <div class="rs-card__icon"><i class="fas fa-list"></i></div>
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
    {{-- ── Modal Detalhes do Historico ── --}}
    <div class="modal fade" id="modalDetalhesLog" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rs-dano-modal">
                <div class="modal-header rs-dano-modal__header">
                    <div>
                        <div class="rs-dano-modal__eyebrow">Detalhes do registro</div>
                        <h5 class="modal-title" id="logModalAcao"></h5>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center mb-4 pb-3" style="border-bottom:2px solid #e2e8f0;gap:16px;flex-wrap:wrap">
                        <span class="small" style="display:flex;align-items:center;gap:6px"><i class="fas fa-user text-muted"></i> <strong id="logModalUsuario"></strong></span>
                        <span class="small" style="display:flex;align-items:center;gap:6px"><i class="fas fa-clock text-muted"></i> <span id="logModalData"></span></span>
                        <span class="ml-auto"><span class="badge" id="logModalNivel"></span></span>
                    </div>
                    <div id="logModalDetalhes"></div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Lightbox overlay --}}
    <div id="rs-lightbox" class="rs-lightbox d-none" role="dialog" aria-modal="true">
        <div class="rs-lightbox__backdrop" id="rs-lightbox-backdrop"></div>
        <div class="rs-lightbox__inner">
            <div class="rs-lightbox__label" id="rs-lightbox-label"></div>
            <img src="" alt="" id="rs-lightbox-img" class="rs-lightbox__img">
            <button type="button" class="rs-lightbox__close" id="rs-lightbox-close">&times;</button>
        </div>
    </div>

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            const RESERVA_ID    = @json($reserva->id);
            const HISTORICO_URL = "{{ route('reserva.historico', $reserva->id) }}";

            /* ── Ícones por nível ── */
            const nivelIcone = {
                info:    'fas fa-info-circle',
                success: 'fas fa-check-circle',
                warning: 'fas fa-exclamation-circle',
                danger:  'fas fa-times-circle',
            };

            /* ── Abre modal com detalhes do log ── */
            function openLogModal(log) {
                const modal   = document.getElementById('modalDetalhesLog');
                if (!modal) return;

                document.getElementById('logModalAcao').textContent  = log.acao || '';
                document.getElementById('logModalUsuario').textContent = log.usuario || '';
                document.getElementById('logModalData').textContent    = log.data || '';
                document.getElementById('logModalNivel').textContent   = (log.nivel || 'info').toUpperCase();

                const badge = document.getElementById('logModalNivel');
                badge.className = 'badge badge-' + ({
                    info: 'info', success: 'success', warning: 'warning', danger: 'danger'
                }[log.nivel] || 'secondary');

                const container = document.getElementById('logModalDetalhes');
                container.innerHTML = '';

                const det = log.detalhes || {};

                // Tabela de chaves simples
                const simples = {};
                const objetos = {};
                Object.keys(det).forEach(function (k) {
                    if (det[k] !== null && typeof det[k] === 'object') {
                        objetos[k] = det[k];
                    } else {
                        simples[k] = det[k];
                    }
                });

                if (Object.keys(simples).length > 0) {
                    const table = document.createElement('table');
                    table.className = 'rs-tl-table';
                    table.innerHTML = Object.keys(simples).map(function (k) {
                        return `<tr><td class="rs-tl-table__key">${k}</td><td>${formatLogValue(simples[k])}</td></tr>`;
                    }).join('');
                    container.appendChild(table);
                }

                Object.keys(objetos).forEach(function (k) {
                    const blockCard = document.createElement('div');
                    blockCard.className = 'rs-tl-bloco';
                    blockCard.innerHTML = `<div class="rs-tl-bloco__title">${k}</div>`;

                    const obj = objetos[k];
                    if (obj.antes || obj.depois) {
                        const subTable = document.createElement('table');
                        subTable.className = 'rs-tl-table';
                        const keys = Array.from(new Set(Object.keys(obj.antes || {}).concat(Object.keys(obj.depois || {}))));
                        subTable.innerHTML = keys.map(function (subKey) {
                            const v1 = obj.antes ? obj.antes[subKey] : undefined;
                            const v2 = obj.depois ? obj.depois[subKey] : undefined;
                            return `<tr>
                                <td class="rs-tl-table__key" style="width:25%">${subKey}</td>
                                <td style="width:37.5%;color:#dc2626">${formatDiffValue(v1)}</td>
                                <td style="width:37.5%;color:#16a34a">${formatDiffValue(v2)}</td>
                            </tr>`;
                        }).join('');
                        blockCard.appendChild(subTable);
                    } else {
                        const pre = document.createElement('pre');
                        pre.className = 'rs-tl-pre';
                        pre.textContent = JSON.stringify(obj, null, 2);
                        blockCard.appendChild(pre);
                    }
                    container.appendChild(blockCard);
                });

                $(modal).modal('show');
            }

            function formatLogValue(v) {
                if (v === null || v === undefined) return '<span class="text-muted">—</span>';
                if (typeof v === 'boolean') return v ? '<span class="text-success">Sim</span>' : '<span class="text-danger">Não</span>';
                if (typeof v === 'object') return '<pre class="mb-0 small" style="background:#f1f5f9;border-radius:6px;padding:4px 8px;max-height:80px;overflow:auto;white-space:pre-wrap;margin:0">' + JSON.stringify(v, null, 2) + '</pre>';
                return String(v);
            }

            function formatDiffValue(v) {
                if (v === null || v === undefined) return '<span class="text-muted small">—</span>';
                if (typeof v === 'boolean') return v ? 'Sim' : 'Não';
                if (typeof v === 'object') return '<span class="small">' + JSON.stringify(v) + '</span>';
                return String(v);
            }

            /* ── Render timeline ── */
            function renderHistorico(logs) {
                const container = document.getElementById('historicoTimeline');
                const countEl   = document.getElementById('historicoCount');
                const countPaneEl = document.getElementById('historicoCountPane');
                if (countEl) {
                    countEl.textContent = logs.length;
                    countEl.classList.toggle('d-none', logs.length === 0);
                }
                if (countPaneEl) countPaneEl.textContent = logs.length;

                if (!logs.length) {
                    container.innerHTML = '<p class="text-muted text-center py-3"><i class="fas fa-inbox mr-1"></i>Nenhum registro encontrado.</p>';
                    container.classList.remove('d-none');
                    return;
                }

                function hasDetalhes(det) {
                    return det && typeof det === 'object' && Object.keys(det).length > 0;
                }

                container.innerHTML = logs.map(function (log, idx) {
                    const nivel    = log.nivel || 'info';
                    const icon     = nivelIcone[nivel] || nivelIcone.info;
                    const det      = log.detalhes || {};
                    const hasDet   = hasDetalhes(det);
                    const detCount = hasDet ? Object.keys(det).length : 0;
                    const logJson  = hasDet ? JSON.stringify(log).replace(/"/g, '&quot;') : '';

                    return `
                    <div class="rs-tl-item ${hasDet ? 'rs-tl-item--clickable' : ''}" ${hasDet ? 'data-log=\'' + logJson + '\'' : ''}>
                        <div class="rs-tl-icon rs-tl-icon--${nivel}"><i class="${icon}"></i></div>
                        <div class="rs-tl-body">
                            <div class="rs-tl-acao">${log.acao || 'Ação registrada'}</div>
                            <div class="rs-tl-meta">
                                <i class="fas fa-user mr-1"></i>${log.usuario}
                                &nbsp;&middot;&nbsp;
                                <i class="fas fa-clock mr-1"></i>${log.data}
                                ${hasDet ? '&nbsp;&middot;&nbsp;<span class="rs-tl-details-badge"><i class="fas fa-list mr-1"></i>' + detCount + ' detalhe(s)</span>' : ''}
                            </div>
                        </div>
                        ${hasDet ? '<div class="rs-tl-chevron"><i class="fas fa-chevron-right"></i></div>' : ''}
                    </div>`;
                }).join('');

                container.querySelectorAll('.rs-tl-item--clickable').forEach(function (el) {
                    el.addEventListener('click', function () {
                        try {
                            var logData = JSON.parse(this.dataset.log);
                            openLogModal(logData);
                        } catch (e) {
                            // ignore parse errors
                        }
                    });
                });

                container.classList.remove('d-none');
            }

            /* ── Carregar histórico ── */
            let historicoCarregado = false;
            function carregarHistorico() {
                if (historicoCarregado) return;
                historicoCarregado = true;
                document.getElementById('historicoLoading')?.classList.remove('d-none');

                fetch(HISTORICO_URL, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json())
                    .then(data => {
                        document.getElementById('historicoLoading')?.classList.add('d-none');
                        renderHistorico(data.logs || []);
                    })
                    .catch(() => {
                        const el = document.getElementById('historicoLoading');
                        if (el) el.textContent = 'Erro ao carregar histórico.';
                    });
            }

            /* ── Auto-load histórico when tab opened ── */
            document.getElementById('tab-historico')?.addEventListener('shown.bs.tab', carregarHistorico);
            // jQuery fallback for AdminLTE Bootstrap 4
            $('#tab-historico').on('shown.bs.tab', carregarHistorico);

            /* ── Lightbox ── */
            const lightbox     = document.getElementById('rs-lightbox');
            const lightboxImg  = document.getElementById('rs-lightbox-img');
            const lightboxLbl  = document.getElementById('rs-lightbox-label');
            const lightboxClose = document.getElementById('rs-lightbox-close');
            const lightboxBack  = document.getElementById('rs-lightbox-backdrop');

            function openLightbox(src, label) {
                if (!lightbox) return;
                lightboxImg.src = src;
                lightboxLbl.textContent = label || '';
                lightbox.classList.remove('d-none');
                document.body.style.overflow = 'hidden';
            }

            function closeLightbox() {
                if (!lightbox) return;
                lightbox.classList.add('d-none');
                lightboxImg.src = '';
                document.body.style.overflow = '';
            }

            // Using event delegation because tabs/partials can load or be updated
            document.addEventListener('click', function (e) {
                const trigger = e.target.closest('.js-lightbox-trigger');
                if (trigger) {
                    e.preventDefault();
                    openLightbox(trigger.dataset.src, trigger.dataset.label);
                }
            });

            lightboxClose?.addEventListener('click', closeLightbox);
            lightboxBack?.addEventListener('click', closeLightbox);
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') closeLightbox();
            });
        })();

        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.querySelector('[data-cancelar-reserva="1"]');
            if (!btn) return;

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';
            const defaultRedirect = @json(route('reserva.minhas'));

            const toast = (icon, title, text = '') => {
                if (window.Swal && typeof window.Swal.fire === 'function') {
                    window.Swal.fire({
                        icon,
                        title,
                        text,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3800,
                        timerProgressBar: true
                    });
                    return;
                }

                alert(text || title);
            };

            btn.addEventListener('click', async function () {
                const podeCancelar = btn.dataset.podeCancelar === '1';
                if (!podeCancelar) {
                    toast('error', 'Cancelamento indisponivel', 'O cancelamento so pode ser feito ate 24 horas apos a criacao da reserva.');
                    return;
                }

                const confirmacao = await window.Swal.fire({
                    icon: 'warning',
                    title: 'Cancelar reserva?',
                    text: 'Deseja realmente cancelar esta reserva?',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, cancelar',
                    cancelButtonText: 'Voltar',
                    confirmButtonColor: '#b91c1c',
                    cancelButtonColor: '#6b7280'
                });

                if (!confirmacao.isConfirmed) {
                    return;
                }

                const originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cancelando...';

                try {
                    const response = await fetch(btn.dataset.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok || !data.success) {
                        toast('error', 'Nao foi possivel cancelar', data.message || 'Nao foi possivel cancelar a reserva.');
                        return;
                    }

                    toast('success', 'Reserva cancelada', data.message || 'Reserva cancelada com sucesso.');
                    setTimeout(() => {
                        window.location.href = data.redirect || defaultRedirect;
                    }, 900);
                } catch (error) {
                    toast('error', 'Erro de conexao', 'Tente novamente em instantes.');
                } finally {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            });
        });
    </script>
@endsection
@stop
