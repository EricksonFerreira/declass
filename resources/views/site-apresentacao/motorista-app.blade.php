<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Motorista de app | {{ $empresa->nome }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --p: #173d63;
            --s: #0f2740;
            --a: #0f766e;
            --line: #d7e2ef;
            --ink: #0f172a;
            --muted: #5f6f84;
            --ok: #16a34a;
            --warn: #b45309;
            --bg: #eef4fb;
        }

        * { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(15, 118, 110, .08), transparent 28%),
                radial-gradient(circle at 85% 10%, rgba(23, 61, 99, .10), transparent 22%),
                linear-gradient(180deg, #f7fbff 0%, var(--bg) 100%);
        }

        a { color: inherit; }

        .top {
            background: rgba(255,255,255,.88);
            border-bottom: 1px solid rgba(215, 226, 239, .9);
            backdrop-filter: blur(8px);
            position: sticky;
            top: 0;
            z-index: 20;
        }

        .top .inner {
            width: min(1160px, calc(100% - 24px));
            margin: 0 auto;
            min-height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            font-weight: 700;
            color: #0f172a;
        }

        .brand img {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            object-fit: contain;
            background: var(--p);
            border: 1px solid #e2e8f0;
            padding: 4px;
        }

        .back {
            text-decoration: none;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 10px 14px;
            color: #334155;
            font-size: 13px;
            font-weight: 700;
            background: #fff;
        }

        .container {
            width: min(1160px, calc(100% - 24px));
            margin: 0 auto;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 300px;
            gap: 14px;
            padding: 16px 0 26px;
        }

        .card {
            background: rgba(255,255,255,.92);
            border: 1px solid rgba(215, 226, 239, .95);
            border-radius: 18px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .08);
            overflow: hidden;
        }

        .head {
            position: relative;
            padding: 16px 18px 18px;
            color: #fff;
            background:
                radial-gradient(circle at top right, rgba(255,255,255,.15), transparent 18%),
                linear-gradient(120deg, var(--p), var(--s));
            isolation: isolate;
        }

        .head::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(255,255,255,.08), transparent 35%);
            pointer-events: none;
        }

        .head h1 {
            margin: 0 0 8px;
            font-size: clamp(1.55rem, 2.8vw, 2.25rem);
            line-height: 1.05;
        }

        .head p {
            margin: 0;
            font-size: 14px;
            opacity: .94;
            max-width: 70ch;
            line-height: 1.6;
        }

        .body {
            padding: 16px 18px 18px;
            background: linear-gradient(180deg, #fff, #fbfdff);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            color: #fff;
            font-size: .76rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 12px;
        }

        .stepper {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }

        .step {
            border: 1px solid var(--line);
            background: #f8fbff;
            border-radius: 14px;
            min-height: 56px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            padding: 10px 12px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            text-align: left;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s, transform .15s, background .15s;
        }

        .step .num {
            width: 22px;
            height: 22px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 12px;
            font-weight: 800;
            flex: 0 0 auto;
            margin-top: 1px;
        }

        .step > span:last-child {
            display: block;
            min-width: 0;
        }

        .step small {
            display: block;
            font-weight: 600;
            color: var(--muted);
            margin-top: 2px;
            line-height: 1.2;
        }

        .step.is-active {
            border-color: var(--p);
            background: #fff;
            box-shadow: 0 10px 24px rgba(23, 61, 99, .10);
            color: var(--p);
        }

        .step.is-active .num {
            background: var(--p);
            color: #fff;
        }

        .panel {
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 16px;
            background: #fff;
        }

        .panel.hidden { display: none; }

        .panel-title {
            margin: 0 0 6px;
            font-size: 1.1rem;
            font-weight: 900;
            color: #0f172a;
        }

        .panel-desc {
            margin: 0 0 14px;
            color: var(--muted);
            font-size: .92rem;
            line-height: 1.6;
        }

        .plan-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .plan-card {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: linear-gradient(180deg, #fff, #fafcff);
            padding: 16px;
            cursor: pointer;
            transition: transform .15s, box-shadow .15s, border-color .15s;
            min-height: 100%;
        }

        .plan-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 28px rgba(15, 23, 42, .07);
        }

        .plan-card.is-selected {
            border-color: var(--p);
            box-shadow: 0 16px 32px rgba(23,61,99,.14);
        }

        .plan-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 10px;
            border-radius: 999px;
            background: #eff6ff;
            color: var(--p);
            font-size: .72rem;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .plan-title {
            margin: 0 0 6px;
            font-size: 1.02rem;
            font-weight: 900;
            color: #0f172a;
        }

        .plan-price {
            margin: 10px 0 8px;
            font-size: 1.4rem;
            font-weight: 900;
            color: var(--p);
        }

        .plan-meta {
            color: var(--muted);
            font-size: .86rem;
            line-height: 1.55;
            margin-top: 4px;
        }

        .plan-actions {
            margin-top: 14px;
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .choice-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .choice {
            border: 1px solid var(--line);
            background: #fff;
            border-radius: 16px;
            padding: 16px;
            cursor: pointer;
            text-align: left;
            transition: transform .15s, box-shadow .15s, border-color .15s;
        }

        .choice:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 24px rgba(15, 23, 42, .06);
        }

        .choice strong {
            display: block;
            font-size: 1rem;
            margin-bottom: 4px;
            color: #0f172a;
        }

        .choice span {
            color: var(--muted);
            font-size: .92rem;
            line-height: 1.55;
        }

        .optional-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .optional-card {
            border: 1px solid var(--line);
            border-radius: 18px;
            background: linear-gradient(180deg, #fff, #fafcff);
            padding: 14px;
            display: grid;
            gap: 10px;
        }

        .optional-card.is-selected {
            border-color: var(--p);
            box-shadow: 0 14px 26px rgba(23, 61, 99, .12);
        }

        .optional-card__head {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            align-items: start;
        }

        .optional-card__name {
            font-size: 1rem;
            font-weight: 900;
            color: #0f172a;
        }

        .optional-card__meta {
            margin-top: 2px;
            color: var(--muted);
            font-size: .88rem;
            line-height: 1.45;
        }

        .optional-card__price {
            font-weight: 900;
            color: var(--a);
            white-space: nowrap;
        }

        .optional-card__actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .optional-qty {
            min-width: 42px;
            text-align: center;
            font-weight: 900;
        }

        .optional-btn {
            border: 1px solid var(--line);
            background: #f8fbff;
            color: #0f172a;
            border-radius: 999px;
            width: 34px;
            height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            cursor: pointer;
        }

        .optional-btn:hover {
            border-color: var(--p);
        }

        .required-mark {
            color: #dc2626;
            font-weight: 900;
        }

        .sidebar {
            display: grid;
            gap: 14px;
            align-content: start;
        }

        .summary {
            padding: 16px;
            position: sticky;
            top: 86px;
        }

        .summary h3 {
            margin: 0 0 10px;
            font-size: 16px;
            font-weight: 900;
            color: #0f172a;
        }

        .summary-grid {
            display: grid;
            gap: 10px;
        }

        .summary-item {
            padding: 12px 14px;
            border-radius: 14px;
            background: linear-gradient(180deg, #f8fbff, #fdfefe);
            border: 1px solid var(--line);
        }

        .summary-item strong {
            display: block;
            font-size: .72rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .summary-item span {
            font-weight: 800;
            color: #0f172a;
        }

        .summary-chip-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .chip {
            padding: 7px 11px;
            border-radius: 999px;
            background: #eef6fb;
            border: 1px solid var(--line);
            font-size: .78rem;
            font-weight: 700;
            color: #334155;
        }

        .summary-total {
            margin-top: 12px;
            border-top: 1px solid var(--line);
            padding-top: 12px;
        }

        .summary-total:not(.hidden) {
            display: grid;
            gap: 6px;
        }

        .summary-total__row {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            padding: 7px 0;
            font-size: .94rem;
        }

        .summary-total__row strong {
            color: #334155;
        }

        .summary-total__row span {
            font-weight: 800;
            color: #0f172a;
        }

        .summary-total__row.is-strong span {
            color: var(--p);
            font-size: 1.02rem;
        }

        .selected-optional-list {
            display: grid;
            gap: 8px;
            margin-top: 12px;
        }

        .selected-optional-item {
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: #fff;
        }

        .selected-optional-item__name {
            display: flex;
            justify-content: space-between;
            gap: 8px;
            align-items: baseline;
            font-weight: 800;
            color: #0f172a;
        }

        .selected-optional-item__meta {
            margin-top: 4px;
            color: var(--muted);
            font-size: .82rem;
            line-height: 1.45;
        }

        .empty-state {
            padding: 12px 14px;
            border-radius: 14px;
            border: 1px dashed var(--line);
            background: #fbfdff;
            color: var(--muted);
            font-size: .9rem;
            line-height: 1.55;
        }

        .notice {
            margin-top: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            font-size: .9rem;
            line-height: 1.55;
        }

        .notice.ok {
            border: 1px solid #bbf7d0;
            background: linear-gradient(180deg, #f0fdf4, #f8fffb);
            color: #166534;
        }

        .notice.warn {
            border: 1px solid #fde68a;
            background: linear-gradient(180deg, #fffbeb, #fffdf4);
            color: #92400e;
        }

        .docs {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .doc-card {
            border: 1px dashed var(--line);
            border-radius: 14px;
            background: #fafcff;
            padding: 14px;
        }

        .doc-card strong {
            display: block;
            margin-bottom: 6px;
            font-size: .92rem;
        }

        .field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field-full {
            grid-column: 1 / -1;
        }

        .field label {
            font-size: .8rem;
            font-weight: 800;
            color: #334155;
        }

        .field input,
        .field select {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 11px 13px;
            font: inherit;
            background: #fff;
            color: var(--ink);
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .btn {
            border: 0;
            border-radius: 999px;
            padding: 11px 16px;
            font-weight: 800;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--p), #2f6f9d);
            color: #fff;
        }

        .btn-secondary {
            background: #eef4fb;
            color: #0f172a;
        }

        .btn-dark {
            background: #0f172a;
            color: #fff;
        }

        .muted {
            color: var(--muted);
        }

        .hidden { display: none; }

        @media (max-width: 1024px) {
            .layout,
            .stepper,
            .plan-grid,
            .choice-row,
            .docs,
            .field-grid,
            .optional-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
@php
    $estacaoId = (string) ($selecionados['estacao_retirada'] ?? '');
    $dataInicio = (string) ($selecionados['data_retirada'] ?? '');
    $horaRetirada = (string) ($selecionados['hora_retirada'] ?? '');

    $estacaoRetiradaLabel = $estacaoId !== ''
        ? ($estacoesRetirada[$estacaoId] ?? $estacaoId)
        : 'Não informada';

    $dataInicioLabel = $dataInicio !== ''
        ? \Illuminate\Support\Carbon::parse($dataInicio)->format('d/m/Y')
        : 'Não informada';

    $horaRetiradaLabel = $horaRetirada !== ''
        ? substr($horaRetirada, 0, 5)
        : 'Não informada';
@endphp

<header class="top">
    <div class="inner">
        <a href="{{ route('site.apresentacao') }}" class="brand">
            <img src="{{ asset('assets/imgs/logo-sem-texto.png') }}" alt="{{ $empresa->nome }}">
            <span>{{ $empresa->nome }}</span>
        </a>
        <a href="{{ url()->previous() }}" class="back">Voltar</a>
    </div>
</header>

<main class="container">
    <div class="layout">
        <section class="card">
            <div class="head">
                <div class="eyebrow">Motorista de app</div>
                <h1>Reserva em passos</h1>
                <p>Escolha um plano, defina opcionais, informe a conta e finalize conforme a regra da empresa.</p>
            </div>


                @if(session('success'))
                    <div class="notice ok" style="margin: 18px 18px 0;">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="notice warn" style="margin: 18px 18px 0;">
                        <strong>Revise os campos informados:</strong>
                        <ul style="margin:8px 0 0;padding-left:18px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            <div class="body">
                <div class="stepper">
                    <button class="step is-active" type="button" data-step-btn="1"><span class="num">1</span><span>Planos<small>Escolha o melhor plano</small></span></button>
                    <button class="step" type="button" data-step-btn="2"><span class="num">2</span><span>Opcionais<small>Escolha os extras</small></span></button>
                    <button class="step" type="button" data-step-btn="3"><span class="num">3</span><span>Conta<small>Opcional</small></span></button>
                    <button class="step" type="button" data-step-btn="4"><span class="num">4</span><span>Dados e documentos<small>Só se houver pendência</small></span></button>
                    <button class="step" type="button" data-step-btn="5"><span class="num">5</span><span>Confirmação<small>Finalizar solicitação</small></span></button>
                </div>

                <section class="panel" data-step-panel="1">
                    <h2 class="panel-title">Planos para motorista de app</h2>
                    <p class="panel-desc">Selecione um plano e avance para o próximo passo do fluxo.</p>

                    @if($planos->isEmpty())
                        <div class="notice warn">Nenhum plano de motorista de app foi cadastrado para esta empresa ainda.</div>
                    @else
                        <div class="plan-grid">
                            @foreach($planos as $plano)
                                @php
                                    $planoOpcionaisJson = $plano->opcionais->map(function ($opcional) {
                                        return [
                                            'id' => (int) $opcional->id,
                                            'nome' => $opcional->nome,
                                            'tipo_cobranca' => $opcional->tipo_cobranca,
                                            'preco_por_dia' => (float) $opcional->preco_por_dia,
                                            'preco_integral' => (float) $opcional->preco_integral,
                                        ];
                                    })->values()->toJson(JSON_UNESCAPED_UNICODE);
                                @endphp
                                <article class="plan-card"
                                         data-plano-card
                                         data-plano-id="{{ $plano->id }}"
                                         data-plano-nome="{{ $plano->nome }}"
                                         data-plano-preco="R$ {{ number_format((float) $plano->valor_periodo, 2, ',', '.') }} por {{ strtolower($plano->periodo_label) }}"
                                         data-plano-periodo="{{ $plano->periodo }}"
                                         data-plano-valor="{{ (float) $plano->valor_periodo }}"
                                         data-plano-calcao="{{ (float) ($plano->calcao ?? 0) }}"
                                         data-plano-opcionais='{{ $planoOpcionaisJson }}'>
                                    <div class="plan-badge">{{ $plano->periodo_label }}</div>
                                    <h3 class="plan-title">{{ $plano->nome }}</h3>
                                    <div class="plan-price">R$ {{ number_format((float) $plano->valor_periodo, 2, ',', '.') }}</div>
                                    <div class="plan-meta"><strong>{{ $plano->km_livre === 'S' ? 'Quilometragem livre' : $plano->km_resumo }}</strong></div>
                                    <div class="plan-meta">Caução: <strong>{{ $plano->calcao !== null ? 'R$ ' . number_format((float) $plano->calcao, 2, ',', '.') : 'Sob consulta' }}</strong></div>
                                    <div class="plan-meta">Opcionais vinculados: <strong>{{ $plano->opcionais->count() }}</strong></div>
                                    <div class="plan-actions">
                                        <button type="button" class="btn btn-primary" data-select-plan>Selecionar plano</button>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="panel hidden" data-step-panel="2">
                    <h2 class="panel-title">Opcionais do plano</h2>
                    <p class="panel-desc">Selecione os opcionais que deseja adicionar à contratação. Os valores seguem o plano escolhido.</p>
                    <div class="notice ok" style="margin-bottom:12px;">
                        Se nenhum opcional fizer sentido, você pode seguir sem selecionar nada.
                    </div>
                    <div id="motoristaAppOpcionaisList" class="optional-grid"></div>
                    <div class="actions">
                        <button type="button" class="btn btn-secondary" data-step-back>Voltar</button>
                        <button type="button" class="btn btn-dark" data-step-btn="3">Continuar</button>
                    </div>
                </section>

                <section class="panel hidden" data-step-panel="3">
                    <h2 class="panel-title">Conta</h2>
                    <p class="panel-desc">Escolha entre entrar com uma conta existente ou seguir com o cadastro do motorista de app.</p>
                    <div class="choice-row">
                        <button type="button" class="choice" data-account-choice="login">
                            <strong>Já tenho uma conta</strong>
                            <span>Vou entrar e continuar a solicitação com os meus dados existentes.</span>
                        </button>
                        <button type="button" class="choice" data-account-choice="register">
                            <strong>Quero me cadastrar</strong>
                            <span>Vou preencher os dados pessoais e seguir para a etapa de documentos.</span>
                        </button>
                    </div>
                    <div class="actions">
                        <button type="button" class="btn btn-secondary" data-step-back>Voltar</button>
                    </div>
                </section>

                <section class="panel hidden" data-step-panel="4">
                    <h2 class="panel-title">Dados e documentos</h2>
                    <p class="panel-desc">Esta etapa só é útil se o cadastro do colaborador ainda estiver pendente de documentação na empresa.</p>
                    <div class="notice ok" style="margin-bottom:12px;">
                        Se o motorista já tiver os documentos validados ou se a empresa não exigir análise manual, você pode apenas revisar os dados e seguir para a confirmação.
                    </div>
                    <form method="POST"
                          action="{{ route('site.apresentacao.motorista-app.store') }}"
                          enctype="multipart/form-data"
                          id="motoristaAppForm">
                        @csrf
                        <input type="hidden" name="plano_id" id="selected_plan_id">
                        <input type="hidden" name="estacao_id" value="{{ $estacaoId }}">
                        <input type="hidden" name="data_inicio" value="{{ $dataInicio }}">
                        <input type="hidden" name="opcionais_payload" id="motoristaAppOpcionaisPayload" value="[]">
                                                
                        <div class="docs">
                            @if($usuarioTemDocumentos)
                                <div class="notice ok" style="grid-column: 1 / -1;">
                                    Seus documentos já foram enviados para esta empresa. Se precisar alterar algo, a solicitação será reaproveitada ao salvar.
                                </div>
                            @else
                                @foreach($docsSelecionados as $docItem)
                                    @php
                                        $docObrigatorio = (($docItem['obrigatorio'] ?? false) || $exigirAprovacaoDocumentos);
                                    @endphp
                                    <div class="doc-card"
                                         data-motorista-doc-card
                                         data-condicao-campo="{{ $docItem['condicao']['campo_key'] ?? '' }}"
                                         data-condicao-operador="{{ $docItem['condicao']['operador'] ?? '' }}"
                                         data-condicao-valor='@json($docItem['condicao']['valor'] ?? [])'
                                         data-required-original="{{ $docObrigatorio ? '1' : '0' }}">
                                        <strong>{{ $docItem['label'] }}{!! $docObrigatorio ? ' <span class="text-danger">*</span>' : '' !!}</strong>
                                        @if(($docItem['type'] ?? 'arquivo') === 'arquivo')
                                            <input type="file"
                                                   name="documentos[{{ $docItem['key'] }}]"
                                                   accept=".pdf,.jpg,.jpeg,.png,.webp"
                                                   {{ $docObrigatorio ? 'required' : '' }}>
                                        @elseif(($docItem['type'] ?? 'arquivo') === 'texto')
                                            <input type="text"
                                                   name="documentos[{{ $docItem['key'] }}]"
                                                   value="{{ old('documentos.' . $docItem['key']) }}"
                                                   placeholder="Digite aqui"
                                                   {{ $docObrigatorio ? 'required' : '' }}>
                                        @elseif(($docItem['type'] ?? 'arquivo') === 'lista')
                                            @php
                                                $listaMultiplas = (bool) ($docItem['multiple'] ?? false);
                                                $oldLista = old('documentos.' . $docItem['key'], $listaMultiplas ? [] : '');
                                                $oldLista = is_array($oldLista) ? $oldLista : [$oldLista];
                                            @endphp
                                            <select name="documentos[{{ $docItem['key'] }}]{{ $listaMultiplas ? '[]' : '' }}"
                                                    {{ $listaMultiplas ? 'multiple' : '' }}
                                                    data-enhance-select
                                                    data-placeholder="{{ $listaMultiplas ? 'Selecione uma ou mais opções' : 'Selecione' }}"
                                                    {{ $docObrigatorio ? 'required' : '' }}>
                                                @unless($listaMultiplas)
                                                    <option value="">Selecione</option>
                                                @endunless
                                                @foreach(($docItem['options'] ?? []) as $opcao)
                                                    <option value="{{ $opcao }}" @selected(in_array($opcao, $oldLista, true))>{{ $opcao }}</option>
                                                @endforeach
                                            </select>
                                        @elseif(($docItem['type'] ?? 'arquivo') === 'data')
                                            <input type="date"
                                                   name="documentos[{{ $docItem['key'] }}]"
                                                   value="{{ old('documentos.' . $docItem['key']) }}"
                                                   {{ $docObrigatorio ? 'required' : '' }}>
                                        @else
                                            <textarea name="documentos[{{ $docItem['key'] }}]"
                                                      rows="4"
                                                      placeholder="Digite aqui"
                                                      {{ $docObrigatorio ? 'required' : '' }}>{{ old('documentos.' . $docItem['key']) }}</textarea>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        @if(!$usuarioTemDocumentos)
                            @if(empty($docsSelecionados))
                                <div class="notice ok">
                                    A empresa não exigiu documentos adicionais nesta configuração. O cadastro pode seguir apenas com os dados pessoais.
                                </div>
                            @endif

                            <div class="notice {{ $exigirAprovacaoDocumentos ? 'warn' : 'ok' }}">
                                @if($exigirAprovacaoDocumentos)
                                    Os documentos enviados serão analisados pela equipe da locadora antes da liberação do cadastro.
                                @else
                                    O cadastro pode seguir sem aprovação manual de documentos, conforme a regra da empresa.
                                @endif
                            </div>
                        @endif

                        <div class="actions"> 
                            <button type="button" class="btn btn-secondary" data-step-back>Voltar</button>
                            <button type="button" class="btn btn-dark" data-step-btn="5">Continuar para confirmação</button>
                        </div>
                    </form>
                </section>

                <section class="panel hidden" data-step-panel="5">
                    <h2 class="panel-title">{{ $exigirAprovacaoDocumentos ? 'Solicitação em análise' : 'Confirmação da reserva' }}</h2>
                    <p class="panel-desc">
                        @if($exigirAprovacaoDocumentos)
                            Sua solicitação já pode ser enviada para análise da locadora.
                        @else
                            Confirme para criar a reserva do motorista de app. O valor estimado da contratação aparece abaixo.
                        @endif
                    </p>

                    <div class="notice {{ $exigirAprovacaoDocumentos ? 'warn' : 'ok' }}" style="margin-top: 0;">
                        @if($exigirAprovacaoDocumentos)
                            A empresa vai revisar os documentos e liberar a contratação quando concluir a análise.
                        @else
                            Depois da confirmação, essa contratação aparecerá em "Minhas reservas" com a duração definida pelo plano.
                        @endif
                    </div>

                    <div class="summary-total hidden" id="confirmation_total_block" style="margin-top: 14px;">
                        <div class="summary-total__row">
                            <strong>Plano</strong>
                            <span id="confirmation_plan_total">—</span>
                        </div>
                        <div class="summary-total__row">
                            <strong>Opcionais</strong>
                            <span id="confirmation_optionals_total">—</span>
                        </div>
                        <div class="selected-optional-list" id="confirmation_optionals_list"></div>
                        <div class="summary-total__row is-strong">
                            <strong>Valor a pagar</strong>
                            <span id="confirmation_grand_total">—</span>
                        </div>
                    </div>

                    <div class="summary-grid" style="margin-top: 14px;">
                        <div class="summary-item">
                            <strong>Estação de retirada</strong>
                            <span>{{ $estacaoRetiradaLabel }}</span>
                        </div>
                        <div class="summary-item">
                            <strong>Data para começar</strong>
                            <span>{{ $dataInicioLabel }}</span>
                        </div>
                        <div class="summary-item">
                            <strong>Hora para começar</strong>
                            <span>{{ $horaRetiradaLabel }}</span>
                        </div>
                        <div class="summary-item">
                            <strong>Documentos</strong>
                            <span>{{ $exigirAprovacaoDocumentos ? 'Análise necessária' : 'Liberação automática' }}</span>
                        </div>
                    </div>

                    <div class="actions">
                        <button type="button" class="btn btn-secondary" data-step-back>Voltar</button>
                        <button type="submit" form="motoristaAppForm" class="btn btn-dark">
                            {{ $exigirAprovacaoDocumentos ? 'Enviar para análise' : 'Confirmar reserva' }}
                        </button>
                    </div>
                </section>
            </div>
        </section>

        <aside class="sidebar">
            <section class="card summary">
                <h3>Resumo</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <strong>Estação de retirada</strong>
                        <span>{{ $estacaoRetiradaLabel }}</span>
                    </div>
                    <div class="summary-item">
                        <strong>Data para começar</strong>
                        <span>{{ $dataInicioLabel }}</span>
                    </div>
                    <div class="summary-item">
                        <strong>Hora para começar</strong>
                        <span>{{ $horaRetiradaLabel }}</span>
                    </div>
                    <div class="summary-item hidden" id="summary_plan_item">
                        <strong>Plano selecionado</strong>
                        <span id="summary_plan_name">Não selecionado</span>
                    </div>
                    <div class="summary-item hidden" id="summary_plan_price_item">
                        <strong>Valor</strong>
                        <span id="summary_plan_price">—</span>
                    </div>
                </div>

                <div class="summary-total hidden" id="summary_total_block">
                    <div class="summary-total__row">
                        <strong>Plano</strong>
                        <span id="summary_plan_total">—</span>
                    </div>
                    <div class="summary-total__row">
                        <strong>Opcionais</strong>
                        <span id="summary_optionals_total">—</span>
                    </div>
                    <div class="selected-optional-list" id="summary_optionals_list"></div>
                    <div class="summary-total__row is-strong">
                        <strong>Total estimado</strong>
                        <span id="summary_grand_total">—</span>
                    </div>
                </div>

                <div class="summary-chip-list">
                    {{-- <span class="chip">{{ $empresa->nome }}</span>
                    <span class="chip">{{ $planos->count() }} plano(s) disponível(is)</span>
                    <span class="chip">{{ count($docsSelecionados) > 0 ? count($docsSelecionados) . ' documento(s) selecionado(s)' : 'Sem documentos extras' }}</span>
                    <span class="chip">{{ $solicitacaoFinalizada ? 'Solicitação concluída' : ($exigirAprovacaoDocumentos ? 'Aprovação ativa' : 'Sem aprovação manual') }}</span> --}}
                </div>
            </section>

            <section class="card summary">
                <div class="panel-title" style="margin-bottom:8px;">Como funciona</div>
                <div class="muted" style="font-size:.92rem;line-height:1.6;">
                    Escolha o plano, entre ou crie sua conta, preencha os dados e envie a solicitação para análise da locadora.
                </div>
            </section>
        </aside>
    </div>
</main>

<script>
(function () {
    const stepButtons = [...document.querySelectorAll('[data-step-btn]')];
    const panels = [...document.querySelectorAll('[data-step-panel]')];
    const planCards = [...document.querySelectorAll('[data-plano-card]')];
    const stepBackButtons = [...document.querySelectorAll('[data-step-back]')];
    const accountChoices = [...document.querySelectorAll('[data-account-choice]')];
    const loginUrl = @json($loginMotoristaUrl);
    const isAuth = @json($isAuth);
    const loginSuccess = @json($loginSuccess);
    const analiseConcluida = @json($analiseConcluida);
    const usuarioTemDocumentos = @json($usuarioTemDocumentos);
    const solicitacaoFinalizada = @json($solicitacaoFinalizada);
    const hasTripParams = Boolean(
        @json(request()->filled('estacao_retirada') || request()->filled('estação_retirada'))
        && @json(request()->filled('data_retirada'))
        && @json(request()->filled('hora_retirada'))
    );
    const selectedPlanStorageKey = 'motorista_app_selected_plan_id';
    const selectedPlanInput = document.getElementById('selected_plan_id');
    const summaryPlanItem = document.getElementById('summary_plan_item');
    const summaryPlanPriceItem = document.getElementById('summary_plan_price_item');
    const summaryPlanName = document.getElementById('summary_plan_name');
    const summaryPlanPrice = document.getElementById('summary_plan_price');
    const summaryTotalBlock = document.getElementById('summary_total_block');
    const summaryPlanTotal = document.getElementById('summary_plan_total');
    const summaryOptionalsTotal = document.getElementById('summary_optionals_total');
    const summaryOptionalsList = document.getElementById('summary_optionals_list');
    const summaryGrandTotal = document.getElementById('summary_grand_total');
    const confirmationTotalBlock = document.getElementById('confirmation_total_block');
    const confirmationPlanTotal = document.getElementById('confirmation_plan_total');
    const confirmationOptionalsTotal = document.getElementById('confirmation_optionals_total');
    const confirmationOptionalsList = document.getElementById('confirmation_optionals_list');
    const confirmationGrandTotal = document.getElementById('confirmation_grand_total');
    const motoristaAppForm = document.getElementById('motoristaAppForm');
    const motoristaAppOpcionaisList = document.getElementById('motoristaAppOpcionaisList');
    const motoristaAppOpcionaisPayload = document.getElementById('motoristaAppOpcionaisPayload');
    const docCards = [...document.querySelectorAll('[data-motorista-doc-card]')];
    let selectedPlanId = null;
    let selectedPlanData = null;
    let selectedPlanPeriod = null;
    let selectedPlanPrice = 0;
    let selectedOptionals = {};
    let step = hasTripParams
        ? 1
        : (solicitacaoFinalizada ? 5 : ((analiseConcluida || (loginSuccess && usuarioTemDocumentos)) ? 5 : ((loginSuccess || usuarioTemDocumentos) ? 4 : 1)));

    function updateSelectedPlan(card) {
        if (!card) return;
        selectedPlanId = card.dataset.planoId || null;
        try {
            selectedPlanData = JSON.parse(card.dataset.planoOpcionais || '[]');
        } catch (error) {
            selectedPlanData = [];
        }
        selectedPlanPeriod = String(card.dataset.planoPeriodo || '');
        selectedPlanPrice = Number(card.dataset.planoValor || 0);
        selectedOptionals = {};
        if (selectedPlanInput) selectedPlanInput.value = selectedPlanId || '';
        if (summaryPlanName) summaryPlanName.textContent = card.dataset.planoNome || 'Plano selecionado';
        if (summaryPlanPrice) summaryPlanPrice.textContent = card.dataset.planoPreco || '—';
        summaryPlanItem?.classList.remove('hidden');
        summaryPlanPriceItem?.classList.remove('hidden');
        summaryTotalBlock?.classList.remove('hidden');
        renderOptionals();
        syncOpcionaisPayload();
        syncResumoFinanceiro();
    }

    function money(value) {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL',
        }).format(Number(value || 0));
    }

    function optionalUnit(optional) {
        return String(optional?.tipo_cobranca || '') === 'por_dia'
            ? Number(optional?.preco_por_dia || 0)
            : Number(optional?.preco_integral || 0);
    }

    function periodDays(period) {
        const map = {
            semana: 7,
            quinze: 15,
            mes: 30,
            trimestre: 90,
            semestre: 180,
            ano: 365,
        };

        return map[String(period || '')] || 1;
    }

    function getOptionalTotals() {
        const days = periodDays(selectedPlanPeriod);
        return Object.values(selectedOptionals).reduce((carry, item) => {
            const optional = Array.isArray(selectedPlanData)
                ? selectedPlanData.find((entry) => String(entry.id) === String(item.id))
                : null;
            if (!optional) {
                return carry;
            }

            const qty = Number(item.quantidade || 0);
            if (qty <= 0) {
                return carry;
            }

            const unit = optionalUnit(optional);
            const lineTotal = String(optional.tipo_cobranca || '') === 'por_dia'
                ? unit * qty * days
                : unit * qty;

            carry.total += lineTotal;
            carry.items.push({
                id: optional.id,
                nome: optional.nome || 'Opcional',
                quantidade: qty,
                tipo: String(optional.tipo_cobranca || '').replace('_', ' '),
                unit,
                lineTotal,
                porDia: String(optional.tipo_cobranca || '') === 'por_dia',
            });
            return carry;
        }, { total: 0, items: [] });
    }

    function syncResumoFinanceiro() {
        if (!summaryTotalBlock) {
            return;
        }

        if (!selectedPlanId) {
            summaryTotalBlock.classList.add('hidden');
            confirmationTotalBlock?.classList.add('hidden');
            return;
        }

        summaryTotalBlock.classList.remove('hidden');
        confirmationTotalBlock?.classList.remove('hidden');
        const totals = getOptionalTotals();
        const days = periodDays(selectedPlanPeriod);
        const planTotal = selectedPlanPrice;
        if (summaryPlanTotal) {
            summaryPlanTotal.textContent = money(planTotal);
        }
        if (confirmationPlanTotal) {
            confirmationPlanTotal.textContent = money(planTotal);
        }
        if (summaryOptionalsTotal) {
            summaryOptionalsTotal.textContent = money(totals.total);
        }
        if (confirmationOptionalsTotal) {
            confirmationOptionalsTotal.textContent = money(totals.total);
        }
        if (summaryGrandTotal) {
            summaryGrandTotal.textContent = money(planTotal + totals.total);
        }
        if (confirmationGrandTotal) {
            confirmationGrandTotal.textContent = money(planTotal + totals.total);
        }
        if (summaryOptionalsList) {
            if (!totals.items.length) {
                summaryOptionalsList.innerHTML = '<div class="empty-state">Nenhum opcional selecionado.</div>';
            } else {
                summaryOptionalsList.innerHTML = totals.items.map((item) => `
                    <div class="selected-optional-item">
                        <div class="selected-optional-item__name">
                            <span>${item.nome}</span>
                            <span>${money(item.lineTotal)}</span>
                        </div>
                        <div class="selected-optional-item__meta">
                            ${item.quantidade}x • ${money(item.unit)}${item.porDia ? ` / dia x ${days} dia(s)` : ''}
                        </div>
                    </div>
                `).join('');
            }
        }
        if (confirmationOptionalsList) {
            if (!totals.items.length) {
                confirmationOptionalsList.innerHTML = '<div class="empty-state">Nenhum opcional selecionado.</div>';
            } else {
                confirmationOptionalsList.innerHTML = totals.items.map((item) => `
                    <div class="selected-optional-item">
                        <div class="selected-optional-item__name">
                            <span>${item.nome}</span>
                            <span>${money(item.lineTotal)}</span>
                        </div>
                        <div class="selected-optional-item__meta">
                            ${item.quantidade}x • ${money(item.unit)}${item.porDia ? ` / dia x ${days} dia(s)` : ''}
                        </div>
                    </div>
                `).join('');
            }
        }
    }

    function renderOptionals() {
        if (!motoristaAppOpcionaisList) {
            return;
        }

        const items = Array.isArray(selectedPlanData) ? selectedPlanData : [];
        if (!selectedPlanId) {
            motoristaAppOpcionaisList.innerHTML = '<div class="notice warn" style="grid-column:1 / -1;">Selecione um plano para ver os opcionais disponíveis.</div>';
            return;
        }

        if (!items.length) {
            motoristaAppOpcionaisList.innerHTML = '<div class="notice ok" style="grid-column:1 / -1;">Este plano não possui opcionais vinculados.</div>';
            syncResumoFinanceiro();
            return;
        }

        motoristaAppOpcionaisList.innerHTML = items.map((optional) => {
            const quantidade = Number(selectedOptionals[optional.id]?.quantidade || 0);
            const ativo = quantidade > 0;
            const priceLabel = String(optional.tipo_cobranca || '') === 'por_dia'
                ? `${money(optional.preco_por_dia)} por dia`
                : `${money(optional.preco_integral)} valor único`;

            return `
                <article class="optional-card ${ativo ? 'is-selected' : ''}" data-optional-id="${optional.id}">
                    <div class="optional-card__head">
                        <div>
                            <div class="optional-card__name">${optional.nome || 'Opcional'}</div>
                            <div class="optional-card__meta">${String(optional.tipo_cobranca || '').replace('_', ' ')}</div>
                        </div>
                        <div class="optional-card__price">${priceLabel}</div>
                    </div>
                    <div class="optional-card__actions">
                        <button type="button" class="optional-btn" data-optional-action="minus" aria-label="Diminuir">-</button>
                        <span class="optional-qty">${quantidade}</span>
                        <button type="button" class="optional-btn" data-optional-action="plus" aria-label="Adicionar">+</button>
                    </div>
                </article>
            `;
        }).join('');
        syncResumoFinanceiro();
    }

    function syncOpcionaisPayload() {
        if (!motoristaAppOpcionaisPayload) {
            return;
        }

        const itens = Object.values(selectedOptionals)
            .filter((item) => Number(item.quantidade || 0) > 0)
            .map((item) => ({
                id: Number(item.id),
                quantidade: Number(item.quantidade),
            }));

        motoristaAppOpcionaisPayload.value = JSON.stringify(itens);
    }

    function getMotoristaDocField(key) {
        if (!motoristaAppForm || !key) {
            return null;
        }

        return motoristaAppForm.querySelector(`[name="documentos[${key}]"]`);
    }

    function getMotoristaDocFieldValue(key) {
        const field = getMotoristaDocField(key);
        if (!field || field.disabled) {
            return { hasValue: false, value: '' };
        }

        if (field.type === 'file') {
            return {
                hasValue: field.files && field.files.length > 0,
                value: field.files && field.files.length > 0 ? '__file__' : '',
            };
        }

        if (field.multiple) {
            const values = [...field.selectedOptions]
                .map((option) => String(option.value || '').trim())
                .filter(Boolean);

            return {
                hasValue: values.length > 0,
                value: values,
            };
        }

        const value = String(field.value || '').trim();
        return {
            hasValue: value !== '',
            value,
        };
    }

    function evaluateMotoristaDocCondition(card) {
        if (!card) {
            return true;
        }

        const campoKey = String(card.dataset.condicaoCampo || '').trim();
        const operador = String(card.dataset.condicaoOperador || '').trim();
        let valorEsperado = [];

        try {
            const parsed = JSON.parse(card.dataset.condicaoValor || '[]');
            valorEsperado = Array.isArray(parsed)
                ? parsed.map((item) => String(item || '').trim()).filter(Boolean)
                : [String(parsed || '').trim()].filter(Boolean);
        } catch (error) {
            valorEsperado = String(card.dataset.condicaoValor || '').trim()
                ? [String(card.dataset.condicaoValor || '').trim()]
                : [];
        }

        if (!campoKey || !operador) {
            return true;
        }

        const estado = getMotoristaDocFieldValue(campoKey);
        if (operador === 'igual') {
            return estado.hasValue && (
                Array.isArray(estado.value)
                    ? valorEsperado.length > 0 && valorEsperado.some((item) => estado.value.includes(item))
                    : valorEsperado.length > 0 && estado.value === valorEsperado[0]
            );
        }

        if (operador === 'diferente') {
            return estado.hasValue && (
                Array.isArray(estado.value)
                    ? valorEsperado.length > 0 && valorEsperado.every((item) => !estado.value.includes(item))
                    : valorEsperado.length > 0 && estado.value !== valorEsperado[0]
            );
        }

        if (operador === 'preenchido') {
            return estado.hasValue;
        }

        if (operador === 'vazio') {
            return !estado.hasValue;
        }

        return true;
    }

    function syncMotoristaDocConditions() {
        if (!docCards.length) {
            return;
        }

        for (let i = 0; i < 5; i += 1) {
            let changed = false;

            docCards.forEach((card) => {
                const visible = evaluateMotoristaDocCondition(card);
                const shouldHide = !visible;
                const currentlyHidden = card.classList.contains('d-none');

                if (shouldHide !== currentlyHidden) {
                    changed = true;
                }

                card.classList.toggle('d-none', shouldHide);

                card.querySelectorAll('input, select, textarea').forEach((field) => {
                    const choicesInstance = field._choicesInstance;

                    if (shouldHide) {
                        field.dataset.requiredOriginal = field.required ? '1' : (field.dataset.requiredOriginal || '0');
                        field.required = false;
                        field.disabled = true;
                        if (choicesInstance && typeof choicesInstance.disable === 'function') {
                            choicesInstance.disable();
                        }
                    } else {
                        field.disabled = false;
                        field.required = field.dataset.requiredOriginal === '1';
                        if (choicesInstance && typeof choicesInstance.enable === 'function') {
                            choicesInstance.enable();
                        }
                    }
                });
            });

            if (!changed) {
                break;
            }
        }
    }

    function showStep(nextStep) {
        step = nextStep;
        stepButtons.forEach((btn) => {
            btn.classList.toggle('is-active', Number(btn.dataset.stepBtn) === step);
        });
        panels.forEach((panel) => {
            panel.classList.toggle('hidden', Number(panel.dataset.stepPanel) !== step);
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    try {
        selectedPlanId = localStorage.getItem(selectedPlanStorageKey);
    } catch (error) {
        selectedPlanId = null;
    }

    if (selectedPlanId) {
        const savedCard = planCards.find((card) => String(card.dataset.planoId) === String(selectedPlanId));
        if (savedCard) {
            planCards.forEach((el) => el.classList.toggle('is-selected', el === savedCard));
            updateSelectedPlan(savedCard);
        }
    }

    planCards.forEach((card) => {
        const button = card.querySelector('[data-select-plan]');

        const selectPlan = () => {
            selectedPlanId = card.dataset.planoId;
            try {
                localStorage.setItem(selectedPlanStorageKey, String(selectedPlanId));
            } catch (error) {
                // ignore storage failures
            }
            planCards.forEach((el) => el.classList.toggle('is-selected', el === card));
            updateSelectedPlan(card);
            showStep(2);
        };

        button?.addEventListener('click', (event) => {
            event.stopPropagation();
            selectPlan();
        });
        card.addEventListener('click', selectPlan);
    });

    accountChoices.forEach((choice) => {
        choice.addEventListener('click', () => {
            const mode = choice.dataset.accountChoice;
            if (mode === 'login') {
                if (isAuth) {
                    showStep(4);
                    return;
                }
                window.location.href = loginUrl;
                return;
            }
            showStep(4);
        });
    });

    stepBackButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            showStep(Math.max(1, step - 1));
        });
    });

    stepButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const target = Number(btn.dataset.stepBtn);
            if (target > 1 && !selectedPlanId) {
                showStep(1);
                return;
            }
            if (target === 3 && !selectedPlanId) {
                showStep(1);
                return;
            }
            showStep(target);
        });
    });

    motoristaAppOpcionaisList?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-optional-action]');
        const card = event.target.closest('[data-optional-id]');
        if (!button || !card) {
            return;
        }

        const id = String(card.dataset.optionalId || '');
        const optional = Array.isArray(selectedPlanData)
            ? selectedPlanData.find((item) => String(item.id) === id)
            : null;
        if (!optional) {
            return;
        }

        const current = Number(selectedOptionals[id]?.quantidade || 0);
        const action = button.dataset.optionalAction;
        const nextValue = action === 'plus'
            ? current + 1
            : Math.max(0, current - 1);

        if (nextValue <= 0) {
            delete selectedOptionals[id];
        } else {
            selectedOptionals[id] = {
                id: Number(optional.id),
                quantidade: nextValue,
            };
        }

        renderOptionals();
        syncOpcionaisPayload();
        syncResumoFinanceiro();
    });

    motoristaAppForm?.addEventListener('submit', (event) => {
        if (!selectedPlanId) {
            event.preventDefault();
            showStep(1);
            alert('Selecione um plano antes de enviar a solicitação.');
        }
    });

    motoristaAppForm?.addEventListener('input', syncMotoristaDocConditions, true);
    motoristaAppForm?.addEventListener('change', syncMotoristaDocConditions, true);

    syncMotoristaDocConditions();

    renderOptionals();
    syncResumoFinanceiro();

    if (solicitacaoFinalizada || analiseConcluida) {
        showStep(5);
    } else if (loginSuccess || usuarioTemDocumentos) {
        showStep(4);
    }
})();
</script>
</body>
</html>
