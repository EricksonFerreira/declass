@extends('adminlte::page')

@section('title', $titulo ?? 'Minha reserva de motorista de app')

@section('content_header')
    <x-breadcrumb :breadcrumbs="$breadcrumb" />
@stop

@section('content')
@php
    $solicitacao = $registro->solicitacao;
    $plano = $registro->planoMotoristaApp ?? $solicitacao?->planoMotoristaApp;
    $usuario = $registro->user;
    $codigoReserva = $registro->codigoReserva?->codigo ?? ('#' . $registro->id);

    $statusRaw = strtolower(trim((string) ($registro->status ?? '')));
    $statusLabel = match ($statusRaw) {
        'pendente_analise', 'pendente_pagamento', 'pendente' => 'Criada',
        'confirmada' => 'Reserva confirmada - aguardando retirada',
        'ativa' => 'Ativa',
        'cancelada' => 'Cancelada',
        default => $statusRaw ? ucfirst(str_replace('_', ' ', $statusRaw)) : 'Criada',
    };
    $statusClass = match ($statusRaw) {
        'ativa' => 'success',
        'confirmada' => 'info',
        'cancelada' => 'danger',
        'pendente_analise', 'pendente_pagamento', 'pendente' => 'warning',
        default => 'secondary',
    };
    $statusIcon = match ($statusRaw) {
        'ativa' => 'fas fa-check-circle',
        'confirmada' => 'fas fa-calendar-check',
        'cancelada' => 'fas fa-times-circle',
        'pendente_analise', 'pendente_pagamento', 'pendente' => 'fas fa-clock',
        default => 'fas fa-info-circle',
    };

    $pagamentoEfetuado = (bool) $registro->pagamento_efetuado;
    $temPagamentoPendente = collect($registro->pagamentos ?? [])->contains(function ($pagamento): bool {
        return strtolower((string) ($pagamento->status ?? '')) === 'pendente';
    });
    $statusPagamento = $pagamentoEfetuado ? 'Pago' : 'Pagamento pendente';
    $pagamentoClass = $pagamentoEfetuado ? 'success' : 'warning';
    $pagamentoIcon = $pagamentoEfetuado ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';

    $dataInicio = $registro->data_inicio ? \Carbon\Carbon::parse($registro->data_inicio)->format('d/m/Y H:i') : '-';
    $dataFim = $registro->data_fim ? \Carbon\Carbon::parse($registro->data_fim)->format('d/m/Y H:i') : '-';
    $criadaEm = $registro->created_at ? \Carbon\Carbon::parse($registro->created_at)->format('d/m/Y H:i') : '-';
    $diasRestantes = $registro->data_fim ? now()->diffInDays(\Carbon\Carbon::parse($registro->data_fim), false) : null;

    $valorPeriodoContratado = (float) (
        $registro->valor_periodo_contrato
        ?: ($registro->valor_plano
        ?: ($plano?->valor_periodo ?? 0))
    );
    $valorPlano = $valorPeriodoContratado;
    $valorOpcionais = (float) ($registro->valor_opcionais ?? 0);
    $valorTotal = round($valorPeriodoContratado + $valorOpcionais, 2);
    $calcao = (float) ($registro->valor_caucao ?? ($plano?->calcao ?? 0));
    $periodoLabel = $plano?->periodo_label ?? '-';
    $kmResumo = $plano?->km_resumo ?? '-';
    $periodoUnidade = match ((string) ($plano?->periodo ?? '')) {
        'quinze' => 'quinze dias',
        'mes' => 'mês',
        'trimestre' => 'trimestre',
        'semestre' => 'semestre',
        'ano' => 'ano',
        default => 'semana',
    };
    $periodoUnidadePlural = match ((string) ($plano?->periodo ?? '')) {
        'quinze' => 'quinze dias',
        'mes' => 'meses',
        'trimestre' => 'trimestres',
        'semestre' => 'semestres',
        'ano' => 'anos',
        default => 'semanas',
    };
    $valorPeriodoAtual = (float) ($plano?->valor_periodo ?? 0);
    $opcionaisSelecionados = is_array($registro->opcionais_json ?? null) ? $registro->opcionais_json : [];
    $opcionaisMap = $plano?->opcionais?->keyBy('id') ?? collect();
    $dadosJson = is_array($registro->dados_json ?? null) ? $registro->dados_json : [];
    $documentosJson = is_array($registro->documentos_json ?? null) ? $registro->documentos_json : [];
    $solicitacaoStatus = $solicitacao?->status ? ucfirst(str_replace('_', ' ', (string) $solicitacao->status)) : 'Nao informada';
    $pagamentos = collect($registro->pagamentos ?? [])->sortByDesc('id')->values();
    $totalPagoCalculado = (float) $pagamentos->filter(fn($p) => strtolower((string)($p->status ?? '')) === 'pago')->sum('valor_total');
    $saldoTotalPendenteCalculado = max(0, round($valorTotal - $totalPagoCalculado, 2));

    $pagamentosPendentes = $pagamentos->filter(function ($pagamento): bool {
        return strtolower((string) ($pagamento->status ?? '')) === 'pendente';
    })->values();
    $pagamentoPendenteAtual = $pagamentosPendentes->first();
    $temPagamentoPendente = $pagamentosPendentes->isNotEmpty() && $saldoTotalPendenteCalculado > 0;
    $qtdPagamentosPendentes = $temPagamentoPendente ? $pagamentosPendentes->count() : 0;
    $pagamentoPendenteId = $pagamentoPendenteAtual?->id;
    $multas = collect($registro->multas ?? [])->sortByDesc('id')->values();
    $sinistros = collect($registro->sinistros ?? [])->sortByDesc('id')->values();
    $renovacaoFechada = $plano?->periodo ? true : false;
    $empresaRef = $empresa ?? $registro->empresa ?? $solicitacao?->empresa;
    $veiculosDisponiveis = collect($veiculosDisponiveis ?? []);
    $veiculoAtual = $registro->veiculo;
    $estaEfetivada = !empty($registro->contrato_efetivado_em);
    $estaCheckin = !empty($registro->checkin_realizado_em);
    $estaCheckout = !empty($registro->checkout_realizado_em);
    $contratoSnapshot = is_array($registro->contrato_snapshot_json ?? null) ? $registro->contrato_snapshot_json : [];
    $contratoAssinado = !empty(data_get($contratoSnapshot, 'assinatura'));
    $temContratoPdf = !empty($registro->contrato_pdf_path);
    $termoConfirmacaoAceitoEm = data_get($documentosJson, 'termo_confirmacao.aceito_em');
    $termoConfirmacaoAceito = !empty($termoConfirmacaoAceitoEm);
    $podeAceitarTermo = $pagamentoEfetuado && !$termoConfirmacaoAceito && !$estaCheckin && $statusRaw !== 'cancelada';
    $podeRegistrarCheckin = $pagamentoEfetuado && $termoConfirmacaoAceito && !$estaCheckin && $statusRaw !== 'cancelada';
    $podeAssinarContrato = $estaCheckin && $temContratoPdf && !$contratoAssinado;
    $podeRenovar = $contratoAssinado && $statusRaw === 'ativa' && !$estaCheckout;
    $limiteCancelamento = $registro->created_at ? \Carbon\Carbon::parse($registro->created_at)->addHours(24) : null;
    $podeCancelar = !$estaEfetivada
        && strtolower((string) ($registro->status ?? '')) !== 'cancelada'
        && $limiteCancelamento
        && now()->lessThanOrEqualTo($limiteCancelamento);
    $inicioVigencia = $registro->data_retirada_real ?? $registro->data_inicio;
    $fimVigencia = $registro->data_fim;
    $inicioVigenciaFormatado = $inicioVigencia ? \Carbon\Carbon::parse($inicioVigencia)->format('d/m/Y H:i') : '-';
    $fimVigenciaFormatado = $fimVigencia ? \Carbon\Carbon::parse($fimVigencia)->format('d/m/Y H:i') : '-';
    $valorPeriodoContratado = (float) ($registro->valor_periodo_contrato ?: $valorPeriodoAtual);
    $periodoContrato = (string) ($registro->periodo_contrato ?: ($plano?->periodo ?? 'semana'));
    $labelPeriodoContrato = $periodoContrato;
    $periodoLabelContrato = match ($periodoContrato) {
        'quinze' => 'Quinze dias',
        'mes' => 'Mês',
        'trimestre' => 'Trimestre',
        'semestre' => 'Semestre',
        'ano' => 'Ano',
        default => 'Semana',
    };
    $opcionaisDetalhados = collect($opcionaisSelecionados)
        ->map(function ($item) use ($opcionaisMap) {
            $opcionalId = (int) ($item['id'] ?? 0);
            $opcional = $opcionaisMap->get($opcionalId);
            $quantidade = max(1, (int) ($item['quantidade'] ?? 1));
            $subtotal = (float) (
                $item['preco_total']
                ?? $opcional?->pivot?->preco_total
                ?? ($opcional?->preco_integral ?? $opcional?->preco_por_dia ?? 0)
            );
            $valorUnitario = $quantidade > 0 ? round($subtotal / $quantidade, 2) : $subtotal;

            return [
                'id' => $opcionalId,
                'nome' => $opcional?->nome ?? ('Opcional #' . $opcionalId),
                'quantidade' => $quantidade,
                'valor_unitario' => $valorUnitario,
                'subtotal' => $subtotal,
                'tipo_cobranca' => (string) ($opcional?->tipo_cobranca ?? '-'),
                'descricao' => (string) ($opcional?->descricao ?? ''),
            ];
        })
        ->filter(fn ($item) => !empty($item['id']))
        ->values();
    $existeDetalheOpcionais = $opcionaisDetalhados->isNotEmpty();
    $camposPersonalizados = collect($empresaRef?->aluguel_app_campos_personalizados ?? [])
        ->filter(fn ($campo) => is_array($campo) && !empty($campo['selected']))
        ->keyBy(function ($campo) {
            return (string) ($campo['key'] ?? '');
        });
    $rotulosPadraoDados = [
        'plano_id' => 'Plano',
        'estacao_id' => 'Estação de retirada',
        'data_inicio' => 'Data de início',
        'possui_ear' => 'Possui EAR',
        'aplicativos' => 'Aplicativos',
    ];
    $formatarValor = function ($valor): string {
        if (is_array($valor)) {
            $lista = array_values(array_filter(array_map(function ($item) {
                if (is_array($item)) {
                    return trim((string) ($item['label'] ?? $item['value'] ?? $item['nome'] ?? ''));
                }
                return trim((string) $item);
            }, $valor)));

            return !empty($lista) ? implode(', ', $lista) : '—';
        }

        if (is_bool($valor)) {
            return $valor ? 'Sim' : 'Não';
        }

        $texto = trim((string) $valor);
        if ($texto === '') {
            return '—';
        }

        if (in_array(strtolower($texto), ['1', 'sim', 's', 'true', 'yes'], true)) {
            return 'Sim';
        }
        if (in_array(strtolower($texto), ['0', 'nao', 'não', 'n', 'false', 'no'], true)) {
            return 'Não';
        }

        return $texto;
    };
    $formatarDataValor = function ($valor): string {
        try {
            return $valor ? \Carbon\Carbon::parse($valor)->format('d/m/Y') : '—';
        } catch (\Throwable $e) {
            return trim((string) $valor) !== '' ? (string) $valor : '—';
        }
    };
