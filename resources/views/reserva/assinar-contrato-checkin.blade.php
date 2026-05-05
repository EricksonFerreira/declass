@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')

<div class="assinatura-page">

    {{-- Hero --}}
    <div class="assinatura-hero">
        <div class="assinatura-hero__dot-bg"></div>
        <div class="assinatura-hero__stripe"></div>
        <div class="assinatura-hero__inner">
            <div class="assinatura-hero__icon">
                <i class="fas fa-signature"></i>
            </div>
            <div>
                <div class="assinatura-hero__eyebrow">Contrato de Check-in</div>
                <h1 class="assinatura-hero__title">Assinatura Digital</h1>
                <p class="assinatura-hero__sub">Reserva #{{ $reserva->id }}</p>
            </div>
        </div>
    </div>

    {{-- Card principal --}}
    <div class="assinatura-card">

        {{-- Logo + dados da empresa --}}
        @php $empresa = $reserva->empresa; $moedaSimbolo = $empresa->moeda_pagamento === 'USD' ? '$' : ($empresa->moeda_pagamento === 'EUR' ? '€' : 'R$'); @endphp
        <div class="assinatura-empresa">
            @if(!empty($empresa->logo_url))
                <img src="{{ $empresa->logo_url }}" alt="{{ $empresa->nome }}" class="assinatura-logo">
            @endif
            <div class="assinatura-empresa-info">
                <strong>{{ $empresa->nome ?? '—' }}</strong>
                <span>{{ $empresa->cnpj ?? '' }}</span>
                <span>{{ $empresa->email ?? '' }} · {{ $empresa->telefone ?? '' }}</span>
            </div>
        </div>

        {{-- Cliente --}}
        <div class="assinatura-secao">
            <div class="assinatura-secao-titulo"><i class="fas fa-user mr-1"></i>Cliente</div>
            <table class="assinatura-tabela">
                <tr><td>Nome</td><td><strong>{{ $reserva->usuario->nome ?? '—' }}</strong></td></tr>
                <tr><td>CPF/CNPJ</td><td>{{ $reserva->usuario->documento ?? '—' }}</td></tr>
                <tr><td>E-mail</td><td>{{ $reserva->usuario->email ?? '—' }}</td></tr>
                <tr><td>Telefone</td><td>{{ $reserva->usuario->telefone ?? '—' }}</td></tr>
            </table>
        </div>

        {{-- Veículo --}}
        <div class="assinatura-secao">
            <div class="assinatura-secao-titulo"><i class="fas fa-car mr-1"></i>Veículo</div>
            @php $ci = $reserva->contratoCheckin; $veic = $ci->veiculo; @endphp
            <table class="assinatura-tabela">
                <tr><td>Marca / Modelo</td><td><strong>{{ $ci->marca->nome ?? '' }} {{ $ci->modelo->nome ?? '—' }}</strong></td></tr>
                <tr><td>Placa</td><td>{{ $veic->placa_licenca ?? '—' }}</td></tr>
                <tr><td>Grupo</td><td>{{ $veic->grupoFrota->nome ?? '—' }}</td></tr>
                <tr><td>Cor</td><td>{{ $veic->cor ?? '—' }}</td></tr>
                <tr><td>Ano</td><td>{{ $veic->ano_fabricacao ?? ($veic->modelo->ano_modelo ?? '—') }}</td></tr>
                <tr><td>Quilometragem</td><td>{{ number_format((float)($ci->quilometragem_atual ?? 0), 0, ',', '.') }} km</td></tr>
                <tr><td>Combustível</td><td>{{ $ci->nivel_combustivel ?? '—' }}</td></tr>
            </table>
        </div>

        {{-- Período --}}
        <div class="assinatura-secao">
            <div class="assinatura-secao-titulo"><i class="fas fa-calendar mr-1"></i>Período da Locação</div>
            <table class="assinatura-tabela">
                <tr><td>Retirada</td><td><strong>{{ \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') }}</strong></td></tr>
                <tr><td>Local de retirada</td><td>{{ $reserva->estacaoRetirada->nome ?? '—' }}</td></tr>
                <tr><td>Devolução prevista</td><td><strong>{{ \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}</strong></td></tr>
                <tr><td>Local de devolução</td><td>{{ $reserva->estacaoDevolucao->nome ?? '—' }}</td></tr>
                <tr><td>Duração</td><td>{{ $diasReserva ?? $reserva->quantidade_dias ?? \Carbon\Carbon::parse($reserva->data_inicio)->diffInDays(\Carbon\Carbon::parse($reserva->data_fim)) + 1 }} dia(s)</td></tr>
            </table>
        </div>

        {{-- Financeiro --}}
        @php
            $tarifaBase = $reserva->tarifa_base ?? 0;
            $dias = $reserva->quantidade_dias ?? \Carbon\Carbon::parse($reserva->data_inicio)->diffInDays(\Carbon\Carbon::parse($reserva->data_fim)) + 1;
            $subtotal = $tarifaBase * $dias;
        @endphp
        <div class="assinatura-secao">
            <div class="assinatura-secao-titulo"><i class="fas fa-money-bill mr-1"></i>Financeiro</div>
            <table class="assinatura-tabela">
                <tr><td>Tarifa diária</td><td>{{ $moedaSimbolo }} {{ number_format($tarifaBase, 2, ',', '.') }}</td></tr>
                <tr><td>Diárias</td><td>{{ $dias }} dia(s)</td></tr>
                <tr><td>Subtotal</td><td><strong>{{ $moedaSimbolo }} {{ number_format($subtotal, 2, ',', '.') }}</strong></td></tr>
                @if($reserva->taxa_entrega ?? false)
                <tr><td>Taxa de entrega</td><td>{{ $moedaSimbolo }} {{ number_format($reserva->taxa_entrega, 2, ',', '.') }}</td></tr>
                @endif
                @if($reserva->taxa_coleta ?? false)
                <tr><td>Taxa de coleta</td><td>{{ $moedaSimbolo }} {{ number_format($reserva->taxa_coleta, 2, ',', '.') }}</td></tr>
                @endif
                <tr><td style="font-weight:700;font-size:.9rem;">Valor total</td><td style="font-weight:800;font-size:.9rem;color:#059669;">{{ $moedaSimbolo }} {{ number_format($subtotal + ($reserva->taxa_entrega ?? 0) + ($reserva->taxa_coleta ?? 0), 2, ',', '.') }}</td></tr>
            </table>
        </div>

        {{-- Observações --}}
        @if(!empty($ci->notas_saida) || !empty($ci->observacoes_saida))
        <div class="assinatura-secao">
            <div class="assinatura-secao-titulo"><i class="fas fa-sticky-note mr-1"></i>Observações</div>
            @if(!empty($ci->notas_saida))<p style="font-size:.82rem;color:#475569;margin:2px 0;">{{ $ci->notas_saida }}</p>@endif
            @if(!empty($ci->observacoes_saida))<p style="font-size:.82rem;color:#475569;margin:2px 0;">{{ $ci->observacoes_saida }}</p>@endif
        </div>
        @endif

        {{-- Contrato --}}
        <div class="assinatura-secao" style="border:none;margin-bottom:0;">
            <div class="assinatura-secao-titulo"><i class="fas fa-file-contract mr-1"></i>Contrato</div>
            <table class="assinatura-tabela">
                <tr><td>Nº do contrato</td><td><strong>#{{ $ci->id ?? '—' }}</strong></td></tr>
                @if(!empty($ci->data_saida))
                <tr><td>Data de saída</td><td>{{ \Carbon\Carbon::parse($ci->data_saida)->format('d/m/Y') }} {{ $ci->hora_saida ? substr($ci->hora_saida, 0, 5) : '' }}</td></tr>
                @endif
                <tr><td>Reserva</td><td>#{{ $reserva->id }}</td></tr>
            </table>
        </div>

        {{-- Linha divisória --}}
        <hr style="margin:20px 0;border:none;border-top:2px solid #e2e8f0;">

        {{-- Instrução --}}
        <div class="assinatura-instruction">
            <i class="fas fa-pencil-alt"></i>
            <span>Use o mouse ou toque na área abaixo para assinar o contrato</span>
        </div>

        {{-- Pad --}}
        <div class="assinatura-pad-wrapper">
            <canvas id="signature-pad"></canvas>
            <div class="assinatura-pad-hint">Área de assinatura</div>
        </div>

        {{-- Ações --}}
        <div class="assinatura-actions">
            <button type="button" id="btn_limpar" class="assinatura-btn assinatura-btn--ghost">
                <i class="fas fa-eraser"></i>
                Limpar
            </button>
            <button type="button" id="btn_salvar" class="assinatura-btn assinatura-btn--primary">
                <i class="fas fa-check"></i>
                Confirmar Assinatura
            </button>
        </div>

        {{-- Status --}}
        <div id="assinatura_status" class="assinatura-status" style="display:none"></div>

    </div>

</div>

<style>
    .assinatura-page {
        max-width: 820px;
        margin: 0 0 40px;
    }

    /* Hero */
    .assinatura-hero {
        position: relative;
        background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
        border-radius: 24px 24px 0 0;
        padding: 28px 28px 24px;
        overflow: hidden;
        display: flex;
        align-items: center;
    }
    .assinatura-hero__dot-bg {
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle, rgba(255,255,255,0.055) 1px, transparent 1px);
        background-size: 20px 20px;
        pointer-events: none;
    }
    .assinatura-hero__stripe {
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, #4338ca, #0ea5e9, #10b981);
    }
    .assinatura-hero__inner {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        gap: 18px;
    }
    .assinatura-hero__icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #60a5fa;
        flex-shrink: 0;
        backdrop-filter: blur(8px);
    }
    .assinatura-hero__eyebrow {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: rgba(148,163,184,0.8);
        display: block;
        margin-bottom: 4px;
    }
    .assinatura-hero__title {
        font-size: 1.6rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 4px;
        letter-spacing: -0.02em;
    }
    .assinatura-hero__sub {
        margin: 0;
        font-size: 0.85rem;
        color: #93c5fd;
        font-weight: 600;
    }

    /* Empresa header */
    .assinatura-empresa { display:flex; align-items:center; gap:14px; margin-bottom:20px; padding:14px 16px; background:#f8fafc; border:1px solid #e9f0fa; border-radius:14px; }
    .assinatura-logo { max-height:52px; width:auto; border-radius:8px; }
    .assinatura-empresa-info { display:flex; flex-direction:column; gap:1px; font-size:.82rem; color:#475569; }
    .assinatura-empresa-info strong { font-size:.95rem; color:#0c1428; }

    /* Sections */
    .assinatura-secao { margin-bottom:16px; border-bottom:1px solid #f1f5f9; padding-bottom:12px; }
    .assinatura-secao-titulo { font-size:.78rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px; }
    .assinatura-tabela { width:100%; font-size:.84rem; border-collapse:collapse; }
    .assinatura-tabela td { padding:4px 6px 4px 0; color:#475569; vertical-align:top; }
    .assinatura-tabela td:first-child { width:180px; color:#64748b; }
    .assinatura-tabela td strong { color:#0c1428; }

    /* Card */
    .assinatura-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-top: 0;
        border-radius: 0 0 24px 24px;
        padding: 28px;
        box-shadow: 0 20px 60px rgba(12,20,40,0.10);
    }

    /* Info strip */
    .assinatura-info {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
        background: #f8fafc;
        border: 1px solid #e9f0fa;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 20px;
    }
    .assinatura-info__item {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .assinatura-info__item > i {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        background: #eef2ff;
        color: #4338ca;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        flex-shrink: 0;
    }
    .assinatura-info__label {
        display: block;
        font-size: 0.65rem;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }
    .assinatura-info__value {
        display: block;
        font-size: 0.85rem;
        font-weight: 700;
        color: #0c1428;
    }

    /* Instruction */
    .assinatura-instruction {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.82rem;
        color: #475569;
        margin-bottom: 12px;
        font-weight: 500;
    }
    .assinatura-instruction i { color: #4338ca; }

    /* Pad wrapper */
    .assinatura-pad-wrapper {
        position: relative;
        border: 2px dashed #c7d2fe;
        border-radius: 16px;
        background: #fafbff;
        overflow: hidden;
        transition: border-color 0.2s;
        margin-bottom: 16px;
    }
    .assinatura-pad-wrapper:hover,
    .assinatura-pad-wrapper:focus-within {
        border-color: #4338ca;
        border-style: solid;
    }
    #signature-pad {
        display: block;
        width: 100%;
        height: 220px;
        cursor: crosshair;
        touch-action: none;
    }
    .assinatura-pad-hint {
        position: absolute;
        bottom: 8px;
        left: 50%;
        transform: translateX(-50%);
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: #c7d2fe;
        pointer-events: none;
        user-select: none;
        transition: opacity 0.2s;
    }

    /* Actions */
    .assinatura-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }
    .assinatura-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
        border-radius: 12px;
        padding: 12px 22px;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.18s;
        letter-spacing: -0.01em;
    }
    .assinatura-btn--ghost {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
    }
    .assinatura-btn--ghost:hover { background: #e2e8f0; color: #0c1428; }
    .assinatura-btn--primary {
        background: linear-gradient(135deg, #4338ca, #6366f1);
        color: #fff;
        box-shadow: 0 6px 16px rgba(67,56,202,0.30);
    }
    .assinatura-btn--primary:hover { opacity: 0.92; transform: translateY(-1px); }
    .assinatura-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    /* Status */
    .assinatura-status {
        margin-top: 14px;
        border-radius: 12px;
        padding: 12px 16px;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .assinatura-status.success { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
    .assinatura-status.error   { background: #fff1f2; color: #be123c; border: 1px solid #fecdd3; }

    @media (max-width: 600px) {
        .assinatura-hero { border-radius: 18px 18px 0 0; padding: 20px; }
        .assinatura-card { padding: 18px; border-radius: 0 0 18px 18px; }
        .assinatura-hero__title { font-size: 1.2rem; }
        .assinatura-actions { flex-direction: column; }
        .assinatura-btn { justify-content: center; }
        #signature-pad { height: 180px; }
    }
</style>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script src="{{ asset('js/sweetAlert.min.js') }}"></script>
<script>
(function () {
    const canvas = document.getElementById('signature-pad');
    const hint   = document.querySelector('.assinatura-pad-hint');

    /* Ajuste de resolução para telas retina */
    function resizeCanvas() {
        const ratio  = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width  = canvas.offsetWidth  * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    const signaturePad = new SignaturePad(canvas, {
        minWidth: 1.5,
        maxWidth: 3,
        penColor: '#0c1428',
    });

    /* Esconde dica ao começar a assinar */
    signaturePad.addEventListener('beginStroke', () => {
        if (hint) hint.style.opacity = '0';
    });

    /* Limpar */
    document.getElementById('btn_limpar').addEventListener('click', () => {
        signaturePad.clear();
        if (hint) hint.style.opacity = '1';
    });

    /* Salvar */
    document.getElementById('btn_salvar').addEventListener('click', () => {
        if (signaturePad.isEmpty()) {
            showStatus('Por favor, desenhe sua assinatura antes de confirmar.', 'error');
            return;
        }

        const btnSalvar = document.getElementById('btn_salvar');
        const originalHtml = btnSalvar.innerHTML;
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

        const dataURL     = signaturePad.toDataURL();
        const reserva_id  = "{{ $reserva->id }}";
        const veiculo_id  = "{{ $reserva->contratoCheckin->veiculo_id }}";
        const contrato_id = "{{ $reserva->contratoCheckin->id }}";

        $.ajax({
            url: "{{ route('reserva.salvarAssinarContratoCheckIn', ['id' => $reserva->id]) }}",
            method: 'POST',
            data: {
                assinatura: dataURL,
                veiculo_id,
                contrato_id,
                _token: '{{ csrf_token() }}',
            },
            success: function (response) {
                showStatus('<i class="fas fa-check-circle"></i> Assinatura salva com sucesso! Redirecionando...', 'success');
                setTimeout(() => {
                    window.location.href = "{{ route('reserva.show', ['reserva' => $reserva->id]) }}";
                }, 1600);
            },
            error: function (xhr) {
                btnSalvar.disabled = false;
                btnSalvar.innerHTML = originalHtml;
                const msg = xhr.responseJSON?.message || 'Erro ao salvar assinatura. Tente novamente.';
                showStatus('<i class="fas fa-exclamation-triangle"></i> ' + msg, 'error');
            },
        });
    });

    function showStatus(html, type) {
        const el = document.getElementById('assinatura_status');
        el.innerHTML = html;
        el.className = 'assinatura-status ' + type;
        el.style.display = 'flex';
        el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
})();
</script>
@endsection
