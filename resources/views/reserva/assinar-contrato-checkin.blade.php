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

    $ci = $contratoCheckin ?? $reserva->contratoCheckin ?? null;
    $veic = $ci?->veiculo ?? $reserva->veiculo ?? ($ctx['veiculo'] ?? null);
    $marcaNome = $ci?->marca?->nome ?? $veic?->marca?->nome ?? '-';
    $modeloNome = $ci?->modelo?->nome ?? $veic?->modelo?->nome ?? '-';
    $placa = $veic?->placa_licenca ?? '-';
    $grupoNome = $veic?->grupoFrota?->nome ?? $ctx['plano_nome'] ?? '-';
    $cor = $veic?->cor ?? '-';
    $ano = $veic?->ano_fabricacao ?? ($veic?->modelo?->ano_modelo ?? '-');
    $km = $ci?->quilometragem_atual ?? $reserva->quilometragem_inicial ?? $veic?->quilometragem ?? 0;
    $comb = $ci?->nivel_combustivel ?? $reserva->nivel_combustivel_inicial ?? '-';
    $exigirAceiteTermo = auth()->check() && (int) auth()->id() === (int) ($reserva->user_id ?? $reserva->usuario_id ?? 0);
@endphp

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
                <p class="assinatura-hero__sub">Reserva {{ $codigoAssinatura }}</p>
                <div class="mt-2" style="display:flex;gap:10px;flex-wrap:wrap;">
                    <a href="{{ $origemMinhasReservas ?? false ? route('reserva.minhas.show', ['id' => $reserva->id]) : route('reserva.show', ['reserva' => $codigoAssinatura]) }}" class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-left mr-1"></i> Voltar
                    </a>
                    <a href="{{ $origemMinhasReservas ?? false ? route('reserva.minhas.vercontratocheckin', ['codigo' => $codigoAssinatura]) : route('reserva.verContratoCheckin', ['codigo' => $codigoAssinatura]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-file-pdf mr-1"></i> Ver PDF do contrato
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Card principal --}}
    <div class="row">
        {{-- Coluna da Esquerda: Informações --}}
        <div class="col-md-6 mb-4">
            <div class="assinatura-card h-100" style="border-radius:24px; box-shadow: 0 10px 30px rgba(12,20,40,0.06); border-top:1px solid #e2e8f0;">
                {{-- Logo + dados da empresa --}}
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

                {{-- Cliente --}}
                <div class="assinatura-secao">
                    <div class="assinatura-secao-titulo"><i class="fas fa-user mr-1"></i>Cliente</div>
                    <table class="assinatura-tabela">
                        <tr><td>Nome</td><td><strong>{{ $usuarioAssinatura?->nome ?? $usuarioAssinatura?->name ?? '—' }}</strong></td></tr>
                        <tr><td>CPF/CNPJ</td><td>{{ $usuarioAssinatura?->cpf ?? $usuarioAssinatura?->documento ?? '—' }}</td></tr>
                        <tr><td>E-mail</td><td>{{ $usuarioAssinatura?->email ?? '—' }}</td></tr>
                        <tr><td>Telefone</td><td>{{ $usuarioAssinatura?->telefone ?? '—' }}</td></tr>
                    </table>
                </div>

                {{-- Veículo --}}
                <div class="assinatura-secao">
                    <div class="assinatura-secao-titulo"><i class="fas fa-car mr-1"></i>Veículo</div>
                    <table class="assinatura-tabela">
                        <tr><td>Marca / Modelo</td><td><strong>{{ $marcaNome }} {{ $modeloNome }}</strong></td></tr>
                        <tr><td>Placa</td><td>{{ $placa }}</td></tr>
                        <tr><td>Grupo / Plano</td><td>{{ $grupoNome }}</td></tr>
                        <tr><td>Cor</td><td>{{ $cor }}</td></tr>
                        <tr><td>Ano</td><td>{{ $ano }}</td></tr>
                        <tr><td>Quilometragem</td><td>{{ number_format((float)$km, 0, ',', '.') }} km</td></tr>
                        <tr><td>Combustível</td><td>{{ $comb }}</td></tr>
                    </table>
                </div>

                {{-- Período --}}
                <div class="assinatura-secao">
                    <div class="assinatura-secao-titulo"><i class="fas fa-calendar mr-1"></i>Período da Locação</div>
                    <table class="assinatura-tabela">
                        <tr><td>Retirada</td><td><strong>{{ $dataInicioAssinatura ? \Carbon\Carbon::parse($dataInicioAssinatura)->format('d/m/Y H:i') : '—' }}</strong></td></tr>
                        <tr><td>Local de retirada</td><td>{{ $ctx['estacao_retirada'] ?? '—' }}</td></tr>
                        <tr><td>Devolução prevista</td><td><strong>{{ $dataFimAssinatura ? \Carbon\Carbon::parse($dataFimAssinatura)->format('d/m/Y H:i') : '—' }}</strong></td></tr>
                        <tr><td>Local de devolução</td><td>{{ $ctx['estacao_devolucao'] ?? '—' }}</td></tr>
                        <tr><td>Duração</td><td>{{ $diasAssinatura }} dia(s)</td></tr>
                        @if(!empty($ctx['plano_nome']))
                        <tr><td>Plano</td><td>{{ $ctx['plano_nome'] }}</td></tr>
                        @endif
                    </table>
                </div>

                {{-- Financeiro --}}
                <div class="assinatura-secao">
                    <div class="assinatura-secao-titulo"><i class="fas fa-money-bill mr-1"></i>Financeiro</div>
                    <table class="assinatura-tabela">
                        <tr><td>{{ $tarifaLabelAssinatura }}</td><td>{{ $moedaSimbolo }} {{ number_format($tarifaBaseAssinatura, 2, ',', '.') }}</td></tr>
                        @if(!$isMap)
                        <tr><td>Diárias</td><td>{{ $diasAssinatura }} dia(s)</td></tr>
                        <tr><td>Subtotal</td><td><strong>{{ $moedaSimbolo }} {{ number_format($subtotalAssinatura, 2, ',', '.') }}</strong></td></tr>
                        @else
                            @if(!empty($ctx['opcionais_lista']) && is_array($ctx['opcionais_lista']))
                                @foreach($ctx['opcionais_lista'] as $opcItem)
                                    <tr>
                                        <td style="padding-left: 18px; color: #475569;">
                                            <i class="fas fa-angle-right mr-1" style="font-size:0.75rem;"></i> {{ $opcItem['nome'] }}
                                            @if(($opcItem['quantidade'] ?? 1) > 1) (x{{ $opcItem['quantidade'] }}) @endif
                                        </td>
                                        <td>{{ $moedaSimbolo }} {{ number_format((float)($opcItem['subtotal'] ?? 0), 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @endif
                            @if(!empty($ctx['valor_opcionais']) && (float)$ctx['valor_opcionais'] > 0)
                            <tr><td><strong>Soma dos opcionais</strong></td><td><strong>{{ $moedaSimbolo }} {{ number_format((float)$ctx['valor_opcionais'], 2, ',', '.') }}</strong></td></tr>
                            @endif
                        @endif
                        @if($taxaEntregaAssinatura > 0)
                        <tr><td>Taxa de entrega</td><td>{{ $moedaSimbolo }} {{ number_format($taxaEntregaAssinatura, 2, ',', '.') }}</td></tr>
                        @endif
                        @if($taxaColetaAssinatura > 0)
                        <tr><td>Taxa de coleta</td><td>{{ $moedaSimbolo }} {{ number_format($taxaColetaAssinatura, 2, ',', '.') }}</td></tr>
                        @endif
                        <tr><td style="font-weight:700;font-size:.9rem;">Valor total por ciclo</td><td style="font-weight:800;font-size:.9rem;color:#059669;">{{ $moedaSimbolo }} {{ number_format($valorTotalAssinatura, 2, ',', '.') }}</td></tr>
                        @if(!empty($ctx['caucao']))
                        @php
                            $caucaoPago = isset($reserva->calcao) && in_array($reserva->calcao->status, ['capturado', 'autorizado', 'pago', 'sucesso'], true);
                        @endphp
                        <tr>
                            <td style="font-size:.85rem;color:#475569;padding-top:6px;">
                                Caução
                                @if(!$caucaoPago)
                                    <span style="font-size: 0.72rem; color: #b45309; font-weight: 700; margin-left: 4px; background-color: #fef3c7; padding: 1px 4px; border-radius: 3px;">Pendente</span>
                                @else
                                    <span style="font-size: 0.72rem; color: #15803d; font-weight: 700; margin-left: 4px; background-color: #dcfce7; padding: 1px 4px; border-radius: 3px;">Confirmado</span>
                                @endif
                            </td>
                            <td style="font-size:.85rem;padding-top:6px;">
                                <strong style="color: {{ !$caucaoPago ? '#b45309' : '#15803d' }};">
                                    {{ $moedaSimbolo }} {{ number_format((float)$ctx['caucao'], 2, ',', '.') }}
                                </strong>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>

                {{-- Observações --}}
                @if(!empty($ci?->notas_saida) || !empty($ci?->observacoes_saida))
                <div class="assinatura-secao">
                    <div class="assinatura-secao-titulo"><i class="fas fa-sticky-note mr-1"></i>Observações</div>
                    @if(!empty($ci?->notas_saida))<p style="font-size:.82rem;color:#475569;margin:2px 0;">{{ $ci->notas_saida }}</p>@endif
                    @if(!empty($ci?->observacoes_saida))<p style="font-size:.82rem;color:#475569;margin:2px 0;">{{ $ci->observacoes_saida }}</p>@endif
                </div>
                @endif

                {{-- Contrato --}}
                <div class="assinatura-secao" style="border:none;margin-bottom:0;padding-bottom:0;">
                    <div class="assinatura-secao-titulo"><i class="fas fa-file-contract mr-1"></i>Contrato</div>
                    <table class="assinatura-tabela">
                        <tr><td>Nº do contrato</td><td><strong>#{{ $ci?->id ?? $reserva->id ?? '—' }}</strong></td></tr>
                        @if(!empty($ci?->data_saida))
                        <tr><td>Data de saída</td><td>{{ \Carbon\Carbon::parse($ci->data_saida)->format('d/m/Y') }} {{ $ci->hora_saida ? substr($ci->hora_saida, 0, 5) : '' }}</td></tr>
                        @endif
                        <tr><td>Reserva</td><td>{{ $codigoAssinatura }}</td></tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Coluna da Direita: Assinatura --}}
        <div class="col-md-6 mb-4">
            <div class="assinatura-card h-100 d-flex flex-column" style="border-radius:24px; box-shadow: 0 10px 30px rgba(12,20,40,0.06); border-top:1px solid #e2e8f0; justify-content: space-between;">
                <form id="form-assinatura-checkin" enctype="multipart/form-data" class="d-flex flex-column h-100" style="flex:1; justify-content: space-between;">
                    <div>
                        {{-- Tabs de modo --}}
                        @if(empty($origemMinhasReservas))
                        <div class="assinatura-tabs">
                            <button type="button" class="assinatura-tab active" data-tab="digital">
                                <i class="fas fa-signature"></i> Assinar Digitalmente
                            </button>
                            <button type="button" class="assinatura-tab" data-tab="upload">
                                <i class="fas fa-file-upload"></i> Anexar Contrato PDF
                            </button>
                        </div>
                        @endif

                        {{-- Tab: Assinatura Digital --}}
                        <div id="tab-digital" class="assinatura-tab-content active">
                            {{-- Instrução --}}
                            <div class="assinatura-instruction">
                                <i class="fas fa-pencil-alt"></i>
                                <span>Use o mouse ou toque na área abaixo para assinar:</span>
                            </div>

                            {{-- Pad --}}
                            <div class="assinatura-pad-wrapper">
                                <canvas id="signature-pad" style="height: 250px;"></canvas>
                                <div class="assinatura-pad-hint">Área de assinatura</div>
                            </div>

                            @if($exigirAceiteTermo)
                                @php
                                    $termoAceitoReserva = \App\Models\TermoConsentimento::where('empresa_id', $empresa->id ?? null)
                                        ->where('tipo', 'aceito-reserva')
                                        ->where('ativo', 'S')
                                        ->orderBy('ordem')
                                        ->first();
                                @endphp
                                <div class="assinatura-aceite" style="margin: 8px 0 16px;">
                                    <label style="display:flex;align-items:flex-start;gap:10px;cursor:pointer;font-size:.92rem;color:#0f172a;">
                                        <input type="checkbox" id="aceite_termo_checkin" name="aceite_termo_checkin" value="1" style="margin-top:4px;">
                                        <span>
                                            Li e aceito o termo de check-in da reserva, confirmando que recebi as informações do veículo e concordo com as condições registradas.
                                            @if($termoAceitoReserva)
                                                <a href="#" data-toggle="modal" data-target="#modalTermoConsentimento" style="text-decoration: underline; color: #4338ca; font-weight: 600;">(Ver Termo)</a>
                                            @endif
                                        </span>
                                    </label>
                                </div>
                            @endif

                            {{-- Ações --}}
                            <div class="assinatura-actions mb-4">
                                <button type="button" id="btn_limpar" class="assinatura-btn assinatura-btn--ghost">
                                    <i class="fas fa-eraser"></i>
                                    Limpar
                                </button>
                                <button type="button" id="btn_salvar" class="assinatura-btn assinatura-btn--primary">
                                    <i class="fas fa-check"></i>
                                    Confirmar Assinatura
                                </button>
                            </div>
                        </div>

                        {{-- Tab: Upload PDF --}}
                        @if(empty($origemMinhasReservas))
                        <div id="tab-upload" class="assinatura-tab-content" style="display:none;">
                            <div class="assinatura-instruction">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <span>Anexe o contrato assinado em formato PDF:</span>
                            </div>

                            <div class="assinatura-upload-area" id="upload-drop-zone" style="padding: 60px 20px;">
                                <input type="file" id="contrato_pdf" accept=".pdf,application/pdf" style="display:none;">
                                <div class="assinatura-upload-placeholder" id="upload-placeholder">
                                    <i class="fas fa-file-pdf"></i>
                                    <p>Arraste o arquivo PDF aqui ou <a href="#" id="upload-browse-link">clique para selecionar</a></p>
                                    <span class="muted">Tamanho máximo: 10MB</span>
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

                            <div class="assinatura-actions mb-4">
                                <button type="button" id="btn_upload_salvar" class="assinatura-btn assinatura-btn--primary" disabled>
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    Enviar Contrato Assinado
                                </button>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Status --}}
                    <div id="assinatura_status" class="assinatura-status" style="display:none; margin-top: auto;"></div>
                </form>
            </div>
        </div>
    </div>

    @if(isset($termoAceitoReserva) && $termoAceitoReserva)
    <!-- Modal Termo de Consentimento -->
    <div class="modal fade" id="modalTermoConsentimento" tabindex="-1" role="dialog" aria-labelledby="modalTermoTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none; box-shadow: 0 15px 40px rgba(0,0,0,0.15);">
                <div class="modal-header" style="background: linear-gradient(135deg, #0c1428 0%, #1a2848 100%); color: white; padding: 18px 24px;">
                    <h5 class="modal-title" id="modalTermoTitle" style="font-weight: 700;">{{ $termoAceitoReserva->titulo }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity: 0.8; background: transparent; border: none; font-size: 1.5rem;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 24px; max-height: 60vh; overflow-y: auto; font-size: 0.95rem; line-height: 1.6; color: #334155;">
                    {!! $termoAceitoReserva->texto !!}
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
        .assinatura-tabs { flex-direction: column; }
    }

    /* Tabs */
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
    .assinatura-tab:not(:last-child) {
        border-right: 1px solid #e2e8f0;
    }
    .assinatura-tab:hover {
        background: #eef2ff;
        color: #4338ca;
    }
    .assinatura-tab.active {
        background: #fff;
        color: #4338ca;
        font-weight: 700;
        box-shadow: inset 0 -3px 0 #4338ca;
    }
    .assinatura-tab i { font-size: 1rem; }

    .assinatura-tab-content { display: none; }
    .assinatura-tab-content.active { display: block; }

    /* Upload area */
    .assinatura-upload-area {
        border: 2px dashed #c7d2fe;
        border-radius: 16px;
        background: #fafbff;
        padding: 40px 20px;
        text-align: center;
        transition: all 0.2s;
        margin-bottom: 16px;
        cursor: pointer;
    }
    .assinatura-upload-area:hover,
    .assinatura-upload-area.dragover {
        border-color: #4338ca;
        border-style: solid;
        background: #eef2ff;
    }
    .assinatura-upload-placeholder i {
        font-size: 2.5rem;
        color: #c7d2fe;
        margin-bottom: 10px;
    }
    .assinatura-upload-placeholder p {
        font-size: 0.88rem;
        color: #475569;
        margin: 0 0 4px;
    }
    .assinatura-upload-placeholder p a {
        color: #4338ca;
        font-weight: 600;
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
</style>

@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script src="{{ asset('js/sweetAlert.min.js') }}"></script>
<script>
(function () {
    // ─── Tabs ───
    const tabs = document.querySelectorAll('.assinatura-tab');
    const tabContents = document.querySelectorAll('.assinatura-tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tabContents.forEach(c => { c.classList.remove('active'); c.style.display = 'none'; });
            tab.classList.add('active');
            const target = document.getElementById('tab-' + tab.dataset.tab);
            if (target) { target.classList.add('active'); target.style.display = 'block'; }
        });
    });

    // ─── Signature Pad ───
    const canvas = document.getElementById('signature-pad');
    const hint   = document.querySelector('.assinatura-pad-hint');
    const aceiteTermo = document.getElementById('aceite_termo_checkin');

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
        const veiculo_id  = "{{ $ci?->veiculo_id ?? $reserva->veiculo_id ?? 0 }}";
        const contrato_id = "{{ $ci?->id ?? 0 }}";
        @if($exigirAceiteTermo)
        if (!aceiteTermo || !aceiteTermo.checked) {
            btnSalvar.disabled = false;
            btnSalvar.innerHTML = originalHtml;
            showStatus('<i class="fas fa-exclamation-triangle"></i> Você precisa aceitar o termo de check-in antes de confirmar a assinatura.', 'error');
            return;
        }
        @endif


        @php
            $urlSalvarAssinatura = request()->routeIs('reserva.minhas.*')
                ? route('reserva.minhas.salvarAssinarContratoCheckInMinhaReserva', [
                    'codigo' => $codigoAssinatura,
                ])
                : route('reserva.salvarAssinarContratoCheckIn', [
                    'codigo' => $codigoAssinatura,
                ]);
        @endphp
        $.ajax({
            url: @json($urlSalvarAssinatura),
            method: 'POST',
            data: {
                assinatura: dataURL,
                veiculo_id,
                contrato_id,
                aceite_termo_checkin: aceiteTermo && aceiteTermo.checked ? 1 : 0,
                origem_minhas: "{{ $origemMinhasReservas ?? false ? '1' : '0' }}",
                _token: '{{ csrf_token() }}',
            },
            success: function (response) {
                showStatus('<i class="fas fa-check-circle"></i> Assinatura salva com sucesso! Redirecionando...', 'success');
                setTimeout(() => {
                    window.location.href = response.redirect || @json($redirectAfterSignUrl ?? route('reserva.show', ['reserva' => $codigoAssinatura]));
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

    // ─── File Upload ───
    const fileInput = document.getElementById('contrato_pdf');
    const dropZone = document.getElementById('upload-drop-zone');
    const placeholder = document.getElementById('upload-placeholder');
    const preview = document.getElementById('upload-preview');
    const fileName = document.getElementById('upload-file-name');
    const fileSize = document.getElementById('upload-file-size');
    const removeBtn = document.getElementById('upload-remove');
    const btnUpload = document.getElementById('btn_upload_salvar');
    const browseLink = document.getElementById('upload-browse-link');
    let selectedFile = null;

    function formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    function setFile(file) {
        if (!file || file.type !== 'application/pdf') {
            showStatus('<i class="fas fa-exclamation-triangle"></i> Apenas arquivos PDF são aceitos.', 'error');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            showStatus('<i class="fas fa-exclamation-triangle"></i> O arquivo excede o tamanho máximo de 10MB.', 'error');
            return;
        }
        selectedFile = file;
        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        placeholder.style.display = 'none';
        preview.style.display = 'flex';
        btnUpload.disabled = false;
    }

    function clearFile() {
        selectedFile = null;
        fileInput.value = '';
        placeholder.style.display = '';
        preview.style.display = 'none';
        btnUpload.disabled = true;
    }

    browseLink.addEventListener('click', (e) => { e.preventDefault(); fileInput.click(); });
    dropZone.addEventListener('click', (e) => {
        if (e.target === removeBtn || removeBtn.contains(e.target)) return;
        if (!selectedFile) fileInput.click();
    });

    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) setFile(e.target.files[0]);
    });

    removeBtn.addEventListener('click', (e) => { e.stopPropagation(); clearFile(); });

    // Drag and drop
    ['dragenter', 'dragover'].forEach(evt => {
        dropZone.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); dropZone.classList.add('dragover'); });
    });
    ['dragleave', 'drop'].forEach(evt => {
        dropZone.addEventListener(evt, (e) => { e.preventDefault(); e.stopPropagation(); dropZone.classList.remove('dragover'); });
    });
    dropZone.addEventListener('drop', (e) => {
        if (e.dataTransfer.files.length > 0) setFile(e.dataTransfer.files[0]);
    });

    btnUpload.addEventListener('click', () => {
        if (!selectedFile) {
            showStatus('<i class="fas fa-exclamation-triangle"></i> Selecione um arquivo PDF.', 'error');
            return;
        }

        const originalHtml = btnUpload.innerHTML;
        btnUpload.disabled = true;
        btnUpload.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

        const formData = new FormData();
        formData.append('contrato_pdf', selectedFile);
        formData.append('veiculo_id', "{{ $ci?->veiculo_id ?? $reserva->veiculo_id ?? 0 }}");
        formData.append('contrato_id', "{{ $ci?->id ?? 0 }}");
        formData.append('modo', 'upload');
        formData.append('origem_minhas', "{{ $origemMinhasReservas ?? false ? '1' : '0' }}");
        if (aceiteTermo && aceiteTermo.checked) {
            formData.append('aceite_termo_checkin', '1');
        }
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: @json($urlSalvarAssinatura),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                showStatus('<i class="fas fa-check-circle"></i> Contrato assinado enviado com sucesso! Redirecionando...', 'success');
                setTimeout(() => {
                    window.location.href = response.redirect || @json($redirectAfterSignUrl ?? route('reserva.show', ['reserva' => $codigoAssinatura]));
                }, 1600);
            },
            error: function (xhr) {
                btnUpload.disabled = false;
                btnUpload.innerHTML = originalHtml;
                const msg = xhr.responseJSON?.message || 'Erro ao enviar o contrato. Tente novamente.';
                showStatus('<i class="fas fa-exclamation-triangle"></i> ' + msg, 'error');
            },
        });
    });

    // ─── Status ───
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
