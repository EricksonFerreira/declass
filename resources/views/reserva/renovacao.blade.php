@extends('adminlte::page')

@section('title', $titulo)

@section('content_header')
    @if(isset($breadcrumb))
        <x-breadcrumb :breadcrumbs="$breadcrumb" />
    @else
        <h1 class="m-0 text-dark" style="font-size:1.1rem;">
            <a href="{{ route('reserva.index') }}" class="text-muted">Reservas</a>
            <span class="mx-1 text-muted">/</span>
            <a href="{{ route('reserva.show', $codigo) }}" class="text-muted">{{ $codigo }}</a>
            <span class="mx-1 text-muted">/</span>
            Renovação
        </h1>
    @endif
@stop


@section('content')

<div class="col-12 px-0">
    <div class="ci-card">

        {{-- ══════════════════════════════════
             CABEÇALHO
        ══════════════════════════════════ --}}
        <div class="ci-card-header">
            <div class="ci-header-left">
                <div class="ci-header-icon">
                    <i class="fas fa-redo-alt"></i>
                </div>
                <div>
                    <div class="ci-header-eyebrow">
                        @if($tipo === 'MAP') Motorista de App @else Reserva Comum @endif
                    </div>
                    <h4 class="ci-header-title">
                        @if($tipo === 'MAP')
                            Renovação do Ciclo de Plano
                        @else
                            Aditivo de Renovação
                        @endif
                    </h4>
                    <p class="ci-header-sub">
                        <i class="fas fa-hashtag mr-1"></i>{{ $codigo }}
                        @if($tipo === 'MAP' && isset($plano))
                            &nbsp;&middot;&nbsp;<i class="fas fa-layer-group mr-1"></i>{{ $plano->nome }}
                        @endif
                        @if($tipo === 'MAP' && isset($reserva->user))
                            &nbsp;&middot;&nbsp;<i class="fas fa-user mr-1"></i>{{ $reserva->user->nome ?? '' }}
                        @elseif($tipo !== 'MAP' && isset($reserva->usuario))
                            &nbsp;&middot;&nbsp;<i class="fas fa-user mr-1"></i>{{ $reserva->usuario->nome ?? '' }}
                        @endif
                    </p>
                </div>
            </div>
            <div class="ci-header-right">
                @php
                    $dataInicio = $tipo === 'MAP'
                        ? ($inicioVigenciaFormatado ?? null)
                        : (\Carbon\Carbon::parse($reserva->data_inicio ?? null)?->format('d/m/Y') ?? null);
                    $dataFim = $tipo === 'MAP'
                        ? ($fimVigenciaFormatado ?? null)
                        : (\Carbon\Carbon::parse($reserva->data_fim ?? null)?->format('d/m/Y') ?? null);
                @endphp
                @if($dataInicio)
                    <div class="ci-date-badge">
                        <i class="fas fa-calendar-alt"></i>
                        <span>{{ $dataInicio }}@if($dataFim) &rarr; {{ $dataFim }}@endif</span>
                    </div>
                @endif
                <a href="{{ route('reserva.show', $codigo) }}" class="ci-btn-back">
                    <i class="fas fa-arrow-left"></i>
                    <span>Voltar</span>
                </a>
            </div>
        </div>

        {{-- ══════════════════════════════════
             CORPO
        ══════════════════════════════════ --}}
        <div class="ci-body">

            @if(session('error'))
                <div class="alert alert-danger mx-3 mt-3">
                    <i class="fas fa-exclamation-triangle mr-2"></i>{{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success mx-3 mt-3">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            {{-- ════════════════════════════════════════════════
                 TIPO MAP — Motorista de App
            ════════════════════════════════════════════════ --}}
            @if($tipo === 'MAP')

                <form action="{{ route('reserva.minhas.motorista-app.renovar', ['id' => $reserva->id]) }}" method="POST" id="form_renovacao_map">
                    @csrf

                    {{-- Bloco 1: Vigência do ciclo atual --}}
                    <div class="ci-block ci-block--no-mb">
                        <div class="ci-block-label">
                            <i class="fas fa-calendar-check"></i>
                            <div>
                                <strong>Ciclo Atual</strong>
                                <span>Informações da vigência contratada em curso</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="rnv-info-card">
                                    <span class="rnv-info-label"><i class="fas fa-layer-group"></i> Plano</span>
                                    <strong class="rnv-info-value">{{ $plano?->nome ?? 'Plano Motorista App' }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="rnv-info-card">
                                    <span class="rnv-info-label"><i class="fas fa-calendar-alt"></i> Início do Ciclo</span>
                                    <strong class="rnv-info-value">{{ $inicioVigenciaFormatado }}</strong>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="rnv-info-card rnv-info-card--end">
                                    <span class="rnv-info-label"><i class="fas fa-flag-checkered"></i> Fim do Ciclo</span>
                                    <strong class="rnv-info-value">{{ $fimVigenciaFormatado }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bloco 2: Escolha de renovação (manter/trocar plano) --}}
                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-exchange-alt"></i>
                            <div>
                                <strong>Tipo de Renovação</strong>
                                <span>Escolha se deseja manter o plano atual ou trocar para outro</span>
                            </div>
                        </div>
                        <div class="rnv-radio-group">
                            <label class="rnv-radio-card" id="card_mantem">
                                <input type="radio" name="acao_renovacao" value="mantem" checked id="acaoMantem">
                                <span class="rnv-radio-card__icon rnv-radio-card__icon--blue">
                                    <i class="fas fa-sync-alt"></i>
                                </span>
                                <span class="rnv-radio-card__body">
                                    <strong>Manter plano atual</strong>
                                    <span>Renova com o mesmo plano e valor contratado</span>
                                </span>
                                <span class="rnv-radio-card__check"><i class="fas fa-check-circle"></i></span>
                            </label>
                            <label class="rnv-radio-card" id="card_trocar">
                                <input type="radio" name="acao_renovacao" value="trocar" id="acaoTrocar">
                                <span class="rnv-radio-card__icon rnv-radio-card__icon--amber">
                                    <i class="fas fa-random"></i>
                                </span>
                                <span class="rnv-radio-card__body">
                                    <strong>Trocar de plano</strong>
                                    <span>Selecione um novo plano e configure os opcionais</span>
                                </span>
                                <span class="rnv-radio-card__check"><i class="fas fa-check-circle"></i></span>
                            </label>
                        </div>
                    </div>

                    {{-- Bloco 3: Seleção de novo plano (só ao trocar) --}}
                    <div class="ci-block" id="bloco_novo_plano" style="display:none;">
                        <div class="ci-block-label">
                            <i class="fas fa-list-alt"></i>
                            <div>
                                <strong>Novo Plano</strong>
                                <span>Selecione o plano para o próximo ciclo</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="plano_id" class="form-label fw-bold">Plano</label>
                                <select name="plano_id" id="plano_id" class="form-control">
                                    @foreach($planos ?? [] as $p)
                                        <option value="{{ $p->id }}"
                                            data-valor="{{ $p->valor_periodo }}"
                                            data-periodo="{{ $p->periodo_label }}"
                                            {{ isset($plano) && $p->id == $plano->id ? 'selected' : '' }}>
                                            {{ $p->nome }} — R$ {{ number_format($p->valor_periodo, 2, ',', '.') }} / {{ $p->periodo_label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Opcionais do novo plano (AJAX) --}}
                        <div id="opcionaisContainer">
                            {{-- Carregado via AJAX ao trocar de plano --}}
                        </div>
                    </div>

                    {{-- Bloco 4: Cupom de desconto --}}
                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-tag"></i>
                            <div>
                                <strong>Cupom de Desconto</strong>
                                <span>Opcional — aplique um código de desconto para esta renovação</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <input type="text" name="cupom_codigo" id="cupom_codigo"
                                        class="form-control text-uppercase"
                                        placeholder="Ex: DESCONTO10"
                                        autocomplete="off">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-primary" id="btn_aplicar_cupom">
                                            <i class="fas fa-check mr-1"></i>Aplicar
                                        </button>
                                    </div>
                                </div>
                                <div id="cupom_feedback" class="mt-2" style="display:none;"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Bloco 5: Quantidade de ciclos --}}
                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-redo"></i>
                            <div>
                                <strong>Períodos de Renovação</strong>
                                <span>Quantos ciclos deseja adicionar ao contrato</span>
                            </div>
                        </div>
                        <div class="row align-items-end">
                            <div class="col-md-3 mb-3">
                                <label for="valorCiclo" class="form-label">Valor por Ciclo</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text bg-white">R$</span></div>
                                    <input type="text" class="form-control bg-white font-weight-bold" id="valorCiclo"
                                        value="{{ number_format($valorPeriodoContratado, 2, ',', '.') }}"
                                        readonly
                                        style="background:#f8fafc !important;">
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="quantidadeCiclos" class="form-label">Quantidade de Ciclos <span class="req">*</span></label>
                                <input type="number" name="periodos" id="quantidadeCiclos"
                                    class="form-control font-weight-bold"
                                    min="1" step="1" value="1" required>
                                <small class="ci-hint">Ciclos a adicionar além do ciclo atual</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="rnv-vigencia-badge" id="vigenciaBadge">
                                    <i class="fas fa-calendar-plus mr-1"></i>
                                    <span>Nova vigência: calculando…</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bloco 6: Observações --}}
                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-pen-alt"></i>
                            <div>
                                <strong>Observações</strong>
                                <span>Notas internas sobre esta renovação (opcional)</span>
                            </div>
                        </div>
                        <textarea name="observacoes" class="form-control form-control-sm" rows="3"
                            placeholder="Opcional — insira notas sobre a renovação se necessário"></textarea>
                    </div>

                    {{-- Resumo + Botão de confirmação --}}
                    <div class="ci-summary-card rnv-summary-final mt-2">
                        <div class="ci-summary-card__icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="ci-summary-card__content">
                            <strong>Resumo da Renovação</strong>
                            <div class="rnv-resumo-linha">
                                <span>Valor por ciclo:</span>
                                <strong id="resumoValorCiclo">R$ {{ number_format($valorPeriodoContratado, 2, ',', '.') }}</strong>
                            </div>
                            <div class="rnv-resumo-linha" id="linhaOpcionaisAtuais" style="{{ $opcionaisAtuaisValor > 0 ? '' : 'display:none;' }}">
                                <span>Opcionais atuais:</span>
                                <strong id="resumoOpcionaisAtuais">R$ {{ number_format($opcionaisAtuaisValor, 2, ',', '.') }}</strong>
                            </div>
                            <div class="rnv-resumo-linha" id="linhaOpcionaisNovos" style="display:none;">
                                <span>Opcionais novos:</span>
                                <strong id="resumoOpcionaisNovos">R$ 0,00</strong>
                            </div>
                            <div class="rnv-resumo-linha">
                                <span>Ciclos adicionais:</span>
                                <strong id="resumoCiclos">1x</strong>
                            </div>
                            <div class="rnv-resumo-linha" id="linhaDesconto" style="display:none;">
                                <span>Desconto (cupom):</span>
                                <strong class="text-danger" id="resumoDesconto">- R$ 0,00</strong>
                            </div>
                            <div class="rnv-resumo-linha rnv-resumo-total">
                                <span>Total a cobrar:</span>
                                <strong class="text-success" id="custoTotalMap">R$ {{ number_format($valorPeriodoContratado + $opcionaisAtuaisValor, 2, ',', '.') }}</strong>
                            </div>
                        </div>
                        <button type="submit" class="ci-btn-create co-btn-finalizar" id="btn_confirmar_renovacao">
                            <i class="fas fa-check-circle"></i>
                            Confirmar Renovação
                        </button>
                    </div>


                </form>

            {{-- ════════════════════════════════════════════════
                 TIPO RES — Reserva Comum
            ════════════════════════════════════════════════ --}}
            @else

                <form action="{{ route('reserva.renovarComum', ['id' => $reserva->id]) }}" method="POST" id="form_renovacao_comum">
                    @csrf

                    {{-- Bloco 1: Dados atuais da reserva --}}
                    <div class="ci-block ci-block--no-mb">
                        <div class="ci-block-label">
                            <i class="fas fa-car"></i>
                            <div>
                                <strong>Dados da Reserva Atual</strong>
                                <span>Veículo e datas da locação em curso</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="rnv-info-card">
                                    <span class="rnv-info-label"><i class="fas fa-car-side"></i> Veículo</span>
                                    <strong class="rnv-info-value">
                                        {{ $reserva->veiculo->marca->nome ?? '-' }}
                                        {{ $reserva->veiculo->modelo->nome ?? '-' }}
                                    </strong>
                                    @if($reserva->veiculo?->placa_licenca)
                                        <span class="badge badge-secondary mt-1">{{ $reserva->veiculo->placa_licenca }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="rnv-info-card">
                                    <span class="rnv-info-label"><i class="fas fa-calendar-alt"></i> Início da Reserva</span>
                                    <strong class="rnv-info-value">
                                        {{ \Carbon\Carbon::parse($reserva->data_inicio)->format('d/m/Y H:i') }}
                                    </strong>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="rnv-info-card rnv-info-card--end">
                                    <span class="rnv-info-label"><i class="fas fa-flag-checkered"></i> Devolução Atual</span>
                                    <strong class="rnv-info-value"
                                        id="dataFimAtual"
                                        data-raw="{{ $reserva->data_fim }}">
                                        {{ \Carbon\Carbon::parse($reserva->data_fim)->format('d/m/Y H:i') }}
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bloco 2: Nova data de devolução --}}
                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-calendar-plus"></i>
                            <div>
                                <strong>Nova Devolução</strong>
                                <span>Defina a nova data, horário e local de devolução do veículo</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="dataFimNova" class="form-label fw-bold">Nova Data <span class="req">*</span></label>
                                <input type="date" name="data_fim_nova" id="dataFimNova" class="form-control"
                                    required
                                    min="{{ \Carbon\Carbon::parse($reserva->data_fim)->format('Y-m-d') }}"
                                    value="{{ \Carbon\Carbon::parse($reserva->data_fim)->format('Y-m-d') }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="horaFimNova" class="form-label fw-bold">Novo Horário <span class="req">*</span></label>
                                <input type="time" name="hora_fim_nova" id="horaFimNova" class="form-control"
                                    required
                                    value="{{ \Carbon\Carbon::parse($reserva->data_fim)->format('H:i') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="estacao_devolucao_id" class="form-label fw-bold">Local de Devolução <span class="req">*</span></label>
                                <select name="estacao_devolucao_id" id="estacao_devolucao_id" class="form-control" required>
                                    @foreach($estacoes as $estacao)
                                        <option value="{{ $estacao->id }}"
                                            {{ (int) $estacao->id === (int) $reserva->estacao_devolucao_id ? 'selected' : '' }}>
                                            {{ $estacao->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="alert alert-warning d-none mb-0 mt-1" id="alertaDataInvalida">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            A nova data e hora deve ser superior à devolução atual.
                        </div>
                    </div>

                    {{-- Bloco 3: Observações --}}
                    <div class="ci-block">
                        <div class="ci-block-label">
                            <i class="fas fa-pen-alt"></i>
                            <div>
                                <strong>Observações</strong>
                                <span>Notas sobre o aditivo de contrato (opcional)</span>
                            </div>
                        </div>
                        <textarea name="observacoes" class="form-control form-control-sm" rows="3"
                            placeholder="Ex: Viagem prolongada, cliente solicitou extensão…"></textarea>
                    </div>

                    {{-- Resumo + Botão de confirmação --}}
                    <div class="ci-summary-card rnv-summary-final mt-2" id="cardResumoComum" style="display:none;">
                        <div class="ci-summary-card__icon ci-summary-card__icon--green">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="ci-summary-card__content">
                            <strong>Simulação do Aditivo</strong>
                            <div class="rnv-resumo-linha">
                                <span>Valor da diária:</span>
                                <strong id="diariaValor" data-raw="{{ $reserva->tarifa_base }}">
                                    R$ {{ number_format($reserva->tarifa_base, 2, ',', '.') }}
                                </strong>
                            </div>
                            <div class="rnv-resumo-linha">
                                <span>Diárias adicionais:</span>
                                <strong class="text-info" id="diariasAdicionais">0 dia(s)</strong>
                            </div>
                            <div class="rnv-resumo-linha rnv-resumo-total">
                                <span>Custo Total Adicional:</span>
                                <strong class="text-success" id="custoTotalComum">R$ 0,00</strong>
                            </div>
                        </div>
                        <button type="submit" class="ci-btn-create co-btn-finalizar" id="btnSubmitComum">
                            <i class="fas fa-check-circle"></i>
                            Confirmar Aditivo
                        </button>
                    </div>

                    {{-- Botão visível quando resumo não carregado --}}
                    <div class="ci-summary-card rnv-summary-final mt-2" id="cardSubmitComumSimples">
                        <div class="ci-summary-card__icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="ci-summary-card__content">
                            <strong>Confirmar prorrogação da reserva</strong>
                            <p>Selecione a nova data e horário de devolução para calcular o custo adicional.</p>
                        </div>
                        <button type="submit" class="ci-btn-create co-btn-finalizar" id="btnSubmitComumSimples">
                            <i class="fas fa-check-circle"></i>
                            Confirmar Aditivo
                        </button>
                    </div>

                </form>

            @endif

        </div>{{-- /ci-body --}}
    </div>{{-- /ci-card --}}
</div>

@stop

@section('css')
<style>
    @include('reserva.partials.vistoria_reserva_estilos_inner')

    /* Compactações globais para evitar scroll */
    .ci-body { padding: 0.75rem !important; }
    .ci-block { padding: 0.65rem 0.95rem !important; margin-bottom: 0.5rem !important; border-radius: 8px !important; }
    .ci-block-label { margin-bottom: 0.5rem !important; padding-bottom: 0.4rem !important; }
    .ci-block-label > i { width: 28px !important; height: 28px !important; font-size: 0.75rem !important; border-radius: 8px !important; }
    .ci-card-header { padding: 0.85rem 1.25rem !important; }
    .ci-header-icon { width: 42px !important; height: 42px !important; font-size: 1.1rem !important; border-radius: 10px !important; }
    .row [class^="col-"] { margin-bottom: 0px !important; }
    .form-group, .mb-3, .my-4 { margin-bottom: 0.5rem !important; margin-top: 0px !important; }
    .pb-3, .py-3 { padding-bottom: 0.4rem !important; }
    .pt-3, .py-3 { padding-top: 0.4rem !important; }

    /* ── Info cards (bloco de dados) ── */
    .rnv-info-card {
        display: flex;
        flex-direction: column;
        gap: 2px;
        padding: 8px 10px;
        background: #f8faff;
        border: 1px solid #e0e7ff;
        border-radius: 8px;
        height: auto;
    }
    .rnv-info-card--end { border-left: 3px solid var(--c-green, #10b981); }
    .rnv-info-label {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--c-slate, #64748b);
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .rnv-info-label i { font-size: 0.6rem; opacity: 0.75; }
    .rnv-info-value {
        font-size: 0.88rem;
        font-weight: 800;
        color: var(--c-ink, #0f172a);
        line-height: 1.2;
    }

    /* ── Radio cards (manter/trocar plano) ── */
    .rnv-radio-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .rnv-radio-card {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 200px;
        padding: 8px 12px;
        background: #fff;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
        position: relative;
        user-select: none;
    }
    .rnv-radio-card input[type="radio"] { display: none; }
    .rnv-radio-card:has(input:checked) {
        border-color: var(--c-indigo, #4f46e5);
        background: #eef2ff;
        box-shadow: 0 0 0 3px rgba(79,70,229,0.10);
    }
    .rnv-radio-card__icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.85rem; flex-shrink: 0;
        background: #eff6ff; color: #2563eb;
    }
    .rnv-radio-card__icon--blue  { background: #eff6ff; color: #2563eb; }
    .rnv-radio-card__icon--amber { background: #fffbeb; color: #d97706; }
    .rnv-radio-card__body {
        flex: 1;
        display: flex; flex-direction: column; gap: 1px;
    }
    .rnv-radio-card__body strong {
        font-size: 0.82rem; font-weight: 800; color: var(--c-ink, #0f172a);
    }
    .rnv-radio-card__body span {
        font-size: 0.7rem; color: var(--c-slate, #64748b);
    }
    .rnv-radio-card__check {
        font-size: 0.95rem;
        color: #d1d5db;
        transition: color 0.2s;
    }
    .rnv-radio-card:has(input:checked) .rnv-radio-card__check {
        color: var(--c-indigo, #4f46e5);
    }

    /* ── Vigência badge ── */
    .rnv-vigencia-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        color: #166534;
        font-size: 0.78rem;
        font-weight: 600;
        width: 100%;
        margin-top: 0px;
    }
    .rnv-vigencia-badge i { color: #16a34a; }

    /* ── Resumo final (summary card adaptado) ── */
    .rnv-summary-final {
        margin: 0.25rem 0 0 0 !important;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px !important;
        background: #f8faff;
        border: 1.5px solid #e0e7ff;
        border-radius: 12px;
    }
    .rnv-summary-final .ci-summary-card__icon {
        width: 38px; height: 38px;
        border-radius: 8px;
        background: color-mix(in srgb, var(--c-indigo, #4f46e5) 12%, #fff 88%);
        color: var(--c-indigo, #4f46e5);
        display: flex; align-items: center; justify-content: center;
        font-size: 0.95rem; flex-shrink: 0;
    }
    .rnv-summary-final .ci-summary-card__icon--green {
        background: #ecfdf5; color: #10b981;
    }
    .rnv-summary-final .ci-summary-card__content {
        flex: 1;
        display: flex; flex-direction: column; gap: 2px;
    }
    .rnv-summary-final .ci-summary-card__content strong {
        font-size: 0.85rem; font-weight: 800; color: var(--c-ink, #0f172a);
    }
    .rnv-resumo-linha {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.78rem;
        color: var(--c-slate, #64748b);
    }
    .rnv-resumo-linha strong { color: var(--c-ink, #0f172a); }
    .rnv-resumo-total {
        border-top: 1px solid #e0e7ff;
        margin-top: 2px;
        padding-top: 2px;
        font-size: 0.82rem;
    }
    .rnv-resumo-total strong { font-size: 0.98rem; }

    /* ── Opcionais AJAX ── */
    .rnv-opcional-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        margin-bottom: 4px;
        transition: background 0.15s;
    }
    .rnv-opcional-item:hover { background: #f8faff; }
    .rnv-opcional-item label {
        flex: 1; cursor: pointer;
        display: flex; justify-content: space-between; align-items: center;
        margin: 0;
    }
    .rnv-opcional-badge {
        font-size: 0.72rem; font-weight: 700;
        padding: 1px 6px;
        background: #eef2ff;
        color: var(--c-indigo, #4f46e5);
        border-radius: 12px;
    }

    /* ── Cupom feedback ── */
    #cupom_feedback .cupom-ok {
        display: flex; align-items: center; gap: 4px;
        color: #166534; font-size: 0.76rem; font-weight: 600;
        background: #f0fdf4; border: 1px solid #bbf7d0;
        border-radius: 6px; padding: 4px 8px;
    }
    #cupom_feedback .cupom-erro {
        display: flex; align-items: center; gap: 4px;
        color: #991b1b; font-size: 0.76rem; font-weight: 600;
        background: #fef2f2; border: 1px solid #fecaca;
        border-radius: 6px; padding: 4px 8px;
    }

    /* ── Campos obrigatórios ── */
    .req { color: #ef4444; font-weight: 700; }
</style>
@stop


@section('js')
<script>
$(document).ready(function () {
    const tipo = @json($tipo);

    // ════════════════════════════════════════════════
    // MAP — Motorista de App
    // ════════════════════════════════════════════════
    if (tipo === 'MAP') {

        let valorCicloBase = parseFloat(@json($valorPeriodoContratado)) || 0;
        let descontoCupom  = 0;
        let cupomValido    = false;

        const $quantidade     = $('#quantidadeCiclos');
        const $valorCicloInpt = $('#valorCiclo');
        const $resumoCiclos   = $('#resumoCiclos');
        const $resumoValor    = $('#resumoValorCiclo');
        const $total          = $('#custoTotalMap');
        const $linhaDesconto  = $('#linhaDesconto');
        const $resumoDesconto = $('#resumoDesconto');
        const $vigBadge       = $('#vigenciaBadge');

        let opcionaisAtuaisValor = parseFloat(@json($opcionaisAtuaisValor ?? 0)) || 0;

        // ── Calcular totais ────────────────────────
        function calcular() {
            const qtd  = parseInt($quantidade.val()) || 1;
            const ciclo = parseFloat($valorCicloInpt.val().replace('.', '').replace(',', '.')) || valorCicloBase;

            $resumoCiclos.text(qtd + 'x');
            $resumoValor.text('R$ ' + fmt(ciclo));

            let opcionaisNovos = 0;
            if ($('input[name="acao_renovacao"]:checked').val() === 'trocar') {
                // Se mudou de plano, somar opcionais novos marcados
                $('.opc-check:checked').each(function() {
                    const labelText = $(this).siblings('label').find('.rnv-opcional-badge').text();
                    const match = labelText.match(/R\$\s*([0-9.,]+)/);
                    if (match) {
                        opcionaisNovos += parseFloat(match[1].replace('.', '').replace(',', '.')) || 0;
                    }
                });
                $('#linhaOpcionaisAtuais').hide();
                if (opcionaisNovos > 0) {
                    $('#linhaOpcionaisNovos').show();
                    $('#resumoOpcionaisNovos').text('R$ ' + fmt(opcionaisNovos));
                } else {
                    $('#linhaOpcionaisNovos').hide();
                }
            } else {
                // Se mantém plano atual, manter os opcionais atuais ativos
                opcionaisNovos = 0;
                $('#linhaOpcionaisNovos').hide();
                if (opcionaisAtuaisValor > 0) {
                    $('#linhaOpcionaisAtuais').show();
                } else {
                    $('#linhaOpcionaisAtuais').hide();
                }
            }

            const custoOpcionais = ($('input[name="acao_renovacao"]:checked').val() === 'trocar') ? opcionaisNovos : opcionaisAtuaisValor;

            const bruto = (ciclo * qtd) + (custoOpcionais * qtd);
            const total = Math.max(0, bruto - descontoCupom);

            if (descontoCupom > 0) {
                $linhaDesconto.show();
                $resumoDesconto.text('- R$ ' + fmt(descontoCupom));
            } else {
                $linhaDesconto.hide();
            }

            $total.text('R$ ' + fmt(total));
            atualizarVigencia(qtd);
        }


        // ── Atualizar badge de vigência ────────────
        const fimCicloAtualStr = @json($fimVigenciaFormatado ?? '');
        const rawPeriodoContrato = @json($reserva->periodo_contrato ?: $plano->periodo ?: 'semana');

        function obterPeriodoAtivo() {
            if ($('input[name="acao_renovacao"]:checked').val() === 'trocar') {
                const opt = $('#plano_id option:selected');
                const label = opt.text().toLowerCase();
                if (label.includes('mês') || label.includes('mes')) return 'mes';
                if (label.includes('quinze')) return 'quinze';
                if (label.includes('trimestre')) return 'trimestre';
                if (label.includes('semestre')) return 'semestre';
                if (label.includes('ano')) return 'ano';
                return 'semana';
            }
            return rawPeriodoContrato;
        }

        function somarPeriodos(dataInicio, tipoPeriodo, qtd) {
            let d = new Date(dataInicio);
            const parts = fimCicloAtualStr.split('/');
            if (parts.length === 3) {
                d = new Date(parseInt(parts[2]), parseInt(parts[1]) - 1, parseInt(parts[0]));
            }
            
            d.setDate(d.getDate() + 1);
            
            const dataInicioFormatada = d.toLocaleDateString('pt-BR');

            if (tipoPeriodo === 'quinze') {
                d.setDate(d.getDate() + (15 * qtd) - 1);
            } else if (tipoPeriodo === 'mes') {
                d.setMonth(d.getMonth() + qtd);
                d.setDate(d.getDate() - 1);
            } else if (tipoPeriodo === 'trimestre') {
                d.setMonth(d.getMonth() + (3 * qtd));
                d.setDate(d.getDate() - 1);
            } else if (tipoPeriodo === 'semestre') {
                d.setMonth(d.getMonth() + (6 * qtd));
                d.setDate(d.getDate() - 1);
            } else if (tipoPeriodo === 'ano') {
                d.setFullYear(d.getFullYear() + qtd);
                d.setDate(d.getDate() - 1);
            } else { 
                d.setDate(d.getDate() + (7 * qtd) - 1);
            }

            const dataFimFormatada = d.toLocaleDateString('pt-BR');
            return { inicio: dataInicioFormatada, fim: dataFimFormatada };
        }

        function atualizarVigencia(qtd) {
            const periodo = obterPeriodoAtivo();
            const vigencia = somarPeriodos(fimCicloAtualStr, periodo, qtd);
            $vigBadge.html('<i class="fas fa-calendar-plus mr-1"></i><span>Nova vigência: <strong>' + vigencia.inicio + '</strong> até <strong>' + vigencia.fim + '</strong></span>');
        }

        // ── Troca de plano ─────────────────────────
        $('input[name="acao_renovacao"]').on('change', function () {
            const trocar = $(this).val() === 'trocar';
            $('#bloco_novo_plano').toggle(trocar);

            if (!trocar) {
                // Restaurar valor do plano original
                valorCicloBase = parseFloat(@json($valorPeriodoContratado)) || 0;
                $valorCicloInpt.val(fmt(valorCicloBase));
                $('#opcionaisContainer').html('');
                calcular();
            } else {
                // Carregar opcionais do plano selecionado
                carregarOpcionais($('#plano_id').val());
            }
        });

        // ── Mudança de plano (select) ──────────────
        $('#plano_id').on('change', function () {
            const opt = $(this).find('option:selected');
            const novoValor = parseFloat(opt.data('valor')) || 0;
            valorCicloBase = novoValor;
            $valorCicloInpt.val(fmt(novoValor));
            carregarOpcionais($(this).val());
            calcular();
        });

        // ── Carregar opcionais via AJAX ────────────
        function carregarOpcionais(planoId) {
            if (!planoId) return;

            const url = @json(route('reserva.planos.opcionais', ['id' => '__ID__'])).replace('__ID__', planoId);

            $('#opcionaisContainer').html('<div class="text-muted p-2"><i class="fas fa-spinner fa-spin mr-1"></i>Carregando opcionais…</div>');

            $.get(url)
                .done(function (data) {
                    if (!data || !data.length) {
                        $('#opcionaisContainer').html('<p class="text-muted small mt-2">Nenhum opcional disponível para este plano.</p>');
                        return;
                    }

                    let html = '<div class="ci-block-label mt-3"><i class="fas fa-plus-circle"></i><div><strong>Opcionais do Plano</strong><span>Selecione os opcionais que deseja incluir</span></div></div>';
                    data.forEach(function (opc) {
                        html += `
                            <div class="rnv-opcional-item">
                                <input type="checkbox" name="opcionais[]" id="opc_${opc.id}" value="${opc.id}" class="opc-check">
                                <label for="opc_${opc.id}">
                                    <span>${opc.nome}</span>
                                    <span class="rnv-opcional-badge">+ R$ ${fmt(parseFloat(opc.valor || 0))}/ciclo</span>
                                </label>
                            </div>`;
                    });

                    $('#opcionaisContainer').html(html);
                    $('.opc-check').on('change', calcular);
                })
                .fail(function () {
                    $('#opcionaisContainer').html('<p class="text-danger small mt-2"><i class="fas fa-exclamation-triangle mr-1"></i>Erro ao carregar opcionais.</p>');
                });
        }

        // ── Aplicar cupom ──────────────────────────
        $('#btn_aplicar_cupom').on('click', function () {
            const codigo = $('#cupom_codigo').val().trim();
            if (!codigo) return;

            const $feedback = $('#cupom_feedback').show();
            $feedback.html('<span class="text-muted small"><i class="fas fa-spinner fa-spin mr-1"></i>Validando…</span>');

            // Simulação local (sem endpoint dedicado no momento)
            // Em produção, substitua por $.post() para um endpoint de validação
            setTimeout(function () {
                $feedback.html('<div class="cupom-ok"><i class="fas fa-check-circle"></i> Cupom aplicado! O desconto será processado ao confirmar.</div>');
                cupomValido = true;
                calcular();
            }, 800);
        });

        // ── Quantidade mudou ───────────────────────
        $quantidade.on('input change', function () {
            if (parseInt($(this).val()) < 1) $(this).val(1);
            calcular();
        });

        // ── Init ───────────────────────────────────
        calcular();

    }

    // ════════════════════════════════════════════════
    // RES — Reserva Comum
    // ════════════════════════════════════════════════
    else {

        const diaria         = parseFloat($('#diariaValor').data('raw')) || 0;
        const dataFimAtualStr = $('#dataFimAtual').data('raw');
        const dataFimAtual    = new Date(dataFimAtualStr);

        const $dataNova          = $('#dataFimNova');
        const $horaNova          = $('#horaFimNova');
        const $diariasAdicionais = $('#diariasAdicionais');
        const $custoTotalComum   = $('#custoTotalComum');
        const $cardResumo        = $('#cardResumoComum');
        const $cardSimples       = $('#cardSubmitComumSimples');
        const $alerta            = $('#alertaDataInvalida');
        const $btnSubmit         = $('#btnSubmitComum');

        function calcularComum() {
            const dataStr = $dataNova.val();
            const horaStr = $horaNova.val();

            if (!dataStr || !horaStr) {
                $cardResumo.hide();
                $cardSimples.show();
                return;
            }

            const dataNovaObj = new Date(dataStr + 'T' + horaStr + ':00');

            if (isNaN(dataNovaObj.getTime()) || dataNovaObj <= dataFimAtual) {
                $cardResumo.hide();
                $cardSimples.hide();
                $alerta.removeClass('d-none');
                $btnSubmit.prop('disabled', true);
                return;
            }

            $alerta.addClass('d-none');
            $btnSubmit.prop('disabled', false);

            const diffMs   = dataNovaObj - dataFimAtual;
            const diffDays = Math.ceil(diffMs / (1000 * 60 * 60 * 24));

            $diariasAdicionais.text('+' + diffDays + ' diária(s)');
            const total = diaria * diffDays;
            $custoTotalComum.text('R$ ' + fmt(total));

            $cardResumo.show();
            $cardSimples.hide();
        }

        $dataNova.on('input change', calcularComum);
        $horaNova.on('input change', calcularComum);
        calcularComum();
    }

    // ── Utilitário de formatação ───────────────────
    function fmt(val) {
        return parseFloat(val || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

});
</script>
@stop
