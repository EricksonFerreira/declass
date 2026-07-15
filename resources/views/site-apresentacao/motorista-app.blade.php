<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Motorista de app | {{ $empresa->nome }}</title>
    <style>
        :root {
            --p: #1f4f7a;
            --s: #14324d;
            --line: #dbe4ee;
            --ink: #0f172a;
            --muted: #64748b;
            --ok: #16a34a;
            --warn: #b45309;
            --bg: #f4f8fc;
        }

        * { box-sizing: border-box; }
        html, body { min-height: 100%; }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            color: var(--ink);
            background: var(--bg);
        }

        a { color: inherit; }

        .top {
            background: #fff;
            border-bottom: 1px solid var(--line);
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
            grid-template-columns: minmax(0, 1fr) 340px;
            gap: 14px;
            padding: 16px 0 26px;
        }

        .card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: 0 10px 22px rgba(15, 23, 42, .06);
            overflow: hidden;
        }

        .head {
            padding: 18px;
            color: #fff;
            background: linear-gradient(120deg, var(--p), var(--s));
        }

        .head h1 {
            margin: 0 0 8px;
            font-size: clamp(1.6rem, 3vw, 2.35rem);
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
            padding: 18px;
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
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 14px;
        }

        .step {
            border: 1px solid var(--line);
            background: #f8fbff;
            border-radius: 14px;
            padding: 13px 14px;
            font-weight: 800;
            color: #334155;
            text-align: left;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s, transform .15s, background .15s;
        }

        .step small {
            display: block;
            font-weight: 600;
            color: var(--muted);
            margin-top: 4px;
        }

        .step.is-active {
            border-color: var(--p);
            background: linear-gradient(180deg, rgba(31,79,122,.09), #fff);
            box-shadow: 0 12px 30px rgba(31, 79, 122, .11);
            color: var(--p);
        }

        .panel {
            border: 1px solid var(--line);
            border-radius: 14px;
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
            border-radius: 16px;
            background: #fff;
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
            box-shadow: 0 16px 32px rgba(31,79,122,.12);
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

        .sidebar {
            display: grid;
            gap: 14px;
            align-content: start;
        }

        .summary {
            padding: 16px;
        }

        .summary-grid {
            display: grid;
            gap: 10px;
        }

        .summary-item {
            padding: 12px 14px;
            border-radius: 14px;
            background: #f8fbff;
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
            background: #f8fafc;
            border: 1px solid var(--line);
            font-size: .78rem;
            font-weight: 700;
            color: #334155;
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
            background: #f0fdf4;
            color: #166534;
        }

        .notice.warn {
            border: 1px solid #fde68a;
            background: #fffbeb;
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
            .plan-grid,
            .choice-row,
            .docs,
            .field-grid,
            .stepper {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
@php
    $docsConfigurados = array_values(array_filter($docsObrigatorios, fn ($doc) => $doc !== 'nenhum'));
    $estacaoRetiradaLabel = $selecionados['estacao_retirada'] !== ''
        ? ($estacoesRetirada[$selecionados['estacao_retirada']] ?? $selecionados['estacao_retirada'])
        : 'Não informada';
    $estacaoDevolucaoLabel = $selecionados['estacao_devolucao'] !== ''
        ? ($estacoesDevolucao[$selecionados['estacao_devolucao']] ?? $selecionados['estacao_devolucao'])
        : 'Não informada';
    $dataRetiradaLabel = trim(($selecionados['data_retirada'] ?: '---') . ' ' . ($selecionados['hora_retirada'] ?: ''));
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
                <h1>Mesmo padrão visual da reserva, agora para motorista de app</h1>
                <p>Escolha a estação, veja os planos disponíveis, siga para o login ou cadastro e envie os documentos exigidos pela empresa.</p>
            </div>

            <div class="body">
                <div class="stepper">
                    <button class="step is-active" type="button" data-step-btn="1">1. Planos<small>Escolha o melhor plano</small></button>
                    <button class="step" type="button" data-step-btn="2">2. Conta<small>Entrar ou cadastrar</small></button>
                    <button class="step" type="button" data-step-btn="3">3. Cadastro<small>Dados e documentos</small></button>
                </div>

                <section class="panel" data-step-panel="1">
                    <h2 class="panel-title">Planos para motorista de app</h2>
                    <p class="panel-desc">Selecione um plano e avance para o próximo passo do fluxo.</p>

                    @if($planos->isEmpty())
                        <div class="notice warn">Nenhum plano de motorista de app foi cadastrado para esta empresa ainda.</div>
                    @else
                        <div class="plan-grid">
                            @foreach($planos as $plano)
                                <article class="plan-card" data-plano-card data-plano-id="{{ $plano->id }}">
                                    <div class="plan-badge">{{ $plano->periodo_label }}</div>
                                    <h3 class="plan-title">{{ $plano->nome }}</h3>
                                    <div class="plan-price">R$ {{ number_format((float) $plano->valor_periodo, 2, ',', '.') }}</div>
                                    <div class="plan-meta">Km: <strong>{{ $plano->km_resumo }}</strong></div>
                                    <div class="plan-meta">Calção: <strong>{{ $plano->calcao !== null ? 'R$ ' . number_format((float) $plano->calcao, 2, ',', '.') : 'Sob consulta' }}</strong></div>
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
                    <h2 class="panel-title">Você já tem uma conta?</h2>
                    <p class="panel-desc">Escolha entre entrar com uma conta existente ou seguir para o cadastro de motorista de app.</p>
                    <div class="choice-row">
                        <button type="button" class="choice" data-account-choice="login">
                            <strong>Já tenho uma conta</strong>
                            <span>Vou entrar e continuar a solicitação com os meus dados existentes.</span>
                        </button>
                        <button type="button" class="choice" data-account-choice="register">
                            <strong>Quero me cadastrar</strong>
                            <span>Vou preencher os dados pessoais e enviar os documentos exigidos pela locadora.</span>
                        </button>
                    </div>
                    <div class="actions">
                        <button type="button" class="btn btn-secondary" data-step-back>Voltar</button>
                    </div>
                </section>

                <section class="panel hidden" data-step-panel="3">
                    <h2 class="panel-title">Cadastro do motorista de app</h2>
                    <p class="panel-desc">Preencha os dados abaixo. Os documentos dependem da configuração da empresa.</p>

                    <form>
                        <div class="field-grid">
                            <div class="field">
                                <label for="app_nome">Nome</label>
                                <input id="app_nome" type="text" required>
                            </div>
                            <div class="field">
                                <label for="app_cpf">CPF</label>
                                <input id="app_cpf" type="text" placeholder="000.000.000-00" required>
                            </div>
                            <div class="field">
                                <label for="app_data_nascimento">Data de nascimento</label>
                                <input id="app_data_nascimento" type="date" required>
                            </div>
                            <div class="field">
                                <label for="app_telefone">Telefone</label>
                                <input id="app_telefone" type="text" placeholder="(99) 99999-9999" required>
                            </div>
                            <div class="field">
                                <label for="app_email">E-mail</label>
                                <input id="app_email" type="email" required>
                            </div>
                            <div class="field field-full">
                                <label for="app_endereco">Endereço</label>
                                <input id="app_endereco" type="text" required>
                            </div>
                        </div>

                        <div class="docs">
                            @foreach($docsObrigatorios as $doc)
                                @php
                                    $label = match ($doc) {
                                        'cnh_definitiva' => 'CNH definitiva',
                                        'cnh_ear' => 'CNH com EAR',
                                        'comprovante_residencia' => 'Comprovante de residência',
                                        'comprovante_cadastro_app' => 'Comprovante de cadastro ativo no aplicativo',
                                        'documento_adicional' => $documentoAdicionalLabel !== '' ? $documentoAdicionalLabel : 'Documento adicional',
                                        default => null,
                                    };
                                @endphp
                                @if($label)
                                    <div class="doc-card">
                                        <strong>{{ $label }}</strong>
                                        <input type="file" name="docs[]" accept=".pdf,.jpg,.jpeg,.png" {{ $exigirAprovacaoDocumentos ? 'required' : '' }}>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @if(empty($docsConfigurados))
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

                        <div class="actions">
                            <button type="button" class="btn btn-secondary" data-step-back>Voltar</button>
                            <button type="button" class="btn btn-dark">Solicitar cadastro</button>
                        </div>
                    </form>
                </section>
            </div>
        </section>

        <aside class="sidebar">
            <section class="card summary">
                <div class="summary-grid">
                    <div class="summary-item">
                        <strong>Estação de retirada</strong>
                        <span>{{ $estacaoRetiradaLabel }}</span>
                    </div>
                    <div class="summary-item">
                        <strong>Data / hora</strong>
                        <span>{{ $dataRetiradaLabel }}</span>
                    </div>
                    <div class="summary-item">
                        <strong>Estação de devolução</strong>
                        <span>{{ $estacaoDevolucaoLabel }}</span>
                    </div>
                </div>

                <div class="summary-chip-list">
                    <span class="chip">{{ $empresa->nome }}</span>
                    <span class="chip">{{ $planos->count() }} plano(s) disponível(is)</span>
                    <span class="chip">{{ count($docsConfigurados) > 0 ? count($docsConfigurados) . ' documento(s)' : 'Sem documentos extras' }}</span>
                    <span class="chip">{{ $exigirAprovacaoDocumentos ? 'Aprovação ativa' : 'Sem aprovação manual' }}</span>
                </div>
            </section>

            <section class="card summary">
                <div class="panel-title" style="margin-bottom:8px;">Como funciona</div>
                <div class="muted" style="font-size:.92rem;line-height:1.6;">
                    O fluxo segue o mesmo padrão visual da reserva: escolha do plano, definição de conta e envio de documentos em etapas simples.
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
    const loginUrl = @json($botaoLoginUrl);
    let selectedPlanId = null;
    let step = 1;

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

    planCards.forEach((card) => {
        const button = card.querySelector('[data-select-plan]');

        const selectPlan = () => {
            selectedPlanId = card.dataset.planoId;
            planCards.forEach((el) => el.classList.toggle('is-selected', el === card));
            showStep(2);
        };

        button?.addEventListener('click', selectPlan);
        card.addEventListener('click', selectPlan);
    });

    accountChoices.forEach((choice) => {
        choice.addEventListener('click', () => {
            const mode = choice.dataset.accountChoice;
            if (mode === 'login') {
                window.location.href = loginUrl;
                return;
            }
            showStep(3);
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
            if (target === 3 && !selectedPlanId) {
                showStep(1);
                return;
            }
            showStep(target);
        });
    });
})();
</script>
</body>
</html>
