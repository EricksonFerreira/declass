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
    $subtotalAssinatura = $isMap ? $tarifaBaseAssinatura : $tarifaBaseAssinatura * (int) $diasAssinatura;
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
    $ehTitularCheckout = auth()->check() && in_array((int) auth()->id(), [
        (int) ($reserva->user_id ?? 0),
        (int) ($reserva->criado_por_user_id ?? 0),
        (int) ($reserva->usuario?->id ?? 0),
    ], true);
    $termoAceitoCheckout = $ehTitularCheckout
        ? \App\Models\TermoConsentimento::where('empresa_id', $empresa->id ?? null)
            ->where('tipo', 'aceito-reserva')
            ->where('ativo', 'S')
            ->orderBy('ordem')
            ->first()
        : null;
    $exibirUploadPdf = !($origemMinhasReservas ?? false);
@endphp

<div class="assinatura-page">
    <div class="assinatura-hero assinatura-hero--checkout">
        <div class="assinatura-hero__dot-bg"></div>
        <div class="assinatura-hero__stripe"></div>
        <div class="assinatura-hero__inner">
            <div class="assinatura-hero__icon assinatura-hero__icon--checkout">
                <i class="fas fa-flag-checkered"></i>
            </div>
            <div>
                <div class="assinatura-hero__eyebrow">Termo de Devolução</div>
                <h1 class="assinatura-hero__title">Assinatura Digital</h1>
                <p class="assinatura-hero__sub">Reserva {{ $codigoAssinatura }}</p>
                <div class="mt-2" style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a href="{{ $origemMinhasReservas ?? false ? route('reserva.minhas.show', ['id' => $reserva->id]) : route('reserva.show', ['reserva' => $codigoAssinatura]) }}" class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                    </a>
                    <a href="{{ route('reserva.minhas.vercontratocheckout', ['codigo' => $codigoAssinatura]) }}" target="_blank" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-file-pdf mr-1"></i> Ver PDF do contrato
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="assinatura-card h-100" style="border-radius:24px; box-shadow: 0 10px 30px rgba(12,20,40,0.06); border-top:1px solid #e2e8f0;">
                @php $moedaSimbolo = ($empresa?->moeda_pagamento ?? 'BRL') === 'USD' ? '$' : (($empresa?->moeda_pagamento ?? 'BRL') === 'EUR' ? '€' : 'R$'); @endphp
                <div class="assinatura-empresa">
                    @if(!empty($empresa?->logo_url))
                        <img src="{{ $empresa->logo_url }}" alt="{{ $empresa->nome }}" class="assinatura-logo">
                    @endif
                    <div class="assinatura-empresa-info">
                        <strong>{{ $empresa?->nome ?? '—' }}</strong>
                        <span>{{ $empresa?->cnpj ?? '' }}</span>
                        <span>{{ $empresa?->email ?? '' }} · {{ $empresa?->telefone ?? '' }}</span>
                    </div>
                </div>

                <div class="assinatura-secao">
                    <div class="assinatura-secao-titulo"><i class="fas fa-user mr-1"></i>Cliente</div>
                    <table class="assinatura-tabela">
                        <tr><td>Nome</td><td><strong>{{ $usuarioAssinatura?->nome ?? $usuarioAssinatura?->name ?? '—' }}</strong></td></tr>
                        <tr><td>CPF/CNPJ</td><td>{{ $usuarioAssinatura?->cpf ?? $usuarioAssinatura?->documento ?? '—' }}</td></tr>
                        <tr><td>E-mail</td><td>{{ $usuarioAssinatura?->email ?? '—' }}</td></tr>
                        <tr><td>Telefone</td><td>{{ $usuarioAssinatura?->telefone ?? '—' }}</td></tr>
                    </table>
                </div>

                <div class="assinatura-secao">
                    <div class="assinatura-secao-titulo"><i class="fas fa-car mr-1"></i>Veículo</div>
                    <table class="assinatura-tabela">
                        <tr><td>Marca / Modelo</td><td><strong>{{ $marcaNome }} {{ $modeloNome }}</strong></td></tr>
                        <tr><td>Placa</td><td>{{ $placa }}</td></tr>
                        <tr><td>Grupo / Plano</td><td>{{ $grupoNome }}</td></tr>
                        <tr><td>Cor</td><td>{{ $cor }}</td></tr>
                        <tr><td>Ano</td><td>{{ $ano }}</td></tr>
                        <tr><td>Quilometragem</td><td>{{ number_format((float) $km, 0, ',', '.') }} km</td></tr>
                        <tr><td>Combustível</td><td>{{ $comb }}</td></tr>
                    </table>
                </div>

                <div class="assinatura-secao">
                    <div class="assinatura-secao-titulo"><i class="fas fa-calendar mr-1"></i>Período da Locação</div>
                    <table class="assinatura-tabela">
                        <tr><td>Retirada</td><td><strong>{{ $dataInicioAssinatura ? \Carbon\Carbon::parse($dataInicioAssinatura)->format('d/m/Y H:i') : '—' }}</strong></td></tr>
                        <tr><td>Devolução prevista</td><td><strong>{{ $dataFimAssinatura ? \Carbon\Carbon::parse($dataFimAssinatura)->format('d/m/Y H:i') : '—' }}</strong></td></tr>
                        <tr><td>Duração</td><td>{{ $diasAssinatura }} dia(s)</td></tr>
                    </table>
                </div>

                <div class="assinatura-secao">
                    <div class="assinatura-secao-titulo"><i class="fas fa-money-bill mr-1"></i>Financeiro</div>
                    <table class="assinatura-tabela">
                        <tr><td>{{ $tarifaLabelAssinatura }}</td><td>{{ $moedaSimbolo }} {{ number_format($tarifaBaseAssinatura, 2, ',', '.') }}</td></tr>
                        @if(!$isMap)
                        <tr><td>Diárias</td><td>{{ $diasAssinatura }} dia(s)</td></tr>
                        <tr><td>Subtotal</td><td><strong>{{ $moedaSimbolo }} {{ number_format($subtotalAssinatura, 2, ',', '.') }}</strong></td></tr>
                        @endif
                        @if($taxaEntregaAssinatura > 0)
                        <tr><td>Taxa de entrega</td><td>{{ $moedaSimbolo }} {{ number_format($taxaEntregaAssinatura, 2, ',', '.') }}</td></tr>
                        @endif
                        @if($taxaColetaAssinatura > 0)
                        <tr><td>Taxa de coleta</td><td>{{ $moedaSimbolo }} {{ number_format($taxaColetaAssinatura, 2, ',', '.') }}</td></tr>
                        @endif
                        <tr><td style="font-weight:700;font-size:.9rem;">Valor total por ciclo</td><td style="font-weight:800;font-size:.9rem;color:#dc2626;">{{ $moedaSimbolo }} {{ number_format($valorTotalAssinatura, 2, ',', '.') }}</td></tr>
                    </table>
                </div>

                @if(!empty($co?->notas_saida) || !empty($co?->observacoes_saida))
                <div class="assinatura-secao">
                    <div class="assinatura-secao-titulo"><i class="fas fa-sticky-note mr-1"></i>Observações</div>
                    @if(!empty($co?->notas_saida))<p style="font-size:.82rem;color:#475569;margin:2px 0;">{{ $co->notas_saida }}</p>@endif
                    @if(!empty($co?->observacoes_saida))<p style="font-size:.82rem;color:#475569;margin:2px 0;">{{ $co->observacoes_saida }}</p>@endif
                </div>
                @endif

                <div class="assinatura-secao" style="border:none;margin-bottom:0;padding-bottom:0;">
                    <div class="assinatura-secao-titulo"><i class="fas fa-file-contract mr-1"></i>Contrato</div>
                    <table class="assinatura-tabela">
                        <tr><td>Nº do contrato</td><td><strong>#{{ $co?->id ?? $reserva->id ?? '—' }}</strong></td></tr>
                        @if(!empty($co?->data_devolucao))
                        <tr><td>Data de devolução</td><td>{{ \Carbon\Carbon::parse($co->data_devolucao)->format('d/m/Y') }} {{ $co->hora_devolucao ? substr($co->hora_devolucao, 0, 5) : '' }}</td></tr>
                        @endif
                        <tr><td>Reserva</td><td>{{ $codigoAssinatura }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="assinatura-card h-100 d-flex flex-column" style="border-radius:24px; box-shadow: 0 10px 30px rgba(12,20,40,0.06); border-top:1px solid #e2e8f0; justify-content: space-between;">
                <form id="form-assinatura-checkout" enctype="multipart/form-data" class="d-flex flex-column h-100" style="flex:1; justify-content: space-between;">
                    <div>
                        <div class="assinatura-tabs">
                            <button type="button" class="assinatura-tab active" data-tab="digital">
                                <i class="fas fa-signature"></i> Assinar Digitalmente
                            </button>
                            @if($exibirUploadPdf)
                            <button type="button" class="assinatura-tab" data-tab="upload">
                                <i class="fas fa-file-pdf"></i> Anexar PDF
                            </button>
                            @endif
                        </div>

                        <div id="tab-digital" class="assinatura-tab-content active">
                            <div class="assinatura-instruction">
                                <i class="fas fa-pencil-alt"></i>
                                <span>Use o mouse ou toque na área abaixo para assinar:</span>
                            </div>

                            <div class="assinatura-pad-wrapper">
                                <canvas id="signature-pad" style="height: 250px;"></canvas>
                                <div class="assinatura-pad-hint">Área de assinatura</div>
                            </div>

                            @if($termoAceitoCheckout)
                                <div class="assinatura-aceite" style="margin: 8px 0 16px;">
                                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:.92rem;color:#0f172a;">
                                        <input type="checkbox" id="aceite_termo_checkout" name="aceite_termo_checkout" value="1" style="margin-top:4px;">
                                        <span>
                                            Li e aceito o termo da reserva, confirmando que concordo com as condições registradas antes de assinar o contrato de check-out.
                                            <a href="#" data-toggle="modal" data-target="#modalTermoConsentimentoCheckout" style="text-decoration: underline; color: #4338ca; font-weight: 600;">(Ver Termo)</a>
                                        </span>
                                    </label>
                                </div>
                            @endif

                            <div class="assinatura-actions mb-4">
                                <button type="button" id="btn_limpar" class="assinatura-btn assinatura-btn--ghost">
                                    <i class="fas fa-eraser"></i>
                                    Limpar
                                </button>
                                <button type="button" id="btn_salvar" class="assinatura-btn assinatura-btn--primary assinatura-btn--checkout">
                                    <i class="fas fa-check"></i>
                                    Confirmar Assinatura
                                </button>
                            </div>
                        </div>

                        @if($exibirUploadPdf)
                        <div id="tab-upload" class="assinatura-tab-content">
                            <div class="assinatura-instruction">
                                <i class="fas fa-file-upload"></i>
                                <span>Envie um PDF assinado para anexar ao contrato de check-out:</span>
                            </div>

                            <div class="assinatura-upload-box" id="upload-drop-zone">
                                <input type="file" id="contrato_pdf" name="contrato_pdf" accept="application/pdf,.pdf" style="display:none;">
                                <div class="assinatura-upload-placeholder" id="upload-placeholder">
                                    <i class="fas fa-file-pdf"></i>
                                    <p>Arraste o arquivo PDF aqui ou <a href="#" id="upload-browse-link">clique para selecionar</a></p>
                                    <span class="muted">Apenas arquivos PDF serão aceitos.</span>
                                </div>
                                <div class="assinatura-upload-preview" id="upload-preview" style="display:none;">
                                    <i class="fas fa-file-pdf"></i>
                                    <div class="assinatura-upload-file-info">
                                        <strong id="upload-file-name"></strong>
                                        <span id="upload-file-size"></span>
                                    </div>
                                    <button type="button" id="upload-remove" class="assinatura-btn assinatura-btn--ghost" style="padding:6px 12px;font-size:.78rem;">
                                        <i class="fas fa-times"></i> Remover
                                    </button>
                                </div>
                            </div>

                            <div class="assinatura-actions mb-4 mt-4">
                                <button type="button" id="btn_anexar_pdf" class="assinatura-btn assinatura-btn--primary assinatura-btn--checkout" disabled>
                                    <i class="fas fa-paperclip"></i>
                                    Anexar PDF
                                </button>
                            </div>
                        </div>
                        @endif

                    </div>

                    <div id="assinatura_status" class="assinatura-status" style="display:none; margin-top: auto;"></div>
                </form>
            </div>
        </div>
    </div>

    @if($termoAceitoCheckout)
    <div class="modal fade" id="modalTermoConsentimentoCheckout" tabindex="-1" role="dialog" aria-labelledby="modalTermoTitleCheckout" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 15px 40px rgba(0,0,0,0.15);">
                <div class="modal-header" style="background: linear-gradient(135deg, #0c1428 0%, #1a2848 100%); color: white; padding: 18px 24px;">
                    <h5 class="modal-title" id="modalTermoTitleCheckout" style="font-weight: 700;">{{ $termoAceitoCheckout->titulo }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8; background: transparent; border: none; font-size: 1.5rem;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 24px; max-height: 60vh; overflow-y: auto; font-size: 0.95rem; line-height: 1.6; color: #334155;">
                    {!! $termoAceitoCheckout->texto !!}
                </div>
                <div class="modal-footer" style="justify-content: flex-end; padding: 14px 24px; background-color: #f8fafc; border-top: 1px solid #e2e8f0; display: flex;">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 10px; font-weight: 600; padding: 8px 20px;">Fechar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    .assinatura-page {
        max-width: 1200px;
        margin: 0 0 40px;
    }

    .assinatura-hero {
        position: relative;
        background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
        border-radius: 24px 24px 0 0;
        padding: 28px 28px 24px;
        overflow: hidden;
        display: flex;
        align-items: center;
    }
    .assinatura-hero--checkout {
        background: linear-gradient(135deg, #1f2937 0%, #0f172a 55%, #1e293b 100%);
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
        background: linear-gradient(90deg, #ef4444, #f59e0b, #f97316);
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
    .assinatura-hero__icon--checkout {
        color: #f87171;
        background: rgba(239,68,68,0.12);
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

    .assinatura-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-top: 0;
        border-radius: 0 0 24px 24px;
        padding: 28px;
        box-shadow: 0 20px 60px rgba(12,20,40,0.10);
    }

    .assinatura-empresa { display:flex; align-items:center; gap:14px; margin-bottom:20px; padding:14px 16px; background:#f8fafc; border:1px solid #e9f0fa; border-radius:14px; }
    .assinatura-logo { max-height:52px; width:auto; border-radius:8px; }
    .assinatura-empresa-info { display:flex; flex-direction:column; gap:1px; font-size:.82rem; color:#475569; }
    .assinatura-empresa-info strong { font-size:.95rem; color:#0c1428; }

    .assinatura-secao { margin-bottom:16px; border-bottom:1px solid #f1f5f9; padding-bottom:12px; }
    .assinatura-secao-titulo { font-size:.78rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:.06em; margin-bottom:8px; }
    .assinatura-tabela { width:100%; font-size:.84rem; border-collapse:collapse; }
    .assinatura-tabela td { padding:4px 6px 4px 0; color:#475569; vertical-align:top; }
    .assinatura-tabela td:first-child { width:180px; color:#64748b; }
    .assinatura-tabela td strong { color:#0c1428; }

    .assinatura-instruction {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.82rem;
        color: #475569;
        margin-bottom: 12px;
        font-weight: 500;
    }
    .assinatura-instruction i { color: #dc2626; }

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
        border-color: #dc2626;
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
        background: linear-gradient(135deg, #dc2626, #ef4444);
        color: #fff;
        box-shadow: 0 6px 16px rgba(220,38,38,0.30);
    }
    .assinatura-btn--checkout {
        background: linear-gradient(135deg, #dc2626, #f97316);
    }
    .assinatura-btn--primary:hover { opacity: 0.92; transform: translateY(-1px); }
    .assinatura-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

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

    .assinatura-tabs {
        display: flex;
        gap: 0;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 20px;
        background: #f8fafc;
    }
    .assinatura-tab {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 14px 18px;
        border: none;
        background: transparent;
        font-size: 0.85rem;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
    }
    .assinatura-tab:hover {
        background: #fff7ed;
        color: #dc2626;
    }
    .assinatura-tab.active {
        background: #fff;
        color: #dc2626;
        font-weight: 700;
        box-shadow: inset 0 -3px 0 #dc2626;
    }
    .assinatura-tab i { font-size: 1rem; }
    .assinatura-tab-content {
        display: none;
    }
    .assinatura-tab-content.active {
        display: block;
    }
    .assinatura-upload-box {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        padding: 28px 18px;
        background: #f8fafc;
        cursor: pointer;
        transition: all 0.2s ease;
        margin-bottom: 16px;
    }
    .assinatura-upload-box:hover,
    .assinatura-upload-box.dragover {
        border-color: #dc2626;
        border-style: solid;
        background: #fff7ed;
    }
    .assinatura-upload-placeholder {
        text-align: center;
    }
    .assinatura-upload-placeholder i {
        font-size: 2.5rem;
        color: #fca5a5;
        margin-bottom: 10px;
    }
    .assinatura-upload-placeholder p {
        font-size: 0.88rem;
        color: #475569;
        margin: 0 0 4px;
    }
    .assinatura-upload-placeholder p a {
        color: #dc2626;
        font-weight: 700;
        text-decoration: underline;
    }
    .assinatura-upload-placeholder .muted {
        font-size: 0.75rem;
        color: #94a3b8;
    }
    .assinatura-upload-preview {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
    }
    .assinatura-upload-preview > i {
        font-size: 2rem;
        color: #dc2626;
    }
    .assinatura-upload-file-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        text-align: left;
    }
    .assinatura-upload-file-info strong {
        font-size: 0.88rem;
        color: #0c1428;
    }
    .assinatura-upload-file-info span {
        font-size: 0.75rem;
        color: #64748b;
    }
    @media (max-width: 600px) {
        .assinatura-hero { border-radius: 18px 18px 0 0; padding: 20px; }
        .assinatura-card { padding: 18px; border-radius: 0 0 18px 18px; }
        .assinatura-hero__title { font-size: 1.2rem; }
        .assinatura-actions { flex-direction: column; }
        .assinatura-btn { justify-content: center; }
        #signature-pad { height: 180px; }
        .assinatura-tabs { flex-direction: column; }
        .assinatura-upload-preview { flex-direction: column; text-align: center; }
        .assinatura-upload-file-info { text-align: center; }
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
    const aceiteTermo = document.getElementById('aceite_termo_checkout');
    const fileInput = document.getElementById('contrato_pdf');
    const uploadDropZone = document.getElementById('upload-drop-zone');
    const uploadPlaceholder = document.getElementById('upload-placeholder');
    const uploadPreview = document.getElementById('upload-preview');
    const uploadBrowseLink = document.getElementById('upload-browse-link');
    const uploadFileName = document.getElementById('upload-file-name');
    const uploadFileSize = document.getElementById('upload-file-size');
    const uploadRemove = document.getElementById('upload-remove');
    const tabButtons = document.querySelectorAll('.assinatura-tab');
    const tabContents = document.querySelectorAll('.assinatura-tab-content');
    const btnLimpar = document.getElementById('btn_limpar');
    const btnSalvar = document.getElementById('btn_salvar');
    const btnAnexarPdf = document.getElementById('btn_anexar_pdf');
    let activeMode = 'digital';

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
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

    function setMode(mode) {
        activeMode = mode;
        tabButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.tab === mode));
        tabContents.forEach(content => content.classList.toggle('active', content.id === `tab-${mode}`));
        if (mode === 'digital') {
            signaturePad.on();
        } else {
            signaturePad.off();
        }
    }

    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => setMode(btn.dataset.tab));
    });

    function formatFileSize(bytes) {
        if (!bytes) return '-';
        const units = ['B', 'KB', 'MB', 'GB'];
        let size = bytes;
        let unit = 0;
        while (size >= 1024 && unit < units.length - 1) {
            size /= 1024;
            unit++;
        }
        return `${size.toFixed(size >= 10 || unit === 0 ? 0 : 1)} ${units[unit]}`;
    }

    function resetUploadPreview() {
        if (uploadPlaceholder) uploadPlaceholder.style.display = 'block';
        if (uploadPreview) uploadPreview.style.display = 'none';
        if (btnAnexarPdf) btnAnexarPdf.disabled = true;
    }

    function syncUploadPreview() {
        const file = fileInput?.files?.[0];
        if (!file) {
            resetUploadPreview();
            return;
        }

        if (uploadPlaceholder) uploadPlaceholder.style.display = 'none';
        if (uploadPreview) uploadPreview.style.display = 'flex';
        if (uploadFileName) uploadFileName.textContent = file.name;
        if (uploadFileSize) uploadFileSize.textContent = formatFileSize(file.size);
        if (btnAnexarPdf) btnAnexarPdf.disabled = false;
    }

    uploadBrowseLink?.addEventListener('click', (event) => {
        event.preventDefault();
        fileInput?.click();
    });

    uploadDropZone?.addEventListener('click', (event) => {
        if (event.target.closest('#upload-remove') || event.target.closest('#upload-browse-link')) {
            return;
        }
        fileInput?.click();
    });

    fileInput?.addEventListener('change', syncUploadPreview);

    uploadRemove?.addEventListener('click', () => {
        if (fileInput) fileInput.value = '';
        resetUploadPreview();
    });

    ['dragenter', 'dragover'].forEach((evt) => {
        uploadDropZone?.addEventListener(evt, (event) => {
            event.preventDefault();
            uploadDropZone.classList.add('dragover');
        });
    });
    ['dragleave', 'drop'].forEach((evt) => {
        uploadDropZone?.addEventListener(evt, (event) => {
            event.preventDefault();
            uploadDropZone.classList.remove('dragover');
        });
    });
    uploadDropZone?.addEventListener('drop', (event) => {
        const file = event.dataTransfer?.files?.[0];
        if (!file) return;
        if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
            showStatus('Envie apenas um arquivo PDF válido.', 'error');
            return;
        }
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        syncUploadPreview();
    });

    btnLimpar.addEventListener('click', () => {
        signaturePad.clear();
        if (hint) hint.style.opacity = '1';
    });

    function enviarAssinatura() {
        if (signaturePad.isEmpty()) {
            showStatus('Por favor, desenhe sua assinatura antes de confirmar.', 'error');
            return;
        }
        if (aceiteTermo && !aceiteTermo.checked) {
            showStatus('Você precisa aceitar o termo de check-out antes de confirmar a assinatura.', 'error');
            return;
        }

        const originalHtml = btnSalvar.innerHTML;
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

        const formData = new FormData();
        formData.append('assinatura', signaturePad.toDataURL());
        formData.append('veiculo_id', "{{ $co?->veiculo_id ?? $reserva->veiculo_id ?? 0 }}");
        formData.append('contrato_id', "{{ $co?->id ?? 0 }}");
        formData.append('modo', 'digital');
        formData.append('aceite_termo_checkout', aceiteTermo && aceiteTermo.checked ? '1' : '0');
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: "{{ $origemMinhasReservas ?? false ? route('reserva.minhas.salvar-assinatura-checkout', ['codigo' => $codigoAssinatura]) : route('reserva.salvarAssinaturaContratoCheckout', ['codigo' => $codigoAssinatura]) }}",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
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
    }

    function anexarPdf() {
        if (! fileInput.files || ! fileInput.files.length) {
            showStatus('Selecione um arquivo PDF antes de anexar.', 'error');
            return;
        }

        const file = fileInput.files[0];
        if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
            showStatus('Envie apenas um arquivo PDF válido.', 'error');
            return;
        }

        const originalHtml = btnAnexarPdf.innerHTML;
        btnAnexarPdf.disabled = true;
        btnAnexarPdf.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

        const formData = new FormData();
        formData.append('contrato_pdf', file);
        formData.append('veiculo_id', "{{ $co?->veiculo_id ?? $reserva->veiculo_id ?? 0 }}");
        formData.append('contrato_id', "{{ $co?->id ?? 0 }}");
        formData.append('modo', 'upload');
        formData.append('assinatura', '');
        formData.append('aceite_termo_checkout', aceiteTermo && aceiteTermo.checked ? '1' : '0');
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: "{{ $origemMinhasReservas ?? false ? route('reserva.minhas.salvar-assinatura-checkout', ['codigo' => $codigoAssinatura]) : route('reserva.salvarAssinaturaContratoCheckout', ['codigo' => $codigoAssinatura]) }}",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                showStatus('<i class="fas fa-check-circle"></i> PDF anexado com sucesso! Redirecionando...', 'success');
                setTimeout(() => {
                    window.location.href = @json($redirectAfterSignUrl ?? route('reserva.show', ['reserva' => $codigoAssinatura]));
                }, 1600);
            },
            error: function (xhr) {
                btnAnexarPdf.disabled = false;
                btnAnexarPdf.innerHTML = originalHtml;
                const msg = xhr.responseJSON?.message || 'Erro ao salvar assinatura. Tente novamente.';
                showStatus('<i class="fas fa-exclamation-triangle"></i> ' + msg, 'error');
            },
        });
    }

    btnSalvar.addEventListener('click', enviarAssinatura);
    if (btnAnexarPdf) {
        btnAnexarPdf.addEventListener('click', anexarPdf);
    }

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