@endphp

<style>
    :root {
        --ma-ink: #0f172a;
        --ma-muted: #64748b;
        --ma-line: #dbe4ee;
        --ma-hero: linear-gradient(135deg, #0f172a 0%, #173d63 55%, #2563eb 100%);
        --ma-accent: #2563eb;
    }
    .ma-page { padding-bottom: 18px; }
    .ma-hero {
        border-radius: 22px;
        overflow: hidden;
        background: var(--ma-hero);
        color: #fff;
        box-shadow: 0 18px 42px rgba(15, 23, 42, .16);
        margin-bottom: 16px;
    }
    .ma-hero__inner {
        padding: 24px 26px;
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
    }
    .ma-hero__title { margin: 0 0 8px; font-size: 1.65rem; font-weight: 800; }
    .ma-hero__text { margin: 0; color: rgba(255,255,255,.82); max-width: 56rem; }
    .ma-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
    .ma-chip {
        display: inline-flex; align-items: center; gap: 7px;
        border-radius: 999px; padding: 7px 12px;
        background: rgba(255,255,255,.10); border: 1px solid rgba(255,255,255,.16);
        font-size: .8rem; font-weight: 800;
    }
    .ma-chip--warn { background: rgba(245,158,11,.15); color: #fff; border-color: rgba(245,158,11,.30); }
    .ma-status {
        display: inline-flex; align-items: center; gap: 8px;
        border-radius: 999px; padding: 8px 13px; font-size: .76rem; font-weight: 900;
        letter-spacing: .05em; text-transform: uppercase;
        background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.16);
    }
    .ma-status.is-success { background: rgba(16,185,129,.15); color: #d1fae5; }
    .ma-status.is-info { background: rgba(59,130,246,.16); color: #dbeafe; }
    .ma-status.is-warning { background: rgba(245,158,11,.15); color: #fffbeb; }
    .ma-status.is-danger { background: rgba(239,68,68,.14); color: #fee2e2; }
    .ma-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
        gap: 14px;
    }
    .ma-card {
        background: #fff; border: 1px solid var(--ma-line); border-radius: 20px;
        overflow: hidden; box-shadow: 0 8px 30px rgba(15,23,42,.04);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .ma-card:hover {
        box-shadow: 0 16px 40px rgba(15,23,42,.08);
        transform: translateY(-2px);
    }
    .ma-card__head {
        padding: 16px 20px;
        border-bottom: 1px solid #eef2f7;
        background: linear-gradient(120deg, #f8fbff, #f1f6ff);
        display: flex; align-items: center; gap: 12px;
    }
    .ma-card__icon {
        width: 42px; height: 42px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(37,99,235,.10); color: var(--ma-accent);
        font-size: 1.1rem;
        transition: all 0.2s;
    }
    .ma-card:hover .ma-card__icon {
        background: var(--ma-accent);
        color: #fff;
    }
    .ma-card__title { font-size: 1.05rem; font-weight: 800; color: var(--ma-ink); }
    .ma-card__sub { font-size: .78rem; color: var(--ma-muted); }
    .ma-card__body { padding: 20px; }
    .ma-summary {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
    .ma-info {
        border: 1px solid var(--ma-line); border-radius: 16px;
        background: #f8fbff; padding: 14px 16px;
        transition: all 0.2s ease;
    }
    .ma-info:hover {
        background: #f1f6ff;
        border-color: #c7d7f4;
    }
    .ma-info__label {
        font-size: .68rem; font-weight: 800; color: #7483a0;
        text-transform: uppercase; letter-spacing: .08em; margin-bottom: 4px;
    }
    .ma-info__value { font-size: .95rem; font-weight: 700; color: var(--ma-ink); line-height: 1.35; }
    .ma-legend {
        display: grid; grid-template-columns: 1fr auto 1fr; gap: 12px;
        align-items: center; border: 1px solid var(--ma-line);
        border-radius: 18px; padding: 18px; background: linear-gradient(135deg, #f8fbff, #f1f6ff);
    }
    .ma-stop { display: flex; align-items: flex-start; gap: 12px; }
    .ma-stop--right { flex-direction: row-reverse; text-align: right; }
    .ma-dot {
        width: 12px; height: 12px; border-radius: 50%; margin-top: 5px; flex-shrink: 0;
    }
    .ma-dot--a { background: #10b981; box-shadow: 0 0 0 4px rgba(16,185,129,.15); }
    .ma-dot--b { background: #ef4444; box-shadow: 0 0 0 4px rgba(239,68,68,.14); }
    .ma-label {
        font-size: .68rem; font-weight: 800; color: #94a3b8;
        text-transform: uppercase; letter-spacing: .08em;
    }
    .ma-place { font-size: 1.05rem; font-weight: 800; color: var(--ma-ink); margin-top: 3px; }
    .ma-date { font-size: .82rem; color: #1d4ed8; font-weight: 700; margin-top: 2px; }
    .ma-mid { display: flex; flex-direction: column; align-items: center; gap: 4px; }
    .ma-mid__line { width: 1px; height: 16px; background: linear-gradient(180deg, #c7d7f4, #93c5fd); }
    .ma-mid__pill {
        background: #1e40af; color: #fff; border-radius: 999px;
        font-size: .68rem; font-weight: 800; padding: 4px 10px;
    }
    .ma-list { display: grid; gap: 12px; }
    .ma-list__item {
        border: 1px solid var(--ma-line); border-radius: 16px; background: #fff; padding: 14px 16px;
        transition: all 0.2s ease;
    }
    .ma-list__item:hover {
        background: #f8fbff;
        border-color: #cbd5e1;
    }
    .ma-list__label {
        font-size: .68rem; font-weight: 800; color: #7483a0;
        text-transform: uppercase; letter-spacing: .08em; margin-bottom: 4px;
    }
    .ma-list__value { font-size: .95rem; color: var(--ma-ink); font-weight: 700; line-height: 1.45; }
    .ma-price {
        display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px;
        margin-top: 14px;
    }
    .ma-price__box {
        border: 1px solid var(--ma-line); border-radius: 16px; background: #f8fbff; padding: 14px 16px;
        transition: all 0.2s ease;
    }
    .ma-price__box:hover {
        background: #f1f6ff;
        border-color: #c7d7f4;
    }
    .ma-price__box--full {
        grid-column: span 3;
    }
    .ma-price__label {
        font-size: .68rem; font-weight: 800; color: #7483a0;
        text-transform: uppercase; letter-spacing: .08em; margin-bottom: 4px;
    }
    .ma-price__value { font-size: 1.05rem; font-weight: 900; color: var(--ma-ink); }
    .ma-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 18px; }
    .ma-btn {
        display: inline-flex; align-items: center; gap: 8px;
        border-radius: 999px; padding: 10px 18px; font-weight: 800;
        text-decoration: none; border: 1px solid var(--ma-line); cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .ma-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15,23,42,.08);
    }
    .ma-btn:active {
        transform: translateY(0);
    }
    .ma-btn--primary { background: var(--ma-accent); color: #fff; border-color: var(--ma-accent); }
    .ma-btn--ghost { background: #fff; color: var(--ma-ink); }
    .ma-btn--success { background: #059669; color: #fff; border-color: #059669; }
    .ma-btn--disabled { background: #f1f5f9; color: #94a3b8; border-color: #e2e8f0; cursor: not-allowed; }
    .ma-sign-pad { width: 100%; min-height: 220px; border: 2px dashed #cbd5e1; border-radius: 16px; background: #fff; touch-action: none; }
    .ma-note {
        border: 1px solid #dbeafe; background: #f8fbff; color: #334155;
        border-radius: 16px; padding: 14px 18px; font-size: .92rem; line-height: 1.6;
    }
    .ma-table {
        width: 100%; border-collapse: collapse; font-size: .84rem;
    }
    .ma-table th {
        background: #f8fafc; color: #64748b; text-transform: uppercase; letter-spacing: .06em;
        font-size: .68rem; font-weight: 800; padding: 10px 12px; border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .ma-table td {
        padding: 10px 12px; vertical-align: middle; border-bottom: 1px solid #eef2f7;
    }
    .ma-table tbody tr:hover { background: #fbfdff; }
    .ma-tag {
        display: inline-flex; align-items: center; gap: 6px; border-radius: 999px;
        padding: 6px 10px; font-size: .74rem; font-weight: 800;
        background: #f1f5f9; color: #475569;
    }
    .ma-tag--green { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
    .ma-tag--amber { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .ma-tag--red { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .ma-payment-banner {
        margin: 0 0 14px;
        border-radius: 18px;
        border: 1px solid rgba(245, 158, 11, .28);
        background: linear-gradient(135deg, #fff8e1, #fff);
        box-shadow: 0 10px 28px rgba(245, 158, 11, .12);
        position: sticky;
        top: 12px;
        z-index: 15;
        overflow: hidden;
    }
    .ma-payment-banner__inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 14px 16px;
    }
    .ma-payment-banner__left {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }
    .ma-payment-banner__icon {
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(245, 158, 11, .18);
        color: #b45309;
        flex-shrink: 0;
        animation: maPaymentPulse 1.4s ease-in-out infinite;
    }
    .ma-payment-banner__title {
        margin: 0;
        font-size: .95rem;
        font-weight: 900;
        color: #92400e;
    }
    .ma-payment-banner__text {
        margin: 2px 0 0;
        font-size: .82rem;
        color: #78350f;
    }
    .ma-payment-banner__actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .ma-payment-banner__btn {
        border-radius: 999px;
        font-weight: 800;
        padding: 8px 12px;
        white-space: nowrap;
    }
    .ma-pill {
        border-radius: 999px; padding: 6px 10px; font-size: .72rem; font-weight: 800;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .ma-pill--pulse {
        animation: maGlow 1.4s ease-in-out infinite;
    }
    .ma-pill--success { background: rgba(16,185,129,.12); color: #047857; }
    .ma-pill--warning { background: rgba(245,158,11,.14); color: #b45309; }
    .ma-pill--danger { background: rgba(239,68,68,.12); color: #b91c1c; }
    .ma-pill--neutral { background: rgba(100,116,139,.12); color: #475569; }
    .ma-pending-row {
        position: relative;
        background: linear-gradient(90deg, rgba(255,248,225,.9), #fff);
    }
    .ma-pending-row::before {
        content: '';
        position: absolute;
        inset: 0;
        border: 2px solid rgba(245, 158, 11, .28);
        border-radius: 10px;
        pointer-events: none;
        animation: maBorderPulse 1.5s ease-in-out infinite;
    }
    @keyframes maPaymentPulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.06); }
    }
    @keyframes maGlow {
        0%, 100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, .42); }
        70% { box-shadow: 0 0 0 12px rgba(245, 158, 11, 0); }
    }
    @keyframes maBorderPulse {
        0%, 100% { opacity: .95; }
        50% { opacity: .5; }
    }
    .ma-history-empty {
        border: 1px dashed #cbd5e1; border-radius: 14px; padding: 18px; color: #64748b; background: #f8fafc;
    }
    .ma-mini {
        display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-top: 14px;
    }
    .ma-mini__item {
        border: 1px solid var(--ma-line); border-radius: 14px; background: #fff; padding: 12px 14px;
    }
    .ma-mini__label { font-size: .66rem; text-transform: uppercase; letter-spacing: .08em; color: #7483a0; font-weight: 800; margin-bottom: 4px; }
    .ma-mini__value { font-size: .92rem; font-weight: 800; color: var(--ma-ink); }
    .ma-section { margin-top: 14px; }
    .ma-subgrid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .ma-kicker {
        font-size: 11px; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; color: #64748b;
    }
    @media (max-width: 992px) {
        .ma-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .ma-summary, .ma-price, .ma-subgrid { grid-template-columns: 1fr; }
        .ma-mini { grid-template-columns: 1fr 1fr; }
        .ma-legend { grid-template-columns: 1fr; }
        .ma-mid { display: none; }
        .ma-stop--right { flex-direction: row; text-align: left; }
    }
</style>

<div class="ma-page">
    <div class="ma-hero">
        <div class="ma-hero__inner">
            <div>
                <h1 class="ma-hero__title">Minha reserva de motorista de app</h1>
                <p class="ma-hero__text">
                    Acompanhe a vigencia, renove por blocos completos do plano, confira os pagamentos e veja multas e sinistros ligados a esta reserva.
                </p>
                <div class="ma-chips">
                    <span class="ma-chip"><i class="fas fa-hashtag"></i> {{ $codigoReserva }}</span>
                    <span class="ma-chip"><i class="fas fa-calendar-plus"></i> Criada em {{ $criadaEm }}</span>
                    <span class="ma-chip"><i class="fas fa-user"></i> {{ $usuario?->nome ?? $usuario?->name ?? 'Cliente' }}</span>
                    @if($temPagamentoPendente || !$pagamentoEfetuado)
                        <span class="ma-chip ma-chip--warn"><i class="fas fa-circle-exclamation"></i> Falta pagamento</span>
                    @endif
                </div>
            </div>
            <div class="text-right">
                <span class="ma-status is-{{ $statusClass }} mb-2">
                    <i class="{{ $statusIcon }}"></i> {{ $statusLabel }}
                </span><br>
                <span class="ma-status is-{{ $pagamentoClass }}">
                    <i class="{{ $pagamentoIcon }}"></i> {{ $statusPagamento }}
                </span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    @if($temPagamentoPendente && $pagamentoPendenteAtual)
        <div class="ma-payment-banner" id="bannerPagamentoPendente">
            <div class="ma-payment-banner__inner">
                <div class="ma-payment-banner__left">
                    <div class="ma-payment-banner__icon">
                        <i class="fas fa-exclamation-triangle fa-lg"></i>
                    </div>
                    <div>
                        <p class="ma-payment-banner__title">
                            Pagamento pendente da renovação
                            @if(session('pagamento'))
                                <span class="badge badge-warning ml-1">Novo</span>
                            @endif
                        </p>
                        <p class="ma-payment-banner__text">
                            {{ $qtdPagamentosPendentes > 1 ? 'Existem ' . $qtdPagamentosPendentes . ' pagamentos pendentes. Confirme o mais recente primeiro.' : 'O próximo ciclo está aguardando confirmação. Clique para ir direto ao histórico de pagamentos.' }}
                        </p>
                    </div>
                </div>
                <div class="ma-payment-banner__actions">
                    <a href="#historico-pagamentos" class="btn btn-warning ma-payment-banner__btn">
                        <i class="fas fa-arrow-down mr-1"></i> Ver pagamento
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="ma-grid">
        <div class="ma-main">
            <div class="ma-card">
                <div class="ma-card__head">
                    <div class="ma-card__icon"><i class="fas fa-route"></i></div>
                    <div>
                        <div class="ma-card__title">Vigencia atual</div>
                        <div class="ma-card__sub">Janela ativa da reserva recorrente</div>
                    </div>
                </div>
                <div class="ma-card__body">
                    <div class="ma-legend">
                        <div class="ma-stop">
                            <div class="ma-dot ma-dot--a"></div>
                            <div>
                                <div class="ma-label">Inicio</div>
                                <div class="ma-place">{{ $dataInicio }}</div>
                            </div>
                        </div>
                        <div class="ma-mid">
                            <div class="ma-mid__line"></div>
                            <div class="ma-mid__pill">
                                {{ $registro->quantidade_dias ?? 0 }} {{ ((int) ($registro->quantidade_dias ?? 0) === 1) ? 'dia' : 'dias' }}
                            </div>
                            <div class="ma-mid__line"></div>
                        </div>
                        <div class="ma-stop ma-stop--right">
                            <div class="ma-dot ma-dot--b"></div>
                            <div>
                                <div class="ma-label">Fim</div>
                                <div class="ma-place">{{ $dataFim }}</div>
                                @if(!is_null($diasRestantes))
                                    <div class="ma-date">
                                        @if($diasRestantes > 0)
                                            {{ $diasRestantes }} dia(s) restantes
                                        @elseif($diasRestantes === 0)
                                            vence hoje
                                        @else
                                            vencida ha {{ abs($diasRestantes) }} dia(s)
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="ma-mini">
                        <div class="ma-mini__item">
                            <div class="ma-mini__label">Vigência contratada</div>
                            <div class="ma-mini__value">{{ $inicioVigenciaFormatado }} até {{ $fimVigenciaFormatado }}</div>
                        </div>
                        <div class="ma-mini__item">
                            <div class="ma-mini__label">Veículo</div>
                            <div class="ma-mini__value">{{ $veiculoAtual?->nome ?? 'Sem vínculo' }}</div>
                        </div>
                        <div class="ma-mini__item">
                            <div class="ma-mini__label">Km inicial</div>
                            <div class="ma-mini__value">{{ $registro->quilometragem_inicial ? number_format((int) $registro->quilometragem_inicial, 0, ',', '.') . ' km' : '—' }}</div>
                        </div>
                        <div class="ma-mini__item">
                            <div class="ma-mini__label">Combustível</div>
                            <div class="ma-mini__value">{{ $registro->nivel_combustivel_inicial ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="ma-actions">
                        @if($podeAceitarTermo)
                            <button type="button" class="ma-btn ma-btn--success" data-toggle="modal" data-target="#modalTermoConfirmacaoMotoristaApp">
                                <i class="fas fa-file-signature"></i>
                                <span>Aceitar termo</span>
                            </button>
                        @elseif($termoConfirmacaoAceito)
                            <span class="ma-btn ma-btn--disabled">
                                <i class="fas fa-file-signature"></i>
                                <span>Termo aceito</span>
                            </span>
                        @endif
                        @if($estaCheckin)
                            <span class="ma-btn ma-btn--disabled">
                                <i class="fas fa-clipboard-check"></i>
                                <span>Check-in registrado</span>
                            </span>
                        @endif
                        @if($temContratoPdf)
                            <a href="{{ route('reserva.minhas.motorista-app.ver-contrato', ['id' => $registro->id]) }}" target="_blank" class="ma-btn ma-btn--ghost">
                                <i class="fas fa-file-contract"></i>
                                <span>{{ $contratoAssinado ? 'Ver contrato assinado' : 'Ver contrato' }}</span>
                            </a>
                        @endif
                        @if($podeAssinarContrato)
                            <button type="button" class="ma-btn ma-btn--success" data-toggle="modal" data-target="#modalAssinarContratoMotoristaApp">
                                <i class="fas fa-signature"></i>
                                <span>Assinar contrato</span>
                            </button>
                        @endif
                        @if($podeRenovar)
                            <button type="button" class="ma-btn ma-btn--primary" data-toggle="modal" data-target="#modalRenovarMotoristaApp">
                                <i class="fas fa-arrows-rotate"></i>
                                <span>Renovar plano</span>
                            </button>
                        @elseif($estaCheckout)
                            <span class="ma-btn ma-btn--disabled" title="Nao e possivel renovar apos o checkout">
                                <i class="fas fa-lock"></i>
                                <span>Renovacao bloqueada</span>
                            </span>
                        @endif
                        @if($podeCancelar)
                            <button type="button" class="ma-btn ma-btn--ghost text-danger" data-toggle="modal" data-target="#modalCancelarMotoristaApp">
                                <i class="fas fa-ban"></i>
                                <span>Cancelar reserva</span>
                            </button>
                        @endif
                        <a href="{{ route('reserva.minhas') }}" class="ma-btn ma-btn--ghost">
                            <i class="fas fa-arrow-left"></i>
                            <span>Voltar</span>
                        </a>
                    </div>

                    <div class="ma-note mt-3">
                        Aqui você pode acompanhar o pagamento e renovar o contrato. O cancelamento segue a mesma janela de 24 horas da regra comum do sistema.
                    </div>
                </div>
            </div>

            <div class="ma-card ma-section">
                <div class="ma-card__head">
                    <div class="ma-card__icon"><i class="fas fa-file-invoice-dollar"></i></div>
                    <div>
                        <div class="ma-card__title">Resumo financeiro</div>
                        <div class="ma-card__sub">Valor base atual e total esperado do ciclo</div>
                    </div>
                    @if($existeDetalheOpcionais)
                        <div class="ml-auto">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#modalDetalhesOpcionaisMotoristaApp">
                                <i class="fas fa-ellipsis-h mr-1"></i> Detalhes
                            </button>
                        </div>
                    @endif
                </div>
                <div class="ma-card__body">
                    <div class="ma-price">
                        <div class="ma-price__box">
                            <div class="ma-price__label">Valor por ciclo contratado</div>
                            <div class="ma-price__value">R$ {{ number_format($valorPeriodoContratado, 2, ',', '.') }}</div>
                        </div>
                        <div class="ma-price__box">
                            <div class="ma-price__label d-flex align-items-center justify-content-between" style="gap:10px;">
                                <span>Opcionais</span>
                                @if($existeDetalheOpcionais)
                                    <button type="button" class="btn btn-xs btn-link p-0 text-primary" data-toggle="modal" data-target="#modalDetalhesOpcionaisMotoristaApp" title="Ver opcionais selecionados">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                            </div>
                            <div class="ma-price__value">R$ {{ number_format($valorOpcionais, 2, ',', '.') }}</div>
                        </div>
                        <div class="ma-price__box">
                            <div class="ma-price__label">Total do ciclo</div>
                            <div class="ma-price__value">R$ {{ number_format($valorTotal, 2, ',', '.') }}</div>
                        </div>
                        @php
                            $pagamentoPendenteCliente = $pagamentos->first(fn($p) => strtolower((string)($p->status ?? '')) === 'pendente');
                            $totalPagoPagamentosCliente = (float) $pagamentos->filter(fn($p) => strtolower((string)($p->status ?? '')) === 'pago')->sum('valor_total');
                            $valorPendenteCliente = max(0, round((float) $valorTotal - $totalPagoPagamentosCliente, 2));
                        @endphp
                        @if($pagamentoPendenteCliente && $valorPendenteCliente > 0)
                            <div class="ma-price__box ma-price__box--full" style="border: 1px solid #f87171; background: #fef2f2; border-radius: 16px; padding: 14px 16px; margin-top: 15px;">
                                <div class="ma-price__label text-danger font-weight-bold"><i class="fas fa-exclamation-circle mr-1"></i> Total Pendente</div>
                                <div class="ma-price__value text-danger" style="font-size: 1.4rem; font-weight: 800;">R$ {{ number_format($valorPendenteCliente, 2, ',', '.') }}</div>
                                <div class="mt-2">
                                    <a href="{{ route('reserva.minhas.pagamento.efetuar', ['id' => $codigoReserva]) }}" class="btn btn-xs btn-danger font-weight-bold text-white">
                                        <i class="fas fa-credit-card mr-1"></i> Efetuar Pagamento
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="ma-price__box ma-price__box--full" style="border: 1px solid #34d399; background: #ecfdf5; border-radius: 16px; padding: 14px 16px; margin-top: 15px;">
                                <div class="ma-price__label text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Status de Pagamento</div>
                                <div class="ma-price__value text-success" style="font-size: 1.4rem; font-weight: 800;">Tudo Pago</div>
                            </div>
                        @endif
                    </div>

                    <div class="ma-summary mt-3">
                        <div class="ma-info">
                            <div class="ma-info__label">Plano</div>
                            <div class="ma-info__value">{{ $plano?->nome ?? 'Nao informado' }}</div>
                        </div>
                        <div class="ma-info">
                            <div class="ma-info__label">Periodo</div>
                            <div class="ma-info__value">{{ $periodoLabelContrato }}</div>
                        </div>
                        <div class="ma-info">
                            <div class="ma-info__label">Km</div>
                            <div class="ma-info__value">{{ $kmResumo }}</div>
                        </div>
                        <div class="ma-info">
                            <div class="ma-info__label">Calcao</div>
                            <div class="ma-info__value">R$ {{ number_format($calcao, 2, ',', '.') }}</div>
                        </div>
                    </div>

                    <div class="mt-3 d-flex flex-wrap" style="gap:8px;">
                        <span class="ma-tag ma-tag--green"><i class="fas fa-user-tie"></i> Motorista APP</span>
                        <span class="ma-tag"><i class="fas fa-signal"></i> {{ $solicitacaoStatus }}</span>
                        <span class="ma-tag ma-tag--amber"><i class="fas fa-calendar-day"></i> Renovação por {{ $periodoLabelContrato }}</span>
                    </div>
                </div>
            </div>

            @php
                $pagamentosQuitadosCliente = $pagamentos->filter(fn($p) => strtolower((string)($p->status ?? '')) === 'pago');
            @endphp

            <div class="ma-card ma-section" id="historico-pagamentos">
                <div class="ma-card__head">
                    <div class="ma-card__icon"><i class="fas fa-receipt"></i></div>
                    <div>
                        <div class="ma-card__title">Historico de pagamentos</div>
                        <div class="ma-card__sub">Cada renovacao quitada fica registrada aqui</div>
                    </div>
                </div>
                <div class="ma-card__body p-0">
                    @if($pagamentosQuitadosCliente->isEmpty())
                        <div class="ma-card__body">
                            <div class="ma-history-empty">
                                <i class="fas fa-receipt fa-2x mb-2 d-block"></i>
                                Nenhum pagamento quitado registrado ainda.
                            </div>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="ma-table mb-0">
                                <thead>
                                    <tr>
                                        <th class="pl-3">Periodo</th>
                                        <th>Valor</th>
                                        <th>Status</th>
                                        <th>Metodo</th>
                                        <th>Pago em</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($pagamentosQuitadosCliente as $pagamento)
                                        @php
                                            $periodoInicio = $pagamento->data_inicio_prevista ? \Carbon\Carbon::parse($pagamento->data_inicio_prevista)->format('d/m/Y') : '-';
                                            $periodoFim = $pagamento->data_fim_prevista ? \Carbon\Carbon::parse($pagamento->data_fim_prevista)->format('d/m/Y') : '-';
                                            $valorUnitarioLinha = (float) ($pagamento->valor_unitario > 0 ? $pagamento->valor_unitario : $valorPeriodoContratado);
                                            $periodosLinha = max(1, (int) ($pagamento->periodos ?? 1));
                                            $valorBaseLinha = $valorUnitarioLinha * $periodosLinha;
                                            $valorTotalLinha = (float) ($pagamento->valor_total > 0 ? $pagamento->valor_total : round($valorBaseLinha + (float) ($registro->valor_opcionais ?? 0), 2));
                                        @endphp
                                        <tr id="pagamento-{{ $pagamento->id }}">
                                            <td class="pl-3">
                                                <div class="font-weight-bold text-dark">{{ $periodoInicio }} a {{ $periodoFim }}</div>
                                                <div class="text-muted" style="font-size:.75rem;">{{ (int) ($pagamento->periodos ?? 1) }} {{ ((int) ($pagamento->periodos ?? 1) === 1) ? $periodoUnidade : $periodoUnidadePlural }}</div>
                                            </td>
                                            <td class="font-weight-bold">R$ {{ number_format($valorTotalLinha, 2, ',', '.') }}</td>
                                            <td>
                                                <span class="ma-pill ma-pill--success">
                                                    <i class="fas fa-check-circle"></i> Pago
                                                </span>
                                            </td>
                                            <td>{{ $pagamento->metodo_label ?? '—' }}</td>
                                            <td>{{ $pagamento->pago_em ? \Carbon\Carbon::parse($pagamento->pago_em)->format('d/m/Y H:i') : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="ma-card ma-section">
                <div class="ma-card__head">
                        <div class="ma-card__icon"><i class="fas fa-car-crash"></i></div>
                        <div>
                            <div class="ma-card__title">Sinistros</div>
                            <div class="ma-card__sub">Ocorrencias ligadas a esta reserva</div>
                        </div>
                    </div>
                    <div class="ma-card__body">
                        @if($sinistros->isEmpty())
                            <div class="ma-history-empty">Nenhum sinistro vinculado a esta reserva.</div>
                        @else
                            <div class="ma-list">
                                @foreach($sinistros as $sinistro)
                                    @php
                                        $statusSinistro = strtolower((string) ($sinistro->status ?? 'aberto'));
                                        $statusChip = match ($statusSinistro) {
                                            'aberto', 'em_analise' => 'ma-pill--warning',
                                            'aprovado', 'indenizado', 'encerrado' => 'ma-pill--success',
                                            'cancelado' => 'ma-pill--neutral',
                                            default => 'ma-pill--neutral',
                                        };
                                        $sinistroData = $sinistro->data_hora_ocorrido ? \Carbon\Carbon::parse($sinistro->data_hora_ocorrido)->format('d/m/Y H:i') : '-';
                                        $sinistroValor = (float) ($sinistro->valor_responsabilidade_cliente ?? $sinistro->valor_prejuizo_estimado ?? 0);
                                    @endphp
                                    <div class="ma-list__item">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <div class="ma-list__label">{{ $sinistro->label_tipo ?? 'Sinistro' }}</div>
                                                <div class="ma-list__value">{{ $sinistro->local_ocorrido ?: 'Local nao informado' }}</div>
                                                <div class="text-muted" style="font-size:.74rem;">{{ $sinistroData }}</div>
                                            </div>
                                            <span class="ma-pill {{ $statusChip }}">{{ $sinistro->label_status ?? ucfirst($statusSinistro) }}</span>
                                        </div>
                                        <div class="mt-2 text-dark font-weight-bold">R$ {{ number_format($sinistroValor, 2, ',', '.') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <div class="ma-card">
                    <div class="ma-card__head">
                        <div class="ma-card__icon"><i class="fas fa-circle-exclamation"></i></div>
                        <div>
                            <div class="ma-card__title">Multas</div>
                            <div class="ma-card__sub">Infrações vinculadas a esta reserva</div>
                        </div>
                    </div>
                    <div class="ma-card__body">
                        @if($multas->isEmpty())
                            <div class="ma-history-empty">Nenhuma multa vinculada a esta reserva.</div>
                        @else
                            <div class="ma-list">
                                @foreach($multas as $multa)
                                    @php
                                        $statusMulta = strtolower((string) ($multa->status_multa ?? 'pendente'));
                                        $statusChip = match ($statusMulta) {
                                            'paga' => 'ma-pill--success',
                                            'pendente', 'em_analise' => 'ma-pill--warning',
                                            'cancelada' => 'ma-pill--neutral',
                                            default => 'ma-pill--neutral',
                                        };
                                        $multaData = $multa->data_hora_infracao ? \Carbon\Carbon::parse($multa->data_hora_infracao)->format('d/m/Y H:i') : '-';
                                        $multaValor = (float) ($multa->valor_final ?? $multa->valor_original ?? 0);
                                    @endphp
                                    <div class="ma-list__item">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <div class="ma-list__label">Multa #{{ $multa->id }}</div>
                                                <div class="ma-list__value">{{ $multa->local_infracao ?: 'Local nao informado' }}</div>
                                                <div class="text-muted" style="font-size:.74rem;">{{ $multaData }}</div>
                                            </div>
                                            <span class="ma-pill {{ $statusChip }}">{{ $multa->label_status_multa ?? ucfirst($statusMulta) }}</span>
                                        </div>
                                        <div class="mt-2 text-dark font-weight-bold">R$ {{ number_format($multaValor, 2, ',', '.') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="ma-card ma-section" style="margin-bottom:16px;">
                <div class="ma-card__head">
                    <div class="ma-card__icon"><i class="fas fa-star"></i></div>
                    <div>
                        <div class="ma-card__title">Sua avaliação</div>
                        <div class="ma-card__sub">Compartilhe sua experiência com esta locação</div>
                    </div>
                </div>
                <div class="ma-card__body">
                    @if($estaCheckout)
                        <div class="ma-history-empty">
                            <i class="fas fa-star fa-2x mb-2 d-block"></i>
                            Obrigado por utilizar nossos serviços! Sua avaliação pode ser enviada através do nosso atendimento.
                        </div>
                    @else
                        <div class="ma-history-empty">
                            <i class="fas fa-star fa-2x mb-2 d-block"></i>
                            A avaliação ficará disponível após a conclusão da reserva.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="ma-side">
            <div class="ma-card">
                <div class="ma-card__head">
                    <div class="ma-card__icon"><i class="fas fa-user"></i></div>
                    <div>
                        <div class="ma-card__title">Dados do cliente</div>
                        <div class="ma-card__sub">Informacoes principais do titular</div>
                    </div>
                </div>
                <div class="ma-card__body">
                    <div class="ma-list">
                        <div class="ma-list__item">
                            <div class="ma-list__label">Nome</div>
                            <div class="ma-list__value">{{ $usuario?->nome ?? $usuario?->name ?? 'Nao informado' }}</div>
                        </div>
                        <div class="ma-list__item">
                            <div class="ma-list__label">Email</div>
                            <div class="ma-list__value">{{ $usuario?->email ?? '-' }}</div>
                        </div>
                        <div class="ma-list__item">
                            <div class="ma-list__label">Telefone</div>
                            <div class="ma-list__value">{{ $usuario?->telefone ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ma-card ma-section">
                <div class="ma-card__head">
                    <div class="ma-card__icon"><i class="fas fa-file-alt"></i></div>
                    <div>
                        <div class="ma-card__title">Dados enviados</div>
                        <div class="ma-card__sub">Resumo do que foi informado na solicitacao</div>
                    </div>
                </div>
                <div class="ma-card__body">
                    @if(empty($dadosJson))
                        <div class="ma-history-empty">Nenhum dado adicional informado.</div>
                    @else
                        <div class="ma-list">
                            @foreach($dadosJson as $key => $value)
                                @php
                                    $campoKey = (string) $key;
                                    $campoCfg = $camposPersonalizados->get($campoKey);
                                    $campoLabel = (string) ($rotulosPadraoDados[$campoKey] ?? $campoCfg['label'] ?? str_replace(['_', '-'], ' ', ucfirst($campoKey)));
                                    $campoTipo = (string) ($campoCfg['type'] ?? '');
                                    if ($campoKey === 'opcionais_payload') {
                                        $valorExibicao = collect($opcionaisSelecionados)
                                            ->map(function ($item) use ($opcionaisMap) {
                                                $opcional = $opcionaisMap->get((int) ($item['id'] ?? 0));
                                                $nome = $opcional?->nome ?? ('Opcional #' . (int) ($item['id'] ?? 0));
                                                $quantidade = (int) ($item['quantidade'] ?? 0);
                                                return $quantidade > 1 ? $nome . ' x' . $quantidade : $nome;
                                            })
                                            ->filter()
                                            ->values()
                                            ->implode(', ');
                                        $valorExibicao = $valorExibicao !== '' ? $valorExibicao : 'Nenhum opcional selecionado';
                                    } elseif ($campoKey === 'plano_id') {
                                        $valorExibicao = $plano?->nome ?? ('Plano #' . (int) $value);
                                    } elseif ($campoKey === 'estacao_id') {
                                        $valorExibicao = $registro->estacaoRetirada?->nome ?? ('Estação #' . (int) $value);
                                    } elseif ($campoKey === 'data_inicio') {
                                        $valorExibicao = $formatarDataValor($value);
                                    } elseif ($campoTipo === 'data') {
                                        $valorExibicao = $formatarDataValor($value);
                                    } else {
                                        $valorExibicao = $formatarValor($value);
                                    }
                                @endphp
                                <div class="ma-list__item">
                                    <div class="ma-list__label">{{ $campoLabel }}</div>
                                    <div class="ma-list__value">{{ $valorExibicao }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            @if(!empty($registro->observacoes))
                <div class="ma-card ma-section">
                    <div class="ma-card__head">
                        <div class="ma-card__icon"><i class="fas fa-sticky-note"></i></div>
                        <div>
                            <div class="ma-card__title">Observacoes</div>
                            <div class="ma-card__sub">Notas internas ou do cliente</div>
                        </div>
                    </div>
                    <div class="ma-card__body">
                        <div class="ma-note">{{ $registro->observacoes }}</div>
                    </div>
                </div>
            @endif

            {{-- <div class="ma-card ma-section">
                <div class="ma-card__head">
                    <div class="ma-card__icon"><i class="fas fa-link"></i></div>
                    <div>
                        <div class="ma-card__title">Acoes rapidas</div>
                        <div class="ma-card__sub">Atalhos uteis para esta tela</div>
                    </div>
                </div>
                <div class="ma-card__body">
                    <div class="ma-actions">
                        <a href="{{ route('reserva.minhas') }}" class="ma-btn ma-btn--ghost">
                            <i class="fas fa-list"></i>
                            <span>Minhas reservas</span>
                        </a>
                        <a href="{{ route('site.apresentacao.motorista-app') }}" class="ma-btn ma-btn--primary">
                            <i class="fas fa-plus"></i>
                            <span>Nova reserva</span>
                        </a>
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetalhesOpcionaisMotoristaApp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#0f172a;color:#fff;">
                <h5 class="modal-title"><i class="fas fa-list-check mr-1"></i> Detalhes dos opcionais</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                @if($opcionaisDetalhados->isEmpty())
                    <div class="alert alert-info mb-0">
                        Nenhum opcional selecionado nesta reserva.
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Opcional</th>
                                    <th class="text-center">Qtd</th>
                                    <th class="text-right">Valor unit.</th>
                                    <th class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($opcionaisDetalhados as $opcional)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold">{{ $opcional['nome'] }}</div>
                                            <small class="text-muted">
                                                Tipo de cobrança: {{ $opcional['tipo_cobranca'] !== '-' ? $opcional['tipo_cobranca'] : 'não informado' }}
                                                @if(!empty($opcional['descricao']))
                                                    <br>{{ $opcional['descricao'] }}
                                                @endif
                                            </small>
                                        </td>
                                        <td class="text-center">{{ (int) $opcional['quantidade'] }}</td>
                                        <td class="text-right">R$ {{ number_format((float) $opcional['valor_unitario'], 2, ',', '.') }}</td>
                                        <td class="text-right font-weight-bold">R$ {{ number_format((float) $opcional['subtotal'], 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Total de opcionais</th>
                                    <th class="text-right">R$ {{ number_format($valorOpcionais, 2, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEfetivarLocacao" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('reserva.minhas.motorista-app.efetivar', ['id' => $registro->id]) }}" method="POST">
                @csrf
                <div class="modal-header" style="background:#0f172a;color:#fff;">
                    <h5 class="modal-title"><i class="fas fa-play mr-1"></i> Efetivar locação</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Ao efetivar, o sistema vincula o veículo, registra km/combustível, gera o contrato e cria o primeiro ciclo financeiro.
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Veículo</label>
                            <select name="veiculo_id" class="form-control" required>
                                <option value="">Selecione</option>
                                @foreach($veiculosDisponiveis as $veiculo)
                                    <option value="{{ $veiculo->id }}" @selected((int) ($registro->veiculo_id ?? 0) === (int) $veiculo->id)>
                                        {{ $veiculo->nome ?? ('Veículo #' . $veiculo->id) }}@if(!empty($veiculo->placa_licenca)) - {{ $veiculo->placa_licenca }}@endif
                                    </option>
                                @endforeach
                            </select>
                            @if($veiculosDisponiveis->isEmpty())
                                <small class="text-danger d-block mt-1">Nenhum veículo ativo disponível para vincular.</small>
                            @endif
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold">Km inicial</label>
                            <input type="number" name="quilometragem_inicial" class="form-control" min="0" step="1" value="{{ old('quilometragem_inicial', $registro->quilometragem_inicial ?? '') }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="font-weight-bold">Combustível</label>
                            <select name="nivel_combustivel_inicial" class="form-control" required>
                                <option value="">Selecione</option>
                                @foreach(['Reserva','1/8','1/4','3/8','1/2','5/8','3/4','7/8','Cheio'] as $nivel)
                                    <option value="{{ $nivel }}">{{ $nivel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="font-weight-bold">Data e hora reais da retirada</label>
                            <input type="datetime-local" name="data_retirada_real" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}">
                        </div>
                    </div>
                    <div class="ma-note">
                        O contrato será congelado com o valor vigente neste momento. Alterações futuras do plano não mudam este contrato ativo.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check mr-1"></i> Efetivar locação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCancelarMotoristaApp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('reserva.minhas.motorista-app.cancelar', ['id' => $registro->id]) }}" method="POST">
                @csrf
                <div class="modal-header" style="background:#b91c1c;color:#fff;">
                    <h5 class="modal-title"><i class="fas fa-ban mr-1"></i> Cancelar reserva</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger mb-0">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        O cancelamento segue a regra comum do sistema: somente dentro de 24 horas da criação e antes da efetivação.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Voltar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-ban mr-1"></i> Cancelar reserva
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRenovarMotoristaApp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('reserva.minhas.motorista-app.renovar', ['id' => $registro->id]) }}" method="POST">
                @csrf
                <div class="modal-header" style="background:#0f172a;color:#fff;">
                    <h5 class="modal-title"><i class="fas fa-arrows-rotate mr-1"></i> Renovar plano</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        A renovação respeita o período fechado do contrato. Informe apenas a quantidade inteira de ciclos.
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4 mb-3">
                            <div class="ma-info">
                                <div class="ma-info__label">Plano atual</div>
                                <div class="ma-info__value">{{ $plano?->nome ?? '—' }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="ma-info">
                                <div class="ma-info__label">Vigência atual</div>
                                <div class="ma-info__value">{{ $inicioVigenciaFormatado }} até {{ $fimVigenciaFormatado }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="ma-info">
                                <div class="ma-info__label">Valor por ciclo</div>
                                <div class="ma-info__value">R$ {{ number_format($valorPeriodoContratado, 2, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="font-weight-bold">Quantidade de ciclos</label>
                            <input type="number" name="periodos" id="maRenovarPeriodos" class="form-control" min="1" step="1" value="1" required>
                            <small class="text-muted">Ex.: 1, 2, 3...</small>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="font-weight-bold">Observacoes</label>
                            <textarea name="observacoes" class="form-control" rows="4" placeholder="Opcional"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="ma-info">
                                <div class="ma-info__label">Nova vigência</div>
                                <div class="ma-info__value" id="maNovaVigencia">-</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="ma-info">
                                <div class="ma-info__label">Total da renovação</div>
                                <div class="ma-info__value" id="maTotalRenovacao">R$ {{ number_format($valorPeriodoContratado, 2, ',', '.') }}</div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="ma-info">
                                <div class="ma-info__label">Próximo ciclo</div>
                                <div class="ma-info__value" id="maProximoCiclo">-</div>
                            </div>
                        </div>
                    </div>
                    <div class="ma-note">
                        A renovação usa o valor congelado do contrato. Alterações de preço só valem por reajuste contratual separado.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check mr-1"></i> Criar renovacao
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTermoConfirmacaoMotoristaApp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="formAceitarTermoMotoristaApp" action="{{ route('reserva.minhas.motorista-app.aceitar-termo', ['id' => $registro->id]) }}" method="POST">
                @csrf
                <div class="modal-header" style="background:#0f172a;color:#fff;">
                    <h5 class="modal-title"><i class="fas fa-file-signature mr-1"></i> {{ trim((string) ($termoAceite?->titulo ?? 'Termo de confirmacao da reserva')) }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    @php
                        $textoTermoAceite = trim((string) ($termoAceite?->texto ?? ''));
                        $mensagemTermoAceite = $textoTermoAceite !== '' ? $textoTermoAceite : 'Este termo confirma a reserva, suas condicoes de retirada e autoriza a geracao posterior do contrato da locacao no check-in.';
                    @endphp
                    <div class="text-muted">
                        {!! nl2br(e($mensagemTermoAceite)) !!}
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="aceiteTermoMotoristaAppCliente" name="aceite_termo" value="1" required>
                        <label class="custom-control-label font-weight-bold" for="aceiteTermoMotoristaAppCliente">
                            Li e aceito o termo de confirmacao da reserva.
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnAceitarTermoMotoristaApp" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Aceitar termo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAssinarContratoMotoristaApp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="formAssinarContratoMotoristaApp" action="{{ route('reserva.minhas.motorista-app.assinar-contrato', ['id' => $registro->id]) }}" method="POST">
                @csrf
                <input type="hidden" name="assinatura" id="maAssinaturaInput">
                <div class="modal-header" style="background:#0f172a;color:#fff;">
                    <h5 class="modal-title"><i class="fas fa-signature mr-1"></i> Assinar contrato</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        Assine no quadro abaixo. Depois de salvar, a locacao fica ativa.
                    </div>
                    <canvas id="maSignatureCanvas" class="ma-sign-pad"></canvas>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small class="text-muted">Use o mouse ou toque na tela para assinar.</small>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="maLimparAssinatura">
                            <i class="fas fa-eraser mr-1"></i> Limpar
                        </button>
                    </div>
                    @if($temContratoPdf)
                        <a href="{{ route('reserva.minhas.motorista-app.ver-contrato', ['id' => $registro->id]) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-3">
                            <i class="fas fa-file-contract mr-1"></i> Ver contrato antes de assinar
                        </a>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Salvar assinatura
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalConfirmarPagamentoMotoristaApp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="formConfirmarPagamentoMotoristaApp" method="POST">
                @csrf
                <div class="modal-header" style="background:#0f172a;color:#fff;">
                    <h5 class="modal-title"><i class="fas fa-credit-card mr-1"></i> Pagar renovação</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Informe o metodo para registrar o pagamento da renovacao selecionada.
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Metodo de pagamento</label>
                            <select name="metodo_pagamento" class="form-control" required>
                                <option value="">Selecione</option>
                                <option value="pix">PIX</option>
                                <option value="cartao_credito">Cartao de credito</option>
                                <option value="cartao_debito">Cartao de debito</option>
                                <option value="boleto">Boleto</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="dinheiro">Dinheiro</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="font-weight-bold">Referencia</label>
                            <input type="text" name="referencia_pagamento" class="form-control" placeholder="Opcional">
                        </div>
                    </div>
                    <div class="ma-note">
                        Valor desta renovacao: <strong id="maValorConfirmacao">-</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-credit-card mr-1"></i> Pagar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var formConfirmar = document.getElementById('formConfirmarPagamentoMotoristaApp');
    var valorConfirmacao = document.getElementById('maValorConfirmacao');
    var inputPeriodos = document.getElementById('maRenovarPeriodos');
    var novaVigencia = document.getElementById('maNovaVigencia');
    var proximoCiclo = document.getElementById('maProximoCiclo');
    var totalRenovacao = document.getElementById('maTotalRenovacao');
    var historicoPagamentos = document.getElementById('historico-pagamentos');
    var assinaturaCanvas = document.getElementById('maSignatureCanvas');
    var assinaturaInput = document.getElementById('maAssinaturaInput');
    var formAssinatura = document.getElementById('formAssinarContratoMotoristaApp');
    var btnLimparAssinatura = document.getElementById('maLimparAssinatura');
    var assinaturaCtx = assinaturaCanvas ? assinaturaCanvas.getContext('2d') : null;
    var assinando = false;
    var assinaturaPreenchida = false;
    var focoPagamentoNovo = @json((bool) session('pagamento'));
    var valorPorCiclo = @json((float) $valorPeriodoContratado);
    var cicloPeriodo = @json((string) $periodoContrato);
    var fimAtualIso = @json(optional($fimVigencia)->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'));

    if (focoPagamentoNovo && historicoPagamentos) {
        setTimeout(function () {
            historicoPagamentos.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 350);
    }

    document.querySelectorAll('[data-pagar-pagamento="1"]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            formConfirmar.action = btn.getAttribute('data-action');
            valorConfirmacao.textContent = btn.getAttribute('data-valor') || '-';
            $('#modalConfirmarPagamentoMotoristaApp').modal('show');
        });
    });

    function pad(n) {
        return String(n).padStart(2, '0');
    }

    function formatDate(date) {
        return pad(date.getDate()) + '/' + pad(date.getMonth() + 1) + '/' + date.getFullYear() + ' ' + pad(date.getHours()) + ':' + pad(date.getMinutes());
    }

    function addCycles(baseDate, periodo, ciclos) {
        var date = new Date(baseDate.getTime());
        var qty = Math.max(1, parseInt(ciclos || 1, 10));

        function addMonthsNoOverflow(months) {
            var day = date.getDate();
            var hours = date.getHours();
            var minutes = date.getMinutes();
            var seconds = date.getSeconds();
            date.setDate(1);
            date.setMonth(date.getMonth() + months);
            var lastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0).getDate();
            date.setDate(Math.min(day, lastDay));
            date.setHours(hours, minutes, seconds, 0);
        }

        for (var i = 0; i < qty; i++) {
            switch (periodo) {
                case 'quinze':
                    date.setDate(date.getDate() + 15);
                    break;
                case 'mes':
                    addMonthsNoOverflow(1);
                    break;
                case 'trimestre':
                    addMonthsNoOverflow(3);
                    break;
                case 'semestre':
                    addMonthsNoOverflow(6);
                    break;
                case 'ano':
                    addMonthsNoOverflow(12);
                    break;
                default:
                    date.setDate(date.getDate() + 7);
                    break;
            }
        }

        return date;
    }

    function refreshRenewalPreview() {
        if (!inputPeriodos || !novaVigencia || !proximoCiclo || !totalRenovacao) {
            return;
        }

        var ciclos = Math.max(1, parseInt(inputPeriodos.value || '1', 10));
        var start = new Date(fimAtualIso.replace(' ', 'T'));
        var end = addCycles(start, cicloPeriodo, ciclos);
        novaVigencia.textContent = formatDate(start) + ' até ' + formatDate(end);
        proximoCiclo.textContent = 'Ciclo ' + ciclos + ' de ' + ciclos;
        totalRenovacao.textContent = 'R$ ' + (valorPorCiclo * ciclos).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    if (inputPeriodos) {
        inputPeriodos.addEventListener('input', refreshRenewalPreview);
        refreshRenewalPreview();
    }

    function resizeAssinaturaCanvas() {
        if (!assinaturaCanvas || !assinaturaCtx) {
            return;
        }
        var rect = assinaturaCanvas.getBoundingClientRect();
        var ratio = window.devicePixelRatio || 1;
        assinaturaCanvas.width = Math.max(1, Math.floor(rect.width * ratio));
        assinaturaCanvas.height = Math.max(220, Math.floor(rect.height * ratio));
        assinaturaCtx.setTransform(ratio, 0, 0, ratio, 0, 0);
        assinaturaCtx.lineWidth = 2.4;
        assinaturaCtx.lineCap = 'round';
        assinaturaCtx.lineJoin = 'round';
        assinaturaCtx.strokeStyle = '#0f172a';
    }

    function pontoAssinatura(event) {
        var rect = assinaturaCanvas.getBoundingClientRect();
        var source = event.touches && event.touches.length ? event.touches[0] : event;
        return { x: source.clientX - rect.left, y: source.clientY - rect.top };
    }

    function iniciarAssinatura(event) {
        if (!assinaturaCtx) return;
        event.preventDefault();
        assinando = true;
        assinaturaPreenchida = true;
        var ponto = pontoAssinatura(event);
        assinaturaCtx.beginPath();
        assinaturaCtx.moveTo(ponto.x, ponto.y);
    }

    function moverAssinatura(event) {
        if (!assinando || !assinaturaCtx) return;
        event.preventDefault();
        var ponto = pontoAssinatura(event);
        assinaturaCtx.lineTo(ponto.x, ponto.y);
        assinaturaCtx.stroke();
    }

    function finalizarAssinatura() {
        assinando = false;
    }

    if (assinaturaCanvas) {
        resizeAssinaturaCanvas();
        window.addEventListener('resize', resizeAssinaturaCanvas);
        assinaturaCanvas.addEventListener('mousedown', iniciarAssinatura);
        assinaturaCanvas.addEventListener('mousemove', moverAssinatura);
        assinaturaCanvas.addEventListener('mouseup', finalizarAssinatura);
        assinaturaCanvas.addEventListener('mouseleave', finalizarAssinatura);
        assinaturaCanvas.addEventListener('touchstart', iniciarAssinatura, { passive: false });
        assinaturaCanvas.addEventListener('touchmove', moverAssinatura, { passive: false });
        assinaturaCanvas.addEventListener('touchend', finalizarAssinatura);
    }

    if (btnLimparAssinatura && assinaturaCanvas && assinaturaCtx) {
        btnLimparAssinatura.addEventListener('click', function () {
            assinaturaCtx.clearRect(0, 0, assinaturaCanvas.width, assinaturaCanvas.height);
            assinaturaPreenchida = false;
            if (assinaturaInput) assinaturaInput.value = '';
        });
    }

    if (formAssinatura && assinaturaCanvas && assinaturaInput) {
        formAssinatura.addEventListener('submit', function (event) {
            if (!assinaturaPreenchida) {
                event.preventDefault();
                alert('Assine no quadro antes de salvar.');
                return;
            }
            assinaturaInput.value = assinaturaCanvas.toDataURL('image/png');
        });
    }

    var formAceitarTermo = document.getElementById('formAceitarTermoMotoristaApp');
    if (formAceitarTermo) {
        formAceitarTermo.addEventListener('submit', function () {
            var btn = document.getElementById('btnAceitarTermoMotoristaApp') || this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...';
            }
        });
    }
});
</script>
@stop
