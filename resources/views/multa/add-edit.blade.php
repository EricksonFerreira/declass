@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger col-xl-10 col-lg-11 col-md-12 col-sm-12 col-xs-12">
            <ul class="mb-0 pl-3">
                @foreach (array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container-fluid px-0 multa-page">
        <div class="module-card mb-3">
            <div class="module-card-header multa-hero-compact">
                <div class="module-header-left">
                    <div class="module-header-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <div class="module-header-eyebrow">Financeiro · Multas</div>
                        <h1 class="module-header-title">{{ $titulo }}</h1>
                        <p class="module-header-sub">Preencha os dados da infração, valores, vínculo com reserva e anexos.</p>
                    </div>
                </div>

                <div class="module-header-right">
                    <a href="{{ route('multa.index') }}" class="btn-xp btn-xp--outline-light btn-xp--sm">
                        <i class="fas fa-list"></i> Lista
                    </a>
                    <a href="{{ route('veiculo.index') }}" class="btn-xp btn-xp--outline-light btn-xp--sm">
                        <i class="fas fa-car"></i> Veículos
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-9 mb-3">
                <div class="config-card shadow-sm border-0">
                    <div class="vs-panel-head border-bottom">
                        <div>
                            <span class="vs-panel-head__eyebrow">Cadastro operacional</span>
                            <h4 class="vs-panel-head__title mb-1">{{ isset($multa) ? 'Editar multa' : 'Nova multa' }}</h4>
                            <p class="vs-panel-head__sub mb-0">Use o assistente em etapas para registrar a infração sem perder o vínculo financeiro.</p>
                        </div>
                        <span class="vs-badge-soft is-warning">
                            <i class="fas fa-clipboard-list"></i>
                            Etapas guiadas
                        </span>
                    </div>

                    <div class="card-body p-4">
                <form id="formMulta" method="POST" action="{{ $rota }}" enctype="multipart/form-data">
                    @csrf
                    @if($metodo === 'PUT') @method('PUT') @endif

                    <div class="multa-page">
                        <div class="multa-wizard-top">
                            <div class="multa-wizard-steps" role="tablist" aria-label="Passos da multa">
                                <button type="button" class="multa-wizard-step is-active" data-multa-step-btn="1">
                                    <span class="num">1</span><span class="txt">Infração</span>
                                </button>
                                <div class="div"></div>
                                <button type="button" class="multa-wizard-step" data-multa-step-btn="2">
                                    <span class="num">2</span><span class="txt">Valores</span>
                                </button>
                                <div class="div"></div>
                                <button type="button" class="multa-wizard-step" data-multa-step-btn="3">
                                    <span class="num">3</span><span class="txt">Documentos</span>
                                </button>
                            </div>
                            <small class="text-muted d-block mt-2">
                                Dica: avance pelos passos e finalize em "Documentos" para salvar.
                            </small>
                        </div>

                        {{-- STEP: Infração --}}
                        <div data-multa-step="1" class="">
                            <div class="multa-section-title">Vínculo com Reserva e Veículo</div>

                            <div class="alert alert-info small py-2 px-3 mb-3">
                                <i class="fas fa-magic mr-1"></i>
                                <strong>Preenchimento automático:</strong> ao selecionar o veículo e preencher a
                                <strong>data/hora da infração</strong>, o sistema verifica se havia uma reserva ativa
                                naquele momento e vincula automaticamente. Se você já souber o número da reserva,
                                pode carregar tudo com o botão abaixo.
                            </div>

                            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap:10px">
                                <button type="button" id="btnPreencherMultaAuto" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-wand-magic-sparkles mr-1"></i>Preencher automaticamente
                                </button>
                                <small id="multa_auto_status" class="text-muted" style="font-size:.75rem;">
                                    Informe a reserva, ou selecione veículo e data/hora para localizar o vínculo.
                                </small>
                            </div>

                            <div class="form-row">
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Reserva</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" name="reserva_id" id="multa_reserva_id_input"
                                               class="form-control form-control-sm"
                                               value="{{ old('reserva_id', $multa->reserva_id ?? $reserva?->id ?? '') }}"
                                               placeholder="ID (auto ou manual)">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" id="btnBuscarReserva" title="Buscar reserva">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger d-none" id="btnLimparReserva" title="Limpar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <span class="input-group-text px-2" id="multa_reserva_icon" title="">
                                                <i class="fas fa-link text-muted"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <small id="multa_reserva_info" class="text-muted d-block mt-1" style="font-size:.75rem;">
                                        @if(!empty($multa->reserva_id ?? $reserva?->id))
                                            @php
                                                $codigoReservaMulta = $reserva?->codigoReserva?->codigo
                                                    ?? ($multa->reserva_id ? 'RES-' . str_pad((string) $multa->reserva_id, 6, '0', STR_PAD_LEFT) : null);
                                            @endphp
                                            <span class="text-success"><i class="fas fa-check-circle mr-1"></i>Reserva #{{ $codigoReservaMulta ?? $multa->reserva_id }} vinculada.</span>
                                        @endif
                                    </small>
                                </div>
                                <div class="col-lg-8 form-group">
                                    <label class="form-label-sm">Veículo</label>
                                    <input type="hidden" name="veiculo_id" id="veiculo_id_input"
                                           value="{{ old('veiculo_id', $multa->veiculo_id ?? $veiculo?->id ?? '') }}">
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="veiculo_display" class="form-control form-control-sm"
                                               readonly placeholder="Clique na lupa para buscar um veículo..."
                                               value="{{ old('veiculo_id') ? '' : ($veiculo ? (($veiculo->nome ?? '') . ($veiculo->placa_licenca ? ' — ' . $veiculo->placa_licenca : '')) : '') }}">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-outline-secondary" id="btnBuscarVeiculo" title="Buscar veículo">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button type="button" class="btn btn-outline-danger d-none" id="btnLimparVeiculo" title="Limpar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="multa-section-title mt-3">Dados da Infração</div>
                            <div class="form-row">
                                <div class="col-lg-3 form-group">
                                    <label class="form-label-sm">Placa no Momento <span class="text-danger">*</span></label>
                                    <input type="text" name="placa_momento" class="form-control form-control-sm"
                                           value="{{ old('placa_momento', $multa->placa_momento ?? $veiculo?->placa_licenca ?? '') }}" required>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="form-label-sm">Número do Auto</label>
                                    <input type="text" name="numero_auto" class="form-control form-control-sm"
                                           value="{{ old('numero_auto', $multa->numero_auto ?? '') }}">
                                </div>
                                <div class="col-lg-6 form-group">
                                    <label class="form-label-sm">Órgão Autuador</label>
                                    <input type="text" name="orgao_autuador" class="form-control form-control-sm"
                                           value="{{ old('orgao_autuador', $multa->orgao_autuador ?? '') }}">
                                </div>
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Data e Hora da Infração <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="data_hora_infracao" class="form-control form-control-sm"
                                           value="{{ old('data_hora_infracao', $multa?->data_hora_infracao?->format('Y-m-d\TH:i') ?? '') }}" required>
                                </div>
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Data de Vencimento <span class="text-danger">*</span></label>
                                    <input type="date" name="data_vencimento" class="form-control form-control-sm"
                                           value="{{ old('data_vencimento', $multa?->data_vencimento?->format('Y-m-d') ?? '') }}" required>
                                </div>
                                <div class="col-lg-12 form-group">
                                    <label class="form-label-sm">Local da Infração</label>
                                    <input type="text" name="local_infracao" class="form-control form-control-sm"
                                           value="{{ old('local_infracao', $multa->local_infracao ?? '') }}">
                                </div>
                                <div class="col-lg-12 form-group">
                                    <label class="form-label-sm">Descrição</label>
                                    <textarea name="descricao" class="form-control form-control-sm" rows="3">{{ old('descricao', $multa->descricao ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- STEP: Valores e Status --}}
                        <div data-multa-step="2" class="d-none">
                            <div class="multa-section-title">Valores</div>
                            <div class="form-row">
                                <div class="col-lg-3 form-group">
                                    <label class="form-label-sm">Valor Original <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                        <input type="number" step="0.01" min="0" name="valor_original" class="form-control form-control-sm"
                                               value="{{ old('valor_original', $multa->valor_original ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="form-label-sm">Valor Desconto</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                        <input type="number" step="0.01" min="0" name="valor_desconto" class="form-control form-control-sm"
                                               value="{{ old('valor_desconto', $multa->valor_desconto ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="form-label-sm">Valor Final <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text">R$</span></div>
                                        <input type="number" step="0.01" min="0" name="valor_final" id="valor_final" class="form-control form-control-sm"
                                               value="{{ old('valor_final', $multa->valor_final ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="col-lg-3 form-group">
                                    <label class="form-label-sm">Pontos (0–10)</label>
                                    <input type="number" min="0" max="10" name="pontos" class="form-control form-control-sm"
                                           value="{{ old('pontos', $multa->pontos ?? '') }}">
                                </div>
                            </div>

                            <div class="multa-section-title mt-3">Financeiro — Quem paga esta multa?</div>

                            <div class="alert alert-light border small py-2 px-3 mb-3">
                                <i class="fas fa-info-circle text-info mr-1"></i>
                                <strong>Entenda:</strong>
                                O <strong>Destino de Cobrança</strong> define se o custo da multa fica com a
                                <strong>Empresa</strong> (débito interno — não cobra ninguém) ou é repassado ao
                                <strong>Cliente</strong> da reserva (ele precisa pagar).
                                A <strong>Forma de Pagamento</strong> indica como o valor foi ou será quitado —
                                pode ser dinheiro em mãos, cartão, PIX ou boleto.
                                Você pode deixar em branco agora e registrar o pagamento depois na tela de detalhes da multa.
                            </div>

                            <div class="form-row">
                                <div class="col-lg-6 form-group">
                                    <label class="form-label-sm">
                                        Destino de Cobrança
                                        <span class="text-danger">*</span>
                                        <i class="fas fa-question-circle text-muted ml-1"
                                           data-toggle="tooltip"
                                           title="Empresa = débito interno (a empresa arca com o custo). Cliente = o cliente da reserva será cobrado."></i>
                                    </label>
                                    <select name="destino_cobranca" class="form-control form-control-sm" required>
                                        @foreach(\App\Models\Multa::DESTINO_COBRANCA as $key => $lbl)
                                            <option value="{{ $key }}"
                                                {{ old('destino_cobranca', $multa->destino_cobranca ?? 'empresa') === $key ? 'selected' : '' }}>
                                                {{ $lbl }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-6 form-group">
                                    <label class="form-label-sm">
                                        Forma de Pagamento
                                        <i class="fas fa-question-circle text-muted ml-1"
                                           data-toggle="tooltip"
                                           title="Opcional agora. Você pode registrar o pagamento depois, na tela de detalhes da multa."></i>
                                    </label>
                                    <select name="forma_pagamento" class="form-control form-control-sm">
                                        <option value="">— Não informar agora —</option>
                                        @foreach(\App\Models\Multa::FORMAS_PAGAMENTO as $key => $lbl)
                                            <option value="{{ $key }}"
                                                {{ old('forma_pagamento', $multa->forma_pagamento ?? '') === $key ? 'selected' : '' }}>
                                                {{ $lbl }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-6 form-group">
                                    <label class="form-label-sm">Referência / Comprovante</label>
                                    <input type="text" name="referencia_pagamento" class="form-control form-control-sm"
                                           value="{{ old('referencia_pagamento', $multa->referencia_pagamento ?? '') }}"
                                           placeholder="NSU do cartão, código PIX, nº do comprovante...">
                                    <small class="text-muted">Opcional — identifica este pagamento para consulta futura.</small>
                                </div>
                            </div>

                            <div class="multa-section-title mt-3">Status</div>
                            <div class="form-row">
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Status da Multa <span class="text-danger">*</span></label>
                                    <select name="status_multa" class="form-control form-control-sm" required>
                                        @foreach($statusMulta as $key => $label)
                                            <option value="{{ $key }}" {{ old('status_multa', $multa->status_multa ?? 'pendente') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-4 form-group">
                                    <label class="form-label-sm">Status de Desconto <span class="text-danger">*</span></label>
                                    <select name="status_desconto" class="form-control form-control-sm" required>
                                        @foreach($statusDesconto as $key => $label)
                                            <option value="{{ $key }}" {{ old('status_desconto', $multa->status_desconto ?? 'nao_aplicado') === $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-12 form-group">
                                    <label class="form-label-sm">Observação</label>
                                    <textarea name="observacao" class="form-control form-control-sm" rows="3">{{ old('observacao', $multa->observacao ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>

                        {{-- STEP: Documentos --}}
                        <div data-multa-step="3" class="d-none">
                            <div class="multa-section-title">Anexos</div>
                            <div class="form-row">
                                <div class="col-lg-6 form-group">
                                    <label class="form-label-sm">Comprovante (imagem ou PDF)</label>
                                    <input type="file" name="anexo_comprovante" class="form-control-file form-control-sm"
                                           accept=".jpg,.jpeg,.png,.pdf">
                                    @if(!empty($multa?->anexo_comprovante_path))
                                        <small class="text-muted d-block mt-1">
                                            Atual: <a href="{{ $multa->anexo_comprovante_url }}" target="_blank">
                                                <i class="fas fa-external-link-alt mr-1"></i>ver arquivo
                                            </a>
                                        </small>
                                    @endif
                                </div>
                                <div class="col-lg-6 form-group">
                                    <label class="form-label-sm">PDF do Auto de Infração</label>
                                    <input type="file" name="anexo_pdf" class="form-control-file form-control-sm" accept=".pdf">
                                    @if(!empty($multa?->anexo_pdf_path))
                                        <small class="text-muted d-block mt-1">
                                            Atual: <a href="{{ $multa->anexo_pdf_url }}" target="_blank">
                                                <i class="fas fa-external-link-alt mr-1"></i>ver PDF
                                            </a>
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="multa-wizard-footer">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="multaWizardPrev">
                                <i class="fas fa-arrow-left mr-1"></i>Voltar
                            </button>
                            <button type="button" class="btn btn-danger btn-sm" id="multaWizardNext">
                                Continuar <i class="fas fa-arrow-right ml-1"></i>
                            </button>
                            <button type="submit" id="btnSalvarMulta" class="btn btn-danger btn-sm d-none">
                                <i class="fas fa-save mr-1"></i>Salvar multa
                            </button>
                        </div>

                    </div>

                </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 mb-3">
                <div class="config-card shadow-sm border-0 h-100">
                    <div class="vs-panel-head border-bottom">
                        <div>
                            <span class="vs-panel-head__eyebrow">Resumo rápido</span>
                            <h4 class="vs-panel-head__title mb-1">Identidade da multa</h4>
                            <p class="vs-panel-head__sub mb-0">Os dados principais ajudam a manter o fluxo financeiro coerente.</p>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="vehicle-summary-stack">
                            <div class="vehicle-summary-card">
                                <span>Veículo</span>
                                <strong>{{ $veiculo?->nome ?? 'Não selecionado' }}</strong>
                                <small>{{ $veiculo?->placa_licenca ?? 'Use a busca para vincular o veículo' }}</small>
                            </div>
                            <div class="vehicle-summary-card">
                                <span>Reserva</span>
                                <strong>{{ $reserva?->id ? '#' . $reserva->id : 'Não vinculada' }}</strong>
                                <small>{{ $reserva?->codigoReserva?->codigo ?? 'Pode ser preenchida automaticamente' }}</small>
                            </div>
                            <div class="vehicle-summary-card">
                                <span>Status</span>
                                <strong>{{ old('status_multa', $multa->status_multa ?? 'pendente') }}</strong>
                                <small>O status financeiro é refletido no fluxo de cobrança.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

<x-veiculo.busca-avancada-modal
    prefix="multa"
    title="Buscar veiculo"
    trigger-id="btnBuscarVeiculo"
    hidden-input-id="veiculo_id_input"
    display-input-id="veiculo_display"
    clear-btn-id="btnLimparVeiculo"
    auto-fill-placa-selector="[name=placa_momento]"
    :filtro-marcas="$filtroMarcas"
    :filtro-grupos="$filtroGrupos"
    :filtro-tipos="$filtroTipos"
    :filtro-status="$filtroStatus"
/>

@section('css')
<style>
.multa-page {
    padding-bottom: 8px;
}

.module-card {
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 24px rgba(12, 20, 40, .10);
    background: #fff;
}

.module-card-header {
    background: linear-gradient(135deg, #0c1428 0%, #1a2848 55%, #1e3461 100%);
    padding: 24px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    position: relative;
    overflow: hidden;
}

.module-card-header::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: radial-gradient(circle, rgba(255, 255, 255, 0.055) 1px, transparent 1px);
    background-size: 20px 20px;
    pointer-events: none;
}

.module-card-header::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #4338ca, #0ea5e9, #10b981);
}

.module-header-left {
    display: flex;
    align-items: center;
    gap: 16px;
    position: relative;
    z-index: 1;
}

.module-header-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: rgba(255, 255, 255, .12);
    border: 1px solid rgba(255, 255, 255, .18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    color: #60a5fa;
    flex-shrink: 0;
    backdrop-filter: blur(8px);
}

.module-header-eyebrow {
    font-size: .62rem;
    font-weight: 700;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: rgba(148, 163, 184, .8);
    display: block;
    margin-bottom: 3px;
}

.module-header-title {
    font-size: 1.45rem;
    font-weight: 800;
    color: #fff;
    margin: 0 0 3px;
    letter-spacing: -.02em;
}

.module-header-sub {
    margin: 0;
    font-size: .82rem;
    color: rgba(148, 163, 184, .9);
}

.module-header-right {
    position: relative;
    z-index: 1;
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.config-card {
    background: #fff;
    border: 1px solid #e2e9f4;
    border-radius: 18px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(12, 20, 40, .06);
}

.btn-xp {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: linear-gradient(135deg, #4338ca, #6366f1);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 9px 16px;
    font-size: .82rem;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
    transition: all .18s;
    letter-spacing: -.01em;
    box-shadow: 0 4px 12px rgba(67, 56, 202, .25);
}

.btn-xp:hover {
    opacity: .88;
    transform: translateY(-1px);
    color: #fff;
    text-decoration: none;
}

.btn-xp--sm {
    padding: 6px 12px;
    font-size: .78rem;
    border-radius: 8px;
}

.btn-xp--outline-light {
    background: rgba(255, 255, 255, .12); !important;
    border: 1px solid rgba(255, 255, 255, .25);
    color: rgba(255, 255, 255, .9);
    box-shadow: none;
}

.btn-xp--outline-light:hover {
    background: rgba(255, 255, 255, .2);
    color: #fff;
}

.vs-panel-head {
    padding: 18px 20px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    background: #fff;
}

.vs-panel-head__eyebrow {
    display: inline-block;
    margin-bottom: 6px;
    font-size: .62rem;
    font-weight: 800;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: #64748b;
}

.vs-panel-head__title {
    margin: 0 0 6px;
    font-size: 1.08rem;
    font-weight: 800;
    color: #0c1428;
}

.vs-panel-head__sub {
    margin: 0;
    color: #64748b;
    font-size: .84rem;
    max-width: 680px;
}

.vs-badge-soft {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 9px 12px;
    background: #eef2ff;
    color: #3730a3;
    border: 1px solid #c7d2fe;
    border-radius: 999px;
    font-size: .76rem;
    font-weight: 700;
    white-space: nowrap;
}

.vehicle-summary-stack {
    display: grid;
    gap: 12px;
}

.vehicle-summary-card {
    background: #f8fafc;
    border: 1px solid #e9f0fa;
    border-radius: 14px;
    padding: 14px 16px;
}

.vehicle-summary-card span {
    display: block;
    font-size: .68rem;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #64748b;
    margin-bottom: 4px;
}

.vehicle-summary-card strong {
    display: block;
    font-size: .98rem;
    color: #0c1428;
    font-weight: 800;
}

.vehicle-summary-card small {
    display: block;
    color: #64748b;
    margin-top: 4px;
    line-height: 1.4;
}

.multa-section-title {
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #888;
    border-bottom: 1px solid #eee;
    padding-bottom: .3rem;
    margin-bottom: 1rem;
}
.form-label-sm {
    font-size: .82rem;
    font-weight: 600;
    margin-bottom: .25rem;
    display: block;
    color: #444;
}
.multa-page {
    --multa-border: color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 35%, #dbe3ef 65%);
}
.multa-wizard-top {
    border: 1px solid var(--multa-border);
    border-radius: 14px;
    padding: 12px 12px 10px;
    background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
    margin-bottom: 14px;
}
.multa-wizard-steps {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}
.multa-wizard-steps .div {
    flex: 1 1 20px;
    height: 2px;
    background: color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 22%, #e5edf7 78%);
    border-radius: 999px;
    min-width: 12px;
}
.multa-wizard-step {
    appearance: none;
    border: 1px solid color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 30%, #dbe3ef 70%);
    background: #fff;
    border-radius: 999px;
    padding: 8px 10px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 700;
    color: #0f172a;
    cursor: pointer;
    transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease, background .12s ease, color .12s ease;
}
.multa-wizard-step .num {
    width: 22px;
    height: 22px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: color-mix(in srgb, var(--empresa-cor-primaria, #dc3545) 12%, #ffffff 88%);
    color: color-mix(in srgb, var(--empresa-cor-primaria, #dc3545) 88%, #111827 12%);
    border: 1px solid color-mix(in srgb, var(--empresa-cor-primaria, #dc3545) 25%, #dbe3ef 75%);
}
.multa-wizard-step.is-active {
    background: var(--empresa-cor-primaria, #dc3545);
    border-color: var(--empresa-cor-primaria, #dc3545);
    color: var(--empresa-cor-primaria-contraste, #fff);
    box-shadow: 0 10px 22px color-mix(in srgb, var(--empresa-cor-primaria, #dc3545) 22%, transparent 78%);
}
.multa-wizard-step.is-active .num {
    background: rgba(255,255,255,.18);
    color: var(--empresa-cor-primaria-contraste, #fff);
    border-color: rgba(255,255,255,.24);
}
.multa-wizard-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    padding: 10px 0 0;
    margin-top: 14px;
    border-top: 1px dashed color-mix(in srgb, var(--empresa-cor-terciaria, #64748B) 28%, #e5edf7 72%);
}
.multa-wizard-footer .btn {
    border-radius: 999px;
    font-weight: 700;
}
</style>
@stop

@push('js')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.css"/>
<link rel="stylesheet" href="/css/driver-tour.css"/>
<script src="https://cdn.jsdelivr.net/npm/driver.js@1/dist/driver.js.iife.js"></script>
<script>
(function () {
    const TOUR_ID     = 'multa-add-edit';
    const STATUS_URL  = '/admin/tour/status/'  + TOUR_ID;
    const CONCLUIR_URL = '/admin/tour/concluir/' + TOUR_ID;
    const RESETAR_URL  = '/admin/tour/resetar/'  + TOUR_ID;
    const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const CACHE_KEY   = 'tour_feito__' + TOUR_ID;
    const forceTour   = new URLSearchParams(window.location.search).get('tour') === '1';

    const steps = [
        { element: '.multa-wizard-top', popover: { title: 'Formulário em 3 etapas', description: 'O cadastro de multa é dividido em: <b>1 – Infração</b> (dados do auto), <b>2 – Valores</b> (montantes e financeiro) e <b>3 – Documentos</b> (comprovantes). Complete cada etapa antes de avançar.', side: 'bottom' } },
        { element: '[data-multa-step-btn="1"]', popover: { title: 'Etapa 1 — Infração', description: 'Informe os dados do auto de infração: placa no momento, número do auto, órgão autuador, data/hora da infração, data de vencimento, local e descrição da ocorrência.', side: 'bottom' } },
        { element: '#veiculo_display', popover: { title: 'Vínculo com veículo', description: 'Clique na lupa para buscar o veículo. Ao selecionar e informar a data da infração, o sistema verifica automaticamente se havia uma reserva ativa e preenche o vínculo com o cliente responsável.', side: 'bottom' } },
        { element: '#multa_reserva_id_input', popover: { title: 'Reserva vinculada', description: 'Quando o sistema encontra uma reserva ativa no momento da infração ela é vinculada automaticamente, facilitando a cobrança ao cliente certo. Você pode também informar o ID manualmente.', side: 'right' } },
        { element: '[data-multa-step-btn="2"]', popover: { title: 'Etapa 2 — Valores', description: 'Preencha o <b>valor original</b> e o <b>valor de desconto</b> (se houver). O <b>valor final</b> é calculado automaticamente. Defina também quem paga (empresa ou cliente), forma de pagamento e status da multa.', side: 'bottom' } },
        { element: '[data-multa-step-btn="3"]', popover: { title: 'Etapa 3 — Documentos', description: 'Anexe o <b>comprovante da infração</b> (imagem ou PDF) e o <b>PDF do auto de infração</b> original. Finalize aqui para salvar a multa.', side: 'bottom' } },
        { element: '#multaWizardNext, #btnSalvarMulta', popover: { title: 'Navegação e salvamento', description: 'Use <b>Continuar</b> para avançar entre as etapas (com validação automática de campos obrigatórios) e <b>Salvar multa</b> na última etapa para gravar o registro.', side: 'top' } },
    ];

    function marcarFeito() {
        localStorage.setItem(CACHE_KEY, '1');
        fetch(CONCLUIR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } }).catch(() => {});
    }

    function getAvailableSteps() {
        return steps.filter(function (step) {
            if (!step || !step.element) return false;

            return String(step.element)
                .split(',')
                .map(function (selector) { return selector.trim(); })
                .filter(Boolean)
                .some(function (selector) {
                    try {
                        return !!document.querySelector(selector);
                    } catch (error) {
                        return false;
                    }
                });
        });
    }

    function buildTour() {
        if (typeof window.driver === 'undefined' || !window.driver.js) return null;

        var availableSteps = getAvailableSteps();
        if (!availableSteps.length) return null;

        var _pulou = false;
        return window.driver.js.driver({
            animate: true, smoothScroll: true, showProgress: true,
            progressText: 'Passo @{{current}} de @{{total}}',
            nextBtnText: 'Próximo →', prevBtnText: '← Anterior', doneBtnText: 'Entendido ✓',
            onDestroyStarted: () => { if (!_pulou) marcarFeito(); },
            onPopoverRender: (popover, { driver }) => {
                if (popover.wrapper.querySelector('.drv-skip-btn')) return;
                var btnTour = document.createElement('button');
                btnTour.className = 'drv-skip-btn drv-skip-tour-btn'; btnTour.type = 'button'; btnTour.textContent = 'Pular tour';
                btnTour.addEventListener('click', function () { _pulou = true; marcarFeito(); driver.destroy(); });
                var btnStep = document.createElement('button');
                btnStep.className = 'drv-skip-btn drv-skip-step-btn'; btnStep.type = 'button'; btnStep.textContent = 'Pular passo';
                btnStep.addEventListener('click', function () { driver.moveNext(); });
                var nav = popover.footer.querySelector('.driver-popover-navigation-btns');
                if (nav) { nav.prepend(btnStep); }
                popover.footer.prepend(btnTour);
            },
            steps: availableSteps,
        });
    }

    function startTour() {
        var tour = buildTour();
        if (!tour) return;
        setTimeout(function () { tour.drive(); }, 350);
    }

    window.restartPageTour = function () {
        localStorage.removeItem(CACHE_KEY);
        fetch(RESETAR_URL, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } })
            .catch(() => {})
            .then(() => startTour());
    };

    if (localStorage.getItem(CACHE_KEY) && !forceTour) return;
    fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            if (data.feito && !forceTour) { localStorage.setItem(CACHE_KEY, '1'); return; }
            startTour();
        })
        .catch(() => {
            if (!localStorage.getItem(CACHE_KEY) || forceTour) startTour();
        });
})();
</script>
<script>
    // ── Tooltips ──
    $('[data-toggle="tooltip"]').tooltip();

    // ── AJAX: preencher reserva/veículo automaticamente ───────────────────────
    const URL_MULTA_BUSCAR_RESERVA = '{{ route("multa.buscarReservaPorVeiculoData") }}';
    let multaReservaTimer = null;

    function multaSetAutoStatus(message, tone = 'muted') {
        const el = document.getElementById('multa_auto_status');
        if (!el) return;
        const colors = {
            muted: 'text-muted',
            info: 'text-info',
            success: 'text-success',
            warning: 'text-warning',
            danger: 'text-danger',
        };
        el.className = colors[tone] || colors.muted;
        el.innerHTML = message;
    }

    function multaSetReservaInfo(html) {
        const infoEl = document.getElementById('multa_reserva_info');
        if (infoEl) infoEl.innerHTML = html;
    }

    function multaResetReservaInfo(message = '') {
        const iconEl = document.getElementById('multa_reserva_icon');
        if (iconEl) iconEl.innerHTML = '<i class="fas fa-link text-muted"></i>';
        multaSetReservaInfo(message);
    }

    function multaPreencherCamposReserva(reserva) {
        const reservaEl = document.getElementById('multa_reserva_id_input');
        const veiculoHidden = document.getElementById('veiculo_id_input');
        const veiculoDisplay = document.getElementById('veiculo_display');
        const placaInput = document.querySelector('[name=placa_momento]');

        if (reservaEl && reserva.id && !reservaEl.value) {
            reservaEl.value = reserva.id;
        }

        if (veiculoHidden && reserva.veiculo_id && !veiculoHidden.value) {
            veiculoHidden.value = reserva.veiculo_id;
        }

        if (veiculoDisplay && reserva.veiculo) {
            veiculoDisplay.value = reserva.placa
                ? `${reserva.veiculo} (${reserva.placa})`
                : reserva.veiculo;
        }

        if (placaInput && reserva.placa && !placaInput.value) {
            placaInput.value = reserva.placa;
        }
    }

    function multaBuscarReserva(modo = 'auto') {
        const reservaId = document.getElementById('multa_reserva_id_input')?.value?.trim();
        const veiculoId  = document.getElementById('veiculo_id_input')?.value;
        const dataHora   = document.querySelector('[name=data_hora_infracao]')?.value;
        const infoEl     = document.getElementById('multa_reserva_info');
        const iconEl     = document.getElementById('multa_reserva_icon');
        const veiculoDisplay = document.getElementById('veiculo_display');
        const placaInput = document.querySelector('[name=placa_momento]');

        const hasVeiculoData = !!veiculoId && !!dataHora;
        const byReserva = !!reservaId && (modo === 'reservation' || (modo === 'auto' && hasVeiculoData));
        const byVeiculoData = hasVeiculoData && (modo === 'auto' || modo === 'vehicle' || modo === 'force' || !!reservaId);

        if (!byReserva && !byVeiculoData) {
            if (modo === 'force') {
                multaSetAutoStatus('Informe a reserva, ou selecione veículo e data/hora para localizar automaticamente.', 'warning');
            }
            return;
        }

        if (iconEl) iconEl.innerHTML = '<i class="fas fa-spinner fa-spin text-secondary"></i>';
        multaSetAutoStatus('Buscando vínculo da multa...', 'info');

        clearTimeout(multaReservaTimer);
        multaReservaTimer = setTimeout(() => {
            const params = new URLSearchParams();
            if (modo === 'force' || byVeiculoData) {
                params.set('veiculo_id', veiculoId);
                params.set('data_hora', dataHora);
            } else if (byReserva && hasVeiculoData) {
                params.set('reserva_id', reservaId);
                params.set('veiculo_id', veiculoId);
                params.set('data_hora', dataHora);
            } else if (byReserva) {
                params.set('reserva_id', reservaId);
            } else {
                params.set('veiculo_id', veiculoId);
                params.set('data_hora', dataHora);
            }

            fetch(`${URL_MULTA_BUSCAR_RESERVA}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (iconEl) iconEl.innerHTML = '<i class="fas fa-link text-muted"></i>';
                if (data.reserva) {
                    const r = data.reserva;
                    multaPreencherCamposReserva(r);
                    const cliente = r.cliente ? `Cliente: ${r.cliente}` : 'Reserva localizada';
                    if (infoEl) infoEl.innerHTML =
                        `<span class="text-success"><i class="fas fa-check-circle mr-1"></i>` +
                        `Reserva <strong>#${r.codigo || r.codigo_reserva || r.id}</strong> detectada automaticamente — ${cliente}.` +
                        `</span>`;
                    multaSetAutoStatus(`Reserva #${r.codigo || r.codigo_reserva || r.id} vinculada automaticamente.`, 'success');
                } else {
                    const reservaEl = document.getElementById('multa_reserva_id_input');
                    const veiculoHidden = document.getElementById('veiculo_id_input');
                    const veiculoDisplay = document.getElementById('veiculo_display');
                    const placaInput = document.querySelector('[name=placa_momento]');

                    if (modo === 'auto' || modo === 'vehicle') {
                        if (reservaEl) reservaEl.value = '';
                        if (veiculoHidden?.value && !data.reserva) {
                            veiculoHidden.value = '';
                        }
                        if (veiculoDisplay && !reservaId) {
                            veiculoDisplay.value = '';
                        }
                        if (placaInput && !reservaId) {
                            placaInput.value = '';
                        }
                        document.getElementById('btnLimparReserva')?.classList.add('d-none');
                    }
                    if (infoEl) infoEl.innerHTML =
                        `<span class="text-warning"><i class="fas fa-exclamation-circle mr-1"></i>` +
                        `Nenhuma reserva encontrada para esta combinação. Você pode preencher manualmente.</span>`;
                    multaSetAutoStatus('Nenhuma reserva encontrada para os dados informados.', 'warning');
                }
            })
            .catch(() => {
                if (iconEl) iconEl.innerHTML = '<i class="fas fa-link text-muted"></i>';
                multaSetAutoStatus('Não foi possível consultar a reserva agora.', 'danger');
            });
        }, 350);
    }

    document.getElementById('btnPreencherMultaAuto')?.addEventListener('click', function () {
        multaBuscarReserva('force');
    });

    document.getElementById('btnBuscarReserva')?.addEventListener('click', function () {
        multaBuscarReserva('reservation');
    });

    document.getElementById('btnLimparReserva')?.addEventListener('click', function () {
        const reservaEl = document.getElementById('multa_reserva_id_input');
        const veiculoHidden = document.getElementById('veiculo_id_input');
        const veiculoDisplay = document.getElementById('veiculo_display');
        const placaInput = document.querySelector('[name=placa_momento]');
        if (reservaEl) reservaEl.value = '';
        if (veiculoHidden) veiculoHidden.value = '';
        if (veiculoDisplay) veiculoDisplay.value = '';
        if (placaInput) placaInput.value = '';
        multaResetReservaInfo('');
        multaSetAutoStatus('Reserva limpa. Selecione veículo e data/hora ou informe um novo ID.', 'muted');
        document.getElementById('btnLimparReserva')?.classList.add('d-none');
    });

    (function () {
        const veiculoHidden  = document.getElementById('veiculo_id_input');
        const dataHoraInput  = document.querySelector('[name=data_hora_infracao]');
        const reservaInput = document.getElementById('multa_reserva_id_input');
        const btnLimparReserva = document.getElementById('btnLimparReserva');

        const triggerBuscaAuto = function () {
            clearTimeout(multaReservaTimer);
            multaReservaTimer = setTimeout(() => multaBuscarReserva('auto'), 250);
        };

        reservaInput?.addEventListener('input', function () {
            btnLimparReserva?.classList.toggle('d-none', !this.value.trim());
            triggerBuscaAuto();
        });
        reservaInput?.addEventListener('change', triggerBuscaAuto);

        dataHoraInput?.addEventListener('input', triggerBuscaAuto);
        dataHoraInput?.addEventListener('change', triggerBuscaAuto);

        veiculoHidden?.addEventListener('change', triggerBuscaAuto);

        if (veiculoHidden) {
            let _prevVal = veiculoHidden.value;
            setInterval(() => {
                if (veiculoHidden.value !== _prevVal) {
                    _prevVal = veiculoHidden.value;
                    multaResetReservaInfo('');
                    triggerBuscaAuto();
                }
            }, 400);
        }

        // Se já tem veículo na edição mas não tem reserva, tenta buscar
        if (veiculoHidden?.value && !reservaInput?.value) {
            triggerBuscaAuto();
        }

        btnLimparReserva?.classList.toggle('d-none', !reservaInput?.value?.trim());
    })();

    // Preenche valor_final automaticamente
    document.querySelectorAll('[name=valor_original],[name=valor_desconto]').forEach(el => {
        el.addEventListener('input', () => {
            const orig = parseFloat(document.querySelector('[name=valor_original]').value) || 0;
            const desc = parseFloat(document.querySelector('[name=valor_desconto]').value) || 0;
            document.getElementById('valor_final').value = Math.max(0, orig - desc).toFixed(2);
        });
    });

    // Wizard/Steps da multa
    (function initMultaWizard() {
        const maxStep = 3;
        let currentStep = 1;

        const prevBtn = document.getElementById('multaWizardPrev');
        const nextBtn = document.getElementById('multaWizardNext');
        const submitBtn = document.getElementById('btnSalvarMulta');

        function getStep() { return currentStep; }

        function setStep(step) {
            const s = Math.min(maxStep, Math.max(1, Number(step) || 1));
            currentStep = s;

            document.querySelectorAll('[data-multa-step]').forEach(el => {
                el.classList.toggle('d-none', Number(el.dataset.multaStep) !== s);
            });

            document.querySelectorAll('[data-multa-step-btn]').forEach(btn => {
                btn.classList.toggle('is-active', Number(btn.dataset.multaStepBtn) === s);
            });

            if (prevBtn) prevBtn.disabled = s <= 1;
            if (nextBtn) nextBtn.classList.toggle('d-none', s >= maxStep);
            if (submitBtn) submitBtn.classList.toggle('d-none', s < maxStep);
        }

        function validateStep(step) {
            const container = document.querySelector(`[data-multa-step="${step}"]`);
            if (!container) return true;

            const fields = Array.from(container.querySelectorAll('input, select, textarea')).filter(f => {
                return !f.disabled && f.type !== 'hidden' && f.willValidate;
            });

            const invalid = fields.find(f => !f.checkValidity());
            if (invalid) {
                invalid.reportValidity();
                return false;
            }
            return true;
        }

        prevBtn?.addEventListener('click', () => setStep(getStep() - 1));
        nextBtn?.addEventListener('click', () => {
            const current = getStep();
            if (!validateStep(current)) return;
            setStep(current + 1);
        });

        document.querySelectorAll('[data-multa-step-btn]').forEach(btn => {
            btn.addEventListener('click', () => {
                const target = Number(btn.dataset.multaStepBtn);
                const current = getStep();
                if (target > current && !validateStep(current)) return;
                setStep(target);
            });
        });

        const form = document.getElementById('formMulta');
        form?.addEventListener('submit', (event) => {
            const current = getStep();
            if (current >= maxStep) return;
            event.preventDefault();
            if (!validateStep(current)) return;
            setStep(current + 1);
        });

        window.multaWizardSetStep = (step) => setStep(step);
        setStep(1);
    })();

    // Abrir step com erro
    (function () {
        const errosCampos = @json(array_keys($errors->messages()));
        const mapaSteps = {
            'reserva_id': 1, 'veiculo_id': 1,
            'placa_momento': 1, 'numero_auto': 1,
            'orgao_autuador': 1, 'data_hora_infracao': 1,
            'data_vencimento': 1, 'local_infracao': 1,
            'descricao': 1,
            'valor_original': 2, 'valor_desconto': 2,
            'valor_final': 2, 'pontos': 2,
            'status_multa': 2, 'status_desconto': 2,
            'observacao': 2,
            'anexo_comprovante': 3, 'anexo_pdf': 3,
        };
        for (const campo of errosCampos) {
            const step = mapaSteps[campo];
            if (step) {
                window.multaWizardSetStep?.(step, true);
                break;
            }
        }
    })();

    // Modal busca de veiculo — migrado para o componente `x-veiculo.busca-avancada-modal`.
    // A lógica JavaScript foi movida para `public/js/veiculo/busca-modal.js` e o componente inicializa a funcionalidade.

    // Validação: `data_vencimento` não pode ser menor que `data_hora_infracao`
    (function () {
        const form = document.getElementById('formMulta');
        if (!form) return;
        const infra = form.querySelector('[name=data_hora_infracao]');
        const venc = form.querySelector('[name=data_vencimento]');
        if (!infra || !venc) return;

        function updateVencMin() {
            if (infra.value) {
                venc.min = infra.value.slice(0, 10);
            } else {
                venc.removeAttribute('min');
            }
        }

        infra.addEventListener('input', updateVencMin);
        updateVencMin();

        form.addEventListener('submit', function (e) {
            if (!infra.value || !venc.value) return;
            const infraDatePart = infra.value.slice(0, 10);
            if (venc.value < infraDatePart) {
                venc.setCustomValidity('Data de vencimento não pode ser anterior à data da infração.');
                venc.reportValidity();
                e.preventDefault();
                return false;
            }
            venc.setCustomValidity('');
        });
    }());
</script>
@endpush
