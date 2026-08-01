@extends('adminlte::page')

@section('title', $titulo ?? 'Minha reserva de motorista de app')

@section('content_header')
    <x-breadcrumb :breadcrumbs="[
        ['name' => 'Home', 'url' => route('home')],
        ['name' => 'Reservas', 'url' => route('reserva.index')],
        ['name' => 'Motorista APP #' . $registro->codigoReserva->codigo],
    ]" />
@stop

@section('content')
@php
    $solicitacao = $registro->solicitacao;
    $plano = $registro->planoMotoristaApp ?? $solicitacao?->planoMotoristaApp;
    $usuario = $registro->user;
    $codigoReserva = $registro->codigoReserva?->codigo ?? ('#' . $registro->id);
    $codigoCheckinOperacional = $registro->codigoReserva?->reserva_id ?: $codigoReserva;

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

    $dataInicio = $registro->data_inicio ? \Carbon\Carbon::parse($registro->data_inicio)->format('d/m/Y H:i') : '-';
    $dataFim = $registro->data_fim ? \Carbon\Carbon::parse($registro->data_fim)->format('d/m/Y H:i') : '-';
    $criadaEm = $registro->created_at ? \Carbon\Carbon::parse($registro->created_at)->format('d/m/Y H:i') : '-';
    $diasRestantes = $registro->data_fim ? now()->diffInDays(\Carbon\Carbon::parse($registro->data_fim), false) : null;

    $valorPeriodoAtual = (float) (
        $plano?->valor_periodo
        ?? $solicitacao?->valor_periodo
        ?? $registro->pagamentos?->first()?->valor_unitario
        ?? $registro->pagamentos?->first()?->valor_total
        ?? 0
    );
    $valorPeriodoContratado = (float) (
        $registro->valor_periodo_contrato
        ?: ($registro->valor_plano
        ?: $valorPeriodoAtual)
    );
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
    $opcionaisSelecionados = is_array($registro->opcionais_json ?? null) ? $registro->opcionais_json : [];
    $opcionaisMap = $plano?->opcionais?->keyBy('id') ?? collect();
    $dadosJson = is_array($registro->dados_json ?? null) ? $registro->dados_json : [];
    $documentosJson = is_array($registro->documentos_json ?? null) ? $registro->documentos_json : [];
    $solicitacaoStatus = $solicitacao?->status ? ucfirst(str_replace('_', ' ', (string) $solicitacao->status)) : 'Nao informada';
    $pagamentos = collect($registro->pagamentos ?? [])->sortByDesc('id')->values();
    $totalPagoPagamentos = (float) $pagamentos
        ->filter(fn ($p) => strtolower((string) ($p->status ?? '')) === 'pago')
        ->sum(fn ($p) => (float) ($p->valor_total ?? $p->valor_unitario ?? $p->valor ?? 0));
    $saldoPagamentoReserva = max(0, round($valorTotal - $totalPagoPagamentos, 2));
    $pagamentoEfetuado = $pagamentoEfetuado || $saldoPagamentoReserva <= 0;
    $pagamentosPendentes = $pagamentos->filter(function ($pagamento): bool {
        return strtolower((string) ($pagamento->status ?? '')) === 'pendente';
    })->values();
    $pagamentoPendenteAtual = $pagamentosPendentes->first();
    $temPagamentoPendente = !$pagamentoEfetuado && $pagamentosPendentes->isNotEmpty();
    $qtdPagamentosPendentes = $pagamentosPendentes->count();
    $pagamentoPendenteId = $pagamentoPendenteAtual?->id;
    $statusPagamento = $pagamentoEfetuado ? 'Pago' : 'Pagamento pendente';
    $pagamentoClass = $pagamentoEfetuado ? 'success' : 'warning';
    $pagamentoIcon = $pagamentoEfetuado ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';
    $multas = collect($registro->multas ?? [])->sortByDesc('id')->values();
    $sinistros = collect($registro->sinistros ?? [])->sortByDesc('id')->values();
    $renovacaoFechada = $plano?->periodo ? true : false;
    $empresaRef = $empresa ?? $registro->empresa ?? $solicitacao?->empresa;
    $veiculosDisponiveis = collect($veiculosDisponiveis ?? []);
    $veiculoAtual = $registro->veiculo;
    $estaEfetivada = !empty($registro->contrato_efetivado_em);
    $estaCheckin = !empty($registro->checkin_realizado_em) || !empty($contratoCheckinMotoristaApp);
    $estaCheckout = !empty($registro->checkout_realizado_em);
    $contratoSnapshot = is_array($registro->contrato_snapshot_json ?? null) ? $registro->contrato_snapshot_json : [];
    $contratoAssinado = !empty(data_get($contratoSnapshot, 'assinatura')) || ($contratoCheckinMotoristaApp && $contratoCheckinMotoristaApp->assinado === 'S');
    $contratoAssinadoEm = data_get($contratoSnapshot, 'documento.data_assinatura') ?? data_get($contratoSnapshot, 'contrato.assinado_em');
    $temContratoPdf = !empty($registro->contrato_pdf_path);
    $contratoCheckinOperacional = $contratoCheckinMotoristaApp ?? null;
    $contratoCheckoutOperacional = $contratoCheckoutMotoristaApp ?? null;
    $calcaoOperacional = $calcaoMotoristaApp ?? null;
    $codigoCalcaoMotoristaApp = $registro->codigoReserva?->codigo ?? ($codigoReserva ?? $codigo);
    $reservaOperacionalCheckinId = (int) ($reservaOperacionalCheckinId ?? data_get($documentosJson, 'checkin.reserva_operacional_id', 0));
    $codigoOperacionalCheckin = $contratoCheckinOperacional?->reserva?->codigoReserva?->codigo
        ?? data_get($documentosJson, 'checkin.reserva_operacional_codigo')
        ?? ($reservaOperacionalCheckinId ? ('RES-' . $reservaOperacionalCheckinId) : null);
    $temContratoCheckin = !empty($contratoCheckinOperacional);
    $checkinContratoAssinado = $temContratoCheckin && $contratoCheckinOperacional->assinado === 'S';
    $checkinPdfDisponivel = $temContratoCheckin && !empty($contratoCheckinOperacional->pdf_path);
    $termoConfirmacaoAceitoEm = data_get($documentosJson, 'termo_confirmacao.aceito_em');
    $termoConfirmacaoAceito = !empty($termoConfirmacaoAceitoEm);
    $assinaturaCheckout = data_get($documentosJson, 'checkout.assinatura') ?? ($contratoCheckoutOperacional->assinatura ?? null);
    $checkoutAssinado = !empty($assinaturaCheckout);
    $calcaoNecessario = $calcao > 0;
    $calcaoStatusOperacional = (string) ($calcaoOperacional->status ?? data_get($documentosJson, 'calcao.status', ''));
    $calcaoConcluido = !$calcaoNecessario || in_array($calcaoStatusOperacional, [
        \App\Models\Calcao::STATUS_AUTORIZADO,
        \App\Models\Calcao::STATUS_PARCIALMENTE_CAPTURADO,
        \App\Models\Calcao::STATUS_CAPTURADO,
        \App\Models\Calcao::STATUS_CANCELADO,
    ], true);
    $calcaoEtapaLabel = !$calcaoNecessario
        ? 'Calção não exigido'
        : ($calcaoConcluido ? 'Calção confirmado' : 'Calção pendente');
    $podeAceitarTermo = $pagamentoEfetuado && !$termoConfirmacaoAceito && !$estaCheckin && $statusRaw !== 'cancelada';
    $podeAssinarContrato = $estaCheckin && $temContratoPdf && !$contratoAssinado;
    $podeRegistrarCheckin = $pagamentoEfetuado && $termoConfirmacaoAceito && $calcaoConcluido && !$temContratoCheckin && !$estaCheckin && $statusRaw !== 'cancelada';
    $podeRegistrarCheckout = $estaCheckin && $checkinContratoAssinado && !$estaCheckout;
    $podeRenovar = $contratoAssinado && $statusRaw !== 'cancelada' && !$estaCheckout;
    $limiteCancelamento = $registro->created_at ? \Carbon\Carbon::parse($registro->created_at)->addHours(24) : null;
    $podeCancelar = !$estaEfetivada
        && strtolower((string) ($registro->status ?? '')) !== 'cancelada'
        && $limiteCancelamento
        && now()->lessThanOrEqualTo($limiteCancelamento);
    $inicioVigencia = $registro->data_retirada_real ?? $registro->data_inicio;
    $fimVigencia = $registro->data_fim;
    $inicioVigenciaFormatado = $inicioVigencia ? \Carbon\Carbon::parse($inicioVigencia)->format('d/m/Y H:i') : '-';
    $fimVigenciaFormatado = $fimVigencia ? \Carbon\Carbon::parse($fimVigencia)->format('d/m/Y H:i') : '-';
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
    $avariasCliente = collect($avariasMotoristaApp ?? []);
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
        background: #fff; border: 1px solid var(--ma-line); border-radius: 18px;
        overflow: hidden; box-shadow: 0 10px 24px rgba(15,23,42,.06);
    }
    .ma-card__head {
        padding: 14px 18px;
        border-bottom: 1px solid #eef2f7;
        background: linear-gradient(120deg, #f8fbff, #f1f6ff);
        display: flex; align-items: center; gap: 10px;
    }
    .ma-card__icon {
        width: 40px; height: 40px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        background: rgba(37,99,235,.12); color: var(--ma-accent);
    }
    .ma-card__title { font-size: 1rem; font-weight: 800; color: var(--ma-ink); }
    .ma-card__sub { font-size: .78rem; color: var(--ma-muted); }
    .ma-card__body { padding: 18px; }
    .ma-summary {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }
    .ma-info {
        border: 1px solid var(--ma-line); border-radius: 14px;
        background: #f8fbff; padding: 12px 14px;
    }
    .ma-info__label {
        font-size: .68rem; font-weight: 800; color: #7483a0;
        text-transform: uppercase; letter-spacing: .08em; margin-bottom: 4px;
    }
    .ma-info__value { font-size: .95rem; font-weight: 700; color: var(--ma-ink); line-height: 1.35; }
    .ma-legend {
        display: grid; grid-template-columns: 1fr auto 1fr; gap: 10px;
        align-items: center; border: 1px solid var(--ma-line);
        border-radius: 16px; padding: 16px; background: linear-gradient(135deg, #f8fbff, #f1f6ff);
    }
    .ma-stop { display: flex; align-items: flex-start; gap: 10px; }
    .ma-stop--right { flex-direction: row-reverse; text-align: right; }
    .ma-dot {
        width: 11px; height: 11px; border-radius: 50%; margin-top: 4px; flex-shrink: 0;
    }
    .ma-dot--a { background: #10b981; box-shadow: 0 0 0 4px rgba(16,185,129,.15); }
    .ma-dot--b { background: #ef4444; box-shadow: 0 0 0 4px rgba(239,68,68,.14); }
    .ma-label {
        font-size: .68rem; font-weight: 800; color: #94a3b8;
        text-transform: uppercase; letter-spacing: .08em;
    }
    .ma-place { font-size: .98rem; font-weight: 800; color: var(--ma-ink); margin-top: 3px; }
    .ma-date { font-size: .82rem; color: #1d4ed8; font-weight: 700; margin-top: 2px; }
    .ma-mid { display: flex; flex-direction: column; align-items: center; gap: 4px; }
    .ma-mid__line { width: 1px; height: 16px; background: linear-gradient(180deg, #c7d7f4, #93c5fd); }
    .ma-mid__pill {
        background: #1e40af; color: #fff; border-radius: 999px;
        font-size: .68rem; font-weight: 800; padding: 4px 9px;
    }
    .ma-list { display: grid; gap: 10px; }
    .ma-list__item {
        border: 1px solid var(--ma-line); border-radius: 14px; background: #fff; padding: 12px 14px;
    }
    .ma-list__label {
        font-size: .68rem; font-weight: 800; color: #7483a0;
        text-transform: uppercase; letter-spacing: .08em; margin-bottom: 3px;
    }
    .ma-list__value { font-size: .92rem; color: var(--ma-ink); font-weight: 700; line-height: 1.45; }
    .ma-price {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: 12px;
        margin-top: 14px;
    }
    .ma-price__box {
        border: 1px solid var(--ma-line); border-radius: 14px; background: #f8fbff; padding: 12px 14px;
    }
    .ma-price__label {
        font-size: .68rem; font-weight: 800; color: #7483a0;
        text-transform: uppercase; letter-spacing: .08em; margin-bottom: 4px;
    }
    .ma-price__value { font-size: 1.02rem; font-weight: 900; color: var(--ma-ink); }
    .ma-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }
    .ma-btn {
        display: inline-flex; align-items: center; gap: 8px;
        border-radius: 999px; padding: 10px 16px; font-weight: 800;
        text-decoration: none; border: 1px solid var(--ma-line); cursor: pointer;
    }
    .ma-btn--primary { background: var(--ma-accent); color: #fff; border-color: var(--ma-accent); }
    .ma-btn--ghost { background: #fff; color: var(--ma-ink); }
    .ma-btn--success { background: #059669; color: #fff; border-color: #059669; }
    .ma-btn--warning { background: #f59e0b; color: #fff; border-color: #f59e0b; }
    .ma-btn--danger { background: #dc2626; color: #fff; border-color: #dc2626; }
    .ma-btn--disabled { background: #f1f5f9; color: #94a3b8; border-color: #e2e8f0; cursor: not-allowed; }
    .ma-action-panel {
        margin-bottom: 16px;
        border: 1px solid #dbeafe;
        border-radius: 20px;
        overflow: hidden;
        background: linear-gradient(135deg, #f8fbff 0%, #ffffff 55%, #eff6ff 100%);
        box-shadow: 0 12px 30px rgba(15,23,42,.07);
    }
    .ma-action-panel__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 16px 18px;
        border-bottom: 1px solid #e5eefc;
    }
    .ma-action-panel__title {
        margin: 0;
        font-size: 1rem;
        font-weight: 900;
        color: var(--ma-ink);
    }
    .ma-action-panel__text {
        margin: 2px 0 0;
        color: var(--ma-muted);
        font-size: .82rem;
    }
    .ma-action-panel__body {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        padding: 14px 18px 18px;
    }
    .ma-flow {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        padding: 0 18px 12px;
    }
    .ma-flow__step {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border: 1px solid #e2e8f0;
        border-radius: 999px;
        background: #f8fafc;
        padding: 4px 10px;
        font-size: .72rem;
        white-space: nowrap;
    }
    .ma-flow__step.is-done { border-color: #bbf7d0; background: #f0fdf4; }
    .ma-flow__step.is-current { border-color: #fde68a; background: #fffbeb; }
    .ma-flow__icon {
        font-size: .65rem;
    }
    .ma-flow__step.is-done .ma-flow__icon { color: #16a34a; }
    .ma-flow__step.is-current .ma-flow__icon { color: #d97706; }
    .ma-flow__step:not(.is-done):not(.is-current) .ma-flow__icon { color: #94a3b8; }
    .ma-flow__label { font-weight: 800; color: #64748b; }
    .ma-flow__value { font-weight: 700; color: var(--ma-ink); }
    .ma-sign-pad {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        background: #fff;
        width: 100%;
        min-height: 210px;
        touch-action: none;
    }
    .ma-note {
        border: 1px solid #dbeafe; background: #f8fbff; color: #334155;
        border-radius: 14px; padding: 14px 16px; font-size: .92rem; line-height: 1.6;
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
        .ma-flow { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 768px) {
        .ma-summary, .ma-price, .ma-subgrid { grid-template-columns: 1fr; }
        .ma-mini { grid-template-columns: 1fr 1fr; }
        .ma-flow { grid-template-columns: 1fr; }
        .ma-action-panel__head { align-items: flex-start; flex-direction: column; }
        .ma-legend { grid-template-columns: 1fr; }
    }

    /* ═══════════════════════════
       TABS WRAPPER (Modern & Robust Reset)
    ═══════════════════════════ */
    .rs-tabs-wrapper {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(15, 23, 42, 0.05);
        margin-top: 10px;
    }

    ul.rs-nav-tabs,
    .rs-nav-tabs.nav-tabs {
        background: #f8fafc !important;
        border: none !important;
        border-bottom: 1px solid #e2e8f0 !important;
        padding: 0 8px !important;
        display: flex !important;
        flex-wrap: nowrap !important;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        margin-bottom: 0 !important;
    }

    ul.rs-nav-tabs::-webkit-scrollbar { display: none; }

    ul.rs-nav-tabs .nav-item {
        flex-shrink: 0 !important;
        margin-bottom: 0 !important;
    }

    ul.rs-nav-tabs .nav-link {
        display: flex !important;
        align-items: center !important;
        gap: 5px !important;
        padding: 8px 10px !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        color: #64748b !important;
        background: transparent !important;
        border: none !important;
        border-bottom: 2px solid transparent !important;
        border-radius: 0 !important;
        white-space: nowrap !important;
        transition: all 0.2s ease !important;
        margin-bottom: -1px !important;
    }

    ul.rs-nav-tabs .nav-link:hover,
    ul.rs-nav-tabs .nav-link:focus {
        color: #2563eb !important;
        background: transparent !important;
        border-color: transparent !important;
        border-bottom-color: #bfdbfe !important;
    }

    ul.rs-nav-tabs .nav-link.active,
    ul.rs-nav-tabs .nav-item.show .nav-link {
        color: #2563eb !important;
        background: transparent !important;
        border: none !important;
        border-bottom: 2px solid #2563eb !important;
        font-weight: 700 !important;
    }

    ul.rs-nav-tabs .nav-link i {
        font-size: 0.75rem !important;
    }

    .rs-tab-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 16px;
        height: 16px;
        padding: 0 5px;
        border-radius: 999px;
        font-size: 0.6rem;
        font-weight: 800;
        line-height: 1;
        margin-left: 3px;
    }

    .rs-tab-badge--green  { background: #dcfce7; color: #15803d; }
    .rs-tab-badge--amber  { background: #fef3c7; color: #b45309; }
    .rs-tab-badge--red    { background: #fee2e2; color: #dc2626; }
    .rs-tab-badge--slate  { background: #f1f5f9; color: #475569; }

    .rs-tab-content {
        padding: 14px;
        background: #fff;
    }
</style>

<div class="ma-page">
    <div class="ma-hero">
        <div class="ma-hero__inner">
            <div>
                <h1 class="ma-hero__title">{{ $titulo ?? 'Reserva de motorista de app' }}</h1>
                <p class="ma-hero__text">
                    Acompanhe a vigencia, contrato, check-in, check-out, pagamentos, multas e sinistros ligados a esta reserva.
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

    <div class="ma-action-panel">
        <div class="ma-action-panel__head">
            <div>
                <h2 class="ma-action-panel__title">Acoes da reserva</h2>
                <p class="ma-action-panel__text">
                    Efetive a locacao, assine o contrato e registre check-in/check-out seguindo a ordem operacional.
                </p>
            </div>
            <span class="ma-status is-{{ $statusClass }}" style="color:black">
                <i class="{{ $statusIcon }}"></i> {{ $statusLabel }}
            </span>
        </div>
        <div class="ma-action-panel__body">
            @if(!$pagamentoEfetuado && $pagamentoPendenteAtual)
                <a href="{{ route('reserva.pagamento.efetuar', ['id' => $codigoReserva]) }}" class="ma-btn ma-btn--primary">
                    <i class="fas fa-credit-card"></i>
                    <span>Pagar</span>
                </a>
            @endif

            @if(!$pagamentoEfetuado && $statusRaw !== 'cancelada')
                <a href="{{ route('reserva-motorista-app.edit', $registro->id) }}" class="ma-btn ma-btn--ghost">
                    <i class="fas fa-pen"></i>
                    <span>Editar</span>
                </a>
            @endif

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

            @if($pagamentoEfetuado && $termoConfirmacaoAceito && !$calcaoConcluido && !$estaCheckin)
                <a href="{{ route('reserva.calcao', ['codigo' => $codigoCalcaoMotoristaApp]) }}" class="ma-btn ma-btn--primary" title="Registrar calcao para liberar o check-in">
                    <i class="fas fa-lock"></i>
                    <span>Registrar Calcao</span>
                </a>
            @endif

            @if($podeRegistrarCheckin)
                <a href="{{ route('reserva.checkin', ['codigo' => $codigoCheckinOperacional]) }}" class="ma-btn ma-btn--success">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Fazer check-in</span>
                </a>
            @elseif(false && $pagamentoEfetuado && $termoConfirmacaoAceito && !$calcaoConcluido && !$estaCheckin)
                <span class="ma-btn ma-btn--disabled" title="Finalize o calção para liberar o check-in">
                    <i class="fas fa-lock"></i>
                    <span>Calção pendente</span>
                </span>
            @elseif($estaCheckin)
                <span class="ma-btn ma-btn--disabled">
                    <i class="fas fa-clipboard-check"></i>
                    <span>Check-in registrado</span>
                </span>
            @elseif($estaEfetivada && !$contratoAssinado)
                <span class="ma-btn ma-btn--disabled" title="Assine o contrato para liberar o check-in">
                    <i class="fas fa-lock"></i>
                    <span>Check-in bloqueado</span>
                </span>
            @endif

            @if($temContratoCheckin && !$checkinContratoAssinado && $codigoCheckinOperacional)
                <a href="{{ route('reserva.assinarContratoCheckin', ['codigo' => $codigoCheckinOperacional, 'retorno' => $codigoReserva]) }}" class="ma-btn ma-btn--success">
                    <i class="fas fa-signature"></i>
                    <span>Assinar contrato de check-in</span>
                </a>
            @elseif($checkinContratoAssinado)
                <span class="ma-btn ma-btn--disabled">
                    <i class="fas fa-signature"></i>
                    <span>Check-in assinado</span>
                </span>
            @endif

            @if($temContratoCheckin && $codigoCheckinOperacional)
                <a href="{{ route('reserva.verContratoCheckin', ['codigo' => $codigoCheckinOperacional]) }}" target="_blank" class="ma-btn ma-btn--ghost">
                    <i class="fas fa-file-pdf"></i>
                    <span>Ver PDF do check-in</span>
                </a>
            @endif

            @if($estaCheckout)
                <a href="{{ route('reserva.minhas.vercontratocheckout', ['codigo' => $codigoReserva]) }}" target="_blank" class="ma-btn ma-btn--ghost">
                    <i class="fas fa-file-pdf"></i>
                    <span>Ver PDF do check-out</span>
                </a>
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
            @elseif($contratoAssinado)
                <span class="ma-btn ma-btn--disabled">
                    <i class="fas fa-signature"></i>
                    <span>Contrato assinado</span>
                </span>
            @endif

            @if($podeRegistrarCheckout)
                <a href="{{ route('reserva.checkout.vistoria', ['codigo' => $codigoReserva]) }}" class="ma-btn ma-btn--warning">
                    <i class="fas fa-flag-checkered"></i>
                    <span>Fazer check-out</span>
                </a>
            @elseif($estaCheckout)
                @php
                    $checkoutDocs = is_array($registro->documentos_json ?? null)
                        ? data_get($registro->documentos_json, 'checkout', [])
                        : [];
                    $assinaturaCo = data_get($checkoutDocs, 'assinatura');
                @endphp
                
                @if(!$assinaturaCo)
                    <a href="{{ route('reserva.assinarContratoCheckout', ['codigo' => $codigoReserva]) }}" class="ma-btn ma-btn--success">
                        <i class="fas fa-signature"></i>
                        <span>Assinar Devolução</span>
                    </a>
                @else
                    <span class="ma-btn ma-btn--disabled">
                        <i class="fas fa-flag-checkered"></i>
                        <span>Check-out finalizado</span>
                    </span>
                    {{-- Opcionalmente pode gerar PDF se houver rota configurada --}}
                @endif
            @endif

            @if($podeRenovar)
                <a href="{{ route('reserva.renovacao.exibir', ['codigo' => $codigoReserva]) }}" class="ma-btn ma-btn--primary">
                    <i class="fas fa-arrows-rotate"></i>
                    <span>Renovar plano</span>
                </a>
            @elseif($estaCheckout)
                <span class="ma-btn ma-btn--disabled" title="Nao e possivel renovar apos o checkout">
                    <i class="fas fa-lock"></i>
                    <span>Renovacao bloqueada</span>
                </span>
            @endif

            @if($podeCancelar)
                <button type="button" class="ma-btn ma-btn--danger" data-toggle="modal" data-target="#modalCancelarMotoristaApp">
                    <i class="fas fa-ban"></i>
                    <span>Cancelar</span>
                </button>
            @endif

            <a href="{{ route('reserva.index') }}" class="ma-btn ma-btn--ghost">
                <i class="fas fa-arrow-left"></i>
                <span>Voltar</span>
            </a>
        </div>
        <div class="ma-flow">
            <div class="ma-flow__step {{ $pagamentoEfetuado ? 'is-done' : 'is-current' }}">
                <i class="ma-flow__icon fas {{ $pagamentoEfetuado ? 'fa-check-circle' : 'fa-clock' }}"></i>
                <span class="ma-flow__label">1.</span>
                <span class="ma-flow__value">{{ $pagamentoEfetuado ? 'Pagamento' : 'Pgto pendente' }}</span>
            </div>
            <div class="ma-flow__step {{ $termoConfirmacaoAceito ? 'is-done' : ($pagamentoEfetuado ? 'is-current' : '') }}">
                <i class="ma-flow__icon fas {{ $termoConfirmacaoAceito ? 'fa-check-circle' : 'fa-clock' }}"></i>
                <span class="ma-flow__label">2.</span>
                <span class="ma-flow__value">{{ $termoConfirmacaoAceito ? 'Termo' : 'Termo pendente' }}</span>
            </div>
            <div class="ma-flow__step {{ $calcaoConcluido ? 'is-done' : ($termoConfirmacaoAceito ? 'is-current' : '') }}">
                <i class="ma-flow__icon fas {{ $calcaoConcluido ? 'fa-check-circle' : 'fa-clock' }}"></i>
                <span class="ma-flow__label">3.</span>
                <span class="ma-flow__value">Caução</span>
            </div>
            <div class="ma-flow__step {{ $estaCheckin ? 'is-done' : ($calcaoConcluido && $termoConfirmacaoAceito ? 'is-current' : '') }}">
                <i class="ma-flow__icon fas {{ $estaCheckin ? 'fa-check-circle' : 'fa-clock' }}"></i>
                <span class="ma-flow__label">4.</span>
                <span class="ma-flow__value">Check-in</span>
            </div>
            <div class="ma-flow__step {{ $checkinContratoAssinado ? 'is-done' : ($estaCheckin ? 'is-current' : '') }}">
                <i class="ma-flow__icon fas {{ $checkinContratoAssinado ? 'fa-check-circle' : 'fa-clock' }}"></i>
                <span class="ma-flow__label">5.</span>
                <span class="ma-flow__value">Contrato</span>
            </div>
            <div class="ma-flow__step {{ $estaCheckout ? 'is-done' : ($checkinContratoAssinado ? 'is-current' : '') }}">
                <i class="ma-flow__icon fas {{ $estaCheckout ? 'fa-check-circle' : 'fa-clock' }}"></i>
                <span class="ma-flow__label">6.</span>
                <span class="ma-flow__value">Check-out</span>
            </div>
            <div class="ma-flow__step {{ $checkoutAssinado ? 'is-done' : ($estaCheckout ? 'is-current' : '') }}">
                <i class="ma-flow__icon fas {{ $checkoutAssinado ? 'fa-check-circle' : 'fa-clock' }}"></i>
                <span class="ma-flow__label">7.</span>
                <span class="ma-flow__value">Assinatura</span>
            </div>
        </div>
    </div>

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

    {{-- ══════════════════════════════════════════
         ABAS PRINCIPAIS
    ══════════════════════════════════════════ --}}
    <div class="rs-tabs-wrapper">
        <ul class="nav nav-tabs rs-nav-tabs" id="reservaMotoristaAppTabs" role="tablist">
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

                    @if($temContratoCheckin)
                        <span class="rs-tab-badge {{ $checkinContratoAssinado ? 'rs-tab-badge--green' : 'rs-tab-badge--amber' }}">
                            {{ $checkinContratoAssinado ? 'Assinado' : 'Pendente' }}
                        </span>
                    @elseif($podeRegistrarCheckin)
                        <span class="rs-tab-badge rs-tab-badge--amber">Pendente</span>
                    @endif
                </a>
            </li>

            @if($temContratoCheckin || $estaCheckin)
                <li class="nav-item">
                    <a class="nav-link" id="tab-checkout" data-toggle="tab" href="#pane-checkout" role="tab">
                        <i class="fas fa-car-crash"></i>
                        <span>Check-out</span>

                        @if($estaCheckout)
                            <span class="rs-tab-badge {{ $checkoutAssinado ? 'rs-tab-badge--green' : 'rs-tab-badge--amber' }}">
                                {{ $checkoutAssinado ? 'Assinado' : 'Pendente' }}
                            </span>
                        @elseif($podeRegistrarCheckout)
                            <span class="rs-tab-badge rs-tab-badge--amber">Pendente</span>
                        @endif
                    </a>
                </li>
            @endif

            <li class="nav-item">
                <a class="nav-link" id="tab-financeiro" data-toggle="tab" href="#pane-financeiro" role="tab">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Financeiro</span>

                    @if($temPagamentoPendente)
                        <span class="rs-tab-badge rs-tab-badge--amber">{{ $qtdPagamentosPendentes }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" id="tab-multas" data-toggle="tab" href="#pane-multas" role="tab">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Multas</span>

                    @if($multas->isNotEmpty())
                        <span class="rs-tab-badge rs-tab-badge--red">{{ $multas->count() }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" id="tab-sinistros" data-toggle="tab" href="#pane-sinistros" role="tab">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>Sinistros</span>

                    @if($sinistros->isNotEmpty())
                        <span class="rs-tab-badge rs-tab-badge--red">{{ $sinistros->count() }}</span>
                    @endif
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" id="tab-historico" data-toggle="tab" href="#pane-historico" role="tab">
                    <i class="fas fa-history"></i>
                    <span>Histórico</span>
                </a>
            </li>
        </ul>

        <div class="tab-content rs-tab-content" id="reservaMotoristaAppTabContent">
            {{-- TAB 1 — VISÃO GERAL --}}
            <div class="tab-pane fade show active" id="pane-visao-geral" role="tabpanel">
                @include('reserva.partials.motorista_app_visao_geral')
            </div>

            {{-- TAB 2 — CHECK-IN --}}
            <div class="tab-pane fade" id="pane-checkin" role="tabpanel">
                @include('reserva.partials.motorista_app_checkin')
            </div>

            {{-- TAB 3 — CHECK-OUT --}}
            <div class="tab-pane fade" id="pane-checkout" role="tabpanel">
                @include('reserva.partials.motorista_app_checkout')
            </div>

            {{-- TAB INTERNA — DANOS/AVARIAS --}}
            <div class="tab-pane fade" id="pane-danos" role="tabpanel">
                @include('reserva.partials.motorista_app_danos')
            </div>

            {{-- TAB 4 — FINANCEIRO --}}
            <div class="tab-pane fade" id="pane-financeiro" role="tabpanel">
                @include('reserva.partials.motorista_app_financeiro')
            </div>

            {{-- TAB 5 — MULTAS --}}
            <div class="tab-pane fade" id="pane-multas" role="tabpanel">
                @include('reserva.partials.motorista_app_multas')
            </div>

            {{-- TAB 6 — SINISTROS --}}
            <div class="tab-pane fade" id="pane-sinistros" role="tabpanel">
                @include('reserva.partials.motorista_app_sinistros')
            </div>

            {{-- TAB 7 — HISTÓRICO --}}
            <div class="tab-pane fade" id="pane-historico" role="tabpanel">
                @include('reserva.partials.motorista_app_historico')
            </div>
        </div>{{-- /tab-content --}}
    </div>{{-- /rs-tabs-wrapper --}}

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

<div class="modal fade" id="modalTermoConfirmacaoMotoristaApp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="formAceitarTermoMotoristaAppAdmin" action="{{ route('reserva.minhas.motorista-app.aceitar-termo', ['id' => $registro->id]) }}" method="POST">
                @csrf
                <div class="modal-header" style="background:#0f172a;color:#fff;">
                    <h5 class="modal-title"><i class="fas fa-file-signature mr-1"></i> {{ trim((string) ($termoAceite?->titulo ?? 'Termo de confirmacao da reserva')) }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    @php
                        $textoTermoAceite = trim((string) ($termoAceite?->texto ?? ''));
                        $mensagemTermoAceite = $textoTermoAceite !== '' ? $textoTermoAceite : 'Este termo confirma a reserva e autoriza a preparacao posterior do contrato de locacao no check-in.';
                    @endphp
                    <div class="text-muted mb-2">
                        <i class="fas fa-info-circle mr-1"></i>
                        {!! nl2br(e($mensagemTermoAceite)) !!}
                    </div>
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="aceiteTermoMotoristaApp" name="aceite_termo" value="1" required>
                        <label class="custom-control-label font-weight-bold" for="aceiteTermoMotoristaApp">
                            Li e aceito o termo de confirmacao da reserva.
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnAceitarTermoMotoristaAppAdmin" class="btn btn-success">
                        <i class="fas fa-check mr-1"></i> Aceitar termo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditarMotoristaApp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#0f172a;color:#fff;">
                <h5 class="modal-title"><i class="fas fa-pen mr-1"></i> Editar reserva</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    A edicao completa da reserva de motorista APP ainda nao possui tela propria. Por enquanto, revise a solicitacao/cadastro antes de confirmar o pagamento.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Voltar</button>
            </div>
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
                        <i class="fas fa-info-circle mr-1"></i>
                        Assine no quadro abaixo. Depois de salvar, o contrato PDF sera regenerado com a assinatura.
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

<div class="modal fade" id="modalCheckoutMotoristaApp" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('reserva.checkout.vistoria', ['codigo' => $codigoReserva]) }}" method="POST">
                @csrf
                <div class="modal-header" style="background:#92400e;color:#fff;">
                    <h5 class="modal-title"><i class="fas fa-flag-checkered mr-1"></i> Registrar check-out</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        Confirme a quilometragem e o nivel de combustivel final do veiculo.
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Km final</label>
                        <input type="number" name="quilometragem_final" class="form-control" min="{{ (int) ($registro->quilometragem_inicial ?? 0) }}" step="1" value="{{ old('quilometragem_final', $registro->quilometragem_final ?? '') }}" required>
                        <small class="text-muted">Km inicial: {{ number_format((int) ($registro->quilometragem_inicial ?? 0), 0, ',', '.') }} km</small>
                    </div>
                    <div class="form-group mb-0">
                        <label class="font-weight-bold">Combustivel final</label>
                        <select name="nivel_combustivel_final" class="form-control" required>
                            <option value="">Selecione</option>
                            @foreach(['Reserva','1/8','1/4','3/8','1/2','5/8','3/4','7/8','Cheio'] as $nivel)
                                <option value="{{ $nivel }}" @selected(old('nivel_combustivel_final', $registro->nivel_combustivel_final ?? '') === $nivel)>{{ $nivel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-check mr-1"></i> Registrar check-out
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVerFotoCheckin" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title font-weight-bold" id="modalVerFotoCheckinTitulo" style="font-size: 1rem;">Foto do Check-in</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-2 text-center bg-black d-flex align-items-center justify-content-center" style="min-height: 350px; background: #0f172a;">
                <img id="modalVerFotoCheckinImg" src="" alt="Foto expandida" class="img-fluid rounded" style="max-height: 75vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalVerTermoAssinado" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            @php
                $termoTituloCongelado = data_get($documentosJson, 'termo_confirmacao.titulo')
                    ?? $termoAceite?->titulo
                    ?? 'Termo de confirmação da reserva';
                $termoTextoCongelado = data_get($documentosJson, 'termo_confirmacao.texto')
                    ?? $termoAceite?->texto
                    ?? 'Este termo confirma a reserva e autoriza a preparação posterior do contrato de locação no check-in.';
            @endphp
            <div class="modal-header" style="background:#0f172a;color:#fff;">
                <h5 class="modal-title"><i class="fas fa-file-contract mr-2"></i> {{ $termoTituloCongelado }}</h5>
                <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success d-flex align-items-center mb-3">
                    <i class="fas fa-check-circle fa-2x mr-3"></i>
                    <div>
                        <strong>Termo Assinado Digitalmente</strong>
                        <div class="small">
                            Aceito em {{ $termoConfirmacaoAceitoEm ? \Carbon\Carbon::parse($termoConfirmacaoAceitoEm)->format('d/m/Y \à\s H:i:s') : '-' }}
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light rounded border mb-3" style="max-height: 400px; overflow-y: auto; font-size: 0.9rem; line-height: 1.6; white-space: pre-line; color: #334155;">
                    {!! e($termoTextoCongelado) !!}
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>
function openPhotoModal(url, title) {
    if (!url) return;
    document.getElementById('modalVerFotoCheckinImg').src = url;
    document.getElementById('modalVerFotoCheckinTitulo').innerText = title || 'Visualização da Foto';
    $('#modalVerFotoCheckin').modal('show');
}

document.addEventListener('DOMContentLoaded', function () {
    var formConfirmar = document.getElementById('formConfirmarPagamentoMotoristaApp');
    var valorConfirmacao = document.getElementById('maValorConfirmacao');
    var inputPeriodos = document.getElementById('maRenovarPeriodos');
    var novaVigencia = document.getElementById('maNovaVigencia');
    var proximoCiclo = document.getElementById('maProximoCiclo');
    var totalRenovacao = document.getElementById('maTotalRenovacao');
    var historicoPagamentos = document.getElementById('historico-pagamentos');
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

    var assinaturaCanvas = document.getElementById('maSignatureCanvas');
    var assinaturaInput = document.getElementById('maAssinaturaInput');
    var formAssinatura = document.getElementById('formAssinarContratoMotoristaApp');
    var btnLimparAssinatura = document.getElementById('maLimparAssinatura');
    var assinaturaCtx = assinaturaCanvas ? assinaturaCanvas.getContext('2d') : null;
    var assinando = false;
    var assinaturaPreenchida = false;

    function resizeAssinaturaCanvas() {
        if (!assinaturaCanvas || !assinaturaCtx) {
            return;
        }

        var rect = assinaturaCanvas.getBoundingClientRect();
        var ratio = window.devicePixelRatio || 1;
        assinaturaCanvas.width = Math.max(1, Math.floor(rect.width * ratio));
        assinaturaCanvas.height = Math.max(210, Math.floor(rect.height * ratio));
        assinaturaCtx.setTransform(ratio, 0, 0, ratio, 0, 0);
        assinaturaCtx.lineWidth = 2.4;
        assinaturaCtx.lineCap = 'round';
        assinaturaCtx.lineJoin = 'round';
        assinaturaCtx.strokeStyle = '#0f172a';
    }

    function pontoAssinatura(event) {
        var rect = assinaturaCanvas.getBoundingClientRect();
        var touch = event.touches && event.touches.length ? event.touches[0] : event;
        return {
            x: touch.clientX - rect.left,
            y: touch.clientY - rect.top
        };
    }

    function iniciarAssinatura(event) {
        if (!assinaturaCtx) {
            return;
        }

        event.preventDefault();
        assinando = true;
        assinaturaPreenchida = true;
        var ponto = pontoAssinatura(event);
        assinaturaCtx.beginPath();
        assinaturaCtx.moveTo(ponto.x, ponto.y);
    }

    function moverAssinatura(event) {
        if (!assinando || !assinaturaCtx) {
            return;
        }

        event.preventDefault();
        var ponto = pontoAssinatura(event);
        assinaturaCtx.lineTo(ponto.x, ponto.y);
        assinaturaCtx.stroke();
    }

    function finalizarAssinatura() {
        assinando = false;
    }

    if (assinaturaCanvas) {
        $('#modalAssinarContratoMotoristaApp').on('shown.bs.modal', function () {
            resizeAssinaturaCanvas();
        });
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
            if (assinaturaInput) {
                assinaturaInput.value = '';
            }
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

    /* ── Lógica do Canvas de Assinatura de Devolução (Check-out) ── */
    var devCoCanvas = document.getElementById('maDevolucaoSignatureCanvas');
    var devCoInput = document.getElementById('maDevolucaoAssinaturaInput');
    var devCoForm = document.getElementById('formAssinarTermoDevolucaoMotoristaApp');
    var devCoBtnClean = document.getElementById('maDevolucaoLimparAssinatura');
    var devCoCtx = devCoCanvas ? devCoCanvas.getContext('2d') : null;
    var devCoDrawing = false;
    var devCoSigned = false;

    function resizeDevCoCanvas() {
        if (!devCoCanvas || !devCoCtx) return;
        var rect = devCoCanvas.getBoundingClientRect();
        var ratio = window.devicePixelRatio || 1;
        devCoCanvas.width = Math.max(1, Math.floor(rect.width * ratio));
        devCoCanvas.height = Math.max(200, Math.floor(rect.height * ratio));
        devCoCtx.setTransform(ratio, 0, 0, ratio, 0, 0);
        devCoCtx.lineWidth = 2.4;
        devCoCtx.lineCap = 'round';
        devCoCtx.lineJoin = 'round';
        devCoCtx.strokeStyle = '#0f172a';
    }

    function getDevCoPoint(event) {
        if (!devCoCanvas) return { x: 0, y: 0 };
        var rect = devCoCanvas.getBoundingClientRect();
        var touch = event.touches && event.touches.length ? event.touches[0] : event;
        return {
            x: touch.clientX - rect.left,
            y: touch.clientY - rect.top
        };
    }

    function startDevCoDrawing(event) {
        if (!devCoCtx) return;
        event.preventDefault();
        devCoDrawing = true;
        devCoSigned = true;
        var point = getDevCoPoint(event);
        devCoCtx.beginPath();
        devCoCtx.moveTo(point.x, point.y);
    }

    function moveDevCoDrawing(event) {
        if (!devCoDrawing || !devCoCtx) return;
        event.preventDefault();
        var point = getDevCoPoint(event);
        devCoCtx.lineTo(point.x, point.y);
        devCoCtx.stroke();
    }

    function stopDevCoDrawing() {
        devCoDrawing = false;
    }

    if (devCoCanvas) {
        setTimeout(resizeDevCoCanvas, 300);
        window.addEventListener('resize', resizeDevCoCanvas);

        // Ativar resize ao abrir a tab de checkout
        $('.rs-nav-tabs a[href="#pane-checkout"]').on('shown.bs.tab', function () {
            setTimeout(resizeDevCoCanvas, 150);
        });

        devCoCanvas.addEventListener('mousedown', startDevCoDrawing);
        devCoCanvas.addEventListener('mousemove', moveDevCoDrawing);
        devCoCanvas.addEventListener('mouseup', stopDevCoDrawing);
        devCoCanvas.addEventListener('mouseleave', stopDevCoDrawing);
        devCoCanvas.addEventListener('touchstart', startDevCoDrawing, { passive: false });
        devCoCanvas.addEventListener('touchmove', moveDevCoDrawing, { passive: false });
        devCoCanvas.addEventListener('touchend', stopDevCoDrawing);
    }

    if (devCoBtnClean && devCoCanvas && devCoCtx) {
        devCoBtnClean.addEventListener('click', function () {
            devCoCtx.clearRect(0, 0, devCoCanvas.width, devCoCanvas.height);
            devCoSigned = false;
            if (devCoInput) devCoInput.value = '';
        });
    }

    if (devCoForm && devCoCanvas && devCoInput) {
        devCoForm.addEventListener('submit', function (event) {
            if (!devCoSigned) {
                event.preventDefault();
                alert('Assine no quadro antes de salvar.');
                return;
            }
            devCoInput.value = devCoCanvas.toDataURL('image/png');
        });
    }

    var formAceitarTermoAdmin = document.getElementById('formAceitarTermoMotoristaAppAdmin');
    @include('reserva.partials.motorista_app_historico')
    if (formAceitarTermoAdmin) {
        formAceitarTermoAdmin.addEventListener('submit', function () {
            var btn = document.getElementById('btnAceitarTermoMotoristaAppAdmin') || this.querySelector('button[type="submit"]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...';
            }
        });
    }

    var btnCheckoutMotorista = document.getElementById('btn-iniciar-checkout-motorista');
    if (btnCheckoutMotorista) {
        btnCheckoutMotorista.addEventListener('click', function () {
            var url        = btnCheckoutMotorista.dataset.url;
            var antesFim   = btnCheckoutMotorista.dataset.antesFim === '1';
            var dataFim    = btnCheckoutMotorista.dataset.dataFim;

            if (antesFim) {
                Swal.fire({
                    title: 'Check-out antecipado',
                    html: '<p style="color:#374151;line-height:1.6">A data prevista de devolução é <strong>' + dataFim + '</strong>.<br><br>' +
                           'Ao realizar o check-out agora, <strong>não haverá estorno</strong> dos dias restantes que já foram cobrados.<br><br>' +
                           'Deseja continuar mesmo assim?</p>',
                    icon: 'warning',
                    iconColor: '#f59e0b',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-flag-checkered mr-1"></i> Sim, fazer check-out',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#0891b2',
                    cancelButtonColor: '#6b7280',
                    reverseButtons: true,
                    customClass: {
                        popup: 'swal-rounded',
                    },
                }).then(function (result) {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            } else {
                window.location.href = url;
            }
        });
    }

    var hash = window.location.hash;
    if (hash) {
        if (hash === '#historico-pagamentos') {
            $('#tab-financeiro').tab('show');
        } else {
            $('.rs-nav-tabs a[href="' + hash + '"]').tab('show');
        }
    }
    $('.rs-nav-tabs a').on('shown.bs.tab', function (e) {
        window.location.hash = e.target.hash;
    });
});
</script>
@stop