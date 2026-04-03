
@php
    $corPrimaria = $empresa->cor_primaria ?: '#1f4f7a';
    $corSecundaria = $empresa->cor_secundaria ?: '#14324d';
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reserva online | {{ $empresa->nome }}</title>
    <style>
        :root { --p: {{ $corPrimaria }}; --s: {{ $corSecundaria }}; --line: #dbe4ee; --ink: #0f172a; --muted: #64748b; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Arial, sans-serif; color: var(--ink); background: #f4f8fc; }
        .container { width: min(1160px, calc(100% - 24px)); margin: 0 auto; }
        .top { background: #fff; border-bottom: 1px solid var(--line); }
        .top .inner { min-height: 70px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .brand { display: flex; align-items: center; gap: 10px; font-weight: 700; }
        .brand img { width: 42px; height: 42px; border-radius: 10px; object-fit: contain; background: var(--p); border: 1px solid #e2e8f0; padding: 4px; }
        .back { text-decoration: none; border: 1px solid var(--line); border-radius: 999px; padding: 10px 14px; color: #334155; font-size: 13px; font-weight: 700; background: #fff; }
        .layout { display: grid; grid-template-columns: 1fr 300px; gap: 14px; padding: 16px 0 24px; }
        .card { background: #fff; border: 1px solid var(--line); border-radius: 14px; box-shadow: 0 10px 22px rgba(15, 23, 42, .06); }
        .head { padding: 18px; color: #fff; background: linear-gradient(120deg, var(--p), var(--s)); border-radius: 14px 14px 0 0; }
        .head h1 { margin: 0 0 8px; font-size: 24px; }
        .head p { margin: 0; font-size: 14px; opacity: .92; }
        .alerts { padding: 12px 18px 0; }
        .alert { border-radius: 10px; padding: 10px 12px; font-size: 14px; margin-bottom: 10px; }
        .ok { border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; }
        .err { border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; }
        .client-tabs { display: flex; gap: 8px; margin-bottom: 10px; flex-wrap: wrap; }
        .client-tab { border: 1px solid var(--line); background: #f8fbff; border-radius: 999px; padding: 8px 14px; font-size: 12px; font-weight: 700; cursor: pointer; color: #334155; }
        .client-tab.active { border-color: var(--p); background: #fff; color: var(--p); }
        .client-panel { border: 1px solid var(--line); border-radius: 12px; background: #f8fbff; padding: 12px; margin-bottom: 12px; }
        .client-panel.hidden { display: none; }
        .client-note { margin: 0 0 10px; font-size: 13px; color: #475569; }
        .prefill { margin: 12px 18px 0; border: 1px solid var(--line); border-radius: 12px; background: #f8fbff; padding: 10px 12px; font-size: 13px; color: #334155; display: none; }
        .prefill strong { display: block; font-size: 11px; text-transform: uppercase; color: #64748b; margin-bottom: 4px; }
        .prefill-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .steps { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; padding: 12px 18px 0; }
        .step-pill { border: 1px solid var(--line); background: #f8fbff; border-radius: 10px; min-height: 46px; display: flex; align-items: center; gap: 8px; padding: 8px; font-size: 12px; font-weight: 700; color: #334155; cursor: pointer; }
        .step-pill .num { width: 22px; height: 22px; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; background: #dbeafe; color: #1d4ed8; }
        .step-pill.active { border-color: var(--p); background: #fff; }
        .step-pill.active .num { background: var(--p); color: #fff; }
        .body { padding: 14px 18px 18px; }
        .panel { display: none; }
        .panel.active { display: block; }
        .title { margin: 0 0 8px; font-size: 18px; }
        .subtitle { margin: 0 0 14px; color: var(--muted); font-size: 13px; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
        .field { display: flex; flex-direction: column; gap: 6px; }
        .field-full { grid-column: 1 / -1; }
        label { font-size: 12px; color: #334155; font-weight: 700; }
        input, select { min-height: 40px; border: 1px solid var(--line); border-radius: 9px; padding: 8px 10px; font-size: 14px; background: #f8fbff; color: var(--ink); }
        input:focus, select:focus { outline: none; border-color: var(--p); box-shadow: 0 0 0 2px rgba(37, 99, 235, .12); background: #fff; }
        .feedback { margin-top: 10px; border: 1px solid var(--line); border-radius: 9px; background: #f8fbff; padding: 9px 10px; font-size: 13px; color: #475569; }
        .feedback.ok { border-color: #bbf7d0; background: #f0fdf4; color: #166534; }
        .feedback.warn { border-color: #fecaca; background: #fef2f2; color: #991b1b; }
        .inline { display: flex; gap: 8px; align-items: end; }
        .btn { min-height: 40px; border: 0; border-radius: 999px; padding: 9px 14px; font-size: 13px; font-weight: 700; cursor: pointer; }
        .btn-primary { background: var(--p); color: #fff; }
        .btn-secondary { border: 1px solid var(--line); background: #fff; color: #334155; }
        .btn-accent { background: #f0b429; color: #111827; }
        .opcionais { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 10px; }
        .opcional { border: 1px solid var(--line); border-radius: 10px; padding: 10px; background: #fff; }
        .opcional .topline { display: flex; justify-content: space-between; gap: 8px; font-size: 13px; font-weight: 700; }
        .opcional .price { font-size: 12px; color: var(--muted); margin-top: 4px; }
        .qty { display: inline-flex; gap: 5px; align-items: center; }
        .qty button { width: 24px; height: 24px; border: 1px solid var(--line); border-radius: 7px; background: #f8fbff; font-weight: 800; cursor: pointer; }
        .actions { margin-top: 16px; display: flex; justify-content: space-between; gap: 8px; }
        .right { display: flex; gap: 8px; }
        .hidden { display: none !important; }
        .review { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .review .box { border: 1px solid var(--line); border-radius: 10px; background: #f8fbff; padding: 9px; }
        .review strong { display: block; margin-bottom: 4px; font-size: 11px; color: #64748b; text-transform: uppercase; }
        .review span { font-size: 13px; color: var(--ink); line-height: 1.45; }
        .summary { padding: 14px; position: sticky; top: 85px; }
        .summary h3 { margin: 0 0 10px; font-size: 16px; }
        .sum { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; padding: 7px 0; border-bottom: 1px dashed var(--line); }
        .sum.total { border-bottom: 0; font-weight: 800; font-size: 16px; margin-top: 6px; }
        .sum-list { list-style: none; margin: 8px 0 0; padding: 0; display: grid; gap: 6px; }
        .sum-list li { border: 1px solid var(--line); border-radius: 8px; background: #f8fbff; padding: 6px 8px; font-size: 12px; color: #334155; }
        @media (max-width: 1020px) { .layout { grid-template-columns: 1fr; } .summary { position: static; } }
        @media (max-width: 740px) { .steps { grid-template-columns: 1fr 1fr; } .grid, .opcionais, .review, .prefill-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header class="top">
        <div class="container inner">
            <a class="brand" href="{{ route('site.apresentacao') }}">
                @if(!empty($empresa->logo_url))
                    <img src="{{ $empresa->logo_url }}" alt="Logo {{ $empresa->nome }}">
                @endif
                <span>{{ $empresa->nome }}</span>
            </a>
            <a class="back" href="{{ route('site.apresentacao') }}">Voltar para o site</a>
        </div>
    </header>

    <main class="container layout">
        <section class="card">
            <div class="head">
                <h1>Reserva em passos</h1>
                <p>Preencha viagem, escolha veiculo, informe seus dados e finalize.</p>
            </div>

            <div class="alerts">
                @if(session('success'))
                    <div class="alert ok">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert err">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="prefill" id="prefillSummary">
                <strong>Resumo da selecao</strong>
                <div class="prefill-grid">
                    <span id="prefRet">Retirada: -</span>
                    <span id="prefDev">Devolucao: -</span>
                    <span id="prefDatas">Datas: -</span>
                </div>
            </div>

            <div class="steps" id="stepsHeader">
                <div class="step-pill active" data-step="1"><span class="num">1</span><span>Viagem</span></div>
                <div class="step-pill" data-step="2"><span class="num">2</span><span>Veiculo</span></div>
                <div class="step-pill" data-step="3"><span class="num">3</span><span>Cliente</span></div>
                <div class="step-pill" data-step="4"><span class="num">4</span><span>Revisao</span></div>
            </div>

            <form id="reservaForm" class="body" method="POST" action="{{ route('site.reserva.store') }}">
                @csrf
                <input type="hidden" id="opcionais_payload" name="opcionais_payload" value="{{ old('opcionais_payload', '') }}">
                <input type="hidden" id="modo_cliente" name="modo_cliente" value="cadastro">

                <section class="panel active" data-panel="1">
                    <h2 class="title">Onde e quando?</h2>
                    <p class="subtitle">Defina retirada, devolucao e datas.</p>
                    <div class="grid">
                        <div class="field">
                            <label for="estacao_retirada_id">Estacao de retirada</label>
                            <select id="estacao_retirada_id" name="estacao_retirada_id" required>
                                <option value="">Selecione...</option>
                                @foreach($estacoes as $estacao)
                                    <option value="{{ $estacao['id'] }}" {{ (string) old('estacao_retirada_id', $prefill['estacao_retirada_id'] ?? '') === (string) $estacao['id'] ? 'selected' : '' }}>
                                        {{ $estacao['nome'] }}{{ $estacao['cidade'] !== '' ? ' - ' . $estacao['cidade'] : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="estacao_devolucao_id">Estacao de devolucao</label>
                            <select id="estacao_devolucao_id" name="estacao_devolucao_id" required>
                                <option value="">Selecione...</option>
                                @foreach($estacoes as $estacao)
                                    <option value="{{ $estacao['id'] }}" {{ (string) old('estacao_devolucao_id', $prefill['estacao_devolucao_id'] ?? '') === (string) $estacao['id'] ? 'selected' : '' }}>
                                        {{ $estacao['nome'] }}{{ $estacao['cidade'] !== '' ? ' - ' . $estacao['cidade'] : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="data_inicio">Data e hora de retirada</label>
                            <input id="data_inicio" type="datetime-local" name="data_inicio" value="{{ old('data_inicio', $prefill['data_inicio'] ?? '') }}" required>
                        </div>
                        <div class="field">
                            <label for="data_fim">Data e hora de devolucao</label>
                            <input id="data_fim" type="datetime-local" name="data_fim" value="{{ old('data_fim', $prefill['data_fim'] ?? '') }}" required>
                        </div>
                    </div>
                    <div class="feedback" id="step1Feedback">Preencha os dados para continuar.</div>
                </section>

                <section class="panel" data-panel="2">
                    <h2 class="title">Veiculo e adicionais</h2>
                    <p class="subtitle">Escolha grupo, modelo e opcionais.</p>
                    <div class="grid">
                        <div class="field">
                            <label for="grupo_frota_id">Grupo</label>
                            <select id="grupo_frota_id" name="grupo_frota_id" required>
                                <option value="">Selecione...</option>
                                @foreach($gruposFrota as $grupo)
                                    <option value="{{ $grupo['id'] }}" data-tarifa="{{ $grupo['valor_diario'] }}" {{ (string) old('grupo_frota_id', '') === (string) $grupo['id'] ? 'selected' : '' }}>
                                        {{ $grupo['nome'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label for="modelo_id">Modelo</label>
                            <select id="modelo_id" name="modelo_id" required disabled>
                                <option value="">Selecione o grupo...</option>
                            </select>
                        </div>
                        <div class="field field-full">
                            <label for="cupom_codigo">Cupom (opcional)</label>
                            <div class="inline">
                                <input id="cupom_codigo" name="cupom_codigo" type="text" maxlength="255" value="{{ old('cupom_codigo', '') }}" placeholder="Digite o codigo">
                                <button class="btn btn-accent" type="button" id="btnCupom">Aplicar cupom</button>
                            </div>
                        </div>
                    </div>
                    <div class="feedback" id="cupomFeedback">Nenhum cupom aplicado.</div>
                    <div class="feedback" id="disponibilidadeFeedback">Informe o grupo para carregar disponibilidade.</div>
                    <div id="opcionaisGrid" class="opcionais"></div>
                </section>
                <section class="panel" data-panel="3">
                    <h2 class="title">Dados do cliente</h2>
                    <p class="subtitle">Escolha se voce ja possui acesso ou se vai se cadastrar agora.</p>

                    <div class="client-tabs">
                        <button type="button" class="client-tab active" data-client-tab="cadastro">Cadastrar</button>
                        <button type="button" class="client-tab" data-client-tab="login">Ja tenho conta</button>
                    </div>

                    <div id="clientLoginPanel" class="client-panel hidden">
                        @if(!empty($isAuth) && $isAuth)
                            <p class="client-note">Voce ja esta logado como {{ $authUserName ?: 'usuario' }}.</p>
                        @else
                            <p class="client-note">Para continuar com o pagamento, faca login na sua conta.</p>
                            @if(!empty($loginRedirectUrl))
                                <a class="btn btn-primary" href="{{ $loginRedirectUrl }}">Fazer login</a>
                            @else
                                <div class="feedback warn">Login indisponivel no momento.</div>
                            @endif
                        @endif
                    </div>

                    <div id="clientCadastroPanel" class="client-panel">
                        <p class="client-note">Se o e-mail ja existir, voce precisara fazer login.</p>
                        <div class="grid" id="clientCadastroFields">
                            <div class="field"><label for="nome">Nome</label><input id="nome" name="nome" value="{{ old('nome', '') }}" maxlength="255" required></div>
                            <div class="field"><label for="email">E-mail</label><input id="email" type="email" name="email" value="{{ old('email', '') }}" maxlength="150" required></div>
                            <div class="field"><label for="sexo">Sexo</label><select id="sexo" name="sexo" required><option value="">Selecione...</option><option value="M" {{ old('sexo', '') === 'M' ? 'selected' : '' }}>Masculino</option><option value="F" {{ old('sexo', '') === 'F' ? 'selected' : '' }}>Feminino</option><option value="O" {{ old('sexo', '') === 'O' ? 'selected' : '' }}>Outro</option></select></div>
                            <div class="field"><label for="data_nascimento">Data nascimento</label><input id="data_nascimento" type="date" name="data_nascimento" value="{{ old('data_nascimento', '') }}" required></div>
                            <div class="field field-full"><label for="local_nascimento">Local nascimento</label><input id="local_nascimento" name="local_nascimento" value="{{ old('local_nascimento', '') }}" maxlength="100" required></div>
                            <div class="field"><label for="numero_identificacao">Numero identificacao</label><input id="numero_identificacao" name="numero_identificacao" value="{{ old('numero_identificacao', '') }}" maxlength="20" required></div>
                            <div class="field"><label for="telefone">Telefone</label><input id="telefone" name="telefone" value="{{ old('telefone', '') }}" maxlength="20" required></div>
                            <div class="field"><label for="celular">Celular</label><input id="celular" name="celular" value="{{ old('celular', '') }}" maxlength="20" placeholder="(99) 99999-9999" required></div>
                            <div class="field"><label for="pais_sigla">Pais</label><select id="pais_sigla" name="pais_sigla" required><option value="">Selecione...</option>@foreach($paises as $sigla => $nomePais)<option value="{{ $sigla }}" {{ old('pais_sigla', '') === $sigla ? 'selected' : '' }}>{{ $nomePais }}</option>@endforeach</select></div>
                            <div class="field"><label for="zip_code">Zip code</label><input id="zip_code" name="zip_code" value="{{ old('zip_code', '') }}" maxlength="20" required></div>
                            <div class="field"><label for="cidade">Cidade</label><input id="cidade" name="cidade" value="{{ old('cidade', '') }}" maxlength="100" required></div>
                            <div class="field field-full"><label for="endereco">Endereco</label><input id="endereco" name="endereco" value="{{ old('endereco', '') }}" maxlength="255" required></div>
                            <div class="field"><label for="numero_carta_conducao">Numero carta conducao</label><input id="numero_carta_conducao" name="numero_carta_conducao" value="{{ old('numero_carta_conducao', '') }}" maxlength="20" required></div>
                            <div class="field"><label for="local_emissao_carta_conducao">Local emissao carta</label><input id="local_emissao_carta_conducao" name="local_emissao_carta_conducao" value="{{ old('local_emissao_carta_conducao', '') }}" maxlength="100" required></div>
                            <div class="field"><label for="pais_emissao_carta_conducao">Pais emissao carta</label><select id="pais_emissao_carta_conducao" name="pais_emissao_carta_conducao" required><option value="">Selecione...</option>@foreach($paises as $sigla => $nomePais)<option value="{{ $sigla }}" {{ old('pais_emissao_carta_conducao', '') === $sigla ? 'selected' : '' }}>{{ $nomePais }}</option>@endforeach</select></div>
                            <div class="field"><label for="data_emissao_carta_conducao">Data emissao carta</label><input id="data_emissao_carta_conducao" type="date" name="data_emissao_carta_conducao" value="{{ old('data_emissao_carta_conducao', '') }}" required></div>
                            <div class="field"><label for="data_validade_carta_conducao">Data validade carta (opcional)</label><input id="data_validade_carta_conducao" type="date" name="data_validade_carta_conducao" value="{{ old('data_validade_carta_conducao', '') }}"></div>
                        </div>
                    </div>
                    <div class="feedback" id="clientFeedback">Escolha uma opcao para continuar.</div>
                </section>

                <section class="panel" data-panel="4">
                    <h2 class="title">Revisao final</h2>
                    <p class="subtitle">Confira e finalize sua reserva.</p>
                    <div class="review">
                        <div class="box"><strong>Retirada</strong><span id="rRetirada">-</span></div>
                        <div class="box"><strong>Devolucao</strong><span id="rDevolucao">-</span></div>
                        <div class="box"><strong>Grupo / Modelo</strong><span id="rVeiculo">-</span></div>
                        <div class="box"><strong>Cliente</strong><span id="rCliente">-</span></div>
                        <div class="box field-full"><strong>Opcionais</strong><span id="rOpcionais">Sem opcionais.</span></div>
                    </div>
                    <div class="feedback" style="margin-top: 10px;">Ao finalizar, a reserva sera registrada no sistema.</div>
                </section>

                <div class="actions">
                    <button type="button" class="btn btn-secondary hidden" id="btnPrev">Voltar</button>
                    <div class="right">
                        <button type="button" class="btn btn-primary" id="btnNext">Continuar</button>
                        <button type="submit" class="btn btn-primary hidden" id="btnSubmit">Finalizar reserva</button>
                    </div>
                </div>
            </form>
        </section>

        <aside class="card summary">
            <h3>Resumo</h3>
            <div class="sum"><span>Diarias</span><strong id="sDias">0 dias</strong></div>
            <div class="sum"><span>Tarifa base</span><strong id="sTarifa">{{ $simboloMoedaEmpresa }} 0,00</strong></div>
            <div class="sum"><span>Opcionais</span><strong id="sOpc">{{ $simboloMoedaEmpresa }} 0,00</strong></div>
            <div class="sum"><span>Desconto</span><strong id="sDesc">0%</strong></div>
            <div class="sum total"><span>Total</span><strong id="sTotal">{{ $simboloMoedaEmpresa }} 0,00</strong></div>
            <ul id="sLista" class="sum-list"></ul>
        </aside>
    </main>

    <script>
        (function () {
            const moeda = @json($moedaEmpresa);
            const disponibilidadeUrl = @json(route('site.reserva.disponibilidade'));
            const cupomUrl = @json(route('site.reserva.verificarCupom'));
            const oldModelId = @json(old('modelo_id', ''));
            const oldOpcionaisPayload = @json(old('opcionais_payload', ''));
            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const isAuth = @json($isAuth);
            const authUserName = @json($authUserName);
            const authUserEmail = @json($authUserEmail);
            const loginRedirectUrl = @json($loginRedirectUrl);
            const stepFromUrl = Number(new URLSearchParams(window.location.search).get('step'));
            const initialStep = !Number.isNaN(stepFromUrl) && stepFromUrl >= 1 && stepFromUrl <= 4 ? stepFromUrl : 2;
            const state = { step: initialStep, tarifa: 0, dias: 0, cupom: null, opcionais: {} };
            const panels = [...document.querySelectorAll('[data-panel]')];
            const pills = [...document.querySelectorAll('[data-step]')];
            const form = document.getElementById('reservaForm');
            const grupo = document.getElementById('grupo_frota_id');
            const modelo = document.getElementById('modelo_id');
            const dataInicio = document.getElementById('data_inicio');
            const dataFim = document.getElementById('data_fim');
            const cupomInput = document.getElementById('cupom_codigo');
            const opcionaisGrid = document.getElementById('opcionaisGrid');
            const clientTabs = [...document.querySelectorAll('[data-client-tab]')];
            const clientLoginPanel = document.getElementById('clientLoginPanel');
            const clientCadastroPanel = document.getElementById('clientCadastroPanel');
            const clientCadastroFields = document.querySelectorAll('#clientCadastroFields input, #clientCadastroFields select, #clientCadastroFields textarea');
            const clientFeedback = document.getElementById('clientFeedback');
            const modoInput = document.getElementById('modo_cliente');
            let clientMode = isAuth ? 'login' : 'cadastro';

            function money(v) { return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: moeda || 'BRL' }).format(Number(v || 0)); }
            function dateLabel(v) { const d = new Date(v); if (Number.isNaN(d.getTime())) return '-'; return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(d); }
            function dias() {
                const i = new Date(dataInicio.value);
                const f = new Date(dataFim.value);
                if (Number.isNaN(i.getTime()) || Number.isNaN(f.getTime())) return 0;
                const diff = f - i;
                if (diff < 86400000) return 0;
                return Math.ceil(diff / 86400000);
            }
            function opcUnit(op) { return op.tipo_cobranca === 'por_dia' ? Number(op.preco_por_dia || 0) : Number(op.preco_integral || 0); }
            function opcTotal() { let t = 0; Object.values(state.opcionais).forEach(function (op) { const q = Number(op.quantidade || 0); if (q <= 0) return; const u = opcUnit(op); t += op.tipo_cobranca === 'por_dia' ? u * q * state.dias : u * q; }); return t; }
            function total() { const base = state.tarifa * state.dias; const subtotal = base + opcTotal(); return subtotal - (subtotal * (Number(state.cupom?.valor || 0) / 100)); }
            function feedback(id, msg, tone) { const el = document.getElementById(id); el.textContent = msg; el.classList.remove('ok', 'warn'); if (tone) el.classList.add(tone); }
            function refresh() {
                state.dias = dias();
                document.getElementById('sDias').textContent = `${state.dias || 0} dias`;
                document.getElementById('sTarifa').textContent = money(state.tarifa * state.dias);
                document.getElementById('sOpc').textContent = money(opcTotal());
                document.getElementById('sDesc').textContent = `${Number(state.cupom?.valor || 0)}%`;
                document.getElementById('sTotal').textContent = money(total());
                const list = document.getElementById('sLista');
                const itens = Object.values(state.opcionais).filter(op => Number(op.quantidade || 0) > 0);
                list.innerHTML = itens.map(function (op) { const linha = op.tipo_cobranca === 'por_dia' ? opcUnit(op) * op.quantidade * state.dias : opcUnit(op) * op.quantidade; return `<li>${op.nome} (${op.quantidade}x) - ${money(linha)}</li>`; }).join('');
            }
            function review() {
                const eRet = document.getElementById('estacao_retirada_id');
                const eDev = document.getElementById('estacao_devolucao_id');
                document.getElementById('rRetirada').textContent = `${eRet.options[eRet.selectedIndex]?.text || '-'} - ${dateLabel(dataInicio.value)}`;
                document.getElementById('rDevolucao').textContent = `${eDev.options[eDev.selectedIndex]?.text || '-'} - ${dateLabel(dataFim.value)}`;
                document.getElementById('rVeiculo').textContent = `${grupo.options[grupo.selectedIndex]?.text || '-'} / ${modelo.options[modelo.selectedIndex]?.text || '-'}`;
                const nomeCliente = document.getElementById('nome')?.value || (isAuth ? (authUserName || 'Usuario logado') : '-');
                const emailCliente = document.getElementById('email')?.value || (isAuth ? (authUserEmail || '-') : '-');
                document.getElementById('rCliente').textContent = `${nomeCliente} - ${emailCliente}`;
                const itens = Object.values(state.opcionais).filter(op => Number(op.quantidade || 0) > 0).map(op => `${op.nome} (${op.quantidade}x)`);
                document.getElementById('rOpcionais').textContent = itens.length ? itens.join(', ') : 'Sem opcionais.';
            }
            function setClientMode(mode) {
                clientMode = mode;
                if (modoInput) {
                    modoInput.value = mode;
                }

                if (clientTabs.length) {
                    clientTabs.forEach((btn) => {
                        btn.classList.toggle('active', btn.dataset.clientTab === mode);
                    });
                }

                if (clientLoginPanel) {
                    clientLoginPanel.classList.toggle('hidden', mode !== 'login');
                }

                if (clientCadastroPanel) {
                    clientCadastroPanel.classList.toggle('hidden', mode !== 'cadastro');
                }

                clientCadastroFields.forEach((field) => {
                    field.disabled = mode !== 'cadastro';
                });
            }
            function updatePrefill() {
                const prefillBox = document.getElementById('prefillSummary');
                if (!prefillBox) return;
                const eRet = document.getElementById('estacao_retirada_id');
                const eDev = document.getElementById('estacao_devolucao_id');
                const hasAny = Boolean(eRet.value || eDev.value || dataInicio.value || dataFim.value);
                prefillBox.style.display = hasAny ? 'block' : 'none';
                document.getElementById('prefRet').textContent = `Retirada: ${eRet.options[eRet.selectedIndex]?.text || '-'}`;
                document.getElementById('prefDev').textContent = `Devolucao: ${eDev.options[eDev.selectedIndex]?.text || '-'}`;
                document.getElementById('prefDatas').textContent = `Datas: ${dateLabel(dataInicio.value)} ate ${dateLabel(dataFim.value)}`;
            }
            function renderStep() {
                pills.forEach(p => p.classList.toggle('active', Number(p.dataset.step) === state.step));
                panels.forEach(p => p.classList.toggle('active', Number(p.dataset.panel) === state.step));
                document.getElementById('btnPrev').classList.toggle('hidden', state.step === 1);
                document.getElementById('btnNext').classList.toggle('hidden', state.step === 4);
                document.getElementById('btnSubmit').classList.toggle('hidden', state.step !== 4);
                if (state.step === 4) review();
            }
            function validStep1(show) {
                if (!document.getElementById('estacao_retirada_id').value || !document.getElementById('estacao_devolucao_id').value || !dataInicio.value || !dataFim.value) {
                    if (show) feedback('step1Feedback', 'Preencha estacoes e datas.', 'warn');
                    return false;
                }
                const now = new Date();
                const retirada = new Date(dataInicio.value);
                if (!Number.isNaN(retirada.getTime()) && retirada < now) {
                    if (show) feedback('step1Feedback', 'A retirada nao pode ser antes de agora.', 'warn');
                    return false;
                }
                if (dias() <= 0) {
                    if (show) feedback('step1Feedback', 'A devolucao deve ser ao menos 1 dia depois da retirada.', 'warn');
                    return false;
                }
                if (show) feedback('step1Feedback', 'Passo valido.', 'ok');
                return true;
            }
            function validStep2(show) {
                if (!grupo.value) { if (show) feedback('disponibilidadeFeedback', 'Selecione um grupo.', 'warn'); return false; }
                if (!modelo.value) { if (show) feedback('disponibilidadeFeedback', 'Selecione um modelo.', 'warn'); return false; }
                return true;
            }
            function validStep3() {
                if (isAuth) {
                    return true;
                }

                if (clientMode === 'login') {
                    if (clientFeedback) {
                        feedback('clientFeedback', 'Faca login para continuar.', 'warn');
                    }
                    return false;
                }

                const ids = ['nome','email','sexo','data_nascimento','local_nascimento','numero_identificacao','telefone','celular','pais_sigla','zip_code','cidade','endereco','numero_carta_conducao','local_emissao_carta_conducao','pais_emissao_carta_conducao','data_emissao_carta_conducao'];
                for (const id of ids) { const field = document.getElementById(id); if (field && !field.checkValidity()) { field.reportValidity(); return false; } }
                return true;
            }
            function renderModelos(list) {
                modelo.innerHTML = '<option value="">Selecione...</option>';
                if (!Array.isArray(list) || list.length === 0) { modelo.disabled = true; return; }
                list.forEach(function (m) { const o = document.createElement('option'); o.value = String(m.id); o.textContent = m.nome; modelo.appendChild(o); });
                modelo.disabled = false;
                if (oldModelId) modelo.value = String(oldModelId);
            }
            function normalizeOpc(tipo) {
                const all = [];
                (tipo || []).forEach(function (g) { (g.opcionais || []).forEach(function (o) { all.push({ id: Number(o.id), nome: o.nome || '', tipo_cobranca: o.tipo_cobranca || '', preco_por_dia: Number(o.preco_por_dia || 0), preco_integral: Number(o.preco_integral || 0) }); }); });
                return all;
            }
            function renderOpc(tipo) {
                const items = normalizeOpc(tipo);
                const valid = {};
                items.forEach(i => valid[i.id] = true);
                Object.keys(state.opcionais).forEach(id => { if (!valid[id]) delete state.opcionais[id]; });
                if (!items.length) { opcionaisGrid.innerHTML = '<div class="feedback">Nenhum opcional disponivel.</div>'; refresh(); return; }
                items.forEach(function (i) { if (!state.opcionais[i.id]) state.opcionais[i.id] = { ...i, quantidade: 0 }; });
                opcionaisGrid.innerHTML = items.map(function (o) {
                    const q = Number(state.opcionais[o.id]?.quantidade || 0);
                    const label = o.tipo_cobranca === 'por_dia' ? `${money(o.preco_por_dia)} / dia` : `${money(o.preco_integral)} / reserva`;
                    return `<article class="opcional"><div class="topline"><span>${o.nome}</span><div class="qty"><button type="button" data-a="m" data-id="${o.id}">-</button><span id="q-${o.id}">${q}</span><button type="button" data-a="p" data-id="${o.id}">+</button></div></div><div class="price">${label}</div></article>`;
                }).join('');
                refresh();
            }
            async function loadDisponibilidade() {
                if (!validStep1(false) || !grupo.value) return;
                const params = new URLSearchParams({ grupo_frota_id: grupo.value, estacao_retirada_id: document.getElementById('estacao_retirada_id').value, estacao_devolucao_id: document.getElementById('estacao_devolucao_id').value, data_inicio: dataInicio.value, data_fim: dataFim.value });
                feedback('disponibilidadeFeedback', 'Carregando disponibilidade...', null);
                try {
                    const res = await fetch(`${disponibilidadeUrl}?${params.toString()}`, { headers: { Accept: 'application/json' } });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Falha ao carregar.');
                    state.tarifa = Number(data.grupo_frota?.valor_diario || 0);
                    renderModelos(data.modelos || []);
                    renderOpc(data.tipo_opcional || []);
                    if (data.aviso_modelos) feedback('disponibilidadeFeedback', data.aviso_modelos, 'warn');
                    else feedback('disponibilidadeFeedback', `Disponibilidade carregada. Veiculos na estacao: ${Number(data.qtd_veiculos_estacao || 0)}.`, 'ok');
                } catch (e) {
                    feedback('disponibilidadeFeedback', e.message || 'Erro ao carregar disponibilidade.', 'warn');
                    modelo.disabled = true;
                    modelo.innerHTML = '<option value="">Selecione...</option>';
                    opcionaisGrid.innerHTML = '';
                    state.tarifa = 0;
                    state.opcionais = {};
                    refresh();
                }
            }
            async function applyCupom() {
                const code = cupomInput.value.trim();
                if (!code) { state.cupom = null; feedback('cupomFeedback', 'Nenhum cupom aplicado.', null); refresh(); return; }
                feedback('cupomFeedback', 'Validando cupom...', null);
                try {
                    const res = await fetch(cupomUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, Accept: 'application/json' }, body: JSON.stringify({ cupom: code }) });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Falha ao validar cupom.');
                    if (!data.cupom) { state.cupom = null; feedback('cupomFeedback', 'Cupom nao encontrado ou inativo.', 'warn'); refresh(); return; }
                    state.cupom = data.cupom;
                    feedback('cupomFeedback', `Cupom aplicado: ${data.cupom.codigo} (${data.cupom.valor}% de desconto).`, 'ok');
                    refresh();
                } catch (e) {
                    state.cupom = null;
                    feedback('cupomFeedback', e.message || 'Erro ao validar cupom.', 'warn');
                    refresh();
                }
            }
            function preparePayload() {
                const itens = Object.values(state.opcionais).filter(o => Number(o.quantidade || 0) > 0).map(o => ({ id: Number(o.id), quantidade: Number(o.quantidade || 0) }));
                document.getElementById('opcionais_payload').value = JSON.stringify(itens);
            }
            function maskCelular() {
                const c = document.getElementById('celular');
                c.addEventListener('input', function () {
                    const d = c.value.replace(/\D/g, '').slice(0, 11);
                    if (d.length <= 2) { c.value = d; return; }
                    if (d.length <= 7) { c.value = `(${d.slice(0, 2)}) ${d.slice(2)}`; return; }
                    c.value = `(${d.slice(0, 2)}) ${d.slice(2, 7)}-${d.slice(7)}`;
                });
            }
            pills.forEach(function (pill) {
                pill.addEventListener('click', function () {
                    let to = Number(pill.dataset.step || 1);
                    if (isAuth && to === 3) {
                        to = 4;
                    }
                    if (to > state.step) { if (state.step === 1 && !validStep1(true)) return; if (state.step === 2 && !validStep2(true)) return; if (state.step === 3 && !validStep3()) return; }
                    state.step = to; renderStep(); refresh();
                });
            });
            document.getElementById('btnPrev').addEventListener('click', function () {
                if (isAuth && state.step === 4) {
                    state.step = 2;
                } else {
                    state.step = Math.max(1, state.step - 1);
                }
                renderStep();
                refresh();
            });
            document.getElementById('btnNext').addEventListener('click', function () {
                if (state.step === 1 && !validStep1(true)) return;
                if (state.step === 2 && !validStep2(true)) return;
                if (state.step === 3 && !validStep3()) return;
                if (state.step === 2 && isAuth) {
                    state.step = 4;
                } else {
                    state.step = Math.min(4, state.step + 1);
                }
                renderStep();
                refresh();
            });
            form.addEventListener('submit', function (e) { if (!validStep1(true) || !validStep2(true) || !validStep3()) { e.preventDefault(); return; } preparePayload(); });
            dataInicio.addEventListener('change', function () {
                const i = new Date(dataInicio.value);
                if (!Number.isNaN(i.getTime())) {
                    dataFim.setAttribute('min', new Date(i.getTime() + 86400000).toISOString().slice(0, 16));
                }
                refresh();
                updatePrefill();
                if (grupo.value) loadDisponibilidade();
            });
            dataFim.addEventListener('change', function () { refresh(); updatePrefill(); if (grupo.value) loadDisponibilidade(); });
            document.getElementById('estacao_retirada_id').addEventListener('change', function () { refresh(); updatePrefill(); if (grupo.value) loadDisponibilidade(); });
            document.getElementById('estacao_devolucao_id').addEventListener('change', function () { refresh(); updatePrefill(); if (grupo.value) loadDisponibilidade(); });
            grupo.addEventListener('change', function () { state.tarifa = Number(grupo.selectedOptions[0]?.dataset.tarifa || 0); state.opcionais = {}; modelo.innerHTML = '<option value="">Selecione o grupo...</option>'; modelo.disabled = true; opcionaisGrid.innerHTML = ''; refresh(); loadDisponibilidade(); });
            modelo.addEventListener('change', refresh);
            document.getElementById('btnCupom').addEventListener('click', applyCupom);
            opcionaisGrid.addEventListener('click', function (ev) { const t = ev.target; if (!(t instanceof HTMLElement)) return; const id = Number(t.dataset.id || 0); if (!id || !state.opcionais[id]) return; if (t.dataset.a === 'p') state.opcionais[id].quantidade = Math.max(0, Number(state.opcionais[id].quantidade || 0) + 1); if (t.dataset.a === 'm') state.opcionais[id].quantidade = Math.max(0, Number(state.opcionais[id].quantidade || 0) - 1); const span = document.getElementById(`q-${id}`); if (span) span.textContent = String(state.opcionais[id].quantidade); refresh(); });
            clientTabs.forEach((btn) => {
                btn.addEventListener('click', () => {
                    if (isAuth) {
                        return;
                    }

                    const mode = btn.dataset.clientTab || 'cadastro';
                    setClientMode(mode);
                    if (clientFeedback) {
                        feedback('clientFeedback', mode === 'login' ? 'Faca login para continuar.' : 'Preencha seus dados para seguir.', null);
                    }
                });
            });
            if (isAuth) {
                clientTabs.forEach((btn) => { btn.disabled = true; });
                setClientMode('login');
            } else {
                setClientMode('cadastro');
            }
            maskCelular();
            const now = new Date();
            if (dataInicio) {
                const minInicio = new Date(now.getTime());
                dataInicio.setAttribute('min', minInicio.toISOString().slice(0, 16));
            }
            if (dataFim && dataInicio.value) {
                const i = new Date(dataInicio.value);
                if (!Number.isNaN(i.getTime())) {
                    dataFim.setAttribute('min', new Date(i.getTime() + 86400000).toISOString().slice(0, 16));
                }
            }
            renderStep();
            refresh();
            updatePrefill();
            (async function initOld() {
                if (grupo.value) {
                    await loadDisponibilidade();
                    if (oldOpcionaisPayload) {
                        try {
                            const arr = JSON.parse(oldOpcionaisPayload);
                            if (Array.isArray(arr)) arr.forEach(function (item) { const id = Number(item.id || 0); const q = Number(item.quantidade || 0); if (!id || q <= 0 || !state.opcionais[id]) return; state.opcionais[id].quantidade = q; const span = document.getElementById(`q-${id}`); if (span) span.textContent = String(q); });
                        } catch (_) {}
                    }
                }
                if (cupomInput.value.trim()) await applyCupom();
                refresh();
            })();
        })();
    </script>
</body>
</html>
