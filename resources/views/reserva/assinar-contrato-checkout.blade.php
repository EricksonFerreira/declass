@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')

@php
    $ctx = $contextoAssinatura ?? [];
    $empresa = $ctx['empresa'] ?? $reserva->empresa ?? null;
    $usuarioAssinatura = $ctx['usuario'] ?? $reserva->usuario ?? $reserva->user ?? null;
    $codigoAssinatura = $ctx['codigo'] ?? ($reserva->codigoReserva?->codigo ?? $reserva->id);
    $dataInicioAssinatura = $ctx['data_inicio'] ?? $reserva->data_inicio ?? null;
    $dataFimAssinatura = $ctx['data_fim'] ?? $reserva->data_fim ?? null;
    $diasAssinatura = $ctx['quantidade_dias']
        ?? ($dataInicioAssinatura && $dataFimAssinatura ? \Carbon\Carbon::parse($dataInicioAssinatura)->diffInDays(\Carbon\Carbon::parse($dataFimAssinatura)) : 1);
    if ($diasAssinatura <= 0) $diasAssinatura = 1;
    $tarifaLabelAssinatura = $ctx['tarifa_label'] ?? 'Tarifa diaria';
    $tarifaBaseAssinatura = (float) ($ctx['tarifa_valor'] ?? $reserva->tarifa_base ?? 0);
    $taxaEntregaAssinatura = (float) ($ctx['taxa_entrega'] ?? $reserva->taxa_entrega ?? 0);
    $taxaColetaAssinatura = (float) ($ctx['taxa_coleta'] ?? $reserva->taxa_coleta ?? 0);
    $isMap = ($ctx['tipo'] ?? 'RES') === 'MAP';
    $subtotalAssinatura = $isMap
        ? $tarifaBaseAssinatura
        : $tarifaBaseAssinatura * (int) $diasAssinatura;
    $valorTotalAssinatura = (float) ($ctx['valor_total'] ?? ($subtotalAssinatura + $taxaEntregaAssinatura + $taxaColetaAssinatura));

    $co = $reserva->contratoCheckout ?? null;
    $veic = $co?->veiculo ?? $reserva->veiculo ?? ($ctx['veiculo'] ?? null);
    $marcaNome = $co?->marca?->nome ?? $veic?->marca?->nome ?? '-';
    $modeloNome = $co?->modelo?->nome ?? $veic?->modelo?->nome ?? '-';
    $placa = $veic?->placa_licenca ?? '-';
    $grupoNome = $veic?->grupoFrota?->nome ?? $ctx['plano_nome'] ?? '-';
    $cor = $veic?->cor ?? '-';
    $ano = $veic?->ano_fabricacao ?? ($veic?->modelo?->ano_modelo ?? '-');
    $km = $co?->quilometragem_devolucao ?? $reserva->quilometragem_final ?? $veic?->quilometragem ?? 0;
    $comb = $co?->nivel_combustivel_devolucao ?? $reserva->nivel_combustivel_final ?? '-';
@endphp

<div class="assinatura-page">

    {{-- Hero --}}
    <div class="assinatura-hero" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
        <div class="assinatura-hero__dot-bg"></div>
        <div class="assinatura-hero__stripe" style="background: #ef4444;"></div>
        <div class="assinatura-hero__inner">
            <div class="assinatura-hero__icon" style="color: #ef4444; background: rgba(239, 68, 68, 0.1);">
                <i class="fas fa-flag-checkered"></i>
            </div>
            <div>
                <div class="assinatura-hero__eyebrow">Termo de Devolução</div>
                <h1 class="assinatura-hero__title">Assinatura de Check-out</h1>
                <p class="assinatura-hero__sub">Reserva {{ $codigoAssinatura }}</p>
            </div>
        </div>
    </div>

    {{-- Body Card --}}
    <div class="assinatura-card">
        <div class="row">
            {{-- Resumo da Reserva & Veículo --}}
            <div class="col-lg-5">
                <div class="assinatura-section-title">
                    <i class="fas fa-info-circle"></i> Resumo da Locação & Veículo
                </div>

                {{-- Veículo --}}
                <div class="veiculo-badge mb-3">
                    <i class="fas fa-car mr-2 text-primary"></i>
                    <div>
                        <strong>{{ $marcaNome }} {{ $modeloNome }}</strong>
                        <span>Placa: {{ $placa }} · Cor: {{ $cor }}</span>
                    </div>
                </div>

                <div class="rs-info-summary">
                    <div class="rs-info-item">
                        <span class="rs-info-label"><i class="fas fa-tachometer-alt"></i> KM Final</span>
                        <span class="rs-info-val">{{ number_format($km, 0, ',', '.') }} km</span>
                    </div>
                    <div class="rs-info-item">
                        <span class="rs-info-label"><i class="fas fa-gas-pump"></i> Combustível Final</span>
                        <span class="rs-info-val">{{ $comb }}</span>
                    </div>
                    <div class="rs-info-item">
                        <span class="rs-info-label"><i class="fas fa-user"></i> Cliente</span>
                        <span class="rs-info-val font-weight-bold">{{ $usuarioAssinatura?->nome ?? $usuarioAssinatura?->name ?? '—' }}</span>
                    </div>
                    <div class="rs-info-item">
                        <span class="rs-info-label"><i class="fas fa-calendar-alt"></i> Devolvido Em</span>
                        <span class="rs-info-val">{{ $co?->data_devolucao ? \Carbon\Carbon::parse($co->data_devolucao)->format('d/m/Y') : '' }} {{ $co?->hora_devolucao ? substr($co->hora_devolucao, 0, 5) : '' }}</span>
                    </div>
                </div>
            </div>

            {{-- Quadro de Assinatura --}}
            <div class="col-lg-7 border-left border-light">
                <div class="assinatura-section-title pl-lg-3 mt-4 mt-lg-0">
                    <i class="fas fa-pen-nib"></i> Assinatura Digital do Cliente
                </div>
                <div class="pl-lg-3">
                    <p class="text-muted small">
                        Ao assinar no quadro abaixo, você confirma a vistoria de check-out, a quilometragem final e o nível de combustível indicados acima.
                    </p>

                    <div class="assinatura-pad-container mb-3">
                        <canvas id="signature-pad" class="assinatura-pad-canvas"></canvas>
                        <div class="assinatura-pad-hint">
                            <i class="fas fa-pencil-alt mb-2"></i>
                            <span>Desenhe sua assinatura digital aqui</span>
                        </div>
                    </div>

                    <div class="assinatura-actions">
                        <button type="button" class="assinatura-btn assinatura-btn--secondary" id="btn_limpar">
                            <i class="fas fa-eraser mr-1"></i> Limpar
                        </button>
                        <button type="button" class="assinatura-btn assinatura-btn--primary" id="btn_salvar" style="background: linear-gradient(135deg, #dc2626, #ef4444); box-shadow: 0 6px 16px rgba(220,38,38,0.30);">
                            <i class="fas fa-check mr-1"></i> Confirmar & Salvar
                        </button>
                    </div>

                    <div id="assinatura_status" class="assinatura-status" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos Premium Baseados em assinar-contrato-checkin */
    .assinatura-page {
        margin: 20px auto;
        max-width: 1040px;
        font-family: 'Outfit', 'Inter', sans-serif;
    }
    .assinatura-hero {
        position: relative;
        border-radius: 20px 20px 0 0;
        padding: 32px;
        color: #fff;
        overflow: hidden;
    }
    .assinatura-hero__dot-bg {
        position: absolute;
        inset: 0;
        opacity: 0.07;
        background-image: radial-gradient(#fff 1px, transparent 1px);
        background-size: 16px 16px;
    }
    .assinatura-hero__stripe {
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 6px;
    }
    .assinatura-hero__inner {
        position: relative;
        display: flex;
        align-items: center;
        gap: 20px;
        z-index: 10;
    }
    .assinatura-hero__icon {
        width: 58px;
        height: 58px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    .assinatura-hero__eyebrow {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        opacity: 0.7;
    }
    .assinatura-hero__title {
        font-size: 1.6rem;
        font-weight: 800;
        margin: 2px 0;
        letter-spacing: -0.5px;
    }
    .assinatura-hero__sub {
        font-size: 0.88rem;
        opacity: 0.8;
        margin: 0;
    }

    /* Card */
    .assinatura-card {
        background: #fff;
        border-radius: 0 0 20px 20px;
        padding: 32px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05), 0 8px 10px -6px rgba(0,0,0,0.05);
    }
    .assinatura-section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .assinatura-section-title i { color: #64748b; }

    /* Veiculo */
    .veiculo-badge {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 18px;
    }
    .veiculo-badge i { font-size: 1.4rem; }
    .veiculo-badge strong { display: block; color: #0f172a; font-size: 0.95rem; }
    .veiculo-badge span { font-size: 0.78rem; color: #64748b; }

    /* Info Summary */
    .rs-info-summary {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .rs-info-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px dashed #f1f5f9;
    }
    .rs-info-label { font-size: 0.85rem; color: #64748b; }
    .rs-info-val { font-size: 0.88rem; color: #1e293b; font-weight: 600; }

    /* Pad container */
    .assinatura-pad-container {
        position: relative;
        border: 2px dashed #cbd5e1;
        background: #f8fafc;
        border-radius: 16px;
        overflow: hidden;
        height: 250px;
    }
    .assinatura-pad-canvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        cursor: crosshair;
        touch-action: none;
        z-index: 5;
    }
    .assinatura-pad-hint {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        color: #94a3b8;
        z-index: 2;
        pointer-events: none;
        transition: opacity 0.25s ease;
    }
    .assinatura-pad-hint i { font-size: 1.8rem; }
    .assinatura-pad-hint span { font-size: 0.85rem; font-weight: 600; }

    /* Actions */
    .assinatura-actions {
        display: flex;
        gap: 12px;
    }
    .assinatura-btn {
        flex: 1;
        border: none;
        border-radius: 12px;
        padding: 12px 20px;
        font-size: 0.88rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .assinatura-btn--secondary {
        background: #f1f5f9;
        color: #475569;
    }
    .assinatura-btn--secondary:hover { background: #e2e8f0; }
    .assinatura-btn--primary {
        color: #fff;
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

    signaturePad.addEventListener('beginStroke', () => {
        if (hint) hint.style.opacity = '0';
    });

    document.getElementById('btn_limpar').addEventListener('click', () => {
        signaturePad.clear();
        if (hint) hint.style.opacity = '1';
    });

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
        const veiculo_id  = "{{ $co?->veiculo_id ?? $reserva->veiculo_id ?? 0 }}";
        const contrato_id = "{{ $co?->id ?? 0 }}";

        $.ajax({
            url: "{{ route('reserva.salvarAssinaturaContratoCheckout', ['codigo' => $codigoAssinatura]) }}",
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
                    window.location.href = @json($redirectAfterSignUrl ?? route('reserva.show', ['reserva' => $codigoAssinatura]));
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
